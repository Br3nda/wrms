<?php
  if ( isset($requestno) && $requestno > 0 ) {
    $query = "SELECT * ";
    $query .= " FROM request ";
    $query .= " WHERE requestno='$requestno' ";
    $request_res = pg_Exec( $wrms_db, $query );
    if ( ! $request_res ) {
      $error_loc = "getrequest.php";
      $error_qry = "$query";
    }
    else if ( pg_NumRows($request_res ) > 0 ) {
      $request = pg_Fetch_Object( $request_res, 0 );
    }
  }
?>
