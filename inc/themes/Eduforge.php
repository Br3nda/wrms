<?php
/**
* CatalystTheme for WRMS
*
* @package   awl
* @subpackage   CatalystTheme
* @author    Andrew McMillan <andrew@catalyst.net.nz>
* @copyright Catalyst IT Ltd
* @license   http://gnu.org/copyleft/gpl.html GNU GPL v2
*/

require_once("Theme.php");

/**
* This is the CatalystTheme class which extends the base Theme
*/
class MyTheme extends Theme {
  /**#@+
  * @access private
  */
  /**#@-*/

  /**#@+
  * @access public
  */
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
  function MyTheme( ) {
    global $c;
    parent::Theme();
    $this->stylesheets[0] = "eduforge.css";
    $this->images = "images/eduforge";

    $this->colors = array(
      "bg1" => "#ffffff", // primary background
      "fg1" =>  "#000000", // text on primary background
      "link1" =>  "#205070", // Links on row0/row1
      "bg2" =>  "#6C859F", // secondary background (behind menus)
      "fg2" =>  "#ffffff", // text on links
      "bg3" =>  "#000000", // tertiary background
      "fg3" =>  "#ffffff", // tertiary foreground
      "hv1" =>  "#30a070", // text on hover
      "hv2" =>  "#40f0a0", // other text on hover
      "row0" =>  "#ffffff", // dark rows in listings
      "row1" =>  "#F5F8FA", // light rows in listings
      "link2" =>  "#333333", // Links on row0/row1
      "bghelp" => "#ffffff", // help background
      "fghelp" =>  "#000000", // text on help background
      8 =>  "#583818", // Form headings
      9 =>  "#f5f8fa", // Mandatory forms
      10 =>  "#50a070", // whatever!

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
  function BlockOpen( $bgcolor="", $border_color="" ) {
    echo '<div class="block">';
  }

  /**
  * Title for a block of options / menus in the sidebar
  */
  function BlockTitle( $title="&nbsp;", $bgcolor="", $border_color="" ) {
    echo '<div class="blockhead">';
    echo "<div align=\"left\"><b><span class=\"mockup\"><span class=\"heading-blue\">$title</span></span></b></div>";
    echo '</div>';
  }


  /**
  * Finish a block in a sidebar
  */
  function BlockClose() {
    echo '</div>';
  }



  /**
  * Do any inline styles, where we need to manipulate them beyond a stylesheet
  */
  function InlineStyles() {

    echo <<<EOSTYLE
.imglink {
  border-style: none;
}
.submit {
  background-image: url(/images/eduforge/bar_aqua_tile.gif);
}

.submit:hover {
  color: #205070;
  border: thin inset;
}
td.spaced-out {
  color: #000000;
  padding-left:20px;
}
.spaced-out-grey {
  color:#666666;
}
th.cols, th.rows {
  background: #fff;
  background-image:url('/images/eduforge/aquaTileSolid.gif');
  background-repeat:repeat-x;
  font: small-caps bold $fontsizes[1] tahoma, sans-serif;
  color: #205070;
  margin: 6px 0px 0px 0px;
  height:15px;
  padding-left:10px;
}

a.cols {
  font: small-caps bold $fontsizes[1] tahoma, sans-serif;
  color: #205070;
  background: transparent;
}

EOSTYLE;

  }


  /**
  * Function to output something relevant if the person is not logged on
  * and they should be to access this particular function.
  */
  function IndexNotLoggedOn() {
  global $c;

    echo <<<INDEXNOTLOGGEDIN
<p><strong>
Welcome to the technical support system of the Open Source Virtual Learning Environment project. For
more information on the project please visit  <a href="http://www.ose.org.nz">www.ose.org.nz</a>.
</strong></p>
<p>The goal of the project is to select, further develop and support open source e-learning
software for deployment throughout New Zealand's education sector.</p>
<p>
Eduforge Support is delivered by <a href="http://catalyst.net.nz/products-moodle.htm">Catalyst
IT Limited</a> , a trusted Moodle Partner and the core development team on the OSVLE project.
To set-up Moodle hosting and support on the Education Cluster please read our project wikis:
</p>

<ul>
<li><a href="http://eduforge.org/wiki/wiki/nzvle/wiki?pagename=VLESetup">Setup of your Virtual
learning Environment</a></li>
<li><a href="http://eduforge.org/wiki/wiki/nzvle/wiki?pagename=VLESupport">Support for your
Virtual learning Environment</a></li>
</ul>
<p>Please e-mail <a href="mailto:$c->admin_email">$c->admin_email</a> if you require further
information.</p>
<p>N.B Please keep the status of your work requests up to date. This helps the Moodle team
to manage their workload and keep your costs low.</p>
INDEXNOTLOGGEDIN;
  }


  /**
  * Function to output a page header
  */
  function PageHeader( $style="normal" ) {
    global $c, $session, $tmnu;
    global $left_panel, $right_panel;

    $systems = new PgQuery(SqlSelectSystems($GLOBALS['org_code']));
    $system_list = $systems->BuildOptionList($GLOBALS['system_id'],'PageHeader');

    echo <<<EOHDR
    <table border="0" cellspacing="0" cellpadding="0" style="height:86px;width:100%;background:url('/images/eduforge/eduforge_paua.jpg');">
      <tr>
       <td width="175" nowrap="nowrap" align="center">
        <a href="/"><img alt="logo" border="0" src="/images/eduforge/eduforge_logo.gif" width="145" height="62" /></a>
      </td>
       <td class="spaced-out">s u p p o r t <span class="spaced-out-grey">. e d u f o r g e . o r g</span></td>
EOHDR;

    if ( $session->logged_in ) {
      echo '<td valign="bottom" style="background: inherit;"><div id="searchbar" style="background: inherit;">';
      echo '<form action="/requestlist.php" method="post" name="search">';

      echo '<span class="prompt" style="vertical-align: 0%;">Find:</span>';
      echo '<span class="entry"><input class="search_for" type="text" name="search_for" value="'.$GLOBALS['search_for'].'"/></span>';

      echo '<span class="prompt" style="vertical-align: 0%;">Systems:</span>';
      echo '<span class="entry"><select name="system_code" class="search_for"><option value="">-- select --</option>'.$system_list;
      echo '</select></span>';
      echo '<span class="entry""><input type="submit" alt="go" class="fsubmit" value="Search" /></span>';
      echo '</form>';
      echo '</div></td>'."\n";
    }

    echo "</tr>\n</table>\n";
    echo '<div id="top_menu">';
    if ( $session->logged_in ) {
      echo '<span style="float:right; margin-right:3px; margin-top:3px;">';
      echo $session->fullname;
      echo '</span>';
    }
    if ( isset($tmnu) && is_object($tmnu) && $tmnu->Size() > 0 ) {
      echo $tmnu->Render();
    }
    echo '</div>'."\n";


    // The left hand sidebar.
    if ( $left_panel ) {
      echo "<table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr bgcolor=\"".$this->colors['bg1']."\">\n";
      echo "<td width=\"10%\" valign=\"top\" class=\"noprint sidebarleft\">";
      include("sidebarleft.php");
      echo "\n</td>\n";

      echo "<td valign=\"top\" width=\"" . ($right_panel ? "80" : ($left_panel ? "90" : "100")) . "%\" style=\"padding: 7px;\">";
    }
  }

  /**
  * Function to output a menu bar at the top
  */
  function TopMenuBar(&$tmnu) {
    // We don't do the menu here in this theme.
  }


  /**
  * Function to do the page footer
  */
  function PageFooter() {
    echo <<<FOOTERTABLE
<div id="page_footer">
</div>
FOOTERTABLE;
  }
}

?>