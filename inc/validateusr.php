<?php
  $because = "";
  if ( "$UserFullName" == "" )	$because .= "User has no full name!<br>";
  if ( "$UserEmail" == "" )	$because .= "User has no e-mail address!<br>";
  if ( "$UserName" == "" )	$because .= "User has no username!<br>";
  if ( "$M" == "add" && "$UserPassword" == "")
    $because .= "User has no password.<br>";

?>
