#!/bin/bash
DATABASE=wrms
DUMPDIR=${1:-/tmp/wrms-dump}
DBHOST=${2:-""}
if [ "$DBHOST" != "" ] ; then
  DBHOST="-i -U general -h ${DBHOST}"
fi

if [ ! -e ${DUMPDIR} ]
then
   mkdir -p ${DUMPDIR}
elif [ ! -d ${DUMPDIR} ]
then
   echo "${DUMPDIR} already exists and is not a directory."
   echo "dump-db.sh script aborted!"
   exit 1
fi

echo "Dumping to directory: ${DUMPDIR}"

echo "Dumping schema"
pg_dump -s ${DBHOST} -n -d ${DATABASE} >${DUMPDIR}/schema-dump.sql

for TABLE in `psql -qt -d $DATABASE -c "SELECT relname from pg_class where relkind != 'i' AND relname !~ '^pg_.*' AND relname !~ '^pga.*' AND relname !~ '.*_seq$';"` ; do
  echo "processing $TABLE"
  if [ "$TABLE" = "request_history" ]; then
    pg_dump -a -n ${DBHOST} -D -d ${DATABASE} -t $TABLE >${DUMPDIR}/t-$TABLE.sql
  else
    pg_dump -a -n ${DBHOST} ${DATABASE} -t $TABLE >${DUMPDIR}/t-$TABLE.sql
  fi
done

# Remove the request_words table which is built on load, if at all.
rm ${DUMPDIR}/t-request_words.sql >>/dev/null 2>&1
