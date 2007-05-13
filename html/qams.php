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
/* Filename:    index.php                                               */
/* Author:      Paul Waite                                              */
/* Description: The main website index page/default page                */
/*                                                                      */
/* ******************************************************************** */
require_once("always.php");
require_once("authorisation-page.php");

$session->LoginRequired();

require_once("maintenance-page.php");

$title = "QAMS";

// -----------------------------------------------------------------------
include_once("qams-project-defs.php");

// FILTERING..
if (!isset($filter)) {
  $filter = "my";
}

// SORT ORDER..
if (!isset($project_sortby)) {
  $project_sortby = "date";
}

// Some processing actions..
if (isset($qa_action)) {
  switch ($qa_action) {
    case "delete":
      if ($session->AllowedTo("QA")) {
        if (isset($request_id)) {
          $project = new qa_project($request_id);
          $project->delete_project();
        }
      }
      break;
  }
}

// Sorting options..
if (isset($project_sortby)) {
  switch ($project_sortby) {
    case "date":
      break;
    case "system":
      break;
  }
}

// This grabs a set of appropriate projects..
$projectset = new qa_project_set();
$projectset->get_projects($filter);

// PROJECT LIST
$s = "<table cellpadding=\"0\" cellspacing=\"2\" border=\"0\" width=\"100%\">\n";
switch($projectset->filtermode) {
  case "user":
    $filterdesc = "(projects involving $projectset->filterdesc)";
    break;
  case "recent":
    $filterdesc = "(recent projects)";
    break;
  case "my":
    $filterdesc = "(my projects)";
    break;
} // switch

// Extra column for QA person..
$ncols = 6;
if ($session->AllowedTo("QA")) {
 $ncols = 7;
}
$s .= "<tr>";
$s .= "<td colspan=\"$ncols\">" . $projectset->project_count() . " projects found $filterdesc" . "</td>";
$s .= "</tr>\n";

// Headings..
$s .= "<tr>";
$s .= "<th class=\"cols\">PR&nbsp;#</th>";
$s .= "<th class=\"cols\">Project For</th>";
$s .= "<th class=\"cols\">Project On</th>";
$s .= "<th class=\"cols\">Description</th>";
$s .= "<th class=\"cols\">W/R Status</th>";
$s .= "<th class=\"cols\">QA Phase</th>";
if ($session->AllowedTo("QA")) {
  $s .= "<th class=\"cols\">&nbsp;</th>";
}
$s .= "</tr>\n";

// Projects listing..
if ($projectset->project_count() > 0) {
  $rowclass = "row1";
  foreach ($projectset->projects as $request_id => $project) {
    $project_on = $session->FormattedDate($project->request_on, 'timestamp' );

    // Project href link..
    $href = "/qams-project.php?request_id=$request_id";
    $projectlink = "<a href=\"$href\">$request_id</a>";

    $project_brief = $project->brief;
    if ($project_brief == "") {
      $project_brief = "(no description)";
    }
    $rowclass = ($rowclass == "row0") ? "row1" : "row0";
    $s .= "<tr class=\"$rowclass\">";
    $s .= "<td class=\"sml\">" . $projectlink . "</td>";
    $s .= "<td class=\"sml\">" . $project->fullname . "</td>";
    $s .= "<td class=\"sml\">" . $project_on . "</td>";
    $s .= "<td class=\"sml\">" . $project_brief . "</td>";
    $s .= "<td class=\"sml\">" . $project->status_desc . "</td>";
    $s .= "<td class=\"sml\" align=\"center\">" . $project->qa_phase . "</td>";
    if ($session->AllowedTo("QA")) {
      $delicon = "<img src=\"/catimg/delete.png\" border=\"0\" title=\"Delete this project\">";
      $href = "$PHP_SELF?request_id=$request_id&qa_action=delete&filter=$filter";
      $s .= "<td class=\"sml\"><a href=\"$href\">$delicon</a></td>";
    }
    // End row..
    $s .= "</tr>\n";
  } // foreach
}
$s .= "</table>\n";

// Store results for later on..
$PROJECTS_LIST = $s;

// #######################################################################
// OUTPUT..
// These go last of all, since they are content
// replacements within replacements..
require_once("top-menu-bar.php");
require_once("page-header.php");

echo $PROJECTS_LIST;

include("page-footer.php");
?>