<?php
  $author = FALSE;
  $allocated_to = FALSE;
  $cltmgr = FALSE;
  $sysmgr = FALSE;

  if ( isset($request) && $request->requester_id == $session->user_no ) $author = TRUE;

  /* Has the person been allocated this request? */
  $query = "SELECT * FROM request_allocated WHERE request_allocated.allocated_to_id= $session->user_no";
  $rid = awm_pgexec( $wrms_db, $query);
  if ( ! $rid ) {
    $error_loc = "get-request-roles.php";
    $error_qry = "$query";
    include("inc/error.php");
  }
  if ( $rid && pg_NumRows($rid) > 0 ) $allocated_to = TRUE;

  /* Is the person client or support manager for this (or any?) system? */
  $query = "SELECT * FROM system_usr WHERE system_usr.user_no=$session->user_no";
  $query .= " AND system_usr.role ~ '[CS]' ";
  if ( isset($request) )
    $query .= " AND system_usr.system_code = '$request->system_code' ";
  $rid = awm_pgexec( $wrms_db, $query);
  if ( ! $rid ) {
    $error_loc = "get-request-roles.php";
    $error_qry = "$query";
    include("inc/error.php");
  }
  if ( $rid && pg_NumRows($rid) > 0 ) $cltmgr = TRUE;

  /* Is the person client or support manager for this (or any?) organisation? */
  $query = "SELECT * FROM org_usr WHERE org_usr.user_no=$session->user_no";
  $query .= " AND org_usr.role~'[CS]' ";
  if ( isset($request) )
    $query .= " AND org_usr.org_code = '$request->org_code' ";
  $rid = awm_pgexec( $wrms_db, $query);
  if ( ! $rid ) {
    $error_loc = "get-request-roles.php";
    $error_qry = "$query";
    include("inc/error.php");
  }
  if ( $rid && pg_NumRows($rid) > 0 ) $sysmgr = TRUE;

  // Also set $sysmgr if the person is Admin...
  if ( $roles['wrms']['Admin'] ) $sysmgr = TRUE;
?>
