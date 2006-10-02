<?php

require_once("vEvent.php");

dbg_error_log("get", "GET method handler");

// The GET method is not sent with any wrapping XML so we simply fetch it

$get_path = $_SERVER['PATH_INFO'];
$etag_none_match = str_replace('"','',$_SERVER["HTTP_IF_NONE_MATCH"]);

list( $junk, $user, $ts_id ) = preg_split( '#/#', $get_path );
$ts_id = intval($ts_id);
$ts_url = sprintf("/%s/%d.ics", $session->username, $ts_id );
if ( $get_path != $ts_url ) {
  $ts_id = 0;
  dbg_error_log('GET', "Looking very like this is not a timesheet: %s", $get_path );
}
dbg_error_log('GET', "User: %s, TS_ID: %s", $user, $ts_id );

$ical_date_format = vEvent::SqlDateFormat();
$ical_duration_format = vEvent::SqlDurationFormat();

$sql = <<<EOSQL
SELECT dav_etag,
       to_char(work_on,$ical_date_format) AS dtstamp,
       to_char(work_on + case when work_on::time < '06:00'::time then '09:00'::time else '00:00'::time end,$ical_date_format) AS dtstart,
       to_char(work_duration,$ical_duration_format) AS duration,
       work_description AS summary,
       'WR#'||request_id::text AS location,
       'WR#'||request_id::text || ' - ' || brief AS description,
       'Invoice '||charged_details::text || ', Charged $'|| to_char(charged_amount,'FM999,999,990.00') ||' by ' || chgby.username || ' on ' || to_char(work_charged,'d/mm/YY') AS invoiced
  FROM request_timesheet JOIN request USING (request_id)
       LEFT OUTER JOIN usr chgby ON (charged_by_id = chgby.user_no)
 WHERE work_by_id = ? AND timesheet_id = ?
EOSQL;

$qry = new PgQuery( $sql, $session->user_no, $ts_id);
if ( $qry->Exec("GET") && $qry->rows == 1 ) {
  $ts = $qry->Fetch();

  header("HTTP/1.1 200 OK");
  header("ETag: $ts->dav_etag");
  if ( isset($debug) ) {
    header("Content-Type: text/plain");
  }
  else {
    header("Content-Type: text/calendar");
  }

  if ( $ts->invoiced != "" ) $ts->description .= "\n" . $ts->invoiced;
  $vevent = new vEvent( array(
                        'uid' => $ts_id."@".$_SERVER['SERVER_NAME'],
                        'dtstart'  => $ts->dtstart,
                        'duration' => $ts->duration,
                        'summary' => $ts->summary,
                        'location' => $ts->location,
                        'description' => $ts->description
                       ));
  print $vevent->Render();

  dbg_error_log( "GET", "User: %d, ETag: %s, Path: /%s/%d.ics", $session->user_no, $ts->dav_etag, $user, $ts_id );

}
else {
  $qry = new PgQuery( "SELECT * FROM caldav_data WHERE user_no = ? AND dav_name = ? ;", $session->user_no, $get_path);
  dbg_error_log("get", "%s", $qry->querystring );
  if ( $qry->Exec("GET") && $qry->rows == 1 ) {
    $event = $qry->Fetch();

    header("HTTP/1.1 200 OK");
    header("ETag: $event->dav_etag");
    if ( isset($debug) ) {
      header("Content-Type: text/plain");
    }
    else {
      header("Content-Type: text/calendar");
    }

    print $event->caldav_data;

    dbg_error_log( "GET", "User: %d, ETag: %s, Path: %s", $session->user_no, $event->dav_etag, $get_path);

  }
  else if ( $qry->rows != 1 ) {
    header("HTTP/1.1 500 Internal Server Error");
    dbg_error_log("ERROR", "Multiple rows match for User: %d, ETag: %s, Path: %s", $session->user_no, $event->dav_etag, $get_path);
  }
  else {
    header("HTTP/1.1 500 Infernal Server Error");
    dbg_error_log("get", "Infernal Server Error - no data for %s - %s", $session->user_no, $get_path);
  }
}

?>