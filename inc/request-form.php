<?php
  include( "$base_dir/inc/code-list.php");
  include( "$base_dir/inc/notify-emails.php");
  include( "$base_dir/inc/nice-date.php");
  include( "$base_dir/inc/html-format.php");
  $status_list   = get_code_list( "request", "status_code", "$request->last_status" );

  include("$base_dir/inc/system-list.php");
  $system_codes = get_system_list("ACERS", "$request->system_code");

  /* get the user's roles relative to the current request */
  include( "$base_dir/inc/get-request-roles.php");

  /* Current request is editable if the user requested it or user is sysmgr, cltmgr or allocated the job */
  $plain = !strcmp( "$args->style", "plain");
  $notable = isset($request) && ($author || $sysmgr || $cltmgr || $allocated_to);
  $quotable = $notable;
  $editable = ($sysmgr || $allocated_to);
  if ( $editable ) $editable = ! $plain;

  if ( $editable ) {
    /* if it's editable then we'll need severity and request_type lists for drop-downs */
    $severities = get_code_list( "request", "severity_code", "$request->severity_code" );
    $request_types = get_code_list( "request", "request_type", "$request->request_type" );
  }

  $notify_to = notify_emails( $wrms_db, $request_id );
  if ( strstr( $notify_to, $session->email ) )
    $tell = "Click here to stop receiving updates to this request";
  else
    $tell = "Click here to receive updates to this request!";

  $tell .= "<br>$notify_to";

  $hdcell = "<th width=7%><img src=images/clear.gif width=50 height=2></th>";
  $tbldef = "<table width=100% cellspacing=0 border=0 cellpadding=3>\n";
  echo $tbldef;
?>

<TR><TD ALIGN=LEFT><P CLASS=helptext>Use this form to enter changes to details for the
requests of your systems, or to enter details for new requests.</P><?php
  if ( "$request->request_id" != "" )
    echo "</TD><TD ALIGN=RIGHT nowrap><font size=-1><A HREF=\"request.php?action=register&request_id=$request_id\">$tell</a></font>";
?></TD>
</TR>
</TABLE>

<?php echo "$tbldef<TR>$hdcell"; ?><TD CLASS=h3 COLSPAN=3 ALIGN=RIGHT><FONT SIZE=+1><B>Request details</B></FONT></TD></TR>
<TR>
<?php
  echo "<TH ALIGN=RIGHT>";
  if ( isset($request) ) echo "WR #:"; else echo "Request:";
  echo "</TH>\n";
  if ( isset($request) ) echo "<TD ALIGN=CENTER><H2>$request->request_id</TH>\n";
  echo "<TD ALIGN=LEFT>\n";
  if ( $editable ) {
    echo "<FORM ACTION=\"modify-request-done.php3\" METHOD=POST>";
    echo "<INPUT TYPE=\"hidden\" NAME=\"request_id\" VALUE=\"$request->request_id\">"; 
    echo "<INPUT TYPE=\"text\" NAME=\"new_brief\" SIZE=55 VALUE=\"$request->brief\">"; 
  }
  else
    echo "$request->brief";
?>
  </TD>
</TR>

<?php
  if ( isset($request) ) {
    echo "<TR><TH ALIGN=RIGHT>From:</TH>";
    echo "<TD ALIGN=CENTER>$request->fullname</TD>\n";
    echo "<TD ALIGN=LEFT>&nbsp;<B>Entered:</B> " . nice_date($request->request_on);
    if ( strcmp( $request->eta, "") )
      echo " &nbsp; &nbsp; &nbsp; <B>ETA:</B> " .  substr( nice_date($request->eta), 7);
    echo "</TD></TR>\n";
  }
?>

<TR>
  <TH ALIGN=RIGHT VALIGN=MIDDLE>Status:</TH>
<?php
  if ( isset($request) ) echo "<TD ALIGN=CENTER>"; else echo "<TD>";
  if ( $editable ) {
    echo "<LABEL><INPUT TYPE=\"checkbox\" NAME=\"new_active\" VALUE=\"TRUE\"";
    if ( $request->active == "t" ) echo " CHECKED";
    echo ">&nbsp;Active</LABEL>";
  }
  else if ( $request->active ) echo "Active";
  else echo "Inactive";
  if ( isset($request) ) {
    echo "</TD><TD ALIGN=LEFT>";
    echo "&nbsp;$request->last_status - $request->status_desc";
  }
  echo "</TD>";
?>
</TR>

