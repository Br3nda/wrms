<?php
function get_system_list( $access="*", $current="" ) {
  global $wrms_db;
  global $session;
  $lookup_code_list = "";

  $query = "SELECT DISTINCT ON lookup_code lookup_code, lookup_desc FROM lookup_code ";
  if ( $access <> "*" ) $query .= ", system_usr";
  $query .= " WHERE source_table='user' AND source_field='system_code' ";
  if ( $access <> "*" ) {
    $query .= " AND lookup_code=system_code ";
    $query .= " AND user_no=$session->user_no ";
    $query .= " AND role~*'[$access]'";
  }
  $query .= " ORDER BY source_table, source_field, lookup_seq, lookup_code";
  if ( $access <> "*" ) $query .= ", role";
  $query .= ", lookup_seq";
  $rid = pg_Exec( $wrms_db, $query);
  if ( ! $rid ) {
    echo "<p>$query";
  }
  else if ( pg_NumRows($rid) > 1 ) {
    // Build table of systems found
    $rows = pg_NumRows( $rid );
    for ( $i=0; $i < $rows; $i++ ) {
      $lookup_code = pg_Fetch_Object( $rid, $i );
      $lookup_code_list .= "<OPTION VALUE=\"$lookup_code->lookup_code\"";
      if ( "$lookup_code->lookup_code" == "$current" ) $lookup_code_list .= " SELECTED";
      $lookup_code->lookup_desc = substr( $lookup_code->lookup_desc, 0, 35);
      $lookup_code_list .= ">$lookup_code->lookup_desc";
    }
  }

  return $lookup_code_list;
}
