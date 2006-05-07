<?php
require_once("organisation-selectors-sql.php");

function ggv($var, $i = "nope", $j = "nope") {

  $answer = null;

  // Return the post value, or failing that, the GET value...
  if ( isset($_POST[$var]) ) $answer = $_POST[$var];
  else if ( isset($_GET[$var]) ) $answer = $_GET[$var];

  if ( "$i" != "nope" ) {
    $answer = $answer[$i];
    if ( "$j" != "nope" ) {
      $answer = $answer[$j];
    }
  }
  return $answer;
}


/////////////////////////////////////////////////////////////
// Render - Return HTML to show the W/R
//   A separate function is called for each logical area
//   on the W/R.
/////////////////////////////////////////////////////////////
function RenderSearchForm( $target_url ) {
  global $session, $theme, $search_record;

  $html = "";
  $search_record = (object) array();
  $org_code = intval($GLOBALS['org_code']);
  if ( $org_code > 0 ) $search_record->org_code = $org_code;
//  $session->Log( 'DBG: isset($_POST[submit])=%s isset($_GET[saved_query])=%s', isset($_POST[submit]), isset($_GET['saved_query'] ) );
  if ( !isset($_POST['submit']) && isset($_GET['saved_query'])) {
    $qry = new PgQuery("SELECT query_params FROM saved_queries WHERE (user_no = ? OR public ) AND lower(query_name) = lower(?);",
                     $session->user_no, $_GET['saved_query'] );
    if ( $qry->Exec('RenderSearchForm') && $qry->rows == 1 && $row = $qry->Fetch() ) {
      $_POST = unserialize($row->query_params);
    }
  }

  $ef = new EntryForm( $REQUEST_URI, $search_record, true );

  // We do the formatting fairly carefully here...
  $ef->SimpleForm('<span style="white-space: nowrap"><span class="srchp">%s:</span><span class="srchf">%s</span></span> ' );

  $html .= $ef->StartForm( array("autocomplete" => "off", "onsubmit" => "return CheckSearchForm();" ) );

  $html .= "<table border=0 cellspacing=2 cellpadding=0 align=center class=row0 width=100% style=\"border: 1px dashed #aaaaaa;\">\n";
  $html .= "<tr><td width=100%><table border=0 cellspacing=0 cellpadding=0 width=100%><tr valign=middle><td width=100%>\n";

  $html .= $ef->DataEntryLine( "Find", "%s", "text", "search_for",
            array( "size" => 10, "class" => "srchf",
                   "title" => "Search for free text in the request or notes.  Regular expressions are OK too." ) );

  // Organisation drop-down
  if ( $session->AllowedTo("Admin") || $session->AllowedTo("Support") || $session->AllowedTo("Contractor") ) {

    $html .= $ef->DataEntryLine( "Organisation", "", "lookup", "org_code",
              array("_sql" => SqlSelectOrganisations($org_code),
                    "_null" => "-- All Organisations --", "onchange" => "OrganisationChanged();",
                    "title" => "The organisation that this work will be done for.",
                    "class" => "srchf",
                    "style" => "width: 18em" ) );
  }

  // System (within Organisation) drop-down
  $html .= $ef->DataEntryLine( "System", "", "lookup", "system_id",
            array("_sql" => SqlSelectSystems($org_code),
                  "_null" => "-- All Systems --", "onchange" => "SystemChanged();",
                  "title" => "The business system that this request applies to.",
                  "class" => "srchf",
                  "style" => "width: 18em") );

  $html .= $ef->DataEntryLine( "By", "", "lookup", "requested_by",
            array("_sql" => SqlSelectRequesters($org_code),
                  "_null" => "-- Any Requester --", "onchange" => "PersonChanged();",
                  "title" => "The client who is requesting this, or who is in charge of ensuring it happens.",
                  "class" => "srchf",
                  "style" => "width: 12em" ) );

  $html .= $ef->DataEntryLine( "Watching", "", "lookup", "interested_in",
            array("_sql" => SqlSelectSubscribers($org_code), "_null" => "-- Any Interested User --",
                  "title" => "The client who is requesting this, or who is in charge of ensuring it happens.",
                  "class" => "srchf",
                  "style" => "width: 12em" ) );

  // Person Assigned to W/R
  $html .= $ef->DataEntryLine( "ToDo", "", "lookup", "allocated_to",
            array("_sql" => SqlSelectSubscribers($org_code),
                  "_null" => "-- Any Assigned User --",
                  "_nobody" => "-- Not Yet Allocated --",
                  "class" => "srchf",
                  "title" => "A person who has been assigned to work on requests.",
                  "style" => "width: 12em" ) );

  // Date range
  $html .= $ef->DataEntryLine( "Last Action", "%s", "date", "from_date",
            array( "size" => 10, "class" => "srchf",
                   "title" => "Only show requests with action after this date." ) );
  $html .= "<a href=\"javascript:show_calendar('forms.form.from_date');\" onmouseover=\"window.status='Date Picker';return true;\" onmouseout=\"window.status='';return true;\">".$theme->Image("date-picker.gif")."</a> &nbsp; \n";

  $html .= $ef->DataEntryLine( "To", "%s", "date", "to_date",
            array( "size" => 10, "class" => "srchf",
                   "title" => "Only show requests with action before this date." ) );
  $html .= "<a href=\"javascript:show_calendar('forms.form.to_date');\" onmouseover=\"window.status='Date Picker';return true;\" onmouseout=\"window.status='';return true;\">".$theme->Image("date-picker.gif")."</a> &nbsp; \n";

  // Type of Request
  $html .= $ef->DataEntryLine( "Type", $this->request_type_desc, "lookup", "type_code",
            array("_type" => "request|request_type",
                  "_null" => "-- All Types --",
                  "class" => "srchf",
                  "style" => "width: 8em",
                  "title" => "Only show this type of request") );

  if ( ($session->AllowedTo("Admin") /* || $session->AllowedTo("Support") */ ) ) {
//    $html .= "<div id=\"whereclause\">";
    $html .= $ef->DataEntryLine( "Where", "%s", "text", "where_clause",
              array( "size" => 60, "class" => "srchf",
                    "title" => "Add an SQL 'WHERE' clause to further refine the search - you will need to know what you are doing..." ) );
//    $html .= "</div>";
  }


  $html .= "<table border='0' cellspacing='0' cellpadding='0' width='100%'><tr>";
  $html .= "<td style=\"vertical-align: top; padding-top: 0.3em; white-space:wrap;\"><span class=\"srchp\">Status:</span></td><td valign='top'>\n";
  $sql = "SELECT * FROM lookup_code WHERE source_table='request' ";
  $sql .= " AND source_field='status_code' ";
  $sql .= " ORDER BY source_table, source_field, lookup_seq, lookup_code ";
  $qry = new PgQuery( $sql );
  if ( $qry->Exec("RenderSearchForm") && $qry->rows > 0 ) {
    $i = 0;
    while ( $status = $qry->Fetch() ) {
      $ef->record->incstat[$status->lookup_code] = (strpos($GLOBALS['default_search_statuses'],$status->lookup_code) != false?1:'');
      if ( $i++ > 0 ) $html .= " ";
      $html .= $ef->DataEntryField( "%s", "checkbox", "incstat[$status->lookup_code]",
              array("_label" => $status->lookup_desc, "class" => "srchf", "value" => 1 ) );
      // if ( $i++  == round($qry->rows / 2) ) $html .= "<br />";
    }
    $html .= $ef->DataEntryField( "%s", "checkbox", "inactive",
              array("_label" => "inactive", "class" => "srchf", "value" => 1 ) );
    $html .= "</td>\n";
  }
  $html .= "</tr></table>\n";


  $html .= RenderTagsPanel($ef);

  $html .= RenderColumnSelections($ef);

  // style="display: block; float:right; clear: left;"
  $html .= '<div id="savesearch">';
  $html .= $ef->DataEntryLine( "Save as", "%s", "text", "savelist",
              array( "size" => 20, "class" => "srchf",
                    "title" => "A name to use to refer to this query in the future." ) );
  $html .= $ef->DataEntryField( "%s", "checkbox", "save_query_order",
              array("_label" => "With Order?", "class" => "srchf", "value" => 1 ) );
  $html .= $ef->DataEntryField( "%s", "checkbox", "save_public",
              array("_label" => "Public?", "class" => "srchf", "value" => 1 ) );

  $search_record->save_hotlist = 't';
  $html .= $ef->DataEntryField( "%s", "checkbox", "save_hotlist",
              array("_label" => "In my menu?", "class" => "srchf", "value" => 1 ) );

  $html .= $ef->SubmitButton( "submit", "Save Query",
            array("title" => "Save this query so you can run it again." ) );
  $html .= "</div>";

  $html .= $ef->DataEntryLine( "Max results", "%s", "text", "maxresults",
              array( "size" => 6, "class" => "srchf",
                    "title" => "The maximum number of rows to show in the listing" ) );

  $html .= $ef->SubmitButton( "submit", "Run Query",
            array("title" => "Run a query with these settings" ) );


  $html .= "</td></tr></table>\n";
  $html .= "</td></tr></table>\n";
  $html .= $ef->EndForm();

  return $html;
}



