<table Border="0" CELLSPACING="0" CELLPADDING=1>
<?php
  $query = "SELECT lookup_code, lookup_desc FROM lookup_code ";
  $query .= " WHERE source_table='codes' AND source_field='menus' ";
  $query .= " ORDER BY lookup_seq";
  $result = awm_pgexec( $wrms_db, $query);
  if ( ! $result ) {
    $error_loc = "lookhead.php";
    $error_qry = "$query";
  }
  else if ( pg_NumRows($result) > 0 ) {
    echo "<tr><td BGCOLOR=$colors[6] class=menu><font size=1>\n";
    for ( $i=0; $i < pg_NumRows($result); $i++) {
      $codes = pg_Fetch_Object( $result, $i );
      list( $s_table, $s_field ) = explode( "|", "$codes->lookup_code" );
      echo "<a href=lookups.php?table=$s_table&field=$s_field";
      if  ( "$table$field" == "$s_table$s_field" ) echo " class=r><b>*"; else echo ">";
      echo "$codes->lookup_desc";
      if  ( "$table$field" == "$s_table$s_field" ) echo "*</b>"; 
      echo "</a>";
      if ( ($i + 1) < pg_NumRows($result) ) echo " | ";
      echo "\n";
    }
    echo "</font></td></tr>\n";
  }

?>
</table>

