<?php
  $query = "SELECT help_hit($session->user_no, '" . tidy($topic) . "');";
  awm_pgexec( $dbconn, $query, "help", true, 5 );
  $query = "SELECT * FROM help WHERE topic = '" . tidy($topic) . "' ";
  if ( isset($seq) ) $query .= "AND seq = " . intval($seq) . " ";
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
            echo "<li><a href=\"/form.php?form=help&topic=" . htmlspecialchars($help->topic) . "\">$help->title</a></li>\n";
          }
          echo "</ol><hr>";
        }
      }
      else {
        $submitlabel = "Add New Help";
        echo "<h1>Help about $topic</h1>";
        echo "<h2> It seems this help hasn't been written yet :-(</h2>\n";
      }
      echo "<form method=post action=\"/form.php?form=help&topic=" . htmlspecialchars($topic) . "\" enctype=\"multipart/form-data\">\n";
      echo "<table>\n";
      echo "<tr><th>Topic</th><td>$topic</td></tr>\n";
      echo "<tr><th>Sequence</th><td><input type=text size=5 value=\"$help->seq\" name=\"new[seq]\"></td></tr>\n";
      echo "<tr><th>Title</th><td><input type=text size=50 value=\"" . htmlspecialchars($help->title) . "\" name=\"new[title]\"></td></tr>\n";
      echo "<tr><th>Content</th><td><textarea cols=70 rows=30 name=\"new[content]\">" . htmlspecialchars($help->content) . "</textarea></td></tr>\n";
      echo "<tr><td colspan=2 align=center><input type=submit size=50 value=\"$submitlabel\" name=submit></td></tr>\n";
      echo "</table>\n";
      echo "</form>\n";
    }
    else if ( 1 == $rows ) {
      // Only a single result, so display it
      $help = pg_Fetch_Object($rid,0);
      echo "<h1>$help->title</h1>\n";
      echo "$help->content\n";
      if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] ) {
        echo "<p><br><p><br><a href=\"/form.php?form=help&action=edit&topic=" . htmlspecialchars($help->topic) . "&seq=$help->seq\">edit this help text</a>\n";
        echo " &nbsp;| &nbsp;<a href=\"/form.php?form=help&action=add&topic=" . htmlspecialchars($help->topic) . "\">add new help text</a>\n";
      }
    }
    else {
      // Many results so display a table of contents
      echo "<ol type=\"1\">";
      for( $i = 0; $i < $rows; $i ++ ) {
        $help = pg_Fetch_Object($rid,$i);
        echo "<li><a href=\"/form.php?form=help&topic=" . htmlspecialchars($help->topic) . "\">$help->title</a></li>\n";
      }
      echo "</ol>";
    }
  }
?>