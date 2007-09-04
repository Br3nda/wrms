<?php
require_once("always.php");
require_once("authorisation-page.php");

$session->LoginRequired();

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
  require_once("page-header.php");
  echo '<script language="JavaScript" src="/js/worksystem.js"></script>' . "\n";
  echo $ws->Render();
  include("page-footer.php");
?>
