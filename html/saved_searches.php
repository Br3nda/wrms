<?php
  require_once("always.php");
  require_once("authorisation-page.php");

  // This page requires login.
  $session->LoginRequired();

  require_once("classBrowser.php");
  $c->local_styles[] = "css/browse.css";

  if ( isset($submit) ) {
    $session->Dbg("SavedSearches", "Seem to be submitting a saved search");
    $query_is_public = $GLOBALS['query_is_public'];
    $show_in_menu    = $GLOBALS['show_in_menu'];
    $sql = "";
    foreach( $query_is_public AS $k => $v ) {
      $k = intval($k);
      if ( is_array($v) && ($k == $session->user_no || $session->AllowedTo("Admin") || $session->AllowedTo("Support")) ) {
        foreach( $v AS $k2 => $v2 ) {
          $public = ($v2 == "on" ? "TRUE" : "FALSE");
          $in_menu = ($show_in_menu[$k][$k2] == "on" ? "TRUE" : "FALSE");
          $k2dec = urldecode($k2);
          $session->Dbg("SavedSearches", "Submitted query_is_public[$k][$k2dec] is >>$v2<<  and show_in_menu[$k][$k2dec] is >>%s<<", $show_in_menu[$k][$k2]);
          $sql .= "UPDATE saved_queries SET public = $public , in_menu = $in_menu WHERE user_no = $k AND query_name = ".qpg($k2dec).";";
        }
      }
    }
    $qry = new PgQuery($sql);
    $qry->Exec("SavedSearches");
  }

  $debuggroups["querystring"] = 1;

  $browser = new Browser("Your Saved Searches");
  if ( $session->AllowedTo("Admin") || $session->AllowedTo("Support") ) {
    $browser->AddColumn( 'user_no', 'User#', 'center', '<a href="/user.php?user_no=##user_no##">%d</a>' );
  }
  else {
    $browser->AddHidden( 'user_no' );
  }
  $browser->AddColumn( 'query_name', 'Query Name', 'left', '<a href="/wrsearch.php?style=plain&saved_query=##URL:query_name##">%s</a>' );
  $browser->AddColumn( 'query_type', 'Type', 'center', '%s' );
//  $browser->AddColumn( 'query_sql', 'SQL', 'left', '%s' );
//  $browser->AddColumn( 'query_params', 'Params', 'left', '%s' );
  $browser->AddColumn( 'maxresults', 'Max#', 'left', '%s' );
  $browser->AddColumn( 'rlsort', 'SortBy', 'center', '%s' );
  $browser->AddColumn( 'rlseq', 'Up/Dn', 'center', '%s' );
  $browser->AddColumn( 'updated', 'Last Modified', 'center', '%-16.16s' );
  $browser->AddColumn( 'public_checkbox', 'Public?', '', '<td class="center"><input name="query_is_public[##user_no##][##URL:query_name##]" value="off" type="hidden"><input name="query_is_public[##user_no##][##URL:query_name##]" class="fcheckbox" type="checkbox"%s></td>', "CASE WHEN public THEN ' checked' ELSE '' END" );
  $browser->AddColumn( 'menu_checkbox', 'Menu?', '', '<td class="center"><input name="show_in_menu[##user_no##][##URL:query_name##]" value="off" type="hidden"><input name="show_in_menu[##user_no##][##URL:query_name##]" class="fcheckbox" type="checkbox"%s></td>', "CASE WHEN in_menu THEN ' checked' ELSE '' END" );
  $browser->AddColumn( 'query_params', 'Actions', 'center', '<a href="/wrsearch.php?saved_query=##URL:query_name##" class="submit">Edit Query</a>' );
  $browser->SetJoins( "saved_queries JOIN usr USING(user_no) " );
  $browser->SetWhere( "usr.user_no = ".$session->user_no );
  if ( isset( $_GET['o']) && isset($_GET['d']) ) {
    $browser->AddOrder( $_GET['o'], $_GET['d'] );
  }
  else {
    $browser->AddOrder( 'query_name', 'A' );
  }

  $browser->RowFormat( "<tr onMouseOver=\"LinkHref(this,1);\" title=\"Click to Display Search Detail\" class=\"r%d\">\n", "</tr>\n", '#even' );
  $browser->DoQuery();

  $c->page_title = "Saved Searches";

  include("page-header.php");
  echo "<form method=\"POST\">\n";
  echo $browser->Render();
  echo "<input type=\"submit\" name=\"submit\" class=\"submit\" value=\"Save Changes\">";
  echo "</form>\n";
  include("page-footer.php");
?>