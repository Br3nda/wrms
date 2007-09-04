<?php
require_once("always.php");

$EngineName = str_replace('.xml','',$_SERVER['PATH_INFO']);
$EngineName = eregi_replace('[^a-z0-9]', '', $EngineName);

switch( $EngineName ) {
  case 'search':
    $url = <<<EOURL
<Url type="text/html" method="GET" template="$c->base_dns/wrsearch.php">
    <Param name="search_for" value="{searchTerms}"/>
  </Url>
EOURL;
    $short = sprintf( 'Search %s WRMS', $c->shortname );
    $full  = "Search for text within a Work Request";
    break;

  case 'request':
    $url = <<<EOURL
<Url type="text/html" method="GET" template="$c->base_dns/wr.php?request_id={searchTerms}" />
EOURL;
    $short = sprintf( 'Get %s W/R', $c->shortname );
    $full  = "Go to a Work Request by number";
    break;

  default:
    echo "<html><head><title>Error: Unknown Search '$EngineName'</title><body><h1>Error: Unknown Search '$EngineName'</h1></body></html>";
    exit;
}

header("Content-type: application/opensearchdescription+xml");
echo <<<EOXML
<?xml version="1.0" encoding="UTF-8"?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/" xmlns:moz="http://www.mozilla.org/2006/browser/search/">
  <ShortName>$short</ShortName>
  <Description>$full</Description>
  <InputEncoding>UTF-8</InputEncoding>
  <Image>$c->base_dns/favicon.ico</Image>
  $url
  <moz:SearchForm>$c->base_dns/wrsearch.php</moz:SearchForm>
</OpenSearchDescription>
EOXML;
