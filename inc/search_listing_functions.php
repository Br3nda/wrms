<?php

//-----------------------------------------------------------
// function Process_Brief_editable_Requests()
// This function is used to process the returned changes (if any)
// from the Brief (editable) report.
//------------------------------------------------------------
function Process_Brief_editable_Requests()
{
    global $session, $debuggroups, $client_messages, $active_flag, $request_status ;

    $sql = "BEGIN; ";
    foreach( $request_status AS $request_id => $new_status ) {
      if ( isset($active_flag) ) {
        $request_active = ($active_flag[$request_id] == 'on' ? 'TRUE' : 'FALSE');
        $session->Log("DBG: request_id=%d, new_status=%s, active=%s, submitted_active=%s",
                         $request_id, $new_status, $request_active, $active_flag[$request_id]);
        $sql .= "SELECT set_request_status(".qpg($request_id).",".qpg($session->user_no).",".qpg($new_status).", $request_active); ";
      }
      else {
        // Or if we are just changing the status, and the active/inactive choice is not available to this user
        $sql .= "SELECT set_request_status(".qpg($request_id).",".qpg($session->user_no).",".qpg($new_status)."); ";
      }
    }

    $q = new PgQuery($sql." COMMIT;");
    $q->Exec('WRSrch::ProcBriefEditable');
}

//------------------------------------------------------------
//
//------------------------------------------------------------
function header_row() {
  global $format, $columns, $available_columns;

   // We want to use nice column names, but sometimes we have to
   // sort by something else (e.g. case insensitive)
   $nice_names = array(
          "request_for" => "lfull",
          "request_by"  => "lby_fullname",
          "brief"       => "lbrief",
          "status"      => "status_desc",
//          "system_code" => "work_system.system_code",
          "type"        => "request_type_desc",
          "tags"        => "request_tags",
          "last_change" => "request.last_activity"
   );

  echo "<tr>\n";
  reset($columns);
  while( list($k,$v) = each( $columns ) ) {
    if ( $v == "0" || $v == "off" ) continue;
    $real_name = $v;
    if ( isset($nice_names[$v]) ) $real_name = $nice_names[$v];
    column_header($available_columns[$real_name], $real_name);
  }
  echo "</tr>";
}

//------------------------------------------------------------
//------------------------------------------------------------
function show_column_value( $column_name, $row ) {
  global $format, $status_query;
  switch( $column_name ) {
    case "request_id":
      echo "<td class=\"sml\" align=\"center\"><a href=\"/wr.php?request_id=$row->request_id\">$row->request_id</a></td>\n";
      break;
    case "lfull":
    case "request_for":
      echo "<td class=\"sml\" style=\"white-space: nowrap;\"><a href=\"mailto:$row->email\">$row->fullname</a></td>\n";
      break;
    case "lby_fullname":
    case "request_by":
      echo "<td class=\"sml\" style=\"white-space: nowrap;\"><a href=\"mailto:$row->by_email\">$row->by_fullname</a></td>\n";
      break;
    case "request_tags":
      echo "<td class=\"sml\">$row->request_tags</td>\n";
      break;
    case "request_on":
      echo "<td class=\"sml\" align=\"center\" style=\"white-space: nowrap;\">$row->date_requested</td>\n";
      break;
    case "lbrief":
    case "description":
      echo "<td class=\"sml\"><a href=\"/wr.php?request_id=$row->request_id\">$row->brief";
      if ( "$row->brief" == "" ) echo substr( $row->detailed, 0, 50) . "...";
      echo "</a></td>\n";
      break;
    case "status":
    case "status_desc":
      if ( "$format" == "edit" && $row->editable ) {
        //provide a drop down to allow editing of the status code for that request
        $status_list = $status_query->BuildOptionList($row->last_status, "show_column_value");
        echo "<td class=\"sml\"><select class=sml name=\"request_status[$row->request_id]\">$status_list</select></td>\n";
      }
      else {
        //otherwise output plain text of the current request status
        echo "<td class=\"sml\">&nbsp;".str_replace(' ', '&nbsp;',$row->status_desc)."&nbsp;</td>\n";
      }
      break;
    case "type":
    case "request_type_desc":
      echo "<td class=\"sml\">&nbsp;" . str_replace( " ", "&nbsp;", $row->request_type_desc) . "&nbsp;</td>\n";
      break;
    case "last_change":
    case "request.last_activity":
      echo "<td class=\"sml\" align=\"center\" style=\"white-space: nowrap;\">" . str_replace( " ", "&nbsp;", $row->last_change) . "</td>\n";
      break;
    case "active":
      if ( "$format" == "edit" && $row->editable ) {
        //adds in the Active field for the Brief (editable) reports
        $checked = ( $row->active == 't' ? " CHECKED" : "" );
        echo <<<EOHTML
<td class="sml" align="center"><input type="checkbox" name="active_flag[$row->request_id]"$checked></td>
EOHTML;
      }
      else {
        echo "<td class=\"sml\" align=\"center\">" . ( $row->active == 't' ? "Active" : "Inactive" ) . "</td>\n";
      }
      break;
    case "urgency":
      echo "<td class=\"sml\" align=\"left\">" . $row->request_urgency_desc . "</td>\n";
      break;
    case "importance":
      echo "<td class=\"sml\" align=\"left\">" . $row->request_importance_desc . "</td>\n";
      break;
    case "system_code":
    case "system_desc":
      printf( '<td class="sml" align="left"><a href="%s&system_id=%d">%s</td>%s', sprintf($GLOBALS['get_uri'], $GLOBALS['rlsort'], $GLOBALS['rlseq']), $row->system_id, $row->{$column_name}, "\n");
      break;
    case "request_hours":
      echo "<td class=\"sml\" align=\"right\">" . $row->{$column_name} . "</td>\n";
      break;
    default:
      echo "<td class=\"sml\" align=\"left\">" . $row->{$column_name} . "</td>\n";
      break;
  }
}

