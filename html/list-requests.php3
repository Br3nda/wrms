<?php
  include( "awm-auth.php3" );
  $title = "List Work Requests";
  include("$homedir/apms-header.php3"); 

  if ( !isset($bugtype) ) {
    $query = "SELECT setting_value FROM awm_usr_setting WHERE username = '$usr->username' AND setting_name = 'list-requests'";
    $rid = pg_Exec( $dbid, $query);
    if ( !$rid )
      echo "<P>Query failed:</P><P>$query</P>";
    else if ( pg_NumRows($rid) )
      list( $bugtype, $buguser, $bugactive, $bugsort, $bugorg, $bugsys, $style, $bugskip ) = explode( "~", pg_Result( $rid, 0, 0));
  }
  if ( !isset($buguser) ) $buguser = "";
  if ( !isset($bugactive) ) $bugactive = "active";
  if ( !isset($bugsort) ) $bugsort = "severity_code DESC";
  else if ( !strcmp( "all", strtolower($buguser) ) ) $buguser = "";
  if ( !isset($bugorg) ) $bugorg = $usr->org_code;
  if ( !isset($bugsys) ) $bugsys = "";
  if ( !isset($style) ) $style = "";
  if ( !isset($bugskip) ) $bugskip = "";

  include( "$funcdir/html_format-func.php3");

  /* update the current values into the users settings so that if we come here directly we */
  /* have a nice default waiting for them                                                  */
  if ( !isset($bugtype) ) {
    $bugtype = "";
    $query = "INSERT INTO awm_usr_setting ( username, setting_name, setting_value) VALUES( '$usr->username','list-requests', '" . implode( array( $bugtype, $buguser, $bugactive, $bugsort, $bugsys, $style, $bugskip ), "~") . "' )";
  }
  else {
    $query = "UPDATE awm_usr_setting SET setting_value = '" . implode( array( $bugtype, $buguser, $bugactive, $bugsort, $bugorg, $bugsys, $style, $bugskip ), "~" ) . "' WHERE username = '$usr->username' AND setting_name = 'list-requests'";
  }
  $rid = pg_Exec( $dbid, $query);

  $current = $bugtype;
  include( "$funcdir/severity-list.php3" );

  $styles[""] = "Short listing with date and status";
  $styles["brief"] = "Brief listing - #, name and status";
  $styles["detail"] = "Longer listing with detail of request";
  $styles["exhaustive"] = "Complete listing with detail and notes";
  $style_list = "";
  while( list( $k, $v ) = each( $styles ) ) {
    $style_list .= "<OPTION VALUE=\"$k\"";
    if ( !strcmp( $k, $style ) ) $style_list .= " SELECTED";
    $style_list .= ">$v";
  }
  include( "$funcdir/nice_date-func.php3" );

/*  echo "<P>Style=$style=</P>"; */
  echo "<TABLE BORDER=0 WIDTH=100%>\n";
  echo "<TR><TH>WR #</TH><TH>Requested by</TH>";
  if ( strcmp( "$style", "brief") )
    echo "<TH>Entry</TH> <TH>Last note</TH> <TH>ETA</TH> <TH>Urgency</TH>";
  else
    echo "<TH>Description</TH>";

  echo "<TH>Status</TH></TR>";
  if ( strcmp( "$style", "brief") )
    echo "<TR><TD COLSPAN=7><HR></TD></TR>";
  else
    echo "<TR><TD COLSPAN=4><HR></TD></TR>";

  $query = "SELECT DISTINCT ON request_id request_id, severity_code, ";
  $query .= "request_by, request_on, brief, detailed, eta, status_desc, ";
  $query .= "request.system_code, person.perorg_name AS fullname, org.perorg_sort_key AS org_code, ";
  $query .= "get_last_note_on(request_id) as last_noted";
  $query .= " FROM request, perorg_system, awm_usr AS requsr, awm_perorg AS person, awm_perorg AS org, status WHERE ";
  $filter = "request.active ";
  if ( $bugactive <> "active" ) $filter = "NOT " . $filter;
  if ( $bugtype <> "" ) $filter .= " AND request.severity_code >= '$bugtype'";
  if ( $buguser <> "" ) $filter .= " AND request.request_by ~* '$buguser'";
  if ( $bugorg <> "" )  $filter .= " AND org.perorg_sort_key ~* '$bugorg'";
  if ( $bugsys <> "" )  $filter .= " AND request.system_code ~* '$bugsys'";
  if ( $bugskip <> "" ) $filter .= " AND request.last_status !~* '$bugskip'";

  $query .= $filter . " AND request.request_by = requsr.username";
  $query .= " AND org.perorg_id = awm_get_rel_parent( requsr.perorg_id, 'Employer') ";
