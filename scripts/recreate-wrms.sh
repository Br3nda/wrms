#!/bin/bash
#
# Recreate the WRMS database from scratch
#
DATABASE=wrms

echo " Destroying old database..."
dropdb $DATABASE
echo " Creating new database..."
createdb --encoding "SQL_ASCII" $DATABASE

echo " Creating database structures..."
psql -q -f create-wrms.sql -d $DATABASE 2>&1 | grep -v "will create implicit " | grep -v "RemoveFunction"

echo " Loading database tables... "
cd dump
for A in t-*.sql; do
  [ "$A" = "t-request_words.sql" ] && continue
  echo "    $A "
  psql -q -f $A -d $DATABASE 2>&1 | uniq
done
cd ..

echo "Finishing load..."
psql -qxtf finish-load.sql -d $DATABASE | grep "|"
