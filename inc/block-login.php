<?php
  block_open();
  block_title("WRMS Login");

  if ( $go_width && $go_height ) {
    $dim = " width=\"$go_width\" height=\"$go_height\" ";
  }

  echo "<tr>\n<td class=block>\n";
  echo "<form action=\"$REQUEST_URI\" method=post>";
  echo "<input type=hidden name=M value=LC>\n";
  echo " &nbsp;username:<br>\n";
  echo " &nbsp; <font size=1><input type=text name=E size=\"12\"></font>\n";

  echo "<br> &nbsp;password:<br>\n";
  echo "&nbsp; <font size=1><input type=password name=L size=\"7\"></font>";

  echo "&nbsp;<input type=submit value=\"GO!\" alt=go name=submit class=\"submit\"></font><br clear=all>\n";
  echo " &nbsp;forget&nbsp;me&nbsp;not:<font size=2><input type=checkbox name=remember value=\"1\"></font>\n";

  echo "</form></td></tr>\n";

  echo "<tr><td class=block>\n";
  echo "<img src=\"images/clear.gif\" width=\"155\" height=\"1\" hspace=\"0\" vspace=\"2\" border=\"0\">\n";
  echo "</td></tr>\n";
  block_close();
  echo "<img src=\"images/clear.gif\" width=\"155\" height=\"50\" hspace=\"0\" vspace=\"2\" border=\"0\">\n";
?>
