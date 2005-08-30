<?php
function html_format_url($matches)
{
  // as usual: $matches[0] is the complete match
  // $matches[1] the match for the first subpattern
  // enclosed in '(...)' and so on
  $real_url = $matches[1];
  $display_url = $real_url;
  if ( strlen($display_url) > 80 ) {
    $display_url = substr( $display_url, 0, 45 ) . " ... " . substr( $display_url, strlen($display_url) - 30 );
  }
  return " <a href=\"$real_url\" title=\"$real_url\" target=\"_new\">$display_url</a> ";
}


function make_help_link($matches)
{
  // as usual: $matches[0] is the complete match
  // $matches[1] the match for the first subpattern
  // enclosed in '##...##' and so on
  // Use like: $s = preg_replace_callback("/##([^#]+)##", "make_help_link", $s);
//  $help_topic = preg_replace( '/^##(.+)##$/', '$1', $matches[1]);
  $help_topic = $matches[1];
  $display_url = $help_topic;
  if ( $GLOBALS['session']->AllowedTo("Admin") || $GLOBALS['session']->AllowedTo("Support") ) {
    if ( strlen($display_url) > 30 ) {
      $display_url = substr( $display_url, 0, 28 ) . "..." ;
    }
  }
  else {
    $display_url = "help";
  }
  return " <a class=\"help\" href=\"/help.php?h=$help_topic\" title=\"Show help on '$help_topic'\" target=\"_new\">[$display_url]</a> ";
}


function html_format( $instr ) {
global $colors;

  // Lines beginning with something bullet-like get made into bullet points
  $instr = preg_replace("/\n *([o+-]) +/", "<br>&nbsp;&nbsp;\$1&nbsp;&nbsp;", $instr);

  // Lines beginning like e-mail comment lines
  $instr = preg_replace("/\n( *[|>][^\n]*)/", "<br><span style=\"background: ".$colors["row1"].";\">\$1</span>", $instr);

  // A word like _word_ displays as bold
  $instr = preg_replace("/ _([^ ]+)_ /", " <b>\$1</b> ", $instr);

  // A URL surrounded like [http://my.url] gets converted to a link
  $instr = preg_replace_callback("#\[(https?://[^]]+)\]#", "html_format_url", $instr);

  // A URL like " http://my.url " also gets converted to a link
  $instr = preg_replace_callback("#(https?://[^[:space:]]+)#", "html_format_url", $instr);

  // A URL like " mailto:user@domain.name " also gets converted to a link
  $instr = preg_replace_callback("/(mailto:[^[:space:]]+@[^[:space:]]+)/", "html_format_url", $instr);

  // A phrase like " W/R #99999 " (and variants) gets converted to a link
  $instr = preg_replace("/(W\/?RM?S? ?#?([[:digit:]]{4,6}))([^[:digit:]]|$)/i",
                " <a href=\"".$GLOBALS['base_dns']."/wr.php?request_id=\$2\">\$1</a>\$3", $instr);

  // Two consecutive newlines is a new paragraph
  $instr = str_replace("\n\n", "</p><p>", $instr);

  // A single newline is a line break
  $instr = str_replace("\n", "<br>", $instr);

  // So we can read the HTML when we view source
  $instr = str_replace("<p>", "\n<p>", $instr);
  $instr = str_replace("<br>", "<br />\n", $instr);

  // Fancy stuff links to nodes in the help
  $instr = link_writeups($instr, "WU");
  return $instr;
}
?>