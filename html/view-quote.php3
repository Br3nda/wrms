<?php
  include( "awm-auth.php3" );
  include("$funcdir/parameters-func.php3");
  $args = parse_parameters( $argv[0] );
  $title = "View Quote Q$args->quote_id";
  include("$homedir/apms-header.php3");
  include("$funcdir/nice_date-func.php3");
  $current = "";
  include("$funcdir/lookup_list-func.php3");

 /* Complex request mainly because we hook in all of the codes tables for easy display */
  $rows = 0;
  $query = "SELECT * FROM request_quote, usr, request, work_system, status, request_type, severity ";
  $query .= "WHERE request_quote.quote_id = '$args->quote_id'";
  $query .= " AND request_quote.quoted_by = usr.username";
  $query .= " AND request_quote.request_id = request.request_id";
  $query .= " AND request.system_code = work_system.system_code";
  $query .= " AND request.last_status = status.status_code";
  $query .= " AND request.request_type = request_type.request_type";
  $query .= " AND request.severity_code = severity.severity_code";

  /* now actually query the database... */
  $rid = pg_Exec( $dbid, $query);
  if ( $rid ) $rows = pg_NumRows($rid);
  if ( ! $rid || ! $rows ) {
    echo "<H3>&nbsp;Query Error -quote #$quote_id not found in database!</H3>";
    echo "<P>The error returned was:</P><PRE>" . pg_ErrorMessage( $dbid ) . "</PRE>";
    echo "<P>The failed query was:</P><PRE>$query</PRE>";
    include("$homedir/apms-footer.php3");
    exit;
  }
  $quote = pg_Fetch_Object( $rid, 0 );

  $current["$quote->request_id"] = 1;
  include( "$funcdir/active-request-list.php3");

  /* System Admin of quoted request only if admin for system (funny that!)*/
  $sysadm = !strcmp( $quote->notify_usr, $usr->username );

  /* Current quote is editable if the user quoted it or user is system administrator */
  $editable = !strcmp( $quote->quoted_by, $usr->username );
  if ( ! $editable ) $editable = $sysadm;

?>

<H2>Quote Details</H2>
<?php
  if ( $editable ) {
    echo "<FORM ACTION=\"modify-quote.php3\" METHOD=POST>";
    echo "<INPUT TYPE=\"hidden\" NAME=\"quote_id\" VALUE=\"$quote->quote_id\">";
  }
?>
<TABLE BORDER=1 CELLSPACING=1 CELLPADDING=1 ALIGN=CENTER WIDTH=100%>
<TR>
  <TH ALIGN=RIGHT WIDTH=20%><FONT SIZE=+2>Q<?php echo "$quote->quote_id</FONT>"; ?></TH>
  <TD ALIGN=LEFT WIDTH=80%>&nbsp;
<?php
  if ( $editable )
    echo "<INPUT TYPE=text SIZE=68 NAME=\"new_brief\" VALUE=\"$quote->quote_brief\"><BR>";
  else
    echo "$quote->quote_brief";
?>
  </TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Description:</TH>
  <TD ALIGN=LEFT>&nbsp;
<?php
  if ( $editable )
   echo "<TEXTAREA NAME=\"new_description\" ROWS=6 COLS=66 WRAP=\"SOFT\">$quote->quote_details</TEXTAREA>";
  else
    echo "$quote->quote_details";
?>
  </TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>By:</TH>
  <TD ALIGN=LEFT>
<?php
  if ( $editable )
    echo "&nbsp;<INPUT TYPE=text SIZE=30 NAME=\"new_quoted_by\" VALUE=\"$quote->quoted_by\">";
  else
    echo "$quote->quoted_by";

  echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<B><SPAN ID=\"likeTH\" STYLE=\"font-size: 16px; font-family: verdana, sans-serif; color: #000050; font-weight: bold;\">On:</SPAN></B>&nbsp;&nbsp;";
  if ( $editable )
    echo "<INPUT TYPE=text SIZE=30 NAME=\"new_quoted_on\" VALUE=\"$quote->quoted_on\">";
  else
    echo "$quote->quoted_on";
?>
  <TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Amount:</TH>
  <TD ALIGN=LEFT>
<?php
  if ( $editable ) {
    echo "&nbsp;<INPUT TYPE=text SIZE=15 MAXLENGTH=25 NAME=\"new_amount\" VALUE=\"$quote->quote_amount\">";
    echo "&nbsp;<SELECT NAME=\"new_quote_units\">";
    echo lookup_list( $dbid, "request_quote", "quote_units", $quote->quote_units);
    echo "</SELECT>";
  }
  else
    echo "$quote->quote_amount $quote->quote_units";
?>
</TD>
</TR>

<?php
  if ( $editable ) {
    echo "<TR><TH ALIGN=RIGHT>Request:</TH><TD ALIGN=CENTER>";
    echo "<SELECT NAME=\"new_request\">$request_list</SELECT>";
    echo "</TD></TR>";
  }

  if ( $editable ) {
    /* of course, if it's editable we need to have an update button don't we, so here it is!  */
    echo "<TR><TD COLSPAN=3 ALIGN=CENTER>";
    echo "<B><INPUT TYPE=\"submit\" NAME=\"submit\" VALUE=\" Apply Changes \"></B></TD></TR></FORM>";
  }
?>
</TABLE>


&nbsp;<BR CLEAR=ALL><H2>Work Request Details</H2>

<TABLE BORDER=1 ALIGN=CENTER WIDTH=100%>
<TR>
  <TH ALIGN=RIGHT>WR #:</TH>
  <TH ALIGN=CENTER><?php echo "$quote->request_id";?></TH>
  <TD ALIGN=LEFT><?php echo "$quote->brief"; ?></TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>From:</TH>
  <TD ALIGN=CENTER><?php echo "$quote->request_by";?></TD>
  <TD ALIGN=LEFT>&nbsp;<?php echo "$quote->request_on";?></TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Status:</TH>
  <TD ALIGN=CENTER><?php if ( $quote->active ) echo "Active"; else echo "Inactive"; ?></TD>
  <TD ALIGN=LEFT>&nbsp;<?php echo "$quote->last_status - $quote->status_desc";?></TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>System:</TH>
  <TD ALIGN=CENTER><?php echo "$quote->system_code</TD><TD ALIGN=LEFT>$quote->system_desc";?></TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Type:</TH>
  <TD ALIGN=CENTER>
<?php
  echo "$quote->request_type</TD><TD ALIGN=LEFT>";
  echo "$quote->request_type_desc";
?>
  </TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Severity:</TH>
  <TD ALIGN=CENTER>
<?php
  echo "$quote->severity_code</TD><TD ALIGN=LEFT>";
  echo "$quote->severity_desc";
?>
  </TD>
</TR>

<TR VALIGN=TOP>
  <TH ALIGN=RIGHT>Detail:</TH>
  <TD ALIGN=LEFT COLSPAN=2><?php echo "$request->detailed"; ?></TD>
</TR>

</TABLE>

<?php
  include("$homedir/apms-footer.php3");
?>







