<?php

/////////////////////////////////////////////////////////////
//   C L A S S   F O R   M E N U   T H I N G S             //
/////////////////////////////////////////////////////////////
class MenuOption {
   var $label;               // The label for the menu item
   var $target;              // The target URL for the menu
   var $title;               // The title for the itme when moused over
   var $active;              // Whether the menu option is active
   var $sortkey;             // For sorting menu options
   var $style;               // Style to render it
   var $submenu_set;         // The MenuSet that this menu is a parent of
   var $rendered;            // Once it actually is...

   // The thing we click
   function MenuOption( $label, $target, $title="", $style="menu", $sortkey=0 ) {
      $this->label  = $label;
      $this->target = $target;
      $this->title  = $title;
      $this->style  = $style;
      $this->attributes = array();
      $this->active = false;

      $this->rendered = "";

      return $this;
   }

   // Gimme the HTML
   function Render( ) {
     $r = sprintf('<span class="%s_left"></span><a href="%s" class="%s" title="%s"%s>%s</a><span class="%s_right"></span>',
             $this->style, $this->target, $this->style, htmlentities($this->title), "%%attributes%%",
             htmlentities($this->label), $this->style );

     // Now process the generic attributes
     reset( $this->attributes );
     $attribute_values = "";
     while( list($k,$v) = each( $this->attributes ) ) {
       if ( substr($k, 0, 1) == '_' ) continue;
       $attribute_values .= " $k=\"".htmlentities($v)."\"";
     }
     $r = str_replace( '%%attributes%%', $attribute_values, $r );

     $this->rendered = $r;
     return "$r\n";
   }

   // Set arbitrary attributes of the menu option
   function Set( $attribute, $value ) {
     $this->attributes[$attribute] = $value;
     return $this;
   }

   // Mark it as active, with a fancy style to distinguish that
   function Active( $style ) {
     $this->active = true;
     $this->style = $style;
     return $this;
   }

   // This menu option is now promoted to the head of a tree
   function AddSubmenu( &$submenu_set ) {
     $this->submenu_set = &$submenu_set;
     return $this;
   }

   function IsActive( ) {
     return ( $this->active );
   }
}


// Class implementing a hierarchical
class MenuSet {
  var $div_class;    // CSS style to use for the div around the options
  var $main_class;   // CSS style to use for normal menu option
  var $active_class; // CSS style to use for active menu option
  var $options;      // An array of MenuOption objects
  var $parent;       // Any menu option that happens to parent this set

  // Constructor
  function MenuSet( $div_class, $main_class, $active_class ) {
    $this->options = array();
    $this->main_class = $main_class;
    $this->active_class = $active_class;
    $this->div_class = $div_class;
  }

  // Add an option, which is a link.
  function &AddOption( $label, $target, $title="", $active=false, $sortkey=0 ) {
    if ( $this->OptionExists( $label ) ) return false ;

    $new_option =& new MenuOption( $label, $target, $title, $this->main_class, $sortkey );
    array_push( $this->options, &$new_option );
    if ( is_bool($active) && $active == false && $_SERVER['REQUEST_URI'] == $target ) {
      // If $active is not set, then we look for an exact match to the current URL
      $new_option->Active( $this->active_class );
    }
    else if ( is_bool($active) && $active ) {
      // When active is specified as a boolean, the recognition has been done externally
      $new_option->Active( $this->active_class );
    }
    else if ( is_string($active) && preg_match($active,$_SERVER['REQUEST_URI']) ) {
      // If $active is a string, then we match the current URL to that as a Perl regex
      $new_option->Active( $this->active_class );
    }
    return $new_option ;
  }

  // Add an option, which is a submenu
  function &AddSubMenu( &$submenu_set, $label, $target, $title="", $active=false, $sortkey=0 ) {
    $new_option =& $this->AddOption( $label, $target, $title, $active, $sortkey );
    $submenu_set->parent = &$new_option ;
    $new_option->AddSubmenu( &$submenu_set );
    return $new_option ;
  }

  // This is called to see if a menu has any options that are active,
  // most likely so that we can then set that menu active, as is done
  // by LinkActiveSubMenus - should be private.
  function HasActive( ) {
    reset($this->options);
    while( list($k,$v) = each($this->options) ) {
      if ( $v->IsActive() ) return true;
    }
    return false;
  }

  // This is called to see if a menu already has this option
  // by AddOption - should possibly be private.
  function OptionExists( $newlabel ) {
    reset($this->options);
    while( list($k,$v) = each($this->options) ) {
      if ( $newlabel == $v->label ) return true ;
    }
    return false;
  }

  // Currently needs to be called manually before rendering but
  // really should probably be called as part of the render now,
  // and then this could be a private routine.
  function LinkActiveSubMenus( ) {
    reset($this->options);
    while( list($k,$v) = each($this->options) ) {
      if ( isset($v->submenu_set) && $v->submenu_set->HasActive() ) {
        // Note that we need to do it this way, since $v is a copy, not a reference
        $this->options[$k]->Active( $this->active_class );
      }
    }
  }

  // Gimme the HTML.  Now.
  function Render( ) {
    $render_sub_menus = false;
    $r = "<div class=\"$this->div_class\">";
    reset($this->options);
    while( list($k,$v) = each($this->options) ) {
      $r .= $v->Render();
      if ( $v->IsActive() && isset($v->submenu_set) ) {
        $render_sub_menus = $v->submenu_set;
      }
    }
    $r .="</div>\n";
    if ( $render_sub_menus != false ) {
      $r .= $render_sub_menus->Render();
    }
    return $r;
  }
}
?>