<?php
  $because = "";

  if ( ! $logged_on )
    $because .= "You must log on with a valid password and maintainer ID\n";

  // Validate that they are only maintaining a request for a system_id they
  if ( is_member_of('Admin') ) {
    // OK, they can do anything :-)
  }
  else
    $because .= "Only administrators have access to this function.\n";
  }

  if ( "$because" <> "" ) {
    $because = "<H2>Errors with request:</H2>\n" . nl2br( $because ) . "<HR>\n";
    $because .= "<P><B>Changes have not been processed - please correct because and re-submit</B></P>\n";
  }
?>

