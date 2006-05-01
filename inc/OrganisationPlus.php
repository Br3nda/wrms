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
  function OrganisationPlus( $id ) {
    global $c, $session;

    // Call the parent constructor
    $this->DBRecord();

    $this->org_code = $id;
    $keys['org_code'] = $this->org_code;

    // Initialise the record
    $this->Initialise('organisation_plus',$keys);

    if ( $this->org_code > 0 ) {
      $this->Read();
      $c->page_title = $this->Get("org_name");
    }
    else {
      $this->new_record = true;
      $this->org_code = 0;
      $c->page_title = "New Organisation";

      // Assign some defaults because it looks like we're starting a new one
      if ( isset($_GET['org_template']) ) {
        // Oh goody, we could get some defaults from a saved template :-)
      }
    }

    $session->Dbg("OrganisationPlus", "Initialising new organisation values");

    // Initialise to standard default values

    // Validation rules for the form fields.
    $this->validation = new Validation("organisation_validation");
    // field name, error message, function name
    $this->validation->AddRule("username", "You have not entered a user name.", "not_empty");
    $this->validation->AddRule("fullname", "You have not entered a full name.", "not_empty");
    $this->validation->AddRule("email", "You have not entered an email address.", "not_empty");
    $this->validation->AddRule("email", "Your email address is invalid.", "valid_email_format");
  }

  /**
  * Render the form / viewer as HTML to show the userclient
  * @return string An HTML fragment to display in the page.
  */
  function Render( ) {
    global $c, $session;

    $html = "";
    $session->Dbg("OrganisationPlus", "Render: type=insert" );

    $ef = new EntryForm( $REQUEST_URI, $this->Values, true );
    $ef->NoHelp();  // Prefer this style, for the moment

    $onsubmit = $this->validation->func_name; // retrieve the name of the onsubmit javascript function
    $html .= $ef->StartForm( array("autocomplete" => "off", "onsubmit" => "return $onsubmit(this)" ) );

    $html .= "<table class=\"data\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">\n";

    $html .= $this->RenderOrganisationDetails($ef);
    $html .= $this->RenderDefaultSystem($ef);
    $html .= $this->RenderPrimaryUser($ef);

    // Render the Javascript validation rules for the form
    $html .= $this->validation->RenderJavascript();

    $html .= "</table>\n";
    $html .= '<div id="footer">';
    $html .= $ef->SubmitButton( "submit", (("insert" == $this->WriteType) ? "Create" : "Update") );
    $html .= '</div>';
    $html .= $ef->EndForm();

$html .= <<<EOSCRIPT
<script language="JavaScript">
function InviteChanged(invite) {
  invite.form.new_password.disabled = invite.checked;
  invite.form.confirm_password.disabled = invite.form.new_password.disabled;
  return true;
}
</script>
EOSCRIPT;

    return $html;
  }


  /**
  * Render the Organisational Details part of the form
  * @return string An HTML fragment to display in the page.
  */
  function RenderOrganisationDetails( $ef ) {
    global $c, $session;

    $html = $ef->BreakLine( $c->page_title . " Details");

    if ( !$this->new_record ) {
      $html .= $ef->DataEntryLine( "Org. Code", "$this->org_code");
    }

    // Name
    $html .= $ef->DataEntryLine( "Name", "%s", "text", "org_name",
              array( "size" => 70, "title" => "The name of the organisation.") );

    // Abbreviation
    $html .= $ef->DataEntryLine( "Abbrev", $this->abbreviation, "text", "abbreviation",
              array("size" => "8", "title" => "A short abbreviation for the organisation.") );

    $html .= $ef->DataEntryLine( "SLA?", ($this->current_sla == 't' ? "Current SLA" : "No SLA"), "checkbox", "current_sla",
              array("title" => "Does this organisation have an SLA?") );

    $html .= $ef->DataEntryLine( "Debtor #", $this->debtor_no, "integer", "debtor_no",
              array("size" => "5", "title" => "The code for this organisation in the accounting system.") );

    $html .= $ef->DataEntryLine( "Hourly Rate", $this->work_rate, "numeric", "work_rate",
              array("size" => "8", "title" => "The default hourly rate for this organisation.") );

    return $html;
  }


  /**
  * Render the Organisational Details part of the form
  * @return string An HTML fragment to display in the page.
  */
  function RenderDefaultSystem( $ef ) {
    global $session;

    $html = $ef->BreakLine("Default System");

    $html .= $ef->DataEntryLine( "System Name", "%s", "text", "system_desc",
              array( "size" => 50, "title" => "The name of the general system that this organisation will log requests against."));

    $html .= $ef->DataEntryLine( "Short Name", "%s", "text", "system_code",
              array( "size" => 12, "title" => "A short abbreviated name for the system."));

    return $html;
  }



  /**
  * Render the User Details part of the form
  * @return string An HTML fragment to display in the page.
  */
  function RenderPrimaryUser( $ef ) {
    global $session;

    $html = $ef->BreakLine("Primary User Details");

    $html .= $ef->DataEntryLine( "User Name", "%s", "text", "username",
              array( "size" => 20, "title" => "The name this user can log into the system with."));

    $this->Set('invite',true);
    $html .= $ef->DataEntryLine( "Send Invite", "%s", "checkbox", "invite",
              array( "size" => 20,
                     "id" => "invite",
                     "title" => "E-mail the user an invitation to log on.",
                     "onchange" => "InviteChanged(this);",
                     "_label" => "Send an invitation with a temporary password"
                    ));

    $this->Set('new_password','******');
    // unset($_POST['new_password']);
    $html .= $ef->DataEntryLine( "Password", "%s", "password", "new_password",
              array( "size" => 20, "title" => "The user's password for logging in.", "disabled" => "true"));
    $this->Set('confirm_password', '******');
    // unset($_POST['confirm_password']);
    $html .= $ef->DataEntryLine( "Confirm Pw", "%s", "password", "confirm_password",
              array( "size" => 20, "title" => "Confirm the new password.", "disabled" => "true") );

    $html .= $ef->DataEntryLine( "Full Name", "%s", "text", "fullname",
              array( "size" => 50, "title" => "The full name of the user."));

    $html .= $ef->DataEntryLine( "Email", "%s", "text", "email",
              array( "size" => 50, "title" => "The user's e-mail address."));

    $html .= $ef->DataEntryLine( "Location", "%s", "text", "location",
              array( "size" => 50, "title" => "The user's normal location within their organisation.") );

    $html .= $ef->DataEntryLine( "Phone", "%s", "text", "phone",
              array( "size" => 20, "title" => "The user's normal phone number during business hours.") );

    $html .= $ef->DataEntryLine( "Mobile", "%s", "text", "mobile",
              array( "size" => 20, "title" => "The user's mobile phone number.") );

    return $html;
  }



  /**
  * Validate the information the user submitted
  * @return boolean Whether the form data validated OK.
  */
  function Validate( ) {
    global $session, $c;
    $session->Dbg("OrganisationPlus", "Validate: Validating registration");

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

    $this->Set("role", 'C' );  // This user will always have coordinate role to start with
    $this->Set("email_ok", true );
    $this->Set("status", 'A' );  // Active
    $this->Set("organisation_specific", true );
    $this->Set("system_active", true );
    $this->Set("org_active", true );

    $session->Dbg("OrganisationPlus", "Validate: OrganisationPlus %s validation", ($valid ? "passed" : "failed"));
    return $valid;
  }

  /**
  *
  */
  function Write() {
    global $c;

    if( parent::Write() && $this->new_record ) {

      $qry = new PgQuery( "SELECT currval('organisation_org_code_seq');" );
      $qry->Exec("OrganisationPlus::Write: Retrieve org_code");
      $sequence_value = $qry->Fetch(true);  // Fetch as an array
      $org_code = $sequence_value[0];
    }
  }
}

?>