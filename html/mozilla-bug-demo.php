<?php
  include("inc/always.php");

  $title = "Demonstrate Mozilla Bug(s) with form styling";
  include("inc/starthead.php");

  echo "<style type=\"text/css\"><!--
p		{font: small tahoma, sans-serif; }
ul		{font: small bold tahoma, sans-serif; }
li		{font: small tahoma, sans-serif; }
th		{font: bold x-small tahoma, sans-serif; color: white; text-align: right; background: $colors[8]; }
td		{font: x-small tahoma, sans-serif; }
input		{font: bold x-large tahoma, sans-serif; background: $colors[7]; }
textarea		{font: bold x-large tahoma, sans-serif; background: $colors[7]; }
select		{font: xx-small tahoma, sans-serif; background: $colors[7]; }
--></style>";

  include("inc/bodydef.php");

?>
<p>Update 14/5/2000 (Linux build 2000051210):<BR>No change since29/3/2000 but RodS advises: 
<i>"We get the width of "Ww" and divide by 2 to get an average char width instead of using the 
average char width of the font."</i>
 and marked <a href=http://bugzilla.mozilla.org/show_bug.cgi?id=33655>bug 33655</a> as INVALID.  I disagree.  Typing "WwWwWwWwWw" into the cell leaves
plenty left over.  Even typing WWWWWWWWWW into the cell leaves spare room, so the width
estimate is over inflated even further.  I believe that a better average character width estimate
would be "0123456789" / 10, or "024" / 3, or something based around the digits.
This approach would be a minor change and would give Mozilla much the same width characteristics
Netscape 4 has in this regard, which seem fine to me.  I can't see the reason for 
needing _all_ possible character strings to fit in a ten character cell, after all the cells can be scrolled
left and right to view longer strings.  Strings of W's are _rare_ in the
real world!  Perhaps "ETAeta" / 6 would be the best average :-)
<p>Just typing in strings of characters in various fonts offers up several suggestions which don't
create the _excessively_ long fields that the current Mozilla offers such as "Gg"/2 or just "w", or
maybe "Mm"/2.  These three options all create fields that are around 10-30% longer than what
Netscape 4 produces (I don't have a copy of IE to compare with unfortunately) and there seems
to be value in agreeing on the same ballpark in this area.  As a page designer I don't really care
how wide x chars is, I just change it until it fits the look I am after (I rarely set maxwidth in any
case) but differences between browsers are just a pain in the butt when they are this large.
<p>Even taking into account that the fonts used by Mozilla differ from those used by Netscape in
this area, the algorithm for input field width is arriving at a value which is around 170% of the
sizes used by Netscape 4.72 .
<ul>Anyway, the bugs:
<li>font-family for TEXTAREA is not being applied from the 
style <a href=http://bugzilla.mozilla.org/show_bug.cgi?id=28219>(see bug)</a></li>
<li>ROWS=Y COLS=X also seem to be being ignored (or 
misinterpreted in some fashion) for TEXTAREA  <a href=http://bugzilla.mozilla.org/show_bug.cgi?id=33654>(see bug)</a></li>
<li>SIZE=X doesn't seem to be being dealt with appropriately for INPUT TYPE=TEXT fields <a href=http://bugzilla.mozilla.org/show_bug.cgi?id=33655>(see bug)</a></li>
<li>Width is not being calculated correctly for the button either.  I haven't got around to filing a bug
for that one yet.  If you do, can you cc: me on it, thanks.</li>
</ul>


<form action=mozilla-bug-demo.php method=post>
<table>
<tr valign=middle>
<th>Text Input</th><td>INPUT TYPE=TEXT SIZE=<b>10</b><BR>
&nbsp;<input TYPE="Text" Size="10" Name="search_for" Value="Some Text01234567890"> (Linux 2000051210 shows 17.2 chars, Netscape 4.72 shows 10.6)<BR>
&nbsp;<input TYPE="Text" Size="10" Name="search_for" Value="0123456789012345678901234567890"> (Linux 2000051210 shows 16.9 chars, vs Netscape 4.72 shows 10.0)<BR>
&nbsp;<input TYPE="Text" Size="10" Name="search_for" Value="WwWwWwWwWw">(shows 12 chars, vs 6.8)<BR>
&nbsp;<input TYPE="Text" Size="10" Name="search_for" Value="MmMmMmMmMm">(shows 12.5 chars, vs 7.0)<BR>
&nbsp;<input TYPE="Text" Size="10" Name="search_for" Value="GgGgGgGgGg">(shows 16 chars, vs 9.0)<BR>
&nbsp;<input TYPE="Text" Size="10" Name="search_for" Value="WgWgWgWgWg">(shows 13 chars, vs 7.7)<BR>
&nbsp;<input TYPE="Text" Size="10" Name="search_for" Value="ETAetaETAetaETAeta">(shows 13 chars, vs 10.1)<BR>
</td>
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
 &nbsp; &nbsp; SELECT {font: xx-small tahoma, sans-serif; background: #f4f0dc; }<BR>
but that doesn't seem to be being applied correctly to TEXTAREA.</p>

<p>Exactly the same problem occurs with the Windows
builds.</p>

</body> 
</html>


