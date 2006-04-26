<?php
  $because = "";

  if ( ! $logged_on )
    $because .= "You must log on with a valid user ID and password\n";

  if ( "$fsystem_id" == "" ) {
    if ( "$session->system_id" == "" )
      $because .= "You must select a valid system_id\n";
    else
      $fsystem_id == $session->system_id;
  }

  // Validate that they are only maintaining a request for a system_id they
  if ( is_member_of('Admin') ) {
    // OK, they can do anything :-)
  }
  else if ( $system_id_roles["$fsystem_id"] == "V" ) {
    $because = "You may only view records for that system_id";
  }
  else if ( $system_id_roles["$fsystem_id"] == "H" || $system_id_roles["$fsystem_id"] == "M" ) {
    // That's OK - this is their home system_id, or maintenance is enabled
  }
  else {
    $because .= "You may only maintain requests from your system_id\n";
  }

  if ( "$because" <> "" ) {
    $because = "<H2>Errors with request:</H2>\n" . nl2br( $because ) . "<HR>\n";
    $because .= "<P><B>Changes have not been processed - please correct because and re-submit</B></P>\n";
  }
?>