<?php
  if ( isset($id) ) $id = intval($id);
  if ( !isset($id) || $id == 0 ) {
    error_log( "$sysabbr attachment: DBG: \$id not defined", 0);
    echo "<html><head><title>Error - invalid attachment ID</title><body><h1>Invalid attachment ID</h1></body></html>";
    exit;
  }

  include_once("always.php");
  require_once("authorisation-page.php");

if ( !$session->logged_in ) {
  // Very quiet
  echo "Error: Not authorised";
  exit;
}

  if ( !( $session->AllowedTo('Admin') || $session->AllowedTo('Support') ) ) {
    $sql = "SELECT * FROM request_attachment NATURAL JOIN request ";
    $sql .= "JOIN usr ON request.requester_id = usr.user_no ";
    $sql .= "WHERE attachment_id = $id ";
    $sql .= "AND org_code = $session->org_code ; ";

    $qry = new PgQuery( $sql );
//    if ( !$qry->Exec("attachment") || $qry->rows == 0 ) {
  }

  $sql = "SELECT * FROM request_attachment, lookup_code ";
  $sql .= "WHERE attachment_id = $id ";
  $sql .= "AND source_table='request' ";
  $sql .= "AND source_field='attach_type' ";
  $sql .= "AND lookup_code = att_type ; ";

  $qry = new PgQuery( $sql );
  if ( $qry->Exec("attachment") && $qry->rows > 0 ) {
    $attachment = $qry->Fetch();
  }
  else {
    $qry = new PgQuery( "SELECT * FROM request_attachment WHERE attachment_id = $id;" );
    if ( !$qry->Exec("attachment") || $qry->rows == 0 ) {
      error_log( "$sysabbr attachment: DBG: id [$id] not found", 0);
      echo "<html><head><title>Error - invalid attachment ID [$id]</title><body><h1>Invalid attachment ID [$id]</h1></body></html>";
      exit;
    }

    $attachment = $qry->Fetch();

    include_once("guess-file-type.php");
    $attachment->lookup_code = guess_file_type( $attachment->att_filename, "$attachment_dir/$id" );
    $attachment->lookup_misc = guess_mime_type( $attachment->lookup_code );
  }

  if ( !isset($attachment_dir) ) $attachment_dir = "attachments";

  header("Content-type: $attachment->lookup_misc");
  header("Content-Disposition: filename=$attachment->att_filename" );
  $bytes = filesize( "$attachment_dir/$id" );
  header("Content-length: $bytes");
  $written = readfile(  "$attachment_dir/$id" );
  if ( $written != $bytes ) error_log( "$sysabbr attachment: DBG: Didn't write complete file - $bytes vs. $written sent.", 0);
  error_log( "$sysabbr attachment: DBG: Served '$attachment->att_filename' as '$attachment->lookup_misc' ($attachment->lookup_code), $bytes bytes");

  if ( $debuglevel > 1 ) {
    $total_query_time = sprintf( "%3.06lf", $total_query_time );
    $total_time = sprintf( "%3.06lf", duration( $begin_processing, microtime() ));
    error_log( "$sysabbr attachment: completed: TQ=$total_query_time ($total_query_count queries) TT=$total_time URI: $REQUEST_URI");
  }
?>
