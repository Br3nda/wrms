<?php
  include("inc/always.php");

  $title = "Demonstrate Mozilla Bug(s) with form styling";
  include("inc/starthead.php");

  echo "<style type=\"text/css\"><!--
p		{font: small tahoma, sans-serif; }
th		{font: bold x-small tahoma, sans-serif; color: white; text-align: right; background: $colors[8]; }
td		{font: x-small tahoma, sans-serif; }
input		{font: bold x-large tahoma, sans-serif; background: $colors[7]; }
textarea		{font: bold x-large tahoma, sans-serif; background: $colors[7]; }
select		{font: xx-small tahoma, sans-serif; background: $colors[7]; }
--></style>";

  include("inc/bodydef.php");

?>
<p>Update 29/3/2000:<BR>Most things seems to be working now, but:</p>
<li>font-family for TEXTAREA is not being applied from the 
style <a href=http://bugzilla.mozilla.org/show_bug.cgi?id=28219>(see bug)</a></li>
<li>ROWS=Y COLS=X also seem to be being ignored (or 
misinterpreted in some fashion) for TEXTAREA</li>
<li>SIZE=X doesn't seem to be being dealt with appropriately for INPUT TYPE=TEXT fields</li>

<form action=mozilla-bug-demo.php method=post>
<table>
<tr valign=middle>
<th>Text Input</th><td>&nbsp;<input TYPE="Text" Size="10" Name="search_for" Value="Some Text01234567890"><BR>
INPUT TYPE=TEXT SIZE=10</td>
</tr>
<tr>
<th>Textarea</th><td>&nbsp;<textarea NAME=system_code wrap=soft rows=5 cols=30 WRAP>Text in a text area before we play with it
12345678901234567890123456789012345678901234567890
3
4
5
6
7
8
9</textarea><BR>TEXTAREA ROWS=5 COLS=30 WRAP=SOFT</td>
</tr>
<tr>
<th>Select</th><td>&nbsp;<select NAME=select_code>
<option value=x>This is option X</option>
<option value=y>This is option Y</option>
<option value=z>This is option Z</option>
</select></td>
</tr>
<tr valign=middle>
<th>Radio Set</th><td>&nbsp;
<label><input TYPE="radio" Name="radio" Value="r1"> Option 1</label>&nbsp;
<label><input TYPE="radio" Name="radio" Value="r2" checked> Option 2</label>&nbsp;
</td>
</tr>
<tr valign=middle>
<th>Check Box</th><td>&nbsp;
<LABEL><input TYPE="checkbox" Name="check1" Value="1" checked> Check 1</label>&nbsp; 
<label><input TYPE="checkbox" Name="check2" Value="2"> Check 2</label>&nbsp;
</td>
</tr>
<tr>
<td colspan=2 align=centre><input TYPE="submit" name="submit" value="do nothing much"></td>
</tr>
</table>
</form>

<p>According to the style, the text input field should be styled as:<BR>
 &nbsp; &nbsp; INPUT {font: bold x-large tahoma, sans-serif; background: #f4f0dc; }<BR>
the text area should be styled identically as:<BR>
 &nbsp; &nbsp; TEXTAREA {font: bold x-large tahoma, sans-serif; background: #f4f0dc; }<BR>
and the SELECT should be styled differently as:<BR>
 &nbsp; &nbsp; TEXTAREA {font: xx-small tahoma, sans-serif; background: #f4f0dc; }<BR>
but bits of those (notably typeface) don't seem to be being applied correctly.</p>

<p>Initially the INPUT field is shown in the correct font, although the actual input cell
seems to be <i>sized</i> to fit a smaller font. Then when you click in it 
the font suddenly shrinks... to a fixed pitch one which now fits in the cell size.</p>

<p>I thought that the font shrinkage might actually more related to a TTF vs Type 1 font switch
and the metrics changing, but it isn't - exactly the same problem occurs with the Windows
builds.</p>

<p>The TEXTAREA is simply not shown in the correct typeface to start with.  
The TEXTAREA also has &quot;ROWS=</p>

<p>The SELECT is shown in the font for INPUT, and when the drop down is shown it is
in a different font entirely, which also seems unrelated to the style-specified font.</p>
</body> 
</html>


