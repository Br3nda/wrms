<?php

  echo "<p>Good ";
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
  echo <<<EOT
 Since you're on the support side of things you now get to see a list of all the organisations and
how many requests are active for each organisation.  Click on the name of an organisation to
see their currently active requests.  Create a search called 'Home' to replace this default list.
EOT;

  $sql = "SELECT *, to_char( last_org_request(org_code), 'D Mon YYYY') AS last_request_date, ";
  $sql .= "active_org_requests(org_code) ";
  $sql .= "FROM organisation ";
  $sql .= "WHERE active ";
  $sql .= "AND last_org_request(org_code) IS NOT NULL ";
  $sql .= "AND ( EXISTS( SELECT 1 FROM org_system os JOIN system_usr su USING(system_id) WHERE os.org_code=organisation.org_code AND su.user_no=$session->user_no AND su.role IN ('A','S')) ";
  $sql .= "OR EXISTS( SELECT 1 FROM request_interested ri JOIN request r USING(request_id) JOIN usr u ON (r.requester_id=u.user_no) WHERE r.active AND r.last_activity > (current_timestamp - '8 months'::interval) AND u.org_code=organisation.org_code AND ri.user_no=$session->user_no ) ";
  $sql .= "OR EXISTS( SELECT 1 FROM request_allocated ra JOIN request r USING(request_id) JOIN usr u ON (r.requester_id=u.user_no) WHERE r.active AND r.last_activity > (current_timestamp - '14 months'::interval) AND u.org_code=organisation.org_code AND ra.allocated_to_id=$session->user_no ) ) ";
  $sql .= "AND active_org_requests(org_code) > 0 ";
  $sql .= "ORDER BY LOWER(organisation.org_name) ";
  $sql .= "LIMIT 100 ";
  $qry = new PgQuery( $sql );
  if ( $qry->Exec("indexsupport") && $qry->rows > 0 ) {
    echo "<table border=\"0\" align=\"center\" width=\"100%\"><tr>\n";
    echo "<th class=\"cols\" align=\"left\">Organisation Name</th>";
    echo "<th class=\"cols\" align=\"center\">Requests</th>";
    echo "<th class=\"cols\" align=\"center\">Last Request</th>";
    echo "<th class=\"cols\" align=\"center\">Show:</th></tr>";

    // Build table of rows
    while ( $thisorganisation = $qry->Fetch() ) {
      $i = (!isset($i) || $i == 0 ? 1 : 0);
      printf("<tr class=\"row%1d\">", $i);

      echo "<td class=\"sml\">&nbsp;<a href=\"requestlist.php?org_code=$thisorganisation->org_code\">$thisorganisation->org_name";
      if ( "$thisorganisation->org_name" == "" ) echo "-- no description --";
      echo "</a>&nbsp;</td>\n";

      echo "<td class=\"sml\" align=\"right\">&nbsp;$thisorganisation->active_org_requests</td>\n";
      echo "<td class=\"sml\" align=\"center\">&nbsp;$thisorganisation->last_request_date</td>\n";

      echo "<td class=\"sml\" align=\"center\"><a class=\"submit\" href=\"org.php?org_code=$thisorganisation->org_code\">Organisation</a>";
      echo "&nbsp;&nbsp;<a class=\"submit\" href=\"usrsearch.php?org_code=$thisorganisation->org_code\">Users</a>";
      echo "&nbsp;&nbsp;<a class=\"submit\" href=\"form.php?org_code=$thisorganisation->org_code&form=timelist&uncharged=1\">Work</a>";
      echo "</td></tr>\n";
    }
    echo "</table>\n";
  }

