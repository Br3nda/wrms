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
    echo "<input type=submit value=\"Delete This User\" name=submit class=submit></form></td>\n";
  }
  echo "</tr></table>\n";
  echo "<form action=usr.php method=post>";
  echo "<input type=hidden name=user_no value=$user_no>";
  echo "<input type=hidden name=M value=";
  if (isset($user_no) && $user_no > 0 ) echo "update"; else echo "add";
  echo ">";
?>

<?php
  echo "$tbldef><tr><td class=sml colspan=2>&nbsp;</td></tr><tr>$hdcell";
  echo "<td class=h3 align=right colspan=2>User Details</td></tr>
<tr>
	<th align=right class=rows>Login ID</th>
	<td class=h1>$user_no&nbsp;</td>
</tr>
<tr>
	<th align=right class=rows>User Name</th>
	<td>";
if ( $roles['wrms']['Admin'] || ("$usr->username" == ""))
  echo "<input Type=\"Text\" Name=\"UserName\" Size=\"15\" Value=\"";
else
  echo "<h3>";
echo "$usr->username";
if ( $roles['wrms']['Admin'] || ("$usr->username" == "") ) echo "\">";
echo "</td>\n</tr>\n";

  echo "<tr>
  <th align=right class=rows>Password</th>
  <td><input type=password name=UserPassword Size=15 value=\"";
  if (isset($user_no) && $user_no > 0 ) echo "      ";
  echo "\"></td>\n</tr>\n";
?>
<tr>
	<th align=right class=rows>Full Name</th>
	<td><input Type="Text" Name="UserFullName" Size="24" Value="<?php echo "$usr->fullname"; ?>"></td>
</tr>
<tr>
	<th align=right class=rows>Email</th>
	<td><input Type="Text" Name="UserEmail" Size="50" Value="<?php echo "$usr->email"; ?>"></td>
</tr>
<?php
  echo "<tr>
  <th align=right class=rows>Phone</th>
  <td><input type=text name=UserPhone Size=24 value=\"$usr->phone\"></td>
</tr>\n";

  echo "<tr>
  <th align=right class=rows>Mobile</th>
  <td><input type=text name=UserMobile Size=24 value=\"$usr->mobile\"></td>
</tr>\n";


  echo "<tr>
  <th align=right class=rows>EMail Changes</th>
  <td>
    <table border=0 cellspacing=0 cellpadding=3><tr>
      <td>
        <label><input type=radio name=\"UserNotifications\"" . ( !isset($usr->mail_style) || trim($usr->mail_style) == "" ? " CHECKED" : "" ) . " value=\"A\"> Always</label>
        <label><input type=radio name=\"UserNotifications\"" . ( "$usr->mail_style" == "S" ? " CHECKED" : "" ) . " value=\"S\"> Sometimes</label>
        <label><input type=radio name=\"UserNotifications\"" . ( "$usr->mail_style" == "O" ? " CHECKED" : "" ) . " value=\"O\"> Occasionally</label>
        <label><input type=radio name=\"UserNotifications\"" . ( "$usr->mail_style" == "N" ? " CHECKED" : "" ) . " value=\"N\"> Never</label>
      </td>
    </tr></table>
  </td>
</tr>\n";

  if ( is_object( $usr->settings ) ) $UserFontsize = $usr->settings->get('fontsize');
  if ( "$UserFontsize" == "" ) $UserFontsize = "10";
  echo "<tr>
  <th align=right class=rows>Base Font Size</th>
  <td>
    <table border=0 cellspacing=0 cellpadding=3><tr>
      <td>\n";
  for ( $pxs = 7; $pxs <= 21; $pxs += 2 ) {
    echo "<label><input type=radio name=\"UserFontsize\"" . ( "$UserFontsize" == "$pxs" ? " CHECKED" : "" ) . " value=\"$pxs\"> $pxs</label>\n";
  }
  echo "      </td>
    </tr></table>
  </td>
