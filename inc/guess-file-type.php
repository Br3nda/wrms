<?php
function guess_file_type( $name, $actual_file ) {

  global $session;

  $type = "";
  $extension = strtolower( preg_replace( "/^.*\./", "", $name ) );

  // Some common attachment types are hard-coded so we don't hit the db
  switch ( $extension ) {
    case "xml":    case "html":   case "pdf":
    case "jpeg":   case "png":    case "bmp":
    case "gif":    case "txt":    case "mpp":
    case "zip":    case "jar":    case "tgz":
    case "ico":    case "svg":    case "planner":
      $type = strtoupper($extension);
      break;

    case "doc":       $type = "MSWORD";    break;
    case "xls":       $type = "MSEXCEL";   break;
    case "sxw":       $type = "OOWRITER";  break;
    case "sxc":       $type = "OOCALC";    break;
    case "jpg":       $type = "JPEG";      break;
    case "htm":       $type = "HTML";      break;

    default:
      $sql = "SELECT type_code FROM attachment_type WHERE ? ~* pattern ORDER BY seq; ";
      $qry = new PgQuery( $sql, $name );
      if ( $qry->Exec("guess-file-type") && $qry->rows > 0 ) {
        $row = $qry->Fetch();
        $type = $row->type_code;
      }
      break;
  }

  $session->Log( "guess_file_type: DBG: File '$name' with extension '$extension' is type '$type'");

  return $type;
}

function guess_mime_type( $type_code ) {

  $sql = "SELECT mime_type FROM attachment_type WHERE type_code = ?; ";
  $qry = new PgQuery( $sql, $type_code );
  if ( $qry->Exec("guess-mime-type") && $qry->rows > 0 ) {
    $row = $qry->Fetch();
    return $row->mime_type;
  }

  return "application/octet-stream";
}
?>