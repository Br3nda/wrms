<?php
function append_if_not( $varname, $cmpwith, $updating ) {
  global $fldlist;
  global $values;
  global $default_std;

  if ( strcmp( $GLOBALS["$varname"], $cmpwith) ) {
    $GLOBALS["$varname"] = str_replace( "\n", ", ", $GLOBALS["$varname"] );
    $GLOBALS["$varname"] = str_replace( "\r", "", $GLOBALS["$varname"] );
    $value = $GLOBALS["$varname"];
    if ( substr("$varname", 0, 3) == "fph" ) {
      /* Validate phone numbers */
      $value = fix_phones( $value, "$default_std" );
    }
    if ( is_array( $GLOBALS["$varname"] ) ) $value = implode( ",", $GLOBALS["$varname"]);
    if ( !strcmp( $value, "???") ) $value = "";
    if ( strcmp( $fldlist, "") ) {
      $fldlist .= ", ";
      $values .= ", ";
    }
    $fldlist .= substr( "$varname", 1);
    $value = str_replace( "'", "''", $value );
    if ( $updating )
      $fldlist .= " = '$value'";
    else
      $values .= "'$value'";
  }
}

  $because .= "<H2>Database Change Submitted</H2><p>";
  $now = date( "h:m:s a" );
  if ( $updating )
    $because .= "Updating ";
  else
    $because .= "Adding ";
  $because .= "Request: $frequestno, $fpfirst $fpfamily updated at $now</P>";

  if ( $updating ) {
    $was = error_reporting(1);
    // We don't care if this fails or not.
    $rid = pg_Exec( $wrms_db,  "INSERT INTO pre_changes SELECT * FROM request WHERE requestno=$frequestno AND last_sent > '-infinity'" );
    error_reporting($was);

    $query = "UPDATE request SET ";
  }
  else
    $query = "INSERT INTO request (";

  $fldlist = "";
  $values = "";
//  if ( $updating ) {
//    $fldlist = "requestno";
//    $values = "'$frequestno'";
//  }
    $flast_change = "now";
    $fperson_organisation = "I";
    append_if_not( "flast_change", "", $updating);
    append_if_not( "fusername", "", $updating);
    append_if_not( "fperson_organisation", "", $updating);
    append_if_not( "fchangetype", "", $updating);
    append_if_not( "frequesttype", "", $updating);
    append_if_not( "fcontacttype", "", $updating);
    append_if_not( "fprtype", "", $updating);
    append_if_not( "fsystem_code", "", $updating);
    append_if_not( "frequestsource", "", $updating);
    append_if_not( "fjoined", "", $updating);
    append_if_not( "fsubs_last_paid", "", $updating);

    append_if_not( "fptitle", "", $updating);
    append_if_not( "fpfamily", "", $updating);
    append_if_not( "fpfirst", "", $updating);
    if ( !strcmp("$fpprefers", "" ) ) $fpprefers = $fpfirst;
    if ( "$fpprefers" == ""  && "$fchangetype" == "new" ) $fpprefers = $fpfirst;
    append_if_not( "fpprefers", "", $updating);
    append_if_not( "fpmiddle", "", $updating);
    append_if_not( "fpbirth", "", $updating);
    append_if_not( "fpindustry", "", $updating);
    append_if_not( "fppostitle", "", $updating);
    append_if_not( "fpethnicity", "", $updating); 
    append_if_not( "fpmarital", "", $updating);

    append_if_not( "fasalutation", "", $updating);
    append_if_not( "falabelline", "", $updating);
    append_if_not( "faddress1", "", $updating);
    append_if_not( "faddress2", "", $updating);
    append_if_not( "fasuburb", "", $updating);
    append_if_not( "fapostcode", "", $updating);
    append_if_not( "facity", "", $updating);
    append_if_not( "facountry", "New Zealand", $updating);
    append_if_not( "fadelivery", "", $updating);

    append_if_not( "fa2ddress1", "", $updating);
    append_if_not( "fa2ddress2", "", $updating);
    append_if_not( "fa2suburb", "", $updating);
    append_if_not( "fa2city", "", $updating);
    append_if_not( "fa2country", "New Zealand", $updating);

    append_if_not( "fphhome", "", $updating);
    append_if_not( "fphwork", "", $updating);
    append_if_not( "fphcell", "", $updating);
    append_if_not( "fphalt", "", $updating);
    append_if_not( "fphfax", "", $updating);
    append_if_not( "fpemail", "", $updating);

    append_if_not( "fstitle", "", $updating);
    append_if_not( "fsfamily", "", $updating);
    append_if_not( "fsfirst", "", $updating);
    if ( "$fsprefers" == ""  && "$fchangetype" == "new" ) $fsprefers = $fsfirst;
    append_if_not( "fsprefers", "", $updating);
    append_if_not( "fsmiddle", "", $updating);
    append_if_not( "fsbirth", "", $updating);
    append_if_not( "fsindustry", "", $updating);
    append_if_not( "fspostitle", "", $updating);
