<?php
class Field
{
  var $type;
  var $name;
  var $prompt;
  var $seq;
  var $x;
  var $y;
  var $select;
  var $default;
  var $current;

  function Field( $type, $name, $prompt, $seq = 50, $default = "", $current = "", $inextra = "" ) {
    $this->type = $type;
    $this->seq = $seq;
    $this->name = $name;
    $this->prompt = $prompt;
    $this->default = $default;
    $this->current = $current;

    if ( isset($this->{"new_$type"}) && function_exists($this->{"new_$type"}) ) {
      $this->{"new_$type"}( $inextra );
    }
    else if ( is_array($inextra) ) {
      $this->attributes = $inextra;
    }
    else {
    }

    return $this;

  }

  function new_lookup( $inextra ) {
    $this->attributes = $inextra;
  }

  function BuildHTML( $stub = "fh_" ) {
    global $sysname;
    error_log( "$sysname DBG: Building HTML for field $this->name" );
    if ( substr($this->type, 0,4) == "btn_" ) {
      $type = substr($this->type, 4);
      $h = "<tr><td class=\"fprompt\">&nbsp;</td><td class=\"fdata\"><input type=\"$type\" name=\"$stub$this->name\" value=\"".$this->prompt."\"></td></tr>\n";
    }
    else {
      $h = "<tr><th class=fprompt>$this->prompt</th>";
      $r = "<";
      switch ( $this->type ) {
        case "select":
          $r .= "select name=\"$this->name\"%%attributes%%>";
          reset( $this->attributes );
          while( list($k,$v) = each( $this->attributes ) ) {
            if ( substr($k, 0, 1) != '_' ) continue;
            $k = substr($k,1);
            $r .= "<option value=\"".htmlentities($k)."\"";
            if ( "$this->current" == "$k" ) $r .= " selected";
            $r .= ">$v</option>\n" ;
          }
          $r .= "</select>\n";
          break;

        case "lookup":
          $r .= "select name=\"$this->name\"%%attributes%%>";
          if ( isset($this->attributes["_sql"]) ) {
            $qry = new PgQuery( $this->attributes["_sql"] );
          }
          else {
            $qry = new PgQuery( "SELECT lookup_id, lookup_description FROM lookups WHERE lookup_type = ? ORDER BY lookup_type, lookup_seq, lookup_id", $this->attributes['_type'] );
          }
          error_log( "$sysname DBG: lookup $this->name via: $qry->querystring" );
          $r .= $qry->BuildOptionList( $this->current, "rndr:$this->name" );
          $r .= "</select>\n";
          break;

        case "multiselect":
          $r .= "select multiple name=\"$this->name" . "[]\"%%attributes%%>";
          if ( isset($this->attributes["_sql"]) ) {
            $qry = new PgQuery( $this->attributes["_sql"] );
          }
          else {
            $qry = new PgQuery( "SELECT lookup_id, lookup_description FROM lookups WHERE lookup_type = ? ORDER BY lookup_type, lookup_seq, lookup_id", $this->attributes['_type'] );
          }
          error_log( "$sysname DBG: lookup $this->name via: $qry->querystring" );
          $r .= $qry->BuildOptionList( $this->current, "rndr:$this->name" );
          $r .= "</select>\n";
          break;

        case "radio":
    $r = "";
          $fmt .= "<label><input type=\"radio-set\" name=\"$this->name\" value=\"%s\"%s%%attributes%%>&nbsp;%s&nbsp; </label>";
          if ( isset($this->attributes["_values"]) ) {
      foreach( $this->attributes["_values"] as $k => $v ) {
        $selected = ($this->current == $k ? " selected" : "" );
        $r .= sprintf( $fmt, $k, $selected, $v );
      }
    }
    else {
            if ( isset($this->attributes["_sql"]) ) {
              $qry = new PgQuery( $this->attributes["_sql"] );
            }
            else {
              $qry = new PgQuery( "SELECT lookup_id, lookup_description FROM lookups WHERE lookup_type = ? ORDER BY lookup_type, lookup_seq, lookup_id", $this->attributes['_type'] );
            }
            error_log( "$sysname DBG: lookup $this->name via: $qry->querystring" );
            $r .= $qry->BuildRadioSet( $fmt, $this->current, "rndr:$this->name" );
    }
          break;

        case "date":
          if ( !isset($this->attributes['size']) || $this->attributes['size'] == "" ) $size = " size=12";
          $r .= "input type=\"text\" name=\"$this->name\"$size value=\"".htmlentities($this->current)."\"%%attributes%%>\n";
          break;

        case "textarea":
          $r .= "textarea type=\"$this->type\" name=\"$this->name\" %%attributes%%>$this->current</textarea>\n";
          break;

        default:
          $r .= "input type=\"$this->type\" name=\"$this->name\" value=\"".htmlentities($this->current)."\"%%attributes%%>\n";
          break;
      }

      // Now process the generic attributes
      if ( is_array($this->attributes) || is_object($this->attributes) ) {
        reset( $this->attributes );
        $attribute_values = "";
        while( list($k,$v) = each( $this->attributes ) ) {
          if ( substr($k, 0, 1) == '_' ) continue;
          $attribute_values .= " $k=\"".htmlentities($v)."\"";
        }
        $r = str_replace( '%%attributes%%', $attribute_values, $r );
      }

      $h .= "<td class=fdata>$r</td></tr>\n";

    }
    return $h;
  }
}


class Form
{
  var $phase = 'display';

  var $action = "";
  var $target = "";
  var $method = "";
  var $record = "";

  var $fields;

  function Form( $name = "fh", $rec = false ) {
    $this->name = $name;
    $this->record = $rec;
    $this->fields = array();
  }

  function AddField( $type, $name, $prompt, $seq = 50, $default = "", $extra = "" ) {
    if ( is_object($this->record) ) {
      $current = $this->record->{$name};
    }
    else {
      $current = "";
    }
    if ( isset($this->fields[$name]) ) {
      $this->fields[$name]->type = $type;
      $this->fields[$name]->seq  = $seq;
      $this->fields[$name]->name = $name;
      $this->fields[$name]->prompt = $prompt;
      $this->fields[$name]->default = $default;
      $this->fields[$name]->current = $current;
      $this->fields[$name]->attributes = $extra;
    }
    else {
      $this->fields[$name] = new Field( $type, $name, $prompt, $seq, $default, $current, $extra );
    }
  }

  function AddButton( $type, $name, $label, $seq = 50 ) {
    $type = "btn_$type";
    $name = "btn_$name";
    $prompt = $label;
    $this->fields[$name] = new Field( $type, $name, $prompt, $seq );
  }

  function BuildHTML( ) {
    global $sysname;
    $fst = sprintf( "<form action=\"%s\" target=\"%s\" method=\"%s\">\n", $this->action, $this->target, $this->method);

    $fst .= "<table>";
    while( list($k,$v) = each($this->fields) ) {
      $fst .= $v->BuildHTML();
    }
    $fst .= "</table></form>";
    return $fst;
  }
}

?>