<?php
  $because = "";

  if ( ! $logged_on )
    $because .= "You must log on with a valid password and maintainer ID\n";

  // Validate that they are only maintaining an organisation they are allowed to access
  if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] ) {
    // OK, they can do anything :-)
  }
  else if ( $roles['wrms']['Manage'] ) {
    if ( "$M" == "add" )
      $because .= "You may not create organisations.\n";
    else {
      $org_code = $session->org_code;
      unset($active);
      unset($current_sla);
      unset($debtor_no);
      unset($work_rate);
    }
  }
  else
    $because .= "You may not maintain this organisation.\n";


  if ( "$because" <> "" ) {
    $because = "<H2>Errors with request:</H2>\n" . nl2br( $because ) . "<HR>\n";
    $because .= "<P><B>Changes have not been processed - please correct because and re-submit</B></P>\n";
  }
?>

