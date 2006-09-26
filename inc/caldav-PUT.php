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

list( $junk, $user, $ts_id ) = preg_split( '#/#', $_SERVER['PATH_INFO'] );
$ts_id = intval($ts_id);
dbg_error_log('PUT', "User: %s, TS_ID: %s", $user, $ts_id );

include_once("vEvent.php");
$ev = new vEvent(array( 'vevent' => $raw_post ));

if ( preg_match( "/^wr[^1-9]*([1-9][0-9]*)[ \/-]*(.*)$/im", $ev->Get("summary"), $matches ) ) {
  $request_id = intval($matches[1]);
  $ev->Put("summary", $matches[2]);
}
else {
  $request_id = intval(preg_replace("/^WR#/", "", $ev->Get("location")));
}
if ( $request_id == 0 ) {
  header("HTTP/1.1 500 Infernal Server Error");
  header("Content-type: text/plain");
  echo "Either subject or location must contain WR#";
}

$qry = new PgQuery( "BEGIN;" );
$qry->Exec("PUT");

$action = "Created";  // By default

if ( isset($etag_match) && $etag_match != '*' && $etag_match != '' ) {
  $qry = new PgQuery( "DELETE FROM request_timesheet WHERE user_no=? AND caldav_etag=? AND timesheet_id=?", $session->user_no, $etag_match, $ts_id );
  $qry->Exec("PUT");
  $action = "Replaced";
}
else {
  $qry = new PgQuery( "SELECT nextval('request_timesheet_timesheet_id_seq') AS ts_id;" );
  $qry->Exec("PUT");
  $row = $qry->Fetch();
  $ts_id = $row->ts_id;
}

if ( isset($ev->tz_locn) && $ev->tz_locn != '' ) {
  $tzset = "SET TIMEZONE TO ".qpg($ev->tz_locn).";";
}
$sql = <<<EOSQL
$tzset
INSERT INTO request_timesheet ( timesheet_id, request_id, work_on, work_duration, work_by_id, work_description, work_units )
     VALUES( $ts_id, ?, ?::timestamp, (?::timestamp - ?::timestamp),
     $session->user_no, ?, 'hours' );
UPDATE request_timesheet
   SET work_quantity = (extract( 'hours' from work_duration)::numeric + extract( 'minutes' from work_duration )::numeric / 60::numeric),
       caldav_etag = md5(timesheet_id||request_id||work_on||work_duration||work_by_id)
 WHERE timesheet_id=$ts_id;
EOSQL;
$qry = new PgQuery( $sql, $request_id, $ev->Get('dtstart'), $ev->Get('dtend'), $ev->Get('dtstart'), $ev->Get('summary') );
$qry->Exec("PUT");

$qry = new PgQuery( "SELECT caldav_etag FROM request_timesheet WHERE timesheet_id = $ts_id;" );
$qry->Exec("PUT");
$row = $qry->Fetch();
$etag = $row->caldav_etag;

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
/*
header("ETag: $etag");
*/

dbg_error_log( "PUT", "User: %d, ETag: %s, Path: %s", $session->user_no, $etag, $put_path);

?>