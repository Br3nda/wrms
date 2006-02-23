<?php
/**
* Classes to handle entry and viewing of field-based data.
*
* @package   AWL
* @subpackage   DataEntry
* @author    Andrew McMillan <andrew@catalyst.net.nz>
* @copyright Andrew McMillan
* @license   http://gnu.org/copyleft/gpl.html GNU GPL v2
*/

/**
* Individual fields used for data entry / viewing.
*
* This object is not really intended to be used directly.  The more normal
* interface is to instantiate an {@link EntryForm} and then issue calls
* to {@link DataEntryLine()} and other {@link EntryForm} methods.
*
* Understanding the operation of this class (and possibly auditing the source
* code, particularly {@link EntryField::Render}) will however convey valuable
* understanding of some of the more
* esoteric features.
*
* @todo This class doesn't really provide a huge amount of utility between construct
* and render, but there must be good things possible there.  Perhaps one EntryField
* is created and used repeatedly as a template (e.g.).  That might be useful to
* support...  Why is this a Class anyway?  Maybe we should have just done half a
* dozen functions (one per major field type) and just used those...  Maybe we should
* build a base class for this and extend it to make EntryField in a better way.
*
* EntryField is only useful at present if you desperately want to use it's simple
* field interface, but want to intimately control the layout (or parts of the layout),
* otherwise you should be using {@link EntryForm} as the main class.
*
* @package AWL
*/
class EntryField
{
  /**#@+
  * @access private
  */
  /**
  * The name of the field
  * @var string
  */
  var $fname;

  /**
  * The type of entry field
  * @var string
  */
  var $ftype;
  /**#@-*/

  /**#@+
  * @access public
  */
  /**
  * The current value
  * @var string
  */
  var $current;

  /**
  * An array of key value pairs
  * @var string
  */
  var $attributes;

  /**
  * Once it actually is...
  * @var string
  */
  var $rendered;
  /**#@-*/

  /**
  * Initialise an EntryField, used for data entry.
  *
  * The following types of fields are possible:
  * <ul>
  * <li>select - Will display a select list of the keys/values in $attributes where the
  * key starts with an underscore.  The key will have the '_' removed before being used
  * as the key in the list.  All the $attributes with keys not beginning with '_' will
  * be used in the normal manner as HTML attributes within the &lt;select ...&gt; tag.</li>
  * <li>lookup - Will display a select list of values from the database.
  * If $attributes defines a '_sql' attibute then that will be used to make
  * the list, otherwise the database values will be from the 'codes' table
  * as in "SELECT code_id, code_value FROM codes WHERE code_type = '_type' ORDER BY code_seq, code_id"
  * using the value of $attributes['_type'] as the code_type.</li>
  * <li>date - Will be a text field, expecting a date value which might be
  * javascript validated at some point in the future.</li>
  * <li>checkbox - Will display a checkbox for an on-off value.</li>
  * <li>textarea - Will display an HTML textarea.</li>
  * <li>file - Will display a file browse / enter field.</li>
  * <li>button - Will display a button field.</li>
  * <li>password - Password entry.  This will display entered data as asterisks.</li>
  * </ul>
  *
  * The $attributes array is useful to set specific HTML attributes within the HTML tag
  * used for the entry field however $attribute keys named starting with an underscore ('_')
  * affect the field operation rather than the HTML.  For the 'select' field type, these are
  * simply used as the keys / values for the selection (with the '_' removed), but other
  * cases are more complex:
  * <ul>
  * <li>_help - While this will be ignored by the EntryField::Render() method the _help
  * should be assigned (or will be assigned the same value as the 'title' attribute) and
  * will (depending on the data-entry line format in force) be displayed as help for the
  * field by the EntryForm::DataEntryLine() method.</li>
  * <li>_sql - When used in a 'lookup' field this controls the SQL to return keys/values
  * for the list.  The actual SQL should return two columns, the first will be used for
  * the key and the second for the displayed value.</li>
  * <li>_type - When used in a 'lookup' field this defines the codes type used.</li>
  * <li>_null - When used in a 'lookup' field this will control the description for an
  * option using a '' key value which will precede the list of values from the database.</li>
  * <li>_zero - When used in a 'lookup' field this will control the description for an
  * option using a '0' key value which will precede the list of values from the database.</li>
  * <li>_label - When used in a 'radio' or 'checkbox' field this will wrap the field
  * with an HTML label tag as <label ...><input field...>$attributes['_label']</label></li>
  * <li> - </li>
  * </ul>
  *
  * @param text $intype The type of field:
  *    select | lookup | date | checkbox | textarea | file | button | password
  *    (anything else is dealt with as "text")
  *
  * @param text $inname The name of the field.
  *
  * @param text $attributes An associative array of extra attributes to be applied
  * to the field.  Optional, but generally important.  Some $attribute keys have
  * special meaning, while others are simply added as HTML attributes to the field.
  *
  * @param text $current_value The current value to use to initialise the
  *                     field.   Optional.
  */
  function EntryField( $intype, $inname, $attributes="", $current_value="" )
  {
    $this->ftype = $intype;
    $this->fname = $inname;
    $this->current = $current_value;

    if ( isset($this->{"new_$intype"}) && function_exists($this->{"new_$intype"}) ) {
      // Optionally call a function within this object called "new_<intype>" for setup
      $this->{"new_$intype"}( $attributes );
    }
    else if ( is_array($attributes) ) {
      $this->attributes = $attributes;
    }
    else {
    }

    $this->rendered = "";
  }

