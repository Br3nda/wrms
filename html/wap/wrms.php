<?php 
require("inc/wap.php");

  WMLinit();

  if ( !isset($id) ) {
    include("inc/getRequests.php");

    WMLCardInit("init", "false", "List"); 
    WMLdo("prev", "", "WRMS", "", "<prev/>");
    WMLCardBody( safe_for_wap($requests) );
  }
  else {
    include("inc/showRequest.php");

    WMLCardInit("showrequest", "false", safe_for_wap("$title") ); 
    WMLdo("prev", "", "List", "", "<prev/>");
    WMLdo("accept", "", "Submit", "wrms.php?l=\$(lo)&amp;p=\$(pw)&amp;id=\$(id)&amp;active=\$(active)", "");
    WMLCardBody( safe_for_wap($request) );
  }

  WMLCardFinn();
  WMLFinn();
?>
