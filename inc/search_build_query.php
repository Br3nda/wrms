<?php
  /////////////////////////////////////////////////////////////////////////////////////////////////
  //
  // Now we build the statement that will find those requests...
  //
  /////////////////////////////////////////////////////////////////////////////////////////////////

  $query = "";
  if ( isset($qry) && "$qry" != "" ) {
    $qquery = "SELECT * FROM saved_queries WHERE user_no = '$session->user_no' AND query_name = '".tidy($qry)."';";
    $result = awm_pgexec( $dbconn, $qquery, "requestlist", false, 7);
    $thisquery = pg_Fetch_Object( $result, 0 );
    $query = $thisquery->query_sql ;
    // If the maxresults they saved was non-default, use that, otherwise we
    // increase the default anyway, because saved queries are more carefully
    // crafted, and less likely to list the whole database
    $maxresults = ( $maxresults == 100 && $thisquery->maxresults != 100 ? $thisquery->maxresults : 500 );
    if ( $thisquery->rlsort && ! isset($_GET['rlsort']) ) {
      $rlsort = $thisquery->rlsort;
      $rlseq = $thisquery->rlseq;
    }
  }
  else {
    $query .= "SELECT request.request_id, brief, usr.fullname, usr.email, request_on, status.lookup_desc AS status_desc, last_activity, detailed ";
    $query .= ", request_type.lookup_desc AS request_type_desc, lower(usr.fullname) AS lfull, lower(brief) AS lbrief ";
    $query .= ", to_char( request.last_activity, 'FMdd Mon yyyy') AS last_change ";
    $query .= ", to_char( request.request_on, 'FMdd Mon yyyy') AS date_requested";
    //provides extra fields that are needed to create a Brief (editable) report
    $query .= ", active, last_status ";
    $query .= ", creator.email AS by_email, creator.fullname AS by_fullname, lower(creator.fullname) AS lby_fullname ";
    $query .= "FROM ";
    if ( intval("$interested_in") > 0 ) $query .= "request_interested, ";
    if ( intval("$allocated_to") > 0 ) $query .= "request_allocated, ";
    $query .= "request, usr, lookup_code AS status ";
    $query .= ", lookup_code AS request_type";
    $query .= ", usr AS creator";

    $query .= " WHERE request.requester_id=usr.user_no AND request.entered_by=creator.user_no ";
    $query .= " AND request_type.source_table='request' AND request_type.source_field='request_type' AND request.request_type = request_type.lookup_code";
    if ( "$inactive" == "" )        $query .= " AND active ";
    if ( ! is_member_of('Admin', 'Support' ) ) {
      $query .= " AND usr.org_code = '$session->org_code' ";
    }
    else if ( isset($org_code) && intval($org_code) > 0 )
      $query .= " AND usr.org_code='$org_code' ";

    if ( intval("$user_no") > 0 )
      $query .= " AND requester_id = " . intval($user_no);
    else if ( intval("$requested_by") > 0 )
      $query .= " AND requester_id = " . intval($requested_by);
    if ( intval("$interested_in") > 0 )
      $query .= " AND request_interested.request_id=request.request_id AND request_interested.user_no = " . intval($interested_in);
    if ( intval("$allocated_to") > 0 )
      $query .= " AND request_allocated.request_id=request.request_id AND request_allocated.allocated_to_id = " . intval($allocated_to);
    else if ( intval("$allocated_to") < 0 )
      $query .= " AND NOT EXISTS( SELECT request_id FROM request_allocated WHERE request_allocated.request_id=request.request_id )";

    if ( "$search_for" != "" ) {
      $query .= " AND (brief ~* '$search_for' ";
      $query .= " OR detailed ~* '$search_for' ) ";
    }
    if ( "$system_code" != "" )     $query .= " AND system_code='$system_code' ";
    if ( "$type_code" != "" )     $query .= " AND request_type=" . intval($type_code);

    if ( "$from_date" != "" )     $query .= " AND request.last_activity >= '$from_date' ";
    if ( "$to_date" != "" )     $query .= " AND request.last_activity<='$to_date' ";

    $query .= " AND status.source_table='request' AND status.source_field='status_code' AND status.lookup_code=request.last_status ";
    if ( $where_clause != "" ) {
      $query .= " AND $where_clause ";
    }

    if ( isset($incstat) && is_array( $incstat ) ) {
      reset($incstat);
      $query .= " AND (request.last_status ~* '[";
      while( list( $k, $v) = each( $incstat ) ) {
        $query .= $k ;
      }
      $query .= "]') ";
      if ( eregi("save", "$submit") && "$savelist" != "" ) {
        $saved_sort = "";
        $saved_seq  = "";
        if ( isset($save_query_order) && $save_query_order ) {
          $saved_sort = $rlsort;
          $saved_seq = $rlseq;
        }
        $savelist = tidy($savelist);
        $qquery   = tidy($query);
        $rlsort   = tidy($rlsort);
        $rlseq    = tidy($rlseq);
        $query = "DELETE FROM saved_queries WHERE user_no = '$session->user_no' AND LOWER(query_name) = LOWER('$savelist');
INSERT INTO saved_queries (user_no, query_name, query_sql, maxresults, rlsort, rlseq)
   VALUES( '$session->user_no', '$savelist', '$qquery', $maxresults, '$rlsort', '$rlseq');
$query";
      }
    }
  }

  $query .= " ORDER BY $rlsort $rlseq ";
  $query .= " LIMIT $maxresults ";
?>