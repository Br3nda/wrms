<?php
  include("inc/always.php");
  include("inc/options.php");

  if ( "$submit" == "Add Request" || "$submit" == "Update Request" ) {
    include("inc/request-valid.php");
    if ( "$because" == "" ) include("inc/request-action.php");
  }
  else
    include("inc/getrequest.php");

  $title = "$system_name - Maintain Request";
  include("inc/starthead.php");
  include("inc/formstyle.php");
?>
</head>
<body BGCOLOR="<?php echo $colors[0]; ?>" LEFTMARGIN=0 TOPMARGIN=0 MARGINHEIGHT=0 MARGINWIDTH=0>
<?php
  include("inc/menuhead.php");

  if ( "$submit" == "Add Request" || "$submit" == "Update Request" ) {
    echo "$because";
    exit;
  }

  include("inc/request-form.php");
?>

</body> 
</html>


