<?php 

	require("inc/wap.php");

	WMLinit();
	
	if(!isset($id)) {

	  include("inc/getRequests.php");

	  WMLCardInit("init", "false", "List"); 
	  WMLdo("prev", "", "WRMS", "", "<prev/>");
	  WMLCardBody($requests);

	} else {

	  include("inc/showRequest.php");

	  WMLCardInit("showrequest", "false"); 
	  WMLdo("prev", "", "List", "", "<prev/>");
	  WMLCardBody($request);

	}

	WMLCardFinn();

	WMLFinn();
?>
