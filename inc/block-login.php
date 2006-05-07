<?php
function send_login_block() {
  global $go_width, $go_height, $REQUEST_URI, $theme;
  echo "<br />\n";
  $theme->BlockOpen();
  $theme->BlockTitle("WRMS Login");

  if ( $go_width && $go_height ) {
    $dim = " width=\"$go_width\" height=\"$go_height\" ";
  }
  $action_target = $REQUEST_URI;
  $action_target = ereg_replace( '\?logout(=1)?&', '?', $action_target );
  $action_target = ereg_replace( '\?logout(=1)?$', '', $action_target );
  $action_target = ereg_replace( '\?M=LO&', '?', $action_target );
  $action_target = ereg_replace( '\?M=LO', '', $action_target );

  echo "<form action=\"$action_target\" method=\"post\" style=\"display:inline;\">";
  echo " &nbsp;username:<br>\n";
  echo " &nbsp; <font size=\"1\"><input type=\"text\" name=\"username\" size=\"12\"></font>\n";

  echo "<br> &nbsp;password:<br>\n";
  echo "&nbsp; <font size=\"1\"><input type=\"password\" name=\"password\" size=\"7\"></font>";

  echo "&nbsp;<input type=\"submit\" value=\"GO!\" alt=\"go\" name=\"submit\" class=\"submit\"></font><br clear=\"all\">\n";
  echo " &nbsp;forget&nbsp;me&nbsp;not:<font size=\"2\"><input type=\"checkbox\" name=\"remember\" value=\"1\"></font>\n";

  echo "</form><br>\n";

  echo "<img src=\"/images/clear.gif\" width=\"155\" height=\"1\" hspace=\"0\" vspace=\"2\" border=\"0\">\n";
  $theme->BlockClose();
}
send_login_block();
?>