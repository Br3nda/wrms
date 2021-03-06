<?php

$dbconn = pg_Connect("dbname=flexwrms host=dewey.db user=general");

$admin_email = "richard /at/ flexible.co.nz";
$basefont = "verdana,sans-serif";
$system_name = "Flexible Learning Network WRMS";
$sysabbr = "flexible";

// Only admin/support can see the ranking report.
$rank_report_anyone = 0;

$qams_enabled = false;
$base_dns = "http://$HTTP_HOST";
$base_url = "";
$external_base_url = $base_dns;
$base_dir = $DOCUMENT_ROOT;
$module = "base";

// The directory where attachments are stored.
// This should be created with mode 1777 as a 'temp' directory
$attachment_dir = "/home/wrms/attachments";

// The subdirectory containing the images for this installation
$images = "fleximg";

// The stylesheet to use
$stylesheet = "/fleximg/flexwrms.css";


// Debugging options
$debuglevel = 10;
$debuggroups['Session'] = 1;
$debuggroups['Login'] = 1;
$debuggroups['querystring'] = 1;
$debuggroups['Request'] = 1;
$debuggroups['WorkSystem'] = 1;

$colors = array(
  "bg1" => "#ffffff", // primary background
  "fg1" =>  "#000000", // text on primary background
  "link1" =>  "#880000", // Links on row0/row1
  "bg2" =>  "#b00000", // secondary background (behind menus)
  "fg2" =>  "#ffffff", // text on links
  "bg3" =>  "#404040", // tertiary background
  "fg3" =>  "#ffffff", // tertiary foreground
  "hv1" =>  "#660000", // text on hover
  "hv2" =>  "#f8f400", // other text on hover
  "row0" =>  "#ffffff", // dark rows in listings
  "row1" =>  "#f0f0f0", // light rows in listings
  "link2" =>  "#333333", // Links on row0/row1
  "bghelp" => "#ffffff", // help background
  "fghelp" =>  "#000000", // text on help background
  "mand" =>  "#c8c8c8", // Mandatory forms

// Parts of a text block (default colors - some blocks might code around this
    "blockfront" => "black",
    "blockback" => "white",
    "blockbg2" => "white",
    "blocktitle" => "white",
    "blocksides" => "#ffffff",
    "blockextra" => "#660000"
);

$fonts = array( "tahoma",   // primary font
    "verdana",  // secondary font
    "help"  => "times",   // help text
    "quote" => "times new roman, times, serif", // quotes in messages
    "narrow"  => "arial narrow, helvetica narrow, times new roman, times", // quotes in messages
    "fixed" => "courier, fixed",  // monospace font
    "block" => "tahoma");   // block font


// Set the bebug variable initially to '0'. This variable is made available
// to all local routines for verbose printing.
if ( !isset($debuglevel) ) $debuglevel = 2;

//-----------------------------------------
// Function to start a block in the sidebar
//-----------------------------------------
function block_open(  $bgcolor="", $border_color="") {
  echo '<div class="block">';
}

//-----------------------------------------
// Function to title a block of options / menus / whatever
//-----------------------------------------
function block_title( $title="&nbsp;", $bgcolor="", $border_color="") {
  echo '<div class="blockhead">'.$title.'</div>';
}

//-----------------------------------------
// Function to finish a block of options / menus / whatever
//-----------------------------------------
function block_close() {
  echo '</div>';
}


//-----------------------------------------
// Function to do any styles, that are local to this installation
//-----------------------------------------
function local_inline_styles() {

  echo <<<EOSTYLE
.submit {
  background-image: url(/fleximg/bar_blue_tile.gif);
}
.submit:hover {
  color: #f8f400;
  border: thin inset;
}
EOSTYLE;

}

require_once("organisation-selectors-sql.php");

//-----------------------------------------
// Function to do the page header, that is local to this installation
//-----------------------------------------
function local_page_header() {
  global $system_name, $sysabbr, $session, $images, $tmnu;

  echo '<div id="topbar"><a href="/"><img src="'.$images.'/logo_main.gif" width="383" height="77" border="0"></a></div>'."\n";
  if ( $session->logged_in /* && !preg_match( "/(requestlist|wrsearch)\.php/i", $GLOBALS['REQUEST_URI'] ) */ ) {
    echo '<div id="searchbar">';
    echo '<form action="/wrsearch.php" method="post" name="search">';

    echo '<span class="prompt" style="vertical-align: 0%;">Find:</span>';
    echo '<span class="entry"><input class="search_for" type="text" name="search_for" value="'.$GLOBALS['search_for'].'"/></span>';

    $systems = new PgQuery(SqlSelectSystems($GLOBALS['org_code']));
    $system_list = $systems->BuildOptionList($GLOBALS['system_id'],'Config::LocPgHdr');
    echo '<span class="prompt" style="vertical-align: 0%;">Systems:</span>';
    echo '<span class="entry"><select name="system_id" class="search_for"><option value="">-- select --</option>'.$system_list;
    echo '</select></span>';
    echo '<span class="entry""><input type="submit" alt="go" class="fsubmit" value="Search" /></span>';
    echo '</form>';
    echo '</div>'."\n";
  }

  echo '<div id="top_menu">';
  if ( $session->logged_in ) {
    echo '<span style="float:right; margin-right:3px; margin-top:3px;">';
    echo $session->fullname;
    echo '</span>';
  }
  if ( isset($tmnu) && is_object($tmnu) && $tmnu->Size() > 0 ) {
    echo $tmnu->Render();
  }
  echo '</div>'."\n";

}

//-----------------------------------------
// Function to do the bottom menu bar, that is local to this installation
//-----------------------------------------
function local_menu_bar(&$tmnu) {
}

//-----------------------------------------
// Function to do the page footer, that is local to this installation
//-----------------------------------------
function local_page_footer() {
  global $session;
  echo '<div id="page_footer">';
  echo '</div>';
}

//-----------------------------------------
// Function to display stuff when a person is not logged in
//-----------------------------------------
function local_index_not_logged_in() {
global $admin_email, $system_name;

  echo <<<INDEXNOTLOGGEDIN
<blockquote>
<p><strong>
Welcome to $system_name. For more information
on Flexible Learning Network, please visit our website at
<a href="http://www.flexible.co.nz">www.flexible.co.nz</a>.
</strong></p>
<p> <br />Please e-mail <a href="mailto:$admin_email">$admin_email</a> if you require further information.</p>
</blockquote>

INDEXNOTLOGGEDIN;
}


?>
