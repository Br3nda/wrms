<?php
global $PHP_SELF, $theme;

$theme->BlockOpen();

if ($qams_enabled && strstr($PHP_SELF, "qams")) {
  $theme->BlockTitle(' &nbsp;<a href="/qams.php" class="blockhead" title="Go to the QAMS Home Page">HOME</a>');
}
else {
  $theme->BlockTitle(' &nbsp;<a href="/" class="blockhead" title="Go to the WRMS Home Page">HOME</a>');
}

function menu_url_line($url,$tooltip,$prompt,$head="") {
  printf( ' &nbsp;<a href="%s" class="block%s" title="%s" alt="%s">%s</a><br />',
                     $url, $head, $tooltip, $tooltip, $prompt );
}

function menu_break_line() {
  global $theme;
  printf('<img class="blocksep" src="/%s/menuBreak.gif" width="130" height="9"><br />'."\n", $theme->images);
}

function show_sidebar_menu() {
  global $PHP_SELF, $session, $c, $theme, $hurl, $lsid, $help_url, $qams_enabled;

  if ($qams_enabled && strstr($PHP_SELF, "qams")) {
    menu_break_line();
    menu_url_line("/", "Go to the WRMS Work Request Management System", "WRMS", "head" );
    menu_break_line();
    // QAMS MENU ITEMS..
    menu_url_line("/qams-project.php?edit=1", "Create a new project", "New Project", "head");
    // Not yet implemented..
    //menu_url_line("/qams-project-search.php", "Search for projects", "Search Projects", "head");
    if ( isset($lsid) )
      menu_url_line("/?logout=1&forget=1$hurl", "Log me out and stop logging me in automatically", "Forget Me", "head" );
    else
      menu_url_line("/?logout=1$hurl", "Au revoir!", "Log Off", "head" );

    menu_break_line();
    echo '<form method="get" action="/qams-project.php" name="quickwr" id="quickwr" style="display:inline">';
    printf('&nbsp;<b>PROJ:</b><input type="text" size="7" title="%s" value="%d" name="request_id">',
              'Enter a Project Number and press [Enter] to go to it directly.', $GLOBALS['request_id'] );
    echo "</form><br />";
    menu_break_line();

    menu_url_line("/qams.php?filter=my", "Show projects I am involved in", "My Projects");
    menu_url_line("/qams.php?filter=recent", "Show the most recent projects", "Recent Projects");

    menu_break_line();

    menu_url_line("/qams-refdoc-index.php", "Quality Assurance Documents Index", "Documents");
  }
  else {
    // WRMS MENU ITEMS..
    menu_url_line("/wr.php", "Enter a new work request into the system.", "New Request", "head" );
    $tooltip = "A comprehensive search facility for reporting on work requests.";
    menu_url_line("/wrsearch.php", $tooltip, "Search Requests", "head" );
    if ( is_member_of('Admin', 'Support', 'Contractor' ) ) {
      menu_url_line("/timesheet.php", "", "Timesheet Entry", "head" );
    }
    if ($qams_enabled ) {
      menu_url_line("/qams.php", "Go to the QAMS Quality Assurance Management System", "Quality System", "head" );
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
      menu_url_line("/new_organisation.php", "Add a new organisation, with a general system and primary user", "New Organisation" );
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
  }
  if ( is_member_of('Admin', 'Support' ) ) {
    menu_url_line("/statuspie.php", 'A pie chart of request statuses for a period / system / organisation', "Status Pie" );
  }
  $theme->BlockClose();
}

show_sidebar_menu();

?>