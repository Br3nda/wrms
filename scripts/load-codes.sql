INSERT INTO "request_type" ("request_type","request_type_desc") values (0,'Help Request');
INSERT INTO "request_type" ("request_type","request_type_desc") values (10,'System Problem');
INSERT INTO "request_type" ("request_type","request_type_desc") values (20,'Bug');
INSERT INTO "request_type" ("request_type","request_type_desc") values (30,'Enhancement');

INSERT INTO "severity" ("severity_code","severity_desc") values (0,'If you get around to this one day...');
INSERT INTO "severity" ("severity_code","severity_desc") values (5,'Nice to have');
INSERT INTO "severity" ("severity_code","severity_desc") values (7,'Next time you are on-site please');
INSERT INTO "severity" ("severity_code","severity_desc") values (10,'An occasional problem');
INSERT INTO "severity" ("severity_code","severity_desc") values (15,'This is sometimes annoying');
INSERT INTO "severity" ("severity_code","severity_desc") values (20,'This is frequently a problem');
INSERT INTO "severity" ("severity_code","severity_desc") values (25,'This can occasionally cause data problems');
INSERT INTO "severity" ("severity_code","severity_desc") values (30,'I think this is important');
INSERT INTO "severity" ("severity_code","severity_desc") values (35,'It causes ongoing data problems');
INSERT INTO "severity" ("severity_code","severity_desc") values (40,'Needs resolving urgently');
INSERT INTO "severity" ("severity_code","severity_desc") values (45,'I know this is important!');
INSERT INTO "severity" ("severity_code","severity_desc") values (50,'Senior management have decided this is an issue');
INSERT INTO "severity" ("severity_code","severity_desc") values (55,'This is extremely urgent!');
INSERT INTO "severity" ("severity_code","severity_desc") values (60,'This is critical to our day to day operation!  Help!');

INSERT INTO "status" ("status_code","status_desc", "next_responsibility_is")
 values ('N','New request', 'Support');
INSERT INTO "status" ("status_code","status_desc", "next_responsibility_is")
 values ('R','Reviewed', 'Client');
INSERT INTO "status" ("status_code","status_desc", "next_responsibility_is")
 values ('H','On Hold', 'Client');
INSERT INTO "status" ("status_code","status_desc", "next_responsibility_is")
 values ('C','Cancelled', 'Client');
INSERT INTO "status" ("status_code","status_desc", "next_responsibility_is")
 values ('I','In Progress', 'Support');
INSERT INTO "status" ("status_code","status_desc", "next_responsibility_is")
 values ('L','Allocated', 'Support');
INSERT INTO "status" ("status_code","status_desc", "next_responsibility_is")
 values ('F','Finished', 'Client');
INSERT INTO "status" ("status_code","status_desc", "next_responsibility_is")
 values ('T','Testing', 'Client');
INSERT INTO "status" ("status_code","status_desc", "next_responsibility_is")
 values ('Q','Quoted', 'Client');
INSERT INTO "status" ("status_code","status_desc", "next_responsibility_is")
 values ('A','Quote Approved', 'Support');

