<?php
  block_open();
//  block_title("<a title=\"\" href=\"usr.php?user_no=$session->user_no\" class=blockhead>$session->fullname</a>");
  block_title("&nbsp;");
  echo "<tr><td class=block>\n &nbsp;";
  $tooltip = "Maintain your name, phone and e-mail details, or change your password";
  echo "<a href=\"usr.php?user_no=$session->user_no\" class=block title=\"$tooltip\" alt=\"$tooltip\">Edit&nbsp;My&nbsp;Info</a>\n";

  /*
  $my_uri = ereg_replace( "[?&]togglehelp=[0-9]", "", $REQUEST_URI);
  echo  "<br>\n &nbsp;<a href=\"$my_uri";
  echo  ( !strpos($my_uri,"?") ? "?" : "&");
  echo "togglehelp=1\" class=block>Turn Help " . ( "$session->help" == "t" ?"Off":"On") . "</a>";
  */
  $tooltip = "Au revoir!";
  echo  "<br>\n &nbsp;<a href=\"/?M=LO$hurl\" class=block title=\"$tooltip\" alt=\"$tooltip\">Log Off</a>";
  $tooltip = "Log me out and stop logging me in automatically";
  echo  "<br>\n &nbsp;<a href=\"/?M=LO&forget=1$hurl\" class=block title=\"$tooltip\" alt=\"$tooltip\">Forget Me</a>";

  if ( $roles[wrms][Request] ) {
    echo "<br><img class=blocksep src=\"images/menuBreak.gif\" width=\"130\" height=\"9\">";
    $tooltip = "Enter a new work request into the system.";
    echo "<br>\n &nbsp;<a href=$base_url/request.php class=block>New&nbsp;Request</a>";
    $tooltip = "Display work requests that you are notified about.";
    echo "<br>\n &nbsp;<a href=$base_url/index.php class=block title=\"$tooltip\" alt=\"$tooltip\">My&nbsp;Requests</a>";
    $tooltip = "Search and list work requests";
    echo "<br>\n &nbsp;<a href=$base_url/requestlist.php class=block title=\"$tooltip\" alt=\"$tooltip\">List&nbsp;Requests</a>";
  }

  if ( $roles[wrms][Admin] || $roles[wrms][Support] || $roles[wrms][Manage] ) {
    $tooltip = "A comprehensive search facility for reporting on work requests";
    echo "<br>\n &nbsp;<a href=$base_url/requestlist.php?qs=complex class=block title=\"$tooltip\" alt=\"$tooltip\">Request&nbsp;Search</a>";
  }
  if ( $roles[wrms][Admin] || $roles[wrms][Support] || $roles[wrms][Manage] ) {
    $tooltip = "Run this saved work request report";
    $query = "SELECT * FROM saved_queries WHERE user_no = '$session->user_no' ORDER BY query_name";
    $result = awm_pgexec( $dbconn, $query, "block-menu");
    if ( $result && pg_NumRows($result) > 0) {
      echo "<br><img class=blocksep src=\"images/menuBreak.gif\" width=\"130\" height=\"9\">";
      for ( $i=0; $i < pg_NumRows($result); $i++ ) {
        $thisquery = pg_Fetch_Object( $result, $i );
        echo "<br>\n &nbsp;<a href=\"$base_url/requestlist.php?style=plain&qry=" . urlencode($thisquery->query_name) . "\" class=block title=\"$tooltip\" alt=\"$tooltip\"><b>&raquo;</b>$thisquery->query_name</a>";
      }
    }
  }
  if ( $roles[wrms][Admin] || $roles[wrms][Support] || $roles[wrms][Manage] ) {
    echo "<br><img class=blocksep src=\"images/menuBreak.gif\" width=\"130\" height=\"9\">";
    $tooltip = "Review and update details about your organisation.";
    echo "<br>\n &nbsp;<a href=\"$base_url/form.php?form=organisation&org_code=$session->org_code\" class=block title=\"$tooltip\" alt=\"$tooltip\">My&nbsp;Organisation</a>";
    $tooltip = "List the WRMS users for your organisation.";
    echo "<br>\n &nbsp;<a href=\"$base_url/usrsearch.php?org_code=$session->org_code\" class=block title=\"$tooltip\" alt=\"$tooltip\">Our&nbsp;Users</a>";
    $tooltip = "Create a new WRMS user for your organisation.";
    echo "<br>\n &nbsp;<a href=\"$base_url/usr.php?org_code=$session->org_code\" class=block title=\"$tooltip\" alt=\"$tooltip\">New&nbsp;User</a>";
    $tooltip = "List the 'Systems' your organisation may create Work Requests for.";
    echo "<br>\n &nbsp;<a href=\"$base_url/form.php?form=syslist&org_code=$session->org_code\" class=block title=\"$tooltip\" alt=\"$tooltip\">Our&nbsp;Systems</a>";
  }

  if ( $roles[wrms][Admin] || $roles[wrms][Support] ) {
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