<?php
  echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=\"100%\">\n";
  echo "<tr><td BGCOLOR=$colors[6] width=\"94%\"><font size=1>\n";
  echo "<a href=$base_url/request.php?request_id=$request_id&style=plain>Printable</a> | \n";
  echo "<a href=$base_url/request.php?request_id=$request_id>Editable</a>\n";
  echo "</font></td>\n";
  echo "<td BGCOLOR=$colors[6] align=right nowrap width=\"3%\"><font size=1><form method=get action=/request.php>\n";
  echo "Go to: </font></td>\n";
  echo "<td BGCOLOR=$colors[6] align=right nowrap width=\"3%\"><font size=1>";
  echo "<input type=hidden value=\"$style\" name=style>\n";
  echo "<input type=text size=6 value=\"$request_id\" name=request_id>\n";
  echo "</font></td>";
  echo "</tr>\n";
  echo "</table>\n";
?>

