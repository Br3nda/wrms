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
/* Filename:    qams-project.php                                        */
/* Author:      Paul Waite                                              */
/* Description: QAMS project maintain/view                              */
/*                                                                      */
/* ******************************************************************** */
require_once("always.php");
require_once("authorisation-page.php");

$session->LoginRequired();

require_once("maintenance-page.php");

$title = "QAMS Project";

// -----------------------------------------------------------------------------------------------
// MAIN CONTENT
include_once("qams-project-defs.php");

// Initialise request identity..
if (!isset($request_id)) {
  $request_id = 0;
}

// New project object to work with..
$project = new qa_project($request_id);

// Overall mode of rendering for this project..
if (!isset($edit)) {
  $edit = 0;
}

switch ($qa_action) {
  case "reapprove":
    if (isset($project->qa_process->qa_steps[$step_id])) {
      $qastep = $project->qa_process->qa_steps[$step_id];
      $qastep->reapprove();
      if ($qastep->overall_approval_status() == "") {
        // Let everyone know what's going on..
        $href  = $GLOBALS[base_dns] . "/qams-step-detail.php"; 
        $href .= "?project_id=$project->request_id";
        $href .= "&step_id=$qastep->qa_step_id";
        $link = "<a href=\"$href\">$href</a>";
        
        $subject = "QAMS Re-Approval: $qastep->qa_step_desc [$project->system_id/$session->username]";
        $s  = "<p>The QA Step '$qastep->qa_step_desc' has now been 'Unapproved' so that it can ";
        $s .= "go through the approval process once again.</p>";
        $s .= "<p>The summary link for this step is provided here:<br>";
        $s .= "&nbsp;&nbsp;" . $link . "</p>";
        $project->QAMSNotifyEmail("QAMS Activity Notice", $s, $subject);
      }
    }
    // drop thru to view QA plan..
  case "qaplan":
    $content = $project->RenderQAPlan();
    break;
  default:
    $content = $project->project_details($edit);
} // switch

// -----------------------------------------------------------------------------------------------
// DELIVER..
require_once("top-menu-bar.php");
require_once("page-header.php");

echo $content;

include("page-footer.php");
?>
