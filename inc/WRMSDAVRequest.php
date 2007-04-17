<?php
/**
* Functions that are needed for all CalDAV Requests
*
*  - Ascertaining the paths
*  - Ascertaining the current user's permission to those paths.
*  - Utility functions which we can use to decide whether this
*    is a permitted activity for this user.
*
* @package   wrms
* @subpackage   WRMSDAVRequest
* @author    Andrew McMillan <andrew@mcmillan.net.nz>
* @copyright Catalyst .Net Ltd
* @license   http://gnu.org/copyleft/gpl.html GNU GPL v2
*/

require_once("XMLElement.php");

define('DEPTH_INFINITY', 9999);

/**
* A class for collecting things to do with this request.
*
* @package   rscds
*/
class WRMSDAVRequest
{
  var $options;

  /**
  * Create a new WRMSDAVRequest object.
  */
  function WRMSDAVRequest( $options = array() ) {
    global $session, $c, $debugging;

    $this->options = $options;

    $this->raw_post = file_get_contents ( 'php://input');

    if ( isset($debugging) && isset($_GET['method']) ) {
      $_SERVER['REQUEST_METHOD'] = $_GET['method'];
    }
    $this->method = $_SERVER['REQUEST_METHOD'];

    /**
    * A variety of requests may set the "Depth" header to control recursion
    */
    $this->depth = ( isset($_SERVER['HTTP_DEPTH']) ? $_SERVER['HTTP_DEPTH'] : 0 );
    if ( $this->depth == 'infinity' ) $this->depth = DEPTH_INFINITY;
    $this->depth = intval($this->depth);

    /**
    * MOVE/COPY use a "Destination" header and (optionally) an "Overwrite" one.
    */
    if ( isset($_SERVER['HTTP_DESTINATION']) ) $this->destination = $_SERVER['HTTP_DESTINATION'];
    $this->overwrite = ( isset($_SERVER['HTTP_OVERWRITE']) ? $_SERVER['HTTP_OVERWRITE'] : 'T' ); // RFC2518, 9.6 says default True.

    /**
    * LOCK things use an "If" header to hold the lock in some cases, and "Lock-token" in others
    */
    if ( isset($_SERVER['HTTP_IF']) ) $this->if_clause = $_SERVER['HTTP_IF'];
    if ( isset($_SERVER['HTTP_LOCK_TOKEN']) && preg_match( '#[<]opaquelocktoken:(.*)[>]#', $_SERVER['HTTP_LOCK_TOKEN'], $matches ) ) {
      $this->lock_token = $matches[1];
    }

    /**
    * LOCK things use a "Timeout" header to set a series of reducing alternative values
    */
    if ( isset($_SERVER['HTTP_TIMEOUT']) ) {
      $timeouts = split( ',', $_SERVER['HTTP_TIMEOUT'] );
      foreach( $timeouts AS $k => $v ) {
        if ( strtolower($v) == 'infinite' ) {
          $this->timeout = (isset($c->maximum_lock_timeout) ? $c->maximum_lock_timeout : 86400 * 100);
          break;
        }
        elseif ( strtolower(substr($v,0,7)) == 'second-' ) {
          $this->timeout = max( intval(substr($v,7)), (isset($c->maximum_lock_timeout) ? $c->maximum_lock_timeout : 86400 * 100) );
          break;
        }
      }
      if ( ! isset($this->timeout) ) $this->timeout = (isset($c->default_lock_timeout) ? $c->default_lock_timeout : 900);
    }

    /**
    * Our path is /<script name>/<user name>/ if it ends in
    * a trailing '/' then it is referring to a DAV 'collection' but otherwise
    * it is referring to a DAV data item.
    *
    * Permissions are controlled as follows:
    *  1. if there is no <user name> component, the request has read privileges
    *  2. if the requester is an admin, the request has read/write priviliges
    *  3. if there is a <user name> component which matches the logged on user
    *     then the request has read/write privileges
    *  4. otherwise we query the defined relationships between users and use
    *     the minimum privileges returned from that analysis.
    */
    $this->path = $_SERVER['PATH_INFO'];
    $bad_chars_regex = '/[\\^\\[\\(\\\\]/';
    if ( preg_match( $bad_chars_regex, $this->path ) ) {
      $this->DoResponse( 400, translate("The calendar path contains illegal characters.") );
    }

    /**
    * RFC2518, 5.2: URL pointing to a collection SHOULD end in '/', and if it does not then
    * we SHOULD return a Content-location header with the correction...
    */
    if ( preg_match( '#/[^/]$#', $this->path ) ) {
      dbg_error_log( "caldav", "Checking whether path might be a person (collection)" );
      $username = preg_replace( '#^/([^/]+)$#', '\1', $this->path );
      $qry = new PgQuery( "SELECT count(1) AS is_collection FROM usr WHERE username = ?;", $username );
      if ( $qry->Exec('caldav') && $qry->rows == 1 && ($row = $qry->Fetch()) && $row->is_collection == 1 ) {
        dbg_error_log( "caldav", "Path is actually a user - sending Content-Location header." );
        $this->path .= '/';
        header( "Content-Location: $this->path" );
        $this->_is_collection = true;
      }
    }

    $path_split = preg_split('#/+#', $this->path );
    $this->permissions = array();
    if ( !isset($path_split[1]) || $path_split[1] == '' ) {
      dbg_error_log( "caldav", "No useful path split possible" );
      unset($this->user_no);
      unset($this->username);
      $this->permissions = array("read" => 'read' );
      dbg_error_log( "caldav", "Read permissions for user accessing /" );
    }
    else {
      $this->username = $path_split[1];
      @dbg_error_log( "caldav", "Path split into at least /// %s /// %s /// %s", $path_split[1], $path_split[2], $path_split[3] );
      $qry = new PgQuery( "SELECT * FROM usr WHERE username = ?;", $this->username );
      if ( $qry->Exec("caldav") && $user = $qry->Fetch() ) {
        $this->user_no = $user->user_no;
      }
      if ( $session->AllowedTo("Admin") ) {
        $this->permissions = array('all' => 'all' );
        dbg_error_log( "caldav", "Full permissions for a systems administrator" );
      }
      else if ( $session->user_no == $this->user_no ) {
        $this->permissions = array('all' => 'all' );
        dbg_error_log( "caldav", "Full permissions for user accessing their own hierarchy" );
      }
      else if ( isset($this->user_no) ) {
        $this->permissions = array('read' => 'read' );
        dbg_error_log( "caldav", "Restricted permissions for user accessing someone elses hierarchy: %s", implode( ", ", $this->permissions ) );
      }
    }
    if ( !isset($this->user_no) ) $this->user_no = $session->user_no;

    /**
    * If the content we are receiving is XML then we parse it here.  RFC2518 says we
    * should reasonably expect to see either text/xml or application/xml
    */
    if ( preg_match( '#(application|text)/xml#', $_SERVER['CONTENT_TYPE'] ) ) {
      dbg_error_log( "caldav", "Parsing input XML Submission" );
      $xml_parser = xml_parser_create_ns('UTF-8');
      $this->xml_tags = array();
      xml_parser_set_option ( $xml_parser, XML_OPTION_SKIP_WHITE, 1 );
      xml_parse_into_struct( $xml_parser, $this->raw_post, $this->xml_tags );
      xml_parser_free($xml_parser);
    }

    /**
    * Look out for If-None-Match or If-Match headers
    */
    if ( isset($_SERVER["HTTP_IF_NONE_MATCH"]) ) {
      $this->etag_none_match = str_replace('"','',$_SERVER["HTTP_IF_NONE_MATCH"]);
      if ( $this->etag_none_match == '' ) unset($this->etag_none_match);
    }
    if ( isset($_SERVER["HTTP_IF_MATCH"]) ) {
      $this->etag_if_match = str_replace('"','',$_SERVER["HTTP_IF_MATCH"]);
      if ( $this->etag_if_match == '' ) unset($this->etag_if_match);
    }

  }

