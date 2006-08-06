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
/* Filename:    qams-request-approval.php                               */
/* Author:      Paul Waite                                              */
/* Description: QAMS step approval request page                         */
/*                                                                      */
/* ******************************************************************** */
require_once("always.php");
require_once("authorisation-page.php");

$session->LoginRequired();

require_once("maintenance-page.php");

$title = "QAMS Request Approval";

// -----------------------------------------------------------------------------------------------
include_once("qams-project-defs.php");

// -----------------------------------------------------------------------
// FUNCTIONS
function ContentForm(&$project, &$qastep) {
  global $have_admin;
  
  $s = "";
  $s .= "<table cellspacing=\"2\" cellpadding=\"2\" width=\"100%\">\n";

  $s .= "<tr class=\"row0\">";
  $s .= "<td colspan=\"2\" style=\"padding-left:20px;padding-right:50px\">"
      . "<p>The information and fields below are to help you obtain a Quality Assurance Approval "
      . "from a nominated reviewer. The reviewer has to be someone 'allocated' to the project, "
      . "so if you don't find them on the list, you will need to add them to the project (use "
      . "the Edit Project link) and then return to this screen once that is done.</p>"
      . "<p>When you click on the 'Request Approval' button QAMS will e-mail the selected reviewer "
      . "with a request for approval. The e-mail will contain links to the WRMS associated with this "
      . "QA Step, <b><u>to which you should have attached any relevant document(s)</b></u> for "
      . "the review.</p>"
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

  // Step description..  
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

  // Overall status..
  $status = qa_status_coloured($qastep->overall_approval_status());
  $s .= "<tr class=\"row1\">";
  $s .= "<th class=\"prompt\"><b>Current overall status:</b> </th>";
  $s .= "<td>$status</td>";
  $s .= "</tr>\n";
  
  // Required approvals list..
  $s .= "<tr class=\"row1\">";
  $s .= "<th class=\"prompt\"><b>Current approval statuses:</b> </th>";
  $s .= "<td>" . $qastep->render_approval_types(false, true) . "</td>";
  $s .= "</tr>\n";
    
  // Request from person..
  $F  = "<select size=\"1\" name=\"approval_from_person\">";
  $F .= "<option value=\"\">-- select a person --</option>\n";
  $extras = array();
  if (isset($project->user_no) && isset($project->fullname)) {
    $extras[$project->user_no] = $project->fullname;
  }
  $extras[$project->project_manager] = $project->project_manager_fullname;
  $extras[$project->qa_mentor] = $project->qa_mentor_fullname;      
  $requestors = $extras + $project->allocated; 
  foreach ($requestors as $user_no => $fullname) {
    $F .= "<option value=\"$user_no\">$fullname</option>\n";
  }
  $F .= "</select>\n";
  
  $s .= "<tr class=\"row1\">";
  $s .= "<th class=\"prompt\"><b>Send to this reviewer:</b> </th>";
  $s .= "<td>" . $F . "</td>";
  $s .= "</tr>\n"; 
  
  // Approval type to request..
  $F  = "<select size=\"1\" name=\"approval_type\">";
  $F .= "<option value=\"\">-- select an approval type --</option>\n";
  foreach ($qastep->approvals_required() as $ap_type_id => $ap_type_desc) {
    $F .= "<option value=\"$ap_type_id\"";
    $F .= ">$ap_type_desc</option>\n";
  }
  $s .= "<tr class=\"row1\">";
  $s .= "<th class=\"prompt\"><b>Requesting this approval:</b> </th>";
  $s .= "<td>" . $F . "</td>";
  $s .= "</tr>\n"; 

  $F  = "<textarea name=\"approval_covernotes\" style=\"width:400px;height:150px\">";
  $F .= "</textarea>";
  $s .= "<tr class=\"row1\">";
  $s .= "<th class=\"prompt\"><b>Covering notes:</b> </th>";
  $s .= "<td>" . $F . "</td>";
  $s .= "</tr>\n";

  // Vertical spacer
  $s .= "<tr class=\"row0\">";
  $s .= "<td colspan=\"2\" height=\"15\">&nbsp;</td>";
  $s .= "</tr>\n";
  
  $s .= "<tr class=\"row0\">";
  $s .= "<td colspan=\"2\" style=\"padding-left:20px;padding-right:50px\">"
      . "<p>The e-mail will also contain clickable links for the recipient to either "
      . "Approve or Refuse the request. If the above all looks ok, then click the "
      . "button below to send the e-mail off.</p>"
      . "</td>";
  $s .= "</tr>\n";
  
  // Vertical spacer
  $s .= "<tr class=\"row0\">";
  $s .= "<td colspan=\"2\" height=\"25\">&nbsp;</td>";
  $s .= "</tr>\n";

  $s .= "</table>\n";
  return $s;
  
} // ContentForm


