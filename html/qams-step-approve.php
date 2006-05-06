<?php
/* ******************************************************************** */
/* CATALYST PHP Source Code                                             */
/* -------------------------------------------------------------------- */
/* This program is free software; you can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License, or    */
/* (at your option) any later version.                                  */
/*                                                                      */
/* This program is distributed in the hope that it will be useful,      */
/* but WITHOUT ANY WARRANTY; without even the implied warranty of       */
/* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        */
/* GNU General Public License for more details.                         */
/*                                                                      */
/* You should have received a copy of the GNU General Public License    */
/* along with this program; if not, write to:                           */
/*   The Free Software Foundation, Inc., 59 Temple Place, Suite 330,    */
/*   Boston, MA  02111-1307  USA                                        */
/* -------------------------------------------------------------------- */
/*                                                                      */
/* Filename:    qams-step-approve.php                                   */
/* Author:      Paul Waite                                              */
/* Description: QAMS step approve page                                  */
/*                                                                      */
/* ******************************************************************** */
require_once("always.php");
require_once("authorisation-page.php");

$session->LoginRequired();

require_once("maintenance-page.php");

$title = "QAMS Step Approval";

// -----------------------------------------------------------------------------------------------
include_once("qams-project-defs.php");

// -----------------------------------------------------------------------
// FUNCTIONS
function ContentForm(&$project, &$qastep, $ap_type_id) {
  global $session;
  
  $s = "";
  $s .= "<table cellspacing=\"2\" cellpadding=\"2\" width=\"100%\">\n";

  $s .= "<tr class=\"row0\">";
  $s .= "<td colspan=\"2\" style=\"padding-left:20px;padding-right:50px\">"
    . "<p>This screen allows you to post an approval decision for the given step of the project.</p>"
    . "<p>Just select your chosen approval response, and add any extra comments or notes "
    . "as required. When you have finished click on the 'Post Decision' button to register it.</p>"
    . "<p>Thanks for participating in our QA program.</p>" 
      . "</td>";
  $s .= "</tr>\n";
  
  // Vertical spacer
  $s .= "<tr class=\"row0\">";
  $s .= "<td colspan=\"2\" height=\"15\">&nbsp;</td>";
  $s .= "</tr>\n";

  // Project brief..
  $s .= "<tr class=\"row1\">";
  $s .= "<th width=\"30%\" class=\"prompt\"><b>Project:</b> </th>";
  $s .= "<td width=\"70%\">$project->brief</td>";
  $s .= "</tr>\n";
  
  // WRMS step link..
  $href  = "/wr.php?request_id=$qastep->request_id";
  $label = "[WRMS $qastep->request_id]";
  $title = "Go to the WRMS record for this QA step (new window)";
  $link  = "<a href=\"$href\" title=\"$title\">$label</a>";
  
  // Step description
  $s .= "<tr class=\"row1\">";
  $s .= "<th class=\"prompt\"><b>Step to approve:</b> </th>";
  $s .= "<td>" . $qastep->qa_step_desc . "&nbsp;&nbsp;" . $link . "</td>";
  $s .= "</tr>\n";

  // Step notes..  
  $s .= "<tr class=\"row1\">";
  $s .= "<th class=\"prompt\"><b>Notes:</b> </th>";
  $s .= "<td><p>" . $qastep->qa_step_notes . "</p></td>";
  $s .= "</tr>\n";

  // Special notes..
  if ($qastep->special_notes != "") {
    $s .= "<tr class=\"row1\">";
    $s .= "<th class=\"prompt\"><b>Special notes:</b> </th>";
    $s .= "<td>" . $qastep->special_notes . "</td>";
    $s .= "</tr>\n";
  }

  // Required approvals list..
  $s .= "<tr class=\"row1\">";
  $s .= "<th class=\"prompt\"><b>Current approval status:</b> </th>";
  $s .= "<td>" . $qastep->render_approval_types(false, true, $ap_type_id) . "</td>";
  $s .= "</tr>\n";
  
  // Warning when changing approval status of already-approved step..
  $qastep->get_approvals();
  if ($qastep->approvals[$ap_type_id]->approval_status == "y") {
    $pmmailto = "<a href=\"mailto:$project->project_manager_email\">"
              . "E-Mail the Project Manager</a>";

    $s .= "<tr class=\"row1\">";
    $s .= "<th>&nbsp;</th>";
    $s .= "<td style=\"padding-right:30px\">"
            . "<p class=\"error\" style=\"margin-bottom:0px\">"
            . "WARNING: THIS HAS ALREADY BEEN APPROVED</p>"
            . "<p class=\"helpnote\" style=\"margin-top:0px\">"
            . "However, you can still change the approval status here, if that is what "
            . "you think is required. If you are not sure, or think this is an error then "
            . "please " . $pmmailto . ", and let him know about the problem.</p>"
            . "</td>";
    $s .= "</tr>\n";
  }
  
  // Approval decision. We only allow the assigned user to see the
  // form elements for doing this, plus QA admin folks..
  if ($project->qa_process->have_admin
   || $qastep->approvals[$ap_type_id]->assigned_to_usr == $session->user_no) {
  
    $F  = "<select size=\"1\" name=\"new_approval_status\">";
    $F .= "<option value=\"\">-- select an approval decision --</option>\n";
    $F .= "<option value=\"y\">Approve this step</option>\n";
    $F .= "<option value=\"n\">Refuse approval for this step</option>\n";
    if ($project->qa_process->have_admin) {
      $F .= "<option value=\"s\">Skip approval for this step</option>\n";
    }
    
    $s .= "<tr class=\"row1\">";
    $s .= "<th class=\"prompt\"><b>Post an approval:</b> </th>";
    $s .= "<td>" . $F . "</td>";
    $s .= "</tr>\n";
  
    $F  = "<textarea name=\"approval_covernotes\" style=\"width:400px;height:150px\">";
    $F .= "</textarea>";
    $s .= "<tr class=\"row1\">";
    $s .= "<th class=\"prompt\"><b>Covering notes:</b> </th>";
    $s .= "<td>" . $F . "</td>";
    $s .= "</tr>\n";
  }
  
  // Vertical spacer
  $s .= "<tr class=\"row0\">";
  $s .= "<td colspan=\"2\" height=\"25\">&nbsp;</td>";
  $s .= "</tr>\n";

  $s .= "</table>\n";
  return $s;
  
} // ContentForm

