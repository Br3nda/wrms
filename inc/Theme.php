<?php
/**
* Theming support for AWL based applications.
*
* @package   awl
* @subpackage   Theme
* @author    Andrew McMillan <andrew@catalyst.net.nz>
* @copyright Catalyst IT Ltd
* @license   http://gnu.org/copyleft/gpl.html GNU GPL v2
*/

/**
* This is the base Theme class, which is extended by each actual
* implemented Theme.
*/
class Theme {
  /**#@+
  * @access private
  */

  /**
  * An array of colors used for display of various items
  * @var string
  */
  var $colors;

  /**
  * An array of fonts used for display of various items
  * @var string
  */
  var $fonts;
  /**#@-*/

  /**#@+
  * @access public
  */
  /**
  * The filename of the associated CSS stylesheets
  * @var array of string
  */
  var $stylesheets;

  /**
  * The directory containing the images for this theme
  * @var string
  */
  var $images;

  /**#@-*/

  /**
  * The Theme is primarily responsible for display of:
  *  - An HTML header preamble, including references to style sheets
  *  - A page header, graphically presenting the system
  *    - possibly with a menu bar
  *  - An optional LH Sidebar, containing various possible items
  *  - A content area, where goes all the real meat.
  *  - An optional RH Sidebar, containing other items
  *  - An optional menu at the bottom of the page
  *  - A page footer, completing the page.
  */
  function Theme( ) {
    global $c;

    $this->stylesheets[] = "main.css";
    $this->images = "images";
    $c->images = "images";  // Compatibilty with AWL... :-)
    $this->colors = array(
      "bg1" => "#ffffff", // primary background
      "fg1" =>  "#000000", // text on primary background
      "link1" =>  "#880000", // Links on row0/row1
      "bg2" =>  "#b00000", // secondary background (behind menus)
      "fg2" =>  "#ffffff", // text on links
      "bg3" =>  "#404040", // tertiary background
      "fg3" =>  "#ffffff", // tertiary foreground
      "hv1" =>  "#660000", // text on hover
      "hv2" =>  "#f8f400", // other text on hover
      "row0" =>  "#ffffff", // dark rows in listings
      "row1" =>  "#f0f0f0", // light rows in listings
      "link2" =>  "#333333", // Links on row0/row1
      "bghelp" => "#ffffff", // help background
      "fghelp" =>  "#000000", // text on help background
      "mand" =>  "#c8c8c8", // Mandatory forms

      // Parts of a text block (default colors - some blocks might code around this
      "blockfront" => "black",
      "blockback" => "white",
      "blockbg2" => "white",
      "blocktitle" => "white",
      "blocksides" => "#ffffff",
      "blockextra" => "#660000"
    );

    $this->fonts = array( "tahoma",   // primary font
      "verdana",  // secondary font
      "help"  => "times",   // help text
      "quote" => "times new roman, times, serif", // quotes in messages
      "narrow"  => "arial narrow, helvetica narrow, times new roman, times", // quotes in messages
      "fixed" => "courier, fixed",  // monospace font
      "block" => "tahoma");   // block font
  }


  /**
  * Start a block in a sidebar
  */
  function BlockOpen(  $bgcolor="", $border_color="") {
    if ( $bgcolor == "" ) $bgcolor=$this->colors["blockback"];
    if ( $border_color == "" ) $border_color=$this->colors["blocksides"];
    echo "<table cellpadding=\"1\" cellspacing=\"0\" border=\"0\" width=\"100%\" bgcolor=\"$border_color\" style=\" margin: 0 1px;\">\n";
    echo "<tr><td>\n";
    echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"$bgcolor\">\n";
  }


  /**
  * Title for a block of options / menus in the sidebar
  */
  function BlockTitle( $title="&nbsp;", $bgcolor="", $border_color="") {
    if ( $bgcolor == "" ) $bgcolor=$this->colors["blocktitle"];
    if ( $border_color == "" ) $border_color=$this->colors["blocksides"];

    echo "<tr bgcolor=\"$bgcolor\">\n";
    echo "<td class=\"blockhead\" align=\"center\">$title</td>\n";
    echo "</tr>\n";
    echo "<tr bgcolor=\"$bgcolor\">\n";
    echo "<td class=\"block\" align=\"left\">\n";
  }


