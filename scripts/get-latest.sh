#!/bin/bash

# Update local database from socrates
SITE=$1
DUMPDIR=/tmp/wrms-dump

[ "$SITE" == "" ] && SITE=sartre.catalyst.net.nz

echo "Grabbing latest version off $SITE"
ssh $SITE "cd ~/wrms/scripts; ./dump-db.sh $DUMPDIR; cd $DUMPDIR; tar cvfz ~/wrms-tables.tgz t-*.sql"
scp $SITE:wrms-tables.tgz ~/wrms/scripts/dump
ssh $SITE "rm wrms-tables.tgz; cd $DUMPDIR; rm *.sql"
pushd ~/wrms/scripts/dump
tar xvfz wrms-tables.tgz
pushd ~/wrms/scripts
./recreate-wrms.sh
popd
popd
