<?php
  $rid = pg_Exec( $dbid, "SELECT * FROM organisation ORDER BY org_name");
  $rows = pg_NumRows( $rid );
  $org_list = "";
  for ( $i=0; $i < $rows; $i++ ) {
    $organisation = pg_Fetch_Object( $rid, $i );
    $org_list .= "<OPTION VALUE=\"$organisation->org_code\"";
    if ( ! strcmp( $organisation->org_code, $current) ) $org_list .= " SELECTED";
    $org_list .= ">$organisation->org_name";
  }
?>
