<?php
  $ls_query = "SELECT username, perorg_name FROM awm_usr, awm_perorg";
  $ls_query .= " WHERE awm_usr.perorg_id = awm_perorg.perorg_id ";
  $ls_query .= " ORDER BY awm_perorg.perorg_sort_key ";
  if ( strcmp( "$where", "") ) $ls_query .= " AND $where" ;
  $rid = pg_Exec( $dbid, $ls_query);
  $rows = pg_NumRows( $rid );
  $usr_list = "";
  for ( $i=0; $i < $rows; $i++ ) {
    $ls_usr = pg_Fetch_Object( $rid, $i );
    $usr_list .= "<OPTION VALUE=\"$ls_usr->username\"";
    if ( ! strcmp( $ls_usr->username, $current) ) $usr_list .= " SELECTED";
    $usr_list .= ">$ls_usr->perorg_name";
  }
?>
