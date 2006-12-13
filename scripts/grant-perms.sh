#!/bin/sh

DBNAME=$1
USER=$2
PERMISSION="${3:-SELECT}"

if [ "$USER" = "" ] ; then
  echo "Usage: $0 <username> [permissions]"
  exit
fi

TABLES="`psql "${DBNAME}" -qt -c \"select relname  from pg_class where relowner > 50 AND relkind in( 'r', 'S');\"`"

for T in ${TABLES} ; do
  psql "${DBNAME}" -c "grant ${PERMISSION} on ${T} to ${USER};"
done
