<?php
  include("always.php");
  require_once("authorisation-page.php");
  $session->LoginRequired();

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
  include("page-header.php");

  include("$form-form.php");

  include("page-footer.php");

?>
