<?php
  include( "awm-auth.php3" );
  $title = "Work Request Submitted";
  include("$homedir/apms-header.php3");
  include("$funcdir/tidy-func.php3");

  /* Other parameters beside dbname and port are host, tty, options, user and password */
  if ( $usr->access_level < 80000 ) {
    $in_username = $usr->username;
    $in_assigned = "";
  }
  error_reporting(2);
  pg_exec( $dbid, "BEGIN;" );
  $query =  "SELECT *, awm_get_rel_parent(awm_usr.perorg_id, 'Employer') AS org_id FROM awm_usr WHERE awm_usr.username = '$in_username';";
  echo "<P>$query</P>";
  $rid = pg_exec( $dbid, $query );
  if ( ! $rid ) {
    echo "<H3>&nbsp;New Request Failed!</H3>\n";
    echo "<P>The error returned was:</P><TT>" . pg_ErrorMessage( $dbid ) . "</TT>";
    echo "<P>The failed query was:</P><TT>$query</TT>";
    pg_exec( "ROLLBACK;" );
    include("apms-footer.php3");
    exit;
  }
  $rusr = pg_Fetch_Object( $rid, 0);

  $query = "INSERT INTO request (request_by, brief, detailed, active, last_status, severity_code, system_code, request_type, requester_id) ";
  $query .= "VALUES( '$in_username', '" . tidy($in_brief) . "','" . tidy($in_detail) . "', TRUE, 'N', $in_severity, '$in_system' , '$in_type', $rusr->perorg_id )";
  echo "<P>$query</P>";
  $rid = pg_exec( $dbid, $query );
  if ( ! $rid ) {
    echo "<H3>&nbsp;New Request Failed!</H3>\n";
    echo "<P>The error returned was:</P><TT>" . pg_ErrorMessage( $dbid ) . "</TT>";
    echo "<P>The failed query was:</P><TT>$query</TT>";
    pg_exec( "ROLLBACK;" );
    include("apms-footer.php3");
    exit;
  }

  $rid = pg_exec( "SELECT last_value FROM request_request_id_seq;" );
  $request_id = pg_Result( $rid,  0, 0);

  $query = "INSERT INTO request_status (request_id, status_by, status_on, status_code, status_by_id) ";
  $query .= "VALUES( $request_id, '$rusr->username', 'now', 'N', $usr->perorg_id)";
  echo "<P>$query</P>";
  $rid = pg_exec( $dbid, $query );

  $rid = pg_exec( "SELECT * FROM request WHERE request.request_id = $request_id;" );
  $request = pg_Fetch_Object( $rid, 0);

  if ( $in_notify )
    $rid = pg_Exec( $dbid, "INSERT INTO request_interested (request_id, username ) VALUES( $request_id, '$rusr->username') ");

  if ( $in_notify ) {
      $query = "SELECT set_perreq_role( $rusr->perorg_id, $request_id, 'INTRST')";
  echo "<P>$query</P>";
      $rid = pg_exec( $dbid, $query );
      if ( ! $rid ) {
        echo "<H3>&nbsp;Submit Interest Failed!</H3>\n";
        echo "<P>The error returned was:</P><TT>" . pg_ErrorMessage( $dbid ) . "</TT>";
        echo "<P>The failed query was:</P><TT>$query</TT>";
      }
    }

  $query = "SELECT * FROM perorg_system ";
  $query .= "WHERE perorg_system.system_code = '$request->system_code' ";
  $query .= "AND perorg_system.persys_role = 'SYSMGR' " ;
  echo "<P>$query</P>";
  $rid = pg_Exec( $dbid, $query);
  if ( ! $rid  )
    echo "<P>Query failed:</P><P>$query</P>";
  else if (!pg_NumRows($rid) )
    echo "<P><B>Warning: </B> No system maintenance manager for '$request->system_code'</P>";
  else {
    for ( $i=0; $i<pg_NumRows($rid); $i++ ) {
      $sys_notify = pg_Fetch_Object( $rid, $i );

      if ( !$in_notify || strcmp( $sys_notify->perorg_id, $rusr->perorg_id) ) {
        $query = "SELECT set_perreq_role( $sys_notify->perorg_id, $request_id, 'INTRST')";
  // echo "<P>$query</P>";
        $rid = pg_exec( $dbid, $query );
        if ( ! $rid ) {
          echo "<H3>&nbsp;SYSMGR Interest Failed!</H3>\n";
          echo "<P>The error returned was:</P><TT>" . pg_ErrorMessage( $dbid ) . "</TT>";
          echo "<P>The failed query was:</P><TT>$query</TT>";
        }
      }
    }
  }
  $query = "SELECT awm_perorg_rel.perorg_rel_id AS perorg_id FROM perorg_system, awm_perorg_rel ";
  $query .= " WHERE persys_role = 'CLTMGR' AND system_code = '$request->system_code' ";
  $query .= " AND awm_perorg_rel.perorg_rel_id = perorg_system.perorg_id AND perorg_rel_type = 'Employer' ";
  $query .= " AND  awm_perorg_rel.perorg_id = $rusr->org_id " ;