  /**
  * Finish a block in a sidebar
  */
  function BlockClose() {
    echo "</td></tr>\n";
    echo "</table>\n";
    echo "</td></tr>\n";
    echo "</table>\n";
  }


  /**
  * Do any inline styles, where we need to manipulate them beyond a stylesheet
  */
  function InlineStyles() {

    echo <<<EOSTYLE
.imglink {
  border-style: none;
}
EOSTYLE;

  }


  /**
  * Function to return an <img src="..."> kind of link.
  */
  function Image($image_file, $width=-1, $height=-1 ) {
    $width  = ($width  > 0 ? sprintf( ' width="%s"',  $width)  : '');
    $height = ($height > 0 ? sprintf( ' height="%s"', $height) : '');
    $image_locn = (file_exists("$this->images/$image_file") ? $this->images : "images");
    $result = sprintf( '<img src="%s/%s" border="0" class="image"%s%s>', $image_locn, $image_file, $width, $height );
    return $result;
  }


  /**
  * Function to return an <a href="..."><img src="..."></a> kind of link.
  */
  function ImgLink($image_file, $link, $width=-1, $height=-1 ) {
    $width  = ($width  > 0 ? sprintf( ' width="%s"',  $width)  : '');
    $height = ($height > 0 ? sprintf( ' height="%s"', $height) : '');
    $image_locn = (file_exists("$this->images/$image_file") ? $this->images : "images");
    $result = sprintf( '<a href="%s" class="imglink"><img src="%s/%s" class="imglink"%s%s></a>', $link, $image_locn, $image_file, $width, $height );
    return $result;
  }


  /**
  * Function to output something relevant if the person is not logged on
  * and they should be to access this particular function.
  */
  function IndexNotLoggedOn() {
  global $c;

    echo <<<INDEXNOTLOGGEDIN
<blockquote>
<p><strong>
Welcome to $c->system_name. For more information
on Catalyst, please visit  <a href="http://www.catalyst.net.nz">www.catalyst.net.nz</a>.
</strong></p>
</blockquote>

<p>Please e-mail <a href="mailto:$c->admin_email">$c->admin_email</a> if you require further information.</p>
INDEXNOTLOGGEDIN;
  }


