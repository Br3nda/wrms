<?php
  include( "$base_dir/inc/code-list.php");
  include( "$base_dir/inc/user-list.php" );
  include( "$base_dir/inc/html-format.php");
  $status_list   = get_code_list( "request", "status_code", "$request->last_status" );
  $is_request = isset( $request );
  $use_sla = ( $is_request && $request->current_sla == 't' && $request->sla_response_time > 0 ) || (!$is_request && $session->current_sla == 't') ;
  $use_sla = ( $is_request && $request->current_sla == 't' ) || (!$is_request && ($session->current_sla == 't' || $roles['wrms']['Admin'] || $roles['wrms']['Support']) ) ;

  if ( $editable ) {
    /* if it's editable then we'll need severity and request_type lists for drop-downs */
    $severities = get_code_list( "request", "severity_code", "$request->severity_code" );
    $request_types = get_code_list( "request", "request_type", "$request->request_type" );
    $sla_urgencies = get_code_list( "request", "sla_response", "$request->request_sla_code" );
    $urgencies = get_code_list( "request", "urgency", "$request->urgency" );
    $importances = get_code_list( "request", "importance", "$request->importance" );

    if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] || $roles['wrms']['Manage']  ) {
      if ( $roles['wrms']['Admin'] || $roles['wrms']['Support']  ) {
        $user_list = "<option value=\"\">--- not selected ---</option>\n" . get_user_list( "", "", "" );
        $support_list = "<option value=\"\">--- not assigned ---</option>\n";
        $support_list .= get_user_list( "Support", "", $session->user_no );
      }
      else
        $user_list = get_user_list( "", $session->org_code, $session->user_no );
    }
    if ( $allocated_to || $sysmgr ) {
      $quote_types = get_code_list( "request_quote", "quote_type", "Q" );
      $quote_units = get_code_list( "request_quote", "quote_units", "hours" );
    }

    include("$base_dir/inc/system-list.php");
    if ( $session->status == 'S' )  // Support Staff
      $system_codes = get_system_list("ASCE", "$request->system_code");
    else
      $system_codes = get_system_list("ASCE", "$request->system_code");
    if ( ! $is_request ) {
      $system_codes = "<option value=\"UNKNOWN\">--- not assigned ---</option>\n$system_codes";
    }
  }

  $hdcell = "";
  $tbldef = "<table width=100% cellspacing=0 border=0 cellpadding=2";
  echo "$tbldef>\n<tr><td align=left>\n";;
  if ( "$because" != "" )
    echo $because;
  else if ( ! $plain ) {
    ?><p class=helptext>Use this form to enter changes to details for the
    requests of your systems, or to enter details for new requests.</p><?php
  }
  echo "</td></tr>\n</table>\n";

  echo "$tbldef bgcolor=$colors[7]><tr>$hdcell<td class=h3 colspan=3 align=right>";
  if ( ! $plain ) {
    echo "<form action=\"request.php\" method=post enctype=\"multipart/form-data\">";
    echo "<input type=\"hidden\" name=\"request_id\" value=\"$request->request_id\">";
  }
  echo "Request details</td></TR>\n";

  if ( !$is_request ) {
    if ( $roles[wrms][Admin] || $roles[wrms][Support] || $roles[wrms][Manage] ) {
      echo "<tr><th class=rows align=right>User:</th>";
      echo "<td colspan=2 valign=middle align=left><select class=sml name=\"new_user_no\">$user_list</select>\n";
      if ( !$is_request ) {
        echo " &nbsp; <label><input class=sml type=checkbox name=\"in_notify\" value=1 checked>&nbsp;update user on the status of this request.</label></td></tr>\n";
      }
      echo "</td></tr>\n";
    }
    if ( $roles[wrms][Admin] || $roles[wrms][Support] ) {
      echo "<TR><TH align=right class=rows>Assign to:</TH>";
      echo "<TD colspan=2 ALIGN=LEFT><SELECT class=sml NAME=\"new_assigned\">$support_list</SELECT></TD></TR>";
      echo "</TD></TR>\n";
    }
  }

  echo "<tr><th class=rows align=right>";
  if ( $is_request ) echo "WR #:"; else echo "Request:";
  echo "</th>\n";
  if ( $is_request ) echo "<td align=center class=h2>$request->request_id</td>\n";
  echo "<td";
  if ( !isset( $request ) ) echo " colspan=2";
  if ( $editable ) {
    echo "><input class=sml type=\"text\" name=\"new_brief\" size=40 value=\"";
    if ( $is_request ) echo htmlspecialchars($request->brief);
    echo "\">";
  }
  else
    echo " valign=middle><h2>$request->brief";
  echo "</td></tr>\n";

  if ( $is_request ) {
    // --------------------- FROM -----------------------
    echo "<TR><th class=rows align=right>From:</TH>";
    echo "<TD ALIGN=CENTER>$request->fullname</TD>\n";
    echo "<TD ALIGN=LEFT><b class=smb>Entered:</b> " . nice_date($request->request_on);
    if ( strcmp( $request->eta, "") )
      echo " &nbsp; &nbsp; &nbsp; <b class=smb>ETA:</b> " .  substr( nice_date($request->eta), 7);
    echo "</TD></TR>\n";

    // --------------------- STATUS -----------------------
    echo "<TR><th class=rows align=right VALIGN=MIDDLE>Status:</TH>\n";
    echo "<TD ALIGN=CENTER>";
    if ( $editable ) {
      echo "<LABEL><INPUT class=sml TYPE=\"checkbox\" NAME=\"new_active\" VALUE=\"TRUE\"";
      if ( strtolower( substr( "$request->active", 0, 1)) == "t" ) echo " CHECKED";
        echo ">&nbsp;Active</LABEL>";
    }
    else if ( strtolower( substr( "$request->active", 0, 1)) == "t" ) echo "Active";
    else echo "Inactive";
    echo "</TD>\n<TD ALIGN=LEFT>&nbsp;$request->last_status - $request->status_desc</TD></TR>\n";
  }

  // --------------------- SYSTEM -----------------------
  if ( ($editable && "$system_codes" <> "") || (! $editable && $is_request ) ) {
    echo "<TR><th class=rows align=right VALIGN=MIDDLE>System:</TH>\n";
    if ( $is_request )
      echo "<TD ALIGN=CENTER>$request->system_code</TD>\n";
    echo "<td align=left";
    if ( !isset( $request ) ) echo " colspan=2";
    if ( $editable )
      echo "><SELECT class=sml NAME=\"new_system_code\">$system_codes</SELECT>";
    else
      echo ">$request->system_desc";
    echo "</td></tr>\n";
  }

  // --------------------- TYPE -----------------------
  echo "<tr><th class=rows align=right>Type:</th>\n";
  if ( $is_request ) echo "<TD ALIGN=CENTER>&nbsp;</TD>\n";
  echo "<td align=left";
  if ( !isset( $request ) ) echo " colspan=2";
  if ( $editable )
    echo "><SELECT class=sml NAME=\"new_type\">$request_types</SELECT>";
  else
    echo ">$request->request_type_desc";
  echo "</td></tr>\n";

  // ---------------URGENCY & SLA Response -------------------
  echo "<tr><th class=rows align=right>Urgency:</th>\n";
  if ( $is_request ) {
    printf( "<td align=center>%s</td>\n", ( $use_sla && ($request->sla_response_time > 0) ? $request->sla_response_type . '-' . $request->sla_response_time : $request->urgency_desc));
  }
  printf( "<td align=left%s>",  ($is_request ?  "" : " colspan=2"));
  if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] ) {
    if ( $editable ) {
      echo "<select class=sml name=\"new_urgency\">$urgencies</select>";
      if ( !$is_request || $use_sla ) {
        echo " <b>OR</b> ";
        echo "<select class=sml name=\"new_sla_code\">$sla_urgencies</select>";
        echo " <b class=smb>(if SLA applies)</b> ";
      }
    }
    else if ( $use_sla && ($request->sla_response_time > 0) )
      echo "$request->sla_response_desc";
    else
      echo "$request->urgency_desc";
    echo "</td></tr>\n";
  }
  else {
    if ( $editable ) {
      printf( "<SELECT class=sml name=\"new_%s\">$urgencies</SELECT>", ($use_sla ? "sla_code" : "urgency"));
      if ( $session->current_sla ) {
        echo " <b>OR</b> ";
        echo "<select class=sml name=\"new_sla_code\">$sla_urgencies</select>";
        echo " <b class=smb>(if SLA applies)</b> ";
      }
    }
    else if ( $use_sla && ($request->sla_response_time > 0) )
      echo "$request->sla_response_desc";
    else
      echo "$request->urgency_desc";
    echo "</td></tr>\n";
  }

  // ---------------Requested Done By -------------------
  echo "<tr><th class=rows align=right>Due Date:</th>\n";
  if ( $is_request ) {
    printf( "<td align=center>%s</td>\n", ("" == "$request->agreed_due_date" ? $request->requested_by_date : $request->agreed_due_date) );
  }
  printf( "<td align=left%s><b class=smb>Requested:</b>",  ($is_request ?  "" : " colspan=2"));
  if ( $editable ) {
    echo "<input class=sml name=\"new_requested_by_date\" value=\"$request->requested_by_date\" size=10>";
  }
  else if ( "$request->requested_by_date" == "" )
    echo " -- not set -- ";
  else
    echo "$request->requested_by_date";
  echo " &nbsp; &nbsp;\n";

  // ---------------Agreed Due By -------------------
  echo "<b class=smb>Agreed:</b>";
  if ( ($roles['wrms']['Admin'] || $roles['wrms']['Support']) && $editable ) {
    echo "<input class=sml name=\"new_agreed_due_date\" value=\"$request->agreed_due_date\" size=10>";
  }
  else if ( "$request->agreed_due_date" == "" )
    echo " -- not set -- ";
  else
    echo "$request->agreed_due_date";
  echo "</td></tr>\n";


  // --------------------- IMPORTANCE -----------------------
  echo "<tr><th class=rows align=right>Importance:</th>\n";
  if ( $is_request ) echo "<TD ALIGN=CENTER>&nbsp;</TD>\n";
  printf( "<td align=left%s>",  ($is_request ?  "" : " colspan=2"));
  if ( $editable )
    echo "<SELECT class=sml NAME=\"new_importance\">$importances</SELECT>";
  else
    echo "$request->importance_desc";
  echo "</td></tr>\n";

  // --------------------- DETAILS -----------------------
  echo "<tr><th class=rows align=right valign=top><br>Details:</th>\n";
  echo "<td colspan=2>";
  if ( $editable )
    echo "<textarea name=\"new_detail\" rows=12 cols=60  wrap=\"SOFT\">$request->detailed</textarea>";
  else
    echo html_format($request->detailed);
  echo "</td></tr>\n";

  // ----------------- NOTIFY (for normal users) -----------------
  if ( ! ($roles['wrms']['Admin'] || $roles['wrms']['Support'] || $roles['wrms']['Manage'] || $is_request) ) {
    echo "<tr><th class=rows align=right>Notify:</th>\n";
    echo "<td colspan=2><label><input type=checkbox name=\"in_notify\" value=1 checked>&nbsp;Keep me updated on the status of this request.</label></td></tr>\n";
  }

  echo "</table>\n";

  //---------------- Update Details */
  if ( isset( $request ) ) {
    $query = "SELECT * FROM request_update, system_update WHERE request_update.request_id = $request->request_id AND request_update.update_id = system_update.update_id ORDER BY request_update.update_id DESC";
    $updateq = awm_pgexec( $wrms_db, $query);
    $rows = pg_NumRows($updateq);
    if ( $rows > 0 ) {
?>
<?php echo "$tbldef>\n<TR><TD CLASS=sml COLSPAN=6>&nbsp;</TD></TR><TR>$hdcell"; ?>
<TD CLASS=h3 COLSPAN=5 ALIGN=RIGHT<?php echo " bgcolor=$colors[8]"; ?>><FONT SIZE=+1 color=<?php echo $colors[1]; ?>><B>Program Update Details</B></FONT></TD></TR>
<TR><th>&nbsp;</th>
 <TH class=cols>ID</TH>
 <TH class=cols>Done By</TH>
 <TH class=cols>Done On</TH>
 <TH class=cols>Description</TH>
 <TH class=cols>&nbsp;</TH>
</TR>
<?php
      for( $i=0; $i<$rows; $i++ ) {
        $update = pg_Fetch_Object( $updateq, $i );

        printf("<tr class=row%1d>", ($i % 2) );
        echo "<th>&nbsp;</TH>\n";
        echo "<td class=h2 VALIGN=TOP ALIGN=CENTER ROWSPAN=2><FONT SIZE=+2>$update->update_id</FONT></td>\n";
        echo "<td>$update->update_by</TD>\n";
        echo "<td>" . nice_date($update->update_on) . "</TD>\n";
        echo "<td>$update->update_brief</td>";
        echo "<td><A HREF=\"$update->file_url\">Download</A></TD>\n";
        echo "</tr>\n";
        if(floor($i/2)-($i/2)==0) echo "<tr bgcolor=$colors[6]>";
        else echo "<tr bgcolor=$colors[7]>";
        echo "<TH>&nbsp;</TH><TD COLSPAN=4>";
        echo html_format( $update->update_description) . "</TD></tr>";
      }
      echo "</TABLE>";
    }


  /***** Quote Details */
  /* we only show quote details if it is 'quotable' (i.e. requestor, administrator or catalyst owner) */
  if ( $quotable ) {
    $query = "SELECT *, get_lookup_desc('request_quote','quote_type', request_quote.quote_type) AS type_desc ";
    $query .= "FROM request_quote, usr ";
    $query .= "WHERE request_quote.request_id = $request->request_id ";
    $query .= "AND request_quote.quote_by_id = usr.user_no ";
    $query .= " ORDER BY request_quote.quote_id";
    $quoteq = awm_pgexec( $wrms_db, $query);
    $rows = pg_NumRows($quoteq);
    if ( $rows > 0 || (($allocated_to || $sysmgr) && !$plain) ) {
      echo "$tbldef><tr><td class=sml colspan=6>&nbsp;</td></tr><tr>$hdcell";
      echo "<td class=h3 colspan=6 align=right bgcolor=$colors[8]><font size=+1 color=$colors[1]><b>Quotations</b></font></td></tr>\n";

      echo "<TR>";
      if ( $rows > 0 ) echo "<th class=smb>Quote</th><th class=smb>Done By</th><th class=smb>";
      else echo "<th class=smb colspan=3>";
      echo "Brief</th>";
      if ( $rows > 0 ) echo "<th class=smb>Done On</th>";
      echo "<th class=smb>Type</th><th class=smb>Amount</th>";
      if ( $rows <= 0 ) echo "<th class=smb>Units</th>";
      echo "</tr>\n";

      for ( $i=0; $i < $rows; $i++ ) {
        $quote = pg_Fetch_Object( $quoteq, $i );
        if ( $i % 2 == 0 ) echo "<tr bgcolor=$colors[row1]>";
        else echo "<tr bgcolor=$colors[row2]>";
        echo "<TD ALIGN=CENTER>$quote->quote_id</TD>\n";
        echo "<TD ALIGN=CENTER>$quote->fullname</TD>\n";
        echo "<TD>$quote->quote_brief</TD>\n";
        echo "<TD ALIGN=CENTER>" . nice_date($quote->quoted_on) . "</TD>\n";
        echo "<TD ALIGN=CENTER>$quote->quote_type - $quote->type_desc</TD>\n";
        echo "<TD ALIGN=RIGHT>" . number_format($quote->quote_amount, 2) . " $quote->quote_units</TD>\n</tr>";

        if ( $i % 2 == 0 ) echo "<tr bgcolor=$colors[row1]>";
        else echo "<tr bgcolor=$colors[row2]>";
        echo "<TD COLSPAN=6>";
        echo html_format($quote->quote_details) . "</A></TD></TR>\n";
      }
      if ( ($allocated_to || $sysmgr) && ! $plain ) {
        printf("<tr class=row%1d>", ($i % 2) );
        echo "<TD colspan=3><input name=new_quote_brief size=35 type=text></TD>\n";
        echo "<TD><select class=sml name=new_quote_type>$quote_types</select></TD>\n";
        echo "<TD ALIGN=RIGHT><input name=new_quote_amount size=10 type=text></td>";
        echo "<TD ALIGN=LEFT><select class=sml name=new_quote_unit>$quote_units</select></TD></tr>\n";
        if ( $i % 2 == 0 ) echo "<tr bgcolor=$colors[row1]>";
        else echo "<tr bgcolor=$colors[row2]>";
        echo "<TD COLSPAN=6><textarea name=new_quote_details rows=4 cols=60 wrap=soft></textarea></TD></TR>\n";
      }
      echo "</TABLE>";
    }
  }  // if quotable

  if ( !$plain && ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] || $roles['wrms']['Manage'] ) ) {
    $user_list = "<option value=\"\">--- no change ---</option>\n";
    if ( $roles['wrms']['Admin'] || $roles['wrms']['Support']  ) {
      $support_list = $user_list;
      $support_list .= get_user_list( "Support", "", "" );
      $user_list .= get_user_list( "", "", "" );
    }
    else
      $user_list .= get_user_list( "", $session->org_code, "" );
  }

  /***** Allocated People */
  /* People who have been allocated to the request - again, only if there are any.  */
  $query = "SELECT usr.user_no, usr.fullname, organisation.abbreviation ";
  $query .= "FROM request_allocated, usr, organisation ";
  $query .= "WHERE request_id = '$request->request_id' ";
  $query .= "AND usr.user_no=request_allocated.allocated_to_id ";
  $query .= "AND organisation.org_code = usr.org_code ";
  $query .= "ORDER BY request_allocated.allocated_on ";
  $allocq = awm_pgexec( $wrms_db, $query);
  $rows = pg_NumRows($allocq);
  if ( $rows > 0 || (! $plain && (($roles['wrms']['Admin'] || $roles['wrms']['Support'] ))) ) {
    echo "$tbldef>\n<TR><TD CLASS=sml COLSPAN=3>&nbsp;</TD></TR>\n";
    echo "<TR>$hdcell<TD CLASS=h3 COLSPAN=2 ALIGN=RIGHT bgcolor=$colors[8]><FONT SIZE=+1 color=$colors[1]><B>Work Allocated To</B></FONT></TD></TR>\n";
    echo "<TR VALIGN=TOP><td>";
    for( $i=0; $i<$rows; $i++ ) {
      $alloc = pg_Fetch_Object( $allocq, $i );
      if ( $i > 0 ) echo ", ";

      if ( ($allocated_to || $sysmgr) && ! $plain )
        echo "<a href=\"request.php?submit=deallocate&user_no=$alloc->user_no&request_id=$request_id\">\n";
      echo "$alloc->fullname ($alloc->abbreviation)\n";
      if ( ($allocated_to || $sysmgr) && ! $plain )
        echo "</a>\n";
    }

    if ( $plain || !($roles['wrms']['Admin'] || $roles['wrms']['Support'] ) )
      echo "</TD>\n<TD>&nbsp;";  // Or we could correct the cellspan above for this case...
    else {
      echo "</TD>\n<TD ALIGN=RIGHT nowrap><font size=-2>";
      echo "Add:&nbsp;<select class=sml name=\"new_allocation\">$support_list</SELECT>\n";
      echo "</font>";
    }
    echo "</TD></TR></TABLE>\n";
  }

  /***** Timesheet Details */
  /* we only show timesheet details if they exist */
  $query = "SELECT *, date_part('epoch',request_timesheet.work_duration) AS seconds ";
  $query .= "FROM request_timesheet, usr ";
  $query .= "WHERE request_timesheet.request_id = $request->request_id ";
  $query .= "AND request_timesheet.work_by_id = usr.user_no ";
  $query .= "ORDER BY request_timesheet.work_on ";
  $workq = awm_pgexec( $wrms_db, $query);
  $rows = pg_NumRows($workq);
  if ( $rows > 0  || (($allocated_to || $sysmgr) && !$plain) ) {
?>
<?php echo "$tbldef>\n<tr><td class=sml colspan=7>&nbsp;</td></tr><tr>$hdcell"; ?>
<td class=h3 colspan=7 align=right<?php echo " bgcolor=$colors[8]"; ?>><FONT SIZE=+1 color=<?php echo $colors[1]; ?>><B>Work Done</B></FONT></TD></TR>
 <tr valign=top>
   <th class=smb>Done By</th>
   <th class=smb>Done On</th>
   <th class=smb>Quantity</th>
   <th class=smb>Rate</th>
   <th class=smb>Cost</th>
   <th class=smb colspan=2>Description</th>
 </tr>
<?php
    $total_cost = 0;
    for( $i=0; $i<$rows; $i++ ) {
      $work = pg_Fetch_Object( $workq, $i );
      $tmp = $work->work_rate * $work->work_quantity;
      $total_cost += $tmp;

      printf("<tr class=row%1d>", ($i % 2) );
      echo "<td>" . str_replace(" ", "&nbsp;", $work->fullname) . "</td>\n";
      echo "<td align=right>" . substr( nice_date($work->work_on), 7) . "</td>\n";
      echo "<td align=right nowrap>$work->work_quantity&nbsp;$work->work_units &nbsp;</td>\n";
      echo "<td align=right nowrap>$work->work_rate&nbsp;</td>\n";
      echo "<td align=right nowrap>$tmp&nbsp;</td>\n";
      echo "<td>$work->work_description</td>\n";
      if ( "$style" != "plain" && ($roles['wrms']['Admin'] || $roles['wrms']['Support'] ) ) {
        echo "<td align=right nowrap><font size=-2><a class=submit href=\"request.php?submit=deltime";
        echo "&request_id=$request_id&timesheet_id=$work->timesheet_id\">";
        echo "DEL</a></font></td>";
      }
      echo "</tr>";
    }
    printf("<tr class=row%1d>", ($i % 2) );
    echo "<td colspan=3 align=left><b>Total</b></td>
<td colspan=2 align=right nowrap>\$$total_cost&nbsp;</td>
<td colspan=2>&nbsp;</td>
</tr>";
    $i++;

    if ( ($allocated_to || $sysmgr) && ! $plain ) {
      printf("<tr class=row%1d>", ($i % 2) );
      echo "<td colspan=2>$session->fullname<br>\n";
      echo "<input name=new_work_on size=10 type=text value=\"";
      if ( isset($old_work_on) ) {
        echo substr( nice_date($old_work_on), 7);
        $quote_units = get_code_list( "request_quote", "quote_units", "$old_work_units" );
      }
      else {
        echo "today";
        $old_work_rate = $request->work_rate;
      }
      echo "\"></TD>\n";
      echo "<td align=center><input name=new_work_quantity size=6 type=text value=\"$old_work_quantity\"><br>\n";
      echo "<select class=sml name=new_work_units>$quote_units</select></TD>\n";
      echo "<td><input name=new_work_rate size=5 type=text value=\"$old_work_rate\"><br>($ per unit)</TD>\n";
      echo "<td colspan=3><textarea name=new_work_details rows=3 cols=30 wrap=soft>$old_work_details</textarea></TD></TR>\n";
    }
    echo "</TABLE>\n";

  }  // if rows>0

  /***** Interested People */
  /* People who are interested - again, only if there are any.  The requestor is not shown */
  $query = "SELECT usr.fullname, organisation.abbreviation, usr.user_no ";
  $query .= "FROM request_interested, usr, organisation ";
  $query .= "WHERE request_id = '$request->request_id' ";
  $query .= "AND request_interested.user_no = usr.user_no ";
  $query .= "AND organisation.org_code = usr.org_code ";
  $peopleq = awm_pgexec( $wrms_db, $query);
  $rows = pg_NumRows($peopleq);
  if ( $rows > 0 ) {
    echo "$tbldef>\n<TR><TD CLASS=sml COLSPAN=3>&nbsp;</TD></TR>\n";
    echo "<TR>$hdcell<TD CLASS=h3 COLSPAN=2 ALIGN=RIGHT bgcolor=$colors[8]><FONT SIZE=+1 color=$colors[1]><B>Interested Users</B></FONT></TD></TR>\n";
    echo "<TR VALIGN=TOP>\n<td>";
    for( $i=0; $i<$rows; $i++ ) {
      $interested = pg_Fetch_Object( $peopleq, $i );
      if ( $i > 0 ) echo ", ";
      if ( ($allocated_to || $sysmgr || $roles['wrms']['Manage']) && ! $plain )
        echo "<a href=\"request.php?submit=deregister&user_no=$interested->user_no&request_id=$request_id\">\n";
      echo "$interested->fullname ($interested->abbreviation)\n";
      if ( ($allocated_to || $sysmgr) && ! $plain )
        echo "</a>\n";
    }

    if ( $plain )
      echo "</TD>\n<TD>&nbsp;";  // Or we could correct the cellspan above for this case...
    else {
      $notify_to = notify_emails( $wrms_db, $request_id );
      if ( strstr( $notify_to, $session->email ) ) {
        $tell = "Stop informing me on this request";
        $action = "deregister";
      }
      else {
        $tell = "Inform me about updates to this request!";
        $action = "register";
      }

      echo "</TD>\n<TD ALIGN=RIGHT nowrap><font size=-2>";
      if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] || $roles['wrms']['Manage']  ) {
        echo "Add:&nbsp;<select class=sml name=\"new_interest\">$user_list</SELECT>\n";
      }
      else
        echo "<a class=r href=\"request.php?submit=$action&request_id=$request_id\">$tell</a>";
      echo "</font>";
    }
    echo "</TD>\n</TR></TABLE>\n";
  }
