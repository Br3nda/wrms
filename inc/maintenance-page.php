<?php
require_once("PgQuery.php");
require_once("DataEntry.php");
require_once("DataUpdate.php");
require_once("FormHandler.php");

require_once("MenuClass.php");
$tmnu = new MenuSet('tmnu', 'tmnu', 'tmnu_active');

if ( (isset($request_id) && $request_id > 0) ) {
  $tmnu->AddOption("Request","/wr.php?request_id=$request_id","View the details for this work request");
  if ( strstr($REQUEST_URI, "/wr.php") ) {
    $tmnu->AddOption("Edit","/wr.php?edit=1&request_id=$request_id","Edit the details for this work request");
  }

}
// if ( strstr($REQUEST_URI,"/wr.php") || (isset($request_id) && $request_id > 0) ) {
//   $tmnu->AddOption("Request","/wr.php?request_id=$request_id","View the details for this work request");
//   if ( strstr($REQUEST_URI, "/wr.php") ) {
//     $tmnu->AddOption("Edit","/wr.php?edit=1&request_id=$request_id","Edit the details for this work request");
//   }
// }
// elseif ( strstr($REQUEST_URI,"/org.php") || (isset($org_code) && $org_code > 0) ) {
if ( strstr($REQUEST_URI,"/org.php") || (isset($org_code) && $org_code > 0) ) {
  $tmnu->AddOption("Organisation","/org.php?org_code=$org_code","View the details for this organisation");
  if ( strstr($REQUEST_URI, "/org.php") ) {
    $tmnu->AddOption("Edit","/org.php?edit=1&org_code=$org_code","Edit the details for this organisation");
  }
  $tmnu->AddOption("Requests","/requestlist.php?org_code=$org_code","List current requests for this organisation");
  $tmnu->AddOption("Systems","/form.php?org_code=$org_code&form=syslist","List systems for this organisation");
  $tmnu->AddOption("Users","/usrsearch.php?org_code=$org_code","List users for this organisation");

//  if ( $session->AllowedTo('Admin') || $session->AllowedTo('Support') ) {
  if ( is_member_of('Admin', 'Support' ) ) {
    $tmnu->AddOption("Uncharged","/form.php?org_code=$org_code&form=timelist&uncharged=1","List users for this organisation");
  }
}
elseif ( strstr($REQUEST_URI,"/system.php") || (isset($system_code) && $system_code != '') ) {
  $tmnu->AddOption("System","/system.php?system_code=$system_code","View the details for this system");
  if ( strstr($REQUEST_URI, "/system.php") ) {
    $tmnu->AddOption("Edit","/system.php?edit=1&system_code=$system_code","Edit the details for this system");
  }
  $tmnu->AddOption("Requests","/requestlist.php?system_code=$system_code","List current requests for this system");
  $tmnu->AddOption("Organisations","/form.php?system_code=$system_code&form=orglist","List organisations for this system");
  $tmnu->AddOption("Users","/usrsearch.php?system_code=$system_code","List users associated with this system");

//  if ( $session->AllowedTo('Admin') || $session->AllowedTo('Support') ) {
  if ( is_member_of('Admin', 'Support' ) ) {
    $tmnu->AddOption("Uncharged","/form.php?system_code=$system_code&form=timelist&uncharged=1","List users for this system");
  }
}
elseif ( strstr($REQUEST_URI,"/attachment_type.php") || (isset($type_code) && $type_code != '') ) {
  $tmnu->AddOption("View Attachment Type","/attachment_type.php?type_code=$type_code","View the details for this Attachment Type");
  $tmnu->AddOption("Edit Attachment Type","/attachment_type.php?edit=1&type_code=$type_code","Edit the details for this Attachment Type");
  $tmnu->AddOption("New Attachment Type","/attachment_type.php","Create a new Attachment Type");
}
elseif ( strstr($REQUEST_URI,"/usr.php") || (isset($user_no) && $user_no != '') ) {
  $tmnu->AddOption("User","/usr.php?user_no=$user_no","View the details for this user");
  if ( strstr($REQUEST_URI, "/usr.php") ) {
    $tmnu->AddOption("Edit","/usr.php?edit=1&user_no=$user_no","Edit the details for this user");
  }
  $tmnu->AddOption("Subscribed","/requestlist.php?qs=complex&interested_in=$user_no","List current requests this user is subscribed to");
  $tmnu->AddOption("Allocated","/requestlist.php?qs=complex&allocated_to=$user_no","List current requests this user is allocated to");

//  if ( $session->AllowedTo('Admin') || $session->AllowedTo('Support') ) {
  if ( is_member_of('Admin', 'Support' ) ) {
    $tmnu->AddOption("Uncharged","/form.php?user_no=$user_no&form=timelist&uncharged=1","List uncharged work for this user");
  }
}
?>