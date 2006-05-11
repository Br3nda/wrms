<?php
include("always.php");
require_once("authorisation-page.php");

$session->LoginRequired();
include("code-list.php");
include( "user-list.php" );
include("page-header.php");
include("system-list.php");
include("organisation-list.php");

$title = "$system_name Work Summary";

// Recommended way of limiting queries to not include sub-tables for 7.1
$result = awm_pgexec( $dbconn, "SET SQL_Inheritance TO OFF;" );

// Initialise variables.
if ( !isset($from_date) ) $from_date = date('01/m/y', mktime(0, 0, 0, date("m"), 0,  date("Y")));
if ( !isset($to_date) )   $to_date   = date('d/m/y',mktime(0, 0, 0, date("m"), 0,  date("Y")));
if ( !isset($break_columns) ) $break_columns = 2;



if ( is_member_of('Admin', 'Accounts' ) ) {
    $system_list = get_system_list( "", $system_id);
    $user_list   = get_user_list( "Support", "", $users );
}
else {
    $system_list = get_system_list( "CES", $system_id);
    $users=array($session->user_no  );
    $user_list="<option value=\"" . $session->user_no . "\">" . substr($session->fullname,0, 25) . " (" . $session->abbreviation . ")" . "</option>";
}

$organisation_list = get_organisation_list($organisation_code);
$request_types     = get_code_list( "request", "request_type", "$request_type" );
$quote_types       = get_code_list( "request_quote", "quote_type", "$quote_type" );
$period_total="week";

$select_columns=array(""=>"","Organisation"=>"o.org_name","System"=>"ws.system_desc","WR#"=>"r.request_id","Work By"=>"rtu.fullname","Request Brief"=>"r.brief","Request Status"=>"r.last_status");
// $select_columns=array(""=>"","Organisation"=>"o.org_name","System"=>"r.system_id","WR#"=>"r.request_id","Work By"=>"rtu.fullname","Request Brief"=>"r.brief","Request Status"=>"r.last_status");

