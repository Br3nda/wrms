#!/bin/bash

# Update local database from socrates
SITE=$1

[ "$SITE" == "" ] && SITE=socrates.catalyst.net.nz

echo "Grabbing latest version off $SITE"
ssh $SITE 'cd ~/wrms/scripts; ./dump-requests.sh'
scp $SITE:wrms/scripts/dumped* ~/wrms/scripts
pushd ~/wrms/scripts
./recreate-wrms.sh
popd
