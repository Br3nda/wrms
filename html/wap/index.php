<?php 

  require("inc/wap.php");
  WMLinit();
	
  include("inc/login.php");

  WMLCardInit("init", "true", "WRMS");
  WMLDo("accept", "", "Submit", "wrms.php?l=\$(lo)&amp;p=\$(pw)", "");
  WMLCardBody($login);
  WMLCardFinn();

  WMLFinn();
?>
