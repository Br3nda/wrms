<?php
  $because = "";
  if ( "$UserFullName" == "" )	$because .= "User has no full name!<br>";
  if ( "$UserEmail" == "" )	$because .= "User has no e-mail address!<br>";
//  if ( "$UserName" == "" )	$because .= "User has no username!<br>";
  if ( "$M" == "add" && "$UserPassword" == "")
    $because .= "User has no password.<br>";

  if ( ! ($roles['wrms']['Admin'] || $roles[wrms]['Support']  || $roles[wrms]['Manage']) )
    $because .= "You are not authorised<BR>";

  if ( ! ($roles['wrms']['Admin'] || $roles[wrms]['Support']) ) {
    if ( isset($usr) && $M <> "add" && $usr->org_code <> $session->org_code )
      $because .= "You may only maintain users for your organisation<BR>";
  }

?>
