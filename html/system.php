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

$ws = new WorkSystem($system_id);
if ( $ws->system_id == 0 ) {
  unset( $system_id );
  $edit = 1;
  $title = ( intval($system_id) > 0 ? "System Unavailable" : "New System" );
}
else {
  $title = "$ws->system_id - $ws->system_desc";
}
$show = 0;
$new = isset($edit) && intval($edit) && !isset($id);

if ( !$session->just_logged_in && $ws->AllowedTo("update") && isset($_POST['submit']) ) {
  if ( $ws->Validate() ) {
    $ws->Write();
    $ws = new WorkSystem($system_id);
  }
}

  require_once("top-menu-bar.php");
  require_once("headers.php");
  echo '<script language="JavaScript" src="/js/worksystem.js"></script>' . "\n";
  echo $ws->Render();
  include("footers.php");
?>