<div align=center>
<?php
  echo "<table width=\"100%\" border=\"0\" bgcolor=\"$colors[1]\" cellspacing=\"0\" cellpadding=\"0\">\n";
  echo "<tr><td class=menu><a href=\"$base_url/index.php\"><img src=\"$base_url/images/wrms.gif\" border=\"0\" alt=\"$system_name\" WIDTH=\"200\" HEIGHT=\"25\"></a></td>\n";
  echo "<td class=menu><font SIZE=1 COLOR=$colors[3]>\n";
  if ( $logged_on ) {
    echo "<span style=\"font-weight: 700; \"><b>$session->fullname</b></span> ($session->user_no)";
    if ( isset($roles) && is_array($roles[$module]) ) {
      while ( list($key, $val) = each($roles[$module])) {
         echo " $key";
      }
    }
    echo "&nbsp; <a href=$base_url/usr.php?user_no=$session->user_no>My Details</a> | ";
    // Will append $module into the URL if it's <> "base"...  This means that wherever we log off
    // we'll still be in the same place afterwards.
    echo "<a href=$base_url/";
    if ( $roles[wrms][Admin] ) {
      $module_menu .= "<span style=\"font-weight: 700; \"><b>Admin:</b></span> <a href=$base_url/usrsearch.php>User Search</a> | ";
      $module_menu .= "<a href=$base_url/usr.php>New User</a> | ";
      $module_menu .= "<a href=$base_url/lookups.php>Lookup Codes</a> &nbsp; ";
    }
    if ( $roles[wrms][Request] ) {
      $module_menu .= "<span style=\"font-weight: 700; \"><b>Requests:</b></span> <a href=$base_url/requestlist.php>List Requests</a> | ";
      $module_menu .= "<a href=$base_url/request.php>New Request</a> | ";
      $module_menu .= "<a href=$base_url/form.php?form=listreq>Reporting</a> | ";
      $module_menu .= "<a href=$base_url/form.php?form=transfer>Transfer</a> | ";
      $module_menu .= "<a href=$base_url/form.php?form=training>Training</a> | ";
      $module_menu .= "<a href=$base_url/form.php?form=deceased>Deceased</a> ";
    }
    echo "index.php?M=LO>Logoff</a>\n &nbsp; $module_menu";
  }
?>&nbsp;</font>
		</TD>
	</tr>
	<tr>
		<td COLSPAN=2 HEIGHT="3" BGCOLOR="<?php echo $colors[5]; ?>"><img SRC="<?php echo $base_url; ?>/images/clear.gif" WIDTH="1" HEIGHT="3" BORDER="0" ALT=" "></td>
	</tr>
</table>
</div>


