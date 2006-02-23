<?php
/**
* Some functions and a base class to help with updating records.
*
* This subpackage provides some functions that are useful around single
* record database activities such as insert and update.
*
* @package   awl
* @subpackage   DataUpdate
* @author    Andrew McMillan <andrew@catalyst.net.nz>
* @copyright Andrew McMillan
* @license   http://gnu.org/copyleft/gpl.html GNU GPL v2
*/

/**
* Get the names of the fields for a particular table
* @param string $tablename The name of the table.
* @return array of string The public fields in the table.
*/
function get_fields( $tablename ) {
  global $session;
  $sql = "SELECT f.attname, t.typname FROM pg_attribute f ";
  $sql .= "JOIN pg_class c ON ( f.attrelid = c.oid ) ";
  $sql .= "JOIN pg_type t ON ( f.atttypid = t.oid ) ";
  $sql .= "WHERE relname = ? AND attnum >= 0 order by f.attnum;";
  $qry = new PgQuery( $sql, $tablename );
  $qry->Exec("DataUpdate");
  $fields = array();
  while( $row = $qry->Fetch() ) {
    $fields["$row->attname"] = $row->typname;
    $session->Log( "DBG: DataUpdate::get_fields: %s => %s", $row->attname, $row->typname );
  }
  return $fields;
}


/**
* Build SQL INSERT/UPDATE statement from an associative array of fieldnames => values.
* @param array $obj The object  of fieldnames => values.
* @param string $type The word "update" or something else (which implies "insert").
* @param string $tablename The name of the table being updated.
* @param string $where What the "WHERE ..." clause needs to be for an UPDATE statement.
* @param string $fprefix An optional string which all fieldnames in $assoc are prefixed with.
* @return string An SQL Update or Insert statement with all fields/values from the array.
*/
function sql_from_object( $obj, $type, $tablename, $where, $fprefix = "" ) {
  global $session;
  $fields = get_fields($tablename);
  $update = strtolower($type) == "update";
  if ( $update )
    $sql = "UPDATE $tablename SET ";
  else
    $sql = "INSERT INTO $tablename (";

  $flst = "";
  $vlst = "";
  foreach( $fields as $fn => $typ ) {
    // $prefixed_fn = $fprefix . $fn;
    $session->Log( "DBG: sql_from_object: $fn => $typ (".$obj->{$fn}.")");
    if ( !isset($obj->{$fn}) && isset($obj->{"xxxx$fn"}) ) {
      // Sometimes we will have prepended 'xxxx' to the field name so that the field
      // name differs from the column name in the database.
      $obj->{$fn} = $obj->{"xxxx$fn"};
    }
    if ( !isset($obj->{$fn}) ) continue;
    $value = str_replace( "'", "''", str_replace("\\", "\\\\", $obj->{$fn}));
    if ( $fn == "password" ) {
      if ( $value == "******" || $value == "" ) continue;
      if ( !preg_match('/\*[0-9a-z]+\*[0-9a-z]+/', $value ) )
        $value = (function_exists('session_salted_md5') ? session_salted_md5($value) : md5($value) );
    }
    if ( eregi("(time|date)", $typ ) && $value == "" ) {
      $value = "NULL";
    }
    else if ( eregi("bool", $typ) )  {
      $value = ( $value == false || $value == "f" || $value == "off" || $value == "no" ? "FALSE"
                  : ( $value == true || $value == "t" || $value == "on" || $value == "yes" ? "TRUE"
                      : "NULL" ));
    }
    else if ( eregi("int", $typ) )  {
      $value = intval( $value );
    }
    else if ( eregi("(text|varchar)", $typ) )  {
      $value = "'$value'";
    }
    else
      $value = "'$value'::$typ";

    if ( $update )
      $flst .= ", $fn = $value";
    else {
      $flst .= ", $fn";
      $vlst .= ", $value";
    }
  }
  $flst = substr($flst,2);
  $vlst = substr($vlst,2);
  $sql .= $flst;
  if ( $update ) {
    $sql .= " $where; ";
  }
  else {
    $sql .= ") VALUES( $vlst ); ";
  }
 return $sql;
}


