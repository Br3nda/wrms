<?php
  $rid = pg_Exec( $dbid, "SELECT * FROM severity");
  $rows = pg_NumRows( $rid );
  $sev_list = "";
  for ( $i=0; $i < $rows; $i++ ) {
    $severity = pg_Fetch_Object( $rid, $i );
    $sev_list .= "<OPTION VALUE=\"$severity->severity_code\"";
    if ( !strcmp( $severity->severity_code, $current ) ) $sev_list .= " SELECTED";
    $sev_list .= ">$severity->severity_desc";
  }
?>
