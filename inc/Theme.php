<?php

/////////////////////////////////////////////////////////////
//   C L A S S   F O R   T H E M E   H A N D L I N G       //
/////////////////////////////////////////////////////////////
class Theme {
  var $scale;     // The point size of normal text
  var $name;      // The name of the underlying theme to use
  var $theme;     // The underlying theme object that does the work
  var $colors;    // An array of the colors to use
  var $fonts;     // Fonts to use
  var $fontsizes; // Sizes for the fonts

  function Theme( $colors, $fonts, $scale, $name = "default" ) {
    $this->scale = ( isset($scale) ? $scale : 11 );
    if ( $this->scale < 5 || $this->scale > 30 ) $this->scale = 11;
    $this->name = $name;

    $this->colors = $colors;
    $this->fonts = $fonts;
    for ( $i=0; $i < 6; $i++) {
      $this->fontsizes[$i] = sprintf( "%dpx", $this->scale + (2 * $i));
    }

  }

  function HTML_Header( $title ) {
    Header("Last-Modified: " . gmdate( "D, d M Y H:i:s T", time()) );
    Header("Pragma: no-cache");

    // Standard headers included everywhere.
    echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" \"http://www.w3.org/TR/REC-html40/loose.dtd\">\n";
    echo "<html>\n<head>\n<title>$title</title>\n";

    echo '<link rel="stylesheet" type="text/css" href="/themes/'.$this->name.'.css" />' . "\n";
    echo '<script language="JavaScript" src="js/date-picker.js"></script>' . "\n";


    // Style stuff
    echo "<style type=\"text/css\"><!--\n";
    $linkstyle = "{color: $this->colors[link1]; text-decoration:none; ";
    echo "A $linkstyle }\n";
    $borders = "border: solid $this->colors[blocksides] 1px; ";
    echo ".msglink $linkstyle }\n";

    if ( $agent == "moz4" ) {
      echo ".menu, .bmenu $linkstyle font: bold $this->fontsizes[1] $this->fonts[1]; text-decoration: underline; background: $this->colors[bg3]; color: $this->colors[fg3]; }
.block    {font: $this->fontsizes[1] $this->fonts[block], sans-serif; color: $this->colors[blockfront]; }
.sml {font: $this->fontsizes[1] $this->fonts[narrow], sans-serif; }
hr.block  {line-height: 0px; margin: -6px; padding: 0px 25px; width: 100px; }
td.sidebarleft { color: white; background-color: $this->colors[blockback]; }
th.h3, td.h3  {font: bold $this->fontsizes[3] $this->fonts[0], sans-serif; color: $this->colors[fg3]; background-color: $this->colors[blockback];margin: 6px 0px 0px 0px; }
.sbutton, .r $linkstyle font: bold $this->fontsizes[0] $this->fonts[1]; text-decoration: underline; background: $this->colors[bg3]; color: $this->colors[fg3]; vertical-align: top; }
.submit { font: small-caps bold $this->fontsizes[0] $this->fonts[1]; background: $this->colors[blocktitle]; color: #f0fff0; }\n";
    }
    else {
      echo ".menu $linkstyle font: small-caps bold $this->fontsizes[1] $this->fonts[1]; background: $this->colors[bg3]; color: $this->colors[fg3]; padding: 0px 1px 1px; margin: 0px 1px; }\n";
      echo "A.wu:hover { text-decoration: underline; color: #44cc44; }\n";
      echo "A.wu { text-decoration: underline; color: #cc4444; }\n";
      echo "A.block:hover, A.blockhead:hover { color: $this->colors[hv1]; }\n";
      echo "A:hover { color: $this->colors[hv1];  }\n";
      echo ".bmenu $linkstyle font: small-caps bold $this->fontsizes[1] $this->fonts[1]; background: $this->colors[bg3]; color: $this->colors[fg3]; padding: 0px 1px 1px; margin: 0px 1px; }
.block    {font: $this->fontsizes[1] $this->fonts[block], sans-serif; color: $this->colors[blockfront]; }
hr.block  {line-height: 9px; margin: 0px; padding: 0px; width: 130px; image: url(/$images/menuBreak.gif); }
img.block {height: 9px; margin: 0px; padding: 0px; width: 130px; clear: both }
td.sidebarleft { color: white; background-color: $this->colors[blockback]; }
.sml {font: $this->fontsizes[1] $this->fonts[narrow], sans-serif; }
.sbutton, .r $linkstyle font: small-caps bold $this->fontsizes[0] $this->fonts[1]; background: $this->colors[bg2]; color: $this->colors[fg2]; padding: 0px 1px 1px 1px; margin: 0px 1px; }
.sbutton:hover, .r:hover { background: $this->colors[bg2]; color: $this->colors[hv2]; }
.submit {text-decoration: none; font: small-caps bold $this->fontsizes[0] $this->fonts[1], sans-serif; background: $this->colors[bg2]; color: #f0fff0; padding: 0px 1px 1px 1px; margin: 0px 1px; border: thin outset;}
.submit:hover {text-decoration: none; font: small-caps bold $this->fontsizes[0] $this->fonts[1], sans-serif; background: $this->colors[bg2]; color: $this->colors[hv2]; padding: 0px 1px 1px 1px; margin: 0px 1px; border: thin inset;}
th.h3, td.h3  {font: bold $this->fontsizes[3] $this->fonts[0], sans-serif; color: $this->colors[fg3]; color: white; background-color: $this->colors[bg3];margin: 6px 0px 0px 0px; }\n";
    }

    echo ".block { text-decoration: none; font: $this->fontsizes[1] $this->fonts[block], sans-serif; color: $this->colors[blockfront]; }\n";
    echo ".blockhead { text-decoration: none; font: $this->fontsizes[1] $this->fonts[block], sans-serif; color: $this->colors[blockfront]; font-weight: 700; }\n";

    echo "body, p, td, input {font: $this->fontsizes[1]  $this->fonts[0], sans-serif; color: $this->colors[fg1]; }
.error {font-family: $this->fonts[1], serif; font-weight: 700; color: white; background: red; }
.error h2 { font-size: $this->fontsizes[4]; }
.error h3 { font-size: $this->fontsizes[3]; }
.error h4 { font-size: $this->fontsizes[2]; }
.help   {font: italic $this->fontsizes[1] $this->fonts[help], serif; color: $this->colors[fghelp]; background: $this->colors[bghelp]; }
.blocka   {font: $this->fontsizes[1] $this->fonts[block], sans-serif; color: $this->colors[blockfront]; }
.blockhead  {font: $this->fontsizes[1] $this->fonts[block], sans-serif; font-weight: 700; color: $this->colors[blockfront]; }
.msgtitle   {font: bold $this->fontsizes[1] $this->fonts[1], sans-serif; font-weight: 700; color: $this->colors[blockfront]; background: $this->colors[blocktitle]; margin: 6px 0px 0px 0px; }
.msginfo    {font: $this->fontsizes[0] $this->fonts[1], sans-serif; margin: 0; text-align: right; color: $this->colors[fg2]; background: $this->colors[bg2]; }
.mand   {font: bold $this->fontsizes[0] $this->fonts[1], sans-serif; background: $this->colors[9];}
.smb {font: $this->fontsizes[0] $this->fonts[narrow], sans-serif; color: $this->colors[fg1]; }
.row0 { background: $this->colors[row0]; color: $this->colors[link2]; }
.row1 { background: $this->colors[row1]; color: $this->colors[link2]; }
a.row0, a.row1 { color: $this->colors[link2]; }
.menu   {font: $this->fontsizes[1] $this->fonts[1], sans-serif; color: $this->colors[fg2]; background: $this->colors[bg1]; }
blockquote {font: italic $this->fontsizes[1]  $this->fonts[quote]; color: $this->colors[fg2]; }
input.sml, select.sml {font: $this->fontsizes[0] $this->fonts[0], sans-serif; }
textarea.sml { font: $this->fontsizes[0] $this->fonts[fixed], fixed; }
h1, .h1, th {font: bold $this->fontsizes[2]/$this->fontsizes[3] $this->fonts[0], sans-serif; color: $this->colors[link1]; }
h2, .h2 {font: normal $this->fontsizes[2] $this->fonts[0], sans-serif; color: $this->colors[link1]; }
h3, th.h3 {font: bold $this->fontsizes[1] $this->fonts[0], sans-serif; color: $this->colors[link1]; }
th.cols, th.rows, a.cols  {font: small-caps bold $this->fontsizes[1] $this->fonts[0], sans-serif; color: $this->colors[fg3];  background: $this->colors[bg3]; margin: 6px 0px 0px 0px; }
.cols  {font: small-caps bold $this->fontsizes[1] $this->fonts[0], sans-serif; color: $this->colors[fg3];  background: $this->colors[bg3]; }
.cols:hover  { color: $this->colors[hv2]; }\n";


    echo ".error {font: bold $this->fontsizes[2] $this->fonts[0], sans-serif; color: $this->colors[fgerr]; background: $this->colors[bgerr]; padding: 10px; margin: 20px; }\n";

    echo "--></style>\n";

    // Now start the body
    echo "</head>\n";
  }


  function Page_Header() {
    echo "<body bgcolor=\"$this->colors[bg1]\" fgcolor=\"$this->colors[fg1]\" leftmargin=\"0\" marginheight=\"0\" marginwidth=\"0\" topmargin=\"0\" text=\"$this->colors[fg1]\" link=\"$this->colors[link1]\" vlink=\"$this->colors[link1]\" alink=\"$this->colors[link2]\" background=\"/$images/tanTile.gif\">\n";
    if ( ! isset($style) || "$style" != "stripped" ) {

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
          <td width="32%" align="right"><a href="/help.php?h=<?php echo str_replace(".php","",$PHP_SELF); ?>"><img src="<?php echo $images; ?>/help.gif" width="101" height="19" border=0></a></td>
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
        if ( !isset($error_qry) || "$error_qry" == "" ) {
          include("sidebarleft.php");
        }
        echo "\n</td>\n";

        echo "<td valign=top width=\"" . ($right_panel ? "80" : ($left_panel ? "90" : "100")) . "%\">";
      }

      echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"7\" width=\"100%\">\n";
      echo "<tr><td>\n";
    } // if style not stripped
  }
}