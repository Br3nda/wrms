
var organisation_id = -1;
var person_id = -1;
var system_code = "";

var popt_rx = /^Person: <option value="(.+)">(.+)<\/option>/i
var sopt_rx = /^System: <option value="(.+)">(.+)<\/option>/i
var aopt_rx = /^Subscriber: <option value="(.+)">(.+)<\/option>/i
var orgtag_rx = /^OrgTag: <option value="(.+)">(.+)<\/option>/i
var number_rx = /^[0-9\.]*$/
var date_rx = /^([0-9][0-9]?-[0-1]?[0-9]-[0-9][0-9]*|today|yesterday|now|tomorrow)$/

var person_options;
var system_options;

var org_sel;
var per_sel;
var sys_sel;
var alloc_sel;
var subsc_sel;
var orgtag_sel;

var looks_like_ie = true;
var xmlhttp=false;
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
@end @*/
if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
  xmlhttp = new XMLHttpRequest();
  looks_like_ie = false;
}

function CleanSelectOptions( sel ) {
  for ( var i=0; i < sel.options.length; i++ ) {
    sel.options[i] = null;
    i--;
  }
}

function OrganisationChanged() {
  var new_org_id = document.forms.form.org_code.value;
  per_sel = document.forms.form.requester_id;
  sys_sel = document.forms.form.system_code;
  alloc_sel = document.forms.form.allocatable;
  subsc_sel = document.forms.form.subscribable;
  orgtag_sel = document.forms.form.orgtaglist;
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
        CleanSelectOptions(alloc_sel);
        CleanSelectOptions(subsc_sel);
        CleanSelectOptions(orgtag_sel);
        per_sel.options[0] = new Option( "-- Any Requester --", "" );
        sys_sel.options[0] = new Option( "-- All Systems --", "" );
        alloc_sel.options[0] = new Option( "-- Any Assigned User --", "" );
        alloc_sel.options[1] = new Option( "-- Not Yet Allocated --", "all" );
        subsc_sel.options[0] = new Option( "-- Any Interested User --", "" );
        orgtag_sel.options[0] = new Option( "-- Any Tag --", "" );
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
          else if ( lines[i].match( /^Subscriber:/ ) ) {
            alloc_sel.options[alloc_sel.options.length] = new Option( lines[i].replace( aopt_rx, "$2"), lines[i].replace( aopt_rx, "$1") )
            subsc_sel.options[subsc_sel.options.length] = new Option( lines[i].replace( aopt_rx, "$2"), lines[i].replace( aopt_rx, "$1") )
          }
          else if ( lines[i].match( /^OrgTag:/ ) ) {
            orgtag_sel.options[orgtag_sel.options.length] = new Option( lines[i].replace( orgtag_rx, "$2"), lines[i].replace( orgtag_rx, "$1") )
          }
        }
        FixTagOptions(orgtag_sel);

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

function CheckSearchForm() {
  return true;
}

//////////////////////////////////////////////////////////
// Since PHP wants a field returning multiple values to
// be named like "variable[]"
//////////////////////////////////////////////////////////
function GetFieldFromName(fname) {
  for ( var i=0; i < document.forms.form.elements.length ; i++ ) {
    if ( document.forms.form.elements[i].name == fname ) {
      return document.forms.form.elements[i];
    }
  }
  alert("Field " + fname + " was not found in the form!");
  return false;
}

//////////////////////////////////////////////////////////
// Insert new values into the list from the selection
//////////////////////////////////////////////////////////
function AssignSelected(select_from, append_fname) {
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
  var j = select_from.selectedIndex;
  var i = append_to.options.length;
  append_to.options[i] = select_from.options[j];
  append_to.options[i].selected = true;
  append_to.size = i + 1;

  if ( j < select_from.options.length ) {
    select_from.options[j].selected = true;
  }
}

//////////////////////////////////////////////////////////
// Copy the options from one select to another.
//////////////////////////////////////////////////////////
function CopyOptions( from_field, to_field ) {
  var currval = 'nothing is selected yet';
  if ( to_field.length > 0 && to_field.selectedIndex < to_field.length ) {
    currval = to_field.value;
  }
  CleanSelectOptions(to_field);
  var found_currval = false;
  for ( var i=0 ; i < from_field.length ; i ++ ) {
    to_field.options[i] = new Option( from_field.options[i].text, from_field.options[i].value, false, false);
    if ( from_field.options[i].value == currval ) {
      found_currval = true;
    }
  }
  if ( found_currval ) {
    to_field.value = currval;
  }
}

