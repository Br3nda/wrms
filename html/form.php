<?php
  include("always.php");
//require_once("Session.php");
  include("options.php");
  require_once("maintenance-page.php");
  include("tidy.php");
  if ( isset($f) ) $form = $f;
  $form = eregi_replace( "[^a-z0-9_]", "", $form);

  if ( isset($submit) && "$submit" <> "") {
    include("$form-valid.php");
    if ( "$because" == "" ) include("$form-action.php");
  }

  $title = "$system_name - " . ucfirst($form);
  $right_panel = false;
  include("headers.php");

  include("$form-form.php");

  include("footers.php");

?>
