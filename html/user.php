<?php
  require_once("always.php");
  require_once("authorisation-page.php");

  $session->LoginRequired();

  require_once("maintenance-page.php");
  require_once("User.class");

  $user = new User($user_no);
  if ( $user->user_no == 0 ) {
    $title = ( $user_no != "" ? "User Unavailable" : "New User" );
  }
  else {
    $title = "$user->user_no - $user->fullname";
  }
  $show = 0;

  if ( !$session->just_logged_in && isset($_POST['submit']) ) {
    if ( $session->AllowedTo("Admin") || $session->AllowedTo("Support")
            || ( $session->AllowedTo("OrgMgr") && ($user->user_no == 0 || $session->org_code == $user->org_code ))
              || ($user->user_no > 0 && $user->user_no == $session->user_no) ) {
      if ( $user->Validate($userf) ) {
        $user->Write($userf);
        $user = new User($user->user_no);
        if ( $user->user_no == 0 ) {
          $title = ( $user_no != "" ? "User Unavailable" : "New User" );
        }
        else {
          $title = "$user->user_no - $user->fullname";
        }
      }
    }
  }

  require_once("top-menu-bar.php");
  require_once("page-header.php");
  echo '<script language="JavaScript" src="/js/user.js"></script>' . "\n";
  echo $user->Render();
  include("page-footer.php");
?>
