<?php
  include("inc/always.php");
  include("inc/options.php");

  $title = "$system_name User Search";
  include("inc/starthead.php");
  include("inc/styledef.php");
  include("inc/bodydef.php");
  include("inc/menuhead.php");

  if ( ! $roles[wrms][Admin] || "$error_msg$error_qry" != "" ) {
    include( "inc/error.php" );
  }
  else {
    echo "<p class=helptext>Use this form to search for users and to maintain their access rights.</p>\n";
    echo "<form Action=\"$base_url/usrsearch.php\" Method=\"POST\">\n";
?>
<table align=center><tr valign=middle>
<td><b>Name </b><input TYPE="Text" Size="20" Name="search_for" Value="<?php echo "$search_for"; ?>"></td>
<td><b>Status</b><SELECT NAME="Status"><Option Value=".">All</Option><Option Value="N"> National &nbsp;</OPTION><Option Value="R"> Regional &nbsp;</OPTION><Option Value="C"> System &nbsp;</OPTION></SELECT></td>
<td><input TYPE="Image" src="images/in-go.gif" alt="go" WIDTH="44" BORDER="0" HEIGHT="26" name="submit"></td>
</tr></table>
</form>  

<?php
  if ( "$search_for$org_code" != "" ) {
    echo "<table border=\"0\" align=center><tr>\n";
    echo "<th>User&nbsp;ID</th><th>Full Name</th>";
    echo "<th>Organisation</th><th>Email</th><th>Updated</th></tr>";

    $query = "SELECT * FROM usr, organisation WHERE usr.org_code=organisation.org_code ";
    if ( "$search_for" != "" ) {
      $query .= " AND (fullname ~* '$search_for' ";
      $query .= " OR username ~* '$search_for' ";
      $query .= " OR email ~* '$search_for' )";
    }
    if ( isset( $org_code ) ) 
      $query .= " AND usr.org_code='$org_code' ";
    $query .= " ORDER BY username ";
//    $query .= " LIMIT 100 ";
    $result = pg_Exec( $wrms_db, $query );
    if ( ! $result ) {
      $error_loc = "usrsearch.php";
      $error_qry = "$query";
    }
    else {
      // Build table of usrs found
      for ( $i=0; $i < pg_NumRows($result); $i++ ) {
        $thisusr = pg_Fetch_Object( $result, $i );

        $query = "SELECT lookup_desc, role FROM lookup_code, system_usr WHERE source_table='user' ";
        $query .= " AND source_field='system_code'";
        $query .= " AND lookup_code=system_usr.system_code ";
        $query .= " AND system_usr.user_no=$thisusr->user_no ";
        $query .= " AND (role='C' OR role='E' OR role='S') ";
        $query .= " ORDER BY role ";
        $roles = pg_Exec( $wrms_db, $query );

        if(floor($i/2)-($i/2)==0) echo "<tr bgcolor=$colors[6]>";
        else echo "<tr bgcolor=$colors[7]>";

        echo "<td class=sml><a href=\"index.php?M=LC&E=$thisusr->username&L=$thisusr->password\">$thisusr->username</a></td>\n";
        echo "<td class=sml><a href=\"usr.php?user_no=$thisusr->user_no\">$thisusr->fullname</a></td>\n";
        echo "<td class=sml><a href=\"org.php?org_code=$thisusr->org_code\">$thisusr->org_name</a></td>\n";
        echo "<td class=sml><a href=\"mailto:$thisusr->email\">$thisusr->email</a>&nbsp;</td>\n";
        echo "<td class=sml>" . substr($thisusr->last_update, 4, 7) . substr($thisusr->last_update, 20, 4) . " " . substr($thisusr->last_update, 11, 5) . "&nbsp;</td>\n";

        echo "</tr>\n";
      }
    }
    echo "</table>\n";
  }

} /* The end of the else ... clause waaay up there! */ ?>
</body> 
</html>


