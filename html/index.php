<?php
  include("always.php");
  require_once("authorisation-page.php");

  $right_panel = false;
  $title = $system_name;
  include("headers.php");

  if ( "$error_loc$error_msg$warn_msg" <> "" ) {
    include( "error.php" );
  }
  if ( "$error_loc$error_msg" == "" ) {
    include("indexpage.php");
  }

  include("footers.php");
?>
