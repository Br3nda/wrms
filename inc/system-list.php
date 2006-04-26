<?php
function get_system_list( $access="", $current=0, $maxwidth=50 ) {
  global $dbconn;
  global $session, $roles;
  $system_id_list = "";
  $current = intval("$current");

  $query = "SELECT work_system.system_id, system_desc ";
  $query .= "FROM work_system WHERE active ";
  if ( $access != "" && !is_member_of('Admin','Support') ) {
    $query .= " AND EXISTS (SELECT system_usr.system_id FROM system_usr WHERE system_usr.system_id=work_system.system_id";
    $query .= " AND user_no=$session->user_no ";
    $query .= " AND role~*'[$access]') ";
  }
  if ( $current <> "" ) {
    $query .= " OR work_system.system_id=$current";
  }
  $query .= " ORDER BY LOWER(system_id);";

  $rid = awm_pgexec( $dbconn, $query);
  if ( ! $rid ) return;

  if ( pg_NumRows($rid) > 0 ) {
    // Build table of systems found
    $rows = pg_NumRows( $rid );
    for ( $i=0; $i < $rows; $i++ ) {
      $system_id = pg_Fetch_Object( $rid, $i );
      $system_id_list .= "<option value=\"".urlencode($system_id->system_id)."\"";

      if ( (is_array($current) && in_array($system_id->system_id,$current,true) ) || ("$system_id->system_id" == "$current" )) $system_id_list .= " SELECTED";
      $system_id->system_desc = substr( $system_id->system_desc, 0, $maxwidth);
      $system_id_list .= ">$system_id->system_desc</option>\n";
    }
  }

  return $system_id_list;
}
?>
