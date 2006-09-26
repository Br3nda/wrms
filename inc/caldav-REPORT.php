<?php

dbg_error_log("REPORT", "method handler");

/**
* FIXME We completely and arbitrarily ignore everything in the REPORT
* request, we just assume they wanted everything, and we're not going
* to give them everything anyway - just the last three months.
*/
/*
$parser = xml_parser_create_ns('UTF-8');
xml_parser_set_option ( $parser, XML_OPTION_SKIP_WHITE, 1 );

function xml_start_callback( $parser, $el_name, $el_attrs ) {
  dbg_error_log( "REPORT", "Parsing $el_name" );
  dbg_log_array( "REPORT", "$el_name::attrs", $el_attrs, true );
}

function xml_end_callback( $parser, $el_name ) {
  dbg_error_log( "REPORT", "Finished Parsing $el_name" );
}

xml_set_element_handler ( $parser, 'xml_start_callback', 'xml_end_callback' );

$rpt_request = array();
xml_parse_into_struct( $parser, $raw_post, $rpt_request );
xml_parser_free($parser);


$reportnum = -1;
$report = array();
foreach( $rpt_request AS $k => $v ) {

  switch ( $v['tag'] ) {

    case 'URN:IETF:PARAMS:XML:NS:CALDAV:CALENDAR-QUERY':
      if ( $v['type'] == "open" ) {
        $reportnum++;
        $report_type = substr($v['tag'],30);
        $report[$reportnum]['type'] = $report_type;
      }
      else {
        unset($report_type);
      }
      break;

    case 'DAV::PROP':
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
      if ( isset($report_properties) ) {
        if ( $v['type'] == "complete" ) {
          $report_properties['GETETAG'] = 1;
        }
      }
      break;

     default:
       dbg_error_log( "REPORT", "Unhandled tag >>".$v['tag']."<<");
  }
}

// dbg_log_array( 'RPT', $rpt_request, true );

// dbg_log_array( 'REPORT', $report, true );
*/

header("HTTP/1.1 207 Multi-Status");
header("Content-type: text/xml;charset=UTF-8");

/**
* FIXME - this needs to be rewritten using XML libraries, in the same manner
* in which the REPORT request is parsed, in fact.  For the time being we will
* attach importance to the care and feeding of Evolution, however.
*/
$response_tpl = <<<RESPONSETPL
    <D:response>
        <D:href>http://%s:%d%s/%s/%d.ics</D:href>
        <D:propstat>
            <D:prop>
                <D:getetag>"%s"</D:getetag>
            </D:prop>
            <D:status>HTTP/1.1 200 OK</D:status>
        </D:propstat>
    </D:response>

RESPONSETPL;


echo <<<REPORTHDR
<?xml version="1.0" encoding="utf-8" ?>
<D:multistatus xmlns:D="DAV:" xmlns:C="urn:ietf:params:xml:ns:caldav">

REPORTHDR;

  $qry = new PgQuery( "SELECT username, caldav_etag, timesheet_id FROM request_timesheet JOIN usr ON (work_by_id=user_no) WHERE work_by_id = ? AND work_on > current_timestamp - '100 days'::interval AND work_duration IS NOT NULL;", $session->user_no );
  if ( $qry->Exec() && $qry->rows > 0 ) {
    while( $ts = $qry->Fetch() ) {
      printf( $response_tpl, $_SERVER['SERVER_NAME'], $_SERVER['SERVER_PORT'], $_SERVER['SCRIPT_NAME'], $ts->username, $ts->timesheet_id, $ts->caldav_etag );
      dbg_error_log("REPORT", "ETag >>%s<< >>http://%s:%s%s/%s/%d.ics<<", $ts->caldav_etag,
                            $_SERVER['SERVER_NAME'], $_SERVER['SERVER_PORT'], $_SERVER['SCRIPT_NAME'], $ts->username, $ts->timesheet_id);
    }
  }

echo <<<EOXML
</D:multistatus>
EOXML;

?>