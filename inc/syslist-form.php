<?php
  include("code-list.php");

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
  if ( "$search_for$org_code " != ""  && is_member_of('Admin','Support', 'Manage', 'OrgMgr') ) {
    $maxresults = ( isset($maxresults) && intval($maxresults) > 0 ? intval($maxresults) : 500 );
    $query = "SELECT work_system.* ";
    if ( "$org_code" <> "" ) $query .= ", org_code ";
    $query .= "FROM work_system ";
    if ( ! is_member_of('Admin', 'Support') ) {
      $query .= "JOIN system_usr ON (work_system.system_id = system_usr.system_id AND system_usr.user_no = $session->user_no) ";
    }
    if ( "$org_code" <> "" ) $query .= ", org_system ";

    if ( "I" == "$status" )
      $query .= "WHERE (NOT active OR active IS NULL) ";
    else
      $query .= "WHERE active ";

    if ( "$search_for" <> "" ) {
      $query .= "AND (system_code ~* '$search_for' ";
      $query .= " OR system_desc ~* '$search_for' ) ";
    }
    if ( isset($org_code) && $org_code > 0 ) {
      $query .= "AND work_system.system_id=org_system.system_id ";
      $query .= "AND org_system.org_code='$org_code' ";
    }
    else {
      $query .= "AND NOT organisation_specific ";
    }
    $query .= " ORDER BY lower(work_system.system_desc) ";
    $query .= " LIMIT $maxresults ";
    $result = awm_pgexec( $dbconn, $query, "syslist", false, 7 );
    if ( ! $result ) {
      $error_loc = "syslist-form.php";
      $error_qry = "$query";
      include("error.php");
    }
    else {
      if ( $result && pg_NumRows($result) > 0 ) {
        echo "\n<small>";
        echo pg_NumRows($result) . " requests found";
        if ( pg_NumRows($result) == $maxresults ) echo " (limit reached)";
        if ( isset($saved_query) && $saved_query != "" ) echo " for <b>$saved_query</b>";
        echo "</small>";
      }
      echo "<table border=\"0\" align=center><tr>\n";
      echo "<th class=cols>ID</th>";
      echo "<th class=cols>Code</th>";
      echo "<th class=cols align=left>&nbsp;System Name</th>";
      echo "<th class=cols align=center>Actions</th></tr>";

      // Build table of systems found
      for ( $i=0; $i < pg_NumRows($result); $i++ ) {
        $thissystem = pg_Fetch_Object( $result, $i );

        printf("<tr class=row%1d>", $i % 2);

        echo "<td class=sml>&nbsp;<a href=\"system.php?system_id=".urlencode($thissystem->system_id)."\">$thissystem->system_id</a>&nbsp;</td>\n";
        echo "<td class=sml>&nbsp;<a href=\"system.php?system_id=".urlencode($thissystem->system_id)."\">$thissystem->system_code</a>&nbsp;</td>\n";
        echo "<td class=sml>&nbsp;<a href=\"system.php?system_id=".urlencode($thissystem->system_id)."\">$thissystem->system_desc";
        if ( "$thissystem->system_desc" == "" ) echo "-- no description --";
        echo "</a>&nbsp;</td>\n";
        echo "<td class=sml><a class=submit href=\"requestlist.php?system_id=".urlencode($thissystem->system_id)."\">Requests</a>\n";
        echo "<a class=submit href=\"usrsearch.php?system_id=$thissystem->system_id\">Users</a>\n";
        if ( is_member_of('Admin','Support') ) {
          echo "<a class=submit href=\"form.php?system_id=".urlencode($thissystem->system_id)."&form=timelist\">Work</a>\n";
          echo "<a class=submit href=\"form.php?form=orglist&system_id=".urlencode($thissystem->system_id)."\">Organisations</a>\n";
          echo "<a class=submit href=\"system_users.php?system_id=".urlencode($thissystem->system_id)."\">Roles</a>\n";
        }

        echo "</td></tr>\n";
      }
      echo "<tr><td class=mand colspan=\"4\" align=center><a class=submit href=\"system.php\">Add A New System</a>";
      echo "</table>\n";
    }
  }
?>
</FORM>

