<?php

require_once("vEvent.php");

dbg_error_log("get", "GET method handler");

// The GET method is not sent with any wrapping XML so we simply fetch it

$get_path = $_SERVER['PATH_INFO'];
$etag_none_match = str_replace('"','',$_SERVER["HTTP_IF_NONE_MATCH"]);

list( $junk, $user, $ts_id ) = preg_split( '#/#', $get_path );
$ts_id = intval($ts_id);
dbg_error_log('Caldav::GET', "User: %s, TS_ID: %s", $user, $ts_id );

$ical_date_format = vEvent::SqlDateFormat();
$ical_duration_format = vEvent::SqlDurationFormat();

$sql = <<<EOSQL
SELECT to_char(work_on,$ical_date_format) AS dtstamp,
       to_char(work_on + case when work_on::time < '06:00'::time then '09:00'::time else '00:00'::time end,$ical_date_format) AS dtstart,
       to_char(work_duration,$ical_duration_format) AS duration,
       work_description AS summary,
       'WR#'||request_id::text AS location,
       'WR#'||request_id::text || ' - ' || brief AS description
  FROM request_timesheet JOIN request USING (request_id)
 WHERE work_by_id = ? AND timesheet_id = ?
EOSQL;

$qry = new PgQuery( $sql, $session->user_no, $ts_id);
if ( $qry->Exec("GET") && $qry->rows == 1 ) {
  $ts = $qry->Fetch();

  header("HTTP/1.1 200 OK");
  header("ETag: $ts->vevent_etag");
  if ( isset($debug) ) {
    header("Content-Type: text/plain");
  }
  else {
    header("Content-Type: text/calendar");
  }

  $vevent = new vEvent( array(
                        'uid' => $ts_id."@".$_SERVER['SERVER_NAME'],
                        'dtstamp'  => $ts->dtstamp,
                        'dtstart'  => $ts->dtstart,
                        'duration' => $ts->duration,
                        'summary' => $ts->summary,
                        'location' => $ts->location,
                        'description' => $ts->description,
                        'action' => "DISPLAY",
                        'class' => "PUBLIC",
                        'transp' => "OPAQUE"
                       ));
  print $vevent->Render();

  dbg_error_log( "GET", "User: %d, ETag: %s, Path: /%s/%d.ics", $session->user_no, $ts->caldav_etag, $user, $ts_id );

}
else {
  header("HTTP/1.1 500 Infernal Server Error");
}

?>