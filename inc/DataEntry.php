<?php

/////////////////////////////////////////////////////////////
//   C L A S S   F O R   D A T A   E N T R Y   T H I N G S //
/////////////////////////////////////////////////////////////
class EntryField
{
   var $fname;               // The original field name
   var $ftype;               // The type of entry field
   var $current;             // The current value
   var $attributes;          // An array of key value pairs
   var $rendered;            // Once it actually is...

  /**
   * Initialise an EntryField, used for data entry
   *
   * @param text $intype The type of field:
   *    select | lookup | date | checkbox | textarea
   *    (anything else is dealt with as "text")
   *
   * @param text $inname The name of the field.
   *
   * @param text $inextra An associative array of extra attributes to
   *                      be applied to the field.  Optional.
   *
   * @param text $inname The current value to use to initialise the
   *                     field.   Optional.
   *
   * @return object
   *
   * @author Andrew McMillan <andrew@catalyst.net.nz>
   */
    function EntryField( $intype, $inname, $inextra="", $current_value="" )
   {
      $this->ftype = $intype;
      $this->fname = $inname;
      $this->current = $current_value;

      if ( isset($this->{"new_$intype"}) && function_exists($this->{"new_$intype"}) ) {
        // Optionally call a function within this object called "new_<intype>" for setup
        $this->{"new_$intype"}( $inextra );
      }
      else if ( is_array($inextra) ) {
        $this->attributes = $inextra;
      }
      else {
      }

      $this->rendered = "";

      return $this;
   }

  /**
   * Render an EntryField into HTML
   *
   * @return text  An HTML fragment for the data-entry field.
   *
   * @author Andrew McMillan <andrew@catalyst.net.nz>
   */
   function Render() {
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
           $r .= ">$v</option>\n" ;
         }
         $r .= "</select>\n";
         break;

       case "lookup":
         $r .= "select name=\"$this->fname\"%%attributes%%>\n";
         if ( isset($this->attributes["_all"]) )
           $r .= sprintf("<option value=\"all\"".("all"==$this->current?" selected":"").">%s</option>\n", $this->attributes["_all"] );
         if ( isset($this->attributes["_null"]) )
           $r .= sprintf("<option value=\"\"".(""==$this->current?" selected":"").">%s</option>\n", $this->attributes["_null"] );
         if ( isset($this->attributes["_zero"]) ) {
           $r .= sprintf("<option value=\"0\"".(0==$this->current?" selected":"").">%s</option>\n", $this->attributes["_zero"] );
         }
         if ( isset($this->attributes["_sql"]) ) {
           $qry = new PgQuery( $this->attributes["_sql"] );
         }
         else {
           list( $tbl, $fld ) = explode("|", $this->attributes['_type'], 2);
           $qry = new PgQuery( "SELECT lookup_code, lookup_desc FROM lookup_code WHERE source_table = ? AND source_field = ? ORDER BY lookup_seq, lookup_code", $tbl, $fld );
         }
         $r .= $qry->BuildOptionList( $this->current, "rndr:$this->fname" );
         $r .= "</select>\n";
         break;

       case "date":
         if ( !isset($this->attributes['size']) || $this->attributes['size'] == "" ) $size = " size=12";
         $r .= "input type=\"text\" name=\"$this->fname\"$size value=\"".htmlentities($this->current)."\"%%attributes%%>\n";
         break;

       case "checkbox":
         $checked =  ( $this->current == 't' || $this->current == 'on' ? " checked" : "" );
         $r .= "input type=\"checkbox\" name=\"$this->fname\"$checked%%attributes%%>\n";
         break;

       case "button":
         $r .= "input type=\"button\" name=\"$this->fname\"%%attributes%%>\n";
         break;

       case "textarea":
         $r .= "textarea name=\"$this->fname\"%%attributes%%>$this->current</textarea>\n";
         break;

