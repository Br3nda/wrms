<?php
function get_code_list( $table, $field, $current="" ) {
  global $wrms_db;
  $rid = pg_Exec( $wrms_db, "SELECT * FROM lookup_code WHERE source_table = '$table' AND source_field = '$field' ORDER BY source_table, source_field, lookup_seq, lookup_code");
  $rows = pg_NumRows( $rid );
  $lookup_code_list = "";
  for ( $i=0; $i < $rows; $i++ ) {
    $lookup_code = pg_Fetch_Object( $rid, $i );
    $lookup_code_list .= "<OPTION VALUE=\"$lookup_code->lookup_code\"";
    if ( "$lookup_code->lookup_code" == "$current" ) $lookup_code_list .= " SELECTED";
    $lookup_code_list .= ">$lookup_code->lookup_desc";
  }
  return $lookup_code_list;
}
?>
