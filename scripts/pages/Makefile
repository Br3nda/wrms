#!/bin/make

.SUFFIXES: .sql .log

.sql.log:
	psql -d wrms -c "delete from awm_page where page_name = '$(subst .sql,,$<)';" >$(subst .sql,.log,$<) 2>&1
	psql -d wrms -c "delete from awm_content where page_name = '$(subst .sql,,$<)';" >>$(subst .sql,.log,$<) 2>&1
	psql -d wrms -f $< >>$(subst .sql,.log,$<) 2>&1



all: $(shell ls *.sql | sed -e s/.sql/.log/ -)

clean:
	rm *.log

