<?php
  include( "$base_dir/inc/code-list.php");
  include( "$base_dir/inc/html-format.php");
  $status_list   = get_code_list( "request", "status_code", "$request->last_status" );

  if ( $editable ) {
    /* if it's editable then we'll need severity and request_type lists for drop-downs */
    $severities = get_code_list( "request", "severity_code", "$request->severity_code" );
    $request_types = get_code_list( "request", "request_type", "$request->request_type" );
    $urgencies = get_code_list( "request", "urgency", "$request->urgency" );
    $importances = get_code_list( "request", "importance", "$request->importance" );

    if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] || $roles['wrms']['Manage']  ) {
      include( "$base_dir/inc/user-list.php" );
      if ( $roles['wrms']['Admin'] || $roles['wrms']['Support']  ) {
        $user_list = get_user_list( "", "", $session->user_no );
        $support_list = get_user_list( "S", "", $session->user_no );
      }
      else
        $user_list = get_user_list( "", $session->org_code, $session->user_no );
    }
    if ( $allocated_to || $sysmgr ) {
      $quote_types = get_code_list( "request_quote", "quote_type", "Q" );
      $quote_units = get_code_list( "request_quote", "quote_units", "hours" );
    }

    include("$base_dir/inc/system-list.php");
    $system_codes = get_system_list("ASCE", "$request->system_code");
  }

  $hdcell = "<th width=7%><img src=images/clear.gif width=60 height=2></th>";
  $tbldef = "<table width=100% cellspacing=0 border=0 cellpadding=2";
  echo "$tbldef>\n<tr><td align=left>\n";;
  if ( "$because" != "" )
    echo $because;
  else if ( ! $plain ) {
    ?><P CLASS=helptext>Use this form to enter changes to details for the
    requests of your systems, or to enter details for new requests.</P><?php
  }
?></TD>
</TR>
</TABLE>

<?php echo "$tbldef bgcolor=$colors[7]><TR>$hdcell<th bgcolor=$colors[8]>";
  if ( ! $plain ) {
    echo "<FORM ACTION=\"request.php\" METHOD=POST>";
    echo "<INPUT TYPE=\"hidden\" NAME=\"request_id\" VALUE=\"$request->request_id\">"; 
  }
