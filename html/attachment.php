<?php
  include("inc/always.php");
//  include("inc/options.php");

  if ( isset($id) ) $id = intval($id);
  if ( $id == 0 ) {
    error_log( "attachment: \$id not defined", 0);
    echo "<html><head><title>Error - invalid attachment ID</title><body><h1>Invalid attachment ID</h1></body></html>";
    exit;
  }

  $query = "SELECT * FROM request_attachment, lookup_code ";
  $query .= "WHERE attachment_id = $id ";
  $query .= "AND source_table='request' AND source_field='attach_type' AND lookup_code = att_type ";
  $query .= " ; ";
  $rid = awm_pgexec( $dbconn, $query, "imp");
  if ( !$rid || pg_NumRows($rid) == 0 ) {
    error_log( "attachment: id [$id] not found", 0);
    echo "<html><head><title>Error - invalid attachment ID [$id]</title><body><h1>Invalid attachment ID [$id]</h1></body></html>";
    exit;
  }

  $attachment = pg_Fetch_Object( $rid, 0);
  header("Content-type: $attachment->lookup_misc");
//  header("Content-Disposition: inline; filename=$attachment->att_filename" );
  header("Content-Disposition: filename=$attachment->att_filename" );
  $bytes = filesize( "attachments/$id" );
  header("Content-length: $bytes");
  $written = readfile(  "attachments/$id" );
  if ( $written != $bytes ) error_log( "attachment: Didn't write complete file - $bytes vs. $written sent.", 0);

  if ( $debuglevel > 0 ) {
    $total_query_time = sprintf( "%3.06lf", $total_query_time );
    $total_time = sprintf( "%3.06lf", duration( $begin_processing, microtime() ));
    error_log( "attachments cImpleted: TQ=$total_query_time ($total_query_count queries) TT=$total_time URI: $REQU
EST_URI", 0);
  }
?>
