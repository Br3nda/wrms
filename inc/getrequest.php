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
    $query .= " FROM request LEFT OUTER JOIN usr ON (request.requester_id = usr.user_no)";
    $query .= " LEFT OUTER JOIN organisation USING( org_code )";
    $query .= " LEFT OUTER JOIN lookup_code AS status ON status.source_table='request' AND status.source_field='status_code' AND status.lookup_code = request.last_status";
    $query .= " LEFT OUTER JOIN lookup_code AS request_type ON request_type.source_table='request' AND request_type.source_field='request_type' AND request.request_type = request_type.lookup_code";
    $query .= " LEFT OUTER JOIN lookup_code AS urgency ON urgency.source_table='request' AND urgency.source_field='urgency' AND int4(urgency.lookup_code)=request.urgency";
    $query .= " LEFT OUTER JOIN lookup_code AS sla_response ON sla_response.source_table='request' AND sla_response.source_field='sla_response' AND sla_response.lookup_code=request_sla_code(sla_response_time,sla_response_type)";
    $query .= " LEFT OUTER JOIN lookup_code AS importance ON importance.source_table='request' AND importance.source_field='importance' AND int4(importance.lookup_code)=request.importance";
    $query .= " LEFT OUTER JOIN work_system USING( system_code )";
    $query .= " WHERE request.request_id = '$request_id'";
    if (! is_member_of('Admin','Support') ) $query .= " AND organisation.org_code = '$session->org_code' ";

    /* now actually query the database... */
    $rid = awm_pgexec( $dbconn, $query, "getrequest", false, 7);
    if ( ! $rid ) {
      $error_loc = "request-form.php";
      $error_qry = "$query";
      include("error.php");
    }
    $rows = pg_NumRows($rid);
    if ( ! $rows ) {
      echo "<p>No records for request: $request_id</p>";
      if ( is_member_of('Admin','Support') ) {
        echo "<p>There is probably a bug in the following query:</p>";
        echo "<p>$query</p>";
      }
      exit; /* Make sure that code below does not get executed when we redirect. */
    }
    $request = pg_Fetch_Object( $rid, 0 );
    $is_request = true;
  }
  else {
    $is_request = false ;
    $request = "";
  }

  if ( isset( $request->active ) && strtolower("$request->active") == "t" ) $request->active = "TRUE";

  /* get the user's roles relative to the current request */
  include( "get-request-roles.php");

  /* Current request is editable if the user requested it or user is sysmgr, cltmgr or allocated the job */
  if ( ! isset($style) ) $style = "";
  if ( ! isset($plain) && isset($style) ) $plain = ("$style" == "plain");
  $statusable = ($author || $sysmgr || $cltmgr || $allocated_to ) && ! $plain;
  $quotable = $statusable && ! $plain;
  $prioritisable = $statusable && ! $plain;
  $editable = ($sysmgr || $allocated_to || ! $is_request || is_member_of('Admin','Support') );
  if ( $editable ) $editable = ! $plain;

//  error_log( "getrequest: plain=$plain, editable=$editable, statusable=$statusable, cltmgr=$cltmgr, sysmgr=$sysmgr, author=$author, allocated_to=$allocated_to, request_id=$request_id", 0);

?>
