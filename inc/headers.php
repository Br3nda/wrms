<?php
  $now = time();
  Header("Last-Modified: " . gmdate( "D, d M Y H:i:s T", $now) );
  $then = $now + 15;
  Header("Expires: " . gmdate( "D, d M Y H:i:s T", $then) );
  Header("Cache-Control: max-age=5, private");

  // Standard headers included everywhere.
  echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" \"http://www.w3.org/TR/REC-html40/loose.dtd\">\n";
  echo "<html>\n<head>\n<title>$title</title>\n";

  echo '<script language="JavaScript" src="js/date-picker.js"></script>' . "\n";

  $BaseFontsize = intval($settings->get('fontsize'));
  if ( $BaseFontsize < 5 || $BaseFontsize > 25 ) $BaseFontsize = 10;

  for ( $i=0; $i < 6; $i++) {
    $fontsizes[$i] = sprintf( "%dpx", $BaseFontsize + (2 * $i));
  }


  // Style stuff
  // Remember: The user-chosen font sizes won't work if  you use a style sheet, of course, in which case you should
  // at least try and have a style sheet for NS4.x, Mozilla 5+, IE 4 and IE5+
  $style_sheets = false;
  if ( $style_sheets ) {
    if ( $agent == "moz4" )
      echo '<link rel="stylesheet" type="text/css" href="/wrmsmoz4.css">' . "\n";
    else
      echo '<link rel="stylesheet" type="text/css" href="/wrms.css">' . "\n" ;
  }
  else {
    echo "<style type=\"text/css\"><!--\n";
    $linkstyle = "{color: $colors[link1]; text-decoration:none; ";
    echo "A $linkstyle }\n";
    $borders = "border: solid $colors[blocksides] 1px; ";
    echo ".msglink $linkstyle }\n";
    echo ".msgunmod $linkstyle; color: $colors[fgunmod]; }\n";

    if ( $agent == "moz4" ) {
      echo ".menu, .bmenu $linkstyle font: bold $fontsizes[1] $fonts[1]; text-decoration: underline; background: $colors[bg3]; color: $colors[fg3]; }
.block		{font: $fontsizes[1] $fonts[block], sans-serif; color: $colors[blockfront]; }
.sml {font: $fontsizes[1] $fonts[narrow], sans-serif; }
hr.block	{line-height: 0px; margin: -6px; padding: 0px 25px; width: 100px; }
td.sidebarleft { color: white; background-color: $colors[blockback]; }
th.h3, td.h3  {font: bold $fontsizes[3] $fonts[0], sans-serif; color: $colors[fg3]; background-color: $colors[blockback];margin: 6px 0px 0px 0px; }
.sbutton, .r $linkstyle font: bold $fontsizes[0] $fonts[1]; text-decoration: underline; background: $colors[bg3]; color: $colors[fg3]; vertical-align: top; }
.submit { font: small-caps bold $fontsizes[0] $fonts[1]; background: $colors[blocktitle]; color: #f0fff0; }\n";
    }
    else {
      echo ".menu $linkstyle font: small-caps bold $fontsizes[1] $fonts[1]; background: $colors[bg3]; color: $colors[fg3]; padding: 0px 1px 1px; margin: 0px 1px; }\n";
      echo "A.block:hover, A.blockhead:hover { color: $colors[hv1]; }\n";
      echo "A:hover { color: $colors[hv1];  }\n";
      echo ".bmenu $linkstyle font: small-caps bold $fontsizes[1] $fonts[1]; background: $colors[bg3]; color: $colors[fg3]; padding: 0px 1px 1px; margin: 0px 1px; }
.block		{font: $fontsizes[1] $fonts[block], sans-serif; color: $colors[blockfront]; }
hr.block	{line-height: 9px; margin: 0px; padding: 0px; width: 130px; image: url(images/menuBreak.gif); }
img.block	{height: 9px; margin: 0px; padding: 0px; width: 130px; clear: both }
td.sidebarleft { color: white; background-color: $colors[blockback]; }
.sml {font: $fontsizes[1] $fonts[narrow], sans-serif; }
.sbutton, .r $linkstyle font: small-caps bold $fontsizes[0] $fonts[1]; background: $colors[bg2]; color: $colors[fg2]; padding: 0px 1px 1px 1px; margin: 0px 1px; }
.sbutton:hover, .r:hover { background: $colors[bg2]; color: $colors[hv2]; }
.submit {text-decoration: none; font: small-caps bold $fontsizes[0] $fonts[1], sans-serif; background: $colors[bg2]; color: #f0fff0; padding: 0px 1px 1px 1px; margin: 0px 1px; border: thin outset;}
.submit:hover {text-decoration: none; font: small-caps bold $fontsizes[0] $fonts[1], sans-serif; background: $colors[bg2]; color: $colors[hv2]; padding: 0px 1px 1px 1px; margin: 0px 1px; border: thin inset;}
th.h3, td.h3  {font: bold $fontsizes[3] $fonts[0], sans-serif; color: $colors[fg3]; color: white; background-color: $colors[bg3];margin: 6px 0px 0px 0px; }\n";
    }

    echo ".block { text-decoration: none; font: $fontsizes[1] $fonts[block], sans-serif; color: " . $colors["blockfront"] . "; }\n";
    echo ".blockhead { text-decoration: none; font: $fontsizes[1] $fonts[block], sans-serif; color: " . $colors["blockfront"] . "; font-weight: 700; }\n";

// body { font: $fontsizes[1]  $fonts[0], sans-serif; background: $colors[bg1]; color=$colors[fg1]; }
    echo "body, p, td, input {font: $fontsizes[1]  $fonts[0], sans-serif; color: $colors[fg1]; }
.help		{font: italic $fontsizes[1] $fonts[help], serif; color: $colors[fghelp]; background: $colors[bghelp]; }
.blocka		{font: $fontsizes[1] $fonts[block], sans-serif; color: $colors[blockfront]; }
.blockhead	{font: $fontsizes[1] $fonts[block], sans-serif; font-weight: 700; color: $colors[blockfront]; }
.msgtitle		{font: bold $fontsizes[1] $fonts[1], sans-serif; font-weight: 700; color: $colors[blockfront]; background: $colors[blocktitle]; margin: 6px 0px 0px 0px; }
.msginfo		{font: $fontsizes[0] $fonts[1], sans-serif; margin: 0; text-align: right; color: $colors[fg2]; background: $colors[bg2]; }
.msginfmod	{font: $fontsizes[0] $fonts[1], sans-serif; margin: 0; text-align: right; color: $colors[fgunmod]; background: $colors[bgunmod]; }
.mand		{font: bold $fontsizes[0] $fonts[1], sans-serif; background: $colors[9];}
.smb		{font: bold $fontsizes[0] $fonts[narrow], sans-serif; }
.row0 { background: $colors[row0]; color: $colors[link2]; }
.row1 { background: $colors[row1]; color: $colors[link2]; }
a.row0, a.row1 { color: $colors[link2]; }
.menu		{font: $fontsizes[1] $fonts[1], sans-serif; color: $colors[fg2]; background: $colors[bg1]; }
blockquote {font: italic $fontsizes[1]  $fonts[quote]; color: $colors[fg2]; }
input.sml, select.sml {font: $fontsizes[0] $fonts[0], sans-serif; }
textarea.sml { font: $fontsizes[0] $fonts[fixed], fixed; }
h1, .h1, th {font: bold $fontsizes[2]/$fontsizes[3] $fonts[0], sans-serif; color: " . $colors[link1] . "; }
h2, .h2 {font: normal $fontsizes[2] $fonts[0], sans-serif; color: " . $colors[link1] . "; }
h3, th.h3 {font: bold $fontsizes[1] $fonts[0], sans-serif; color: " . $colors[link1] . "; }
th.cols, th.rows, a.cols  {font: small-caps bold $fontsizes[1] $fonts[0], sans-serif; color: $colors[fg3];  background: $colors[bg3]; margin: 6px 0px 0px 0px; }
.cols  {font: small-caps bold $fontsizes[1] $fonts[0], sans-serif; color: $colors[fg3];  background: $colors[bg3]; }
.cols:hover  { color: $colors[hv2]; }\n";

// table, body { background-image: url(/images/page-tile2.jpg); }

    if ( (isset($error_message) && $error_message <> "") || (isset($warn_message) && $warn_message <> "") ) {
      echo ".error {font: bold $fontsizes[2] $fonts[0], sans-serif; color: $colors[fgerr]; background: $colors[bgerr]; padding: 10px; margin: 20px; }\n";
    }

    echo "--></style>\n";
  } // if not style sheets


  // Now start the body
  echo "</head>\n";
  echo "<body bgcolor=\"$colors[bg1]\" fgcolor=\"$colors[fg1]\" leftmargin=\"0\" marginheight=\"0\" marginwidth=\"0\" topmargin=\"0\" text=\"$colors[fg1]\" link=\"$colors[link1]\" vlink=\"$colors[link1]\" alink=\"$colors[link2]\" background=\"images/tanTile.gif\">\n";
