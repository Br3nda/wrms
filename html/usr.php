<?php
  include("inc/always.php");
  include("inc/options.php");

  if ( ! ($roles['wrms']['Admin'] || $roles['wrms']['Support'] || $roles['wrms']['Manage']) )
    $user_no = $session->user_no;

  if ( "$submit" != "" ) {
    include("inc/getusr.php");
    include("inc/validateusr.php");
    if ( $because == "" ) {
      if ( "$submit" == "Delete This User" ) {
        if ( $user_no != $session->user_no ) {
          include("inc/deleteusr.php");
        }
      }
      else
        include("inc/writeusr.php");
    }
  }

  include("inc/getusr.php");

  if ( ! ($roles['wrms']['Admin'] || $roles['wrms']['Support']) && $session->org_code <> $usr->org_code )
    $because = "You may only view users from your organisation";

  $title = "$system_name User Manager";
  include("inc/starthead.php");
  include("inc/styledef.php");
  include("inc/bodydef.php");
  include("inc/menuhead.php");

  // Pre-build the list of systems
  if ( "$error_qry" == "" ) {
    $query = "SELECT * FROM work_system ";
    if ( ! ($roles['wrms']['Admin'] || $roles['wrms']['Support'] ) ) {
      $query .= " WHERE system_code=org_system.system_code ";
      $query .= " AND org_system.org_code='$session->org_code' ";
    }
    $query .= " ORDER BY system_code ";
    $sys_res = pg_Exec( $wrms_db, $query );
    if ( ! $sys_res ) {
      $error_loc = "usr.php";
      $error_qry = "$query";
    }
  }

  // Pre-build the list of user groups
  if ( "$error_qry" == "" ) {
    $query = "SELECT * FROM ugroup";
    $grp_res = pg_Exec( $wrms_db, $query );
    if ( ! $grp_res ) {
      $error_loc = "usr.php";
      $error_qry = "$query";
    }
  }

  $hdcell = "<th width=7%><img src=images/clear.gif width=60 height=2></th>";
  $tbldef = "<table width=100% cellspacing=0 border=0 cellpadding=2";

  if ( "$error_qry" != "" ) {
    include( "inc/error.php" );
  }
  else {
    if ( ! ($roles['wrms']['Admin'] || $roles['wrms']['Support']) && $session->org_code <> $usr->org_code )
      return;

    echo "<table border=0 cellspacing=0 cellpadding=0 width=90% height=30><tr valign=bottom><td>\n";
    if ( "$because" != "" ) {
      echo "$because";
    }
    else {
      echo "<h3 style=\"padding: 0pt; margin: 0pt; \">User Profile";
      if (isset($user_no) && $user_no > 0 ) echo " for $usr->fullname";
      echo "</h3>\n";
    }
    echo "</td>\n";
    if ( $roles['wrms']['Admin'] ) {
      echo "<td align=right><form action=usr.php method=post>";
      echo "<input type=hidden name=user_no value=$user_no>";
      echo "<input type=hidden name=M value=delete>";
      echo "<font size=1 weight=bold><input type=submit value=\"Delete This User\" name=submit style=\"color: navy; padding: 0pt; margin: 0pt;\"></font></form></td>\n";
    }
    echo "</tr></table>\n";
    echo "<form action=usr.php method=post>";
    echo "<input type=hidden name=user_no value=$user_no>";
    echo "<input type=hidden name=M value=";
    if (isset($user_no) && $user_no > 0 ) echo "update"; else echo "add";
    echo ">";
?>

<?php echo "$tbldef><TR><TD CLASS=sml COLSPAN=2>&nbsp;</TD></TR><TR>$hdcell"; ?>
<TD CLASS=h3 ALIGN=RIGHT<?php echo " bgcolor=$colors[8]"; ?>><FONT SIZE=+1 color=<?php echo $colors[1]; ?>><B>User Details</B></FONT></TD></TR>
<TR bgcolor=<?php echo $colors[6]; ?>> 
	<th align=right>Login ID</TH>
	<TD><font Size=2><?php echo "$user_no"; ?>&nbsp;</font></td>
</tr>	 
<tr bgcolor=<?php echo $colors[6]; ?>> 
	<th align=right>User Name</th> 
	<td><?php
if ( $roles['wrms']['Admin'] || ("$usr->username" == ""))
  echo "<input Type=\"Text\" Name=\"UserName\" Size=\"15\" Value=\"";
else
  echo "<h3>";
echo "$usr->username";
if ( $roles['wrms']['Admin'] || ("$usr->username" == "") ) echo "\">";
echo "</td>"; ?>
</tr> 
<tr bgcolor=<?php echo $colors[6]; ?>> 
	<th align=right>Password</th> 
	<td><font Size="2"><input Type=password Name="UserPassword" Size="15" Value="<?php
if (isset($user_no) && $user_no > 0 ) echo "      ";
?>"></font></td> 
</tr> 
<tr bgcolor=<?php echo $colors[6]; ?>> 
	<th align=right>Email</th> 
	<td><font size=2><input Type="Text" Name="UserEmail" Size="50" Value="<?php echo "$usr->email"; ?>"></font></td> 
</tr> 
<tr bgcolor=<?php echo $colors[6]; ?>> 
	<th align=right>Full Name</th>
	<td><font Size="2"><input Type="Text" Name="UserFullName" Size="24" Value="<?php echo "$usr->fullname"; ?>"></font></td> 
</tr> 
<?php if ( $roles['wrms']['Admin'] ) { ?>
<tr bgcolor=<?php echo $colors[6]; ?>> 
	<th align=right>User Status</th> 
	<td><font Size="2">
	<input Type="Radio" Name="UserStatus" Value="S"<?php if ("$usr->status" == "S" ) echo " CHECKED"; ?>> System Support &nbsp; 
	<input Type="Radio" Name="UserStatus" Value="C"<?php if ("$usr->status" == "C" ) echo " CHECKED"; ?>> Client Coordinator &nbsp;
	<input Type="Radio" Name="UserStatus" Value="U"<?php if ("$usr->status" == "U" ) echo " CHECKED"; ?>> System User &nbsp;</font></td> 
</tr>
<?php
  }  // end of   'if Admin... ' about 5 lines up
  if ( "$user_no" > 0 ) {
    echo "<tr bgcolor=$colors[6]>\n<th align=right>Date Joined&nbsp;</th>";
    echo "<td VALIGN=TOP><font Size=2>";
    echo substr("$usr->joined", 0, 16);
    echo " &nbsp; &nbsp; &nbsp; &nbsp; Last Updated&nbsp; ";
    echo substr("$usr->last_update", 0, 16);
    echo "</td></tr>";
  }

  if ( $roles[wrms][Admin] ) {
    // This displays checkboxes to select the users special roles.
    echo "\n<tr bgcolor=$colors[6]>\n<th align=right>User Roles</th>";
    echo "<td VALIGN=TOP><font Size=2>\n<table border=0 cellspacing=0 cellpadding=3><tr>\n";
    for ( $i=0; $i <pg_NumRows($grp_res); $i++) {
      if ( $i > 0 && ($i % 3) == 0 ) echo "</tr><tr>";
      $grp = pg_Fetch_Object( $grp_res, $i );
      echo "<td><font size=2><input type=checkbox name=\"UserRole[$grp->module_name][$grp->group_name]\"";
      if ( isset($UserRole) && is_array($UserRole) && $UserRole[$grp->module_name][$grp->group_name] ) echo " CHECKED";
      echo "> " . ucfirst($grp->module_name) . " $grp->group_name\n";
      echo " &nbsp; </font></td>\n";
    }
    echo "</select></font></td></tr></table></td></tr>\n";
  }
?>
</table> 

<?php echo "$tbldef><TR><TD CLASS=sml COLSPAN=3>&nbsp;</TD></TR><TR>$hdcell"; ?>
<TD CLASS=h3 COLSPAN=2 ALIGN=RIGHT<?php echo " bgcolor=$colors[8]"; ?>><FONT SIZE=+1 color=<?php echo $colors[1]; ?>><B>System Access</B></FONT></TD></TR>

<?php
    // This displays all those checkboxes to select the systems the user can access.
    for ( $i=0; $i <pg_NumRows($sys_res); $i++) {
      $sys = pg_Fetch_Object( $sys_res, $i );
      if(floor($i/2)-($i/2)==0) echo "<tr bgcolor=$colors[6]>";
      else echo "<tr bgcolor=$colors[7]>";
      echo "$hdcell\n";
      echo "<td NOWRAP><font size=1>$sys->system_desc</td>\n";
      echo "<td NOWRAP><font size=1>\n";
      if ( isset($UserCat) && is_array($UserCat) )
        $code = $UserCat[$sys->system_code];
      else
        $code = "";
      echo "<select name=\"UserCat[$sys->system_code]\">\n";

      echo "<option value=\"\"";
      if ( "$code" == "" ) echo " SELECTED";
      echo ">--- no access ---</option>\n";

      echo "<option value=A";
      if ( "$code" == 'A') echo " SELECTED";
      echo ">Administration</option>\n";

      echo "<option value=S";
      if ( "$code" == 'S') echo " SELECTED";
      echo ">System Support</option>\n";

      echo "<option value=C";
      if ( "$code" == 'C') echo " SELECTED";
      echo ">Client Coordinator</option>\n";

      echo "<option value=E";
      if ( "$code" == 'E') echo " SELECTED";
      echo ">Enter Requests</option>\n";

      echo "<option value=R";
      if ( "$code" == 'R') echo " SELECTED";
      echo ">Own Requests</option>\n";

      echo "<option value=U";
      if ( "$code" == 'U') echo " SELECTED";
      echo ">View Requests</option>\n";

      echo "</select></font></td>\n";
    }

    echo "</table>\n";

    echo "$tbldef>\n<tr><td align=center class=mand>";
    echo "<B><INPUT TYPE=\"submit\" NAME=\"submit\" VALUE=\"";
    if ( isset($user_no) && $user_no > 0 )
      echo " Apply Changes ";
    else
      echo " Add User ";
    echo "\"></b></td>\n</tr></table></form>";
  } // end of "else 'there was no error' way up there.
  } // Do we need another?
?>
</body> 
</html>