       case "file":
         if ( !isset($this->attributes['size']) || $this->attributes['size'] == "" ) $size = " size=25";
         $r .= "input type=\"file\" name=\"$this->fname\"$size value=\"".htmlentities($this->current)."\"%%attributes%%>\n";
         break;

       default:
         $r .= "input type=\"text\" name=\"$this->fname\" value=\"".htmlentities($this->current)."\"%%attributes%%>\n";
         break;
     }

     // Now process the generic attributes
     reset( $this->attributes );
     $attribute_values = "";
     while( list($k,$v) = each( $this->attributes ) ) {
       if ( substr($k, 0, 1) == '_' ) continue;
       $attribute_values .= " $k=\"".htmlentities($v)."\"";
     }
     $r = str_replace( '%%attributes%%', $attribute_values, $r );

     $this->rendered = $r;
     return $r;
   }

   function new_lookup( $inextra ) {
     $this->attributes = $inextra;
   }
}

class EntryForm
{
  var $action;          // The submit action for the form
  var $record;          // The record that the form is dealing with
  var $editmode;        // Whethere we are editing, or not
  var $name;            // The name of the form
  var $class;           // The CSS class of the form
  var $break_line_format;
  var $table_line_format;
  var $saved_line_format;

  function EntryForm( $action, &$record, $editmode=false )
  {
    $this->action   = $action;
    $this->record   = &$record;
    $this->editmode = $editmode;
    $this->break_line_format = '<tr><th class="ph" colspan="2">%s</th></tr>'."\n";
    $this->table_line_format = '<tr><th class="prompt">%s</th><td class="entry">%s<span class="help">%s</span></td></tr>'."\n";

    return $this;
  }

  function NoHelp( ) {
    $this->break_line_format = '<tr><th class="ph" colspan="2">%s</th></tr>'."\n";
    $this->table_line_format = '<tr><th class="prompt">%s</th><td class="entry">%s</td></tr>'."\n";
  }

  function HelpInLine( ) {
    $this->break_line_format = '<tr><th class="ph" colspan="2">%s</th></tr>'."\n";
    $this->table_line_format = '<tr><th class="prompt">%s</th><td class="entry">%s<span class="help">%s</span></td></tr>'."\n";
  }

  function HelpInCell( ) {
    $this->break_line_format = '<tr><th class="ph" colspan="3">%s</th></tr>'."\n";
    $this->table_line_format = '<tr><th class="prompt">%s</th><td class="entry">%s</td><td class="help">%s</td></tr>'."\n";
  }

  function SimpleForm( ) {
    $this->break_line_format = '%s'."\n";
    $this->table_line_format = '<span class="prompt">%s:</span>&nbsp;<span class="entry">%s</span>'."\n";
  }

  function TempLineFormat( $new_format = '<span class="prompt">%s:</span>&nbsp;<span class="entry">%s</span>' ) {
    $this->saved_line_format = $this->table_line_format;
    $this->table_line_format = $new_format ."\n";
  }

  function RevertLineFormat( $new_format = '<span class="prompt">%s</span>&nbsp;<span class="entry">%s</span>' ) {
    if ( isset($this->saved_line_format) ) {
      $this->table_line_format = $this->saved_line_format;
    }
  }

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

  function EndForm( ) {
    return "</form>\n";
  }

  //////////////////////////////////////////////////////
  // A utility function for a heading line within a data entry table
  //////////////////////////////////////////////////////
  function BreakLine( $text = '' )
  {
    return sprintf( $this->break_line_format, $text);
  }

  //////////////////////////////////////////////////////
  // A utility function for a hidden field within a data entry table
  //////////////////////////////////////////////////////
  function HiddenField($fname,$fvalue) {
    return sprintf( '<input type="hidden" name="%s" value="%s" />%s', $fname, htmlentities($fvalue), "\n" );
  }

