<?php
if ( $logged_on ) {
  if ( is_member_of('Admin','Support') ) {
    include("indexsupport.php");
  }
  else {
    include("indexclients.php");
  }
}
else { ?>

<H4>For access to the <?php echo $system_name; ?> you should log on with
the username and password that have been issued to you.</H4>

<h4>If you would like to request access, please e-mail <?php echo $admin_email; ?>.</h4>

<?php } ?>

