<?php
if ( $logged_on ) {
  $sql = "SELECT * FROM saved_queries WHERE user_no=$session->user_no AND lower(query_name)='home';";
  $qry = new PgQuery( $sql );
  if ( $qry->Exec("indexsupport") && $qry->rows > 0 ) {

    // Can't just let anyone type in a where clause on the command line!
    if ( ! is_member_of('Admin' ) ) {
      $where_clause = "";
    }

    // Internal column names (some have 'nice' alternatives defined in header_row() )
    // The order of these defines the ordering when columns are chosen
    $available_columns = array(
            "request_id" => "WR&nbsp;#",
            "lby_fullname" => "Created By",
            "lfull" => "Request For",
            "request_on" => "Request On",
            "lbrief" => "Description",
            "request_type_desc" => "Type",
            "request_tags" => "Tags",
            "status_desc" => "Status",
            "system_code" => "System Code",
            "system_desc" => "System Name",
            "request.last_activity" => "Last Chng",
            "urgency" => "Urgency",
            "importance" => "Importance",
            "active" => "Active",
    );

    /**
    * The hours column is not visible to clients.
    */
    if ( $session->AllowedTo("Support") || $session->AllowedTo("Admin") ) {
      $available_columns["request_hours"] = "Hours";
    }

    $saved_qry_row = $qry->Fetch();
    $search_query = $saved_qry_row->query_sql ;
    // $style = 'stripped';

    $query_params = unserialize($saved_qry_row->query_params);
    $columns = $query_params["columns"];
    if ( !isset($columns) || !is_array($columns) ) {
      if ( $format == "edit" )
        $columns = array("request_id","lfull","request_on","lbrief","status_desc","active","request_type_desc","request.last_activity");
      else
        $columns = array("request_id","lfull","request_on","lbrief","status_desc","request_type_desc","request.last_activity");
    }


    // If the maxresults they saved was non-default, use that, otherwise we
    // increase the default anyway, because saved queries are more carefully
    // crafted, and less likely to list the whole database
    $mr = 1000;
    if ( (!isset($maxresults) || intval($maxresults) == 0 || $maxresults == 100)
           && intval($saved_qry_row->maxresults) != 100 && intval($saved_qry_row->maxresults) != 100 )
      $mr = $saved_qry_row->maxresults;
    $maxresults = $mr;
    if ( $saved_qry_row->rlsort ) {
      $rlsort = $saved_qry_row->rlsort;
      $rlseq = $saved_qry_row->rlseq;
    }
    else {
      // Enforce some sanity
      $rlsort = (isset($_GET['rlsort']) ? $_GET['rlsort'] : 'last_activity');
      $rlseq = (isset($_GET['rlseq']) && $_GET['rlseq'] == 'ASC' ? 'ASC' : 'DESC');
    }

    if ( isset($flipped_columns[$rlsort]) ) {
      // We can only sort by a column if it is present in the target list!
      $search_query .= " ORDER BY $rlsort $rlseq ";
    }

    if ( !isset($maxresults) || intval($maxresults) == 0 ) $maxresults = 200;
    $search_query .= " LIMIT $maxresults ";

    include_once("search_listing_functions.php");
    include_once("search_list_results.php");

  }
  elseif ( is_member_of('Admin','Support') ) {
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

<p>If you have forgotten your password, you can <a href="/temppass.php">request a temporary one</a>.</p>

<?php }

if ( ! $logged_on ) {
  $domains = array("hotmail.com", "yahoo.com", "bigpond.com.au", "xtra.co.nz", "debiana.net", "rugbylive.com", "hapua.com", "catalyst.net.nz", "mcmillan.net.nz", "cat-it.co.nz", "gmail.com");
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