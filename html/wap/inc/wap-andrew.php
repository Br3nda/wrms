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

$wap_browser = !( eregi("(mozilla)|(opera)|(lynx)|(msie)", $HTTP_USER_AGENT) );
$wap_buttons = array( "Options", "", "");
$wap_pagedata = "";
$leftwidth = 50;
$colwidth = 216;
$rightwidth = 330;

// This function performs the basic initialisation of the wml page - each separate page
// needs this stuff at the head of the page so that .php file get sent with wml headers

////////////////////////////////////////////////////////////////////
// Initialises the WAP page, sending headers and
// setting up the start of the page.
////////////////////////////////////////////////////////////////////
function WMLinit() {
  global $wap_pagedata, $wap_browser;
  global $wap_buttons, $HTTP_USER_AGENT, $colors, $fonts
  global $leftwidth, $colwidth, $rightwidth;

//  Header("Last-Modified: Tue, 28 Mar 2000 23:57:41 GMT");
  $now = time();
  $then = $now + 300;
  Header("Expires: " . gmdate( "D, d M Y H:i:s T", $then) );
  Header("Last-Modified: " . gmdate( "D, d M Y H:i:s T", $now) );
  Header("Cache-Control: private");
  if ( $GLOBALS['wap_browser'] ) {
    Header("Accept-Ranges: none");
    Header("Content-type:  text/vnd.wap.wml");
    $wap_pagedata = "<?xml version=\"1.0\"?>";
    $wap_pagedata .= "<!DOCTYPE wml PUBLIC \"-//WAPFORUM//DTD WML 1.1//EN\" ";
    $wap_pagedata .= "\"http://www.wapforum.org/DTD/wml_1.1.xml\">\n";
    $wap_pagedata .= "<wml>\n";
  }
  else {
    Header("Content-type:  text/html");
    echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 TRANSITIONAL//EN\">\n";
    echo "<html>\n<head>\n";
    echo "<title>WAPNews: NewsRoom for people on the move...</title>\n";
    include("../inc/styledef.php");
    echo "</head>\n<body bgcolor=$colors[0]>\n"; ?>
</center>
<table BORDER="0" WIDTH="100%" CELLSPACING="0" CELLPADDING="0" BGCOLOR="<?php echo $colors[1];?>">
<tr>
	<td class=menu><a href=http://newsroom.catalyst.net.nz/index.php><img src="images/NewsRoom.gif" border="0" alt="<?php echo "$system_name"; ?>" width="200" height="25"></a></td>
	<td class=menu><font SIZE=1 COLOR=<?php echo $colors[3]; ?>>
		<a href="http://newsroom.catalyst.net.nz/products.php">Products and Services</a> | 
		<a href="http://newsroom.catalyst.net.nz/about.php">About </a> | 
		<a href="http://newsroom.catalyst.net.nz/editorial.php">Editorial Policy</a> | 
		<a href="http://newsroom.catalyst.net.nz/copyright.php">Copyright</a> | 
		<a href="http://newsroom.catalyst.net.nz/links.php">Links</a>
		&nbsp;</font>
		</TD>
	</tr>
	<tr>
		<td COLSPAN=2 HEIGHT="3" BGCOLOR="<?php echo $colors[3]; ?>"><img border=0 src=images/clear.gif width="1" height="3" border="0" alt=" "></td>
	</tr>
</table>
</center>
<?php
    $tablewidth = $leftwidth + $colwidth + $rightwidth;
    echo "<table width=$tablewidth cellspacing=0 cellpadding=0 border=0 bgcolor=" . $colors[0] . " align=left>\n";

    // A row to hold the top of the phone
    echo "<tr>\n<td width=$leftwidth valign=bottom>&nbsp;</td>\n";
    echo "<td bgcolor=#6368aa width=$colwidth valign=top align=center><img src=images/ph-top.gif alt=\"Top of Phone\" border=0 hspace=0 vspace=0 width=$colwidth height=84></td>\n";
    // Now a cell for the message from our sponsor...
    echo "<td bgcolor=$colors[0] align=right valign=top width=$rightwidth rowspan=5>";
    echo "<font size=96pt><i><span style=\" font: bold italic 96pt $fonts[0], sans-serif; \">W@P</span></i></font>\n";
    echo "<br>&nbsp;<br><table align=right border=0 cellspacing=2 cellpadding=0><tr><td valign=top colspan=2 align=right>W@Plication development by...</td>\n";
    echo "<td><img border=0 src=images/clear.gif alt=\" \" width=70 height=2></td>\n</tr>\n";
    echo "<tr>\n<td>&nbsp;</td><td valign=bottom colspan=2 align=right>";
    echo "<a href=\"http://www.catalyst.net.nz\" target=_new>";
    echo "<img src=\"images/cat-it-sml.gif\" Border=0 alt=\"Catalyst.Net Limited\" width=130 height=54></a>";
    echo "<br><img border=0 src=images/clear.gif alt=\" \" width=150 height=2></td></tr></table>";
?>
<br clear=all>&nbsp;<br>&nbsp;<div align=left>
<table cellspacing=5 cellpadding=5 border=0 valign=middle align=center width=95%><tr><td>
<p>The bit on the left is supposed to look 'roughly' like the WAP site might on a
real telephone.
<p>Of course, if you have a real phone the experience is somewhat different,
but the site here can also be useful to help you learn to navigate the W@P
web, which can take a little time to master because of the screen and keyboard
limitations of the W@P devices themselves.
</div>
</td></tr></table>
<?php
    echo "<BR><img border=0 src=images/clear.gif alt=\" \" width=$rightwidth height=2></td>\n";
    echo "</td>\n</tr>\n";

    // Finally the actual row to hold the content...
    echo "<tr>\n<td width=$leftwidth valign=top>&nbsp;</td>\n";
    echo "<td bgcolor=#b9e3bf width=$colwidth valign=top>\n";
  }

}

