#!/bin/bash
pushd ~/wrms/scripts
DATABASE=wrms
echo -n "Dumping request data "
pg_dump -a -D -n -t request $DATABASE >dumped_requests.sql
echo -n "."
pg_dump -a -d -n -t request_history $DATABASE >>dumped_requests.sql
echo -n "."
pg_dump -a -D -n -t request_note $DATABASE >>dumped_requests.sql
echo -n "."
pg_dump -a -D -n -t request_status $DATABASE >>dumped_requests.sql
echo -n "."
pg_dump -a -D -n -t request_interested $DATABASE >>dumped_requests.sql
echo -n "."
pg_dump -a -D -n -t request_quote $DATABASE >>dumped_requests.sql
echo -n "."
pg_dump -a -D -n -t request_allocated $DATABASE >>dumped_requests.sql
echo -n "."
pg_dump -a -D -n -t request_timesheet $DATABASE >>dumped_requests.sql
echo -n "."
pg_dump -a -D -n -t perorg_request $DATABASE >>dumped_requests.sql
echo "."

echo -n "Dumping general tables "
pg_dump -a -D -n -t usr $DATABASE >dumped_tables.sql
echo -n "."
pg_dump -a -D -n -t usr_setting $DATABASE >>dumped_tables.sql
echo -n "."
pg_dump -a -D -n -t system_update $DATABASE >>dumped_tables.sql
echo -n "."
pg_dump -a -D -n -t request_update $DATABASE >>dumped_tables.sql
echo "."

echo -n "Dumping system codes "
pg_dump -a -D -n -t organisation $DATABASE >>dumped_tables.sql
echo -n "."
pg_dump -a -D -n -t work_system $DATABASE >>dumped_tables.sql
echo -n "."
pg_dump -a -D -n -t org_system $DATABASE >>dumped_tables.sql
echo -n "."
pg_dump -a -D -n -t perorg_system $DATABASE >>dumped_tables.sql
echo "."

echo -n "Dumping user data "
pg_dump -a -D -n -t awm_perorg $DATABASE >>dumped_tables.sql
echo -n "."
pg_dump -a -D -n -t awm_perorg_data $DATABASE >>dumped_tables.sql
echo -n "."
pg_dump -a -D -n -t awm_perorg_rel $DATABASE >>dumped_tables.sql
echo -n "."
pg_dump -a -D -n -t awm_usr $DATABASE >>dumped_tables.sql
echo -n "."
pg_dump -a -D -n -t awm_usr_group $DATABASE >>dumped_tables.sql
echo -n "."
pg_dump -a -D -n -t awm_usr_setting $DATABASE >>dumped_tables.sql
echo "."

echo -n "Dumping lookup codes "
pg_dump -a -D -n -t lookup_code $DATABASE >dumped_codes.sql
echo "."

popd
