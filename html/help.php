<?php
  include("always.php");
  include("options.php");
  $form = "help";
  $topic = str_replace("\\", "", $h );
  $topic = str_replace("/", "", $topic );
  $topic = str_replace("'", "''", $topic );

  if ( "$submit" <> "") {
    include("$form-valid.php");
    if ( "$because" == "" ) include("$form-action.php");
  }

  $title = "$system_name - " . ucfirst($form);
  $right_panel = true;
  include("headers.php");

  include("$form-form.php");

  include("footers.php");

?>
