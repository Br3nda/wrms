<?php

dbg_error_log("PUT", "method handler");

// The PUT method is not sent with any wrapping XML so we simply store it
// after constructing an eTag and getting a name for it...

$fh = fopen('/tmp/PUT.txt','w');
foreach( $raw_headers AS $k => $v ) {
  fwrite($fh,sprintf( "$k: %s\n", $v ));
}
fwrite($fh,"\n");
fwrite($fh,$raw_post);
fclose($fh);

$etag_none_match = str_replace('"','',$_SERVER["HTTP_IF_NONE_MATCH"]);
$etag_match = str_replace('"','',$_SERVER["HTTP_IF_MATCH"]);

$put_path = $_SERVER['PATH_INFO'];
list( $junk, $user, $ts_id ) = preg_split( '#/#', $put_path );
$ts_id = intval($ts_id);
dbg_error_log('PUT', "User: %s, TS_ID: %s", $user, $ts_id );

include_once("vEvent.php");
$ev = new vEvent(array( 'vevent' => $raw_post ));

/**
* Attempt to discover a WR that this is related to
*/
if ( preg_match( "/^(wr)?[^1-9]?([1-9][0-9]*)[ \/-]*(.*)$/im", $ev->Get("summary"), $matches ) ) {
  $request_id = intval($matches[2]);
  $ev->Put("summary", $matches[3]);
}
else {
  $request_id = intval(preg_replace("/^WR#/", "", $ev->Get("location")));
}

$delete_dav_event = false;

/**
* If they didn't send an etag_match header, we need to check if the PUT object already exists
* and we are hence updating it.  And we just set our etag_match to that.
*/
$qry = new PgQuery( "SELECT * FROM caldav_data WHERE user_no=? AND dav_name=?", $session->user_no, $put_path );
$qry->Exec("PUT");
if ( $qry->rows > 1 ) {
  header("HTTP/1.1 500 Infernal Server Error");
  dbg_error_log("ERROR","Multiple events match replaced path for user %d, path %s", $session->user_no, $put_path );
  exit(0);
}
elseif ( $qry->rows == 1 ) {
  $dav_event = $qry->Fetch();
  if ( $request_id > 0 ) {
    /**
    * We can delete the CalDAV event and re-parse it into a new timesheet
    */
    $delete_dav_event = true;
    $etag_match = '';
  }
  else {
    /**
    * We will just update the existing CalDAV event.
    */
    $etag_match = $dav_event->dav_etag;
  }
}
elseif ( ( !isset($etag_match) || $etag_match == '*' || $etag_match == '' )
       && ($ts_id."@".$_SERVER['SERVER_NAME'] == $ev->Get("uid") )
         ) {
  // It really looks like we are maintaining an existing timesheet
  $qry = new PgQuery( "SELECT dav_etag FROM request_timesheet WHERE timesheet_id=$ts_id;" );
  $qry->Exec("PUT");
  if ( $qry->rows != 1 ) {
    header("HTTP/1.1 500 Infernal Server Error");
    dbg_error_log("ERROR","Found %d rows matching request %d, timesheet %d for user %s(%d)", $request_id, $ts_id, $session->username, $session->user_no );
    exit(0);
  }
  elseif ( $qry->rows == 1 ) {
    $dav_event = $qry->Fetch();
    $etag_match = $dav_event->dav_etag;
  }
}


dbg_error_log("PUT", " ETags In: if-match: '%s', if-none-match: '%s'", $etag_match, $etag_none_match );

if ( $request_id == 0 ) {
  dbg_error_log( "PUT", "No request_id found for '%s' in '%s' or '%s'", $session->username, $ev->Get("summary"), $ev->Get("location") );
  if ( ! preg_match( '/^Not a timesheet!/', $ev->Get("summary") ) ) {
    $ev->Put("summary","Not a timesheet! was: " . $ev->Get("summary") );
  }
  $ev->Put("description", 'No request ID.  Either the location should match #^(WR)?[0-9]+# or the summary should match #^WR[0-9]+/Description of work$# ');
  $reprocessed_event_data = $ev->Render();
  $etag = md5($reprocessed_event_data);
  if ( $etag_match == '*' || $etag_match == '' ) {
    /**
    * If we got this far without an etag we must be inserting it.
    */
    $qry = new PgQuery( "INSERT INTO caldav_data ( user_no, dav_name, dav_etag, caldav_data, caldav_type, logged_user ) VALUES( ?, ?, ?, ?, ?, ?)",
                          $session->user_no, $put_path, $etag, $reprocessed_event_data, $ev->type, $session->user_no );
    $qry->Exec("PUT");

    header("HTTP/1.1 201 Created");
    /**
    * From draft 13, 5.3.4 we understand that Since we screwed with it, we _don't_ send an etag
    */
  }
  else {
    $qry = new PgQuery( "UPDATE caldav_data SET caldav_data=?, dav_etag=?, caldav_type=?, logged_user=? WHERE user_no=? AND dav_name=? AND dav_etag=?",
                                                $reprocessed_event_data, $etag, $ev->type, $session->user_no, $session->user_no, $put_path, $etag_match );
    $qry->Exec("PUT");

    header("HTTP/1.1 201 Replaced");
    /**
    * From draft 13, 5.3.4 we understand that Since we screwed with it, we _don't_ send an etag
    */
  }

  /**
  * We can't do any more with this since there was no identifiable request_id
  */
  exit(0);
}