//  echo "<basefont face=\"$fonts[0], sans-serif\" size=\"2\" color=\"$colors[fg1]\">\n";
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>
      <table width="100%" border="0" cellspacing="0" cellpadding="0" background="images/tanTile.gif">
        <tr>
          <td background="images/tanTile.gif" height="55"><img src="images/wrmsTOP.gif" width="700" height="60"></td>
        </tr>
      </table>
      <table width="100%" border="0" cellspacing="0" cellpadding="0" background="images/midTile.gif">
        <tr>
          <td width="40%">
            <table width="142" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td align="left"><img src="images/WRMSheader.gif" width="470" height="19"></td>
              </tr>
            </table>
          </td>
          <td width="28%"><font size=1>&nbsp;</font></td>
          <td width="32%" align="right"><a href=/help.php><img src="images/help.gif" width="101" height="19" border=0></a></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<?php

  // The left hand sidebar.
  if ( $left_panel ) {
    echo "<table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr bgcolor=$colors[bg1]>\n";
    echo "<td width=\"10%\" valign=\"top\" class=sidebarleft>";
    if ( "$error_qry" == "" ) {
      include("inc/sidebarleft.php");
    }
    echo "\n</td>\n";

    echo "<td valign=top width=\"" . ($right_panel ? "80" : ($left_panel ? "90" : "100")) . "%\">";
  }

  echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"7\" width=\"100%\">\n";
  echo "<tr><td>\n";


  // Display errors / Warnings
  if ( (isset($error_message) && $error_message <> "") || (isset($warn_message) && $warn_message <> "") ) {
    echo "<table border=\"0\" width=\"450\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"$colors[bgerr]\" fgcolor=\"$colors[fgerr]\" align=center><tr>\n";
    echo "<th class=error>$error_message$warn_message<th>\n</tr></table>\n<br clear=all>";
    if ( isset($error_message) && $error_message <> "" ) {
      include("$base_dir/inc/footers.php");
      exit;
    }
  }

?>