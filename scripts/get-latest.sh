#!/bin/bash

# Update local database from socrates
SITE=$1
DUMPDIR=/tmp/wrms-dump

[ "$SITE" == "" ] && SITE=sartre.catalyst.net.nz

echo "Grabbing latest version off $SITE"
ssh $SITE 'cd ~/wrms/scripts; ./dump-db.sh; tar cvfz ~/wrms-tables.tgz $DUMPDIR/t-*.sql'
scp $SITE:wrms-tables.tgz ~/wrms/scripts/dump
pushd ~/wrms/scripts
tar xvfz dump/wrms-tables.tgz
./recreate-wrms.sh
popd
