<?php
  include("inc/always.php");
  include("inc/options.php");
  include("inc/code-list.php");
  include( "$base_dir/inc/user-list.php" );

  if ( isset($system_code) && $system_code == "." ) unset( $system_code );

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

  // Build up the column header cell, with %s gaps for the sort, sequence and sequence image
  $header_cell = "<th class=cols><a class=cols href=\"$PHP_SELF?rlsort=%s&rlseq=%s";
  if ( isset($org_code) ) $header_cell .= "&org_code=$org_code";
  if ( isset($system_code) ) $header_cell .= "&system_code=$system_code";
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
  if ( "$qry" != "" ) $header_cell .= "&qry=$qry";
  if ( "$style" != "" ) $header_cell .= "&style=$style";
  if ( "$format" != "" ) $header_cell .= "&format=$format";
  $header_cell .= "\">%s";      // %s for the Cell heading
  $header_cell .= "%s</th>";    // %s For the image

function column_header( $ftext, $fname ) {
  global $rlsort, $rlseq, $header_cell;
  if ( "$rlsort" == "$fname" ) {
    $fseq = ( "$rlseq" == "DESC" ? "ASC" : "DESC");
    $seq_image .= "&nbsp;<img border=0 src=\"images/sort-$rlseq.png\">";
  }
  printf( $header_cell, $fname, $fseq, $ftext, $seq_image );
}

  if ( "$qry" != "" && "$action" == "delete" ) {
    $query = "DELETE FROM saved_queries WHERE user_no = '$session->user_no' AND LOWER(query_name) = LOWER('$qry');";
    $result = awm_pgexec( $dbconn, $query, "requestlist", false, 7);
    unset($qry);
  }

  include("inc/headers.php");

if ( ! $roles['wrms']['Request'] || "$error_msg$error_qry" != "" ) {
  include( "inc/error.php" );
}
else {
  if ( !isset( $style ) || ($style != "plain" && $style != "stripped") ) {
    echo "<form Action=\"$base_url/requestlist.php";
    if ( "$org_code$qs" != "" ) {
      echo "?";
      if ( "$org_code" != "" ) echo "org_code=$org_code" . ( "$qs" == "" ? "" : "&");
      if ( "$qs" != "" ) echo "qs=$qs";
    }
    echo "\" Method=\"POST\">";
    echo "</h3>\n";

    include("inc/system-list.php");
    if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] )
      $system_list = get_system_list( "", "$system_code", 35);
    else
      $system_list = get_system_list( "CES", "$system_code", 35);

    echo "<table border=0 cellspacing=2 cellpadding=0 align=center class=row0 width=100% style=\"border: 1px dashed #aaaaaa;\">\n<tr>\n";
    echo "<td width=100%><table border=0 cellspacing=0 cellpadding=0 width=100%><tr valign=middle>\n";
    if ( "$qs" == "complex" )
      echo "<td class=smb>Find:</td><td class=sml><input class=sml type=text size=10 name=search_for value=\"$search_for\"></td>\n";

    echo "<td class=smb>&nbsp;System:</td><td class=sml><select class=sml name=system_code><option value=\".\">--- All Systems ---</option>$system_list</select></td>\n";

  if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] ) {
    include( "inc/organisation-list.php" );
    $orglist = "<option value=\"\">--- All Organisations ---</option>\n" . get_organisation_list( "$org_code", 30 );
    echo "<td class=smb>&nbsp;Organisation:</td><td class=sml><select class=sml name=\"org_code\">\n$orglist</select></td>\n";
  }
  if ( "$qs" != "complex" )
   echo "<td valign=middle class=smb align=center><input type=submit value=\"RUN QUERY\" alt=go name=submit class=\"submit\"></td><td class=smb width=100px> &nbsp; &nbsp; &nbsp; </td>\n";
  echo "</tr></table></td></tr>\n";


  if ( "$qs" == "complex" ) {
    if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] || $roles['wrms']['Manage'] ) {
      if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] ) {
        $user_org_code = "";
      }
      else {
        $user_org_code = "$session->org_code";
      }
      echo "<tr><td width=100%><table border=0 cellspacing=0 cellpadding=0 width=100%><tr valign=middle>\n";
      if ( $roles['wrms']['Admin'] || $roles['wrms']['Support']  || $roles['wrms']['Manage']) {
        $user_list = "<option value=\"\">--- Any Requester ---</option>" . get_user_list( "", $user_org_code, "" );
        echo "<td class=smb>By:</td><td class=sml><select class=sml name=requested_by>$user_list</select></td>\n";
        if ( !($roles['wrms']['Admin'] || $roles['wrms']['Support']) && !isset($interested_in) ) $interested_in = $session->user_no;
        $user_list = "<option value=\"\">--- Any Interested User ---</option>" . get_user_list( "", $user_org_code, $interested_in );
        echo "<td class=smb>Watching:</td><td class=sml><select class=sml name=interested_in>$user_list</select></td>\n";
      }
      if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] ) {
//        if ( !isset($allocated_to) ) $allocated_to = $session->user_no;
        $user_list = "<option value=\"\">--- Any Assigned Staff ---</option>" . get_user_list( "Support", "", $allocated_to );
        echo "<td class=smb>ToDo:</td><td class=sml><select class=sml name=allocated_to>$user_list</select></td>\n";
      }
      echo "</tr></table></td></tr>\n";
    }
