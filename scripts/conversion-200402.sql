set sql_inheritance to 'off';

-- Change the request_history table to be _not_ an inherited table
alter table request_history rename to old_request_history;
create table request_history as select * from old_request_history;
drop table old_request_history;

-- Change the indexes on request to be partial (where active)
drop index xak0_request;
drop index xak1_request;
drop index xak2_request;
drop index xak3_request;
drop index xak4_request;
create index xak0_request on request ( active, request_id ) where active ;
create index xak1_request on request ( active, severity_code, request_by ) where active ;
create index xak2_request on request ( active, request_by ) where active ;
create index xak3_request on request ( active, last_status ) where active ;
vacuum full verbose analyze request;

