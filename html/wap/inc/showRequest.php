<?php

  include("inc/always.php");
  include("inc/tidy.php");

  $title = "";
  $request = "<p>Request not found</p>";

  if ( isset($id) && intval($id) > 0 ) {

    $query = "SELECT request_id, brief, detailed, severity_code, request_by, urgency, wap_status ";
    $query .= "FROM request WHERE request.request_id=$id; ";

    $result = pg_Exec( $wrms_db, $query );
    if ( $result && pg_NumRows($result) > 0 ) {
      $thisrequest = pg_Fetch_Object( $result, $i );

      $request = "";
      if ( isset($active) ) {
        error_log( "wrmswap: Should be marking $id as active=$active", 0);
        if ( $thisrequest->wap_status != $active ) {
          // Looks like we need to update things then...
          $query = "SELECT request_id, brief, detailed, severity_code, request_by, urgency, wap_status ";
          $query .= "FROM request WHERE request.request_id=$id; ";
          $result = pg_Exec( $wrms_db, $query );

          $query = "UPDATE request SET wap_status=$active WHERE request_id=$id; ";
          $result = pg_Exec( $wrms_db, $query );
        }
      }


      $request .= "<p><small>";
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
<option value=\"0\"" . ($thisrequest->wap_status == 0 ? " selected" : "") . ">Inactive</option>
<option value=\"1\"" . ($thisrequest->wap_status == 1 ? " selected" : "") . ">Active</option>
<option value=\"2\"" . ($thisrequest->wap_status == 2 ? " selected" : "") . ">Complete</option>
</select>
</fieldset>
</p>";

    }
  }
  else {
    $request = "<p>I'm sorry the request id you gave was invalid</p>";
  }

?>
