<?php
if ( isset($c->theme) && file_exists("../inc/themes/$c->theme.php") ) {
  include_once("themes/$c->theme.php");
}
else {
  include_once("themes/Default.php");
}

function send_headers() {
  global $colors, $fontsizes, $fonts, $stylesheet, $error_message, $warn_message, $client_messages;
  global $title, $style, $left_panel, $right_panel, $images, $tmnu, $settings, $session, $help_url;
  global $c, $session, $theme;

  $theme = new MyTheme();

  $now = time();
  // Header("Last-Modified: " . gmdate( "D, d M Y H:i:s T", $now) );
  $then = $now + 15;
  // Header("Expires: " . gmdate( "D, d M Y H:i:s T", $then) );
  // Header("Cache-Control: max-age=5, private");
  Header("Cache-Control: private");
  Header("Pragma: no-cache");

  $theme->HTMLHeader();
  $theme->PageHeader( $style );

  if ( (isset($client_messages) && is_array($client_messages) && count($client_messages) > 0 ) || count($c->messages) > 0 ) {
    echo "<div id=\"messages\"><ul class=\"messages\">\n";
    foreach( $client_messages AS $i => $msg ) {
      // ##HelpTextKey## gets converted to a "/help.php?h=HelpTextKey" link
      $msg = preg_replace_callback("/##([^#]+)##/", "make_help_link", $msg);
      echo "<li class=\"messages\">$msg</li>\n";
    }
    foreach( $c->messages AS $i => $msg ) {
      // ##HelpTextKey## gets converted to a "/help.php?h=HelpTextKey" link
      $msg = preg_replace_callback("/##([^#]+)##/", "make_help_link", $msg);
      echo "<li class=\"messages\">$msg</li>\n";
    }
    echo "</ul></div>\n";
  }

  if ( isset($tmnu) && is_object($tmnu) ) {
    $tmnu->LinkActiveSubMenus();
    $theme->TopMenuBar($tmnu);
  }
}
send_headers();

?>