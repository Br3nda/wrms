<?php
  include("$base_dir/inc/html-format.php");
function nice_time( $in_time ) {
  /* does nothing yet... */
  return substr("$in_time", 2);
}
  if ( "$because" != "" )
    echo $because;
  else if ( ! $plain ) {
//    ? ><P class=helptext>Use this form to maintain organisations who may have requests associated
// with them.</P><?php
  }
// <P class=helptext>This page lists timesheets.</P>
?>

<FORM METHOD=POST ACTION="<?php 
echo "$base_url/form.php?form=work";
if ( isset($org_code) && $org_code != "" ) echo "&org_code=$org_code";
if ( isset($system_code) && $system_code != "" ) echo "&system_code=$system_code";
if ( isset($user_no) && $user_no != "" ) echo "&user_no=$user_no";
?>">
<table align=center><tr valign=middle>
<td><b>Desc.</b><input TYPE="Text" Size="20" Name="search_for" Value="<?php echo "$search_for"; ?>"></td>
<td><label for=uncharged><input type=checkbox value=1 name=uncharged<?php if ("$uncharged"<>"" ) echo " checked"; ?>> Uncharged</label></td>
<td><label for=charge><input type=checkbox value=1 name=charge<?php if ("$charge"<>"" ) echo " checked"; ?>> Charge</label></td>
<td><input TYPE="Image" src="images/in-go.gif" alt="go" WIDTH="44" BORDER="0" HEIGHT="26" name="submit"></td>
</tr></table>
</form>  


