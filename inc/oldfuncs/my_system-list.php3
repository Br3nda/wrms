<?php
  $query = "SELECT DISTINCT ON system_code * FROM perorg_system, work_system ";
  $query .= "WHERE perorg_system.perorg_id = '$usr->org_id' ";
  $query .= "AND work_system.system_code = perorg_system.system_code";
  $rid = pg_Exec( $dbid, $query);
  $num_systems = pg_NumRows( $rid );
  if ( ! $num_systems ) {?>
<TABLE WIDTH=80% BGCOLOR=#e0f7f7 CELLPADDING=10 BORDER=1 ALIGN=CENTER><TR><TD>
<P><B>Sorry:</B> No systems were found for the organisation which you work for!</P>
<P>This either means that:<BR>
&nbsp;&nbsp;&nbsp;-  the work request system doesn't know who you work for;<BR>
&nbsp;&nbsp;&nbsp;-  nobody in your organisation is listed as a user, manager or maintainer of any system.</P>
<P>Please arrange for an administrator to set up the Organisations, Users and Systems
 tables correctly before submitting any requests.</P>
</TD></TR></TABLE>
<?php
    if ( isset($usr) && $usr->access_level >= 90000 ) {
      echo "<P>Since you appear to be an administrator, you may want to <A HREF=admin-frame.php3>click here</A> to enter the administration menus. ";
      echo " You may need to create organisations, systems and users.  You <B><I>will</I></B> need to maintain users so that they are appropriately associated as users, managers or support staff for the various systems.</P>";
    }
  }
  $sys_list = "";
  $last_system = "APMS";
  for ( $i=0; $i < $num_systems; $i++ ) {
    $sys_type = pg_Fetch_Object( $rid, $i );
    $sys_list .= "<OPTION VALUE=\"$sys_type->system_code\">$sys_type->system_desc";
    $last_system = $sys_type->system_code;
  }
?>
