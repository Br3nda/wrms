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
<blockquote>
<p><strong>
Welcome to the technical support system of the Open Source Virtual Learning Environment project. For more information on the project please visit  <a href="http://www.ose.org.nz">www.ose.org.nz</a>. 
</strong></p>
</blockquote>
<p>The goal of the project is to select, further develop and support open source e-learning software for deployment throughout New Zealand's education sector.</p>
<p>
Eduforge Support is delivered by <a href="http://catalyst.net.nz/products-moodle.htm">Catalyst IT Limited</a> , a trusted Moodle Partner and the core development team on the OSVLE project.
To set-up Moodle hosting and support on the Education Cluster please read our project wikis:
</p>

<ul>
<li><a href="http://eduforge.org/wiki/wiki/nzvle/wiki?pagename=VLESetup">Setup of your Virtual learning Environment</a></li>
<li><a href="http://eduforge.org/wiki/wiki/nzvle/wiki?pagename=VLESupport">Support for your Virtual learning Environment</a></li>
</ul>
<p>Please e-mail <a href="mailto:<?php echo $admin_email; ?>"><?php echo $admin_email; ?></a> if you require further information.</p>

<?php } ?>

