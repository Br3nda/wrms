<?php
function get_organisation_list( $current="", $maxwidth=50 ) {
  global $wrms_db;
  global $session;
  $org_code_list = "";

  $query = "SELECT * ";
  $query .= "FROM organisation ";
  $query .= " ORDER BY abbreviation";
  $rid = awm_pgexec( $wrms_db, $query);
  if ( ! $rid ) {
    echo "<p>$query";
  }
  else if ( pg_NumRows($rid) > 1 ) {
    // Build table of organisations found
    $rows = pg_NumRows( $rid );
    for ( $i=0; $i < $rows; $i++ ) {
      $org_code = pg_Fetch_Object( $rid, $i );
      $org_code_list .= "<OPTION VALUE=\"$org_code->org_code\"";
      if ( "$org_code->org_code" == "$current" ) $org_code_list .= " SELECTED";
      $our_name = substr( "$org_code->abbreviation - $org_code->org_name", 0, $maxwidth);
      $org_code_list .= ">$our_name";
    }
  }

  return $org_code_list;
}
