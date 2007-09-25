#!/usr/bin/perl -w
#
# Given two users, organisations or systems, this program
# will attempt to merge the second one into the first one,
# so that the second one may be deleted from the database
# afterwards.
#
# The input may also be provided as input files consisting
# of lines containing a pair of comma-separated ids which
# are to be merged.
#

use strict;
use DBI;
use Getopt::Long qw(:config permute);  # allow mixed args.
use Pod::Usage;

# Options variables
my $debug = 0;
my $quiet = 0;
my $help  = 0;
my $man   = 0;

my ( $dsn, $dbuser, $dbpass, $type, $id1, $id2, $idfile );

GetOptions ('debug!'    => \$debug,
            'dsn=s'     => \$dsn,
            'dbuser=s'  => \$dbuser,
            'dbpass=s'  => \$dbpass,
            'type=s'    => \$type,
            'id=f'      => \$id1,
            'id2=f'     => \$id2,
            'idfile=s'  => \$idfile,
            'quiet!'    => \$quiet,
            'help'      => \$help,
            'man'       => \$man  );

pod2usage(1) if $help;
pod2usage(-exitstatus => 0, -verbose => 2) if $man;

my $tword = 'users';
$tword = 'systems'         if ( $type =~ /^s/i );
$tword = 'organisations'   if ( $type =~ /^o/i );

############################################################
# Open database connection and set up our queries
############################################################
my $dbh = DBI->connect("dbi:Pg:dbname=$dsn", $dbuser, $dbpass, { AutoCommit => 0, pg_server_prepare => 0 } ) or die "Can't connect to database $dsn";


my $merge_qry;

if ( $type =~ /^u/i ) {
  $merge_qry = $dbh->prepare( <<EOQ  ) or die $dbh->errstr;
UPDATE request SET entered_by = \$1 WHERE entered_by = \$2;
UPDATE request SET requester_id = \$1 WHERE requester_id = \$2;
UPDATE request_action SET updated_by_id = \$1 WHERE updated_by_id = \$2;
UPDATE request_allocated SET allocated_to_id = \$1 WHERE allocated_to_id = \$2;
UPDATE request_attachment SET attached_by = \$1 WHERE attached_by = \$2;
UPDATE request_history SET entered_by = \$1 WHERE entered_by = \$2;
UPDATE request_history SET requester_id = \$1 WHERE requester_id = \$2;
UPDATE request_interested SET user_no = \$1 WHERE user_no = \$2 AND NOT EXISTS( SELECT 1 FROM request_interested ri WHERE request_interested.request_id = ri.request_id AND ri.user_no = \$1);
UPDATE request_note SET note_by_id = \$1 WHERE note_by_id = \$2;
UPDATE request_project SET project_manager = \$1 WHERE project_manager = \$2;
UPDATE request_project SET qa_mentor = \$1 WHERE qa_mentor = \$2;
UPDATE request_qa_action SET action_by = \$1 WHERE action_by = \$2;
UPDATE request_quote SET quote_by_id = \$1 WHERE quote_by_id = \$2;
UPDATE request_quote SET approved_by_id = \$1 WHERE approved_by_id = \$2;
UPDATE request_status SET status_by_id = \$1 WHERE status_by_id = \$2;
UPDATE request_timesheet SET work_by_id = \$1 WHERE work_by_id = \$2;
UPDATE request_timesheet SET charged_by_id = \$1 WHERE charged_by_id = \$2;
UPDATE qa_project_step SET responsible_usr = \$1 WHERE responsible_usr = \$2;
UPDATE qa_project_approval SET assigned_to_usr = \$1 WHERE assigned_to_usr = \$2;
UPDATE qa_project_approval SET approval_by_usr = \$1 WHERE approval_by_usr = \$2;
UPDATE caldav_data SET user_no = \$1 WHERE user_no = \$2;
UPDATE caldav_data SET logged_user = \$1 WHERE logged_user = \$2;
UPDATE help_hit SET user_no = \$1 WHERE user_no = \$2 AND NOT EXISTS( SELECT 1 FROM help_hit hh WHERE help_hit.topic = hh.topic AND hh.user_no = \$1);
UPDATE infonode SET created_by = \$1 WHERE created_by = \$2;
UPDATE organisation SET admin_user_no = \$1 WHERE admin_user_no = \$2;
UPDATE saved_queries SET user_no = \$1 WHERE user_no = \$2 AND NOT EXISTS( SELECT 1 FROM saved_queries sq WHERE saved_queries.query_name = sq.query_name AND sq.user_no = \$1);
UPDATE session SET user_no = \$1 WHERE user_no = \$2;
UPDATE system_usr SET user_no = \$1 WHERE user_no = \$2 AND NOT EXISTS( SELECT 1 FROM system_usr su WHERE system_usr.system_id = su.system_id AND su.user_no = \$1);
UPDATE timesheet_note SET note_by_id = \$1 WHERE note_by_id = \$2;
UPDATE wu SET wu_by = \$1 WHERE wu_by = \$2;
UPDATE wu_vote SET wu_by = \$1 WHERE wu_by = \$2;
UPDATE wu_vote SET vote_by = \$1 WHERE vote_by = \$2;
UPDATE saved_queries SET
       query_sql = replace(query_sql,
                       'requester_id = '||\$2::text||' ',
		       'requester_id = '||\$1::text||' '), 
       query_params = replace(query_params,
                       '"requested_by";s:' || (length(\$2)::text) || ':"' || \$2 || '"',
		       '"requested_by";s:' || (length(\$1)::text) || ':"' || \$1 || '"')
		    WHERE query_sql ~ ('requester_id = '||\$2::text||' ');
UPDATE saved_queries SET
       query_sql = replace(query_sql,
                       'interested.user_no = '||\$2::text||' ',
		       'interested.user_no = '||\$1::text||' '), 
       query_params = replace(query_params,
                       '"interested_in";s:' || (length(\$2)::text) || ':"' || \$2 || '"',
		       '"interested_in";s:' || (length(\$1)::text) || ':"' || \$1 || '"')
		    WHERE query_sql ~ ('interested.user_no = '||\$2::text||' ');
UPDATE saved_queries SET
       query_sql = replace(query_sql,
                       'allocated_to_id = '||\$2::text||' ',
		       'allocated_to_id = '||\$1::text||' '), 
       query_params = replace(query_params,
                       '"allocated_to";s:' || (length(\$2)::text) || ':"' || \$2 || '"',
		       '"allocated_to";s:' || (length(\$1)::text) || ':"' || \$1 || '"')
		    WHERE query_sql ~ ('allocated_to_id = '||\$2::text||' ');
EOQ
}
elsif ( $type =~ /^s/i ) {
  $merge_qry = $dbh->prepare( <<EOQ  ) or die $dbh->errstr;
UPDATE system_usr SET system_id = \$1 WHERE system_id = \$2;
UPDATE org_system SET system_id = \$1 WHERE system_id = \$2;
UPDATE organisation SET general_system = \$1 WHERE general_system = \$2;
UPDATE request SET system_id = \$1 WHERE system_id = \$2;
UPDATE saved_queries SET
       query_sql = replace(query_sql,
                       'system_id='||\$2::text||' ',
		       'system_id='||\$1::text||' '), 
       query_params = replace(query_params,
                       '"system_id";s:' || (length(\$2)::text) || ':"' || \$2 || '"',
		       '"system_id";s:' || (length(\$1)::text) || ':"' || \$1 || '"')
		    WHERE query_sql ~ ('system_id='||\$2::text||' ');
EOQ
}
elsif ( $type =~ /^o/i ) {
  $merge_qry = $dbh->prepare( <<EOQ  ) or die $dbh->errstr;
UPDATE org_system SET org_code = \$1 WHERE org_code = \$2;
UPDATE organisation_action SET org_code = \$1 WHERE org_code = \$2;
UPDATE organisation_tag SET org_code = \$1 WHERE org_code = \$2;
UPDATE usr SET org_code = \$1 WHERE org_code = \$2;
UPDATE saved_queries SET
       query_sql = replace(query_sql,
                       'org_code='||\$2::text||' ',
		       'org_code='||\$1::text||' '), 
       query_params = replace(query_params,
                       '"org_code";s:' || (length(\$2)::text) || ':"' || \$2 || '"',
		       '"org_code";s:' || (length(\$1)::text) || ':"' || \$1 || '"')
		    WHERE query_sql ~ ('org_code='||\$2::text||' ');
EOQ
}

