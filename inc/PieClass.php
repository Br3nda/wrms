<?php

require_once("PgQuery.php");

class PieChart {
  var $qry;
  var $radius;
  var $font_height;
  var $data;
  var $total;

  function PieChart($sql) {
    $this->qry = new PgQuery($sql);
    $this->radius = 150;
    $this->margin = 10;
    $this->font_height = 12;
    $this->total = 0;
    $this->colorSlice = array('#FF0000','#00FF00','#0000FF','#FFFF00','#FF00FF','#00FFFF',
                              '#bb0000','#00bb00','#0000bb','#bbbb00','#bb00bb','#00bbbb',
                              '#880000','#008800','#000088','#888800','#880088','#008888',
                             );
  }

  function _CirclePoint($degrees, $radius) {
    //avoid problems with doubles
    $degrees += 0.0001;
    $x = cos(deg2rad($degrees)) * $radius;
    $y = sin(deg2rad($degrees)) * $radius;
    return (array($x, $y));
  }

  function Exec($location="PieChart") {
    $this->data = array();

    // Check for a number of rows first, otherwise Exec the query
    if ( $this->qry->rows > 0 || $this->qry->Exec($location) ) {
      $this->total = 0;
      $this->rownum = -1;
      while( $row = $this->qry->Fetch(true) ) {
        $this->data[$row[0]] = $row[1];
        $this->total += $row[1];
      }
    }
    return $this->qry->rows;
  }
/*
   <!--Testing/Signoff: 13 (of 16) 0 to 293 degrees in #FF0000-->
   <svg:path d="M 309,160 A 140,140 0 1 1 218,21 L 160,160 z" style="fill:#c50000" id="path6"/>
   <!--New request: 1 (of 16) 293 to 315 degrees in #00FF00-->
   <svg:path d="M 160,160 L 218,21 C 235.91977,28.460475 252.22119,39.328089 266,53 L 160,160 z " style="fill:#00ff00" id="path8"/>
   <!--Catalyst Testing: 1 (of 16) 315 to 338 degrees in #0000FF-->
   <svg:path d="M 160,160 L 266,53 C 280.25019,67.278128 291.474,84.283905 299,103 L 160,160 z " style="fill:#0000ff" id="path10"/>
   <!--In Progress: 1 (of 16) 338 to 360 degrees in #FFFF00-->
   <svg:path d="M 160,160 L 299,103 C 306.00161,121.17078 309.39815,140.53102 309,160 L 160,160 z " style="fill:#ffff00" id="path12"/>
*/
  function RenderSlice( $name, $offset, $value, $colour_index ) {

      $StartDegrees = round(($offset / $this->total) * 360);
      $EndDegrees = round((($offset + $value)/$this->total)*360);
      $CurrentColor = $this->colorSlice[($colour_index % count($this->colorSlice))];

      $CenterX = $this->radius + $this->margin;
      $CenterY = $this->radius + $this->margin;

      print("<!--$name: $offset for $value (of $this->total) $StartDegrees to $EndDegrees degrees in $CurrentColor-->\n");

      print("<path d=\"");

      //draw out to circle edge
      list($StartX, $StartY) = $this->_CirclePoint($StartDegrees, $this->radius);
      list($EndX, $EndY) = $this->_CirclePoint($EndDegrees, $this->radius);

      $floodstyle = (( ($value * 2) > $this->total ) ? 1 : 0 );

      print("M " .  floor($CenterX + $StartX) . "," .  floor($CenterY + $StartY) ." ");
      print("A $this->radius,$this->radius 0 $floodstyle 1 " .  floor($CenterX + $EndX) . "," .  floor($CenterY + $EndY) ." ");

      //finish at center of circle
      print("L $CenterX,$CenterY ");

      //close polygon
      print("z\" ");

      print("style=\"fill:$CurrentColor;\"/>\n");
  }


  function Render() {
    if ( !isset($this->data) ) {
      $this->Exec();
    }

    //determine graphic size
    $Diameter = $this->radius * 2;
    $Width = $Diameter + ($this->margin * 2);
    $Height = $Diameter + ($this->margin * 2) + (($this->font_height + 2) * count($this->data));
    $CenterX = $this->radius + $this->margin;
    $CenterY = $this->radius + $this->margin;

    //allocate colors
    $colorBody = 'white';
    $colorBorder = 'black';
    $colorText = 'black';

    //set the content type
    header("Content-type: image/svg+xml");

    //mark this as XML
    print('<?xml version="1.0" encoding="iso-8859-1"?>' . "\n");

    //Point to SVG DTD
    print('<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 20000303 Stylable//EN" ');
                    print('"http://www.w3.org/TR/2000/03/WD-SVG-20000303/DTD/' .  'svg-20000303-stylable.dtd">' . "\n");

    //start SVG document, set size in "user units"
    print("<svg xmlns=\"http://www.w3.org/2000/svg\" xml:space=\"preserve\" width=\"5in\" height=\"5in\" viewBox=\"0 0 $Width $Height\">\n");

    /*
    ** make background rectangle
    */

    print("<rect x=\"0\" y=\"0\" width=\"$Width\" height=\"$Height\" style=\"fill:$colorBody\" />\n");

    /*
    ** draw each slice
    */

    $offset = 0;
    $index = 0;

    foreach($this->data as $Label=>$Value) {
      $this->RenderSlice( $Label, $offset, $Value, $index++ );
      $offset += $Value;
    }

    /*
    ** draw border
    */
    print("<circle cx=\"$CenterX\" cy=\"$CenterY\" r=\"$this->radius\" " .
                  "style=\"fill:none; stroke:$colorBorder; stroke-width:5\" />\n");

    /*
    ** draw legend
    */
    $index=0;
    foreach($this->data as $Label=>$Value) {
      $CurrentColor = $this->colorSlice[$index%(count($this->colorSlice))];
      $BoxY = $Diameter + 20 + (($index++)*($this->font_height+2));
      $LabelY = $BoxY + $this->font_height;

      //draw color box
      print("<rect x=\"10\" y=\"$BoxY\" width=\"20\" height=\"$this->font_height\" rx=\"5\" " .
                  "style=\"fill:$CurrentColor; stroke:$colorBorder; stroke-width:1\" />\n");

      //draw label
      print("<text x=\"40\" y=\"$LabelY\" style=\"file:$colorText; font-size:$this->font_height\">" .
        "$Label: $Value" .  "</text>\n");
    }

    //end SVG document
    print('</svg>' . "\n");

  }
}




?>