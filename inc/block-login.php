<?php
  block_open();
  block_title("WRMS Login");
  echo "<tr>\n<td class=blockhead>\n";
  echo "<form action=\"$REQUEST_URI\" method=post>";
  echo "<input type=hidden name=M value=LC>\n";
  echo " &nbsp;username:<br>\n";
  echo " &nbsp; <font size=1><input type=text name=E size=\"12\"></font>\n";

  echo "<br> &nbsp;password:<br>\n";
  echo "&nbsp; <font size=1><input type=password name=L size=\"7\"></font>";
  echo "&nbsp;<input type=image src=\"$images/in-go.gif\" alt=go width=\"$go_width\" height=\"$go_height\" border=\"0\" name=submit$submit_align></font><br clear=all>\n";
  echo " &nbsp;forget&nbsp;me&nbsp;not:<font size=2><input type=checkbox name=remember value=\"1\"></font>\n";
?>
</form></td></tr>
<tr valign=middle><td class=blockhead align=center style="padding: 4px 0px 5px 0px;"><a href="join.php" title="Click here to join!"><img src="images/join.gif" width="106" height="15" border="0"></a></td></tr>
<?php block_close(); ?>

