<?php
  block_open();

  block_title("&nbsp;");
  echo "<tr><td class=block>\n &nbsp;";
  $tooltip = "Maintain your name, phone and e-mail details, or change your password";
  echo "<a href=\"user.php?edit=1&user_no=$session->user_no\" class=block title=\"$tooltip\" alt=\"$tooltip\">Edit&nbsp;My&nbsp;Info</a>\n";

  $tooltip = "Au revoir!";
  echo  "<br>\n &nbsp;<a href=\"/?M=LO$hurl\" class=block title=\"$tooltip\" alt=\"$tooltip\">Log Off</a>";
  $tooltip = "Log me out and stop logging me in automatically";
  echo  "<br>\n &nbsp;<a href=\"/?M=LO&forget=1$hurl\" class=block title=\"$tooltip\" alt=\"$tooltip\">Forget Me</a>";

  if ( is_member_of('Request') ) {
    echo "<br><img class=blocksep src=\"/$images/menuBreak.gif\" width=\"130\" height=\"9\">";
    $tooltip = "Go to the WRMS home page";
    echo "<br>\n &nbsp;<a href=$base_url/index.php class=block title=\"$tooltip\" alt=\"$tooltip\">WRMS&nbsp;Home</a>";
    $tooltip = "Enter a new work request into the system.";
    echo "<br>\n &nbsp;<a href=$base_url/wr.php class=block>New&nbsp;Request</a>";
    $tooltip = "Search and list work requests";
    echo "<br>\n &nbsp;<a href=$base_url/requestlist.php class=block title=\"$tooltip\" alt=\"$tooltip\">List&nbsp;Requests</a>";

    $tooltip = "A comprehensive search facility for reporting on work requests";
    echo "<br>\n &nbsp;<a href=$base_url/requestlist.php?qs=complex class=block title=\"$tooltip\" alt=\"$tooltip\">Request&nbsp;Search</a>";

    if ( is_member_of('Admin', 'Support') || $rank_report_anyone ) {
      $tooltip = "A really comprehensive search facility for reporting on work requests (version 2)";
      echo "<br>\n &nbsp;<a href=\"$base_url/wrsearch.php\" class=\"block\" title=\"$tooltip\" alt=\"$tooltip\">Search&nbsp;V2</a>";

      $tooltip = "A ranked list of work requests, most important and urgent at the top";
      echo "<br>\n &nbsp;<a href=\"$base_url/requestrank.php?qs=\"complex\" class=\"block\" title=\"$tooltip\" alt=\"$tooltip\">Request&nbsp;Ranking</a>";
    }
  }

  if ( is_member_of('Admin', 'Support', 'Manage', 'Request') ) {
    $tooltip = "Run this saved search";
    $tooltip2 = "Edit this saved search";
    $qry = new PgQuery( "SELECT * FROM saved_queries WHERE user_no = '$session->user_no' ORDER BY query_name" );
    if ( $qry->Exec("block-menu") && $qry->rows > 0) {
      echo "<br><img class=blocksep src=\"/$images/menuBreak.gif\" width=\"130\" height=\"9\">";
      while ( $thisquery = $qry->Fetch() ) {
        echo "<br>\n &nbsp;<a href=\"$base_url/requestlist.php?style=plain&saved_query=" . urlencode($thisquery->query_name) . "\" class=block title=\"$tooltip\" alt=\"$tooltip\"><b>&raquo;</b>$thisquery->query_name</a>";
        if ( $thisquery->query_params != "" ) {
          echo "&nbsp;<a href=\"$base_url/wrsearch.php?saved_query=" . urlencode($thisquery->query_name) . "\" class=block title=\"$tooltip2\"><b>&laquo;e&raquo;</b></a>";
        }
      }
    }
  }

  echo "<br><img class=blocksep src=\"/$images/menuBreak.gif\" width=\"130\" height=\"9\">";
  echo "<table border=0 width=100%><form method=get action=\"/wr.php\" name=\"quickwr\" id=\"quickwr\"><tr><td align=right>";
  echo "<input type=text size=7 value=\"$request_id\" name=request_id></td></tr></form></table>";

  if ( is_member_of('Admin', 'Support', 'Manage') ) {
    echo "<img class=blocksep src=\"/$images/menuBreak.gif\" width=\"130\" height=\"9\">";
    $tooltip = "Review and update details about your organisation.";
    echo "<br>\n &nbsp;<a href=\"$base_url/org.php?org_code=$session->org_code\" class=block title=\"$tooltip\" alt=\"$tooltip\">My&nbsp;Organisation</a>";
    $tooltip = "List the WRMS users for your organisation.";
    echo "<br>\n &nbsp;<a href=\"$base_url/usrsearch.php?org_code=$session->org_code\" class=block title=\"$tooltip\" alt=\"$tooltip\">Our&nbsp;Users</a>";
    $tooltip = "Create a new WRMS user for your organisation.";
    echo "<br>\n &nbsp;<a href=\"$base_url/usr.php?org_code=$session->org_code\" class=block title=\"$tooltip\" alt=\"$tooltip\">New&nbsp;User</a>";
    $tooltip = "List the 'Systems' your organisation may create Work Requests for.";
    echo "<br>\n &nbsp;<a href=\"$base_url/form.php?form=syslist&org_code=$session->org_code\" class=block title=\"$tooltip\" alt=\"$tooltip\">Our&nbsp;Systems</a>";
  }

  if ( is_member_of('Admin', 'Support' ) ) {
    echo "<br><img class=blocksep src=\"/$images/menuBreak.gif\" width=\"130\" height=\"9\"><br>\n &nbsp;<a href=$base_url/timesheet.php class=block>Timesheet Entry</a>";
    echo "<br>\n &nbsp;<a href=$base_url/form.php?f=orglist class=block>All&nbsp;Organisations</a>";
    echo "<br>\n &nbsp;<a href=$base_url/form.php?f=syslist class=block>All&nbsp;Systems</a>";
    echo "<br>\n &nbsp;<a href=$base_url/form.php?f=timelist&user_no=$session->user_no&uncharged=1 class=block>My&nbsp;Uncharged&nbsp;Work</a>";
    echo "<br>\n &nbsp;<a href=$base_url/form.php?f=timelist&uncharged=1 class=block>All&nbsp;Work</a>";
    echo "<br>\n &nbsp;<a href=$base_url/form.php?f=simpletimelist class=block>Work by Person</a>";
    echo "<br>\n &nbsp;<a href=$base_url/form.php?f=timelist&uncharged=1&charge=1 class=block>Work&nbsp;To&nbsp;Charge</a>";
    $tooltip = "A report showing the activity in the WRMS.";
    echo "<br>\n &nbsp;<a href=$base_url/requestchange.php class=block>WRMS&nbsp;Activity</a>";
  }

  if ( is_member_of('Admin') ) {
    echo "<br><img class=blocksep src=\"/$images/menuBreak.gif\" width=\"130\" height=\"9\"><br>\n &nbsp;<a href=\"$base_url/lookups.php\" class=block>Lookup&nbsp;Codes</a>";
    echo "<br>\n &nbsp;<a href=\"$base_url/form.php?form=attachment_type\" class=block>Attachment&nbsp;Types</a>";
    echo "<br>\n &nbsp;<a href=\"$base_url/form.php?form=sessionlist\" class=block>Sessions</a>";
  }

  echo "</td></tr>\n";
  block_close();

  echo "<img src=\"/images/clear.gif\" width=\"155\" height=\"50\" hspace=\"0\" vspace=\"2\" border=\"0\">\n";

?>
