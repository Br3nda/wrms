<?php
  include("always.php");
  require_once("authorisation-page.php");


  $session->LoginRequired();
  include("code-list.php");
  include( "user-list.php" );

  $title = "$system_name Plan";

  // Recommended way of limiting queries to not include sub-tables for 7.1
  $result = awm_pgexec( $dbconn, "SET SQL_Inheritance TO OFF;" );

  include("headers.php");

  // Initialise variables.
  include("system-list.php");
  include("organisation-list.php");

  if ( !isset($from_date) ) $from_date = "";
  if ( !isset($to_date) )   $to_date   = "";

  if ( !isset($break_columns) ) $break_columns = 0;

  if ( is_member_of('Admin', 'Support' ) ) {
    $system_list = get_system_list( "", "$system_code");
    }
  else {
    $system_list = get_system_list( "CES", "$system_code");
    }

  $organisation_list = get_organisation_list($organisation_code);
  $request_types     = get_code_list( "request", "request_type", "$request_type" );
  $quote_types       = get_code_list( "request_quote", "quote_type", "$quote_type" );
  $period_total="week";

    $select_columns=array(""=>"","Organisation"=>"o.org_name","System"=>"r.system_code","WR#"=>"r.request_id","Work By"=>"rtu.username","Request Brief"=>"r.brief");

//  $select_columns=array("o.org_name"=>"Organisation","r.system_code"=>"System","r.request_id"=>"WR#","rtu.username"=>"Work By"=>"rtu.username");

    function buildSelect($name, $key_values, $current ) {
        // global $select_columns;
        $select="<select name=\"" .$name . "\" class=\"sml\">\n";
        foreach ($key_values as $key=>$value) {
            $select .=  "<option";
            if ($key==$current) $select .= " selected=\"selected\"";
            $select .= ">$key</option>\n";
        }
        $select.="</select>\n";
        return $select;
    }
?>


<form  method="POST" action="<?php echo $PHP_SELF; ?>" class=row1>
    <table>
    <tr>
      <td class=sml>
        <?php
            echo buildSelect("columns[]", $select_columns, $columns[0]) ;
            echo buildSelect("columns[]", $select_columns, $columns[1]) ;
            echo buildSelect("columns[]", $select_columns, $columns[2]) ;
            echo buildSelect("columns[]", $select_columns, $columns[3]) ;

        ?>
      </td>
    </tr>
  </table>

  <table>
    <tr>
      <td class="sml"><label for="organisation_code">Organisation</label></td>
      <td  valign="top" class=sml">
        <select class=sml name="organisation_code[]" id="organisation_code" size="6" multiple="true">
            <option value="">(All)</option>
            <?php echo $organisation_list; ?>
          </select>
      </td>

      <td class=sml><label for="system_code">System</label></td>
      <td class=sml>
        <select class=sml name=system_code id="system_code" size="6" multiple="true">
          <option value="">(All)</option>
          <?php echo $system_list; ?>
        </select>
      </td>


    </tr>
  </table>
  
  <table>
    <tr>
      <td class=smb>Subtotal Levels</td>
      <td class=sml>
        <?php
            // Number of break columns to do subtotals by.-!>
            echo buildSelect("break_columns", array(0=>0,1=>1,2=>2,3=>3),$break_columns) ;
        ?>
      </td>
      <td class=smb>From Date</td>
      <td class=sml><input type=text name=from_date size=10<?php if ("$from_date" != "") echo " value=$from_date";?>></td>
      <td class=smb>To Date</td>
      <td class=sml><input type=text name=to_date size=10<?php if ("$to_date" != "") echo " value=$to_date";?>></td>
      <td valign=middle class=smb align=center>
        <input type=submit value="RUN QUERY" alt=go name=submit class="submit">
      </td>
    </tr>
  </table>
</form>

<?php

$organisation_code=array_filter($organisation_code);

