#!/bin/bash

# Update local database from socrates
SITE=$1

[ "$SITE" == "" ] && SITE=socrates.catalyst.net.nz

echo "Grabbing latest version off $SITE"
ssh $SITE 'cd ~/wrms/scripts; ./dump-db.sh'
scp $SITE:wrms/scripts/dump/* ~/wrms/scripts/dump
pushd ~/wrms/scripts
./recreate-wrms.sh
popd
