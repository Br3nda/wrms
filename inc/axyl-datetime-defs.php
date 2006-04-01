<?php
/* ******************************************************************** */
/* CATALYST PHP Source Code                                             */
/* -------------------------------------------------------------------- */
/* This program is free software; you can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License, or    */
/* (at your option) any later version.                                  */
/*                                                                      */
/* This program is distributed in the hope that it will be useful,      */
/* but WITHOUT ANY WARRANTY; without even the implied warranty of       */
/* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        */
/* GNU General Public License for more details.                         */
/*                                                                      */
/* You should have received a copy of the GNU General Public License    */
/* along with this program; if not, write to:                           */
/*   The Free Software Foundation, Inc., 59 Temple Place, Suite 330,    */
/*   Boston, MA  02111-1307  USA                                        */
/* -------------------------------------------------------------------- */
/*                                                                      */
/* Filename:    datetime-defs.php                                       */
/* Author:      Paul Waite                                              */
/* Description: Various general date/time routines.                     */
/*                                                                      */
/* ******************************************************************** */
/** @package datetime */

// DATE FORMAT DEFINITIONS
/** Example: 31/12/1999 23:59 */
define("DISPLAY_DATE_FORMAT",      "d/m/Y H:i");

/** Example: 31/12/1999 23:59:59 */
define("DISPLAY_TIMESTAMP_FORMAT", "d/m/Y H:i:s");

/** Example: 31/12/1999 */
define("DISPLAY_DATE_ONLY",        "d/m/Y");

/** Example: 1999/12/31 */
define("BACKWARDS_DATE_ONLY",      "Y/m/d");

/** Example: Mar 3rd */
define("NICE_DATE_NOYEAR",         "M jS");

/** Example: Mar 3rd 1:30pm */
define("NICE_DATETIME",            "M jS g:ia");

/** Example: Mar 3 21:30 */
define("SHORT_DATETIME",           "M j H:i");

/** Example: Mar 3rd 1999 */
define("NICE_DATE",                "M jS Y");

/** Example: Mar 3rd 1999 1:30pm */
define("NICE_FULLDATETIME",        "M jS Y g:ia");

/** Example: Friday, 20th July 2001 */
define("DAY_AND_DATE",             "l, jS F Y");

/** Example: 23:59 */
define("DISPLAY_TIME_ONLY",        "H:i");

/** Example: 1:30pm */
define("NICE_TIME_ONLY",           "g:ia");

/** Example: 12-31-1999 23:59:59 */
define("POSTGRES_DATE_FORMAT",     "m/d/Y H:i:s");

/** Example: 1999-07-17 23:59:59 */
define("POSTGRES_STD_FORMAT",      "Y-m-d H:i:s");

/** Example: 1999-07-17 23:59:59 */
define("ISO_DATABASE_FORMAT",      "Y-m-d H:i:s");

/** Example: 23/5/2001 */
define("STD_DMY",                  "d/m/Y");

/** Example: 12/31/1999 23:59:59 */
define("SQL_FORMAT",               "m/d/Y H:i:s");

/** Example: Sun 23:59 */
define("DOW_HHMM",                 "D H:i");

/** ISO 8601 Example: YYYYMMDDTHHMMSS-HHMM */
define("ISO_8601",                 "Ymd\THisO");

// -----------------------------------------------------------------------
// Handy vars..
/** Months associative array keyed on three-letter monthname, returns monthno (1-12) */
$months       = array("Jan" => 1, "Feb" =>  2, "Mar" =>  3, "Apr" =>  4,
                      "May" => 5, "Jun" =>  6, "Jul" =>  7, "Aug" =>  8,
                      "Sep" => 9, "Oct" => 10, "Nov" => 11, "Dec" => 12);
/** Array elements 1-12 contain full month names, zeroth element is 'Unknown' */
$monthnames   = array("Unknown","January","February","March","April","May","June",
                      "July","August","September","October","November","December");