/*    append_if_not( "fsethnicity", "", $updating); */

    append_if_not( "fcommittee", "", $updating);
    append_if_not( "fnomailout", "", $updating);
    append_if_not( "fnotes", "", $updating);
    append_if_not( "fto_dbadmin", "", $updating);

    append_if_not( "fc1prefers", "", $updating);
    append_if_not( "fc1family", "", $updating);
    append_if_not( "fc1birth", "", $updating);
    append_if_not( "fc2prefers", "", $updating);
    append_if_not( "fc2family", "", $updating);
    append_if_not( "fc2birth", "", $updating);
    append_if_not( "fc3prefers", "", $updating);
    append_if_not( "fc3family", "", $updating);
    append_if_not( "fc3birth", "", $updating);
    append_if_not( "fc4prefers", "", $updating);
    append_if_not( "fc4family", "", $updating);
    append_if_not( "fc4birth", "", $updating);
    append_if_not( "fc5prefers", "", $updating);
    append_if_not( "fc5family", "", $updating);
    append_if_not( "fc5birth", "", $updating);
    append_if_not( "fc6prefers", "", $updating);
    append_if_not( "fc6family", "", $updating);
    append_if_not( "fc6birth", "", $updating);
    append_if_not( "fc7prefers", "", $updating);
    append_if_not( "fc7family", "", $updating);
    append_if_not( "fc7birth", "", $updating);
    append_if_not( "fc8prefers", "", $updating);
    append_if_not( "fc8family", "", $updating);
    append_if_not( "fc8birth", "", $updating);
    append_if_not( "fon_antenatal", "", $updating);
    append_if_not( "ftbirthdue", "", $updating);

  if ( $updating )
    $query .= $fldlist . " WHERE requestno = $frequestno";
  else
    $query .= $fldlist . ") VALUES( $values  )";

  pg_Exec( $wrms_db, "BEGIN" );
  $rid = pg_Exec( $wrms_db,  $query );
  if ( !$rid ) {
    $because .= "<P>Error with query</P><P>$query</P>";
    $msg = "<!doctype html public \"-//w3c//dtd html 4.0 transitional//en\"><HTML><BODY>";
    $msg .= "<P>Error with query</P><P>$query</P>";
    $msg .= "<P>Message: " . pg_errormessage($wrms_db) . "</P>";
    $msg .= "</HTML>";
    mail( "andrew@cat-it.co.nz", "Error with query", $msg, "Content-Type: text/html; charset=us-ascii" );
    pg_Exec( $wrms_db, "ROLLBACK" );
    $because .= "<H3>E-Mail has been sent to the system maintainer</H3>";
    exit;
  }
  else {
    $because .= "<H3>Information Saved</H3>";

    if ( "$fchangetype" == "new" ) {
      $rid = pg_Exec( $wrms_db,  "SELECT last_value FROM request_requestno_seq" );
      $frequestno = pg_result($rid, 0, "last_value");
    }
    pg_Exec( $wrms_db, "COMMIT" );

    if ( !strcmp( $fon_antenatal, "current") ) {
      $query = "INSERT INTO request_action ( requestno, action_date, action_type )";
      $query .= " VALUES( '$frequestno', '$ftbirthdue', 'DUE') ";

      $rid = pg_Exec( $wrms_db,  $query );
      if ( !$rid ) {
        $because .= "<P>Error with query</P><P>$query</P>";
        $msg = "<!doctype html public \"-//w3c//dtd html 4.0 transitional//en\"><HTML><BODY>";
        $msg .= "<P>Error with query</P><P>$query</P>";
        $msg .= "<P>Message: " . pg_errormessage($wrms_db) . "</P>";
        $msg .= "</HTML>";
        mail( "andrew@cat-it.co.nz", "Error with query", $msg, "Content-Type: text/html; charset=us-ascii" );
        $because .= "<H3>E-Mail has been sent to the system maintainer</H3>";
        exit;
      }
    }
  }


