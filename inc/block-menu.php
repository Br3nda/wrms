<?php
  block_open();
//  block_title("<a title=\"\" href=\"usr.php?user_no=$session->user_no\" class=blockhead>$session->fullname</a>");
  block_title("&nbsp;");
  echo "<tr><td class=block>\n &nbsp;";
  echo "<a href=\"usr.php?user_no=$session->user_no\" class=block>Edit&nbsp;My&nbsp;Info</a>\n";
  $my_uri = ereg_replace( "[?&]togglehelp=[0-9]", "", $REQUEST_URI);
  echo  "<br>\n &nbsp;<a href=\"$my_uri";
  echo  ( !strpos($my_uri,"?") ? "?" : "&");
  echo "togglehelp=1\" class=block>Turn Help " . ( "$session->help" == "t" ?"Off":"On") . "</a>";
  echo  "<br>\n &nbsp;<a href=\"/?M=LO$hurl\" class=block>Log Off</a>";
  echo  "<br>\n &nbsp;<a href=\"/?M=LO&forget=1$hurl\" class=block>Forget Me</a>";

  if ( $roles[wrms][Request] ) {
    echo "<br><img class=blocksep src=\"images/menuBreak.gif\" width=\"130\" height=\"9\"><br>\n &nbsp;<a href=$base_url/request.php class=block>New&nbsp;Request</a>";
    $query = "SELECT * FROM saved_queries WHERE user_no = '$session->user_no' ORDER BY query_name";
    $result = awm_pgexec( $dbconn, $query, "block-menu");
    if ( $result && pg_NumRows($result) > 0) {
      for ( $i=0; $i < pg_NumRows($result); $i++ ) {
        $thisquery = pg_Fetch_Object( $result, $i );
        echo "<br>\n &nbsp;<a href=\"$base_url/requestlist.php?style=plain&qry=" . urlencode($thisquery->query_name) . "\" class=block>$thisquery->query_name</a>";
      }
    }
    echo "<br>\n &nbsp;<a href=$base_url/index.php class=block>My&nbsp;Requests</a>";
    echo "<br>\n &nbsp;<a href=$base_url/requestlist.php class=block>List&nbsp;Requests</a>";
  }

  if ( $roles[wrms][Manage] ) {
    echo "<br>\n &nbsp;<a href=$base_url/requestlist.php?qs=complex class=block>Request&nbsp;Search</a>";
    echo "<br><img class=blocksep src=\"images/menuBreak.gif\" width=\"130\" height=\"9\"><br>\n &nbsp;<a href=\"$base_url/form.php?form=organisation&org_code=$session->org_code\" class=block>My&nbsp;Organisation</a>";
    echo "<br>\n &nbsp;<a href=\"$base_url/usrsearch.php?org_code=$session->org_code\" class=block>Our&nbsp;Users</a>";
    echo "<br>\n &nbsp;<a href=\"$base_url/usr.php?org_code=$session->org_code\" class=block>New&nbsp;User</a>";
    echo "<br>\n &nbsp;<a href=\"$base_url/form.php?form=syslist&org_code=$session->org_code\" class=block>Our&nbsp;Systems</a>";
  }

  if ( $roles[wrms][Support] ) {
    echo "<br><img class=blocksep src=\"images/menuBreak.gif\" width=\"130\" height=\"9\"><br>\n &nbsp;<a href=$base_url/form.php?f=orglist class=block>All&nbsp;Organisations</a>";
    echo "<br>\n &nbsp;<a href=$base_url/form.php?f=syslist class=block>All&nbsp;Systems</a>";
    echo "<br>\n &nbsp;<a href=$base_url/form.php?f=timelist&user_no=$session->user_no&uncharged=1 class=block>My&nbsp;Uncharged&nbsp;Work</a>";
    echo "<br>\n &nbsp;<a href=$base_url/form.php?f=work&user_no=$session->user_no&uncharged=1 class=block>Gav's&nbsp;Work</a>";
    echo "<br>\n &nbsp;<a href=$base_url/form.php?f=timelist&uncharged=1 class=block>All&nbsp;Work</a>";
    echo "<br>\n &nbsp;<a href=$base_url/form.php?f=timelist&uncharged=1&charge=1 class=block>Work&nbsp;To&nbsp;Charge</a>";
  }

  if ( $roles[wrms][Admin] ) {
    echo "<br><img class=blocksep src=\"images/menuBreak.gif\" width=\"130\" height=\"9\"><br>\n &nbsp;<a href=$base_url/lookups.php class=block>Lookup&nbsp;Codes</a>";
    echo "<br>\n &nbsp;<a href=$base_url/form.php?form=sessionlist class=block>Sessions</a>";
  }

  echo "</td></tr>\n";
  block_close();

  echo "<img src=\"images/clear.gif\" width=\"155\" height=\"50\" hspace=\"0\" vspace=\"2\" border=\"0\">\n";

?>