<?php
  include("inc/always.php");
  include("inc/options.php");
  $right_panel = false;
  include("inc/headers.php");

  if ( "$error_loc$error_msg$warn_msg" <> "" ) {
    include( "inc/error.php" );
  }
  if ( "$error_loc$error_msg" == "" ) {
    include("inc/indexpage.php");
  }

  include("inc/footers.php");
?>

