<?php
  echo "<form action=\"$look_href\" method=POST id=search name=search>\n";
  echo "<table border=0 cellpadding=0 cellspacing=0 width=80%><tr style=\"border-top: solid medium navy\">";
  echo "<td nowrap align=right><font size=1>&nbsp;Search <input type=text size=30 name=stext value=\"$stext\"></font> &nbsp; </td>\n";
  echo "<td align=left> &nbsp; <input type=image src=$base_url/images/in-go.gif alt=go width=44 border=0 height=26 id=go name=go></td></tr>\n";
  echo "</table></form>";
?>
