<?php
  include("always.php");
  require_once("authorisation-page.php");
  $session->LoginRequired();

  require_once("maintenance-page.php");
  require_once("organisation-selectors-sql.php");


  /////////////////////////////////////////////////////////////
  // Render - Return HTML to show the W/R
  //   A separate function is called for each logical area
  //   on the W/R.
  /////////////////////////////////////////////////////////////
  function RenderPieForm( ) {
    global $session, $theme, $search_record;

    $html = "";
    $search_record = (object) array();
    $org_code = intval($GLOBALS['org_code']);
    if ( $org_code > 0 ) $search_record->org_code = $org_code;

    $ef = new EntryForm( $REQUEST_URI, $search_record, true );

    // We do the formatting fairly carefully here...
    $ef->SimpleForm('<span style="white-space: nowrap"><span class="srchp">%s:</span><span class="srchf">%s</span></span> ' );

    $html .= $ef->StartForm( array("autocomplete" => "off", "onsubmit" => "return CheckSearchForm();" ) );

    $html .= "<table border=0 cellspacing=2 cellpadding=0 align=center class=row0 width=100% style=\"border: 1px dashed #aaaaaa;\">\n";
    $html .= "<tr><td width=100%><table border=0 cellspacing=0 cellpadding=0 width=100%><tr valign=middle><td width=100%>\n";

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

    // Type of Request
    $html .= $ef->DataEntryLine( "Type", $this->request_type_desc, "lookup", "type_code",
              array("_type" => "request|request_type",
                    "_null" => "-- All Types --",
                    "class" => "srchf",
                    "style" => "width: 8em",
                    "title" => "Only show this type of request") );

    // Date range
    $html .= $ef->DataEntryLine( "From", "%s", "date", "from_date",
              array( "size" => 10, "class" => "srchf",
                    "title" => "Only show requests with action after this date." ) );
    $html .= "<a href=\"javascript:show_calendar('forms.form.from_date');\" onmouseover=\"window.status='Date Picker';return true;\" onmouseout=\"window.status='';return true;\">".$theme->Image("date-picker.gif")."</a> &nbsp; \n";

    $html .= $ef->DataEntryLine( "To", "%s", "date", "to_date",
              array( "size" => 10, "class" => "srchf",
                    "title" => "Only show requests with action before this date." ) );
    $html .= "<a href=\"javascript:show_calendar('forms.form.to_date');\" onmouseover=\"window.status='Date Picker';return true;\" onmouseout=\"window.status='';return true;\">".$theme->Image("date-picker.gif")."</a> &nbsp; \n";

/*
    if ( ($session->AllowedTo("Admin") ) ) {
  //    $html .= "<div id=\"whereclause\">";
      $html .= $ef->DataEntryLine( "Where", "%s", "text", "where_clause",
                array( "size" => 60, "class" => "srchf",
                      "title" => "Add an SQL 'WHERE' clause to further refine the search - you will need to know what you are doing..." ) );
  //    $html .= "</div>";
    }

    $html .= $ef->DataEntryField( "%s", "checkbox", "inactive",
                array("_label" => "inactive", "class" => "srchf", "value" => 1 ) );
*/
    $html .= $ef->SubmitButton( "submit", "Make Pie",
              array("title" => "Build a Pie Chart with these settings" ) );


    $html .= "</td></tr></table>\n";
    $html .= "</td></tr></table>\n";
    $html .= $ef->EndForm();

    return $html;
  }


  include("page-header.php");
  echo '<script language="JavaScript" src="/js/statuspie.js"></script>' . "\n";

  echo RenderPieForm();

  $pie_parms = array();
  if ( isset($org_code) && intval($org_code) > 0 )  array_push( $pie_parms, "org_code=$org_code");
  if ( isset($system_id) && $system_id != "" )  array_push( $pie_parms, "system_id=$system_id");
  if ( isset($requested_by) && intval($requested_by) > 0 )   array_push( $pie_parms, "requested_by=$requested_by");
  if ( isset($interested_in) && intval($interested_in) > 0 ) array_push( $pie_parms, "interested_in=$interested_in");
  if ( isset($allocated_to) && intval($allocated_to) > 0 )   array_push( $pie_parms, "allocated_to=$allocated_to");
  if ( isset($type_code) && $type_code != '' )  array_push( $pie_parms, "request_type=$type_code");
  if ( isset($to_date) && $to_date != '' )  array_push( $pie_parms, "to_date=$to_date");
  if ( isset($from_date) && $from_date != '' )  array_push( $pie_parms, "from_date=$from_date");

  $pie_url = "/statuspie-svg.php";
  for( $i=0; $i < count($pie_parms) && $i < 20; $i++ ) {
    $pie_url .= ( $i == 0 ? '?' : '&' ) . $pie_parms[$i] ;
  }

  echo <<<EOHTML
<object data="$pie_url" type="image/svg+xml" width="100%%" height="600">
You'll need to get a browser that supports SVG to see this graph.  Mozilla
Firefox 1.5 is such a browser, or there is an Adobe plugin for Microsoft
Internet Explorer that will also do the job.
</object>

EOHTML;

  include("page-footer.php");

?>