/**
* Build SQL INSERT/UPDATE statement from the $_POST associative array
* @param string $type The word "update" or something else (which implies "insert").
* @param string $tablename The name of the table being updated.
* @param string $where What the "WHERE ..." clause needs to be for an UPDATE statement.
* @param string $fprefix An optional string which all fieldnames in $assoc are prefixed with.
* @return string An SQL Update or Insert statement with all fields/values from the array.
*/
function sql_from_post( $type, $tablename, $where, $fprefix = "" ) {
  global $session;
  $fields = get_fields($tablename);
  $update = strtolower($type) == "update";
  if ( $update )
    $sql = "UPDATE $tablename SET ";
  else
    $sql = "INSERT INTO $tablename (";

  $flst = "";
  $vlst = "";
  foreach( $fields as $fn => $typ ) {
    $fn = $fprefix . $fn;
    $session->Log( "_POST: DBG: $fn => $typ (".$_POST[$fn].")");
    if ( !isset($_POST[$fn]) && isset($_POST["xxxx$fn"]) ) {
      // Sometimes we will have prepended 'xxxx' to the field name so that the field
      // name differs from the column name in the database.
      $_POST[$fn] = $_POST["xxxx$fn"];
      $session->Log( "_POST: DBG: xxxx$fn => $typ (".$_POST[$fn].")");
    }
    if ( !isset($_POST[$fn]) ) continue;
    $value = str_replace( "'", "''", str_replace("\\", "\\\\", $_POST[$fn]));
    if ( $fn == "password" ) {
      if ( $value == "******" || $value == "" ) continue;
      if ( !preg_match('/\*[0-9a-z]+\*[0-9a-z]+/', $value ) ) $value = md5($value);
    }
    if ( eregi("(time|date)", $typ ) && $value == "" ) {
      $value = "NULL";
    }
    else if ( eregi("bool", $typ) )  {
      $value = ( $value == "f" || $value == "off" ? "FALSE" : "TRUE" );
    }
    else if ( eregi("int", $typ) )  {
      $value = intval( $value );
    }
    else if ( eregi("(text|varchar)", $typ) )  {
      $value = "'$value'";
    }
    else
      $value = "'$value'::$typ";

    if ( $update )
      $flst .= ", $fn = $value";
    else {
      $flst .= ", $fn";
      $vlst .= ", $value";
    }
  }
  $flst = substr($flst,2);
  $vlst = substr($vlst,2);
  $sql .= $flst;
  if ( $update ) {
    $sql .= " $where; ";
  }
  else {
    $sql .= ") VALUES( $vlst ); ";
  }
 return $sql;
}


/**
* Since we are going to actually read/write from the database.
*/
require_once("PgQuery.php");

/**
* A Base class to use for records which will be read/written from the database.
* @package   awl
*/
class DBRecord
{
  /**#@+
  * @access private
  */
  /**
  * The database table that this record goes in
  * @var string
  */
  var $Table;

  /**
  * The field names for the record.  The array index is the field name
  * and the array value is the field type.
  * @var array
  */
  var $Fields;

  /**
  * The keys for the record as an array of key => value pairs
  * @var array
  */
  var $Keys;

  /**
  * The field values for the record
  * @var object
  */
  var $Values;

  /**
  * The type of database write we will want: either "update" or "insert"
  * @var object
  */
  var $WriteType;

  /**
  * A list of associated other tables.
  * @var array of string
  */
  var $OtherTable;

  /**
  * The field names for each of the other tables associated.  The array index
  * is the table name, the string is a list of field names (and perhaps aliases)
  * to stuff into the target list for the SELECT.
  * @var array of string
  */
  var $OtherTargets;

  /**
  * An array of JOIN ... clauses.  The first array index is the table name and the array value
  * is the JOIN clause like "LEFT JOIN tn t1 USING (myforeignkey)".
  * @var array of string
  */
  var $OtherJoin;

  /**
  * An array of partial WHERE clauses.  These will be combined (if present) with the key
  * where clause on the main table.
  * @var array of string
  */
  var $OtherWhere;

  /**#@-*/

  /**#@+
  * @access public
  */
  /**
  * The mode we are in for any form
  * @var object
  */
  var $EditMode;

  /**#@-*/

  /**
  * Really numbingly simple construction.
  */
  function DBRecord( ) {
    global $session;
    $session->Log("DBG: DBRecord::Constructor: called" );
    $this->WriteType = "insert";
    $this->EditMode = false;
    $values = (object) array();
    $this->Values = &$values;
  }

  /**
  * This will read the record from the database if it's available, and
  * the $keys parameter is a non-empty array.
  * @param string $table The name of the database table
  * @param array $keys An associative array containing fieldname => value pairs for the record key.
  */
  function Initialise( $table, $keys = array() ) {
    global $session;
    $session->Log("DBG: DBRecord::Initialise: called" );
    $this->Table = $table;
    $this->Fields = get_fields($this->Table);
    $this->Keys = $keys;
    $this->WriteType = "insert";
  }

  /**
  * This will join an additional table to the maintained set
  * @param string $table The name of the database table
  * @param array $keys An associative array containing fieldname => value pairs for the record key.
  * @param string $join A PostgreSQL join clause.
  * @param string $prefix A field prefix to use for these fields to distinguish them from fields
  *                       in other joined tables with the same name.
  */
  function AddTable( $table, $target_list, $join_clause, $and_where ) {
    global $session;
    $session->Log("DBG: DBRecord::AddTable: $table called" );
    $this->OtherTable[] = $table;
    $this->OtherTargets[$table] = $target_list;
    $this->OtherJoin[$table] = $join_clause;
    $this->OtherWhere[$table] = $and_where;
  }

