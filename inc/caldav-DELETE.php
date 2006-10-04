<?php

dbg_error_log("delete", "DELETE method handler");

// The DELETE method is not sent with any wrapping XML so we simply delete it

$delete_path = $_SERVER['PATH_INFO'];
$etag_none_match = str_replace('"','',$_SERVER["HTTP_IF_NONE_MATCH"]);
$etag_match = str_replace('"','',$_SERVER["HTTP_IF_MATCH"]);

$delete_user_no = $session->user_no;
$delete_user_name = $session->username;
if ( preg_match( "#^/([^/]+)/([0-9]+)(.*)\.ics$#", $delete_path, $matches ) ) {
  $in_username = $matches[1];
  $in_tsid = $matches[2];
  $in_otherstuff = $matches[3];
  $ts_id = intval($in_tsid);
  if ( $in_otherstuff != '' && $in_otherstuff != '@'.$_SERVER['SERVER_NAME'] ) ) {
    $ts_id = 0;
    dbg_error_log('GET', "Looking very like this is not a timesheet: %s", $delete_path );
  }
}
dbg_error_log('DELETE', "User: %s, TS_ID: %s, PATH: '%s'", $user, $ts_id, $delete_path );

if ( $session->AllowedTo("Admin") ) {
  $qry = new PgQuery( "SELECT user_no FROM usr WHERE username = ?;", $in_username );
  if ( $qry->Exec("REPORT") && $row = $qry->Fetch() ) {
    $delete_user_no = $row->user_no;
    $delete_user_name = $in_username;
  }
}
else {
  if ( $in_username != $session->username ) {
    header("HTTP/1.1 403 Access Denied");
    header("Content-type: text/plain");
    dbg_error_log( "DELETE", "Access denied: User: %d, Path: %s", $qry->rows, $session->user_no, $delete_path);
    echo "Access Denied";
    exit(0);
  }
}


if ( ( !isset($etag_match) || $etag_match == '*' || $etag_match == '' )
       && (("/".$session->username."/$ts_id".".ics" == "$delete_path" )
           || ("/".$session->username."/$ts_id@".$_SERVER['SERVER_NAME'].".ics" == "$delete_path" ))
         ) {
  // It really looks like we are deleting an existing timesheet
  $qry = new PgQuery( "SELECT dav_etag FROM request_timesheet WHERE timesheet_id=$ts_id;" );
  $qry->Exec("PUT");
  if ( $qry->rows != 1 ) {
    header("HTTP/1.1 500 Infernal Server Error");
    dbg_error_log("ERROR","Found %d rows matching request %d, timesheet %d for user %s(%d)", $request_id, $ts_id, $delete_user_name, $delete_user_no );
    exit(0);
  }
  elseif ( $qry->rows == 1 ) {
    $dav_event = $qry->Fetch();
    $etag_match = $dav_event->dav_etag;
  }
}

if ( ( isset($etag_match) && $etag_match != '*' && $etag_match != '' )
       && (("/".$session->username."/$ts_id".".ics" == "$delete_path" )
           || ("/".$session->username."/$ts_id@".$_SERVER['SERVER_NAME'].".ics" == "$delete_path" ))
         ) {
  $qry = new PgQuery( "SELECT * FROM request_timesheet WHERE work_by_id=? AND dav_etag=? AND timesheet_id=?", $session->user_no, $etag_match, $ts_id );
  if ( $qry->Exec("DELETE") && $qry->rows == 1 ) {
    $qry = new PgQuery( "DELETE FROM request_timesheet WHERE work_by_id=? AND dav_etag=? AND timesheet_id=?", $session->user_no, $etag_match, $ts_id );
    if ( $qry->Exec("DELETE") ) {
      header("HTTP/1.1 200 OK");
      dbg_error_log( "DELETE", "DELETE: User: %d, ETag: %s, Path: %s", $session->user_no, $etag_none_match, $delete_path);
    }
    else {
      header("HTTP/1.1 500 Infernal Server Error");
      dbg_error_log( "DELETE", "DELETE failed: User: %d, ETag: %s, Path: %s, SQL: %s", $session->user_no, $etag_none_match, $delete_path, $qry->querystring);
    }
  }
  else {
    header("HTTP/1.1 404 Not Found");
    dbg_error_log( "DELETE", "DELETE row not found: User: %d, ETag: %s, Path: %s", $qry->rows, $session->user_no, $etag_none_match, $delete_path);
  }
}
else {
  $qry = new PgQuery( "SELECT * FROM caldav_data WHERE user_no=? AND dav_name=?", $session->user_no, $delete_path );
  if ( $qry->Exec("DELETE") && $qry->rows == 1 ) {
    $qry = new PgQuery( "DELETE FROM caldav_data WHERE user_no=? AND dav_name=?", $session->user_no, $delete_path );
    if ( $qry->Exec("DELETE") ) {
      header("HTTP/1.1 200 OK");
      dbg_error_log( "DELETE", "DELETE: User: %d, Path: %s", $session->user_no, $delete_path);
    }
    else {
      header("HTTP/1.1 500 Infernal Server Error");
      dbg_error_log( "DELETE", "DELETE failed: User: %d, Path: %s, SQL: %s", $session->user_no, $delete_path, $qry->querystring);
    }
  }
  else {
    header("HTTP/1.1 404 Not Found");
    dbg_error_log( "DELETE", "DELETE row not found: User: %d, Path: %s", $qry->rows, $session->user_no, $delete_path);
  }
}

?>