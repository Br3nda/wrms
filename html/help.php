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
//    $because = "<h2>" . ucfirst($form) . " Form Submitted</h2>$because";
  }

  $title = "$system_name - " . ucfirst($form);
  $right_panel = false;
  include("inc/headers.php");

  include("inc/$form-form.php");

  include("inc/footers.php");

?>