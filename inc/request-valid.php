<?php
  $because = "";

  if ( isset($new_quote_amount) && isset($new_quote_brief) && ($new_quote_brief != "") && ($new_quote_amount != "") ) {
    if ( ! is_real(doubleval($new_quote_amount)) || doubleval($new_quote_amount) <= 0 )
      $because .= "<h2>New Quote Failed!</h2><p>&quot;Amount&quot; must be numeric</p>\n";
  }

?>