/** Array of month lengths 0-11 */
$monthlengths = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
/** Days of week keyed by three-letter day-name, returns day number (0-6) */
$daywk        = array("Sun" => 0, "Mon" => 1, "Tue" => 2, "Wed" => 3, "Thu" => 4,
                      "Fri" => 5, "Sat" => 6);
/** Array elements 0-6 contain full day names Sunday(0) -> Saturday(6) */
$daynames     = array("Sunday","Monday","Tuesday","Wednesday","Thursday",
                      "Friday","Saturday");

// .......................................................................
/**
* Return the days in a month
* Given an integer month number 1-12, and an optional
* year (defaults to current) this function returns
* the number of days in the given month.
* @param integer $monthno  Month no. 1 - 12
* @param integer $year     Year
* @return integer Number of days in month
*/
function daysinmonth($monthno, $year=0) {
  global $monthlengths;
  $numdays = 0;
  if ($monthno >= 1 && $monthno <= 12) {
    $numdays = $monthlengths[$monthno];
    // The leap year crappola..
    if ($monthno == 2) {
      if ($year == 0) {
        $datetoday = getdate();
        $year = $datetoday["year"];
      }
      if (isLeapYear($year)) {
        $numdays += 1;
      }
    }
  }
  return $numdays;
}

// .......................................................................
/**
* Check if year is a leap year.
* Determines whether the given year is a leap year.
* @param integer $year     Year
* @return boolean True if year is a leap year, else false
*/
function isLeapYear($year) {
  $result = ($year % 4 == 0) &&
            (($year % 100 != 0) || ($year % 400 == 0));
  return $result;
}

// .......................................................................
/**
* Tell nice difference between too datetimes
* Returns a nice expression of the difference between the
* two given datetimes written in a nice English sentence
* @param string $dfrom From date in Postgres datetime format
* @param string $dto   To date in Postgres datetime format
* @return string Nice sentence describing difference
*/
function prose_diff($dfrom, $dto) {
  $prose = "";
  if ($dfrom == "now") $fromts = time();
  else $fromts = datetime_to_timestamp($dfrom);

  if ($dto == "now") $tots = time();
  else $tots = datetime_to_timestamp($dto);

  return prose_diff_ts($fromts, $tots);
} // prose_diff

// .......................................................................
/**
* Tell nice difference between too Unix timestamps
* Returns a nice expression of the difference between the
* two given timestamps, written in a nice English sentence
* @param integer $fromts From datetime as Unix timestamp
* @param integer $tots   To datetime as Unix timestamp
* @return string Nice sentence describing the time interval
*/
function prose_diff_ts($fromts, $tots) {
  $prose = "";
  $diff = $tots - $fromts;
  if ($diff >= 0) {
    $weeks = floor($diff / 604800);
    $diff = $diff - ($weeks * 604800);
    $days  = floor($diff / 86400);
    $diff = $diff - ($days * 86400);
    $hours  = floor($diff / 3600);
    $diff = $diff - ($hours * 3600);
    $mins  = floor($diff / 60);
    if ($weeks > 0) $prose .= "$weeks weeks";
    if ($days > 0) {
      if ($prose != "") $prose .= ", ";
      $prose .= "$days days";
    }
    if ($hours > 0) {
      if ($prose != "") $prose .= ", ";
      $prose .= "$hours hours";
    }
    if ($mins > 0) {
      if ($prose != "") $prose .= ", and ";
      $prose .= "$mins minutes";
    }
  }
  return $prose;
} // prose_diff_ts

// .......................................................................
/**
* Tell minutes difference between too datetimes
* Returns the minutes difference between the two given
* datetimes.
* @param string $dfrom From date in 'descriptive' format
* @param string $dto   To date in 'descriptive' format
* @return integer The number of minutes difference
*/
function mins_diff($dfrom, $dto) {
  if ($dfrom == "now") $fromts = time();
  else $fromts = datetime_to_timestamp($dfrom);

  if ($dto == "now") $tots = time();
  else $tots = datetime_to_timestamp($dto);

  $diff = $tots - $fromts;
  if ($diff >= 0) $mins = floor($diff / 60);
  else $mins = -1;

  return $mins;
}

