<?php
require_once("always.php");
require_once("authorisation-page.php");
if ( !$session->logged_in ) {
  include("headers.php");
  echo "<h3>Please log on for access to work requests</h3>\n";
  include("footers.php");
  exit;
}

require_once("maintenance-page.php");
require_once("WorkSystem.class");

$ws = new WorkSystem($system_code);
if ( $ws->system_code == "" ) {
  unset( $system_code );
  $title = ( isset($GLOBALS['edit']) ? "System Unavailable" : "New System" );
}
else {
  $title = "$ws->system_code - $ws->system_desc";
}

if ( $M != "LC" && $ws->AllowedTo("update") && isset($_POST['submit']) ) {
  if ( $ws->Validate($wsf) ) {
    $ws->Write($wsf);
  }
}

  require_once("top-menu-bar.php");
  require_once("headers.php");
  echo '<script language="JavaScript" src="/js/worksystem.js"></script>' . "\n";
  echo $ws->Render();
  include("footers.php");
?>