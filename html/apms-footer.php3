<BR CLEAR=ALL><HR>
<TABLE WIDTH=100% CELLSPACING=2 CELLPADDING=0>
<?php /*** if the user is an admin user then we display an admin menu just above the standard user menu */
if ( !isset($wrms_home) ) $wrms_home = ".";
if ( isset($usr) && $usr->access_level >= 90000 ) {
  echo "<TR><TD COLSPAN=2 ALIGN=CENTER><FONT SIZE=\"-1\" FACE=\"tahoma,sans-serif\">";
  echo "<A HREF=\"$wrms_home/admin-frame.php3\">Admin Menus</A>";
  echo "&nbsp;|&nbsp;<A HREF=\"$wrms_home/list-updates.php3\">Updates</A>";
  echo "&nbsp;|&nbsp;<A HREF=\"$wrms_home/list-timesheets.php3\">Timesheets</A>";
  echo "&nbsp;|&nbsp;<A HREF=\"$wrms_home/list-quotes.php3\">Quotes</A>";
  echo "&nbsp;|&nbsp;<A HREF=\"$wrms_home/new-update.php3\">New Update</A>";
  if ( strcmp("$admin_options","") ) echo $admin_options;
  echo "</FONT></TD></TR>";
}
?>
<TR><TD ALIGN=LEFT VALIGN=TOP WIDTH=20%><?php
  if ( isset($usr) ) echo "<FONT SIZE=\"-1\" FACE=\"tahoma,sans-serif\"><A HREF=\"$wrms_home/user-details.php3\">Details for $usr->username</A>&nbsp;|&nbsp;";
  echo "<A HREF=\"$wrms_home/help.php3\">Help!</A></FONT></TD>";
  echo "<TD ALIGN=RIGHT WIDTH=80%><FONT SIZE=\"-1\" FACE=\"tahoma,sans-serif\">";
  echo "<A HREF=\"$wrms_home/home.php3\">Catalyst WRMS Home</A>";
  echo "&nbsp;|&nbsp;<A HREF=\"$wrms_home/new-request.php3\">New Work Request</A>";
  echo "&nbsp;|&nbsp;<A HREF=\"$wrms_home/list-requests.php3\">List Work Requests</A>";
  echo "&nbsp;|&nbsp;<A HREF=\"$wrms_home/search.php3\">Search</A>";
  if ( strcmp("$user_options","") ) echo $user_options;
?>
</FONT></TD></TR></TABLE>
<HR>

</BODY>
</HTML>
