<?php
  $because = "";
  if ( isset($UserFullName) && "$UserFullName" == "" )	$because .= "User has no full name!<br>";
  if ( isset($UserEmail)    && "$UserEmail" == "" )	$because .= "User has no e-mail address!<br>";

  if ( "$M" == "add" && "$UserPassword" == "")
    $because .= "User has no password.<br>";

  if ( ! is_member_of('Admin','Support','Manage')
         && "$M" == "update" && $user_no != $session->user_no )
    $because .= "You are not authorised<BR>";

  if ( ! is_member_of('Admin','Support') ) {
    if ( isset($usr) && $M <> "add" && $usr->org_code <> $session->org_code )
      $because .= "You may only maintain users for your organisation<BR>";
  }

  if ( "$M" == "add" ) {
    if ( isset($UserName) && "$UserName" == "" )	$because .= "User has no username!<br>";
    $query = "SELECT * FROM usr WHERE username = '" . tidy($UserName) . "';";
    $rid = awm_pgexec( $dbconn, $query, "validateuser" );
    if ( ! $rid ) {
      $because .= "Database Error!<br>";
    }
    else if ( pg_NumRows($rid) > 0 ) {
      $because .= "That username (\"$UserName\") is already active<br>";
    }
  }

?>
