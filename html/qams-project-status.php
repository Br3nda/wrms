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
/* Filename:    qams-project-status.php                                 */
/* Author:      Paul Waite                                              */
/* Description: QAMS project status report page                         */
/*                                                                      */
/* ******************************************************************** */
require_once("always.php");
require_once("authorisation-page.php");

$session->LoginRequired();

require_once("maintenance-page.php");

$title = "QAMS Project Status";

// -----------------------------------------------------------------------------------------------
include_once("qams-project-defs.php");

// -----------------------------------------------------------------------
// FUNCTIONS
function ContentForm(&$project) {
  global $session;

  $have_admin = $project->qa_process->have_admin;

  $s = "";
  $s .= "<table cellspacing=\"2\" cellpadding=\"2\" width=\"100%\">\n";

  $s .= "<tr class=\"row0\">";
  $s .= "<td colspan=\"2\" style=\"padding-left:20px;padding-right:50px\">"
      . "<p>This screen provides you with a snapshot report on the current QA status of the project. "
      . "The main area of interest here is the 'Steps Showing Activity' section, which lists those "
      . "quality assurance steps which have shown some activity - ie. have been assigned, have had "
      . "approvals requested, or approval decisions posted.</p>"
      . "<p>The list is presented in ascending date-time order, so the steps at the bottom are the "
      . "ones showing the most recent activity. Within each step we have a summary of the activity "
      . "itself, including who was assigned and when, followed by a list of approvals which have "
      . "been active.</p>"
      . "<p>At the bottom, an exception report is provided. This deals with approval ordering, in "
      . "particular the 'serious' issue of approving items before the QA Plan is approved. The "
      . "others are just warnings arising from approval of items before everything in the phase "
      . "preceding it has been approved.</p>"
      . "</td>";
  $s .= "</tr>\n";

  // Vertical spacer
  $s .= "<tr class=\"row0\">";
  $s .= "<td colspan=\"2\" height=\"15\">&nbsp;</td>";
  $s .= "</tr>\n";

  // Project details: brief desc, PM, QA mentor, Current phase..
  $s .= "<tr class=\"row1\">";
  $s .= "<th width=\"30%\" class=\"prompt\"><b>Project:</b> </th>";
  $s .= "<td width=\"70%\">$project->brief</td>";
  $s .= "</tr>\n";
  $s .= "<tr class=\"row1\">";
  $s .= "<th width=\"30%\" class=\"prompt\"><b>Description:</b> </th>";
  $s .= "<td width=\"70%\"><p>$project->detailed</p></td>";
  $s .= "</tr>\n";
  if ($project->project_manager_fullname != "") {
    $pmlink  = "<a href=\"/user.php?user_no=$project->project_manager\">";
    $pmlink .= $project->project_manager_fullname;
    $pmlink .= "</a>";
    $s .= "<tr class=\"row1\">";
    $s .= "<th width=\"30%\" class=\"prompt\"><b>Project Manager:</b> </th>";
    $s .= "<td width=\"70%\">$pmlink</td>";
    $s .= "</tr>\n";
  }
  if ($project->qa_mentor_fullname != "") {
    $qalink  = "<a href=\"/user.php?user_no=$project->qa_mentor\">";
    $qalink .= $project->qa_mentor_fullname;
    $qalink .= "</a>";
    $s .= "<tr class=\"row1\">";
    $s .= "<th width=\"30%\" class=\"prompt\"><b>QA Mentor:</b> </th>";
    $s .= "<td width=\"70%\">$qalink</td>";
    $s .= "</tr>\n";
  }
  $s .= "<tr class=\"row1\">";
  $s .= "<th width=\"30%\" class=\"prompt\"><b>Last activity phase:</b> </th>";
  $s .= "<td width=\"70%\">" . ($project->qa_phase != "" ? $project->qa_phase : "Not started") . "</td>";
  $s .= "</tr>\n";
  $s .= "<tr class=\"row1\">";
  $s .= "<th width=\"30%\" class=\"prompt\"><b>QA Plan Approved:</b> </th>";
  $s .= "<td width=\"70%\">" . ($project->qa_process->QAPlanApproved() ? "Yes" : "No") . "</td>";
  $s .= "</tr>\n";

  // Vertical spacer
  $s .= "<tr class=\"row0\">";
  $s .= "<td colspan=\"2\" height=\"15\">&nbsp;</td>";
  $s .= "</tr>\n";

  // Now grab the project step details in the orderings we need..
  $steps_reportrec = array();
  $steps_active = array();
  foreach ($project->qa_process->qa_steps as $step_id => $qastep) {
    // Acquire approvals data..
    $qastep->get_approvals();
    $step_status = $qastep->overall_approval_status();

    // Initialise data arrays..
    $stepdata = array();
    $appdata = array();
    $app_inactive = array();

    // Summary info for the step..
    $stepdata["desc"] = $qastep->qa_step_desc;
    $stepdata["status"] = $step_status;
    $stepdata["responsible_fullname"] = $qastep->responsible_fullname;
    $stepdata["responsible_ts"] = strtotime($qastep->responsible_datetime);

    // More stuff for steps which are active..
    if ($step_status != "" || $qastep->responsible_fullname != "") {
      $latest_activity_ts = $stepdata["responsible_ts"];
      // These approvals should already be ordered by ascending datetime..
      foreach ($qastep->approvals as $ap_type_id => $approval) {
        // Ascertain the latest activity timestamp..
        switch ($approval->approval_status) {
          case "y": $dt = $approval->approval_datetime; break;
          default: $dt = $approval->assigned_datetime;
        } // switch
        $ts = strtotime($dt);
        if ($ts > $latest_activity_ts) {
          $latest_activity_ts = $ts;
        }
        // Store our approval data for report..
        $apdesc = $approval->qa_approval_type_desc;
        $appdata[$apdesc]["status"] = $approval->approval_status;
        $appdata[$apdesc]["assigned_fullname"] = $approval->assigned_fullname;
        $appdata[$apdesc]["assigned_dt"] = $approval->assigned_datetime;
        $appdata[$apdesc]["assigned_days"] = $approval->since_assignment_days();
        $appdata[$apdesc]["approval_fullname"] = $approval->approval_fullname;
        $appdata[$apdesc]["approval_dt"] = $approval->approval_datetime;
        $appdata[$apdesc]["comment"] = $approval->comment;
      } // foreach step approval

      // Make a note of outstanding approvals required for this step..
      foreach ($qastep->approvals_required() as $ap_type_id => $apdesc) {
        if (!isset($appdata[$apdesc])) {
          $app_inactive[$appreqd->qa_approval_type_id] = $apdesc;
        }
      } // foreach

      // Active steps. Can be ordered by timestamp later..
      $steps_active[$step_id] = $latest_activity_ts;
    }

    // Store any step approvals data..
    $stepdata["approvals"] = $appdata;

    // Store list of approvals still showing no activity..
    $stepdata["approvals_inactive"] = $app_inactive;

    // Save the report record..
    $steps_reportrec[$step_id] = $stepdata;

  } // foreach step

  // The report goes in its own table..
  $d  = "";
  $d .= "<table cellspacing=\"2\" cellpadding=\"2\" width=\"100%\">\n";

  // Display active steps summary..
  if (count($steps_active) > 0) {
    $d .= "<tr><td class=\"cols\" colspan=\"3\">Steps Showing Activity</td></tr>\n";
    $d .= "<tr>";
    $d .= "<th width=\"60%\" style=\"text-align:left\">Step</th>";
    $d .= "<th width=\"20%\" style=\"text-align:left\">Last changed</th>";
    $d .= "<th width=\"20%\" style=\"text-align:left\">Current status</th>";
    $d .= "<tr>\n";
    asort($steps_active);
    foreach ($steps_active as $step_id => $ts) {
      $stepdata = $steps_reportrec[$step_id];

      // Step detail link..
      $href  = "/qams-step-detail.php";
      $href .= "?project_id=$project->request_id";
      $href .= "&step_id=$step_id";
      $label = "[detail]";
      $title = "Go to the detail screen for this QA step (new window)";
      $link = "<a href=\"$href\" title=\"$title\" target=\"_new\">$label</a>";

      $d .= "<tr>";
      $d .= "<td style=\"border-top:solid 1px grey\"><b>" . $stepdata["desc"] . "</b>&nbsp;$link</td>";
      $d .= "<td style=\"border-top:solid 1px grey\">" . $session->FormattedDate( date('Y-m-d H:i:s',$ts), timestamp) . "</td>";
      $d .= "<td style=\"border-top:solid 1px grey\">" . qa_status_coloured($stepdata["status"]) . "</td>";
      $d .= "<tr>\n";

      // Step assignment..
      $a = "";
      if ($stepdata["responsible_fullname"] != "") {
        $a .= $stepdata["responsible_fullname"] . " was assigned to this step on ";
        $a .= $session->FormattedDate($stepdata["responsible_ts"], 'timestamp');
        $a .= ". ";
      }

      // Approvals activity..
      $approvals = $stepdata["approvals"];
      if (count($approvals) > 0) {
        foreach ($approvals as $apdesc => $appdata) {
          $a .= "<p>$apdesc: ";
          if ($appdata["assigned_fullname"] != "") {
            $a .= "Approval sought from " . $appdata["assigned_fullname"] . " on ";
            $a .= $session->FormattedDate($appdata["assigned_dt"],'timestamp') . ". ";
          }
          if ($appdata["approval_fullname"] != "") {
            $a .= "The relevant approval was then posted on ";
            $a .= $session->FormattedDate($appdata["approval_dt"],'timestamp');
            if ($appdata["assigned_fullname"] != $appdata["approval_fullname"]) {
              $a .= " by " . $appdata["approval_fullname"];
              $a .= " <span style=\"color:red\">in override mode</span>";
            }
            $a .= ", setting the status to " . qa_status_coloured($appdata["status"]);
          }
          else {
            $a .= "<span style=\"color:orange\" title=\"Days since approval was requested\">";
            $a .= "(" . $appdata["assigned_days"] . " days)";
            $a .= "</span>";
          }
          if ($appdata["comment"] != "") {
            $a .= " with the following comments:&nbsp;";
            $a .= "<i>" . $appdata["comment"] . "</i>";
          }
          $a .= "</p>\n";
        } // foreach approval
      }
      else {
        $a = "<p>No approvals posted yet.</p>";
      }

      // Approvals inactive status..
      $app_inactive = $stepdata["approvals_inactive"];
      if (count($app_inactive) > 0) {
        if ($have_admin) {
          $href  = "/qams-request-approval.php";
          $href .= "?project_id=$qastep->project_id";
          $href .= "&step_id=$qastep->qa_step_id";
          $label = "[Seek]";
          $title = "Seek an approval from someone for this QA step";
          $link  = "&nbsp;&nbsp;<a href=\"$href\" title=\"$title\">$label</a>";
        }
        else {
          $link = "";
        }
        $a .= "<p><span style=\"color:orange\">Approvals showing no activity:</span> "
            . implode(", ", array_values($app_inactive))
            . $link
            . "</p>";
      }

      // Stuff into main table..
      $d .= "<tr>";
      $d .= "<td colspan=\"3\" style=\"padding-left:50px;padding-right:30px\">" . $a . "</td>";
      $d .= "<tr>\n";
    }
  }

  // Display inactive steps summary..
  $d .= "<tr><td class=\"cols\" colspan=\"3\">Steps not yet started</td></tr>\n";
  foreach ($steps_reportrec as $step_id => $stepdata) {
    if (in_array($step_id, array_keys($steps_active))) {
      continue;
    }
    else {
      $d .= "<tr>";
      $d .= "<td colspan=\"3\">" . $stepdata["desc"] . "</td>";
      $d .= "<tr>\n";
    }
  }

  // Warnings - here we are looking for major approvals which have been
  // acquired out of prescribed order..
  $exceptions = array();
  $d .= "<tr><td class=\"cols\" colspan=\"3\">Exception Report</td></tr>\n";

  // CHECK #1: Check for any steps approved before QA Plan..
  $steps = array_keys($project->qa_process->qa_steps);
  $exceptions["serious"] = order_check(
                            $project,
                            array(STEP_ID_QAPLAN),
                            $steps
                            );

  // CHECK #2: Out-of-Order approvals..
  $exceptions["phases"] = array();
  $steps = array();
  $presteps = array();
  $q  = "SELECT *";
  $q .= "  FROM qa_step s, qa_phase p";
  $q .= " WHERE p.qa_phase_desc <> 'Concept'";
  $q .= "   AND s.qa_phase=p.qa_phase";
  $q .= " ORDER BY p.qa_phase_order, s.qa_step_order";
  $qry = new PGQuery($q);
  if ($qry->Exec("qams-project-status.php approval order check") && $qry->rows > 0) {
    while($row = $qry->Fetch()) {
      $phase = $row->qa_phase;
      if ($phase != $last_phase) {
        if (count($presteps) > 0 && count($steps) > 0) {
          $excepts = order_check($project, $presteps, $steps);
          if (count($excepts) > 0) {
            $exceptions["phases"][] = "<i>$last_phase Phase</i>";
            $exceptions["phases"] = array_merge($exceptions["phases"], $excepts);
            $exceptions["phases"][] = "&nbsp;";
          }
        }
        // Check next phase against all steps which go before..
        $presteps = array_merge($presteps, $steps);
        $steps = array();
        $last_phase = $phase;
      }
      // Filling steps array..
      $steps[] = $row->qa_step_id;
    } // while

    // And do the final pair of phases..
    if (count($presteps) > 0 && count($steps) > 0) {
      $excepts = order_check($project, $presteps, $steps);
      if (count($excepts) > 0) {
        $exceptions["phases"][] = "<i>$last_phase Phase</i>";
        $exceptions["phases"] = array_merge($exceptions["phases"], $excepts);
        $exceptions["phases"][] = "&nbsp;";
      }
    }
  }

  // Now produce the output, if any exists..
  if (count($exceptions["serious"]) > 0) {
    $d .= "<tr><td colspan=\"3\"><b>QA Plan Exceptions:</b></td></tr>\n";
    foreach ($exceptions["serious"] as $exceptline) {
      $d .= "<tr><td colspan=\"3\"><span style=\"color:red\">$exceptline</span></td></tr>\n";
    }
  }
  if (count($exceptions["phases"]) > 0) {
    $d .= "<tr><td colspan=\"3\" height=\"15\">&nbsp;</td></tr>\n";
    $d .= "<tr><td colspan=\"3\"><b>Out-of-Order Approvals:</b></td></tr>\n";
    foreach ($exceptions["phases"] as $exceptline) {
      $d .= "<tr><td colspan=\"3\">$exceptline</td></tr>\n";
    }
  }

  $d .= "</table>\n";

  // Put report table into main table cell/row..
  $s .= "<tr class=\"row0\">";
  $s .= "<td colspan=\"2\">" . $d . "</td>\n";
  $s .= "</tr>\n";

  // Vertical spacer
  $s .= "<tr class=\"row0\">";
  $s .= "<td colspan=\"2\" height=\"25\">&nbsp;</td>";
  $s .= "</tr>\n";

  $s .= "</table>\n";
  return $s;

} // ContentForm

