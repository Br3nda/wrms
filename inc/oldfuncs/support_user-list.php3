<?php
  $query = "SELECT DISTINCT ON username username, awm_perorg.perorg_id, perorg_name ";
  $query .= "FROM awm_usr, perorg_system, awm_perorg ";
  $query .= "WHERE (perorg_system.persys_role = 'SUPPORT') ";
  $query .= "AND awm_get_rel_parent( awm_usr.perorg_id, 'Employer') = perorg_system.perorg_id ";
  $query .= "AND awm_usr.perorg_id = awm_perorg.perorg_id ";
  $query .= " ORDER BY awm_perorg.perorg_sort_key ";
  $current = "$usr->username";
  $rid = pg_Exec( $dbid, $query);
  if ( ! $rid )
    echo "<P>Query failed:</P><TT>$query</TT>";
  else {
    $rows = pg_NumRows( $rid );
    $support_usr_list = "<OPTION VALUE=\"\">--- not selected ---";
    for ( $i=0; $i < $rows; $i++ ) {
      $ls_usr = pg_Fetch_Object( $rid, $i );
      $support_usr_list .= "<OPTION VALUE=\"$ls_usr->perorg_id\"";
      if ( ! strcmp( $ls_usr->username, $current) ) $support_usr_list .= " SELECTED";
      $support_usr_list .= ">$ls_usr->perorg_name";
    }
  }
?>
