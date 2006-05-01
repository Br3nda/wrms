<?php
function send_headers() {
  global $colors, $fontsizes, $fonts, $stylesheet, $error_message, $warn_message, $client_messages, $c;
  global $title, $style, $left_panel, $right_panel, $images, $tmnu, $settings, $session, $help_url;

  $now = time();
  // Header("Last-Modified: " . gmdate( "D, d M Y H:i:s T", $now) );
  $then = $now + 15;
  // Header("Expires: " . gmdate( "D, d M Y H:i:s T", $then) );
  // Header("Cache-Control: max-age=5, private");
  Header("Cache-Control: private");
  Header("Pragma: no-cache");

  if ( isset($c->page_title) ) $title = $c->page_title;

  // Standard headers included everywhere.
  echo "<!DOC"."TYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" \"http://www.w3.org/TR/REC-html40/loose.dtd\">\n";
  echo "<html>\n<head>\n<title>$title</title>\n";

  if ( !isset($stylesheet) ) $stylesheet = "main.css";
  echo '<link rel="stylesheet" type="text/css" href="'.$stylesheet.'" />' . "\n";
  echo '<script language="JavaScript" src="js/date-picker.js"></script>' . "\n";

  $fontsizes = array( "xx-small", "x-small", "small", "medium" );
  if ( is_object($settings) )
    $BaseFontsize = intval($settings->get('fontsize'));
  else
    $BaseFontsize = 11;
  if ( $BaseFontsize < 5 || $BaseFontsize > 25 ) $BaseFontsize = 10;

  for ( $i=0; $i < 6; $i++) {
    $fontsizes[$i] = sprintf( "%dpx", $BaseFontsize + (2 * $i));
  }

  // Style stuff
  echo "<style type=\"text/css\"><!--\n";
  $linkstyle = "{color: $colors[link1]; text-decoration:none; ";
  echo "A $linkstyle }\n";
  $borders = "border: solid $colors[blocksides] 1px; ";
  echo ".msglink $linkstyle }\n";

  echo ".menu $linkstyle font: small-caps bold $fontsizes[1] $fonts[1]; background: $colors[bg3]; color: $colors[fg3]; padding: 0px 1px 1px; margin: 0px 1px; }\n";
  echo "A.wu:hover { text-decoration: underline; color: #44cc44; }\n";
  echo "A.wu { text-decoration: underline; color: #cc4444; }\n";
  echo "A.block:hover, A.blockhead:hover { color: $colors[hv1]; }\n";
  echo "A:hover { color: $colors[hv1];  }\n";
  echo ".bmenu $linkstyle font: small-caps bold $fontsizes[1] $fonts[1]; background: $colors[bg3]; color: $colors[fg3]; padding: 0px 1px 1px; margin: 0px 1px; }
.block    {font: $fontsizes[1] $fonts[block], sans-serif; color: $colors[blockfront]; }
hr.block  {line-height: 9px; margin: 0px; padding: 0px; width: 130px; background: url(/$images/menuBreak.gif); }
img.block {height: 9px; margin: 0px; padding: 0px; width: 130px; clear: both }
td.sidebarleft { color: white; background-color: $colors[blockback]; }
.sml {font: $fontsizes[1] $fonts[narrow], sans-serif; }
.sbutton, .r $linkstyle font: small-caps bold $fontsizes[0] $fonts[1]; background: $colors[bg2]; color: $colors[fg2]; padding: 0px 1px 1px 1px; margin: 0px 1px; }
.sbutton:hover, .r:hover { background: $colors[bg2]; color: $colors[hv2]; }
.submit {text-decoration: none; font: small-caps bold $fontsizes[0] $fonts[1], sans-serif; background: $colors[bg2]; color: $colors[fg2]; padding: 0px 2px 1px 2px; margin: 0px 2px; border: thin outset;}
.submit:hover {text-decoration: none; font: small-caps bold $fontsizes[0] $fonts[1], sans-serif; background: $colors[bg2]; color: $colors[hv2]; padding: 0px 2px 1px 2px; margin: 0px 2px; border: thin inset;}
th.h3, td.h3  {font: bold $fontsizes[3] $fonts[0], sans-serif; color: $colors[fg3]; color: white; background-color: $colors[bg3];margin: 6px 0px 0px 0px; }\n";

  echo ".block { text-decoration: none; font: $fontsizes[1] $fonts[block], sans-serif; color: $colors[blockfront]; }\n";
  echo ".blockhead { text-decoration: none; font: $fontsizes[1] $fonts[block], sans-serif; color: $colors[blockfront]; font-weight: 700; }\n";

  echo "body, p, td, input {font: $fontsizes[1]  $fonts[0], sans-serif; color: $colors[fg1]; }
