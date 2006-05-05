<?php
/************************************************************************/
/* CATALYST Php  Source Code                                            */
/* Copyright (C)2002 Catalyst IT Limited                                */
/*                                                                      */
/* Filename:    project-defs.php                                        */
/* Author:      Paul Waite                                              */
/* Date:        February 2002                                           */
/* Description: Handle projects                                         */
/*                                                                      */
/************************************************************************/
include_once("qams-request-defs.php");
include_once("axyl-datetime-defs.php");
include_once("DataEntry.php");
include_once("DataUpdate.php");

/**
 * Step IDs: the unique identifiers for the QA steps are fixed, and so
 * some of the ost important ones are provided here as defines.
 */
define("STEP_ID_QAPLAN", 1);
define("STEP_ID_CONCEPTDOC", 3);
define("STEP_ID_FUNCSPEC", 4);
define("STEP_ID_PRELIMDESIGN", 5);
define("STEP_ID_SPEC", 8);
define("STEP_ID_DESIGN", 9);
define("STEP_ID_PROJPLAN", 11);
define("STEP_ID_MAINT_MANUAL", 15);
define("STEP_ID_ACCEPT_TESTS", 25);
define("STEP_ID_MAINTPLAN", 27);
define("STEP_ID_POSTREVIEW", 28);

// Prefix for building page URLs..
$URL_PREFIX = ($_SERVER["HTTPS"] != "" ? "https" : "http") . "://"
            . $_SERVER["HTTP_HOST"];
// -----------------------------------------------------------------------
/**
 * This class is a container for a set of projects.
 */
class qa_project_set {
  /** The set of projects. */
  var $projects = array();
  /** Filtering mode: 'my', 'user', 'recent' */
  var $filtermode = "my";
  /** Filter value. Content depends on mode above */
  var $filtervalue = "";
  /** Descriptive for filter value, eg. full username */
  var $filterdesc = "";
  /** Max projects to fill the set with */
  var $max_projects = 50;
  // .....................................................................
  /**
   * Constructor for our QA project set. Creates an empty set, and sets
   * the maximum number of projects to fill it with on refresh.
   * @param integer $max_projects Max projects to fill the set with
   */
  function qa_project_set($max_projects=50) {
    $this->max_projects  = $max_projects;

  } // qa_project_set
  // .....................................................................
  /**
   * Returns the number of projects we have currently.
   * @return integer The count of projects in our set.
   */
  function project_count() {
    return count($this->projects);
  } // project_count
  // .....................................................................
  /**
   * Acquire projects from the database according to the filter
   * settings we have currently in force. This refreshes the current
   * list of projects we have, from scratch.
   * @param text $filtermode
   */
  function get_projects($filtermode="my", $filtervalue="") {
    global $session;

    // Allow for an optional filtering override..
    if ($filtermode  !== "") $this->filtermode  = $filtermode;
    if ($filtervalue !== "") $this->filtervalue = $filtervalue;

    // Initialise..
    $this->projects = array();
    $q = "";

    switch($this->filtermode) {
      case "my":
        $this->filtervalue = $session->user_no;
        $this->filterdesc = $session->fullname;
        // drop thru' to user filter..
      case "user":
        if ($this->filtervalue != "" && is_numeric($this->filtervalue)) {
          $user_no = $this->filtervalue;
          if ($this->filtermode == "user") {
            $qry = new PgQuery("SELECT * FROM usr WHERE user_no=$this->filtervalue");
            if ($qry->Exec("get_projects: filter") && $qry->rows == 1 && $row = $qry->Fetch()) {
              $this->filterdesc = $row->fullname;
            }
          }
          $q  = "SELECT DISTINCT rp.*, p.qa_phase, usr.*, r.*,status.lookup_desc AS status_desc";
          $q .= " FROM";
          $q .= " request_project rp LEFT OUTER JOIN qa_phase p ON p.qa_phase=rp.qa_phase,";
          $q .= " request r LEFT OUTER JOIN lookup_code AS status";
          $q .= "    ON (status.source_table='request'";
          $q .= "   AND status.source_field='status_code'";
          $q .= "   AND status.lookup_code = r.last_status)";
          $q .= "  LEFT OUTER JOIN usr ON (usr.user_no=r.requester_id),";
          $q .= "  request_allocated ra, request_interested ri";
          $q .= " WHERE r.request_id=rp.request_id";
          $q .= "   AND r.request_type='90'";
          $q .= "   AND ra.request_id=r.request_id";
          $q .= "   AND ri.request_id=r.request_id";
          $q .= "   AND (ra.allocated_to_id=$user_no"
                    . " OR ri.user_no=$user_no"
                    . " OR r.requester_id=$user_no"
                    . " OR r.entered_by=$user_no"
                    . " OR rp.project_manager=$user_no"
                    . " OR rp.qa_mentor=$user_no"
                    . ")";
        }
        break;

      case "recent":
        $q  = "SELECT rp.*, p.qa_phase, usr.*, r.*,status.lookup_desc AS status_desc";
        $q .= " FROM";
        $q .= " request_project rp LEFT OUTER JOIN qa_phase p ON p.qa_phase=rp.qa_phase,";
        $q .= " request r LEFT OUTER JOIN lookup_code AS status";
        $q .= "    ON (status.source_table='request'";
        $q .= "   AND status.source_field='status_code'";
        $q .= "   AND status.lookup_code = r.last_status)";
        $q .= "  LEFT OUTER JOIN usr ON (usr.user_no=r.requester_id)";
        $q .= " WHERE r.request_id=rp.request_id";
        $q .= "   AND request_type='90'";
        break;

    } // switch

    // Only execute it if query was created above..
    if ($q != "") {
      $q .= " ORDER BY r.last_activity DESC";
      $q .= " LIMIT $this->max_projects";
      $qry = new PgQuery($q);
      if ($qry->Exec("get_projects: loop") && $qry->rows > 0) {
        while( $row = $qry->Fetch(true) ) {
          $request_id = $row["request_id"];
          // Create new project as a container only - stuff data in..
          $proj = new qa_project();
          foreach ($row as $fieldname => $fieldvalue) {
            if (!is_numeric($fieldname)) {
              $proj->{$fieldname} = $fieldvalue;
            }
          }
          $proj->request_id = $request_id;
          $proj->new_record = false;
          $this->projects[$request_id] = $proj;
        } // while
      }
    }
  } // get_projects

} // project_set class

// -----------------------------------------------------------------------
/**
 * This class is a container for a single QA project. A QA Project is
 * actually just a WRMS request, but is also maintained as a record in
 * a separate table 'project', which associates it with the WRMS record.
 * This allows us to identify those WRMS records which are actually
 * 'projects' rather than normal work requests.
 */
