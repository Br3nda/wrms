<?php
	include("inc/always.php");
	include("inc/options.php");
	include("inc/code-list.php");
	include( "$base_dir/inc/user-list.php" );

	$title = "$system_name Request Ranking List";

    // Recommended way of limiting queries to not include sub-tables for 7.1
    $result = awm_pgexec( $wrms_db, "SET SQL_Inheritance TO OFF;" );

	include("inc/headers.php");

	// Initialise variables.
	include("inc/system-list.php");

	if ( is_member_of('Admin', 'Support' ) ) {
		$system_list = get_system_list( "", "$system_code");
		}
	else {
		$system_list = get_system_list( "CES", "$system_code");
		}

	include( "inc/organisation-list.php" );
	$org_list = get_organisation_list( "$org_code");
?>

<form  method="POST" action="<?php echo $PHP_SELF; ?>" class=row1>
	<table align=center>
		<tr>
			<td class=smb>&nbsp;System:</td>
			<td class=sml>
				<select class=sml name=system_code>
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
	$result = awm_pgexec( $wrms_db, $query, "requestrank", false, 7 );

 	for ( $i=0; $i < pg_NumRows($result); $i++ ) {
		if (gettype($i/6) == "integer") echo "</tr><tr>";
		$thisrequest = pg_Fetch_Object( $result, $i );
		echo "<td class=sml>";
		echo "<input type=checkbox ";
		if ( !isset( $status) || $status[$thisrequest->lookup_code] <> "" ) echo " checked";
		echo " value=$thisrequest->lookup_code name=status[$thisrequest->lookup_code]>";
		echo "$thisrequest->lookup_desc";
		echo "</td>";
	}
?>
		</tr>
	</table>
</form>

<?php
	$query  = "SELECT r.request_id, r.brief, r.detailed, lci.lookup_desc AS importance, lcu.lookup_desc AS urgency, lcs.lookup_desc AS status, lct.lookup_desc AS type, r.requested_by_date, ";
	$query .= "(CASE ";
  	$query .= "WHEN r.urgency = 20 THEN (date_part('day',now() - r.requested_by_date) + 20) * (r.importance * 2 + 10) ";
  	$query .= "WHEN r.urgency = 40 THEN (date_part('day',now() - r.requested_by_date) + 10) * (r.importance * 2 + 10) ";
  	$query .= "ELSE (r.urgency + 10) * (r.importance * 2 + 10) ";
	$query .= "END ) AS ranking ";
	$query .= "FROM request r, usr u, lookup_code lcu, lookup_code lci, lookup_code lcs, lookup_code lct ";
	$query .= "WHERE r.request_by = u.username ";
	$query .= "AND lcu.source_table = 'request' and lcu.source_field = 'urgency'     AND lcu.lookup_code = r.urgency ";
	$query .= "AND lci.source_table = 'request' and lci.source_field = 'importance'  AND lci.lookup_code = r.importance ";
	$query .= "AND lcs.source_table = 'request' and lcs.source_field = 'status_code' AND lcs.lookup_code = r.last_status ";
	$query .= "AND lct.source_table = 'request' and lct.source_field = 'request_type' AND lct.lookup_code = r.request_type ";

	if ( isset($org_code)    && "$org_code"    != "(All)" ) $query .= " AND org_code='$org_code' ";
	if ( isset($system_code) && "$system_code" != "(All)" ) $query .= " AND system_code='$system_code' ";

	if ( isset($status) && is_array( $status ) ) {
        reset($status);
        $query .= " AND (r.last_status ~* '[";
        while( list( $k, $v) = each( $status ) ) {
          $query .= $k ;
        }
        $query .= "]') ";
	}

	$query .= "ORDER BY ranking DESC";

	error_log( "wrms requestrank: DBG: 1-> $query", 0);

	$result = awm_pgexec( $wrms_db, $query, "requestrank", false, 7 );

 	// Build table of requests found
	echo "<table>";

	echo "<tr>";
		echo "<th class=cols>Request Id</th>";
		echo "<th class=cols>Brief</th>";
		echo "<th class=cols>Importance</th>";
		echo "<th class=cols>Urgency</th>";
		echo "<th class=cols>Requested By Date</th>";
		echo "<th class=cols>Status</th>";
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
		echo "<td class=sml>$thisrequest->requested_by_date</td>";
		echo "<td class=sml>$thisrequest->status</td>";
		echo "<td class=sml>$thisrequest->type</td>";
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




	include("inc/footers.php");
?>
