<p>Good <?php
  $hour = strftime( "%H" );
  if ( $hour > 22 || $hour < 6 ) {
    echo "night.  Go to bed!<p>";
  }
  else if ( $hour < 12 )
    echo "morning.";
  else if ( $hour < 18 )
    echo "afternoon.";
  else
    echo "evening.";

?> Since you're on the support side of things you now get to see a list of all the organisations and
how many requests are active for each organisation.  Click on the name of an organisation to
see their currently active requests.
<?php
  // Should already be tested, but we might as well check again.
  if ( ! is_member_of('Admin','Support') ) return;

  $query = "SELECT *, to_char( last_org_request(org_code), 'D Mon YYYY') AS last_request_date, ";
  $query .= "active_org_requests(org_code) ";
  $query .= "FROM organisation WHERE active AND last_org_request(org_code) IS NOT NULL AND active_org_requests(org_code) > 0 ";
  $query .= "ORDER BY LOWER(organisation.org_name) ";
  $query .= "LIMIT 100 ";
  $result = awm_pgexec( $dbconn, $query, "indexsupport", false, 5 );
  if ( $result && pg_NumRows( $result ) ) {
    echo "<table border=\"0\" align=center width=100%><tr>\n";
    echo "<th class=cols align=left>Organisation Name</th>";
    echo "<th class=cols align=center>Requests</th>";
    echo "<th class=cols align=center>Last Request</th>";
    echo "<th class=cols align=center>Show:</th></tr>";

    // Build table of organisations found
    for ( $i=0; $i < pg_NumRows($result); $i++ ) {
      $thisorganisation = pg_Fetch_Object( $result, $i );

      printf("<tr class=row%1d>", $i % 2);

      echo "<td class=sml>&nbsp;<a href=\"requestlist.php?org_code=$thisorganisation->org_code\">$thisorganisation->org_name";
      if ( "$thisorganisation->org_name" == "" ) echo "-- no description --";
      echo "</a>&nbsp;</td>\n";

      echo "<td class=sml align=right>&nbsp;$thisorganisation->active_org_requests</td>\n";
      echo "<td class=sml align=center>&nbsp;$thisorganisation->last_request_date</td>\n";

      echo "<td class=sml align=center><a class=submit href=\"form.php?form=organisation&org_code=$thisorganisation->org_code\">Details</a>";
      echo "&nbsp;&nbsp;<a class=submit href=\"usrsearch.php?org_code=$thisorganisation->org_code\">Users</a>";
      echo "&nbsp;&nbsp;<a class=submit href=\"form.php?org_code=$thisorganisation->org_code&form=timelist&uncharged=1\">Work</a>";
      //echo "&nbsp;&nbsp;<a class=submit href=\"form.php?org_code=$thisorganisation->org_code&form=simpletimelist&uncharged=1\">Work by Person</a>";
      echo "</td></tr>\n";
    }
    echo "</table>\n";
    // echo "<p>$query</p>";
  }

?>