  /**
  * Checks whether the resource is locked, returning any lock token, or false
  *
  * FIXME: This logic does not catch all locking scenarios.  For example an infinite
  * depth request should check the permissions for all collections and resources within
  * that.  At present we only maintain permissions on a per-collection basis though.
  *
  * @param string $dav_name The resource which we want to know the lock status for
  */
  function IsLocked() {
    if ( !isset($this->_locks_found) ) {
      $this->_locks_found = array();
      /**
      * Find the locks that might apply and load them into an array
      */
      $sql = "SELECT * FROM locks WHERE ?::text ~ ('^'||dav_name||?)::text;";
      $qry = new PgQuery($sql, $this->path, ($this->IsInfiniteDepth() ? '' : '$') );
      if ( $qry->Exec("caldav",__LINE__,__FILE__) ) {
        while( $lock_row = $qry->Fetch() ) {
          $this->_locks_found[$lock_row->opaquelocktoken] = $lock_row;
        }
      }
      else {
        $this->DoResponse(500,translate("Database Error"));
        // Does not return.
      }
    }

    foreach( $this->_locks_found AS $lock_token => $lock_row ) {
      if ( $lock_row->depth == DEPTH_INFINITY || $lock_row->dav_name == $this->path ) {
        return $lock_token;
      }
    }

    return false;  // Nothing matched
  }


