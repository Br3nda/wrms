<?php
  include("inc/always.php");
  include("inc/options.php");
  include("inc/tidy.php");
  if ( isset($f) ) $form = $f;
  $form = eregi_replace( "[^a-z0-9_]", "", $form);

  if ( "$submit" <> "") {
    include("inc/$form-valid.php");
    if ( "$because" == "" ) include("inc/$form-action.php");
  }

  $title = "$system_name - " . ucfirst($form);
  $right_panel = false;
  include("inc/headers.php");

  include("inc/$form-form.php");

  include("inc/footers.php");

?>