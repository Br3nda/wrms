<?php
  include("$base_dir/inc/code-list.php");
  $system_codes = get_code_list( "request", "system_code" );
  $courses = get_code_list( "request", "course_code" );
  $training = get_code_list( "request", "training_code" );

  include("$base_dir/inc/system-list.php");
  $system_codes = get_system_list("ECRS", "$request->system_code");
?>
<P class=helptext>Use this form to select organisations for maintenance or review.</P>

<FORM METHOD=POST ACTION="<?php echo "$SCRIPT_NAME?form=$form"; ?>">
<table><tr valign=middle>
<td><b>Name</b><input TYPE="Text" Size="20" Name="search_for" Value="<?php echo "$search_for"; ?>"></td>
<td><input TYPE="Image" src="images/in-go.gif" alt="go" WIDTH="44" BORDER="0" HEIGHT="26" name="submit"></td>
</tr></table>
</form>  

<?php
  if ( "$search_for" != "" ) {
    $query = "SELECT * FROM organisation ";
    $query .= " WHERE active ";
    $query .= " AND (org_code ~* '$search_for' ";
    $query .= " OR org_name ~* '$search_for' ) ";
    $query .= " ORDER BY org_code ";
    $query .= " LIMIT 100 ";
    $result = pg_Exec( $wrms_db, $query );
    if ( ! $result ) {
      $error_loc = "orglist-form.php";
      $error_qry = "$query";
      include("inc/error.php");
    }
    else {
      echo "<p>" . pg_NumRows($result) . " organisations found</p>"; // <p>$query</p>";
      echo "<table border=\"0\" align=system_code><tr>\n";
      echo "<th>Org Code</th><th>Debtor #</th>";
      echo "<th>Full Name</th><th>Actions</th></tr>";

      // Build table of organisations found
      for ( $i=0; $i < pg_NumRows($result); $i++ ) {
        $thisorganisation = pg_Fetch_Object( $result, $i );

        if(floor($i/2)-($i/2)==0) echo "<tr bgcolor=$colors[6]>";
        else echo "<tr bgcolor=$colors[7]>";

        echo "<td class=sml align=center><a href=\"form.php?form=organisation&org_code=$thisorganisation->org_code\">$thisorganisation->org_code</a></td>\n";
        echo "<td class=sml align=right>$thisorganisation->debtor_no</td>\n";
        echo "<td class=sml><a href=\"form.php?form=organisation&org_code=$thisorganisation->org_code\">$thisorganisation->org_name";
        if ( "$thisorganisation->org_name" == "" ) echo "-- no description --";
        echo "</a></td>\n";
        echo "<td class=sml><a href=\"requestlist.php?org_code=$thisorganisation->org_code\">Requests</a>&nbsp;\n";
        echo "<a href=\"usrsearch.php?org_code=$thisorganisation->org_code\">Users</a>&nbsp;\n";
        echo "<a href=\"form.php?org_code=$thisorganisation->org_code&form=syslist\">Systems</a></td>\n";

        echo "</tr>\n";
      }
      echo "</table>\n";
    }
  }
?>
</TABLE>
</FORM>

