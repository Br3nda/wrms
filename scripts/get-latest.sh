#!/bin/bash

# Update local database from socrates
SITE=$1
DUMPDIR=/tmp/wrms-dump

[ "$SITE" == "" ] && SITE=andrew@dewey

if [ ! -e ~/projects/wrms/scripts/dump ]
then
   mkdir -p ~/projects/wrms/scripts/dump
elif [ ! -d ~/projects/wrms/scripts/dump ]
then
   echo "~/projects/wrms/scripts/dump already exits and is not a directory."
   echo "get-latest.sh script aborted!"
   exit 1
fi

if [ "${SITE}" != "nofetch" ] ; then
  echo "Grabbing latest version off $SITE"
  nssh $SITE ~/projects/wrms/scripts/dump-db.sh
  nssh poe "scp $SITE:wrms-tables.tgz ."
  nssh poe "scp wrms-tables.tgz socrates:"
  scp socrates:wrms-tables.tgz ~/wrms/scripts/dump
  ssh socrates rm wrms-tables.tgz
  nssh poe rm wrms-tables.tgz
  nssh $SITE "rm wrms-tables.tgz; rm -r $DUMPDIR"
else
  echo "Using previous dump"
fi
pushd ~/projects/wrms/scripts/dump
tar xvfz wrms-tables.tgz
pushd ~/projects/wrms/scripts
./recreate-wrms.sh
popd
popd
