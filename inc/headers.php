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

  // Style sheet
  $style_sheets = false;
  if ( $style_sheets ) {
    if ( $agent == "moz4" )
      echo '<link rel="stylesheet" type="text/css" href="/wrmsmoz4.css">' . "\n";
    else
      echo '<link rel="stylesheet" type="text/css" href="/wrms.css">' . "\n" ;
  }
  else {
    echo "<style type=\"text/css\"><!--\n";
    $linkstyle = "{color: $colors[fg2]; text-decoration:none; ";
    echo "A $linkstyle }\n";
    $borders = "border: solid $colors[blocksides] 1px; ";
    echo ".msglink $linkstyle }\n";
    echo ".msgunmod $linkstyle; color: $colors[fgunmod]; }\n";

    if ( $agent == "moz4" ) {
      echo ".menu, .bmenu $linkstyle font: bold $fontsizes[1] $fonts[1]; text-decoration: underline; background: $colors[bg3]; color: $colors[fg3]; }
.block		{font: $fontsizes[1] $fonts[block], sans-serif; color: $colors[blockfront]; }
.sml {font: $fontsizes[0] $fonts[narrow], sans-serif; }
hr.block	{line-height: 0px; margin: -6px; padding: 0px 25px; width: 100px; }
td.sidebarleft { color: white; background-color: $colors[blockback]; }
th.h3, td.h3  {font: bold $fontsizes[3] $fonts[0], sans-serif; color: $colors[fg3]; color: white; background-color: $colors[blockback];margin: 6px 0px 0px 0px; }
.sbutton, .r $linkstyle font: bold $fontsizes[0] $fonts[1]; text-decoration: underline; background: $colors[bg3]; color: $colors[fg3]; vertical-align: top; }
.submit { font: small-caps bold $fontsizes[0] $fonts[1]; background: $colors[blocktitle]; color: #f0fff0; }\n";
    }
    else {
      echo ".menu $linkstyle font: small-caps bold $fontsizes[1] $fonts[1]; background: $colors[bg3]; color: $colors[fg3]; padding: 0px 1px 1px; margin: 0px 1px; }\n";
      echo "A.block:hover, A.blockhead:hover { color: yellow; }\n";
      echo "A:hover { color: black;  }\n";
      echo ".bmenu $linkstyle font: small-caps bold $fontsizes[1] $fonts[1]; background: $colors[bg3]; color: $colors[fg3]; padding: 0px 1px 1px; margin: 0px 1px; }
.block		{font: $fontsizes[1] $fonts[block], sans-serif; color: $colors[blockfront]; background-image: url(/images/page-tile2.jpg); }
hr.block	{line-height: 0px; margin: -6px; padding: 0px; width: 75%; text-align: center; }
td.sidebarleft { color: white; background-color: $colors[blockback];background-image: url(/images/page-tile2.jpg); height: 100%; }
.sml {font: $fontsizes[0] $fonts[narrow], sans-serif; height: 20px; }
.sbutton, .r $linkstyle font: small-caps bold $fontsizes[0] $fonts[1]; background: $colors[blocktitle]; color: $colors[fg3]; padding: 0px 1px 1px 1px; margin: 0px 1px; }
.sbutton:hover, .r:hover { background: $colors[bg3]; color: yellow; }
.submit {text-decoration: none; font: small-caps bold xx-small verdana; background: $colors[blocktitle]; color: #f0fff0; padding: 0px 1px 1px 1px; margin: 0px 1px; border: thin outset;}
.submit:hover {text-decoration: none; font: small-caps bold xx-small verdana; background: $colors[blocktitle]; color: yellow; padding: 0px 1px 1px 1px; margin: 0px 1px; border: thin inset;}
th.h3, td.h3  {font: bold $fontsizes[3] $fonts[0], sans-serif; color: $colors[fg3]; color: white; background-color: $colors[blockback];background-image: url(/images/page-tile2.jpg); margin: 6px 0px 0px 0px; }\n";
    }

    echo ".block { text-decoration: none; font: $fontsizes[1] $fonts[block], sans-serif; color: " . $colors["blockfront"] . "; }\n";
    echo ".blockhead { text-decoration: none; font: $fontsizes[1] $fonts[block], sans-serif; color: " . $colors["blockfront"] . "; font-weight: 700; }\n";

    echo "p, td {font: $fontsizes[1]  $fonts[0], sans-serif; color: $colors[fg1]; }
.help		{font: italic $fontsizes[1] $fonts[help], serif; color: $colors[fghelp]; background: $colors[bghelp]; }
.blocka		{font: $fontsizes[1] $fonts[block], sans-serif; color: $colors[blockfront]; }
.blockhead	{font: $fontsizes[1] $fonts[block], sans-serif; font-weight: 700; color: $colors[blockfront]; }
.msgtitle		{font: bold $fontsizes[1] $fonts[1], sans-serif; font-weight: 700; color: $colors[blockfront]; background: $colors[blocktitle]; margin: 6px 0px 0px 0px; }
.msginfo		{font: $fontsizes[0] $fonts[1], sans-serif; margin: 0; text-align: right; color: $colors[fg2]; background: $colors[bg4]; }
.msginfmod	{font: $fontsizes[0] $fonts[1], sans-serif; margin: 0; text-align: right; color: $colors[fgunmod]; background: $colors[bgunmod]; }
.mand		{font: bold $fontsizes[0] $fonts[1], sans-serif; background: $colors[9];}
.smb		{font: bold $fontsizes[0] $fonts[narrow], sans-serif; }
.row0 { background: $colors[row0]; }
.row1 { background: $colors[row1]; }
.menu		{font: x-small tahoma, sans-serif; color: $colors[fg2]; background: $colors[bg1]; }
blockquote {font: italic $fontsizes[1]  $fonts[quote]; color: $colors[fg2]; }
input.sml, select.sml {font: $fontsizes[0] $fonts[0], sans-serif; }
h1, th {font: bold 18px/20px $fonts[0], sans-serif; color: " . $colors[fg2] . "; }
h2, th.h2 {font: normal $fontsizes[2] $fonts[0], sans-serif; color: " . $colors[fg2] . "; }
h3, th.cols, th.rows  {font: bold $fontsizes[1] $fonts[0], sans-serif; color: $colors[fg2]; margin: 6px 0px 0px 0px; }\n";

// table, body { background-image: url(/images/page-tile2.jpg); }

    if ( (isset($error_message) && $error_message <> "") || (isset($warn_message) && $warn_message <> "") ) {
      echo ".error {font: bold $fontsizes[2] $fonts[0], sans-serif; color: $colors[fgerr]; background: $colors[bgerr]; padding: 10px; margin: 20px; }\n";
    }

    echo "--></style>\n";
  } // if not style sheets


  // Now start the body
  echo "</head>\n";
  echo "<body bgcolor=\"$colors[bg1]\" fgcolor=\"$colors[fg1]\" leftmargin=\"0\" marginheight=\"0\" marginwidth=\"0\" topmargin=\"0\" text=\"$colors[fg1]\" link=\"$colors[fg2]\" vlink=\"$colors[fg2]\" alink=\"$colors[fg2]\">\n";
  echo "<basefont face=\"$fonts[0], sans-serif\" size=\"2\" color=\"$colors[fg1]\">\n";

  include("inc/menuhead.php");

  // The left hand sidebar.
  if ( $left_panel ) {
    echo "<table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr>\n";
    echo "<td width=\"10%\" valign=\"top\" class=sidebarleft>";
    if ( "$error_qry" == "" ) {
      include("inc/sidebarleft.php");
    }
    echo "\n</td>\n";

    echo "<td valign=top width=\"" . ($right_panel ? "80" : ($left_panel ? "90" : "100")) . "%\">";
  }

  echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
  echo "<tr><td colspan=2 bgcolor=\"#ffffff\"><img src=\"images/clear.gif\" width=\"500\" height=\"1\" border=\"0\" alt=\" \"></td></tr>\n";
  echo "<tr bgcolor=$colors[blocktitle]>\n";
  echo "<th class=blockhead align=left width=\"95%\">&nbsp;" . ("$forum_title" <> "" ? $forum_title : $title) . "</th>\n";

  if ( !isset($form) ) $form = str_replace(".php", "", "$SCRIPT_NAME");
  $help_uri = "form.php?f=help&topic=$form";
  echo "<td class=blockhead align=right width=\"5%\"><a href=\"$help_uri\" class=sbutton>&nbsp;HELP".( "$session->help" == "t" ?"&nbsp;OFF":"")."&nbsp;</a></td>\n";
  echo "</tr>\n</table>\n";

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