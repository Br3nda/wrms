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

  // Force some variables to have values.
  if ( !isset($format) ) $format = "";
  if ( !isset($style) ) $style = "";
  if ( !isset($savedquery) ) $savedquery = "";
  if ( !isset($qs) ) $qs = "";
  if ( !isset($org_code) ) $org_code = "";
  if ( !isset($system_code) ) $system_code = "";
  if ( !isset($search_for) ) $search_for = "";
  if ( !isset($interested_in) ) $interested_in = "";
  if ( !isset($allocated_to) ) $allocated_to = "";
  if ( !isset($type_code) ) $type_code = "";
  if ( !isset($inactive) ) $inactive = "";
  if ( !isset($user_no) ) $user_no = "";
  if ( !isset($requested_by) ) $requested_by = "";
  if ( !isset($from_date) ) $from_date = "";
  if ( !isset($to_date) ) $to_date = "";
  if ( !isset($where_clause) ) $where_clause = "";
  if ( !isset($default_search_statuses) ) $default_search_statuses = '@NRILKTQADSPZU';

  // If they didn't provide a $columns, we use a default.
  if ( !isset($columns) || $columns == "" || $columns == array() ) {
    $columns = array("request_id","lfull","request_on","lbrief","status_desc","request_type_desc","request.last_activity");
  }
  elseif ( ! is_array($columns) )
    $columns = explode( ',', $columns );

  // Internal column names (some have 'nice' alternatives defined in header_row() )
  // The order of these defines the ordering when columns are chosen
  $available_columns = array(
          "request_id" => "WR&nbsp;#",
          "lby_fullname" => "Created By",
          "lfull" => "Request For",
          "request_on" => "Request On",
          "lbrief" => "Description",
          "request_type_desc" => "Type",
          "status_desc" => "Status",
          "request.last_activity" => "Last Chng",
          "active" => "Active",
   );


  include_once("search_listing_functions.php");
  include_once("search_build_query.php");

  include("headers.php");
  echo '<script language="JavaScript" src="/js/wrsearch.js"></script>' . "\n";

  include_once("search_form.php");
  include_once("search_list_results.php");

  include("footers.php");

?>