/*  $query = "SELECT * FROM perorg_system ";
  $query .= "WHERE perorg_system.system_code = '$request->system_code' ";
  $query .= "AND perorg_system.persys_role = 'CLTMGR' " ; */
  echo "<P>$query</P>";
  $rid = pg_Exec( $dbid, $query);
  if ( ! $rid  )
    echo "<P>Query failed:</P><TT>$query</TT>";
  else if (!pg_NumRows($rid) )
    echo "<P><B>Warning: </B> No client organisation manager for '$request->system_code'</P>";
  else {
    for ($i=0; $i <pg_NumRows($rid); $i++ ) {
      $admin_notify = pg_Fetch_Object( $rid, $i );

      $query = "SELECT set_perreq_role( $admin_notify->perorg_id, $request_id, 'INTRST')";
  echo "<P>$query</P>";
      $rid = pg_exec( $dbid, $query );
      if ( ! $rid && $i == 0 ) {
        echo "<H3>&nbsp;Admin Interest Failed!</H3>\n";
        echo "<P>The error returned was:</P><TT>" . pg_ErrorMessage( $dbid ) . "</TT>";
        echo "<P>The failed query was:</P><TT>$query</TT>";
      }
    }
  }

  if ( isset( $in_assigned ) && $in_assigned != "" ) {
    $query = "SELECT *, employer.perorg_name AS org_code FROM awm_perorg AS employer, awm_perorg AS person ";
    $query .= "WHERE person.perorg_id = $in_assigned ";
    $query .= "AND employer.perorg_id = awm_get_rel_parent( person.perorg_id, 'Employer') ";
  echo "<P>$query</P>";
    $rid = pg_exec( $dbid, $query );
    if ( ! $rid )
      echo "<P>Query failed:</P><TT>$query</TT>";
    else {
      $assignee = pg_Fetch_Object( $rid, 0 );
    }

    $query = "SELECT set_perreq_role( $in_assigned, $request_id, 'ALLOC' )";
  echo "<P>$query</P>";
    $rid = pg_exec( $dbid, $query );
    if ( ! $rid ) {
      echo "<H3>&nbsp;New Assignment Failed!</H3>\n";
      echo "<P>The error returned was:</P><TT>" . pg_ErrorMessage( $dbid ) . "</TT>";
      echo "<P>The failed query was:</P><TT>$query</TT>";
      pg_exec( "ROLLBACK;" );
      include("$homedir/apms-footer.php3");
      exit;
    }

    echo "<H2>Assignment of $assignee->perorg_name ($assignee->org_code) to WR #$request_id</H2>";

    $query = "SELECT set_perreq_role( $in_assigned, $request_id, 'INTRST' )";
  echo "<P>$query</P>";
    $rid = pg_exec( $dbid, $query );
    if ( ! $rid ) {
      echo "<H3>&nbsp;Interested in Assignment Failed!</H3>\n";
      echo "<P>The error returned was:</P><TT>" . pg_ErrorMessage( $dbid ) . "</TT>";
      echo "<P>The failed query was:</P><TT>$query</TT>";
    }
  }

  pg_exec( $dbid, "END;" );
  include("$funcdir/notify_emails-func.php3");

  $send_to = notify_emails( $dbid, $request_id );
  $subject = "WR #$request->request_id[$rusr->username]: $request->brief";
  $message = "Submitted by:  $rusr->fullname\n"
           . "Request No.:   $request->request_id\n"
           . "Request On:     $request->request_on\n"
           . "Overview:      $request->brief\n\n"
           . "Detailed Description:\n$request->detailed\n\n"
           . "The request can be reviewed and changed at:\n"
           . "    $wrms_home/modify-request.php3?request_id=$request->request_id\n";

  mail( $send_to, $subject, $message, "From: catalyst-wrms@cat-it.co.nz\nReply-To: $rusr->email" );

  echo "<H2>Your request number for enquiries is $request_id.</H2>";
  echo "<P>The details of your request, along with future notes and status updates will be e-mailed to the following addresses:<BR>&nbsp;&nbsp;&nbsp;'$send_to'.</P>";

  include("$homedir/apms-footer.php3"); 
?>