  /**
  * Returns the name for this depth: 0, 1, infinity
  */
  function GetDepthName( ) {
    if ( $this->IsInfiniteDepth() ) return 'infinity';
    return $this->depth;
  }

  /**
  * Returns the tail of a Regex appropriate for this Depth, when appended to
  *
  */
  function DepthRegexTail() {
    if ( $this->IsInfiniteDepth() ) return '';
    if ( $this->depth == 0 ) return '$';
    return '[^/]*/?$';
  }

  /**
  * Returns the locked row, either from the cache or from the database
  *
  * @param string $dav_name The resource which we want to know the lock status for
  */
  function GetLockRow( $lock_token ) {
    if ( isset($this->_locks_found) && isset($this->_locks_found[$lock_token]) ) {
      return $this->_locks_found[$lock_token];
    }

    $sql = "SELECT * FROM locks WHERE opaquelocktoken = ?;";
    $qry = new PgQuery($sql, $lock_token );
    if ( $qry->Exec("caldav",__LINE__,__FILE__) ) {
      $lock_row = $qry->Fetch();
      $this->_locks_found = array( $lock_token => $lock_row );
      return $this->_locks_found[$lock_token];
    }
    else {
      $request->DoResponse( 500, translate("Database Error") );
    }

    return false;  // Nothing matched
  }


  /**
  * Checks to see whether the lock token given matches one of the ones handed in
  * with the request.
  *
  * @param string $lock_token The opaquelocktoken which we are looking for
  */
  function ValidateLockToken( $lock_token ) {
    if ( isset($this->lock_token) && $this->lock_token == $lock_token ) return true;
    if ( isset($this->if_clause) ) {
      dbg_error_log( "caldav", "Checking lock token '%s' against '%s'", $lock_token, $this->if_clause );
      $tokens = preg_split( '/[<>]/', $this->if_clause );
      foreach( $tokens AS $k => $v ) {
        dbg_error_log( "caldav", "Checking lock token '%s' against '%s'", $lock_token, $v );
        if ( 'opaquelocktoken:' == substr( $v, 0, 16 ) ) {
          if ( substr( $v, 16 ) == $lock_token ) {
            dbg_error_log( "caldav", "Lock token '%s' validated OK against '%s'", $lock_token, $v );
            return true;
          }
        }
      }
    }
    else {
      @dbg_error_log( "caldav", "Invalid lock token '%s' - not in Lock-token (%s) or If headers (%s) ", $lock_token, $this->lock_token, $this->if_clause );
    }

    return false;
  }


  /**
  * Returns the DB object associated with a lock token, or false.
  *
  * @param string $lock_token The opaquelocktoken which we are looking for
  */
  function GetLockDetails( $lock_token ) {
    if ( !isset($this->_locks_found) && false === $this->IsLocked() ) return false;
    if ( isset($this->_locks_found[$lock_token]) ) return $this->_locks_found[$lock_token];
    return false;
  }


