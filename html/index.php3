<?php
  header("Location: http://wrms.catalyst.net.nz/index.php");  /* Redirect browser to login page */
  exit; /* Make sure that code below does not get executed when we redirect. */

// Dead code below...
  $title = "WRMS Home";
  include("apms-header.php3");
?>

<H2>Welcome to Catalyst's Work Request Management System</H1>
<P>&nbsp;<BR>This site is designed to allow Catalyst's clients to enter requests for work to be performed
by Catalyst.<BR>&nbsp;</P>

<P>If you are not currently registered for the system, you can <A HREF=register.php3>register here</A>.  Otherwise you will be asked to log in when you click on one of the links at the bottom of the page.</P>

<?php
  include("apms-footer.php3");
?>