//////////////////////////////////////////////////////////
// Fix the Tag options to make them match the organisation ones
//////////////////////////////////////////////////////////
function FixTagOptions(from_field) {
  var old_field;
  var new_field;
  var the_parent;
  var currval;
  for( var i=0; i < stanza_count; i++ ) {
    old_field = GetFieldFromName('tag_list['+ i +']');
    currval = old_field.value;
    the_parent = old_field.parentNode;
    new_field = from_field.cloneNode(true);
    new_field.setAttribute('name', 'tag_list[' + i + ']' );
    new_field.value = currval;
    the_parent.replaceChild( new_field, old_field );
  }
}

//////////////////////////////////////////////////////////
// Save the current settings in the Tag panel so we can restore them
//////////////////////////////////////////////////////////
var tag_and_vals = new Array();
var tag_lb_vals = new Array();
var tag_list_vals = new Array();
var tag_rb_vals = new Array();
function SaveTagSettings() {
  var fld;
  for( var i=0; i < stanza_count; i++ ) {
    if ( i > 0 ) {
      fld = GetFieldFromName('tag_and[' + i + ']');
      tag_and_vals[i] = fld.value;
    }
    fld = GetFieldFromName('tag_lb[' + i + ']');
    tag_lb_vals[i] = fld.value;
    fld = GetFieldFromName('tag_rb[' + i + ']');
    tag_rb_vals[i] = fld.value;
    fld = GetFieldFromName('tag_list['+ i +']');
    tag_list_vals[i] = fld.value;
  }
}

//////////////////////////////////////////////////////////
// Restore the settings in the Tag panel from the saved ones
//////////////////////////////////////////////////////////
function RestoreTagSettings() {
  var fld;
  for( var i=0; i < stanza_count; i++ ) {
    if ( i > 0 ) {
      fld = GetFieldFromName('tag_and[' + i + ']');
      fld.value = tag_and_vals[i];
    }
    fld = GetFieldFromName('tag_lb[' + i + ']');
    fld.value = tag_lb_vals[i];
    fld = GetFieldFromName('tag_rb[' + i + ']');
    fld.value = tag_rb_vals[i];
    fld = GetFieldFromName('tag_list['+ i +']');
    fld.value = tag_list_vals[i];
  }
}

//////////////////////////////////////////////////////////
// Construct the fields for a Tag selection stanza roughly
// of the form:
//    <AND / OR> <( or not><SELECT TAG> <) or not>
//////////////////////////////////////////////////////////
var stanza_count = 0;
var tag_selections = new Array();
function ExtendTagSelections() {
  orgtag_sel = document.forms.form.orgtaglist;
  var moretags = document.getElementById('moretags');
  var stanza = '';
  if ( looks_like_ie && stanza_count > 0 && (stanza_count % 2) == 0 ) {
    stanza += '<br />';
  }
  stanza += '<span class="srchf" style="white-space: nowrap;">';
  if ( stanza_count > 0 ) {
    stanza += ' <select class="srchf" style=\"width: 4em\" name="tag_and['+stanza_count+']"><option value="AND">AND</option><option value="OR" checked>OR</option></select> ';
  }
  else {
    stanza += ' <div style=\"display: block; float: left; margin-top: 0.4em; height: 1.0em; width: 4.2em\"><span class="srchp">Tags:</span></div> ';
  }
  stanza += ' <select class="srchf" name="tag_lb['+stanza_count+']"><option value=" "> </option><option value="(">(</option><option value="((">((</option><option value="(((">(((</option></select> ';
  stanza += ' <select class="srchf" style=\"width: 12em\" name="tag_list['+stanza_count+']"></select>';
  stanza += ' <select class="srchf" name="tag_rb['+stanza_count+']"><option value=" "> </option><option value=")">)</option><option value="))">))</option><option value=")))">)))</option></select> ';
  stanza += '</span> ';

  SaveTagSettings();
  moretags.innerHTML += stanza;
  RestoreTagSettings();

  tag_selections[stanza_count] = GetFieldFromName('tag_list['+stanza_count+']');
  CopyOptions( orgtag_sel, tag_selections[stanza_count] );

  stanza_count++;
  document.forms.form.taglist_count.value = stanza_count;
  return '';
}


//////////////////////////////////////////////////////////
// Construct initial set of tag select stanzas and assign
// starting positions based on comma-separated lists.
function TagSelectionStanza(count,and_v,lb_v,list_v,rb_v) {
  if ( count == 0 ) count++;
  for ( var i=0; i < count; i++ ) {
    ExtendTagSelections();
  }

  // Build the arrays of current values so we can RestoreTagSettings
  tag_and_vals = and_v.split(',');
  tag_lb_vals = lb_v.split(',');
  tag_list_vals = list_v.split(',');
  tag_rb_vals = rb_v.split(',');

  RestoreTagSettings();

}
