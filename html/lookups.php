<?php
  include("always.php");
  include("options.php");

  if ( $logged_on && "$action" <> "" ) {
    include("lookwrite.php");
  }

  $title = "$system_name - " . ucfirst("$table") . ", " . ucfirst("$field");
  include("headers.php");
  include("lookhead.php");

  if ( "$because" <> "" ) echo "$because";

  if ( $logged_on && "$error_loc$error_msg" == "" ) {
    $look_href = "$SCRIPT_NAME?table=$table&field=$field&stext=$stext";
    include("looksearch.php");
    include("looklist.php");
  }
?>
<h4>Hints</h4>
<p class=helptext>Each table which is presented as a drop-down list for the user should
have one code which has the lowest sequence, a blank &quot;code&quot; value and a description
resembling &quot;--- not selected ---&quot; to allow the user not entering a value.</p>
<p class=helptext>If possible keep the &quot;code&quot; value as short as possible and avoid
spaces. It will usually be interpreted by computers rather than people, but it will be sent
back and forth several times for each form submitted.</p>
<?php
  include("footers.php");
?>