  /**
  * Function to output the HTML header part of the document.
  */
  function HTMLHeader() {
    global $c;

    if ( isset($c->page_title) ) $title = $c->page_title;

    // Standard headers included everywhere.
    echo "<!DOC"."TYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" \"http://www.w3.org/TR/REC-html40/loose.dtd\">\n";
    echo "<html>\n<head>\n<title>$title</title>\n";

    if ( isset($this->stylesheets) ) {
      foreach ( $this->stylesheets AS $stylesheet ) {
        echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$stylesheet\" />\n";
      }
    }

    if ( isset($c->local_styles) ) {
      // Always load local styles last, so they can override prior ones...
      foreach ( $c->local_styles AS $stylesheet ) {
        echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$stylesheet\" />\n";
      }
    }

    if ( isset($c->print_styles) ) {
      // Finally, load print styles last, so they can override all of the above...
      foreach ( $c->print_styles AS $stylesheet ) {
        echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$stylesheet\" media=\"print\"/>\n";
      }
    }

    if ( isset($c->scripts) ) {
      foreach ( $c->scripts AS $script ) {
        echo "<script language=\"JavaScript\" src=\"$script\"></script>\n";
      }
    }

    $fontsizes = array( "xx-small", "x-small", "small", "medium" );
    if ( is_object($settings) )
      $BaseFontsize = intval($settings->get('fontsize'));
    else
      $BaseFontsize = 11;
    if ( $BaseFontsize < 5 || $BaseFontsize > 25 ) $BaseFontsize = 10;

    for ( $i=0; $i < 6; $i++) {
      $fontsizes[$i] = sprintf( "%dpx", $BaseFontsize + (2 * $i));
    }

    $colors = $this->colors;
    $fonts  = $this->fonts;

    $linkstyle = " ";
    $borders = "border: solid $colors[blocksides] 1px; ";

    // Style stuff
    echo <<<EOINSTYLE
<style type="text/css">
body, p, td, input {
  font: $fontsizes[1] $fonts[0], sans-serif; color: $colors[fg1];
}

A, .msglink, .menu, .bmenu, .sbutton, .r {
  color: $colors[link1]; text-decoration:none;
}

A.wu, A.wu:hover {
  text-decoration: underline;
  color: #44cc44;
}

A:hover, A.block:hover, A.blockhead:hover {
  color: $colors[hv1];
}

.menu, .bmenu {
  font: small-caps bold $fontsizes[1] $fonts[1];
  background: $colors[bg3];
  color: $colors[fg3];
  padding: 0px 1px 1px;
  margin: 0px 1px;
}

.block {
  font: $fontsizes[1] $fonts[block], sans-serif;
  color: $colors[blockfront];
}

hr.block {
  line-height: 9px;
  margin: 0px;
  padding: 0px;
  width: 130px;
  background: url(/$c->images/menuBreak.gif);
}

img.block {
  height: 9px;
  margin: 0px;
  padding: 0px;
  width: 130px;
  clear: both;
}

td.sidebarleft {
  color: white;
  background-color: $colors[blockback];
}

.sml {
  font: $fontsizes[1] $fonts[narrow], sans-serif;
}

.sbutton, .r {
  font: small-caps bold $fontsizes[0] $fonts[1];
  background: $colors[bg2];
  color: $colors[fg2];
  padding: 0px 1px 1px 1px;
  margin: 0px 1px;
}

.sbutton:hover, .r:hover {
  background: $colors[bg2];
  color: $colors[hv2];
}

.submit {
  text-decoration: none;
  font: small-caps bold $fontsizes[0] $fonts[1], sans-serif;
  background: $colors[bg2];
  color: $colors[fg2];
  padding: 0px 2px 1px 2px;
  margin: 0px 2px;
  border: thin outset;
}

.submit:hover {
  text-decoration: none;
  font: small-caps bold $fontsizes[0] $fonts[1], sans-serif;
  background: $colors[bg2];
  color: $colors[hv2];
  padding: 0px 2px 1px 2px;
  margin: 0px 2px;
  border: thin inset;
}

th.h3, td.h3  {
  font: bold $fontsizes[3] $fonts[0], sans-serif;
  color: $colors[fg3];
  color: white;
  background-color: $colors[bg3];
  margin: 6px 0px 0px 0px;
}

.block {
  text-decoration: none;
  font: $fontsizes[1] $fonts[block], sans-serif;
  color: $colors[blockfront];
}

.blockhead {
  text-decoration: none;
  font: $fontsizes[1] $fonts[block], sans-serif;
  color: $colors[blockfront];
  font-weight: 700;
}

.error {
  font-family: $fonts[1], serif;
  font-weight: 700;
  color: white;
  background: red;
}

.error h2 {
  font-size: $fontsizes[4];
}

.error h3 {
  font-size: $fontsizes[3];
}

.error h4 {
  font-size: $fontsizes[2];
}

.help {
  font: italic $fontsizes[1] $fonts[help], serif;
  color: $colors[fghelp];
  background: $colors[bghelp];
}

.blocka {
  font: $fontsizes[1] $fonts[block], sans-serif;
  color: $colors[blockfront];
}

.blockhead {
  font: $fontsizes[1] $fonts[block], sans-serif;
  font-weight: 700;
  color: $colors[blockfront];
}

.msgtitle {
  font: bold $fontsizes[1] $fonts[1], sans-serif;
  font-weight: 700;
  color: $colors[blockfront];
  background: $colors[blocktitle];
  margin: 6px 0px 0px 0px;
}

.msginfo {
  font: $fontsizes[0] $fonts[1], sans-serif;
  margin: 0;
  text-align: right;
  color: $colors[fg2];
  background: $colors[bg2];
}

.mand {
  font: bold $fontsizes[0] $fonts[1], sans-serif;
  background: $colors[mand];
}

.smb {
  font: $fontsizes[0] $fonts[narrow], sans-serif;
  color: $colors[fg1];
}

.row0, .r0 {
  background: $colors[row0];
  color: $colors[link2];
}

.row1, .r1 {
  background: $colors[row1];
  color: $colors[link2];
}

a.row0, a.row1 {
  color: $colors[link2];
}

.menu {
  font: $fontsizes[1] $fonts[1], sans-serif;
  color: $colors[fg2];
  background: $colors[bg1];
}

blockquote {
  font: italic $fontsizes[1] $fonts[quote];
  color: $colors[fg2];
}

input.sml, select.sml {
  font: $fontsizes[0] $fonts[0], sans-serif;
}

textarea.sml {
  font: $fontsizes[0] $fonts[fixed], fixed;
}

h1, .h1, th {
  font: bold $fontsizes[2]/$fontsizes[3] $fonts[0], sans-serif;
  color: $colors[link1];
}

h2, .h2 {
  font: normal $fontsizes[2] $fonts[0], sans-serif;
  color: $colors[link1];
}

h3, th.h3 {
  font: bold $fontsizes[1] $fonts[0], sans-serif;
  color: $colors[link1];
}

th.cols, th.rows, a.cols {
  font: small-caps bold $fontsizes[1] $fonts[0], sans-serif;
  color: $colors[fg3];
  background: $colors[bg3];
  margin: 6px 0px 0px 0px;
}
.cols  {
  font: small-caps bold $fontsizes[1] $fonts[0], sans-serif;
  color: $colors[fg3];
  background: $colors[bg3];
}

.cols:hover {
  color: $colors[hv2];
}

EOINSTYLE;

    $this->InlineStyles();

    echo "\n</style>\n";
    echo "<style type=\"text/css\" media=\"print\"><!--\n";
    echo ".noprint, #topbar, #searchbar, #top_menu { display: none; }\n";
    echo "--></style>\n";

    // Now start the body
    echo "</head>\n";
    echo "<body bgcolor=\"$colors[bg1]\" fgcolor=\"$colors[fg1]\" leftmargin=\"0\" marginheight=\"0\" marginwidth=\"0\" topmargin=\"0\" text=\"$colors[fg1]\" link=\"$colors[link1]\" vlink=\"$colors[link1]\" alink=\"$colors[link2]\">\n";
  }