class qa_project extends qams_request {
  /** If true then this is a brand new project */
  var $new_project = false;
  /** User ID (wrms user_no) of Project Manager */
  var $project_manager;
  /** User ID (wrms user_no) of QA Mentor */
  var $qa_mentor;
  /** Model ID originally chosen */
  var $qa_model_id;
  /** The current QA phase of this project. This is updated
   * by approval activity, to be the phase of the QA Step
   * that the activity was for. */
  var $qa_phase = "";
  /**
   * This object contains the complete QA process
   * steps for this project.
   */
  var $qa_process;
  // .....................................................................
  /** Constructor for a project. If the ID is passed in then we try to
   * get the record from the database.
   * @param integer $id Unique ID of the project, or zero for unknown/new
   */
  function qa_project($id=0) {
    // This will read the record from the database, or
    // initialise the new object if the id is zero..
    $this->qams_request($id);

    // Local new record flag..
    if ($this->new_record) {
      $this->new_project = true;
    }

    // Process any posted data..
    if ($this->POSTprocess()) {
      $this->save_project();
    }

    // Retrieve QA project-specific data
    $this->get_project();
  } // qa_project
  // .....................................................................
  /**
   * Get the QA project data from the database.
   */
  function get_project() {
    if ($this->request_id > 0) {
      $q  = "SELECT rp.*,";
      $q .= "pm.fullname AS pm_name, pm.email AS pm_email,";
      $q .= "qa.fullname AS qa_name, qa.email AS qa_email";
      $q .= "  FROM request_project rp";
      $q .= "  LEFT OUTER JOIN usr AS pm ON pm.user_no=rp.project_manager";
      $q .= "  LEFT OUTER JOIN usr AS qa ON qa.user_no=rp.qa_mentor";
      $q .= " WHERE rp.request_id=$this->request_id";

      $qry = new PgQuery($q);
      if ($qry->Exec("qa_project::get_project") && $qry->rows > 0) {
        $row = $qry->Fetch();
        $this->project_manager = $row->project_manager;
        $this->project_manager_fullname = $row->pm_name;
        $this->project_manager_email = $row->pm_email;
        $this->qa_mentor = $row->qa_mentor;
        $this->qa_mentor_fullname = $row->qa_name;
        $this->qa_mentor_email = $row->qa_email;
        $this->qa_model_id = $row->qa_model_id;
        $this->qa_phase = $row->qa_phase;

        // This reads in the complete QA process for this project including
        // all of the QA steps, and the approvals for each one..
        $this->qa_process = new qa_process($this);

        // Read allocations and interested users..
        $this->get_allocated();
        $this->get_interested();
      }
    }
  } // get_project
  // .....................................................................
  /**
   * Save the  project records to the database.
   * @return boolean True if saved without problems.
   */
  function save_project() {
    $this->status_code = "I"; // In Progress
    $ok = $this->save_request();

    // If new project, create the project record..
    if ($ok && $this->new_project && $this->request_id > 0) {
      // Make sure model ID is set..
      if (isset($this->qa_model_id)) {
        $model = $this->qa_model_id;
      }
      else {
        $model = 1; // default to small
      }

      // Create the request - project index record..
      $q  = "INSERT INTO request_project (";
      $q .= " request_id, project_manager, qa_mentor, qa_model_id ";
      $q .= ") ";
      $q .= "VALUES(?, ?, ?, ?);";
      $qry = new PgQuery(
                $q,
                $this->request_id,
                $this->project_manager,
                $this->qa_mentor,
                $this->qa_model_id
                );
      if (!$qry->Exec("qa_project::save_project")) {
        $client_messages[] = "$qry->errorstring";
        $ok = false;
      }

      // Update these - need them further on..
      $this->get_allocated();
      $this->get_interested();

      // Acquire the QA steps for this model..
      $q  = "SELECT m.*, s.*, p.*, doc.*";
      $q .= "  FROM qa_model m, qa_model_step ms,";
      $q .= "       qa_phase p, qa_step s";
      $q .= " LEFT OUTER JOIN qa_document AS doc ON doc.qa_document_id=s.qa_document_id";
      $q .= " WHERE m.qa_model_id=$model";
      $q .= "   AND ms.qa_model_id=m.qa_model_id";
      $q .= "   AND s.qa_step_id=ms.qa_step_id";
      $q .= "   AND p.qa_phase=s.qa_phase";
      $q .= "   AND s.enabled";
      $q .= " ORDER BY p.qa_phase_order, s.qa_step_order";
      $qry = new PgQuery($q);
      if ($qry->Exec("qa_project::save_project") && $qry->rows > 0) {
        while( $row = $qry->Fetch(true) ) {
          $qastep = new qa_project_step($this->request_id, 0, $row);
          $qastep->insert_into_project($this);
        }
      }
      // Not a new project anymore..
      $this->new_project = false;
    }
    return $ok;
  } // save_project
  // .....................................................................
  /**
   * Delete a project from the database. This is a serious admin action
   * and probably not in the normal user interface. It's damn handy for
   * development/testing though!
   */
  function delete_project() {
    $res = false;
    if ($this->request_id > 0) {
      $ok = true;

      // All in a single tranny..
      $qry = new PgQuery("BEGIN");
      $qry->Exec("qa_project::delete_project");

      // Remove all the subordinate request records for each project step..
      $request_table_suffixes = array(
          "action", "allocated", "attachment", "history", "interested",
          "note", "quote", "status", "tag", "timesheet", "request"
          );
      foreach ($this->qa_process->qa_steps as $qa_step_id => $qastep) {
        // Delete all subordinate records..
        foreach($request_table_suffixes as $suffix) {
          $qry = new PgQuery("DELETE FROM request_$suffix WHERE request_id=$qastep->request_id");
          $ok = $qry->Exec("qa_project::delete_project");
          if ($ok === false) {
            break;
          }
        }
      }

      if ($ok) {
        $qry = new PgQuery("DELETE FROM qa_project_approval WHERE project_id=$this->request_id");
        $ok = $qry->Exec("qa_project::delete_project");
      }
      if ($ok) {
        $qry = new PgQuery("DELETE FROM qa_project_step_approval WHERE project_id=$this->request_id");
        $ok = $qry->Exec("qa_project::delete_project");
      }
      if ($ok) {
        $qry = new PgQuery("DELETE FROM qa_project_step WHERE project_id=$this->request_id");
        $ok = $qry->Exec("qa_project::delete_project");
      }
      if ($ok) {
        $qry = new PgQuery("DELETE FROM request_project WHERE request_id=$this->request_id");
        $ok = $qry->Exec("qa_project::delete_project");
      }

      // Delete step request recs..
      foreach ($this->qa_process->qa_steps as $qa_step_id => $qastep) {
        if ($ok) {
          $qry = new PgQuery("DELETE FROM request WHERE request_id=$qastep->request_id");
          $ok = $qry->Exec("qa_project::delete_project");
        }
      }

      // Now delete the WRMS project master records..
      foreach($request_table_suffixes as $suffix) {
        if ($ok) {
          $qry = new PgQuery("DELETE FROM request_$suffix WHERE request_id=$this->request_id");
          $ok = $qry->Exec("qa_project::delete_project");
        }
      }
      // Then delete the main record..
      if ($ok) {
        $qry = new PgQuery("DELETE FROM request WHERE request_id=$this->request_id");
        $ok = $qry->Exec("qa_project::delete_project");
      }

      // Commit or rollback..
      $qry = new PgQuery(($ok ? "COMMIT;" : "ROLLBACK;"));
      $res = $qry->Exec("qa_project::delete_project");
    }
    return $res;
  } // delete_project
  // .....................................................................
  /**
   * Return an array of permitted qa_actions comprising what the given (or
   * current) user is allowed to do with QA steps. This is just at the
   * general (project) level. The qa_actions we have so far are:
   *   view  - Able to view QA steps and approval stuff etc.
   *   admin - Able to administer QA config for the project
   * @param integer $user_no Optional user code; defaults to logged-in user
   * @return array List of permitted qa_actions, as strings.
   */
  function PermittedActions($user_no=false) {
    global $session;
    $qa_actions = array();
    if ($user_no === false) {
      $user_no = $session->user_no;
    }
    // Let everyone view it..
    $qa_actions["view"] = true;
    // Only the PM and QA mentor get to do the heavy stuff..
    if ($user_no == $this->project_manager
     || $user_no == $this->qa_mentor
     || $session->AllowedTo("QA")
    ) {
      $qa_actions["admin"] = true;
    }
    return $qa_actions;
  } // PermittedActions
  // .....................................................................
  /** Acquire the array of recipient email addresses for this project.
   * @return array Array of recipients email => full name
   */
  function GetRecipients() {
    $this->get_interested();
    $this->get_allocated();
    $recips = array_merge($this->allocated_email, $this->interested_email);
    if ($this->project_manager_email != "") {
      $recips[$this->project_manager_email] = $this->project_manager_fullname;
    }
    if ($this->qa_mentor_email != "") {
      $recips[$this->qa_mentor_email] = $this->qa_mentor_fullname;
    }
    return $recips;    
  } // GetRecipients
  // .....................................................................
  /**
   * Send an e-mail to everyone on the project, plus interested users.
   * @param string $desc Description of this e-mail; what it is for
   * @param string $ebody The body of the email you want to send
   * @param string $esubject Override subject, else standard QAMS one
   * @param mixed $recipients Override recipients array, else use default
   * @param mixed $attachfiles Array of optional paths to files to attach
   */
  function QAMSNotifyEmail($desc, $ebody, $esubject="", $recipients=false, $attachfiles=false) {
    global $base_dns, $session;
    global $sysabbr, $system_name, $admin_email, $debug_email;

    $mail = &new phpmailer ();
    //$mail->IsSMTP();
    $mail->IsSendmail();

    // From addressing..
    $mail->From = $this->project_manager_email;
    $mail->Sender = $mail->From;
    $mail->FromName = $this->project_manager_fullname;
    $mail->AddReplyTo( $session->email, $session->fullname );

    // Recipients..
    if (is_array($recipients)) {
      $send_to = $recipients;
    }
    else {
      $send_to = $this->GetRecipients();
    }
    foreach ($send_to as $email => $name) {
      if (isset($debug_email)) {
        if ($debug_to != "") $debug_to .= ", ";
        $debug_to .= "$name <$email>";
      }
      else {
        $mail->AddAddress($email, $name);
      }
    }
    if (isset($debug_email)) {
      $mail->AddAddress($debug_email, "QAMS EMail Testing");
    }

    // Subject..
    if ($esubject == "") {
      // Standard subject line..
      $mail->Subject = "QAMS Project #"
                     . $this->request_id
                     . " [".$session->system_codes[$this->system_id]."/".$session->username . "] "
                     . $this->brief
                     ;
    }
    else {
      // Over-ridden subject line..
      $mail->Subject = $esubject;
    }

    // Body..
    $tbody  = "QAMS Project:    $this->brief\n"
            . "Project Manager: $this->project_manager_fullname [$this->project_manager_email]\n"
            . "This E-Mail:     $desc\n"
            ;

    $hbody  = <<<EOX
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><head>
<title>$mail->Subject</title>
<link rel='stylesheet' type='text/css' href='$GLOBALS[base_dns]/email.css' />
<style type="text/css"><!--
A {color: navy; text-decoration:underline;  }
body, input {font: 13px  tahoma, sans-serif; color: #000000; margin: 0.3em; }
p, td { margin: 0 2em 0.5em; }
td.etd {font: bold 13px tahoma, sans-serif; color: black; background-color: #f0ece8; white-space:nowrap; }
th.eth {font: bold 15px tahoma, sans-serif; color: white; background-color: #440000; margin: 0; padding: 1px 4px; }
.row0 { background: #ffffff; color: #333333; }
.row1 { background: #f0ece8; color: #333333; }
h1, .h1 {font: bold 15px/17px tahoma, sans-serif; color: #660000; margin: 2em 0.3em 0; }
h2, .h2 {font: normal 15px tahoma, sans-serif; color: #660000;  margin: 1.4em 0.3em 0; }
h3, th.h3 {font: bold 13px tahoma, sans-serif; color: #660000; margin: 1em 0.3em 0; }
h4, .h4 {font: bold 13px tahoma, sans-serif; color: #660000;  margin: 0.7em 0.3em 0; }
hr.footerline {line-height: 1em; margin: 1em; padding: 2px; height: 2px; width: 95%; color: #440000; background-color: #840000; clear: both; border: none; align: center; }
.footer { font: normal 11px tahoma, sans-serif; }
--></style>
</head>\n<body>
<table>
<tr><th align="left" class="eth">QAMS Project</th><td class="etd">$this->brief</td></tr>
<tr><th align="left" class="eth">Project Manager</th><td class="etd"><a href="mailto:$this->project_manager_email">$this->project_manager_fullname</a></td></tr>
<tr><th align="left" class="eth">This E-Mail</th><td class="etd">$desc</td></tr>
EOX
;

    $tbody .= "\n\n";
    $hbody .= "</table>\n";

    // Now add the main information..
    $hbody .= $ebody;
    $ebody  = str_replace("<p>", "\n\n", $ebody);
    $ebody  = str_replace("<br>", "\n",  $ebody);
    $ebody  = str_replace("&nbsp;", " ", $ebody);
    $tbody .= strip_tags($ebody);

    // Link to project summary page..
    $href = $GLOBALS[base_dns] . "/qams-project.php?request_id=$this->request_id";
    $link = "<a href=\"$href\">$href</a>";

    $hbody .= "<hr class=\"footerline\"><p class=\"footer\">Full details of the project, can be reviewed at:<br>"
            . "&nbsp;&nbsp;" . $link . "<br><br></p>";
    $tbody .= "\nFull details of the project, can be reviewed at:\n"
            . "  $href\n\n";
    $hbody .= "</body>\n</html>\n";

    // Assign HTMl and plaintext parts..
    $mail->Body = $hbody;
    $mail->AltBody = $tbody;

    // Attach any files..
    if ($attachfiles !== false) {
      foreach ($attachfiles as $path => $name) {
        $mail->AddAttachment($path, $name);
      }
    }
    return $mail->Send();

  } // QAMSNotifyEmail
  // .....................................................................
  /**
   * Return project details. If editable return as form fields with the
   * form tags, encapsulated in a nice table with all of the fields we
   * need to create/edit a QA project.
   * @param integer $edit Flag, if 1 then editable fields in a form
   */
  function project_details($edit=0) {
    $s = "";

    $ef = new EntryForm($REQUEST_URI, $this, $edit);
    $ef->NoHelp();

    if ($ef->editmode) {
      $s .= $ef->StartForm();
      if ( $this->request_id > 0 ) {
        $s .= $ef->HiddenField( "request_id", $this->request_id );
        $s .= $ef->HiddenField( "qa_action", "edit" );
      }
      $s .= $ef->HiddenField( "post_action", "details_update" );
    }
    // Just the things we want created/updated in QAMS..
    $s .= "<table width=\"100%\" class=\"data\" cellspacing=\"0\" cellpadding=\"0\">\n";
    $s .= $this->RenderDetails($ef);
    if (!$ef->editmode) {
      $s .= $this->RenderQASummary($ef);
    }
    $s .= $this->RenderAllocations($ef);
    $s .= $this->RenderInterests($ef);
    $s .= "<tr><td height=\"15\" colspan=\"2\">&nbsp;</td></tr>";
    $s .= "</table>\n";

    if ( $ef->editmode ) {
      $s .= $ef->SubmitButton( "submit", ($this->new_record ? "Create" : "Update") );
      $s .= $ef->EndForm();
      $s .= "<script type=\"text/javascript\" src=\"/js/request.js\"></script>\n";
    }
    return $s;

  } // project_details
  // .....................................................................
  /**
   * Return project QA Plan. If editable return as form fields with
   * the form tags, encapsulated in a nice table with all of the fields we
   * need to edit a project's QA plan.
   * @param integer $edit Flag, if 1 then editable fields in a form
   */
  function RenderQAPlan($edit=1) {
    global $qa_action;

    $s = "";
    $ef = new EntryForm($REQUEST_URI, $this, $edit);
    $ef->NoHelp();

    if ($ef->editmode) {
      $s .= $ef->StartForm();
      if ( $this->request_id > 0 ) {
        $s .= $ef->HiddenField( "request_id", $this->request_id );
        $s .= $ef->HiddenField( "post_action", "config_update" );
        $s .= $ef->HiddenField( "qa_action", "qaplan" );
        $s .= $ef->HiddenField( "edit", "1" );
      }
    }
    // Just the things we want created/updated in QAMS..
    $s .= "<table width=\"100%\" class=\"data\" cellspacing=\"0\" cellpadding=\"0\">\n";
    if (isset($this->qa_process)) {

      $s .= $ef->BreakLine("Quality Assurance Plan");
      $s .= "<tr><td height=\"15\" colspan=\"2\">&nbsp;</td></tr>\n";

      // Project info..
      $s .= "<tr><td colspan=\"2\"><b>Project:</b> " . $this->brief . "</td></tr>\n";
      if ($this->project_manager_fullname != "") {
        $pmlink  = "<a href=\"/user.php?user_no=$this->project_manager\">";
        $pmlink .= $this->project_manager_fullname;
        $pmlink .= "</a>";
        $s .= "<tr><td colspan=\"2\"><b>Project Manager:</b> " . $pmlink . "</td></tr>";
      }
      if ($this->qa_mentor_fullname != "") {
        $qalink  = "<a href=\"/user.php?user_no=$this->qa_mentor\">";
        $qalink .= $this->qa_mentor_fullname;
        $qalink .= "</a>";
        $s .= "<tr><td colspan=\"2\"><b>QA Mentor:</b> " . $qalink . "</td></tr>";
      }
      $s .= "<tr><td height=\"15\" colspan=\"2\">&nbsp;</td></tr>";
      $s .= "<tr><td colspan=\"2\">" . $this->qa_process->QAPlan() . "</td></tr>";
    }
    $s .= "<tr><td height=\"15\" colspan=\"2\">&nbsp;</td></tr>";
    $s .= "</table>\n";

    $qaplan_status = $this->qa_process->overall_approval_status(STEP_ID_QAPLAN);
    if ( $ef->editmode && $qaplan_status != "y" && $qaplan_status != "p") {
      $s .= $ef->SubmitButton( "submit", "Update" );
      $s .= $ef->EndForm();
    }
    return $s;

  } // RenderQAPlan
  // .....................................................................
  /**
   * Render the Quality Assurance details for this project.
   * @param object $ef The edit form we are building.
   */
  function RenderQASummary( $ef ) {
    $html = "";
    if (isset($this->qa_process)) {
      $html .= $ef->BreakLine("Quality Assurance Status");
      if ($this->project_manager_fullname != "") {
        $href = "/user.php?user_no=$this->project_manager";
        $link = "<a href=\"$href\">" . $this->project_manager_fullname . "</a>";
        $html .= "<tr><td colspan=\"2\"><b>Project Manager:</b> " . $link . "</td></tr>";
      }
      if ($this->qa_mentor_fullname != "") {
        $href = "/user.php?user_no=$this->qa_mentor";
        $link = "<a href=\"$href\">" . $this->qa_mentor_fullname . "</a>";
        $html .= "<tr><td colspan=\"2\"><b>QA Mentor:</b> " . $link . "</td></tr>";
      }
      $html .= "<p>&nbsp;</p>";
      $html .= "<tr><td colspan=\"2\">" . $this->qa_process->QASummary() . "</td></tr>";
    }
    return $html;

  } // RenderQASummary
  // .....................................................................
  /** Overridden method from Request, just so we display the subset of
   * request data we are interested in for QAMS.
   */
  function RenderDetails( $ef ) {
    global $session, $bigboxcols, $bigboxrows;
    $html = "";
    $html .= $ef->BreakLine("Project Details");

    if (!$this->new_record) {
      $html .= $ef->DataEntryLine(
                     "W/R #",
                     "$this->request_id"
                    ." &nbsp; &nbsp; <b>Requested:</b> " . nice_date($this->request_on)
                    ." &nbsp; &nbsp; <b>Status:</b> " . $this->status_desc
                    );
      $html .= $ef->BreakLine("Quality Assurance Details");
    }
    else {
      // QA Model
      // This determines which default approvals are set up initially..
      $html .= $ef->DataEntryLine( "QA Model", "", "lookup", "qa_model_id",
              array("_sql" => "SELECT * FROM qa_model",
                    "_null" => "--- choose a model ---",
                    "title" => "The QA model most appropriate to this project"));

      // PROJECT ROLES
      // These are specific important roles to be assigned to the project for QA
      // purposes. Note, we also consider that anyone who has been ALLOCATED to
      // this project is a valid QA Reviewer (client or internal).

      // Project Manager
      $html .= $ef->DataEntryLine( "Project Mgr", "$this->fullname", "lookup", "project_manager",
              array("_sql" => SqlSelectRequesters($session->org_code),
                    "_null" => "--- select a person ---",
                    "title" => "The project manager in charge of this project."));

      // QA Mentor
      $html .= $ef->DataEntryLine( "QA Mentor", "$this->fullname", "lookup", "qa_mentor",
              array("_sql" => SqlSelectRequesters($session->org_code),
                    "_null" => "--- select a person ---",
                    "title" => "The QA mentor helping with quality assurance on this project."));
    }

    $html .= $ef->DataEntryLine( "Brief", "%s", "text", "brief",
              array("size" => 70, "title" => "A brief description of the project."));

    // Organisation drop-down
    if ($session->AllowedTo("Admin") || $session->AllowedTo("Support") || $session->AllowedTo("Contractor")) {
      $html .= $ef->DataEntryLine( "Organisation", "$this->org_name", "lookup", "org_code",
                array("_sql" => SqlSelectOrganisations($this->org_code),
                      "_null" => "--- select an organisation ---", "onchange" => "OrganisationChanged();",
                      "title" => "The organisation that this work will be done for."));
    }
    else {
      if ($this->new_record) $this->org_name = $session->org_name;
      $html .= $ef->DataEntryLine("Organisation", "$this->org_name", "", "");
    }

    // Person within Organisation drop-down
    $html .= $ef->DataEntryLine( "Person", "$this->fullname", "lookup", "requester_id",
              array("_sql" => SqlSelectRequesters($this->org_code),
                    "_null" => "--- select a person ---", "onchange" => "PersonChanged();",
                    "title" => "The client who is requesting this, or who is in charge of ensuring it happens."));

    // System (within Organisation) drop-down
    $html .= $ef->DataEntryLine( "System", "$this->system_desc", "lookup", "system_id",
              array("_sql" => SqlSelectSystems($this->org_code),
                    "_null" => "--- select a system ---", "onchange" => "SystemChanged();",
                    "title" => "The business system that this project applies to."));

    // Urgency of Request
    $html .= $ef->DataEntryLine( "Urgency", $this->urgency_desc, "lookup", "urgency",
              array("_type" => "request|urgency", "title" => "The urgency of the project, separate from the long-term importance") );

    // Importance of Request
    $html .= $ef->DataEntryLine( "Importance", $this->importance_desc, "lookup", "importance",
              array("_type" => "request|importance", "title" => "The relative long-term importance of the project, separate from the urgency") );


    // Requested By Date
    $html .= $ef->DataEntryLine( "Requested By", $this->requested_by_date, "date", "requested_by_date",
              array("title" => "The date that you would like this project completed by",
                        "onblur" => "this.value=CheckDate(this)"));

    // Agreed Due Date
    $html .= $ef->DataEntryLine( "Agreed Due", $this->agreed_due_date, "date", "agreed_due_date",
              array("title" => "The date that has been agreed that the project will be completed by / on",
                        "onblur" => "this.value=CheckDate(this)"));

    // Detailed description
    $html .= $ef->DataEntryLine( "Details", str_replace('%','%%', html_format($this->detailed)), "textarea", "detailed",
              array("title" => "Full details of the project", "rows" => $bigboxrows, "cols" => $bigboxcols));
    return $html;
  } // RenderDetails
  // .....................................................................
  /**
   * Process POSTed form data.
   */
  function POSTprocess() {
    global $post_action, $session;
    $processed = false;
    switch ($post_action) {
      // Posted project details form..
      case "details_update":
        $this->chtype = ($this->new_project) ? "create" : "update";
        // Some forced settings for QAMS..
        $_POST["request_type"] = "90";
        if ($this->new_project) {
          $_POST["status_code"] = "I";
          // Make sure creator is always allocated initially..
          $alloc = array();
          if (isset($_POST["new_allocations"])) {
            $alloc = $_POST["new_allocations"];
          }
          if (!in_array($session->user_no, $alloc)) {
            $alloc[] = $session->user_no;
          }
          $_POST["new_allocations"] = $alloc;
        }
        // Stash posted QAMS-specific stuff..
        foreach ($this->post_fields as $posted_name) {
          if (isset($_POST["$posted_name"])) {
            $this->{$posted_name} = $_POST["$posted_name"];
          }
        }
        // This will trigger a save..
        $processed = true;
        break;

      // Posted project QA Plan form. This contains possible mods
      // to the required approvals per QA step..
      case "config_update":
        // Process special notes updates..
        $q  = "SELECT * FROM qa_project_step";
        $q .= " WHERE project_id=$this->request_id";
        $qry = new PgQuery($q);
        if ($qry->Exec("qa_project::POSTprocess") && $qry->rows > 0) {
          while($row = $qry->Fetch()) {
            $step_id = $row->qa_step_id;
            $original_notes = stripslashes($row->notes);
            $new_notes = "special_notes_$step_id";
            global $$new_notes;
            if (isset($$new_notes) && $$new_notes != $original_notes) {
              $q = "UPDATE qa_project_step";
              $q .= " SET notes='" . addslashes($$new_notes) . "'";
              $q .= " WHERE project_id=$this->request_id";
              $q .= " AND qa_step_id=$step_id";
              $up = new PgQuery($q);
              $up->Exec("qa_project::POSTprocess");
            }
          }
        }

        // Process changes to approval types required..
        $this->POSTprocess_approval_updates();

        // Process removal of QA steps..
        global $step_removals;
        if (isset($step_removals) && count($step_removals) > 0) {
          $remkeys = implode(",", $step_removals);
          $qry = new PgQuery("BEGIN");
          $ok = $qry->Exec("qa_project::POSTprocess");
          if ($ok) {
            $q  = "DELETE FROM qa_project_approval";
            $q .= " WHERE project_id=$this->request_id";
            $q .= "   AND qa_step_id IN ($remkeys)";
            $qry = new PgQuery($q);
            $ok = $qry->Exec("qa_project::POSTprocess");
          }
          if ($ok) {
            $q  = "DELETE FROM qa_project_step_approval";
            $q .= " WHERE project_id=$this->request_id";
            $q .= "   AND qa_step_id IN ($remkeys)";
            $qry = new PgQuery($q);
            $ok = $qry->Exec("qa_project::POSTprocess");
          }
          if ($ok) {
            $q  = "DELETE FROM qa_project_step";
            $q .= " WHERE project_id=$this->request_id";
            $q .= "   AND qa_step_id IN ($remkeys)";
            $qry = new PgQuery($q);
            $ok = $qry->Exec("qa_project::POSTprocess");
          }
          // Commit or rollback..
          $qry = new PgQuery(($ok ? "COMMIT;" : "ROLLBACK;"));
          $res = $qry->Exec("qa_project::POSTprocess");
        }
        // Process addition of QA steps..
        global $step_additions;
        if (isset($step_additions) && count($step_additions) > 0) {
          $_POST = array();
          $this->get_project();
          $this->get_allocated();
          $this->get_interested();
          foreach ($step_additions as $new_step_id) {
            $newstep = new qa_project_step($this->request_id);
            $newstep->get_step_data($new_step_id);
            $newstep->insert_into_project($this);
          } // foreach
        }
        break;
    } // switch

    global $action;
    if ( isset($action) ) {
      // Actions are usually activated by clickable links. Eg. removing
      // an interested user etc. We handle these here. The action will
      // make any DB mods it needs to make..
      $this->Actions(true);
    }

    return $processed;
  } // POSTprocess
  // .....................................................................
  /**
   * Process the POST of approval types addition and removal changes
   * for this project. Returns true if changes were made.
   * @param mixed $step_id If provided, limit POST checking to the given step
   * @return boolean True if one or more changes were made
   */
  function POSTprocess_approval_updates($step_id=false) {
    $res = false;
    // This will be posted as an array var..
    $postvar = "step_approval_types";
    global $$postvar;
    if (isset($$postvar)) {
      $posted_approvals = $$postvar;
      // (a) Process approval types being removed..
      $q  = "SELECT * FROM qa_project_step_approval";
      $q .= " WHERE project_id=$this->request_id";
      if ($step_id !== false) {
        $q .= " AND qa_step_id=$step_id";
      }
      $qry = new PgQuery($q);
      if ($qry->Exec("qa_project::POSTprocess_approval_updates") && $qry->rows > 0) {
        while($row = $qry->Fetch()) {
          // Can only change if in 'not started' status..
          $status = $row->last_approval_status;
          $step_id = $row->qa_step_id;
          $ap_type_id = $row->qa_approval_type_id;
          if (!in_array($step_id . "|" . $ap_type_id, $posted_approvals)) {
            if ($status == "") {
              $qastep = new qa_project_step();
              $qastep->get($this->request_id, $step_id);
              $qastep->remove_required_approval($ap_type_id);
              $res = true;
            }
          }
          // Keep record of currently required approvals..
          $reqd_approvals[] = $step_id . "|" . $ap_type_id;
        } // while
      }
      // (b) Process approval types being added..
      if (count($posted_approvals) > 0) {
        foreach ($posted_approvals as $key) {
          $bits = explode("|", $key);
          $new_step_id = $bits[0];
          $new_ap_type_id = $bits[1];
          if (!in_array($new_step_id . "|" . $new_ap_type_id, $reqd_approvals)) {
            $q  = "INSERT INTO qa_project_step_approval (";
            $q .= " project_id, qa_step_id, qa_approval_type_id ";
            $q .= ") ";
            $q .= "VALUES(?, ?, ?);";
            $qry = new PgQuery(
                      $q,
                      $this->request_id,
                      $new_step_id,
                      $new_ap_type_id
                      );
            if (!$qry->Exec("qa_project::POSTprocess_approval_updates")) {
              $client_messages[] = "$qry->errorstring";
            }
            $res = true;
          }
        } // foreach
      }
    }
    // Indication that changes were made..
    return $res;
  } // POSTprocess_approval_updates

} // qa_project class

// -----------------------------------------------------------------------
/**
 * A complete QA process, comprising all of the QA Steps involved
 * in that process. This is a container for all of the steps that a
 * project needs to achive in terms of QA.
 */
class qa_process {
  /** The project (object) we get the QA process for */
  var $project;
  /** The request ID for our project, nicely renamed. This
   * is actually the request id of the master WRMS record. */
  var $project_id;
  /** The array of QA step objects for the project */
  var $qa_steps = array();
  /** Array of permitted qa_actions for current user */
  var $permitted_actions = array();
  /** True if the currently logged-in user has QA admin
   * privileges. This is a combination of Axyl group
   * membership and project assignments.
   */
  var $have_admin = false;
  // .....................................................................
  /**
   * Constructor for the QA process tree. This creates the complete
   * set of QA steps for the given project. The project is passed in
   * as a pointer to the complete project object, since we make
   * use of some of its data and methods.
   * @param mixed $project Reference to the project to get QA process for.
   */
  function qa_process(&$project) {
    global $session;

    $this->project = $project;
    $this->project_id = $project->request_id;
    $this->get_qa_steps();
    $this->permitted_actions = $project->PermittedActions();
    $this->have_admin = isset($this->permitted_actions["admin"])
           || $session->AllowedTo("QA");
  } // qa_process
  // .....................................................................
  /**
   * Get the QA steps for this project from the database.
   */
  function get_qa_steps() {
    if (isset($this->project_id)) {
      $q  = "SELECT qaps.*, qas.*, qap.*, doc.*,";
      $q .= " usr.fullname AS responsible_fullname, usr.email AS responsible_email";
      $q .= " FROM qa_project_step qaps";
      $q .= " LEFT OUTER JOIN usr ON usr.user_no=qaps.responsible_usr,";
      $q .= " qa_step qas";
      $q .= " LEFT OUTER JOIN qa_document AS doc ON doc.qa_document_id=qas.qa_document_id,";
      $q .= " qa_phase qap";
      $q .= " WHERE qaps.project_id=$this->project_id";
      $q .= "   AND qas.qa_step_id=qaps.qa_step_id";
      $q .= "   AND qap.qa_phase=qas.qa_phase";
      $q .= " ORDER BY qap.qa_phase_order, qas.qa_step_order";
      $qry = new PgQuery($q);
      if ($qry->Exec("qa_process::get_qa_steps") && $qry->rows > 0) {
        while( $row = $qry->Fetch(true) ) {
          $qa_step_id = $row["qa_step_id"];
          $request_id = $row["request_id"];
          $qa_step = new qa_project_step($this->project_id, $request_id, $row);
          $this->qa_steps[$qa_step_id] = $qa_step;
        } // while
      }
    }
  } // get_qa_steps
  // .....................................................................
  /**
   * Return the overall approval status of the given step. This just calls
   * the method of the same name for the step required. The overall status
   * is the combination of all individual approvals for the step.
   * @param integer $qa_step_id The ID of the step to get the status of
   * @return string Overall approval status of the given QA step.
   */
  function overall_approval_status($qa_step_id) {
    $res = "";
    if (isset($this->qa_steps[$qa_step_id])) {
      $qastep = $this->qa_steps[$qa_step_id];
      $qastep->get_approvals();
      $res = $qastep->overall_approval_status();
    }
    return $res;
  } // get_approval_status
  // .....................................................................
  /**
   * Determine whether the QA Plan for this QA Process has been approved.
   * @return boolean True if the QA Plan has been approved, else false.
   */
  function QAPlanApproved() {
    return ($this->overall_approval_status(STEP_ID_QAPLAN) == "y");
  } // QAPlanApproved
  // .....................................................................
  /**
   * Render this QA process as an HTML table containing maintenance
   * widgets which allow the various possible QA steps to be enabled or
   * disabled for this QA process.
   */
  function QAPlan() {
    // Determine what status the QA Plan is in. It can be un-approved,
    // seeking approval, or approved.
    // NB: The step id for the QA Plan is always '1'..
    $qaplan_status = qa_approval_status($this->overall_approval_status(STEP_ID_QAPLAN));
    if ($qaplan_status == "") {
      $qaplan_status = "Unapproved";
    }

    // For display of the approval status of this QA Plan..
    $thisplan = "This Quality Assurance Plan is";
    switch ($qaplan_status) {
      case "Approved":
        $qaplan_status_display = "<span style=\"color:green;font-size:12pt\">$thisplan Approved</span>";
        break;
      case "Unapproved":
        $qaplan_status_display = "<span style=\"color:red;font-size:12pt\">$thisplan Not Yet Approved</span>";
        break;
      default:
        $qaplan_status_display = "<span style=\"color:orange;font-size:12pt\">$thisplan Currently Seeking Approval</span>";
    } // switch

    // Main QA Plan table..
    $s  = "";
    $s .= "<table cellspacing=\"2\" cellpadding=\"0\" width=\"100%\">\n";
    $s .= "<tr>";
    $s .= "<td align=\"center\" colspan=\"2\">$qaplan_status_display</td>";
    $s .= "</tr>\n";
    $s .= "<tr><td height=\"15\" colspan=\"2\">&nbsp;</td></tr>\n";
    $s .= "<tr>";
    $s .= "<th style=\"text-align:left;font-weight:bold;border-bottom:solid black 1px;padding-left:3px\">Step</th>\n";
    $s .= "<th valign=\"top\">";
    $s .= " <table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">";
    $s .= " <tr>";
    $s .= "  <th width=\"80%\" style=\"text-align:left;font-weight:bold;border-bottom:solid black 1px\">Approval type</th>\n";
    $s .= "  <th width=\"20%\" style=\"text-align:center;font-weight:bold;border-bottom:solid black 1px\">Reqd.</th>\n";
    $s .= " </tr>\n";
    $s .= " </table>\n";
    $s .= "</th>\n";
    $s .= "</tr>\n";

    $rowclass = "row1";
    $last_phase = "";

    // We have to do ALL QA steps possible..
    $q  = "SELECT * FROM qa_step s, qa_phase p";
    $q .= " WHERE p.qa_phase=s.qa_phase";
    $q .= " ORDER BY qa_phase_order, qa_step_order";
    $qry = new PgQuery($q);
    if ($qry->Exec("qa_process::configuration") && $qry->rows > 0) {
      while( $row = $qry->Fetch(true) ) {
        $phase = $row["qa_phase_desc"];
        $qa_step_id = $row["qa_step_id"];
        if (isset($this->qa_steps[$qa_step_id])) {
          // This step is part of our QA process..
          $qastep = $this->qa_steps[$qa_step_id];
          $qastep->get_approvals();
          $step_required = true;
        }
        else {
          if ($qaplan_status != "Unapproved") {
            // Unless the QA Plan is un-approved, don't even bother listing the
            // steps that aren't involved in it..
            continue;
          }
          else {
            // Create a place-holder QA step..
            $qastep = new qa_project_step($this->project_id, 0, $row);
            $step_required = false;
          }
        }

        // Row styling - required steps are dark..
        $rowclass = ($step_required) ? "row1" : "row0";

        if ($phase != $last_phase) {
          $s .= "<tr class=\"cols\">";
          $s .= "<td colspan=\"2\" height=\"20\" valign=\"bottom\" style=\"font-weight:bold;color:white;padding-left:3px\">" . strtoupper($phase) . " PHASE</td>";
          $s .= "</tr>\n";
          $last_phase = $phase;
        }

        $s .= "<tr class=\"$rowclass\">";

        // Step description
        $step_desc = $qastep->qa_step_desc;
        if ($step_required) {
          // Bold the step description..
          $step_desc = "<b>$step_desc</b>";
        }

        // Clickable action links..
        $acts = array();

        // Snapshot statuses of this step..
        $overall_step_status = $qastep->overall_approval_status();
        $step_status = qa_status_coloured($overall_step_status);

        // Friendlier overall step status..
        $overall_step_status = qa_approval_status($overall_step_status);
        if ($overall_step_status == "") {
          $overall_step_status = "Unapproved";
        }

        if ($step_required) {
          if ($overall_step_status != "Unapproved") {
            $acts[] = $step_status;
          }
          if ($this->have_admin) {
            // Step detail link..
            $href  = "/qams-step-detail.php";
            $href .= "?project_id=$qastep->project_id";
            $href .= "&step_id=$qastep->qa_step_id";
            $label = "[detail]";
            $title = "Go to the detail screen for this QA step";
            $link = "<a href=\"$href\" title=\"$title\">$label</a>";

            // Step description..
            $step_desc = "<b>$step_desc</b>&nbsp;&nbsp" . $link;

            // Only have actions if not fully approved..
            if ($overall_step_status != "Approved") {
              // Assignment action link..
              $assignment = $qastep->assigned();
              $href  = "/qams-step-detail.php";
              $href .= "?project_id=$qastep->project_id";
              $href .= "&step_id=$qastep->qa_step_id";
              if ($assignment === false) {
                $label = "Assign to";
                $title = "Assign responsibility for this QA step to someone";
                $link = "<a href=\"$href\" title=\"$title\">$label</a>";
                $acts[] = $link;
              }
              else {
                $fullname = $assignment["fullname"];
                $label = "Re-assign from";
                $title = "Re-assign responsibility for this QA step";
                $link = "<a href=\"$href\" title=\"$title\">$label</a>";
                $acts[] = "$link $fullname";
              }

              // Seek action link..
              $href  = "/qams-request-approval.php";
              $href .= "?project_id=$qastep->project_id";
              $href .= "&step_id=$qastep->qa_step_id";
              $label = "Seek approval";
              $title = "Seek approval for this QA step from someone";
              $link = "<a href=\"$href\" title=\"$title\">$label</a>";
              $acts[] = $link;

              // Remove action link..
              if ($qastep->mandatory) {
                $acts[] = "(mandatory)";
              }
              else {
                // Can't remove if Plan is approved..
                if ($qaplan_status == "Unapproved") {
                  $remchk = "<input type=\"checkbox\""
                          . " name=\"step_removals[]\""
                          . " title=\"Tag this step for removal from the QA process\""
                          . " value=\"$qastep->qa_step_id\""
                          . ">"
                          ;
                  $acts[] = $remchk . "&nbsp;Remove";
                }
              }
            }
            else {
              // Allow for this step to be modified and then re-approved
              $href  = "$REQUEST_URI";
              $href .= "?request_id=$qastep->project_id";
              $href .= "&step_id=$qastep->qa_step_id";
              $href .= "&qa_action=reapprove";
              $label = "Re-approve";
              $title = "Click if this step needs to be changed, and then re-approved";
              $link = "<a href=\"$href\" title=\"$title\">$label</a>";
              $acts[] = $link;
            }
          }
        }
        else {
          if ($qaplan_status == "Unapproved") {
            // Add action link..
            $addchk = "<input type=\"checkbox\""
                    . " name=\"step_additions[]\""
                    . " title=\"Tag this step for adding to the QA process\""
                    . " value=\"$qastep->qa_step_id\""
                    . ">";
            $acts[] = $addchk . "&nbsp;Add";
          }
        }

        // Render the description and any action links..
        $allacts = implode("<br>&nbsp;&nbsp;", $acts);
        if ($allacts != "") {
          $step_desc .= "<br>&nbsp;&nbsp;" . $allacts;
        }
        $s .= "<td width=\"50%\" valign=\"top\" style=\"padding-left:3px\">$step_desc</td>";

        // Approvals lists..
        if ($step_required) {
          $s .= "<td width=\"50%\" valign=\"top\">";
          $s .= $qastep->render_approval_types(
                    $this->have_admin
                    && $qaplan_status == "Unapproved"
                    //&& $overall_step_status == "Unapproved"
                    );
          $s .= "<br></td>";
        }
        else {
          $s .= "<td width=\"50%\">&nbsp;</td>";
        }
        $s .= "</tr>\n";

        // Special notes..
        if ($step_required) {
          if ($this->have_admin && $qaplan_status == "Unapproved") {
            $F  = "<textarea name=\"special_notes_" . $qastep->qa_step_id . "\" style=\"width:550px;height:65px\">";
            $F .= $qastep->special_notes;
            $F .= "</textarea>";
            $s .= "<tr class=\"$rowclass\">";
            $s .= "<td align=\"center\" valign=\"top\" colspan=\"2\" style=\"padding-bottom:3px\">";

            $s .= "<table cellspacing=\"2\" cellpadding=\"0\" width=\"100%\" align=\"center\">\n";
            $s .= "<tr>";
            $s .= "<td width=\"20%\" align=\"right\" valign=\"top\">Special notes:</td>";
            $s .= "<td width=\"80%\" valign=\"top\">" . $F . "</td>";
            $s .= "</tr>\n";
            $s .= "</table>\n";

            $s .= "</td></tr>\n";
          }
          elseif ($qastep->special_notes != "") {
            $s .= "<tr class=\"$rowclass\">";
            $s .= "<td>&nbsp;</td>";
            $s .= "<td style=\"padding-right:10px\">";
            $s .= "<p><b>Note:</b>&nbsp;" . $qastep->special_notes . "</p>";
            $s .= "</td>";
            $s .= "</td></tr>\n";
          }
          $s .= "<tr><td style=\"border-top:solid grey 1px;\" colspan=\"2\" height=\"6\">&nbsp;</td></tr>\n";
        }

      } // while
    }
    $s .= "</table>\n";
    return $s;

  } // QAPlan
  // .....................................................................
  /**
   * This provides a summary view of the overall Quality Assurance status
   * of the project.
   */
  function QASummary() {
    $s = "";
    if (count($this->qa_steps) > 0) {
      $s .= "<table cellspacing=\"2\" cellpadding=\"0\" width=\"100%\">\n";
      $s .= "<tr>";
      $s .= "<th width=\"45%\" style=\"text-align:left;font-weight:bold;border-bottom:solid black 1px\">QA Step</th>";
      $s .= "<th width=\"20%\" style=\"text-align:left;font-weight:bold;border-bottom:solid black 1px\">Assigned to</th>";
      $s .= "<th width=\"15%\" style=\"text-align:left;font-weight:bold;border-bottom:solid black 1px\">Status</th>";
      $s .= "<th width=\"20%\" style=\"text-align:left;font-weight:bold;border-bottom:solid black 1px\">Actions</th>";
      $s .= "</tr>\n";

      $rowclass = "row1";
      foreach ($this->qa_steps as $qa_step_id => $qastep) {
        $rowclass = ($rowclass == "row0") ? "row1" : "row0";
        $s .= "<tr class=\"$class\">";

        // Step description..
        $desc = $qastep->qa_step_desc;
        if ($qastep->mandatory) {
          $desc .= " [Mandatory]";
        }
        $s .= "<td valign=\"top\">$desc</td>";

        // Assignment..
        $s .= "<td valign=\"top\">$qastep->responsible_fullname</td>";

        // Status of this step..
        $oas = $qastep->overall_approval_status();
        $status = qa_status_coloured($qastep->overall_approval_status());
        if ($oas == "" && $qastep->responsible_fullname != "") {
          $status = "<span style=\"color:grey\">Assigned</span>";
        }
        $s .= "<td valign=\"top\">$status</td>";

        // WRMS link..
        $acts = array();
        $href  = "/wr.php?request_id=$qastep->request_id";
        $label = "W/R";
        $title = "Go to the WRMS record for this QA step (new window)";
        $link = "<a href=\"$href\" title=\"$title\" target=\"_new\">$label</a>";
        $acts[] = $link;

        // Step detail link..
        $href  = "/qams-step-detail.php";
        $href .= "?project_id=$qastep->project_id";
        $href .= "&step_id=$qastep->qa_step_id";
        $label = "Detail";
        $title = "View details for this QA step";
        $link = "<a href=\"$href\" title=\"$title\">$label</a>";
        $acts[] = $link;

        $s .= "<td valign=\"top\">" . implode("&nbsp;&nbsp;", $acts) . "</td>";

        $s .= "</tr>\n";
      }
      $s .= "</table>\n";
    }
    else {
      $s .= "<p>No QA approvals defined.</p>";
    }
    return $s;
  } // QASummary

} // class qa_process

// -----------------------------------------------------------------------
/**
 * Encapsulation of a QA step record. This object contains the basic
 * QA step information.
 */
class qa_step {
  var $qa_step_id;
  var $qa_phase;
  var $qa_phase_desc;
  var $qa_document_id;
  var $qa_document_title;
  var $qa_document_desc;
  var $qa_step_desc;
  var $qa_step_notes;
  var $qa_step_order;
  var $mandatory;
  var $enabled;
  /** Whether the data is a valid (existing) QA step or not */
  var $valid = false;
  // .....................................................................
  /**
   * Constructor for a QA Step. Allows us to define the data from
   * an existing query row, optionally.
   * @param integer $qa_step_id Optional key code for the QA step.
   * @param mixed $row Optional row data for the QA step.
   */
  function qa_step($qa_step_id=0, $row=false) {
    // Key ids..
    $this->qa_step_id = $qa_step_id;
    // Suck in any data provided..
    if ($row !== false) {
      $this->assign_from_row($row);
      $this->valid = true;
    }
  } // qa_step
  // .....................................................................
  /**
   * Assign the core object variables from database record array.
   * @param array $row Database record array.
   */
  function assign_from_row($row) {
    if (is_array($row)) {
      $this->qa_step_id        = $row["qa_step_id"];
      $this->qa_phase          = $row["qa_phase"];
      $this->qa_phase_desc     = $row["qa_phase_desc"];
      $this->qa_document_id    = $row["qa_document_id"];
      $this->qa_document_title = $row["qa_document_title"];
      $this->qa_document_desc  = $row["qa_document_desc"];
      $this->qa_step_desc      = $row["qa_step_desc"];
      $this->qa_step_notes     = stripslashes($row["qa_step_notes"]);
      $this->qa_step_order     = $row["qa_step_order"];
      $this->mandatory         = ($row["mandatory"] == "t");
      $this->enabled           = ($row["enabled"] == "t");
    }
  } // assign_from_row
  // .....................................................................
  /**
   * Get this step from the database from scratch. Useful when you want
   * to fill details in for an isolated QA step.
   * @param integer $qa_step_id The QA step ID to get for this project
   */
  function get_step_data($qa_step_id) {
    $q  = "SELECT s.*, doc.*, p.*";
    $q .= "  FROM qa_step s";
    $q .= "  LEFT OUTER JOIN qa_document AS doc ON doc.qa_document_id=s.qa_document_id,";
    $q .= " qa_phase p";
    $q .= " WHERE s.qa_step_id=$qa_step_id";
    $q .= "   AND p.qa_phase=s.qa_phase";
    $q .= " ORDER BY p.qa_phase_order, s.qa_step_order";
    $qry = new PgQuery($q);
    if ($qry->Exec("qa_step::get_step_data") && $qry->rows > 0) {
      $row = $qry->Fetch(true);
      $this->qa_step_id = $qa_step_id;
      $this->assign_from_row($row);
      $this->valid = true;
    }
    return $this->valid;
  } // get_step_data
  // .....................................................................
  /**
   * Save this project QA step data.
   */
  function save() {
    $res = false;
    if ($this->valid) {
      $q  = "UPDATE qa_step SET ";
      $q .= " qa_phase = ?,";
      $q .= " qa_document_id = ?,";
      $q .= " qa_step_desc = ?,";
      $q .= " qa_step_notes = ?,";
      $q .= " qa_step_order = ?,";
      $q .= " mandatory = ?,";
      $q .= " enabled = ?";
      $q .= " WHERE qa_step_id=$this->qa_step_id";
      $qry = new PgQuery(
              $q,
              $this->qa_phase,
              $this->qa_document_id,
              $this->qa_step_desc,
              addslashes($this->qa_step_notes),
              $this->qa_step_order,
              ($this->mandatory ? "t" : "f"),
              ($this->enabled ? "t" : "f")
              );
      $res = $qry->Exec("qa_step::save");
    }
    return $res;
  } // save
} // class qa_step

// -----------------------------------------------------------------------
/**
 * Encapsulation of a QA step record for a project. This object contains
 * all of the current approvals as well as the basic QA step information.
 */
class qa_project_step extends qa_step {
  /** The project ID, and master WRMS ID for this step */
  var $project_id;
  /** The WRMS request ID associated with this step. */
  var $request_id;
  /** Unique user no. of person who has been assigned to this step. */
  var $responsible_usr;
  /** Full name of above person. */
  var $responsible_fullname;
  /** Datetime responsible person was assigned to this step */
  var $responsible_datetime;
  /** E-Mail of above person. */
  var $responsible_email;
  /** Special notes entered into the QA Plan screen against
   * this project step, over and above the set notes for
   * the step held on qa_step table.
   */
  var $special_notes = "";
  /** Array containing the last approval object
   * for this QA step */
  var $approvals;
  /** Array containing the full approval history
   * for this QA step */
  var $approvals_history;
  /** Array containing the approval types configured
   * as being required for this project QA step */
  var $approvals_required;
  /** Array containing the approval types which form
   * the default set for this QA step */
  var $approvals_default;
  /** Array containing the last approval status for the
   * given approval type of this QA step */
  var $last_approval_status;
  /** Path to template document */
  var $path_to_template;
  /** Path to example document */
  var $path_to_example;
  /** Whether the data is a valid (existing) QA step or not */
  var $valid = false;
  // .....................................................................
  /**
   * Constructor for a QA Step. Allows us to define the data from
   * an existing query row, optionally.
   * @param integer $project_id Key code for the QA step project.
   * @param integer $request_id Key code for the associated WRMS record.
   */
  function qa_project_step($project_id=0, $request_id=0, $row=false) {
    // Key ids..
    $this->project_id = $project_id;
    $this->request_id = $request_id;
    // Suck in any data provided..
    if ($row !== false) {
      $this->assign_from_row($row);
      $this->valid = true;
    }
  } // qa_step
  // .....................................................................
  /**
   * Assign the core object variables from database record array.
   * @param array $row Database record array.
   */
  function assign_from_row($row) {
    if (is_array($row)) {
      parent::assign_from_row($row);
      $this->responsible_usr      = $row["responsible_usr"];
      $this->responsible_fullname = $row["responsible_fullname"];
      $this->responsible_datetime = $row["responsible_datetime"];
      $this->responsible_email    = $row["responsible_email"];
      $this->special_notes        = stripslashes($row["notes"]);
    }
  } // assign_from_row
  // .....................................................................
  /**
   * Get this step from the database from scratch. Useful when you want
   * to work with an individual project QA step.
   * @param integer $project_id The ID of the project the step is in
   * @param integer $qa_step_id The QA step ID to get for this project
   */
  function get($project_id, $qa_step_id) {
    $q  = "SELECT ps.*, s.*, p.*, doc.*,";
    $q .= " usr.fullname AS responsible_fullname, usr.email AS responsible_email";
    $q .= "  FROM qa_project_step ps";
    $q .= "  LEFT OUTER JOIN usr ON (usr.user_no=ps.responsible_usr),";
    $q .= " qa_step s";
    $q .= "  LEFT OUTER JOIN qa_document AS doc ON (doc.qa_document_id=s.qa_document_id),";
    $q .= " qa_phase p";
    $q .= " WHERE ps.project_id=$project_id";
    $q .= "   AND ps.qa_step_id=$qa_step_id";
    $q .= "   AND s.qa_step_id=ps.qa_step_id";
    $q .= "   AND p.qa_phase=s.qa_phase";
    $q .= " ORDER BY p.qa_phase_order, s.qa_step_order";
    $qry = new PgQuery($q);
    if ($qry->Exec("qa_project_step::get") && $qry->rows > 0) {
      $row = $qry->Fetch(true);
      $this->project_id = $row["project_id"];
      $this->request_id = $row["request_id"];
      $this->assign_from_row($row);
      $this->valid = true;
    }
    return $this->valid;
  } // get
  // .....................................................................
  /**
   * Save this project QA step data.
   */
  function save() {
    $res = false;
    if ($this->project_id > 0 && $this->request_id > 0 && $this->valid) {
      $responsible_usr = (isset($this->responsible_usr) && $this->responsible_usr != "") ? $this->responsible_usr : "NULL";
      $responsible_datetime = (isset($this->responsible_datetime) && $this->responsible_datetime != "") ? "'$this->responsible_datetime'" : "NULL";
      $q  = "UPDATE qa_project_step SET ";
      $q .= " request_id=$this->request_id,";
      $q .= " responsible_usr=$responsible_usr,";
      $q .= " responsible_datetime=$responsible_datetime,";
      $q .= " notes='" . addslashes($this->special_notes) . "'";
      $q .= " WHERE project_id=$this->project_id";
      $q .= "   AND qa_step_id=$this->qa_step_id";
      $qry = new PgQuery($q);
      $res = $qry->Exec("qa_project_step::save");
    }
    return $res;
  } // save
  // .....................................................................
  /**
   * Insert this QA step into the given project. When this is called the
   * basic QA step data will already be in place, and all we need is the
   * project to create it for.
   * @param object $project Reference to project object to insert the step for
   */
  function insert_into_project(&$project) {
    // Create a WRMS request for this step..
    $wrms = new qams_request();
    $wrms->chtype = "create";
    $_POST["submit"] = "create";
    $_POST['send_no_email'] = "on"; // Stop WRMS spam

    // Some settings always in common with master project..
    $wrms->org_code     = $project->org_code;
    $wrms->system_id    = $project->system_id;
    $wrms->requester_id = $project->requester_id;
    $wrms->last_status  = $project->last_status;
    $wrms->urgency      = $project->urgency;
    $wrms->importance   = $project->importance;
    $wrms->entered_by   = $project->entered_by;
    $wrms->status_code  = "I"; // In Progress

    // Give it our brief, plus the step decription..
    $wrms->brief = "$project->brief: $this->qa_step_desc";

    // Assemble the detailed blurb..
    $s = "";

    // Condign notes pertaining to this QA step. Hopefully the
    // QA gurus will have populated the database with some very
    // useful guidance here (hint, hint!) ;-)
    if ($this->qa_step_notes != "") {
      $s .= "$this->qa_step_notes";
    }

    // Para covering the document requirement..
    if ($this->qa_document_title != "") {
      $s .= "\n\nThis QA step is concerned with a document entitled '$this->qa_document_title'. "
          . "Please attach any and all versions of that document to this work request "
          . "so that it is available for approval/review."
          ;
      if ($this->qa_document_desc != "") {
        $s .= " $this->qa_document_desc";
      }
    }

    // Boilerplate gumph..
    $s .= "\n\nThis work request has been automatically created by QAMS, to "
        . "facilitate the '$this->qa_step_desc' quality assurance step, in the "
        . "'$this->qa_phase_desc' phase of the project."
        ;

    $wrms->detailed = $s;

    // Save our newly built QA step request..
    $wrms->save_request();

    if ($wrms->request_id > 0) {
      $this->request_id = $wrms->request_id;
      $wrms->chtype = "update";
      $_POST["submit"] = "update";

      // Now link it to our main project WRMS..
      $_POST["link_type"] = "P"; // Precedes
      $_POST["parent_request_id"] = $project->request_id;
      $wrms->AddParent();

      // Make sure allocations are the same..
      if (count($project->allocated) > 0) {
        $_POST["new_allocations"] = array_keys($project->allocated);
        $wrms->NewAllocations();
      }

      // Make sure interested users are the same..
      if (count($project->interested) > 0) {
        $_POST["new_subscription"] = array_keys($project->interested);
        $wrms->NewSubscriptions();
      }

      // Finally, create the project step record itself..
      $q  = "INSERT INTO qa_project_step (";
      $q .= " project_id, qa_step_id, request_id ";
      $q .= ") ";
      $q .= "VALUES(?, ?, ?);";
      $qry = new PgQuery(
                $q,
                $project->request_id,
                $this->qa_step_id,
                $this->request_id
                );
      $qry->Exec("qa_project_step::insert_into_project");


      // And its default project step approval records too..
      foreach ($this->approvals_default() as $qa_type_id => $qa_type_desc) {
        $q  = "INSERT INTO qa_project_step_approval (";
        $q .= " project_id, qa_step_id, qa_approval_type_id ";
        $q .= ") ";
        $q .= "VALUES(?, ?, ?);";
        $qry = new PgQuery(
                  $q,
                  $project->request_id,
                  $this->qa_step_id,
                  $qa_type_id
                  );
        $qry->Exec("qa_project_step::insert_into_project");
      } // foreach

      // The data is now valid..
      $this->valid = true;
    }
    // Clear out for next step..
    $_POST = array();

  } // insert_into_project
  // .....................................................................
  /**
   * Remove a project QA step from the project. Note that this is a basic
   * method, and assumes that various checking as to the advisability of
   * doing this has been done beforehand.
   */
  function remove_project_step() {
    $ok = false;
    if ($this->project_id > 0 && $this->qa_step_id > 0) {
      $qry = new PgQuery("BEGIN");
      $ok = $qry->Exec("qa_project::delete_project");
      if ($ok) {
        $qry = new PgQuery(
                "DELETE FROM qa_project_approval"
              . " WHERE project_id=$this->project_id"
              . "   AND qa_step_id=$this->qa_step_id"
              );
        $ok = $qry->Exec("qa_project_step::remove_project_step");
      }
      if ($ok) {
        $qry = new PgQuery(
                "DELETE FROM qa_project_step_approval"
              . " WHERE project_id=$this->project_id"
              . "   AND qa_step_id=$this->qa_step_id"
              );
        $ok = $qry->Exec("qa_project_step::remove_project_step");
      }
      if ($ok) {
        $qry = new PgQuery(
                "DELETE FROM qa_project_step"
              . " WHERE project_id=$this->project_id"
              . "   AND qa_step_id=$this->qa_step_id"
              );
        $ok = $qry->Exec("qa_project_step::remove_project_step");
      }
      $qry = new PgQuery(($ok ? "COMMIT;" : "ROLLBACK;"));
      $res = $qry->Exec("qa_project_step::remove_project_step");

      // If it was removed ok, then this record is
      // by definition now invalid..
      $this->valid = !$ok;
    }
    return $ok && $res;
  } // remove_project_step
  // .....................................................................
  /**
   * Add a new required approval type to this QA step. We create the
   * appropriate database records.
   * @param integer $ap_type_id The ID of the approval type to add.
   * @return boolean True if approval type was added ok.
   */
  function add_required_approval($ap_type_id, $ap_type_desc="") {
    $ok = false;
    $this->get_approvals_required();
    if (!isset($this->approvals_required[$ap_type_id])) {
      $q  = "INSERT INTO qa_project_step_approval (";
      $q .= " project_id, qa_step_id, qa_approval_type_id ";
      $q .= ") ";
      $q .= "VALUES(?, ?, ?);";
      $qry = new PgQuery(
                $q,
                $this->project_id,
                $this->qa_step_id,
                $qa_type_id
                );
      $ok = $qry->Exec("qa_project_step::insert_into_project");
      // Refresh required approvals..
      unset($this->approvals_required);
      $this->get_approvals_required();
    }
    return $ok;
  } // add_required_approval
  // .....................................................................
  /**
   * Remove a required approval type from this QA step. We delete the
   * appropriate database records. Note that this is a fairly low
   * level method which will also remove any approvals associated
   * with this type.
   * @param integer $ap_type_id The ID of the approval type to add.
   * @return boolean True if approval type was removed ok.
   */
  function remove_required_approval($ap_type_id) {
    $ok = false;
    $this->get_approvals_required();
    if (isset($this->approvals_required[$ap_type_id])) {
      $qry = new PgQuery("BEGIN");
      $ok = $qry->Exec("qa_project::delete_project");
      if ($ok) {
        $qry = new PgQuery(
                "DELETE FROM qa_project_approval"
              . " WHERE project_id=$this->project_id"
              . "   AND qa_step_id=$this->qa_step_id"
              . "   AND qa_approval_type_id=$ap_type_id"
              );
        $ok = $qry->Exec("qa_project_step::remove_required_approval");
      }
      if ($ok) {
        $qry = new PgQuery(
                "DELETE FROM qa_project_step_approval"
              . " WHERE project_id=$this->project_id"
              . "   AND qa_step_id=$this->qa_step_id"
              . "   AND qa_approval_type_id=$ap_type_id"
              );
        $ok = $qry->Exec("qa_project_step::remove_project_step");
      }
      $qry = new PgQuery(($ok ? "COMMIT;" : "ROLLBACK;"));
      $res = $qry->Exec("qa_project_step::remove_required_approval");

      // Avoid Db access - just remove the data from our local vars..
      unset($this->approvals_required[$ap_type_id]);
      unset($this->last_approval_status[$ap_type_id]);
      if (isset($this->approvals) && isset($this->approvals[$ap_type_id])) {
        unset($this->approvals[$ap_type_id]);
      }
      if (isset($this->approvals_history) && isset($this->approvals_history[$ap_type_id])) {
        unset($this->approvals_history[$ap_type_id]);
      }
    }
    return $ok && $res;
  } // remove_required_approval
  // .....................................................................
  /**
   * Acquire the approvals set from the database for this project QA step
   * including all of the history of approvals for each approval type.
   * Note: this will only do anything if the local class variable
   * 'approvals' is unset.
   * @param boolean $force If true we re-read the data regardless
   */
  function get_approvals($force=false) {
    if ($force || (!isset($this->approvals) && isset($this->project_id) && isset($this->qa_step_id))) {
      $this->approvals = array();
      $this->approvals_history = array();
      $q  = "SELECT pa.*, apt.*,";
      $q .= "       assigned.username AS assigned_username, assigned.fullname AS assigned_fullname,";
      $q .= "       approval.username AS approval_username, approval.fullname AS approval_fullname";
      $q .= "  FROM qa_approval_type apt, qa_project_approval pa";
      $q .= "  LEFT OUTER JOIN usr AS assigned ON assigned.user_no=pa.assigned_to_usr";
      $q .= "  LEFT OUTER JOIN usr AS approval ON approval.user_no=pa.approval_by_usr";
      $q .= " WHERE pa.project_id=$this->project_id";
      $q .= "   AND pa.qa_step_id=$this->qa_step_id";
      $q .= "   AND apt.qa_approval_type_id=pa.qa_approval_type_id";
      $q .= " ORDER BY pa.approval_datetime, pa.assigned_datetime";
      $qry = new PgQuery($q);
      if ($qry->Exec("qa_process_step::get_approvals") && $qry->rows > 0) {
        while($row = $qry->Fetch(true)) {
          $ap_type_id = $row["qa_approval_type_id"];
          $approval = new qa_project_approval(
                            $this->project_id,
                            $row
                            );
          // Store the full approval histories..
          $this->approvals_history[$ap_type_id][] = $approval;

          // Store the most recent approval for each type. Since we
          // query above in ascending date order this ends up right..
          $this->approvals[$ap_type_id] = $approval;
        } // while
      }
    }
  } // get_approvals
  // .....................................................................
  /**
   * Return the array of approvals present for this step. Reads the
   * database the first time, and from then on just returns the var.
   */
  function approvals() {
    $res = array();
    if (isset($this->approvals)) {
      $res = $this->approvals;
    }
    elseif (isset($this->qa_step_id)) {
      $this->get_approvals();
      $res = $this->approvals;
    }
    return $res;
  } // approvals
  // .....................................................................
  /**
   * Acquire the required approvals set from the database for this
   * QA step. This is the list of all approval types which have to be
   * actioned, as opposed to the approval records themselves.
   * @param boolean $force If true we re-read the data regardless
   */
  function get_approvals_required($force=false) {
    if ($force || (!isset($this->approvals_required) && isset($this->qa_step_id))) {
      $this->approvals_required = array();
      $this->last_approval_status = array();
      $q  = "SELECT * FROM qa_project_step_approval psa, qa_approval_type apt";
      $q .= " WHERE psa.project_id=$this->project_id";
      $q .= "   AND psa.qa_step_id=$this->qa_step_id";
      $q .= "   AND apt.qa_approval_type_id=psa.qa_approval_type_id";
      $qry = new PgQuery($q);
      if ($qry->Exec("qa_process_step::get_approvals_required") && $qry->rows > 0) {
        while($row = $qry->Fetch()) {
          $ap_type_id = $row->qa_approval_type_id;
          $this->approvals_required[$ap_type_id] = $row->qa_approval_type_desc;
          $this->last_approval_status[$ap_type_id] = $row->last_approval_status;
        } // while
      }
    }
  } // get_approvals_required
  // .....................................................................
  /**
   * Return the array of required approvals for this step. Reads the
   * database the first time, and from then on just returns the var.
   */
  function approvals_required() {
    $res = array();
    if (isset($this->approvals_required)) {
      $res = $this->approvals_required;
    }
    elseif (isset($this->qa_step_id)) {
      $this->get_approvals_required();
      $res = $this->approvals_required;
    }
    return $res;
  } // approvals_required
  // .....................................................................
  /**
   * Acquire the default approvals set from the database for this
   * QA step. This is the list of all approval types which are to be
   * initially assigned when the step is created.
   * @param boolean $force If true we re-read the data regardless
   */
  function get_approvals_default($force=false) {
    if ($force || (!isset($this->approvals_default) && isset($this->qa_step_id))) {
      $this->approvals_default = array();
      $q  = "SELECT * FROM qa_approval ap, qa_approval_type apt";
      $q .= " WHERE ap.qa_step_id=$this->qa_step_id";
      $q .= "   AND apt.qa_approval_type_id=ap.qa_approval_type_id";
      $qry = new PgQuery($q);
      if ($qry->Exec("qa_process_step::get_approvals_default") && $qry->rows > 0) {
        while($row = $qry->Fetch()) {
          $ap_type_id = $row->qa_approval_type_id;
          $ap_type_desc = $row->qa_approval_type_desc;
          $this->approvals_default[$ap_type_id] = $ap_type_desc;
        } // while
      }
    }
  } // get_approvals_default
  // .....................................................................
  /**
   * Return the array of default approvals for this step. Reads the
   * database the first time, and from then on just returns the var.
   */
  function approvals_default() {
    $res = array();
    if (isset($this->approvals_default)) {
      $res = $this->approvals_default;
    }
    elseif (isset($this->qa_step_id)) {
      $this->get_approvals_default();
      $res = $this->approvals_default;
    }
    return $res;
  } // approvals_default
  // .....................................................................
  /**
   * Determine the overall approval status of this QA step. This involves
   * checking what is required, against what has been approved, and
   * returning a simple summary which is one of these statuses:
   *    ''  - no approval activity yet recorded on this step
   *    'p' - In progress, some but not all approvals present
   *    'y' - Approved, full set of approvals present
   *    'n' - Refused, at least one approver refused approval
   *    's' - Skipped, all approvals were skipped (PM option only)
   * @return string Overall approval status code
   */
  function overall_approval_status() {
    // Initialise..
    $status = "";
    $totreqd = 0; $approved = 0; $refused = 0; $skipped = 0; $wip = 0;
    foreach ($this->approvals_required() as $ap_type_id => $ap_desc) {
      $totreqd += 1;
      if (isset($this->last_approval_status[$ap_type_id])) {
        switch ($this->last_approval_status[$ap_type_id]) {
          case "y": $approved += 1; break;
          case "n": $refused  += 1; break;
          case "s": $skipped  += 1; break;
          case "p": $wip      += 1; break;
        } // switch
      }
    } // foreach

    // Determine status..
    if ($totreqd > 0) {
      if ($refused > 0) {
        $status = "n"; // danny de-vetoed
      }
      else {
        if ($approved == $totreqd) {
          $status = "y"; // all approved
        }
        elseif ($skipped == $totreqd) {
            $status = "s"; // all skipped
        }
        elseif (($skipped + $approved) == $totreqd) {
            $status = "y"; // all approved
        }
      }
      if ($status == "" && ($approved > 0 || $skipped > 0 || $wip > 0)) {
        $status = "p"; // in progress
      }
    }
    return $status;
  } // overall_approval_status
  // .....................................................................
  /**
   * Returns the Unix timestamp that the overall approval status of this step
   * was first set to 'Approved'. This can be useful for determining whether
   * step approvals were acquired in the expect order. Returns zero if
   * the step has never acquired 'Approved' status.
   * return integer Unix timestamp when first approved, else zero
   */
  function first_approved_timestamp() {
    $res = 0;
    $approved = array();
    $this->get_approvals();
    foreach ($this->approvals_required() as $ap_type_id => $ap_desc) {
      foreach ($this->approvals_history[$ap_type_id] as $approval) {
        if ($approval->approval_status == "y") {
          $approved[$ap_type_id] = datetime_to_timestamp($approval->approval_datetime);
          break;
        }
      } // foreach
    } // foreach

    // Set to false if not all approval types set, else max timestamp..
    foreach ($this->approvals_required() as $ap_type_id => $ap_desc) {
      if (isset($approved[$ap_type_id])) {
        if ($approved[$ap_type_id] > $res) {
          $res = $approved[$ap_type_id];
        }
      }
      else {
        $res = 0;
        break;
      }
    } // foreach

    // Return timestamp or false..
    return $res;

  } // first_approved_timestamp
  // .....................................................................
  /**
   * Determine whether the given approval type of this QA step was approved
   * by over-riding the assigned approver. If it was not overridden, then
   * we return false otherwise we return true. NB: if this hasn't been
   * approved yet, we return false.
   * @return boolean True if this QA Step was override-approved
   */
  function approval_overridden($ap_type_id) {
    $res = false;
    if (isset($this->approvals[$ap_type_id])) {
      $approval = $this->approvals[$ap_type_id];
      if ($approval->approval_datetime != ""
       && $approval->approval_by_usr != $approval->assigned_to_usr) {
          $res = true;
      }
    }
    return $res;
  } // approval_overridden
  // .....................................................................
  /**
   * Request approval for the given approval type for this QA step. This
   * creates a new 'in-progress' 'qa_project_approval' record, with the
   * given user as the assigned person.
   * @param integer $ap_type_id Approval type to approve for this step.
   * @param integer $user_no User being requested to submit approval.
   * @return boolean True if request for approval succeeded
   */
  function request_approval($ap_type_id, $user_no) {
    $res = false;
    $this->get_approvals();
    // Do we have a record loaded for this type?..
    if (isset($this->approvals[$ap_type_id])) {
      $approval = $this->approvals[$ap_type_id];
      // Can't use it if already been approved..
      if ($approval->approval_datetime != "") {
        $approval = new qa_project_approval($this->project_id);
      }
    }
    else {
      $approval = new qa_project_approval($this->project_id);
    }
    // Initialise it if it is new..
    if ($approval->qa_approval_id == 0) {
      $approval->qa_step_id = $this->qa_step_id;
      $approval->qa_approval_type_id = $ap_type_id;
    }
    // Assign the data..
    $approval->approval_status = "p"; // In Progress
    $approval->assigned_to_usr = $user_no;
    $approval->assigned_datetime = timestamp_to_datetime();
    $approval->approval_by_usr = "";
    $approval->approval_datetime = "";
    $approval->comment = "";

    // Now save it. This will insert a new record if necessary..
    $qry = new PgQuery("BEGIN");
    $ok = $qry->Exec("qa_project_step::request_approval");
    if ($ok) {
      // Save/create the approval record..
      $ok = $approval->save();
      if ($ok) {
        // Save last approval status..
        $q  = "UPDATE qa_project_step_approval SET ";
        $q .= " last_approval_status='p'";
        $q .= " WHERE project_id=$this->project_id";
        $q .= "   AND qa_step_id=$this->qa_step_id";
        $q .= "   AND qa_approval_type_id=$ap_type_id";
        $qry = new PgQuery($q);
        $ok = $qry->Exec("qa_project_step::request_approval");
      }
      if ($ok) {
        // Save current QA phase to project record..
        $q  = "UPDATE request_project SET";
        $q .= " qa_phase='$this->qa_phase'";
        $q .= " WHERE request_id=$this->project_id";
        $qry = new PgQuery($q);
        $ok = $qry->Exec("qa_project_step::request_approval");
      }
      $qry = new PgQuery(($ok ? "COMMIT;" : "ROLLBACK;"));
      $res = $qry->Exec("qa_project_step::request_approval");

      // Forced-refresh locally..
      $this->get_approvals(true);
      $this->get_approvals_required(true);
    }
    return $res;

  } // request_approval
  // .....................................................................
  /**
   * Approve a given approval type for this QA step. For complete flexibility
   * this method also also allows the user ID as a parameter, but normally this
   * will be the logged-in user doing the approval. To perform this we first
   * look at the latest approval record, and use it IF the 'approval_datetime'
   * field is still blank. Otherwise we create a new approval record.
   * @param integer $ap_type_id Approval type to approve for this step.
   * @param string $status Status to store in approval record.
   * @param string $comment Comment to add to this approval
   * @param integer $user_no User being requested to submit approval.
   * @return boolean True if approval succeeded
   */
  function approve($ap_type_id, $status, $comment, $user_no=false) {
    global $session;
    if ($user_no === false) {
      $user_no = $session->user_no;
    }
    $res = false;
    $this->get_approvals();
    // Do we have a record loaded for this type?..
    if (isset($this->approvals[$ap_type_id])) {
      $approval = $this->approvals[$ap_type_id];
      // Can't use it if already been approved..
      if ($approval->approval_datetime != "") {
        $approval = new qa_project_approval($this->project_id);
      }
    }
    else {
      $approval = new qa_project_approval($this->project_id);
    }
    // Initialise it if it is new..
    if ($approval->qa_approval_id == 0) {
      $approval->qa_step_id = $this->qa_step_id;
      $approval->qa_approval_type_id = $ap_type_id;
      $approval->assigned_to_usr = $user_no;
      $approval->assigned_datetime = timestamp_to_datetime();
    }
    // Assign the approval data. We may be writing an 'unapproved'
    // approval here, which is when status is nullstring. This is done
    // when we 'reapprove' a step..
    $approval->approval_status = $status;
    if ($status == "") {
      // UN-approval mode..
      $approval->assigned_to_usr = "";
      $approval->assigned_datetime = "";
      $approval->approval_by_usr = "";
      $approval->approval_datetime = "";
      $approval->comment = "";
    }
    else {
      // Normal approval..
      $approval->approval_by_usr = $user_no;
      $approval->approval_datetime = timestamp_to_datetime();
      $approval->comment = addslashes($comment);
    }

    $qry = new PgQuery("BEGIN");
    $ok = $qry->Exec("qa_project_step::approve");
    if ($ok) {
      // Save/create the approval record..
      $ok = $approval->save();
      if ($ok) {
        // Save last approval status..
        $q  = "UPDATE qa_project_step_approval SET";
        $q .= " last_approval_status=" . ($status != "" ? "'$status'" : "NULL");
        $q .= " WHERE project_id=$this->project_id";
        $q .= "   AND qa_step_id=$this->qa_step_id";
        $q .= "   AND qa_approval_type_id=$ap_type_id";
        $qry = new PgQuery($q);
        $ok = $qry->Exec("qa_project_step::approve");
      }
      if ($ok) {
        // Save current phase to project record..
        $q  = "UPDATE request_project SET";
        $q .= " qa_phase='$this->qa_phase'";
        $q .= " WHERE request_id=$this->project_id";
        $qry = new PgQuery($q);
        $ok = $qry->Exec("qa_project_step::approve");
      }
      $qry = new PgQuery(($ok ? "COMMIT;" : "ROLLBACK;"));
      $res = $qry->Exec("qa_project_step::approve");

      // Forced-refresh locally..
      $this->get_approvals(true);
      $this->get_approvals_required(true);
    }
    return $res;

  } // approve
  // .....................................................................
  /**
   * Set up this step for re-approval. Re-approval is when we reset any
   * current statuses and approval records to '' (nullstring), so that the
   * QA step can be worked on once again (assigned etc.) and then go
   * through the same approval process as before. Note: this will not
   * over-write any previous approval history records - usually it causes
   * a new record with blank (nullstring) status to be created.
   */
  function reapprove() {
    $comment = "Set to In Progress status for re-approval.";
    foreach ($this->approvals_required() as $ap_type_id => $ap_type_desc) {
      $this->approve($ap_type_id, "", $comment);
    } // foreach
  } // reapprove
  // .....................................................................
  /**
   * Return the QA Step assignment status. If FALSE is returned then the
   * step is not yet assigned to anyone. Otherwise, we return the user_no,
   * fullname, and email in a standard array.
   * @return mixed FALSE if not assigned, else array with user details in it.
   */
  function assigned() {
    $res = false;
    if (isset($this->responsible_usr) && isset($this->responsible_fullname)) {
      if ($this->responsible_fullname != "") {
        $res = array(
                "usr"      => $this->responsible_usr,
                "fullname" => $this->responsible_fullname,
                "datetime" => $this->responsible_datetime,
                "email"    => $this->responsible_email
                );
      }
    }
    return $res;
  } // assigned
  // .....................................................................
  /**
   * Return the editability status of a particular approval type for this
   * step. If the type is required, and has some approvals already, then
   * we would return false, else true. If the type is not required, then
   * we just return true always.
   */
  function approval_type_editable($ap_type_id) {
    $res = true;
    $this->get_approvals_required();
    if (isset($this->last_approval_status[$ap_type_id])
    && $this->last_approval_status[$ap_type_id] != "") {
      $res = false;
    }
    return $res;
  } // approval_type_editable
  // .....................................................................
  /**
   * Acquire the QA step document paths. These are the paths to the template
   * and example documents (if any) defined for our QA Step, and for the
   * QA model chosen when this project was created.
   */
  function get_documents() {
    if (isset($this->qa_document_id) && $this->qa_document_id != "") {
      $q  = "SELECT * FROM request_project rp, qa_model_documents md, qa_document d";
      $q .= " WHERE rp.request_id=$this->project_id";
      $q .= "   AND md.qa_model_id=rp.qa_model_id";
      $q .= "   AND md.qa_document_id=$this->qa_document_id";
      $qry = new PgQuery($q);
      if ($qry->Exec("qa_process_step::get_documents") && $qry->rows > 0) {
        $row = $qry->Fetch();
        $this->path_to_template = $row->path_to_template;
        $this->path_to_example  = $row->path_to_example;
      }
    }
  } // get_documents
  // .....................................................................
  /**
   * Render an approval types status listing in a table. This table contains
   * multiple rows, one approval type per row. All possible approvals types
   * are listed, with colour status and a checkbox which is checked for all
   * approval types currently required for the project.
   * @param boolean $have_admin True if we are able to edit the approvals
   * @param boolean $summary If true, only show required, and without checkboxes
   * @param boolean $aptypeid If defined, then show only this approval type
   */
  function render_approval_types($have_admin=false, $summary=false, $aptypeid=false) {
    $s = "";

    // Inner table containing rows - all approval types..
    $s .= "<table cellspacing=\"2\" cellpadding=\"0\" width=\"100%\">\n";

    foreach ($this->approvals_required() as $ap_type_id => $ap_type_desc) {
      if ($aptypeid !== false && $aptypeid != $ap_type_id) {
        continue;
      }
      $s .= "<tr class=\"$rowclass\">";
      $last_approval_status = $this->last_approval_status[$ap_type_id];
      $suffix = "";
      $this->get_approvals();
      if ($this->approvals[$ap_type_id]->approved_datetime != "") {
        $suffix = "(as of "
                . datetime_to_displaydate(
                      NICE_FULLDATETIME,
                      $this->approvals[$ap_type_id]->approval_datetime)
                . ")";
      }
      elseif ($this->approvals[$ap_type_id]->assigned_datetime != "") {
        $suffix = "(as of "
                . datetime_to_displaydate(
                      NICE_FULLDATETIME,
                      $this->approvals[$ap_type_id]->assigned_datetime
                      )
                . ")";
      }
      $desc = qa_status_coloured($last_approval_status, $ap_type_desc, $suffix);
      $s .= "<td width=\"70%\">$desc</td>";

      if ($summary === false) {
        $link = "&nbsp;";
        if ($have_admin) {
          $href  = "/qams-step-approve.php";
          $href .= "?project_id=$this->project_id";
          $href .= "&step_id=$this->qa_step_id";
          $href .= "&ap_type_id=$ap_type_id";
          $label = "[Approve]";
          $title = "Post a decision on this approval type";
          $link  = "<a href=\"$href\" title=\"$title\">$label</a>";
        }
        $s .= "<td width=\"15%\" align=\"center\" style=\"text-align:center\">$link</td>";

        $chk = "<input type=\"checkbox\""
             . " name=\"step_approval_types[]\""
             . " value=\"" . $this->qa_step_id . "|" . $ap_type_id . "\""
             . " checked"
             . (($last_approval_status == "" && $have_admin) ? "" : " disabled")
             . ">"
             ;
        $s .= "<td width=\"15%\" align=\"center\" style=\"text-align:center\">" . $chk . "</td>";
      }
    }
    $s .= "</tr>\n";

    if ($summary === false && $have_admin) {
      // Get all approval types not assigned to this step yet..
      $q = "SELECT * FROM qa_approval_type";
      $reqd_ap_ids = array_keys($this->approvals_required());
      if (is_array($reqd_ap_ids) && count($reqd_ap_ids > 0)) {
        $reqd_ap_sql = implode(",", $reqd_ap_ids);
        if ($reqd_ap_sql != "") {
          $q .= " WHERE qa_approval_type_id NOT IN ($reqd_ap_sql)";
        }
      }
      $qry = new PgQuery($q);
      if ($qry->Exec("qa_project_step::render_approval_types") && $qry->rows > 0) {
        while($row = $qry->Fetch()) {
          $ap_type_id = $row->qa_approval_type_id;
          $ap_type_desc = $row->qa_approval_type_desc;
          $s .= "<tr>";
          $s .= "<td>$ap_type_desc</td>";
          $s .= "<td>&nbsp;</td>";
          $chk = "<input type=\"checkbox\""
               . " name=\"step_approval_types[]\""
               . " value=\"" . $this->qa_step_id . "|" . $ap_type_id . "\""
               . ($have_admin ? "" : " disabled")
               . ">"
               ;
          $s .= "<td align=\"center\" style=\"text-align:center\">" . $chk . "</td>";
          $s .= "</tr>\n";
        } // while
      }
    }
    $s .= "</table>\n";
    return $s;

  } // render_approval_types
  // .....................................................................
  /**
   * Render approvals history for this QA Step as an html table.
   * Approvals history is stored as an array of arrays, where the containing
   * array is by approval type ID. The history therefore comes out as grouped
   * by approval type, and in ascending datetime order within each group.
   * @return string An HTML table containing the approval history
   */
  function render_approvals_history() {
    $s = "";
    $this->get_approvals();
    if (count($this->approvals_history) > 0) {
      $s .= "<table cellspacing=\"2\" cellpadding=\"0\" width=\"100%\">\n";

      // Some headings..
      $s .= "<tr>";
      $s .= "<th width=\"25%\" class=\"cols\">Type of approval</th>";
      $s .= "<th width=\"25%\" class=\"cols\">Assigned to</th>";
      $s .= "<th width=\"34%\" class=\"cols\">Approval</th>";
      $s .= "<th width=\"16%\" class=\"cols\">Status</th>";
      $s .= "</tr>\n";

       foreach ($this->approvals_history as $approvals) {
        $appcnt = count($approvals);
        $cnt = 0;
        foreach ($approvals as $ap_type_id => $approval) {
          $cnt += 1;
          $status = $approval->approval_status;
          $rowclass = ($cnt == $appcnt) ? "row1" : "row0";
          $s .= "<tr class=\"$rowclass\">";

          // Description..
          $s .= "<td valign=\"top\" style=\"border-top:dotted lightgrey 1px\">";
          $s .= $approval->qa_approval_type_desc;
          if ($cnt == $appcnt) {
            $s .= "<br>(current status)";
          }
          $s .= "</td>";

          // Assignment..
          if ($approval->assigned_datetime != "") {
            $ass = $approval->assigned_fullname . "<br>";
            $ass .= datetime_to_displaydate(NICE_FULLDATETIME, $approval->assigned_datetime);
          }
          else {
            $ass = "&nbsp;";
          }
          $s .= "<td valign=\"top\" style=\"border-top:dotted lightgrey 1px\">$ass</td>";

          // Approval..
          $app = "";
          if ($approval->approval_datetime != "") {
            if ($approval->assigned_username != $approval->approval_username) {
              $app .= "<span style=\"color:red\" title=\"Approval by override\">";
              $app .= $approval->approval_fullname . "</span>";
            }
            else {
              $app .= $approval->approval_fullname;
            }
            $app .= "<br>";
            $app .= datetime_to_displaydate(NICE_FULLDATETIME, $approval->approval_datetime);
            if ($approval->comment != "") {
              $app .= "<br>$approval->comment";
            }
          }
          elseif ($approval->assigned_datetime != "") {
            $app = "<span style=\"color:orange\">Awaiting approval.</span>";
          }
          else {
            $app = "&nbsp;";
          }
          $s .= "<td valign=\"top\" style=\"border-top:dotted lightgrey 1px\">$app</td>";

          // Status..
          $s .= "<td valign=\"top\" style=\"border-top:dotted lightgrey 1px\">" . qa_status_coloured($status) . "</td>";
          $s .= "</tr>\n";
        } // foreach

      } // foreach

      $s .= "</table>\n";
    }
    return $s;

  } // render_approvals

} // class qa_project_step

// -----------------------------------------------------------------------
/** Encapsulation of a QA approval record. */
class qa_project_approval {
  var $qa_approval_id = 0;
  var $project_id = 0;
  var $qa_step_id = 0;
  var $qa_approval_type_id = 0;
  var $qa_approval_type_desc = "";
  var $approval_status = "";
  var $assigned_to_usr = 0;
  var $assigned_username = "";
  var $assigned_fullname = "";
  var $assigned_datetime = "";
  var $approval_by_usr = 0;
  var $approval_username = "";
  var $approval_fullname = "";
  var $approval_datetime = "";
  var $comment = "";
  // .....................................................................
  /**
   * Constructor. Creates an approval object with options initialisation
   * of data from a database row.
   * @param integer $project_id The ID of the project the step is in
   * @param array $row Database record array.
   */
  function qa_project_approval($project_id, $row=false) {
    $this->project_id = $project_id;
    if ($row !== false) {
      $this->assign_from_row($row);
    }
  } // qa_project_approval
  // .....................................................................
  /**
   * Assign the object variables from database record array. We store
   * everything submitted in the $row variable, avoiding any duplicated
   * numeric elements present in DB row vars.
   * @param array $row Database record array.
   */
  function assign_from_row($row) {
    // Store each row property locally..
    foreach ($row as $name => $val) {
      if (!is_numeric($name)) {
        if ($name == "comment") {
          $val = stripslashes($val);
        }
        $this->{$name} = $val;
      }
    }
  } // assign_from_row
  // .....................................................................
  /**
   * Returns the number of days which have elapsed since the approval
   * was requested from (assigned to) somebody. If the approval hasn't
   * been assigned, then we just return zero.
   * @return integer Number of days since approval was assigned
   */
  function since_assignment_days() {
    $res = 0;
    if ($this->assigned_datetime != "") {
      $res = number_format((time() - datetime_to_timestamp($this->assigned_datetime)) / (3600 * 24), 0);
      if ($res < 0) {
        $res = 0;
      }
    }
    return $res;
  } // since_assignment_days
  // .....................................................................
  /**
   * Save this approval record into the approval history set. If the ID
   * is still zero then we assume a new record needs to be inserted, else
   * we update the existing one.
   * @return boolean True if the approval was safely saved.
   */
  function save() {
    $ok = false;

    // Fields which we want to NULL if not set, or nullstring..
    $assigned_to_usr   = (isset($this->assigned_to_usr)   && $this->assigned_to_usr != "")   ? $this->assigned_to_usr : "NULL";
    $approval_by_usr   = (isset($this->approval_by_usr)   && $this->approval_by_usr != "")   ? $this->approval_by_usr : "NULL";
    $assigned_datetime = (isset($this->assigned_datetime) && $this->assigned_datetime != "") ? "'$this->assigned_datetime'" : "NULL";
    $approval_datetime = (isset($this->approval_datetime) && $this->approval_datetime != "") ? "'$this->approval_datetime'" : "NULL";
    $approval_status   = (isset($this->approval_status)   && $this->approval_status != "")   ? "'$this->approval_status'" : "NULL";

    if ($this->qa_approval_id == 0) {
      // New record - grab next sequence value..
      $qry = new PgQuery("SELECT NEXTVAL('seq_qa_approval_id')");
      if ($qry->Exec()) {
        $row = $qry->Fetch(true);
        $this->qa_approval_id = $row[0];
      }

      // Create new approval..
      $q  = "INSERT INTO qa_project_approval (";
      $q .= " qa_approval_id, project_id, qa_step_id, qa_approval_type_id,";
      $q .= " approval_status, assigned_to_usr, approval_by_usr, comment,";
      $q .= " approval_datetime, assigned_datetime";
      $q .= ") ";
      $q .= "VALUES(";
      $q .= "$this->qa_approval_id,";
      $q .= "$this->project_id,";
      $q .= "$this->qa_step_id,";
      $q .= "$this->qa_approval_type_id,";
      $q .= "$approval_status,";
      $q .= "$assigned_to_usr,";
      $q .= "$approval_by_usr,";
      $q .= "'" . addslashes($this->comment) . "',";
      $q .= "$approval_datetime,";
      $q .= "$assigned_datetime";
      $q .= ")";
      $qry = new PgQuery($q);
      $ok = $qry->Exec("qa_project_approval::save");
    }
    else {
      // Existing record update..
      $q  = "UPDATE qa_project_approval SET ";
      $q .= " project_id=$this->project_id,";
      $q .= " qa_step_id=$this->qa_step_id,";
      $q .= " qa_approval_type_id=$this->qa_approval_type_id,";
      $q .= " approval_status=$approval_status,";
      $q .= " assigned_to_usr=$assigned_to_usr,";
      $q .= " approval_by_usr=$approval_by_usr,";
      $q .= " comment='" . addslashes($this->comment) . "',";
      $q .= " approval_datetime=$approval_datetime,";
      $q .= " assigned_datetime=$assigned_datetime";
      $q .= " WHERE qa_approval_id=$this->qa_approval_id";
      $qry = new PgQuery($q);
      $ok = $qry->Exec("qa_project_approval::save");
    }
    return $ok;
  } // save

} // class qa_project_approval

// -----------------------------------------------------------------------
// UTILITY FUNCTIONS
/**
 * Returns the 'nice' decoded description for a QAMS approval status code.
 * @param string $code Status code: 'y', 'n', 'p', or 's'
 */
function qa_approval_status($code) {
  switch ($code) {
    case "y": return "Approved"; break;
    case "n": return "Refused"; break;
    case "s": return "Skipped"; break;
    case "p": return "In progress"; break;
     default: return "";
  } // switch
} // qa_approval_status

// .......................................................................
/**
 * Returns the key colour for a QAMS approval status code. Used in
 * highlighting stuff in QAMS.
 * @param string $code Status code: 'y', 'n', 'p', or 's'
 */
function qa_approval_colour($code) {
  switch ($code) {
    case "y": return "green"; break;
    case "n": return "red"; break;
    case "s": return "#E260E0"; break;
    case "p": return "blue"; break;
     default: return "";
  } // switch
} // qa_approval_colour

// .......................................................................
/**
 * Returns the 'nice' decoded description for a QAMS approval
 * status code, and spanned with the appropriate colour.
 * @param string $code Status code: 'y', 'n', 'p', or 's'
 * @param string $label Optional replacement label, displaces status to title
 */
function qa_status_coloured($code, $label="", $suffix="") {
  $status = qa_approval_status($code);

  $title = $status;
  if ($label == "") {
    $label = $status;
  }
  else {
    $title = $status;
  }
  if ($label == "") $label = "--";
  if ($title == "") $title = "No approvals";

  return  "<span "
        . "style=\"color:" . qa_approval_colour($code) . "\""
        . "title=\"$title $suffix\""
        . ">"
        . $label
        . "</span>";
} // qa_status_coloured
// -----------------------------------------------------------------------
?>
