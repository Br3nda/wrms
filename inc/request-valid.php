<?php
  include("awm/funcs/testdate.php3");
  include("awm/funcs/fix-phones.php3");

  $because = "";
  if ( ! $logged_on )
    $because .= "You must log on with a valid password and maintainer ID\n";
  if ( "$fsystem_code" == "" ) {
    if ( "$session->system_code" == "" )
      $because .= "You must select a valid system_code\n";
    else
      $fsystem_code == $session->system_code;
  }


  if ( !strcmp( $fchangetype, "") ) $because .= "You must select the type of change you are entering\n";
  if ( !strcmp( $fchangetype, "new") ) {
    /* confirm we have the mandatory fields */
    if ( !strcmp( $frequesttype, "") ) $because .= "You must select the type of request you are adding\n";
    if ( !strcmp( $fpfamily, "") ) $because .= "You must enter the family name for the request to be added\n";
    if ( !strcmp( $fpfirst, "") ) $because .= "You must enter the first name for the request to be added\n";
    if ( !strcmp( $frequesttype, "") ) $because .= "You must select the type of request you are adding\n";
    if ( !strcmp( $fjoined, "") ) $because .= "You must enter a join date for new requests\n";
    if ( !testdate( $fjoined) ) $because .= "The join date must be in the form 'dd/mm/yyyy' \n";
    $frequestno = "";

    /* need to try and limit the numbers created from multiple submits &c ! */
    $query = "SELECT * FROM request WHERE pfamily = '$fpfamily' AND pfirst = '$fpfirst' ";
    $query .= " AND system_code = '$fsystem_code' AND last_change >= 'yesterday'";
  }
  else {
    if ( $frequestno <> "" && is_integer($frequestno) ) $because .= "You must enter a valid requestship number for changes\n";
    $query = "SELECT * FROM request WHERE requestno = $frequestno";
  }
  $rid = pg_Exec( $wrms_db,  $query );
  if ( !$rid ) {
    $because .= "Error with query\n$query\n\n";
  }
  else {
    if ( pg_NumRows($rid) > 0 ) {
      $c_request = pg_Fetch_Object( $rid, 0);
      if ( $fchangetype == "new" ) {
        $frequestno = $c_request->requestno;
/*        $because .= "Request '$c_request->requestno' ($c_request->pfirst $c_request->pfamily) already exists for that system_code - cannot add new request with same name\n"; */
      }
    }
  }
  $updating = isset( $c_request ) && strcmp( "$fchangetype","new" ) ;

  // Validate that they are only maintaining a request for a system_code they
  if ( $roles[wrms][Admin] ) {
    // OK, they can do anything :-)
  }
  else {
    if ( $system_code_roles["$fsystem_code"] == "V" )
      $because = "You may only view records for that system_code";
    else if ( $system_code_roles["$fsystem_code"] == "H" || $system_code_roles["$fsystem_code"] == "V" ) {
      // That's OK - this is their home system_code, or maintenance is enabled
      if ( $updating ) {
        // We still have to check that if we're maintaining an existing request
        // that they are a request of one of these system_codes.
        $query = "SELECT * FROM request WHERE requestno='$frequestno'";
        $rid = pg_Exec( $wrms_db,  $query );
        if ( pg_NumRows($rid) == 1 ) {
          // Otherwise we just have to assume it's OK!  This should be OK because
          // that will only be requests added in the last week at PCNZ National.
          $request = pg_Fetch_Object( $rid, 0 );
          if ( $system_code_roles[$request->system_code] <> "M" && $system_code_roles[$request->system_code] <> "H" )
            $because .= "You may only maintain requests from your system_code\n";
        }
      }
    }
    else
      $because .= "You may only maintain requests from your system_code\n";
  }

  $query = "SELECT * FROM lookup_code WHERE source_table = 'request' ";
  $query .= " AND source_field = 'system_code' AND lookup_code = '$fsystem_code' ";
  $rid = pg_Exec( $wrms_db,  $query );
  if ( !$rid ) {
    echo "<P>Error with query</P><P>$query</P>";
    $msg = "<!doctype html public \"-//w3c//dtd html 4.0 transitional//en\"><HTML><BODY>";
    $msg .= "<P>Error with query</P><P>$query</P>";
    $msg .= "<P>Message: " . pg_errormessage($wrms_db) . "</P>";
    $msg .= "</HTML>";
    mail( "andrew@cat-it.co.nz", "Error with query", $msg, "Content-Type: text/html; charset=us-ascii" );
    echo "<H3>E-Mail has been sent to the system maintainer</H3>";
    exit;
  }
  else {
    if ( pg_NumRows($rid) == 0 )
      $because .= "You must select a valid system_code\n";
    else
      $default_std = pg_result( $rid, 0, 'lookup_misc');
  }

  if ( !testdate( $fpbirth) ) $because .= "The request's birth date must be in the form 'dd/mm/yyyy' \n";
  if ( !testdate( $fsbirth) ) $because .= "The partner's birth date must be in the form 'dd/mm/yyyy' \n";
  if ( !testdate( $fc1birth) ) $because .= "The first child's birth date must be in the form 'dd/mm/yyyy' \n";
  if ( !testdate( $fc2birth) ) $because .= "The second child's birth date must be in the form 'dd/mm/yyyy' \n";
  if ( !testdate( $fc3birth) ) $because .= "The third child's birth date must be in the form 'dd/mm/yyyy' \n";
  if ( !testdate( $fc4birth) ) $because .= "The fourth child's birth date must be in the form 'dd/mm/yyyy' \n";
  if ( !testdate( $fc5birth) ) $because .= "The fifth child's birth date must be in the form 'dd/mm/yyyy' \n";
  if ( !testdate( $fc6birth) ) $because .= "The sixth child's birth date must be in the form 'dd/mm/yyyy' \n";
  if ( !testdate( $fc7birth) ) $because .= "The seventh child's birth date must be in the form 'dd/mm/yyyy' \n";
  if ( !testdate( $fc8birth) ) $because .= "The eighth child's birth date must be in the form 'dd/mm/yyyy' \n";
  if ( !testdate( $ftbirthdue) ) $because .= "The mother's due date must be in the form 'dd/mm/yyyy' \n";

  if ( "$because" <> "" ) {
    $because = "<H2>Errors with submission:</H2>\n" . nl2br( $because ) . "<HR>\n";
    $because .= "<P><B>Changes have not been processed - please correct because and re-submit</B></P>\n";
  }

?>
