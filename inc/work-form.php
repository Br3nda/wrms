<?php
  include("$base_dir/inc/html-format.php");
function nice_time( $in_time ) {
  /* does nothing yet... */
  return substr("$in_time", 2);
}
  if ( "$because" != "" )
    echo $because;
  else if ( ! $plain ) {
//    ? ><P class=helptext>Use this form to maintain organisations who may have requests associated
// with them.</P><?php
  }
// <P class=helptext>This page lists timesheets.</P>
?>

<?php
  if ( "$search_for$system_code " != "" ) {
    $query = "SELECT request.*, organisation.*, request_timesheet.*, ";
    $query .= " worker.fullname AS worker_name, requester.fullname AS requester_name, ";
    $query .= " last_status_on(request.request_id) AS status_on, last_status AS status, get_status_desc(last_status) AS status_desc";
    $query .= " FROM request, usr AS worker, usr AS requester, organisation, request_timesheet ";
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
    if ( "$uncharged" != "" ) {
//      if ( "$charge" != "" )
//        $query .= " AND request_timesheet.ok_to_charge=TRUE ";
//      $query .= " AND request_timesheet.work_charged IS NULL ";
//      $query .= " ORDER BY org_code, work_on";
//      $query .= " $order_by ";
    }
    else {
 //     $query .= " ORDER BY organisation.org_code, request_timesheet.request_id, request_timesheet.work_on";
 //     $query .= " $order_by ";
 //     $query .= " LIMIT 100 ";
    }

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
    $query .= " LIMIT 100 ";

    $result = awm_pgexec( $wrms_db, $query );
    if ( ! $result ) {
      $error_loc = "timelist-form.php";
      $error_qry = "$query";
      include("inc/error.php");
    }
    else {
      echo "<FORM METHOD=POST ACTION=\"$REQUEST_URI\">\n";
      echo "<table border=0 align=center>\n";

      // Build table of organisations found
      for ( $i=0; $i < pg_NumRows($result); $i++ ) {
        $timesheet = pg_Fetch_Object( $result, $i );

        if ( ($i % 2) == 0 ) echo "<tr class=bgcolor0>";
 //       if(floor($i/2)-($i/2)==0) echo "<tr bgcolor=$colors[6]>";
        else echo "<tr class=bgcolor1>";
 //       else echo "<tr bgcolor=$colors[7]>";

//        echo "<td class=sml valign=top>$timesheet->requester_name ($timesheet->abbreviation, #$timesheet->debtor_no)</td>\n";
        echo "<td class=sml valign=top>$timesheet->requester_name</td>\n";
 	echo "<td class=sml valign=top>$timesheet->abbreviation</td>\n";
 	echo "<td class=sml valign=top>$timesheet->debtor_no</td>\n";
        echo "<td class=sml valign=top><a href=\"/request.php?request_id=$timesheet->request_id\">$timesheet->request_id</a></td>\n";
/*
        $sub_query = "SELECT status_on, status_code, lookup_code.lookup_desc ";
        $sub_query .= "  FROM request_status, lookup_code";
        $sub_query .= "   WHERE request_status.request_id = $timesheet->request_id ";
        $sub_query .= "   AND lookup_code.source_table = 'request_status'";
        $sub_query .= "   AND lookup_code.source_field = 'status_code'";
        $sub_query .= "   AND lookup_code.lookup_code = request_status.status_code";
        $sub_query .= "   ORDER BY status_on DESC LIMIT 1";
        $sub_query_result = awm_pgexec( $wrms_db, $sub_query );
        $sub_query_row = pg_Fetch_Object($sub_query_result, 0) ;
*/
        echo "<td class=sml valign=top>" ;
//          if ( pg_NumRows($sub_query_result) > 0 ) echo $sub_query_row->lookup_desc ;
        echo "$timesheet->status_desc";
        echo  "</td>\n";

        echo "<td class=sml valign=top>" ;
//          if ( pg_NumRows($sub_query_result) > 0 ) echo substr(nice_date($sub_query_row->status_on),7) ;
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
        echo "</td>";
   //     if ( "$timesheet->work_charged" == "" ) {
    //      if ( "$uncharged" == "" ) echo "<td class=sml>uncharged</td>";
     //   }
      //  else

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

      echo "<form method=post action=\"$REQUEST_URI\">\n";
      echo "<thead><tr>";

      echo "<th class=cols>Requested by";
      echo '<select class=sml name="filter[request.requester_id]">' . "\n";
      echo "<option class=sml value=''>All</option>\n";
      $select_query = "SELECT user_no, fullname FROM usr ORDER BY fullname";
      $select_result = awm_pgexec( $wrms_db, $select_query );
      for ( $i=0; $i < pg_NumRows($select_result); $i++ ) {
        $select_option = pg_Fetch_Object( $select_result, $i );
        echo "<option class=sml value='$select_option->user_no'";
        if ( $select_option->user_no == $filter["request.requester_id"] ) {
          echo ' selected';
        }
        echo ">$select_option->fullname</option>\n";
      }
      echo "</select>\n";
      echo "</th>\n";

      echo "<th class=cols>Org.";
      echo '<select class=sml name="filter[organisation.org_code]">' . "\n";
      echo "<option class=sml value=''>All</option>\n";
      $select_query = "SELECT org_code, abbreviation FROM organisation ORDER BY abbreviation";
      $select_result = awm_pgexec( $wrms_db, $select_query );
      for ( $i=0; $i < pg_NumRows($select_result); $i++ ) {
        $select_option = pg_Fetch_Object( $select_result, $i );
        echo "<option class=sml value='$select_option->org_code'";
        if ( $select_option->org_code == $filter["organisation.org_code"] ) {
          echo ' selected';
        }
        echo ">$select_option->abbreviation</option>\n";
      }
      echo "</select>\n";
      echo "</th>\n";

      echo '<th class=cols>Dbtr. No.';
      echo '<input TYPE="Image" src="images/down.gif" alt="Sort" BORDER="0" name="sort[organisation.org_code]" >';
      echo "</th>\n";

      echo '<th class=cols>WR No.<input TYPE="Image" src="images/down.gif" alt="Sort" BORDER="0" name="sort[request_timesheet.request_id]" ></th>';

      echo "<th class=cols>WR Status";
      echo '<select class=sml name="filter[request.last_status]">' . "\n";
      echo "<option class=sml value=''>All</option>\n";
      $select_query = "SELECT lookup_code, lookup_desc FROM lookup_code";
      $select_query .= " WHERE lookup_code.source_table = 'request' ";
      $select_query .= " AND lookup_code.source_field = 'status_code' ";
      $select_query .= " ORDER BY lookup_code.lookup_desc";
      $select_result = awm_pgexec( $wrms_db, $select_query );
      for ( $i=0; $i < pg_NumRows($select_result); $i++ ) {
        $select_option = pg_Fetch_Object( $select_result, $i );
        echo "<option class=sml value=\"'$select_option->lookup_code'\"";
        if ( "'" . $select_option->lookup_code . "'" == $filter["request.last_status"] ) {
          echo ' selected';
        }
        echo ">$select_option->lookup_desc</option>\n";
      }
      echo "</select>\n";
      echo "</th>\n";

      echo "<th class=cols>Status On</th>";
      echo "<th class=cols>WR Brief</th>";
      echo '<th class=cols>Work Description<input TYPE="Image" src="images/down.gif" alt="Sort" BORDER="0" name="sort[request_timesheet.work_description]" ></th>';
      echo "<th class=cols>Done By</th>";
      echo "<th class=cols>Done on</th>";
      echo "<th class=cols>Qty.</th>";
      echo "<th class=cols>Rate</th>";
      echo "<th class=cols>Charge Amount</th>";
      echo "<th class=cols>Ok to Charge</th>";
      echo "<th class=cols>Charged On</th>";
      echo "<th class=cols>Invoice No.</th>";
      echo "</tr>\n";
      echo "<tr>\n";
      echo "<td class=sml>\n";
      echo "</td>\n";
      echo "</tr>\n";
      echo "</thead>\n";

      echo "</form>\n";

      echo "</table>\n";
    }
  }
?>
</TABLE>
</FORM>

