<?php
  $debuggroups['req-valid'] = 1;
  $because = "";

  if ( isset($new_system_code) && $new_system_code == "UNKNOWN" )
    $because .= "<h2 class=error>New Request Failed!</h2><h4 class=error>&quot;System&quot; must be selected</h4>\n";

  if ( isset($new_quote_amount) && isset($new_quote_brief) && ($new_quote_brief != "") && ($new_quote_amount != "") ) {
    if ( ! is_real(doubleval($new_quote_amount)) || doubleval($new_quote_amount) <= 0 )
      $because .= "<h2 class=error>New Quote Failed!</h2><h4 class=error>&quot;Amount&quot; must be numeric</h4>\n";
  }

  // Now attempt to catch support staff who log themselves as 'requester'
  if ( $request_id > 0 ) {
    $qs = "SELECT count(*) FROM usr, org_system WHERE org_system.org_code = usr.org_code ";
    $qs .= "AND usr.user_no = ? AND org_system.system_code = ?";
    $query = new PgQuery( $qs, $new_user_no, $new_system_code );
    if ( $query->Exec('req-valid') && $query->rows == 0 ) {
      $because .= "<h2 class=error>Requester Error</h2><h4 class=error>That requester is not allowed to make
      requests for that system.  You should ensure that the person 'requesting' this is from
      an appropriate organisation (not Catalyst staff, if possible).<br /></h4>\n";
    }
  }
?>