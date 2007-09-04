<?php
  require_once("always.php");
  require_once("authorisation-page.php");

  $session->LoginRequired();

  require_once("code-list.php");
  require_once("user-list.php" );
  require_once("maintenance-page.php");
  require_once("organisation-selectors-sql.php");

  // Force some variables to have values.
  if ( !isset($format) ) $format = "";
  if ( !isset($style) ) $style = "";
  if ( !isset($org_code) ) $org_code = 0;
  if ( !isset($system_id) ) $system_id = 0;
  if ( !isset($search_for) ) $search_for = "";
  if ( !isset($interested_in) ) $interested_in = "";
  if ( !isset($allocated_to) ) $allocated_to = "";
  if ( !isset($type_code) ) $type_code = "";
  if ( !isset($inactive) ) $inactive = "";
  if ( !isset($user_no) ) $user_no = "";
  if ( !isset($requested_by) ) $requested_by = "";
  if ( !isset($from_date) ) $from_date = "";
  if ( !isset($to_date) ) $to_date = "";

  // If they didn't provide a $columns, we use a default.
  if ( !isset($columns) || $columns == "" || $columns == array() ) {
    $columns = array("request_id","lfull","request_on","lbrief","status_desc","request_tags","request_type_desc","request.last_activity");
  }
  elseif ( ! is_array($columns) )
    $columns = explode( ',', $columns );

  // Internal column names (some have 'nice' alternatives defined in header_row() )
  // The order of these defines the ordering when columns are chosen
  $available_columns = array(
          "request_id" => "WR&nbsp;#",
          "lby_fullname" => "Created By",
          "lfull" => "Request For",
          "request_on" => "Request On",
          "lbrief" => "Description",
          "request_type_desc" => "Type",
          "request_tags" => "Tags",
          "status_desc" => "Status",
          "request.last_activity" => "Last Chng",
          "request.active" => "Active",
   );


/**
* Builds up and outputs the HTML for a linked column header on the request list
*/
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

/**
* Output a single row of the column headers
*/
function header_row() {
  global $format, $columns, $available_columns;

   // We want to use nice column names, but sometimes we have to
   // sort by something else (e.g. case insensitive)
   $nice_names = array(
          "request_for" => "lfull",
          "request_by"  => "lby_fullname",
          "brief"       => "lbrief",
          "status"      => "status_desc",
          "type"        => "request_type_desc",
          "tags"        => "request_tags",
          "last_change" => "request.last_activity"
   );

  echo "<tr>\n";
  reset($columns);
  while( list($k,$v) = each( $columns ) ) {
    $real_name = $v;
    if ( isset($nice_names[$v]) ) $real_name = $nice_names[$v];
    column_header($available_columns[$real_name], $real_name);
  }
  echo "</tr>";
}

/**
* Display a single data vale on the report
*/
function show_column_value( $column_name, $row ) {
  global $format;
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
      echo "<td class=\"sml\">&nbsp;".str_replace(' ', '&nbsp;',$row->status_desc)."&nbsp;</td>\n";
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
      echo "<td class=\"sml\" align=\"center\">" . ( $row->active == 't' ? "Active" : "Inactive" ) . "</td>\n";
      break;
  }
}

/**
*  Output a single row of report data
*/
function data_row( $row, $rc ) {
  global $columns;

  printf( "<tr class=\"row%1d\">\n", $rc % 2);
  reset($columns);
  while( list($k,$v) = each( $columns ) ) {
    show_column_value($v,$row);
  }
  echo "</tr>\n";
}


