#!/bin/bash
pushd ~/wrms
echo -n "Dumping request data "
pg_dump -a -D -n -t request wrms >dumped_requests.sql
echo -n "."
pg_dump -a -d -n -t request_history wrms >>dumped_requests.sql
echo -n "."
pg_dump -a -D -n -t request_note wrms >>dumped_requests.sql
echo -n "."
pg_dump -a -D -n -t request_status wrms >>dumped_requests.sql
echo -n "."
pg_dump -a -D -n -t request_interested wrms >>dumped_requests.sql
echo -n "."
pg_dump -a -D -n -t request_quote wrms >>dumped_requests.sql
echo -n "."
pg_dump -a -D -n -t request_allocated wrms >>dumped_requests.sql
echo -n "."
pg_dump -a -D -n -t request_timesheet wrms >>dumped_requests.sql
echo -n "."
pg_dump -a -D -n -t perorg_request wrms >>dumped_requests.sql
echo "."

echo -n "Dumping general tables "
pg_dump -a -D -n -t usr wrms >dumped_tables.sql
echo -n "."
pg_dump -a -D -n -t usr_setting wrms >>dumped_tables.sql
echo -n "."
pg_dump -a -D -n -t system_update wrms >>dumped_tables.sql
echo -n "."
pg_dump -a -D -n -t request_update wrms >>dumped_tables.sql
echo "."

echo -n "Dumping system codes "
pg_dump -a -D -n -t organisation wrms >>dumped_tables.sql
echo -n "."
pg_dump -a -D -n -t work_system wrms >>dumped_tables.sql
echo -n "."
pg_dump -a -D -n -t org_system wrms >>dumped_tables.sql
echo -n "."
pg_dump -a -D -n -t perorg_system wrms >>dumped_tables.sql
echo "."

echo -n "Dumping user data "
pg_dump -a -D -n -t awm_perorg wrms >>dumped_tables.sql
echo -n "."
pg_dump -a -D -n -t awm_perorg_data wrms >>dumped_tables.sql
echo -n "."
pg_dump -a -D -n -t awm_perorg_rel wrms >>dumped_tables.sql
echo -n "."
pg_dump -a -D -n -t awm_usr wrms >>dumped_tables.sql
echo -n "."
pg_dump -a -D -n -t awm_usr_group wrms >>dumped_tables.sql
echo -n "."
pg_dump -a -D -n -t awm_usr_setting wrms >>dumped_tables.sql
echo "."

echo -n "Dumping lookup codes "
pg_dump -a -D -n -t lookup_code wrms >dumped_codes.sql
echo "."

popd