<?php
  if ( "$search_for$system_code " != "" ) {
    $query = "SELECT request.*, organisation.*, request_timesheet.*, ";
    $query .= " worker.fullname AS worker_name, requester.fullname AS requester_name";
    $query .= " FROM request, usr AS worker, usr AS requester, organisation, request_timesheet ";
    $query .= " WHERE request_timesheet.request_id = request.request_id";
    $query .= " AND worker.user_no = work_by_id ";
    $query .= " AND requester.user_no = requester_id ";
    $query .= " AND organisation.org_code = requester.org_code ";

    if ( "$user_no" <> "" ) {
      $query .= " AND work_by_id=$user_no ";
    }

    if ( "$search_for" <> "" ) {
      $query .= " AND work_description ~* '$search_for' ";
    }
    if ( "$system_code" <> "" ) {
      $query .= " AND request.system_code='$system_code' ";
    }
    if ( "$org_code" <> "" ) {
      $query .= " AND requester.org_code='$org_code' ";
    }

    if ( "$after" != "" )
      $query .= " AND request_timesheet.work_on>'$after' ";
    if ( "$before" != "" )
      $query .= " AND request_timesheet.work_on<'$before' ";
    if ( "$uncharged" != "" ) {
      if ( "$charge" != "" )
        $query .= " AND request_timesheet.ok_to_charge=TRUE ";
      $query .= " AND request_timesheet.work_charged IS NULL ";
//      $query .= " ORDER BY org_code, work_on";
//      $query .= " $order_by ";
    }
    else {
 //     $query .= " ORDER BY organisation.org_code, request_timesheet.request_id, request_timesheet.work_on";
 //     $query .= " $order_by ";
      $query .= " LIMIT 100 ";
    }

    if( "$order_by" == "" && isset ($sort) && is_array($sort) ) {
      while( list( $k, $v ) = each ( $sort ) ) {
	      $order_by = " ORDER BY $k";
      }
    }

    $query .= " $order_by ";

    $result = pg_Exec( $wrms_db, $query );
    if ( ! $result ) {
      $error_loc = "timelist-form.php";
      $error_qry = "$query";
      include("inc/error.php");
    }
    else {
      echo "<p>&nbsp;" . pg_NumRows($result) . " timesheets found</p>\n"; // <p>$query</p>";
      if ( "$uncharged" != "" ) {
        echo "<FORM METHOD=POST ACTION=\"$REQUEST_URI";
        if ( ! strpos( $REQUEST_URI, "uncharged" ) ) echo "&uncharged=1";
        echo "\">\n";
      }
      echo "<table border=\"0\" align=center><tr>\n";
      echo "<th class=cols>Requested by</th>";
      echo "<th class=cols>Org.</th>";
      echo "<th class=cols>Debtor No.</th>";
      echo "<th class=cols>Done on</th>";
      echo "<th class=cols>Qty.</th>";
      echo "<th class=cols>Rate</th>";
      echo "<th class=cols>Done By</th>";
      echo '<th class=cols>WR No.<input TYPE="Image" src="images/down.gif" alt="Sort" BORDER="0" name="sort[request_timesheet.request_id]" ></th>';
    //  if ( "$uncharged" == "" )
    //    echo "<th class=cols>Charged on</th>";
      echo '<th class=cols>Work Description<input TYPE="Image" src="images/down.gif" alt="Sort" BORDER="0" name="sort[request_timesheet.work_description]" ></th>';
      echo "<th class=cols>WR Brief</th>";
      echo "<th class=cols>Charge</th>";
      echo "<th class=cols>Charged On</th>";
      echo "<th class=cols>Amount</th>";
      echo "<th class=cols>Invoice No.</th>";
      echo "</tr>\n";

      // Build table of organisations found
      for ( $i=0; $i < pg_NumRows($result); $i++ ) {
        $timesheet = pg_Fetch_Object( $result, $i );

        if(floor($i/2)-($i/2)==0) echo "<tr bgcolor=$colors[6]>";
        else echo "<tr bgcolor=$colors[7]>";

//        echo "<td class=sml>$timesheet->requester_name ($timesheet->abbreviation, #$timesheet->debtor_no)</td>\n";
        echo "<td class=sml>$timesheet->requester_name</td>\n";
 	echo "<td class=sml>$timesheet->abbreviation</td>\n";
 	echo "<td class=sml>$timesheet->debtor_no</td>\n";
        echo "<td class=sml nowrap>" . substr( nice_date($timesheet->work_on), 7) . "</td>\n";
        echo "<td class=sml nowrap>$timesheet->work_quantity $timesheet->work_units</td>\n";
        echo "<td class=sml align=right nowrap>$timesheet->work_rate&nbsp;</td>\n";
        echo "<td class=sml>$timesheet->worker_name</td>\n";
        if ( "$timesheet->work_charged" == "" ) {
          if ( "$uncharged" == "" ) echo "<td class=sml>uncharged</td>";
        }
        else
          echo "<td class=sml>" . substr( nice_date($timesheet->work_charged), 7) . "</td>";
        echo "<td class=sml><A HREF=$base_url/request.php?request_id=$timesheet->request_id>$timesheet->request_id</A></I></td>";
        echo "<td class=sml>" . html_format( $timesheet->work_description) . "</td>";

        if ( "$uncharged" != "" ) {
        //  echo "</tr>\n";
        //  if(floor($i/2)-($i/2)==0) echo "<tr bgcolor=$colors[6]>";
        //  else echo "<tr bgcolor=$colors[7]>";
          echo "<td class=sml valign=top>$timesheet->brief</td>";
          echo "<td class=sml><input type=checkbox value=1 name=\"chg_ok[$timesheet->timesheet_id]\"";
          if ( "$timesheet->ok_to_charge" == "t" ) echo " checked";
          echo "></td>";
          echo "<td class=sml><input type=text size=10 name=\"chg_on[$timesheet->timesheet_id]\" value=\"" . date( "d/m/Y" ) . "\"></td>";
          echo "<td class=sml><input type=text size=12 name=\"chg_amt[$timesheet->timesheet_id]\" value=\"\"></td>";
          echo "<td class=sml><input type=text size=6 name=\"chg_inv[$timesheet->timesheet_id]\" value=\"\"></td>";
          echo "</tr>\n";
        }
        echo "</tr>\n";
      }
      if ( "$uncharged" != "" ) {
        echo "<tr><td class=mand colspan=6 align=center><input TYPE=submit alt=\"apply changes\" name=submit value=\"Apply Charges\"></td></tr>\n";
        echo "</form>\n";
      }
      echo "</table>\n";
    }
  }
?>
</TABLE>
</FORM>

