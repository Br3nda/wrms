#!/usr/bin/perl 
#
#  This is a hack to retire requests which have been marked as 'testing'
#  for more than X days.
#

# A few semi-constants...
$retire_days = 35;
$base_url = "http://wrms.catalyst.net.nz";
$mailprog = "sendmail -t";
# $mailprog = "pretendmail";


use Pg;
$conn = Pg::connectdb("dbname=wrms");
die "Couldn\'t connect to database!" if ( $conn->status == PGRES_CONNECTION_BAD );


sub retire_request {
  local $i, $to, $status, $users, $query;
  $query = "SELECT fullname, email FROM request_interested, usr ";
  $query .= " WHERE request_interested.request_id = $request_id" ;
  $query .= " AND request_interested.user_no = usr.user_no";
  print "$query\n";
  $users = $conn->exec( $query );
  $status = $users->resultStatus;
  # print STDERR "$status \t$query\n";
  if ( $status >= PGRES_BAD_RESPONSE ) {
    print STDERR "ERROR: $query\n" . $conn->errorMessage . "\n";
  }
	$to = "";
  for ( $i=0; $i < $users->ntuples; $i++  ) {
	  $to .= "To: " . $users->getvalue($i,0) . " <" . $users->getvalue($i,1) . ">\n";
	}
	open ( MSG, "| " . $mailprog ) || die "Can't run sendmail";
	print MSG $to;
	print MSG "From: catalyst-wrms\@catalyst.net.nz\n";
	print MSG "Errors-To: wrmsadmin\@catalyst.net.nz\n";
	print MSG "Subject: WR #$request_id retired ($request_brief)\n\n";
	print MSG "WR #$request_id - $request_brief\n\n";
	print MSG "This work request, originally made by $request_by on $request_on, ";
	print MSG "has been automatically retired because it has been in testing ";
	print MSG "for more than $retire_days days.\n\n";
	print MSG "For more information (or if you want to change it back!) visit:\n";
	print MSG "      $base_url/request.php?request_id=$request_id\n";
	print MSG "or send an e-mail to someone at Catalyst.\n";
	print MSG "\n";
	close MSG;

  # Now actually retire the request...
	$query = "INSERT INTO request_status (request_id, status_on, status_by_id,";
	$query .= " status_by, status_code) VALUES( $request_id, 'now', 0, 'wrms', 'F');";
	$query .= "UPDATE request SET last_status='F', last_activity='now',";
	$query .= " active=FALSE";
	$query .= " WHERE request_id=$request_id;";
  print "$query\n";
  $update = $conn->exec( $query );
  $status = $update->resultStatus;
  # print STDERR "$status \t$query\n";
  if ( $status >= PGRES_BAD_RESPONSE ) {
    print STDERR "ERROR: $query\n" . $conn->errorMessage . "\n";
  }
}



$query = "SELECT DISTINCT request_id, brief, fullname, request_on  FROM request, usr";
$query .= " WHERE request.last_status = 'T'";
$query .= " AND request.request_id = request_status.request_id";
$query .= " AND request_status.status_code = 'T'";
$query .= " AND request_status.status_on < ('today'::datetime - '$retire_days days'::timespan) ";
$query .= " AND request.requester_id = usr.user_no";
$requests = $conn->exec( $query );
$status = $requests->resultStatus;
print STDERR "$status \t$query\n";
if ( $status >= PGRES_BAD_RESPONSE ) {
  print STDERR "ERROR: $query\n" . $conn->errorMessage . "\n";
}
for ( $i=0; $i < $requests->ntuples; $i++  ) {
	$request_id = $requests->getvalue($i,0);
	$request_brief = $requests->getvalue($i,1);
	$request_by = $requests->getvalue($i,2);
	$request_on = $requests->getvalue($i,3);
	retire_request( );
}

