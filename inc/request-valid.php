<?php
  $because = "";

  if ( isset($new_system_code) && $new_system_code = "UNKNOWN" )
    $because .= "<h2>New Request Failed!</h2><h4>&quot;System&quot; must be selected</h4>\n";
  if ( isset($new_quote_amount) && isset($new_quote_brief) && ($new_quote_brief != "") && ($new_quote_amount != "") ) {
    if ( ! is_real(doubleval($new_quote_amount)) || doubleval($new_quote_amount) <= 0 )
      $because .= "<h2>New Quote Failed!</h2><h4>&quot;Amount&quot; must be numeric</h4>\n";
  }

?>
