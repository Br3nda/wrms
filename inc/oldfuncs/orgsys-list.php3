<?php
  $rid = pg_Exec( $dbid, "SELECT DISTINCT ON system_code * FROM org_system,work_system WHERE org_system.org_code = '$usr->org_code' AND work_system.system_code = org_system.system_code");
  $num_systems = pg_NumRows( $rid );
  $sys_list = "";
  $last_system = "WRMS";
  for ( $i=0; $i < $num_systems; $i++ ) {
    $sys_type = pg_Fetch_Object( $rid, $i );
    $sys_list .= "<OPTION VALUE=\"$sys_type->system_code\">$sys_type->system_desc";
    $last_system = $sys_type;
  }
?>
