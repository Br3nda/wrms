<?php
  include("inc/always.php");
  include("inc/options.php");

  $title = "$system_name User Search";
  include("inc/starthead.php");
  include("inc/styledef.php");
  include("inc/bodydef.php");
  include("inc/menuhead.php");

  if ( ! ($roles['wrms']['Admin'] || $roles[wrms]['Support']  || $roles[wrms]['Manage']) || "$error_msg$error_qry" != "" ) {
    include( "inc/error.php" );
  }
  else {
    echo "<p class=helptext>Use this form to search for users and to maintain their access rights.</p>\n";
    echo "<form Action=\"$base_url/usrsearch.php\" Method=\"POST\">\n";
?>
<table align=center><tr valign=middle>
<td><b>Name </b><input TYPE="Text" Size="20" Name="search_for" Value="<?php echo "$search_for"; ?>"></td>
<td><b>Type </b><SELECT NAME="user_type">
<Option Value="."> All </Option>
<Option Value="S"<?php if ( "$user_type" == "S" ) echo " checked"; ?>> System Support </OPTION>
<Option Value="C"<?php if ( "$user_type" == "S" ) echo " checked"; ?>> Client Coordinator </OPTION>
<Option Value="U"<?php if ( "$user_type" == "S" ) echo " checked"; ?>> System User </OPTION>
</SELECT></td>
<td><input TYPE="Image" src="images/in-go.gif" alt="go" WIDTH="44" BORDER="0" HEIGHT="26" name="submit"></td>
</tr></table>
</form>  

<?php
  if ( "$search_for$org_code$system_code " != "" && ( $roles['wrms']['Manage'] || $roles['wrms']['Admin'] || $roles['wrms']['Support'] ) ) {

    $query = "SELECT DISTINCT ON user_no * FROM usr, organisation";
//    $query .= ", session";
    if ( isset( $system_code ) ) $query .= ", system_usr, lookup_code";
    $query .= " WHERE usr.org_code=organisation.org_code ";
//    $query .= " AND usr.user_no=session.user_no ";
    if ( "$search_for" != "" ) {
      $query .= " AND (fullname ~* '$search_for' ";
      $query .= " OR username ~* '$search_for' ";
      $query .= " OR email ~* '$search_for' )";
    }
    if ( $roles[wrms][Manage] && ! ($roles[wrms][Admin] || $roles[wrms][Support]) )
      $query .= " AND usr.org_code='$session->org_code' ";
    else if ( isset( $org_code ) ) 
      $query .= " AND usr.org_code='$org_code' ";

    if ( isset( $user_type ) ) {
      $query .= " AND usr.status~'^$user_type'";
    }

    if ( isset( $system_code ) ) {
      $query .= " AND system_usr.system_code='$system_code'";
      $query .= " AND system_usr.user_no=usr.user_no";
      $query .= " AND lookup_code.source_table='system_usr' AND lookup_code.source_field='role' AND lookup_code.lookup_code=system_usr.role ";
    }

    $query .= " ORDER BY username";
//    $query .= ", session_end DESC ";
//    $query .= " LIMIT 100 ";
    $result = pg_Exec( $wrms_db, $query );
    if ( ! $result ) {
      $error_loc = "usrsearch.php";
      $error_qry = "$query";
      include( "inc/error.php" );
    }
    else {
      // Build table of usrs found
      echo "<p>" . pg_NumRows($result) . " users found</p>"; // <p>$query</p>";
      echo "<table border=\"0\" align=center>\n<tr>\n";
      echo "<th class=cols>User&nbsp;ID</th><th class=cols>Full Name</th>\n";
      echo "<th class=cols>Organisation</th><th class=cols>Email</th>\n";
      if ( isset( $system_code ) )
        echo "<th class=cols>User Role</th>\n";
      echo "<th class=cols>Updated</th>\n";
      if ( ! isset( $system_code ) )
        echo "<th class=cols>Accessed</th>\n";
      echo "<th class=cols>Actions</th>\n";
      echo "</tr>\n";

      for ( $i=0; $i < pg_NumRows($result); $i++ ) {
        $thisusr = pg_Fetch_Object( $result, $i );

        if(floor($i/2)-($i/2)==0) echo "<tr bgcolor=$colors[6]>\n";
        else echo "<tr bgcolor=$colors[7]>\n";

        echo "<td class=sml><a href=\"index.php?M=LC&E=$thisusr->username&L=";
        echo md5(strtolower($thisusr->password));
        echo "\">$thisusr->username</a></td>\n";
        echo "<td class=sml><a href=\"usr.php?user_no=$thisusr->user_no\">$thisusr->fullname</a></td>\n";
        echo "<td class=sml><a href=\"form.php?form=organisation&org_code=$thisusr->org_code\">$thisusr->org_name</a></td>\n";
        echo "<td class=sml><a href=\"mailto:$thisusr->email\">$thisusr->email</a>&nbsp;</td>\n";
        if ( isset( $system_code ) )
          echo "<td class=sml>$thisusr->lookup_desc ($thisusr->role)&nbsp;</td>\n";
        echo "<td class=sml>" . substr($thisusr->last_update, 4, 7) . substr($thisusr->last_update, 20, 4) . " " . substr($thisusr->last_update, 11, 5) . "&nbsp;</td>\n";
        if ( ! isset( $system_code ) )
          echo "<td class=sml>" . substr($thisusr->last_accessed, 4, 7) . substr($thisusr->last_accessed, 20, 4) . " " . substr($thisusr->last_accessed, 11, 5) . "&nbsp;</td>\n";

        echo "<td class=menu><a class=r href=\"requestlist.php?user_no=$thisusr->user_no\">Requested</a> &nbsp; \n";
        echo "<a class=r href=\"requestlist.php?allocated_to=$thisusr->user_no\">Allocated</a> &nbsp; \n";
        echo "<a class=r href=\"requestlist.php?interested=$thisusr->user_no\">Interested</a>\n";
        if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] )
          echo " &nbsp; <a class=r href=\"form.php?user_no=$thisusr->user_no&form=timelist\">Work</a>\n";

        echo "</td></tr>\n";
      }
      echo "</table>\n";
    }
  }

} /* The end of the else ... clause waaay up there! */ ?>
</body> 
</html>


