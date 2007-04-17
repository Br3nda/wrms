<?php

dbg_error_log("REPORT", "method handler");

require_once("XMLElement.php");
require_once("vEvent.php");

$report_path = $_SERVER['PATH_INFO'];

$attributes = array();
$reportnum = -1;
$report = array();
foreach( $request->xml_tags AS $k => $v ) {

  switch ( $v['tag'] ) {

    case 'URN:IETF:PARAMS:XML:NS:CALDAV:CALENDAR-MULTIGET':
      dbg_log_array( "REPORT", "CALENDAR-MULTIGET", $v, true );
      $report[$reportnum]['multiget'] = 1;
      if ( $v['type'] == "open" ) {
        $multiget_names = array();
      }
      else if ( $v['type'] == "close" ) {
        $report[$reportnum]['get_names'] = $multiget_names;
        unset($multiget_names);
      }
      break;

    case 'URN:IETF:PARAMS:XML:NS:CALDAV:CALENDAR-DATA':
      dbg_log_array( "REPORT", "CALENDAR-DATA", $v, true );
      if ( $v['type'] == "complete" ) {
        $report[$reportnum]['include_data'] = 1;
      }
      break;

    case 'URN:IETF:PARAMS:XML:NS:CALDAV:CALENDAR-QUERY':
      dbg_log_array( "REPORT", "CALENDAR-QUERY", $v, true );
      if ( $v['type'] == "open" ) {
        $reportnum++;
        $report_type = substr($v['tag'],30);
        $report[$reportnum]['type'] = $report_type;
        $report[$reportnum]['include_href'] = 1;
        $report[$reportnum]['include_data'] = 0;
        $report[$reportnum]['start'] = date('Ymd\THis\Z',(time() - (86400 * 100))); // Default to the last 100 days.
        $report[$reportnum]['end'] = date('Ymd\THis\Z');;
      }
      else {
        unset($report_type);
      }
      break;

    case 'URN:IETF:PARAMS:XML:NS:CALDAV:TIME-RANGE':
      dbg_log_array( "REPORT", "TIME-RANGE", $v, true );
      if ( isset($v['attributes']['START']) ) {
        $report[$reportnum]['start'] = $v['attributes']['START'];
      }
      if ( isset($v['attributes']['END']) ) {
        $report[$reportnum]['end'] = $v['attributes']['END'];
      }
      break;

    case 'URN:IETF:PARAMS:XML:NS:CALDAV:COMP-FILTER':
      dbg_log_array( "REPORT", "COMP-FILTER", $v, true );
      if ( isset($v['attributes']['NAME']) && ($v['attributes']['NAME'] == 'VCALENDAR' )) {
        $report[$reportnum]['calendar'] = 1;
      }
      if ( isset($v['attributes']['NAME']) ) {
        if ( isset($report[$reportnum]['calendar']) && ($v['attributes']['NAME'] == 'VEVENT') ) {
          $report[$reportnum]['calendar-event'] = 1;
        }
        if ( isset($report[$reportnum]['calendar']) && ($v['attributes']['NAME'] == 'VTODO') ) {
          $report[$reportnum]['calendar-todo'] = 1;
        }
        if ( isset($report[$reportnum]['calendar']) && ($v['attributes']['NAME'] == 'VFREEBUSY') ) {
          $report[$reportnum]['calendar-freebusy'] = 1;
        }
      }
      break;

    case 'URN:IETF:PARAMS:XML:NS:CALDAV:FILTER':
      dbg_error_log( "REPORT", "Not using %s information which follows...", $v['tag'] );
      dbg_log_array( "REPORT", "FILTER", $v, true );
      break;

    case 'DAV::PROP':
      dbg_log_array( "REPORT", "DAV::PROP", $v, true );
      if ( isset($report_type) ) {
        if ( $v['type'] == "open" ) {
          $report_properties = array();
        }
        else if ( $v['type'] == "close" ) {
          $report[$reportnum]['properties'] = $report_properties;
          unset($report_properties);
        }
        else {
          dbg_error_log( "REPORT", "Unexpected DAV::PROP type of ".$v['type'] );
        }
      }
      else {
        dbg_error_log( "REPORT", "Unexpected DAV::PROP type of ".$v['type']." when no active report type.");
      }
      break;

    case 'DAV::GETETAG':
    case 'DAV::GETCONTENTLENGTH':
    case 'DAV::GETCONTENTTYPE':
    case 'DAV::RESOURCETYPE':
      if ( isset($report_properties) ) {
        $attribute = substr($v['tag'],5);
        $report_properties[$attribute] = 1;
      }
      break;

    case 'DAV::HREF':
      dbg_log_array( "REPORT", "DAV::HREF", $v, true );
      if ( isset($report[$reportnum]['multiget']) ) {
        $multiget_names[] = $v['value'];
      }

     default:
       dbg_error_log( "REPORT", "Unhandled tag >>".$v['tag']."<<");
  }
}

