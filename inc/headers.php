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
      echo ".menu, .bmenu $linkstyle font: bold $fontsizes[1] $fonts[1]; text-decoration: underline; background: $colors[bg3]; color: $colors[fg3]; }\n";
      echo ".sbutton, .r $linkstyle font: bold $fontsizes[0] $fonts[1]; text-decoration: underline; background: $colors[bg3]; color: $colors[fg3]; vertical-align: top; }\n";
    }
    else {
      echo ".menu $linkstyle font: small-caps bold $fontsizes[1] $fonts[1]; background: $colors[bg3]; color: $colors[fg3]; padding: 0px 1px 1px; margin: 0px 1px; }\n";
      echo ".bmenu $linkstyle font: small-caps bold $fontsizes[1] $fonts[1]; background: $colors[bg3]; color: $colors[fg3]; padding: 0px 1px 1px; margin: 0px 1px; }\n";
      echo ".sbutton, .r $linkstyle font: small-caps bold $fontsizes[0] $fonts[1]; background: $colors[bg3]; color: $colors[fg3]; padding: 0px 1px 1px 1px; margin: 0px 1px; }\n";
      echo ".submit {text-decoration: none; font: small-caps bold xx-small verdana; background: #886c50; color: #f0fff0; padding: 0px 1px 1px 1px; margin: 0px 1px; border: thin outset;}\n";
      echo ".submit:hover {text-decoration: none; font: small-caps bold xx-small verdana; background: #886c50; color: yellow; padding: 0px 1px 1px 1px; margin: 0px 1px; border: thin inset;}\n";
    }

    echo ".block { text-decoration: none; font: $fontsizes[1] $fonts[block], sans-serif; color: " . $colors["blockfront"] . "; }\n";
    echo ".blockhead { text-decoration: none; font: $fontsizes[1] $fonts[block], sans-serif; color: " . $colors["blockfront"] . "; font-weight: 700; }\n";

    echo "p, td {font: $fontsizes[1]  $fonts[0], sans-serif; color: $colors[fg1]; }
.sml {font: $fontsizes[0] $fonts[0], sans-serif; }
.help		{font: italic $fontsizes[1] $fonts[help], serif; color: $colors[fghelp]; background: $colors[bghelp]; }
.block		{font: $fontsizes[1] $fonts[block], sans-serif; color: $colors[blockfront]; }
.blocka		{font: $fontsizes[1] $fonts[block], sans-serif; color: $colors[blockfront]; }
.blockhead	{font: $fontsizes[1] $fonts[block], sans-serif; font-weight: 700; color: $colors[blockfront]; }
.msgtitle		{font: bold $fontsizes[1] $fonts[1], sans-serif; font-weight: 700; color: $colors[blockfront]; background: $colors[blocktitle]; margin: 6px 0px 0px 0px; }
.msginfo		{font: $fontsizes[0] $fonts[1], sans-serif; margin: 0; text-align: right; color: $colors[fg2]; background: $colors[bg4]; }
.msginfmod	{font: $fontsizes[0] $fonts[1], sans-serif; margin: 0; text-align: right; color: $colors[fgunmod]; background: $colors[bgunmod]; }
.mand		{font: bold x-small tahoma, sans-serif; background: $colors[9];}
.smb		{font: bold x-small tahoma, sans-serif; }
.menu		{font: x-small tahoma, sans-serif; color: $colors[fg2]; background: $colors[bg2]; }
blockquote {font: italic $fontsizes[1]  $fonts[quote]; color: $colors[fg2]; }
input.sml, select.sml {font: $fontsizes[0] $fonts[0], sans-serif; }
h1, th {font: bold 18px/20px $fonts[0], sans-serif; color: " . $colors[fg2] . "; }
h2, th.h2 {font: normal $fontsizes[2] $fonts[0], sans-serif; color: " . $colors[fg2] . "; }
h3, th.h3, th.cols, th.rows  {font: bold $fontsizes[1] $fonts[0], sans-serif; color: $colors[fg2]; margin: 6px 0px 0px 0px; }\n";

    if ( (isset($error_message) && $error_message <> "") || (isset($warn_message) && $warn_message <> "") ) {
      echo ".error {font: bold $fontsizes[2] $fonts[0], sans-serif; color: $colors[fgerr]; background: $colors[bgerr]; padding: 10px; margin: 20px; }\n";
    }

    echo "--></style>\n";
  } // if not style sheets


  // Now start the body
  echo "</head>\n";
  echo "<body bgcolor=\"$colors[bg1]\" fgcolor=\"$colors[fg1]\" leftmargin=\"0\" marginheight=\"0\" marginwidth=\"0\" topmargin=\"0\" text=\"$colors[fg1]\" link=\"$colors[fg2]\" vlink=\"$colors[fg2]\" alink=\"$colors[fg2]\" background=\"$images/page-tile.jpg\">\n";
  echo "<basefont face=\"$fonts[0], sans-serif\" size=\"2\" color=\"$colors[fg1]\">\n";

  include("inc/menuhead.php");

  // The left hand sidebar.
  echo "<table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr>\n";
  echo "<td width=\"10%\" bgcolor=\"$colors[blockback]\" valign=\"top\">";
  if ( "$error_qry" == "" ) {
    include("inc/sidebarleft.php");
  }
  echo "\n</td>\n";

  echo "<td valign=top width=\"" . ($right_panel ? "80" : "90") . "%\">";

  echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
  echo "<tr><td colspan=2 bgcolor=\"#ffffff\"><img src=\"images/clear.gif\" width=\"500\" height=\"1\" border=\"0\" alt=\" \"></td></tr>\n";
  echo "<tr bgcolor=$colors[blocktitle]>\n";
  echo "<th class=blockhead align=left width=\"95%\">&nbsp;" . ("$forum_title" <> "" ? $forum_title : $title) . "</th>\n";

  if ( !isset($form) ) $form = str_replace(".php", "", "$SCRIPT_NAME");
  $help_uri = "form.php?f=help&topic=$form";
  echo "<td class=blockhead align=right width=\"5%\"><a href=\"$help_uri\" class=sbutton>&nbsp;HELP".( "$session->help" == "t" ?"&nbsp;OFF":"")."&nbsp;</a></td>\n";
  echo "</tr>\n</table>";

  echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"7\" width=\"100%\" height=\"100%\">\n";
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
