<?php
  include( "awm-auth.php3" );
  $title = "List Work Requests";
  include("$homedir/apms-header.php3"); 
?>
<FORM method="post" action="search.php3">
<TABLE BORDER=0 CELLPADDING=4 CELLSPACING=0 BGCOLOR=#c7e097 ALIGN=CENTER>

<TR><TD><INPUT TYPE=TEXT SIZE=40 MAXLENGTH=150 NAME=searchfor VALUE="<? echo "$searchfor"; ?>"></TD>
<TD VALIGN=MIDDLE ALIGN=CENTER ROWSPAN=2><B><INPUT name="submit" value=" Search " type="submit"></B></TD></TR>
</TABLE></FORM>

<?php
  /* Only continue if we are actioning a previous submission... */
  if ( !isset( $searchfor )  ) {
    include("$homedir/apms-footer.php3");
    exit;
  }
  echo "<HR>";

  $query = "SELECT * FROM request WHERE detailed ~* '$searchfor' OR brief ~* '$searchfor'";
  $query .= " ORDER BY request_id DESC ";

/*  echo "<BR>$query"; */
  $rid = pg_Exec( $dbid, $query );
  $rows = pg_NumRows( $rid );
  echo "<BR>Found $rows request matches...<DIV ALIGN=CENTER><TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3>\n";
  for ( $i=0; $i<$rows; $i++ ) {
    $ob = pg_Fetch_Object( $rid, $i);
    if ( ($i / 2) == round($i / 2) ) $colors = "#f0f0b0"; else $colors = "#f0f0d0";
    echo "<TR BGCOLOR=$colors>";
    echo "<TD>$ob->request_id</TD>";
    echo "<TD><A HREF=modify-request.php3?request_id=$ob->request_id>";
    if ( "$ob->brief" == "" ) echo "link"; else echo "$ob->brief";
    echo "</A></TD></TR>\n";
  }
  echo "</TABLE></DIV>\n";

  $query = "SELECT * FROM request_note, request WHERE note_detail ~* '$searchfor' ";
  $query .= " AND request_note.request_id = request.request_id ";
  $query .= " ORDER BY request_note.request_id DESC ";

/*  echo "<BR>$query"; */
  $rid = pg_Exec( $dbid, $query );
  $rows = pg_NumRows( $rid );
  echo "<BR>Found $rows note matches...<DIV ALIGN=CENTER><TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3>\n";
  for ( $i=0; $i<$rows; $i++ ) {
    $ob = pg_Fetch_Object( $rid, $i);
    if ( ($i / 2) == round($i / 2) ) $colors = "#f0f0b0"; else $colors = "#f0f0d0";
    echo "<TR BGCOLOR=$colors>";
    echo "<TD>$ob->request_id</TD>";
    echo "<TD><A HREF=modify-request.php3?request_id=$ob->request_id>";
    if ( "$ob->brief" == "" ) echo "link"; else echo "$ob->brief";
    echo "</A></TD></TR>\n";
  }
  echo "</TABLE></DIV>\n";

  include("$homedir/apms-footer.php3");
?>