if ( $reportnum == -1 ) {
  // Fake the request anyway...
  $reportnum++;
  $report_type = substr($v['tag'],30);
  $report[$reportnum]['type'] = $report_type;
  $report[$reportnum]['include_href'] = 1;
  $report[$reportnum]['include_data'] = 0;
  $report[$reportnum]['start'] = date('Ymd\THis\Z',(time() - (86400 * 40))); // Default to the last 40 days.
  $report[$reportnum]['end'] = date('Ymd\THis\Z');;
}

if ( $unsupported_stuff ) {
  /**
  * FIXME This is really a template for what we should do in the event of an error.
  */
   header('HTTP/1.1 403 Forbidden');
   header('Content-Type: application/xml; charset="utf-8"');
   echo <<<EOXML
<?xml version="1.0" encoding="utf-8" ?>
<D:error xmlns:D="DAV:" xmlns:C="urn:ietf:params:xml:ns:caldav">
  <C:supported-filter>
    <C:prop-filter name="X-ABC-GUID"/>
  </C:supported-filter>
</D:error>
EOXML;
  exit(0);
}

$report_user_no = $session->user_no;
$report_user_name = $session->username;
if ( $session->AllowedTo("Admin") || $session->AllowedTo("Support") || $session->AllowedTo("Accounts") ) {
  if ( preg_match( "#^/([^/]+)(/|$)#", $report_path, $matches ) ) {
    $in_username = $matches[1];
    $qry = new PgQuery( "SELECT user_no FROM usr WHERE username = ?;", $in_username );
    if ( $qry->Exec("REPORT") && $row = $qry->Fetch() ) {
      $report_user_no = $row->user_no;
      $report_user_name = $in_username;
    }
  }
}


$ical_date_format = vEvent::SqlDateFormat();
$ical_duration_format = vEvent::SqlDurationFormat();