// .......................................................................
/**
* Nice days difference between two timestamps. Returns a nice phrase
* expressing the difference in days.
* @param string $fromts From Unix timestamp
* @param string $tots To Unix timestamp (defaults to 'now')
* @return string Nice sentence describing difference in days
*/
function days_diff_ts($fromts, $tots="now") {
  $s = "";
  if ($tots === "now") {
    $tots = time();
  }
  $diff_secs = abs($fromts - $tots);
  $diff_days = number_format($diff_secs / (3600 * 24), 0);
  if ($diff_days == 0) {
    $s = "less than a day";
  }
  elseif ($diff_days == 1) {
    $s = "1 day";
  }
  else {
    $s = "$diff_days days";
  }
  return $s;
} // days_diff_ts

// -----------------------------------------------------------------------
// DATE CONVERSION: USER DISPLAY -> DATABASE
// -----------------------------------------------------------------------
/**
* Conversion: descriptive to timestamp.
* Converts a descriptive date/time string into a timestamp.
* @param string $displaydate   Date in any 'descriptive' format
* @return integer Unix timestamp (earliest = 1970-1-1)
*/
function displaydate_to_timestamp($displaydate) {
  $res = 0;
  if ($displaydate) {
    $dmy = get_DMY($displaydate);
    if (($dmy["year"] == 1900 || $dmy["year"] == 1970) && $dmy["month"] == 1 && $dmy["day"] == 1) {
      // Invalid date, so try magic wand..
      $res = strtotime($displaydate);
    }
    else {
      if (checkdate($dmy["month"], $dmy["day"], $dmy["year"])) {
          $hms = get_HMS($displaydate);
          $res = mktime( $hms["hours"], $hms["minutes"], $hms["seconds"],
                        $dmy["month"], $dmy["day"],     $dmy["year"] );
      }
      else {
        // Try this magic routine as last resort..
        $res = strtotime($displaydate);
      }
    }
  }
  return $res;
}

// .......................................................................
/**
* Conversion: timestamp to datetime.
* Returns a string in the correct format for populating
* a database 'datetime' field, as per ISO 8601 spec.
* @param  integer $timestamp   Unix timestamp
* @return string  Datetime string in ISO YYYY-MM-DD HH:MM:SS format
*/
function timestamp_to_datetime($timestamp=false) {
  $res = false;
  if ($timestamp === false) {
    $timestamp = time();
  }
  $res = date(ISO_DATABASE_FORMAT, $timestamp);
  return $res;
}

// .......................................................................
/**
* Conversion: descriptive to datetime.
* Converts a descriptive date/time string into a database
* compatible 'datetime' field as per Postgres ISO spec.
* @param string $displaydate   Date in any 'descriptive' format
* @return string  Datetime string in Postgres YYYY-MM-DD HH:MM:SS format
*/
function displaydate_to_datetime($displaydate) {
  return timestamp_to_datetime( displaydate_to_timestamp($displaydate) );
}

// .......................................................................
/**
* Conversion: descriptive to datetime without the time element.
* Converts a descriptive date/time string into a database
* compatible 'date' field as per Postgres ISO spec.
* @param string $displaydate   Date in any 'descriptive' format
* @return string  Date string in Postgres YYYY-MM-DD format
*/
function displaydate_to_date($displaydate) {
  $dt = displaydate_to_datetime($displaydate);
  $bits = explode(" ", $dt);
  return $bits[0];
}

