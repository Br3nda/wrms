<?php
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
     global $session, $dbconn;
     $plain = FALSE;

     include("getrequest.php");

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
     global $dbconn, $EditableRequests, $session, $ChangedRequests_count, $because;

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
        $rid = awm_pgexec( $dbconn, $query, "requestlist", TRUE, 7 );
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
        awm_pgexec( $dbconn, "BEGIN;", "requestlist" );

        /* take a snapshot of the current request record and store in request_history*/

        $query = "INSERT INTO request_history (SELECT * FROM request WHERE request.request_id = $ReturnedRequestId);";

        $rid = awm_pgexec( $dbconn, $query, "requestlist", TRUE, 7 );
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

        $rid = awm_pgexec( $dbconn, $query, "requestlist", TRUE, 7 );
        if ( ! $rid ) {
           $because .= "<P>Request $ReturnedRequestId: Error updating request! - query 3</P>\n";
           continue;
        }


        //update the request_status table with the new status for that request if permitted
        if ( isset($ReturnedRequestStatus) )
        {
           $query = "INSERT INTO request_status (request_id, status_by, status_on, status_code, status_by_id)";
           $query .= "VALUES( $ReturnedRequestId, '$session->username', 'now', '$ReturnedRequestStatus', $session->user_no);";

           $rid = awm_pgexec( $dbconn, $query, "requestlist", TRUE, 7 );
           if ( ! $rid ) {
              $because .= "<P>Request $ReturnedRequestId: Error updating request! - query 4</P>\n";
              continue;
           }

        }

        awm_pgexec( $dbconn, "COMMIT;", "requestlist" );

        $ChangedRequests_count++;
     }
  }

function header_row() {
  global $format, $columns, $available_columns;

   // We want to use nice column names, but sometimes we have to
   // sort by something else (e.g. case insensitive)
   $nice_names = array(
          "request_for" => "lfull",
          "request_by"  => "lby_fullname",
          "brief"       => "lbrief",
          "status"      => "status_desc",
          "type"        => "request_type_desc",
          "tags"        => "request_tags",
          "last_change" => "request.last_activity"
   );

  echo "<tr>\n";
  reset($columns);
  while( list($k,$v) = each( $columns ) ) {
    $real_name = $v;
    if ( isset($nice_names[$v]) ) $real_name = $nice_names[$v];
    column_header($available_columns[$real_name], $real_name);
  }
  echo "</tr>";
}

function show_column_value( $column_name, $row ) {
  global $format, $status_edit, $active_edit, $EditableRequests_count;
  switch( $column_name ) {
    case "request_id":
      if ( "$format" == "edit" )  //used to control whether or not a request id hidden variable is also added which builds up the 'id' column in the EditableRequests array for the Brief (editable) report
        echo "<td class=sml align=center>" . ( ($status_edit || $active_edit ) ? "<input type=hidden name=\"EditableRequests[$EditableRequests_count][0]\" value=\"$row->request_id\">" : "" ) . "<a href=\"/wr.php?request_id=$row->request_id\">$row->request_id</a></td>\n";
      else
        echo "<td class=sml align=center><a href=\"/wr.php?request_id=$row->request_id\">$row->request_id</a></td>\n";
      break;
    case "lfull":
    case "request_for":
      echo "<td class=sml nowrap><a href=\"mailto:$row->email\">$row->fullname</a></td>\n";
      break;
    case "lby_fullname":
    case "request_by":
      echo "<td class=sml nowrap><a href=\"mailto:$row->by_email\">$row->by_fullname</a></td>\n";
      break;
    case "request_tags":
      echo "<td class=\"sml\">$row->request_tags</td>\n";
      break;
    case "request_on":
      echo "<td class=sml align=center>$row->date_requested</td>\n";
      break;
    case "lbrief":
    case "description":
      echo "<td class=sml><a href=\"/wr.php?request_id=$row->request_id\">$row->brief";
      if ( "$row->brief" == "" ) echo substr( $row->detailed, 0, 50) . "...";
      echo "</a></td>\n";
      break;
    case "status":
    case "status_desc":
      if ( "$format" == "edit" && $status_edit ) {
        //tests to see if report should provide editable status fields where appropriate
        //tests to see if the logged in user is able to edit the status field for this request record
        //provide a drop down to allow editing of the status code for that request
        $status_list   = get_code_list( "request", "status_code", "$row->last_status" );
        echo "<td class=sml><select class=sml name=\"EditableRequests[$EditableRequests_count][1]\">$status_list</select></td>\n";
      }
      else {
        //otherwise output plain text of the current request status
        echo "<td class=sml>&nbsp;".str_replace(' ', '&nbsp;',$row->status_desc)."&nbsp;</td>\n";
      }
      break;
    case "type":
    case "request_type_desc":
      echo "<td class=sml>&nbsp;" . str_replace( " ", "&nbsp;", $row->request_type_desc) . "&nbsp;</td>\n";
      break;
    case "last_change":
    case "request.last_activity":
      echo "<td class=sml align=center>" . str_replace( " ", "&nbsp;", $row->last_change) . "</td>\n";
      break;
    case "active":
      if ( "$format" == "edit" && ( $active_edit || $status_edit) ) {
        //adds in the Active field for the Brief (editable) reports
        if ( $active_edit ) //tests to see if the logged in user is able to edit the active field for this request
          echo "<td class=sml align=center><input type=\"hidden\" name=\"EditableRequests[$EditableRequests_count][2]\" value=\"active_edit\"><input type=checkbox name=\"EditableRequests[$EditableRequests_count][3]\" value=\"1\" " . ( $row->active == 't' ? "CHECKED" : "" ) . "></td>\n";
        else if ( $status_edit )
          echo "<td class=sml align=center><input type=\"hidden\" name=\"EditableRequests[$EditableRequests_count][2]\" value=\"active_read\">" . ( $row->active == 't' ? "Active" : "Inactive" ) . "</td>\n";
      }
      else {
        echo "<td class=sml align=center>" . ( $row->active == 't' ? "Active" : "Inactive" ) . "</td>\n";
      }
      break;
  }
}

function data_row( $row, $rc ) {
  global $columns;

  printf( "<tr class=row%1d>\n", $rc % 2);
  reset($columns);
  while( list($k,$v) = each( $columns ) ) {
    show_column_value($v,$row);
  }
  echo "</tr>\n";
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
  if ( "$savedquery" != "" ) $header_cell .= "&savedquery=$savedquery";
  if ( "$style" != "" ) $header_cell .= "&style=$style";
  if ( "$format" != "" ) $header_cell .= "&format=$format";
  if ( isset($choose_columns) && $choose_columns ) $header_cell .= "&choose_columns=1";
  $header_cell .= "\">%s";      // %s for the Cell heading
  $header_cell .= "%s</a></th>";    // %s For the image

//Builds up and outputs the HTML for a linked column header on the request list
function column_header( $ftext, $fname ) {
  global $rlsort, $rlseq, $header_cell, $images;
  $fseq = "";
  $seq_image = "";
  if ( "$rlsort" == "$fname" ) {
    $fseq = ( "$rlseq" == "DESC" ? "ASC" : "DESC");
    $seq_image .= "&nbsp;<img border=0 src=\"/$images/sort-$rlseq.png\">";
  }
  printf( $header_cell, $fname, $fseq, $ftext, $seq_image );
}

?>