<?php
  include("always.php");
  include("options.php");
  $right_panel = false;
  $title = "Catalyst's Work Request Management System";
  include("headers.php");

  if ( "$error_loc$error_msg$warn_msg" <> "" ) {
    include( "error.php" );
  }
  if ( "$error_loc$error_msg" == "" ) {
    include("indexpage.php");
  }

  include("footers.php");
?>
