<?php

  // Force some variables to have values.
  if ( !isset($format) ) $format = "";
  if ( !isset($style) ) $style = "";
  if ( !isset($qry) ) $qry = "";
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
  global $session;

  $html = "";

  $ef = new EntryForm( $REQUEST_URI, $this, true );
  $ef->SimpleForm('<span class="smb">%s:</span>&nbsp;<span class="sml">%s</span>' );  // We do the formatting fairly carefully here...

  $html .= $ef->StartForm( array("autocomplete" => "off", "onsubmit" => "return CheckSearchForm();" ) );

  $html .= "<table border=0 cellspacing=2 cellpadding=0 align=center class=row0 width=100% style=\"border: 1px dashed #aaaaaa;\">\n";
//  $html .= "<tr><td width=100%><table border=0 cellspacing=0 cellpadding=0 width=100%><tr valign=middle>\n";

  $html .= $ef->DataEntryLine( "Find", "%s", "text", "search_for",
            array( "size" => 10, "class" => "smb",
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
                    "class" => "sml",
                    "style" => "width: 20em" ) );
  }

  // System (within Organisation) drop-down
  $sql = "SELECT work_system.system_code, system_desc FROM work_system ";
  if ( ! ($session->AllowedTo("Admin") || $session->AllowedTo("Support") ) ) {
    $sql .= "JOIN org_system ON org_system.system_code = work_system.system_code ";
    $sql .= "JOIN system_usr ON $session->user_no = system_usr.user_no AND org_system.system_code = system_usr.system_code ";
  }
  $sql .= "WHERE active ";
  if ( ! ($session->AllowedTo("Admin") || $session->AllowedTo("Support") ) ) {
    $sql .= "AND org_system.org_code = $session->org_code ";
    $sql .= "AND system_usr.role IN ( 'A', 'S', 'C', 'E' ) ";
  }
  $sql .= "ORDER BY lower(system_desc)";
  $html .= $ef->DataEntryLine( "System", "", "lookup", "system_code",
            array("_sql" => $sql, "_null" => "-- All Systems --", "onchange" => "SystemChanged();",
                  "title" => "The business system that this request applies to.",
                  "class" => "sml",
                  "style" => "width: 20em") );

  $html .= "<br />\n";

  // Person within Organisation drop-down
  $sql = "SELECT user_no, fullname ";
  $sql .= " ||' ('||abbreviation||')' ";
  $sql .= "FROM usr JOIN organisation ON organisation.org_code = usr.org_code ";
  $sql .= "WHERE status != 'I' AND organisation.active ";
  if ( ! ($session->AllowedTo("Admin") || $session->AllowedTo("Support") ) )
    $sql .= "AND organisation.org_code = $session->org_code AND usr.org_code = $session->org_code ";
  $sql .= "AND EXISTS(SELECT work_system.system_code FROM org_system JOIN work_system ON (org_system.system_code = work_system.system_code) WHERE org_system.org_code = organisation.org_code AND work_system.active) ";
  $sql .= "ORDER BY lower(fullname)";
  $html .= $ef->DataEntryLine( "By", "", "lookup", "requester_id",
            array("_sql" => $sql, "_null" => "-- Any Requester --", "onchange" => "PersonChanged();",
                  "title" => "The client who is requesting this, or who is in charge of ensuring it happens.",
                  "class" => "smb",
                  "style" => "width: 12em" ) );


  // Person Interested in W/R
  $html .= $ef->DataEntryLine( "Watching", "", "lookup", "subscribable",
            array("_sql" => $sql, "_null" => "-- Any Interested User --",
                  "title" => "The client who is requesting this, or who is in charge of ensuring it happens.",
                  "class" => "smb",
                  "style" => "width: 12em" ) );

  // Person Assigned to W/R
  $html .= $ef->DataEntryLine( "ToDo", "", "lookup", "allocatable",
            array("_sql" => $sql,
                  "_null" => "-- Any Assigned User --",
                  "_all" => "-- Not Yet Allocated --",
                  "class" => "smb",
                  "title" => "A person who has been assigned to work on requests.",
                  "style" => "width: 12em" ) );


  $html .= $ef->SubmitButton( "submit", "Run Query",
            array("title" => "Run a query with these settings" ) );
  $html .= "</table>\n";