// -----------------------------------------------------------------------------------------------
/**
 * Check that the given set of QA Steps have been approved AFTER the
 * given list of pre-requisite QA Steps.
 * @param object $project The project containing the QA steps
 * @param array $presteps Array of Step ID's of steps which need to be approved first
 * @param array $steps Array of Step ID's which we are checking
 * @return array List of exceptions discovered, if any
 */
function order_check(&$project, $presteps, $steps) {
  $exceptions = array();
  foreach ($presteps as $pre_step_id) {
    if (isset($project->qa_process->qa_steps[$pre_step_id])) {
      $pre_qastep = $project->qa_process->qa_steps[$pre_step_id];
      $pre_qadesc = $pre_qastep->qa_step_desc;
      $pre_ts = $pre_qastep->first_approved_timestamp();
      foreach ($steps as $step_id) {
        if ($step_id != $pre_step_id) {
          if (isset($project->qa_process->qa_steps[$step_id])) {
            $qastep = $project->qa_process->qa_steps[$step_id];
            $ts = $qastep->first_approved_timestamp();
            if ($ts > 0) {
              if ($pre_ts == 0) {
                $exceptions[] = $qastep->qa_step_desc . " was first approved with '$pre_qadesc' unapproved.";
              }
              elseif ($pre_ts > 0 && $ts < $pre_ts) {
                $diffdays = days_diff_ts($pre_ts, $ts);
                $exceptions[] = $qastep->qa_step_desc . " was first approved $diffdays before '$pre_qadesc' was.";
              }
            }
          }
        }
      } // foreach
    }
  } // foreach presteps

  // Return our exception lines..
  return $exceptions;

} // order_check

// -----------------------------------------------------------------------------------------------
// MAIN CONTENT

// Must haves..
if (!isset($project_id)) {
  exit;
}

// New project object to work with..
$project = new qa_project($project_id);
$have_admin = $project->qa_process->have_admin;

// Start main table..
$s = "";
$s .= "<table width=\"100%\" class=\"data\" cellspacing=\"0\" cellpadding=\"0\">\n";
$s .= "<tr><th class=\"ph\" colspan=\"2\">Project Quality Assurance Status</th></tr>";
$s .= "<tr><td height=\"15\" colspan=\"2\">&nbsp;</td></tr>";
$s .= "<tr><td colspan=\"2\">" . ContentForm($project) . "</td></tr>";
$s .= "<tr><td height=\"15\" colspan=\"2\">&nbsp;</td></tr>";
$s .= "</table>\n";

// -----------------------------------------------------------------------------
// ASSEMBLE CONTENT
$content = $s;

// -----------------------------------------------------------------------------
// DELIVER..
require_once("top-menu-bar.php");
require_once("page-header.php");

echo $content;

include("page-footer.php");
?>
