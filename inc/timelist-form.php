<?php
  include("html-format.php");
  include( "user-list.php" );
function nice_time( $in_time ) {
  /* does nothing yet... */
  return substr("$in_time", 2);
}
  if ( "$because" != "" )
    echo $because;

  if ( !isset($tlsort) ) $tlsort = $settings->get('tlsort');
  if ( !isset($tlseq) ) $tlseq = $settings->get('tlseq');
  if ( "$tlsort" == "" ) $tlsort = "requester_name";
  $tlseq = strtoupper($tlseq);
  if ( "$tlseq" == "" ) {
      $tlseq = "ASC";
  }
  if ( "$tlseq" != "ASC" ) $tlseq = "DESC";
  $tlsort = eregi_replace( "[\\`]", "", $tlsort);
  $settings->set('tlsort', $tlsort);
  $settings->set('tlseq', $tlseq);

  if ( isset($user_no) ) $user_no = intval($user_no);
  if ( isset($work_for) ) $work_for = intval($work_for);

  if (  "$style" != "stripped" ) {
    echo "<form method=get action=\"$base_url/form.php\">\n";
    echo "<input type=hidden value=\"timelist\" name=f>\n";
    echo "<table border=0 cellpadding=0 cellspacing=2 align=center class=row0 style=\"border: 1px dashed #aaaaaa;\"><tr><td><table border=0 cellpadding=0 cellspacing=0 width=100%><tr>\n";
    echo "<td class=smb>Find:</td>\n";
    printf("<td class=sml><input class=sml type=text size=\"10\" name=search_for value=\"%s\"></td>\n", htmlspecialchars($search_for));

    if ( is_member_of('Admin','Support', 'Manage') ) {
      $user_list = "<option value=\"\">--- Any Requester ---</option>" . get_user_list( "", $user_org_code, "$requested_by" );
      echo "<td class=smb>&nbsp;&nbsp;Request&nbsp;By:</td><td class=sml><select class=sml name=requested_by>$user_list</select></td>\n";
    }

    if ( is_member_of('Admin','Support') ) {
      $user_list = "<option value=\"\">--- All Users ---</option>" . get_user_list( "Support", "", $user_no );
    }
    echo "<td class=smb align=right>&nbsp;&nbsp;Work&nbsp;By:</td><td class=sml><select class=sml name=user_no>$user_list</select></td>\n";

    printf("<td align=right><input type=checkbox value=1 name=uncharged%s></td><td align=left class=smb><label for=uncharged>Uncharged</label></td>\n", ("$uncharged"<>"" ? " checked" : ""));
    printf("<td align=right><input type=checkbox value=1 name=charge%s></td><td align=left class=smb><label for=charge>Charge</label></td>\n", ("$charge"<>"" ? " checked" : ""));
    echo "</tr></table></td>\n<tr><td><table border=0 cellpadding=0 cellspacing=0 width=100%>\n";
    include("system-list.php");
    if ( is_member_of('Admin','Support') )
      $system_list = get_system_list( "", "$system_code", 35);
    else
      $system_list = get_system_list( "CES", "$system_code", 35);
    echo "<td class=smb>System:</td><td class=sml><font size=1><select class=sml name=system_code><option value=\"\">--- All Systems ---</option>$system_list</select></font></td>\n";

    if ( is_member_of('Admin','Support') ) {
      include( "organisation-list.php" );
      $orglist = "<option value=\"\">--- All Organisations ---</option>\n" . get_organisation_list( "$org_code", 35 );
      echo "<td class=smb>&nbsp; &nbsp;Organisation:</td><td class=sml><select class=sml name=\"org_code\">\n$orglist</select></td>\n";
    }
    echo "<td align=left><input type=submit class=submit alt=go id=go value=\"GO>>\"name=go></td>\n";
    echo "</tr></table></td></tr>
<tr><td><table border=0 cellspacing=0 cellpadding=0 width=100%><tr valign=middle>
<td class=smb align=right>Work&nbsp;From:</td>
<td nowrap class=smb><input type=text size=10 name=from_date class=sml value=\"$from_date\">
<a href=\"javascript:show_calendar('forms[0].from_date');\" onmouseover=\"window.status='Date Picker';return true;\" onmouseout=\"window.status='';return true;\"><img valign=middle src=\"/$images/date-picker.gif\" border=0></a>
</td>

<td class=smb align=right>&nbsp;To:</td>
<td nowrap class=smb><input type=text size=10 name=to_date class=sml value=\"$to_date\">
<a href=\"javascript:show_calendar('forms[0].to_date');\" onmouseover=\"window.status='Date Picker';return true;\" onmouseout=\"window.status='';return true;\"><img valign=middle src=\"/$images/date-picker.gif\" border=0></a>
</td>
<td class=smb align=right>&nbsp;Type:</td>
<td nowrap class=smb><select name=\"type_code\" class=sml><option value=\"\">-- All Types --</option>$request_types</select></td>
</tr></table></td>
</tr>
</table>\n</form>\n";
  }

  $numcols = 7;
  if ( "$search_for$system_code " != "" ) {
    $query = "SELECT request.*, organisation.*, request_timesheet.*, ";
    $query .= " worker.fullname AS worker_name, requester.fullname AS requester_name";
    $query .= " FROM request, usr AS worker, usr AS requester, organisation, request_timesheet ";
    $query .= " WHERE request_timesheet.request_id = request.request_id";
    $query .= " AND worker.user_no = work_by_id ";
    $query .= " AND requester.user_no = requester_id ";
    $query .= " AND organisation.org_code = requester.org_code ";

    if ( isset($user_no) && $user_no > 0 ) {
      $query .= " AND work_by_id=$user_no ";
    }

    if ( "$requested_by" <> "" ) {
      $query .= " AND requester_id=$requested_by ";
    }
    if ( "$search_for" <> "" ) {
      $query .= " AND work_description ~* '$search_for' ";
    }
    if ( "$system_code" <> "" ) {
      $query .= " AND request.system_code='$system_code' ";
    }
    if ( isset($org_code) && $org_code > 0 ) {
      $numcols--;  // No organisation column
      $query .= " AND requester.org_code='$org_code' ";
    }
    if ( "$from_date" != "" ) {
      $query .= " AND request_timesheet.work_on::date >= '$from_date'::date ";
    }
    if ( "$to_date" != "" ) {
      $query .= " AND request_timesheet.work_on::date <= '$to_date'::date ";
    }

    if ( "$uncharged" != "" ) {
      $numcols++;  // No charged on column
      if ( "$charge" != "" )
        $query .= " AND request_timesheet.ok_to_charge=TRUE ";
      $query .= " AND request_timesheet.work_charged IS NULL ";
    }
    $query .= " ORDER BY $tlsort $tlseq ";
    if ( !isset($maxresults) || intval($maxresults) == 0 ) $maxresults = 1000;
    $query .= " LIMIT $maxresults ";
    $result = awm_pgexec( $dbconn, $query, 'timelist', FALSE, 7 );
    if ( $result ) {

      // Build up the column header cell, with %s gaps for the sort, sequence and sequence image
      $header_cell = "<th class=cols><a class=cols href=\"$PHP_SELF?f=$form&tlsort=%s&tlseq=%s";
      if ( isset($org_code) ) $header_cell .= "&org_code=$org_code";
      if ( isset($system_code) ) $header_cell .= "&system_code=$system_code";
      if ( isset($search_for) ) $header_cell .= "&search_for=$search_for";
      if ( isset($inactive) ) $header_cell .= "&inactive=$inactive";
      if ( isset($requested_by) ) $header_cell .= "&requested_by=$requested_by";
      if ( isset($user_no) ) $header_cell .= "&user_no=$user_no";
      if ( isset($uncharged) ) $header_cell .= "&uncharged=$uncharged";
      if ( isset($from_date) ) $header_cell .= "&from_date=$from_date";
      if ( isset($to_date) ) $header_cell .= "&to_date=$to_date";
      if ( isset($type_code) ) $header_cell .= "&type_code=$type_code";
      if ( isset($incstat) && is_array( $incstat ) ) {
        reset($incstat);
        while( list($k,$v) = each( $incstat ) ) {
          $header_cell .= "&incstat[$k]=$v";
        }
      }
      if ( "$qry" != "" ) $header_cell .= "&qry=$qry";
      if ( "$style" != "" ) $header_cell .= "&style=$style";
      if ( "$format" != "" ) $header_cell .= "&format=$format";
      $header_cell .= "\">%s";      // %s for the Cell heading
      $header_cell .= "%s</th>";    // %s For the image

      function column_header( $ftext, $fname ) {
        global $tlsort, $tlseq, $header_cell, $images;
        if ( "$tlsort" == "$fname" ) {
          $fseq = ( "$tlseq" == "DESC" ? "ASC" : "DESC");
          $seq_image .= "&nbsp;<img border=0 src=\"/$images/sort-$tlseq.png\">";
        }
        printf( $header_cell, $fname, $fseq, $ftext, $seq_image );
      }
      function header_row() {
        echo "<tr>\n";
        column_header("Work For", "requester_name");
        if ( "$GLOBALS[org_code]" == "" )
          column_header("Org.", "abbreviation" );
        column_header("WR&nbsp;#", "request_timesheet.request_id" );
        column_header("Done On", "work_on" );
        column_header("Duration", "work_quantity" );
        column_header("Rate", "work_rate");
        column_header("Done By", "worker_name" );
        if ( "$GLOBALS[uncharged]" == "" )
          column_header("Charged on", "work_charged");
        column_header("Description", "work_description" );
        column_header("Request", "brief" );
        column_header("OK to charge", "" );
        column_header("Invoice", "charged_details" );
        column_header("Amount", "charged_amount" );
        column_header("Charged On", "work_charged" );
        echo "</tr>";
      }
      if (  "$style" != "stripped" ) {
        echo "<p><small>&nbsp;" . pg_NumRows($result) . " timesheets found\n";
        if ( pg_NumRows($result) == $maxresults ) echo " (limit reached)";
        if ( "$uncharged" != "" ) {
          printf( "<form enctype=\"multipart/form-data\" method=post action=\"%s%s\">\n", $REQUEST_URI, ( ! strpos( $REQUEST_URI, "uncharged" ) ? "&uncharged=1" : ""));
        }
      }
      echo "<table border=\"0\" cellspacing=1 align=center>\n";
      header_row();

      $grand_total = 0.0;
      $total_hours = 0.0;

      // Build table of organisations found
      for ( $i=0; $i < pg_NumRows($result); $i++ ) {
        $timesheet = pg_Fetch_Object( $result, $i );

        $grand_total += doubleval( $timesheet->work_quantity * $timesheet->work_rate );
        switch ( $timesheet->work_units ) {
          case 'hours': $total_hours += doubleval( $timesheet->work_quantity );  break;
          case 'days':  $total_hours += doubleval( $timesheet->work_quantity * 8 );  break;
        }

        printf( "<tr class=row%1d>\n", ($i % 2));

        echo "<td class=sml nowrap>$timesheet->requester_name</td>\n";
        if ( "$GLOBALS[org_code]" == "" )
          echo "<td class=sml nowrap>$timesheet->abbreviation</td>\n";
        echo "<td class=sml align=center nowrap><a href=\"$base_url/wr.php?request_id=$timesheet->request_id\">$timesheet->request_id</a></td>\n";
        echo "<td class=sml>" . substr( nice_date($timesheet->work_on), 7) . "</td>\n";
        echo "<td class=sml>$timesheet->work_quantity $timesheet->work_units</td>\n";
        echo "<td class=sml align=right>$timesheet->work_rate&nbsp;</td>\n";
        echo "<td class=sml>$timesheet->worker_name</td>\n";
        if ( "$timesheet->work_charged" == "" ) {
          if ( "$uncharged" == "" ) echo "<td class=sml>uncharged</td>";
        }
        else
          echo "<td class=sml>" . substr( nice_date($timesheet->work_charged), 7) . "</td>";
        echo "<td class=sml>" . html_format( $timesheet->work_description) . "</td>";

        if ( "$uncharged" != "" ) {
          echo "<td class=sml <a href=\"$base_url/wr.php?request_id=$timesheet->request_id\">$timesheet->brief</a></td>\n";
          echo "<td class=sml align=right>";
          printf("<input type=\"checkbox\" value=\"1\" id=\"$timesheet->timesheet_id\" name=\"chg_ok[$timesheet->timesheet_id]\"%s>", ( "$timesheet->ok_to_charge" == "t" ? " checked" : ""));
          printf("<input type=hidden name=\"chg_worker[$timesheet->timesheet_id]\" value=\"%s\">", htmlspecialchars($timesheet->worker_name));
          printf("<input type=hidden name=\"chg_desc[$timesheet->timesheet_id]\" value=\"%s\">", htmlspecialchars($timesheet->work_description));
          printf("<input type=hidden name=\"chg_request[$timesheet->timesheet_id]\" value=\"%s\">", htmlspecialchars($timesheet->request_id));
          printf("<input type=hidden name=\"chg_requester[$timesheet->timesheet_id]\" value=\"%s\">", htmlspecialchars($timesheet->requester_name));
          echo "</td>\n";
          echo "<td class=sml><font size=2><input type=text size=6 name=\"chg_inv[$timesheet->timesheet_id]\" value=\"\"></font>&nbsp;</td>\n";
          echo "<td class=sml><font size=2><input type=text size=8 name=\"chg_amt[$timesheet->timesheet_id]\" value=\"\"></font>&nbsp;</td>\n";
          echo "<td class=sml><font size=2><input type=text size=10 name=\"chg_on[$timesheet->timesheet_id]\" value=\"" . date( "d/m/Y" ) . "\"></font>&nbsp;</td>\n";
        }
        echo "</tr>\n";
      }
      if ( "$uncharged" != "" ) {
        echo "<tr><td colspan=$numcols><input type=submit class=submit alt=\"apply changes\" name=submit value=\"Apply Charges\"></td></tr>\n";
        echo "</form>\n";
      }
      printf( "<tr class=row%1d>\n", ($i % 2));
      printf( "<td align=right colspan=" . ("$org_code" == "" ? "4" : "3" ) . ">%9.2f hours</td>\n", $total_hours);
      printf( "<th colspan=2 align=right>%9.2f</td>\n", $grand_total);
      echo "<td colspan=2>&nbsp;</td></tr>\n";
      echo "</table>\n";
    }
  }
  echo "</table></form>\n";

    if ( is_member_of('Admin','Support') && ( "$style" != "stripped" )) {
      $this_page = "$PHP_SELF?f=$form&style=%s&format=%s";
      if ( "$qry" != "" ) $this_page .= "&qry=$qry";
      if ( "$search_for" != "" ) $this_page .= "&search_for=$search_for";
      if ( "$org_code" != "" ) $this_page .= "&org_code=$org_code";
      if ( "$system_code" != "" ) $this_page .= "&system_code=$system_code";
      if ( isset($requested_by) ) $this_page .= "&requested_by=$requested_by";
      if ( isset($user_no) ) $this_page .= "&user_no=$user_no";
      if ( isset($from_date) ) $this_page .= "&from_date=$from_date";
      if ( isset($to_date) ) $this_page .= "&to_date=$to_date";
      if ( isset($type_code) ) $this_page .= "&type_code=$type_code";
      if ( isset($uncharged) ) $this_page .= "&uncharged=$uncharged";
      if ( "$style" != "" ) $this_page .= "&style=$style";
      if ( "$format" != "" ) $this_page .= "&format=$format";

      echo "<br clear=all><hr>\n<table cellpadding=5 cellspacing=5 align=right><tr><td>Rerun as report: </td>\n<td>\n";
      printf( "<a href=\"$this_page\" target=_new>Standard</a>\n", "stripped", "brief");
      if ( "$qry" != "" ) {
        echo "</td><td>|&nbsp; &nbsp; or <a href=\"$PHP_SELF?f=form&qs=complex&qry=$qry&action=delete\" class=sbutton>Delete</a> it\n";
      }
      echo "</td></tr></table>\n";
    }
?>