/*  $query .= " AND perorg_system.perorg_id = org.perorg_id "; */
  $query .= " AND person.perorg_id = request.requester_id ";
  $query .= " AND request.system_code = perorg_system.system_code";
  $query .= " AND status.status_code = request.last_status ";
  if ( $bugsort <> "" ) $query .= " ORDER BY $bugsort";
  $rid = pg_Exec( $dbid,$query );
  $rows = pg_NumRows( $rid );
  for ( $i=0; $i < $rows; $i++ ) {
    $request = pg_Fetch_Object( $rid, $i );
    if ( "$request->brief" == "" ) $request->brief = substr( $request->detailed, 0, 80);
    $href_req = "<A HREF=\"$wrms_home/modify-request.php3?request_id=$request->request_id\">";

    echo "<TR><TD ALIGN=RIGHT><B>$request->request_id</B>&nbsp;</TD>";
    echo "<TD>$request->fullname ($request->org_code)</TD><TD>";

    if ( strcmp( "$style", "brief") ) {
      echo nice_date( $request->request_on ) . "</TD><TD>";
      if ( strcmp( "", "$request->last_noted") ) echo nice_date( $request->last_noted );
      echo "</TD><TD>";
      if ( strcmp( "", "$request->eta") ) echo substr( nice_date( $request->eta ), 7);
      echo "</TD><TD ALIGN=CENTER>$request->severity_code</TD>";
    }
    else
      echo "$href_req$request->brief</A></TD>";

    echo "<TD>$request->status_desc</TD></TR>";

    if ( strcmp( "$style", "brief") ) {
      echo "<TR><TD>&nbsp;</TD><TD COLSPAN=6>$href_req$request->brief</A></TD></TR>";
      if ( !strcmp( "$style", "detail") || !strcmp( "$style", "exhaustive") )
        echo "<TR><TD></TD><TD COLSPAN=6>" . html_format( $request->detailed ) . "</TD></TR>";

      if ( !strcmp( "$style", "exhaustive") ) {
        echo "<TR><TD></TD><TD COLSPAN=6>";

        $noteq = pg_Exec( $dbid, "SELECT * FROM request_note WHERE request_note.request_id = '$request->request_id'");
        $noterows = pg_NumRows($noteq);
        if ( $noterows > 0 ) {
          echo "<TABLE BORDER=0 CELLSPACING=2 CELLPADDING=1 WIDTH=100% BGCOLOR=#b7daf0><TR><TD COLSPAN=2 ALIGN=LEFT><B>Notes</B></TD></TR>";
          for( $j=0; $j<$noterows; $j++ ) {
            $request_note = pg_Fetch_Object( $noteq, $j );
            echo "<TR><TD>$request_note->note_by</TD>";
            echo "<TD ROWSPAN=2>" . html_format($request_note->note_detail) . "</TD></TR>";
            echo "<TR><TD>";
            echo nice_date($request_note->note_on);
            echo "</TD></TR>";
          }
          echo "</TABLE>";
        }
        echo "</TD></TR>";
        echo "<TR BGCOLOR=#607790><TD COLSPAN=7><IMG src=images/clear.gif width=1 height=3 hspace=0 vspace=0></TD></TR>\n";
      }
      else
        echo "<TR><TD COLSPAN=7><FONT SIZE=-4><HR></FONT></TD></TR>\n";
    }
  }