</tr>\n";


  if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] || $roles['wrms']['Manage'] ) {
    echo "\n<tr>\n<th align=right class=rows>Status</th>";
    echo "<td VALIGN=TOP>\n<table border=0 cellspacing=0 cellpadding=3><tr>\n";
    echo "<td>";
    echo "<label><input type=radio name=\"UserStatus\"" . ( !isset($usr->status) || $usr->status <> "I" ? " CHECKED" : "" ) . " value=\"A\"> Active</label>\n";
    echo "<label><input type=radio name=\"UserStatus\"" . ( "$usr->status" == "I" ? " CHECKED" : "" ) . " value=\"I\"> Inactive</label>\n";
    echo "</td>\n</tr></table></td></tr>\n";
  }

  if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] ) {
    $org_code_list = get_organisation_list( "$usr->org_code" );
    echo "<tr>\n";
    echo "<th align=right class=rows>Organisation</th>\n";
    echo "<td><select class=sml name=UserOrganisation>$org_code_list</select>\n";
    echo "</tr>\n";
  }

  if ( "$user_no" > 0 ) {
    echo "<tr>\n<th align=right class=rows>Date Joined&nbsp;</th>";
    echo "<td VALIGN=TOP>";
    echo substr("$usr->joined", 0, 16);
    echo " &nbsp; &nbsp; &nbsp; &nbsp; Last Updated&nbsp; ";
    echo substr("$usr->last_update", 0, 16);
    echo "</td></tr>";
  }

  if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] || $roles['wrms']['Manage'] ) {
    // This displays checkboxes to select the users special roles.
    echo "\n<tr>\n<th align=right class=rows>User Roles</th>";
    echo "<td valign=top>\n<table border=0 cellspacing=0 cellpadding=3><tr>\n";
    for ( $i=0, $j=0; $i <pg_NumRows($grp_res); $i++) {
      $grp = pg_Fetch_Object( $grp_res, $i );
      if ( "$grp->group_name" == "Admin" && !$roles[wrms][Admin] ) continue;
      if ( "$grp->group_name" == "Support" && !($roles[wrms][Admin] || $roles[wrms][Support]) ) continue;
      if ( "$grp->group_name" == "Manage" && !($roles[wrms][Admin] || $roles[wrms][Support] || $roles[wrms][Manage]) ) continue;
      if ( $j > 0 && ($j % 3) == 0 ) echo "</tr><tr>";
      echo "<td><input type=checkbox name=\"NewUserRole[$grp->module_name][$grp->group_name]\"";
      if ( isset($UserRole) && is_array($UserRole) && $UserRole[$grp->module_name][$grp->group_name] ) echo " CHECKED";
      else if ( !isset($UserRole) && ("$grp->group_name" == "Request") ) echo " CHECKED";
      echo "> " . ucfirst($grp->module_name) . " $grp->group_name\n";
      echo " &nbsp; </td>\n";
      $j++;
    }
    echo "</select></td></tr></table></td></tr>\n";
  }


  $submitline = "$tbldef>\n<tr><td align=center><input type=\"submit\" class=submit name=\"submit\" VALUE=\"";
  if ( isset($user_no) && $user_no > 0 )
    $submitline .= " Apply Changes ";
  else
    $submitline .= " Add User ";
  $submitline .= "\"></b></td>\n</tr></table>";

  echo "</table>$submitline\n";
  echo "$tbldef><TR><TD CLASS=sml COLSPAN=3>&nbsp;</TD></TR><TR>$hdcell";

  echo "<td class=h3 colspan=2 align=right>System Access</td></tr>\n";

  // This displays all those checkboxes to select the systems the user can access.
  for ( $i=0; $i < pg_NumRows($sys_res); $i++) {
    $sys = pg_Fetch_Object( $sys_res, $i );
    echo "<tr class=row" . ($i % 2) . ">";
    echo "$hdcell\n";
    echo "<td nowrap>$sys->system_desc</td>\n";
    echo "<td nowrap>\n";
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

      echo "</select></td>\n";
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

  echo "</table>\n$submitline\n</form>\n";

include("inc/footers.php");
?>