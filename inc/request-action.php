<?php
  include("$base_dir/inc/strip-ms.php");

  if ( ! $editable ) {
    $because .= "<H2>Not Authorised</H2>";
    $because .= "<P>You are not authorised to change details of request #$request_id</P>";
  }

  /* scope a transaction to the whole change */
  pg_exec( $wrms_db, "BEGIN;" );

  if ( isset( $request ) ) {
    /////////////////////////////////////
    // Update an existing request
    /////////////////////////////////////
    if ( strtolower($request->active) == "t" ) $request->active = "TRUE";
    $note_added = ($new_note != "");
    $status_changed = ($request->last_status != $new_status );
    $old_eta = substr( nice_date($request->eta), 7);
    $eta_changed = (("$old_eta" != "$new_eta") && ( "$new_eta" != ""));
    $changes =  ($request->brief != $new_brief)
             || ($request->detailed != $new_detail)
             || ($request->request_type != $new_type )
             || ($request->severity_code != $new_severity )
             || ($request->system_code != $new_system_code )
             || ($request->active != $new_active )
             || ($request->last_status != $new_status )
             || $eta_changed || $status_changed ;
    if ( $debuglevel >= 1 )
      echo "<p>---" . ($request->brief != $new_brief) . "-"
             . ($request->detailed != $new_detail) . "+"
             . ($request->request_type != $new_type ) . "-"
             . ($request->severity_code != $new_severity ) . "+"
             . ($request->system_code != $new_system_code ) . "-"
             . ($request->active != $new_active ) . "+"
             . ($request->last_status != $new_status ) . "-"
             . $eta_changed . "+" . $status_changed
             . "---$request->request_type != $new_type</p>" ;

    if ( ! $changes ) {
      $because = "";
      return;
    }

    $new_brief = tidy( $new_brief );
    $new_detail = tidy( $new_detail );
    $new_note = tidy( $new_note );

    /* take a snapshot of the current record */
    $query = "INSERT INTO request_history SELECT * FROM request WHERE request.request_id = '$request_id'";
    $rid = pg_exec( $wrms_db, $query );
    if ( ! $rid ) {
      $because .= "<H3>Snapshot Failed!</H3>\n";
      $because .= "<P>The error returned was:</P><PRE>" . pg_ErrorMessage( $wrms_db ) . "</PRE>";
      $because .= "<P>The failed query was:</P><PRE>$query</PRE>";
      pg_exec( $wrms_db, "ROLLBACK;" );
      return;
    }

    if ( $statusable && $status_changed ) {
      // Changed Status Stuff
      $query = "INSERT INTO request_status (request_id, status_by, status_on, status_code, status_by_id) ";
      $query .= "VALUES( $request_id, '$session->username', 'now', '$new_status', $session->user_no)";
      $rid = pg_exec( $wrms_db, $query );
      if ( ! $rid ) {
        $errmsg = pg_ErrorMessage( $wrms_db );
        $because .= "<H3>&nbsp;Status Change Failed!</H3>\n";
        $because .= "<P>The error returned was:</P><PRE>" . pg_ErrorMessage( $wrms_db ) . "</PRE>";
        $because .= "<P>The failed query was:</P><PRE>$query</PRE>";
        pg_exec( $wrms_db, "ROLLBACK;" );
        return;
      }
      if ( eregi( "[fhc]", "$new_status") )
        $new_active = "FALSE";
      else
        $new_active = "TRUE";
    }

    if ( $new_active <> "TRUE" ) $new_active = "FALSE";
    $query = "UPDATE request SET brief = '$new_brief', detailed = '$new_detail',";
    if ( ($sysmgr || $allocated_to) && $eta_changed ) $query .= " eta = '$new_eta',";
    $query .= " active = $new_active, last_status = '$new_status',";
    $query .= " request_type = $new_type, severity_code = $new_severity,";
    $query .= " system_code = '$new_system_code' ";
    $query .= "WHERE request.request_id = '$request_id'";
    $rid = pg_exec( $wrms_db, $query );
    if ( ! $rid ) {
      $because .= "<H3>&nbsp;Update Request Failed!</H3>\n";
      $because .= "<P>The error returned was:</P><PRE>" . pg_ErrorMessage( $wrms_db ) . "</PRE>";
      $because .="<P>The failed query was:</P><PRE>$query</PRE>";
      pg_exec( $wrms_db, "ROLLBACK;" );
      return;
    }
    else if ( $debuglevel > 0 ) {
      $because .="<P>The query was:</P><TT>$query</TT>";
    }

    if ( $note_added ) {
      /* non-null note was entered */
      $query = "INSERT INTO request_note (request_id, note_by, note_by_id, note_on, note_detail) ";
      $query .= "VALUES( $request_id, '$session->username', $session->user_no, 'now', '" . tidy($new_note) . "')";
      $rid = pg_exec( $wrms_db, $query );
      if ( ! $rid ) {
        $errmsg = pg_ErrorMessage( $wrms_db );
        $because .= "<H3>&nbsp;Status Change Failed!</H3>\n";
        $because .= "<P>The error returned was:</P><PRE>" . pg_ErrorMessage( $wrms_db ) . "</PRE>";
        $because .= "<P>The failed query was:</P><PRE>$query</PRE>";
        pg_exec( $wrms_db, "ROLLBACK;" );
        return;
      }
    }

    $because .= "<h2>Request number $request_id modified.</h2>";

    if ( $statusable && $status_changed )
      $because .= "<p>Request status has been changed.</p>\n";

    if ( $request->active != $new_active ) {
      $because .= "<p>Request has been ";
      if ( $new_active == "TRUE" ) $because .= "re-"; else $because .= "de-";
      $because .= "activated</p>";
    }

    if ( $note_added )
      $because .= "<p>Notes added to request.</p>\n";
  }
  else {
    /////////////////////////////////////
    // Create a new request
    /////////////////////////////////////
    $query = "select nextval('request_request_id_seq');";
    $rid = pg_exec( $wrms_db, $query );
    if ( ! $rid ) {
      $because .= "<P>The failed query was:</P><TT>$query</TT>";
      pg_exec( $wrms_db, "ROLLBACK;" );
      return;
    }
    $request_id = pg_Result( $rid, 0, 0);

    $query = "INSERT INTO request (request_id, request_by, brief, detailed, active, last_status, severity_code, system_code, request_type, requester_id) ";
    $query .= "VALUES( $request_id, '$session->username', '" . tidy($new_brief) . "','" . tidy($new_detail) . "', TRUE, 'N', $new_severity, '$new_system_code' , '$new_request_type', $session->user_no )";
    if ( ! $rid ) {
      $because .= "<P>The failed query was:</P><TT>$query</TT>";
      pg_exec( $wrms_db, "ROLLBACK;" );
      return;
    }

    $query = "INSERT INTO request_status (request_id, status_by, status_on, status_code, status_by_id) ";
    $query .= "VALUES( $request_id, '$session->username', 'now', '$new_status', $session->user_no)";
    $rid = pg_exec( $wrms_db, $query );

    if ( $in_notify ) {
      $rid = pg_Exec( $wrms_db, "INSERT INTO request_interested (request_id, username, user_no ) VALUES( $request_id, '$session->username', $session->user_no) ");
      if ( ! $rid ) {
        $because .= "<H3>&nbsp;Submit Interest Failed!</H3>\n";
        $because .= "<P>The error returned was:</P><TT>" . pg_ErrorMessage( $wrms_db ) . "</TT>";
        $because .= "<P>The failed query was:</P><TT>$query</TT>";
        pg_exec( $wrms_db, "ROLLBACK;" );
        return;
      }
    }

    $query = "SELECT * FROM system_usr, usr ";
    $query .= "WHERE system_usr.system_code = '$new_system_code' ";
    $query .= "AND system_usr.role = 'C' " ;
    $query .= "AND system_usr.user_no = usr.user_no " ;
    $query .= "AND usr.org_code = '$session->org_code' " ;
    $rid = pg_Exec( $wrms_db, $query);
    if ( ! $rid  ) {
      $because .= "<P>Query failed:</P><P>$query</P>";
      pg_exec( $wrms_db, "ROLLBACK;" );
      return;
    }
    else if (!pg_NumRows($rid) )
      $because .= "<P><B>Warning: </B> No system maintenance manager for '$new_system_code'</P>";
    else {
      for ( $i=0; $i<pg_NumRows($rid); $i++ ) {
        $sys_notify = pg_Fetch_Object( $rid, $i );

        if ( !$in_notify || strcmp( $sys_notify->user_no, $session->user_no) ) {
          $query = "SELECT set_interested( $sys_notify->user_no, $request_id )";
          $rid = pg_exec( $wrms_db, $query );
          if ( ! $rid ) {
            $because .= "<H3>System Manager Interest Failed!</H3>\n";
            $because .= "<P>The error returned was:</P><TT>" . pg_ErrorMessage( $wrms_db ) . "</TT>";
            $because .= "<P>The failed query was:</P><TT>$query</TT>";
            pg_exec( $wrms_db, "ROLLBACK;" );
            return;
          }
        }
      }
    }

    $query = "SELECT * FROM org_usr, usr ";
    $query .= "WHERE org_usr.org_code = '$session->org_code' ";
    $query .= "AND org_usr.role = 'C' " ;
    $query .= "AND org_usr.user_no = usr.user_no " ;
    $rid = pg_Exec( $wrms_db, $query);
    if ( ! $rid  ) {
      $because .= "<P>Query failed:</P><P>$query</P>";
      pg_exec( $wrms_db, "ROLLBACK;" );
      return;
    }
    else if (!pg_NumRows($rid) )
      $because .= "<P><B>Warning: </B> No organisation manager for '$session->org_code'</P>";
    else {
      for ($i=0; $i <pg_NumRows($rid); $i++ ) {
        $admin_notify = pg_Fetch_Object( $rid, $i );

        if ( !$in_notify || strcmp( $admin_notify->user_no, $session->user_no) ) {
          $query = "SELECT set_interested( $admin_notify->user_no, $request_id )";
          $rid = pg_exec( $wrms_db, $query );
          if ( ! $rid ) {
            $because .= "<H3>Organisation Manager Interest Failed!</H3>\n";
            $because .= "<P>The error returned was:</P><TT>" . pg_ErrorMessage( $wrms_db ) . "</TT>";
            $because .= "<P>The failed query was:</P><TT>$query</TT>";
            pg_exec( $wrms_db, "ROLLBACK;" );
            return;
          }
        }
      }
    }
    $because .= "<H2>Your request number for enquiries is $request_id.</H2>";
  }


  ////////////////////////////////////////////////////////
  // Assignment of work request happens to new or old jobs
  ////////////////////////////////////////////////////////
  if ( isset( $new_assigned ) && $new_assigned != "" ) {
    $query = "SELECT set_interested( $new_assigned, $request_id );";
    $query .= "SELECT set_allocated( $new_assigned, $request_id )";
    $rid = pg_exec( $wrms_db, $query );
    if ( ! $rid ) {
      $because .= "<H3>Work Assignment Failed!</H3>\n";
      $because .= "<P>The error returned was:</P><TT>" . pg_ErrorMessage( $wrms_db ) . "</TT>";
      $because .= "<P>The failed query was:</P><TT>$query</TT>";
      pg_exec( $wrms_db, "ROLLBACK;" );
      return;
    }
    $because .= "<H2>Assignment of User # $new_assigned to WR #$request_id</H2>";
  }


  // Looks like we made it through that transaction then...
  pg_exec( $wrms_db, "END;" );

  //////////////////////////////////////////////
  // Work out what to tell and who to tell it to
  //////////////////////////////////////////////
  $send_to = notify_emails( $wrms_db, $request_id );
  $because .="<p>Details of the changes, along with future notes and status updates will be e-mailed to the following addresses: &nbsp; $send_to.</p>";

  $msub = "WR #$request->request_id[$session->username] changed: (" . stripslashes($request->brief) . ")";
  $msg = "Request No.:  $request->request_id\n"
       . "Request On:   $request->request_on\n"
       . "Overview:     $request->brief\n\n"
       . "Changed by:   $session->fullname\n"
       . "Changed on:   " . date( "D d M H:i:s Y" ) . "\n"
       . "Urgency:      $request->severity_code \"$request->severity_desc\"\n\n"
       . "Detailed Description:\n" . stripslashes($request->detailed) . "\n\n\n";
  if ( $status_changed ) {
    $rid = pg_Exec( $wrms_db, "SELECT get_status_desc('$new_status')" );
    $msg .= "New Status:   $new_status - " . pg_Result( $rid, 0, 0) . " (previous status was $request->last_status - $request->status_desc)\n\n";
  }
  if ( $request->active != $new_active ) {
    $msg .= "<p>Request has been ";
    if ( $new_active == "TRUE" ) $msg .= "re-"; else $msg .= "de-";
    $msg .= "activated</p>";
  }
  if ( $eta_changed )  {
    $msg .= "New ETA:      $new_eta";
    if ( "$request->eta" != "" ) $msg .= "  (previous ETA was $request->eta)";
    $msg .= "\n";
  }
  if ( $note_added )
    $msg .= "\nAdditional Notes:\n" . stripslashes($new_note) . "\n\n";

  $msg .= "\nFull details of the request, with all changes and notes, can be reviewed and changed at:\n"
       .  "    $wrms_home/modify-request.php3?request_id=$request->request_id\n";

  mail( $send_to, $msub, $msg, "From: catalyst-wrms@cat-it.co.nz\nReply-To: $session->email" );

?>



