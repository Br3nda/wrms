<?php
  include("always.php");
  include("options.php");
  include("maintenance-page.php");

  $title = "$system_name User Search";
  include("headers.php");

  if ( ! is_member_of('Admin','Support','Manage') ) {
    echo "<p class=error>Unauthorised</p>\n";
  }
  else {
    echo "<form Action=\"$base_url/usrsearch.php\" Method=\"post\">\n";

    echo "<table align=center cellspacing=0 cellpadding=2><tr valign=middle>\n";
    echo "<td class=smb><b>Name:</b></td><td><input class=sml type=text size=\"8\" name=search_for value=\"$search_for\"></td>\n";

    if ( is_member_of('Admin','Support') ) {
      include( "organisation-list.php" );
      $orglist = "<option value=\"\">--- All Organisations ---</option>\n" . get_organisation_list( "$org_code", 20 );
      echo "<td class=smb>Org:</td><td><select class=sml name=\"org_code\">\n$orglist</select></td>\n";
    }
    if ( is_member_of('Admin','Support','Manage') ) {
      include( "system-list.php" );
      $syslist = "<option value=\"\">--- All Systems ---</option>\n" . get_system_list( "VOECS", "$system_code", 20 );
      echo "<td class=smb><b>Type:</b></td><td><select class=sml name=\"system_code\">\n$syslist</select></td>\n";
      echo "<td class=smb>&nbsp;<b><label id=status>Inactive:</label></b></td><td><input id=status class=sml type=\"checkbox\" name=status value=I" . ("$status" == "I" ? " checked" : "") . "></td>\n";
    }

    echo "<td><input type=submit class=submit alt=go value=\"GO>>\" name=submit></td>\n";
    echo "</tr></table>\n</form>\n";

    if ( "$search_for$org_code$system_code$status " != "" ) {

      $query = "SELECT *, to_char( last_update, 'dd/mm/yyyy' ) AS last_update, to_char( last_accessed, 'dd/mm/yyyy' ) AS last_accessed ";
      $query .= "FROM usr, organisation";

      if ( isset( $system_code ) && $system_code <> "" ) $query .= ", system_usr, lookup_code";
      $query .= " WHERE usr.org_code=organisation.org_code ";
      if ( !isset( $org_code ) || $org_code == "" )
        $query .= "AND organisation.active ";
      if ( !isset( $status ) || $status <> "I" )
        $query .= "AND usr.status != 'I' ";

      if ( "$search_for" != "" ) {
        $query .= " AND (fullname ~* '$search_for' ";
        $query .= " OR username ~* '$search_for' ";
        $query .= " OR email ~* '$search_for' )";
      }
      if ( is_member_of('Manage') && ! is_member_of('Admin','Support') ) {
        $query .= " AND usr.org_code='$session->org_code' ";
      }
      else if ( isset( $org_code ) && $org_code != "" ) {
        $query .= " AND usr.org_code='$org_code' ";
      }

      if ( isset( $system_code ) && $system_code <> ""  ) {
        $query .= " AND system_usr.system_code='$system_code'";
        $query .= " AND system_usr.user_no=usr.user_no";
        $query .= " AND lookup_code.source_table='system_usr' AND lookup_code.source_field='role' AND lookup_code.lookup_code=system_usr.role ";
      }

      $query .= " ORDER BY LOWER(fullname);";

      $result = awm_pgexec( $dbconn, $query, 'usrsearch' );
      if ( $result ) {
      // Build table of usrs found
        echo "<p>&nbsp;" . pg_NumRows($result) . " users found</p>";
        echo "<table border=\"0\" cellpadding=2 cellspacing=1 align=center width=100%>\n<tr>\n";
        echo "<th class=cols>User&nbsp;ID</th><th class=cols>Full Name</th>\n";
        if ( "$org_code" == "" )
          echo "<th class=cols>Organisation</th>\n";
        echo "<th class=cols>Email</th>\n";
        if ( isset( $system_code )  && $system_code <> "")
          echo "<th class=cols>User Role</th>\n";
        echo "<th class=cols>Updated</th>\n";
        echo "<th class=cols>Accessed</th>\n";
        echo "<th class=cols>Actions</th>\n";
        echo "</tr>\n";

      for ( $i=0; $i < pg_NumRows($result); $i++ ) {
        $thisusr = pg_Fetch_Object( $result, $i );

        printf("<tr class=row%1d>\n", $i % 2);

        echo "<td class=sml><a href=\"usr.php?user_no=$thisusr->user_no\">$thisusr->username</a></td>\n";
        echo "<td class=sml><a href=\"usr.php?user_no=$thisusr->user_no\">$thisusr->fullname</a></td>\n";
        if ( "$org_code" == "" )
          echo "<td class=sml><a href=\"form.php?form=organisation&org_code=$thisusr->org_code\">$thisusr->org_name</a></td>\n";
        echo "<td class=sml><a href=\"mailto:$thisusr->email\">$thisusr->email</a>&nbsp;</td>\n";
        if ( isset( $system_code ) && $system_code <> "" )
          echo "<td class=sml>$thisusr->lookup_desc ($thisusr->role)&nbsp;</td>\n";
        echo "<td class=sml>$thisusr->last_update&nbsp;</td>\n";
        echo "<td class=sml>$thisusr->last_accessed&nbsp;</td>\n";

        echo "<td class=sml><a class=submit href=\"requestlist.php?user_no=$thisusr->user_no\">Requested</a>\n";
        if ( is_member_of('Admin','Support') ) {
          echo "<a class=submit href=\"requestlist.php?allocated_to=$thisusr->user_no\">Allocated</a>\n";
          echo "<a class=submit href=\"form.php?user_no=$thisusr->user_no&form=timelist&uncharged=1\">Work</a>\n";
        }
        echo "</td></tr>\n";
      }
      echo "</table>\n";
    }
  }

} /* The end of the else ... clause waaay up there! */

  include("footers.php");
?>