$msg  = "System: $fsystem_code\n";
$msg .= "UserName: $fusername\n";
$msg .= "UserPassword: *******\n";
$msg .= "ChangeType: $fchangetype\n";
$msg .= "RequestNo: $frequestno\n";
$msg .= "ContactType: $fcontacttype\n";
$msg .= "RequestType: $frequesttype\n";
$msg .= "RequestSource: $frequestsource\n";
$msg .= "JoinDate: $fjoined\n";
$msg .= "SubsLastPaid: $fsubs_last_paid\n";

$msg .= "RequestTitle: $fptitle\n";
$msg .= "RequestFamilyName: $fpfamily\n";
$msg .= "RequestFirstName: $fpfirst\n";
$msg .= "RequestPreferred: $fpprefers\n";
$msg .= "RequestMiddleName: $fpmiddle\n";
$msg .= "RequestBirthdate: $fpbirth\n";
$msg .= "RequestPositionTitle: $fppostitle\n";
$msg .= "RequestIndustry: $fpindustry\n";
$msg .= "RequestEthnicity: $fpethnicity\n";
$msg .= "RequestMaritalStatus: $fpmarital\n";

$msg .= "Salutation: $fasalutation\n";
$msg .= "1st Label Line: $falabelline\n";
$msg .= "Post Address1: $faddress1\n";
$msg .= "Post Address2: $faddress2\n";
$msg .= "Post Suburb: $fasuburb\n";
$msg .= "Post City/Town: $facity\n";
$msg .= "Post Postcode: $fapostcode\n";
$msg .= "Post Country: $facountry\n";
$msg .= "Delivery Area: $fadelivery\n";

$msg .= "Del Address1: $fa2ddress1\n";
$msg .= "Del Address2: $fa2ddress2\n";
$msg .= "Del Suburb: $fa2suburb\n";
$msg .= "Del City/Town: $fa2city\n";
$msg .= "Del Country: $fa2country\n";

$msg .= "RequestHomePhone: $fphhome\n";
$msg .= "RequestWorkPhone: $fphwork\n";
$msg .= "RequestCellNo: $fphcell\n";
$msg .= "RequestAltNo: $fphalt\n";
$msg .= "RequestFaxNo: $fphfax\n";
$msg .= "RequestEmail: $fpemail\n";

$msg .= "PartnerTitle: $fstitle\n";
$msg .= "PartnerFamilyName: $fsfamily\n";
$msg .= "PartnerFirstName: $fsfirst\n";
$msg .= "PartnerPreferred: $fsprefers\n";
$msg .= "PartnerMiddleName: $fsmiddle\n";
$msg .= "PartnerBirthdate: $fsbirth\n";
$msg .= "Partnerindustry: $fsindustry\n";
$msg .= "PartnerPositionTitle: $fspostitle\n";
/* $msg .= "PartnerEthnicity: $fsethnicity\n"; */