if ( isset($organisation_code) || isset($system_code) ) {
    $columns=array_filter($columns);

    if (! array_search("WR#",$columns)) $columns[]="WR#" ;  // Always needs to be selected so subselects will work

    $query  = "SELECT DISTINCT";
    foreach ($columns as $column) $query .= "  $select_columns[$column]  AS \"$column\" ,\n";

    $query .= " ARRAY(SELECT date(rt2.work_on) FROM request_timesheet rt2 WHERE rt2.request_id = r.request_id";

    if (!array_search("Work By",$columns)!==false) $query .= " AND rt2.work_by_id = rt.work_by_id" ;

    $query .= " AND date(rt2.work_on) >= '$from_date' AND date(rt2.work_on) <= '$to_date'
                    GROUP BY date(rt2.work_on) ORDER BY date(rt2.work_on)) AS date,\n";

    $query .= " ARRAY(SELECT SUM(rt2.work_quantity) FROM request_timesheet rt2 WHERE rt2.request_id = r.request_id" ;
    if (!array_search("Work By",$columns)!==false) $query .= " AND rt2.work_by_id = rt.work_by_id" ;
    
    $query .= " AND date(rt2.work_on) >= '$from_date' AND date(rt2.work_on) <= '$to_date'
                    GROUP BY date(rt2.work_on) ORDER BY date(rt2.work_on)) AS quantity\n";

    $query .= " FROM request r\n";
    $query .= " LEFT JOIN usr rqu ON rqu.user_no = r.requester_id\n";
    $query .= " LEFT JOIN organisation o USING (org_code)\n";
    $query .= " LEFT OUTER JOIN request_timesheet rt  USING (request_id)\n";
    $query .= " LEFT OUTER JOIN usr rtu ON rtu.user_no = rt.work_by_id\n";

    // Build WHERE clause

    if ( count($organisation_code) > 0 ) $where .= " AND o.org_code IN (" . implode(",",$organisation_code) . ") ";
    if ( "$from_date" != "" ) $where .= " AND rt.work_on >= '$from_date' ";
    if ( "$to_date"   != "" ) $where .= " AND rt.work_on <= '$to_date' ";

    if (isset($where)) $query .= " WHERE " . substr($where,4);

    // Build GROUP BY clause

    foreach($columns as $column) $by[]=$select_columns[$column];

    // $query .= " GROUP BY " . implode(",",$by) . "\n";

    $query .= " ORDER BY " . implode(",",$by) . "\n";

