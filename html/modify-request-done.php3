<?php
  include( "awm-auth.php3" );
  $title = "Work Request Modified";
  include("apms-header.php3"); 
  include("$funcdir/tidy-func.php3");
  include("$funcdir/notify_emails-func.php3");

  $query = "SELECT * FROM request, severity ";
  $query .= "WHERE request.request_id = '$request_id'";
  $query .= " AND request.severity_code = severity.severity_code";
  $rid = pg_Exec( $dbid, $query );
  if ( $rid ) $rows = pg_NumRows($rid);
  if ( ! $rid || ! $rows ) {
    echo "<H3>&nbsp;Query Error -request #$request_id not found in database!</H3>";
    echo "<P>The error returned was:</P><PRE>" . pg_ErrorMessage( $dbid ) . "</PRE>";
    echo "<P>The failed query was:</P><PRE>$query</PRE>";
    include("apms-footer.php3");
    exit;
  }
  $request = pg_Fetch_Object( $rid, 0 );

  /* get the user's roles relative to the current request */
  include( "$funcdir/get-request-roles.php3");

  $editable = !strcmp( $request->requester_id, $usr->perorg_id );
  if ( ! $editable ) $editable = ( $sysmgr || $cltmgr || $allocated_to );

  if ( ! $editable ) {
    echo "<H2>Not Authorised</H2>";
    echo "<P>You are not authorised to change details of request #$request_id</P>";
    include("apms-footer.php3");
    exit;
  }

  $new_brief = tidy( $new_brief);
  $new_detail = tidy( $new_detail);
  $changes = strcmp( $request->brief, $new_brief );
  if ( ! $changes ) $changes = strcmp( $request->detailed, $new_detail);
  if ( ! $changes ) $changes = ( $request->request_type != $new_type );
  if ( ! $changes ) $changes = ( $request->severity_code != $new_severity );
  if ( ! $changes ) $changes = ( $request->active != $new_active );

  if ( ! $changes ) {
    echo "<H2>Not Changed</H2>";
    echo "<P>You do not appear to have changed any details of request #$request_id</P>";
    include("apms-footer.php3");
    exit;
  }

  /* scope a transaction to the whole change */
  pg_exec( "BEGIN;" );

  /* take a snapshot of the current record */
  $query = "INSERT INTO request_history SELECT * FROM request WHERE request.request_id = '$request_id'";
  $rid = pg_exec( $dbid, $query );
  if ( ! $rid ) {
    echo "<H3>&nbsp;Snapshot Failed!</H3>\n";
    echo "<P>The error returned was:</P><PRE>" . pg_ErrorMessage( $dbid ) . "</PRE>";
    echo "<P>The failed query was:</P><PRE>$query</PRE>";
    pg_exec( "ROLLBACK;" );
    include("apms-footer.php3");
    exit;
  }

  if ( $new_active <> "TRUE" ) $new_active = "FALSE";
  $query = "UPDATE request SET brief = '$new_brief', detailed = '$new_detail', request_type = $new_type, severity_code = $new_severity, active = $new_active ";
  $query .= "WHERE request.request_id = '$request_id'";
  $rid = pg_exec( $dbid, $query );
  if ( ! $rid ) {
    echo "<H3>&nbsp;Update Request Failed!</H3>\n";
    echo "<P>The error returned was:</P><PRE>" . pg_ErrorMessage( $dbid ) . "</PRE>";
    echo "<P>The failed query was:</P><PRE>$query</PRE>";
    pg_exec( "ROLLBACK;" );
    include("apms-footer.php3");
    exit;
  }
  error_reporting($was);

  pg_exec( "END;" );

  echo "<H2>Modification Successful</H2>";
  echo "<P>Request number $request_id has been modified.</P>";

  $send_to = notify_emails( $dbid, $request_id );

  echo "<P>The details of the changes, along with future notes and status updates will be e-mailed to the following addresses:<BR>&nbsp;&nbsp;&nbsp;'$send_to'.</P>";

  $msub = "WR #$request->request_id[$usr->username] changed: (" . stripslashes($request->brief) . ")";
  $msg = "Changed by:  $usr->fullname\n"
           . "Request No.:   $request->request_id\n"
           . "Request On:     $request->request_on\n"
           . "Overview:      $request->brief\n\n"
           . "Urgency:       $request->severity_code \"$request->severity_desc\"\n\n"
           . "Detailed Description:\n" . stripslashes($request->detailed) . "\n\n\n"
           . "The request can be reviewed and changed at:\n"
           . "    $wrms_home/view-request.php3?request_id=$request->request_id\n";
  mail( $send_to, $msub, $msg, "From: catalyst-wrms@cat-it.co.nz\nReply-To: $usr->email" );

  include("$homedir/apms-footer.php3");
?>
