<?php
  if ( $logged_on )
    $user_no = $session->user_no;
  else {
    $user_no = 0;
    echo "<h1>You need to log on to access this function</h1>";
    return;
  }
  if ( "$nodename" == "" && !isset($node_id) ) {
    echo "<h1>Default page goes here</h1>";
    return;
  }

  $query = "SELECT *, to_char(wu_on, 'YYYY-MM-DD at HH24:MI') AS nice_date ";
  $query .= "FROM infonode, wu, usr WHERE ";
  if ( isset($node_id) && $node_id > 0 )
    $query .= "infonode.node_id = $node_id ";
  else
    $query .= "LOWER(nodename) = '" . strtolower(tidy($nodename)) . "' ";
  if ( isset($by) ) $query .= "AND wu_by = " . intval($by) . " ";
  $query .= "AND infonode.node_id = wu.node_id ";
  $query .= "AND usr.user_no = wu.wu_by ";
  $query .= "ORDER BY wu.node_id, wu_on;";
  $rid = awm_pgexec( $dbconn, $query, "wu", false, 7);
  if ( !$rid ) {
    echo "<h1>Editorial about $nodename</h1>";
    echo "<h2 style=\"font-family: comic sans ms, verdana, fantasy; font-size: 200px; line-height: 80px; padding-top: 70px; font-weight: 700; text-align: center\">wu!!!</h2>\n";
  }
  else {
    $rows = pg_NumRows($rid);
    if ( $rows > 0 ) {
      // Read first row to get the infonode.nodename
      $wu = pg_Fetch_Object($rid,0);
      $nodename = $wu->nodename;
      $current_node = $wu->node_id;
    }
    // Show the title for the infonode
    echo "<h1>$nodename</h1>\n";
    unset($my_wu);
    if ( $rows > 0 ) {
      if ( $can_vote )
        echo "<form method=post action=\"/wu.php?node_id=$current_node\">";
      // Show the existing editorials.
      for( $i = 0; $i < $rows; $i ++ ) {
        $wu = pg_Fetch_Object($rid,$i);
        echo "<table width=100%><tr>\n";
        if ( $can_vote ) {
          $btn = "<label>%s<input type=radio name=\"vote[$wu->wu_by]\" value=\"%s\">%s</label> &nbsp; ";
          echo "<td align=center>";
          if ( $can_cool ) printf( $btn, "Cool", "C", "" );
          if ( $can_vote ) {
            printf( $btn, "+", "+", "" );
            printf( $btn, "", "-", "-" );
          }
          if ( $can_can ) printf( $btn, "", "K", "Kill" );
          echo "</td>\n";
        }
        echo "<td align=right>" . link_writeups("by [$wu->username]") . " on $wu->nice_date</td></tr></table>\n";
        echo link_writeups("$wu->content\n");
        echo "<hr>\n";
        if ( $session->user_no == $wu->wu_by ) $my_wu = htmlspecialchars($wu->content);
      }
      if ( $can_vote )
        echo "<input type=submit value=\"Vote\" name=submit>\n</form>";
      if ( $can_edit )
        echo "<p><br>&nbsp;<br>&nbsp;<br>";
    }
    else {
      $words = split( "[^a-zA-Z0-9]+", strtolower( $nodename), 4);
      $query = "";
      while( list($k, $v) = each($words) ) {
        if ( $query != "" ) $query .= "UNION ";
        $query .= "SELECT * FROM infonode WHERE LOWER(nodename) ~ '$v' ";
      }
      $query .= "LIMIT 100;";
      echo "<p>I can't find an exact match for \"$nodename\" but perhaps one of these is the answer you need:";
      $rid = awm_pgexec( $dbconn, $query, "wu-form");
      if ( $rid && pg_NumRows($rid) > 0 ) {
        echo "<ul>";
        for( $i = 0; $i < pg_NumRows($rid); $i ++ ) {
          $node = pg_Fetch_Object($rid,$i);
          echo "<li><a href=\"/wu.php?node_id=$node->node_id&last=$last\">$node->nodename</a></li>\n";
        }
        echo "</ul></p>";
        if ( $can_edit )
          echo "<p><br>&nbsp;<br><p>Alternatively, if none of these fits the bill, enter your new editorial directly in the space below:";
      }
    }

    if ( $can_edit ) {
      if ( isset($my_wu) ) {
        $submitlabel = "Update Editorial";
      }
      else {
        $submitlabel = "Add New Editorial";
      }
      $seq = intval($seq);
      echo "<form method=post action=\"/wu.php?wu=" . htmlspecialchars($nodename);
      if ( isset($my_wu) ) echo "&node_id=$wu->node_id";
      echo "\" enctype=\"multipart/form-data\">\n";
      echo "<textarea cols=70 rows=30 name=\"new[content]\">$my_wu</textarea><br clear=all>\n";
      echo "<input type=submit value=\"$submitlabel\" name=submit>\n";
      echo "</form>\n";
    }
  }
?>