//------------------------------------------------------------
//------------------------------------------------------------
function data_row( $row, $rc ) {
  global $columns;

  printf( "<tr class=row%1d>\n", $rc % 2);
  reset($columns);
  while( list($k,$v) = each( $columns ) ) {
    if ( $v == "0" || $v == "off" ) continue;
    show_column_value($v,$row);
  }
  echo "</tr>\n";
}


//------------------------------------------------------------
//Builds up and outputs the HTML for a linked column header on the request list
//------------------------------------------------------------
function column_header( $ftext, $fname ) {
  global $rlsort, $rlseq, $header_cell, $theme;
  $fseq = "";
  $seq_image = "";
  if ( "$rlsort" == "$fname" ) {
    $fseq = ( "$rlseq" == "DESC" ? "ASC" : "DESC");
    $seq_image .= "&nbsp;".$theme->Image("sort-$rlseq.png");
  }
  printf( $header_cell, $fname, $fseq, $ftext, $seq_image );
}


//------------------------------------------------------------
///////////////////////////////////////////////////////////
// And this is not a function now
///////////////////////////////////////////////////////////
//------------------------------------------------------------

  if ( "$format" == "edit" && isset($submitBriefEditable) ) {
    // If changes have been returned from Brief (editable) then function is called update the database with the changes
    $session->Log("DBG: format=%s, submitBriefEditable=%s", $format, $submitBriefEditable );
    $ChangedRequests_count = 0;
    Process_Brief_editable_Requests();
  }

  $title = "$system_name Request List";

  if ( !isset($rlsort) ) $rlsort = $settings->get('rlsort');
  if ( !isset($rlseq) ) $rlseq = $settings->get('rlseq');
  if ( "$rlsort" == "" ) $rlsort = "request_id";
  $rlseq = strtoupper($rlseq);
  if ( "$rlseq" == "" ) {
    $rlseq = ( "$rlsort" == "request_id" || "$rlsort" == "request_on" || "$rlsort" == "last_change" ? "DESC" : "ASC");
  }
  if ( "$rlseq" != "ASC" ) $rlseq = "DESC";
  $settings->set('rlsort', $rlsort);
  $settings->set('rlseq', $rlseq);

  if ( isset($org_code) ) $org_code = intval($org_code);
  if ( isset($system_id) ) $system_id = intval($system_id);

  // Build up the column header cell, with %s gaps for the sort, sequence and sequence image
  $get_uri = "$PHP_SELF?rlsort=%s&rlseq=%s";
  if ( isset($qs) ) $get_uri .= "&qs=$qs";
  if ( $org_code > 0 ) $get_uri .= "&org_code=$org_code";
  if ( $system_id > 0 ) $get_uri .= "&system_id=$system_id";
  if ( isset($search_for) ) $get_uri .= "&search_for=$search_for";
  if ( isset($inactive) ) $get_uri .= "&inactive=$inactive";
  if ( isset($requested_by) ) $get_uri .= "&requested_by=$requested_by";
  if ( isset($interested_in) ) $get_uri .= "&interested_in=$interested_in";
  if ( isset($allocated_to) ) $get_uri .= "&allocated_to=$allocated_to";
  if ( isset($from_date) ) $get_uri .= "&from_date=$from_date";
  if ( isset($to_date) ) $get_uri .= "&to_date=$to_date";
  if ( isset($type_code) ) $get_uri .= "&type_code=$type_code";
  if ( isset($columns) ) $get_uri .= "&columns=" . implode(",",$columns);
  if ( isset($incstat) && is_array( $incstat ) ) {
    reset($incstat);
    while( list($k,$v) = each( $incstat ) ) {
      $get_uri .= "&incstat[$k]=$v";
    }
  }
  if ( "$saved_query" != "" ) $get_uri .= "&saved_query=$saved_query";
  if ( "$style" != "" ) $get_uri .= "&style=$style";
  if ( "$format" != "" ) $get_uri .= "&format=$format";
  if ( isset($choose_columns) && $choose_columns ) $get_uri .= "&choose_columns=1";
  $header_cell = sprintf('<th class="cols"><a class="cols" href="%s">%%s%%s</a></th>', $get_uri);      // %s for the Cell heading and image which will be added later

  $status_query = new PgQuery( "SELECT lookup_code, lookup_desc FROM lookup_code WHERE source_table = 'request' AND source_field = 'status_code' ORDER BY source_table, source_field, lookup_seq, lookup_code;");
  $status_query->Exec("search_listing_functions");
?>
