<?php
  $query = "SELECT * ";
  $query .= " FROM lookup_code ";
  $query .= " WHERE source_table='$table' AND source_field='$field' ";
  if ( "$stext" <> "" )
    $query .= " AND (lookup_desc~*'$stext' OR lookup_code~*'$stext') ";
  $query .= " ORDER BY source_table, source_field, lookup_seq, lookup_code";

  $result = awm_pgexec( $wrms_db, $query );
  if ( ! $result ) {
    $error_loc = "inc/looklist.php";
    $error_qry = "$query";
    echo "</table>";  // so netscape actually shows the error!
    include( "inc/error.php" );
    exit;
  }

  echo "<br clear=all><table width=100% border=1 cellspacing=1 cellpadding=2>\n";
  echo "<tr><th height=30 class=cols>Seq</th><th class=cols>Code</th><th class=cols>Description</th>";
  echo "<th class=cols>Miscellaneous</th><th class=cols><span style=\" font-weight: 300;\">Actions</span></td></tr>\n";

function edit_line() {
  global $action, $look_href, $lookup_code, $lookup_seq, $lookup_desc, $lookup_misc, $edited, $maxdesc;
  echo "<form action=\"$look_href\" method=POST id=add name=add>";
  echo "<input type=hidden name=action value=";
  if ( "$action" == "edit" ) echo "update"; else echo "insert";
  echo "><input type=hidden name=old_lookup_code value=\"$lookup_code\">\n";
  echo "<td align=center nowrap class=sml><input class=sml Type=Text Name=lookup_seq Size=5 MAXLENGTH=10 Value=\"$lookup_seq\"></td>\n";
  echo "<td align=left class=sml><input class=sml type=text name=lookup_code size=8 MAXLENGTH=30 Value=\"$lookup_code\"></td>\n";
  if ( $maxdesc > 70 )
    echo "<td align=left class=sml><textarea name=lookup_desc rows=5 cols=60 wrap>$lookup_desc</textarea></td>\n";
  else
    echo "<td align=left class=sml><input class=sml Type=Text Name=lookup_desc Size=16 Value=\"$lookup_desc\"></td>\n";
  echo "<td align=left class=sml><input class=sml Type=Text Name=lookup_misc Size=12 Value=\"$lookup_misc\"></td>\n";
  echo "<td align=center><input class=submit type=submit value=\"";
  if ( "$action" == "edit" ) echo "Update"; else echo "Add";
  echo "\" name=submit></td>\n";
  echo "</form>";
  $edited = true;
}

  $max_desc = 0;
  $edited = false;
  if ( pg_NumRows($result) == 0 ) {
    echo "<tr><td colspan=99><H3>No codes found</h3></td></tr>";
  }
  else {
    for ( $i=0; $i < pg_NumRows($result); $i++ ) {
      $lookup = pg_Fetch_Object( $result, $i );

      // Note these tags aren't terminated until below...
      printf("<tr class=row%1d%s>\n", $i % 2, ( "$action" == "edit" && "$lookup_code" == "$lookup->lookup_code" ? " valign=top" : "") );

      $maxdesc = max( $maxdesc, strlen($lookup->lookup_desc) );
      if ( "$action" == "edit" && "$lookup_code" == "$lookup->lookup_code" ) {
        edit_line();
      }
      else {
        echo "<td class=sml>$lookup->lookup_seq&nbsp;</td>";
        echo "<td class=sml>$lookup->lookup_code&nbsp;</td>";
        echo "<td class=sml>$lookup->lookup_desc&nbsp;</td>";
        echo "<td class=sml>$lookup->lookup_misc&nbsp;</td>\n";
        echo "<td align=center class=sml><font size=1>\n";
        echo "<a class=submit href=\"$look_href&lookup_code=" . rawurlencode($lookup->lookup_code) . "&action=edit\">Edit</a> \n";
        echo "<a class=submit href=\"$look_href&lookup_code=" . rawurlencode($lookup->lookup_code) . "&action=delete\">Delete</a>";
        echo "</font></td>";
      }
      echo "</tr>\n";
    }
  }
  if ( ! $edited ) {
    echo "<tr valign=top>\n";
    edit_line();
    echo "</tr>\n";
  }

  echo "</table>\n";
?>

