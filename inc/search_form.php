<?php

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

  // If they didn't provide a $columns, we use a default.
  if ( !isset($columns) || $columns == "" || $columns == array() ) {
    $columns = array("request_id","lfull","request_on","lbrief","status_desc","request_type_desc","request.last_activity");
    if ( "$format" == "edit" )  //adds in the Active field header for the Brief (editable) report
      array_push( $columns, "active");
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


/////////////////////////////////////////////////////////////
// Render - Return HTML to show the W/R
//   A separate function is called for each logical area
//   on the W/R.
/////////////////////////////////////////////////////////////
function RenderSearchForm( $target_url ) {
  global $session, $images;

  $html = "";

  $ef = new EntryForm( $REQUEST_URI, $this, true );

  // We do the formatting fairly carefully here...
  $ef->SimpleForm('<span style="white-space: nowrap"><span class="srchp">%s:</span><span class="srchf">%s</span></span> ' );

  $html .= $ef->StartForm( array("autocomplete" => "off", "onsubmit" => "return CheckSearchForm();" ) );

  $html .= "<table border=0 cellspacing=2 cellpadding=0 align=center class=row0 width=100% style=\"border: 1px dashed #aaaaaa;\">\n";
  $html .= "<tr><td width=100%><table border=0 cellspacing=0 cellpadding=0 width=100%><tr valign=middle>\n";

  $html .= $ef->DataEntryLine( "Find", "%s", "text", "search_for",
            array( "size" => 10, "class" => "srchf",
                   "title" => "Search for free text in the request or notes.  Regular expressions are OK too." ) );

  // Organisation drop-down
  if ( $session->AllowedTo("Admin") || $session->AllowedTo("Support") ) {
    $sql = "SELECT org_code, org_name || ' (' || abbreviation || ')' AS org_name ";
    $sql .= "FROM organisation WHERE active AND abbreviation !~ '^ *$' ";
    $sql .= "AND EXISTS(SELECT user_no FROM usr WHERE usr.org_code = organisation.org_code AND usr.status != 'I') ";
    $sql .= "AND EXISTS(SELECT work_system.system_code FROM org_system JOIN work_system ON (org_system.system_code = work_system.system_code) WHERE org_system.org_code = organisation.org_code AND work_system.active) ";
    $sql .= "ORDER BY lower(org_name)";
    $html .= $ef->DataEntryLine( "Organisation", "", "lookup", "org_code",
              array("_sql" => $sql, "_null" => "-- All Organisations --", "onchange" => "OrganisationChanged();",
                    "title" => "The organisation that this work will be done for.",
                    "class" => "srchf",
                    "style" => "width: 18em" ) );
  }

  // System (within Organisation) drop-down
  $sql = "SELECT work_system.system_code, system_desc FROM work_system ";
  $sql .= "WHERE active ";
  $sql .= "AND EXISTS (SELECT 1 FROM org_system WHERE org_system.system_code = work_system.system_code ";
  if ( $org_code != 0 && ($session->AllowedTo("Admin") || $session->AllowedTo("Support") ) )
    $sql .= "AND org_system.org_code = $org_code ";
  else if ( ! ($session->AllowedTo("Admin") || $session->AllowedTo("Support") ) )
    $sql .= "AND org_system.org_code = $session->org_code ";
  $sql .= ") ";
  $sql .= "ORDER BY lower(system_desc);";
  $html .= $ef->DataEntryLine( "System", "", "lookup", "system_code",
            array("_sql" => $sql, "_null" => "-- All Systems --", "onchange" => "SystemChanged();",
                  "title" => "The business system that this request applies to.",
                  "class" => "srchf",
                  "style" => "width: 18em") );

  // Person within Organisation drop-down
  $sql = "SELECT user_no, fullname ";
  $sql .= " ||' ('||abbreviation||')' ";
  $sql .= "FROM usr JOIN organisation ON organisation.org_code = usr.org_code ";
  $sql .= "WHERE status != 'I' AND organisation.active ";
  if ( ! ($session->AllowedTo("Admin") || $session->AllowedTo("Support") ) )
    $sql .= "AND organisation.org_code = usr.org_code AND usr.org_code IN ( $session->org_code ) ";
  $sql .= "AND EXISTS(SELECT work_system.system_code FROM org_system JOIN work_system ON (org_system.system_code = work_system.system_code) WHERE org_system.org_code = organisation.org_code AND work_system.active) ";
  $sql .= "ORDER BY lower(fullname)";
  $html .= $ef->DataEntryLine( "By", "", "lookup", "requester_id",
            array("_sql" => $sql, "_null" => "-- Any Requester --", "onchange" => "PersonChanged();",
                  "title" => "The client who is requesting this, or who is in charge of ensuring it happens.",
                  "class" => "srchf",
                  "style" => "width: 12em" ) );


  // Person Interested in W/R
  $html .= $ef->DataEntryLine( "Watching", "", "lookup", "subscribable",
            array("_sql" => $sql, "_null" => "-- Any Interested User --",
                  "title" => "The client who is requesting this, or who is in charge of ensuring it happens.",
                  "class" => "srchf",
                  "style" => "width: 12em" ) );

  // Person Assigned to W/R
  $html .= $ef->DataEntryLine( "ToDo", "", "lookup", "allocatable",
            array("_sql" => $sql,
                  "_null" => "-- Any Assigned User --",
                  "_all" => "-- Not Yet Allocated --",
                  "class" => "srchf",
                  "title" => "A person who has been assigned to work on requests.",
                  "style" => "width: 12em" ) );

  // Date range
  $html .= $ef->DataEntryLine( "Last Action", "%s", "date", "from_date",
            array( "size" => 10, "class" => "srchf",
                   "title" => "Only show requests with action after this date." ) );
  $html .= "<a href=\"javascript:show_calendar('forms.form.from_date');\" onmouseover=\"window.status='Date Picker';return true;\" onmouseout=\"window.status='';return true;\"><img valign=\"middle\" src=\"/$images/date-picker.gif\" border=\"0\"></a> &nbsp; ";

  $html .= $ef->DataEntryLine( "To", "%s", "date", "to_date",
            array( "size" => 10, "class" => "srchf",
                   "title" => "Only show requests with action before this date." ) );
  $html .= "<a href=\"javascript:show_calendar('forms.form.to_date');\" onmouseover=\"window.status='Date Picker';return true;\" onmouseout=\"window.status='';return true;\"><img valign=\"middle\" src=\"/$images/date-picker.gif\" border=\"0\"></a> &nbsp; ";

  // Type of Request
  $html .= $ef->DataEntryLine( "Type", $this->request_type_desc, "lookup", "type_code",
            array("_type" => "request|request_type",
                  "_null" => "-- All Types --",
                  "class" => "srchf",
                  "style" => "width: 8em",
                  "title" => "Only show this type of request") );

  $html .= "<table border='0' cellspacing='0' cellpadding='0' width='100%'><tr>";
  $html .= "<td style=\"vertical-align: top; padding-top: 0.3em;\"><span class=\"srchp\">Status:</span></td><td valign='top'>\n";
  $sql = "SELECT * FROM lookup_code WHERE source_table='request' ";
  $sql .= " AND source_field='status_code' ";
  $sql .= " ORDER BY source_table, source_field, lookup_seq, lookup_code ";
  $qry = new PgQuery( $sql );
  if ( $qry->Exec("RenderSearchForm") && $qry->rows > 0 ) {
    while ( $status = $qry->Fetch() ) {
      $html .= $ef->DataEntryField( "%s", "checkbox", "incstat[$status->lookup_code]",
              array("_label" => $status->lookup_desc, "class" => "srchf", "value" => 1 ) );
    }
    $html .= $ef->DataEntryField( "%s", "checkbox", "inactive",
              array("_label" => "inactive", "class" => "srchf", "value" => 1 ) );
    $html .= "</td>\n";
  }
  $html .= "</tr></table>\n";

  $html .= RenderTagsPanel($ef);

  if ( ($session->AllowedTo("Admin") /* || $session->AllowedTo("Support") */ ) ) {
    $html .= "<div id=\"whereclause\">";
    $html .= $ef->DataEntryLine( "Where", "%s", "text", "where_clause",
              array( "size" => 60, "class" => "srchf",
                    "title" => "Add an SQL 'WHERE' clause to further refine the search - you will need to know what you are doing..." ) );
    $html .= "</div>";
  }

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


  $html .= "</table>\n";
  $html .= "</td></tr></table>\n";
  $html .= $ef->EndForm();

  return $html;
}



/////////////////////////////////////////////////////////////
// RenderTagsPanel - Return HTML to show the Tags panel of
//   the search screen.
/////////////////////////////////////////////////////////////
function RenderTagsPanel( $ef ) {
  global $session, $images, $taglist_count;

  $html = "";
  $org_code = intval($GLOBALS['org_code']);

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
            array("_sql" => $sql, "_null" => "-- Any Tag --", /* "onchange" => "TagChanged();", */
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
  $taglist_count = intval($taglist_count);
  for ( $i=0; $i < $taglist_count; $i++ ) {
    $tag_and_v .= $GLOBALS['tag_and'][$i] . ',';
    $tag_lb_v .= $GLOBALS['tag_lb'][$i] . ',';
    $tag_list_v .= $GLOBALS['tag_list'][$i] . ',';
    $tag_rb_v .= $GLOBALS['tag_rb'][$i] . ',';
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
  global $available_columns, $columns;

  $html .= '<div id="columnselect">';
  $html .= '<span class="srchp">Columns:</span>';
  reset($available_columns);
  $save_cols = $columns;
  $columns = array_flip($columns);
  $i=0;
  while( list($k,$v) = each( $available_columns ) ) {
    $html .= $ef->DataEntryField( "%s", "checkbox", "columns[$i]",
              array("_label" => $v, "class" => "srchf", "value" => $k ) );
    $i++;
  }
  $columns = $save_cols;
  $html .= "</div>\n";

  return $html;
}


if ( !isset( $style ) || ($style != "plain" && $style != "stripped") ) {
  $form_url_parameters = array();
  if ( isset($org_code) && intval($org_code) > 0 )  array_push( $form_url_parameters, "org_code=$org_code");
  if ( isset($qs) && "$qs" != "" )                  array_push( $form_url_parameters, "qs=$qs");
  if ( isset($choose_columns) && $choose_columns )  array_push( $form_url_parameters, "choose_columns=1");
  $form_url = "$PHP_SELF";
  for( $i=0; $i < count($form_url_parameters) && $i < 20; $i++ ) {
    $form_url .= ( $i == 0 ? '?' : '&' ) . $form_url_parameters[$i] ;
  }
  echo RenderSearchForm($form_url);

/*


  echo "<table border=0 cellspacing=0 cellpadding=0 align=center>\n";
  echo "<tr valign=middle>\n";
  echo "<td valign=middle align=right class=srchf>Max results:</td><td class=srchf valign=top><input type=text size=6 value=\"$maxresults\" name=maxresults class=\"srchf\"></td>\n";
  echo "<td valign=middle align=right class=srchf>&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"checkbox\" id=\"save_query_order\" value=\"1\" name=\"save_query_order\" class=\"srchf\" /></td><td class=\"srchf\" valign=\"middle\"><label for=\"save_query_order\" >Save&nbsp;Order?&nbsp;&nbsp;&nbsp;</label></td>\n";
  echo "<td valign=middle align=right class=srchf>&nbsp; &nbsp; Save query as:</td>\n";
  echo "<td valign=middle align=center><input type=text size=20 value=\"$savelist\" name=savelist class=\"srchf\"></td>\n";
  echo "<td valign=middle align=left><input type=submit value=\"SAVE QUERY\" alt=save name=submit class=\"submit\"></td>\n";
  echo "</tr></table>\n</td></tr>\n";

  echo "</table>\n</form>\n";
*/

} // if  not plain  or stripped style
?>