//    echo $query;

    // Execute query
    $result = awm_pgexec( $dbconn, $query, "plan", false, 7 );

    // Create xml doc to put query data into.

    $doc = domxml_new_doc("1.0");
    $xtable = $doc->add_root("table");
    $xtable->set_attribute("style", "empty-cells: show; border-collapse: collapse; border: 1px solid $colors[row1] ;");
    $xtable->set_attribute("border", "1");

    // Create column headers for selected fields.
    $xthead = $xtable->new_child("thead", "");
    $xtr = $xthead->new_child("tr", "");
    $clone_tr = $doc->create_element("tr");

    foreach ($columns as $column) {
        $xth = $xtr->new_child("th", $column);
        $xth->set_attribute("class", "cols");
        $clone_td = $doc->create_element("td");
        $clone_td = $clone_tr->append_child($clone_td);
        $clone_td->set_attribute("class", "sml");
        $clone_td->set_attribute("nowrap","nowrap");
    }

    list($day, $month, $year) = explode('/', nice_date($from_date));
    $from_timestamp = mktime(0, 0, 0, $month, $day, $year);
    list($day, $month, $year) = explode('/', nice_date($to_date));
    $to_timestamp = mktime(0, 0, 0, $month, $day, $year);
    $j_from_date  = GregorianToJD( date("n",$from_timestamp),date("j",$from_timestamp),date("Y",$from_timestamp));
    $j_to_date    = GregorianToJD( date("n",$to_timestamp),date("j",$to_timestamp),date("Y",$to_timestamp));

    $temp_timestamp = $from_timestamp;
    $temp_date = getdate($from_timestamp);

    for ($day = 0; $day <= $j_to_date - $j_from_date; $day++ ) {
            $temp_timestamp = mktime($temp_date["hour"],$temp_date["minutes"],$temp_date["seconds"],$temp_date["mon"],$temp_date["mday"] + $day,$temp_date["year"]);
            $day_array[date("Y-m-d", $temp_timestamp)] = $day;
            $xth = $xtr->new_child("th", date("D d m y", $temp_timestamp));
            $xth->set_attribute("class", "cols");

            $clone_td = $doc->create_element("td");
            $clone_td = $clone_tr->append_child($clone_td);
            if (date("D", $temp_timestamp) == "Sat" || date("D", $temp_timestamp) == "Sun") $clone_td->set_attribute("class","sml row1");
            else $clone_td->set_attribute("class","sml");
            $clone_td->set_attribute("align","right");
    }

    // How many columns to skip over when starting any subtotal calculations.
    $subtotal_column_start = count($columns);

    $xtbody = $doc->create_element("tbody");
    $xtable->append_child($xtbody);

    // Put result rows into xml doc.
    for ( $i=0; $i < pg_NumRows($result); $i++ ) {
        $row = pg_fetch_array( $result, $i );

        $xtr = $doc->create_element("tr");
        $xtr = $clone_tr->clone_node(true);  // Copy this empty row so we don't have to reset all the styles etc.
        $xtr = $xtbody->append_child($xtr);

        $xtds=$xtr->get_elements_by_tagname("td");

        // Set content of requested columns
        for ($j=0;$j<count($columns);$j++) $xtds[$j]->set_content(htmlspecialchars($row[$columns[$j]]));

        //  Set quantity content for date range.
        $date       = array_filter(explode(",", ereg_replace("[{\"-\"}]", "", $row["date"])));
        $quantity   = explode(",", ereg_replace("[{\"-\"}]", "", $row["quantity"]));

        for ($j=0;$j<count($date);$j++) $xtds[$day_array[$date[$j]]+$subtotal_column_start]->set_content(number_format($quantity[$j],2));
    }

    // Because f*&^%ing php DomNode->set_content doesn't f*&^%ing work.
    function replace_content( &$node, $new_content ) 
    {
        $dom  = &$node->owner_document();
        $kids = &$node->child_nodes();
        foreach ( $kids as $kid )
            if ( $kid->node_type() == XML_TEXT_NODE )
                $node->remove_child ($kid);
        $node->set_content($new_content);
    } 

    function insert_subtotal( &$row_node, &$subtotal, $break_level ) {
        // $row_node is the row to insert the subtotal after.
        global $doc, $clone_tr, $subtotal_column_start, $xtbody ;

        // For when there are no rows.
        if   (is_null($row_node)) return;

        // Create a subtotal row to put subtotal values into.
        $subtotal_row = $doc->create_element("tr");
        $subtotal_row = $row_node->clone_node(true);
        $subtotal_row->set_attribute("class", "subtotal");
        $subtotal_cols=$subtotal_row->get_elements_by_tagname("td");

        // Put the subtotal values into cells.
        for ($i=$break_level;$i<$subtotal_column_start; $i++) replace_content($subtotal_cols[$i],"");
        $subtotal_cols[max(0,$break_level-1)]->set_content(" Total");

        for ($i=$subtotal_column_start; $i<count($subtotal_cols); $i++){
            if ($subtotal[$i] != 0) {
                replace_content($subtotal_cols[$i],number_format($subtotal[$i],2));
                unset($subtotal[$i]);
            }
            else replace_content($subtotal_cols[$i],"");
        }

        // Insert the row into the xml doc
        if(is_null($row_node->next_sibling())) $xtbody->append_child($subtotal_row);
        else $row_node->insert_before($subtotal_row, $row_node->next_sibling());
    }

    function insert_period_totals() {
        // Insert period columns in the header row
        global $subtotal_column_start, $doc, $xtable, $j_from_date;
        $theads=$xtable->get_elements_by_tagname("thead");
        $trs=$theads[0]->get_elements_by_tagname("tr");
        $ths=$trs[0]->get_elements_by_tagname("th");

        $first_subtotal_col=$subtotal_column_start+1+bcmod(7-jddayofweek($j_from_date),7);

        // Loop thru ths adding subtotal cols after each sunday
        for ($j=$first_subtotal_col;$j<=count($ths);$j+=7) {

            $th=$doc->create_element("th");
            $th->set_content("sub total");
            $th->set_attribute("class","cols period");

            if   (is_null($ths[$j])) $trs[0]->append_child($th);
            else $th->insert_before($th, $ths[$j]);
        }
        $th=$doc->create_element("th");
        $th->set_content("total");
        $th->set_attribute("class","cols period");
        $trs[0]->append_child($th);

        $tbodys=$xtable->get_elements_by_tagname("tbody");
        $trs=$tbodys[0]->get_elements_by_tagname("tr");

        for ($i=0;$i<count($trs);$i++) {
            $tds=$trs[$i]->get_elements_by_tagname("td");
            $subtotal=0;
            $total=0;
            // Loop thru ths adding subtotal cols after each sunday
            for ($j=$subtotal_column_start;$j<count($tds);$j++) {
                if (bcmod($j-$first_subtotal_col,7)==0) {
                    $td=$doc->create_element("td");
                    if   (is_null($tds[$j])) $trs[0]->append_child($td);
                    else $td->insert_before($td, $tds[$j]);
                    if ($subtotal != 0) $td->set_content(number_format($subtotal,2));
                    $total=$total+$subtotal;
                    $subtotal=0;
                    $td->set_attribute("class","sml period");
                    $td->set_attribute("align","right");
                }
                $subtotal=$subtotal+$tds[$j]->get_content();
            }
            $total=$total+$subtotal;
            $td=$doc->create_element("td");
            $td->set_attribute("class","sml period");
            $td->set_attribute("align","right");
            $trs[$i]->append_child($td);
            if ($total != 0) $td->set_content(number_format($total,2));
        }
    }

    // Insert period totals into xml doc
    if ($period_total!="") insert_period_totals($period_total) ;

    // Insert subtotal rows into document
    if ($break_columns != 0 ) {
        $xtbodys = $xtable->get_elements_by_tagname("tbody"); // Skip over thead, tfooter

        foreach ($xtbodys as $xtbody) {
            foreach ($xtrows=$xtbody->get_elements_by_tagname("tr") as $key=>$xtrow) {
                $xtds=$xtrow->get_elements_by_tagname("td");


                // Check for break. Insert subtotal rows if needed.
                for ($i = 1; $i<=($break_columns); $i++){
                    if ($xtds[$i-1]->get_content() != $prev_break[$i]){
                        if ($key!=count($xtrows)-1 ) { // Don't need if last row.
                            if ($key != 0) insert_subtotal($xtrows[$key-1], $subtotal[$i], $i);
                            if ($i<$break_columns-2) $prev_break[$i+1]="";
                        }
                        $prev_break[$i]=$xtds[$i-1]->get_content();
                    }
                }

                // Accumulate subtotals
                for ($i=$subtotal_column_start; $i < count($xtds); $i++){
                    for ($j=0; $j<=$break_columns;$j++) $subtotal[$j][$i]=$subtotal[$j][$i]+$xtds[$i]->get_content();
                }
            }

            // Last totals.
            for ($i=0; $i<=$break_columns; $i++){
                insert_subtotal($xtrows[count($xtrows)-1], $subtotal[$i], $i);
            }
            $xtbody = $xtbody->next_sibling();
        }
    }

    // Output the html table
    echo $doc->html_dump_mem(true);

}

    include("footers.php");
?>


