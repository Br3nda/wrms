<?php
if ( $logged_on ) {
  if ( is_member_of('Admin','Support') ) {
    include("inc/indexsupport.php");
  }
  else {
    include("inc/indexclients.php");
  }
}
else { ?>

<H4>For access to Catalyst's Work Request Management System you should log on with
the username and password that have been issued to you.</H4>

<h4>If you would like to request access, please e-mail Andrew at Catalyst.</h4>

<?php } ?>

