<?php
/**
* DefaultTheme for WRMS
*
* @package   WRMS
* @subpackage   DefaultTheme
* @author    Andrew McMillan <andrew@catalyst.net.nz>
* @copyright Catalyst IT Ltd
* @license   http://gnu.org/copyleft/gpl.html GNU GPL v2
*/

require_once("Theme.php");

/**
* This is the DefaultTheme class which extends the base Theme.
* We don't really do much in this one...
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
  * The DefaultTheme is a fallback that won't be used often.
  */
  function MyTheme( ) {
    parent::Theme();
  }

}

?>