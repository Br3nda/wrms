<?php
  include("inc/always.php");
  include("inc/options.php");
  include("inc/code-list.php");
  include( "$base_dir/inc/user-list.php" );

  // Force some variables to have values.
  if ( !isset($format) ) $format = "";
  if ( !isset($style) ) $style = "";
  if ( !isset($qry) ) $qry = "";
  if ( !isset($qs) ) $qs = "";
  if ( !isset($org_code) ) $org_code = "";
  if ( !isset($system_code) ) $system_code = "";
  if ( !isset($search_for) ) $search_for = "";
  if ( !isset($interested_in) ) $interested_in = "";
  if ( !isset($allocated_to) ) $allocated_to = "";
  if ( !isset($type_code) ) $type_code = "";
  if ( !isset($inactive) ) $inactive = "";
  if ( !isset($user_no) ) $user_no = "";
  if ( !isset($requested_by) ) $requested_by = "";
  if ( !isset($from_date) ) $from_date = "";
  if ( !isset($to_date) ) $to_date = "";

//Uses a URL variable format = edit in order to indicate that the report should be in the Brief (editable) format

//------------------------------------------------------
// function RequestEditPermissions()
// This function is used to determine the editing permissions for the status and active
// fields of a request based on the particular request in question and the user that is logged in
// This function returns an array of two boolean values
// the first boolean value in the array is a flag for the edit permission
// of the status field of a request
// the second boolean value in the array is a flag for the edit permission
// of the active field of a request
//------------------------------------------------------
  function RequestEditPermissions($request_id)
  {
     //  This code was written by Simon and gets marked 3/10 - could do better.
     global $session, $wrms_db, $base_dir;
     $plain = FALSE;

     include("inc/getrequest.php");

     return array($statusable, $editable);
  }