  /**
  * Render an EntryField into HTML
  * @see EntryField::EntryField(), EntryForm::DataEntryLine()
  *
  * @return text  An HTML fragment for the data-entry field.
  */
  function Render() {
    global $session;

    $r = "<";
    switch ( $this->ftype ) {

      case "select":
        $r .= "select name=\"$this->fname\"%%attributes%%>";
        reset( $this->attributes );
        while( list($k,$v) = each( $this->attributes ) ) {
          if ( substr($k, 0, 1) != '_' ) continue;
          if ( $k == '_help' ) continue;
          $k = substr($k,1);
          $r .= "<option value=\"".htmlentities($k)."\"";
          if ( "$this->current" == "$k" ) $r .= " selected";
          $r .= ">$v</option>" ;
        }
        $r .= "</select>";
        break;

      case "lookup":
        $r .= "select name=\"$this->fname\"%%attributes%%>";
        reset( $this->attributes );
        while( list($k,$v) = each( $this->attributes ) ) {
          if ( substr($k, 0, 1) != '_' ) continue;
          $k = substr($k,1);
          if ( $k == 'help' || $k == "sql" || $k == "type" ) continue;
          if ( $k == "null" ) $k = "";
          if ( $k == "zero" ) $k = "0";
          $r .= "<option value=\"".htmlentities($k)."\"";
          if ( "$this->current" == "$k" ) $r .= " selected";
          $r .= ">$v</option>" ;
        }
        if ( isset($this->attributes["_sql"]) ) {
          $qry = new PgQuery( $this->attributes["_sql"] );
        }
        else {
          list( $tbl, $fld ) = explode("|", $this->attributes['_type'], 2);
          $qry = new PgQuery( "SELECT lookup_code, lookup_desc FROM lookup_code WHERE source_table = ? AND source_field = ? ORDER BY lookup_seq, lookup_code", $tbl, $fld );
        }
        $r .= $qry->BuildOptionList( $this->current, "rndr:$this->fname" );
        $r .= "</select>";
        break;

      case "date":
        if ( !isset($this->attributes['size']) || $this->attributes['size'] == "" ) $size = " size=12";
        $r .= "input type=\"text\" name=\"$this->fname\"$size value=\"".htmlentities($this->current)."\"%%attributes%%>";
        break;

      case "checkbox":
        // We send a hidden field with a false value, which will be overridden by the real
        // field with a true value (if true) or not overridden (if false).
        $r .= "input type=\"hidden\" name=\"$this->fname\" value=\"off\"><";
      case "radio":
        $checked = "";
        if ( $this->current == 't' || intval($this->current) == 1 || $this->current == 'on'
              || (isset($this->attributes['value']) && $this->current == $this->attributes['value'] ) )
          $checked = " checked";
        $id = "id_$this->fname" . ( $this->ftype == "radio" ? "_".$this->attributes['value'] : "");
        if ( isset($this->attributes['_label']) ) {
          $r .= "label for=\"$id\"";
          if ( isset($this->attributes['class']) )
            $r .= ' class="'. $this->attributes['class'] . '"';
          $r .= "><";
        }
        $r .= "input type=\"$this->ftype\" name=\"$this->fname\" id=\"$id\"$checked%%attributes%%>";
        if ( isset($this->attributes['_label']) ) {
          $r .= " " . $this->attributes['_label'];
          $r .= "</label>";
        }
        break;

      case "button":
        $r .= "input type=\"button\" name=\"$this->fname\"%%attributes%%>";
        break;

      case "submit":
        $r .= "input type=\"submit\" name=\"$this->fname\" value=\"".htmlentities($this->current)."\"%%attributes%%>";
        break;

      case "textarea":
        $r .= "textarea name=\"$this->fname\"%%attributes%%>$this->current</textarea>";
        break;

      case "file":
        if ( !isset($this->attributes['size']) || $this->attributes['size'] == "" ) $size = " size=25";
        $r .= "input type=\"file\" name=\"$this->fname\"$size value=\"".htmlentities($this->current)."\"%%attributes%%>";
        break;

      case "password":
        $r .= "input type=\"password\" name=\"$this->fname\" value=\"".htmlentities($this->current)."\"%%attributes%%>";
        break;

      default:
        $r .= "input type=\"text\" name=\"$this->fname\" value=\"".htmlentities($this->current)."\"%%attributes%%>";
        break;
    }

    // Now process the generic attributes
    reset( $this->attributes );
    $attribute_values = "";
    while( list($k,$v) = each( $this->attributes ) ) {
      if ( $k == '_readonly' ) $attribute_values .= " readonly";
      else if ( $k == '_disabled' ) $attribute_values .= " disabled";
      if ( substr($k, 0, 1) == '_' ) continue;
      $attribute_values .= " $k=\"".htmlentities($v)."\"";
    }
    $r = str_replace( '%%attributes%%', $attribute_values, $r );

    $this->rendered = $r;
    return $r;
  }

