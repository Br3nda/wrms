<?php
  include( "$base_dir/inc/code-list.php");
  include( "$base_dir/inc/notify-emails.php");
  include( "$base_dir/inc/nice-date.php");
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

?>
<P CLASS=helptext">Use this form to enter changes to details for the requests of your systems, or to enter 
details for new requests.  Areas highlighted in a lighter yellow are required fields in one case or another.</P>
<FORM METHOD=POST ACTION=request.php ENCTYPE="multipart/form-data">
<TABLE WIDTH=100% cellspacing=0 border=5>
<TR><TD ALIGN=LEFT><H2>Work Request Details</H2></TD><?php
  echo "<TD ALIGN=RIGHT><A HREF=\"request.php?action=register&request_id=$request_id\">$tell</a></form>";
?></TD>
</TR>
</TABLE>

<TABLE border=1>
<TR><TD CLASS=h3 COLSPAN=3 ALIGN=RIGHT><FONT SIZE=+1><B>Request details</B></FONT></TD></TR>
<TR>
  <TH ALIGN=RIGHT>WR #:</TH>
  <TH ALIGN=CENTER><?php echo "$request->request_id";?></TH>
  <TD ALIGN=LEFT>
<?php
  if ( $editable ) {
    echo "<FORM ACTION=\"modify-request-done.php3\" METHOD=POST>";
    echo "<INPUT TYPE=\"hidden\" NAME=\"request_id\" VALUE=\"$request->request_id\">"; 
    echo "<INPUT TYPE=\"text\" NAME=\"new_brief\" SIZE=65 VALUE=\"$request->brief\">"; 
  }
  else
    echo "$request->brief";
?>
  </TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>From:</TH>
  <TD ALIGN=CENTER><?php echo "$request->request_by";?></TD>
  <TD ALIGN=LEFT>&nbsp;<?php
     echo "<B>Entered:</B> " . nice_date($request->request_on);
     if ( strcmp( $request->eta, "") )
       echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<B>ETA:</B> " .  substr( nice_date($request->eta), 7);
  ?></TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Status:</TH>
  <TD ALIGN=CENTER><?php
  if ( $editable ) {
    echo "<LABEL><INPUT TYPE=\"checkbox\" NAME=\"new_active\" VALUE=\"TRUE\"";
    if ( $request->active == "t" ) echo " CHECKED";
    echo ">&nbsp;Active</LABEL>";
  }
  else if ( $request->active ) echo "Active";
  else echo "Inactive";
 ?></TD>
  <TD ALIGN=LEFT>&nbsp;<?php echo "$request->last_status - $request->status_desc";?></TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>System:</TH>
  <TD ALIGN=CENTER>
<?php
  echo "$request->system_code</TD><TD ALIGN=LEFT>";
  if ( $editable )
    echo "&nbsp;<SELECT NAME=\"new_system_code\">$system_codes</SELECT>"; 
  else
    echo "$request->system_desc";
?>
  </TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Type:</TH>
  <TD ALIGN=CENTER>
<?php
  echo "$request->request_type</TD><TD ALIGN=LEFT>";
  if ( $editable )
    echo "&nbsp;<SELECT NAME=\"new_type\">$request_types</SELECT>"; 
  else
    echo "$request->request_type_desc";
?>
  </TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Urgency:</TH>
  <TD ALIGN=CENTER>
<?php
  echo "$request->severity_code</TD><TD ALIGN=LEFT>";
  if ( $editable )
    echo "&nbsp;<SELECT NAME=\"new_severity\">$severities</SELECT>"; 
  else
    echo "$request->severity_desc";
?>
  </TD>
</TR>

<TR VALIGN=TOP>
  <TH ALIGN=RIGHT>Detail:</TH>
  <TD ALIGN=LEFT COLSPAN=2>
<?php
  if ( $editable )
    echo "<TEXTAREA NAME=\"new_detail\" ROWS=10 COLS=70  WRAP=\"SOFT\">$request->detailed</TEXTAREA>"; 
  else
    echo html_format($request->detailed);
?>
</TD>
</TR>

