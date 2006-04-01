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
/* Filename:    qams-refdoc-index.php                                   */
/* Author:      Paul Waite                                              */
/* Description: QAMS reference documents index                          */
/*                                                                      */
/* ******************************************************************** */
require_once("always.php");
require_once("authorisation-page.php");

$session->LoginRequired();

$title = "Quality Assurance Documents";

// -----------------------------------------------------------------------
include_once("qams-project-defs.php");

// -----------------------------------------------------------------------------------------------
// MAIN CONTENT
$s = "";

// DOCUMENTS ASSOCIATED WITH QA STEPS
// Let's see if we can do this bit with a single query..
$q  = "SELECT p.*, s.*, d.*, m.*, md.path_to_template, md.path_to_example";
$q .= " FROM qa_phase p, qa_step s, qa_document d, qa_model m, qa_model_documents md";
$q .= " WHERE s.qa_phase=p.qa_phase";
$q .= "   AND md.qa_model_id=m.qa_model_id";
$q .= "   AND md.qa_document_id=s.qa_document_id";
$q .= "   AND d.qa_document_id=s.qa_document_id";
$q .= " ORDER BY p.qa_phase_order, s.qa_step_order, m.qa_model_order";
$qry = new PgQuery($q);
if ($qry->Exec("qams-refdoc-index.php:get documents") && $qry->rows > 0) {
  while( $row = $qry->Fetch(true) ) {
    $phase = $row["qa_phase_desc"];
    $qa_model_name = $row["qa_model_name"];
    $qa_document_id = $row["qa_document_id"];
    $qa_document_title = $row["qa_document_title"];
    $qa_document_desc = $row["qa_document_desc"];
    $template = $row["path_to_template"];
    $example  = $row["path_to_example"];
    // Only interested if we actually have a URL..
    if ($template != "" || $example != "") {
      $id = "$phase|$qa_document_id";
      $doc[$id] = $qa_document_title;
      $docdesc[$id] = $qa_document_desc;
      if ($template != "") {
        $doc_template[$id][$qa_model_name] = $template;
      }
      if ($example != "") {
        $doc_example[$id][$qa_model_name] = $example;
      }
    }
  } // while
}
          
$ncols = 3;
$RepositoryLink = "<a href=\"/qadoc/\" target=\"_new\">Quality Assurance Document Repository</a>";

// The QA Manual should have been exported as PDF, but just in case..
$ManualExtns = array("pdf", "sxw", "doc");
foreach ($ManualExtns as $extn) {
  $ManualPath = "qadoc/reference/QA_Manual.$extn"; 
  if (file_exists("./$ManualPath")) break;
}

$QAManualLink = "<a href=\"/$ManualPath\" target=\"_new\">Quality Assurance Manual</a>";

// Start main table..    
$s .= "<table width=\"100%\" class=\"data\" cellspacing=\"4\" cellpadding=\"0\">\n";
$s .= "<tr><th colspan=\"$ncols\" class=\"ph\">Quality Assurance Documents</th></tr>\n";
$s .= "<tr><td colspan=\"$ncols\" height=\"15\">&nbsp;</td></tr>\n";
$s .= "<tr><td colspan=\"$ncols\">";
$s .= "<p>This is an index of all the Quality Assurance documents available for download from "
    . "QAMS. These are listed below. The order is by project Phase for all those documents "
    . "which are associated with QA steps. If you wish you can also browse the $RepositoryLink.</p>\n"
    . "<p>For your reference, the $QAManualLink is also available.</p>\n";
    ;
$s .= "</td></tr>\n";
$s .= "<tr><td colspan=\"$ncols\" height=\"15\">&nbsp;</td></tr>\n";

foreach ($doc as $id => $qa_document_title) {
  $bits = explode("|", $id);
  $phase = $bits[0];    
  if ($phase != $last_phase) {
    $s .= "<tr><td colspan=\"$ncols\" height=\"15\">&nbsp;</td></tr>\n";
    $s .= "<tr class=\"cols\">";
    $s .= "<td colspan=\"$ncols\" height=\"20\" valign=\"bottom\" style=\"font-weight:bold;color:white;padding-left:3px\">";
    $s .= strtoupper($phase) . " PHASE DOCUMENTS";
    $s .= "</td>";
    $s .= "</tr>\n";
    $s .= "<tr>";
    $s .= "<th width=\"66%\">&nbsp;</th>";
    $s .= "<th width=\"17%\">Templates</th>";
    $s .= "<th width=\"17%\">Examples</th>";
    $s .= "</tr>";
    $last_phase = $phase;
  }

  // Document title and description..
  $s .= "<tr>";
  $s .= "<td valign=\"top\" style=\"padding-right:20px\"><b>" . $doc[$id] . "</b><br>";
  $s .= $docdesc[$id] . "</td>";

  // Template document links..
  $s .= "<td valign=\"top\">";
  if (isset($doc_template[$id])) {
    foreach ($doc_template[$id] as $model_name => $template) {
      if ($template != "") {
        $doctitle = "Template for " . $doc[$id] . " for " . strtolower($model_name) . " projects";
        $s .= "<a href=\"$template\" title=\"$doctitle\">$model_name model</a>";
      }
    }
  }
  $s .= "</td>";
    
  // Example document links..
  $s .= "<td valign=\"top\">";
  if (isset($doc_example[$id])) {
    foreach ($doc_example[$id] as $model_name => $example) {
      if ($example != "") {
        $doctitle = "Example of " . $doc[$id] . " for " . strtolower($model_name) . " projects";
        $s .= "<a href=\"$example\" title=\"$doctitle\">$model_name model</a>";
      }
    }
  }
  $s .= "</td>";
  $s .= "</tr>\n";
}

// Finish off..
$s .= "<tr><td colspan=\"$ncols\" height=\"15\">&nbsp;</td></tr>";
$s .= "</table>\n";

$content = $s;

// -----------------------------------------------------------------------------
// DELIVER..
require_once("top-menu-bar.php");
require_once("headers.php");

echo $content;

include("footers.php");
?>