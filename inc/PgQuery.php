<?php

///////////////////////
//   Connect to DB   //
///////////////////////
if (!$dbconn) {
  die( "Application is not connected to wrms database!" );
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

   return "";
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
                  $this->querystring .= "NULL";
               }

            else if ( is_array($arg) && $arg['plain'] != "" )
               {
                 // We abuse this, but people should access it through the PgQuery::plain($v) function
                  $this->querystring .= $arg['plain'];
               }

            else
               {

                  $this->querystring .= "'" . str_replace( "'", "''", str_replace( "\\", "\\\\", $arg)) . "'";
// From PHP 4.2 use:
//                  $this->querystring .= "'" . pg_escape_string($arg) . "'";
               }

            $this->querystring .= $parts[$i] ;
         }

      $this->querystring .= $parts[$z];
      return $this;
   }


   function Plain( $field )
      {
        // Abuse the array type to extend our ability to avoid \\ and ' replacement
         return array( 'plain' => $field );
      }


///////////////////////////
//   Execute the query   //
///////////////////////////
   function Exec( $location="" )
   {
      GLOBAL $dbconn;
      GLOBAL $debuggroups;

      if ($debuggroups["queryclass"])
         {
            error_log("query_class.php   query: $this->querystring");
         }

      $t1 = microtime();
      $this->result = pg_exec( $dbconn, $this->querystring );
      $this->rows = pg_numrows($this->result);

      $t2 = microtime();
      $this->execution_time = sprintf( "%2.06lf", duration( $t1, $t2 ));

      $locn = sprintf( "%-12.12s", $location );

      if ( !$this->result )
         {
            log_error( $locn, "QF", $this->querystring );
            log_error( $locn, "QF", pg_errormessage() );
         }

      else if ( $this->execution_time > $this->query_time_warning )
         {
            log_error( $locn, "SQ", "Took: $this->execution_time for $this->querystring" ); // SQ == Slow Query :-)
         }
      else if ( $debuggroups[$location] == 1 )
         {
            log_error( $locn, "DBGQ", "Took: $this->execution_time for $this->querystring" );
         }

         return $this->result;
     }

//////////////////////////////////////////////////
//   Fetch an object from the result resource   //
//////////////////////////////////////////////////
   function Fetch()
      {
         GLOBAL $debuggroups;

         if ($debuggroups["queryclass"])
            error_log( "Result: $this->result Rows: $this->rows, Rownum: $this->rownum");
         if ( ! $this->result ) return FALSE;
         if ( ($this->rownum + 1) >= $this->rows ) return FALSE;


         $this->rownum++;
         if ($debuggroups["queryclass"])
            error_log( "Fetching row $this->rownum" );
         $this->object = pg_fetch_object($this->result, $this->rownum);


         return $this->object;
      }
}
///////////////////////////////////////////////////////////////////////////
//   E N D   O F   C L A S S   F O R   D A T A B A S E   Q U E R I E S   //
///////////////////////////////////////////////////////////////////////////
?>