  /**
  * This will assign $_POST values to the internal Values object for each
  * field that exists in the Fields array.
  */
  function PostToValues( $prefix = "" ) {
    global $session;
    foreach ( $this->Fields AS $fname => $ftype ) {
      $session->Log("DBG: DBRecord::PostToValues: %s => %s", $fname, $_POST["$prefix$fname"] );
      if ( isset($_POST["$prefix$fname"]) ) {
        $this->Set($fname, $_POST["$prefix$fname"]);
        $session->Log("DBG: DBRecord::PostToValues: %s => %s", $fname, $_POST["$prefix$fname"] );
      }
    }
  }

  /**
  * Builds a table join clause
  * @return string A simple SQL target join clause excluding the primary table.
  */
  function _BuildJoinClause() {
    $clause = "";
    foreach( $this->OtherJoins AS $t => $join ) {
      if ( ! preg_match( '/^\s*$/', $join ) ) {
        $clause .= ( $clause == "" ? "" : " " )  . $join;
      }
    }

    return $clause;
  }

  /**
  * Builds a field target list
  * @return string A simple SQL target field list for each field, possibly including prefixes.
  */
  function _BuildFieldList() {
    $list = "";
    foreach( $this->Fields AS $fname => $ftype ) {
      $list .= ( $list == "" ? "" : ", " );
      $list .= "$fname" . ( $this->prefix == "" ? "" : " AS \"$this->prefix$fname\"" );
    }

    foreach( $this->OtherTargets AS $t => $targets ) {
      if ( ! preg_match( '/^\s*$/', $targets ) ) {
        $list .= ( $list == "" ? "" : ", " )  . $targets;
      }
    }

    return $list;
  }

  /**
  * Builds a where clause to match the supplied keys
  * @param boolean $overwrite_values Controls whether the data values for the key fields will be forced to match the key values
  * @return string A simple SQL where clause, including the initial "WHERE", for each key / value.
  */
  function _BuildWhereClause($overwrite_values=false) {
    $where = "";
    foreach( $this->Keys AS $k => $v ) {
      // At least assign the key fields...
      if ( $overwrite_values ) $this->Values->{$k} = $v;
      // And build the WHERE clause
      $where .= ( $where == "" ? "WHERE " : " AND " );
      $where .= "$k = " . qpg($v);
    }

    if ( isset($this->OtherWhere) && is_array($this->OtherWhere) ) {
      foreach( $this->OtherWhere AS $t => $and_where ) {
        if ( ! preg_match( '/^\s*$/', $and_where ) ) {
          $where .= ( $where == "" ? "WHERE " : " AND " )  . $and_where;
        }
      }
    }

    return $where;
  }

  /**
  * Sets a single field in the record
  * @param string $fname The name of the field to set the value for
  * @param string $fval The value to set the field to
  * @return mixed The new value of the field (i.e. $fval).
  */
  function Set($fname, $fval) {
    global $session;
    $session->Log("DBG: DBRecord::Set: %s => %s", $fname, $fval );
    $this->Values->{$fname} = $fval;
    return $fval;
  }

  /**
  * Returns a single field from the record
  * @param string $fname The name of the field to set the value for
  * @return mixed The current value of the field.
  */
  function Get($fname) {
    global $session;
    $session->Log("DBG: DBRecord::Get: %s => %s", $fname, $this->Values->{$fname} );
    return $this->Values->{$fname};
  }

  /**
  * To write the record to the database
  * @return boolean Success.
  */
  function Write() {
    global $session;
    $session->Log( "DBG: Writing %s record as %s.", $this->Table, $this->WriteType );
    $sql = sql_from_object( $this->Values, $this->WriteType, $this->Table, $this->_BuildWhereClause(), $this->prefix );
    $qry = new PgQuery($sql);
    return $qry->Exec( __CLASS__, __LINE__, __FILE__ );
  }

  /**
  * To read the record from the database.
  * If we don't have any keys then the record will be blank.
  * @return boolean Whether we actually read a record.
  */
  function Read() {
    global $session;
    $i_read_the_record = false;
    $values = (object) array();
    $this->EditMode = true;
    $where = $this->_BuildWhereClause(true);
    if ( "" != $where ) {
      // $fieldlist = $this->_BuildFieldList();
      $fieldlist = "*";
  //    $join = $this->_BuildJoinClause(true);
      $sql = "SELECT $fieldlist FROM $this->Table $where";
      $qry = new PgQuery($sql);
      if ( $qry->Exec( __CLASS__, __LINE__, __FILE__ ) && $qry->rows > 0 ) {
        $i_read_the_record = true;
        $values = $qry->Fetch();
        $this->EditMode = false;  // Default to not editing if we read the record.
        $session->Log( "DBG: DBRecord::Read: Read %s record from table.", $this->Table, $this->WriteType );
      }
    }
    $this->Values = &$values;
    $this->WriteType = ( $i_read_the_record ? "update" : "insert" );
    $session->Log( "DBG: DBRecord::Read: Record %s write type is %s.", $this->Table, $this->WriteType );
    return $i_read_the_record;
  }
}

?>
