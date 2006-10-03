<?php
function send_wusearch_block() {
global $theme;
  $theme->BlockOpen();
  $theme->BlockTitle("Search");
?>
<form action="/wu.php" method="GET">
<input type="hidden" name="last" value="0">
<input type="text" name="wu" value="" size="18">
<input type="submit" value="FIND NOW&raquo;" class="submit">
</form>
<?php
  echo "<img src=\"/images/clear.gif\" width=\"155\" height=\"50\" hspace=\"0\" vspace=\"2\" border=\"0\">\n";
  $theme->BlockClose();

}
send_wusearch_block();
?>
