<?php

  if ( ! is_member_of('Admin','Support') ) return;

  $qry = new PgQuery("SELECT * FROM attachment_type ORDER BY seq;");
  if ( $qry->Exec('Form::att_type') ) {
    echo "<small>" . $qry->rows . " types found";
    echo "<table border=\"0\" align=\"center\">";
    if ( $qry->rows > 0 ) {
      echo "<tr>\n";
      echo '<th class="pcol">Code</th>';
      echo '<th class="pcol">Description</th>';
      echo '<th class="pcol" align="center">Seq</th>';
      echo '<th class="pcol">Mime Type</th>';
      echo '<th class="pcol">Pattern</th>';
      echo '<th class="pcol">Mime Pattern</th>';
      echo "</tr>\n";

      // Build table of systems found
      $i = 0;
      $line_format  = '<tr class="row%1d">';
      $line_format .= '<td class="sml"><a href="attachment_type.php?type_code=%s">%s</a></td>';
      $line_format .= '<td class="sml"><a href="attachment_type.php?type_code=%s">%s</a></td>';
      $line_format .= '<td class="sml" align="right">%d</td>';
      $line_format .= '<td class="sml">%s</td>';
      $line_format .= '<td class="sml">%s</td>';
      $line_format .= '<td class="sml">%s</td>';
      $line_format .= "</tr>\n";

      while( $row = $qry->Fetch() ) {
        printf($line_format, $i++ % 2, $row->type_code, $row->type_code, $row->type_code, $row->type_desc,
                            $row->seq, $row->mime_type, $row->pattern, $row->mime_pattern );

      }
    }
    echo "<tr><td class=\"mand\" colspan=\"6\" align=\"center\"><a class=\"submit\" href=\"attachment_type.php\">New Attachment Type</a></td></tr>";
    echo "</table>\n";
  }
?>