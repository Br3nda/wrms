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

    WMLCardInit("showrequest", "false"); 
    WMLdo("prev", "", "List", "", "<prev/>");
    WMLCardBody( safe_for_wap($request) );
  }

  WMLCardFinn();
  WMLFinn();
?>
