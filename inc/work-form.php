<?php
  include("html-format.php");
function nice_time( $in_time ) {
  /* does nothing yet... */
  return substr("$in_time", 2);
}
  if ( "$because" != "" )
    echo $because;
?>

<?php
  if ( "$search_for$system_code " != "" ) {
    if ( !isset($maxresults) || intval($maxresults) == 0 ) $maxresults = 200;
    $query = "SELECT request.*, organisation.*, request_timesheet.*, ";
    $query .= " worker.fullname AS worker_name, requester.fullname AS requester_name, ";
    $query .= " last_status_on(request.request_id) AS status_on, last_status AS status, get_status_desc(last_status) AS status_desc ";
    $query .= " FROM request ";
    if ( ! is_member_of('Admin', 'Support') ) {
      $query .= "JOIN work_system USING (system_code) ";
      $query .= "JOIN system_usr ON (work_system.system_code = system_usr.system_code AND system_usr.user_no = $session->user_no) ";
    }
    $query .= ", usr AS worker, usr AS requester, organisation, request_timesheet ";
    $query .= " WHERE request_timesheet.request_id = request.request_id";
    $query .= " AND worker.user_no = work_by_id ";
    $query .= " AND requester.user_no = requester_id ";
    $query .= " AND organisation.org_code = requester.org_code ";

    if ( "$user_no" <> "" ) {
      $query .= " AND work_by_id=$user_no ";
    }

    if ( "$search_for" <> "" ) {
      $query .= " AND work_description ~* '$search_for' ";
    }
    if ( "$system_code" <> "" ) {
      $query .= " AND request.system_code='$system_code' ";
    }
    if ( "$org_code" <> "" ) {
      $query .= " AND requester.org_code='$org_code' ";
    }

    if ( "$after" != "" )
      $query .= " AND request_timesheet.work_on>'$after' ";
    if ( "$before" != "" )
      $query .= " AND request_timesheet.work_on<'$before' ";

    if( isset ($filter) && is_array($filter) ) {
      while( list( $k, $v ) = each ( $filter ) ) {
        if ($v != "") { $query .= " AND $k = $v "; }
      }
    }

    if( "$order_by" == "" && isset ($sort) && is_array($sort) ) {
      while( list( $k, $v ) = each ( $sort ) ) {
        $order_by = " ORDER BY $k";
      }
    }

    $query .= " $order_by ";
    $query .= " LIMIT $maxresults ";

    $result = awm_pgexec( $dbconn, $query );
    if ( ! $result ) {
      $error_loc = "work-form.php";
      $error_qry = "$query";
      include("error.php");
    }
    else {
      echo "<FORM METHOD=POST ACTION=\"$REQUEST_URI\">\n";
      echo "<table border=0 align=center>\n";

      // Build table of organisations found
      for ( $i=0; $i < pg_NumRows($result); $i++ ) {
        $timesheet = pg_Fetch_Object( $result, $i );

        if ( ($i % 2) == 0 ) echo "<tr class=bgcolor0>";
        else echo "<tr class=bgcolor1>";

        echo "<td class=sml valign=top>$timesheet->requester_name</td>\n";
        echo "<td class=sml valign=top>$timesheet->abbreviation</td>\n";
        echo "<td class=sml valign=top>$timesheet->debtor_no</td>\n";
        echo "<td class=sml valign=top><a href=\"/wr.php?request_id=$timesheet->request_id\">$timesheet->request_id</a></td>\n";

        echo "<td class=sml valign=top>" ;
        echo "$timesheet->status_desc";
        echo  "</td>\n";

        echo "<td class=sml valign=top>" ;
        echo "$timesheet->status_on";
        echo  "</td>\n";

        echo "<td class=sml valign=top>$timesheet->brief</td>\n";
        echo "<td class=sml valign=top>" . html_format( $timesheet->work_description) . "</td>\n";
        echo "<td class=sml valign=top>$timesheet->worker_name</td>\n";
        echo "<td class=sml valign=top nowrap>" . substr( nice_date($timesheet->work_on), 7) . "</td>\n";
        echo "<td class=sml valign=top>$timesheet->work_quantity $timesheet->work_units</td>\n";
        echo "<td class=sml valign=top align=right nowrap>$timesheet->work_rate</td>\n";
        echo "<td class=sml valign=top align=right nowrap>";
        if ( "$timesheet->work_charged" == "" ) {
          echo "<input type=text size=6 name=\"chg_amt[$timesheet->timesheet_id]\" value=\"" ;
          if ( "$timesheet->charged_amount" == "" ) echo $timesheet->work_rate*$timesheet->work_quantity;
          else echo $timesheet->charged_amount ;
          echo "\">";
        }
        else echo "$timesheet->charged_amount";
        echo "</td>\n";
        echo "<td class=sml valign=top>";
        if ( "$timesheet->work_charged" == "" ) {
          echo "<input type=checkbox value=1 name=\"chg_ok[$timesheet->timesheet_id]\"";
          if ( "$timesheet->ok_to_charge" == "t" ) echo " checked";
          echo ">";
        }
        echo "</td>\n";

        echo "<td class=sml valign=top>";
        if ( "$timesheet->work_charged" == "" && "$timesheet->ok_to_charge" == "t" ) {
          echo "<input name=\"chg_on[$timesheet->timesheet_id]\" type=text size=10 value=\"" . date("d/m/Y") . "\">";
        }
        else echo substr( nice_date($timesheet->work_charged), 7) ;
        echo "</td>\n";

        echo "<td class=sml valign=top>";
        if ( "$timesheet->work_charged" == "" && "$timesheet->ok_to_charge" == "t" ) {
          echo "<input name=\"chg_inv[$timesheet->timesheet_id]\" type=text size=6 >";
        }
        else echo $timesheet->charged_details ;
        echo "</td>\n";

        echo "</tr>\n";
      }
      if ( "$uncharged" != "" ) {
        echo "<tr><td class=mand colspan=6 align=center><input type=submit alt=\"apply changes\" name=submit value=\"Apply Charges\"></td></tr>\n";
        echo "</form>\n";
      }


      echo "<form method=post action=\"$REQUEST_URI\" name=\"options\">\n";
      echo   "<thead>";
      echo     "<tr>";
      echo       "<th class=cols>Requested by";
      echo         '<select class=sml name="filter[request.requester_id]">' . "\n";
      echo           "<option class=sml value=''>All</option>\n";
      $select_query = "SELECT user_no, fullname FROM usr ";
      if ( ! is_member_of('Admin', 'Support') ) {
        $select_query .= "WHERE usr.org_code = $session->org_code";
      }
      $select_query .= "ORDER BY fullname";
      $select_result = awm_pgexec( $dbconn, $select_query );
      for ( $i=0; $i < pg_NumRows($select_result); $i++ ) {
        $select_option = pg_Fetch_Object( $select_result, $i );
        echo "<option class=sml value='$select_option->user_no'";
        if ( $select_option->user_no == $filter["request.requester_id"] ) {
          echo ' selected';
        }
        echo ">$select_option->fullname</option>\n";
      }
      echo         "</select>\n";
      echo       "</th>\n";

      echo       "<th class=cols>Org.";
      echo         '<select class=sml name="filter[organisation.org_code]">' . "\n";
      echo           "<option class=sml value=''>All</option>\n";
      $select_query = "SELECT org_code, abbreviation FROM organisation ";
      if ( ! is_member_of('Admin', 'Support') ) {
        $select_query .= "WHERE organisation.org_code = $session->org_code";
      }
      $select_query .= "ORDER BY abbreviation";
      $select_result = awm_pgexec( $dbconn, $select_query );
      for ( $i=0; $i < pg_NumRows($select_result); $i++ ) {
        $select_option = pg_Fetch_Object( $select_result, $i );
        echo "<option class=sml value='$select_option->org_code'";
        if ( $select_option->org_code == $filter["organisation.org_code"] ) {
          echo ' selected';
        }
        echo ">$select_option->abbreviation</option>\n";
      }
      echo         "</select>\n";
      echo       "</th>\n";

      echo       '<th class=cols>Dbtr. No.';
      echo         '<input TYPE="Image" src="/$images/down.gif" alt="Sort" BORDER="0" name="sort[organisation.org_code]" >';
      echo       "</th>\n";

      echo       '<th class=cols>WR No.<input TYPE="Image" src="/'.$images.'/down.gif" alt="Sort" BORDER="0" name="sort[request_timesheet.request_id]" ></th>' . "\n";

      echo       "<th class=cols>WR Status";
      echo         '<select class=sml name="filter[request.last_status]">' . "\n";
      echo           "<option class=sml value=''>All</option>\n";
      $select_query = "SELECT lookup_code, lookup_desc FROM lookup_code";
      $select_query .= " WHERE lookup_code.source_table = 'request' ";
      $select_query .= " AND lookup_code.source_field = 'status_code' ";
      $select_query .= " ORDER BY lookup_code.lookup_desc";
      $select_result = awm_pgexec( $dbconn, $select_query );
      for ( $i=0; $i < pg_NumRows($select_result); $i++ ) {
        $select_option = pg_Fetch_Object( $select_result, $i );
        echo "<option class=sml value=\"'$select_option->lookup_code'\"";
        if ( "'" . $select_option->lookup_code . "'" == $filter["request.last_status"] ) {
          echo ' selected';
        }
        echo ">$select_option->lookup_desc</option>\n";
      }
      echo         "</select>\n";
      echo       "</th>\n";

      echo       "<th class=cols>Status On</th>\n";
      echo       "<th class=cols>WR Brief</th>\n";
      echo       '<th class=cols>';
      echo         '<table cellpadding=2 cellspacing=0 border=0>';
      echo           '<tr>';
      echo             '<td></td>';
      echo             '<td align="middle"><input TYPE="Image" src="/'.$images.'/up.gif" alt="Sort Ascending" BORDER="0" name="sort_asc[request_timesheet.work_description]" ></td>';
      echo           '</tr>';
      echo           '<tr>';
      echo             '<td><input TYPE="Image" src="/$images/hide.gif" alt="Hide Work Description" BORDER="0" name="hide[request_timesheet.work_description]" ></td>';
      echo             '<td>Work Description</td>';
      echo           '</tr>';
      echo           '<tr>';
      echo             '<td></td>';
      echo             '<td align="middle"><input TYPE="Image" src="/'.$images.'/down.gif" alt="Sort Descending" BORDER="0" name="sort[request_timesheet.work_description]" ></td>';
      echo           '</tr>';
      echo         '</table>';
      echo       "</th>\n";
      echo       "<th class=cols>Done By</th>\n";

      echo       '<th class=cols nowrap>Done on<br>';
      echo         '<input type="text" name="done_on_from_date" size=10 maxlength=10 class=sml >';
      echo         '<a href="javascript:show_calendar(\'options.done_on_from_date\');"';
      echo           ' onmouseover="window.status=\'Date Picker\';return true;"';
      echo           ' onmouseout="window.status=\'\';return true;">';
      echo           '<img valign="middle" src="/'.$images.'/date-picker.gif" border=0>';
      echo         '</a><br>';
      echo         '<input type="text" name="done_on_to_date" size=10 maxlength=10 class=sml >';
      echo         '<a href="javascript:show_calendar(\'options.done_on_to_date\');"';
      echo           ' onmouseover="window.status=\'Date Picker\';return true;"';
      echo           ' onmouseout="window.status=\'\';return true;">';
      echo           '<img valign="middle" src="/'.$images.'/date-picker.gif" border=0>';
      echo         '</a>';
      echo       "</th>\n";
      echo       "<th class=cols>Qty.</th>\n";
      echo       "<th class=cols>Rate</th>\n";
      echo       "<th class=cols>Charge Amount</th>\n";
      echo       "<th class=cols>Ok to Charge</th>\n";
      echo       "<th class=cols>Charged On</th>\n";
      echo       "<th class=cols>Invoice No.</th>\n";
      echo     "</tr>\n";
      echo   "</thead>\n";

      echo "</form>\n";

      echo "</table>\n";
    }
  }
?>
</TABLE>
</FORM>

