<?php
  $because = "";

  if ( ! $logged_on )
    $because .= "You must log on with a valid password and maintainer ID\n";
  if ( "$fsystem_code" == "" ) {
    if ( "$session->system_code" == "" )
      $because .= "You must select a valid system_code\n";
    else
      $fsystem_code == $session->system_code;
  }

  // Validate that they are only maintaining a request for a system_code they
  if ( $roles[wrms][Admin] ) {
    // OK, they can do anything :-)
  }
  else if ( $system_code_roles["$fsystem_code"] == "V" ) {
    $because .= "You may only transfer requests from your own system_code\n";
  }
  else if ( $system_code_roles["$fsystem_code"] == "H" || $system_code_roles["$fsystem_code"] == "M" ) {
    // That's OK - this is their home system_code, or maintenance is enabled
  }
  else
    $because .= "You may only transfer requests from your own system_code\n";
  }

  if ( "$because" <> "" ) {
    $because = "<H2>Errors with request:</H2>\n" . nl2br( $because ) . "<HR>\n";
    $because .= "<P><B>Changes have not been processed - please correct because and re-submit</B></P>\n";
  }

?>

