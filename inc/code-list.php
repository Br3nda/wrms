<?php
function get_code_list( $table, $field, $current="", $misc="", $tag="option", $varname="" ) {
  global $dbconn;
  $query = "SELECT * FROM lookup_code WHERE source_table = '$table' AND source_field = '$field' ORDER BY source_table, source_field, lookup_seq, lookup_code";
  $rid = awm_pgexec( $dbconn, $query, "codelist", false );
  $rows = pg_NumRows( $rid );
  $lookup_code_list = "";

  if ( $tag <> "option" ) {
    $prestuff = "input type=";
    $selected = " checked";
  }
  else {
    $prestuff = "";
    $selected = " selected";
  }

  for ( $i=0; $i < $rows; $i++ ) {
    $lookup_code = pg_Fetch_Object( $rid, $i );
    $lookup_code_list .= "<$prestuff$tag value=\"$lookup_code->lookup_code\"";
    if ( "$varname" <> "" ) $lookup_code_list .= " name=$varname";
    if ( "$lookup_code->lookup_code" == "$current" ) $lookup_code_list .= $selected;
    $lookup_code_list .= ">";
    $lookup_code_list .= "$lookup_code->lookup_desc";
    if ( "$misc" <> "" && "$lookup_code->lookup_misc" <> "") $lookup_code_list .= " - $lookup_code->lookup_misc";
    if ( "$tag" == "option" ) $lookup_code_list .= "</$tag>";
    else $lookup_code_list .= "&nbsp;\n";
  }

  return $lookup_code_list;
}
?>