// -----------------------------------------------------------------------------------------------
// MAIN CONTENT

// Must haves..
if (!isset($project_id) || !isset($step_id) || !isset($ap_type_id)) {
  exit;
}

// New project object to work with..
$project = new qa_project($project_id);
$have_admin = $project->qa_process->have_admin; 

// ---------------------------------------------------------------------
// Make sure we have a valid QA step..
if (!isset($qastep)) {
  if (isset($project_id) && isset($step_id)) {
    $qastep = new qa_project_step();
    $qastep->get($project_id, $step_id);
    if (!$qastep->valid) {
      unset($qastep);
    }
  }
}

$s = "";
if (isset($qastep)) {
  
  // PROCESS POSTED UPDATES..
  if (isset($submit) && $submit == "Post Decision") {
    if ($project->request_id > 0 && $ap_type_id != "" && $new_approval_status != "") {
      // Where are we currently..
      $orig_overall_status = $qastep->overall_approval_status();
       
      // Write the approval history record..
      $qastep->approve($ap_type_id, $new_approval_status, $approval_covernotes);
      
      $s = "";
      $display_status = strtoupper(qa_approval_status($new_approval_status));
      $subject = "QAMS Approval: $qastep->qa_step_desc [$display_status/$project->system_id/$session->username]";
      
      // Assemble body for approver..
      $s .= "<p>The Quality Assurance Step '$qastep->qa_step_desc' has had an Approval ";
      $s .= "posted to it by $session->fullname.</p>";
      
      $s .= "<p>The decision was: '$display_status'.</p>";

      // Statement of resulting overall status..      
      $new_overall_status = $qastep->overall_approval_status();
      $s .= "<p>The overall approval status of the Step ";
      if ($new_overall_status == $orig_overall_status) {
        $s .= "remains as";
      }
      else {
        $s .= "has now changed to";
      }
      $s .= " '" . qa_approval_status($new_overall_status) . "'.";
      $s .= "</p>";
      
      if ($qastep->approval_overridden($ap_type_id)) {
        $s .= "<p>NB: the approval was overridden. " . $qastep->approvals[$ap_type_id]->assigned_fullname;
        $s .= " was originally assigned to approve this.</p>";
      }

      // Summary link for step..      
      $s .= "<p>The summary link for this step is provided here:<br>";
      $href = $GLOBALS[base_dns] . "/qams-step-detail.php?project_id=$qastep->project_id&step_id=$qastep->qa_step_id";
      $stlink = "<a href=\"$href\">$href</a>";
      $s .= "&nbsp;&nbsp;" . $stlink . "</p>";
      
      // Covering notes..
      if ($approval_covernotes != "") {
        $s .= "<p><b>The approver also noted:</b><br>";
        $s .= $approval_covernotes . "</p>";
      }
      $project->QAMSNotifyEmail("Approval Post Advice", $s, $subject);
      
      // Now re-direct them where they can see the project summary..
      header("Location: /qams-project.php?request_id=$project->request_id");
      exit;
    }
  }
  
  // Main content..    
  $s = "";
  $ef = new EntryForm($REQUEST_URI, $project, ($have_admin ? 1 : 0));
  $ef->NoHelp();
  
  $s .= $ef->StartForm();
  $s .= $ef->HiddenField( "qa_action", "$qa_action" );
  if ( $project->request_id > 0 ) {
    $s .= $ef->HiddenField( "project_id", $project->request_id );
    $s .= $ef->HiddenField( "step_id", "$qastep->qa_step_id" );
    $s .= $ef->HiddenField( "ap_type_id", "$ap_type_id" );
  }
  // Start main table..    
  $s .= "<table width=\"100%\" class=\"data\" cellspacing=\"0\" cellpadding=\"0\">\n";
  $s .= $ef->BreakLine("Quality Assurance Approval");
  $s .= "<tr><td height=\"15\" colspan=\"2\">&nbsp;</td></tr>";
  $s .= "<tr><td colspan=\"2\">" . ContentForm($project, $qastep, $ap_type_id) . "</td></tr>";
  $s .= "<tr><td height=\"15\" colspan=\"2\">&nbsp;</td></tr>";
  $s .= "</table>\n";

  $s .= $ef->SubmitButton( "submit", "Post Decision" );
  $s .= $ef->EndForm();
  
} // isset qastep

// -----------------------------------------------------------------------------
// ASSEMBLE CONTENT
if ($s == "") {
  $content = "<p>Nothing known about that QA step.</p>"; 
}
else {
  $content = $s;
}

// -----------------------------------------------------------------------------
// DELIVER..
require_once("top-menu-bar.php");
require_once("page-header.php");

echo $content;

include("page-footer.php");
?>
