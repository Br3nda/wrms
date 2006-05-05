<?php
/************************************************************************/
/* CATALYST Php  Source Code                                            */
/* Copyright (C)2002 Catalyst IT Limited                                */
/*                                                                      */
/* Filename:    qams-request-defs.php                                   */
/* Author:      Paul Waite                                              */
/* Date:        February 2002                                           */
/* Description: Customised request class for QAMS                       */
/*                                                                      */
/************************************************************************/
include_once("Request.class");

// -----------------------------------------------------------------------
/**
 * This class is a container for a WRMS request object. It extends the
 * WRMS 'Request' class and overrides some of its methods for QAMS.
 */
class qams_request extends Request {
  /** Local, independent flag of newness.. */
  var $new_request = false;
  /** List of interested users: user_no and full name */
  var $interested = array();
  /** List of allocated users: user_no and full name  */
  var $allocated = array();
  /** List of interested email recipients: user_no and email */
  var $interested_email = array();
  /** List of allocated email recipients: user_no and email  */
  var $allocated_email = array();
  /** Whether to let WRMS do emails or not (default not) */
  var $send_no_email = "on";
  /**
   * The names of database fields we are interested in
   * maintaining as part of the project definition. These
   * are fields which get POSTed and maintained by forms.
   */
  var $post_fields = array(
        "qa_model_id",
        "project_manager",
        "qa_mentor",
        "brief",
        "detailed",
        "request_on",
        "request_type",
        "last_status",
        "status_code",
        "urgency",
        "importance",
        "requester_id",
        "org_code",
        "system_id",
        "requested_by_date",
        "agreed_due_date"
        );
  // .....................................................................
  /** Construct a new WRMS request object. Note that this uses the integer
   * value of zero (0) to denote 'new' request to be compatible with
   * the way the parent class functions.
   * @param integer $id Unique request ID. Zero means new request.
   */
  function qams_request($id=0) {
    // Call the parent constructor. Note that this will
    // set this->request_id from the database record..
    $this->Request($id);
    if ($this->new_record) {
      $this->new_request = true;
      $this->initialise();
    }

  } // qams_request
  // .....................................................................
  /**
   * Initialise some QAMS request values. Used for new QAMS requests.
   */
  function initialise() {
    global $session;
    $this->request_on = date("Y-m-d H:i:s");
    $this->request_type = 90;
    $this->request_type_desc = "Quality Assurance";
    $this->entered_by = $session->user_no;
    $this->last_activity = date("Y-m-d H:i:s");
    $this->active = true;
  } // initialise
  // .....................................................................
  /**
   * Get a specific request ID from the database. Normally not required,
   * since the constructor usually reads the record for the given ID.
   * @param integer $id Request ID to read from database, 0=New request
   */
  function get_request($id=0) {
    $this->ReadRequest($id);
    if ($this->new_record) {
      $this->new_request = true;
      $this->request_id = 0;
    }
    else {
      $this->new_request = false;
      $this->request_id = $id;
    }
  } // get_request
  // .....................................................................
  /**
   * Get interested users, and stash them in an array.
   */
  function get_interested() {
    $this->interested = array();
    $q  = "SELECT request_interested.*, usr.*";
    $q .= "  FROM request_interested JOIN usr ON (request_interested.user_no=usr.user_no)";
    $q .= " WHERE request_id=$this->request_id";
    $q .= " ORDER BY request_id, usr.fullname";
    $qry = new PgQuery($q);
    if ($qry->Exec("qams_request::get_interested") && $qry->rows > 0) {
      while( $row = $qry->Fetch() ) {
        $user_no = $row->user_no;
        $this->interested[$user_no] = $row->fullname;
        $this->interested_email[$row->email] = $row->fullname;
      }
    }
  } // get_interested
  // .....................................................................
  /**
   * Get allocated users, and stash them in an array.
   */
  function get_allocated() {
    $this->allocated = array();
    $q  = "SELECT request_allocated.*, usr.*";
    $q .= "  FROM request_allocated JOIN usr ON (allocated_to_id=user_no)";
    $q .= " WHERE request_id=$this->request_id ";
    $q .= " ORDER BY request_id";
    $qry = new PgQuery($q);
    if ($qry->Exec("qams_request::get_allocated") && $qry->rows > 0) {
      while( $row = $qry->Fetch() ) {
        $user_no = $row->user_no;
        $this->allocated[$user_no] = $row->fullname;
        $this->allocated_email[$row->email] = $row->fullname;;
      }
    }
  } // get_allocated
  // .....................................................................
  /** Save the request to the database. This is a QAMS-specific DB save
   * method, just catering for the data we need to setup up the basic
   * WRMS records.
   */
  function save_request() {
    $saved = false;
    // We do this thing with the _POST array so that our class
    // vars get saved. This is to cater to WRMS Write() logic..
    foreach ($this->post_fields as $posted_name) {
      $_POST["$posted_name"] = $this->{$posted_name};
    }

    // Whether to let WRMS do its e-mail thing. Normally we do
    // emails directly from QAMS..
    if ($this->send_no_email) {
      $_POST["send_no_email"] = "on";
    }

    if ($this->Validate(true)) {
      $saved = $this->Write(true);
      if ($saved && $this->new_request) {
        // Fetch the request_id for this record.
        $q = "SELECT currval('request_request_id_seq');";
        $qry = new PgQuery($q);
        $qry->Exec("WR::Write");
        $row = $qry->Fetch(true);    // Fetch results as array
        $this->request_id = $row[0];
        // Not new anymore..
        $this->new_request = false;
      }
    }
    return $saved;
  } // save_request

} // class qams_request

// -----------------------------------------------------------------------
?>
