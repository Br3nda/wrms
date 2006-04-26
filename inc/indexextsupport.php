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

?> Since you're on the support side of things you now get to see a list of systems
and the numbers of requests active for each one.  Click on the name of a system to
see those currently active requests.
<?php
  // Should already be tested, but we might as well check again.
  if ( ! is_member_of('Admin','Support','Contractor') ) return;

  $query = "SELECT s.system_id, lower(s.system_desc) AS lcname, s.system_desc, ";
  $query .= "to_char( max(r.request_on), 'D Mon YYYY') AS last_request_date, ";
  $query .= "count(r.request_id) AS active_sys_requests ";
  $query .= "FROM work_system s ";
  $query .= "JOIN system_usr su ON s.system_id = su.system_id ";
  $query .= "JOIN request r ON r.system_id = su.system_id ";
  $query .= "WHERE su.user_no = $session->user_no AND s.active AND r.active ";
  $query .= "AND su.role IN ( 'A', 'S', 'C', 'E', 'O', 'V' ) ";
  $query .= "AND (r.last_activity > (current_timestamp - '30 days'::interval) ";
  $query .= "     OR r.last_status NOT IN ( 'F', 'C' ) ) ";
  $query .= "GROUP BY lower(s.system_desc), s.system_id, s.system_desc ";
  $query .= "HAVING COUNT(r.request_id) > 0 ";
  $query .= "ORDER BY lower(s.system_desc) ";
  $result = awm_pgexec( $dbconn, $query, "indexsupport", false, 5 );
  if ( $result && pg_NumRows( $result ) ) {
    echo "<table border=\"0\" align=center width=100%><tr>\n";
    echo "<th class=cols align=left>Organisation Name</th>";
    echo "<th class=cols align=center>Requests</th>";
    echo "<th class=cols align=center>Last Request</th>";
    echo "<th class=cols align=center>Show:</th></tr>";

    // Build table of organisations found
    for ( $i=0; $i < pg_NumRows($result); $i++ ) {
      $this_system = pg_Fetch_Object( $result, $i );

      printf("<tr class=row%1d>", $i % 2);

      echo "<td class=sml>&nbsp;<a href=\"requestlist.php?system_id=$this_system->system_id\">$this_system->system_desc";
      if ( "$this_system->system_desc" == "" ) echo "-- no description --";
      echo "</a>&nbsp;</td>\n";

      echo "<td class=sml align=right>&nbsp;$this_system->active_sys_requests</td>\n";
      echo "<td class=sml align=center>&nbsp;$this_system->last_request_date</td>\n";

      echo "<td class=sml align=center><a class=\"submit\" href=\"org.php?org_code=$this_system->org_code\">Details</a>";
      echo "&nbsp;&nbsp;<a class=submit href=\"usrsearch.php?org_code=$this_system->org_code\">Users</a>";
      echo "&nbsp;&nbsp;<a class=submit href=\"form.php?org_code=$this_system->org_code&form=timelist&uncharged=1\">Work</a>";
      //echo "&nbsp;&nbsp;<a class=submit href=\"form.php?org_code=$this_system->org_code&form=simpletimelist&uncharged=1\">Work by Person</a>";
      echo "</td></tr>\n";
    }
    echo "</table>\n";
  }

?>
