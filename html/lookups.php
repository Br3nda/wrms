<?php
  include("inc/always.php");
  include("inc/options.php");

  if ( $logged_on && "$action" <> "" ) {
    include("inc/lookwrite.php");
  }

  $title = "$system_name - " . ucfirst("$table") . ", " . ucfirst("$field");
  include("inc/headers.php");
  include("inc/lookhead.php");

  if ( "$because" <> "" ) echo "$because";

  if ( $logged_on && "$error_loc$error_msg" == "" ) {
    $look_href = "$SCRIPT_NAME?table=$table&field=$field&stext=$stext";
    include("inc/looksearch.php");
    include("inc/looklist.php");
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
  include("inc/footers.php");
?>
