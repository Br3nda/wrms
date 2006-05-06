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
require_once("AttachmentType.class");

// Since there is no unique ID for this table other than the short name
// we need to do some further mucking around.
if ( isset($_GET['type_code']) ) {
  $old_type_code = clean_type_code($_GET['type_code']);
  $type_code = $old_type_code;
}
if ( isset($_POST['type_code']) ) {
  $new_type_code = clean_type_code($_POST['type_code']);
  $type_code = $new_type_code;
}

$att = new AttachmentType($old_type_code);

if ( !$session->just_logged_in && $att->AllowedTo("update") && isset($_POST['submit']) ) {
  if ( $att->Validate() ) {
    $att->Write();
    $att = new AttachmentType($new_type_code);
  }
}

if ( $att->new_record ) {
  unset( $type_code );
  $edit = 1;
  $title = "New Attachment Type" ;
}
else {
  $title = "$att->type_code - $att->type_desc";
}
$show = 0;

  require_once("top-menu-bar.php");
  require_once("page-header.php");

  echo $att->Render();

  include("page-footer.php");
?>