//  else if ( !isset($requested_by) )
//    $requested_by = $session->user_no;


    $request_types = get_code_list( "request", "request_type", "$type_code" );
?>
<tr><td><table border=0 cellspacing=0 cellpadding=0 width=100%><tr valign=middle>
<td class=smb align=right>Last&nbsp;Action&nbsp;From:</td>
<td nowrap class=smb><input type=text size=10 name=from_date class=sml value="<?php echo "$from_date"; ?>">
<a href="javascript:show_calendar('forms[0].from_date');" onmouseover="window.status='Date Picker';return true;" onmouseout="window.status='';return true;"><img valign="middle" src="/images/date-picker.gif" border=0></a>
</td>

<td class=smb align=right>&nbsp;To:</td>
<td nowrap class=smb><input type=text size=10 name=to_date class=sml value="<?php echo "$to_date"; ?>">
<a href="javascript:show_calendar('forms[0].to_date');" onmouseover="window.status='Date Picker';return true;" onmouseout="window.status='';return true;"><img valign="middle" src="/images/date-picker.gif" border=0></a>
</td>
<td class=smb align=right>&nbsp;Type:</td>
<td nowrap class=smb><select name="type_code" class=sml><option value="">-- All Types --</option><?php echo "$request_types"; ?></select></td>
</tr></table></td>
</tr>
<?php
    echo "<tr><td>\n";
    echo "<table border=0 cellspacing=0 cellpadding=0><tr valign=middle><td class=smb align=right valign=top>When:</td><td class=sml valign=top>\n";
    $query = "SELECT * FROM lookup_code WHERE source_table='request' ";
    $query .= " AND source_field='status_code' ";
    $query .= " ORDER BY source_table, source_field, lookup_seq, lookup_code ";
    $rid = pg_Exec( $wrms_db, $query);
    if ( $rid && pg_NumRows($rid) > 1 ) {
      $nrows = pg_NumRows($rid);
      for ( $i=0; $i<$nrows; $i++ ) {
        $status = pg_Fetch_Object( $rid, $i );
        echo "<input type=checkbox name=incstat[$status->lookup_code]";
        if ( !isset( $incstat) || $incstat[$status->lookup_code] <> "" ) echo " checked";
        echo " value=1>" . str_replace( " ", "&nbsp;", $status->lookup_desc) . " &nbsp; ";
        if ( $i == intval($nrows / 2) ) echo "&nbsp;<br>";
      }
      echo "<input type=checkbox name=inactive";
      if ( isset($inactive) ) echo " checked";
      echo " value=1>Inactive";
//      echo "</td>\n</tr></table></td></tr>";
      echo "c</td>\n";
    }
    echo "<td valign=middle class=smb align=center><input type=submit value=\"RUN QUERY\" alt=go name=submit class=\"submit\"></td>\n";
    echo "</tr></table>\n</td></tr>\n";

    echo "<tr><td>\n";
    echo "<table border=0 cellspacing=0 cellpadding=0 align=center>\n";
    echo "<tr valign=middle>\n";
    echo "<td valign=middle class=smb align=right>Save query as:</td><td class=sml valign=top>\n";
    echo "<td valign=middle class=smb align=center><input type=text size=20 value=\"$savelist\" name=savelist class=\"sml\"></td>\n";
    echo "<td valign=middle class=smb align=left><input type=submit value=\"SAVE QUERY\" alt=save name=submit class=\"submit\"></td>\n";
    echo "</tr></table>\n</td></tr>\n";
  }
