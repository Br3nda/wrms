<?php
  block_open();

  block_title(' &nbsp;<a href="/" class="blockhead" title="Go to the WRMS Home Page">HOME</a>');

function menu_url_line($url,$tooltip,$prompt,$head="") {
  printf( ' &nbsp;<a href="%s" class="block%s" title="%s" alt="%s">%s</a><br />',
                     $url, $head, $tooltip, $tooltip, $prompt );
}

function menu_break_line() {
  printf('<img class="blocksep" src="/%s/menuBreak.gif" width="130" height="9"><br />'."\n", $GLOBALS['images']);
}

function show_sidebar_menu() {
  global $session, $hurl, $lsid, $help_url;

  menu_url_line("/wr.php", "Enter a new work request into the system.", "New Request", "head" );
  $tooltip = "A comprehensive search facility for reporting on work requests.";
  menu_url_line("/wrsearch.php", $tooltip, "Search Requests", "head" );
  if ( is_member_of('Admin', 'Support', 'Contractor' ) ) {
    menu_url_line("/timesheet.php", "", "Timesheet Entry", "head" );
  }
  if ( isset($lsid) )
    menu_url_line("/?logout=1&forget=1$hurl", "Log me out and stop logging me in automatically", "Forget Me", "head" );
  else
    menu_url_line("/?logout=1$hurl", "Au revoir!", "Log Off", "head" );

  menu_break_line();
  echo '<form method="get" action="/wr.php" name="quickwr" id="quickwr" style="display:inline">';
  printf('&nbsp;<b>W/R:</b><input type="text" size="7" title="%s" value="%d" name="request_id">',
            'Enter a W/R number and press [Enter] to go to it directly.', $GLOBALS['request_id'] );
  echo "</form><br />";

  $tooltip = "Run this saved search";
  $tooltip2 = "Edit this saved search";
  $qry = new PgQuery( "SELECT * FROM saved_queries WHERE user_no = '$session->user_no' ORDER BY query_name" );
  if ( $qry->Exec("block-menu") && $qry->rows > 0) {
    menu_break_line();
    while ( $thisquery = $qry->Fetch() ) {
      echo "&nbsp;<a href=\"/wrsearch.php?style=plain&saved_query=" . urlencode($thisquery->query_name) . "\" class=\"block\" title=\"$tooltip\" alt=\"$tooltip\"><b>&raquo;</b>$thisquery->query_name</a>";
      if ( $thisquery->query_params != "" ) {
        echo "&nbsp;<a href=\"/wrsearch.php?saved_query=" . urlencode($thisquery->query_name) . "\" class=\"block\" title=\"$tooltip2\"><b>&laquo;e&raquo;</b></a>";
      }
      echo "<br />\n";
    }
  }

  menu_break_line();
  menu_url_line($help_url, "Help on this screen", "Help" );
  $tooltip = "Maintain your name, phone and e-mail details, or change your password";
  menu_url_line("user.php?edit=1&user_no=$session->user_no", $tooltip, "Edit My Info" );


  if ( is_member_of('Admin', 'Support', 'OrgMgr') ) {
//    menu_break_line();
    $tooltip = "Review and update details about your organisation.";
    menu_url_line("/org.php?org_code=$session->org_code", $tooltip, "My Organisation" );
    $tooltip = "List the WRMS users for your organisation.";
    menu_url_line("/usrsearch.php?org_code=$session->org_code", $tooltip, "Our Users" );
    $tooltip = "Create a new WRMS user for your organisation.";
    menu_url_line("/user.php?org_code=$session->org_code", $tooltip, "New User" );
    $tooltip = "List the 'Systems' your organisation may create Work Requests for.";
    menu_url_line("/form.php?form=syslist&org_code=$session->org_code", $tooltip, "Our Systems" );
    if ( is_member_of('Admin') ) {
      menu_url_line("/lookups.php", "", "Lookup Codes" );
      menu_url_line("/form.php?form=attachment_type", "", "Attachment Types" );
      menu_url_line("/form.php?form=sessionlist", "", "Sessions" );
    }
  }

  if ( is_member_of('Admin', 'Support' ) ) {
    menu_break_line();
    menu_url_line("/form.php?f=orglist", "", "All Organisations" );
    menu_url_line("/form.php?f=syslist", "", "All Systems" );
    menu_url_line("/form.php?user_no=$session->user_no&form=timelist&uncharged=1", "", "My Uncharged Work" );
    menu_url_line("/form.php?f=timelist&uncharged=1", "", "All Work" );
    menu_url_line("/form.php?f=simpletimelist", "", "Work by Person" );
    menu_url_line("/form.php?f=timelist&uncharged=1&charge=1", "", "Work To Charge" );
    $tooltip = "A report showing the activity in the WRMS.";
    menu_url_line("/requestchange.php", $tooltip, "WRMS Activity" );
  }

  if ( is_member_of('Admin', 'Support') || $GLOBALS['rank_report_anyone'] ) {
    $tooltip = "A ranked list of work requests, most important and urgent at the top";
    menu_url_line("/requestrank.php?qs=complex", $tooltip, "Request Ranking" );
  }

  block_close();
}

show_sidebar_menu();

?>