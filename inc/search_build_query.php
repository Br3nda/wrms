<?php
  /////////////////////////////////////////////////////////////////////////////////////////////////
  //
  // Now we build the statement that will find those requests...
  //
  /////////////////////////////////////////////////////////////////////////////////////////////////

  $search_query = "";
  if ( !isset($maxresults) ) $maxresults = 100;
  if ( isset($savedquery) && "$savedquery" != "" ) {
    $qquery = "SELECT * FROM saved_queries WHERE user_no = '$session->user_no' AND query_name = '".tidy($savedquery)."';";
    $result = awm_pgexec( $dbconn, $qquery, "requestlist", false, 7);
    $thisquery = pg_Fetch_Object( $result, 0 );
    $search_query = $thisquery->query_sql ;
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
    error_log("$sysabbr: DBG: Building fresh search query from fields...");
    $search_query .= "SELECT request.request_id, brief, usr.fullname, usr.email, request_on, status.lookup_desc AS status_desc, last_activity, detailed ";
    $search_query .= ", request_type.lookup_desc AS request_type_desc, lower(usr.fullname) AS lfull, lower(brief) AS lbrief ";
    $search_query .= ", to_char( request.last_activity, 'FMdd Mon yyyy') AS last_change ";
    $search_query .= ", to_char( request.request_on, 'FMdd Mon yyyy') AS date_requested";
    //provides extra fields that are needed to create a Brief (editable) report
    $search_query .= ", active, last_status ";
    $search_query .= ", creator.email AS by_email, creator.fullname AS by_fullname, lower(creator.fullname) AS lby_fullname ";
    $search_query .= "FROM ";
    if ( intval("$interested_in") > 0 ) $search_query .= "request_interested, ";
    if ( intval("$allocated_to") > 0 ) $search_query .= "request_allocated, ";
    $search_query .= "request, usr, lookup_code AS status ";
    $search_query .= ", lookup_code AS request_type";
    $search_query .= ", usr AS creator";

    $search_query .= " WHERE request.requester_id=usr.user_no AND request.entered_by=creator.user_no ";
    $search_query .= " AND request_type.source_table='request' AND request_type.source_field='request_type' AND request.request_type = request_type.lookup_code";
    if ( "$inactive" == "" )        $search_query .= " AND active ";
    if ( ! is_member_of('Admin', 'Support' ) ) {
      $search_query .= " AND usr.org_code = '$session->org_code' ";
    }
    else if ( isset($org_code) && intval($org_code) > 0 )
      $search_query .= " AND usr.org_code='$org_code' ";

    if ( intval("$user_no") > 0 )
      $search_query .= " AND requester_id = " . intval($user_no);
    else if ( intval("$requested_by") > 0 )
      $search_query .= " AND requester_id = " . intval($requested_by);
    if ( intval("$interested_in") > 0 )
      $search_query .= " AND request_interested.request_id=request.request_id AND request_interested.user_no = " . intval($interested_in);
    if ( intval("$allocated_to") > 0 )
      $search_query .= " AND request_allocated.request_id=request.request_id AND request_allocated.allocated_to_id = " . intval($allocated_to);
    else if ( intval("$allocated_to") < 0 )
      $search_query .= " AND NOT EXISTS( SELECT request_id FROM request_allocated WHERE request_allocated.request_id=request.request_id )";

    if ( "$search_for" != "" ) {
      $search_query .= " AND (brief ~* '$search_for' ";
      $search_query .= " OR detailed ~* '$search_for' ) ";
    }
    if ( "$system_code" != "" )     $search_query .= " AND system_code='$system_code' ";
    if ( "$type_code" != "" )     $search_query .= " AND request_type=" . intval($type_code);

    if ( "$from_date" != "" )     $search_query .= " AND request.last_activity >= '$from_date' ";
    if ( "$to_date" != "" )     $search_query .= " AND request.last_activity<='$to_date' ";

    $taglist_count = intval($taglist_count);
    error_log( "$sysabbr: DBG: Tag List count: $taglist_count, " . is_array($tag_list) );
    if ( isset($tag_list) && is_array($tag_list) ) {
      $taglist_subquery = "";
      for ( $i=0; $i < $taglist_count ; $i++ ) {
        $tag_id = intval($tag_list[$i]);
        error_log( "$sysabbr: DBG: Tag List[$i]: $tag_id" );
        if ( $tag_id > 0 ) {
          $taglist_subquery .= sprintf("%s %s EXISTS( SELECT 1 FROM request_tag WHERE request_id=request.request_id AND tag_id=%d) %s ",
                                         ($i>0?$tag_and[$i]:''), $tag_lb[$i], $tag_id, $tag_rb[$i]);
          error_log( "$sysabbr: DBG: Tag List subquery: $taglist_subquery" );
        }
      }
      if ( $taglist_subquery != "" ) {
        $taglist_subquery = str_replace("'","",str_replace("\\", "", $taglist_subquery ));
        $search_query .= "AND (" . $taglist_subquery . ") ";
        error_log( "$sysabbr: DBG: Tag List subquery: $taglist_subquery" );
      }
    }

    $search_query .= " AND status.source_table='request' AND status.source_field='status_code' AND status.lookup_code=request.last_status ";
    if ( $where_clause != "" ) {
      $search_query .= " AND $where_clause ";
    }

    if ( isset($incstat) && is_array( $incstat ) ) {
      reset($incstat);
      $search_query .= " AND (request.last_status ~* '[";
      while( list( $k, $v) = each( $incstat ) ) {
        $search_query .= $k ;
      }
      $search_query .= "]') ";
      if ( eregi("save", "$submit") && "$savelist" != "" ) {
        $saved_sort = "";
        $saved_seq  = "";
        if ( isset($save_query_order) && $save_query_order ) {
          $saved_sort = $rlsort;
          $saved_seq = $rlseq;
        }
        $savelist = tidy($savelist);
        $qquery   = tidy($search_query);
        $rlsort   = tidy($rlsort);
        $rlseq    = tidy($rlseq);
        $search_query = "DELETE FROM saved_queries WHERE user_no = '$session->user_no' AND LOWER(query_name) = LOWER('$savelist');
INSERT INTO saved_queries (user_no, query_name, query_sql, maxresults, rlsort, rlseq)
   VALUES( '$session->user_no', '$savelist', '$qquery', $maxresults, '$rlsort', '$rlseq');
$search_query";
      }
    }
  }

  $search_query .= " ORDER BY $rlsort $rlseq ";
  $search_query .= " LIMIT $maxresults ";
?>