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
      if ( isset($active[$id]) ) {
        error_log( "wrmswap: Should be marking $id as active=$active", 0);
        if ( $thisrequest->wap_status != $active[$id] ) {
          // Looks like we need to update things then...
          $query = "SELECT request_id, brief, detailed, severity_code, request_by, urgency, wap_status ";
          $query .= "FROM request WHERE request.request_id=$id; ";
          $result = pg_Exec( $wrms_db, $query );

          $query = "UPDATE request SET wap_status=" . $active[$id] . " WHERE request_id=$id; ";
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
<fieldset title=\"Status\">
Status: <select name=\"active[$id]\" ivalue=\"$thisrequest->wap_status\">
<option value=\"0\">Inactive</option>
<option value=\"1\">Active</option>
<option value=\"2\">Complete</option>
</select>
</fieldset>
</p>";

    }
  }
  else {
    $request = "<p>I'm sorry the request id you gave was invalid</p>";
  }

?>
