<?php
  // Convert some legacy WRMS stuff to what we want here...
  if ( isset($LI) && !isset($M) && !isset($session_id) ) {
    // Handle the login cookie
      list( $E, $L ) = split( ";", $LI);
      $E = strtr( "$E", "abcdefghijklmnopqrstuvwxyz", "nopqrstuvwxyzabcdefghijklm" );
      $M = "LC";
  }
  if ( isset($session_id) && !isset($sid) ) {
    list( $session_test, $session_hash) = explode( " ", $session_id);
    $sid = "$session_test;$session_hash";
  }
  if ( isset($E) ) $username = $E;
  if ( isset($L) ) $password = $L;

  require_once("Session.php");

  $logged_on = $session->logged_in;
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