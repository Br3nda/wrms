<?php
  include("inc/always.php");
  include("inc/options.php");
  $form = "help";
  $topic = str_replace("\\", "", $h );
  $topic = str_replace("/", "", $topic );
  $topic = str_replace("'", "''", $topic );

  error_log( "1", 0);
  if ( "$submit" <> "") {
    include("inc/$form-valid.php");
    if ( "$because" == "" ) include("inc/$form-action.php");
//    $because = "<h2>" . ucfirst($form) . " Form Submitted</h2>$because";
  }

  $title = "$system_name - " . ucfirst($form);
  $right_panel = true;
  error_log( "2", 0);
  include("inc/headers.php");

  error_log( "3", 0);
  include("inc/$form-form.php");
  error_log( "4", 0);

  include("inc/footers.php");

?>