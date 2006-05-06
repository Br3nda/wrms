<?php
require_once("always.php");
require_once("authorisation-page.php");

  include("code-list.php");
  include( "user-list.php" );

  $title = "$system_name Request Ranking List";

    // Recommended way of limiting queries to not include sub-tables for 7.1
    $result = awm_pgexec( $dbconn, "SET SQL_Inheritance TO OFF;" );

  require_once("top-menu-bar.php");
  require_once("page-header.php");

  // Initialise variables.
  include("system-list.php");

  if ( is_member_of('Admin', 'Support' ) ) {
    $system_list = get_system_list( "", "$system_id");
    }
  else {
    $system_list = get_system_list( "CES", "$system_id");
    }

  include( "organisation-list.php" );
  $org_list = get_organisation_list( "$org_code");
?>

<form  method="POST" action="<?php echo $PHP_SELF; ?>" class=row1>
  <table align=center>
    <tr>
      <td class=smb>&nbsp;System:</td>
      <td class=sml>
        <select class=sml name=system_id>
          <option>(All)</option>
          <?php echo $system_list; ?>
        </select>
      </td>
      <td class=smb>&nbsp;Organisation:</td>
      <td class=sml>
        <select class=sml name="org_code">
          <option>(All)</option>
            <?php echo $org_list; ?>
        </select>
      </td>
      <td valign=middle class=smb align=center>
        <input type=submit value="RUN QUERY" alt=go name=submit class="submit">
      </td>
    </tr>
  </table>
  <table>
    <tr>
<?php
  // Build list of statuses with checkboxes. 6 per table row.
  $query = "SELECT lookup_code, lookup_desc FROM lookup_code lc WHERE lc.source_table = 'request' AND lc.source_field = 'status_code'";
  $result = awm_pgexec( $dbconn, $query, "requestrank", false, 7 );

  for ( $i=0; $i < pg_NumRows($result); $i++ ) {
    $lc = pg_Fetch_Object( $result, $i );
    echo "<label>";
    echo "<input type=\"checkbox\" ";
    if ( !isset($_POST['status']) ) $status[$lc->lookup_code] = (strpos("@FCH",$lc->lookup_code) > 0 ? "" : $lc->lookup_code);
    if ( $status[$lc->lookup_code] <> "" ) echo " checked";
    echo " value=\"$lc->lookup_code\" name=\"status[$lc->lookup_code]\">";
    echo "$lc->lookup_desc";
    echo "</label>";
  }
?>
    </tr>
  </table>
</form>

