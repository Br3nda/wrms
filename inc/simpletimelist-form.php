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
    echo "<input type=hidden value=\"simpletimelist\" name=f>\n";
    echo "<table border=0 cellpadding=0 cellspacing=2 align=center class=row0 style=\"border: 1px dashed #aaaaaa;\"><tr><td>\n";

    if ( is_member_of('Admin','Support') ) {
      $user_list = "<option value=\"\">--- All Users ---</option>" . get_user_list( "Support", "", $user_no );
    }
    echo "&nbsp;Work By:&nbsp;<select class=\"sml\" name=\"user_no\">$user_list</select>&nbsp;\n";

    $selected_0 = ( $date_restriction == 0 ) ? ' selected="selected"' : '';
    $selected_1 = ( $date_restriction == 1 ) ? ' selected="selected"' : '';
    $selected_2 = ( $date_restriction == 2 ) ? ' selected="selected"' : '';
    $selected_3 = ( $date_restriction == 3 ) ? ' selected="selected"' : '';
    $selected_a = ( $date_restriction == 'any' ) ? ' selected="selected"' : '';

    echo "&nbsp;Show from:&nbsp;<select name=\"date_restriction\">
  <option value=\"0\"$selected_0>This month</option>
  <option value=\"1\"$selected_1>Last month</option>
  <option value=\"2\"$selected_2>2 months ago</option>
  <option value=\"3\"$selected_3>3 months ago</option>
  <option value=\"all\"$selected_a>No date restriction</option>
</select>";

    echo "<input type=submit class=submit alt=go id=\"go\" name=\"go\" value=\"GO>>\"name=go>
