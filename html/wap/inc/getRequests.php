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

    $query = "SELECT DISTINCT ON request_id request.request_id, brief,
		last_activity, lookup_desc AS status_desc 
		FROM request, request_interested, usr, lookup_code AS status 
		WHERE request.request_id=request_interested.request_id
		AND status.source_table='request' 
		AND status.source_field='status_code' 
		AND request_interested.user_no=$user_no
    		AND request.active 
		AND request.last_status~*'[AILNRQA]' 
		ORDER BY request.severity_code DESC LIMIT 50 ";

    $result = pg_Exec( $wrms_db, $query );

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
