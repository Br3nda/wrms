<TABLE CELLSPACING="1" CELLPADDING="7">
<TR>
	<TD BGCOLOR="<?php echo $colors[1]; ?>">
		<TABLE width="100%" border="0" cellspacing="0" cellpadding="0" BGCOLOR="<?php echo $colors[0]; ?>">
	<form action="<?php echo "$REQUEST_URI"; ?>" Method="post">
		<input TYPE="Hidden" Name="M" Value="LC">
		<tr><td colspan=3><SPAN STYLE="font-size: x-small; font-weight: 700; color: <?php echo $colors[2]; ?>; ">Login to <?php echo "$system_name"; ?></span></td></tr>
		<tr><td> &nbsp; </td><td class=sml colspan=2><font size=1><b>User Name</b></font></td></tr>
		<tr><td class=sml> &nbsp; </td><td colspan=2><font size=2><input TYPE="TEXT" Name="E" SIZE="15"></font></td></tr>
		<tr><td class=sml> &nbsp; </td><td colspan=2 class=sml><font size=1><b>Password</b></font></td></tr>
		<tr valign=middle><td> &nbsp; </td><td width=30%><font size=2><input TYPE="password" Name="L" SIZE="8"></td>
		<td valign=middle width=70%>&nbsp;<input type="image" src="<?php $base_url; ?>/images/in-go.gif" alt="go" WIDTH="44" HEIGHT="26" BORDER=0 name="submit"></font></td></tr>
		<tr valign=middle><td> &nbsp; </td><td colspan=2 align=right><SPAN STYLE="font-size: x-small; font-weight: 700; color: <?php echo $colors[2]; ?>; ">Remember my login details:&nbsp;</span><font size=2><input type=checkbox name=remember value=1></font>&nbsp;</td></tr>
	</form>
		</table>
</TR>
<TR>
	<TD bgcolor="<?php echo $colors[1]; ?>" valign=top align=center class=sml>
		<font size=-2 color=<?php echo $colors[3]; ?>>Application development by...</font><br>
		<A href="http://www.catalyst.net.nz" target="_new"><IMG SRC="<?php echo $base_url; ?>/images/cat-it-sml.gif" Border=0 alt="Catalyst.Net Limited" width=130 height=54></A><br>&nbsp;<br>
	</TD>
</TR>
<TR>
	<TD bgcolor="<?php echo $colors[1]; ?>" valign=top align=center>
		&nbsp;
	</TD>
</TR>
</table>

