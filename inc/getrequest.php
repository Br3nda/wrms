<?php
  if ( isset($request_id) && $request_id > 0 ) {

    /* Complex request mainly because we hook in all of the codes tables for easy display */
    $rows = 0;
    // Note: careful adjustment of the field order - work_system and request both have 'active' e.g.
    $query = "SELECT organisation.*, usr.*, work_system.*, request.*";
    $query .= ", to_char( request.requested_by_date, 'dd/mm/yyyy' ) AS requested_by_date, to_char( request.agreed_due_date, 'dd/mm/yyyy' ) AS agreed_due_date ";
    $query .= ", status.lookup_desc AS status_desc";
    $query .= ", request_type.lookup_desc AS request_type_desc";
    $query .= ", urgency.lookup_desc AS urgency_desc";
    $query .= ", sla_response.lookup_desc AS sla_response_desc";
    $query .= ", importance.lookup_desc AS importance_desc";
    $query .= ", system_desc, request_sla_code(sla_response_time,sla_response_type) ";
    $query .= " FROM request, usr, organisation";
    $query .= ", lookup_code AS status";
    $query .= ", lookup_code AS request_type";
    $query .= ", lookup_code AS urgency";
    $query .= ", lookup_code AS sla_response";
    $query .= ", lookup_code AS importance";
    $query .= ", work_system ";
    $query .= " WHERE request.request_id = '$request_id'";
    $query .= " AND request.requester_id = usr.user_no ";
    $query .= " AND organisation.org_code = usr.org_code ";
    if (! ($roles['wrms']['Admin'] || $roles['wrms']['Support']) )
      $query .= " AND organisation.org_code = '$session->org_code' ";
    $query .= " AND status.source_table='request' AND status.source_field='status_code' AND status.lookup_code = request.last_status";
    $query .= " AND request_type.source_table='request' AND request_type.source_field='request_type' AND request.request_type = request_type.lookup_code";
    $query .= " AND urgency.source_table='request' AND urgency.source_field='urgency' AND int4(urgency.lookup_code)=request.urgency";
    $query .= " AND sla_response.source_table='request' AND sla_response.source_field='sla_response' AND sla_response.lookup_code=request_sla_code(sla_response_time,sla_response_type) ";
    $query .= " AND importance.source_table='request' AND importance.source_field='importance' AND int4(importance.lookup_code)=request.importance";
    $query .= " AND work_system.system_code=request.system_code";

    /* now actually query the database... */
    $rid = awm_pgexec( $wrms_db, $query, "getrequest", false, 7);
    if ( ! $rid ) {
      $error_loc = "request-form.php";
      $error_qry = "$query";
      include("inc/error.php");
    }
    $rows = pg_NumRows($rid);
    if ( ! $rows ) {
      /* We do a really basic query to make sure we actually get the request */
//      $query = "SELECT * FROM request WHERE request_id='$request_id' ";
//      $rid = awm_pgexec( $wrms_db, $query);
//      if ( ! $rid ) {
//        $error_loc = "request-form.php";
//        $error_qry = "$query";
//        include("inc/error.php");
//      }
      echo "<p>No records for: $query</p>";
      // header("Location: $base_url");  /* Redirect browser to login page */
      exit; /* Make sure that code below does not get executed when we redirect. */
    }
    $request = pg_Fetch_Object( $rid, 0 );
  }

  /* get the user's roles relative to the current request */
  include( "$base_dir/inc/get-request-roles.php");

  if ( strtolower("$request->active") == "t" ) $request->active = "TRUE";

  /* Current request is editable if the user requested it or user is sysmgr, cltmgr or allocated the job */
  if ( ! isset($plain) && isset($style) ) $plain = ("$style" == "plain");
  $statusable = /* isset($request) && */ ($author || $sysmgr || $cltmgr || $allocated_to );
  $quotable = $statusable;
  $editable = ($sysmgr || $allocated_to || ! isset($request_id) );
  if ( $editable ) $editable = ! $plain;

  error_log( "getrequest: plain=$plain, editable=$editable, statusable=$statusable, cltmgr=$cltmgr, sysmgr=$sysmgr, author=$author, allocated_to=$allocated_to, request_id=$request_id", 0);

?>
