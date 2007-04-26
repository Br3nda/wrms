<?php
  /**
  *
  * Now we build the statement that will find those requests...
  */

  $search_query = "";
  if ( !isset($_POST['submit']) && isset($_GET['saved_query'])) {
    $sql =  "SELECT * FROM saved_queries ";
    $sql .= "WHERE (user_no = '$session->user_no' OR public ) ";
    $sql .= "AND query_name = ?;";
    $qry = new PgQuery( $sql, $saved_query );
    $qry->Exec("WRSearch::Build");
    $thisquery = $qry->Fetch();
    $search_query = $thisquery->query_sql ;

    $saved_columns = unserialize($thisquery->query_params);
    $saved_columns = $saved_columns["columns"];
    if ( isset($saved_columns) && is_array($saved_columns) ) $columns = $saved_columns;

    // If the maxresults they saved was non-default, use that, otherwise we
    // increase the default anyway, because saved queries are more carefully
    // crafted, and less likely to list the whole database
    $mr = 1000;
    if ( (!isset($maxresults) || intval($maxresults) == 0 || $maxresults == 100)
           && intval($thisquery->maxresults) != 100 && intval($thisquery->maxresults) != 100 )
      $mr = $thisquery->maxresults;
    $maxresults = $mr;
    if ( $thisquery->rlsort && ! isset($_GET['rlsort']) ) {
      $rlsort = $thisquery->rlsort;
      $rlseq = $thisquery->rlseq;
    }

    if ( !isset($saved_columns['rlsort']) ) {
      // Enforce some sanity
      $rlsort = (isset($_GET['rlsort']) ? $_GET['rlsort'] : 'last_activity');
      $rlseq = 'DESC';
    }

  }
  else {
    $flipped_columns = array_flip($columns);

    $search_query .= "SELECT request.request_id, brief, usr.fullname, usr.email, request_on, status.lookup_desc AS status_desc, last_activity, detailed ";
    $search_query .= ", request_urgency.lookup_desc AS request_urgency_desc, request.urgency AS urgency, request.system_id";
    $search_query .= ", request_importance.lookup_desc AS request_importance_desc, request.importance AS importance";
    $search_query .= ", request_type.lookup_desc AS request_type_desc, lower(usr.fullname) AS lfull, lower(brief) AS lbrief ";
    $search_query .= ", to_char( request.last_activity, 'FMdd Mon yyyy') AS last_change ";
    $search_query .= ", to_char( request.request_on, 'FMdd Mon yyyy') AS date_requested";
    //provides extra fields that are needed to create a Brief (editable) report
    $search_query .= ", request.active, last_status ";
    $search_query .= ", creator.email AS by_email, creator.fullname AS by_fullname, lower(creator.fullname) AS lby_fullname ";
    if ( isset($flipped_columns['request_tags']) || $rlsort == "request_tags" ) {
      $search_query .= ", request_tags(request.request_id) ";
    }
    if ( isset($flipped_columns['system_code']) || $rlsort == "system_code" ) {
      $search_query .= ", work_system.system_code ";
    }
    if ( isset($flipped_columns['system_desc']) || $rlsort == "system_desc" ) {
      $search_query .= ", work_system.system_desc ";
    }
    if ( isset($flipped_columns['request_hours']) || $rlsort == "request_hours" ) {
      $search_query .= ", total_work(request.request_id) AS request_hours ";
    }
    $search_query .= "FROM ";
    if ( intval("$interested_in") > 0 ) $search_query .= "request_interested, ";
    if ( intval("$allocated_to") > 0 ) $search_query .= "request_allocated, ";
    $search_query .= "request ";
    $search_query .= "JOIN work_system USING (system_id) ";
    if ( ! is_member_of('Admin', 'Support') ) {
      $search_query .= "JOIN system_usr ON (work_system.system_id = system_usr.system_id AND system_usr.user_no = $session->user_no) ";
    }
    $search_query .= ", usr";
    $search_query .= ", lookup_code AS status ";
    $search_query .= ", lookup_code AS request_type";
    $search_query .= ", lookup_code AS request_urgency";
    $search_query .= ", lookup_code AS request_importance";
    $search_query .= ", usr AS creator";

    $search_query .= " WHERE request.requester_id=usr.user_no AND request.entered_by=creator.user_no ";
    $search_query .= " AND request_type.source_table='request' AND request_type.source_field='request_type' AND request.request_type = request_type.lookup_code";
    $search_query .= " AND request_urgency.source_table='request' AND request_urgency.source_field='urgency' AND request.urgency = request_urgency.lookup_code";
    $search_query .= " AND request_importance.source_table='request' AND request_importance.source_field='importance' AND request.importance = request_importance.lookup_code";
    if ( "$inactive" == "" || $inactive == 0 || $inactive == 'off')        $search_query .= " AND request.active ";
    if ( ! is_member_of('Admin', 'Support', 'Contractor' ) ) {
      $search_query .= " AND usr.org_code = '$session->org_code' ";
    }
    else if ( isset($org_code) && intval($org_code) > 0 )
      $search_query .= " AND usr.org_code=".intval($org_code);

    if ( intval("$user_no") > 0 )
      $search_query .= " AND requester_id = " . intval($user_no);
    else if ( intval("$requested_by") > 0 )
      $search_query .= " AND requester_id = " . intval($requested_by);
    if ( intval("$interested_in") > 0 )
      $search_query .= " AND request_interested.request_id=request.request_id AND request_interested.user_no = " . intval($interested_in);
    if ( intval("$allocated_to") > 0 )
      $search_query .= " AND request_allocated.request_id=request.request_id AND request_allocated.allocated_to_id = " . intval($allocated_to);
    else if ( $allocated_to == "nobody" )
      $search_query .= " AND NOT EXISTS( SELECT request_id FROM request_allocated WHERE request_allocated.request_id=request.request_id )";

    if ( "$search_for" != "" ) {
      $search_query .= " AND (brief ~* ".qpg($search_for)." ";
      $search_query .= " OR detailed ~* ".qpg($search_for)." ";
      $search_query .= " OR EXISTS(SELECT 1 FROM request_note WHERE request_id = request.request_id AND note_detail ~* ".qpg($search_for).")) ";
    }
    if ( $system_id > 0 )     $search_query .= " AND request.system_id=".intval($system_id);
    if ( "$type_code" != "" )     $search_query .= " AND request_type=" . intval($type_code);

    if ( "$from_date" != "" )     $search_query .= " AND request.last_activity >= '$from_date' ";
    if ( "$to_date" != "" )     $search_query .= " AND request.last_activity<='$to_date' ";

    $taglist_count = intval($taglist_count);
    if ( isset($tag_list) && is_array($tag_list) ) {
      $taglist_subquery = "";
      $lb_count = 0;
      $rb_count = 0;
      for ( $i=0; $i < $taglist_count ; $i++ ) {
        $tag_id = intval($tag_list[$i]);
        if ( $tag_id > 0 ) {
          if ( $i == 0 )
            $boolean = "";
          else {
            switch ( $tag_and[$i] ) {
              case 'AND':
              case 'OR':
              case 'AND NOT':
              case 'OR NOT':
                $boolean = $tag_and[$i];
                break;

              case 'ANDNOT':
                $boolean = 'AND NOT';
                break;
              case 'ORNOT':
                $boolean = 'OR NOT';
                break;
              default:
                $boolean = "AND";
            }
          }
          $lb = ereg_replace('[^(]','',$tag_lb[$i] );
          $rb = ereg_replace('[^)]','',$tag_rb[$i] );
          $lb_count += strlen($lb);
          $rb_count += strlen($rb);
          $taglist_subquery .= sprintf("%s %s EXISTS( SELECT 1 FROM request_tag WHERE request_id=request.request_id AND tag_id=%d) %s ",
                                         $boolean, $lb, $tag_id, $rb);
        }
      }
      if ( $taglist_subquery != "" ) {
        $taglist_subquery = str_replace("'","",str_replace("\\", "", $taglist_subquery ));
        $search_query .= "AND (" . $taglist_subquery . ") ";
        if ( $lb_count != $rb_count ) {
          $client_messages[] = "You have $lb_count left brackets and $rb_count right brackets - they should match!";
        }
      }
    }

    $search_query .= " AND status.source_table='request' AND status.source_field='status_code' AND status.lookup_code=request.last_status ";
    if ( $where_clause != "" && ($session->AllowedTo('Admin') /* || $session->AllowedTo('Support') */ )) {
      $search_query .= " AND $where_clause ";  // Not checked, but only an Admin can do this...
    }

    $search_query .= " AND (request.last_status ~* '[";
    if ( isset($incstat) && is_array( $incstat ) ) {
      reset($incstat);
      while( list( $k, $v) = each( $incstat ) ) {
        if ( $v != 0 && $v != 'off' ) $search_query .= $k ;
      }
    }
    else {
      $search_query .= $default_search_statuses;
    }
    $search_query .= "]') ";

    if ( eregi("save", "$submit") && "$savelist" != "" ) {
      $saved_sort = "";
      $saved_seq  = "";
      if ( isset($save_query_order) && intval($save_query_order) > "0" ) {
        $saved_sort = $rlsort;
        $saved_seq = $rlseq;
      }
      $qparams   = qpg(serialize($_POST));
      $savelist = qpg($savelist);
      $qquery   = qpg($search_query);
      $save_rlsort   = qpg($saved_rlsort);
      $save_rlseq    = qpg($saved_rlseq);
      $save_public   = qpg(intval($save_public)  > 0);
      $save_in_menu  = qpg(intval($save_hotlist) > 0);
      $search_query = "DELETE FROM saved_queries WHERE user_no = $session->user_no AND LOWER(query_name) = LOWER($savelist);
INSERT INTO saved_queries (user_no, query_name, query_sql, maxresults, rlsort, rlseq, public, updated, in_menu, query_params)
  VALUES( $session->user_no, $savelist, $qquery, ".intval($maxresults).",
    $save_rlsort, $save_rlseq, $save_public, current_timestamp, $save_in_menu, $qparams);
$search_query";
    }

  }

  if ( $rlsort != 'request_tags' || isset($flipped_columns['request_tags']) ) {
    // We can only sort by request_tags if it is present in the target list!
    $search_query .= " ORDER BY $rlsort $rlseq ";
  }
  if ( !isset($maxresults) || intval($maxresults) == 0 ) $maxresults = 200;
  $search_query .= " LIMIT $maxresults ";
  // echo "<p>$search_query</p>";
?>