<?php

  $logged_on = $session->logged_in;  // FIXME: Compatibility with older code...
  $settings = new Setting( $session->config_data );
  while( list( $k, $v ) = each( $session->roles ) ) {
    $roles["wrms"]["$k"] = 1;
  }

  if ( !($session->AllowedTo("Admin") || $session->AllowedTo("Support") || $session->AllowedTo("Contractor")) ) {
    $org_code = $session->org_code;
  }

  if ( is_object($settings) ) {
    $bigboxrows = $settings->get('bigboxrows');
    $bigboxcols = $settings->get('bigboxcols');
  }
  if ( intval($bigboxrows) == 0 ) $bigboxrows = 10;
  if ( intval($bigboxcols) == 0 ) $bigboxcols = 60;
?>