$qry = new PgQuery( "BEGIN;" );
$qry->Exec("PUT");

if ( $delete_dav_event ) {
  $qry = new PgQuery( "DELETE FROM caldav_data WHERE user_no=? AND dav_name=?", $session->user_no, $put_path );
  $qry->Exec("PUT");
}

$action = "Created";  // By default

if ( isset($ev->tz_locn) && $ev->tz_locn != '' ) {
  $tzset = "SET TIMEZONE TO ".qpg($ev->tz_locn).";";
}
if ( isset($etag_match) && $etag_match != '*' && $etag_match != '' ) {
  $action = "Replaced";
  $sql = <<<EOSQL
$tzset
UPDATE request_timesheet
   SET request_id=?, work_on=?::timestamp, work_duration=(?::timestamp - ?::timestamp),
           work_by_id=$session->user_no, work_description=?, work_units='hours'
 WHERE timesheet_id=$ts_id;
UPDATE request_timesheet
   SET work_quantity = (extract( 'hours' from work_duration)::numeric + extract( 'minutes' from work_duration )::numeric / 60::numeric),
       dav_etag = md5(timesheet_id||request_id||work_on||work_duration||work_by_id||COALESCE(charged_details,'')||work_description)
 WHERE timesheet_id=$ts_id;
EOSQL;
  $qry = new PgQuery( $sql, $request_id, $ev->Get('dtstart'), $ev->Get('dtend'), $ev->Get('dtstart'), $ev->Get('summary') );
  $qry->Exec("PUT");
}
else {
  $qry = new PgQuery( "SELECT nextval('request_timesheet_timesheet_id_seq') AS ts_id;" );
  $qry->Exec("PUT");
  $row = $qry->Fetch();
  $ts_id = $row->ts_id;
  $sql = <<<EOSQL
$tzset
INSERT INTO request_timesheet ( timesheet_id, request_id, work_on, work_duration, work_by_id, work_description, work_units )
    VALUES( $ts_id, ?, ?::timestamp, (?::timestamp - ?::timestamp), $session->user_no, ?, 'hours' );
UPDATE request_timesheet
  SET work_quantity = (extract( 'hours' from work_duration)::numeric + extract( 'minutes' from work_duration )::numeric / 60::numeric),
      dav_etag = md5(timesheet_id||request_id||work_on||work_duration||work_by_id||COALESCE(charged_details,'')||work_description)
WHERE timesheet_id=$ts_id;
EOSQL;
  $qry = new PgQuery( $sql, $request_id, $ev->Get('dtstart'), $ev->Get('dtend'), $ev->Get('dtstart'), $ev->Get('summary') );
  $qry->Exec("PUT");
}


$qry = new PgQuery( "SELECT dav_etag FROM request_timesheet WHERE timesheet_id = $ts_id;" );
$qry->Exec("PUT");
$row = $qry->Fetch();
$etag = $row->dav_etag;

$qry = new PgQuery( "COMMIT;" );
$qry->Exec("PUT");

header("HTTP/1.1 201 $action");

/**
* From draft 13, 5.3.4 we find:
* "In the case where the data stored by a server as a result of a PUT
* request is not equivalent by octet equality to the submitted calendar
* object resource, the behavior of the ETag response header is not
* specified here, with the exception that a strong entity tag MUST NOT be
* returned in the response. As a result, clients may need to retrieve the
* modified calendar object resource (and ETag) as a basis for further
* changes, rather than use the calendar object resource it had sent with
* the PUT request."
*
* So: since we fucked with it significantly, we don't return an etag, and the
* client (possibly) knows they will have to request it again.
*/

dbg_error_log( "PUT", "User: %d, ETag: %s, Path: %s", $session->user_no, $etag, $put_path);

?>