CREATE TABLE attachment_type (
   type_code TEXT PRIMARY KEY,
   type_desc TEXT,
   seq INT4,
   mime_type TEXT,
   pattern TEXT,
   mime_pattern TEXT
);