for( $i=0; $i <= $reportnum; $i++ ) {
  dbg_error_log("REPORT", "Report[%d] Start:%s, End: %s, Events: %d, Todos: %d, Freebusy: %d",
         $i, $report[$i]['start'], $report[$i]['end'], $report[$i]['calendar-event'], $report[$i]['calendar-todo'], $report[$i]['calendar-freebusy']);
  if ( $report[$i]['calendar-event'] != 1 ) continue;
  $sql = <<<EOSQL
  SELECT usr.username, dav_etag, timesheet_id,
        to_char(work_on,$ical_date_format) AS dtstamp,
        to_char(work_on + case when work_on::time < '06:00'::time then '09:00'::time else '00:00'::time end,$ical_date_format) AS dtstart,
        to_char(work_duration,$ical_duration_format) AS duration,
        work_description AS summary,
        'WR#'||request_id::text AS location,
        'WR#'||request_id::text || ' - ' || brief AS description,
        'Invoice '||charged_details::text || ', Charged $'|| to_char(charged_amount,'FM999,999,990.00') ||' by ' || chgby.username || ' on ' || to_char(work_charged,'d/mm/YY') AS invoiced
    FROM request_timesheet JOIN request USING (request_id) JOIN usr ON (work_by_id=usr.user_no)
       LEFT OUTER JOIN usr chgby ON (charged_by_id = chgby.user_no)
  WHERE work_by_id = ? AND work_duration IS NOT NULL

EOSQL;

  $where = "";
  if ( isset( $report[$i]['start'] ) ) {
    $where = "AND ((work_on + work_duration) >= ".qpg($report[$i]['start'])."::timestamp with time zone) ";
  }
  if ( isset( $report[$i]['end'] ) ) {
    $where .= "AND work_on <= ".qpg($report[$i]['end'])."::timestamp with time zone ";
  }
  $sql .= $where;
  $sql .= " ORDER BY work_on ASC";

  $responses = array();
  $qry = new PgQuery( $sql, $report_user_no );
  // echo $qry->querystring;
  if ( $qry->Exec() && $qry->rows > 0 ) {
    while( $ts = $qry->Fetch() ) {
      if ( $ts->invoiced != "" ) $ts->description .= "\n" . $ts->invoiced;
      $response = new XMLElement("response" );
      $prop = new XMLElement("prop" );
      $ev = new vEvent( array(
                        'uid' => $ts->timesheet_id."@".$_SERVER['SERVER_NAME'],
                        'dtstart'  => $ts->dtstart,
                        'duration' => $ts->duration,
                        'summary' => $ts->summary,
                        'location' => $ts->location,
                        'description' => $ts->description
                        ));

      if ( isset($report[$i]['include_href']) && $report[$i]['include_href'] > 0 ) {
        $url = sprintf("http://%s:%d%s/%s/%d.ics", $_SERVER['SERVER_NAME'], $_SERVER['SERVER_PORT'], $_SERVER['SCRIPT_NAME'], $report_user_name, $ts->timesheet_id );
        $response->NewElement("href",$url);
      }
      if ( isset($report[$i]['include_data']) && $report[$i]['include_data'] > 0 ) {
        $caldata = $ev->Render();
        $prop->NewElement("calendar-data",$caldata, array("xmlns" => "urn:ietf:params:xml:ns:caldav") );
      }
      if ( isset($report[$i]['properties']['GETETAG']) ) {
        $prop->NewElement("getetag", '"'.$ts->dav_etag.'"' );
      }
      $status = new XMLElement("status", "HTTP/1.1 200 OK" );

      $response->NewElement( "propstat", array( $prop, $status) );

      $responses[] = $response;

      dbg_error_log("REPORT", "TS Response: ETag >>%s<< >>%s<<", $ts->dav_etag, $url );
    }
  }

  /**
  * We also include _all_ caldav_data entries in there, since these
  * are events which failed to parse into timesheets.
  */
  $qry = new PgQuery( "SELECT * FROM caldav_data WHERE user_no = ?", $report_user_no );
  if ( $qry->Exec() && $qry->rows > 0 ) {
    while( $dav = $qry->Fetch() ) {
      $response = new XMLElement("response" );
      $prop = new XMLElement("prop" );
      $url = sprintf("http://%s:%d%s%s", $_SERVER['SERVER_NAME'], $_SERVER['SERVER_PORT'], $_SERVER['SCRIPT_NAME'], $dav->dav_name );

      if ( isset($report[$i]['include_href']) && $report[$i]['include_href'] > 0 ) {
        $response->NewElement("href",$url);
      }
      if ( isset($report[$i]['include_data']) && $report[$i]['include_data'] > 0 ) {
        $prop->NewElement("calendar-data",$dav->caldav_data, array("xmlns" => "urn:ietf:params:xml:ns:caldav") );
      }
      if ( isset($report[$i]['properties']['GETETAG']) ) {
        $prop->NewElement("getetag", '"'.$dav->dav_etag.'"' );
      }
      $status = new XMLElement("status", "HTTP/1.1 200 OK" );

      $response->NewElement( "propstat", array( $prop, $status) );

      $responses[] = $response;

      dbg_error_log("REPORT", "DAV Response: ETag >>%s<< >>%s<<", $dav->dav_etag, $url );
    }
  }
}

$multistatus = new XMLElement( "multistatus", $responses, array('xmlns'=>'DAV:') );

// dbg_log_array( "REPORT", "XML", $multistatus, true );

$xmldoc = $multistatus->Render();
$etag = md5($xmldoc);

header("HTTP/1.1 207 Multi-Status");
header("Content-type: text/xml;charset=UTF-8");
header("DAV: 1, 2, calendar-schedule");
header("ETag: \"$etag\"");

echo'<?xml version="1.0" encoding="UTF-8" ?>'."\n";
echo $xmldoc;

?>