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

// Since there is no unique ID for this table other than the short name
// we need to do some further mucking around.
if ( isset($_GET['system_code']) ) {
  $old_system_code = clean_system_code($_GET['system_code']);
  $system_code = $old_system_code;
}
if ( isset($_POST['system_code']) ) {
  $new_system_code = clean_system_code($_POST['system_code']);
  $system_code = $new_system_code;
}

$ws = new WorkSystem($old_system_code);

if ( !$session->just_logged_in && $ws->AllowedTo("update") && isset($_POST['submit']) ) {
  if ( $ws->Validate() ) {
    $ws->Write();
    $ws = new WorkSystem($new_system_code);
  }
}
if ( $ws->system_code == "" ) {
  $title = ( isset($GLOBALS['edit']) ? "System Unavailable" : "New System" );
}
else {
  $title = "$ws->system_code - $ws->system_desc";
}

  require_once("top-menu-bar.php");
  require_once("headers.php");
  echo '<script language="JavaScript" src="/js/worksystem.js"></script>' . "\n";
  echo $ws->Render();
  include("footers.php");
?>