<?php
  if ( $logged_on )
    $user_no = $session->user_no;
  else
    $user_no = 0;
  $query = "SELECT help_hit($user_no, '" . tidy($topic) . "');";
  awm_pgexec( $dbconn, $query, "help", true, 5 );

  $query = "SELECT * FROM help WHERE topic = '" . tidy($topic) . "' ";
  if ( isset($seq) && $action == "edit" ) $query .= "AND seq = " . intval($seq) . " ";
  $query .= "ORDER BY topic, seq;";
  $rid = awm_pgexec( $dbconn, $query, "help");
  if ( !$rid ) {
    echo "<h1>Help about $topic</h1>";
    echo "<h2 style=\"font-family: comic sans ms, verdana, fantasy; font-size: 200px; line-height: 80px; padding-top: 70px; font-weight: 700; text-align: center\">HELP!!!</h2>\n";
  }
  else {
    $rows = pg_NumRows($rid);
    if ( 0 == $rows || $action == "add" || ($action == "edit" && $rows == 1) ) {
      // No rows!  Maybe they want to add it?
      if ( "edit" == "$action" ) {
        $submitlabel = "Update Help";
        $help = pg_Fetch_Object($rid,0);
      }
      else if ( "add" == "$action" ) {
        $submitlabel = "Add New Help";
        echo "<h1>Help about $topic</h1>";
        if ( $rows > 0 ) {
          echo "<p>Existing help on this topic includes:</p>";
          echo "<ol type=\"1\">";
          for( $i = 0; $i < $rows; $i ++ ) {
            $help = pg_Fetch_Object($rid,$i);
            echo "<li><a href=\"/help.php?h=" . htmlspecialchars($help->topic) . "\">$help->title</a></li>\n";
          }
          echo "</ol><hr>";
        }
      }
      else {
        $submitlabel = "Add New Help";
        echo <<<EOHTML
<h1>Help about $topic</h1>
<h2> It seems this help hasn't been written yet :-(</h2>
<p>Could you please <u><a href="mailto:$admin_email?Subject=Help needed for $base_dns$REQUEST_URI&Body=Please add some help here:%0A%0A%20%20%20&lt;$base_dns$REQUEST_URI&gt;%0A%0AThanks,%0A$session->fullname">
e-mail $admin_email to request the help</a></u> to be written.</p>
<p>Thanks</p>

EOHTML;

      }
      if ( is_member_of('Admin','Support') ) {
        $seq = intval($seq);
        echo "<form method=post action=\"/help.php?h=" . htmlspecialchars($topic) . "&seq=$seq\" enctype=\"multipart/form-data\">\n";
        echo "<table>\n";
        echo "<tr><th>Topic</th><td>$topic</td></tr>\n";
        echo "<tr><th>Sequence</th><td><input type=text size=5 value=\"$help->seq\" name=\"new[seq]\"></td></tr>\n";
        echo "<tr><th>Title</th><td><input type=text size=50 value=\"" . htmlspecialchars($help->title) . "\" name=\"new[title]\"></td></tr>\n";
        echo "<tr><td colspan=2><textarea cols=70 rows=30 name=\"new[content]\">" . htmlspecialchars($help->content) . "</textarea></td></tr>\n";
        echo "<tr><td colspan=2 align=center><input type=submit size=50 value=\"$submitlabel\" name=submit></td></tr>\n";
        echo "</table>\n";
        echo "</form>\n";
      }
    }
    else if ( 1 == $rows ) {
      // Only a single result, so display it
      $help = pg_Fetch_Object($rid,0);
      echo link_writeups("<h1>$help->title</h1>\n");
      echo link_writeups("$help->content\n");
      if ( is_member_of('Admin','Support') ) {
        echo "<p><br><p><br><a href=\"/help.php?action=edit&h=" . htmlspecialchars($help->topic) . "&seq=$help->seq\">edit this help text</a>\n";
        echo " &nbsp;| &nbsp;<a href=\"/help.php?action=add&h=" . htmlspecialchars($help->topic) . "\">add new help text</a>\n";
      }
    }
    else {
      // Many results so display a table of contents
      if ( isset($show_all) )
        echo "<h1>Detailed Help</h1>\n";
      else
        echo "<h1>Select Your Help Topic</h1>\n";
      echo "<ol type=\"1\">";
      for( $i = 0; $i < $rows; $i ++ ) {
        $help = pg_Fetch_Object($rid,$i);
        if ( (isset($seq) && $help->seq == $seq) || isset($show_all) ) {
          echo link_writeups("<li><b><big>$help->title</big></b></li>\n");
          echo link_writeups("$help->content\n");
          if ( is_member_of('Admin','Support') ) {
            echo "<p align=right><a href=\"/help.php?action=edit&h=" . htmlspecialchars($help->topic) . "&seq=$help->seq\">edit this help text</a></p>\n";
          }
        }
        else
          echo link_writeups("<li><a href=\"/help.php?h=" . htmlspecialchars($help->topic) . "&seq=$help->seq\">$help->title</a></li>\n");
      }
      echo "</ol>";
      if ( is_member_of('Admin','Support') ) {
        echo "<p align=right><a href=\"/help.php?action=add&h=" . htmlspecialchars($topic) . "\">add new help text</a>\n";
      }
      if ( !isset($show_all) )
        echo "<p align=right><a href=\"$REQUEST_URI&show_all=1\">show all</a>\n";
    }
  }
?>