  /**
  * Function called indirectly when a new EntryField of type 'lookup' is created.
  * @param array $attributes The attributes array that was passed in to the new EntryField()
  * constructor.
  */
  function new_lookup( $attributes ) {
    $this->attributes = $attributes;
  }

}

/**
* A class to handle displaying a form on the page (for editing) or a structured
* layout of non-editable content (for viewing), with a simple switch to flip from
* view mode to edit mode.
*
* @package AWL
*/
class EntryForm
{
  /**#@+
  * @access private
  */
  /**
  * The submit action for the form
  * @var string
  */
  var $action;

  /**
  * The record that the form is dealing with
  * @var string
  */
  var $record;

  /**
  * Whether we are editing, or not
  * @var string
  */
  var $editmode;

  /**
  * The name of the form
  * @var string
  */
  var $name;

  /**
  * The CSS class of the form
  * @var string
  */
  var $class;

  /**
  * Format string for lines that are breaks in the data entry field groupings
  * @var string
  */
  var $break_line_format;

  /**
  * Format string for normal data entry field lines.
  * @var string
  */
  var $table_line_format;

  /**
  * Format string that has been temporarily saved so we can restore it later
  * @var string
  */
  var $saved_line_format;
  /**#@-*/

  /**
  * Initialise a new data-entry form.
  * @param string $action The action when the form is submitted.
  * @param objectref $record A reference to the database object we are displaying / editing.
  * @param boolean $editmode Whether we are editing.
  */
  function EntryForm( $action, &$record, $editmode=false )
  {
    $this->action   = $action;
    $this->record   = &$record;
    $this->editmode = $editmode;
    $this->break_line_format = '<tr><th class="ph" colspan="2">%s</th></tr>'."\n";
    $this->table_line_format = '<tr><th class="prompt">%s</th><td class="entry">%s<span class="help">%s</span></td></tr>'."\n";
  }

  /**
  * Initialise some more of the forms fields, possibly with a prefix
  * @param objectref $record A reference to the database object we are displaying / editing.
  * @param string $prefix A prefix to prepend to the field name.
  */
  function PopulateForm( &$record, $prefix="" )
  {
    foreach( $record AS $k => $v ) {
      $this->record->{"$prefix$k"} = $v;
    }
  }

  /**
  * Set the line format to have no help display
  */
  function NoHelp( ) {
    $this->break_line_format = '<tr><th class="ph" colspan="2">%s</th></tr>'."\n";
    $this->table_line_format = '<tr><th class="prompt">%s</th><td class="entry">%s</td></tr>'."\n";
  }

  /**
  * Set the line format to have help displayed in the same cell as the entry field.
  */
  function HelpInLine( ) {
    $this->break_line_format = '<tr><th class="ph" colspan="2">%s</th></tr>'."\n";
    $this->table_line_format = '<tr><th class="prompt">%s</th><td class="entry">%s<span class="help">%s</span></td></tr>'."\n";
  }

