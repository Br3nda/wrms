<?php
  $because = "";

  if ( ! $logged_on )
    $because .= "You must log on with a valid password and maintainer ID\n";

  // Validate that they are only maintaining a request for a system_code they
  if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] ) {
    // OK, they can do anything :-)
  }
  else
    $because .= "You may not maintain timesheet information.\n";


  if ( "$because" <> "" ) {
    $because = "<H2>Errors with request:</H2>\n" . nl2br( $because ) . "<HR>\n";
    $because .= "<P><B>Changes have not been processed - please correct because and re-submit</B></P>\n";
  }
?>

