<?php
  header("Location: http://wrms.catalyst.net.nz/index.php");  /* Redirect browser to login page */
  exit; /* Make sure that code below does not get executed when we redirect. */

  include( "awm-auth.php3" );
  $title = "Create New Work Request";
  include("$homedir/apms-header.php3"); 

  $current = "";
  include( "$funcdir/request_type-list.php3" );
  include( "$funcdir/severity-list.php3" );
  include( "$funcdir/my_system-list.php3" );
  if ( ! $num_systems ) exit();
  $current = $usr->username;
  include("$funcdir/user-list.php3");
  include("$funcdir/support_user-list.php3");

?>

<FORM ACTION="new-request-done.php3" METHOD=POST>
<TABLE BORDER=1 ALIGN=CENTER WIDTH=100%>
<TR>
  <TH ALIGN=RIGHT>User:</TH>
<?php
  echo "<TD ALIGN=LEFT>&nbsp;";
  if ( $usr->access_level > 79999 ) {
    echo "<SELECT NAME=\"in_username\">$usr_list</SELECT>";
    echo "<TR><TH ALIGN=RIGHT>Assign to:</TH><TD ALIGN=LEFT>&nbsp;<SELECT NAME=\"in_assigned\">$support_usr_list</SELECT></TD></TR>";
  }
  else
    echo "$usr->fullname ($usr->org_code)";

?>
</TD></TR>
<TR>
  <TH ALIGN=RIGHT>Brief:</TH>
  <TD ALIGN=LEFT>&nbsp;<INPUT TYPE="text" SIZE=75 MAXLENGTH=250 NAME="in_brief" VALUE=""></TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Type:</TH>
  <TD ALIGN=LEFT>&nbsp;<SELECT NAME="in_type"><?php echo $type_list ?></SELECT></TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Severity:</TH>
  <TD ALIGN=LEFT>&nbsp;<SELECT NAME="in_severity"><?php echo $sev_list ?></SELECT></TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Detail:</TH>
  <TD ALIGN=LEFT><TEXTAREA NAME="in_detail" ROWS=15 COLS=75  WRAP="SOFT"></TEXTAREA></TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Notify:</TH>
  <TD ALIGN=LEFT>&nbsp;<LABEL><INPUT TYPE=checkbox NAME="in_notify" VALUE=1 CHECKED>&nbsp;Keep me updated on the status of this request.</LABEL></TD>
</TR>

<?php if ( $num_systems > 1 ) { ?>
  <TR>
    <TH ALIGN=RIGHT>System:</TH>
    <TD ALIGN=LEFT>&nbsp;<SELECT NAME="in_system"><?php echo $sys_list ?></SELECT></TD>
  </TR>
<?php } else {
  echo "\n<INPUT TYPE=\"hidden\" NAME=\"in_system\" VALUE=\"$last_system\">";
}
?>

<TR>
  <TD COLSPAN=2 ALIGN=CENTER><B><INPUT TYPE="submit" NAME="Submit" VALUE=" Submit "></B></TD>
</TR>

</TABLE>
</FORM>

<?php include("$homedir/apms-footer.php3"); ?>