/////////////////////////////////////////////////////////////
// RenderTagsPanel - Return HTML to show the Tags panel of
//   the search screen.
/////////////////////////////////////////////////////////////
function RenderTagsPanel( $ef ) {
  global $session;

  $html = "";
  $org_code = intval(ggv('org_code'));

  // Tags List format is as simple as possible...
  $ef->TempLineFormat('<span class="srchf" style="white-space: nowrap">%s%s</span>' );

  $sql = "SELECT tag_id, tag_description ";
  if ( $org_code == 0 && ($session->AllowedTo("Admin") || $session->AllowedTo("Support") ) )
    $sql .= " || ' (' || abbreviation || ')' AS tag_description ";

  $sql .= "FROM organisation NATURAL JOIN organisation_tag ";
  $sql .= "WHERE organisation.active AND organisation_tag.active ";
  if ( $org_code != 0 && ($session->AllowedTo("Admin") || $session->AllowedTo("Support") ) )
    $sql .= "AND organisation.org_code = $org_code ";
  else if ( ! ($session->AllowedTo("Admin") || $session->AllowedTo("Support") ) )
    $sql .= "AND organisation.org_code = $session->org_code ";
  $sql .= "ORDER BY lower(abbreviation), tag_sequence, lower(tag_description)";

  $html .= "<div id=\"tagselect\" style=\"display :none;\">";
  $html .= $ef->DataEntryLine( "Tag List", "", "lookup", "orgtaglist",
            array("_sql" => SqlSelectOrgTags($org_code), "_null" => "-- Any Tag --", /* "onchange" => "TagChanged();", */
                  "title" => "A tag that you want included or excluded from the report.",
                  "class" => "srchf",
                  "id"    => "taglistselect",
                  "style" => "width: 12em" ) );
  $html .= $ef->DataEntryLine( "Tag Search Fields", "%s", "text", "taglist_count", array("id"=>"taglistcount") );
  $html .= "</div>";

  $tag_and_v = "";
  $tag_lb_v = "";
  $tag_list_v = "";
  $tag_rb_v = "";
  $taglist_count = intval(ggv('taglist_count'));
  for ( $i=0; $i < $taglist_count; $i++ ) {
    $tag_and_v .= ggv('tag_and',$i) . ',';
    $tag_lb_v .= ggv('tag_lb',$i) . ',';
    $tag_list_v .= ggv('tag_list',$i) . ',';
    $tag_rb_v .= ggv('tag_rb',$i) . ',';
  }

  $html .= "<div id=\"moretags\" style=\"display :inline;\">";
  $html .= "<script type='text/javascript'>TagSelectionStanza($taglist_count,'$tag_and_v','$tag_lb_v','$tag_list_v','$tag_rb_v');</script>";
  $html .= "</div>";

  $html .= $ef->DataEntryLine( "", "", "button", "extend_tags",
            array("value" => "More Tags",
                  "onclick" => "ExtendTagSelections();",
                  "title" => "Click to add another tag for the search.",
                  "class" => "fsubmit" ) );

  $ef->RevertLineFormat();

  return $html;
}


