<?php
  include("$base_dir/inc/code-list.php");
//  $training = get_code_list( "request", "training_code" );

?>
<P class=helptext>Use this form to select systems for maintenance or review.</P>

<FORM METHOD=POST ACTION="<?php echo "$SCRIPT_NAME?form=$form"; ?>">
<table align=center><tr valign=middle>
<td><b>Name</b><input TYPE="Text" Size="20" Name="search_for" Value="<?php echo "$search_for"; ?>"></td>
<td><input TYPE="Image" src="images/in-go.gif" alt="go" WIDTH="44" BORDER="0" HEIGHT="26" name="submit"></td>
</tr></table>
</form>  

<?php
  if ( !($roles['wrms']['Admin'] || $roles['wrms']['Support']) ) $org_code = $session->org_code;
  if ( "$search_for$org_code " != ""  && ( $roles['wrms']['Manage'] || $roles['wrms']['Admin'] || $roles['wrms']['Support'] ) ) {
    $query = "SELECT DISTINCT ON (system_code) work_system.* ";
    if ( "$org_code" <> "" ) $query .= ", org_code ";
    $query .= "FROM work_system ";
    if ( "$org_code" <> "" ) $query .= ", org_system ";
    if ( "$search_for$org_code" != "" ) $query .= "WHERE ";
    if ( "$search_for" <> "" ) {
      $query .= " (system_code ~* '$search_for' ";
      $query .= " OR system_desc ~* '$search_for' ) ";
      if ( "$org_code" <> "" ) $query .= "AND ";
    }
    if ( "$org_code" <> "" ) {
      $query .= " work_system.system_code=org_system.system_code ";
      $query .= "AND org_system.org_code='$org_code' ";
    }
    $query .= " ORDER BY work_system.system_code ";
    $query .= " LIMIT 100 ";
    $result = awm_pgexec( $wrms_db, $query );
    if ( ! $result ) {
      $error_loc = "syslist-form.php";
      $error_qry = "$query";
      include("inc/error.php");
    }
    else {
      echo "<p>" . pg_NumRows($result) . " systems found</p>"; // <p>$query</p>";
      echo "<table border=\"0\" align=center><tr>\n";
      echo "<th class=cols>System</th>";
      echo "<th class=cols align=left>&nbsp;Full Name</th>";
      echo "<th class=cols align=center>Actions</th></tr>";

      // Build table of systems found
      for ( $i=0; $i < pg_NumRows($result); $i++ ) {
        $thissystem = pg_Fetch_Object( $result, $i );

        if(floor($i/2)-($i/2)==0) echo "<tr bgcolor=$colors[6]>";
        else echo "<tr bgcolor=$colors[7]>";

        echo "<td align=center>&nbsp;<a href=\"form.php?form=system&system_code=$thissystem->system_code\">$thissystem->system_code</a>&nbsp;</td>\n";
        echo "<td>&nbsp;<a href=\"form.php?form=system&system_code=$thissystem->system_code\">$thissystem->system_desc";
        if ( "$thissystem->system_desc" == "" ) echo "-- no description --";
        echo "</a>&nbsp;</td>\n";
        echo "<td class=menu><a class=r href=\"requestlist.php?system_code=$thissystem->system_code\">Requests</a> &nbsp; \n";
        echo "<a class=r href=\"form.php?form=orglist&system_code=$thissystem->system_code\">Organisations</a> &nbsp; \n";
        echo "<a class=r href=\"usrsearch.php?system_code=$thissystem->system_code\">Users</a>\n";
        if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] )
          echo " &nbsp; <a class=r href=\"form.php?system_code=$thissystem->system_code&form=timelist\">Work</a>\n";

        echo "</td></tr>\n";
      }
      echo "<tr><td class=mand colspan=3 align=center><a class=r href=\"form.php?form=system\">Add A New System</a>";
      echo "</table>\n";
    }
  }
?>
</FORM>

