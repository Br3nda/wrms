<?php
  if ( isset($style) && "$style" != "stripped" ) {
    echo "</td></tr></table>\n";
    if ( $left_panel ) {
      echo "</td>\n";

      if ( $right_panel ) {
        echo "<td width=\"10%\" bgcolor=\"$colors[bg1]\" valign=top>\n";
        include("sidebarright.php");
        echo "\n</td>\n";
      }

      echo "</tr></table>\n";
    }
    if ( function_exists("local_page_footer") ) {
      local_page_footer();
    }
    else {
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" height="16" background="/<?php echo $images; ?>/WRMSbottomTile.gif">
  <tr>
    <td width="41%" height="10" valign="top"><img src="/<?php echo $images; ?>/WRMSbottom.gif" width="473" height="16">
    </td>
    <td width="37%" height="10">&nbsp;</td>
    <td width="22%" align="right" height="10" valign="top"><img src="/<?php echo $images; ?>/WRMSbottom1.gif" width="155" height="16"></td>
  </tr>
</table>
<?php
    }
  }
if ( is_object ( $settings ) && $settings->is_modified() ) {

  if ( ! is_numeric( $settings->get('counter')) )
    $settings->set('counter', 0 );
  else
    $settings->set('counter', $settings->get('counter') + 1 );

  $query = "UPDATE session SET session_config='" . $settings->to_save() . "' WHERE session_id=$session->session_id; ";
  if ( $session->user_no > 0 )
    $query .= "UPDATE usr SET config_data='" . $settings->to_save() . "' WHERE user_no=$session->user_no; ";

  $result = awm_pgexec( $dbconn, $query );
}


  error_reporting(7);
  if ( $debuglevel > 0 ) {
    $total_query_time = sprintf( "%3.06lf", $total_query_time );
    error_log( "$sysabbr total_query_ TQ: $total_query_time URI: $REQUEST_URI", 0);
    $total_time = sprintf( "%3.06lf", duration( $begin_processing, microtime() ));
    error_log( "$sysabbr process_time TT: $total_time      $HTTP_USER_AGENT $HTTP_REFERER  ", 0);
  }

?>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<script language="JavaScript" src="js/overlib.js"></script>
</body>
</html>
