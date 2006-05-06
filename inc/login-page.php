<?php
  $right_panel = false;
  $title = $system_name;
  include("page-header.php");

  if ( "$error_loc$error_msg$warn_msg" <> "" ) {
    include( "error.php" );
  }
  if ( "$error_loc$error_msg" == "" ) {
    include("indexpage.php");
  }

  include("page-footer.php");
?>
