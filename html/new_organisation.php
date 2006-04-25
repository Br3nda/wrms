<?php
  require_once("always.php");

  require_once("OrganisationPlus.php");

  $reg = new OrganisationPlus();

  $c->page_title = "Organisation";

  $show = 0;

  // form submitted
  if ( isset($_POST['submit']) ) {
    $session->Log( "DBG: Record %s write type is %s.", $reg->Table, "insert" );
    $reg->PostToValues();
    if ( $reg->Validate() ) {
      $reg->Write();
    }
  }
  include("page-header.php");

  echo $org->Render();

  include("page-footer.php");
?>