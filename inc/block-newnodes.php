<?php
  $query = "SELECT * FROM wu, infonode, usr ";
  $query .= "WHERE wu.node_id = infonode.node_id AND wu.wu_by = usr.user_no ";
  $query .= "ORDER BY wu_on DESC LIMIT 20;";
  $rid = awm_pgexec( $dbconn, $query, "newnodes");
  if ( ! $rid || pg_NumRows($rid) == 0 ) return;
  block_open($colors['row1'], $colors['bg2'] );
  block_title("New Nodes");
  echo "<tr><td class=block style=\"padding: 3px;\">\n";

  for ( $i = 0; $i < pg_NumRows($rid); $i ++) {
    if ( $i > 0 ) echo "<br>\n";
    $wu = pg_Fetch_Object( $rid, $i);
    echo "<a class=blockhead href=\"/wu.php?node_id=$wu->node_id\" class=block>$wu->nodename</a> by $wu->username\n";
  }

  echo "</td></tr>\n";
  block_close();

  echo "<img src=\"/images/clear.gif\" width=\"155\" height=\"50\" hspace=\"0\" vspace=\"2\" border=\"0\">\n";

?>
