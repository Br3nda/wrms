<?php
  $right_panel = false;
  $title = "Catalyst's Work Request Management System";
  include("inc/headers.php");

  if ( "$error_loc$error_msg$warn_msg" <> "" ) {
    include( "inc/error.php" );
  }
  if ( "$error_loc$error_msg" == "" ) {
    include("inc/indexpage.php");
  }

  include("inc/footers.php");
?>
