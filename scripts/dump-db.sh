#!/bin/bash
DATABASE=wrms
DUMPDIR=dump
echo "Dumping schema"
pg_dump -s -n -d $DATABASE >$DUMPDIR/schema-dump.sql

for TABLE in `psql -qt -d $DATABASE -c "SELECT relname from pg_class where relkind != 'i' AND relowner > 50 AND relname !~ 'pga.*' AND relname !~ '.*_seq';"` ; do
  echo "processing $TABLE"
  pg_dump -a -n -D -d $DATABASE -t $TABLE >$DUMPDIR/t-$TABLE.sql
done

