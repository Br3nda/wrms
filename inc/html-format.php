<?php
function html_format( $instr ) {
global $colors;

  // Lines beginning with something bullet-like get made into bullet points
  $instr = ereg_replace("\n *([o+-]) +", "<br>&nbsp;&nbsp;\\1&nbsp;&nbsp;", $instr);

  // Lines beginning like e-mail comment lines
  $instr = ereg_replace("\n( *[|>][^\n]*)", "<br><span style=\"background: ".$colors["row1"].";\">\\1</span>", $instr);

  // A word like _word_ displays as bold
  $instr = ereg_replace(" _([^ ]+)_ ", " <b>\\1</b> ", $instr);

  // A URL surrounded like [http://my.url] gets converted to a link
  $instr = ereg_replace(" \[(https?://[^]]+)\] ", " <a href=\"\\1\" target=_new>\\1</a> ", $instr);

  // A URL like " http://my.url " also gets converted to a link
  $instr = ereg_replace(" (https?://[^ ]+) ", " <a href=\"\\1\" target=_new>\\1</a> ", $instr);

  // A URL like " mailto:user@domain.name " also gets converted to a link
  $instr = ereg_replace(" mailto:([^ ]+@[^ ]+) ", " <a href=\"mailto:\\1\">\\1</a> ", $instr);

  // A phrase like " W/R #99999 " (and variants) gets converted to a link
  $instr = preg_replace("/(W\/?R ?#?([[:digit:]]{4,6}))([^[:digit:]]|$)/i",
                " <a href=\"".$GLOBALS['base_dns']."/wr.php?request_id=\$2\">\$1</a>\$3", $instr);

  // Two consecutive newlines is a new paragraph
  $instr = str_replace("\n\n", "</p><p>", $instr);

  // A single newline is a line break
  $instr = str_replace("\n", "<br>", $instr);

  // So we can read the HTML when we view source
  $instr = str_replace("<p>", "\n<p>", $instr);
  $instr = str_replace("<br>", "<br />\n", $instr);

  // Fancy stuff links to nodes in the help
  $instr = link_writeups($instr);
  return $instr;
}
?>