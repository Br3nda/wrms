#!/bin/bash

# Update local database from socrates
SITE=$1
DUMPDIR=/tmp/wrms-dump

[ "$SITE" == "" ] && SITE=andrew@dewey

if [ ! -e ~/wrms/scripts/dump ]
then
   mkdir -p ~/wrms/scripts/dump
elif [ ! -d ~/wrms/scripts/dump ]
then
   echo "~/wrms/scripts/dump already exits and is not a directory."
   echo "get-latest.sh script aborted!"
   exit 1
fi

if [ "${SITE}" != "nofetch" ] ; then
  echo "Grabbing latest version off $SITE"
  nssh $SITE ~/wrms/scripts/dump-db.sh
  nssh plato "scp $SITE:wrms-tables.tgz ."
  nssh plato "scp wrms-tables.tgz socrates:"
  scp socrates:wrms-tables.tgz ~/wrms/scripts/dump
  ssh socrates rm wrms-tables.tgz
  nssh plato rm wrms-tables.tgz
  nssh $SITE "rm wrms-tables.tgz; rm -r $DUMPDIR"
else
  echo "Using previous dump"
fi
pushd ~/wrms/scripts/dump
tar xvfz wrms-tables.tgz
pushd ~/wrms/scripts
./recreate-wrms.sh
popd
popd
