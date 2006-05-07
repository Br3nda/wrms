<?php
  $theme->BlockOpen($colors['row1'], $colors['bg2'] );
  $theme->BlockTitle("Search");
  echo "<tr><td class=block style=\"padding: 3px;\">\n";
?>
<form action="/wu.php" method=GET>
<input type=hidden name=last value=0>
<input type=text name=wu value="" size=18>
<input type=submit value="FIND NOW&raquo;" class=submit>
</form>
<?php
  echo "</td></tr>\n";
  $theme->BlockClose();

  echo "<img src=\"/images/clear.gif\" width=\"155\" height=\"50\" hspace=\"0\" vspace=\"2\" border=\"0\">\n";

?>
