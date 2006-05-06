<?php
require_once("always.php");
require_once("authorisation-page.php");

$systems = array();
$qry = new PgQuery( "SELECT * FROM work_system WHERE active ORDER BY system_desc ASC;" );
if ( !$qry->Exec("rqchange") || $qry->rows == 0 ) {
  $error_message = "Can't find any active systems";
}
  require_once("top-menu-bar.php");
  require_once("page-header.php");

// Fetch the systems into an array
while( $row = $qry->Fetch() ) {
  $systems[$row->system_id] = $row ;
}

function add_system_data( $sql, $column ) {
global $systems;

  $qry = new PgQuery( $sql );
  if ( !$qry->Exec("rqchange") || $qry->rows == 0 ) return;

  while( $row = $qry->Fetch() ) {
    $systems[$row->key]->{$column} = $row->data ;
    if ( isset($row->system_desc) )
      $systems[$row->key]->{'system_desc'} = $row->system_desc ;
  }
}

$sql = "SELECT system_id AS key, count(*) AS data FROM request ";
$sql .= "WHERE request_on > 'today'::timestamp - '1 week'::interval GROUP BY system_id;";
add_system_data( $sql, 'new_in_week' );

$sql = "SELECT system_id AS key, count(*) AS data FROM request_status INNER JOIN request USING ( request_id ) ";
$sql .= "WHERE status_on > 'today'::timestamp - '1 week'::interval AND status_code = 'F' GROUP BY system_id;";
add_system_data( $sql, 'done_in_week' );

$sql = "SELECT work_system.system_id AS key, count(*) AS data, work_system.system_desc FROM work_system ";
$sql .= "JOIN request USING (system_id) ";
$sql .= "WHERE request.active AND last_status != 'F' GROUP BY work_system.system_id, work_system.system_desc;";
add_system_data( $sql, 'still_active' );

// Now output the collected report.
echo "<table width=\"100%\">\n";
echo "<tr>\n";
echo "<th class=cols align=left>Code</td>\n";
echo "<th class=cols align=left>Description</td>\n";
echo "<th class=cols>New</td>\n";
echo "<th class=cols>Done</td>\n";
echo "<th class=cols>Active</td>\n";
echo "</tr>\n";

reset($systems);
$i = 0;
foreach( $systems AS $scode => $sys ) {
  $class = " class=row" . $i++ % 2;
  $url1 = "/requestrank.php?system_id=".urlencode($scode)."&inactive=0&status[N]=1&status[R]=1&status[H]=1&status[C]=1&status[I]=1&status[L]=1&status[T]=1&status[Q]=1&status[A]=1&status[D]=1&status[S]=1&status[P]=1&status[Z]=1";
  $url2 = "/requestlist.php?style=stripped&format=brief&system_id=".urlencode($scode)."&inactive=0&incstat[N]=1&incstat[R]=1&incstat[H]=1&incstat[C]=1&incstat[I]=1&incstat[L]=1&incstat[T]=1&incstat[Q]=1&incstat[A]=1&incstat[D]=1&incstat[S]=1&incstat[P]=1&incstat[Z]=1";
  echo "<tr>\n";
  echo "<td$class>$scode</td>\n";
  echo "<td$class><a href=\"$url1\" target=_new>$sys->system_desc</a></td>\n";
  echo "<td$class align=center>$sys->new_in_week</td>\n";
  echo "<td$class align=center>$sys->done_in_week</td>\n";
  echo "<td$class align=center><a href=\"$url2\" target=_new>$sys->still_active</a></td>\n";
  echo "</tr>\n";
}
echo "</table>\n";

include("page-footer.php");

?>