////////////////////////////////////////////////////////////////////
// Finishes the job.  Calculates the length and
// sends the stuff.
////////////////////////////////////////////////////////////////////
function WMLfinn() {
  global $wap_pagedata, $wap_browser;
  global $wap_buttons, $HTTP_USER_AGENT, $colors, $fonts;
  global $leftwidth, $colwidth, $rightwidth;

  if ( $wap_browser ) {
    $wap_pagedata .= "</wml>\n";
    Header( "Content-Length: " . strlen($wap_pagedata) );
    echo $wap_pagedata;
  }
  else {
    echo "</td>\n";   // That closes the data cell

    // A row for the bottom of "phone" screen
    echo "<tr>\n<td width=$leftwidth valign=top>&nbsp;</td>\n";
    echo "<td bgcolor=#6368aa width=$colwidth valign=top align=center><img src=images/ph-bottom.gif width=$colwidth height=20 hspace=0 vspace=0 alt=\"Bottom of Phone\" border=0></td>\n</tr>\n";

    // A row for the "phone" buttons...
    echo "<tr>\n<td width=$leftwidth valign=top>&nbsp;<BR><img border=0 src=images/clear.gif alt=\" \" width=$leftwidth height=2></td>\n";
    echo "<td bgcolor=#6368aa width=$colwidth>\n";
    echo "<table width=$colwidth border=0 cellspacing=4 cellpadding=0 bgcolor=#f4edd4 align=center><tr>\n";
    $cellwidth = floor( ($colwidth - 8) / 3) ;
    for ( $i=0; $i<3; $i++ )
      echo "<td width=$cellwidth align=center valign=top><h4>$wap_buttons[$i]</td>\n";
    echo "</tr></table>\n</td>";
    echo "</tr>\n";

    // A final row so the RHS can grow further...
    echo "<tr>\n<td width=$leftwidth valign=bottom>&nbsp;<BR><img border=0 src=images/clear.gif alt=\" \" width=$leftwidth height=2></td>\n";
?>
<td width=$colwidth>
&nbsp;<p align=center>The buttons at the bottom attempt to emulate the buttons shown on
your phone, although the [Back] button on the phone is more equivalent 
to the one built into your browser - it's a bit harder to emulate that in an 
HTML link.
<BR>&nbsp;<BR>&nbsp;<BR>&nbsp;
</td>
<?php
    echo "</tr>\n";

    echo "</table>\n</body>\n</html>\n";
  }
}

