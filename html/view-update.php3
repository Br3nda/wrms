<?php
  include( "awm-auth.php3" );
  include("$funcdir/parameters-func.php3");
  $args = parse_parameters( $argv[0] );
  $title = "View Update U$args->update_id";
  include("$homedir/apms-header.php3");
  include("$funcdir/nice_date-func.php3");

 /* Complex request mainly because we hook in all of the codes tables for easy display */
  $rows = 0;
  $query = "SELECT * FROM system_update, awm_usr, work_system ";
  $query .= "WHERE system_update.update_id = '$args->update_id'";
  $query .= " AND system_update.update_by_id = awm_usr.perorg_id";
  $query .= " AND system_update.system_code = work_system.system_code";

  /* now actually query the database... */
  $rid = pg_Exec( $dbid, $query);
  if ( $rid ) $rows = pg_NumRows($rid);
  if ( ! $rid || ! $rows ) {
    echo "<H3>&nbsp;Query Error -update #$update_id not found in database!</H3>";
    echo "<P>The error returned was:</P><PRE>" . pg_ErrorMessage( $dbid ) . "</PRE>";
    echo "<P>The failed query was:</P><PRE>$query</PRE>";
    include("$homedir/apms-footer.php3");
    exit;
  }
  $update = pg_Fetch_Object( $rid, 0 );

  /* status list so we can have a drop-down for the 'request-list' bit at the bottom. */
  $query = "SELECT * FROM request_update WHERE update_id = $args->update_id";
  $rid = pg_Exec( $dbid, $query);
  $rows = pg_NumRows( $rid );
  for ( $i = 0; $i < $rows; $i++ ) {
    $req_update = pg_Fetch_Object( $rid, $i );
    $current[$req_update->request_id] = 1;
  }
  include("$funcdir/active-request-list.php3");

  /* System Admin of request only if admin for system (funny that!)*/
  $sysadm = !strcmp( $update->admin_usr, $usr->username );

  /* Current update is editable if the user requested it or user is administrator */
  $editable = !strcmp( $update->username, $usr->username );
  if ( ! $editable ) $editable = $sysadm;

  if ( $editable ) {
    /* if it's editable then we'll need a system list for drop-down */
    $current = $update->system_code;
    include("$funcdir/system-list.php3");
  }

?>

<TABLE WIDTH="100%" BORDER=0 CELLPADDING=0 CELLSPACING=0>
<TR><TD ALIGN=LEFT VALIGN=BOTTOM><H2>Update File Details</H2></TD>
   <?php echo "<TH ALIGN=CENTER VALIGN=CENTER><A HREF=\"$update->file_url\">Download this Update</A></TH>"; ?>
</TR></TABLE>

<?php
  if ( $editable ) {
    echo "<FORM ACTION=\"modify-update.php3\" METHOD=POST>";
    echo "<INPUT TYPE=\"hidden\" NAME=\"update_id\" VALUE=\"$update->update_id\">";
  }
?>
<TABLE BORDER=1 CELLSPACING=1 CELLPADDING=1 ALIGN=CENTER WIDTH=100%>
<TR>
  <TH ALIGN=RIGHT WIDTH=20%><FONT SIZE=+2>U<?php echo "$update->update_id</FONT>"; ?></TH>
  <TD ALIGN=LEFT WIDTH=80%>&nbsp;
<?php
  if ( $editable )
    echo "<INPUT TYPE=text SIZE=68 NAME=\"new_update_brief\" VALUE=\"$update->update_brief\">";
  else
    echo "$update->update_brief";
?>
  </TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Description:</TH>
  <TD ALIGN=LEFT>&nbsp;
<?php
  if ( $editable )
    echo "<TEXTAREA NAME=\"new_description\" ROWS=10 COLS=66 WRAP=\"SOFT\">$update->update_description</TEXTAREA>";
  else
    echo "$update->update_description";
?>
  </TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>By:</TH>
  <TD ALIGN=LEFT>
<?php
  if ( $editable )
    echo "<INPUT TYPE=text SIZE=30 NAME=\"new_update_by\" VALUE=\"$update->update_by\">";
  else
    echo "$update->update_by";

  echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<B><SPAN ID=\"likeTH\" STYLE=\"font-size: 16px; font-family: verdana, sans-serif; color: #000050; font-weight: bold;\">On:</SPAN></B>&nbsp;&nbsp;";
  if ( $editable )
    echo "<INPUT TYPE=text SIZE=30 NAME=\"new_update_on\" VALUE=\"$update->update_on\">";
  else
    echo "$update->update_on";
?>
  <TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>File URL:</TH>
  <TD ALIGN=LEFT>
<?php
  if ( $editable )
    echo "<INPUT TYPE=text SIZE=75 MAXLENGTH=255 NAME=\"new_file_url\" VALUE=\"$update->file_url\">";
  else
    echo "$update->file_url";
?>
  </TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>System:</TH>
  <TD ALIGN=LEFT>
<?php
  if ( $editable )
    echo "<SELECT NAME=\"new_system\">$system_list</SELECT>";
  else
    echo "$update->system_code - $update->system_desc";
?>
  </TD>
</TR>

<?php
  if ( $editable ) {
    echo "<TR><TH ALIGN=RIGHT>Requests:</TH><TD ALIGN=LEFT>\n";
    echo "<TABLE WIDTH=90% BORDER=1><TR><TD WIDTH=70% ALIGN=RIGHT><SELECT SIZE=15 MULTIPLE NAME=\"new_requests[]\">$request_list</SELECT></TD><TD WIDTH=30% VALIGN=BOTTOM><FONT SIZE=-2 FACE=sans-serif>Only active requests are listed.  To assign the update against an inactive request you would first need to re-activate it.</FONT></TD></TR></TABLE>\n";
    echo "</TD></TR>\n";
  }

  if ( $editable ) {
    /* of course, if it's editable we need to have an update button don't we, so here it is!  */
    echo "<TR><TD COLSPAN=2 ALIGN=CENTER>\n";
    echo "<B><INPUT TYPE=\"submit\" NAME=\"submit\" VALUE=\" Apply Changes \"></B></TD></TR></TABLE></FORM>\n";
  }
  else
    echo "</TABLE>\n";
?>


<?php /***** Request Details */
  $query = "SELECT * FROM request_update, request WHERE request_update.update_id = $args->update_id AND request_update.request_id = request.request_id";
  $requestq = pg_Exec( $dbid, $query);
  $rows = pg_NumRows($requestq);
  if ( $rows > 0 ) {
?>
 &nbsp;<BR CLEAR=ALL><H2>Work Requests Updated</H2>
 <TABLE BORDER=1 ALIGN=CENTER WIDTH=100%>
 <TR VALIGN=TOP>
   <TH ALIGN=LEFT>Request</TH>
   <TH>Requested On</TH>
   <TH>Description</TH>
   <TH>System</TH>
 </TR>
<?php
    for( $i=0; $i<$rows; $i++ ) {
      $request = pg_Fetch_Object( $requestq, $i );

      echo "<TR><TD>$request->request_id&nbsp;</TD>";
      echo "<TD>" . nice_date($request->request_on) . "&nbsp;</TD>";
      echo "<TD><A HREF=\"$wrms_home/view-request.php3?request_id=$request->request_id\">$request->brief</A></TD>";
      echo "<TD>$request->system_code</TD></TR>";
    }
    echo "</TABLE>";
  }
  include("$homedir/apms-footer.php3");
?>