//   $html .= "</td></tr></table>\n";
  $html .= $ef->EndForm();

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

  include("system-list.php");
  if ( is_member_of('Admin', 'Support' ) ) {
    $system_list = get_system_list( "", "$system_code", 35);
  }
  else {
    $system_list = get_system_list( "CES", "$system_code", 35);
  }

  echo "<table border=0 cellspacing=2 cellpadding=0 align=center class=row0 width=100% style=\"border: 1px dashed #aaaaaa;\">\n<tr>\n";
  echo "<td width=100%><table border=0 cellspacing=0 cellpadding=0 width=100%><tr valign=middle>\n";
  echo "<td class=smb>Find:</td><td class=sml><input class=sml type=text size=10 name=search_for value=\"$search_for\"></td>\n";

  echo "<td class=smb>&nbsp;System:</td><td class=sml><select class=sml name=system_code><option value=\".\">--- All Systems ---</option>$system_list</select></td>\n";

  if ( is_member_of('Admin', 'Support') ) {
    include( "organisation-list.php" );
    $orglist = "<option value=\"\">--- All Organisations ---</option>\n" . get_organisation_list( "$org_code", 30 );
    echo "<td class=smb>&nbsp;Organisation:</td><td class=sml><select class=sml name=\"org_code\">\n$orglist</select></td>\n";
  }
  echo "<td valign=middle class=smb align=center><input type=submit value=\"RUN QUERY\" alt=go name=submit class=\"submit\"></td><td class=smb width=100px> &nbsp; &nbsp; &nbsp; </td>\n";
  echo "</tr></table></td></tr>\n";


  if ( is_member_of('Admin', 'Support') ) {
    $user_org_code = "";
  }
  else {
    $user_org_code = "$session->org_code";
  }
  echo "<tr><td width=100%><table border=0 cellspacing=0 cellpadding=0 width=100%><tr valign=middle>\n";
  $user_list = "<option value=\"\">--- Any Requester ---</option>"; // . get_user_list( "", $user_org_code, "" );
  echo "<td class=smb>By:</td><td class=sml><select class=sml name=requested_by>$user_list</select></td>\n";
  if ( ! is_member_of('Admin', 'Support')  && ! isset($interested_in) ) $interested_in = $session->user_no;
  $user_list = "<option value=\"\">--- Any Interested User ---</option>"; // . get_user_list( "", $user_org_code, $interested_in );
  echo "<td class=smb>Watching:</td><td class=sml><select class=sml name=interested_in>$user_list</select></td>\n";
  $user_list = "<option value=\"\">--- Any Assigned Staff ---</option><option value=\"-1\">Not Yet Allocated</option>"; // . get_user_list( "Support", "", $allocated_to );
  echo "<td class=smb>ToDo:</td><td class=sml><select class=sml name=allocated_to>$user_list</select></td>\n";
  echo "</tr></table></td></tr>\n";

  $request_types = ""; // get_code_list( "request", "request_type", "$type_code" );
?>
<tr><td><table border=0 cellspacing=0 cellpadding=0 width=100%><tr valign=middle>
<td class=smb align=right>Last&nbsp;Action&nbsp;From:</td>
<td nowrap class=smb><input type=text size=10 name=from_date class=sml value="<?php echo "$from_date"; ?>">
<a href="javascript:show_calendar('search.from_date');" onmouseover="window.status='Date Picker';return true;" onmouseout="window.status='';return true;"><img valign="middle" src="/<?php echo $images; ?>/date-picker.gif" border=0></a>
</td>