<TR>
  <TH ALIGN=RIGHT>System:</TH>
  <?php if ( isset($request) )
    echo "<TD ALIGN=CENTER>$request->system_code</TD>\n";
  echo "<TD ALIGN=LEFT>";
  if ( $editable )
    echo "&nbsp;<SELECT NAME=\"new_system_code\">$system_codes</SELECT>"; 
  else
    echo "$request->system_desc";
?>
  </TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Type:</TH>
  <?php if ( isset($request) )
    echo "<TD ALIGN=CENTER>$request->request_type</TD>\n";
  echo "<TD ALIGN=LEFT>";
  if ( $editable )
    echo "&nbsp;<SELECT NAME=\"new_type\">$request_types</SELECT>"; 
  else
    echo "$request->request_type_desc";
?>
  </TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Urgency:</TH>
  <?php if ( isset($request) )
    echo "<TD ALIGN=CENTER>$request->severity_code</TD>\n";
  echo "<TD ALIGN=LEFT>";
  if ( $editable )
    echo "&nbsp;<SELECT NAME=\"new_severity\">$severities</SELECT>"; 
  else
    echo "$request->severity_desc";
?>
  </TD>
</TR>

<TR VALIGN=TOP>
  <TH ALIGN=RIGHT>&nbsp;<BR>Details:</TH>
  <TD ALIGN=LEFT COLSPAN=2>
<?php
  if ( $editable )
    echo "<TEXTAREA NAME=\"new_detail\" ROWS=8 COLS=60  WRAP=\"SOFT\">$request->detailed</TEXTAREA>"; 
  else
    echo html_format($request->detailed);
?>
</TD>
</TR>

<?php
  if ( $editable ) {
    /* of course, if it's editable we need to have an update button don't we, so here it is!  */
    echo "<TR><TD COLSPAN=3 ALIGN=CENTER CLASS=mand>";
    echo "<B><INPUT TYPE=\"submit\" NAME=\"submit\" VALUE=\"";
    if ( isset($request) )
      echo " Apply Changes ";
    else
      echo " Enter Request ";
    echo "\"></B></TD></TR></FORM>";
  }
?>

</TABLE>


