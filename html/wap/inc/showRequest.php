<?php

  include("inc/always.php");
  include("inc/tidy.php");

  $request = "<p><small>";
  if(isset($id)) {

    $query = "SELECT request_id, detailed, severity_code, 
		request_by, urgency
		FROM request 
		WHERE request.request_id=$id";

    $result = pg_Exec( $wrms_db, $query );

    for ( $i=0; $i < pg_NumRows($result); $i++ ) {
      $thisrequest = pg_Fetch_Object( $result, $i );
      $request .= tidy($thisrequest->detailed)."<br/>
      Request By: ".tidy($thisrequest->request_by)."<br/>
      Severity Code: ".tidy($thisrequest->severity_code)."<br/>
      Urgency: ".tidy($thisrequest->urgency);
    }
  }
  else {
    $request .= "I'm sorry the request id you gave was invalid";
  }
  $request .= "</small></p>";

?>
