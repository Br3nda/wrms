<?php
  include( "code-list.php");
  include( "user-list.php" );
  include( "html-format.php");

  $status_list   = get_code_list( "request", "status_code", "$request->last_status" );
  $use_sla = ( $is_request && $request->current_sla == 't' && $request->sla_response_time > 0 ) || (!$is_request && $session->current_sla == 't') ;
  $use_sla = ( $is_request && $request->current_sla == 't' )
                 || ( !$is_request && ($session->current_sla == 't' || is_member_of('Admin', 'Support' ) ) ) ;
  $attach_types = get_code_list( "request", "attach_type", "" );

  $urgencies = get_code_list( "request", "urgency", "$request->urgency" );
  $importances = get_code_list( "request", "importance", "$request->importance" );
  if ( $editable ) {
    /* if it's editable then we'll need severity and request_type lists for drop-downs */
    $severities = get_code_list( "request", "severity_code", "$request->severity_code" );
    $request_types = get_code_list( "request", "request_type", "$request->request_type" );
    $sla_urgencies = get_code_list( "request", "sla_response", "$request->request_sla_code" );

    if ( $sysmgr || is_member_of('Admin', 'Support' ,'Manage') ) {
      if ( $sysmgr || is_member_of('Admin', 'Support')  ) {
        $user_list = "<option value=\"\">--- not selected ---</option>\n" . get_user_list( "", "", "" );
        $support_list = "<option value=\"\">--- not assigned ---</option>\n";
        $support_list .= get_user_list( "Support", "", $session->user_no );
      }
      else
        $user_list = get_user_list( "", $session->org_code, $session->user_no );
      $quote_types = get_code_list( "request_quote", "quote_type", "Q" );
      $quote_units = get_code_list( "request_quote", "quote_units", "hours" );
    }
    else if ( $allocated_to ) {
      $quote_types = get_code_list( "request_quote", "quote_type", "Q" );
      $quote_units = get_code_list( "request_quote", "quote_units", "hours" );
    }

    include("system-list.php");
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
  if ( isset($because) && "$because" != "" )
    echo $because;
  else if ( ! $plain ) {
    ?><p class=helptext>Use this form to enter changes to details for the
    requests of your systems, or to enter details for new requests.</p><?php
  }
  echo "</td></tr>\n</table>\n";

  echo "$tbldef bgcolor=$colors[bg1]><tr>$hdcell<td class=h3 colspan=3 align=right>";
  if ( ! $plain ) {
    echo "<form action=\"request.php\" method=post enctype=\"multipart/form-data\">";
    echo "<input type=\"hidden\" name=\"request_id\" value=\"$request->request_id\">";
  }
  echo "Request details</td></TR>\n";

  if ( !$is_request  ) {
    if ( is_member_of('Admin', 'Support', 'Manage') ) {
      echo "<tr><th class=rows align=right>On Behalf Of:</th><td colspan=2 valign=middle align=left>";
      echo "<select class=sml name=\"new_user_no\">$user_list</select>\n";
      if ( is_member_of('Admin', 'Support' ) ) $person_role = "client"; else $person_role = "user";
      echo " &nbsp; <label><input class=sml type=checkbox name=\"in_notify\" value=1 checked>&nbsp;update $person_role on the status of this request.</label>\n";
      echo "</td></tr>\n";
    }
    if ( is_member_of('Admin', 'Support' ) ) {
      echo "<TR><TH align=right class=rows>Assign to:</TH>";
      echo "<TD colspan=2 ALIGN=LEFT><SELECT class=sml NAME=\"new_assigned\">$support_list</SELECT></TD></TR>";
    }
  }

  echo "<tr><th class=rows align=right>";
  if ( $is_request ) echo "WR #:"; else echo "Request:";
  echo "</th>\n";
  if ( $is_request ) echo "<td align=center class=h2>$request->request_id</td>\n";
  echo "<td";
  if ( ! $is_request ) echo " colspan=2";
  if ( $editable ) {
    echo "><input class=sml type=\"text\" name=\"new_brief\" size=40 value=\"";
    if ( $is_request ) echo htmlspecialchars($request->brief);
    echo "\">";
  }
  else {
    echo " valign=middle><h2>$request->brief";
  }
  echo "</td></tr>\n";


  if ( $is_request ) {
    // --------------------- FROM -----------------------
    if ( $is_request && strcmp( $request->eta, "") )
      echo " &nbsp; &nbsp; &nbsp; <b class=smb>ETA:</b> " .  substr( nice_date($request->eta), 7);
    if ( is_member_of('Admin', 'Support' ) ) {
      echo "<TR><th class=rows align=right>On Behalf Of:</TH>";
      echo "<td align=center>" . nice_date($request->request_on) . "</td>";
      echo "<TD ALIGN=LEFT COLSPAN=2>\n";
      if ( $editable  ) {
        echo "<select class=sml name=\"new_user_no\">" . get_user_list( "", "", $request->requester_id ) . "</select>\n";
      }
      else {
        echo "$request->fullname\n";
      }
      if ( $is_request && strcmp( $request->eta, "") )
        echo " &nbsp; &nbsp; &nbsp; <b class=smb>ETA:</b> " .  substr( nice_date($request->eta), 7);
      echo "</TD></TR>\n";
    }
    else {
      echo "<TR><th class=rows align=right>For:</TH>";
      echo "<TD ALIGN=CENTER>$request->fullname</TD>\n";
      echo "<TD ALIGN=LEFT><b class=smb>Entered:</b> " . nice_date($request->request_on);
      if ( $is_request && strcmp( $request->eta, "") )
        echo " &nbsp; &nbsp; &nbsp; <b class=smb>ETA:</b> " .  substr( nice_date($request->eta), 7);
      echo "</TD></TR>\n";
    }


    // --------------------- STATUS -----------------------
    if ( is_member_of('Admin', 'Support','Manage' ) ) {
      echo "<TR><th class=rows align=right VALIGN=MIDDLE>Status:</TH>\n";
      echo "<TD ALIGN=CENTER>";
      if ( $editable ) {
        echo "<LABEL><INPUT class=sml TYPE=\"checkbox\" NAME=\"new_active\" VALUE=\"TRUE\"";
        if ( !$is_request || strtolower( substr( "$request->active", 0, 1)) == "t" ) echo " CHECKED";
        echo ">&nbsp;Active</LABEL>";
      }
      else if ( $is_request && strtolower( substr( "$request->active", 0, 1)) == "t" ) echo "Active";
      else echo "Inactive";
      echo "</TD>\n<TD ALIGN=LEFT>&nbsp;$request->last_status - $request->status_desc</TD></TR>\n";
    }
  }

  // --------------------- SYSTEM -----------------------
  if ( ($editable && "$system_codes" <> "") || (! $editable && $is_request ) ) {
    echo "<TR><th class=rows align=right VALIGN=MIDDLE>System:</TH>\n";
    if ( $is_request )
      echo "<TD ALIGN=CENTER>$request->system_code</TD>\n";
    echo "<td align=left";
    if ( ! $is_request ) echo " colspan=2";
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
  if ( ! $is_request  ) echo " colspan=2";
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
  if ( is_member_of('Admin', 'Support', 'Manage')  ) {
    if ( $editable || $prioritisable ) {
      echo "<select class=sml name=\"new_urgency\">$urgencies</select>";
      if ( !$is_request || $use_sla ) {
        echo " <b>OR</b> ";
        echo "<select class=sml name=\"new_sla_code\">$sla_urgencies</select>";
        echo " <b class=smb>(if SLA applies)</b> ";
      }
    }
    else if ( $use_sla && $is_request && ($request->sla_response_time > 0) )
      echo "$request->sla_response_desc";
    else
      echo "$request->urgency_desc";
    echo "</td></tr>\n";
  }
  else {
    if ( $editable || $prioritisable  ) {
      printf( "<SELECT class=sml name=\"new_%s\">$urgencies</SELECT>", ($use_sla ? "sla_code" : "urgency"));
      if ( $session->current_sla ) {
        echo " <b>OR</b> ";
        echo "<select class=sml name=\"new_sla_code\">$sla_urgencies</select>";
        echo " <b class=smb>(if SLA applies)</b> ";
      }
    }
    else if ( $use_sla && $is_request && ($request->sla_response_time > 0) )
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
  if ( $editable || $prioritisable  ) {
    echo "<input class=sml name=\"new_requested_by_date\" value=\"$request->requested_by_date\" size=10>";
  }
  else if ( ! $is_request || "$request->requested_by_date" == "" )
    echo " -- not set -- ";
  else
    echo "$request->requested_by_date";
  echo " &nbsp; &nbsp;\n";

  // ---------------Agreed Due By -------------------
  echo "<b class=smb>Agreed:</b>";
  if ( is_member_of('Admin', 'Support', 'Manage') && ( $editable || $prioritisable ) ) {
    echo "<input class=sml name=\"new_agreed_due_date\" value=\"$request->agreed_due_date\" size=10>";
  }
  else if ( $is_request && "$request->agreed_due_date" == "" )
    echo " -- not set -- ";
  else
    echo "$request->agreed_due_date";
  echo "</td></tr>\n";


  // --------------------- IMPORTANCE -----------------------
  echo "<tr><th class=rows align=right>Importance:</th>\n";
  if ( $is_request ) echo "<TD ALIGN=CENTER>&nbsp;</TD>\n";
  printf( "<td align=left%s>",  ($is_request ?  "" : " colspan=2"));
  if ( $editable || $prioritisable  )
    echo "<SELECT class=sml NAME=\"new_importance\">$importances</SELECT>";
  else
    echo "$request->importance_desc";
  echo "</td></tr>\n";

  // --------------------- DETAILS -----------------------
  echo "<tr><th class=rows align=right valign=top><br>Details:</th>\n";
  echo "<td colspan=2>";
  if ( $editable )
    echo "<textarea class=sml name=\"new_detail\" rows=$bigboxrows cols=$bigboxcols  wrap=\"SOFT\">$request->detailed</textarea>";
  else
    echo html_format($request->detailed);
  echo "</td></tr>\n";

  // ----------------- NOTIFY (for normal users) -----------------
  if ( ! $plain && ( ! $is_request || is_member_of('Admin', 'Support', 'Manage') ) ) {
    echo "<tr><th class=rows align=right>Notify:</th>\n";
    echo "<td colspan=2><label><input type=checkbox name=\"in_notify\" value=1 checked>&nbsp;Keep me updated on the status of this request.</label></td></tr>\n";
  }

  echo "</table>\n";

  //---------------- Attachment Details */
  if ( $is_request ) {
    $query = "SELECT * FROM request_attachment WHERE request_attachment.request_id = $request->request_id ";
    $query .= "ORDER BY request_attachment.attachment_id;";
    $updateq = awm_pgexec( $dbconn, $query, 'request_form');
    $rows = pg_NumRows($updateq);
  }
  else
    $rows = 0;

  if ( ! $plain || $rows > 0 ) {
    echo "$tbldef>\n<TR><TD CLASS=sml COLSPAN=5>&nbsp;</TD></TR><TR>$hdcell";
    echo "<td class=h3 colspan=4 align=right>File Attachments</td></tr>\n";
    echo "<tr>\n";
    echo "<th class=cols>Filename</th>\n";
    echo "<th class=cols>Type</th>\n";
    echo "<th class=cols>Display / X <i>x</i> Y</th>\n";
    echo "<th class=cols>Description</th>\n";
    echo "</tr>\n";
  }
  if ( $rows > 0 ) {

    for( $i=0; $i<$rows; $i++ ) {
      $attachment = pg_Fetch_Object( $updateq, $i );

      printf("<tr class=row%1d>", ($i % 2) );
      echo "<td><a href=/attachment.php/" . urlencode($attachment->att_filename) . "?id=$attachment->attachment_id target=_new>$attachment->att_filename</a></td>\n";
      echo "<td>$attachment->att_type</td>\n";
      echo "<td>" . nice_date($attachment->attached_on) . "</TD>\n";
      echo "<td>$attachment->att_brief</td>";
      echo "</tr>\n";
      if ( $attachment->att_inline == "t" ) {
        printf("<tr class=row%1d>", ($i % 2) );
        echo "<td colspan=4>";
        echo "<iframe width=$attachment->att_width height=$attachment->att_height src=/attachment.php/$attachment->att_filename?id=$attachment->attachment_id>\n";
        echo "<a href=/attachment.php/" . urlencode($attachment->att_filename) . "?id=$attachment->attachment_id target=_new>View Attachment</a>\n";
        echo "</iframe>\n";
        echo "</td></tr>";
      }
    }
  }
  if ( ! $plain ) {
    printf("<tr class=row%1d>", ($i % 2) );
    echo "<td><input class=sml name=new_attachment_file size=20 type=file></td>\n";
    echo "<td><select class=sml name=new_attachment_type>$attach_types</select></td>\n";
    if ( is_member_of('Admin', 'Support' ) ) {
      echo "<td nowrap><label>Show inline<input name=new_attach_inline type=checkbox value=1></label><input name=new_attach_x size=3 type=text>x<input name=new_attach_y size=3 type=text></td>";
      echo "<td>";
    }
    else {
      echo "<td colspan=2>";
    }
    echo "<input class=sml size=30 name=new_attach_brief type=text></td></tr>\n";
  }
  if ( ! $plain || $rows > 0 )
    echo "</table>";


  /***** Quote Details */
  /* we only show quote details if it is 'quotable' (i.e. requestor, administrator or catalyst owner) */
  if ( $quotable ) {
    if ( $is_request ) {
      $query = "SELECT *, au.fullname AS approved_by_fullname , get_lookup_desc('request_quote','quote_type', request_quote.quote_type) AS type_desc ";
      $query .= "FROM request_quote ";
      $query .= "LEFT JOIN usr au ON (au.user_no = request_quote.approved_by_id) ";
      $query .= ", usr ";
      $query .= "WHERE request_quote.request_id = $request->request_id ";
      $query .= "AND request_quote.quote_by_id = usr.user_no ";
      $query .= " ORDER BY request_quote.quote_id";
      $quoteq = awm_pgexec( $dbconn, $query);
      $rows = pg_NumRows($quoteq);
    }
    else
      $rows = 0;
    if ( $rows > 0 || (($allocated_to || $sysmgr || is_member_of('Support') ) && !$plain) ) {
      echo "$tbldef><tr><td class=sml colspan=10>&nbsp;</td></tr><tr>$hdcell";
      echo "<td class=h3 colspan=10 align=right>Quotations</td></tr>\n";
      echo "<TR>";
      echo "<th class=cols>Quote</th><th class=cols>Quoted By</th><th class=cols>Brief</th>";
      echo "<th class=cols>Quoted On</th>";
      echo "<th class=cols>Type</th><th class=cols>Quantity</th><th class=cols>Units</th><th class=cols>Approved By</th>"; 
      echo "<th class=cols>Approved</th><th class=cols>Inv No</th>";
      echo "</tr>\n";

      for ( $i=0; $i < $rows; $i++ ) {
        $quote = pg_Fetch_Object( $quoteq, $i );
        printf("<tr class=row%1d>", ($i % 2) );
        echo "<TD ALIGN=CENTER>$quote->quote_id</TD>\n";
        echo "<TD>$quote->fullname</TD>\n"; 
        echo "<TD>$quote->quote_brief</TD>\n";
        echo "<TD ALIGN=CENTER>" . substr(nice_date($quote->quoted_on),7) . "</TD>";
        echo "<TD>$quote->type_desc</TD>";
        echo "<TD ALIGN=RIGHT>" . number_format($quote->quote_amount, 2) . "</td><td>$quote->quote_units</TD>\n";
        if ($quote->approved_by_id == '')
	      echo "<td>$session->fullname</td>" .
		   "<td align=center><input type=checkbox name=quote_approved[$quote->quote_id]></td>";
	else echo "<TD>$quote->approved_by_fullname</TD>" .
		  "<td>" . substr(nice_date($quote->approved_on),7) . "</td>";
        if ( $quote->invoice_no == '' &&  (is_member_of('Admin','Support'))) 
		echo "<TD ALIGN=CENTER><input size=6 type=text name=quote_invoice_no[$quote->quote_id]></TD>";
        else echo "<TD ALIGN=CENTER>$quote->invoice_no</TD>";
        echo "</tr>\n";

        printf("<tr class=row%1d>", ($i % 2) );
        echo "<TD COLSPAN=10>";
        echo html_format($quote->quote_details) . "</A></TD></TR>\n";
      }
    }

    if ( ($allocated_to || $sysmgr  || is_member_of('Support') ) && ! $plain ) {
      printf("<tr class=row%1d>", ($i % 2) );
      echo "<TD></td><td>$session->fullname</td>\n";
      echo "<TD><input name=new_quote_brief size=35 type=text></TD><td></td>\n";
      echo "<TD><select class=sml name=new_quote_type>$quote_types</select></TD>\n";
      echo "<TD ALIGN=RIGHT><input name=new_quote_amount size=10 type=text></td>";
      echo "<TD ALIGN=LEFT><select class=sml name=new_quote_unit>$quote_units</select></TD>" .
           "<td colspan=3></td></tr>\n";
      printf("<tr class=row%1d>", ($i % 2) );
      echo "<TD COLSPAN=10><textarea class=sml name=new_quote_details rows=4 cols=60 wrap=soft></textarea></TD></TR>\n";
    }
    echo "</TABLE>";
  }  // if quotable

  if ( !$plain && is_member_of('Admin', 'Support', 'Manage') ) {
    $user_list = "<option value=\"\">--- no change ---</option>\n";
    if ( is_member_of('Admin', 'Support') ) {
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
  $allocq = awm_pgexec( $dbconn, $query);
  $rows = pg_NumRows($allocq);
  if ( $is_request && ( $rows > 0 || (! $plain && is_member_of('Admin', 'Support', 'Manage') ) ) ) {
    echo "$tbldef>\n<TR><TD CLASS=sml COLSPAN=3>&nbsp;</TD></TR>\n";
    echo "<TR>$hdcell<TD CLASS=h3 COLSPAN=2 ALIGN=RIGHT>Work Allocated To</TD></TR>\n";
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

    if ( $plain || ! is_member_of('Admin', 'Support') ) {
      echo "</TD>\n<TD>&nbsp;";  // Or we could correct the cellspan above for this case...
    }
    else {
      echo "</TD>\n<td align=right nowrap>Add:&nbsp;<select class=sml name=\"new_allocation\">$support_list</SELECT>\n";
    }
    echo "</TD></TR></TABLE>\n";
  }

  /***** Timesheet Details */
  /* we only show timesheet details if they exist */
  if ( $is_request && is_member_of('Admin', 'Support') ) {
    $query = "SELECT *, date_part('epoch',request_timesheet.work_duration) AS seconds ";
    $query .= "FROM request_timesheet, usr ";
    $query .= "WHERE request_timesheet.request_id = $request->request_id ";
    $query .= "AND request_timesheet.work_by_id = usr.user_no ";
    $query .= "ORDER BY request_timesheet.work_on ";
    $workq = awm_pgexec( $dbconn, $query);
    $rows = pg_NumRows($workq);
  }
  else
    $rows = 0;

  if ( $rows > 0  || (($allocated_to || $sysmgr || is_member_of('Support')) && !$plain) ) {

    echo "$tbldef>\n<tr><td class=sml colspan=7>&nbsp;</td></tr><tr>$hdcell";
    echo "<td class=h3 colspan=7 align=right>Work Done</TD></TR>\n";
    echo "<tr valign=top>\n";
    echo "<th class=smb>Done By</th>\n";
    echo "<th class=smb>Done On</th>\n";
    echo "<th class=smb>Quantity</th>\n";
    echo "<th class=smb>Rate</th>\n";
    echo "<th class=smb>Cost</th>\n";
    echo "<th class=smb colspan=2>Description</th>\n";
    echo "</tr>\n";

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
      if ( ! $plain && is_member_of('Admin', 'Support', 'Manage') ) {
        echo "<td align=right nowrap><a class=submit href=\"request.php?submit=deltime";
        echo "&request_id=$request_id&timesheet_id=$work->timesheet_id\">";
        echo "DEL</a></td>";
      }
      echo "</tr>";
    }
    printf("<tr class=row%1d>", ($i % 2) );
    echo "<td colspan=3 align=left><b>Total</b></td>
<td colspan=2 align=right nowrap>\$$total_cost&nbsp;</td>
<td colspan=2>&nbsp;</td>
</tr>";
    $i++;

    if ( ($allocated_to || $sysmgr || is_member_of('Support')) && ! $plain ) {
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
      echo "<td colspan=3><textarea class=sml name=new_work_details rows=3 cols=30 wrap=soft>$old_work_details</textarea></TD></TR>\n";
    }
    echo "</TABLE>\n";

  }  // if timesheet rows>0

  if ( $is_request ) {
    /***** Interested People */
    /* People who are interested - again, only if there are any.  The requestor is not shown */
    $query = "SELECT usr.fullname, organisation.abbreviation, usr.user_no ";
    $query .= "FROM request_interested, usr, organisation ";
    $query .= "WHERE request_id = '$request->request_id' ";
    $query .= "AND request_interested.user_no = usr.user_no ";
    $query .= "AND organisation.org_code = usr.org_code ";
    $peopleq = awm_pgexec( $dbconn, $query);
    $rows = pg_NumRows($peopleq);
    echo "$tbldef>\n<TR><TD CLASS=sml COLSPAN=3>&nbsp;</TD></TR>\n";
    printf( "<TR>$hdcell<TD CLASS=h3 COLSPAN=%d align=right>Interested Users</TD></TR>\n", ( $plain ? 3 : 2) );
    echo "<TR VALIGN=TOP>\n<td>";
    if ( $rows > 0 ) {
      for( $i=0; $i<$rows; $i++ ) {
        $interested = pg_Fetch_Object( $peopleq, $i );
        if ( $i > 0 ) echo ", ";
        if ( ! $plain && ($allocated_to || $sysmgr || is_member_of('Admin', 'Support', 'Manage')) ) {
          echo "<a href=\"request.php?submit=deregister&user_no=$interested->user_no&request_id=$request_id\">\n";
        }
        echo "$interested->fullname ($interested->abbreviation)\n";
        if ( ($allocated_to || $sysmgr) && ! $plain )
          echo "</a>\n";
      }
    }
  
    if ( !$plain ) {
      $notify_to = notify_emails( $dbconn, $request_id );
      if ( strstr( $notify_to, $session->email ) ) {
        $tell = "Stop informing me on this request";
        $action = "deregister";
      }
      else {
        $tell = "Inform me about updates to this request!";
        $action = "register";
      }
  
      echo "</TD>\n<TD ALIGN=RIGHT nowrap>";
      if ( is_member_of('Admin', 'Support', 'Manage') ) {
        echo "Add:&nbsp;<select class=sml name=\"new_interest\">$user_list</SELECT>\n";
      }
      else {
        echo "<a class=r href=\"request.php?submit=$action&request_id=$request_id\">$tell</a>";
      }
      echo "</TD>\n</TR></TABLE>\n";
    }
  }


  /***** Linked Work Requests */
  $link_wr_query  = "SELECT ";
  $link_wr_query .= "rr.request_id    AS parent_request_id, ";
  $link_wr_query .= "r.brief          AS parent_brief, ";
  $link_wr_query .= "lcs.lookup_desc  AS parent_status, ";
  $link_wr_query .= "lc.lookup_desc   AS parent_link_desc, ";
  $link_wr_query .= "null             AS child_link_desc, ";
  $link_wr_query .= "null             AS child_request_id, ";
  $link_wr_query .= "null             AS child_brief, ";
  $link_wr_query .= "null             AS child_status ";
  $link_wr_query .= "FROM request_request rr ";
  $link_wr_query .= "JOIN request r USING (request_id) ";
  $link_wr_query .= "JOIN lookup_code lc ON lc.source_table = 'request_request' AND lc.source_field = 'link_type' AND lc.lookup_code = rr.link_type ";
  $link_wr_query .= "JOIN lookup_code lcs ON lcs.source_table = 'request' AND lcs.source_field = 'status_code' AND lcs.lookup_code = r.last_status ";
  $link_wr_query .= "WHERE rr.to_request_id = '$request->request_id' ";

  $link_wr_query .= "UNION SELECT ";
  $link_wr_query .= "null             AS parent_request_id, ";
  $link_wr_query .= "null             AS parent_brief, ";
  $link_wr_query .= "null             AS parent_status, ";
  $link_wr_query .= "null             AS parent_link_desc, ";
  $link_wr_query .= "lc.lookup_desc   AS child_link_desc, ";
  $link_wr_query .= "rr.to_request_id AS child_request_id, ";
  $link_wr_query .= "r.brief          AS child_brief, ";
  $link_wr_query .= "lcs.lookup_desc  AS child_status ";
  $link_wr_query .= "FROM request_request rr ";
  $link_wr_query .= "JOIN request r ON r.request_id = rr.to_request_id ";
  $link_wr_query .= "JOIN lookup_code lc ON lc.source_table = 'request_request' AND lc.source_field = 'link_type' AND lc.lookup_code = rr.link_type ";
  $link_wr_query .= "JOIN lookup_code lcs ON lcs.source_table = 'request' AND lcs.source_field = 'status_code' AND lcs.lookup_code = r.last_status ";
  $link_wr_query .= "WHERE rr.request_id = '$request->request_id' ";

  $link_wr_result = awm_pgexec( $dbconn, $link_wr_query );

  $rows = pg_NumRows($link_wr_result);
  if ( $rows > 0 ) {
    echo "$tbldef>\n<TR><TD CLASS=sml COLSPAN=7>&nbsp;</TD></TR>\n";
    echo "<TR>$hdcell<TD CLASS=h3 COLSPAN=7 align=right>Linked Work Requests</TD></TR>\n";
    echo "<TR><TH ALIGN=LEFT class=cols>Parent WR</TH><TH ALIGN=LEFT class=cols>Brief</TH>";
    echo "<TH ALIGN=LEFT class=cols>Status</TH>";
    echo "<TH ALIGN=LEFT class=cols>Link Description</TH><TH ALIGN=LEFT class=cols>This WR</TH>";
    echo "<TH ALIGN=LEFT class=cols>Link Description</TH><TH ALIGN=LEFT class=cols>Child WR</TH>";
    echo "<TH ALIGN=LEFT class=cols>Brief</TH>";
    echo "<TH ALIGN=LEFT class=cols>Status</TH></TR>\n";

    /*** details of linked WRs */
    for( $i=0; $i<$rows; $i++ ) {
      $link_wr = pg_Fetch_Object( $link_wr_result, $i );
      printf("<tr class=row%1d>", ($i % 2) );
      echo "<TD><A href=request.php?request_id=$link_wr->parent_request_id>$link_wr->parent_request_id</A></TD>";
      echo "<TD>$link_wr->parent_brief</TD>";
      echo "<TD>$link_wr->parent_status</TD>";
      echo "<TD>$link_wr->parent_link_desc</TD>";
      echo "<TD>$request->request_id</TD>";
      echo "<TD>$link_wr->child_link_desc</TD>";
      echo "<TD><A href=request.php?request_id=$link_wr->child_request_id>$link_wr->child_request_id</A></TD>";
      echo "<TD>$link_wr->child_brief</TD>";
      echo "<TD>$link_wr->child_status</TD></TR>\n";
    }
    echo "</TABLE>\n";
  }  // if rows > 0


  /***** Notes */
  $noteq = "SELECT * FROM request_note WHERE request_note.request_id = '$request->request_id' ";
  $noteq .= "ORDER BY note_on ";
  $note_res = awm_pgexec( $dbconn, $noteq );
  $rows = pg_NumRows($note_res);
  if ( $rows > 0 ) {
    echo "$tbldef>\n<TR><TD CLASS=sml COLSPAN=4>&nbsp;</TD></TR><TR>$hdcell";

    echo "<TD CLASS=h3 COLSPAN=3 align=right>Associated Notes</TD></TR>\n";
    echo "<TR VALIGN=TOP>\n";
    echo "<TH ALIGN=LEFT class=cols>Noted&nbsp;By</TH>\n";
    echo "<TH class=cols>Noted On</TH>\n";
    echo "<TH ALIGN=LEFT class=cols>Details</TH>\n";
    echo "</TR>\n";

    /*** the actual details of notes */
    for( $i=0; $i<$rows; $i++ ) {
      $request_note = pg_Fetch_Object( $note_res, $i );
      printf("<tr class=row%1d>", ($i % 2) );
      echo "<TD>$request_note->note_by</TD><TD>";
      echo nice_date($request_note->note_on);
      echo "</TD>\n<TD>" . html_format($request_note->note_detail) . "</TD></TR>\n";
    }
    echo "</TABLE>\n";
  }  // if rows > 0



  /***** Status Changes */
  $statq = "SELECT * FROM request_status, lookup_code AS status, usr ";
  $statq .= " WHERE request_status.request_id = '$request->request_id' AND request_status.status_code = status.lookup_code ";
  $statq .= " AND status.source_table='request' AND status.source_field='status_code' ";
  $statq .= " AND usr.user_no=request_status.status_by_id ";
  $statq .= " ORDER BY status_on ";
  $stat_res = awm_pgexec( $dbconn, $statq);
  $rows = pg_NumRows($stat_res);
  if ( $rows > 0 ) {
    echo "$tbldef>\n<TR><TD CLASS=sml COLSPAN=4>&nbsp;</TD></TR><TR>$hdcell";

    echo "<td class=h3 colspan=3 align=right>Changes in Status</TD></TR>\n";
    echo "<tr valign=top>\n";
    echo "<th class=smb width=\"15%\" align=left>Changed By</th>\n";
    echo "<th class=smb width=\"25%\" align=left>Changed On</th>\n";
    echo "<th class=smb width=\"60%\" align=left>Changed To</th>\n";
    echo "</tr>\n";

    /* the actual status stuff */
    for( $i=0; $i<$rows; $i++ ) {
      $request_status = pg_Fetch_Object( $stat_res, $i );
      printf("<tr class=row%1d>", ($i % 2) );
      echo "<TD>$request_status->fullname</TD>\n<TD>" . nice_date($request_status->status_on) . "</TD> <TD>$request_status->status_code - $request_status->lookup_desc</TD></TR>\n";
    }
    echo "</TABLE>\n";
  }  // if rows > 0

  if ( ! $plain ) {

    echo "$tbldef>\n<tr><td class=sml colspan=4>&nbsp;</td></tr>\n<tr>$hdcell";
    echo "<td class=h3 colspan=4 align=right>";
    /**** only update status & eta if they are administrator */
    if ( $statusable ) echo "Change Status or ";
    echo "Add Notes</td></tr>\n";
    if ( $statusable ) {
      echo "<tr>";
      echo "<th class=rows align=right>New Status:</th>";
      echo "<td align=left width=100><select class=sml name=\"new_status\">$status_list</select></td>";
      if ( $sysmgr || $allocated_to || is_member_of('Support') || "$request->eta" <> "" ) {
        echo "<th class=rows align=right>&nbsp; ETA:</th>";
        echo "<td align=left>&nbsp;";
        if ( $sysmgr || $allocated_to || is_member_of('Support') ) echo "<input type=text name=\"new_eta\" size=30 value=\"";
        echo substr( nice_date( $request->eta ), 7);
        if ( $sysmgr || $allocated_to || is_member_of('Support') ) echo "\">";
        echo "</td>";
      }
      else {
        echo "<th class=rows align=right colspan=2>&nbsp;</th>";
      }
      echo "</tr>\n";
    }
    echo "<tr valign=top>
  <th class=rows align=right>New Note:<div class=sml align=\"left\"><br>
<label><input type=\"checkbox\" name=\"convert_html\" value=1>Process<br>HTML as Text</label></div></TH>
  <td align=left colspan=3><textarea class=sml name=\"new_note\" rows=$bigboxrows cols=$bigboxcols  wrap=\"SOFT\"></textarea></TD>
</tr>
</table>\n";

  }  // if ! plain


if ( "$style" != "plain" ) {
  echo "$tbldef>\n";
  echo "<tr><td align=left>";
  echo "<b><input type=\"submit\" class=submit name=\"submit\" VALUE=\"";
  if ( $is_request )
    echo " Apply Changes ";
  else
    echo " Enter Request ";
  echo "\"></b>";
  if ( is_member_of('Admin', 'Support') ) {
    echo "&nbsp; &nbsp; <label><input type=checkbox name=send_no_mail value=1> Do not send e-mail update </label>";
  }
  echo "</td>\n</tr></table></form>";
}

?>