// -----------------------------------------------------------------------
// DATE CONVERSION: DATABASE -> USER DISPLAY
// -----------------------------------------------------------------------
/**
* Conversion: datetime to descriptive
* Returns a timestamp from a database-formatted datetime string.
* We are assuming the datetime is formatted in the ISO format of
* "1999-07-17 23:59:59+12" - POSTGRES_STD_FORMAT (ISO)
* This is set up by the standard application via an SQL query
* with the "SET DATESTYLE ISO" command (see query-defs.php).
* @param  string  $datetime   Database-compatible ISO date string
* @return integer Unix timestamp (earliest = 1970-1-1)
*/
function datetime_to_timestamp($datetime) {
  $res = FALSE;
  if ($datetime) {
    $dt = explode("+", $datetime);
    $dtim = explode(" ", $dt[0]);

    $ymd = explode("-", $dtim[0]);
    if (isset($dtim[1])) $tim = explode(":", $dtim[1]);

    if (isset($ymd[0])) $year = $ymd[0];
    else $year = 1970;
    if (isset($ymd[1])) $month = $ymd[1];
    else $month = 1;
    if (isset($ymd[2])) $day = $ymd[2];
    else $day = 1;

    if (isset($tim[0])) $hrs = $tim[0];
    else $hrs = 0;
    if (isset($tim[1])) $min = $tim[1];
    else $min = 0;
    if (isset($tim[2])) $sec = $tim[2];
    else $sec = 0;

    $res = mktime($hrs, $min, $sec, $month, $day, $year);
  }
  return $res;
}

// .......................................................................
/**
* Conversion: timestamp to descriptive
* Convert a Unix timestamp into a descriptive date/time format
* for the user display, using the given displayformat string.
* @param  string  $displayformat  See defined formats.
* @param  integer $timestamp      Unix timestamp
* @return string  The formatted descriptive datetime string
*/
function timestamp_to_displaydate($displayformat, $timestamp="") {
  if ($timestamp == "") {
    $timestamp = time();
  }
  return date($displayformat, $timestamp);
}

// .......................................................................
/**
* Conversion: datetime to descriptive
* Convert a database-compatible datetime string into a
* descriptive date/time format for the user display,
* using the given displayformat string.
* @param  string  $displayformat  See defined formats.
* @param  string  $datetime       Database datetime string
* @return string  The formatted descriptive datetime string
*/
function datetime_to_displaydate($displayformat, $datetime) {
  if ($datetime == NULL) {
    return "";
  }
  else {
    return timestamp_to_displaydate( $displayformat, datetime_to_timestamp($datetime) );
  }
}

// .......................................................................
/**
* Format date as DD/MM/YYYY
* Take a date string as entered in a form and reformat it
* to DD/MM/YYYY. Obviously it as to be more or less in this
* format to begin with, but we cope with a few foibles here
* like two-digit year, different delimiters and also strip
* off any time component added to the end.
* @param  string  $dtstr  Date string to format
* @return string  The formatted DD/MM/YYYY date
*/
function format_DMY($dtstr="") {
  // Null parm means grab todays date...
  if ($dtstr == "") return todaysDate_DMY();
  list($day, $month, $year) = get_DMY($dtstr);

  // Return the date with leading zeros..
  return (sprintf("%02d/%02d/%04d", $day, $month, $year));
}

// .......................................................................
/**
* Return month number
* Return the number of the named month, default
* to Jan if problems arise.
* @param string $monthname  Name of the month
* @return integer The number of the month 1 - 12
*/
function monthno($monthname) {
  global $months;
  $res = 1;
  if ($monthname) {
    $monthname = substr(ucfirst(strtolower(trim($monthname))), 0, 3);
    $res = $months["$monthname"];
  }
  return $res;
}

// .......................................................................
/**
* Today as MM/DD/YYYY
* Returns the given datetime as a date, and in SQL
* 'm/d/Y' format.
* @return string The formatted MM/DD/YYYY SQL date
*/
function todaysDate_MDY() {
  return date("m/d/Y");
}

// .......................................................................
/**
* Today as DD/MM/YYYY
* Returns the given datetime as a date, and in EUROPEAN
* 'd/m/Y' format.
* @return string  The formatted DD/MM/YYYY date
*/
function todaysDate_DMY() {
  return date("d/m/Y");
}