?>


<?php /***** Notes */
  $noteq = "SELECT * FROM request_note WHERE request_note.request_id = '$request->request_id' ";
  $noteq .= "ORDER BY note_on ";
  $note_res = awm_pgexec( $wrms_db, $noteq );
  $rows = pg_NumRows($note_res);
  if ( $rows > 0 ) {
?>

<?php echo "$tbldef>\n<TR><TD CLASS=sml COLSPAN=4>&nbsp;</TD></TR><TR>$hdcell"; ?>
<TD CLASS=h3 COLSPAN=3 ALIGN=RIGHT<?php echo " bgcolor=$colors[8]"; ?>><FONT SIZE=+1 color=<?php echo $colors[1]; ?>><B>Associated Notes</B></FONT></TD></TR>
<TR VALIGN=TOP>
  <TH ALIGN=LEFT class=cols>Noted&nbsp;By</TH>
  <TH class=cols>Noted On</TH>
  <TH ALIGN=LEFT class=cols>Details</TH>
</TR>
<?php /*** the actual details of notes */
    for( $i=0; $i<$rows; $i++ ) {
      $request_note = pg_Fetch_Object( $note_res, $i );
      printf("<tr class=row%1d>", ($i % 2) );
      echo "<TD>$request_note->note_by</TD><TD>";
      echo nice_date($request_note->note_on);
      echo "</TD>\n<TD>" . html_format($request_note->note_detail) . "</TD></TR>\n";
    }
    echo "</TABLE>\n";
  }  // if rows > 0
