<?php

  include("inc/always.php");
  include("inc/tidy.php");

  $title = "";
  $request = "<p>Request not found</p>";

  if ( isset($id) && intval($id) > 0 ) {

    $query = "SELECT request_id, brief, detailed, severity_code, request_by, urgency ";
    $query .= "FROM request WHERE request.request_id=$id; ";

    if ( isset($active) ) {
      error_log( "wrmswap: Should be marking $id as active=$active", 0);
    }


    $result = pg_Exec( $wrms_db, $query );
    if ( $result && pg_NumRows($result) > 0 ) {
      $thisrequest = pg_Fetch_Object( $result, $i );

      $request = "<p><small>";
      $request .= tidy($thisrequest->detailed) . "<br/>
      Request By: " . tidy($thisrequest->request_by) . "<br/>
      Severity Code: " . tidy($thisrequest->severity_code) . "<br/>
      Urgency: " . tidy($thisrequest->urgency);
      $request .= "</small></p>";

      $title = $thisrequest->brief ;

      // Have to put '$id' onto the name of the fieldset otherwise the value gets cached!
      $request .= "<p align=\"center\">
<fieldset title=\"Status$id\">
Status: <select name=\"active\">
<option value=\"0\"" . ($active==0?" selected":"") . ">Inactive</option>
<option value=\"1\"" . ($active==1?" selected":"") . ">Active</option>
<option value=\"2\"" . ($active==2?" selected":"") . ">Complete</option>
</select>
</fieldset>
</p>";

    }
  }
  else {
    $request = "<p>I'm sorry the request id you gave was invalid</p>";
  }

?>