//-----------------------------------------------------------
// function Process_Brief_editable_Requests()
// This function is used to process the returned changes (if any)
// from the Brief (editable) report. All editable request lines in the
// list are returned with their editable fields values whether or
// not they have been changed. All changed editable field values are written
// back into the database
//------------------------------------------------------------
  function Process_Brief_editable_Requests()
  {
     global $wrms_db, $EditableRequests, $session, $ChangedRequests_count, $because;

     if ( !isset($EditableRequests) )
        return;

     $count = count($EditableRequests);

     for ( $i = 0 ; $i < $count ; $i++ ) //Loop through and process each requested in the returned array $EditableRequests
     {
        //$ReturnedRequestId - contains the request id of the current request to update
        //$ReturnedRequestStatus - if set stores the status value of the current request to be updated to, if unset then indicates that the current request status is not allowed to be edited by the logged in user or its value hasn't been changed
        //$ReturnedRequestActivePermit - if TRUE then indicates that the current request active field is allowed to be edited by the logged in user, if FALSE it indicates that the current request active field is not allowed to be edited by the logged in user or that the active field value hasn't been changed
        //$ReturnedRequestActive - if 1 then means the current request active field should be updated to 't', if 0 then means the current request active field should be updated to 'f'
        //Note that the value for $ReturnedRequestActive is not valid unless $ReturnedRequestActivePermit is TRUE.
        //Note that for status and active values returned in the $EditableRequests array that are no change from the original values
        //the $ReturnedRequestStatus is made unset and the $ReturnedRequestActivePermit is made FALSE in order to prevent duplicate records written into the
        //request_history and request_status tables

        $ReturnedRequestId = $EditableRequests[$i][0];

        //Retrieve current request status and active field values from the database
        $query = "SELECT last_status, active FROM request WHERE request_id = $ReturnedRequestId;";
        $rid = awm_pgexec( $wrms_db, $query, "requestlist", TRUE, 7 );
        if ( !$rid || pg_numrows($rid) > 1 || pg_numrows($rid) == 0 )
        {
           $because .= "<P>Request $ReturnedRequestId: Error updating request! - query 1</P>\n";
           continue;
        }

        $CurrentRequest = pg_fetch_object($rid, 0);

        if ( isset($EditableRequests[$i][1]) )
        {
           //Check that returned status value is different to the status value stored in the request record

           //Unset $ReturnedRequestStatus if there has been no change made to the status
           if ( $CurrentRequest->last_status == $EditableRequests[$i][1] )
              unset($ReturnedRequestStatus);
           else
              $ReturnedRequestStatus = $EditableRequests[$i][1];
        }
        else if ( isset($ReturnedRequestStatus) )
           unset($ReturnedRequestStatus);

        if ( isset($EditableRequests[$i][2]) && $EditableRequests[$i][2] == "active_edit" )
        {
           //Permission on active checkbox

           if ( isset($EditableRequests[$i][3]) )
              $ReturnedRequestActive = $EditableRequests[$i][3];
           else
              $ReturnedRequestActive = 0;

           //Set $ReturnedRequestActviePermit to TRUE if a change has been made otherwise set to false
           if ( ($CurrentRequest->active == 't' && $ReturnedRequestActive == 0) || ( $CurrentRequest->active == 'f' && $ReturnedRequestActive == 1 ) )
              $ReturnedRequestActivePermit = TRUE;
           else
              $ReturnedRequestActivePermit = FALSE;
        }
        else
        {
           //No permission on active checkbox
           $ReturnedRequestActivePermit = FALSE;
        }

        if ( !isset($ReturnedRequestStatus) && !$ReturnedRequestActivePermit )
        {
           continue;
        }

        //Begin SQL Transaction for the updating of each request
        awm_pgexec( $wrms_db, "BEGIN;", "requestlist" );

        /* take a snapshot of the current request record and store in request_history*/

        $query = "INSERT INTO request_history (SELECT * FROM request WHERE request.request_id = $ReturnedRequestId);";

        $rid = awm_pgexec( $wrms_db, $query, "requestlist", TRUE, 7 );
        if ( ! $rid ) {
           $because .= "<P>Request $ReturnedRequestId: Error updating request! - query 2</P>\n";
           continue;
        }

        //update request record in request - status field and/or active field

        $query = "UPDATE request SET ";
        if ( isset($ReturnedRequestStatus ) )
           $query .= " last_status = '$ReturnedRequestStatus', ";
        if ( $ReturnedRequestActivePermit )
           $query .= " active = '$ReturnedRequestActive', ";
        $query .= " last_activity = 'now' ";
        $query .= "WHERE request.request_id = $ReturnedRequestId; ";

        $rid = awm_pgexec( $wrms_db, $query, "requestlist", TRUE, 7 );
        if ( ! $rid ) {
           $because .= "<P>Request $ReturnedRequestId: Error updating request! - query 3</P>\n";
           continue;
        }


        //update the request_status table with the new status for that request if permitted
        if ( isset($ReturnedRequestStatus) )
        {
           $query = "INSERT INTO request_status (request_id, status_by, status_on, status_code, status_by_id)";
           $query .= "VALUES( $ReturnedRequestId, '$session->username', 'now', '$ReturnedRequestStatus', $session->user_no);";

           $rid = awm_pgexec( $wrms_db, $query, "requestlist", TRUE, 7 );
           if ( ! $rid ) {
              $because .= "<P>Request $ReturnedRequestId: Error updating request! - query 4</P>\n";
              continue;
           }

        }

        awm_pgexec( $wrms_db, "COMMIT;", "requestlist" );

        $ChangedRequests_count++;
     }
  }

  function header_row() {
    global $format;

    echo "<tr>\n";
    column_header("WR&nbsp;#", "request_id");
    column_header("Request By", "lfull" );
    column_header("Request On", "request_on" );
    column_header("Description", "lbrief");
    column_header("Status", "status_desc" );
    column_header("Type", "request_type_desc" );
    column_header("Last Chng", "request.last_activity");
    if ( "$format" == "edit" )  //adds in the Active field header for the Brief (editable) report
        column_header("Active", "active");
    echo "</tr>";
  }

  if ( "$format" == "edit" && isset($submitBriefEditable) ) // If changes have been returned from Brief (editable) then function is called update the database with the changes
  {
     $ChangedRequests_count = 0;
     $because ="";
     Process_Brief_editable_Requests();
  }

  if ( $system_code == "." ) $system_code = "";

  $title = "$system_name Request List";

  if ( !isset($rlsort) ) $rlsort = $settings->get('rlsort');
  if ( !isset($rlseq) ) $rlseq = $settings->get('rlseq');
  if ( "$rlsort" == "" ) $rlsort = "request_id";
  $rlseq = strtoupper($rlseq);
  if ( "$rlseq" == "" ) {
    $rlseq = ( "$rlsort" == "request_id" || "$rlsort" == "request_on" || "$rlsort" == "last_change" ? "DESC" : "ASC");
  }
  if ( "$rlseq" != "ASC" ) $rlseq = "DESC";
  $settings->set('rlsort', $rlsort);
  $settings->set('rlseq', $rlseq);

  if ( isset($org_code) ) $org_code = intval($org_code);

  // Build up the column header cell, with %s gaps for the sort, sequence and sequence image
  $header_cell = "<th class=cols><a class=cols href=\"$PHP_SELF?rlsort=%s&rlseq=%s";
  if ( isset($qs) ) $header_cell .= "&qs=$qs";
  if ( isset($org_code) ) $header_cell .= "&org_code=$org_code";
  if ( $system_code != "" ) $header_cell .= "&system_code=$system_code";
  if ( isset($search_for) ) $header_cell .= "&search_for=$search_for";
  if ( isset($inactive) ) $header_cell .= "&inactive=$inactive";
  if ( isset($requested_by) ) $header_cell .= "&requested_by=$requested_by";
  if ( isset($interested_in) ) $header_cell .= "&interested_in=$interested_in";
  if ( isset($allocated_to) ) $header_cell .= "&allocated_to=$allocated_to";
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
  $header_cell .= "%s</a></th>";    // %s For the image

