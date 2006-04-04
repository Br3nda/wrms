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
    $this->colorSlice = array('#ee0000','#00ee00','#0000ee','#eeee00','#ee00ee','#00eeee',
                              '#bb0000','#00bb00','#0000bb','#bbbb00','#bb00bb','#00bbbb',
                              '#880000','#008800','#000088','#888800','#880088','#008888',
                             );
  }

  function _CirclePoint($degrees, $radius) {
    $x = cos(deg2rad($degrees)) * $radius;
    $y = sin(deg2rad($degrees)) * $radius;
    return (array($x, $y));
  }

  function Exec($location="PieChart") {
    $this->data = array();
    $this->url = array();

    // Check for a number of rows first, otherwise Exec the query
    if ( $this->qry->rows > 0 || $this->qry->Exec($location) ) {
      $this->total = 0;
      $this->rownum = -1;
      while( $row = $this->qry->Fetch(true) ) {
        $this->data[$row[0]] = $row[1];
        if ( isset($row[2]) ) $this->url[$row[0]] = $row[2];
        $this->total += $row[1];
      }
    }
    return $this->qry->rows;
  }

  function RenderSlice( $name, $offset, $value, $colour_index ) {

      $StartDegrees = round(($offset / $this->total) * 360);
      $EndDegrees = round((($offset + $value)/$this->total)*360);
      $CurrentColor = $this->colorSlice[($colour_index % count($this->colorSlice))];

      $CenterX = $this->radius + $this->margin;
      $CenterY = $this->radius + $this->margin;

      $h = "<!--$name: $offset for $value (of $this->total) $StartDegrees to $EndDegrees degrees in $CurrentColor-->\n";

      $h .= "<path d=\"";

      //draw out to circle edge
      list($StartX, $StartY) = $this->_CirclePoint($StartDegrees, $this->radius);
      list($EndX, $EndY) = $this->_CirclePoint($EndDegrees, $this->radius);

      $floodstyle = (( ($value * 2) > $this->total ) ? 1 : 0 );

      $h .= ("M " .  ($CenterX + $StartX) . "," .  ($CenterY + $StartY) ." ");
      $h .= ("A $this->radius,$this->radius 0 $floodstyle 1 " .  ($CenterX + $EndX) . "," .  ($CenterY + $EndY) ." ");

      //finish at center of circle
      $h .= ("L $CenterX,$CenterY ");

      //close polygon
      $h .= ("z\" ");

      $h .= ("style=\"fill:$CurrentColor;\"/>\n");
      return $h;
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
    print("<svg xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xml:space=\"preserve\" >\n");

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
      if ( isset($this->url[$Label]) ) {
        printf( '<a xlink:href="%s" xlink:show="parent">'."\n%s</a>\n", htmlentities($this->url[$Label]), $this->RenderSlice( $Label, $offset, $Value, $index ) );
      }
      else {
        echo $this->RenderSlice( $Label, $offset, $Value, $index );
      }
      $index++;
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
      print("<text x=\"40\" y=\"$LabelY\" style=\"fill:$colorText; font-size:$this->font_height"."px\">" .
        "$Label: $Value" .  "</text>\n");
    }

    //end SVG document
    print('</svg>' . "\n");

  }
}


?>