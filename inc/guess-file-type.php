<?php
function guess_file_type( $name, $actual_file ) {

  $type = "";
  $extension = strtolower( preg_replace( "/^.*\./", "", $name ) );

  // Some common attachment types are hard-coded so we don't hit the db
  switch ( $extension ) {
    case "doc":
    case "xls":
    case "xml":
    case "html":
    case "sxw":
    case "sxc":
    case "pdf":
    case "jpeg":
    case "png":
    case "gif":
    case "txt":
    case "mpp":
    case "zip":
    case "jar":
    case "tgz":
      $type = $extension;
      break;

    case "jpg":       $type = "jpeg";      break;
    case "htm":       $type = "html";      break;

    default:
      $sql = "SELECT type_code FROM attachment_type WHERE ? ~* pattern ORDER BY seq; ";
      $qry = new PgQuery( $sql, $name );
      if ( $qry->Exec("guess-file-type") && $qry->rows > 0 ) {
        $row = $qry->Fetch();
        $type = $row->type_code;
      }
      break;
  }

  error_log( "$GLOBALS[sysabbr] guess_file_type: DBG: File '$name' with extension '$extension' is type '$type'");

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