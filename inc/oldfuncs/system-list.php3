<?php
  $rid = pg_Exec( $dbid, "SELECT * FROM work_system");
  $rows = pg_NumRows( $rid );
  $system_list = "";
  for ( $i=0; $i < $rows; $i++ ) {
    $work_system = pg_Fetch_Object( $rid, $i );
    $system_list .= "<OPTION VALUE=\"$work_system->system_code\"";
    if ( ! strcmp( $work_system->system_code, $current) ) $system_list .= " SELECTED";
    $system_list .= ">$work_system->system_desc";
  }
?>
