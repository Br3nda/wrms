<?php
  if ( isset($request_id) && $request_id > 0 ) {

    /* Complex request mainly because we hook in all of the codes tables for easy display */
    $rows = 0;
    $query = "SELECT *";
    $query .= ", status.lookup_desc AS status_desc";
    $query .= ", request_type.lookup_desc AS request_desc";
    $query .= ", severity.lookup_desc AS severity_desc";
    $query .= ", system_desc ";
    $query .= " FROM request, usr";
    $query .= ", lookup_code AS status";
    $query .= ", lookup_code AS request_type";
    $query .= ", lookup_code AS severity";
    $query .= ", work_system ";
    $query .= " WHERE request.request_id = '$request_id'";
    $query .= " AND request.requester_id = usr.user_no ";
    $query .= " AND status.source_table='request' AND status.source_field='status_code' AND status.lookup_code = request.last_status";
    $query .= " AND request_type.source_table='request' AND request_type.source_field='request_type' AND request.request_type = request_type.lookup_code";
    $query .= " AND severity.source_table='request' AND severity.source_field='severity_code' AND severity.lookup_code=request.severity_code";
    $query .= " AND work_system.system_code=request.system_code";

    /* now actually query the database... */
    $rid = pg_Exec( $wrms_db, $query);
    if ( ! $rid ) {
      $error_loc = "request-form.php";
      $error_qry = "$query";
      include("inc/error.php");
    }
    $rows = pg_NumRows($rid);
    if ( ! $rows ) {
      /* We do a really basic query to make sure we actually get the request */
      $query = "SELECT * FROM request WHERE request_id='$request_id' ";
      $rid = pg_Exec( $wrms_db, $query);
      if ( ! $rid ) {
        $error_loc = "request-form.php";
        $error_qry = "$query";
        include("inc/error.php");
      }
      echo "<p>$query</p>";
    }
    $request = pg_Fetch_Object( $rid, 0 );

  }

  /* get the user's roles relative to the current request */
  include( "$base_dir/inc/get-request-roles.php");

  /* Current request is editable if the user requested it or user is sysmgr, cltmgr or allocated the job */
  $plain = ("$style" == "plain");
  $statusable = isset($request) && ($author || $sysmgr || $cltmgr || $allocated_to );
  $quotable = $statusable;
  $editable = ($sysmgr || $allocated_to);
  if ( $editable ) $editable = ! $plain;

?>