</td>
</tr>
</table>\n</form>\n";
  }

  $numcols = 7;
  if ( isset($user_no) ) {
    $query = "SELECT request.*, organisation.*, request_timesheet.*, ";
    $query .= " requester.fullname AS requester_name";
    $query .= " FROM request, usr AS worker, usr AS requester, organisation, request_timesheet ";
    $query .= " WHERE request_timesheet.request_id = request.request_id";
    $query .= " AND worker.user_no = work_by_id ";
    $query .= " AND requester.user_no = requester_id ";
    $query .= " AND organisation.org_code = requester.org_code ";

    if ( isset($user_no) && $user_no > 0 ) {
      $query .= " AND work_by_id=$user_no ";
    }

    switch ( $date_restriction )
    {
      case ( '0' ):
    $from_date = date('Y/m/') . '1';
    $query .= " AND request_timesheet.work_on::date >= '$from_date'::date";
    break;
  case ( '1' ):
    $from_date = date('Y/m/', strtotime('-1 month')) . '1';
    $to_date = date('Y/m/', strtotime('-1 month')) . date('j', strtotime('-1 month'));
    $query .= " AND request_timesheet.work_on::date >= '$from_date'::date";
    $query .= " AND request_timesheet.work_on::date <= '$to_date'::date + '23:59'::interval";
    break;
  case ( '2' ):
    $from_date = date('Y/m/', strtotime('-2 months')) . '1';
    $to_date = date('Y/m/', strtotime('-2 months')) . date('j', strtotime('-2 months'));
    $query .= " AND request_timesheet.work_on::date >= '$from_date'::date";
    $query .= " AND request_timesheet.work_on::date <= '$to_date'::date + '23:59'::interval";
    break;
  case ( '3' ):
    $from_date = date('Y/m/', strtotime('-3 months')) . '1';
    $to_date = date('Y/m/', strtotime('-3 months')) . date('j', strtotime('-3 months'));
    $query .= " AND request_timesheet.work_on::date >= '$from_date'::date";
    $query .= " AND request_timesheet.work_on::date <= '$to_date'::date + '23:59'::interval";
    break;
  default:
    if ( isset($from_date) )
    {
      $query .= " AND request_timesheet.work_on::date >= '$from_date'::date";
    }
    if ( isset($to_date) )
    {
      $query .= " AND request_timesheet.work_on::date <= '$to_date'::date";
    }
    break;
  }

    $query .= " ORDER BY $tlsort $tlseq ";
    if ( !isset($maxresults) || intval($maxresults) == 0 ) $maxresults = 1000;
    $query .= " LIMIT 1000 ";
    $result = awm_pgexec( $dbconn, $query, 'simpletimelist', false, 7 );
    if ( $result ) {

      // Build up the column header cell, with %s gaps for the sort, sequence and sequence image
      $header_cell = "<th class=cols><a class=cols href=\"$PHP_SELF?f=$form&tlsort=%s&tlseq=%s";
      if ( isset($user_no) ) $header_cell .= "&amp;user_no=$user_no";
      if ( isset($from_date) ) $header_cell .= "&from_date=$from_date";
      if ( isset($to_date) ) $header_cell .= "&to_date=$to_date";
  if ( isset($date_restriction) ) $header_cell .= "&amp;date_restriction=$date_restriction";
      if ( isset($incstat) && is_array( $incstat ) ) {
        reset($incstat);
        while( list($k,$v) = each( $incstat ) ) {
          $header_cell .= "&incstat[$k]=$v";
        }
      }
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
        if ( "$GLOBALS[uncharged]" == "" )
          column_header("Charged on", "work_charged");
        column_header("Description", "work_description" );
        column_header("Request", "brief" );
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

  $requests = array();

      // Build table of organisations found
      for ( $i=0; $i < pg_NumRows($result); $i++ ) {
        $timesheet = pg_Fetch_Object( $result, $i );

        $grand_total += doubleval( $timesheet->work_quantity * $timesheet->work_rate );
        switch ( $timesheet->work_units ) {
          case 'hours': $total_hours += doubleval( $timesheet->work_quantity );  break;
          case 'days':  $total_hours += doubleval( $timesheet->work_quantity * 8 );  break;
        }

    $requests[$timesheet->request_id]['hours'] += $timesheet->work_quantity;
    $requests[$timesheet->request_id]['name'] = $timesheet->brief;

        printf( "<tr class=row%1d>\n", ($i % 2));

        echo "<td class=sml nowrap>$timesheet->requester_name</td>\n";
        if ( "$GLOBALS[org_code]" == "" )
          echo "<td class=sml nowrap>$timesheet->abbreviation</td>\n";
        echo "<td class=sml align=center nowrap><a href=\"$base_url/wr.php?request_id=$timesheet->request_id\">$timesheet->request_id</a></td>\n";
        echo "<td class=sml>" . substr( nice_date($timesheet->work_on), 7) . "</td>\n";
        echo "<td class=sml>$timesheet->work_quantity $timesheet->work_units</td>\n";
        echo "<td class=sml align=right>$timesheet->work_rate&nbsp;</td>\n";

    if ( "$timesheet->work_charged" == "" ) {
          if ( "$uncharged" == "" ) echo "<td class=sml>uncharged</td>";
        }
        else
          echo "<td class=sml>" . substr( nice_date($timesheet->work_charged), 7) . "</td>";
        echo "<td class=sml>" . html_format( $timesheet->work_description) . "</td>";

        echo "<td class=sml <a href=\"$base_url/wr.php?request_id=$timesheet->request_id\">$timesheet->brief</a></td>\n";
        echo "</tr>\n";
      }

      printf( "<tr class=row%1d>\n", ($i % 2));
      printf( "<td align=right colspan=" . ("$org_code" == "" ? "4" : "3" ) . ">%9.2f hours</td>\n", $total_hours);
      printf( "<th colspan=2 align=right>%9.2f</td>\n", $grand_total);
      echo "<td colspan=3>&nbsp;</td></tr>\n";
      echo "</table>\n";
    }
  }
  echo "</table></form>\n";


  //
  // Dump the table grouping everything by work request
  //
  if ( isset($requests) && is_array($requests) )
  {
  echo "<table border=0 cellpadding=0 cellspacing=\"1\" align=center class=\"row0\" width=\"65%\" style=\"border: 1px dashed #aaaaaa;\"><tr><td>\n";
  echo '<tr><th class="cols">Request #</th><th class="cols">Request Name</th><th class="cols">Hours worked</th></tr>';
  $hours_total = 0.0;

  foreach ( $requests as $id => $request )
  {
    echo '<tr class="row' . $row++ % 2 . '"><td>' . $id . '</td><td>' . $request['name'] . '</td><td align="right">' . $request['hours'] . "</td></tr>\n";
    $hours_total += $request['hours'];
  }
  // Summary row
  echo '<tr><th class="cols">' . sizeof($requests) . ' requests</th><th class="cols" align="right" colspan="2">' . $hours_total . " hours</th></tr>\n";
  echo '</table>';
  //echo '<pre>' . print_r($requests, true) . '</pre>';
  }

    if ( is_member_of('Admin','Support') && ( "$style" != "stripped" )) {
      $this_page = "$PHP_SELF?f=$form&style=%s&format=%s";
      if ( isset($user_no) ) $this_page .= "&user_no=$user_no";
      if ( isset($from_date) ) $this_page .= "&from_date=$from_date";
      if ( isset($to_date) ) $this_page .= "&to_date=$to_date";
      if ( "$style" != "" ) $this_page .= "&style=$style";
      if ( "$format" != "" ) $this_page .= "&format=$format";
  if ( isset($date_restriction) ) $this_page .= "&amp;date_restriction=$date_restriction";

      echo "<br clear=all><hr>\n<table cellpadding=5 cellspacing=5 align=right><tr><td>Rerun as report: </td>\n<td>\n";
      printf( "<a href=\"$this_page\" target=_new>Standard</a>\n", "stripped", "brief");
      if ( "$qry" != "" ) {
        echo "</td><td>|&nbsp; &nbsp; or <a href=\"$PHP_SELF?f=form&qs=complex&qry=$qry&action=delete\" class=sbutton>Delete</a> it\n";
      }
      echo "</td></tr></table>\n";
    }
?>