.error {font-family: $fonts[1], serif; font-weight: 700; color: white; background: red; }
.error h2 { font-size: $fontsizes[4]; }
.error h3 { font-size: $fontsizes[3]; }
.error h4 { font-size: $fontsizes[2]; }
.help   {font: italic $fontsizes[1] $fonts[help], serif; color: $colors[fghelp]; background: $colors[bghelp]; }
.blocka   {font: $fontsizes[1] $fonts[block], sans-serif; color: $colors[blockfront]; }
.blockhead  {font: $fontsizes[1] $fonts[block], sans-serif; font-weight: 700; color: $colors[blockfront]; }
.msgtitle   {font: bold $fontsizes[1] $fonts[1], sans-serif; font-weight: 700; color: $colors[blockfront]; background: $colors[blocktitle]; margin: 6px 0px 0px 0px; }
.msginfo    {font: $fontsizes[0] $fonts[1], sans-serif; margin: 0; text-align: right; color: $colors[fg2]; background: $colors[bg2]; }
.mand   {font: bold $fontsizes[0] $fonts[1], sans-serif; background: $colors[mand];}
.smb {font: $fontsizes[0] $fonts[narrow], sans-serif; color: $colors[fg1]; }
.row0 { background: $colors[row0]; color: $colors[link2]; }
.row1 { background: $colors[row1]; color: $colors[link2]; }
a.row0, a.row1 { color: $colors[link2]; }
.menu   {font: $fontsizes[1] $fonts[1], sans-serif; color: $colors[fg2]; background: $colors[bg1]; }
blockquote {font: italic $fontsizes[1]  $fonts[quote]; color: $colors[fg2]; }
input.sml, select.sml {font: $fontsizes[0] $fonts[0], sans-serif; }
textarea.sml { font: $fontsizes[0] $fonts[fixed], fixed; }
h1, .h1, th {font: bold $fontsizes[2]/$fontsizes[3] $fonts[0], sans-serif; color: $colors[link1]; }
h2, .h2 {font: normal $fontsizes[2] $fonts[0], sans-serif; color: $colors[link1]; }
h3, th.h3 {font: bold $fontsizes[1] $fonts[0], sans-serif; color: $colors[link1]; }
th.cols, th.rows, a.cols  {font: small-caps bold $fontsizes[1] $fonts[0], sans-serif; color: $colors[fg3];  background: $colors[bg3]; margin: 6px 0px 0px 0px; }
.cols  {font: small-caps bold $fontsizes[1] $fonts[0], sans-serif; color: $colors[fg3];  background: $colors[bg3]; }
.cols:hover  { color: $colors[hv2]; }\n";



  if ( (isset($error_message) && $error_message <> "") || (isset($warn_message) && $warn_message <> "") ) {
    echo ".error {font: bold $fontsizes[2] $fonts[0], sans-serif; color: $colors[fgerr]; background: $colors[bgerr]; padding: 10px; margin: 20px; }\n";
  }

  if ( function_exists("local_inline_styles") ) {
    local_inline_styles();
  }

  echo "--></style>\n";
  echo "<style type=\"text/css\" media=\"print\"><!--\n";
  echo ".noprint, #topbar, #searchbar, #top_menu { display: none; }\n";
  echo "--></style>\n";

  // Now start the body
  echo "</head>\n";
  echo "<body bgcolor=\"$colors[bg1]\" fgcolor=\"$colors[fg1]\" leftmargin=\"0\" marginheight=\"0\" marginwidth=\"0\" topmargin=\"0\" text=\"$colors[fg1]\" link=\"$colors[link1]\" vlink=\"$colors[link1]\" alink=\"$colors[link2]\" background=\"/$images/tanTile.gif\">\n";
  if ( ! isset($style) || "$style" != "stripped" ) {
    if ( function_exists("local_page_header") ) {
      local_page_header();
    }
    else {
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>
      <table width="100%" border="0" cellspacing="0" cellpadding="0" background="<?php echo $images; ?>/tanTile.gif">
        <tr>
          <td background="<?php echo $images; ?>/tanTile.gif" height="55"><img src="<?php echo $images; ?>/wrmsTOP.gif" width="700" height="60"></td>
        </tr>
      </table>
      <table width="100%" border="0" cellspacing="0" cellpadding="0" background="<?php echo $images; ?>/midTile.gif">
        <tr>
          <td width="40%">
            <table width="142" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td align="left"><img src="<?php echo $images; ?>/WRMSheader.gif" width="470" height="19"></td>
              </tr>
            </table>
          </td>
          <td width="28%"><font size=1>&nbsp;</font></td>
          <td width="32%" align="right"><a href="<?php echo $help_url; ?>"><img src="<?php echo $images; ?>/help.gif" width="101" height="19" border=0></a></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<?php
    }

    // The left hand sidebar.
    if ( $left_panel ) {
      echo "<table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr bgcolor=$colors[bg1]>\n";
      echo "<td width=\"10%\" valign=\"top\" class=\"noprint sidebarleft\">";
      if ( !isset($error_qry) || "$error_qry" == "" ) {
        include("sidebarleft.php");
      }
      echo "\n</td>\n";

      echo "<td valign=top width=\"" . ($right_panel ? "80" : ($left_panel ? "90" : "100")) . "%\">";
    }

    echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"7\" width=\"100%\">\n";
    echo "<tr><td>\n";
  } // if style not stripped

  if ( (isset($client_messages) && is_array($client_messages) && count($client_messages) > 0 ) || count($c->messages) > 0 ) {
    echo "<div id=\"messages\"><ul class=\"messages\">\n";
    foreach( $client_messages AS $i => $msg ) {
      // ##HelpTextKey## gets converted to a "/help.php?h=HelpTextKey" link
      $msg = preg_replace_callback("/##([^#]+)##/", "make_help_link", $msg);
      echo "<li class=\"messages\">$msg</li>\n";
    }
    foreach( $c->messages AS $i => $msg ) {
      // ##HelpTextKey## gets converted to a "/help.php?h=HelpTextKey" link
      $msg = preg_replace_callback("/##([^#]+)##/", "make_help_link", $msg);
      echo "<li class=\"messages\">$msg</li>\n";
    }
    echo "</ul></div>\n";
  }

  // The older style way to display errors / warnings
  if ( (isset($error_message) && $error_message <> "") || (isset($warn_message) && $warn_message <> "") ) {
    echo "<table border=\"0\" width=\"450\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"$colors[bgerr]\" fgcolor=\"$colors[fgerr]\" align=center><tr>\n";
    echo "<th class=error>$error_message$warn_message<th>\n</tr></table>\n<br clear=all>";
    if ( isset($error_message) && $error_message <> "" ) {
      include("footers.php");
      exit;
    }
  }

  if ( isset($tmnu) && is_object($tmnu) ) {
    $tmnu->LinkActiveSubMenus();
    if ( function_exists("local_menu_bar") ) {
      local_menu_bar($tmnu);
    }
    else {
      echo $tmnu->Render();
    }
  }
}
send_headers();
?>
