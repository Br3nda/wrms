<?php
  if ( $logged_on )
    include("inc/block-menu.php");
  else
    include("inc/block-login.php");

  block_open();
  echo "<tr><td>\n";
  echo "<img src=\"images/clear.gif\" width=\"125\" height=\"1\" hspace=\"0\" vspace=\"0\" border=\"0\"></td></tr>\n";
  echo "</td></tr>\n";
  block_close();
?>
