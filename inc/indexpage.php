<?php
echo "<H3>$system_name</H3>\n";
if ( $logged_on ) { ?>

<H4>Please select an action from the menus at the top of the page, or select
one of the recently modified requests from the list below.</H4>
<?php
  $query = "SELECT DISTINCT request.request_id, brief, fullname, email, last_activity, lookup_desc AS status_desc, request.system_code ";
  if ( $roles['wrms']['Admin'] || $roles['wrms']['Support']  ) {
    // Satisfy v7 requirement for order field in target list
    $query .= ", request.urgency, request.importance ";
  }
  $query .= "FROM request, request_interested, usr, lookup_code AS status ";
  $query .= "WHERE request.request_id=request_interested.request_id ";
  if ( $roles['wrms']['Manage'] && ! ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] )  ) {
//    $query .= "AND EXISTS (SELECT system_usr.system_code FROM system_usr, work_system WHERE system_usr.system_code=work_system.system_code";
//    $query .= " AND user_no=$session->user_no ";
//    $query .= " AND role~*'[CES]') ";
    $query .= " AND usr.org_code=$session->org_code ";
  }
  else {
    $query .= "AND (request_interested.user_no=$session->user_no ";
    $query .= "OR request.requester_id=$session->user_no) ";
  }
  $query .= "AND request.requester_id=usr.user_no ";
  $query .= "AND status.source_table='request' AND status.source_field='status_code' AND status.lookup_code=request.last_status ";
  if ( $roles['wrms']['Admin'] || $roles['wrms']['Support']  ) {
    $query .= "AND request.active AND request.last_status~*'[AILNRQA]' ";
//    $query .= "ORDER BY request.importance DESC, request.urgency DESC, request.request_id LIMIT 50 ";
    $query .= "ORDER BY last_activity DESC LIMIT 50 ";
  }
  else {
    $query .= "AND request.active AND request.last_status~*'[AILNRQA]' ";
    $query .= "ORDER BY last_activity DESC LIMIT 20 ";
  }
  $result = awm_pgexec( $wrms_db, $query, 'indexpage', 7 );
  if ( $result ) {
    echo "<table border=0 align=center cellspacing=0 cellpadding=2><tr>\n";
    echo "<th class=cols>WR&nbsp;#</th><th class=cols>Requested By</th>";
    echo "<th class=cols>Description</th><th class=cols>Status</th><th class=cols>Last&nbsp;Activity</th></tr>\n";
    for ( $i=0; $i < pg_NumRows($result); $i++ ) {
      $thisrequest = pg_Fetch_Object( $result, $i );

      if( ($i % 2) == 0 ) echo "<tr bgcolor=$colors[6]>";
      else echo "<tr bgcolor=$colors[7]>";

      echo "<td class=sml align=center><a href=\"request.php?request_id=$thisrequest->request_id\">$thisrequest->request_id</a></td>\n";
      echo "<td class=sml nowrap><a href=\"mailto:$thisrequest->email\">$thisrequest->fullname</a></td>\n";
      echo "<td class=sml><a href=\"request.php?request_id=$thisrequest->request_id\">$thisrequest->brief";
      if ( "$thisrequest->brief" == "" ) echo "-- no description --";
      echo "</a></td>\n";
      echo "<td class=sml>$thisrequest->status_desc</td>\n";
      echo "<td class=sml nowrap>" . nice_date($thisrequest->last_activity) . "</td>\n";

      echo "</tr>\n";
    }
    echo "</table>\n";
  }
}
else { ?>

<H4>For access to Catalyst's Work Request Management System you should log on with
the username and password that have been issued to you.</H4>

<h4>If you would like to request access, please e-mail Andrew at Catalyst.</h4>

<?php } ?>