?>&nbsp;</th>
<TD CLASS=h3 ALIGN=RIGHT<?php echo " bgcolor=$colors[8]"; ?>><FONT SIZE=+1 color=<?php echo $colors[1]; ?>><B>Request details</B></FONT></TD></TR>
<?php
  if ( !isset($request) ) {
    if ( $roles[wrms][Admin] || $roles[wrms][Support] || $roles[wrms][Manage] ) {
      echo "<TR><TH ALIGN=RIGHT>User:</TH>";
      echo "<TD colspan=2 ALIGN=LEFT><SELECT NAME=\"new_user_no\">$user_list</SELECT></td></tr>\n";
    }
    if ( $roles[wrms][Admin] || $roles[wrms][Support] ) {
      echo "<TR><TH align=right>Assign to:</TH>";
      echo "<TD colspan=2 ALIGN=LEFT><SELECT NAME=\"new_assigned\">$support_list</SELECT></TD></TR>";
      echo "</TD></TR>\n";
    }
  }

  echo "<TR><TH ALIGN=RIGHT>";
  if ( isset($request) ) echo "WR #:"; else echo "Request:";
  echo "</TH>\n";
  if ( isset($request) ) echo "<td align=center class=h2>$request->request_id</td>\n";
  echo "<td";
  if ( !isset( $request ) ) echo " colspan=2";
  if ( $editable ) {
    echo "><INPUT TYPE=\"text\" NAME=\"new_brief\" SIZE=55 VALUE=\"";
    if ( isset($request) ) echo htmlspecialchars($request->brief);
    echo "\">"; 
  }
  else
    echo " valign=middle><h2>$request->brief";

  echo "</TD></TR>\n";

  if ( isset($request) ) {
    echo "<TR><TH ALIGN=RIGHT>From:</TH>";
    echo "<TD ALIGN=CENTER>$request->fullname</TD>\n";
    echo "<TD ALIGN=LEFT>&nbsp;<B>Entered:</B> " . nice_date($request->request_on);
    if ( strcmp( $request->eta, "") )
      echo " &nbsp; &nbsp; &nbsp; <B>ETA:</B> " .  substr( nice_date($request->eta), 7);
    echo "</TD></TR>\n";

    echo "<TR><TH ALIGN=RIGHT VALIGN=MIDDLE>Status:</TH>\n";
    echo "<TD ALIGN=CENTER>";
    if ( $editable ) {
      echo "<LABEL><INPUT TYPE=\"checkbox\" NAME=\"new_active\" VALUE=\"TRUE\"";
      if ( strtolower( substr( "$request->active", 0, 1)) == "t" ) echo " CHECKED";
        echo ">&nbsp;Active</LABEL>";
    }
    else if ( strtolower( substr( "$request->active", 0, 1)) == "t" ) echo "Active";
    else echo "Inactive";
    echo "</TD>\n<TD ALIGN=LEFT>&nbsp;$request->last_status - $request->status_desc</TD></TR>\n";
  }

  if ( ($editable && "$system_codes" <> "") || (! $editable && isset($request) ) ) {
    echo "<TR><TH ALIGN=RIGHT VALIGN=MIDDLE>System:</TH>\n";
    if ( isset($request) )
      echo "<TD ALIGN=CENTER>$request->system_code</TD>\n";
    echo "<td align=left";
    if ( !isset( $request ) ) echo " colspan=2";
    if ( $editable )
      echo "><SELECT NAME=\"new_system_code\">$system_codes</SELECT>"; 
    else
      echo ">$request->system_desc";
  }
?>
  </TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Type:</TH>
  <?php if ( isset($request) )
    echo "<TD ALIGN=CENTER>$request->request_type</TD>\n";
  echo "<td align=left";
  if ( !isset( $request ) ) echo " colspan=2";
  if ( $editable )
    echo "><SELECT NAME=\"new_type\">$request_types</SELECT>"; 
  else
    echo ">$request->request_type_desc";
?>
  </TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Urgency:</TH>
  <?php if ( isset($request) )
    echo "<TD ALIGN=CENTER>$request->urgency</TD>\n";
  echo "<td align=left";
  if ( !isset( $request ) ) echo " colspan=2";
  if ( $editable )
    echo "><SELECT NAME=\"new_urgency\">$urgencies</SELECT>"; 
  else
    echo ">$request->urgency_desc";
?>
  </TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Importance:</TH>
  <?php if ( isset($request) )
    echo "<TD ALIGN=CENTER>$request->importance</TD>\n";
  echo "<td align=left";
  if ( !isset( $request ) ) echo " colspan=2";
  if ( $editable )
    echo "><SELECT NAME=\"new_importance\">$importances</SELECT>"; 
  else
    echo ">$request->importance_desc";
?>
  </TD>
</TR>

<tr valign=top>
  <TH ALIGN=RIGHT>&nbsp;<BR>Details:</TH>
  <TD ALIGN=LEFT COLSPAN=2>
<?php
  if ( $editable )
    echo "<TEXTAREA NAME=\"new_detail\" ROWS=8 COLS=60  WRAP=\"SOFT\">$request->detailed</TEXTAREA>"; 
  else
    echo html_format($request->detailed);

  if ( !isset($request) ) {
    echo "<TR><TH ALIGN=RIGHT>Notify:</TH>\n";
    echo "<TD colspan=2 ALIGN=LEFT><LABEL><INPUT TYPE=checkbox NAME=\"in_notify\" VALUE=1 CHECKED>&nbsp;Keep me updated on the status of this request.</LABEL></TD></TR>\n";
  }
?>
</TD></TR></TABLE>