<?php /***** Update Details */
  if ( isset( $request ) ) {
    $query = "SELECT * FROM request_update, system_update WHERE request_update.request_id = $request->request_id AND request_update.update_id = system_update.update_id ORDER BY request_update.update_id DESC";
    $updateq = pg_Exec( $wrms_db, $query);
    $rows = pg_NumRows($updateq);
    if ( $rows > 0 ) {
?>
<?php echo "$tbldef<TR><TD CLASS=sml COLSPAN=5>&nbsp;</TD></TR><TR>$hdcell"; ?>
<TD CLASS=h3 COLSPAN=4 ALIGN=RIGHT><FONT SIZE=+1><B>Program Update Details</B></FONT></TD></TR>
 <TR><TH>ID</TH><TH>Done By</TH><TH>Done On</TH><TH>Description</TH><TH>&nbsp;</TH></TR>
<?php
      for( $i=0; $i<$rows; $i++ ) {
        $update = pg_Fetch_Object( $updateq, $i );

        if(floor($i/2)-($i/2)==0) echo "<tr bgcolor=$colors[6]>";
        else echo "<tr bgcolor=$colors[7]>";
        echo "<TH VALIGN=TOP ALIGN=CENTER ROWSPAN=2><FONT SIZE=+2>$update->update_id</FONT></TH>\n";
        echo "<TD>$update->update_by</TD>\n";
        echo "<TD>" . nice_date($update->update_on) . "</TD>\n";
        echo "<TD><A HREF=\"view-update.php3?update_id=$update->update_id&username=$usr->username\">";
        echo "$update->update_brief</A></TD>";
        echo "<TD><A HREF=\"$update->file_url\">Download</A></TD>\n";
        echo "</TR>\n";
        echo "<TR><TD COLSPAN=4>";
        echo html_format( $update->update_description) . "</TD></TR><TR><TD></TD></TR>";
      }
      echo "</TABLE>";
    }
?>

<?php /***** Quote Details */
  /* we only show quote details if it is 'quotable' (i.e. requestor, administrator or catalyst owner) */
  if ($quotable ) {
    $query = "SELECT *, awm_get_lookup_desc('request_quote','quote_type', request_quote.quote_type) AS type_desc ";
    $query .= "FROM request_quote, usr ";
    $query .= "WHERE request_quote.request_id = $request->request_id ";
    $query .= "AND request_quote.quote_by_id = usr.user_no ";
    $quoteq = pg_Exec( $wrms_db, $query);
    $rows = pg_NumRows($quoteq);
    if ( $rows > 0 ) {
?>

<?php echo "$tbldef<TR><TD CLASS=sml COLSPAN=6>&nbsp;</TD></TR><TR>$hdcell"; ?>
<TD CLASS=h3 COLSPAN=5 ALIGN=RIGHT><FONT SIZE=+1><B>Quotations</B></FONT></TD></TR>
 <TR><TH>Quote</TH><TH>Done By</TH><TH>Brief</TH><TH>Done On</TH><TH>Type</TH><TH>Amount</TH></TR>
<?php

      for ( $i=0; $i < $rows; $i++ ) {
        $quote = pg_Fetch_Object( $quoteq, $i );
        if(floor($i/2)-($i/2)==0) echo "<tr bgcolor=$colors[6]>";
        else echo "<tr bgcolor=$colors[7]>";
        echo "<TH ALIGN=CENTER VALIGN=TOP ROWSPAN=2><FONT SIZE=+2>$quote->quote_id</FONT></TH>\n";
        echo "<TD ALIGN=CENTER>$quote->fullname</TD>\n";
        echo "<TD><A HREF=\"view-quote.php3?quote_id=$quote->quote_id\">$quote->quote_brief</A></TD>\n";
        echo "<TD ALIGN=CENTER>" . nice_date($quote->quoted_on) . "</TD>\n";
        echo "<TD ALIGN=CENTER>$quote->quote_type - $quote->type_desc</TD>\n";
        echo "<TD ALIGN=RIGHT>" . number_format($quote->quote_amount, 2) . " $quote->quote_units</TD>\n";
        echo "</TR><TR><TD COLSPAN=6>";
        echo html_format($quote->quote_details) . "</A></TD></TR>\n";
        if ( ($i + 1) < $rows )
          echo "<TR><TD COLSPAN=7><FONT SIZE=-4>&nbsp;</FONT></TD></TR>";
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
  $allocq = pg_Exec( $wrms_db, $query);
  $rows = pg_NumRows($allocq);
  if ( $rows > 0 ) {
    echo "$tbldef<TR><TD CLASS=sml COLSPAN=2>&nbsp;</TD></TR>\n";
    echo "<TR>$hdcell<TD CLASS=h3 ALIGN=RIGHT><FONT SIZE=+1><B>Work Allocated To</B></FONT></TD></TR>\n";
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
  $query .= "FROM request_timesheet, awm_usr, awm_perorg ";
  $query .= "WHERE request_timesheet.request_id = $request->request_id ";
  $query .= "AND request_timesheet.work_by_id = awm_usr.perorg_id ";
  $query .= "AND awm_perorg.perorg_id = awm_usr.perorg_id ";
  $workq = pg_Exec( $wrms_db, $query);
  $rows = pg_NumRows($workq);
  if ( $rows > 0 ) {
?>
<?php echo "$tbldef<TR><TD CLASS=sml COLSPAN=5>&nbsp;</TD></TR><TR>$hdcell"; ?>
<TD CLASS=h3 COLSPAN=4 ALIGN=RIGHT><FONT SIZE=+1><B>Work Done</B></FONT></TD></TR>
 <TR VALIGN=TOP>
   <th>&nbsp;</th>
   <TH ALIGN=LEFT>Done By</TH>
   <TH>Done On</TH>
   <TH>Hours</TH>
   <TH>Description</TH>
 </TR>
<?php
      for( $i=0; $i<$rows; $i++ ) {
        $work = pg_Fetch_Object( $workq, $i );

        if(floor($i/2)-($i/2)==0) echo "<tr bgcolor=$colors[6]>";
        else echo "<tr bgcolor=$colors[7]>";
        echo "<th>&nbsp;</th><TD>$work->perorg_name</TD>\n";
        echo "<TD>" . nice_date($work->work_on) . "</TD>\n";
        echo "<TD ALIGN=RIGHT>" . sprintf( "%.2f", round(($work->seconds / 900) + 0.4 ) / 4) . "&nbsp;</TD>\n";
        echo "<TD>$work->work_description</TD></TR>\n";
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
    echo "$tbldef<TR><TD CLASS=sml COLSPAN=2>&nbsp;</TD></TR>\n";
    echo "<TR>$hdcell<TD CLASS=h3 ALIGN=RIGHT><FONT SIZE=+1><B>Other Interested Users</B></FONT></TD></TR>\n";
    echo "<TR VALIGN=TOP><th nowrap>&nbsp; &nbsp; &nbsp; </th><td>";
    for( $i=0; $i<$rows; $i++ ) {
      $interested = pg_Fetch_Object( $peopleq, $i );
      if ( $i > 0 ) echo ", ";
      echo "$interested->fullname ($interested->abbreviation)\n";
    }
    echo "</TD></TR></TABLE>\n";
  }
?>


<?php /***** Notes */
  $noteq = pg_Exec( $wrms_db, "SELECT * FROM request_note WHERE request_note.request_id = '$request->request_id'");
  $rows = pg_NumRows($noteq);
  if ( $rows > 0 ) {
?>

<?php echo "$tbldef<TR><TD CLASS=sml COLSPAN=4>&nbsp;</TD></TR><TR>$hdcell"; ?>
<TD CLASS=h3 COLSPAN=3 ALIGN=RIGHT><FONT SIZE=+1><B>Associated Notes</B></FONT></TD></TR>
<TR VALIGN=TOP>
  <TH NOWRAP>&nbsp; &nbsp; </TH>
  <TH ALIGN=LEFT>Noted By</TH>
  <TH ALIGN=LEFT>Noted On</TH>
  <TH ALIGN=LEFT>Details</TH>
</TR>
<?php /*** the actual details of notes */
    for( $i=0; $i<$rows; $i++ ) {
      $request_note = pg_Fetch_Object( $noteq, $i );
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
  $stat_res = pg_Exec( $wrms_db, $statq);
  $rows = pg_NumRows($stat_res);
  if ( $rows > 0 ) {
?>
<?php echo "$tbldef<TR><TD CLASS=sml COLSPAN=4>&nbsp;</TD></TR><TR>$hdcell"; ?>
<TD CLASS=h3 COLSPAN=3 ALIGN=RIGHT><FONT SIZE=+1><B>Changes in Status</B></FONT></TD></TR>
<TR VALIGN=TOP>
  <TH NOWRAP>&nbsp; &nbsp; &nbsp; </TH>
  <TH ALIGN=LEFT WIDTH="15%">Changed By</TH>
  <TH WIDTH="25%" ALIGN=LEFT>Changed On</TH>
  <TH WIDTH="60%" ALIGN=LEFT>Changed To</TH>
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

    echo "$tbldef<TR><TD CLASS=sml COLSPAN=4><FORM ACTION=\"request-changed.php3\" METHOD=POST></TD></TR><TR>$hdcell";
    echo "<TD CLASS=h3 COLSPAN=3 ALIGN=RIGHT><FONT SIZE=+1><B>";
    /**** only update status & eta if they are administrator */
    if ( $administrator ) echo "Change Status or ";
    echo "Add Notes</B></FONT></TD></TR>\n";
    echo "";
    echo "<INPUT TYPE=\"hidden\" NAME=\"request_id\" VALUE=\"$request->request_id\">";
    if ( $administrator && $notable ) {
      echo "<TR>";
      echo "<TH ALIGN=RIGHT>New Status:</TH>";
      echo "<TD ALIGN=LEFT>&nbsp;<SELECT NAME=\"new_status\">$status_list</SELECT></TD>";
      echo "<TH ALIGN=RIGHT>&nbsp;&nbsp;&nbsp;ETA:</TH>";
      echo "<TD ALIGN=LEFT>&nbsp;";
      if ( $sysmgr || $allocated_to ) echo "<INPUT TYPE=text NAME=\"new_eta\" SIZE=30 VALUE=\"";
      echo substr( nice_date( $request->eta ), 7);
      if ( $sysmgr || $allocated_to ) echo "\">";
      echo "</TD></TR>\n";
    }
?>

<TR VALIGN=TOP border=0 cellspacing=0 cellpadding=4>
  <TH ALIGN=RIGHT>New Note:</TH>
  <TD ALIGN=LEFT COLSPAN=3><TEXTAREA NAME="new_note" ROWS=8 COLS=60  WRAP="SOFT"></TEXTAREA></TD>
</TR>
<TR><TD CLASS=mand COLSPAN=4 ALIGN=CENTER><FONT SIZE=+1><B>
<INPUT TYPE=submit VALUE="Update Request" NAME=submit></B></FONT></form></TD></TR>
</TABLE>

<?php
  }  // if ! plain
}  // isset($request) (way up there with the update details!)
?>

