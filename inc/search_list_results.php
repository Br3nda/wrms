<?php

  $qry = new PgQuery( $search_query );
  $result = $qry->Exec("SearchQuery");

  if ( "$style" != "stripped" ) {
    if ( $result && $qry->rows > 0 ) {
      echo "\n<small>$qry->rows requests found";
      if ( isset($savedquery) && $savedquery != "" ) echo " for <b>$savedquery</b>";
      echo "</small>";
    }
    else {
      echo "\n<p><small>No requests found</small></p>";
    }
  }

  if ( "$style" != "stripped" || ("$style" == "stripped" && "$format" == "edit")) {
    $this_page = "$PHP_SELF?style=%s&format=%s";
    if ( isset($savedquery) ) $usavedquery = str_replace('%','%%',urlencode($savedquery));
    if ( "$savedquery" != "" ) $this_page .= "&savedquery=$usavedquery";
    if ( "$search_for" != "" ) $this_page .= "&search_for=" . urlencode($search_for);
    if ( "$org_code" != "" ) $this_page .= "&org_code=$org_code";
    if ( "$system_code" != "" ) $this_page .= "&system_code=$system_code";
    if ( isset($inactive) ) $this_page .= "&inactive=$inactive";
    if ( isset($requested_by) ) $this_page .= "&requested_by=$requested_by";
    if ( isset($interested_in) ) $this_page .= "&interested_in=$interested_in";
    if ( isset($allocated_to) ) $this_page .= "&allocated_to=$allocated_to";
    if ( isset($from_date) ) $this_page .= "&from_date=$from_date";
    if ( isset($to_date) ) $this_page .= "&to_date=$to_date";
    if ( isset($type_code) ) $this_page .= "&type_code=$type_code";
    if ( isset($incstat) && is_array( $incstat ) ) {
      reset($incstat);
      while( list($k,$v) = each( $incstat ) ) {
        $this_page .= "&incstat[$k]=$v";
      }
    }
  }

  if ( "$format" == "edit" && "$because" != "")
    echo $because;

  if ( "$format" == "edit" ) //encloses any Brief (editable) reports in a form tag to enable submit form functionality
    printf ("<form action=\"$this_page\" method=\"post\">\n", "stripped", "edit");

  if ( $style == "stripped" ) {
    echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"2\" width=\"100%\">\n<tr>\n";
    echo "<th class=cols style=\"text-align: left\">$savedquery</th>";
    echo "<th class=cols style=\"text-align: right\">" . pg_NumRows($result) . " requests at " . date("H:i j M y") . "</th>";
    echo "</tr></table>\n";
  }
  echo "<table border=\"0\" width=\"100%\">\n";

  $show_notes = ($format == "ultimate" || $format == "detailed" );
  $show_details = ($format == "ultimate" || $format == "detailed" || "$format" == "activity" || "$format" == "quotes" );
  $show_quotes = ( $format == "ultimate" || "$format" == "activity" || "$format" == "quotes" );
  $show_work = ( ($format == "ultimate" || "$format" == "activity" ) &&  is_member_of('Admin', 'Support' ) );
  if ( $show_details ) {
    include("html-format.php");
  }
  else
    header_row();


  if ( $result ) {
    $grand_total = 0.0;
    $grand_qty_total = 0.0;

    // Build table of requests found
    $i=0;
    while ( $thisrequest = $qry->Fetch() ) {

      if ( "$format" == "edit" ) {
        //We set some flags if the user is editing things on the listing
        // - $status_edit flags whether or not the logged in user has permissions to edit the status for the current request to be listed
        // - $active_edit flags whether or not the logged in user has permissions to edit the active field for the current request to be listed

        list($status_edit, $active_edit) = RequestEditPermissions($thisrequest->request_id); //Calls function to find out and return the editing permissions for the current request

        if ( $i == 0 )  //if statement to initialise counter to be used as the
          $EditableRequests_count = 0; // position no in the EditableRequests array that is returned when
          //a Brief (editable) report is submitted back
          //with one or more editable requests in its list

      }

      if ( $show_details ) header_row();
      data_row($thisrequest, $i);

      if ( $show_details ) {
        printf( "<tr class=row%1d>\n", $i % 2);
        echo "<td colspan=7>" . html_format($thisrequest->detailed) . "</td>\n";
        echo "</tr>\n";
      }
      if ( $show_quotes ) {
        $subquery = "SELECT *, to_char( quoted_on, 'DD/MM/YYYY') AS nice_date ";
        $subquery .= "FROM request_quote, usr ";
        $subquery .= "WHERE request_id = $thisrequest->request_id ";
        $subquery .= "AND usr.user_no = request_quote.quote_by_id ";
        $subquery .= "ORDER BY request_id, quoted_on ";
        $total = 0.0;
        $qty_total = 0.0;
        $subres = awm_pgexec( $dbconn, $subquery, "requestlist" );
        for ( $j=0; $subres && $j < pg_NumRows($subres); $j++ ) {
          $thisquote = pg_Fetch_Object( $subres, $j );
          printf( "<tr class=row%1d valign=top>\n", $i % 2);
          echo "<td>$thisquote->nice_date</td>\n";
          echo "<td>$thisquote->fullname</td>\n";
          echo "<td colspan=4><b>$thisquote->quote_brief</b><br><hr>\n";
          echo html_format($thisquote->quote_details);
          echo "</td>\n";
          printf("<td align=right>%9.2f &nbsp; %s</td>\n", $thisquote->quote_amount, $thisquote->quote_units);
          echo "</tr>\n";
        }
      }
      if ( $show_notes ) {
        $subquery = "SELECT *, to_char( note_on, 'DD/MM/YYYY') AS nice_date ";
        $subquery .= "FROM request_note, usr ";
        $subquery .= "WHERE request_id = $thisrequest->request_id ";
        $subquery .= "AND usr.user_no = request_note.note_by_id ";
        $subquery .= "ORDER BY request_id, note_on ";
        $subres = awm_pgexec( $dbconn, $subquery, "requestlist" );
        for ( $j=0; $subres && $j < pg_NumRows($subres); $j++ ) {
          $thisnote = pg_Fetch_Object( $subres, $j );
          printf( "<tr class=row%1d valign=top>\n", $i % 2);
          echo "<td>$thisnote->nice_date</td>\n";
          echo "<td>$thisnote->fullname</td>\n";
          echo "<td colspan=5>" . html_format($thisnote->note_detail) . "</td>\n";
          echo "</tr>\n";
        }
      }
      if ( $show_work ) {
        $subquery = "SELECT *, to_char( work_on, 'DD/MM/YYYY') AS nice_date ";
        $subquery .= "FROM request_timesheet, usr ";
        $subquery .= "WHERE request_id = $thisrequest->request_id ";
        $subquery .= "AND usr.user_no = request_timesheet.work_by_id ";
        $subquery .= "ORDER BY request_id, work_on ";
        $total = 0.0;
        $qty_total = 0.0;
        $subres = awm_pgexec( $dbconn, $subquery, "requestlist" );
        for ( $j=0; $subres && $j < pg_NumRows($subres); $j++ ) {
          $thiswork = pg_Fetch_Object( $subres, $j );
          printf( "<tr class=row%1d valign=top>\n", $i % 2);
          echo "<td>$thiswork->nice_date</td>\n";
          echo "<td>$thiswork->fullname</td>\n";
          echo "<td colspan=2>$thiswork->work_description</td>\n";
          printf("<td align=right>%9.2f</td>\n", $thiswork->work_quantity);
          printf("<td align=right>%9.2f</td>\n", $thiswork->work_rate);
          $value = $thiswork->work_quantity * $thiswork->work_rate;
          $total += $value;
          $qty_total += $thiswork->work_quantity;
          printf("<td align=right>%9.2f</td>\n", $value);
          echo "</tr>\n";
        }
        if ( $j > 0 )
          printf( "<tr class=row%1d>\n<td colspan=4>&nbsp; &nbsp; &nbsp; Request #$thisrequest->request_id total</td>\n<td align=right>%9.2f &nbsp; </td><td>&nbsp;</td><td align=right>%9.2f &nbsp; </td>\n</tr>\n", $i % 2, $qty_total, $total);
        $grand_total += $total;
        $grand_qty_total += $qty_total;
      }

      if ( $show_details )
        echo "<tr class=row3>\n<td colspan=7>&nbsp;</td></tr>\n";

      $i++;
      if ( (isset($status_edit) && $status_edit) || (isset($active_edit) && $active_edit ) ) //Maintains the $EditableRequests_count counter
       $EditableRequests_count++;
    }
  }

  // At the bottom of the listing we display some totals in some cases
  if ( $show_work )
    printf( "<tr class=row%1d>\n<th align=left colspan=4>Grand Total</th>\n<th align=right>%9.2f &nbsp; </th><th>&nbsp;</th><th align=right>%9.2f &nbsp; </th>\n</tr>\n", $i % 2, $grand_qty_total, $grand_total);

  if ( "$format" == "edit" ) {
    if ( $EditableRequests_count == 0 ) {
      echo "<tr><td align=\"center\" colspan=\"8\"><br>You do not have permission to edit any of the requests in this report.<br>&nbsp;</td></tr>";
    }
    else {
      echo "<tr><td align=\"left\" colspan=\"4\"><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=reset class=\"submit\" value=\"Reset Attributes\"><br>&nbsp;</td><td align=\"right\" colspan=\"4\"><br><input type=submit value=\"Apply Changes\" name=\"submitBriefEditable\" class=\"submit\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>&nbsp;<td></tr>";
      if ( isset($ChangedRequests_count) )
        echo "<tr><td align=\"center\" colspan=\"8\"><br>" . ($ChangedRequests_count == 0 ? "No" : "$ChangedRequests_count" ) . " request". ( ($ChangedRequests_count > 1 || $ChangedRequests_count == 0) ? "s" : "" ) . " updated.<br>&nbsp;</td></tr>";
    }
  }

  echo "</table>\n";

  if ( "$format" == "edit" )  //end form enclosing Brief (editable) report
     echo "</form>\n";

  //$this_page string build code block was here
  if ( "$style" != "stripped" ) {
    echo "<br clear=all><hr>\n<table cellpadding=5 cellspacing=5 align=right><tr><td>Rerun as report: </td>\n<td>\n";
    printf( "<a href=\"$this_page\" target=_new>Brief</a>\n", "stripped", "brief");
    printf( " &nbsp;|&nbsp; <a href=\"$this_page&maxresults=5000\">All Rows</a>\n", $style, $format);
    if ( is_member_of('Admin', 'Support') ) {
      printf( " &nbsp;|&nbsp; <a href=\"$this_page\" target=_new>Activity</a>\n", "stripped", "activity");
    }
    printf( " &nbsp;|&nbsp; <a href=\"$this_page\" target=_new>Detailed</a>\n", "stripped", "detailed");
    if ( is_member_of('Admin', 'Support', 'Manage') ) {
      printf( " &nbsp;|&nbsp; <a href=\"$this_page\" target=_new>Quotes</a>\n", "stripped", "quotes");
    }
    if ( is_member_of('Admin', 'Support') ) {
      printf( " &nbsp;|&nbsp; <a href=\"$this_page\" target=_new>Ultimate</a>\n", "stripped", "ultimate");
    }
    if ( is_member_of('Admin', 'Support') ) {
      printf( " &nbsp;|&nbsp; <a href=\"$this_page\" target=_new>Brief (editable)</a>\n", "stripped", "edit");  //uses the format = edit setting in this link for the Brief (editable) report
    }
    if ( "$savedquery" != "" ) {
      echo "</td><td>|&nbsp; &nbsp; or <a href=\"$PHP_SELF?qs=complex&savedquery=".urlencode($savedquery)."&action=delete\" class=sbutton>Delete</a> it\n";
    }
    echo "</td></tr></table>\n";
  }

?>