////////////////////////////////////////////////////////////////////
// 
////////////////////////////////////////////////////////////////////
function WMLdo($type, $name="", $label="", $gohref="", $body="") {
  global $wap_browser, $wap_buttons, $HTTP_REFERER;
  global $wap_pagedata;

  if ( $wap_browser ) {
    $wap_pagedata .= "<do type=\"$type\"";
    if(chop($name)<>"") $wap_pagedata .= " name=\"$name\"";
    if(chop($label)<>"")  $wap_pagedata .= " label=\"$label\"";
    $wap_pagedata .= ">\n";

    if(chop($body)<>"") $wap_pagedata .= $body;
    if(chop($gohref)<>"") $wap_pagedata .= " <go href=\"$gohref\"/>\n";
    $wap_pagedata .= "</do>\n";
  }
  else {
    $button = 0;
    if ( "$gohref" != "" ) $button = 1;
    else if ( $body == "<prev/>" ) {
      $gohref = $HTTP_REFERER;
      $label = "Back";
      $button = 2;
    }
    $wap_buttons[$button] .= "<a class=r href=\"$gohref\">$label</a><br>\n";
  }
}

////////////////////////////////////////////////////////////////////
// Initialise a card.
////////////////////////////////////////////////////////////////////
function WMLCardInit($id, $newcontext="", $cardtitle="") {
  global $wap_browser;
  global $wap_pagedata;
  if ( $wap_browser ) {
    $wap_pagedata .= "<card id=\"$id\"";
    if(chop($newcontext)<>"") $wap_pagedata .= " newcontext=\"$newcontext\"";
    if(chop($cardtitle)<>"") $wap_pagedata .= " title=\"$cardtitle\"";
    $wap_pagedata .= ">\n";
  }
  else {
    echo "<table cellpadding=5 cellspacing=0 border=0 width=100%><tr>\n";
    echo "<td bgcolor=#6368aa>&nbsp;</td>\n";
    echo "<TD>\n<h4 align=center>WapNews: $cardtitle</h4>\n";
  }
}

////////////////////////////////////////////////////////////////////
// 
////////////////////////////////////////////////////////////////////
function WMLCardBody($body) {
  global $wap_browser, $wap_buttons, $HTTP_USER_AGENT, $colors, $fonts;
  global $wap_pagedata;

  if ( ! $wap_browser ) {
    echo $body . "\n";
  }
  else {
    // Show Newsroom logo, then the body...
    $wap_pagedata .= "<p><img alt=\"W@PNews\" src=\"images/wapnews_w.wbmp\"/></p>\n$body\n";
  }
}

////////////////////////////////////////////////////////////////////
// 
////////////////////////////////////////////////////////////////////
function WMLCardFinn() {
  global $wap_pagedata, $wap_browser;

  if ( $wap_browser ) {
    $wap_pagedata .= "</card>\n";
  }
  else {
    echo "</td>\n";
    echo "<td bgcolor=#6368aa>&nbsp;</td>\n";
    echo "</tr></table>\n";
  }

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

////////////////////////////////////////////////////////////////////
// This returns a line break which is appropriate for WML or for
// a standard HTML browser.
////////////////////////////////////////////////////////////////////
function WMLLineBreak() {

  $result = "<br";
  if ( $GLOBALS['wap_browser'] ) $result .= "/";
  $result .= ">\n";
  return $result;
}

?>
