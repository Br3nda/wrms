<?php
  include( "awm-auth.php3" );
  $title = "Create New System Update";
  include("apms-header.php3"); 

  $current = "";
  include( "$funcdir/my_system-list.php3" );
  $current = "";
  include("$funcdir/active-request-list.php3");
?>

<FORM ACTION="new-update-done.php3" METHOD=POST ENCTYPE="multipart/form-data">
<INPUT TYPE="hidden" name="MAX_FILE_SIZE" value="2000000">
<TABLE BORDER=1 ALIGN=CENTER WIDTH=100%>
<TR>
  <TH ALIGN=RIGHT>Update file:</TH>
  <TD ALIGN=LEFT>&nbsp;
<INPUT TYPE="file" SIZE=64 MAXLENGTH=250 NAME="in_file" VALUE="upd*.zip" ACCEPT="application/x-zip-compressed"></TD>
</TR>

<TR VALIGN=TOP>
  <TH ALIGN=RIGHT>Brief:</TH>
  <TD ALIGN=LEFT>&nbsp;<INPUT TYPE=text NAME="in_brief" SIZE=75 VALUE=""></TD>
</TR>

<TR VALIGN=TOP>
  <TH ALIGN=RIGHT>Description:</TH>
  <TD ALIGN=LEFT>&nbsp;<TEXTAREA NAME="in_description" ROWS=15 COLS=72  WRAP="SOFT"></TEXTAREA></TD>
</TR>

<?php if ( $num_systems > 1 ) { ?>
  <TR>
    <TH ALIGN=RIGHT>System:</TH>
    <TD ALIGN=LEFT>&nbsp;<SELECT NAME="in_system"><?php echo $sys_list ?></SELECT></TD>
  </TR>
<?php } else {
  echo "<INPUT TYPE=\"hidden\" NAME=\"in_system\" VALUE=\"$sys_type\">";
}
?>

<TR>
  <TH ALIGN=RIGHT>Requests:</TH>
  <TD ALIGN=LEFT>
<?php
    echo "<TABLE WIDTH=90% BORDER=0><TR><TD WIDTH=70% ALIGN=RIGHT><SELECT MULTIPLE NAME=\"in_requests[]\" SIZE=30>$request_list</SELECT></TD><TD WIDTH=30% VALIGN=BOTTOM><FONT SIZE=-2 FACE=sans-serif>Only active requests are listed.  To assign the update against an inactive request you would first need to re-activate it.<BR>&nbsp;</FONT></TD></TR></TABLE>";
?>
  </TD>
</TR>

<TR>
  <TD COLSPAN=2 ALIGN=CENTER><B><INPUT TYPE="submit" NAME="Submit" VALUE=" Submit "></B></TD>
</TR>

</TABLE>
</FORM>

<?php include("apms-footer.php3"); ?>