/**
* Now back into the main line...
*/

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

  if ( isset($system_id) ) $system_id = intval($system_id);
  if ( isset($org_code) ) $org_code = intval($org_code);

  // Build up the column header cell, with %s gaps for the sort, sequence and sequence image
  $header_cell = "<th class=cols><a class=cols href=\"$PHP_SELF?rlsort=%s&rlseq=%s";
  if ( $org_code > 0 ) $header_cell .= "&org_code=$org_code";
  if ( $system_id > 0 ) $header_cell .= "&system_id=$system_id";
  if ( isset($search_for) ) $header_cell .= "&search_for=$search_for";
  if ( isset($inactive) ) $header_cell .= "&inactive=$inactive";
  if ( isset($requested_by) ) $header_cell .= "&requested_by=$requested_by";
  if ( isset($interested_in) ) $header_cell .= "&interested_in=$interested_in";
  if ( isset($allocated_to) ) $header_cell .= "&allocated_to=$allocated_to";
  if ( isset($from_date) ) $header_cell .= "&from_date=$from_date";
  if ( isset($to_date) ) $header_cell .= "&to_date=$to_date";
  if ( isset($type_code) ) $header_cell .= "&type_code=$type_code";
  if ( isset($incstat) && is_array( $incstat ) ) {
    reset($incstat);
    while( list($k,$v) = each( $incstat ) ) {
      $header_cell .= "&incstat[$k]=$v";
    }
  }
  if ( "$style" != "" ) $header_cell .= "&style=$style";
  if ( "$format" != "" ) $header_cell .= "&format=$format";
  if ( isset($choose_columns) && $choose_columns ) $header_cell .= "&choose_columns=1";
  $header_cell .= "\">%s";      // %s for the Cell heading
  $header_cell .= "%s</a></th>";    // %s For the image

  require_once("top-menu-bar.php");
  require_once("page-header.php");

  if ( !isset( $style ) || ($style != "plain" && $style != "stripped") ) {
    $form_url_parameters = array();
    if ( isset($org_code) && intval($org_code) > 0 )  array_push( $form_url_parameters, "org_code=$org_code");
    if ( isset($choose_columns) && $choose_columns )  array_push( $form_url_parameters, "choose_columns=1");
    $form_url = "$PHP_SELF";
    for( $i=0; $i < count($form_url_parameters) && $i < 20; $i++ ) {
      $form_url .= ( $i == 0 ? '?' : '&' ) . $form_url_parameters[$i] ;
    }
    echo "<form name=\"search\" action=\"$form_url\" Method=\"POST\">";

    $systems = new PgQuery(SqlSelectSystems($org_code));
    $system_list = $systems->BuildOptionList($system_id,"requestlist");


    echo "<table border=0 cellspacing=2 cellpadding=0 align=center class=row0 width=100% style=\"border: 1px dashed #aaaaaa;\">\n<tr>\n";
    echo "<td width=100%><table border=0 cellspacing=0 cellpadding=0 width=100%><tr valign=middle>\n";

    echo "<td class=smb>&nbsp;System:</td><td class=\"sml\"><select class=\"sml\" name=system_id><option value=\".\">--- All Systems ---</option>$system_list</select></td>\n";

    if ( is_member_of('Admin', 'Support','Contractor') ) {
      $organisations = new PgQuery(SqlSelectOrganisations($org_code));
      $orglist = "<option value=\"\">--- All Organisations ---</option>\n" . $organisations->BuildOptionList( "$org_code", "requestlist" );
      echo "<td class=\"smb\">&nbsp;Organisation:</td><td class=\"sml\"><select class=\"sml\" name=\"org_code\">\n$orglist</select></td>\n";
    }
    echo "<td valign=middle class=smb align=center><input type=submit value=\"RUN\" alt=\"Run\" title=\"Run a query with these settings\" name=submit class=\"submit\">";
    echo "</tr></table></td></tr>\n";
    echo "</table></form>\n";

  } // if  not plain  or stripped style


  /**
  * Now we build the statement that will find those requests...
  */
  $query = "";

  $maxresults = ( isset($maxresults) && intval($maxresults) > 0 ? intval($maxresults) : 100 );
  $flipped_columns = array_flip($columns);

  $query .= "SELECT request.request_id, brief, usr.fullname, usr.email, request_on, status.lookup_desc AS status_desc, last_activity, detailed ";
  $query .= ", request_type.lookup_desc AS request_type_desc, lower(usr.fullname) AS lfull, lower(brief) AS lbrief ";
  $query .= ", to_char( request.last_activity, 'FMdd Mon yyyy') AS last_change ";
  $query .= ", to_char( request.request_on, 'FMdd Mon yyyy') AS date_requested";
  $query .= ", request.active, last_status ";
  $query .= ", creator.email AS by_email, creator.fullname AS by_fullname, lower(creator.fullname) AS lby_fullname ";
  if ( isset($flipped_columns['request_tags']) ) {
    $query .= ", request_tags(request.request_id) ";
  }
  $query .= "FROM ";
  if ( intval("$interested_in") > 0 ) $query .= "request_interested, ";
  if ( intval("$allocated_to") > 0 ) $query .= "request_allocated, ";
  $query .= "request ";
  if ( ! is_member_of('Admin', 'Support') ) {
    $query .= "JOIN work_system USING (system_id) ";
    $query .= "JOIN system_usr ON (work_system.system_id = system_usr.system_id AND system_usr.user_no = $session->user_no) ";
  }
  $query .= ", usr";
  $query .= ", lookup_code AS status ";
  $query .= ", lookup_code AS request_type";
  $query .= ", usr AS creator";

  $query .= " WHERE request.requester_id=usr.user_no AND request.entered_by=creator.user_no ";
  $query .= " AND request_type.source_table='request' AND request_type.source_field='request_type' AND request.request_type = request_type.lookup_code";
  if ( "$inactive" == "" )        $query .= " AND request.active ";
  if ( ! is_member_of('Admin', 'Support', 'Contractor' ) ) {
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
  if ( $system_id > 0 )     $query .= " AND request.system_id=$system_id ";
  if ( "$type_code" != "" )     $query .= " AND request_type=" . intval($type_code);

  if ( "$from_date" != "" )     $query .= " AND request.last_activity >= '$from_date' ";
  if ( "$to_date" != "" )     $query .= " AND request.last_activity<='$to_date' ";

  $query .= " AND status.source_table='request' AND status.source_field='status_code' AND status.lookup_code=request.last_status ";

  if ( isset($incstat) && is_array( $incstat ) ) {
    reset($incstat);
    $query .= " AND (request.last_status ~* '[";
    while( list( $k, $v) = each( $incstat ) ) {
      $query .= $k ;
    }
    $query .= "]') ";
  }

  $query .= " ORDER BY $rlsort $rlseq ";
  $query .= " LIMIT $maxresults ";

  $result = awm_pgexec( $dbconn, $query, "requestlist", false, 7 );

  if ( "$style" != "stripped" ) {
    if ( $result && pg_NumRows($result) > 0 ) {
      echo "\n<small>";
      echo pg_NumRows($result) . " requests found";
      if ( pg_NumRows($result) == $maxresults ) echo " (limit reached)";
      echo "</small>";
    }
    else {
      echo "\n<p><small>No requests found</small></p>";
    }
  }

  if ( "$style" != "stripped" ) {
    $this_page = "wrsearch.php?style=%s&format=%s";
    if ( "$search_for" != "" ) $this_page .= "&search_for=" . urlencode($search_for);
    if ( $org_code > 0 ) $this_page .= "&org_code=$org_code";
    if ( $system_id > 0 ) $this_page .= "&system_id=$system_id";
    if ( isset($inactive) ) $this_page .= "&inactive=$inactive";
    if ( isset($requested_by) ) $this_page .= "&requested_by=$requested_by";
    if ( isset($interested_in) ) $this_page .= "&interested_in=$interested_in";
    if ( isset($allocated_to) ) $this_page .= "&allocated_to=$allocated_to";
    if ( isset($from_date) ) $this_page .= "&from_date=$from_date";
    if ( isset($to_date) ) $this_page .= "&to_date=$to_date";
    if ( isset($type_code) ) $this_page .= "&type_code=$type_code";
    if ( isset($incstat) && is_array( $incstat ) ) {
      reset($incstat);
      while( list($k,$v) = each( $incstat ) ) {
        $this_page .= "&incstat[$k]=$v";
      }
    }
  }

  if ( $style == "stripped" ) {
    echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"2\" width=\"100%\">\n<tr>\n";
    echo "<th class=\"cols\" style=\"text-align: right\">" . pg_NumRows($result) . " requests at " . date("H:i j M y") . "</th>";
    echo "</tr></table>\n";
  }
  echo "<table border=\"0\" width=\"100%\">\n";

  $show_notes = ($format == "ultimate" || $format == "detailed" );
  $show_details = ($format == "ultimate" || $format == "detailed" || "$format" == "activity" || "$format" == "quotes" );
  $show_quotes = ( $format == "ultimate" || "$format" == "activity" || "$format" == "quotes" );
  $show_work = ( ($format == "ultimate" || "$format" == "activity" ) &&  is_member_of('Admin', 'Support' ) );
  if ( ! $show_details ) header_row();


  if ( $result ) {
    $grand_total = 0.0;
    $grand_qty_total = 0.0;

    // Build table of requests found
    for ( $i=0; $i < pg_NumRows($result); $i++ ) {
      $thisrequest = pg_Fetch_Object( $result, $i );

      if ( $show_details ) header_row();
      data_row($thisrequest, $i);

      if ( $show_details ) {
        printf( "<tr class=\"row%1d\">\n", $i % 2);
        echo "<td colspan=\"7\">" . html_format($thisrequest->detailed) . "</td>\n";
        echo "</tr>\n";
      }
      if ( $show_quotes ) {
        $subquery = "SELECT *, to_char( quoted_on, 'DD/MM/YYYY') AS nice_date ";
        $subquery .= "FROM request_quote, usr ";
        $subquery .= "WHERE request_id = $thisrequest->request_id ";
        $subquery .= "AND usr.user_no = request_quote.quote_by_id ";
        $subquery .= "ORDER BY request_id, quoted_on ";
        $total = 0.0;
        $qty_total = 0.0;
        $subres = awm_pgexec( $dbconn, $subquery, "requestlist" );
        for ( $j=0; $subres && $j < pg_NumRows($subres); $j++ ) {
          $thisquote = pg_Fetch_Object( $subres, $j );
          printf( "<tr class=\"row%1d\" valign=\"top\">\n", $i % 2);
          echo "<td>$thisquote->nice_date</td>\n";
          echo "<td>$thisquote->fullname</td>\n";
          echo "<td colspan=\"4\"><b>$thisquote->quote_brief</b><br><hr>\n";
          echo html_format($thisquote->quote_details);
          echo "</td>\n";
          printf("<td align=\"right\">%9.2f &nbsp; %s</td>\n", $thisquote->quote_amount, $thisquote->quote_units);
          echo "</tr>\n";
        }
      }
      if ( $show_notes ) {
        $subquery = "SELECT *, to_char( note_on, 'DD/MM/YYYY') AS nice_date ";
        $subquery .= "FROM request_note, usr ";
        $subquery .= "WHERE request_id = $thisrequest->request_id ";
        $subquery .= "AND usr.user_no = request_note.note_by_id ";
        $subquery .= "ORDER BY request_id, note_on ";
        $subres = awm_pgexec( $dbconn, $subquery, "requestlist" );
        for ( $j=0; $subres && $j < pg_NumRows($subres); $j++ ) {
          $thisnote = pg_Fetch_Object( $subres, $j );
          printf( "<tr class=\"row%1d\" valign=\"top\">\n", $i % 2);
          echo "<td>$thisnote->nice_date</td>\n";
          echo "<td>$thisnote->fullname</td>\n";
          echo "<td colspan=\"5\">" . html_format($thisnote->note_detail) . "</td>\n";
          echo "</tr>\n";
        }
      }
      if ( $show_work ) {
        $subquery = "SELECT *, to_char( work_on, 'DD/MM/YYYY') AS nice_date ";
        $subquery .= "FROM request_timesheet, usr ";
        $subquery .= "WHERE request_id = $thisrequest->request_id ";
        $subquery .= "AND usr.user_no = request_timesheet.work_by_id ";
        $subquery .= "ORDER BY request_id, work_on ";
        $total = 0.0;
        $qty_total = 0.0;
        $subres = awm_pgexec( $dbconn, $subquery, "requestlist" );
        for ( $j=0; $subres && $j < pg_NumRows($subres); $j++ ) {
          $thiswork = pg_Fetch_Object( $subres, $j );
          printf( "<tr class=row%1d valign=top>\n", $i % 2);
          echo "<td>$thiswork->nice_date</td>\n";
          echo "<td>$thiswork->fullname</td>\n";
          echo "<td colspan=\"2\">$thiswork->work_description</td>\n";
          printf("<td align=\"right\">%9.2f &nbsp; </td>\n", $thiswork->work_quantity);
          printf("<td align=\"right\">%9.2f &nbsp; </td>\n", $thiswork->work_rate);
          $value = $thiswork->work_quantity * $thiswork->work_rate;
          $total += $value;
          $qty_total += $thiswork->work_quantity;
          printf("<td align=\"right\">%9.2f &nbsp; </td>\n", $value);
          echo "</tr>\n";
        }
        if ( $j > 0 )
          printf( "<tr class=\"row%1d\">\n<td colspan=\"4\">&nbsp; &nbsp; &nbsp; Request #$thisrequest->request_id total</td>\n<td align=\"right\">%9.2f &nbsp; </td><td>&nbsp;</td><td align=right>%9.2f &nbsp; </td>\n</tr>\n", $i % 2, $qty_total, $total);
        $grand_total += $total;
        $grand_qty_total += $qty_total;
      }

      if ( $show_details )
        echo "<tr class=\"row3\">\n<td colspan=\"7\">&nbsp;</td></tr>\n";

    }
  }
  if ( $show_work )
    printf( "<tr class=\"row%1d\">\n<th align=\"left\" colspan=\"4\">Grand Total</th>\n<th align=\"right\">%9.2f &nbsp; </th><th>&nbsp;</th><th align=\"right\">%9.2f &nbsp; </th>\n</tr>\n", $i % 2, $grand_qty_total, $grand_total);

  echo "</table>\n";


  if ( "$style" != "stripped" )
  {
    echo "<br clear=all><hr>\n<table cellpadding=5 cellspacing=5 align=right><tr><td>Rerun as report: </td>\n<td>\n";
    printf( "<a href=\"$this_page\" target=_new>Brief</a>\n", "stripped", "brief");
    printf( " &nbsp;|&nbsp; <a href=\"$this_page&maxresults=5000\">All Rows</a>\n", $style, $format);
    if ( is_member_of('Admin', 'Support') ) {
      printf( " &nbsp;|&nbsp; <a href=\"$this_page\" target=_new>Activity</a>\n", "stripped", "activity");
    }
    printf( " &nbsp;|&nbsp; <a href=\"$this_page\" target=_new>Detailed</a>\n", "stripped", "detailed");
    if ( is_member_of('Admin', 'Support', 'Manage') ) {
      printf( " &nbsp;|&nbsp; <a href=\"$this_page\" target=_new>Quotes</a>\n", "stripped", "quotes");
    }
    if ( is_member_of('Admin', 'Support') ) {
      printf( " &nbsp;|&nbsp; <a href=\"$this_page\" target=_new>Ultimate</a>\n", "stripped", "ultimate");
    }
    if ( is_member_of('Admin', 'Support') ) {
      printf( " &nbsp;|&nbsp; <a href=\"$this_page\" target=_new>Brief (editable)</a>\n", "stripped", "edit");
    }
    echo "</td></tr></table>\n";
  }


include("page-footer.php");

?>