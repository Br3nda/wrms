<?php

/* 
 * This file contains a library of functions that can be used in part to 
 * generate a wml page. 
 *
 *  functions include ...
 *	WMLinit - initialise a new wml page
 *
 *  This version 29/03/2000 AJM
 *  Modifications 4/4/2000 by AWM to detect browser type and adjust some
 *    operation characteristics appropriately.
 *  Further pissing around by AWM to restructure the browser experience to
 *    give a more visually phone-like appearance.
 *
 */

// This function performs the basic initialisation of the wml page - each separate page
// needs this stuff at the head of the page so that .php file get sent with wml headers

////////////////////////////////////////////////////////////////////
// Initialises the WAP page, sending headers and
// setting up the start of the page.
////////////////////////////////////////////////////////////////////
$wap_pagedata = "";

function WMLinit() {
  global $wap_pagedata;
  $now = time();
  $then = $now + 300;
  Header("Expires: " . gmdate( "D, d M Y H:i:s T", $then) );
  Header("Last-Modified: " . gmdate( "D, d M Y H:i:s T", $now) );
  Header("Cache-Control: private");
  Header("Accept-Ranges: none");
  Header("Content-type:  text/vnd.wap.wml");
  $wap_pagedata = "<?xml version=\"1.0\"?>";
  $wap_pagedata .= "<!DOCTYPE wml PUBLIC \"-//WAPFORUM//DTD WML 1.1//EN\" ";
  $wap_pagedata .= "\"http://www.wapforum.org/DTD/wml_1.1.xml\">\n";
  $wap_pagedata .= "<wml>\n";
}

////////////////////////////////////////////////////////////////////
// Finishes the job.  Calculates the length and
// sends the stuff.
////////////////////////////////////////////////////////////////////
function WMLfinn() {
  global $wap_pagedata;
  $wap_pagedata .= "</wml>\n";
  Header("Content-Length: " . strlen($wap_pagedata) );
  echo $wap_pagedata;
}

////////////////////////////////////////////////////////////////////
// 
////////////////////////////////////////////////////////////////////
function WMLdo($type, $name="", $label="", $gohref="", $body="") {
  global $wap_pagedata;
  $wap_pagedata .= "<do type=\"$type\"";
  if(chop($name)<>"") $wap_pagedata .= " name=\"$name\"";
  if(chop($label)<>"")  $wap_pagedata .= " label=\"$label\"";
  $wap_pagedata .= ">\n";
  if(chop($body)<>"") $wap_pagedata .= $body;
  if(chop($gohref)<>"") $wap_pagedata .= " <go href=\"$gohref\"/>\n";
  $wap_pagedata .= "</do>\n";
}

////////////////////////////////////////////////////////////////////
// Initialise a card.
////////////////////////////////////////////////////////////////////
function WMLCardInit($id, $newcontext="", $cardtitle="") {
  global $wap_pagedata;
  $wap_pagedata .= "<card id=\"$id\"";
  if(chop($newcontext)<>"") $wap_pagedata .= " newcontext=\"$newcontext\"";
  if(chop($cardtitle)<>"") $wap_pagedata .= " title=\"$cardtitle\"";
  $wap_pagedata .= ">\n";
}

////////////////////////////////////////////////////////////////////
// 
////////////////////////////////////////////////////////////////////
function WMLCardBody($body) {
  global $wap_pagedata;
  $wap_pagedata .= "$body\n";
}

////////////////////////////////////////////////////////////////////
// 
////////////////////////////////////////////////////////////////////
function WMLCardFinn() {
  global $wap_pagedata;
  $wap_pagedata .= "</card>\n";
}

////////////////////////////////////////////////////////////////////
// Start of a template.  Ignored for HTML
////////////////////////////////////////////////////////////////////
function WMLTemplateInit() {
  global $wap_pagedata;
  $wap_pagedata .= "<template>\n";
}

////////////////////////////////////////////////////////////////////
// End of a template.  Ignored for HTML
////////////////////////////////////////////////////////////////////
function WMLTemplateFinn() {
  global $wap_pagedata;
  $wap_pagedata .= "</template>\n";
}
?>
