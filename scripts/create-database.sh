#!/bin/sh
#
# Build the WRMS database
#

DBNAME="${1:-wrms}"
ADMINPW="${2}"

DBADIR="`dirname \"$0\"`/dba"

if ! createdb -E UTF8 "${DBNAME}" -T template0 ; then
  echo "Unable to create database"
  exit 1
fi

#
# This will fail if the language already exists, but it should not
# because we created from template0.
createlang plpgsql "${DBNAME}"

# Things matching this are not errors
FINE="(^CREATE |^GRANT|^BEGIN|^COMMIT| NOTICE: )"

# Load the AWL DBA stuff first...
AWLDBA="/usr/share/awl/dba"
psql -q -f "${AWLDBA}/awl-tables.sql" "${DBNAME}" 2>&1 | egrep -v "${FINE}"
psql -q -f "${AWLDBA}/schema-management.sql" "${DBNAME}" 2>&1 | egrep -v "${FINE}"

# Now load the WRMS bits on top of that
psql -q -f "${DBADIR}/wrms.sql" "${DBNAME}" 2>&1 | egrep -v "${FINE}"

psql -q -f "${DBADIR}/procedures.sql" "${DBNAME}"
psql -q -f "${DBADIR}/views.sql" "${DBNAME}"

psql -q -f "${DBADIR}/base-data.sql" "${DBNAME}"

#
# We can override the admin password generation for regression testing predictability
if [ "${ADMINPW}" = "" ] ; then
  #
  # Generate a random administrative password.  If pwgen is available we'll use that,
  # otherwise try and hack something up using a few standard utilities
  ADMINPW="`pwgen -Bcny 2>/dev/null | tr \"\\\'\" '^='`"
fi

if [ "$ADMINPW" = "" ] ; then
  # OK.  They didn't supply one, and pwgen didn't work, so we hack something
  # together from /dev/random ...
  ADMINPW="`dd if=/dev/urandom bs=512 count=1 2>/dev/null | tr -c -d "[:alnum:]" | cut -c2-9`"
fi

if [ "$ADMINPW" = "" ] ; then
  # Right.  We're getting desperate now.  We'll have to use a default password
  # and hope that they change it to something more sensible.
  ADMINPW="please change this password"
fi

psql -q -c "UPDATE usr SET password = '**${ADMINPW}' WHERE user_no = 1;" "${DBNAME}"

echo "The password for the 'admin' user has been set to '${ADMINPW}'"

#
# The supported locales are in a separate file to make them easier to upgrade
psql -q -f "${DBADIR}/supported_locales.sql" "${DBNAME}"
