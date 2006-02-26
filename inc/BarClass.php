<?php

require_once("PgQuery.php");

class BarChart {
  var $qry;
  var $radius;
  var $font_height;
  var $data;
  var $total;

  function BarChart($sql) {
    $this->qry = new PgQuery($sql);
    $this->height = 150;
    $this->width = 450;
    $this->margin = 10;
    $this->font_height = 12;
    $this->total = 0;
    $this->max = 0;
    $this->colorSlice = array('#ee0000','#00ee00','#0000ee','#eeee00','#ee00ee','#00eeee',
                              '#bb0000','#00bb00','#0000bb','#bbbb00','#bb00bb','#00bbbb',
                              '#880000','#008800','#000088','#888800','#880088','#008888',
                             );
  }

  function _ScaledRectangle($index, $label, $value, $color) {
    $width = $this->barwidth;
    $height = intval(($value * $this->height) / $this->max);
    $x = intval(($index * $this->width) / $this->count) + $this->margin;
    $y = $this->height + $this->margin - $height;
    $rect = sprintf('<rect x="%s" y="%s" width="%s" height="%s" rx="0" style="fill:%s; stroke:black; stroke-width:1" />'."\n", $x, $y, $width, $height, $color);

    //draw value
    $x = $x + ($this->barwidth / 2) - (($this->font_height/2) * strlen("$value"));
    $y = $this->height + $this->margin + $this->font_height + 2;
    $rect .= sprintf('<text x="%s" y="%s" style="fill:black; font-size:%s">%s</text>'."\n", $x, $y, $this->font_height, $value );

    //draw label
    $x = intval(($index * $this->width) / $this->count) + $this->margin;
    $y = $this->height + $this->margin + (($this->font_height + 2) * 2);
    $rect .= sprintf('<text x="%s" y="%s" style="fill:black; font-size:%s" transform="rotate(30,%s,%s)">%s</text>'."\n", $x, $y, $this->font_height, $x, $y, $label );

    return $rect;
  }

  function Exec($location="BarChart") {
    $this->data = array();

    // Check for a number of rows first, otherwise Exec the query
    if ( $this->qry->rows > 0 || $this->qry->Exec($location) ) {
      $this->total = 0;
      $this->max = 0;
      $this->rownum = -1;
      while( $row = $this->qry->Fetch(true) ) {
        $this->data[$row[0]] = $row[1];
        $this->total += $row[1];
        if ( $row[1] > $this->max ) $this->max = $row[1];
      }
      $this->barwidth = intval((0.9 *$this->width) / $this->qry->rows);
    }
    $this->count = $this->qry->rows;
    return $this->count;
  }


  function Render() {
    if ( !isset($this->data) ) {
      $this->Exec();
    }

    //determine graphic size
    $Width = $this->width + ($this->margin * 2);
    $Height = $this->height + ($this->margin * 2) + (($this->font_height + 2) * $this->count);

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
    print("<svg xmlns=\"http://www.w3.org/2000/svg\" xml:space=\"preserve\" >\n");

    /*
    ** make background rectangle
    */

    print("<rect x=\"0\" y=\"0\" width=\"$Width\" height=\"$Height\" style=\"fill:$colorBody\" />\n");

    /*
    ** draw each bar
    */

    $offset = 0;
    $index = 0;

    foreach($this->data as $Label=>$Value) {
      print $this->_ScaledRectangle( $index++, $Label, $Value, $this->colorSlice[3] );
    }

    /*
    ** draw border
    */
//    printf('<rect x="%s" y="%s" width="%s" height="%s" style="fill:none; stroke:black; stroke-width:3"'." />\n",
//                $this->margin - 4, $this->margin - 4, $this->width + 4, $this->height + 4);

    //end SVG document
    print('</svg>' . "\n");

  }
}


?>