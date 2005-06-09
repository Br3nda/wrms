<?php
  if ( $GLOBALS['session']->logged_in )
    include("block-menu.php");
  else
    include("block-login.php");
?>