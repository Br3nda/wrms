<?php
  $because = "";

  if ( isset($new_system_code) && $new_system_code == "UNKNOWN" )
    $because .= "<h2>New Request Failed!</h2><h4>&quot;System&quot; must be selected</h4>\n";
  if ( isset($new_quote_amount) && isset($new_quote_brief) && ($new_quote_brief != "") && ($new_quote_amount != "") ) {
    if ( ! is_real(doubleval($new_quote_amount)) || doubleval($new_quote_amount) <= 0 )
      $because .= "<h2>New Quote Failed!</h2><h4>&quot;Amount&quot; must be numeric</h4>\n";
  }

  // Now attempt to catch support staff who log themselves as 'requester'
  $query = "SELECT * FROM request, work_system, usr, organisation ";
  $query .= "WHERE request.system_code = work_system.system_code ";
  $query .= "AND request.requester_id = usr.user_no ";
  $query .= "AND usr.org_code = organisation.org_code ";
  $rid = awm_pgexec( $dbconn, $query, "request-valid" );
  if ( !$rid ) $because .= "<h1>Database Error!</h1>";
  else if ( pg_NumRows($rid) == 0 )
    $because .= "<h2>Requester Error</h2><h4>That requester is not allowed to make
    requests for that system.  You should ensure that the person 'requesting' this is from
    an appropriate organisation (not Catalyst staff, if possible).</h4>\n";
?>