// .......................................................................
/**
* Time now as array
* Returns the current time as an associative array:
*  ["hours"]   - Hours since midnight
*  ["minutes"] - Minute of the curent hour
*  ["seconds"] - Seconds of the current minute
* @return array  Containing the time components
*/
function timeNow() {
  $tod = getdate();
  $tinow["hours"]   = $tod["hours"];
  $tinow["minutes"] = $tod["minutes"];
  $tinow["seconds"] = $tod["seconds"];
  return $tinow;
}

// .......................................................................
/**
* Time now as string
* Returns the current time as a string 'hr:min:sec'
* @return string Time now in hh:mm:ss format.
*/
function timeNowAsStr() {
  $tinow = timeNow();
  return $tinow["hours"] . ":" . $tinow["minutes"] . ":" . $tinow["seconds"];
}

// .......................................................................
/**
* Year now as integer
* Returns the year as it is now.
* @return integer Year now in 9999 format
*/
function yearNow() {
  $tod = getdate();
  return $tod["year"];
}

// .......................................................................
/**
* Month now as integer
* Returns the month (1-12) as it is now.
* @return integer Month number now
*/
function monthNow() {
  $tod = getdate();
  return $tod["mon"];
}

// .......................................................................
/**
* Month now as string
* Returns the month name (eg. "January") as it is now..
* @return string Month name now
*/
function monthNameNow() {
  $tod = getdate();
  return $tod["month"];
}

// .......................................................................
/**
* Day of Month now as integer
* Returns the day of the month (1-31) as it is now.
* @return integer Day of Month now
*/
function dayNow() {
  $tod = getdate();
  return $tod["wday"];
}

// .......................................................................
/**
* Day of Week now as string
* Returns the day name (eg. "Monday") as it is now.
* @return string Day of week name
*/
function dayNameNow() {
  $tod = getdate();
  return $tod["weekday"];
}

// .......................................................................
/**
* Get date components of datetime string
* Returns the date components of a formatted date string.
* We start by assuming DD/MM/YYYY ordering, but can
* alter this if it looks wrong. Note that the date string we
* are looking at must be in vaguely standard format, ie. with
* "/" or "-" or "." as delimiter etc. We return an array as:
*   ["day"]   - Day of month (1 - 31)
*   ["month"] - Month number (1 - 12)
*   ["year"]  - Year eg. 1982
* @param string $displaydate Descriptive display date string
* @return array Date components
*/
function get_DMY($displaydate) {
  $dtbits = explode(" ", $displaydate);
  $dt = $dtbits[0];

  // We have to have a valid delimiter..
  if (strstr($dt, "/")) $delim = "/";
  elseif (strstr($dt, "-")) $delim = "-";
  elseif (strstr($dt, ".")) $delim = ".";
  else $delim = "";

  // No delimiter means no date..
  if ($delim == "") {
    $day   = 1;
    $month = 1;
    $year  = 1900;
  }
  else {
    $dtbits = explode($delim, $dt);
    $day   = $dtbits[0];
    $month = $dtbits[1];
    $year  = $dtbits[2];
  }

  // Check for year-month-day order..
  if ($day > 31) {
    $tmp = $year;
    $year = $day;
    $day = $tmp;
  }

  // Check for month-day-year order..
  if ($month > 12 && $day <= 12) {
    $tmp = $month;
    $month = $day;
    $day = $tmp;
  }

  // Make sure year is always four digits..
  if ($year < 30) $year += 2000;
  elseif ($year < 100) $year += 1900;

  // Final validity check..
  if (!checkdate($month, $day, $year)) {
    $day   = 1;
    $month = 1;
    $year  = 1900;
  }

  // Return as a assoc. array
  $dmy["day"] = $day;
  $dmy["month"] = $month;
  $dmy["year"] = $year;
  return $dmy;
}