  /**
  * Function to output a page header
  */
  function PageHeader( $style="normal" ) {
    global $c, $session, $tmnu;
    global $left_panel, $right_panel;

    echo <<<EOHDR
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>
      <table width="100%" border="0" cellspacing="0" cellpadding="0"">
        <tr>
          <td background="$this->images/tanTile.gif" height="55"><img src="$this->images/wrmsTOP.gif" width="700" height="60"></td>
        </tr>
      </table>
      <table width="100%" border="0" cellspacing="0" cellpadding="0"">
        <tr>
          <td width="40%">
            <table width="142" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td align="left"><img src="$this->images/WRMSheader.gif" width="470" height="19"></td>
              </tr>
            </table>
          </td>
          <td width="28%"><font size=1>&nbsp;</font></td>
          <td width="32%" align="right"><a href="$GLOBALS[help_url]"><img src="$this->images/help.gif" width="101" height="19" border=0></a></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
EOHDR;

    if ( $style != "stripped" ) {
      // The left hand sidebar.
      if ( $left_panel ) {
        echo "<table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr bgcolor=\"".$this->colors['bg1']."\">\n";
        echo "<td width=\"10%\" valign=\"top\" class=\"noprint sidebarleft\">";
        if ( !isset($error_qry) || "$error_qry" == "" ) {
          include("sidebarleft.php");
        }
        echo "\n</td>\n";

        echo "<td valign=top width=\"" . ($right_panel ? "80" : ($left_panel ? "90" : "100")) . "%\">";
      }
    }
  }


  /**
  * Function to output a menu bar at the top
  */
  function TopMenuBar(&$tmnu) {
    echo $tmnu->Render();
  }


  /**
  * Function to output a menu bar at the bottom
  */
  function BottomMenuBar(&$tmnu) {
    /* By default there are no menus at the bottom of the page */
  }

  /**
  * Function to do the page footer
  */
  function PageFooter() {

      echo <<<FOOTERTABLE
<table width="100%" border="0" cellspacing="0" cellpadding="0" height="16" background="/$this->images/WRMSbottomTile.gif">
  <tr>
    <td width="41%" height="10" valign="top"><img src="/$this->images/WRMSbottom.gif" width="473" height="16">
    </td>
    <td width="37%" height="10">&nbsp;</td>
    <td width="22%" align="right" height="10" valign="top"><img src="/$this->images/WRMSbottom1.gif" width="155" height="16"></td>
  </tr>
</table>
FOOTERTABLE;
  }
}

?>