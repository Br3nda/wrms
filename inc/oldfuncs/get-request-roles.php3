<?php
  $allocated_to = FALSE;
  $cltmgr = FALSE;
  $sysmgr = FALSE;

  /* Has the person been allocated this request? */
  $query = "SELECT * FROM perorg_request WHERE perorg_request.perorg_id = $usr->perorg_id";
  $query .= " AND perorg_request.perreq_role = 'ALLOC' ";
/*  echo "<P>The query is:<BR><TT>$query</TT>"; */
  $rid = pg_Exec( $dbid, $query);
  if ( ! $rid ) {
    echo "<H3>Query Error:</H3>";
    echo "<P>The error returned was:<BR><TT>" . pg_ErrorMessage( $dbid ) . "</TT>";
    echo "<P>The failed query was:<BR><TT>$query</TT>";
    include("$homedir/apms-footer.php3");
    exit;
  }
  if ( pg_NumRows($rid) > 0 ) $allocated_to = TRUE;

  /* Is the person client manager for this system? */
  $query = "SELECT * FROM perorg_system WHERE perorg_system.perorg_id = $usr->perorg_id";
  $query .= " AND perorg_system.persys_role = 'CLTMGR' ";
  $query .= " AND perorg_system.system_code = '$request->system_code' ";
/*  echo "<P>The query is:<BR><TT>$query</TT>"; */
  $rid = pg_Exec( $dbid, $query);
  if ( ! $rid ) {
    echo "<H3>Query Error:</H3>";
    echo "<P>The error returned was:<BR><TT>" . pg_ErrorMessage( $dbid ) . "</TT>";
    echo "<P>The failed query was:<BR><TT>$query</TT>";
    include("$homedir/apms-footer.php3");
    exit;
  }
  if ( pg_NumRows($rid) > 0 ) $cltmgr = TRUE;

  /* Is the person been support manager for this system? */
  $query = "SELECT * FROM perorg_system WHERE perorg_system.perorg_id = $usr->perorg_id";
  $query .= " AND perorg_system.persys_role = 'SYSMGR' ";
  $query .= " AND perorg_system.system_code = '$request->system_code' ";
/*  echo "<P>The query is:<BR><TT>$query</TT>"; */
  $rid = pg_Exec( $dbid, $query);
  if ( ! $rid ) {
    echo "<H3>Query Error:</H3>";
    echo "<P>The error returned was:<BR><TT>" . pg_ErrorMessage( $dbid ) . "</TT>";
    echo "<P>The failed query was:<BR><TT>$query</TT>";
    include("$homedir/apms-footer.php3");
    exit;
  }
  if ( pg_NumRows($rid) > 0 ) $sysmgr = TRUE;

?>
