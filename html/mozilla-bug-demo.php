<?php
  include("inc/always.php");
  include("inc/options.php");

  $title = "Demonstrate Mozilla Bug(s) with form styling";
  include("inc/starthead.php");

  echo "<style type=\"text/css\"><!--
p		{font: small tahoma, sans-serif; }
th		{font: bold x-small tahoma, sans-serif; color: white; text-align: right; background: $colors[8]; }
td		{font: x-small tahoma, sans-serif; }
input		{font: bold x-large tahoma, sans-serif; }
textarea	{font: bold x-large tahoma, sans-serif; }
select		{font: xx-small tahoma, sans-serif; }
--></style>";

  include("inc/bodydef.php");

?>
<form action=mozilla-bug-demo.php method=post>
<table>
<tr valign=middle>
<th>Text Input</th><td>&nbsp;<input TYPE="Text" Size="10" Name="search_for" Value="Some Text"></td>
</tr>
<tr>
<th>Textarea</th><td>&nbsp;<textarea NAME=system_code wrap rows=5 cols=30>Text in a text area before we play with it</textarea></td>
</tr>
<tr>
<th>Select</th><td>&nbsp;<select NAME=select_code>
<option value=x>This is option X</option>
<option value=y>This is option Y</option>
<option value=z>This is option Z</option>
</select></td>
</tr>
<tr>
<td colspan=2 align=centre><input TYPE="submit" name="submit" value="do nothing much"></td>
</tr>
</table>
</form>

<p>According to the style, the text input field should be styled as:<BR>
 &nbsp; &nbsp; INPUT {font: bold x-large tahoma, sans-serif; }<BR>
the text area should be styled identically as:<BR>
 &nbsp; &nbsp; TEXTAREA {font: bold x-large tahoma, sans-serif; }<BR>
and the SELECT should be styled differently as:<BR>
 &nbsp; &nbsp; TEXTAREA {font: xx-small tahoma, sans-serif; }<BR>
but bits of those (notably typeface) don't seem to be being applied correctly.</p>

<p>Initially the INPUT field is shown in the correct font, although the actual input cell
seems to be <i>sized</i> to fit a smaller font. Then when you click in it 
the font suddenly shrinks... to a fixed pitch one which now fits in the cell size.</p>

<p>I guess that the font shrinkage is actually more related to a TTF vs Type 1 font switch
and the metrics changing.</p>

<p>The TEXTAREA is simply not shown in the correct typeface to start with.</p>

<p>The SELECT is shown in the font for INPUT, and when the drop down is shown it is
in a different font entirely, which also seems unrelated to the style-specified font.</p>
</body> 
</html>


