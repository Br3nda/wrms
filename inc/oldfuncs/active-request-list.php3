<?php
  $rid = pg_Exec( $dbid, "SELECT * FROM request WHERE active ORDER BY severity_code, request_id DESC" );
  $rows = pg_NumRows( $rid );
  $org_list = "";
  for ( $i=0; $i < $rows; $i++ ) {
    $request = pg_Fetch_Object( $rid, $i );
    $request_list .= "<OPTION VALUE=\"$request->request_id\"";
    if ( $current[$request->request_id] ) $request_list .= " SELECTED";
    $request_list .= ">$request->request_id - $request->brief";
  }
?>