  /**
  * This will either (a) return false if no locks apply, or (b) return the lock_token
  * which the request successfully included to open the lock, or:
  * (c) respond directly to the client with the failure.
  *
  * @return mixed false (no lock) or opaquelocktoken (opened lock)
  */
  function FailIfLocked() {
    if ( $existing_lock = $this->IsLocked() ) { // NOTE Assignment in if() is expected here.
      dbg_error_log( "caldav", "There is a lock on '%s'", $this->path);
      if ( ! $this->ValidateLockToken($existing_lock) ) {
        $lock_row = $this->GetLockRow($existing_lock);
        /**
        * Already locked - deny it
        */
        $response[] = new XMLElement( 'response', array(
            new XMLElement( 'href',   $lock_row->dav_name ),
            new XMLElement( 'status', 'HTTP/1.1 423 Resource Locked')
        ));
        if ( $lock_row->dav_name != $this->path ) {
          $response[] = new XMLElement( 'response', array(
              new XMLElement( 'href',   $this->path ),
              new XMLElement( 'propstat', array(
                new XMLElement( 'prop', new XMLElement( 'lockdiscovery' ) ),
                new XMLElement( 'status', 'HTTP/1.1 424 Failed Dependency')
              ))
          ));
        }
        $response = new XMLElement( "multistatus", $response, array('xmlns'=>'DAV:') );
        $xmldoc = $response->Render(0,'<?xml version="1.0" encoding="utf-8" ?>');
        $this->DoResponse( 207, $xmldoc, 'text/xml; charset="utf-8"' );
        // Which we won't come back from
      }
      return $existing_lock;
    }
    return false;
  }


  /**
  * Returns true if the URL referenced by this request points at a collection.
  */
  function IsCollection( ) {
    if ( !isset($this->_is_collection) ) {
      $this->_is_collection = preg_match( '#/$#', $this->path );
    }
    return $this->_is_collection;
  }


  /**
  * Returns true if the URL referenced by this request points at a principal.
  */
  function IsPrincipal( ) {
    if ( !isset($this->_is_principal) ) {
      $this->_is_principal = preg_match( '#^/[^/]+/$#', $this->path );
    }
    return $this->_is_principal;
  }


  /**
  * Returns true if the request asked for infinite depth
  */
  function IsInfiniteDepth( ) {
    return ($this->depth == DEPTH_INFINITY);
  }


  /**
  * Are we allowed to do the requested activity
  *
  * +------------+------------------------------------------------------+
  * | METHOD     | PRIVILEGES                                           |
  * +------------+------------------------------------------------------+
  * | MKCALENDAR | DAV:bind                                             |
  * | REPORT     | DAV:read or CALDAV:read-free-busy (on all referenced |
  * |            | resources)                                           |
  * +------------+------------------------------------------------------+
  *
  * @param string $activity The activity we want to do.
  */
  function AllowedTo( $activity ) {
    if ( isset($this->permissions['all']) ) return true;
    switch( $activity ) {
      case 'freebusy':
        return isset($this->permissions['read']) || isset($this->permissions['freebusy']);
        break;
      case 'delete':
        return isset($this->permissions['unbind']);
        break;
      case 'proppatch':
        return isset($this->permissions['write']) || isset($this->permissions['write-properties']);
        break;
      case 'modify':
        return isset($this->permissions['write']) || isset($this->permissions['write-content']);
        break;

      case 'create':
      case 'mkcalendar':
      case 'mkcol':
        return isset($this->permissions['bind']);
        break;

      case 'read':
      case 'lock':
      case 'unlock':
      default:
        return isset($this->permissions[$activity]);
        break;
    }

    return false;
  }



  /**
  * Sometimes it's a perfectly formed request, but we just don't do that :-(
  * @param array $unsupported An array of the properties we don't support.
  */
  function UnsupportedRequest( $unsupported ) {
    if ( isset($unsupported) && count($unsupported) > 0 ) {
      $badprops = new XMLElement( "prop" );
      foreach( $unsupported AS $k => $v ) {
        // Not supported at this point...
        dbg_error_log("ERROR", " %s: Support for $v:$k properties is not implemented yet", $this->method );
        $badprops->NewElement(strtolower($k),false,array("xmlns" => strtolower($v)));
      }
      $error = new XMLElement("error", new XMLElement( "LOCK",$badprops), array("xmlns" => "DAV:") );

      $this->DoResponse( 422, $error->Render(0,'<?xml version="1.0" ?>'), 'text/xml; charset="utf-8"');
    }
  }


