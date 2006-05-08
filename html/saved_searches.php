<?php
  require_once("always.php");
  require_once("authorisation-page.php");

  // This page requires login.
  $session->LoginRequired();

  require_once("classBrowser.php");
  $c->stylesheets[] = "css/browse.css";


  $browser = new Browser("");
  $browser->AddColumn( 'user_no', 'User#', 'center', '<a href="/user.php?user_no=##user_no##">%d</a>' );
  $browser->AddColumn( 'query_name', 'Query Name', 'left', '<a href="/wrsearch.php?style=plain&saved_query=##URL:query_name##">%s</a>' );
  $browser->AddColumn( 'query_type', 'Type', 'center', '%s' );
//  $browser->AddColumn( 'query_sql', 'SQL', 'left', '%s' );
//  $browser->AddColumn( 'query_params', 'Params', 'left', '%s' );
  $browser->AddColumn( 'maxresults', 'Max#', 'left', '%s' );
  $browser->AddColumn( 'rlsort', 'SortBy', 'center', '%s' );
  $browser->AddColumn( 'rlseq', 'Up/Dn', 'center', '%s' );
  $browser->AddColumn( 'public', 'Public?', 'center', '%s' );
  $browser->AddColumn( 'in_menu', 'Menu?', 'center', '%s' );
  $browser->AddColumn( 'updated', 'Last Modified', 'center', '%-16.16s' );
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
