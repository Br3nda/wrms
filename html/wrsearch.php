<?php
  include("always.php");
  require_once("authorisation-page.php");
  if ( !$session->logged_in ) {
    include("headers.php");
    echo "<h3>Please log on for access to work requests</h3>\n";
    include("footers.php");
    exit;
  }

  if ( isset($qry) && "$qry" != "" && "$action" == "delete" ) {
    $q = new PgQuery( "DELETE FROM saved_queries WHERE user_no = '$session->user_no' AND lower(query_name) = lower(?);", $qry);
    $q->Exec("wrsearch");
    unset($qry);
  }

  require_once("maintenance-page.php");

  include_once("search_listing_functions.php");
  include_once("search_build_query.php");

  include("headers.php");
  echo '<script language="JavaScript" src="/js/wrsearch.js"></script>' . "\n";

  include_once("search_form.php");
  include_once("search_list_results.php");

  include("footers.php");

?>