set sql_inheritance to 'off';

-- Change the request_history table to be _not_ an inherited table
alter table request_history rename to old_request_history;
create table request_history as select * from request where request_id != request_id ;
alter table request_history add column entered_by int;
alter table request_history add column modified_on timestamptz;
alter table request_history alter column modified_on set default current_timestamp;
insert into request_history 
       (
            request_id, 
            request_on, 
            active, 
            last_status, 
            wap_status, 
            sla_response_hours, 
            urgency, 
            importance, 
            severity_code, 
            request_type, 
            requester_id, 
            eta, 
            last_activity, 
            sla_response_time, 
            sla_response_type, 
            requested_by_date, 
            agreed_due_date, 
            request_by, 
            brief, 
            detailed, 
            system_code, 
            modified_on
       )
       select * from old_request_history;
drop table old_request_history;
GRANT INSERT, UPDATE, SELECT ON request_history TO general;


-- Add a field to request to identify the person who entered the request
alter table request add column entered_by int;
update request
   set entered_by = (select status_by_id from request_status 
                                        where request_status.request_id = request.request_id
                                        order by status_on limit 1) ;

-- Add some fields to the saved_queries table
alter table saved_queries add column maxresults int;
alter table saved_queries add column rlsort text;
alter table saved_queries add column rlseq text;

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