<?php
  $maxresults = ( isset($maxresults) && intval($maxresults) > 0 ? intval($maxresults) : 1000 );

  $query  = "SELECT r.request_id, r.brief, r.detailed, lci.lookup_desc AS importance, lcu.lookup_desc AS urgency, lcs.lookup_desc AS status, lct.lookup_desc AS type, COALESCE(r.agreed_due_date, r.requested_by_date)::DATE AS by_date, ";
  $query .= "(CASE ";
    $query .= "WHEN r.urgency = 20 THEN (date_part('day',now() - COALESCE(r.agreed_due_date, r.requested_by_date)) + 20) * (r.importance * 2 + 10) ";
    $query .= "WHEN r.urgency = 40 THEN (date_part('day',now() - COALESCE(r.agreed_due_date, r.requested_by_date)) + 10) * (r.importance * 2 + 10) ";
    $query .= "ELSE (r.urgency + 10) * (r.importance * 2 + 10) ";
  $query .= "END ) AS ranking ";
  $query .= "FROM request r, usr u, lookup_code lcu, lookup_code lci, lookup_code lcs, lookup_code lct ";
  $query .= "WHERE r.request_by = u.username ";
  $query .= "AND lcu.source_table = 'request' and lcu.source_field = 'urgency'     AND lcu.lookup_code = r.urgency ";
  $query .= "AND lci.source_table = 'request' and lci.source_field = 'importance'  AND lci.lookup_code = r.importance ";
  $query .= "AND lcs.source_table = 'request' and lcs.source_field = 'status_code' AND lcs.lookup_code = r.last_status ";
  $query .= "AND lct.source_table = 'request' and lct.source_field = 'request_type' AND lct.lookup_code = r.request_type ";

  if ( isset($org_code)    && "$org_code"    != "(All)" ) $query .= " AND org_code=".intval($org_code);
  if ( isset($system_id) && "$system_id" != "(All)" ) $query .= " AND system_id=".intval($system_id);

  if ( isset($status) && is_array( $status ) ) {
        reset($status);
        $query .= " AND (r.last_status ~* '[";
        while( list( $k, $v) = each( $status ) ) {
          $query .= $k ;
        }
        $query .= "]') ";
  }

  $query .= " ORDER BY ranking DESC";
  $query .= " LIMIT $maxresults";

  $session->Dbg( "RequestRank", "Query: -> $query");

  $result = awm_pgexec( $dbconn, $query, "requestrank", false, 7 );

  if ( $result && pg_NumRows($result) > 0 ) {
    echo "\n<small>";
    echo pg_NumRows($result) . " requests found";
    if ( pg_NumRows($result) == $maxresults ) echo " (limit reached)";
    if ( isset($saved_query) && $saved_query != "" ) echo " for <b>$saved_query</b>";
    echo "</small>";
  }
  else {
    echo "\n<p><small>No requests found</small></p>";
  }

  // Build table of requests found
  echo "<table>";

  echo "<tr>";
    echo "<th class=cols>Request Id</th>";
    echo "<th class=cols>Brief</th>";
    echo "<th class=cols>Importance</th>";
    echo "<th class=cols>Urgency</th>";
    echo "<th class=cols>By Date</th>";
    echo "<th class=cols>Status</th>";
    echo "<th class=cols>Quotes</th>";
    echo "<th class=cols>Type</th>";
    echo "<th class=cols>Ranking</th>";
  echo "</tr>";

  for ( $i=0; $i < pg_NumRows($result); $i++ ) {
    $thisrequest = pg_Fetch_Object( $result, $i );




    printf( "<tr class=row%1d>", $i % 2);
    echo "<td class=sml><a href=\"request.php?request_id=$thisrequest->request_id\">$thisrequest->request_id</a></td>";
    echo "<td class=sml>$thisrequest->brief</td>";
    echo "<td class=sml>$thisrequest->importance</td>";
    echo "<td class=sml>$thisrequest->urgency</td>";
    echo "<td class=sml>$thisrequest->by_date</td>";
    echo "<td class=sml>".str_replace(' ', '&nbsp;',$thisrequest->status)."</td>";
    echo "<td class=sml>";

    // Display request quote info.

    $quotes_query = "SELECT quote_id, quoted_on::DATE AS quoted_date, quote_type, quote_amount, quote_units, " .
          "approved_on::DATE AS approved_date, quote_brief, quoted_by, username " .
        "FROM request_quote " .
        "LEFT OUTER JOIN usr ON usr.user_no = request_quote.approved_by_id " .
        "WHERE request_quote.request_id = $thisrequest->request_id";

    $quotes_result = awm_pgexec( $dbconn, $quotes_query, "requestrank", false, 7 );

    for ( $ii=0; $ii < pg_NumRows($quotes_result); $ii++ ) {
      $quote_result = pg_Fetch_Object( $quotes_result, $ii );


      echo "<INPUT TYPE=checkbox NAME=q_$quote_result->quote_id CHECKED " .
           "title='Quoted by $quote_result->quoted_by on $quote_result->quoted_date: $quote_result->quote_amount" .
           "&nbsp;$quote_result->quote_units'>";
      if ($quote_result->approved_date <> "")
              echo "&nbsp;<INPUT TYPE=checkbox NAME=a_$quote_result->quote_id CHECKED ".
             "title='Approved by $quote_result->username on $quote_result->approved_date'>";
      echo "<BR>";
    }

    echo "</td>";
    echo "<td class=sml>".str_replace(' ', '&nbsp;',$thisrequest->type)."</td>";
    echo "<td class=sml>$thisrequest->ranking</td>";
    echo "</tr>\n";
  }
  echo "</table>";



/*      if ( intval("$user_no") > 0 )
        $query .= " AND requester_id = " . intval($user_no);
      else if ( intval("$requested_by") > 0 )
        $query .= " AND requester_id = " . intval($requested_by);

    if ( intval("$interested_in") > 0 )
        $query .= " AND request_interested.request_id=request.request_id AND request_interested.user_no = " . intval($interested_in);
      if ( intval("$allocated_to") > 0 )
        $query .= " AND request_allocated.request_id=request.request_id AND request_allocated.allocated_to_id = " . intval($allocated_to);

      if ( "$search_for" != "" ) {
        $query .= " AND (brief ~* '$search_for' ";
        $query .= " OR detailed ~* '$search_for' ) ";
      }

      if ( "$type_code" != "" )     $query .= " AND request_type=" . intval($type_code);

      if ( "$from_date" != "" )     $query .= " AND request.last_activity >= '$from_date' ";
      if ( "$to_date" != "" )     $query .= " AND request.last_activity<='$to_date' ";


      if ( $where_clause != "" ) {
        $query .= " AND $where_clause ";
      }

      if ( isset($incstat) && is_array( $incstat ) ) {
        reset($incstat);
        $query .= " AND (request.last_status ~* '[";
        while( list( $k, $v) = each( $incstat ) ) {
          $query .= $k ;
        }
        $query .= "]') ";
        error_log( "wrms requestlist: DBG: 1-> $query", 0);
        if ( eregi("save", "$submit") && "$savelist" != "" ) {
          $savelist = tidy($savelist);
          $qquery = tidy($query);
          $query = "DELETE FROM saved_queries WHERE user_no = '$session->user_no' AND LOWER(query_name) = LOWER('$savelist');
INSERT INTO saved_queries (user_no, query_name, query_sql) VALUES( '$session->user_no', '$savelist', '$qquery');
$query";
        }
      }
    } */

  echo "\n<small>" . pg_NumRows($result) . " requests found</small>";




  include("page-footer.php");
?>
