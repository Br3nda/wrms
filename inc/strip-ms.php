<?php
// Very useful function for stripping MS-isms out of the code
function fix_MS_isms( $instr ) {
  $instr = str_replace( chr(145), "'", $instr);
  $instr = str_replace( chr(146), "'", $instr);
  $instr = str_replace( chr(147), '"', $instr);
  $instr = str_replace( chr(148), '"', $instr);
  $instr = str_replace( chr(150), '&#8212;', $instr);
  $instr = str_replace( chr(169), '&copy;', $instr);
  $instr = str_replace( chr(175), '&reg;', $instr);
  return $instr ;
}
?>
