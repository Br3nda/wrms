<?php
  include("inc/always.php");
  include("inc/options.php");

  $title = "$system_name User Search";
  include("inc/headers.php");

  if ( ! ($roles['wrms']['Admin'] || $roles[wrms]['Support']  || $roles[wrms]['Manage']) || "$error_msg$error_qry" != "" ) {
    include( "inc/error.php" );
  }
  else {
    echo "<p class=helptext>Use this form to search for users and to maintain their access rights.</p>\n";
    echo "<form Action=\"$base_url/usrsearch.php\" Method=\"POST\">\n";

    echo "<table align=center cellspacing=0 cellpadding=2><tr valign=middle>\n";
    echo "<td><b>Name:</b><input type=text size=\"20\" name=search_for value=\"$search_for\"></td>\n";

    if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] ) {
      include( "inc/organisation-list.php" );
      $orglist = "<option value=\"\">--- All Organisations ---</option>\n" . get_organisation_list( "$org_code" );
      echo "<td><b>Org:</b><select name=\"org_code\">\n$orglist</select></td>\n";
      echo "<td>&nbsp;<label><b>Inactive:</b><input type=\"checkbox\" name=status value=I" . ("$status" == "I" ? " checked" : "") . "></label></td>\n";
    }
    else if ( $roles['wrms']['Manage'] ) {
      include( "inc/system-list.php" );
      $syslist = get_system_list( "VOECS", "$system_code" );
      echo "<td><b>Type </b><select name=\"system_code\">\n$syslist</select></td>\n";
    }

    echo "<td><input type=image src=images/in-go.gif alt=go width=\"44\" border=\"0\" height=\"26\" name=submit></td>\n";
    echo "</tr></table>\n</form>\n";

    if ( "$search_for$org_code$system_code$status " != "" && ( $roles['wrms']['Manage'] || $roles['wrms']['Admin'] || $roles['wrms']['Support'] ) ) {

      $query = "SELECT * FROM usr, organisation";
//    $query .= ", session";
      if ( isset( $system_code ) ) $query .= ", system_usr, lookup_code";
      $query .= " WHERE usr.org_code=organisation.org_code ";
      if ( !isset( $org_code ) || $org_code == "" )
        $query .= "AND organisation.active ";
      if ( !isset( $status ) || $status <> "I" )
        $query .= "AND usr.status != 'I' ";
//    $query .= " AND usr.user_no=session.user_no ";
      if ( "$search_for" != "" ) {
        $query .= " AND (fullname ~* '$search_for' ";
        $query .= " OR username ~* '$search_for' ";
        $query .= " OR email ~* '$search_for' )";
      }
      if ( $roles[wrms][Manage] && ! ($roles[wrms][Admin] || $roles[wrms][Support]) )
        $query .= " AND usr.org_code='$session->org_code' ";
      else if ( isset( $org_code ) && $org_code != "" )
        $query .= " AND usr.org_code='$org_code' ";

      if ( isset( $system_code ) ) {
        $query .= " AND system_usr.system_code='$system_code'";
        $query .= " AND system_usr.user_no=usr.user_no";
        $query .= " AND lookup_code.source_table='system_usr' AND lookup_code.source_field='role' AND lookup_code.lookup_code=system_usr.role ";
      }

      $query .= " ORDER BY LOWER(fullname)";
//    $query .= ", session_end DESC ";
//    $query .= " LIMIT 100 ";
      $result = awm_pgexec( $wrms_db, $query, 'usrsearch' );
      if ( $result ) {
      // Build table of usrs found
        echo "<p>&nbsp;" . pg_NumRows($result) . " users found</p>"; // <p>$query</p>";
        echo "<table border=\"0\" cellpadding=2 cellspacing=1 align=center>\n<tr>\n";
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

        if ( $i % 2 == 0 ) echo "<tr bgcolor=$colors[row1]>\n";
        else echo "<tr bgcolor=$colors[row2]>\n";

        echo "<td class=sml><a href=\"index.php?M=LC&E=$thisusr->username&L=";
        echo md5(strtolower($thisusr->password));
        echo "\">$thisusr->username</a></td>\n";
        echo "<td class=sml><a href=\"usr.php?user_no=$thisusr->user_no\">$thisusr->fullname</a></td>\n";
        echo "<td class=sml><a href=\"form.php?form=organisation&org_code=$thisusr->org_code\">$thisusr->org_name</a></td>\n";
        echo "<td class=sml><a href=\"mailto:$thisusr->email\">$thisusr->email</a>&nbsp;</td>\n";
        if ( isset( $system_code ) )
          echo "<td class=sml>$thisusr->lookup_desc ($thisusr->role)&nbsp;</td>\n";
        echo "<td class=sml>" . nice_date($thisusr->last_update) . "&nbsp;</td>\n";
        if ( ! isset( $system_code ) )
          echo "<td class=sml>" . nice_date($thisusr->last_accessed) . "&nbsp;</td>\n";

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

} /* The end of the else ... clause waaay up there! */

  include("inc/footers.php");
?>
