
var organisation_id = -1;
var person_id = -1;
var system_code = "";

var popt_rx = /^Person: <option value="(.+)">(.+)<\/option>/i
var sopt_rx = /^System: <option value="(.+)">(.+)<\/option>/i
var number_rx = /^[0-9\.]*$/
var date_rx = /^([0-9][0-9]?-[0-1]?[0-9]-[0-9][0-9]*|today|yesterday|now|tomorrow)$/

var person_options;
var system_options;

var org_sel;
var per_sel;
var sys_sel;

var xmlhttp;
/*@cc_on @*/
/*@if (@_jscript_version >= 5)
// JScript gives us Conditional compilation, we can cope with old IE versions.
// and security blocked creation of the objects.
 try {
  xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
 } catch (e) {
  try {
   xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
  } catch (E) {
   xmlhttp = false;
  }
 }
@else
 xmlhttp = false;
@end @*/
if (!xmlhttp && typeof XMLHttpRequest!=undefined) {
  xmlhttp = new XMLHttpRequest();
} else {
  xmlhttp = false;
}

function CleanSelectOptions( sel ) {
  for ( var i=0; i < sel.options.length; i++ ) {
//    if ( ! sel.options[i].selected ) {
      sel.options[i] = null;
      i--;
//    }
  }
}

function OrganisationChanged() {
  var new_org_id = document.forms.form.org_code.value;
  per_sel = document.forms.form.requester_id;
  sys_sel = document.forms.form.system_code;
  if ( new_org_id != organisation_id ) {
    organisation_id = new_org_id;
    xmlhttp.open("GET", "/js.php?org_code=" + new_org_id, true);
    xmlhttp.onreadystatechange = function() {
      if (xmlhttp.readyState==4) {
        var rsp = xmlhttp.responseText;
        var lines = rsp.split("\n");
        var old_person_id = per_sel.value;
        var old_system_code = sys_sel.value;
        CleanSelectOptions(per_sel);
        CleanSelectOptions(sys_sel);
        per_sel.options[0] = new Option( "--- select a person ---", "" );
        sys_sel.options[0] = new Option( "--- select a system ---", "" );
        person_id = -1;
        system_code = "";
        for ( var i=0; i < lines.length; i++ ) {
          if ( lines[i].match( popt_rx ) ) {
            per_sel.options[per_sel.options.length] = new Option( lines[i].replace( popt_rx, "$2"), lines[i].replace( popt_rx, "$1") )
            if ( per_sel.options[per_sel.options.length - 1].value == old_person_id ) {
              per_sel.options.selectedIndex = per_sel.options.length - 1;
            }
          }
          else if ( lines[i].match( /^System:/ ) ) {
            sys_sel.options[sys_sel.options.length] = new Option( lines[i].replace( sopt_rx, "$2"), lines[i].replace( sopt_rx, "$1") )
            if ( sys_sel.options[sys_sel.options.length - 1].value == old_system_code ) {
              sys_sel.options.selectedIndex = sys_sel.options.length - 1;
            }
          }
        }

        document.forms.form.requester_id.value
      }
    }
    xmlhttp.send(null)
  }
}


function PersonChanged() {
  // Force the organisation to match...
  per_sel = document.forms.form.requester_id;
  var new_person_id = per_sel.value;
  var person_orgcode = per_sel.options[per_sel.selectedIndex].text.replace( /^.+\(([^(]+)\)$/, "$1" );

  org_sel = document.forms.form.org_code;
  var org_rx = new RegExp( ("^.+\\(" + person_orgcode + "[)]$") );
  for ( var i=0; i < org_sel.options.length; i++ ) {
    if ( org_sel.options[i].text.match( org_rx ) ) {
      org_sel.selectedIndex = i;
      OrganisationChanged();
      person_id = new_person_id;
      organisation_id = org_sel.value;
      return true;
    }
  }
  alert("Couldn't find organisation for " + person_orgcode + "!");
  return false;
}

function SystemChanged() {
  // Force the organisation to match...
  sys_sel = document.forms.form.system_code;
  system_code = sys_sel.value;
}

function ValidNumber( num_value ) {
  // Check if this is a valid number
  if ( ! num_value.value.match( number_rx ) ) {
    alert("That is not a plain number!", "title" );
    num_value.focus();
//    return false;
  }
}

function CheckDate(objField)
{
  if ( objField.value != "" ) {
    if ( ! objField.value.match( date_rx ) ) {
      alert("That is not a valid date");
      objField.focus();
    }
  }
  return objField.value;
}

function CheckNumber(objField,min,max)
{
  if ( objField.value != "" ) {
    CheckNum = parseFloat(objField.value);
    if (isNaN(CheckNum)) {
      alert("That is not a number");
      objField.focus();
      return objField.value;
    }
    else {
      if (CheckNum >= min && CheckNum <= max)
        return CheckNum;
      else {
        alert("This field must be greater than or equal to " + min + " and less than or equal to " + max)
        objField.focus();
        return CheckNum
      }
    }
  }
  return "";
}

function CheckRequestForm() {
  if ( person_id < 0 || system_code == "" || organisation_id < 0 ) {
    per_sel = document.forms.form.requester_id;
    person_id = per_sel.value;
    org_sel = document.forms.form.org_code;
    organisation_id = org_sel.value;
    sys_sel = document.forms.form.system_code;
    system_code = sys_sel.value;
    if ( person_id <= 0 || system_code == "" || organisation_id <= 0 ) {
      alert("You must select appropriate organisation,\nperson and system details!" );
      return false;
    }
  }
  if ( document.forms.form.brief.value == "" ) {
    alert("All requests should have a brief description!" );
    document.forms.form.brief.focus();
    return false;
  }
  if ( document.forms.form.quote_brief.value != "" ) {
    if ( document.forms.form.quote_amount.value == "" ) {
      alert("If you enter a quote, you must enter an amount!" );
      document.forms.form.quote_amount.focus();
      return false;
    }
    if ( ! document.forms.form.quote_amount.value.match( number_rx ) ) {
      alert("The quoted amount should be a plain number!" );
      document.forms.form.quote_amount.focus();
      return false;
    }
  }
  return true;
}

//////////////////////////////////////////////////////////
// Since PHP wants a field returning multiple values to
// be named like "variable[]"
//////////////////////////////////////////////////////////
function GetFieldFromName(fname) {
  for ( i=0; i < document.forms.form.elements.length ; i++ ) {
    if ( document.forms.form.elements[i].name == fname ) {
      return document.forms.form.elements[i];
    }
  }
  alert("Field " + append_fname + " was not found in the form!");
  return false;
}

//////////////////////////////////////////////////////////
// Insert new values into the list from the selection
//////////////////////////////////////////////////////////
function AllocateSelected(select_from, append_fname) {
  append_to = GetFieldFromName(append_fname)
  if ( ! append_to  ) return false;

  if ( select_from.options.length == 0 ) return false;
  sel_id = select_from.value;
  if ( append_to.value == "" ) {
    append_to.value = sel_id;
  }
  else {
    append_to.value += "," + sel_id;
  }
  i = append_to.options.length;
  append_to.options[i] = select_from.options[select_from.selectedIndex];
  append_to.options[i].selected = true;
  append_to.size = i + 1;
  select_from.options[select_from.selectedIndex] = null;
}

function UnallocateSelected(remove_from, display_to) {
  sel_id = select_from.value;
  if ( append_to.value == "" ) {
    append_to.value = sel_id;
  }
  else {
    append_to.value += "," + sel_id;
  }
}
