<?php
/**
* PWL Theme for WRMS
*
* @package   WRMS
* @subpackage   PWLTheme
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
    $this->stylesheets[0] = "pwl.css";
    $this->images = "pwimg";
    $c->images = "pwimg";  // Compatibilty with AWL... :-)
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
    echo '<div class="blockhead">'.$title.'</div>';
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
.submit {
  background-image: url(/pwimg/bar_purple_tile.gif);
}
.submit:hover {
  color: #f8f400;
  border: thin inset;
}
.imglink {
  border-style: none;
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
<blockquote>
<p><strong>
Welcome to $c->system_name. For more information
on Plumbing World, please visit  <a href="http://www.plumbingworld.co.nz">www.plumbingworld.co.nz</a>.
</strong></p>
</blockquote>

<p>Please e-mail <a href="mailto:$c->admin_email">$c->admin_email</a> if you require further information.</p>
INDEXNOTLOGGEDIN;
  }


  /**
  * Function to output a page header
  */
  function PageHeader( $style="normal" ) {
    global $c, $session, $tmnu;

    if ( ! $this->panel_top ) return;

    echo '<div id="topbar">';
    echo $this->ImgLink('pwl-logo.png', '/', 252, 60);
    echo '</div>'."\n";
    if ( $session->logged_in  ) {
      echo '<div id="searchbar">';
      echo '<form action="/wrsearch.php" method="post" name="search">';

      echo '<span class="prompt" style="vertical-align: 0%;">Find:</span>';
      echo '<span class="entry"><input class="search_for" type="text" name="search_for" value="'.$GLOBALS['search_for'].'"/></span>';

      $systems = new PgQuery(SqlSelectSystems($GLOBALS['org_code']));
      $system_list = $systems->BuildOptionList($GLOBALS['system_id'],'Config::LocPgHdr');
      echo '<span class="prompt" style="vertical-align: 0%;">Systems:</span>';
      echo '<span class="entry"><select name="system_id" class="search_for"><option value="">-- select --</option>'.$system_list;
      echo '</select></span>';
      echo '<span class="entry""><input type="submit" alt="go" class="fsubmit" value="Search" /></span>';
      echo '</form>';
      echo '</div>'."\n";
    }

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
    global $c;
    echo <<<FOOTERTABLE
<div id="page_footer">
WRMS: $c->code_major.$c->code_minor.$c->code_patch-$c->code_debian , DB: $c->schema_major.$c->schema_minor.$c->schema_patch
</div>
FOOTERTABLE;
  }
}

?>