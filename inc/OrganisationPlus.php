<?php
/**
* A class to handle writing and validating usr_client_bank records.
*
* @package   wrms
* @author    Andrew McMillan <andrew@catalyst.net.nz>
* @copyright Catalyst .Net Ltd
* @license   GPLv2
*/

/**
* We need to access some session information.
*/
require_once("Session.php");

/**
* We use the DataEntry class for data display and updating
*/
require_once("DataEntry.php");

/**
* We use the DataUpdate class and inherit from DBRecord
*/
require_once("DataUpdate.php");

/**
* We use the Validation class to add rules for the form
*/
require_once("Validation.php");

/**
* A class to handle writing and validating usr_client_bank records.
* @package   wrms
* @subpackage   OrganisationPlus
*/
class OrganisationPlus extends DBRecord {

  /**#@+
  * @access private
  */

  /**
  * A validation object for storing form field rules
  * @var object
  */
  var $validation;

  /**#@-*/

  /**
  * The constructor initialises a new record.
  */
  function OrganisationPlus() {
    global $session;

    // Call the parent constructor
    $this->DBRecord();

    $this->org_code = 0;
    $keys['org_code'] = 0;

    // Initialise the record
    $this->Initialise('org_plus_system_plus_usr',$keys);
    $this->Read();

    $session->Log("DBG: Initialising new registration values");

    // Initialise to standard default values

    // Validation rules for the form fields.
    $this->validation = new Validation("registration_validation");
    // field name, error message, function name
    $this->validation->AddRule("username", "You have not entered a user name.", "not_empty");
    $this->validation->AddRule("fullname", "You have not entered a full name.", "not_empty");
    $this->validation->AddRule("email", "You have not entered an email address.", "not_empty");
    $this->validation->AddRule("email", "Your email address is invalid.", "valid_email_format");
    $this->validation->AddRule("bank_name", "You have not entered a bank name.", "not_empty");
    $this->validation->AddRule("bank_abbr", "You have not entered a bank abbreviation.", "not_empty");
  }

  /**
  * Render the form / viewer as HTML to show the userclient
  * @return string An HTML fragment to display in the page.
  */
  function Render( ) {
    global $session;

    $html = "";
    $session->Log("DBG: OrganisationPlus::Render: type=insert" );

    $ef = new EntryForm( $REQUEST_URI, $this->Values, true );
    $ef->NoHelp();  // Prefer this style, for the moment

    $onsubmit = $this->validation->func_name; // retrieve the name of the onsubmit javascript function
    $html .= $ef->StartForm( array("autocomplete" => "off", "onsubmit" => "return $onsubmit(this)" ) );

    $html .= "<table class=\"data\" cellspacing=\"0\" cellpadding=\"0\">\n";

    $html .= $ef->BreakLine("OrganisationPlus Details");

    $html .= $ef->DataEntryLine( "User Name", "%s", "text", "username",
              array( "size" => 20, "title" => "The name this user can log into the system with."));

    $this->Set('new_password','******');
    unset($_POST['new_password']);
    $html .= $ef->DataEntryLine( "Password", "%s", "password", "new_password",
              array( "size" => 20, "title" => "The user's password for logging in."));
    $this->Set('confirm_password', '******');
    unset($_POST['confirm_password']);
    $html .= $ef->DataEntryLine( "Confirm Password", "%s", "password", "confirm_password",
              array( "size" => 20, "title" => "Confirm the new password.") );

    $html .= $ef->DataEntryLine( "Full Name", "%s", "text", "fullname",
              array( "size" => 50, "title" => "The description of the system."));

    $html .= $ef->DataEntryLine( "Email", "%s", "text", "email",
              array( "size" => 50, "title" => "The user's e-mail address."));

    $html .= $ef->DataEntryLine( "Bank Name", "%s", "text", "bank_name",
              array( "size" => 50, "title" => "The name of your bank."));

    $html .= $ef->DataEntryLine( "Bank Abbreviation", "%s", "text", "bank_abbr",
              array( "size" => 8, "title" => "The abbreviated name of your bank.", "maxlength" => "8"));

    // Render the Javascript validation rules for the form
    $html .= $this->validation->RenderJavascript();

    $html .= "</table>\n";
    $html .= '<div id="footer">';
    $html .= $ef->SubmitButton( "submit", (("insert" == $this->WriteType) ? "Create" : "Update") );
    $html .= '</div>';
    $html .= $ef->EndForm();

    return $html;
  }


  /**
  * Validate the information the user submitted
  * @return boolean Whether the form data validated OK.
  */
  function Validate( ) {
    global $session, $c;
    $session->Log("DBG: OrganisationPlus::Validate: Validating registration");

    $valid = $this->validation->Validate($this);

    // Password changing is a little special...
    if ( $_POST['new_password'] != "******" && $_POST['new_password'] != ""  ) {
      if ( $_POST['new_password'] == $_POST['confirm_password'] ) {
        $this->Set('password',$_POST['new_password']);
      }
      else {
        $c->messages[] = "ERROR: The new password must match the confirmed password.";
        $valid = false;
      }
    }

    $this->Set("is_banker", true );
    $this->Set("active", false );

    $session->Log("DBG: OrganisationPlus::Validate: OrganisationPlus %s validation", ($valid ? "passed" : "failed"));
    return $valid;
  }

  /**
  *
  */
  function Write() {
    global $c;

    if( parent::Write() ) {

      $qry = new PgQuery( "SELECT currval('usr_org_code_seq');" );
      $qry->Exec("OrganisationPlus::Write: Retrieve org_code");
      $sequence_value = $qry->Fetch(true);  // Fetch as an array
      $org_code = $sequence_value[0];
    }
  }
}

?>