<?php
  if ( "$style" != "clean" ) {
    echo "</td></tr></table>\n</td>\n";

    if ( $right_panel ) {
      echo "<td width=\"10%\" bgcolor=\"$colors[bg1]\" valign=top>\n";
      include("inc/sidebarright.php");
      echo "\n</td>\n";
    }

    echo "</tr></table>\n";
  }
//  phpinfo();
  echo "</body>\n</html>";

if ( is_object ( $settings ) ) {

/*
  if ( ! is_numeric( $settings->get('counter')) )
    $settings->set('counter', 0 );
  else
    $settings->set('counter', $settings->get('counter') + 1 );

  $query = "UPDATE session SET session_config='" . $settings->to_save() . "' WHERE session_id=$session->session_id; ";
  if ( $session->user_no > 0 )
    $query .= "UPDATE usr SET config_data='" . $settings->to_save() . "' WHERE user_no=$session->user_no; ";

  $result = awm_pgexec( $dbconn, $query );
  if ( !$result ) {
    error_log( "$sysabbr footers QF: $query", 0);
  }
*/


  error_reporting(7);
  if ( $debuglevel > 0 ) {
    $total_query_time = sprintf( "%3.06lf", $total_query_time );
    error_log( "$sysabbr total_query_ TQ: $total_query_time URI: $REQUEST_URI", 0);
    $total_time = sprintf( "%3.06lf", duration( $begin_processing, microtime() ));
    error_log( "$sysabbr process_time TT: $total_time      $HTTP_USER_AGENT $HTTP_REFERER  ", 0);
  }

}
?>
