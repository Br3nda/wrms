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
require_once("User.class");

$user = new User($user_no);
if ( $user->user_no == "" ) {
  unset( $user_no );
  $edit = 1;
  $title = ( $user_no != "" ? "User Unavailable" : "New User" );
}
else {
  $title = "$user->user_no - $user->fullname";
}
$show = 0;
$new = isset($edit) && intval($edit) && !isset($id);

if ( $M != "LC" && isset($_POST['submit']) ) {
  if ( $session->AllowedTo("Admin") || $session->AllowedTo("Support")
           || ( $session->AllowedTo("Manage") && $session->org_code == $this->org_code )
            || ($user->user_no > 0 && $user->user_no == $session->user_no) ) {
    if ( $user->Validate($userf) ) {
      $user->Write($userf);
      $user = new User($user_no);
    }
  }
}

include("headers.php");
echo '<script language="JavaScript" src="/js/worksystem.js"></script>' . "\n";
echo $user->Render();
include("footers.php");
?>