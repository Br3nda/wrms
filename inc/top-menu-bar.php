<?php

require_once("MenuClass.php");
$tmnu = new MenuSet('tmnu', 'tmnu', 'tmnu_active');

function request_menus(&$tmnu, $wr) {

  error_log("DBG: request_menus - $wr->request_id");
  if ( $wr->request_id > 0 ) {
    $tmnu->AddOption("WR#$wr->request_id","/wr.php?request_id=$wr->request_id","View the details for this work request");
    if ( $wr->AllowedTo('update') )
      $tmnu->AddOption("Edit","/wr.php?edit=1&request_id=$wr->request_id","Edit the details for this work request");
    if ( $wr->AllowedTo('create') )
      $tmnu->AddOption("New Related","/wr.php?parent_request_id=$wr->request_id","Create a new work request related to this one.");
  }

  if ( $wr->org_code > 0 )
    $tmnu->AddOption("Organisation","/org.php?org_code=$wr->org_code&request_id=$wr->request_id","View the details for this organisation");
  if ( $wr->system_code != "" )
    $tmnu->AddOption("System","/system.php?system_code=".urlencode($wr->system_code)."&request_id=$wr->request_id","View the details for this system");
  if ( $wr->user_no > 0 )
    $tmnu->AddOption("User","/user.php?user_no=$wr->user_no&request_id=$wr->request_id","View the details for the requesting user");
}

function user_menus(&$tmnu,$user) {
  global $session;
  if ( intval("$user->user_no") == 0 ) return;

  $tmnu->AddOption($user->username,"/user.php?user_no=$user->user_no","View the details for this user");
  $tmnu->AddOption("Edit","/user.php?edit=1&user_no=$user->user_no","Edit the details for this user");
  $tmnu->AddOption("Subscribed","/requestlist.php?qs=complex&interested_in=$user->user_no","List current requests this user is subscribed to");
  $tmnu->AddOption("Allocated","/requestlist.php?qs=complex&allocated_to=$user->user_no","List current requests this user is allocated to");

  if ( $session->AllowedTo('Admin') || $session->AllowedTo('Support') ) {
    $tmnu->AddOption("Uncharged","/form.php?user_no=$user->user_no&form=timelist&uncharged=1","List uncharged work for this user");
  }
  if ( $user->org_code > 0 )
    $tmnu->AddOption("Organisation","/org.php?org_code=$user->org_code","View the details for this organisation");
}

function organisation_menus(&$tmnu,$org) {
  global $session;
  if ( intval("$org->org_code") == 0 ) return;

  $tmnu->AddOption("Organisation","/org.php?org_code=$org->org_code","View the details for this organisation");
  $tmnu->AddOption("Edit","/org.php?edit=1&org_code=$org->org_code","Edit the details for this organisation");
  $tmnu->AddOption("Requests","/requestlist.php?org_code=$org->org_code","List current requests for this organisation");
  $tmnu->AddOption("Systems","/form.php?org_code=$org->org_code&form=syslist","List systems for this organisation");
  $tmnu->AddOption("Users","/usrsearch.php?org_code=$org->org_code","List users for this organisation");

  if ( $session->AllowedTo('Admin') || $session->AllowedTo('Support') ) {
    $tmnu->AddOption("Uncharged","/form.php?org_code=$org->org_code&form=timelist&uncharged=1","List users for this organisation");
  }
}

function system_menus(&$tmnu,$system) {
  global $session;
  $session->Log("DBG: system_menus: %s", $system->system_code);
  if ( $system->system_code == "" ) return;
  if ( !$system->AllowedTo('view') ) return;

  $tmnu->AddOption($system->system_code,"/system.php?system_code=".urlencode($system->system_code),"View the details for this system");
  if ( $system->AllowedTo('update') )
    $tmnu->AddOption("Edit","/system.php?edit=1&system_code=".urlencode($system->system_code),"Edit the details for this system");
  $tmnu->AddOption("Requests","/requestlist.php?system_code=".urlencode($system->system_code),"List current requests for this system");
  $tmnu->AddOption("Organisations","/form.php?system_code=".urlencode($system->system_code)."&form=orglist","List organisations for this system");
  $tmnu->AddOption("Users","/usrsearch.php?system_code=".urlencode($system->system_code),"List users associated with this system");
  $tmnu->AddOption("Roles","/system_users.php?system_code=".urlencode($system->system_code),"Maintain roles of users associated with this system");

  if ( $session->AllowedTo('Admin') || $session->AllowedTo('Support') ) {
    $tmnu->AddOption("Uncharged","/form.php?system_code=".urlencode($system->system_code)."&form=timelist&uncharged=1","List users for this system");
  }
}