// .......................................................................
/**
* Get time components of datetime string
* Extract the HH:MM[:SS] from datetime string
* Returns the time components of a formatted date string.
* We return an array as:
*   ["hours"]   - Hours (0 - 23)
*   ["minutes"] - Minutes (0 - 59)
*   ["seconds"] - Seconds (0 - 59)
* @param string $displaydate Descriptive display date string
* @return array Time components
*/
function get_HMS($displaydate="") {
  if ($displaydate == "") {
    // Return current time..
    return timeNow();
  }
  else {
    // Unpack string..
    if (strstr($displaydate, " ")) {
      $dtbits = explode(" ", $displaydate);
      if (isset($dtbits[1])) $ti = $dtbits[1];
      else $ti = $dtbits[0];
    }
    else {
      $ti = $displaydate;
    }

    // Search for our time delimiter..
    if (strstr($ti, ":")) $delim = ":";
    else $delim = "";

    // Only proceed if we have a delimiter. No delimiter
    // means there is no time in the string at all..
    if ($delim != "") $tibits = explode($delim, $ti);

    // Extract results or default the values..
    if (isset($tibits[0])) $hrs = $tibits[0];
    else $hrs = 0;
    if (isset($tibits[1])) $min = $tibits[1];
    else $min = 0;
    if (isset($tibits[2])) $sec = $tibits[2];
    else $sec = 0;
  }

  // Return as a assoc. array
  $hms["hours"]   = $hrs;
  $hms["minutes"] = $min;
  $hms["seconds"] = $sec;
  return $hms;
}

// .......................................................................
/** Classes to handle a 24-hr time schedule. Covers the setting of
** any number of slots, and testing whether a given time is in one of
** the slots. The timeslot class. Holds details of a single slot.
* @package datetime
*/
class timeslot {
  var $ts_start = 0;
  var $ts_end = 0;
  var $name = "";
  function timeslot($start=0, $end=0, $name="") {
    $this->ts_start = $start;
    $this->ts_end = $end;
    $this->name = $name;
  }
  function inslot($time) {
    return ($time >= $this->ts_start && $time <= $this->ts_end);
  }
}

// .......................................................................
/** Classes to handle a 24-hr time schedule. Covers the setting of
** any number of slots, and testing whether a given time is in one of
** the slots. The schedule class. Holds multiple timeslots.
* @package datetime
*/
class schedule {
  /** Array of timeslots in a schedule. */
  var $timeslots = array();

  function schedule($timeslots="") {
    if (is_array($timeslots)) {
      $this->timeslots = $timeslots;
    }
  }
  // .....................................................................
  /**
  * Add timeslot. The start and end parameters can either be Unix timestamps,
  * or string datetime specifiers.
  * @param mixed $start Start time for timeslot, string datetime or Unix timestamp
  * @param mixed $end End time for timeslot, string datetime or Unix timestamp
  * @param string $name The name or ID associated with this timeslot.
  */
  function add_timeslot($start, $end, $name="") {
    if (!is_int($start)) {
      $start = strtotime($start);
    }
    if (!is_int($end)) {
      $end = strtotime($end);
    }
    $this->timeslots[] = new timeslot($start, $end, $name);
  }
  // .....................................................................
  /*
  * Check if the given time lies within a schedule slot. If it isn't then we
  * return false. If it is then we return the name of the slot that it is in.
  * If timeslots overlap then the rule is we return the FIRST one which we
  * find. That way you should start by defining short/specific timeslots and
  * add the generic/catchall timeslots at the bottom of your list.
  * @param mixed $datetime Time to check, string datetime or Unix timestamp
  */
  function timeslot($datetime) {
    $slot = false;
    if (!is_int($datetime)) {
      $datetime = strtotime($datetime);
    }
    foreach ($this->timeslots as $timeslot) {
      if ($timeslot->inslot($datetime)) {
        $slot = $timeslot->name;
        break;
      }
    }
    // Return FALSE or slotname found..
    return $slot;
  }
}

// -----------------------------------------------------------------------
?>