  /**
  * Utility function we call when we have a simple status-based response to
  * return to the client.  Possibly
  *
  * @param int $status The HTTP status code to send.
  * @param string $message The friendly text message to send with the response.
  */
  function DoResponse( $status, $message="", $content_type="text/plain" ) {
    global $session, $c;
    switch( $status ) {
      case 100: $status_text = "Continue"; break;
      case 101: $status_text = "Switching Protocols"; break;
      case 200: $status_text = "OK"; break;
      case 201: $status_text = "Created"; break;
      case 202: $status_text = "Accepted"; break;
      case 203: $status_text = "Non-Authoritative Information"; break;
      case 204: $status_text = "No Content"; break;
      case 205: $status_text = "Reset Content"; break;
      case 206: $status_text = "Partial Content"; break;
      case 207: $status_text = "Multi-Status"; break;
      case 300: $status_text = "Multiple Choices"; break;
      case 301: $status_text = "Moved Permanently"; break;
      case 302: $status_text = "Found"; break;
      case 303: $status_text = "See Other"; break;
      case 304: $status_text = "Not Modified"; break;
      case 305: $status_text = "Use Proxy"; break;
      case 307: $status_text = "Temporary Redirect"; break;
      case 400: $status_text = "Bad Request"; break;
      case 401: $status_text = "Unauthorized"; break;
      case 402: $status_text = "Payment Required"; break;
      case 403: $status_text = "Forbidden"; break;
      case 404: $status_text = "Not Found"; break;
      case 405: $status_text = "Method Not Allowed"; break;
      case 406: $status_text = "Not Acceptable"; break;
      case 407: $status_text = "Proxy Authentication Required"; break;
      case 408: $status_text = "Request Timeout"; break;
      case 409: $status_text = "Conflict"; break;
      case 410: $status_text = "Gone"; break;
      case 411: $status_text = "Length Required"; break;
      case 412: $status_text = "Precondition Failed"; break;
      case 413: $status_text = "Request Entity Too Large"; break;
      case 414: $status_text = "Request-URI Too Long"; break;
      case 415: $status_text = "Unsupported Media Type"; break;
      case 416: $status_text = "Requested Range Not Satisfiable"; break;
      case 417: $status_text = "Expectation Failed"; break;
      case 422: $status_text = "Unprocessable Entity"; break;
      case 423: $status_text = "Locked"; break;
      case 424: $status_text = "Failed Dependency"; break;
      case 500: $status_text = "Internal Server Error"; break;
      case 501: $status_text = "Not Implemented"; break;
      case 502: $status_text = "Bad Gateway"; break;
      case 503: $status_text = "Service Unavailable"; break;
      case 504: $status_text = "Gateway Timeout"; break;
      case 505: $status_text = "HTTP Version Not Supported"; break;
    }
    @header( sprintf("HTTP/1.1 %d %s", $status, $status_text) );
    @header( sprintf("X-RSCDS-Version: RSCDS/%d.%d.%d; DB/%d.%d.%d", $c->code_major, $c->code_minor, $c->code_patch, $c->schema_major, $c->schema_minor, $c->schema_patch) );
    @header( "Content-type: ".$content_type );
    echo $message;

    if ( strlen($message) > 100 || strstr($message, "\n") ) {
      $message = substr( preg_replace("#\s+#m", ' ', $message ), 0, 100) . (strlen($message) > 100 ? "..." : "");
    }

    dbg_error_log("caldav", "Status: %d, Message: %s, User: %d, Path: %s", $status, $message, $session->user_no, $this->path);

    exit(0);
  }


  /**
  * Return an array of what the DAV privileges are that are supported
  *
  * @return array The supported privileges.
  */
  function SupportedPrivileges() {
    $privs = array( "all"=>1, "read"=>1, "write"=>1, "bind"=>1, "unbind"=>1, "write-content"=>1);
    return $privs;
  }
}

?>