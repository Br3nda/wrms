<?php

///////////////////////
//   Connect to DB   //
///////////////////////
if ( !isset($dbconn) ) {
  die( 'Database is not connected!' );
}


///////////////////////////////////////////////////////////////////////
//   Duration of times entered - to see how long a query has taken   //
///////////////////////////////////////////////////////////////////////
function duration( $t1, $t2 )                   // Enter two times from microtime() call
{
  list ( $ms1, $s1 ) = explode ( " ", $t1 );   // Format times - by spliting seconds and microseconds
  list ( $ms2, $s2 ) = explode ( " ", $t2 );
  $s1 = $s2 - $s1;
  $s1 = $s1 + ( $ms2 -$ms1 );
  return $s1;                                  // Return duration of time
}

///////////////////
//   Log Error   //
///////////////////
   function log_error( $locn, $tag, $string)
   {
      GLOBAL $sysname;

      while( strlen( $string ) > 0 )
         {
            error_log( "$sysname $locn $tag: " . substr( $string, 0, 240), 0 );
            $string = substr( "$string", 240 );
         }

   return '';
   }

/////////////////////////////////////////////////////////////
//   C L A S S   F O R   D A T A B A S E   Q U E R I E S   //
/////////////////////////////////////////////////////////////
class PgQuery
{
  var $querystring;               // stores a query string
  var $result;                    // stores a resource result
  var $rows;                      // number of rows from pg_numrows - for fetching result
  var $rownum = -1;               // number of current row
  var $execution_time;            // stores the query execution time - used to deal with long queries
  var $query_time_warning = 0.3;  // how long the query should take before a warning is issued
  var $location;                  // Where we called this query from so we can find it in our code!
  var $object;                    // the object of rows
  var $errorstring;               // The error message, if it fails



////////////////////////////////////////////
//   Replace parameters into Query String during initialisation
////////////////////////////////////////////
  function PgQuery()
  {
    $this->result = 0;
    $this->rows = 0;
    $this->execution_time = 0;
    $this->rownum = -1;

    $argc = func_num_args();
    $qry = func_get_arg(0);

    $parts = explode( '?', $qry );
    $this->querystring = $parts[0];
    $z = min( count($parts), $argc );
    for( $i = 1; $i < $z; $i++ )
    {
      $arg = func_get_arg($i);
      if ( !isset($arg) )
      {
        $this->querystring .= 'NULL';
      }
      elseif ( is_array($arg) && $arg['plain'] != '' )
      {
        // We abuse this, but people should access it through the PgQuery::plain($v) function
        $this->querystring .= $arg['plain'];
      }
      else
      {
        $this->querystring .= $this->quote($arg);
      }
      $this->querystring .= $parts[$i];
    }

    if ( isset($parts[$z]) ) $this->querystring .= $parts[$z];
    return $this;
  }

  /**
  * Quote the given string so it can be safely used within string delimiters
  * in a query.
  * @param $string mixed Data to be quoted
  * @return mixed "NULL" string, quoted string or original data
  */
  function quote($str = null)
  {
    switch (strtolower(gettype($str))) {
      case 'null':
        return 'NULL';
      case 'integer':
      case 'double' :
        return $str;
      case 'boolean':
        return $str ? 'TRUE' : 'FALSE';
      case 'string':
      default:
        $str = str_replace("'", "''", $str);
        //PostgreSQL treats a backslash as an escape character.
        $str = str_replace('\\', '\\\\', $str);
        return "'$str'";
    }
  }

  function Plain( $field )
  {
    // Abuse the array type to extend our ability to avoid \\ and ' replacement
    return array( 'plain' => $field );
  }

  ///////////////////////////
  //   Execute the query   //
  ///////////////////////////
  function Exec( $location = '' )
   {
    global $dbconn, $debuggroups;
    $this->location = $location;

    if ( isset($debuggroups['querystring']) )
    {
      log_error( 'PgQuery.php', 'query', $this->querystring );
    }

    $t1 = microtime();
    $this->result = pg_exec( $dbconn, $this->querystring );
    $this->rows = pg_numrows($this->result);
    $t2 = microtime();
    $this->execution_time = sprintf( "%2.06lf", duration( $t1, $t2 ));
    $locn = sprintf( "%-12.12s", $location );

    if ( !$this->result )
    {
      $this->errorstring = pg_errormessage();
      log_error( $locn, 'QF', $this->querystring );
      log_error( $locn, 'QF', $this->errorstring );
      // $bt = debug_backtrace();
      // foreach( $bt as $k => $v ) {
        // log_error( $locn, 'QF', sprintf( "Called by %s from %s line %d", $v['function'], $v['file'], $v['line'] ));
      // }
    }
    elseif ( $this->execution_time > $this->query_time_warning )
    {
      log_error( $locn, 'SQ', "Took: $this->execution_time for $this->querystring" ); // SQ == Slow Query :-)
    }
    elseif ( isset($debuggroups[$this->location]) && $debuggroups[$this->location] )
    {
      log_error( $locn, 'DBGQ', "Took: $this->execution_time for $this->querystring" );
    }

     return $this->result;
  }

  //////////////////////////////////////////////////
  //   Fetch an object from the result resource   //
  //////////////////////////////////////////////////
  function Fetch($as_array = false)
  {
    global $debuggroups;

    if ( isset($debuggroups["$this->location"]) && $debuggroups["$this->location"] > 2 ) {
      error_log( "Result: $this->result Rows: $this->rows, Rownum: $this->rownum");
    }
    if ( ! $this->result ) return false;
    if ( ($this->rownum + 1) >= $this->rows ) return false;

    $this->rownum++;
    if ( isset($debuggroups["$this->location"]) && $debuggroups["$this->location"] > 1 ) {
      error_log( "Fetching row $this->rownum" );
    }
    if ( $as_array )
    {
      $this->object = pg_fetch_array($this->result, $this->rownum);
    }
    else
    {
      $this->object = pg_fetch_object($this->result, $this->rownum);
    }

    return $this->object;
  }

  //////////////////////////////////////////////////
  //   Build an option list from the query        //
  //////////////////////////////////////////////////
  function BuildOptionList( $current = '', $location = 'options' )
  {
    $result = '';

    if ( $this->Exec($location) )
    {
      while( $row = $this->Fetch(true) )
      {
        $selected = ( ( $row[0] == $current || $row[1] == $current ) ? ' selected="selected"' : '' );
        $nextrow = "<option value=\"$row[0]\"$selected>$row[1]</option>\n";
        if ( preg_match('/&/', $nextrow) ) $nextrow = preg_replace( '/&/', '&amp;', $nextrow);
        $result .= $nextrow;
      }
    }
    return $result;
   }

}
///////////////////////////////////////////////////////////////////////////
//   E N D   O F   C L A S S   F O R   D A T A B A S E   Q U E R I E S   //
///////////////////////////////////////////////////////////////////////////

?>
