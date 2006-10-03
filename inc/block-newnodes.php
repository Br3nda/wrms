<?php
function send_newnodes_block() {
global $theme;
  $qry = new PgQuery("SELECT * FROM wu JOIN infonode USING(node_id) JOIN usr ON ( wu_by = user_no ) ORDER BY wu_on DESC LIMIT 20;");
  if ( ! $qry->Exec("newnodes") || $qry->rows == 0 ) return;
  $theme->BlockOpen();
  $theme->BlockTitle("New Nodes");

  $i=0;
  while ( $wu = $qry->Fetch() ) {
    if ( $i++ > 0 ) echo "<br>\n";
    echo "<a class=blockhead href=\"/wu.php?node_id=$wu->node_id\" class=block>$wu->nodename</a> by $wu->username\n";
  }

  echo "<img src=\"/images/clear.gif\" width=\"155\" height=\"50\" hspace=\"0\" vspace=\"2\" border=\"0\">\n";

  $theme->BlockClose();

}
send_newnodes_block();
?>
