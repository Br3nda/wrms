<?php
//  include("$base_dir/inc/code-list.php");
//  include("$base_dir/inc/nice-date.php");
// <P class=helptext>Use this form to select sessions for maintenance or review.</P>
?>

<FORM METHOD=POST ACTION="<?php echo "$SCRIPT_NAME?form=$form"; ?>">
<table align=center><tr valign=middle>
<td><b>Sessions Prior To:</b><font size=2><input TYPE="Text" Size="20" Name="search_for" Value="<?php echo "$search_for"; ?>"></font></td>
<td><input TYPE="Image" src="images/in-go.gif" alt="go" WIDTH="64" BORDER="0" HEIGHT="27" name="submit"></td>
</tr></table>
</form>  

<?php
  if ( !($roles['Admin'] || $roles['Support']) ) $session_id = $session->session_id;
  if ( "$search_for$system_code " != "" ) {
    $query = "SELECT *, (session_end - session_start)::timespan AS duration FROM usr, session ";
    $query .= "WHERE usr.user_no = session.user_no ";
    if ( "$search_for" <> "" )
      $query .= " AND session_start <= '$search_for' ";
    if ( "$user_no" <> "" )
      $query .= " AND session.user_no = '$user_no' ";
    $query .= " ORDER BY session.session_start DESC";
    $query .= " LIMIT 30 ";
    $result = awm_pgexec( $wrms_db, $query );
    if ( ! $result ) {
      $error_loc = "sessionlist-form.php";
      $error_qry = "$query";
      include("inc/error.php");
    }
    else {
      echo "<p>" . pg_NumRows($result) . " sessions found</p>"; // <p>$query</p>";
      echo "<table border=\"0\" align=center><tr>\n";
      echo "<th class=cols>Session #</th><th class=cols>Name</th>";
      echo "<th class=cols align=left>Start</th>";
      echo "<th class=cols align=left>Duration</th>";
      echo "</tr>";

      // Build table of sessions found
      for ( $i=0; $i < pg_NumRows($result); $i++ ) {
        $thissession = pg_Fetch_Object( $result, $i );

        if(floor($i/2)-($i/2)==0) echo "<tr bgcolor=$colors[6]>";
        else echo "<tr bgcolor=$colors[7]>";

        echo "<td align=center>&nbsp;$thissession->session_id&nbsp;</td>\n";

        echo "<td>&nbsp;<a href=\"usr.php?user_no=$thissession->user_no\">$thissession->full_name";
        if ( "$thissession->full_name" == "" ) echo "$thissession->username";
        echo "</a>&nbsp;</td>\n";

        echo "<td>" . nice_date($thissession->session_start) . "</td>\n";
        echo "<td>$thissession->duration</td>\n";

        echo "</tr>\n";
      }
//      echo "<tr><td class=mand colspan=5 align=center><a class=r href=\"form.php?form=session&session_id=new\">Add A New Session</a>";
      echo "</table>\n";
    }
  }
?>
</TABLE>
</FORM>

