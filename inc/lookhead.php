<table border="0" cellspacing="0" cellpadding="1">
<?php
  $query = "SELECT lookup_code, lookup_desc FROM lookup_code ";
  $query .= " WHERE source_table='codes' AND source_field='menus' ";
  $query .= " ORDER BY lookup_seq;";
  $result = awm_pgexec( $dbconn, $query, "lookhead");
  if ( $result && pg_NumRows($result) > 0 ) {
    echo "<tr><td>\n";
    for ( $i=0; $i < pg_NumRows($result); $i++) {
      $codes = pg_Fetch_Object( $result, $i );
      list( $s_table, $s_field ) = explode( "|", "$codes->lookup_code" );
      if ( $agent == "moz4" && $i > 0 ) echo " | ";
      printf( "<a class=submit href=\"lookups.php?table=$s_table&field=$s_field\">%s$codes->lookup_desc%s</a>\n",
                       ("$table$field" == "$s_table$s_field" ? "<b>*" : ""), ("$table$field" == "$s_table$s_field" ? "*</b>" : "") );
    }
    echo "</td></tr>\n";
  }

?>
</table>
