<?php
function html_format( $instr ) {
  $instr = ereg_replace("\n *([o-]) +", "<BR>&nbsp;&nbsp;\\1&nbsp;&nbsp;", $instr);
  $instr = ereg_replace("_([0-9a-zA-Z]+)_", "<B>\\1</B>", $instr);
  $instr = str_replace("\n\n", "<P>", $instr);
  $instr = str_replace("\n", "<BR>", $instr);
  return $instr;
}
?>
