<?php
  include("inc/always.php");
  include("inc/options.php");
  include("inc/organisation-list.php");
  include("inc/tidy.php");

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

  if ( ! ($roles['wrms']['Admin'] || $roles['wrms']['Support']) && ($session->org_code <> $usr->org_code && intval("$user_no") <> 0) )
    $because = "You may only view users from your organisation";

  $title = "$system_name User Manager";
  include("inc/headers.php");

  // Pre-build the list of systems
  if ( "$error_qry" == "" ) {
    $query = "SELECT DISTINCT ON (work_system.system_code) * FROM work_system, org_system ";
    $query .= " WHERE work_system.system_code=org_system.system_code ";
    if ( ! ($roles['wrms']['Admin'] || $roles['wrms']['Support'] ) ) {
      $query .= " AND org_system.org_code='$session->org_code' ";
    }
    $query .= " ORDER BY work_system.system_code ";
    $sys_res = awm_pgexec( $wrms_db, $query, "usr", false, 7 );
  }

  // Pre-build the list of user groups
  if ( $sys_res ) {
    $query = "SELECT * FROM ugroup";
    $grp_res = awm_pgexec( $wrms_db, $query, "usr" );
  }

  $hdcell = "";
  $tbldef = "<table width=100% cellspacing=0 border=0 cellpadding=2";

  if ( ! ($roles['wrms']['Admin'] || $roles['wrms']['Support']) && ($session->org_code <> $usr->org_code && intval("$user_no") <> 0) )
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
    echo "<font size=1 weight=bold><input type=submit value=\"Delete This User\" name=submit class=submit></font></form></td>\n";
  }
  echo "</tr></table>\n";
  echo "<form action=usr.php method=post>";
  echo "<input type=hidden name=user_no value=$user_no>";
  echo "<input type=hidden name=M value=";
  if (isset($user_no) && $user_no > 0 ) echo "update"; else echo "add";
  echo ">";
?>

<?php echo "$tbldef><TR><TD CLASS=sml COLSPAN=2>&nbsp;</TD></TR><TR>$hdcell"; ?>
<TD CLASS=h3 ALIGN=RIGHT colspan=2>User Details</TD></TR>
<TR bgcolor=<?php echo $colors[6]; ?>>
	<th align=right class=rows>Login ID</TH>
	<TD><font Size=2><?php echo "$user_no"; ?>&nbsp;</font></td>
</tr>
<tr bgcolor=<?php echo $colors[6]; ?>>
	<th align=right class=rows>User Name</th>
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
	<th align=right class=rows>Password</th>
	<td><font Size="2"><input Type=password Name="UserPassword" Size="15" Value="<?php
if (isset($user_no) && $user_no > 0 ) echo "      ";
?>"></font></td>
</tr>
<tr bgcolor=<?php echo $colors[6]; ?>>
	<th align=right class=rows>Email</th>
	<td><font size=2><input Type="Text" Name="UserEmail" Size="50" Value="<?php echo "$usr->email"; ?>"></font></td>
</tr>
<tr bgcolor=<?php echo $colors[6]; ?>>
	<th align=right class=rows>Full Name</th>
	<td><font Size="2"><input Type="Text" Name="UserFullName" Size="24" Value="<?php echo "$usr->fullname"; ?>"></font></td>