<td class=smb align=right>&nbsp;To:</td>
<td nowrap class=smb><input type=text size=10 name=to_date class=sml value="<?php echo "$to_date"; ?>">
<a href="javascript:show_calendar('search.to_date');" onmouseover="window.status='Date Picker';return true;" onmouseout="window.status='';return true;"><img valign="middle" src="/<?php echo $images; ?>/date-picker.gif" border=0></a>
</td>
<td class=smb align=right>&nbsp;Type:</td>
<td nowrap class=smb><select name="type_code" class=sml><option value="">-- All Types --</option><?php echo "$request_types"; ?></select></td>
</tr></table></td>
</tr>
<?php
  echo "<tr><td>\n";
  echo "<table border=0 cellspacing=0 cellpadding=0><tr valign=middle><td class=smb align=right valign=top>When:</td><td class=sml valign=top>\n";
  $query = "SELECT * FROM lookup_code WHERE source_table='request' ";
  $query .= " AND source_field='status_code' ";
  $query .= " ORDER BY source_table, source_field, lookup_seq, lookup_code ";
  $rid = pg_Exec( $dbconn, $query);
  if ( $rid && pg_NumRows($rid) > 1 ) {
    $nrows = pg_NumRows($rid);
    for ( $i=0; $i<$nrows; $i++ ) {
      $status = pg_Fetch_Object( $rid, $i );
      echo "<label for=\"incstat[$status->lookup_code]\" style=\"white-space: nowrap\"><input type=\"checkbox\" id=\"incstat[$status->lookup_code]\" name=\"incstat[$status->lookup_code]\"";
      if ( !isset( $incstat) || isset($incstat[$status->lookup_code]) ) echo " checked";
      echo " value=1>" . str_replace( " ", "&nbsp;", $status->lookup_desc) . "</label> &nbsp; \n";
//        if ( $i == intval(($nrows + 1) / 3) ) echo "&nbsp;<br>";
    }
    echo "<input type=checkbox name=inactive";
    if ( $inactive != "" ) echo " checked";
    echo " value=1>Inactive";
    echo "</td>\n";
  }
  echo "<td valign=middle class=smb align=center><input type=submit value=\"RUN\" alt=\"Run\" title=\"Run a query with these settings\" name=submit class=\"submit\"></td>\n";
  echo "</tr></table>\n</td></tr>\n";

  if ( isset($choose_columns) && $choose_columns ) {
    echo "<tr><td>\n";
    echo "<table border=0 cellspacing=0 cellpadding=0 align=left>\n";
    echo "<tr valign=middle>\n";
    echo "<td valign=middle align=right class=smb>Columns:</td><td class=sml valign=top>";
    // echo "<select name=\"columns[]\" multiple size=\"6\">\n";
    reset($available_columns);
    $cols_set = array_flip($columns);
    $i=0;
    while( list($k,$v) = each( $available_columns ) ) {
      // echo "<option value=\"$k\"";
      // if ( isset($columns[$k]) ) echo " selected";
      // echo ">$v</option>\n";;
      echo "<label for=\"columns[$i]\" style=\"white-space: nowrap\"><input type=\"checkbox\" id=\"columns[$i]\" name=\"columns[$i]\"";
      if ( isset($cols_set[$k]) ) echo " checked";
      echo " value=\"$k\">" . str_replace( " ", "&nbsp;", $v) . "</label> &nbsp; \n";
      $i++;
    }
    // echo "</select>\n";
    echo "</td>\n";
    echo "</tr></table>\n</td></tr>\n";
  }


  if ( is_member_of('Admin') ) {
    echo "<tr><td>\n";
    echo "<table border=0 cellspacing=0 cellpadding=0 align=left>\n";
    echo "<tr valign=middle>\n";
    echo "<td valign=middle align=right class=smb>WHERE:</td><td class=sml valign=top><input type=text size=100 value=\"".htmlentities($where_clause)."\" name=where_clause class=\"sml\"></td>\n";
    echo "</tr></table>\n</td></tr>\n";
  }

  echo "<table border=0 cellspacing=0 cellpadding=0 align=center>\n";
  echo "<tr valign=middle>\n";
  echo "<td valign=middle align=right class=smb>Max results:</td><td class=sml valign=top><input type=text size=6 value=\"$maxresults\" name=maxresults class=\"sml\"></td>\n";
  echo "<td valign=middle align=right class=sml>&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"checkbox\" id=\"save_query_order\" value=\"1\" name=\"save_query_order\" class=\"sml\" /></td><td class=\"smb\" valign=\"middle\"><label for=\"save_query_order\" >Save&nbsp;Order?&nbsp;&nbsp;&nbsp;</label></td>\n";
  echo "<td valign=middle align=right class=smb>&nbsp; &nbsp; Save query as:</td>\n";
  echo "<td valign=middle align=center><input type=text size=20 value=\"$savelist\" name=savelist class=\"sml\"></td>\n";
  echo "<td valign=middle align=left><input type=submit value=\"SAVE QUERY\" alt=save name=submit class=\"submit\"></td>\n";
  echo "</tr></table>\n</td></tr>\n";

  echo "</table>\n</form>\n";
} // if  not plain  or stripped style
