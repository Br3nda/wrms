<?php

  include("inc/always.php");
  include("inc/tidy.php");

  $user_no = "";
  $query = "SELECT user_no FROM usr WHERE username=LOWER('$l') and password=LOWER('$p')";
  $result = pg_Exec( $wrms_db, $query );
  if(pg_NumRows($result) > 0) {
    $user_no = pg_Result($result, 0, "user_no");
  }

  $requests = "<p><small>";

  if("$user_no" <> "") {

    $query = "SELECT DISTINCT request.request_id, brief, last_activity, ";
    $query .= "lookup_desc AS status_desc, severity_code ";
    $query .= "FROM request, request_interested, lookup_code AS status ";
    $query .= "WHERE request.request_id=request_interested.request_id ";
    $query .= "AND status.source_table='request' ";
    $query .= "AND status.source_field='status_code' ";
    $query .= "AND status.lookup_code=request.last_status ";
    $query .= "AND request_interested.user_no=$user_no ";
    $query .= "AND request.active ";
    $query .= "AND request.last_status~*'[AILNRQA]' ";
    $query .= "ORDER BY request.severity_code DESC LIMIT 20; ";

    $result = pg_Exec( $wrms_db, $query );
    if ( !$result ) {
      error_log( "wrms wap/inc/getRequests.php query error: $query", 0);
    }

    for ( $i=0; $i < pg_NumRows($result); $i++ ) {
      $thisrequest = pg_Fetch_Object( $result, $i );
      $requests .= "<a href=\"wrms.php?id=$thisrequest->request_id\">".
			tidy($thisrequest->brief)."</a><br/>\n";
    }
  } else {
	$requests .= "I'm sorry you must login first";
  }
  $requests .= "</small></p>";

?>
