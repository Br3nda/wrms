#!/bin/bash
DATABASE=wrms
DUMPDIR=dump

rm ${DUMPDIR}/*.sql
echo "Dumping schema"
pg_dump -s -n -d ${DATABASE} >${DUMPDIR}/schema-dump.sql

for TABLE in `psql -qt -d $DATABASE -c "SELECT relname from pg_class where relkind != 'i' AND relname !~ '^pg_.*' AND relname !~ '^pga.*' AND relname !~ '.*_seq$';"` ; do
  echo "processing $TABLE"
  if [ "$TABLE" = "request_history" ]; then
    pg_dump -a -n -D -d ${DATABASE} -t $TABLE >${DUMPDIR}/t-$TABLE.sql
  else
    pg_dump -a -n ${DATABASE} -t $TABLE >${DUMPDIR}/t-$TABLE.sql
  fi
done

# Remove the request_words table which is built on load
rm ${DUMPDIR}/t-request_words.sql >>/dev/null 2>&1