merge_two($id1,$id2) if ( defined($id1) && defined($id2) );

if ( defined($idfile) ) {
  open( IDFILE, '<', $idfile);
  while( <IDFILE> ) {
    /^[\s"]*(\d+)[\s"]*,[\s"]*(\d+)[\s"]*(,.*)?$/ && merge_two( $1, $2 ) or do {
      print "Skipping line\n";
    };
  }
  close(IDFILE);
}

$dbh->commit;

sub merge_two {
  my $id1 = shift;
  my $id2 = shift;

  print "Merging $tword $id2 onto $id1\n";
  $merge_qry->execute( $id1, $id2 ) or die $dbh->errstr;
}


__END__

=head1 NAME

merge-utility.pl - Merge two WRMS systems/organisations/users

=head1 SYNOPSIS

merge-utility.pl [options]

 Options:
   --dsn          The DSN of the dbname[;host=...][;port=...]
   --dbuser       The database username
   --dbpass       The database password
   --type         The type (U, O or S for user, org or system)
   --id           The ID of the primary merge object
   --id2          The ID of the other merge object
   --idfile       A file of lines of the form "id1,id2"
   --help         brief help message
   --debug        enable debugging output

=head1 OPTIONS

=over 8

=item B<--dsn>

The name of the database, possibly with extra DSN parameters, so
that it could be "wrms;host=dewey;port=5433" rather than just a
simple 'wrms'.

=item B<--dbuser>

A user name for connecting to the database.

=item B<--dbpass>

A password for connecting to the database.

=item B<--type>

The type of records to be merged: U, O or S, indicating 'User',
'Organisation', or 'System' records.

=item B<--id> (and B<--id2>)

The IDs of the two records to be merged.  Afterwards only the first
record should be referenced and the second one may then be deleted.

=item B<--idfile>

A file with each line containing a pair of IDs to be merged,
separated by a comma:

101,321
121,359
143,379
...


=item B<--help>

Print a brief help message and exits.

=item B<--debug>

Enable debugging output.

=back

=head1 DESCRIPTION

 will merge two records.

Given two users, organisations or systems, B<merge-utility.pl>
will attempt to merge the second one into the first one, so
that the second one may be deleted from the database afterwards.

The input may also be provided as input files consisting of
lines containing a pair of comma-separated ids which are to be
merged.

=cut

