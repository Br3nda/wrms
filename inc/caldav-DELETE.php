<?php

dbg_error_log("delete", "DELETE method handler");

// The DELETE method is not sent with any wrapping XML so we simply delete it

$get_path = $_SERVER['PATH_INFO'];
$etag_none_match = str_replace('"','',$_SERVER["HTTP_IF_NONE_MATCH"]);

$etag_none_match = str_replace('"','',$_SERVER["HTTP_IF_NONE_MATCH"]);
$etag_match = str_replace('"','',$_SERVER["HTTP_IF_MATCH"]);

list( $junk, $user, $ts_id ) = preg_split( '#/#', $_SERVER['PATH_INFO'] );
$ts_id = intval($ts_id);
dbg_error_log('DELETE', "User: %s, TS_ID: %s", $user, $ts_id );

$qry = new PgQuery( "SELECT * FROM request_timesheet WHERE user_no=? AND caldav_etag=? AND timesheet_id=?", $session->user_no, $etag_match, $ts_id );
if ( $qry->Exec("DELETE") && $qry->rows == 1 ) {
  $qry = new PgQuery( "DELETE FROM request_timesheet WHERE user_no=? AND caldav_etag=? AND timesheet_id=?", $session->user_no, $etag_match, $ts_id );
  if ( $qry->Exec("DELETE") ) {
    header("HTTP/1.1 200 OK");
    dbg_error_log( "DELETE", "DELETE: User: %d, ETag: %s, Path: %s", $session->user_no, $etag_none_match, $get_path);
  }
  else {
    header("HTTP/1.1 500 Infernal Server Error");
    dbg_error_log( "DELETE", "DELETE failed: User: %d, ETag: %s, Path: %s, SQL: %s", $session->user_no, $etag_none_match, $get_path, $qry->querystring);
  }
}
else {
  header("HTTP/1.1 404 Not Found");
  dbg_error_log( "DELETE", "DELETE row not found: User: %d, ETag: %s, Path: %s", $qry->rows, $session->user_no, $etag_none_match, $get_path);
}

?>