#!/bin/sh

USER=$1
PERMISSION="${2:-SELECT}"

if [ "$USER" = "" ] ; then
  echo "Usage: $0 <username> [permissions]"
  exit
fi

TABLES="`psql wrms -qt -c \"select relname  from pg_class where relowner > 50 AND relkind in( 'r', 'S');\"`"

for T in ${TABLES} ; do
  psql wrms -c "grant ${PERMISSION} on ${T} to ${USER};"
done