?>

<?php /***** Status Changes */
  $statq = "SELECT * FROM request_status, lookup_code AS status, usr ";
  $statq .= " WHERE request_status.request_id = '$request->request_id' AND request_status.status_code = status.lookup_code ";
  $statq .= " AND status.source_table='request' AND status.source_field='status_code' ";
  $statq .= " AND usr.user_no=request_status.status_by_id ";
  $statq .= " ORDER BY status_on ";
  $stat_res = awm_pgexec( $wrms_db, $statq);
  $rows = pg_NumRows($stat_res);
  if ( $rows > 0 ) {
    echo "$tbldef>\n<TR><TD CLASS=sml COLSPAN=4>&nbsp;</TD></TR><TR>$hdcell";
?>
<td class=h3 colspan=3 align=right<?php echo " bgcolor=$colors[8]"; ?>><font size=+1 color=<?php echo $colors[1]; ?>><B>Changes in Status</B></FONT></TD></TR>
<tr valign=top>
  <th class=smb width="15%" align=left>Changed By</th>
  <th class=smb width="25%" align=left>Changed On</th>
  <th class=smb width="60%" align=left>Changed To</th>
</tr>
<?php /* the actual status stuff */
    for( $i=0; $i<$rows; $i++ ) {
      $request_status = pg_Fetch_Object( $stat_res, $i );
      printf("<tr class=row%1d>", ($i % 2) );
      echo "<TD>$request_status->fullname</TD>\n<TD>" . nice_date($request_status->status_on) . "</TD> <TD>$request_status->status_code - $request_status->lookup_desc</TD></TR>\n";
    }
    echo "</TABLE>\n";
  }  // if rows > 0

  if ( ! $plain ) {

    echo "$tbldef>\n<tr><td class=sml colspan=4>&nbsp;</td></tr>\n<tr>$hdcell";
    echo "<td class=h3 colspan=4 align=right bgcolor=$colors[8]><font size=+1 color=$colors[1]><b>";
    /**** only update status & eta if they are administrator */
    if ( $statusable ) echo "Change Status or ";
    echo "Add Notes</b></font></td></tr>\n";
    if ( $statusable ) {
      echo "<tr>";
      echo "<th class=rows align=right>New Status:</th>";
      echo "<td align=left width=100><select class=sml name=\"new_status\">$status_list</select></td>";
      if ( $sysmgr || $allocated_to || "$request->eta" <> "" ) {
        echo "<th class=rows align=right>&nbsp; ETA:</th>";
        echo "<td align=left>&nbsp;";
        if ( $sysmgr || $allocated_to ) echo "<input type=text name=\"new_eta\" size=30 value=\"";
        echo substr( nice_date( $request->eta ), 7);
        if ( $sysmgr || $allocated_to ) echo "\">";
        echo "</td>";
      }
      echo "</tr>\n";
    }
?>

<tr valign=top>
  <th class=rows align=right>New Note:<div class=sml align="left"><br>
<label><input type="checkbox" name="convert_html" value="1">Process<br>HTML as Text</label></div></TH>
  <td align=left colspan=3><textarea name="new_note" rows=8 cols=60  wrap="SOFT"></textarea></TD>
</tr>
</table>
<?php

  }  // if ! plain
}  // $is_request (way up there with the update details!)

if ( "$style" != "plain" ) {
  echo "$tbldef>\n";
  echo "<tr><td align=left class=smb>";
  echo "<B><input type=\"submit\" class=submit name=\"submit\" VALUE=\"";
  if ( $is_request )
    echo " Apply Changes ";
  else
    echo " Enter Request ";
  echo "\"></b>";
  if ( $roles['wrms']['Admin'] || $roles['wrms']['Support']  ) {
    echo "&nbsp; &nbsp; <label><input type=checkbox name=send_no_mail value=1>Do not send e-mail update</label>";
  }
  echo "</td>\n</tr></table></form>";
}

?>
