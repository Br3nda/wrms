#!/bin/bash

# Update local database from socrates
SITE=$1

[ "$SITE" == "" ] && SITE=socrates.catalyst.net.nz

echo "Grabbing latest version off $SITE"
ssh $SITE 'cd ~/wrms; ./dump-requests.sh'
scp $SITE:wrms/dumped* ~/wrms
pushd ~/wrms
./recreate-wrms.sh
popd
