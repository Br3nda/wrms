<?php
  include("inc/always.php");
  include("inc/options.php");

  if ( ! $roles[wrms][Admin] ) $user_no = $session->user_no;
  if ( "$submit" == "Add User" || "$submit" == "Update User" ) {
    include("inc/validateusr.php");
    include("inc/writeusr.php");
  }
  else if ( "$submit" == "Delete This User" ) {
    include("inc/deleteusr.php");
  }

  include("inc/getusr.php");
  $title = "$system_name User Manager";
  include("inc/starthead.php");
  include("inc/styledef.php");
  include("inc/bodydef.php");
  include("inc/menuhead.php");

//  echo "<p>Submit: $submit<br>M: $M<BR>User No: $user_no</p>";
  if ( "$submit" == "Add User" || "$submit" == "Update User" || "$submit" == "Delete This User" ) {
    echo "$because";
    exit;
  }

  // Pre-build the list of systems
  if ( "$error_qry" == "" ) {
    $query = "SELECT * FROM lookup_code ";
    $query .= " WHERE source_table='user' ";
    $query .= " AND source_field='system_code' ";
    $query .= " AND lookup_code!=''";
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

  if ( "$error_qry" != "" ) {
    include( "inc/error.php" );
  }
  else {
    echo "<table border=0 cellspacing=0 cellpadding=0 width=90% height=30><tr valign=bottom><td>\n";
    echo "<h3 style=\"padding: 0pt; margin: 0pt; \">User Profile";
    if (isset($user_no) && $user_no > 0 ) echo " for $usr->fullname";
    echo "</h3></td>\n";
    if ( $roles[wrms][Admin] ) {
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
<P><FONT COLOR=DARKBLUE><B>Enter and Update User Details below</b></font></p>
<table width=90% border="0" cellpadding="4" cellspacing="0" bgcolor=<?php echo $colors[6]; ?> align=center>
	<TR> 
		<TD align=right><font Size=2>Login ID</font></TD>
		<TD><font Size=2><?php echo "$user_no"; ?>&nbsp;</font></td>
	</tr>	 
	<tr> 
		<td align=right><font Size="2">User Name&nbsp;</font></td> 
		<td><font Size="2"><?php
if ( $roles[wrms][Admin] ) echo "<input Type=\"Text\" Name=\"UserName\" Size=\"15\" Value=\"";
echo "$usr->username";
if ( $roles[wrms][Admin] ) echo "\">";
echo "</font></td>"; ?>
	</tr> 
	<tr> 
		<td align=right><font Size="2">Password&nbsp;</font></td> 
		<td><font Size="2"><input Type=password Name="UserPassword" Size="15" Value="<?php
if (isset($user_no) && $user_no > 0 ) echo "      ";
?>"></font></td> 
	</tr> 
	<tr> 
		<td align=right valign=top><font size=2 style="margin-top: 10px; ">Email&nbsp;</font></td> 
		<td><font size=2><input Type="Text" Name="UserEmail" Size="50" Value="<?php echo "$usr->email"; ?>"></font></td> 
	</tr> 
	<tr> 
		<td align=right><font Size="2">Full Name&nbsp;</font></td> 
		<td><font Size="2"><input Type="Text" Name="UserFullName" Size="24" Value="<?php echo "$usr->fullname"; ?>"></font></td> 
	</tr> 
<?php if ( $roles[wrms][Admin] ) { ?>
	<tr> 
		<td align=right><font Size="2">User Status</font></TD> 
		<td><font Size="2">
		<input Type="Radio" Name="UserStatus" Value="N"<?php if ("$usr->status" == "S" ) echo " CHECKED"; ?>> System Support &nbsp; 
		<input Type="Radio" Name="UserStatus" Value="R"<?php if ("$usr->status" == "C" ) echo " CHECKED"; ?>> Client Coordinator &nbsp;
		<input Type="Radio" Name="UserStatus" Value="C"<?php if ("$usr->status" == "U" ) echo " CHECKED"; ?>> System User &nbsp;</font></td> 
	</tr>
<?php
  }  // end of   'if Admin... '
  if ( "$user_no" > 0 ) {
    echo "<tr><td align=right><font Size=2>Date Joined&nbsp;</td>";
    echo "<td VALIGN=TOP><font Size=2>";
    echo substr("$usr->joined", 0, 16);
    echo " &nbsp; &nbsp; &nbsp; &nbsp; Last Updated&nbsp; ";
    echo substr("$usr->last_update", 0, 16);
    echo "</td></tr>";
  }

  if ( $roles[wrms][Admin] ) {
    // This displays checkboxes to select the users special roles.
    echo "\n<tr><td align=right valign=top><font size=2 style=\"margin-top: 10px; \">User Roles&nbsp;</td>";
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
?>
</table> 

<P>&nbsp;<BR><FONT COLOR=DARKBLUE><B>Choose the Systems and Restrictions</b></font>

<?php
    // This displays all those checkboxes to select the wires the story should go out on.
    echo "<table border=0 cellpadding=1 cellspacing=0 width=100%>\n";
    for ( $i=0; $i <pg_NumRows($sys_res); $i++) {
      $sys = pg_Fetch_Object( $sys_res, $i );
      if ( $i > 0 && ($i % 2) == 0 ) echo "</tr><tr>";
      else if ( $i > 0 ) echo "<td bgcolor=$colors[5]>&nbsp;</td>";
      echo "<td NOWRAP><font size=1>$sys->lookup_desc</td>\n";
      echo "<td NOWRAP><font size=1>\n";
      if ( isset($UserCat) && is_array($UserCat) )
        $code = $UserCat[$sys->lookup_code];
      else
        $code = "";
      echo "<select name=\"UserCat[$sys->lookup_code]\">\n";
      echo "<option value=\"\"";
      if ( "$code" == "" ) echo " SELECTED";
      echo ">--- no access ---</option>\n";
      echo "<option value=A";
      if ( "$code" == 'A') echo " SELECTED";
      echo ">Administration</option>";
      echo "<option value=M";
      if ( "$code" == 'C') echo " SELECTED";
      echo ">Client Coordinator</option>";
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
  }  // If role is wrms/Admin
  echo "</table>\n";
?>
<P><INPUT Type=Submit Value="<?php if ( isset($user_no) && $user_no > 0 ) echo "Update"; else echo "Add"; ?> User" name=submit></P>

<?php  } /* The end of the else ... clause waaay up there! */ ?>
</body> 
</html>


