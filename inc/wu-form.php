<?php
  if ( $logged_on )
    $user_no = $session->user_no;
  else {
    $user_no = 0;
    echo "<h1>You need to log on to access this function</h1>";
    return;
  }
  if ( "$nodename" == "" ) {
    echo "<h1>Default page goes here</h1>";
    return;
  }

  $query = "SELECT * FROM infonode, wu WHERE LOWER(nodename) = '" . strtolower(tidy($nodename)) . "' ";
  if ( isset($by) ) $query .= "AND wu_by = " . intval($by) . " ";
  $query .= "AND infonode.node_id = wu.node_id ";
  $query .= "ORDER BY wu.node_id, wu_on;";
  $rid = awm_pgexec( $dbconn, $query, "wu");
  if ( !$rid ) {
    echo "<h1>Writeup about $nodename</h1>";
    echo "<h2 style=\"font-family: comic sans ms, verdana, fantasy; font-size: 200px; line-height: 80px; padding-top: 70px; font-weight: 700; text-align: center\">wu!!!</h2>\n";
  }
  else {
    $rows = pg_NumRows($rid);
    if ( 0 == $rows || $action == "add" || ($action == "edit" && $rows == 1) ) {
      // No rows!  Maybe they want to add it?
      if ( "edit" == "$action" ) {
        $submitlabel = "Update wu";
        $wu = pg_Fetch_Object($rid,0);
      }
      else if ( "add" == "$action" ) {
        $submitlabel = "Add New wu";
        echo "<h1>wu about $nodename</h1>";
        if ( $rows > 0 ) {
          echo "<p>Existing wu on this nodename includes:</p>";
          echo "<ol type=\"1\">";
          for( $i = 0; $i < $rows; $i ++ ) {
            $wu = pg_Fetch_Object($rid,$i);
            echo "<li><a href=\"/wu.php?h=" . htmlspecialchars($wu->nodename) . "\">$wu->title</a></li>\n";
          }
          echo "</ol><hr>";
        }
      }
      else {
        $submitlabel = "Add New wu";
        echo "<h1>Writeup about $nodename</h1>";
        echo "<h2> It seems nothing has been written on this subject yet :-)</h2>\n";
      }
      if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] ) {
        $seq = intval($seq);
        echo "<form method=post action=\"/wu.php?h=" . htmlspecialchars($nodename) . "&seq=$seq\" enctype=\"multipart/form-data\">\n";
        echo "<table>\n";
        echo "<tr><th>Name</th><td>$nodename</td></tr>\n";
        echo "<tr><th>Content</th><td><textarea cols=70 rows=30 name=\"new[content]\">" . htmlspecialchars($wu->content) . "</textarea></td></tr>\n";
        echo "<tr><td colspan=2 align=center><input type=submit size=50 value=\"$submitlabel\" name=submit></td></tr>\n";
        echo "</table>\n";
        echo "</form>\n";
      }
    }
    else if ( 1 == $rows ) {
      // Only a single result, so display it
      $wu = pg_Fetch_Object($rid,0);
      echo "<h1>$wu->title</h1>\n";
      echo "$wu->content\n";
      if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] ) {
        echo "<p><br><p><br><a href=\"/wu.php?action=edit&h=" . htmlspecialchars($wu->nodename) . "&by=$wu->wu_by\">edit this writeup</a>\n";
        echo " &nbsp;| &nbsp;<a href=\"/wu.php?action=add&h=" . htmlspecialchars($wu->nodename) . "\">add new node</a>\n";
      }
    }
    else {
      // Many results so display a table of contents
      if ( isset($show_all) )
        echo "<h1>Detailed wu</h1>\n";
      else
        echo "<h1>Select Your wu nodename</h1>\n";
      echo "<ol type=\"1\">";
      for( $i = 0; $i < $rows; $i ++ ) {
        $wu = pg_Fetch_Object($rid,$i);
        if ( (isset($seq) && $wu->seq == $seq) || isset($show_all) ) {
          echo "<li><b><big>$wu->title</big></b></li>\n";
          echo "$wu->content\n";
          if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] ) {
            echo "<p align=right><a href=\"/wu.php?action=edit&h=" . htmlspecialchars($wu->nodename) . "&seq=$wu->seq\">edit this wu text</a></p>\n";
//            echo " &nbsp;| &nbsp;<a href=\"/wu.php?action=add&h=" . htmlspecialchars($wu->nodename) . "\">add new wu text</a>\n";
          }
        }
        else
          echo "<li><a href=\"/wu.php?h=" . htmlspecialchars($wu->nodename) . "&seq=$wu->seq\">$wu->title</a></li>\n";
      }
      echo "</ol>";
      if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] ) {
        echo "<p align=right><a href=\"/wu.php?action=add&h=" . htmlspecialchars($nodename) . "\">add new wu text</a>\n";
      }
      if ( !isset($show_all) )
        echo "<p align=right><a href=\"$REQUEST_URI&show_all=1\">show all</a>\n";
    }
  }
?>