<?php
function get_system_list( $access="", $current="", $maxwidth=50 ) {
  global $wrms_db;
  global $session;
  $system_code_list = "";

  $query = "SELECT DISTINCT ON system_code work_system.system_code, system_desc ";
  $query .= "FROM work_system ";
  if ( $access <> "" ) {
    $query .= ", system_usr";
    $query .= " WHERE work_system.system_code=system_usr.system_code ";
    $query .= " AND user_no=$session->user_no ";
    $query .= " AND role~*'[$access]'";
  }
  $query .= " ORDER BY work_system.system_code";
  if ( $access <> "" ) $query .= ", role";
  $rid = pg_Exec( $wrms_db, $query);
  if ( ! $rid ) {
    echo "<p>$query";
  }
  // Alan changed > 1 to > 0 because - well it wasn't getting the first one
  // else if ( pg_NumRows($rid) > 1 ) {
  else if ( pg_NumRows($rid) > 0 ) {
    // Build table of systems found
    $rows = pg_NumRows( $rid );
    for ( $i=0; $i < $rows; $i++ ) {
      $system_code = pg_Fetch_Object( $rid, $i );
      $system_code_list .= "<option value=\"$system_code->system_code\"";
      if ( "$system_code->system_code" == "$current" ) $system_code_list .= " SELECTED";
      $system_code->system_desc = substr( $system_code->system_desc, 0, $maxwidth);
      $system_code_list .= ">$system_code->system_desc</option>\n";
    }
  }

  return $system_code_list;
}
