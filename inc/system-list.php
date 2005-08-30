<?php
function get_system_list( $access="", $current="", $maxwidth=50 ) {
  global $dbconn;
  global $session, $roles;
  $system_code_list = "";

  $query = "SELECT work_system.system_code, system_desc ";
  $query .= "FROM work_system WHERE active ";
  if ( $access != "" && !is_member_of('Admin','Support') ) {
    $query .= " AND EXISTS (SELECT system_usr.system_code FROM system_usr WHERE system_usr.system_code=work_system.system_code";
    $query .= " AND user_no=$session->user_no ";
    $query .= " AND role~*'[$access]') ";
  }
  if ( $current <> "" ) {
    $query .= " OR work_system.system_code='$current'";
  }
  $query .= " ORDER BY LOWER(system_code);";

  $rid = awm_pgexec( $dbconn, $query);
  if ( ! $rid ) return;

  if ( pg_NumRows($rid) > 0 ) {
    // Build table of systems found
    $rows = pg_NumRows( $rid );
    for ( $i=0; $i < $rows; $i++ ) {
      $system_code = pg_Fetch_Object( $rid, $i );
      $system_code_list .= "<option value=\"".urlencode($system_code->system_code)."\"";
      if ( "$system_code->system_code" == "$current" ) $system_code_list .= " SELECTED";
      $system_code->system_desc = substr( $system_code->system_desc, 0, $maxwidth);
      $system_code_list .= ">$system_code->system_desc</option>\n";
    }
  }

  return $system_code_list;
}
?>
