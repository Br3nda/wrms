<?php
  include( "awm-auth.php3" );
  $title = "Status / Notes Changed WR#$request_id";
  include("$homedir/apms-header.php3"); 
  include( "$funcdir/tidy-func.php3" );
  include( "$funcdir/notify_emails-func.php3" );
  require( "$funcdir/nice_date-func.php3");
  if ( $was ) error_reporting($was);
  
  /* Other parameters beside dbname and port are host, tty, options, user and password */
  pg_exec( "BEGIN;" );

  $query = "SELECT * FROM request ";
  $query .= "WHERE request.request_id = '$request_id'";
  $rid = pg_exec( $dbid, $query);
  if ( ! $rid ) {
    $errmsg = pg_ErrorMessage( $dbid );
    echo "<H3>&nbsp;Update of Request $request_id Failed!</H3>\n";
    echo "<P>The error returned was:</P><PRE>" . pg_ErrorMessage( $dbid ) . "</PRE>";
    echo "<P>The failed query was:</P><PRE>$query</PRE>";
    pg_exec( "ROLLBACK;" );
    include("$homedir/apms-footer.php3");
    exit;
  }
  $request = pg_Fetch_Object( $rid, 0);

  /* get the user's roles relative to the current request */
  include( "$funcdir/get-request-roles.php3");

  $to_active = 0;
  $to_inactive = 0;
  $status_changed = 0;
  $eta_changed = 0;
  $note_added = strcmp( "", $new_note );
  if ( $sysmgr || $cltmgr || $allocated_to ) {
    $status_changed = strcmp( $request->last_status, $new_status );
    $old_eta = substr( nice_date($request->eta), 7);
    $eta_changed = strcmp("$old_eta", "$new_eta") && strcmp( "$new_eta", "");
    if ( $status_changed ) {
      $query = "INSERT INTO request_status (request_id, status_by, status_on, status_code, status_by_id) ";
      $query .= "VALUES( $request_id, '$usr->username', 'now', '$new_status', $usr->perorg_id)";
      $rid = pg_exec( $dbid, $query );
      if ( ! $rid ) {
        $errmsg = pg_ErrorMessage( $dbid );
        echo "<H3>&nbsp;Status Change Failed!</H3>\n";
        echo "<P>The error returned was:</P><PRE>" . pg_ErrorMessage( $dbid ) . "</PRE>";
        echo "<P>The failed query was:</P><PRE>$query</PRE>";
        pg_exec( "ROLLBACK;" );
        include("$homedir/apms-footer.php3");
        exit;
      }
      echo "<H3>Status Change Saved!</H3>\n";
    }

    if ( $status_changed || $eta_changed ) {
      $query = "UPDATE request SET last_status = '$new_status'";
      if ( $eta_changed && ( $sysmgr || $allocated_to ) )  $query .= ", eta = '$new_eta'";
      if ( eregi( "[fhc]", "$new_status") ) {
        $query .= ", active = 'f'";
        $to_inactive = $request->active;
      }
      else {
        $query .= ", active = 't'";
        $to_active = ! $request->active;
      }
      $query .= " WHERE request_id = $request_id";
      $rid = pg_exec( $dbid, $query );
      if ( ! $rid ) {
        $errmsg = pg_ErrorMessage( $dbid );
        echo "<H3>&nbsp;Update of Request.Last_Status Failed!</H3>\n";
        echo "<P>The error returned was:</P><PRE>" . pg_ErrorMessage( $dbid ) . "</PRE>";
        echo "<P>The failed query was:</P><PRE>$query</PRE>";
        pg_exec( "ROLLBACK;" );
        include("$homedir/apms-footer.php3");
        exit;
      }
      if ( $eta_changed ) echo "<H3>Status/ETA Saved to Request!</H3>\n";
    }
  }

  if ( $note_added ) {
    /* non-null note was entered */
    $query = "INSERT INTO request_note (request_id, note_by, note_on, note_detail) ";
    $qnote = str_replace("'", "''", $new_note);
    $query .= "VALUES( $request_id, '$usr->username', 'now', '" . tidy($new_note) . "')";
    $rid = pg_exec( $dbid, $query );
    if ( ! $rid ) {
      $errmsg = pg_ErrorMessage( $dbid );
      echo "<H3>&nbsp;Status Change Failed!</H3>\n";
      echo "<P>The error returned was:</P><PRE>" . pg_ErrorMessage( $dbid ) . "</PRE>";
      echo "<P>The failed query was:</P><PRE>$query</PRE>";
      pg_exec( "ROLLBACK;" );
      include("$homedir/apms-footer.php3");
      exit;
    }
    echo "<H3>New Notes Saved!</H3>\n";
  }
  pg_exec( "END;" );

  if ( $note_added || $status_changed || $eta_changed || $to_inactive || $to_active ) {
    $mto = notify_emails( $dbid, $request->request_id);
    $msg .= "Request No.: $request->request_id\n";
    $msg .= "Request On:  $request->request_on\n";
    $msg .= "Overview:    " . stripslashes($request->brief) . "\n\n";
    $msg  = "Changed by:  $usr->fullname\n";
    $msg  = "Changed on:  " . date( "D d M H:i:s Y" ) . "\n";
    $msub = "WR #$request->request_id[$usr->username] changed: ";
    if ( $status_changed ) {
      $rid = pg_Exec( $dbid, "SELECT get_status_desc('$new_status')" );
      $msg .= "New Status:  $new_status - " . pg_Result( $rid, 0, 0) . " (previous status was $request->last_status - $request->status_desc)\n\n";
      $msub .= pg_Result( $rid, 0, 0) . ", ";
    }
    if ( $to_active )           $msg .= "Request has been re-activated.\n";
    if ( $to_inactive )         $msg .= "Request has been de-activated.\n";
    if ( $eta_changed )  {
      $msg .= "New ETA:  $new_eta";
      if ( strcmp("", "$request->eta") ) $msg .= "(was $request->eta)";
      $msg .= "\n";
    }
    if ( $note_added )          $msg .= "New Notes:\n" . stripslashes($new_note) . "\n\n";

    $msg .= "\nFull details of the request, with all changes and notes, can be reviewed and changed at:\n"
               . "    $wrms_home/view-request.php3?request_id=$request->request_id\n";

    mail( $mto, $msub . $request->brief, $msg, "From: catalyst-wrms@cat-it.co.nz\nReply-To: $usr->email" );
    echo "<H2>Request '$request_id' has been changed.</H2>";
    echo "<P>The details of the changes, along with future notes and status updates will be e-mailed to the following currently registered people:<BR>&nbsp;&nbsp;&nbsp;'$mto'.</P>";
	
  }
  else {
    echo "<H2>Request $request_id was not changed</H2>";
  }

  include("$homedir/apms-footer.php3");
?>
