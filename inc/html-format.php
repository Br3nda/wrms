<?php
function html_format( $instr ) {
  $instr = ereg_replace("\n *([o+-]) +", "<br>&nbsp;&nbsp;\\1&nbsp;&nbsp;", $instr);
  $instr = ereg_replace("_([0-9a-zA-Z]+)_", "<b>\\1</b>", $instr);
  $instr = str_replace("\n\n", "<p>", $instr);
  $instr = str_replace("\n", "<br>", $instr);
  $instr = str_replace("<p>", "\n<p>", $instr);
  $instr = str_replace("<br>", "<br>\n", $instr);
  $instr = link_writeups($instr);
  return $instr;
}
?>
