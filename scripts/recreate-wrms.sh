#!/bin/bash
#
# Recreate the WRMS database from scratch
#
DATABASE=wrms
pg_dump -a -n -D -d $DATABASE -t session >dump/t-session.sql
echo " Destroying old database..."
destroydb $DATABASE
echo " Creating new database..."
createdb $DATABASE
echo " Creating database structures..."
psql -q -f create-wrms.sql -d $DATABASE 2>&1 | grep -v "will create implicit "
echo " Loading database tables..."
psql -q -t -f load-codes.sql wrms

# echo " Loading 'awm' tables..."
# psql -q -t -f seed-awm.sql wrms

echo " Loading dumped codes (may show errors)..."
psql -q -t -f dumped_codes.sql wrms

# echo -n " Loading page definitions:"
# cd pages
# for A in *.sql; do
#   echo -n " $A"
#   psql -q -f $A wrms
# done
# cd ..
# echo "."

echo " Loading dumped requests..."
psql -q -t -f dumped_requests.sql -d $DATABASE
echo " Loading dumped tables ..."
psql -q -t -f dumped_tables.sql -d $DATABASE

echo "Converting to new style ..."
psql -q -t -f convert-users.sql -d $DATABASE

echo "Finishing load..."
psql -qxtf finish-load.sql -d $DATABASE | grep -v RECORD
