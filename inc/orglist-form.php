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
<table align=center><tr valign=middle>
<td><b>Name</b><font size=2><input TYPE="Text" Size="20" Name="search_for" Value="<?php echo "$search_for"; ?>"></font></td>
<td><input TYPE="Image" src="images/in-go.gif" alt="go" WIDTH="44" BORDER="0" HEIGHT="26" name="submit"></td>
</tr></table>
</form>  

<?php
  if ( !($roles['wrms']['Admin'] || $roles['wrms']['Support']) ) $org_code = $session->org_code;
  if ( "$search_for$system_code " != "" ) {
    $query = "SELECT * FROM organisation ";
    if ( "$system_code" <> "" ) $query .= ", org_system ";
    if ( "$search_for$system_code" != "" ) $query .= " WHERE active ";
    if ( "$search_for" <> "" ) {
      $query .= " AND (org_code ~* '$search_for' ";
      $query .= " OR org_name ~* '$search_for' ) ";
    }
    if ( "$system_code" <> "" ) {
      $query .= " AND org_system.org_code = organisation.org_code ";
      $query .= " AND org_system.system_code='$system_code' ";
    }
    if ( "$org_code" <> "" ) {
      $query .= " AND org_system.org_code = $org_code ";
    }
    $query .= " ORDER BY organisation.org_name ";
    $query .= " LIMIT 100 ";
    $result = awm_pgexec( $wrms_db, $query );
    if ( ! $result ) {
      $error_loc = "orglist-form.php";
      $error_qry = "$query";
      include("inc/error.php");
    }
    else {
      echo "<p>" . pg_NumRows($result) . " organisations found</p>"; // <p>$query</p>";
      echo "<table border=\"0\" align=center><tr>\n";
      echo "<th class=cols>Org Code</th><th class=cols>Debtor #</th>";
      echo "<th class=cols align=left>Full Name</th>";
      echo "<th class=cols align=center>Actions</th></tr>";

      // Build table of organisations found
      for ( $i=0; $i < pg_NumRows($result); $i++ ) {
        $thisorganisation = pg_Fetch_Object( $result, $i );

        if(floor($i/2)-($i/2)==0) echo "<tr bgcolor=$colors[6]>";
        else echo "<tr bgcolor=$colors[7]>";

        echo "<td align=center>&nbsp;<a href=\"form.php?form=organisation&org_code=$thisorganisation->org_code\">$thisorganisation->org_code</a>&nbsp;</td>\n";
        echo "<td align=right>&nbsp;$thisorganisation->debtor_no</td>\n";
        echo "<td>&nbsp;<a href=\"form.php?form=organisation&org_code=$thisorganisation->org_code\">$thisorganisation->org_name";
        if ( "$thisorganisation->org_name" == "" ) echo "-- no description --";
        echo "</a>&nbsp;</td>\n";
        echo "<td class=sml>&nbsp;<a class=r href=\"requestlist.php?org_code=$thisorganisation->org_code\">Requests</a> &nbsp; \n";
        echo "<a class=r href=\"usrsearch.php?org_code=$thisorganisation->org_code\">Users</a> &nbsp; \n";
        echo "<a class=r href=\"form.php?org_code=$thisorganisation->org_code&form=syslist\">Systems</a>\n";
        if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] )
          echo " &nbsp; <a class=r href=\"form.php?org_code=$thisorganisation->org_code&form=timelist&uncharged=1\">Work</a>\n";

        echo "&nbsp;</td></tr>\n";
      }
      echo "<tr><td class=mand colspan=4 align=center><a class=r href=\"form.php?form=organisation&org_code=new\">Add A New Organisation</a>";
      echo "</table>\n";
    }
  }
?>
</TABLE>
</FORM>