</tr>
<?php

  if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] || $roles['wrms']['Manage'] ) {
    echo "\n<tr bgcolor=$colors[6]>\n<th align=right class=rows>Status</th>";
    echo "<td VALIGN=TOP><font Size=2>\n<table border=0 cellspacing=0 cellpadding=3><tr>\n";
    echo "<td><font size=2>";
    echo "<label><input type=radio name=\"UserStatus\"" . ( !isset($usr->status) || $usr->status <> "I" ? " CHECKED" : "" ) . " value=\"A\"> Active</label>\n";
    echo "<label><input type=radio name=\"UserStatus\"" . ( "$usr->status" == "I" ? " CHECKED" : "" ) . " value=\"I\"> Inactive</label>\n";
    echo "</font></td>\n</tr></table></td></tr>\n";
  }

  if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] ) {
    $org_code_list = get_organisation_list( "$usr->org_code" );
    echo "<tr bgcolor=$colors[6]>\n";
    echo "<th align=right class=rows>Organisation</th>\n";
    echo "<td><font Size=\"2\"><select class=sml name=UserOrganisation>$org_code_list</select>\n";
    echo "</tr>\n";
  }

  if ( "$user_no" > 0 ) {
    echo "<tr bgcolor=$colors[6]>\n<th align=right class=rows>Date Joined&nbsp;</th>";
    echo "<td VALIGN=TOP><font Size=2>";
    echo substr("$usr->joined", 0, 16);
    echo " &nbsp; &nbsp; &nbsp; &nbsp; Last Updated&nbsp; ";
    echo substr("$usr->last_update", 0, 16);
    echo "</td></tr>";
  }

  if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] || $roles['wrms']['Manage'] ) {
    // This displays checkboxes to select the users special roles.
    echo "\n<tr bgcolor=$colors[6]>\n<th align=right class=rows>User Roles</th>";
    echo "<td VALIGN=TOP><font Size=2>\n<table border=0 cellspacing=0 cellpadding=3><tr>\n";
    for ( $i=0, $j=0; $i <pg_NumRows($grp_res); $i++) {
      $grp = pg_Fetch_Object( $grp_res, $i );
      if ( "$grp->group_name" == "Admin" && !$roles[wrms][Admin] ) continue;
      if ( "$grp->group_name" == "Support" && !($roles[wrms][Admin] || $roles[wrms][Support]) ) continue;
      if ( "$grp->group_name" == "Manage" && !($roles[wrms][Admin] || $roles[wrms][Support] || $roles[wrms][Manage]) ) continue;
      if ( $j > 0 && ($j % 3) == 0 ) echo "</tr><tr>";
      echo "<td><font size=2><input type=checkbox name=\"NewUserRole[$grp->module_name][$grp->group_name]\"";
      if ( isset($UserRole) && is_array($UserRole) && $UserRole[$grp->module_name][$grp->group_name] ) echo " CHECKED";
      else if ( !isset($UserRole) && ("$grp->group_name" == "Request") ) echo " CHECKED";
      echo "> " . ucfirst($grp->module_name) . " $grp->group_name\n";
      echo " &nbsp; </font></td>\n";
      $j++;
    }
    echo "</select></font></td></tr></table></td></tr>\n";
  }
?>
</table>

<?php echo "$tbldef><TR><TD CLASS=sml COLSPAN=3>&nbsp;</TD></TR><TR>$hdcell"; ?>
<TD CLASS=h3 COLSPAN=2 ALIGN=RIGHT<?php echo " bgcolor=$colors[8]"; ?>><FONT SIZE=+1 color=<?php echo $colors[1]; ?>><B>System Access</B></FONT></TD></TR>

<?php
  // This displays all those checkboxes to select the systems the user can access.
  for ( $i=0; $i < pg_NumRows($sys_res); $i++) {
    $sys = pg_Fetch_Object( $sys_res, $i );
    if ( $i % 2 == 0 ) echo "<tr bgcolor=$colors[row1]>";
    else echo "<tr bgcolor=$colors[row2]>";
    echo "$hdcell\n";
    echo "<td nowrap><font size=1>$sys->system_desc</td>\n";
    echo "<td nowrap><font size=1>\n";
    if ( isset($UserCat) && is_array($UserCat) )
      $code = $UserCat[$sys->system_code];
    else
      $code = "";
    if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] || $roles['wrms']['Manage'] ) {
      echo "<select class=sml style=\"width: 150px;\" name=\"NewUserCat[$sys->system_code]\">\n";

      echo "<option value=\"\"";
      if ( "$code" == "" && ($roles['wrms']['Admin'] || $roles['wrms']['Support']) ) echo " selected";
      echo ">--- no access ---</option>\n";

      if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] ) {
        echo "<option value=A";
        if ( "$code" == 'A') echo " selected";
        echo ">Administration</option>\n";

        echo "<option value=S";
        if ( "$code" == 'S') echo " selected";
        echo ">System Support</option>\n";
      }

      echo "<option value=C";
      if ( "$code" == 'C') echo " selected";
      echo ">Client Coordinator</option>\n";

      echo "<option value=E";
      if ( "$code" == 'E' || ("$code" == "" && !($roles['wrms']['Admin'] || $roles['wrms']['Support'])) ) echo " selected";
      echo ">Enter Requests</option>\n";

      echo "<option value=R";
      if ( "$code" == 'R') echo " selected";
      echo ">Own Requests</option>\n";

      echo "<option value=U";
      if ( "$code" == 'U') echo " selected";
      echo ">View Requests</option>\n";

      echo "</select></font></td>\n";
    }
    else {
      if ( "$code" == 'A')      echo "Administration";
      else if ( "$code" == 'S') echo "System Support";
      else if ( "$code" == 'C') echo "Client Coordinator";
      else if ( "$code" == 'E') echo "Enter Requests";
      else if ( "$code" == 'R') echo "Own Requests";
      else if ( "$code" == 'U') echo "View Requests";
    }
  }

  echo "</table>\n";

  echo "$tbldef>\n<tr><td align=center class=mand>";
  echo "<B><input type=\"submit\" class=submit name=\"submit\" VALUE=\"";
  if ( isset($user_no) && $user_no > 0 )
    echo " Apply Changes ";
  else
    echo " Add User ";
  echo "\"></b></td>\n</tr></table></form>";

include("inc/footers.php");
?>