<?php /***** Update Details */
  if ( isset( $request ) ) {
    $query = "SELECT * FROM request_update, system_update WHERE request_update.request_id = $request->request_id AND request_update.update_id = system_update.update_id ORDER BY request_update.update_id DESC";
    $updateq = pg_Exec( $wrms_db, $query);
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

        if(floor($i/2)-($i/2)==0) echo "<tr bgcolor=$colors[6]>";
        else echo "<tr bgcolor=$colors[7]>";
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
?>

<?php /***** Quote Details */
  /* we only show quote details if it is 'quotable' (i.e. requestor, administrator or catalyst owner) */
  if ( $quotable ) {
    $query = "SELECT *, get_lookup_desc('request_quote','quote_type', request_quote.quote_type) AS type_desc ";
    $query .= "FROM request_quote, usr ";
    $query .= "WHERE request_quote.request_id = $request->request_id ";
    $query .= "AND request_quote.quote_by_id = usr.user_no ";
    $query .= " ORDER BY request_quote.quote_id";
    $quoteq = pg_Exec( $wrms_db, $query);
    $rows = pg_NumRows($quoteq);
    if ( $rows > 0 || (($allocated_to || $sysmgr) && !$plain) ) {
?>

<?php echo "$tbldef><TR><TD CLASS=sml COLSPAN=6>&nbsp;</TD></TR><TR>$hdcell"; ?>
<TD CLASS=h3 COLSPAN=5 ALIGN=RIGHT<?php echo " bgcolor=$colors[8]"; ?>><FONT SIZE=+1 color=<?php echo $colors[1]; ?>><B>Quotations</B></FONT></TD></TR>
<?php
      echo "<TR>";
      if ( $rows > 0 ) echo "<TH>Quote</TH><TH class=cols>Done By"; else echo "<th>&nbsp;</th><th class=cols>&nbsp;";
      echo "</TH><TH class=cols>Brief</TH>";
      if ( $rows > 0 ) echo "<TH class=cols>Done On</TH>";
      echo "<TH class=cols>Type</TH><TH class=cols>Amount</TH>";
      if ( $rows <= 0 ) echo "<TH class=cols>Units</TH>";
      echo "</tr>\n";

      for ( $i=0; $i < $rows; $i++ ) {
        $quote = pg_Fetch_Object( $quoteq, $i );
        if(floor($i/2)-($i/2)==0) echo "<tr bgcolor=$colors[6]>";
        else echo "<tr bgcolor=$colors[7]>";
        echo "<TH ALIGN=CENTER VALIGN=TOP ROWSPAN=2><FONT SIZE=+2>$quote->quote_id</FONT></TH>\n";
        echo "<TD ALIGN=CENTER>$quote->fullname</TD>\n";
        echo "<TD>$quote->quote_brief</TD>\n";
        echo "<TD ALIGN=CENTER>" . nice_date($quote->quoted_on) . "</TD>\n";
        echo "<TD ALIGN=CENTER>$quote->quote_type - $quote->type_desc</TD>\n";
        echo "<TD ALIGN=RIGHT>" . number_format($quote->quote_amount, 2) . " $quote->quote_units</TD>\n</tr>";
        if(floor($i/2)-($i/2)==0) echo "<tr bgcolor=$colors[6]>";
        else echo "<tr bgcolor=$colors[7]>";
        echo "<TD COLSPAN=5>";
        echo html_format($quote->quote_details) . "</A></TD></TR>\n";
      }
      if ( ($allocated_to || $sysmgr) && ! $plain ) {
        if(floor($i/2)-($i/2)==0) echo "<tr bgcolor=$colors[6]>";
        else echo "<tr bgcolor=$colors[7]>";
        echo "<th>&nbsp;</th>\n";
        echo "<TD colspan=2><input name=new_quote_brief size=35 type=text></TD>\n";
        echo "<TD><select name=new_quote_type>$quote_types</select></TD>\n";
        echo "<TD ALIGN=RIGHT><input name=new_quote_amount size=10 type=text></td>";
        echo "<TD ALIGN=LEFT><select name=new_quote_unit>$quote_units</select></TD></tr>\n";
        if(floor($i/2)-($i/2)==0) echo "<tr bgcolor=$colors[6]>";
        else echo "<tr bgcolor=$colors[7]>";
        echo "<th>&nbsp;</th>\n";
        echo "<TD COLSPAN=5><textarea name=new_quote_details rows=4 cols=60 wrap=soft></textarea></TD></TR>\n";
      }
      echo "</TABLE>";
    }
  }  // if quotable

  /***** Allocated People */
  /* People who have been allocated to the request - again, only if there are any.  */
  $query = "SELECT usr.fullname, organisation.abbreviation ";
  $query .= "FROM request_allocated, usr, organisation ";
  $query .= "WHERE request_id = '$request->request_id' ";
  $query .= "AND usr.user_no=request_allocated.allocated_to_id ";
  $query .= "AND organisation.org_code = usr.org_code ";
  $query .= "ORDER BY request_allocated.allocated_on ";
  $allocq = pg_Exec( $wrms_db, $query);
  $rows = pg_NumRows($allocq);
  if ( $rows > 0 ) {
    echo "$tbldef>\n<TR><TD CLASS=sml COLSPAN=2>&nbsp;</TD></TR>\n";
    echo "<TR>$hdcell<TD CLASS=h3 ALIGN=RIGHT bgcolor=$colors[8]><FONT SIZE=+1 color=$colors[1]><B>Work Allocated To</B></FONT></TD></TR>\n";
    echo "<TR VALIGN=TOP><th>&nbsp;</th><td>";
    for( $i=0; $i<$rows; $i++ ) {
      $alloc = pg_Fetch_Object( $allocq, $i );
      if ( $i > 0 ) echo ", ";
      echo "$alloc->fullname ($alloc->abbreviation)";
    }
    echo "</TD></TR></TABLE>\n";
  }
?>


<?php /***** Timesheet Details */
  /* we only show timesheet details if they exist */
  $query = "SELECT *, date_part('epoch',request_timesheet.work_duration) AS seconds ";
  $query .= "FROM request_timesheet, usr ";
  $query .= "WHERE request_timesheet.request_id = $request->request_id ";
  $query .= "AND request_timesheet.work_by_id = usr.user_no ";
  $query .= "ORDER BY request_timesheet.work_on ";
  $workq = pg_Exec( $wrms_db, $query);
  $rows = pg_NumRows($workq);
  if ( $rows > 0  || (($allocated_to || $sysmgr) && !$plain) ) {
?>
<?php echo "$tbldef>\n<TR><TD CLASS=sml COLSPAN=6>&nbsp;</TD></TR><TR>$hdcell"; ?>
<TD CLASS=h3 COLSPAN=5 ALIGN=RIGHT<?php echo " bgcolor=$colors[8]"; ?>><FONT SIZE=+1 color=<?php echo $colors[1]; ?>><B>Work Done</B></FONT></TD></TR>
 <TR VALIGN=TOP>
   <th>&nbsp;</th>
   <TH ALIGN=LEFT class=cols>Done By</TH>
   <TH class=cols>Done On</TH>
   <TH class=cols>Quantity</TH>
   <TH class=cols>Rate</TH>
   <TH class=cols>Description</TH>
 </TR>
<?php
    for( $i=0; $i<$rows; $i++ ) {
      $work = pg_Fetch_Object( $workq, $i );

      if(floor($i/2)-($i/2)==0) echo "<tr bgcolor=$colors[6]>";
      else echo "<tr bgcolor=$colors[7]>";
      echo "<th>&nbsp;</th><TD>" . str_replace(" ", "&nbsp;", $work->fullname) . "</TD>\n";
      echo "<TD align=center>" . substr( nice_date($work->work_on), 7) . "</TD>\n";
      echo "<TD ALIGN=RIGHT>$work->work_quantity&nbsp;$work->work_units &nbsp; &nbsp; </TD>\n";
      echo "<TD ALIGN=RIGHT>$work->work_rate &nbsp; &nbsp; </TD>\n";
      echo "<TD>$work->work_description</TD></TR>\n";
    }

    if ( ($allocated_to || $sysmgr) && ! $plain ) {
      if(floor($i/2)-($i/2)==0) echo "<tr bgcolor=$colors[6]";
      else echo "<tr bgcolor=$colors[7]";
      echo " valign=top><th>&nbsp;</th>\n";
      echo "<td><BR>$session->fullname</td>\n";
      echo "<TD><input name=new_work_on size=9 type=text value=today></TD>\n";
      echo "<TD align=center><input name=new_work_quantity size=6 type=text><br>\n";
      echo "<select name=new_work_units>$quote_units</select></TD>\n";
      echo "<TD><input name=new_work_rate size=5 type=text><br>($ per unit)</TD>\n";
      echo "<TD><textarea name=new_work_details rows=3 cols=30 wrap=soft></textarea></TD></TR>\n";
    }
    echo "</TABLE>\n";

  }  // if rows>0
?>


<?php /***** Interested People */
  /* People who are interested - again, only if there are any.  The requestor is not shown */
  $query = "SELECT usr.fullname, organisation.abbreviation ";
  $query .= "FROM request_interested, usr, organisation ";
  $query .= "WHERE request_id = '$request->request_id' ";
  $query .= "AND request_interested.user_no = usr.user_no ";
  $query .= "AND organisation.org_code = usr.org_code ";
  $peopleq = pg_Exec( $wrms_db, $query);
  $rows = pg_NumRows($peopleq);
  if ( $rows > 0 ) {
    echo "$tbldef>\n<TR><TD CLASS=sml COLSPAN=3>&nbsp;</TD></TR>\n";
    echo "<TR>$hdcell<TD CLASS=h3 COLSPAN=2 ALIGN=RIGHT bgcolor=$colors[8]><FONT SIZE=+1 color=$colors[1]><B>Interested Users</B></FONT></TD></TR>\n";
    echo "<TR VALIGN=TOP><th nowrap>&nbsp;</th>\n<td>";
    for( $i=0; $i<$rows; $i++ ) {
      $interested = pg_Fetch_Object( $peopleq, $i );
      if ( $i > 0 ) echo ", ";
      echo "$interested->fullname ($interested->abbreviation)\n";
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

      echo "</TD>\n<TD ALIGN=RIGHT nowrap><font size=-2><A HREF=\"request.php?submit=$action&request_id=$request_id\">";
      echo "<span style=\"background: $colors[6];\">$tell</span></a></font>";
    }
    echo "</TD>\n</TR></TABLE>\n";
  }
?>


<?php /***** Notes */
  $noteq = "SELECT * FROM request_note WHERE request_note.request_id = '$request->request_id' ";
  $noteq .= "ORDER BY note_on ";
  $note_res = pg_Exec( $wrms_db, $noteq );
  $rows = pg_NumRows($note_res);
  if ( $rows > 0 ) {
?>

<?php echo "$tbldef>\n<TR><TD CLASS=sml COLSPAN=4>&nbsp;</TD></TR><TR>$hdcell"; ?>
<TD CLASS=h3 COLSPAN=3 ALIGN=RIGHT<?php echo " bgcolor=$colors[8]"; ?>><FONT SIZE=+1 color=<?php echo $colors[1]; ?>><B>Associated Notes</B></FONT></TD></TR>
<TR VALIGN=TOP>
  <TH NOWRAP>&nbsp;</TH>
  <TH ALIGN=LEFT class=cols>Noted&nbsp;By</TH>
  <TH class=cols>Noted On</TH>
  <TH ALIGN=LEFT class=cols>Details</TH>
</TR>
<?php /*** the actual details of notes */
    for( $i=0; $i<$rows; $i++ ) {
      $request_note = pg_Fetch_Object( $note_res, $i );
      if(floor($i/2)-($i/2)==0) echo "<tr bgcolor=$colors[6]>";
      else echo "<tr bgcolor=$colors[7]>";
      echo "<TH NOWRAP>&nbsp; &nbsp; </TH>";
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
  $stat_res = pg_Exec( $wrms_db, $statq);
  $rows = pg_NumRows($stat_res);
  if ( $rows > 0 ) {
?>
<?php echo "$tbldef>\n<TR><TD CLASS=sml COLSPAN=4>&nbsp;</TD></TR><TR>$hdcell"; ?>
<TD CLASS=h3 COLSPAN=3 ALIGN=RIGHT<?php echo " bgcolor=$colors[8]"; ?>><FONT SIZE=+1 color=<?php echo $colors[1]; ?>><B>Changes in Status</B></FONT></TD></TR>
<TR VALIGN=TOP>
  <TH>&nbsp;</TH>
  <TH class=cols ALIGN=LEFT WIDTH="15%">Changed By</TH>
  <TH class=cols WIDTH="25%" ALIGN=LEFT>Changed On</TH>
  <TH class=cols WIDTH="60%" ALIGN=LEFT>Changed To</TH>
</TR>
<?php /* the actual status stuff */
    for( $i=0; $i<$rows; $i++ ) {
      $request_status = pg_Fetch_Object( $stat_res, $i );
      if(floor($i/2)-($i/2)==0) echo "<tr bgcolor=$colors[6]>";
      else echo "<tr bgcolor=$colors[7]>";
      echo "<TH>&nbsp; &nbsp; &nbsp; </TH>";
      echo "<TD>$request_status->fullname</TD>\n<TD>" . nice_date($request_status->status_on) . "</TD> <TD>$request_status->status_code - $request_status->lookup_desc</TD></TR>\n";
    }
    echo "</TABLE>\n";
  }  // if rows > 0

  if ( ! $plain ) {

    echo "$tbldef>\n<TR><TD CLASS=sml COLSPAN=4>&nbsp;</TD></TR>\n<TR>$hdcell";
    echo "<TD CLASS=h3 COLSPAN=3 ALIGN=RIGHT bgcolor=$colors[8]><FONT SIZE=+1 color=$colors[1]><B>";
    /**** only update status & eta if they are administrator */
    if ( $statusable ) echo "Change Status or ";
    echo "Add Notes</B></FONT></TD></TR>\n";
    if ( $statusable ) {
      echo "<TR>";
      echo "<TH ALIGN=RIGHT>New Status:</TH>";
      echo "<TD ALIGN=LEFT width=100><SELECT NAME=\"new_status\">$status_list</SELECT></TD>";
      if ( $sysmgr || $allocated_to || "$request->eta" <> "" ) {
        echo "<TH ALIGN=RIGHT>&nbsp; ETA:</TH>";
        echo "<TD ALIGN=LEFT>&nbsp;";
        if ( $sysmgr || $allocated_to ) echo "<INPUT TYPE=text NAME=\"new_eta\" SIZE=30 VALUE=\"";
        echo substr( nice_date( $request->eta ), 7);
        if ( $sysmgr || $allocated_to ) echo "\">";
        echo "</TD>";
      }
      echo "</TR>\n";
    }
?>

<TR VALIGN=TOP>
  <TH ALIGN=RIGHT>New Note:</TH>
  <TD ALIGN=LEFT COLSPAN=3><TEXTAREA NAME="new_note" ROWS=8 COLS=60  WRAP="SOFT"></TEXTAREA></TD>
</TR>
</table>
<?php

  }  // if ! plain
}  // isset($request) (way up there with the update details!)

  echo "$tbldef>\n<tr><td align=center class=mand>";
  echo "<B><INPUT TYPE=\"submit\" NAME=\"submit\" VALUE=\"";
  if ( isset($request) )
    echo " Apply Changes ";
  else
    echo " Enter Request ";
  echo "\"></b></td>\n</tr></table></form>";
?>

