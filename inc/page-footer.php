<?php
function send_footers() {
  global $settings, $c, $session, $theme, $dbconn, $total_query_time, $debuglevel;
  global $REQUEST_URI, $HTTP_USER_AGENT, $HTTP_REFERER, $PHP_SELF;

  $theme->EndContentArea();
  if ( $theme->panel_right )  $theme->RightPanel();
  $theme->EndPanels();
  if ( $theme->panel_bottom ) $theme->PageFooter();

  echo <<<CLOSEHTML
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<script language="JavaScript" src="js/overlib.js"></script>
</body>
</html>
CLOSEHTML;

  if ( is_object ( $settings ) && $settings->is_modified() ) {

    if ( ! is_numeric($settings->get('counter')) )
      $settings->set('counter', 0 );
    else
      $settings->set('counter', $settings->get('counter') + 1 );

    $config_data_string = qpg($settings->to_save());
    $query = "UPDATE session SET session_config=$config_data_string ";
    $query .= "WHERE session_id=$session->session_id ";
    $query .= "AND session_config != $config_data_string; ";
    if ( $session->user_no > 0 ) {
      $query .= "UPDATE usr SET config_data=$config_data_string WHERE user_no=$session->user_no ";
      $query .= "AND config_data != $config_data_string; ";
    }

    $result = awm_pgexec( $dbconn, $query );
  }

  error_reporting(7);
  if ( $debuglevel > 0 ) {
    $total_query_time = sprintf( "%3.06lf", $total_query_time );
    error_log( $c->sysabbr." total_query_ TQ: $total_query_time URI: $REQUEST_URI", 0);
    $total_time = sprintf( "%3.06lf", duration( $c->started, microtime() ));
    error_log( $c->sysabbr." process_time TT: $total_time      Agent: $HTTP_USER_AGENT Referrer: $HTTP_REFERER  ", 0);
    error_log( "=============================================== Endof $PHP_SELF" );
  }

}
send_footers();
?>