?>
</TABLE>

<P>&nbsp;<BR CLEAR=ALL></P>
<H2>Adjust Query</H2>
<FORM ACTION="list-requests.php3" METHOD=POST>
<TABLE BORDER=1 ALIGN=CENTER WIDTH=100%>
<TR>
  <TH ALIGN=RIGHT>Minimum<BR>Urgency:</TH>
  <TD ALIGN=LEFT>&nbsp;<SELECT NAME="bugtype"><?php echo "$sev_list"; ?></SELECT></TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Username:</TH>
  <TD ALIGN=LEFT><INPUT TYPE="text" NAME="buguser" SIZE=25 MAXLENGTH=45 VALUE="<?php echo $buguser ?>"> &nbsp;&nbsp;(leave blank for all users)</TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Organisation:</TH>
  <TD ALIGN=LEFT><INPUT TYPE="text" NAME="bugorg" SIZE=10 MAXLENGTH=45 VALUE="<?php echo $bugorg ?>"> &nbsp;&nbsp;(leave blank for all organisations)</TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>System:</TH>
  <TD ALIGN=LEFT><INPUT TYPE="text" NAME="bugsys" SIZE=10 MAXLENGTH=45 VALUE="<?php echo $bugsys ?>"> &nbsp;&nbsp;(leave blank for all systems)</TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Skip Statuses:</TH>
  <TD ALIGN=LEFT><INPUT TYPE="text" NAME="bugskip" SIZE=10 MAXLENGTH=45 VALUE="<?php echo $bugskip ?>"> &nbsp;&nbsp;(leave blank for all statuses)</TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Listing<BR>Style:</TH>
  <TD ALIGN=LEFT>&nbsp;<SELECT NAME="style"><?php echo "$style_list"; ?></SELECT></TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Sort by:</TH>
  <TD ALIGN=LEFT>
    &nbsp;<LABEL><INPUT TYPE="radio" NAME="bugsort" VALUE="request_id ASC"<?php if ( !strcmp($bugsort,"request_id ASC")) echo " CHECKED"; ?>> Increasing WR # </LABEL>
    &nbsp;&nbsp;
    &nbsp;<LABEL><INPUT TYPE="radio" NAME="bugsort" VALUE="request_id DESC"<?php if ( !strcmp($bugsort,"request_id DESC")) echo " CHECKED"; ?>> Decreasing WR # </LABEL>
    &nbsp;&nbsp;
    &nbsp;<LABEL><INPUT TYPE="radio" NAME="bugsort" VALUE="severity_code DESC"<?php if ( !strcmp($bugsort,"severity_code DESC")) echo " CHECKED"; ?>> Decreasing severity </LABEL>
    &nbsp;&nbsp;
    &nbsp;<LABEL><INPUT TYPE="radio" NAME="bugsort" VALUE="last_noted DESC"<?php if ( !strcmp($bugsort,"last_noted DESC")) echo " CHECKED"; ?>> Last Notes </LABEL>
  </TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Active:</TH>
  <TD ALIGN=LEFT>
    &nbsp;<LABEL><INPUT TYPE="radio" NAME="bugactive" VALUE="active" CHECKED> Active requests </LABEL>
    &nbsp;&nbsp;
    &nbsp;<LABEL><INPUT TYPE="radio" NAME="bugactive" VALUE="NOT active"> Inactive requests </LABEL>
  </TD>
</TR>

<TR>
  <TD COLSPAN=2 ALIGN=CENTER><B><INPUT NAME="submit" TYPE="submit" VALUE=" Refresh Listing "></B></TD>
</TR>
</TABLE>
</FORM>

<?php
 if ( $usr->access_level > 99800 ) echo "<P>Query was:<BR>$query</P>";
 include("$homedir/apms-footer.php3"); ?>