/////////////////////////////////////////////////////////////
// RenderColumnSelections - Return HTML to show the column
// selections for the search screen.
/////////////////////////////////////////////////////////////
function RenderColumnSelections( $ef ) {
  global $available_columns, $columns, $search_record;

  $flipped_columns = array_flip($columns);
  $html .= '<div id="columnselect">';
  $html .= '<span class="srchp">Columns:</span>';
  $i=0;
  foreach( $available_columns AS $k => $v ) {
    $ef->record->columns[$k] = (isset($flipped_columns[$k])?$k:'');
    error_log( "DBG: $k => $v -- " . $flipped_columns[$k] . ", " . $ef->record->columns[$k] );
    $html .= $ef->DataEntryField( "%s", "checkbox", "columns[$k]",
              array("_label" => $v, "class" => "srchf", "value" => $k ) );
    $i++;
  }
  $html .= "</div>\n";

  return $html;
}


if ( !isset( $style ) || ($style != "plain" && $style != "stripped") ) {
  $form_url_parameters = array();
  if ( isset($org_code) && intval($org_code) > 0 )  array_push( $form_url_parameters, "org_code=$org_code");
  if ( isset($qs) && "$qs" != "" )                  array_push( $form_url_parameters, "qs=$qs");
  $form_url = "$PHP_SELF";
  for( $i=0; $i < count($form_url_parameters) && $i < 20; $i++ ) {
    $form_url .= ( $i == 0 ? '?' : '&' ) . $form_url_parameters[$i] ;
  }
  echo RenderSearchForm($form_url);

} // if  not plain  or stripped style
?>