  //////////////////////////////////////////////////////
  // A utility function for a submit button within a data entry table
  //////////////////////////////////////////////////////
  function SubmitButton( $fname, $fvalue )
  {
    return sprintf( $this->table_line_format, '&nbsp;',
                       sprintf('<input type="submit" name="%s" value="%s" class="submit" />',
                                               $fname, $fvalue), "");
  }

  /////////////////////////////////////////////////////
  // Internal function for parsing the type extra on a field.
  /////////////////////////////////////////////////////
  function _ParseTypeExtra( $ftype = '', $type_extra = '' )  {
    if ( !is_array($type_extra) ) {
      list( $k, $v ) = explode( '=', $type_extra );
      $type_extra = array( $k => $v );
    }

    // Default the help to the title, or to blank
    if ( !isset($type_extra['_help']) ) {
      $type_extra['_help'] = "";
      if ( isset($type_extra['title']) )
        $type_extra['_help'] = $type_extra['title'];
    }

    // Default the style to fdate, ftext, fcheckbox etc.
    if ( !isset($type_extra['class']) ) {
      $type_extra['class'] = "f$ftype";
    }

    return $type_extra;
  }

  //////////////////////////////////////////////////////
  // A utility function for a data entry line within a table
  //////////////////////////////////////////////////////
  function DataEntryField( $format, $ftype='', $fname='', $type_extra='' )
  {
    global $session;

    if ( ($fname == '' || $ftype == '') ) {
      // Displaying never-editable values
      return $format;
    }
    elseif ( !$this->editmode ) {
      // Displaying editable values when we are not editing
      return sprintf($format, $this->record->{$fname} );
    }

    $currval = '';
    // Get the default value, preferably from $_POST
    if ( preg_match("/^(.+)\[(.+)\]$/", $fname, $parts) ) {
      $p1 = $parts[1];
      $p2 = $parts[2];
      error_log( "DBG: fname=$fname, p1=$p1, p2=$p2, POSTVAL=" . $_POST[$p1][$p2] . ", record=".$this->record->{"$p1"}["$p2"] );
      // fixme - This could be changed to handle more dimensions on submitted variable names
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
      else if ( isset($this->record) && is_object($this->record) && isset($this->record->{"$fname"}) ) {
        $currval = $this->record->{"$fname"};
      }
    }
    if ( $ftype == "date" ) $currval = format_date($currval);

    // Now build the entry field and render it
    $field = new EntryField( $ftype, $fname, $this->_ParseTypeExtra($ftype,$type_extra), $currval );
    return $field->Render();
  }


  //////////////////////////////////////////////////////
  // A utility function for a data entry line within a table
  //////////////////////////////////////////////////////
  function DataEntryLine( $prompt, $format, $ftype='', $fname='', $type_extra='' )
  {
    $type_extra = $this->_ParseTypeExtra( $ftype, $type_extra );
    return sprintf( $this->table_line_format, $prompt,
                $this->DataEntryField( $format, $ftype, $fname, $type_extra ),
                $type_extra['_help'] );
  }


  //////////////////////////////////////////////////////
  // A utility function for a data entry line, where the
  // prompt is a drop-down.
  //////////////////////////////////////////////////////
  function MultiEntryLine( $prompt_options, $prompt_name, $default_prompt, $format, $ftype='', $fname='', $type_extra='' )
  {
    global $session;

    $prompt = "<select name=\"$prompt_name\">\n";

    reset($prompt_options);
    while( list($k,$v) = each($prompt_options) ) {
      $selected = ( ( $k == $default_prompt ) ? ' selected="selected"' : '' );
      $nextrow = "<option value=\"$k\"$selected>$v</option>\n";
      if ( preg_match('/&/', $nextrow) ) $nextrow = preg_replace( '/&/', '&amp;', $nextrow);
      $prompt .= $nextrow;
    }
    $prompt .= "</select>\n";

    return $this->DataEntryLine( $prompt, $format, $ftype, $fname, $type_extra );
  }
}

?>