<?php
function lookup_list( $dbid, $table, $field, $current="" ) {
  $rid = pg_Exec( $dbid, "SELECT * FROM lookup_code WHERE source_table = '$table' AND source_field = '$field'");
  $rows = pg_NumRows( $rid );
  $lookup_code_list = "";
  for ( $i=0; $i < $rows; $i++ ) {
    $lookup_code = pg_Fetch_Object( $rid, $i );
    $lookup_code_list .= "<OPTION VALUE=\"$lookup_code->lookup_code\"";
    if ( ! strcmp( $lookup_code->lookup_code, $current) ) $lookup_code_list .= " SELECTED";
    $lookup_code_list .= ">$lookup_code->lookup_desc";
  }
  return $lookup_code_list;
}
?>