$msg .= "ChildPreferred1: $fc1prefers\n";
$msg .= "ChildFamily1: $fc1family\n";
$msg .= "ChildBirthdate1: $fc1birth\n";
$msg .= "ChildPreferred2: $fc2prefers\n";
$msg .= "ChildFamily2: $fc2family\n";
$msg .= "ChildBirthdate2: $fc2birth\n";
$msg .= "ChildPreferred3: $fc3prefers\n";
$msg .= "ChildFamily3: $fc3family\n";
$msg .= "ChildBirthdate3: $fc3birth\n";
$msg .= "ChildPreferred4: $fc4prefers\n";
$msg .= "ChildFamily4: $fc4family\n";
$msg .= "ChildBirthdate4: $fc4birth\n";
$msg .= "ChildPreferred5: $fc5prefers\n";
$msg .= "ChildFamily5: $fc5family\n";
$msg .= "ChildBirthdate5: $fc5birth\n";
$msg .= "ChildPreferred6: $fc6prefers\n";
$msg .= "ChildFamily6: $fc6family\n";
$msg .= "ChildBirthdate6: $fc6birth\n";
$msg .= "ChildPreferred7: $fc7prefers\n";
$msg .= "ChildFamily7: $fc7family\n";
$msg .= "ChildBirthdate7: $fc7birth\n";
$msg .= "ChildPreferred8: $fc8prefers\n";
$msg .= "ChildFamily8: $fc8family\n";
$msg .= "ChildBirthdate8: $fc8birth\n";

$msg .= "AntenatalStatus: $fon_antenatal\n";
$msg .= "BirthDue: $ftbirthdue\n";
/*
$msg .= "SystemRoles:  " ;
 if ( is_array( $froles) ) $msg .= implode( ",", $froles);
$msg .= "\n";
*/
$msg .= "CouldCommittee: $fcommittee\n";
$msg .= "NoMailout: $fnomailout\n";
$msg .= "Notes: $fnotes\n";
$msg .= "Submitted: $now\n";


  $because .= nl2br( $msg );
  $because .= "<HR>";
  mail( "wrmsadmin@catalyst.net.nz", "[debugging] Database Change Submitted", $msg, "From: wrmsdb@catalyst.net.nz" );
  $because .= "<P>Debugging message sent to DB administrator</P>";

  if ( strcmp( trim("$fto_dbadmin"), "" ) ) {
    $msg = "<!doctype html public \"-//w3c//dtd html 4.0 transitional//en\"><HTML><BODY>";
    $msg .= "<TABLE BORDER=1 WIDTH=90% ALIGN=CENTER>";
    $msg .= "<TR><TH ALIGN=RIGHT>System:</TH><TD> $fsystem_code</TD></TR>\n";
    $msg .= "<TR><TH ALIGN=RIGHT>Maintainer:</TH><TD> $fusername</TD></TR>\n";
    $msg .= "<TR><TH ALIGN=RIGHT>ChangeType:</TH><TD> $fchangetype</TD></TR>\n";
    $msg .= "<TR><TH ALIGN=RIGHT>RequestNo:</TH><TD> $frequestno</TD></TR>\n";
    $msg .= "<TR><TH ALIGN=RIGHT>RequestType:</TH><TD> $frequesttype</TD></TR>\n";
    $msg .= "<TR><TH ALIGN=RIGHT>RequestSource:</TH><TD> $frequestsource</TD></TR>\n";
    $msg .= "<TR><TH ALIGN=RIGHT>JoinRenew:</TH><TD> $joinrenew</TD></TR>\n<P>&nbsp;</P><HR>";
    $msg .= "<H3>Notes to DB Admin:</H3>\n<P>$fto_dbadmin</P></HTML>\n";
    $because .= "<HR>";
    mail( "wrmsadmin@catalyst.net.nz", "Notes to DB Maintainer",  $msg, "From: $usr->email\nContent-Type: text/html; charset=us-ascii" );
    $because .= "<P>Notes message sent to DB administrator</P>";

  }
/*  phpinfo(); */
?>

