<?php
	include("always.php");
	include("options.php");
	include("code-list.php");
	include( "user-list.php" );

	$title = "$system_name Request Billing List";

    // Recommended way of limiting queries to not include sub-tables for 7.1
    $result = awm_pgexec( $dbconn, "SET SQL_Inheritance TO OFF;" );

	include("headers.php");

	// Initialise variables.
	include("system-list.php");

	if ( is_member_of('Admin', 'Support' ) ) {
		$system_list = get_system_list( "", "$system_code");
		}
	else {
		$system_list = get_system_list( "CES", "$system_code");
		}

    $request_types = get_code_list( "request", "request_type", "$request_type" );
        
?>

<form  method="POST" action="<?php echo $PHP_SELF; ?>" class=row1>
	<table align=center>
		<tr>
			<td class=smb>System</td>
			<td class=sml>
				<select class=sml name=system_code>
					<option>(All)</option>
					<?php echo $system_list; ?>
				</select>
			</td>
			<td class=smb>Request Type</td>
			<td class=sml>
				<select class=sml name=request_type>
					<option>(All)</option>
						<?php echo $request_types; ?>
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

    if ( isset($system_code) || isset($request_type)) {

	$query  = "SELECT";
	$query .= "  r.request_id                         AS \"id\"" ;
	$query .= ", r.system_code                        AS system" ;
	$query .= ", SUBSTR(r.brief,1,40)                 AS \"request brief\"";
	$query .= ", lc.lookup_desc                       AS \"request type\"";
	$query .= ", lcs.lookup_desc                       AS \"status\"";
	$query .= ", rq.quote_type                        AS \"quote type\"";
	$query .= ", rq.quote_brief                       AS \"quote brief\"";
	$query .= ", rq.quoted_by                         AS \"quoted by\"";
/*
        // Add quote_units to list of select fields.

	$wt_query = "SELECT lookup_code FROM lookup_code WHERE source_table = 'request_quote' AND source_field = 'quote_units'";
	$wt_result = awm_pgexec( $dbconn, $wt_query, "billing.quote_units");

 	for ( $i=0; $i < pg_NumRows($wt_result); $i++ ) {
		$wt_row = pg_fetch_row( $wt_result, $i );
		$query .= ", (SELECT SUM(q.quote_amount) FROM request_quote q WHERE q.request_id = r.request_id AND q.quote_units = '$wt_row[0]' ) AS $wt_row[0]";
	}
	*/

	$query .= ", to_char(rq.quoted_on,'DD/MM/YYYY')   AS \"quoted on\"" ;
	$query .= ", usr.username                         AS \"approved by\"" ;
	$query .= ", to_char(rq.approved_on,'DD/MM/YYYY') AS \"approved on\"" ;
	$query .= ", rq.quote_amount                      AS \"quote amount\"";
	$query .= ", rq.quote_units                      AS \"units\"";
	$query .= ", rq.invoice_no                        AS \"inv no\"" ;

	$query .= " FROM request r";
	$query .= " LEFT OUTER JOIN lookup_code lc ON lc.source_table = 'request' AND lc.source_field = 'request_type' AND lc.lookup_code  = r.request_type";
	$query .= " LEFT OUTER JOIN lookup_code lcs ON lcs.source_table = 'request' AND lcs.source_field = 'status_code' AND lcs.lookup_code  = r.last_status";
	$query .= " LEFT OUTER JOIN request_quote rq ON rq.request_id = r.request_id";
	$query .= " LEFT OUTER JOIN usr ON usr.user_no = rq.approved_by_id ";

	// Build WHERE clause

        if ( isset($system_code)    && "$system_code"    != "(All)" ) $where .= " AND r.system_code='$system_code' ";
        if ( isset($request_type) && "$request_type" != "(All)" ) $where .= " AND r.request_type='$request_type' ";

	if (isset($where)) $query .= " WHERE " . substr($where,4);

        if ( isset($status) && is_array( $status ) ) {
	     reset($status);
	     $query .= " AND (r.last_status ~* '[";
	     while( list( $k, $v) = each( $status ) ) {
	       $query .= $k ;
	     }
	     $query .= "]') ";
	}

	// Build ORDER BY clause

 	$query .= " ORDER BY r.system_code, r.request_id ;";

	
	// Execute query

	$result = awm_pgexec( $dbconn, $query, "billing", false, 7 );

	echo "<table border=0><tr>";

        // Create column headers for selected fields.

	for ($i = 0; $i < pg_numfields($result); $i++) {
    		echo "<th class=cols>" . pg_fieldname($result, $i) . "</th>";
	}
	
        // Add request_timesheet work types to list of select fields.

	echo "<th class=cols>work amount</th><th class=cols>units</th<th class=cols>inv no</th><th class=cols>linked id</th><th class=cols>link type</th><th class=cols>work amount</th><th class=cols>units</th><th class=cols>inv no</th></tr>\n";

	// Print result rows.

 	for ( $i=0; $i < pg_NumRows($result); $i++ ) {
		printf( "<tr class=row%1d>", $i % 2);
		$row = pg_fetch_array( $result, $i );

		// Print work totals for first instance on any WR
		if ($row["id"] <> $prev_id) {
		  // work on this WR
		  $w_query  = "SELECT sum(rt.work_quantity) AS quantity, rt.work_units AS units, rt.charged_details AS \"inv no\"";
		  $w_query .= " FROM request_timesheet rt WHERE rt.request_id = " . $row["id"] ;
		  $w_query .= " GROUP BY rt.work_units, rt.charged_details";		  
		  $w_result = awm_pgexec( $dbconn, $w_query, "billing.work", false, 7 );

		  // work on linked WRs
		  $lw_query  = "SELECT rr.to_request_id, rr.link_type, sum(rt.work_quantity) AS quantity, rt.work_units AS units, rt.charged_details AS \"inv no\"";
                  $lw_query .= " FROM request_request rr ";
      		  $lw_query .= " LEFT OUTER JOIN request_timesheet rt ON rt.request_id = rr.to_request_id ";
		  $lw_query .= " WHERE rr.request_id = " . $row["id"] ;
		  $lw_query .= " GROUP BY rr.to_request_id, rr.link_type, rt.work_units, rt.charged_details";
		  $lw_result = awm_pgexec( $dbconn, $lw_query, "billing.linkedwork", false, 7 );

		  $prev_id = $row["id"] ;
		}

		$max_res = max(pg_numrows($w_result),pg_numrows($lw_result));

		for ($j = 0; $j < pg_numfields($result) ; $j++) {
			echo "<td class=sml";
			if ($max_res > 1) echo " rowspan=" . $max_res ;
			echo ">";
			if ($j == 0) echo "<a href=request.php?request_id=" . $row["id"] . ">";
			echo  $row[$j] ;
		        if ($j == 0) echo "</a>";
			echo "</td>";
		}

                if ($max_res == 0) echo "<td class=sml></td><td class=sml></td><td class=sml></td><td class=sml></td><td class=sml></td><td class=sml></td><td class=sml></td><td class=sml></td></tr>\n";
		else for ($j = 0; $j < $max_res; $j++) {
		  if ($j < pg_numrows($w_result)) { 
		    $w_row = pg_fetch_row($w_result);

		    for ($k = 0; $k < pg_numfields($w_result) ; $k++) {
		      echo "<td class=sml>$w_row[$k]</td>";
		    }
		  }
		  else echo "<td class=sml></td><td class=sml></td><td class=sml></td>";		  
		  
		  if ($j < pg_numrows($lw_result)) {
		    $lw_row = pg_fetch_row($lw_result);

		    for ($k = 0; $k < pg_numfields($lw_result) ; $k++) {
		      echo "<td class=sml>";
		      if ($k == 0) echo "<a href=request.php?request_id=" . $lw_row[$k] . ">";
                      echo  $lw_row[$k] ;
                      if ($k == 0) echo "</a>";
         	      echo "</td>";
		    }
		  }
		  
                  else echo "<td class=sml></td><td class=sml></td><td class=sml></td><td class=sml></td><td class=sml></td>"; 
		  echo "</tr>\n";
		  if ($j < $max_res) printf( "<tr class=row%1d>", $i % 2);
		}
	}

	echo "</table>";

	echo "\n<small>" . pg_NumRows($result) . " requests found</small>";
    }

    include("footers.php");
?>
