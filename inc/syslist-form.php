<?php
  include("$base_dir/inc/code-list.php");

?>
<FORM METHOD=POST ACTION="<?php echo "$SCRIPT_NAME?form=$form"; ?>">
<table align=center><tr valign=middle>
<td><b>Name</b><input class=sml TYPE="Text" Size="20" Name="search_for" Value="<?php echo "$search_for"; ?>"></td>
<td class=smb>&nbsp;<b><label id=status>Inactive:</label></b></td><td><input id=status class=sml type="checkbox" name=status value=I<?php if ("$status" == "I" ) echo" checked";?>></td>
<td><input TYPE="submit" alt="go" class=submit value="GO>>" name="submit"></td>
</tr></table>
</form>

<?php
  if ( ! is_member_of('Admin','Support') ) $org_code = $session->org_code;
  if ( "$search_for$org_code " != ""  && is_member_of('Admin','Support', 'Manage') ) {
    $query = "SELECT DISTINCT ON (system_code) work_system.* ";
    if ( "$org_code" <> "" ) $query .= ", org_code ";
    $query .= "FROM work_system ";
    if ( "$org_code" <> "" ) $query .= ", org_system ";

    if ( "I" == "$status" )
      $query .= "WHERE (NOT active OR active IS NULL) ";
    else
      $query .= "WHERE active ";

    if ( "$search_for" <> "" ) {
      $query .= "AND (system_code ~* '$search_for' ";
      $query .= " OR system_desc ~* '$search_for' ) ";
    }
    if ( "$org_code" <> "" ) {
      $query .= "AND work_system.system_code=org_system.system_code ";
      $query .= "AND org_system.org_code='$org_code' ";
    }
    $query .= " ORDER BY work_system.system_code ";
    $query .= " LIMIT 100 ";
    $result = awm_pgexec( $wrms_db, $query, "syslist", false, 7 );
    if ( ! $result ) {
      $error_loc = "syslist-form.php";
      $error_qry = "$query";
      include("inc/error.php");
    }
    else {
      echo "<small>" . pg_NumRows($result) . " systems found";
      echo "<table border=\"0\" align=center><tr>\n";
      echo "<th class=cols>System</th>";
      echo "<th class=cols align=left>&nbsp;Full Name</th>";
      echo "<th class=cols align=center>Actions</th></tr>";

      // Build table of systems found
      for ( $i=0; $i < pg_NumRows($result); $i++ ) {
        $thissystem = pg_Fetch_Object( $result, $i );

        printf("<tr class=row%1d>", $i % 2);

        echo "<td class=sml>&nbsp;<a href=\"form.php?form=system&system_code=$thissystem->system_code\">$thissystem->system_code</a>&nbsp;</td>\n";
        echo "<td class=sml>&nbsp;<a href=\"form.php?form=system&system_code=$thissystem->system_code\">$thissystem->system_desc";
        if ( "$thissystem->system_desc" == "" ) echo "-- no description --";
        echo "</a>&nbsp;</td>\n";
        echo "<td class=sml><a class=submit href=\"requestlist.php?system_code=$thissystem->system_code\">Requests</a>\n";
        echo "<a class=submit href=\"usrsearch.php?system_code=$thissystem->system_code\">Users</a>\n";
        if ( is_member_of('Admin','Support') ) {
          echo "<a class=submit href=\"form.php?system_code=$thissystem->system_code&form=timelist\">Work</a>\n";
          echo "<a class=submit href=\"form.php?form=orglist&system_code=$thissystem->system_code\">Organisations</a>\n";
        }

        echo "</td></tr>\n";
      }
      echo "<tr><td class=mand colspan=3 align=center><a class=submit href=\"form.php?form=system\">Add A New System</a>";
      echo "</table>\n";
    }
  }
?>
</FORM>

