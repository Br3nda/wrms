<?php
    $rid = pg_Exec( $dbid, "SELECT * FROM request_type");
    $rows = pg_NumRows( $rid );
    $type_list = "";
    for ( $i=0; $i < $rows; $i++ ) {
      $request_type = pg_Fetch_Object( $rid, $i );
      $type_list .= "<OPTION VALUE=\"$request_type->request_type\"";
      if ( ! strcmp( $request_type->request_type, $current) ) $type_list .= " SELECTED";
      $type_list .= ">$request_type->request_type_desc";
    }
?>