  /**
  * Set the line format to have help displayed in it's own separate cell
  */
  function HelpInCell( ) {
    $this->break_line_format = '<tr><th class="ph" colspan="3">%s</th></tr>'."\n";
    $this->table_line_format = '<tr><th class="prompt">%s</th><td class="entry">%s</td><td class="help">%s</td></tr>'."\n";
  }

  /**
  * Set the line format to an extremely simple CSS based prompt / field layout.
  */
  function SimpleForm( $new_format = '<span class="prompt">%s:</span>&nbsp;<span class="entry">%s</span>' ) {
    $this->break_line_format = '%s'."\n";
    $this->table_line_format = $new_format."\n";
  }

  /**
  * Set the line format to a temporary one that we can revert from.
  * @param string $new_format The (optional) new format we will temporarily use.
  */
  function TempLineFormat( $new_format = '<span class="prompt">%s:</span>&nbsp;<span class="entry">%s</span>' ) {
    $this->saved_line_format = $this->table_line_format;
    $this->table_line_format = $new_format ."\n";
  }

  /**
  * Revert the line format to what was in place before the last TempLineFormat call.
  */
  function RevertLineFormat( ) {
    if ( isset($this->saved_line_format) ) {
      $this->table_line_format = $this->saved_line_format;
    }
  }

  /**
  * Start the actual HTML form.  Return the fragment to do this.
  * @param array $extra_attributes Extra key/value pairs for the FORM tag.
  * @return string The HTML fragment for the start of the form.
  */
  function StartForm( $extra_attributes='' ) {
    if ( !is_array($extra_attributes) && $extra_attributes != '' ) {
      list( $k, $v ) = explode( '=', $extra_attributes );
      $extra_attributes = array( $k => $v );
    }
    $extra_attributes['action']  = $this->action;
    if ( !isset($extra_attributes['method']) )  $extra_attributes['method']  = 'post';
    if ( !isset($extra_attributes['enctype']) ) $extra_attributes['enctype'] = 'multipart/form-data';
    if ( !isset($extra_attributes['name']) )    $extra_attributes['name']    = 'form';
    if ( !isset($extra_attributes['class']) )   $extra_attributes['class']   = 'formdata';
    if ( !isset($extra_attributes['id']) )      $extra_attributes['id']      = $extra_attributes['name'];

    // Now process the generic attributes
    reset( $extra_attributes );
    $attribute_values = "";
    while( list($k,$v) = each( $extra_attributes ) ) {
      $attribute_values .= " $k=\"".htmlentities($v)."\"";
    }
    return "<form$attribute_values>\n";
  }

  /**
  * Return the HTML fragment to end the form.
  * @return string The HTML fragment to end the form.
  */
  function EndForm( ) {
    return "</form>\n";
  }

  /**
  * A utility function for a heading line within a data entry table
  * @return string The HTML fragment to end the form.
  */
  function BreakLine( $text = '' )
  {
    return sprintf( $this->break_line_format, $text);
  }

  /**
  * A utility function for a hidden field within a data entry table
  *
  * @param string $fname The name of the field.
  * @param string $fvalue The value of the field.
  * @return string The HTML fragment for the hidden field.
  */
  function HiddenField($fname,$fvalue) {
    return sprintf( '<input type="hidden" name="%s" value="%s" />%s', $fname, htmlentities($fvalue), "\n" );
  }

  /**
  * Internal function for parsing the type extra on a field.
  *
  * If the '_help' attribute is not set it will be assigned the value of
  * the 'title' attribute, if there is one.
  *
  * If the 'class' attribute is not set it will be assigned to 'flookup',
  * 'fselect', etc, according to the field type.
  * @static
  * @return string The parsed type extra.
  */
  function _ParseAttributes( $ftype = '', $attributes = '' )  {

    if ( !is_array($attributes) ) {
      if ( strpos( $attributes, '=' ) === false ) {
        $attributes = array();
      }
      else {
        list( $k, $v ) = explode( '=', $attributes );
        $attributes = array( $k => $v );
      }
    }

    // Default the help to the title, or to blank
    if ( !isset($attributes['_help']) ) {
      $attributes['_help'] = "";
      if ( isset($attributes['title']) )
        $attributes['_help'] = $attributes['title'];
    }

    // Default the style to fdate, ftext, fcheckbox etc.
    if ( !isset($attributes['class']) ) {
      $attributes['class'] = "f$ftype";
    }

    return $attributes;
  }