<?php
  if ( $editable ) {
    /* of course, if it's editable we need to have an update button don't we, so here it is!  */
    echo "<TR><TD COLSPAN=3 ALIGN=CENTER CLASS=mand>";
    echo "<B><INPUT TYPE=\"submit\" NAME=\"submit\" VALUE=\" Apply Changes \"></B></TD></TR></FORM>";
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
 &nbsp;<BR CLEAR=ALL><H2>Update Details</H2>
 <TABLE BORDER=1 ALIGN=CENTER WIDTH=100%>
 <TR><TH>ID</TH><TH>Done By</TH><TH>Done On</TH><TH>Description</TH><TH></TH></TR>
<?php
      for( $i=0; $i<$rows; $i++ ) {
        $update = pg_Fetch_Object( $updateq, $i );

        echo "<TR>";
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
  }
?>

<?php /***** Quote Details */
  /* we only show quote details if it is 'quotable' (i.e. requestor, administrator or catalyst owner) */
  if ($quotable ) {
    $query = "SELECT *, awm_get_lookup_desc('request_quote','quote_type', request_quote.quote_type) AS type_desc ";
    $query .= "FROM request_quote, awm_usr, awm_perorg ";
    $query .= "WHERE request_quote.request_id = $request->request_id ";
    $query .= "AND request_quote.quote_by_id = awm_usr.perorg_id ";
    $query .= "AND awm_perorg.perorg_id = awm_usr.perorg_id ";
    $quoteq = pg_Exec( $wrms_db, $query);
    $rows = pg_NumRows($quoteq);
    if ( $rows > 0 ) {
?>
 &nbsp;<BR CLEAR=ALL><H2>Quotations</H2>
 <TABLE BORDER=1 ALIGN=CENTER WIDTH=100%>
 <TR><TH>Quote</TH><TH>WR #</TH><TH>Done By</TH><TH>Brief</TH><TH>Done On</TH><TH>Type</TH><TH>Amount</TH></TR>
<?php

      for ( $i=0; $i < $rows; $i++ ) {
        $quote = pg_Fetch_Object( $quoteq, $i );
        echo "<TR>";
        echo "<TH ALIGN=CENTER VALIGN=TOP ROWSPAN=2><FONT SIZE=+2>$quote->quote_id</FONT></TH>\n";
        echo "<TD ALIGN=CENTER><A HREF=\"modify-request.php3?request_id=$quote->request_id\">$quote->request_id</A></TD>\n";
        echo "<TD ALIGN=CENTER>$quote->perorg_name</TD>\n";
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
  }

  if ( isset( $request ) ) {
    /***** Allocated People */
    /* People who have been allocated to the request - again, only if there are any.  */
    $query = "SELECT person.perorg_name AS fullname, org.perorg_sort_key AS org_code ";
    $query .= "FROM perorg_request, awm_perorg AS person, awm_perorg AS org ";
    $query .= "WHERE request_id = '$request->request_id' ";
    $query .= "AND perorg_request.perorg_id = person.perorg_id ";
    $query .= "AND perorg_request.perreq_role = 'ALLOC' ";
    $query .= "AND org.perorg_id = awm_get_rel_parent(perorg_request.perorg_id, 'Employer') ";
    $allocq = pg_Exec( $wrms_db, $query);
    $rows = pg_NumRows($allocq);
    if ( $rows > 0 ) {
      echo "&nbsp;<BR CLEAR=ALL><H2>Work Allocated To</H2><P ALIGN=\"LEFT\">";
      for( $i=0; $i<$rows; $i++ ) {
        $alloc = pg_Fetch_Object( $allocq, $i );
        if ( $i > 0 ) echo ", ";
        echo "$alloc->fullname ($alloc->org_code)";
      }
      echo "</P>";
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
 &nbsp;<BR CLEAR=ALL><H2>Work Done</H2>
 <TABLE BORDER=1 ALIGN=CENTER WIDTH=100%>
 <TR VALIGN=TOP>
   <TH ALIGN=LEFT>Done By</TH>
   <TH>Done On</TH>
   <TH>Hours</TH>
   <TH>Description</TH>
 </TR>
<?php
      for( $i=0; $i<$rows; $i++ ) {
        $work = pg_Fetch_Object( $workq, $i );

        echo "<TR><TD>$work->perorg_name</TD>";
        echo "<TD>" . nice_date($work->work_on) . "</TD>";
        echo "<TD ALIGN=RIGHT>" . sprintf( "%.2f", round(($work->seconds / 900) + 0.4 ) / 4) . "&nbsp;</TD>";
        echo "<TD>$work->work_description</TD></TR>";
      }
      echo "</TABLE>";
    }  // if rows>0
?>


<?php /***** Interested People */
  /* People who are interested - again, only if there are any.  The requestor is not shown */
  $query = "SELECT person.perorg_name AS fullname, org.perorg_sort_key AS org_code ";
  $query .= "FROM perorg_request, awm_perorg AS person, awm_perorg AS org ";
  $query .= "WHERE request_id = '$request->request_id' ";
  $query .= "AND perorg_request.perorg_id = person.perorg_id ";
  $query .= "AND perorg_request.perreq_role = 'INTRST' ";
  $query .= "AND org.perorg_id = awm_get_rel_parent(perorg_request.perorg_id, 'Employer') ";
  $peopleq = pg_Exec( $wrms_db, $query);
  $rows = pg_NumRows($peopleq);
  if ( $rows > 0 ) {
    echo "&nbsp;<BR CLEAR=ALL><H2>Other Interested Users</H2><P ALIGN=\"LEFT\">";
    for( $i=0; $i<$rows; $i++ ) {
      $interested = pg_Fetch_Object( $peopleq, $i );
      if ( $i > 0 ) echo ", ";
      echo "$interested->fullname ($interested->org_code)";
    }
    echo "</P>";
  }
?>


<?php /***** Notes */
  $noteq = pg_Exec( $wrms_db, "SELECT * FROM request_note WHERE request_note.request_id = '$request->request_id'");
  $rows = pg_NumRows($noteq);
  if ( $rows > 0 ) {
?>
&nbsp;<BR CLEAR=ALL>
<H2>Associated Notes</H2>
<TABLE BORDER=1 ALIGN=CENTER WIDTH=100%>
<TR VALIGN=TOP>
  <TH ALIGN=LEFT>Noted By</TH>
  <TH>Noted On</TH>
  <TH>Details</TH>
</TR>
<?php /*** the actual details of notes */
    for( $i=0; $i<$rows; $i++ ) {
      $request_note = pg_Fetch_Object( $noteq, $i );
      echo "<TR VALIGN=TOP><TD>$request_note->note_by</TD> <TD>";
      echo nice_date($request_note->note_on);
      echo "</TD> <TD>" . html_format($request_note->note_detail) . "</TD></TR>";
    }
    echo "</TABLE>";
  }
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
&nbsp;<BR CLEAR=ALL>
<H2>Changes in Status</H2>
<TABLE BORDER=1 ALIGN=CENTER WIDTH=100% CLASS=mgn>
<TR VALIGN=TOP>
  <TH ALIGN=LEFT WIDTH="15%">Changed By</TH>
  <TH WIDTH="25%">Changed On</TH>
  <TH WIDTH="60%">Changed To</TH>
</TR>
<?php /* the actual status stuff */
    for( $i=0; $i<$rows; $i++ ) {
      $request_status = pg_Fetch_Object( $stat_res, $i );
      echo "<TR VALIGN=TOP><TD>$request_status->fullname</TD> <TD>" . nice_date($request_status->status_on) . "</TD> <TD>$request_status->status_code - $request_status->lookup_desc</TD></TR>";
    }
    echo "</TABLE>";
  }

  if ( ! $plain ) {

    /* separate updateable bits from main informaton screens */
    echo "&nbsp;<P>&nbsp;<BR CLEAR=ALL><HR>";

    /* only update status if administrator - anyone can add a note though */
    echo "<H2>Update ";
    if ( $administrator ) echo "Status or ";
    echo "Notes</H2>";

?>

<FORM ACTION="request-changed.php3" METHOD=POST>
<?php echo "<INPUT TYPE=\"hidden\" NAME=\"request_id\" VALUE=\"$request->request_id\">"; ?>
<TABLE BORDER=1 ALIGN=CENTER WIDTH=100%>
<?php /**** only update status & eta if they are administrator */
  if ( $notable ) {
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
}
?>

<TR VALIGN=TOP>
  <TH ALIGN=RIGHT>New Note:</TH>
  <TD ALIGN=LEFT COLSPAN=3><TEXTAREA NAME="new_note" ROWS=10 COLS=70  WRAP="SOFT"></TEXTAREA></TD>
</TR>

<TR><TD CLASS=mand COLSPAN=4 ALIGN=CENTER><FONT SIZE=+1><B><INPUT TYPE=submit VALUE="Update Request" NAME="submit"></B></FONT></TD></TR>
<?php
  }  // isset($request)
?>
</TABLE>