// -----------------------------------------------------------------------------------------------
// MAIN CONTENT

// Initialise request identity..
if (!isset($project_id)) {
  $project_id = 0;
}

// New project object to work with..
$project = new qa_project($project_id);
$have_admin = $project->qa_process->have_admin; 

// For the edit form..
$edit = $have_admin;

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
  if (isset($submit) && $submit == "Request Approval") {
    if ($project->request_id > 0 && $approval_from_person != "" && $approval_type != "") {
      // Now create and deliver some e-mail..
      $qry = new PgQuery("SELECT email, fullname FROM usr WHERE user_no=$approval_from_person");
      if ($qry->Exec("qams-request-approval.php::get approver") && $qry->rows > 0) {
        $row = $qry->Fetch();
        
        // Approver email..
        $approver_email = $row->email;
        $approver_fullname = $row->fullname;
        $subject = "QAMS Approval Request: $qastep->qa_step_desc [$project->system_id/$project->username]";
        $recipients = array($approver_email => $approver_fullname);

        // First of all, let's sort out the database..
        $qastep->request_approval($approval_type, $approval_from_person);
        
        // Assemble body for approver..
        $s = "";
        $s .= "<p><b>STEP 1: Review</b> <br>";
        $s .= "This is a request for you to review and approve a Quality Assurance Step ";
        $s .= "for the project. Please do so as soon as you can manage it, otherwise it ";
        $s .= "is possible the project may be held up.</p>";
        $s .= "<p>The step for review is: " . $qastep->qa_step_desc . "</p>";
        if ($qastep->qa_step_notes != "") {
          $s .= "<p>Notes for reviewers: " . $qastep->qa_step_notes . "</p>";
        }
        if ($qastep->qa_document_title != "") {
          $s .= "<p>The focus of this review is documentation, the '" . $qastep->qa_document_title . "'. ";
          if ($qastep->qa_document_desc != "") {
            $s .= $qastep->qa_document_desc;
          }
          $s .= "You can find this documentation attached to the Work Request below.</p>";
        }
        
        // WRMS access to step work request..
        $s .= "<p>The Work Request associated with this step is:<br>"; 
        $href = $URL_PREFIX . "/wr.php?request_id=$qastep->request_id";
        $wrlink = "<a href=\"$href\">$href</a>";
        $s .= "&nbsp;&nbsp;" . $wrlink . "</p>";
        
        // Covering notes..
        if ($approval_covernotes != "") {
          $s .= "<p><b>Specific Notes:</b><br>";
          $s .= $approval_covernotes . "</p>";
        }
        
        // Approval link..
        $s .= "<p><b>STEP 2: Approval</b> <br>";
        $s .= "To post an approval decision on this step, please use the following link to access QAMS:<br>";
        $href  = $URL_PREFIX . "/qams-step-approve.php"; 
        $href .= "?project_id=$project->request_id";
        $href .= "&step_id=$qastep->qa_step_id";
        $href .= "&ap_type_id=$approval_type";
        $applink = "<a href=\"$href\">$href</a>";
        $s .= "&nbsp;&nbsp;" . $applink . "</p>";
         
        $s .= "<p>Thank you for participating in Catalyst Quality Assurance.</p>";        
        $project->QAMSNotifyEmail("Request for QA approval", $s, $subject, $recipients);
        
        // Other emails to let everyone know what's going on..
        $recipients = $project->GetRecipients();
        if (isset($recipients[$approver_email])) {
          unset($recipients[$approver_email]);
        }
        $s  = "<p>A request has been sent to $approver_fullname to review and ";
        $s .= "approve the Quality Assurance Step '$qastep->qa_step_desc' on this project. ";
        $s .= "The summary link for this step is provided here:<br>";
        $href = $URL_PREFIX . "/qams-step-detail.php?project_id=$qastep->project_id&step_id=$qastep->qa_step_id";
        $stlink = "<a href=\"$href\">$href</a>";
        $s .= "&nbsp;&nbsp;" . $stlink . "</p>";
        $project->QAMSNotifyEmail("QAMS Activity Notice", $s, $subject, $recipients);
      } // got approver email
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
  }
  // Start main table..    
  $s .= "<table width=\"100%\" class=\"data\" cellspacing=\"0\" cellpadding=\"0\">\n";

  $s .= $ef->BreakLine("Quality Assurance Approval Request");
  $s .= "<tr><td height=\"15\" colspan=\"2\">&nbsp;</td></tr>";
  $s .= "<tr><td colspan=\"2\">" . ContentForm($project, $qastep) . "</td></tr>";
  $s .= "<tr><td height=\"15\" colspan=\"2\">&nbsp;</td></tr>";
  $s .= "</table>\n";
  
  $s .= $ef->SubmitButton( "submit", "Request Approval" );
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
