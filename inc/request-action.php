<?php
function dates_equal( $date1, $date2 ) {
  if ( "$date1" == "$date2" ) return true;
  return strtotime( "$date1" ) == strtotime( "$date2" );
}

  if ( "$submit" == "register" ) {
    $query = "INSERT INTO request_interested (request_id, username, user_no ) VALUES( $request_id, '$session->username', $session->user_no); ";
    $rid = awm_pgexec( $wrms_db, $query );
    if ( $rid ) {
      $because .= "<h3>You have been added to this request</h3>";
    }
    return;
  }
  else if ( "$submit" == "deregister" ) {
    if ( isset($user_no) && $user_no <> $session->user_no ) {
      if ( !( $allocated_to || $sysmgr ) ) {
        $because .= "<H3>You are not authorised to remove people from this request!</H3>\n";
      }
    }
    else
      $user_no = $session->user_no;
    $query = "DELETE FROM request_interested WHERE request_id=$request_id AND user_no=$user_no; ";
    $rid = awm_pgexec( $wrms_db, $query, "req-action" );
    if ( $rid ) {
      if ( $user_no == $session->user_no )
        $because .= "<h3>You have ";
      else
        $because .= "<h3>User $user_no has ";
      $because .= "been removed from this request</h3>";
    }
    return;
  }
  else if ( "$submit" == "deallocate" ) {
    if ( isset($user_no) && $user_no <> $session->user_no ) {
      if ( !( $allocated_to || $sysmgr ) ) {
        $because .= "<H3>You are not authorised to deallocate people from this request!</H3>\n";
      }
    }
    else
      $user_no = $session->user_no;
    $query = "DELETE FROM request_allocated WHERE request_id=$request_id AND allocated_to_id=$user_no; ";
    $rid = awm_pgexec( $wrms_db, $query, "req-action" );
    if ( $rid ) {
      if ( $user_no == $session->user_no )
        $because .= "<h3>You are ";
      else
        $because .= "<h3>User $user_no is ";
      $because .= "no longer allocated to this request</h3>";
    }
    return;
  }
  else if ( "$submit" == "deltime" ) {
    $query = "SELECT * FROM request_timesheet WHERE timesheet_id=$timesheet_id ; ";
    $rid = awm_pgexec( $wrms_db, $query, "req-action" );
    if ( $rid ) {
      $work = pg_Fetch_Object( $rid, 0);
      $old_work_on = $work->work_on;
      $old_work_units = $work->work_units;
      $old_work_quantity = $work->work_quantity;
      $old_work_rate = $work->work_rate;
      $old_work_details = $work->work_description;
    }
    $query = "DELETE FROM request_timesheet WHERE timesheet_id=$timesheet_id ; ";
    $rid = awm_pgexec( $wrms_db, $query, "req-action" );
    if ( $rid ) {
      $because .= "<h3>Timesheet deleted.</h3>";
    }
    return;
  }

  /* scope a transaction to the whole change */
  awm_pgexec( $wrms_db, "BEGIN;", "req-action" );
  if ( isset( $request ) ) {
    /////////////////////////////////////
    // Update an existing request
    /////////////////////////////////////
    $chtype = "change";
    $requsr = $session;

    // Have to be pedantic here - the translation from database -> variable is basic.
//    error_log( "$sysabbr request-action1: Active: $request->active, New: $new_active", 0);
    if ( isset($new_active) && $new_active <> "TRUE" ) $new_active = "FALSE";
    if ( strtolower( substr( $request->active, 0, 1)) == "t" )
      $request->active = "TRUE";
    else
      $request->active = "FALSE";

    $note_added = ($new_note != "");
    $quote_added = ($new_quote_brief != "") && ($new_quote_amount != "");
    $work_added = ($new_work_on != "") && ($new_work_quantity != "") && ($new_work_details != "") && ($new_work_rate != "") ;
    $status_changed = isset($new_status) && ($request->last_status != $new_status );
    $interest_added = isset($new_interest) && ($new_interest != "" );
    $allocation_added = isset($new_allocation) && ($new_allocation != "" );
    $eta_changed = !dates_equal($request->eta, $new_eta);
    $requested_by_changed = !dates_equal($request->requested_by_date, $new_requested_by_date);
    $agreed_changed = !dates_equal($request->agreed_due_date, $new_agreed_due_date);
    $changes =  (isset($new_brief) && $request->brief != $new_brief)
             || (isset($new_detail) && $request->detailed != $new_detail)
             || (isset($new_type) && $request->request_type != $new_type )
             || (isset($new_severity) && $request->severity_code != $new_severity )
             || (isset($new_urgency) && $request->urgency != $new_urgency )
             || (isset($new_sla_code) && $request->request_sla_code != $new_sla_code )
             || (isset($new_importance) && $request->importance != $new_importance )
             || (isset($new_system_code) && $request->system_code != $new_system_code )
             || (isset($new_active) && $request->active != $new_active )
             || $eta_changed || $status_changed || $note_added || $quote_added || $allocation_added ;
    $send_some_mail = $changes;
    $changes = $changes || $work_added || $interest_added;
    error_log( "$sysabbr request-action: $changes---"
             . (isset($new_brief) && $request->brief != $new_brief) . "-"
             . (isset($new_detail) && $request->detailed != $new_detail) . "+"
             . (isset($new_type) && $request->request_type != $new_type ) . "-"
             . (isset($new_severity) && $request->severity_code != $new_severity ) . "+"
             . (isset($new_urgency) && $request->urgency != $new_urgency ) . "-"
             . (isset($new_sla_code) && $request->request_sla_code != $new_sla_code ) . "*"
             . "$requested_by_changed+$agreed_changed-"
             . (isset($new_importance) && $request->importance != $new_importance ) . "+"
             . (isset($new_system_code) && $request->system_code != $new_system_code ) . "-"
             . (isset($new_active) && $request->active != $new_active ) . "+  also  -"
             . "$eta_changed+$status_changed-$note_added+"
             . "$quote_added-$work_added+$interest_added-$allocation_added+"
             . "---", 0) ;

    if ( ! $changes ) {
      $because = "";
      $chtype = "";
      return;
    }

    /* take a snapshot of the current record */
    $query = "INSERT INTO request_history SELECT * FROM request WHERE request.request_id = '$request_id'";
    $rid = awm_pgexec( $wrms_db, $query, "req-action" );
    if ( ! $rid ) {
      $because .= "<H3>Snapshot Failed!</H3>\n";
      $because .= "<P>The error returned was:</P><TT>" . pg_ErrorMessage( $wrms_db ) . "</TT>";
      $because .= "<P>The failed query was:</P><TT>$query</TT>";
      awm_pgexec( $wrms_db, "ROLLBACK;" );
      return;
    }

    if ( $statusable && $status_changed ) {
      // Changed Status Stuff
      $query = "INSERT INTO request_status (request_id, status_by, status_on, status_code, status_by_id) ";
      $query .= "VALUES( $request_id, '$session->username', 'now', '$new_status', $session->user_no)";
      $rid = awm_pgexec( $wrms_db, $query, "req-action" );
      if ( ! $rid ) {
        $errmsg = pg_ErrorMessage( $wrms_db );
        $because .= "<H3>&nbsp;Status Change Failed!</H3>\n";
        $because .= "<P>The error returned was:</P><TT>" . pg_ErrorMessage( $wrms_db ) . "</TT>";
        $because .= "<P>The failed query was:</P><TT>$query</TT>";
        awm_pgexec( $wrms_db, "ROLLBACK;" );
        return;
      }
      if ( eregi( "[fhc]", "$new_status") )
        $new_active = "FALSE";
      else
        $new_active = "TRUE";
    }
//    error_log( "$sysabbr request-action2: Active: $request->active, New: $new_active (statusable: $statusable, status_changed: $status_changed", 0);

    $query = "UPDATE request SET";
    if ( isset($new_brief) && $request->brief != $new_brief )
      $query .= " brief = '" . tidy($new_brief) . "',";
    if ( isset($new_detail) && $request->detailed != $new_detail )
      $query .= " detailed = '" . tidy( $new_detail ) . "',";
    if ( isset($new_eta) && ($sysmgr || $allocated_to) && $eta_changed ) $query .= " eta = '$new_eta',";
    if ( isset($new_active) && $request->active != $new_active )
      $query .= " active = $new_active,";
    if ( $status_changed )
      $query .= " last_status = '$new_status',";
    if ( isset($new_type) && $request->request_type != $new_type )
      $query .= " request_type = $new_type,";
    if (isset($new_sla_code) && $request->request_sla_code != $new_sla_code ) {
      $sla_split = explode('|', $new_sla_code, 2);
      $query .= " sla_response_time = '" . intval($sla_split[0]) . " hours', sla_response_type = '$sla_split[1]',";
    }
    if ( "$new_requested_by_date" <> "" && ($sysmgr || $allocated_to) && $requested_by_changed )
      $query .= " requested_by_date = '$new_requested_by_date',";
    if ( "$new_agreed_due_date" <> "" && ($sysmgr || $allocated_to) && $agreed_changed )
      $query .= " agreed_due_date = '$new_agreed_due_date',";
    if ( isset($new_urgency) && $request->urgency != $new_urgency )
      $query .= " urgency = $new_urgency,";
    if ( isset($new_importance) && $request->importance != $new_importance )
      $query .= " importance = $new_importance,";
    if ( isset($new_system_code) && $request->system_code != $new_system_code )
      $query .= " system_code = '$new_system_code',";
    $query .= " last_activity = 'now' ";
    $query .= "WHERE request.request_id = '$request_id'; ";
    $rid = awm_pgexec( $wrms_db, $query, "req-action", true, 7 );
    if ( ! $rid ) {
//      $because .= "<H3>&nbsp;Update Request Failed!</H3>\n";
//      $because .= "<P>The error returned was:</P><TT>" . pg_ErrorMessage( $wrms_db ) . "</TT>";
//      $because .="<P>The failed query was:</P><TT>$query</TT>";
//      awm_pgexec( $wrms_db, "ROLLBACK;", "req-action" );
      return;
    }
    else
      error_log( "$sysabbr request-action Q: $query", 0);


    if ( $quote_added ) {
      $query = "SELECT NEXTVAL('request_quote_quote_id_seq');";
      $rid = awm_pgexec( $wrms_db, $query, "req-action", true, 7 );
      if ( ! $rid ) {
//        $errmsg = pg_ErrorMessage( $wrms_db );
//        $because .= "<H3>Failed to get new quote ID!</H3>\n";
//        $because .= "<P>The error returned was:</P><TT>" . pg_ErrorMessage( $wrms_db ) . "</TT>";
//        $because .= "<P>The failed query was:</P><TT>$query</TT>";
//        awm_pgexec( $wrms_db, "ROLLBACK;", "req-action" );
        return;
      }
      $new_quote_id = pg_Result( $rid, 0, 0);

      $new_quote_details = tidy( $new_quote_details );
      $new_quote_brief = tidy( $new_quote_brief );

      $query = "INSERT INTO request_quote (quote_id, quoted_by, quote_brief, quote_details, quote_type, quote_amount, quote_units, request_id, quote_by_id) ";
      $query .= "VALUES( $new_quote_id, '$session->username', '$new_quote_brief', '$new_quote_details', '$new_quote_type', '$new_quote_amount', '$new_quote_unit', $request_id, $session->user_no )";
      $rid = awm_pgexec( $wrms_db, $query, "req-action" );
      if ( ! $rid ) {
        $because .= "<H3>New Quote Failed!</H3>\n";
        $because .= "<P>The error returned was:</P><TT>" . pg_ErrorMessage( $wrms_db ) . "</TT>";
        $because .= "<P>The failed query was:</P><TT>$query</TT>";
        awm_pgexec( $wrms_db, "ROLLBACK;", "req-action" );
        return;
      }

    }

    if ( $work_added ) {
      /* non-null timesheet was entered */
      $new_work_details = tidy( $new_work_details );
//      $query = "DELETE FROM request_timesheet WHERE request_id=$request_id AND work_on='$new_work_on'; ";
      $query = "INSERT INTO request_timesheet (request_id,  work_on, work_quantity, work_units, work_rate, work_by_id, work_by, work_description ) ";
      $query .= "VALUES( $request_id, '$new_work_on', '$new_work_quantity', '$new_work_units', '$new_work_rate', $session->user_no, '$session->username', '$new_work_details')";
      $rid = awm_pgexec( $wrms_db, $query, "req-action" );
      if ( ! $rid ) {
        $errmsg = pg_ErrorMessage( $wrms_db );
        $because .= "<H3>New Timesheet Failed!</H3>\n";
        $because .= "<P>The error returned was:</P><TT>" . pg_ErrorMessage( $wrms_db ) . "</TT>";
        $because .= "<P>The failed query was:</P><TT>$query</TT>";
        awm_pgexec( $wrms_db, "ROLLBACK;", "req-action" );
        return;
      }
    }

    if ( $interest_added ) {
      /* new user was added as interested */
      $query = "SELECT set_interested( $new_interest, $request_id ); ";
      $rid = awm_pgexec( $wrms_db, $query, "req-action" );
      if ( $rid ) $because .= "<h3>User $new_interest has been added to this request</h3>";
    }

    if ( $allocation_added ) {
      /* new user was added as allocated */
      $query = "SELECT set_interested( $new_allocation, $request_id ); ";
      $rid = awm_pgexec( $wrms_db, $query, "req-action" );
      $query = "SELECT set_allocated( $new_allocation, $request_id ) AS alloc_to, * FROM usr WHERE usr.user_no=$new_allocation; ";
      $rid = awm_pgexec( $wrms_db, $query, "req-action" );
      if ( $rid ) {
        $alloc = pg_Fetch_Object( $rid, 0);
        $because .= "<h3>$alloc->fullname (user #$new_allocation) has been allocated to work on this request</h3>";
      }
    }

    if ( $note_added ) {
      /* non-null note was entered */
      $insert_note = $new_note;
      if ( intval("$convert_html") > 0 ) $insert_note = htmlspecialchars($insert_note);
      $insert_note = tidy($insert_note);
      $query = "INSERT INTO request_note (request_id, note_by, note_by_id, note_on, note_detail) ";
      $query .= "VALUES( $request_id, '$session->username', $session->user_no, 'now', '$insert_note')";
      $rid = awm_pgexec( $wrms_db, $query, "req-action", true );
      if ( ! $rid ) return;
    }

    $because .= "<h2>Request number $request_id modified.</h2><p>";

    if ( $statusable && $status_changed )
      $because .= "<br>Request status has been changed.\n";

//    error_log( "$sysabbr request-action3: Active: $request->active, New: $new_active (statusable: $statusable, status_changed: $status_changed", 0);
    if ( isset($new_active) && $request->active != $new_active ) {
      $because .= "<br>Request has been ";
      if ( $new_active == "TRUE" ) $because .= "re-"; else $because .= "de-";
      $because .= "activated\n";
    }

    if ( $work_added )
      $because .= "<br>Timesheet added to request.\n";

    if ( $quote_added )
      $because .= "<br>Quote $new_quote_id added to request.\n";

    if ( $note_added )
      $because .= "<br>Notes added to request.\n";

    $previous = $request;
  }
  else {
    /////////////////////////////////////
    // Create a new request
    /////////////////////////////////////
    $chtype = "create";
    $query = "select nextval('request_request_id_seq');";
    $rid = awm_pgexec( $wrms_db, $query, "req-action", true );
    if ( ! $rid ) return;
    $request_id = pg_Result( $rid, 0, 0);

    if ( $new_user_no > 0 && $new_user_no <> $session->user_no ) {
      $query = "SELECT * FROM usr WHERE usr.user_no=$new_user_no ";
      $rid = awm_pgexec( $wrms_db, $query );
      if ( ! $rid || pg_NumRows($rid) == 0 ) {
        $because .= "<P>The failed query was:</P><TT>$query</TT>";
        awm_pgexec( $wrms_db, "ROLLBACK;", "req-action" );
        return;
      }
      $requsr = pg_Fetch_Object( $rid, 0);
    }
    else
      $requsr = $session;

    $sla_split = explode('|', $new_sla_code, 2);
    $query = "INSERT INTO request (request_id, request_by, brief, detailed, active, last_status, urgency, importance, ";
    $query .= "system_code, request_type, requester_id, last_activity, sla_response_time, sla_response_type";
    if ( "$new_requested_by_date" <> "" ) $query .= ", requested_by_date";
    if ( "$new_agreed_due_date" <> "" ) $query .= ", agreed_due_date";
    $query .= ") ";
    $query .= "VALUES( $request_id, '$requsr->username', '" . tidy($new_brief) . "','" . tidy($new_detail) . "', ";
    $query .= "TRUE, 'N', $new_urgency, $new_importance, '$new_system_code' , '$new_type', $requsr->user_no, 'now', ";
    $query .= "'" . intval($sla_split[0]) . " hours', '$sla_split[1]' ";
    if ( "$new_requested_by_date" <> "" ) $query .= ", '$new_requested_by_date'";
    if ( "$new_agreed_due_date" <> "" ) $query .= ", '$new_agreed_due_date'";
    $query .= ")";
    $rid = awm_pgexec( $wrms_db, $query, "req-action", true, 7 );
    if ( ! $rid ) return;

    $query = "INSERT INTO request_status (request_id, status_by, status_on, status_code, status_by_id) ";
    $query .= "VALUES( $request_id, '$session->username', 'now', 'N', $session->user_no)";
    $rid = awm_pgexec( $wrms_db, $query, "req-action", true, 7 );
    if ( ! $rid ) {
//      $because .= "<P>The failed query was:</P><TT>$query</TT>";
//      awm_pgexec( $wrms_db, "ROLLBACK;", "req-action" );
      return;
    }


    if ( $in_notify ) {
      $query = "INSERT INTO request_interested (request_id, username, user_no ) ";
      $query .= " VALUES( $request_id, '$requsr->username', $requsr->user_no); ";
      $rid = awm_pgexec( $wrms_db, $query, "req-action", true, 7 );
      if ( ! $rid ) {
//        $because .= "<H3>&nbsp;Submit Interest Failed!</H3>\n";
//        $because .= "<P>The error returned was:</P><TT>" . pg_ErrorMessage( $wrms_db ) . "</TT>";
//        $because .= "<P>The failed query was:</P><TT>$query</TT>";
//        awm_pgexec( $wrms_db, "ROLLBACK;", "req-action" );
        return;
      }
    }

    $query = "SELECT * FROM system_usr, usr ";
    $query .= "WHERE system_usr.system_code = '$new_system_code' ";
    $query .= "AND system_usr.role = 'S' " ;
    $query .= "AND system_usr.user_no = usr.user_no " ;
    $rid = awm_pgexec( $wrms_db, $query, "req-action", true, 7);
    if ( ! $rid  ) {
//      $because .= "<P>Query failed:</P><P>$query</P>";
//      awm_pgexec( $wrms_db, "ROLLBACK;", "req-action" );
      return;
    }
    else if (!pg_NumRows($rid) )
      $because .= "<P><B>Warning: </B> No system maintenance manager for '$new_system_code'</P>";
    else {
      for ( $i=0; $i<pg_NumRows($rid); $i++ ) {
        $sys_notify = pg_Fetch_Object( $rid, $i );

        if ( !$in_notify || strcmp( $sys_notify->user_no, $requsr->user_no) ) {
          $query = "SELECT set_interested( $sys_notify->user_no, $request_id ); ";
          $rrid = awm_pgexec( $wrms_db, $query, "req-action", true, 7 );
          if ( ! $rrid ) {
//            $because .= "<H3>System Manager Interest Failed!</H3>\n";
//            $because .= "<P>The error returned was:</P><TT>" . pg_ErrorMessage( $wrms_db ) . "</TT>";
//            $because .= "<P>The failed query was:</P><TT>$query</TT>";
//            awm_pgexec( $wrms_db, "ROLLBACK;", "req-action" );
            return;
          }
        }
      }
    }

    $query = "SELECT * FROM system_usr, usr ";
    $query .= "WHERE system_usr.system_code = '$new_system_code' ";
    $query .= "AND system_usr.role = 'C' " ;
    $query .= "AND system_usr.user_no = usr.user_no " ;
    $query .= "AND usr.org_code=$requsr->org_code; " ;
    $rid = awm_pgexec( $wrms_db, $query, "req-action", true, 7);
    if ( ! $rid  ) {
//      $because .= "<P>Query failed:</P><P>$query</P>";
//      awm_pgexec( $wrms_db, "ROLLBACK;", "req-action" );
      return;
    }
    else if (!pg_NumRows($rid) )
      $because .= "<P><B>Warning: </B> No system organisational coordinator for '$new_system_code'</P>";
    else {
      for ( $i=0; $i<pg_NumRows($rid); $i++ ) {
        $sys_notify = pg_Fetch_Object( $rid, $i );

        if ( !$in_notify || strcmp( $sys_notify->user_no, $requsr->user_no) ) {
          $query = "SELECT set_interested( $sys_notify->user_no, $request_id ); ";
          $rrid = awm_pgexec( $wrms_db, $query, "req-action" );
          if ( ! $rrid ) {
            $because .= "<H3>Organisational Cooordinator Interest Failed!</H3>\n";
            $because .= "<P>The error returned was:</P><TT>" . pg_ErrorMessage( $wrms_db ) . "</TT>";
            $because .= "<P>The failed query was:</P><TT>$query</TT>";
            awm_pgexec( $wrms_db, "ROLLBACK;", "req-action" );
            return;
          }
        }
      }
    }

    $because .= "<H2>Your request number for enquiries is $request_id.</H2>";
    $send_some_mail = TRUE;
  }


  // Looks like we made it through that transaction then...
  awm_pgexec( $wrms_db, "END;", "req-action" );

  ////////////////////////////////////////////////////////
  // Assignment of work request happens to new or old jobs
  ////////////////////////////////////////////////////////
  if ( isset( $new_assigned ) && $new_assigned != "" ) {
    $query = "SELECT set_interested( $new_assigned, $request_id ); ";
    $query .= "SELECT set_allocated( $new_assigned, $request_id )";
    $rid = awm_pgexec( $wrms_db, $query, "req-action" );
    if ( ! $rid ) {
      $because .= "<H3>Work Assignment Failed!</H3>\n";
      $because .= "<P>The error returned was:</P><TT>" . pg_ErrorMessage( $wrms_db ) . "</TT>";
      $because .= "<P>The failed query was:</P><TT>$query</TT>";
      awm_pgexec( $wrms_db, "ROLLBACK;", "req-action" );
      return;
    }
    $because .= "<H2>Assignment of User # $new_assigned to WR #$request_id</H2>";
  }

  include("$base_dir/inc/getrequest.php");

  if ( $send_some_mail ) {
    //////////////////////////////////////////////
    // Work out what to tell and who to tell it to
    //////////////////////////////////////////////
    $send_to = notify_emails( $wrms_db, $request_id );
    $because .="<p>Details of the changes, along with future notes and status updates will be e-mailed to the following addresses:<br>";
    $because .= htmlentities($send_to) . "</p>";

    $msub = "WR #$request_id [$session->username] $chtype" . "d: " . stripslashes($request->brief);
    $msg = "Request No.:  $request_id\n"
         . "Overview:     $request->brief\n";

    if ( isset($previous) && "$request->brief" != "$previous->brief" ) {
      $msg .= "          (was: $previous->brief)\n";
    }

    $msg .= "Urgency:      $request->urgency_desc\n"
          . "Importance:   $request->importance_desc\n";

    if ( $chtype == "change" )
      $msg .= "Request On:   $request->request_on\n";

    $msg .= ucfirst($chtype) . "d by:   $session->fullname\n";

    if ( $requsr->user_no <> $session->user_no )
      $msg .= ucfirst($chtype) . "d for:  $requsr->fullname\n";

    $msg .= ucfirst($chtype) . "d on:   " . date( "D d M H:i:s Y" ) . "\n\n";

    if ( $status_changed ) {
      $rid = awm_pgexec( $wrms_db, "SELECT get_status_desc('$new_status')", "req-action" );
      $msg .= "New Status:   $new_status - " . pg_Result( $rid, 0, 0) . " (previous status was $previous->last_status - $previous->status_desc)\n";
    }

    if ( isset($new_active) && $request->active != $new_active ) {
      $msg .= "Request has been ";
      if ( $new_active == "TRUE" ) $msg .= "re-"; else $msg .= "de-";
      $msg .= "activated\n";
    }

    if ( isset($previous) && "$request->eta" <> "$previous->eta" )  {
      $msg .= "New ETA:      $new_eta";
      if ( "$request->eta" != "" ) $msg .= "  (previous ETA was " . substr( nice_date($previous->eta), 7) . ")";
      $msg .= "\n";
    }
    if ( $quote_added )
      $msg .= "Quotation:    A new quote has been entered against this request.\n";

    if ( $allocation_added )
      $msg .= "Work Allocated:    $work->fullname has been allocated to work on this request.\n";


    if ( $chtype == "change" && $request->detailed != $previous->detailed ) {
      $msg .= "\nPrevious Description:\n"
            . "====================\n"
            . stripslashes($previous->detailed) . "\n\n";
    }

    if ( $chtype == "create" || ( $chtype == "change" && $request->detailed != $previous->detailed ) )
      $msg .= "\nDetailed Description:\n"
            . "====================\n"
            . stripslashes($request->detailed) . "\n\n";

    if ( $note_added )
      $msg .= "\nAdditional Notes:\n"
            . "================\n"
            . stripslashes($new_note) . "\n\n";

    $msg .= "\nFull details of the request, with all changes and notes, can be reviewed and changed at:\n"
         .  "    http://$HTTP_HOST$base_url/request.php?request_id=$request_id\n";

     $other = "From: Catalyst Work Requests <wrms@catalyst.net.nz>\n";
     $other .= "Reply-To: $session->fullname <$session->email>\n";
     $other .= "Errors-To: wrmsadmin@catalyst.net.nz";
    mail( $send_to, $msub, $msg,  $other );
  }
?>