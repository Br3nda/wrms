#!/bin/bash

# Update local database from socrates
SITE=$1

[ "$SITE" == "" ] && SITE=sartre.catalyst.net.nz

echo "Grabbing latest version off $SITE"
ssh $SITE 'cd ~/wrms/scripts; ./dump-db.sh; tar cvfz dump/tables.tgz dump/t-*.sql'
scp $SITE:wrms/scripts/dump/tables.tgz ~/wrms/scripts/dump
pushd ~/wrms/scripts
tar xvfz dump/tables.tgz
./recreate-wrms.sh
popd
