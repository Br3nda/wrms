<?php

  include("inc/always.php");
  include("inc/tidy.php");

  $request = "<p><small>";
  if(isset($id)) {

    $query = "SELECT DISTINCT ON request_id request.request_id, detailed, severity_code, 
		request_by, urgency, last_activity, lookup_desc AS status_desc
		FROM request, request_interested, lookup_code AS status, usr 
		WHERE request.request_id=$id";

    $result = pg_Exec( $wrms_db, $query );

    for ( $i=0; $i < pg_NumRows($result); $i++ ) {
      $thisrequest = pg_Fetch_Object( $result, $i );
      $request .= tidy($thisrequest->detailed)."<br/>
		Request By: ".tidy($thisrequest->request_by)."<br/>
		Severity Code: ".tidy($thisrequest->severity_code)."<br/>
		Urgency: ".tidy($thisrequest->urgency);
    }
  } else {
	$request .= "I'm sorry the request id you gave was invalid";
  }
  $request .= "</small></p>";

?>