function attachment_type_menus(&$tmnu,$att) {
  if ( isset($att) && "$att->type_code" != "" ) {
    $tmnu->AddOption("$att->type_code","/attachment_type.php?type_code=$att->type_code","View the details for this Attachment Type" );
    $tmnu->AddOption("Edit","/attachment_type.php?edit=1&type_code=$att->type_code","Edit the details for this Attachment Type",
               isset($GLOBALS['edit']) && $GLOBALS['edit'] && ! $att->new_record );
  }
  $tmnu->AddOption("New","/attachment_type.php","Create a new Attachment Type" );
  $tmnu->AddOption("List","/form.php?form=attachment_type","List the existing attachment types");
}

function request_id_menus(&$tmnu, $request_id) {
  global $session, $org_code, $system_code;
  if ( intval("$request_id") == 0 ) return;
  $request_id = intval($request_id);


  error_log("DBG: request_menus - $request_id - $org_code - $system_code");
  if ( $request_id > 0 ) {
    $tmnu->AddOption("WR#$request_id","/wr.php?request_id=$request_id","View the details for this work request");
    $tmnu->AddOption("Edit Request","/wr.php?edit=1&request_id=$request_id","Edit the details for this work request");
  }

  if ( $org_code > 0 ) {
    $tmnu->AddOption("Organisation","/org.php?org_code=$org_code&request_id=$request_id","View the details for this organisation");
    $tmnu->AddOption("Edit Organisation","/org.php?edit=1&org_code=$org_code","Edit the details for this organisation");
  }
  if ( $system_code != "" ) {
    $tmnu->AddOption("System","/system.php?system_code=".urlencode($system_code)."&request_id=$request_id","View the details for this system");
    $tmnu->AddOption("Edit System","/system.php?edit=1&system_code=".urlencode($system_code),"Edit the details for this system");
  }
}

function org_code_menus(&$tmnu,$org_code) {
  global $session;
  $session->Log("DBG: org_code_menus: $org_code");
  if ( intval("$org_code") == 0 ) return;
  $org_code = intval($org_code);

  $tmnu->AddOption("Organisation","/org.php?org_code=$org_code","View the details for this organisation");
  $tmnu->AddOption("Edit","/org.php?edit=1&org_code=$org_code","Edit the details for this organisation");
  $tmnu->AddOption("Requests","/requestlist.php?org_code=$org_code","List current requests for this organisation");
  $tmnu->AddOption("Systems","/form.php?org_code=$org_code&form=syslist","List systems for this organisation");
  $tmnu->AddOption("Users","/usrsearch.php?org_code=$org_code","List users for this organisation");

  if ( $session->AllowedTo('Admin') || $session->AllowedTo('Support') ) {
    $tmnu->AddOption("Uncharged","/form.php?org_code=$org_code&form=timelist&uncharged=1","List users for this organisation");
  }
}

function system_code_menus(&$tmnu,$system_code) {
  global $session;
  $session->Log("DBG: system_code_menus: $system_code");
  if ( $system_code == "" ) return;

  $tmnu->AddOption($system_code,"/system.php?system_code=".urlencode($system_code),"View the details for this system");
  $tmnu->AddOption("Edit","/system.php?edit=1&system_code=".urlencode($system_code),"Edit the details for this system");
  $tmnu->AddOption("Requests","/requestlist.php?system_code=".urlencode($system_code),"List current requests for this system");
  $tmnu->AddOption("Organisations","/form.php?system_code=".urlencode($system_code)."&form=orglist","List organisations for this system");
  $tmnu->AddOption("Users","/usrsearch.php?system_code=".urlencode($system_code),"List users associated with this system");
  $tmnu->AddOption("Roles","/system_users.php?system_code=".urlencode($system_code),"Maintain roles of users associated with this system");

  if ( $session->AllowedTo('Admin') || $session->AllowedTo('Support') ) {
    $tmnu->AddOption("Uncharged","/form.php?system_code=".urlencode($system_code)."&form=timelist&uncharged=1","List users for this system");
  }
}