function buildSelect($name, $key_values, $current ) {
        // global $select_columns;
        $select="<select name=\"" .$name . "\" class=\"sml\" id=\"" . $name . "\">\n";
        foreach ($key_values as $key=>$value) {
            $select .=  "<option";
            if ($key==$current) $select .= " selected=\"selected\"";
            $select .= ">$key</option>\n";
        }
        $select.="</select>\n";
        return $select;
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


// Because f*&^%ing php DomNode->set_content doesn't f*&^%ing work.
function replace_content( &$node, $new_content ) {
        $dom  = &$node->owner_document();
        $kids = &$node->child_nodes();
        foreach ( $kids as $kid )
            if ( $kid->node_type() == XML_TEXT_NODE )
                $node->remove_child ($kid);
        $node->set_content($new_content);
}


function insert_period_totals() {
        // Insert period columns in the header row
        global $subtotal_column_start, $doc, $xtable, $j_from_date;
        $theads=$xtable->get_elements_by_tagname("thead");
        $trs=$theads[0]->get_elements_by_tagname("tr");
        $ths=$trs[0]->get_elements_by_tagname("th");

        $first_subtotal_col=$subtotal_column_start+bcmod(7-jddayofweek($j_from_date),7);

        // Loop thru ths adding subtotal cols after each sunday
        for ($j=$first_subtotal_col;$j<count($ths);$j+=7) {

            $th=$doc->create_element("th");
            $th->set_content("sub total");
            $th->set_attribute("class","cols period");

            if   (is_null($ths[$j]->next_sibling())) $trs[0]->append_child($th);
            else $th->insert_before($th, $ths[$j]->next_sibling());
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
            // Loop thru tds adding subtotal cols after each sunday
            for ($j=$subtotal_column_start;$j<count($tds);$j++) {
                if (bcmod($j-$first_subtotal_col,7)==0 ) {
                    $td=$doc->create_element("td");
                    if   (is_null($tds[$j]->next_sibling())) $trs[$i]->append_child($td);
                    else $td->insert_before($td, $tds[$j]->next_sibling());
                    if ($subtotal != 0) $td->set_content(number_format($subtotal,2));
                    $total=$total+$subtotal;
                    $subtotal=0;
                    $td->set_attribute("class","sml period");
                    $td->set_attribute("align","right");
                }
                $subtotal=$subtotal+$tds[$j]->get_content();
            }
            // Insert Total column
            $total=$total+$subtotal;
            $td=$doc->create_element("td");
            $td->set_attribute("class","sml period");
            $td->set_attribute("align","right");
            $trs[$i]->append_child($td);
            if ($total != 0) $td->set_content(number_format($total,2));
        }
}

?>

<div class="row1" style="padding: 3px;">
<form  method="POST" action="<?php echo $PHP_SELF; ?>">
  <h4>Select the entities you want to report on:</h4>
  <table>
    <tr>
      <td><label for="organisation_code" class="sml">Organisation</label></td>
      <td>
        <select class=sml name="organisation_code[]" id="organisation_code" size="6" multiple="true">
            <option value=""<?php if (isset($organisation_code) && array_search("",$organisation_code) !== false) echo " selected=\"selected\""?>>(All)</option>
            <?php echo $organisation_list; ?>
          </select>
      </td>

      <td class=sml><label class="sml" for="system_id">System</label></td>
      <td class=sml>
        <select class=sml name="system_id[]" id="system_id" size="6" multiple="true">
          <option value=""<?php if (isset($system_id) && array_search("",$system_id) !== false) echo " selected=\"selected\""?>>(All)</option>
          <?php echo $system_list; ?>
        </select>
      </td>

      <td class=sml><label for="users">Work By</label></td>
      <td class=sml>
        <select class=sml name="users[]" id="users" size="6" multiple="true">
          <option value=""<?php if (isset($users) && array_search("",$users) !== false) echo " selected=\"selected\""?>>(All)</option>
          <?php echo $user_list; ?>
        </select>
      </td>
    </tr>
  </table>
  <p>
    <label class="sml" for="break_columns">Subtotal Levels</label>
        <?php
            // Number of break columns to do subtotals by.-!>
            echo buildSelect("break_columns", array(0=>0,1=>1,2=>2,3=>3),$break_columns) ;
        ?>
      <label class="sml" for="from_date">From Date</label>
      <input type=text name="from_date" id="from_date" class="sml" size=10<?php if ("$from_date" != "") echo " value=$from_date";?>>
      <label for="to_date" class=sml>To Date</label>
      <input type=text name=to_date id="to_date" class="sml" size=10<?php if ("$to_date" != "") echo " value=$to_date";?>>
  </p>

  <h4>Select the columns you want on the report:</h4>
  <p><?php
            echo buildSelect("columns[]", $select_columns, isset($columns[0]) ? $columns[0] : "Organisation") ;
            echo buildSelect("columns[]", $select_columns, isset($columns[1]) ? $columns[1] : "System") ;
            echo buildSelect("columns[]", $select_columns, $columns[2]) ;
            echo buildSelect("columns[]", $select_columns, $columns[3]) ;
            echo buildSelect("columns[]", $select_columns, $columns[4]) ;
            echo buildSelect("columns[]", $select_columns, $columns[5]) ;
        ?>
        <input type=submit value="RUN QUERY" alt=go name=submit class="submit">
</form>
</div>

<?php

// if  ( !($session->AllowedTo("Admin") || $session->AllowedTo("Support"))) $users[]=$session->user_no;

if ( isset($organisation_code )) $organisation_code=array_filter($organisation_code);
if ( isset($system_id)) $system_id=array_filter($system_id);
if (isset($users)) $users=array_filter($users);

 if ($_SERVER['REQUEST_METHOD'] == "POST") {

// if ( isset($organisation_code) || isset($system_id) || isset($users)) {
    // Select all data

    $columns=array_filter($columns);

    if (! array_search("WR#",$columns)) $columns[]="WR#" ;  // Always needs to be selected so subselects will work

    $query  = "SELECT DISTINCT";
    foreach ($columns as $column) $query .= "  $select_columns[$column]  AS \"$column\" ,\n";

    $query .= " ARRAY(SELECT date(rt2.work_on) FROM request_timesheet rt2 WHERE rt2.request_id = r.request_id AND rt2.work_units = 'hours'";
    if (array_search("Work By",$columns)!==false) $query .= " AND rt2.work_by_id = rt.work_by_id" ;

    $query .= " AND date(rt2.work_on) >= '$from_date' AND date(rt2.work_on) <= '$to_date'
                    GROUP BY date(rt2.work_on) ORDER BY date(rt2.work_on)) AS date,\n";

    $query .= " ARRAY(SELECT SUM(rt2.work_quantity) FROM request_timesheet rt2 WHERE rt2.request_id = r.request_id AND rt2.work_units = 'hours'" ;
    if (array_search("Work By",$columns)!==false) $query .= " AND rt2.work_by_id = rt.work_by_id" ;

    $query .= " AND date(rt2.work_on) >= '$from_date' AND date(rt2.work_on) <= '$to_date'
                    GROUP BY date(rt2.work_on) ORDER BY date(rt2.work_on)) AS quantity\n";

    $query .= " FROM request r\n";
    $query .= " LEFT JOIN usr rqu ON rqu.user_no = r.requester_id\n";
    $query .= " LEFT JOIN work_system ws USING (system_id)\n";
    $query .= " LEFT JOIN organisation o USING (org_code)\n";
    $query .= " LEFT OUTER JOIN request_timesheet rt  USING (request_id)\n";
    $query .= " LEFT OUTER JOIN usr rtu ON rtu.user_no = rt.work_by_id\n";

    // Build WHERE clause
    if ( count($organisation_code) > 0 ) $where .= " AND o.org_code IN (" . implode(",",$organisation_code) . ") ";
    if ( count($system_id) > 0 ) $where .= " AND r.system_id IN ('" . implode("','",$system_id) . "') ";
    if ( count($users) > 0 ) $where .= " AND rt.work_by_id IN (" . implode(",",$users) . ") ";

    if ( "$from_date" != "" ) $where .= " AND rt.work_on >= '$from_date' ";
    if ( "$to_date"   != "" ) $where .= " AND rt.work_on <= '$to_date' ";

    if (isset($where)) $query .= " WHERE " . substr($where,4);

    // Build ORDER BY clause
    foreach($columns as $column) $by[]=$select_columns[$column];
    $query .= " ORDER BY " . implode(",",$by) . "\n";

    // echo "<pre>$query";

    // Execute query
    $result = awm_pgexec( $dbconn, $query, "plan", false, 7 );

    // Create xml doc to put query data into.

    $doc = domxml_new_doc("1.0");
    $xtable = $doc->add_root("table");
    $xtable->set_attribute("style", "empty-cells: show; border-collapse: collapse; border: 1px solid $colors[row1] ;");
    $xtable->set_attribute("border", "1");

    // Create column headers for selected fields.
    $xthead = $xtable->new_child("thead", "");
    $xthr = $xthead->new_child("tr", "");
    $clone_tr = $doc->create_element("tr");

    foreach ($columns as $column) {
        $xth = $xthr->new_child("th", $column);
        $xth->set_attribute("class", "cols");
        $clone_td = $doc->create_element("td");
        $clone_td = $clone_tr->append_child($clone_td);
        $clone_td->set_attribute("class", "sml");
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
            $xth = $xthr->new_child("th", date("D d m y", $temp_timestamp));
            $xth->set_attribute("class", "cols");

            $clone_td = $doc->create_element("td");
            $clone_td = $clone_tr->append_child($clone_td);
            if (date("D", $temp_timestamp) == "Sat" || date("D", $temp_timestamp) == "Sun") $clone_td->set_attribute("class","sml row1");
            else $clone_td->set_attribute("class","sml");
            $clone_td->set_attribute("align","right");
    }

    // Create table footer with empty row
    $xtfoot = $xtable->new_child("tfoot", "");
    $xtfr = $xtfoot->new_child("tr", "");


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
                            if ($key != 0) insert_subtotal($xtrows[$key-1], $subtotal[$i], $i);
                            if ($i<$break_columns) $prev_break[$i+1]="";
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

    $xtd = $xtfr->new_child("td","Rows selected: " . pg_NumRows($result));
    $xtd->set_attribute("colspan", count($xthr->child_nodes()));

    // Output the html table
    echo $doc->html_dump_mem(true);

}

    include("page-footer.php");
?>
