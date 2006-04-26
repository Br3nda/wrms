<?php
  include("always.php");
  require_once("authorisation-page.php");
  $session->LoginRequired();

  require_once("BarClass.php");

  if ( !isset($from_date) || !isset($to_date) ) {
    $finish = localtime();
    $start = $finish;
    $start[4]--;
    if ( $start[4] < 0 ) {
      $start[4] += 12;
      $start[5]--;
    }
    $from_date = strftime( "%Y-%m-%d", mktime( 0, 0, 0, $start[4], 1, $start[5] ) );
    $to_date   = strftime( "%Y-%m-%d", mktime( 0, 0, 0, $finish[4], 1, $finish[5] ) );
  }

  // First, we construct SQL with a two-column result set
  $sql = "SELECT status_lookup.lookup_desc, count(request.request_id) ";
  $sql .= "FROM request JOIN request_status ON request.request_id=request_status.request_id AND status_code=last_status ";
  $sql .= "JOIN lookup_code status_lookup ON status_lookup.source_table='request' AND status_lookup.source_field='status_code' AND status_lookup.lookup_code=last_status ";
  if ( isset($allocated_to) ) {
    $allocated_to = intval($allocated_to);
    $sql .= "JOIN request_allocated ON request.request_id=request_allocated.request_id AND allocated_to_id = $allocated_to ";
  }
  if ( isset($interested_in) ) {
    $interested_in = intval($interested_in);
    $sql .= "JOIN request_interested ON request.request_id=request_interested.request_id AND request_interested.user_no = $interested_in ";
  }
  if ( isset($org_code) ) {
    $org_code = intval($org_code);
    $sql .= "JOIN usr ON request.requester_id=usr.user_no AND usr.org_code = $org_code ";
  }
  $sql .= "WHERE ((last_status IN ('F', 'C') AND status_on BETWEEN ".qpg($from_date)." AND ".qpg($to_date).") ";
  $sql .= "OR last_status NOT IN ('F','C')) ";
  if ( isset($system_id) ) {
    $system_id = qpg($system_id);
    $sql .= "AND request.system_id=$system_id ";
  }
  if ( isset($request_type) ) {
    $request_type = qpg($request_type);
    $sql .= "AND request.request_type=$request_type ";
  }
  if ( isset($requested_by) ) {
    $requested_by = intval($requested_by);
    $sql .= "AND requester_id = $requested_by ";
  }
  $sql .= "GROUP BY status_lookup.lookup_desc ";
  $sql .= "ORDER BY 2 DESC;";

//  echo "$sql";
  $pie = new BarChart( $sql );

/*
  include("headers.php");
*/
  $pie->Render();
/*
  include("footers.php");
*/
  error_reporting(7);
  if ( $debuglevel > 0 ) {
    $total_query_time = sprintf( "%3.06lf", $total_query_time );
    error_log( "$sysabbr total_query_ TQ: $total_query_time URI: $REQUEST_URI", 0);
    $total_time = sprintf( "%3.06lf", duration( $begin_processing, microtime() ));
    error_log( "$sysabbr process_time TT: $total_time      Agent: $HTTP_USER_AGENT Referrer: $HTTP_REFERER  ", 0);
    error_log( "=============================================== Endof $PHP_SELF" );
  }
?>