  /**
  * A utility function for a data entry line within a table
  * @return string The HTML fragment to display the data entry field
  */
  function DataEntryField( $format, $ftype='', $base_fname='', $attributes='', $prefix='' )
  {
    global $session;

    if ( ($base_fname == '' || $ftype == '') ) {
      // Displaying never-editable values
      return $format;
    }
    $fname = $prefix . $base_fname;

/*
    if ( substr($real_fname,0,4) == $prefix ) {
      // Sometimes we will prepend a prefix to the field name so that the field
      // name differs from the column name in the database.  We also remove it
      // when it's submitted.
      $fname = substr($real_fname,strlen($prefix));
      // Also assign any posted value
      if ( !isset($_POST[$fname]) && isset($_POST[$real_fname]) )
        $_POST[$fname] = $_POST[$real_fname];
    }
    else {
      $fname = $real_fname;
    }
*/
      $session->Log( "DBG: fmt='%s', fname='%s', fvalue='%s'", $format, $fname, $this->record->{$fname} );
    if ( !$this->editmode ) {
      // Displaying editable values when we are not editing
      $session->Log( "DBG: fmt='%s', fname='%s', fvalue='%s'", $format, $fname, $this->record->{$fname} );
      return sprintf($format, $this->record->{$fname} );
    }

    $currval = '';
    // Get the default value, preferably from $_POST
    if ( preg_match("/^(.+)\[(.+)\]$/", $fname, $parts) ) {
      $p1 = $parts[1];
      $p2 = $parts[2];
//      error_log( "DBG: fname=$fname, p1=$p1, p2=$p2, POSTVAL=" . $_POST[$p1][$p2] . ", record=".$this->record->{"$p1"}["$p2"] );
      // FIXME - This could be changed to handle more dimensions on submitted variable names
      if ( isset($_POST[$p1]) ) {
        if ( isset($_POST[$p1][$p2]) ) {
          $currval = $_POST[$p1][$p2];
        }
      }
      else if ( isset($this->record) && is_object($this->record)
                && isset($this->record->{"$p1"}["$p2"])
              ) {
        $currval = $this->record->{"$p1"}["$p2"];
      }
    }
    else {
      if ( isset($_POST[$fname]) ) {
        $currval = $_POST[$fname];
      }
      else if ( isset($this->record) && is_object($this->record) && isset($this->record->{"$base_fname"}) ) {
        $currval = $this->record->{"$base_fname"};
      }
      else if ( isset($this->record) && is_object($this->record) && isset($this->record->{"$fname"}) ) {
        $currval = $this->record->{"$fname"};
      }
    }
    if ( $ftype == "date" ) $currval = nice_date($currval);

    // Now build the entry field and render it
    $field = new EntryField( $ftype, $fname, $this->_ParseAttributes($ftype,$attributes), $currval );
    return $field->Render();
  }


  /**
  * A utility function for a submit button within a data entry table
  * @return string The HTML fragment to display a submit button for the form.
  */
  function SubmitButton( $fname, $fvalue, $attributes = '' )
  {
    $field = new EntryField( 'submit', $fname, $this->_ParseAttributes('submit', $attributes), $fvalue );
    return $field->Render();
  }

  /**
  * A utility function for a data entry line within a table
  * @return string The HTML fragment to display the prompt and field.
  */
  function DataEntryLine( $prompt, $field_format, $ftype='', $fname='', $attributes='', $prefix = '' )
  {
    $attributes = $this->_ParseAttributes( $ftype, $attributes );
    return sprintf( $this->table_line_format, $prompt,
                $this->DataEntryField( $field_format, $ftype, $fname, $attributes, $prefix ),
                $attributes['_help'] );
  }


  /**
  * A utility function for a data entry line, where the prompt is a drop-down.
  * @return string The HTML fragment for the drop-down prompt and associated entry field.
  */
  function MultiEntryLine( $prompt_options, $prompt_name, $default_prompt, $format, $ftype='', $fname='', $attributes='', $prefix )
  {
    global $session;

    $prompt = "<select name=\"$prompt_name\">";

    reset($prompt_options);
    while( list($k,$v) = each($prompt_options) ) {
      $selected = ( ( $k == $default_prompt ) ? ' selected="selected"' : '' );
      $nextrow = "<option value=\"$k\"$selected>$v</option>";
      if ( preg_match('/&/', $nextrow) ) $nextrow = preg_replace( '/&/', '&amp;', $nextrow);
      $prompt .= $nextrow;
    }
    $prompt .= "</select>";

    return $this->DataEntryLine( $prompt, $format, $ftype, $fname, $attributes, $prefix );
  }

}

?>
