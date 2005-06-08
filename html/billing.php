<?php
  include("always.php");
  require_once("authorisation-page.php");
  $session->LoginRequired();
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

    $quote_types = get_code_list( "request_quote", "quote_type", "$quote_type" );

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
      <td class=smb>Quote Type</td>
      <td class=sml>
        <select class=sml name=quote_type>
          <option>(All)</option>
            <?php echo $quote_types; ?>
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
    <tr>
      <td class=sml><input type=radio value=invoiced name=invoiced<?php if ("$invoiced" == "invoiced") echo " checked";?>>Invoiced</td>
      <td class=sml><input type=radio value=uninvoiced name=invoiced<?php if ("$invoiced" == "uninvoiced") echo " checked";?>>Uninvoiced</td>
      <td class=sml><input type=radio value=both name=invoiced<?php if ("$invoiced" == "both") echo " checked";?>>Both</td>
    </tr>
  </table>
</form>

<?php

    if ( isset($system_code) || isset($request_type) || isset($quote_type) ) {

  $query  = "SELECT";
  $query .= "  r.request_id                         AS \"id\"" ;
  $query .= ", r.system_code                        AS system" ;
  $query .= ", SUBSTR(r.brief,1,40)                 AS \"request brief\"";
  $query .= ", lc.lookup_desc                       AS \"request type\"";
  $query .= ", lcs.lookup_desc                       AS \"status\"";
  $query .= ", rq.quote_type                        AS \"quote type\"";
  $query .= ", rq.quote_brief                       AS \"quote brief\"";
  $query .= ", rq.quoted_by                         AS \"quoted by\"";
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
        if ( isset($quote_type) && "$quote_type" != "(All)" ) $where .= " AND rq.quote_type='$quote_type' ";

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

  echo "<th class=cols>work amount</th><th class=cols>units</th<th class=cols>inv no</th><th class=cols>linked id</th><th class=cols>link type</th><th class=cols>work amount</th><th class=cols>units</th><th class=cols>status</th><th class=cols>inv no</th></tr>\n";

  // Print result rows.

  for ( $i=0; $i < pg_NumRows($result); $i++ ) {
    $row = pg_fetch_array( $result, $i );

    $numrows_w_result = 0;
    $numrows_lw_result = 0;

    // Print work totals for first instance on any WR
    if ($row["id"] <> $prev_id) {
      // work on this WR
      $w_query  = "SELECT sum(rt.work_quantity) AS quantity, rt.work_units AS units, rt.charged_details AS \"inv no\"";
      $w_query .= " FROM request_timesheet rt WHERE rt.request_id = " . $row["id"] ;
      if ("$invoiced" == "invoiced") $w_query .= " AND rt.charged_details IS NOT null ";
      else if ("$invoiced" == "uninvoiced") $w_query .= " AND rt.charged_details IS null ";
      $w_query .= " GROUP BY rt.work_units, rt.charged_details ";
      $w_result = awm_pgexec( $dbconn, $w_query, "billing.work", false, 7 );
      $numrows_w_result = pg_numrows($w_result) ;

      // work on linked WRs
      $lw_query  = "SELECT rr.to_request_id, rr.link_type, sum(rt.work_quantity) AS quantity, rt.work_units AS units, lc.lookup_desc, rt.charged_details AS \"inv no\"";
      $lw_query .= " FROM request_request rr ";
      if ("$invoiced" == "both") $lw_query .= " LEFT OUTER";
      $lw_query .= " JOIN request_timesheet rt ON rt.request_id = rr.to_request_id ";
      if ("$invoiced" == "invoiced") $lw_query .= " AND rt.charged_details IS NOT null AND rt.charged_details <> ''";
      else if ("$invoiced" == "uninvoiced") $lw_query .= " AND (rt.charged_details IS null OR rt.charged_details = '')";
      $lw_query .= " LEFT OUTER JOIN request r ON r.request_id = rr.to_request_id ";
      $lw_query .= " LEFT OUTER JOIN lookup_code lc ON lc.source_table = 'request' AND lc.source_field = 'status_code' AND lc.lookup_code  = r.last_status";
      $lw_query .= " WHERE rr.request_id = " . $row["id"] ;
      $lw_query .= " GROUP BY rr.to_request_id, rr.link_type, rt.work_units, lc.lookup_desc, rt.charged_details";
      $lw_result = awm_pgexec( $dbconn, $lw_query, "billing.linkedwork", false, 7 );
      $numrows_lw_result = pg_numrows($lw_result) ;

      $prev_id = $row["id"] ;
    }


    $max_res = max($numrows_w_result, $numrows_lw_result);

    // echo " id: " . $row["id"] ." " . $max_res . " " . $row["inv no"] . " " . $numrows_w_result . " " . $numrows_lw_result .  " " ;

    // If looking for invoiced only, and there are no invoice numbers for work done, or any quote, then skip it.
    if ($max_res == 0 && "$invoiced" == "invoiced" && $row["inv no"] == "" ) continue;

    // looking for un-invoiced stuff.
    if ("$invoiced" == "uninvoiced") {
      // If there is a 'Q' type quote with an invoice no, and no un-invoiced work then skip it.
      if ($row["quote type"] == "Q" && $row["inv no"] <> "" && $numrows_w_result == 0) continue;

      // If there is no un-invoiced work on this WR and this is a 'E' type quote with no un-invoiced work on linked request then skip it.
      if ($numrows_w_result == 0 && $row["quote type"] == "E" && $row["inv no"] == "" && $numrows_lw_result == 0) continue;

      // If there are invoice numbers for all work done, and only invoiced quotes, or no quote, then skip it.
      if ($max_res == 0 && ($row["quote type"] == "" || $row["inv no"] <> "") ) continue;
    }

                $printed_rows++;
    printf( "<tr class=row%1d>", $printed_rows % 2);

    for ($j = 0; $j < pg_numfields($result) ; $j++) {
      echo "<td class=sml";
      if ($max_res > 1) echo " rowspan=" . $max_res ;
      echo ">";
      if ($j == 0) echo "<a href=request.php?request_id=" . $row["id"] . ">";
      echo  $row[$j] ;
            if ($j == 0) echo "</a>";
      echo "</td>";
    }

                // No work or linked WRs found
                if ($max_res == 0) echo "<td class=sml></td><td class=sml></td><td class=sml></td><td class=sml></td><td class=sml></td><td class=sml></td><td class=sml></td><td class=sml></td><td class=sml></td></tr>\n";

                // Work or linked WR found.
    else for ($j = 0; $j < $max_res; $j++) {
                  // Display work for this WR
      if ($j < $numrows_w_result) {
        $w_row = pg_fetch_row($w_result);

        for ($k = 0; $k < pg_numfields($w_result) ; $k++) {
          echo "<td class=sml>$w_row[$k]</td>";
        }
      }
      else echo "<td class=sml></td><td class=sml></td><td class=sml></td>";

      if ($j < $numrows_lw_result) {
                    // Display work on linked WR
        $lw_row = pg_fetch_row($lw_result);

        for ($k = 0; $k < pg_numfields($lw_result) ; $k++) {
          echo "<td class=sml>";
          if ($k == 0) echo "<a href=request.php?request_id=" . $lw_row[$k] . ">";
                      echo  $lw_row[$k];
                      if ($k == 0) echo "</a>";
                echo "</td>";
        }
      }
                  else echo "<td class=sml></td><td class=sml></td><td class=sml></td><td class=sml></td><td class=sml></td><td class=sml></td>";
      echo "</tr>\n";
      if ($j < $max_res) printf( "<tr class=row%1d>", $i % 2);

    }
  }

  echo "</table>";

  echo "\n<small>" . $printed_rows . " requests found</small>";
    }

    include("footers.php");
?>
