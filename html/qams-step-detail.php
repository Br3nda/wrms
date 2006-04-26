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
/* Filename:    qams-step-detail.php                                    */
/* Author:      Paul Waite                                              */
/* Description: QAMS step editing and viewing page                      */
/*                                                                      */
/* ******************************************************************** */
require_once("always.php");
require_once("authorisation-page.php");

$session->LoginRequired();

require_once("maintenance-page.php");

$title = "QAMS Step Detail";

// -----------------------------------------------------------------------
include_once("qams-project-defs.php");

// -----------------------------------------------------------------------
// FUNCTIONS
function ContentForm(&$project, &$qastep, $view_history="no") {
  global $session;
  
  $have_admin = $project->qa_process->have_admin;
  
  $s = "";
  $s .= "<table cellspacing=\"2\" cellpadding=\"2\" width=\"100%\">\n";
  $s .= "<tr class=\"row0\">";
  
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
  $s .= "<th class=\"prompt\"><b>Step:</b> </th>";
  $s .= "<td>" . $qastep->qa_step_desc . "&nbsp;&nbsp;" . $link . "</td>";
  $s .= "</tr>\n";
  
  if ($qastep->qa_document_title != "") {
    $s .= "<tr class=\"row1\">";
    $s .= "<th class=\"prompt\"><b>Associated document:</b> </th>";
    $s .= "<td>" . $qastep->qa_document_title . "</td>";
    $s .= "</tr>\n";
    if ($qastep->qa_document_desc != "") {
      $s .= "<tr class=\"row1\">";
      $s .= "<th class=\"prompt\">&nbsp;</b> </th>";
      $s .= "<td>" . $qastep->qa_document_desc . "</td>";
      $s .= "</tr>\n";
    }
  }
  
  // Step notes..
  $s .= "<tr class=\"row1\">";
  $s .= "<th class=\"prompt\"><b>Notes for reviewers:</b> </th>";
  $s .= "<td>" . $qastep->qa_step_notes . "</td>";
  $s .= "</tr>\n";
  
  // Special notes..
  if ($qastep->special_notes != "") {
    $s .= "<tr class=\"row1\">";
    $s .= "<th class=\"prompt\"><b>Special notes:</b> </th>";
    $s .= "<td>" . $qastep->special_notes . "</td>";
    $s .= "</tr>\n";
  }

  // Assignment..
  if ($have_admin) {
    // Person selector for assignment..
    $F  = "<select size=\"1\" name=\"new_assignment\">";
    $F .= "<option value=\"\">-- select a person --</option>\n";
    $extras = array(
        $project->project_manager => $project->project_manager_fullname,
        $project->qa_mentor => $project->qa_mentor_fullname
        );
    $allocatables = $project->allocated + $extras; 
    foreach ($allocatables as $user_no => $fullname) {
      $F .= "<option value=\"$user_no\"";
      if ($user_no == $qastep->responsible_usr) {
        $F .= " selected";
      }
      $F .= ">$fullname</option>\n";
    }
    $F .= "</select>\n";
    
    $s .= "<tr class=\"row0\">";
    $s .= "<th class=\"prompt\"><b>Assigned to:</b> </th>";
    $s .= "<td>" . $F . "</td>";
    $s .= "</tr>\n";
    
    $assignment = $qastep->assigned();
    if ($assignment !== false) {
      $s .= "<tr class=\"row0\">";
      $s .= "<th>&nbsp;</th>";
      $s .= "<td>"
          . "<b>Assigned " . datetime_to_displaydate(NICE_FULLDATETIME, $assignment["datetime"]) . "</b>"
          . "</td>";
      $s .= "</tr>\n";
    }
    
    $s .= "<tr class=\"row0\">";
    $s .= "<th>&nbsp;</th>";
    $s .= "<td style=\"padding-right:30px\">"
            . "<p><i>Assigning a person to "
            . "a QA Step makes them responsible for delivering it. QAMS will also send "
            . "an e-mail to them, containing all of the relevant details.</i></p>"
            . "</td>";
    $s .= "</tr>\n";
    
    // Empty text area for assignment notes..
    $F  = "<textarea name=\"assignment_covernotes\" style=\"width:400px;height:150px\">";
    $F .= "</textarea>";
    
    $s .= "<tr class=\"row0\">";
    $s .= "<th class=\"prompt\"><b>Assignment notes:</b> </th>";
    $s .= "<td>" . $F . "</td>";
    $s .= "</tr>\n";
    $s .= "<tr class=\"row0\">";
    $s .= "<th>&nbsp;</th>";
    $s .= "<td style=\"padding-right:30px\">"
            . "<p><i>If you changed the above "
            . "assignee, any notes entered above will be e-mailed to them along with the "
            . "usual details.</i></p>"
            . "</td>";
    $s .= "</tr>\n";
  }
  else {
    $assigned = $qastep->responsible_fullname;
    $s .= "<tr class=\"row0\">";
    $s .= "<th class=\"prompt\"><b>Assigned to:</b> </th>";
    $s .= "<td>" . ($assigned != "" ? $assigned : "(nobody)") . "</td>";
    $s .= "</tr>\n";
  }

  // Overall status..
  $status = qa_status_coloured($qastep->overall_approval_status());
  $s .= "<tr class=\"row1\">";
  $s .= "<th class=\"prompt\"><b>Current overall status:</b> </th>";
  $s .= "<td>" . $status . "</td>";
  $s .= "</tr>\n";

  // Required approvals list..
  $s .= "<tr class=\"row1\">";
  $s .= "<th class=\"prompt\"><b>Current approval statuses:</b> </th>";
  $s .= "<td>" . $qastep->render_approval_types($have_admin) . "</td>";
  $s .= "</tr>\n";
  
  // Approvals being sought..
  $cnt = 0;
  $approvals = $qastep->approvals();
  $sought = array();
  foreach ($approvals as $ap_type_id => $approval) {
    if (($approval->approval_status == "p" || $approval->approval_status == "")
      && $approval->assigned_datetime != ""
    ) {
      
      $days  = "<span style=\"color:orange\" title=\"Days since approval was requested\">";
      $days .= "(" . $approval->since_assignment_days() . " days)";
      $days .= "</span>";
      
      $seek = $approval->qa_approval_type_desc . "&nbsp;sought";
      if ($approval->assigned_fullname != "") {
        $seek .= "&nbsp;from&nbsp;" . $approval->assigned_fullname;
      }
      $seek .= "&nbsp;" . datetime_to_displaydate(NICE_FULLDATETIME, $approval->assigned_datetime);
      $seek .= "&nbsp;$days";
      
      $sought[] = $seek;
    }
  }
  if (count($sought) == 0) {
    $appseek_status = "(none)";
  }
  else {
    $appseek_status = implode("<br>", $sought) . "<br>";
  }
  if ($have_admin) {
    $href  = "/qams-request-approval.php";
    $href .= "?project_id=$qastep->project_id";
    $href .= "&step_id=$qastep->qa_step_id";
    $label = "[Seek]";
    $title = "Seek an approval from someone for this QA step";
    $link  = "<a href=\"$href\" title=\"$title\">$label</a>";
  }
  else {
    $link = "";
  } 
  $s .= "<tr class=\"row1\">";
  $s .= "<th class=\"prompt\"><b>Approvals being sought:</b> </th>";
  $s .= "<td>" . "$appseek_status $link" . "</td>";
  $s .= "</tr>\n";

  // Approvals history..
  if ($view_history == "yes") {
    $s .= "<tr>";
    $s .= "<td colspan=\"2\" align=\"center\" style=\"text-align:center;vertical-align:bottom;height:50px\"><b>Approvals history</b></td>";
    $s .= "</tr>\n";
  
    $s .= "<tr class=\"row0\">";
    $s .= "<td colspan=\"2\" align=\"center\">" . $qastep->render_approvals_history() . "</td>";
    $s .= "</tr>\n";
  }
  else {
    $href  = $REQUEST_URI;
    $href .= "?project_id=$qastep->project_id";
    $href .= "&step_id=$qastep->qa_step_id";
    $href .= "&view_history=yes";
    $label = "View Approvals History";
    $title = "View approvals history for this QA step";
    $link = "<a href=\"$href\" title=\"$title\">$label</a>";
    $s .= "<tr>";
    $s .= "<td colspan=\"2\" align=\"center\" style=\"text-align:center;vertical-align:bottom;height:50px\">";
    $s .= $link;
    $s .= "</td>";
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

// New project object to work with..
$project = new qa_project($project_id);

// Administrator status..
$have_admin = $project->qa_process->have_admin; 

// Viewing approvals history?
if (!isset($view_history)) {
  $view_history = "no";
}

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
  if (isset($submit) && $submit == "Update") {
    if ($project->request_id > 0) {
      // Possible approval type updates..
      if ($project->POSTprocess_approval_updates($qastep->qa_step_id)) {
        $project->get_project();
      }
      // Possible assignment/re-assignment..
      if (isset($new_assignment)) {
        if ($new_assignment != $qastep->responsible_usr) {

          // First, save the assignment..          
          $qastep->responsible_usr = ($new_assignment != "" ? $new_assignment : NULLVALUE);
          $qastep->responsible_datetime = timestamp_to_datetime();
          $qastep->save();
          // Save current phase to project record..
          $q  = "UPDATE request_project SET";
          $q .= " qa_phase='$qastep->qa_phase'";
          $q .= " WHERE request_id=$qastep->project_id";
          $qry = new PgQuery($q);
          $ok = $qry->Exec("qams-step-detail.php::assignment");
          
          // Re-read to get new user name and email..
          $qastep->get($project->request_id, $qastep->qa_step_id);

          // If we are assigning someone, then let everyone know. Otherwise a null
          // assignment is de-assigning somebody, which we keep quiet about..
          if ($new_assignment != "") {          
            $qry = new PgQuery("SELECT email, fullname FROM usr WHERE user_no=$new_assignment");
            if ($qry->Exec("qams-step-detail.php::new_assignment") && $qry->rows > 0) {
              $row = $qry->Fetch();
              // Assignee email..
              $assignee_email = $row->email;
              $assignee_fullname = $row->fullname;
              $subject = "QAMS Assignment: $qastep->qa_step_desc [$project->system_id/$project->username]";
              $recipients = array($assignee_email => $assignee_fullname);
          
              // Assemble body for assignee..
              $s .= "<p>Congratulations! You have been chosen from thousands of eager applicants ";
              $s .= "to take ownership of this quality assurance step, and deliver it through ";
              $s .= "the approval process.</p>";
  
              $s .= "<p>The step you are charged with getting through approval is known as '" . $qastep->qa_step_desc . "'</p>";
              if ($qastep->qa_step_notes != "") {
                $s .= "<p>Some notes on what reviewers will be looking for when approving this step: ";
                $s .= $qastep->qa_step_notes . "</p>";
              }
              
              if ($qastep->qa_document_title != "") {
                $s .= "<p>For this step you have to produce a document, the '" . $qastep->qa_document_title . "'. ";
                if ($qastep->qa_document_desc != "") {
                  $s .= $qastep->qa_document_desc;
                }
                $s .= " ";
                $s .= "<b><u>NB: You should attach any versions of this document that you produce, to the ";
                $s .= "Work Request given below</u></b>.</p>";
                
                // Look up templates and examples..
                $qastep->get_documents();
                $docurls = array();
                if ($qastep->path_to_template != "") {
                  $docurls[$qastep->path_to_template] = "Template for $qastep->qa_document_title";
                  $template = true;
                }
                if ($qastep->path_to_example != "") {
                  $docurls[$qastep->path_to_example] = "Example for $qastep->qa_document_title";
                  $example = true;
                }
                if ($template || $example) {
                  $s .= "<p>To help you in this task, QAMS has provided you with ";
                  if ($template && $example) {
                    $s .= "links to a template and an example document. The example is a filled-out ";
                    $s .= "and finished item just to look through for ideas on what to put in. ";
                    $s .= "The template is an empty document for you to take as your starting ";
                    $s .= "point.";
                  }
                  elseif ($template) {
                    $s .= "a link to a template document for you to take as your starting point.";
                  }
                  else {
                    $s .= "a link to an example document for you to take as your starting point. ";
                    $s .= "You will have to strip out content which is not applicable.";
                  }
                  $s .= "</p>";
                  
                  // Insert link(s)..
                  foreach ($docurls as $href => $label) {
                    // Make sure it's a good clickable link..
                    if (!strstr("http", $href)) {
                      if (substr($href, 0, 1) != "/") {
                        $href = "/$href";
                      }
                      $href = $GLOBALS[base_dns] . $href;
                    }
                    $link  = "<a href=\"$href\" title=\"Click to download $label\">$href</a>";
                    $s .= "<p>Click the below link to download $label:<br>";
                    $s .= "&nbsp;&nbsp;$link";
                    $s .= "</p>";
                  }
                }
              }
              
              // Covering notes..
              if ($assignment_covernotes != "") {
                $s .= "<p><b>Specific Notes:</b><br>";
                $s .= $assignment_covernotes . "</p>";
              }
            
              // WRMS access to step work request..
              $s .= "<p>The Work Request associated with this step is:<br>"; 
              $href = $GLOBALS[base_dns] . "/wr.php?request_id=$qastep->request_id";
              $wrlink = "<a href=\"$href\">$href</a>";
              $s .= "&nbsp;&nbsp;" . $wrlink . "</p>";
            
              $project->QAMSNotifyEmail("QAMS Assignment", $s, $subject, $recipients);
              
              // Other emails to let everyone know what's going on..
              $recipients = $project->GetRecipients();
              if (isset($recipients[$assignee_email])) {
                unset($recipients[$assignee_email]);
              }
              $s  = "<p>$assignee_fullname has been assigned to the Quality Assurance Step ";
              $s .= "'$qastep->qa_step_desc' on this project. The summary link for the step ";
              $s .= "itself is provided here:<br>";
              $href = $GLOBALS[base_dns] . "/qams-step-detail.php?project_id=$qastep->project_id&step_id=$qastep->qa_step_id";
              $stlink = "<a href=\"$href\">$href</a>";
              $s .= "&nbsp;&nbsp;" . $stlink . "</p>";
              $project->QAMSNotifyEmail("QAMS Activity Notice", $s, $subject, $recipients);
            }
          }
        } // null assignment
      } // assignment changed 
    }
  }
  
  // Main content..    
  $s = "";
  $ef = new EntryForm($RESPONSE->requested, $project, ($have_admin ? 1 : 0));
  $ef->NoHelp();
  
  if ($ef->editmode) {
    $s .= $ef->StartForm();
    $s .= $ef->HiddenField( "qa_action", "$qa_action" );
    if ( $project->request_id > 0 ) {
      $s .= $ef->HiddenField( "project_id", $project->request_id );
      $s .= $ef->HiddenField( "step_id", "$qastep->qa_step_id" );
    }
  }
  // Start main table..    
  $s .= "<table width=\"100%\" class=\"data\" cellspacing=\"0\" cellpadding=\"0\">\n";

  $s .= $ef->BreakLine("Quality Assurance Step");
  $s .= "<tr><td height=\"15\" colspan=\"2\">&nbsp;</td></tr>";

  if ($this->project_manager_fullname != "") {
    $href = "/user.php?user_no=$this->project_manager";
    $link = "<a href=\"$href\">" . $this->project_manager_fullname . "</a>"; 
    $s .= "<tr><td colspan=\"2\"><b>Project Manager:</b> " . $link . "</td></tr>";
  } 
  if ($this->qa_mentor_fullname != "") {
    $href = "/user.php?user_no=$this->qa_mentor";
    $link = "<a href=\"$href\">" . $this->qa_mentor_fullname . "</a>"; 
    $s .= "<tr><td colspan=\"2\"><b>QA Mentor:</b> " . $link . "</td></tr>";
  } 
  
  $s .= "<tr><td height=\"15\" colspan=\"2\">&nbsp;</td></tr>";
  $s .= "<tr><td colspan=\"2\">" . ContentForm($project, $qastep, $view_history) . "</td></tr>";
  $s .= "<tr><td height=\"15\" colspan=\"2\">&nbsp;</td></tr>";
  $s .= "</table>\n";
  
  if ( $ef->editmode ) {
    $s .= $ef->SubmitButton( "submit", "Update" );
    $s .= $ef->EndForm();
  }
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
require_once("headers.php");

echo $content;

include("footers.php");
?>
