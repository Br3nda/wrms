#!/bin/bash

mkdir ~/backups >/dev/null 2>&1
cd ~/backups

savelog -c 40 wrms.pgdump >/dev/null
pg_dump -Fc wrms >wrms.pgdump
