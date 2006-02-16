<?php
  include("always.php");
  require_once("authorisation-page.php");
  $session->LoginRequired();

  require_once("PieClass.php");

  $sql = "SELECT status_lookup.lookup_desc, count(request.request_id) ";
  $sql .= "FROM request JOIN request_status ON request.request_id=request_status.request_id AND status_code=last_status ";
  $sql .= "JOIN lookup_code status_lookup ON status_lookup.source_table='request' AND status_lookup.source_field='status_code' AND status_lookup.lookup_code=last_status ";
  if ( isset($allocated_to) ) {
    $allocated_to = intval($allocated_to);
    $sql .= "JOIN request_allocated ON request.request_id=request_allocated.request_id AND allocated_to_id = $allocated_to ";
  }
  if ( isset($org_code) ) {
    $org_code = intval($org_code);
    $sql .= "JOIN usr ON request.requester_id=usr.user_no AND usr.org_code = $org_code ";
  }
  $sql .= "WHERE ((last_status IN ('F', 'C') AND status_on between '2006-01-01' AND '2006-02-01') ";
  $sql .= "OR last_status NOT IN ('F','C')) ";
  if ( isset($system_code) ) {
    $request_type = qpg($system_code);
    $sql .= "AND request.system_code=$system_code ";
  }
  if ( isset($request_type) ) {
    $request_type = qpg($request_type);
    $sql .= "AND request.request_type=$request_type ";
  }
  $sql .= "GROUP BY status_lookup.lookup_desc ";
  $sql .= "ORDER BY 2 DESC;";

//  echo "$sql";
  $pie = new PieChart( $sql );

/*
  include("headers.php");
*/
  $pie->Render();
/*
  include("footers.php");
*/
?>