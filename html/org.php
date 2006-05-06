<?php
require_once("always.php");
require_once("authorisation-page.php");
if ( !$session->logged_in ) {
  include("page-header.php");
  echo "<h3>Please log on for access to work requests</h3>\n";
  include("page-footer.php");
  exit;
}

require_once("maintenance-page.php");
require_once("Organisation.class");

$org = new Organisation($org_code);
if ( $org->org_code == 0 ) {
  unset( $org_code );
  $edit = 1;
  $title = ( intval($org_code) > 0 ? "Organisation Unavailable" : "New Organisation" );
}
else {
  $title = "$org->org_code - $org->org_name";
}
$show = 0;
$new = isset($edit) && intval($edit) && !isset($id);

if ( $org->AllowedTo("update") && isset($_POST['submit']) ) {
  if ( $org->Validate($orgf) ) {
    $org->Write($orgf);
    $org = new Organisation($org_code);
  }
}

  require_once("top-menu-bar.php");
  require_once("page-header.php");
  echo '<script language="JavaScript" src="/js/organisation.js"></script>' . "\n";
  echo $org->Render();
  include("page-footer.php");
?>
