<?php
  include( "awm-auth.php3" );
  $title = "Register Interest in WR#$request_id";
  include("apms-header.php3"); 

  $query = "SELECT * FROM request, status, request_type, severity, work_system ";
  $query .= "WHERE request.request_id = '$request_id'";
  $query .= " AND request.last_status = status.status_code";
  $query .= " AND request.request_type = request_type.request_type";
  $query .= " AND request.system_code = work_system.system_code";
  $query .= " AND request.severity_code = severity.severity_code";
  $rid = pg_Exec( $dbid, $query);
  if ( $rid ) $rows = pg_NumRows($rid);
  if ( ! $rid || ! $rows ) {
    echo "<H3>&nbsp;Query Error -request #$request_id not found in database!</H3>";
    echo "<P>The error returned was:</P><PRE>" . pg_ErrorMessage( $dbid ) . "</PRE>";
    echo "<P>The failed query was:</P><PRE>$query</PRE>";
    include("apms-footer.php3");
    exit;
  }
  $request = pg_Fetch_Object( $rid, 0 );

  $query = "SELECT * FROM perorg_request WHERE request_id = $request_id ";
  $query .= "AND perorg_id = '$usr->perorg_id' AND perreq_role = 'INTRST' ";
  $rid = pg_exec( $dbid, $query );
  if ( $rid && pg_NumRows($rid) ) {
    $query = "DELETE FROM perorg_request WHERE perorg_request.request_id = $request_id ";
    $query .= "AND perorg_id = '$usr->perorg_id' AND perreq_role = 'INTRST' ";
    $rid = pg_exec( $dbid, $query );
    if ( $rid ) {
      echo "<H2>You have been de-registered for this Work Request</H2>";
    }
    else {
      echo "<H3>&nbsp;Query Error -request #$request_id not found in database!</H3>";
      echo "<P>The error returned was:</P><PRE>" . pg_ErrorMessage( $dbid ) . "</PRE>";
      echo "<P>The failed query was:</P><PRE>$query</PRE>";
    }
    include("apms-footer.php3");
    exit;
  }

  $query = "INSERT INTO perorg_request (request_id, perorg_id, perreq_role ) ";
  $query .= "VALUES( $request_id, '$usr->perorg_id', 'INTRST' )";
  $rid = pg_exec( $dbid, $query );
  if ( ! $rid ) {
    $errmsg = pg_ErrorMessage( $dbid );
    echo "<H3>&nbsp;Status Change Failed!</H3>\n";
    echo "<P>The error returned was:</P><PRE>" . pg_ErrorMessage( $dbid ) . "</PRE>";
    echo "<P>The failed query was:</P><PRE>$query</PRE>";
    include("apms-footer.php3");
    exit;
  }

  echo "<H2>Registered</H2>";
  echo "<P>You will now receive notification of updates to WR# $request_id ($request->brief).</P>";

  include("apms-footer.php3");
?>
