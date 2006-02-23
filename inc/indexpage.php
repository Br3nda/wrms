<?php
if ( $logged_on ) {
  if ( is_member_of('Admin','Support') ) {
    include("indexsupport.php");
  }
  elseif ( $session->AllowedTo('Contractor') ) {
    include("indexextsupport.php");
  }
  else {
    include("indexclients.php");
  }
}
else if ( function_exists("local_index_not_logged_in") ) {
  local_index_not_logged_in();
}
else { ?>

<H4>For access to the <?php echo $system_name; ?> you should log on with
the username and password that have been issued to you.</H4>

<h4>If you would like to request access, please e-mail <?php echo $admin_email; ?>.</h4>

<?php }

if ( ! $logged_on ) {
  $domains = array("hotmail.com", "yahoo.com", "bigpond.com.au", "xtra.co.nz", "debiana.net", "rugbylive.com", "hapua.com", "catalyst.net.nz", "mcmillan.net.nz", "cat-it.co.nz");
  $norm_letters = "abcdefghilmnoprstuvwy-_.123";
  $norm_length = strlen($norm_letters) - 1;
  $odd_letters = "zxjqk";
  echo "<!-- \n";
  for ( $i=0; $i < 7; $i++ ) {
    $address = substr( $norm_letters, rand(0,16),1);
    $ltrcount = rand(3,5);
    for( $j=0; $j < $ltrcount; $j++ ) {
      $address .= substr( $norm_letters, rand(0,$norm_length),1);
      if ( $j==0 || $j==3 )
        $address .= substr( $odd_letters, rand(0,strlen($odd_letters)-1),1);
    }
    $address .= substr( $norm_letters, rand(0,16),1);
    $address .= "@" . $domains[rand(0,count($domains)-1)];
    print "$address\n";
  }
  echo "-->\n";

  if ( $session->login_failed ) {
    echo <<<EOHTML
<hr>
<h3>Forgotten Your Password?</h3>
<form action="$action_target" method="post">
  <table>
    <tr>
      <th class="prompt">User Name:</th>
      <td class="entry"><input class="text" type="text" name="username" size="12" /></td>
    </tr>
    <tr>
      <th class="prompt">or E-Mail:</th>
      <td class="entry"><input class="text" type="text" name="email_address" size="50" /></td>
    </tr>
    <tr>
      <th class="prompt">&nbsp;</th>
      <td class="entry">
        <input class="submit" type="submit" value="Send me a temporary password" alt="Enter a username, or e-mail address, and click here." name="lostpass" />
      </td>
    </tr>
  </table>
  <p>Note: If you have multiple accounts with the same e-mail address, they will <em>all</em>
     be assigned a new temporary password, but only the one(s) that you use that temporary password
     on will have the existing password invalidated.</p>
  <p>Any temporary password will only be valid for 24 hours.</p>
</form>
EOHTML;
  }
}

?>