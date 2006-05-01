<?php
  include("system-list.php");
  $system_ids = get_system_list("ECRS", "$request->system_id");
?>
<FORM METHOD=POST ACTION="<?php echo "$SCRIPT_NAME?form=$form"; ?>">
<table align=center><tr valign=middle>
<td><b>Name</b><font size=2><input TYPE="Text" Size="20" Name="search_for" Value="<?php echo "$search_for"; ?>"></font></td>
<td class=smb>&nbsp;<b><label id=status>Inactive:</label></b></td><td><input id=status class=sml type="checkbox" name=status value=I<?php if ("$status" == "I" ) echo" checked";?>></td>
<td><input type="submit" alt="go" name="submit" value="GO>>" class="submit"></td>
</tr></table>
</form>

<?php
  if ( !is_member_of('Admin','Support') )  $org_code = $session->org_code;
  if ( "$search_for$system_id " != "" ) {
    $query = "SELECT * FROM organisation ";
    if ( "$system_id$org_code" <> "" ) $query .= ", org_system ";
    $query .= sprintf("WHERE %s active ", ("I" == "$status" ? "NOT" : "") );
    if ( "$search_for" <> "" ) {
      $query .= " AND (org_code ~* '$search_for' ";
      $query .= " OR org_name ~* '$search_for' ) ";
    }
    if ( "$system_id$org_code" <> "" ) {
      $query .= " AND org_system.org_code = organisation.org_code ";
    }
    if ( "$system_id" <> "" ) {
      $query .= " AND org_system.system_id=".intval($system_id);
    }
    if ( "$org_code" <> "" ) {
      $query .= " AND org_system.org_code =".intval($org_code);
    }
    $query .= " ORDER BY LOWER(organisation.org_name) ";
    $query .= " LIMIT 500 ";
    $result = awm_pgexec( $dbconn, $query, "orglist-form", false, 7 );
    if ( ! $result ) {
      $error_loc = "orglist-form.php";
      $error_qry = "$query";
      include("error.php");
    }
    else {
      echo "<p><small>" . pg_NumRows($result) . " organisations found</small></p>";
      echo "<table border=\"0\" align=center width=100%><tr>\n";

      echo "<th class=cols align=left>Abbrev.</th>\n";
      echo "<th class=cols align=left>Full Name</th>";
      if ( is_member_of('Admin','Support') ) echo "<th class=cols>Debtor #</th>";
      echo "<th class=cols align=center>Actions</th></tr>";

      // Build table of organisations found
      for ( $i=0; $i < pg_NumRows($result); $i++ ) {
        $thisorganisation = pg_Fetch_Object( $result, $i );

        printf("<tr class=row%1d>", $i % 2);


        echo "<td class=sml>&nbsp;<a href=\"org.php?org_code=$thisorganisation->org_code\">$thisorganisation->abbreviation</a>&nbsp;</td>\n";
        echo "<td class=sml>&nbsp;<a href=\"org.php?org_code=$thisorganisation->org_code\">$thisorganisation->org_name";
        if ( "$thisorganisation->org_name" == "" ) echo "-- no description --";
        echo "</a>&nbsp;</td>\n";
        if ( is_member_of('Admin','Support') ) {
          printf( "<td class=sml align=right>%s &nbsp; &nbsp;</td>\n", (intval($thisorganisation->debtor_no) > 0 ? "$thisorganisation->debtor_no" : "-") );
        }
        echo "<td class=sml><a class=submit href=\"requestlist.php?org_code=$thisorganisation->org_code\">Requests</a>";
        echo "<a class=submit href=\"usrsearch.php?org_code=$thisorganisation->org_code\">Users</a>";
        echo "<a class=submit href=\"form.php?org_code=$thisorganisation->org_code&form=syslist\">Systems</a>";
        if ( is_member_of('Admin','Support') ) {
          echo "<a class=submit href=\"form.php?org_code=$thisorganisation->org_code&form=timelist&uncharged=1\">Work</a>";
          if ( is_member_of('Admin') ) {
            echo "<a class=submit href=\"new_organisation.php?id=$thisorganisation->org_code\">OrgPlus</a>";
          }
        }

        echo "</td></tr>\n";
      }
//      echo "<tr><td class=mand colspan=4 align=center><a class=submit href=\"new_organisation.php\">Add A New Organisation</a>";
      echo "</table>\n";
    }
  }
?>
</TABLE>
</FORM>

