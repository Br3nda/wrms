<?php
  include("awm-auth.php3");
  $title = "Home";
  include("$homedir/apms-header.php3"); 

?>

<TABLE BORDER=2 WIDTH=80% ALIGN=CENTER>
  <TR><TD ALIGN=RIGHT>User name:</TD><TD><?php echo "$usr->username";?></TR>
  <TR><TD ALIGN=RIGHT>EMail Address:</TD><TD><?php echo "$usr->email";?></TR>
  <TR><TD ALIGN=RIGHT>Full name:</TD><TD><?php echo "$usr->fullname";?></TR>
  <TR><TD ALIGN=RIGHT>Notes:</TD><TD><?php echo "$usr->note";?>&nbsp;</TR>
</TABLE>
<HR>

<UL><FONT SIZE=5><B>Manuals</B></FONT>
<LI><A HREF="manual/user/">Starting Users</A>
<LI><A HREF="manual/advanced/">Advanced Users</A>
<LI><A HREF="manual/admin/">Administration</A>
</UL>

<HR>

<?php include("$homedir/apms-footer.php3"); ?>