function user_no_menus(&$tmnu,$user_no) {
  global $session;
  $session->Log("DBG: user_no_menus: $user_no");
  if ( intval("$user_no") == 0 ) return;
  $user_no = intval($user_no);

  $tmnu->AddOption("User","/user.php?user_no=$user_no","View the details for this user");
  $tmnu->AddOption("Edit","/user.php?edit=1&user_no=$user_no","Edit the details for this user");
  $tmnu->AddOption("Subscribed","/requestlist.php?qs=complex&interested_in=$user_no","List current requests this user is subscribed to");
  $tmnu->AddOption("Allocated","/requestlist.php?qs=complex&allocated_to=$user_no","List current requests this user is allocated to");

  if ( $session->AllowedTo('Admin') || $session->AllowedTo('Support') ) {
    $tmnu->AddOption("Uncharged","/form.php?user_no=$user_no&form=timelist&uncharged=1","List uncharged work for this user");
  }
}

function qams_menus(&$tmnu) {
  global $session, $project, $qastep;
  
  // Safety-first.. 
  $have_admin = false;
  
  // WRMS access to project master work request..
  if (isset($project) && is_object($project)) {
    $have_admin = $project->qa_process->have_admin;

    // Master WRMS..    
    $tmnu->AddOption(
        "WR#$project->request_id",
        "/wr.php?request_id=$project->request_id",
        "Go to the master WRMS for this project"
        );
  
    // QA Summary for project..
    $tmnu->AddOption(
        "Summary",
        "/qams-project.php?request_id=$project->request_id",
        "View the quality assurance summary for this project"
        );
        
    // QA steps options.. 
    if (isset($qastep)) {       
      $tmnu->AddOption(
          "Step Detail",
          "/qams-step-detail.php?project_id=$project->request_id&step_id=$qastep->qa_step_id",
          "View the detail page for the current QA step"
          );
    }
    
    // View/edit QA Plan..
    $tmnu->AddOption(
        "QA Plan",
        "/qams-project.php?qa_action=qaplan&request_id=$project->request_id" . ($have_admin ? "&edit=1" : ""),
        "View the quality assurance plan for this project"
        );

    // View/edit project details..
    $tmnu->AddOption(
        "Project",
        "/qams-project.php?request_id=$project->request_id" . ($have_admin ? "&edit=1" : ""),
        "View the QA project details"
        );
        
    // View project status..
    $tmnu->AddOption(
        "Status",
        "/qams-project-status.php?project_id=$project->request_id",
        "View the project status report"
        );
  }
} // qams_menus

$session->Log("DBG: top-menu-bar: %s, %s, %s, %s", $REQUEST_URI, $request_id, $user_no, $system_code);

if     ( strstr($REQUEST_URI,"qams") )                  qams_menus($tmnu,$wr);
elseif ( strstr($REQUEST_URI,"/wr.php") )               request_menus($tmnu,$wr);
elseif ( isset($request_id) && $request_id > 0 )        request_id_menus($tmnu,$request_id);
elseif ( strstr($REQUEST_URI,"/org.php") )              organisation_menus($tmnu,$org);
elseif ( strstr($REQUEST_URI,"/system.php") )           system_menus($tmnu,$ws);
elseif ( strstr($REQUEST_URI,"/attachment_type.php") )  attachment_type_menus($tmnu,$att);
elseif ( strstr($REQUEST_URI,"/user.php") )             user_menus($tmnu,$user);
elseif ( isset($org_code) && $org_code > 0 )            org_code_menus($tmnu,$org_code);
elseif ( isset($system_code) && $system_code != "" )    system_code_menus($tmnu,$system_code);
elseif ( isset($user_no) && $user_no > 0 )              user_no_menus($tmnu,$user_no);

?>