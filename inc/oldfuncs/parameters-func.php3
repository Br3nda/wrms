<?php
function parse_parameters($str) {
  /* This bit of php3 will parse an argument line of the form:
         "parameter1=value1&parameter2=value2&parameter3=value3&...&parameterN=valueN"
     into an associative array so that the results can be used so:
         $x = $args->parameter1;
         $y = $args->parameter3;
     and so forth.
     The function should be called as $args = parse_parameters($argv[0]); )
  */
  $args = explode( "&", $str );
  while( list( $k, $v) = each( $args ) ) {
    list( $nk, $nv) = explode( "=", $v);
    $answers["$nk"] = $nv;
  }
  return $answers;
}

