#!/bin/bash
#
# Recreate the WRMS database from scratch
#
echo " Destroying old database..."
destroydb wrms
echo " Creating new database..."
createdb wrms
echo " Creating database structures..."
cd ~/wrms
psql -q -f ~/wrms/create-wrms.sql -d wrms 2>&1 | grep -v "will create implicit "
echo " Creating 'awm' database structures..."
psql -q -f ~/wrms/create-awm.sql -d wrms 2>&1 | grep -v "will create implicit "
echo " Loading database tables..."
psql -q -t -f ~/wrms/load-codes.sql wrms

# echo " Loading 'awm' tables..."
# psql -q -t -f seed-awm.sql wrms

echo " Loading dumped codes (may show errors)..."
psql -q -t -f dumped_codes.sql wrms

echo -n " Loading page definitions:"
cd pages
for A in *.sql; do
  echo -n " $A"
  psql -q -f $A wrms
done
cd ..
echo "."

echo " Loading dumped requests..."
psql -q -t -f dumped_requests.sql -d wrms
echo " Loading dumped tables ..."
psql -q -t -f dumped_tables.sql -d wrms
echo "Finishing load..."
psql -q -t -f finish-load.sql -d wrms

