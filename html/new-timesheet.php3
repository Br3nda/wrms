<?php
  include( "awm-auth.php3" );
  $title = "Record Timesheet";
  include("$homedir/apms-header.php3"); 

  require( "$funcdir/parameters-func.php3");
  $args = parse_parameters($argv[0]);
  if ( !isset($request_id) ) $request_id = "$args->request_id";
  
  include("$funcdir/support_user-list.php3");
?>

<FORM ACTION="new-timesheet-done.php3" METHOD=POST>
<TABLE BORDER=1 ALIGN=CENTER WIDTH=100%>
<TR>
  <TH ALIGN=RIGHT>Work request:</TH>
  <TD ALIGN=LEFT>&nbsp;<INPUT TYPE=text SIZE=10 NAME="in_request" VALUE=<?php echo "$request_id"; ?>></TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Started:</TH>
  <TD ALIGN=LEFT>&nbsp;<INPUT TYPE=text NAME="in_work_on" VALUE="" SIZE=20 MAXLENGTH=50>
<FONT SIZE=1 FACE=tahoma,sans-serif>&nbsp;Use "date[, time]" where [date] may be yesterday, today, or a specific date.</FONT></TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Duration:</TH>
  <TD ALIGN=LEFT>&nbsp;<INPUT TYPE=text SIZE=20 MAXLENGTH=50 NAME="in_duration" VALUE="">
<FONT SIZE=1 FACE=tahoma,sans-serif>&nbsp;Most time formats should be acceptable such as "20 minutes" or 2 days 30 minutes.</FONT></TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Done by:</TH>
  <TD ALIGN=LEFT>&nbsp;<SELECT NAME="in_work_by"><?php echo "$support_usr_list"; ?></SELECT></TD>
</TR>

<TR VALIGN=TOP>
  <TH ALIGN=RIGHT>Description:</TH>
  <TD ALIGN=LEFT>&nbsp;<TEXTAREA NAME="in_description" ROWS=12 COLS=66  WRAP="SOFT"></TEXTAREA></TD>
</TR>

<TR>
  <TD COLSPAN=2 ALIGN=CENTER><B><INPUT TYPE="submit" NAME="Submit" VALUE=" Submit "></B></TD>
</TR>

</TABLE>
</FORM>

<?php include("apms-footer.php3"); ?>
