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

echo " Loading database tables... "
cd dump
for A in t-*.sql; do
  echo "    $A "
  psql -q -f $A -d $DATABASE 2>&1 | uniq
done
cd ..
# echo "."

echo "Finishing load..."
psql -qxtf finish-load.sql -d $DATABASE | grep -v RECORD
