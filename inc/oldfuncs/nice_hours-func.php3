<?php
  function nice_hours($str) {
    $hours = 0;
    if ( eregi( "([0-9-]+) weeks?", $str, $num ) ) $hours += 168 * $num[1];
    if ( eregi( "([0-9-]+) days?", $str, $num ) ) $hours += 24 * $num[1];
    if ( eregi( "([0-9-]+) hours?", $str, $num ) ) $hours += $num[1];
    $mins = 0;
    if ( eregi( "([0-9-]+) mins?", $str, $num ) ) $mins += $num[1];
    $secs = 0;
    if ( eregi( "([0-9.-]+) secs?", $str, $num ) ) $secs += $num[1];

    $answer = sprintf( "%d:%02d:%02d", $hours, $mins, $secs );
    return $answer;
  }
?>
