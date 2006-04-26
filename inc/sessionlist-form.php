<FORM METHOD=POST ACTION="<?php echo "$SCRIPT_NAME?form=$form"; ?>">
<table align=center><tr valign=middle>
<td><b>Sessions Prior To:</b><font size=2><input TYPE="Text" Size="20" Name="search_for" Value="<?php echo "$search_for"; ?>"></font></td>
<td><input TYPE="Image" src="<?php echo $images; ?>/in-go.gif" alt="go" BORDER="0" name="submit"></td>
</tr></table>
</form>

<?php
  if ( ! is_member_of('Admin','Support') ) $session_id = $session->session_id;
  if ( "$search_for " != "" ) {
    $query = "SELECT *, (session_end - session_start)::interval AS duration FROM usr, session ";
    $query .= "WHERE usr.user_no = session.user_no ";
    if ( "$search_for" <> "" )
      $query .= " AND session_start <= '$search_for' ";
    if ( "$user_no" <> "" )
      $query .= " AND session.user_no = '$user_no' ";
    $query .= " ORDER BY session.session_start DESC";
    $query .= " LIMIT 30 ";
    $result = awm_pgexec( $dbconn, $query );
    if ( ! $result ) {
      $error_loc = "sessionlist-form.php";
      $error_qry = "$query";
      include("error.php");
    }
    else {
      echo "<p>" . pg_NumRows($result) . " sessions found</p>";
      echo "<table border=\"0\" align=center><tr>\n";
      echo "<th class=cols>Session #</th><th class=cols>Name</th>";
      echo "<th class=cols align=left>Start</th>";
      echo "<th class=cols align=left>Duration</th>";
      echo "</tr>";

      // Build table of sessions found
      for ( $i=0; $i < pg_NumRows($result); $i++ ) {
        $thissession = pg_Fetch_Object( $result, $i );

        if ( $i % 2  == 0 ) echo "<tr bgcolor=$colors[6]>";
        else echo "<tr bgcolor=$colors[7]>";

        echo "<td align=center>&nbsp;$thissession->session_id&nbsp;</td>\n";

        echo "<td>&nbsp;<a href=\"user.php?user_no=$thissession->user_no\">$thissession->fullname";
        if ( "$thissession->fullname" == "" ) echo "$thissession->username";
        echo "</a>&nbsp;</td>\n";

        echo "<td>" . nice_date($thissession->session_start) . "</td>\n";
        echo "<td>$thissession->duration</td>\n";

        echo "</tr>\n";
      }
      echo "</table>\n";
    }
  }

  echo "</table>\n</form>\n";
?>