?>
</table>
</form>

<?php
  } // if  not plain  or stripped style


  /////////////////////////////////////////////////////////////////////////////////////////////////
  //
  // Now we build the statement that will find those requests...
  //
  /////////////////////////////////////////////////////////////////////////////////////////////////

  if ( "$qry$search_for$org_code$system_code " != "" ) {
    $query = "";
    if ( "$qry" != "" ) {
      $qry = tidy($qry);
      $qquery .= "SELECT * FROM saved_queries WHERE user_no = '$session->user_no' AND query_name = '$qry';";
      $result = awm_pgexec( $dbconn, $qquery, "requestlist", false, 7);
      $thisquery = pg_Fetch_Object( $result, 0 );
      $query = $thisquery->query_sql ;
      error_log ( "Q: $qquery " . pg_NumRows($result), 0);
    }
    else {
      // Recommended way of limiting queries to not include sub-tables for 7.1
      $result = awm_pgexec( $wrms_db, "SET SQL_Inheritance TO OFF;" );

      $query .= "SELECT request.request_id, brief, fullname, email, request_on, status.lookup_desc AS status_desc, last_activity, detailed ";
      $query .= ", request_type.lookup_desc AS request_type_desc, lower(fullname) AS lfull, lower(brief) AS lbrief ";
      $query .= ", to_char( request.last_activity, 'FMdd Mon yyyy') AS last_change ";
      $query .= ", to_char( request.request_on, 'FMdd Mon yyyy') AS date_requested ";
      $query .= "FROM ";
      if ( intval("$interested_in") > 0 ) $query .= "request_interested, ";
      if ( intval("$allocated_to") > 0 ) $query .= "request_allocated, ";
      $query .= "request, usr, lookup_code AS status ";
      $query .= ", lookup_code AS request_type";

      $query .= " WHERE request.request_by=usr.username ";
      $query .= " AND request_type.source_table='request' AND request_type.source_field='request_type' AND request.request_type = request_type.lookup_code";
      if ( "$inactive" == "" )        $query .= " AND active ";
      if (! ($roles['wrms']['Admin'] || $roles['wrms']['Support']) )
        $query .= " AND org_code = '$session->org_code' ";
      else if ( isset($org_code) && intval($org_code) > 0 )
        $query .= " AND org_code='$org_code' ";

      if ( intval("$user_no") > 0 )
        $query .= " AND requester_id = " . intval($user_no);
      else if ( intval("$requested_by") > 0 )
        $query .= " AND requester_id = " . intval($requested_by);
      if ( intval("$interested_in") > 0 )
        $query .= " AND request_interested.request_id=request.request_id AND request_interested.user_no = " . intval($interested_in);
      if ( intval("$allocated_to") > 0 )
        $query .= " AND request_allocated.request_id=request.request_id AND request_allocated.allocated_to_id = " . intval($allocated_to);

      if ( "$search_for" != "" ) {
        $query .= " AND (brief ~* '$search_for' ";
        $query .= " OR detailed ~* '$search_for' ) ";
      }
      if ( "$system_code" != "" )     $query .= " AND system_code='$system_code' ";
      if ( "$type_code" != "" )     $query .= " AND request_type=" . intval($type_code);
      error_log( "type_code = >>$type_code<<", 0);

      if ( "$from_date" != "" )     $query .= " AND request.last_activity >= '$from_date' ";

      if ( "$to_date" != "" )     $query .= " AND request.last_activity<='$to_date' ";

      if ( isset($incstat) && is_array( $incstat ) ) {
        reset($incstat);
        $query .= " AND (request.last_status ~* '[";
        while( list( $k, $v) = each( $incstat ) ) {
          $query .= $k ;
        }
        $query .= "]') ";
        error_log( "1-> $query", 0);
        if ( eregi("save", "$submit") && "$savelist" != "" ) {
          $savelist = tidy($savelist);
          $qquery = tidy($query);
          $query = "DELETE FROM saved_queries WHERE user_no = '$session->user_no' AND LOWER(query_name) = LOWER('$savelist');
INSERT INTO saved_queries (user_no, query_name, query_sql) VALUES( '$session->user_no', '$savelist', '$qquery');
$query";
        }
      }
    }
    $query .= " AND status.source_table='request' AND status.source_field='status_code' AND status.lookup_code=request.last_status ";
    $query .= " ORDER BY $rlsort $rlseq ";
    $query .= " LIMIT 100 ";
    $result = awm_pgexec( $wrms_db, $query, "requestlist", false, 7 );

    if ( "$style" != "stripped" ) {
      if ( $result && pg_NumRows($result) > 0 )
        echo "\n<small>" . pg_NumRows($result) . " requests found</small>"; // <p>$query</p>";
      else {
        echo "\n<p><small>No requests found</small></p>";
        if ( $roles['wrms']['Admin'] )
          echo "<p>You are an admin, so I can show you this:<br>\n<small><small>$query</small></small></p>";
      }
    }

function header_row() {
    echo "<tr>\n";
    column_header("WR&nbsp;#", "request_id");
    column_header("Request By", "lfull" );
    column_header("Request On", "request_on" );
    column_header("Description", "lbrief");
    column_header("Status", "status_desc" );
    column_header("Type", "request_type_desc" );
    column_header("Last Chng", "request.last_activity");
    echo "</tr>";
}
    echo "<table border=\"0\" align=left width=100%>\n";

    $show_notes = ($format == "ultimate" || $format == "detailed" );
    $show_details = ($format == "ultimate" || $format == "detailed" || "$format" == "activity" || "$format" == "quotes" );
    $show_quotes = ( $format == "ultimate" || "$format" == "activity" || "$format" == "quotes" );
    $show_work = ( $format == "ultimate" || "$format" == "activity" ) && ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] );
    if ( $show_details ) {
      include("inc/html-format.php");
    }
    else
      header_row();


    if ( $result ) {
      $grand_total = 0.0;

      // Build table of requests found
      for ( $i=0; $i < pg_NumRows($result); $i++ ) {
        $thisrequest = pg_Fetch_Object( $result, $i );

        if ( $show_details ) header_row();
        printf( "<tr class=row%1d>\n", $i % 2);

        echo "<td class=sml align=center><a href=\"request.php?request_id=$thisrequest->request_id\">$thisrequest->request_id</a></td>\n";
        echo "<td class=sml nowrap><a href=\"mailto:$thisrequest->email\">$thisrequest->fullname</a></td>\n";
        echo "<td class=sml align=center>$thisrequest->date_requested</td>\n";
        echo "<td class=sml><a href=\"request.php?request_id=$thisrequest->request_id\">$thisrequest->brief";
//        if ( "$thisrequest->brief" == "" ) echo "-- no description --";
        if ( "$thisrequest->brief" == "" ) echo substr( $thisrequest->detailed, 0, 50) . "...";
        echo "</a></td>\n";
        echo "<td class=sml>&nbsp;$thisrequest->status_desc&nbsp;</td>\n";
        echo "<td class=sml>&nbsp;" . str_replace( " ", "&nbsp;", $thisrequest->request_type_desc) . "&nbsp;</td>\n";
        echo "<td class=sml align=center>" . str_replace( " ", "&nbsp;", $thisrequest->last_change) . "</td>\n";

        echo "</tr>\n";

        if ( $show_details ) {
          printf( "<tr class=row%1d>\n", $i % 2);
          echo "<td colspan=7>" . html_format($thisrequest->detailed) . "</td>\n";
          echo "</tr>\n";
        }
        if ( $show_quotes ) {
          $subquery = "SELECT *, to_char( quoted_on, 'DD/MM/YYYY') AS nice_date ";
          $subquery .= "FROM request_quote, usr ";
          $subquery .= "WHERE request_id = $thisrequest->request_id ";
          $subquery .= "AND usr.user_no = request_quote.quote_by_id ";
          $subquery .= "ORDER BY request_id, quoted_on ";
          $total = 0.0;
          $subres = awm_pgexec( $dbconn, $subquery, "requestlist" );
          for ( $j=0; $subres && $j < pg_NumRows($subres); $j++ ) {
            $thisquote = pg_Fetch_Object( $subres, $j );
            printf( "<tr class=row%1d valign=top>\n", $i % 2);
            echo "<td>$thisquote->nice_date</td>\n";
            echo "<td>$thisquote->fullname</td>\n";
            echo "<td colspan=4><b>$thisquote->quote_brief</b><br><hr>\n";
            echo html_format($thisquote->quote_details);
            echo "</td>\n";
            printf("<td align=right>%9.2f &nbsp; %s</td>\n", $thisquote->quote_amount, $thisquote->quote_units);
            // printf("<td align=right>%9.2f &nbsp; </td>\n", $thisquote->quote_rate);
            // $value = $thisquote->quote_quantity * $thisquote->quote_rate;
            // $total += $value;
            // printf("<td align=right>%9.2f &nbsp; </td>\n", $value);
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
            printf( "<tr class=row%1d valign=top>\n", $i % 2);
            echo "<td>$thisnote->nice_date</td>\n";
            echo "<td>$thisnote->fullname</td>\n";
            echo "<td colspan=5>" . html_format($thisnote->note_detail) . "</td>\n";
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
          $subres = awm_pgexec( $dbconn, $subquery, "requestlist" );
          for ( $j=0; $subres && $j < pg_NumRows($subres); $j++ ) {
            $thiswork = pg_Fetch_Object( $subres, $j );
            printf( "<tr class=row%1d valign=top>\n", $i % 2);
            echo "<td>$thiswork->nice_date</td>\n";
            echo "<td>$thiswork->fullname</td>\n";
            echo "<td colspan=2>$thiswork->work_description</td>\n";
            printf("<td align=right>%9.2f &nbsp; </td>\n", $thiswork->work_quantity);
            printf("<td align=right>%9.2f &nbsp; </td>\n", $thiswork->work_rate);
            $value = $thiswork->work_quantity * $thiswork->work_rate;
            $total += $value;
            printf("<td align=right>%9.2f &nbsp; </td>\n", $value);
            echo "</tr>\n";
          }
          if ( $j > 0 )
            printf( "<tr class=row%1d>\n<td colspan=6>&nbsp; &nbsp; &nbsp; Request #$thisrequest->request_id total</td>\n<td align=right>%9.2f &nbsp; </td>\n</tr>\n", $i % 2, $total);
          $grand_total += $total;
        }

        if ( $show_details )
          echo "<tr class=row3>\n<td colspan=7>&nbsp;</td></tr>\n";
      }
    }
    if ( $show_work )
      printf( "<tr class=row%1d>\n<th align=left colspan=6>Grand Total</th>\n<th align=right>%9.2f &nbsp; </th>\n</tr>\n", $i % 2, $grand_total);
    echo "</table>\n";

    if ( "$style" != "stripped" ) {
      $this_page = "$PHP_SELF?style=%s&format=%s";
      if ( "$qry" != "" ) $this_page .= "&qry=$qry";
      if ( "$search_for" != "" ) $this_page .= "&search_for=$search_for";
      if ( "$org_code" != "" ) $this_page .= "&org_code=$org_code";
      if ( "$system_code" != "" ) $this_page .= "&system_code=$system_code";
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

      echo "<br clear=all><hr>\n<table cellpadding=5 cellspacing=5 align=right><tr><td>Rerun as report: </td>\n<td>\n";
      printf( "<a href=\"$this_page\" target=_new>Brief</a>\n", "stripped", "brief");
      if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] )
        printf( " &nbsp;|&nbsp; <a href=\"$this_page\" target=_new>Activity</a>\n", "stripped", "activity");
      printf( " &nbsp;|&nbsp; <a href=\"$this_page\" target=_new>Detailed</a>\n", "stripped", "detailed");
      if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] || $roles['wrms']['Manage'] )
        printf( " &nbsp;|&nbsp; <a href=\"$this_page\" target=_new>Quotes</a>\n", "stripped", "quotes");
      if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] )
        printf( " &nbsp;|&nbsp; <a href=\"$this_page\" target=_new>Ultimate</a>\n", "stripped", "ultimate");
      if ( "$qry" != "" ) {
        echo "</td><td>|&nbsp; &nbsp; or <a href=\"$PHP_SELF?qs=complex&qry=$qry&action=delete\" class=sbutton>Delete</a> it\n";
      }
      echo "</td></tr></table>\n";
    }

  }

} /* The end of the else ... clause waaay up there! */

include("inc/footers.php");

?>
