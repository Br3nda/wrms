<?php

  include("inc/always.php");
  include("inc/tidy.php");

  $title = "";
  $request = "<p>Request not found</p>";

  if ( isset($id) && intval($id) > 0 ) {

    if ( isset($active) )
      error_log( "Should be marking $id as active=$active", 0);

    $query = "SELECT request_id, brief, detailed, severity_code, request_by, urgency ";
    $query .= "FROM request WHERE request.request_id=$id; ";

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

      WMLDo("accept", "", "Submit", "wrms.php?l=\$(lo)&amp;p=\$(pw)&amp;id=\$(id)&amp;active=\$(active)", "");
      $request .= "<p align=\"center\">
<fieldset title=\"Status\">
Status: <select name=\"active\">
<option value=\"0\">Inactive</option>
<option value=\"1\">Active</option>
<option value=\"2\">Complete</option>
</select>
</fieldset>
</p>";

    }
  }
  else {
    $request .= "I'm sorry the request id you gave was invalid";
  }

?>
