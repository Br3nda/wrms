<?php
  $title = "Mail User Password";
  include("apms-header.php3");
  $funcdir = "./funcs";
  $dbid = pg_Connect("dbname=wrms user=general");
?>
<P>Have you forgotten your password?  Enter your username in the box below and it will be e-mailed to you
 directly.  Of course if you have forgotten your user name you're truly stuffed!  In that case, you probably need to
 send an e-mail directly to one of us at <A HREF=http://www.catalyst.net.nz>Catalyst.</A></P>

<FORM ACTION="mailpass-done.php3" METHOD=POST>
<TABLE BORDER=0 ALIGN=CENTER WIDTH="75%">
<TR>
  <TH ALIGN=RIGHT>User Name:</TH>
  <TD ALIGN=LEFT>&nbsp;<INPUT TYPE="text" SIZE=30 NAME="in_username" VALUE=""></TD>
</TR>

<TR>
  <TD COLSPAN=2 ALIGN=CENTER><FONT FACE="arial,helv,sans serif" SIZE=+1><B><INPUT TYPE="submit" NAME="submit" VALUE=" Mail Password! "></B></FONT></TD>
</TR>

</TABLE>
</FORM>

<?php include("apms-footer.php3"); ?>
