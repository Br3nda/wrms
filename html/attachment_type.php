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
require_once("AttachmentType.class");

$att = new AttachmentType($type_code, (trim("$type_code") == "" ) );
if ( $att->new_record ) {
  unset( $type_code );
  $edit = 1;
  $title = "New Attachment Type" ;
}
else {
  $title = "$att->type_code - $att->type_desc";
}
$show = 0;

if ( $M != "LC" && $att->AllowedTo("update") && isset($_POST['submit']) ) {
  if ( $att->Validate($attf) ) {
    $att->Write($attf);
  }
}

  include("headers.php");

  echo $att->Render();

  include("footers.php");
?>