//Builds up and outputs the HTML for a linked column header on the request list
function column_header( $ftext, $fname ) {
  global $rlsort, $rlseq, $header_cell;
  $fseq = "";
  $seq_image = "";
  if ( "$rlsort" == "$fname" ) {
    $fseq = ( "$rlseq" == "DESC" ? "ASC" : "DESC");
    $seq_image .= "&nbsp;<img border=0 src=\"images/sort-$rlseq.png\">";
  }
  printf( $header_cell, $fname, $fseq, $ftext, $seq_image );
}

  if ( "$qry" != "" && "$action" == "delete" ) {
    $query = "DELETE FROM saved_queries WHERE user_no = '$session->user_no' AND LOWER(query_name) = LOWER('$qry');";
    $result = awm_pgexec( $dbconn, $query, "requestlist", false, 7);
    unset($qry);
  }

  include("inc/headers.php");

if ( ! is_member_of('Request') || ((isset($error_msg) || isset($error_qry)) && "$error_msg$error_qry" != "") ) {
  include( "inc/error.php" );
}
else {
  if ( !isset( $style ) || ($style != "plain" && $style != "stripped") ) {
    echo "<form Action=\"$PHP_SELF";
    if ( "$org_code$qs" != "" ) {
      echo "?";
      if ( "$org_code" != "" ) echo "org_code=$org_code" . ( "$qs" == "" ? "" : "&");
      if ( "$qs" != "" ) echo "qs=$qs";
    }
    echo "\" Method=\"POST\">";
    echo "</h3>\n";

    include("inc/system-list.php");
    if ( is_member_of('Admin', 'Support' ) ) {
      $system_list = get_system_list( "", "$system_code", 35);
    }
    else {
      $system_list = get_system_list( "CES", "$system_code", 35);
    }

    echo "<table border=0 cellspacing=2 cellpadding=0 align=center class=row0 width=100% style=\"border: 1px dashed #aaaaaa;\">\n<tr>\n";
    echo "<td width=100%><table border=0 cellspacing=0 cellpadding=0 width=100%><tr valign=middle>\n";
    if ( "$qs" == "complex" )
      echo "<td class=smb>Find:</td><td class=sml><input class=sml type=text size=10 name=search_for value=\"$search_for\"></td>\n";

    echo "<td class=smb>&nbsp;System:</td><td class=sml><select class=sml name=system_code><option value=\".\">--- All Systems ---</option>$system_list</select></td>\n";

  if ( is_member_of('Admin', 'Support') ) {
    include( "inc/organisation-list.php" );
    $orglist = "<option value=\"\">--- All Organisations ---</option>\n" . get_organisation_list( "$org_code", 30 );
    echo "<td class=smb>&nbsp;Organisation:</td><td class=sml><select class=sml name=\"org_code\">\n$orglist</select></td>\n";
  }
  if ( "$qs" != "complex" )
   echo "<td valign=middle class=smb align=center><input type=submit value=\"RUN QUERY\" alt=go name=submit class=\"submit\"></td><td class=smb width=100px> &nbsp; &nbsp; &nbsp; </td>\n";
  echo "</tr></table></td></tr>\n";


  if ( "$qs" == "complex" ) {
    if ( is_member_of('Admin', 'Support', 'Manage') ) {
      if ( is_member_of('Admin', 'Support') ) {
        $user_org_code = "";
      }
      else {
        $user_org_code = "$session->org_code";
      }
      echo "<tr><td width=100%><table border=0 cellspacing=0 cellpadding=0 width=100%><tr valign=middle>\n";
      if ( is_member_of('Admin', 'Support', 'Manage') ) {
        $user_list = "<option value=\"\">--- Any Requester ---</option>" . get_user_list( "", $user_org_code, "" );
        echo "<td class=smb>By:</td><td class=sml><select class=sml name=requested_by>$user_list</select></td>\n";
        if ( ! is_member_of('Admin', 'Support')  && ! isset($interested_in) ) $interested_in = $session->user_no;
        $user_list = "<option value=\"\">--- Any Interested User ---</option>" . get_user_list( "", $user_org_code, $interested_in );
        echo "<td class=smb>Watching:</td><td class=sml><select class=sml name=interested_in>$user_list</select></td>\n";
      }
      if ( is_member_of('Admin', 'Support') ) {
        $user_list = "<option value=\"\">--- Any Assigned Staff ---</option>" . get_user_list( "Support", "", $allocated_to );
        echo "<td class=smb>ToDo:</td><td class=sml><select class=sml name=allocated_to>$user_list</select></td>\n";
      }
      echo "</tr></table></td></tr>\n";
    }

    $request_types = get_code_list( "request", "request_type", "$type_code" );
?>
<tr><td><table border=0 cellspacing=0 cellpadding=0 width=100%><tr valign=middle>
<td class=smb align=right>Last&nbsp;Action&nbsp;From:</td>
<td nowrap class=smb><input type=text size=10 name=from_date class=sml value="<?php echo "$from_date"; ?>">
<a href="javascript:show_calendar('forms[0].from_date');" onmouseover="window.status='Date Picker';return true;" onmouseout="window.status='';return true;"><img valign="middle" src="/images/date-picker.gif" border=0></a>
</td>

<td class=smb align=right>&nbsp;To:</td>
<td nowrap class=smb><input type=text size=10 name=to_date class=sml value="<?php echo "$to_date"; ?>">
<a href="javascript:show_calendar('forms[0].to_date');" onmouseover="window.status='Date Picker';return true;" onmouseout="window.status='';return true;"><img valign="middle" src="/images/date-picker.gif" border=0></a>
</td>
<td class=smb align=right>&nbsp;Type:</td>
<td nowrap class=smb><select name="type_code" class=sml><option value="">-- All Types --</option><?php echo "$request_types"; ?></select></td>
</tr></table></td>
</tr>
<?php
    echo "<tr><td>\n";
    echo "<table border=0 cellspacing=0 cellpadding=0><tr valign=middle><td class=smb align=right valign=top>When:</td><td class=sml valign=top>\n";
    $query = "SELECT * FROM lookup_code WHERE source_table='request' ";
    $query .= " AND source_field='status_code' ";
    $query .= " ORDER BY source_table, source_field, lookup_seq, lookup_code ";
    $rid = pg_Exec( $wrms_db, $query);
    if ( $rid && pg_NumRows($rid) > 1 ) {
      $nrows = pg_NumRows($rid);
      for ( $i=0; $i<$nrows; $i++ ) {
        $status = pg_Fetch_Object( $rid, $i );
        echo "<input type=checkbox name=incstat[$status->lookup_code]";
        if ( !isset( $incstat) || $incstat[$status->lookup_code] <> "" ) echo " checked";
        echo " value=1>" . str_replace( " ", "&nbsp;", $status->lookup_desc) . " &nbsp; ";
        if ( $i == intval(($nrows + 1) / 2) ) echo "&nbsp;<br>";
      }
      echo "<input type=checkbox name=inactive";
      if ( isset($inactive) ) echo " checked";
      echo " value=1>Inactive";
      echo "</td>\n";
    }
    echo "<td valign=middle class=smb align=center><input type=submit value=\"RUN QUERY\" alt=go name=submit class=\"submit\"></td>\n";
    echo "</tr></table>\n</td></tr>\n";

    echo "<tr><td>\n";
    echo "<table border=0 cellspacing=0 cellpadding=0 align=center>\n";
    echo "<tr valign=middle>\n";
    echo "<td valign=middle align=right class=smb>Max results:</td><td class=sml valign=top><input type=text size=6 value=\"$maxresults\" name=maxresults class=\"sml\"></td>\n";
    echo "<td valign=middle align=right class=smb>&nbsp; &nbsp; Save query as:</td>\n";
    echo "<td valign=middle align=center><input type=text size=20 value=\"$savelist\" name=savelist class=\"sml\"></td>\n";
    echo "<td valign=middle align=left><input type=submit value=\"SAVE QUERY\" alt=save name=submit class=\"submit\"></td>\n";
    echo "</tr></table>\n</td></tr>\n";
  }
?>
</table>
</form>

<?php
  } // if  not plain  or stripped style


  /////////////////////////////////////////////////////////////////////////////////////////////////
  //
  // Now we build the statement that will find those requests...
  //
  /////////////////////////////////////////////////////////////////////////////////////////////////

  // if ( "$qry$search_for$org_code$system_code" != "" ) {
    // Recommended way of limiting queries to not include sub-tables for 7.1
    $result = awm_pgexec( $wrms_db, "SET SQL_Inheritance TO OFF;" );
    $query = "";
    if ( isset($qry) && "$qry" != "" ) {
      $qry = tidy($qry);
      $qquery = "SELECT * FROM saved_queries WHERE user_no = '$session->user_no' AND query_name = '$qry';";
      $result = awm_pgexec( $dbconn, $qquery, "requestlist", false, 7);
      $thisquery = pg_Fetch_Object( $result, 0 );
      $query = $thisquery->query_sql ;
    }
    else {

      $query .= "SELECT request.request_id, brief, fullname, email, request_on, status.lookup_desc AS status_desc, last_activity, detailed ";
      $query .= ", request_type.lookup_desc AS request_type_desc, lower(fullname) AS lfull, lower(brief) AS lbrief ";
      $query .= ", to_char( request.last_activity, 'FMdd Mon yyyy') AS last_change ";
      $query .= ", to_char( request.request_on, 'FMdd Mon yyyy') AS date_requested";
      //provides extra fields that are needed to create a Brief (editable) report
      $query .= ", active, last_status ";
      $query .= "FROM ";
      if ( intval("$interested_in") > 0 ) $query .= "request_interested, ";
      if ( intval("$allocated_to") > 0 ) $query .= "request_allocated, ";
      $query .= "request, usr, lookup_code AS status ";
      $query .= ", lookup_code AS request_type";

      $query .= " WHERE request.request_by=usr.username ";
      $query .= " AND request_type.source_table='request' AND request_type.source_field='request_type' AND request.request_type = request_type.lookup_code";
      if ( "$inactive" == "" )        $query .= " AND active ";
      if ( ! is_member_of('Admin', 'Support' ) ) {
        $query .= " AND org_code = '$session->org_code' ";
      }
      else if ( isset($org_code) && intval($org_code) > 0 )
        $query .= " AND org_code='$org_code' ";

      if ( intval("$user_no") > 0 )
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
      if ( "$system_code" != "" )     $query .= " AND system_code='$system_code' ";
      if ( "$type_code" != "" )     $query .= " AND request_type=" . intval($type_code);

      if ( "$from_date" != "" )     $query .= " AND request.last_activity >= '$from_date' ";
      if ( "$to_date" != "" )     $query .= " AND request.last_activity<='$to_date' ";

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
    }

    $query .= " AND status.source_table='request' AND status.source_field='status_code' AND status.lookup_code=request.last_status ";
    $query .= " ORDER BY $rlsort $rlseq ";
    $query .= " LIMIT $maxresults ";
    $result = awm_pgexec( $wrms_db, $query, "requestlist", false, 7 );

    if ( "$style" != "stripped" ) {
      if ( $result && pg_NumRows($result) > 0 )
        echo "\n<small>" . pg_NumRows($result) . " requests found</small>";
      else {
        echo "\n<p><small>No requests found</small></p>";
      }
    }

    if ( "$style" != "stripped" || ("$style" == "stripped" && "$format" == "edit")) {
      $this_page = "$PHP_SELF?style=%s&format=%s";
      if ( isset($qry) ) $uqry = urlencode($qry);
      if ( "$qry" != "" ) $this_page .= "&qry=$uqry";
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

    echo "<table border=\"0\" align=left width=100%>\n";

    $show_notes = ($format == "ultimate" || $format == "detailed" );
    $show_details = ($format == "ultimate" || $format == "detailed" || "$format" == "activity" || "$format" == "quotes" );
    $show_quotes = ( $format == "ultimate" || "$format" == "activity" || "$format" == "quotes" );
    $show_work = ( ($format == "ultimate" || "$format" == "activity" ) &&  is_member_of('Admin', 'Support' ) );
    if ( $show_details ) {
      include("inc/html-format.php");
    }
    else
      header_row();


    if ( $result ) {
      $grand_total = 0.0;
      $grand_qty_total = 0.0;

      // Build table of requests found
      for ( $i=0; $i < pg_NumRows($result); $i++ ) {
        $thisrequest = pg_Fetch_Object( $result, $i );

        if ( "$format" == "edit" ) //tests to see if request needs to be checked for editing permissions for the logged in user
        {
           //$status_edit flags whether or not the logged in user has permissions to edit the status for the current request to be listed
           //$active_edit flags whether or not the logged in user has permissions to edit the active field for the current request to be listed

           list($status_edit, $active_edit) = RequestEditPermissions($thisrequest->request_id); //Calls function to find out and return the editing permissions for the current request

           if ( $i == 0 )  //if statement to initialise counter to be used as the
                $EditableRequests_count = 0; // position no in the EditableRequests array that is returned when
            //a Brief (editable) report is submitted back
            //with one or more editable requests in its list

        }

        if ( $show_details ) header_row();
        printf( "<tr class=row%1d>\n", $i % 2);
        if ( "$format" == "edit" )  //used to control whether or not a request id hidden variable is also added which builds up the 'id' column in the EditableRequests array for the Brief (editable) report
           echo "<td class=sml align=center>" . ( ($status_edit || $active_edit ) ? "<input type=hidden name=\"EditableRequests[$EditableRequests_count][0]\" value=\"$thisrequest->request_id\">" : "" ) . "<a href=\"request.php?request_id=$thisrequest->request_id\">$thisrequest->request_id</a></td>\n";
        else
           echo "<td class=sml align=center><a href=\"request.php?request_id=$thisrequest->request_id\">$thisrequest->request_id</a></td>\n";
        echo "<td class=sml nowrap><a href=\"mailto:$thisrequest->email\">$thisrequest->fullname</a></td>\n";
        echo "<td class=sml align=center>$thisrequest->date_requested</td>\n";
        echo "<td class=sml><a href=\"request.php?request_id=$thisrequest->request_id\">$thisrequest->brief";
        if ( "$thisrequest->brief" == "" ) echo substr( $thisrequest->detailed, 0, 50) . "...";
        echo "</a></td>\n";
        if ( "$format" == "edit" )//tests to see if report should provide editable status fields where appropriate
        {
           if ( $status_edit ) //tests to see if the logged in user is able to edit the status field for this request record
           {  //provide a drop down to allow editing of the status code for that request
              $status_list   = get_code_list( "request", "status_code", "$thisrequest->last_status" );
              echo "<td class=sml><select class=sml name=\"EditableRequests[$EditableRequests_count][1]\">$status_list</select></td>\n";
           }
           else //otherwise output plain text of the current request status
              echo "<td class=sml>&nbsp;$thisrequest->status_desc&nbsp;</td>\n";
        }
        else
           echo "<td class=sml>&nbsp;$thisrequest->status_desc&nbsp;</td>\n";
        echo "<td class=sml>&nbsp;" . str_replace( " ", "&nbsp;", $thisrequest->request_type_desc) . "&nbsp;</td>\n";
        echo "<td class=sml align=center>" . str_replace( " ", "&nbsp;", $thisrequest->last_change) . "</td>\n";
        if ( "$format" == "edit" ) //adds in the Active field for the Brief (editable) reports
        {
           if ( $active_edit ) //tests to see if the logged in user is able to edit the active field for this request
              echo "<td class=sml align=center><input type=\"hidden\" name=\"EditableRequests[$EditableRequests_count][2]\" value=\"active_edit\"><input type=checkbox name=\"EditableRequests[$EditableRequests_count][3]\" value=\"1\" " . ( $thisrequest->active == 't' ? "CHECKED" : "" ) . "></td>\n";
           else if ( $status_edit )
              echo "<td class=sml align=center><input type=\"hidden\" name=\"EditableRequests[$EditableRequests_count][2]\" value=\"active_read\">" . ( $thisrequest->active == 't' ? "Active" : "Inactive" ) . "</td>\n";
           else
              echo "<td class=sml align=center>" . ( $thisrequest->active == 't' ? "Active" : "Inactive" ) . "</td>\n";
        }
        echo "</tr>\n";

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
            printf("<td align=right>%9.2f &nbsp; </td>\n", $thiswork->work_quantity);
            printf("<td align=right>%9.2f &nbsp; </td>\n", $thiswork->work_rate);
            $value = $thiswork->work_quantity * $thiswork->work_rate;
            $total += $value;
            $qty_total += $thiswork->work_quantity;
            printf("<td align=right>%9.2f &nbsp; </td>\n", $value);
            echo "</tr>\n";
          }
          if ( $j > 0 )
            printf( "<tr class=row%1d>\n<td colspan=4>&nbsp; &nbsp; &nbsp; Request #$thisrequest->request_id total</td>\n<td align=right>%9.2f &nbsp; </td><td>&nbsp;</td><td align=right>%9.2f &nbsp; </td>\n</tr>\n", $i % 2, $qty_total, $total);
          $grand_total += $total;
          $grand_qty_total += $qty_total;
        }

        if ( $show_details )
          echo "<tr class=row3>\n<td colspan=7>&nbsp;</td></tr>\n";

        if ( (isset($status_edit) && $status_edit) || (isset($active_edit) && $active_edit ) ) //Maintains the $EditableRequests_count counter
         $EditableRequests_count++;
      }
    }
    if ( $show_work )
      printf( "<tr class=row%1d>\n<th align=left colspan=4>Grand Total</th>\n<th align=right>%9.2f &nbsp; </th><th>&nbsp;</th><th align=right>%9.2f &nbsp; </th>\n</tr>\n", $i % 2, $grand_qty_total, $grand_total);

    if ( "$format" == "edit" )
       if ( $EditableRequests_count == 0 )
          echo "<tr><td align=\"center\" colspan=\"8\"><br>You do not have permission to edit any of the requests in this report.<br>&nbsp;</td></tr>";
       else
       {
          echo "<tr><td align=\"left\" colspan=\"4\"><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=reset class=\"submit\" value=\"Reset Attributes\"><br>&nbsp;</td><td align=\"right\" colspan=\"4\"><br><input type=submit value=\"Apply Changes\" name=\"submitBriefEditable\" class=\"submit\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>&nbsp;<td></tr>";
          if ( isset($ChangedRequests_count) )
             echo "<tr><td align=\"center\" colspan=\"8\"><br>" . ($ChangedRequests_count == 0 ? "No" : "$ChangedRequests_count" ) . " request". ( ($ChangedRequests_count > 1 || $ChangedRequests_count == 0) ? "s" : "" ) . " updated.<br>&nbsp;</td></tr>";
       }

    echo "</table>\n";

    if ( "$format" == "edit" )  //end form enclosing Brief (editable) report
       echo "</form>\n";

    //$this_page string build code block was here
    if ( "$style" != "stripped" )
    {
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
      if ( "$qry" != "" ) {
        echo "</td><td>|&nbsp; &nbsp; or <a href=\"$PHP_SELF?qs=complex&qry=$uqry&action=delete\" class=sbutton>Delete</a> it\n";
      }
      echo "</td></tr></table>\n";
    }

  // }

} /* The end of the else ... clause waaay up there! */

include("inc/footers.php");

?>
