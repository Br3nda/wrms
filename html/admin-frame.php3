<?php
  include("awm/funcs/authenticate.php3");
  if ( $usr->access_level < 80000 ) {
    echo "<P>Unauthorised</P>";
    exit;
  }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 TRANSITIONAL//EN">
<HTML>
<HEAD>
<TITLE>Work Request System Administration</TITLE>
</HEAD>
<frameset rows="*">
  <frameset cols="15%,*">
    <frame name="menu" src="admin-menu.php3" frameborder="0" scrolling="auto" title="menus and links">
    <frameset rows="70%,*">
      <frame name="entry" frameborder="1" src="admin-home.php3" scrolling="auto" title="enter form data here">
      <frame name="help" frameborder="1" src="admin-help.php3" scrolling="auto" title="help and results information">
    </frameset>
  </frameset>
  <noframes>
  <body>
  <p>This page uses frames, but your browser doesn't support them.</p>
  <P>You can try and use these links, but menus and error information may not be as nicely displayed.  This should work, but it hasn't been tested as I haven't got such a browser myself.</P>
  <UL>
  <LI><A HREF=admin-menu.html>The Administration Menu</A></LI>
  <LI><A HREF=admin-home.php3>Administration Home Page</A></LI>
  <LI><A HREF=admin-help.html>Administration Help</A></LI>
  </UL>
  </body>
  </noframes>
</frameset>
</HTML>
