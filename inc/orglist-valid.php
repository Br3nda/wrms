<?php
  $because = "";

  if ( ! $logged_on )
    $because .= "You must log on with a valid password and maintainer ID\n";
  $fsystem_id = intval("$fsystem_id");
  if ( $fsystem_id == 0 ) {
    if ( $session->system_id == 0 )
      $because .= "You must select a valid system\n";
    else
      $fsystem_id == $session->system_id;
  }

  // Validate that they are only maintaining a request for a system_id they
  if ( is_member_of('Admin') ) {
    // OK, they can do anything :-)
  }
  else if ( $system_id_roles["$fsystem_id"] == "V" ) {
    $because = "You may only view records for that system";
  }
  else if ( $system_id_roles["$fsystem_id"] == "H" || $system_id_roles["$fsystem_id"] == "M" ) {
    // That's OK - this is their home system_id, or maintenance is enabled
  }
  else {
    $because .= "You may only maintain requests from your organisation\n";
  }

  if ( "$because" <> "" ) {
    $because = "<H2>Errors with request:</H2>\n" . nl2br( $because ) . "<HR>\n";
    $because .= "<P><B>Changes have not been processed - please correct because and re-submit</B></P>\n";
  }
?>