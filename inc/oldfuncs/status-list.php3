<?php
  $rid = pg_Exec( $dbid, "SELECT * FROM status");
  $rows = pg_NumRows( $rid );
  $stat_list = "";
  for ( $i=0; $i < $rows; $i++ ) {
    $status = pg_Fetch_Object( $rid, $i );
    $stat_list .= "<OPTION VALUE=\"$status->status_code\"";
    if ( ! strcmp( $status->status_code, $current) ) $stat_list .= " SELECTED";
    $stat_list .= ">$status->status_desc";
  }
?>
