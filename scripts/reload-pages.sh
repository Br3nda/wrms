#!/bin/bash
#
# Reload the WRMS pages
#
echo "Clearing current pages..."
psql -q -c "DELETE FROM awm_page;" wrms
psql -q -c "DELETE FROM awm_content;" wrms

echo "Loading page definitions:"
for A in pages/*.sql; do
  echo -n "$A"
  psql -q -f $A wrms
  echo "."
done

