<?php
  $title = "New User Registration";
  include("apms-header.php3");
  $funcdir = "./funcs";
  $dbid = pg_Connect("dbname=wrms user=general");
  include("$funcdir/organisation-list.php3");
  $org_list = "<OPTION VALUE=\"\" SELECTED>--- Select Organisation ---$org_list";
?>
<P>This site is to allow Catalyst's clients to enter requests for work to be performed
by Catalyst &nbsp;-&nbsp; if you are looking for a job, I suggest you visit the <A HREF=http://www.jobnet.co.nz>JobNet</A>,
a site which we maintain for Wellington Newspapers.</P>

<HR>
<FORM ACTION="register-done.php3" METHOD=POST>
<TABLE BORDER=0 ALIGN=CENTER WIDTH="95%">
<TR>
  <TH ALIGN=RIGHT>User Name:</TH>
  <TD ALIGN=LEFT>&nbsp;<INPUT TYPE="text" SIZE=20 NAME="in_username" VALUE=""></TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Full Name:</TH>
  <TD ALIGN=LEFT>&nbsp;<INPUT TYPE="text" SIZE=40 NAME="in_fullname" VALUE=""></TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>EMail Address:</TH>
  <TD ALIGN=LEFT>&nbsp;<INPUT TYPE="text" SIZE=40 NAME="in_email" VALUE=""></TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Password:</TH>
  <TD ALIGN=LEFT>&nbsp;<INPUT TYPE="password" SIZE=20 NAME="in_password" VALUE=""></TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Organisation:<BR>&nbsp;</TH>
  <TD ALIGN=LEFT>&nbsp;<SELECT NAME="in_org_code"><?php echo $org_list; ?></SELECT>
<BR>&nbsp;&nbsp;<FONT SIZE=2>If your organisation isn't in the list, note the details below and I will add it.</FONT></TD>
</TR>

<TR>
  <TD COLSPAN=2 ALIGN=CENTER><FONT FACE="arial,helv,sans serif" SIZE=+1><B><INPUT TYPE="submit" NAME="submit" VALUE=" Register! "></B></FONT><HR></TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Anything else:<BR>&nbsp;<BR>&nbsp;</TH>
  <TD ALIGN=LEFT><TEXTAREA NAME="in_note" ROWS=4 COLS=58 WRAP="SOFT"></TEXTAREA></TD>
</TR>
</TABLE>
</FORM>

<?php include("apms-footer.php3"); ?>
