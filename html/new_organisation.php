<?php
  require_once("always.php");
  require_once("authorisation-page.php");
  $session->LoginRequired();
  require_once("maintenance-page.php");

  require_once("OrganisationPlus.php");

  if ( isset($id) ) $id = intval($id);
  $org = new OrganisationPlus($id);

//  $show = 0;

  // form submitted
  if ( isset($_POST['submit']) ) {
    $session->Dbg( "OrgPlus", "Record %s write type is %s.", $org->Table, "insert" );
    $org->PostToValues();
    if ( $org->Validate() ) {
      $org->Write();
    }
  }
  include("headers.php");

  echo $org->Render();

  include("footers.php");
?>