<?php
  require_once("always.php");
  require_once("authorisation-page.php");

  // This page requires login.
  $session->LoginRequired();

  require_once("classBrowser.php");
  $c->stylesheets[] = "css/browse.css";


  $browser = new Browser("");
  $browser->AddColumn( 'user_no', 'User#', 'center', '<a href="/user.php?user_no=##user_no##">%d</a>' );
  $browser->AddColumn( 'query_name', 'Query Name', 'center', '<a href="/wrsearch.php?user_no=##user_no##">%d</a>' );
  $browser->SetJoins( "saved_queries JOIN usr USING(user_no) " );
  $browser->SetWhere( "usr.user_no = ".$session->user_no );
  if ( isset( $_GET['o']) && isset($_GET['d']) ) {
    $browser->AddOrder( $_GET['o'], $_GET['d'] );
  }
  else
    $browser->AddOrder( 'query_name', 'A' );

  $browser->RowFormat( "<tr onMouseOver=\"LinkHref(this,1);\" title=\"Click to Display Search Detail\" class=\"r%d\">\n", "</tr>\n", '#even' );
  $browser->DoQuery();

  $c->page_title = "Saved Searches";

  include("page-header.php");
  echo $browser->Render();
  include("page-footer.php");
?>
