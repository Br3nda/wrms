<?php
  include("inc/always.php");
  include("inc/options.php");
  $form = "help";
  $topic = str_replace("\\", "", $h );
  $topic = str_replace("/", "", $topic );
  $topic = str_replace("'", "''", $topic );

  if ( "$submit" <> "") {
    include("inc/$form-valid.php");
    if ( "$because" == "" ) include("inc/$form-action.php");
  }

  $title = "$system_name - " . ucfirst($form);
  $right_panel = true;
  include("inc/headers.php");

  include("inc/$form-form.php");

  include("inc/footers.php");

?>
