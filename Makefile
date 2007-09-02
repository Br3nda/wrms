#!/usr/bin/make -f
#

package=wrms
version=$(shell cat VERSION)

all: inc/always.php built-docs

built-docs: phpdoc.ini html/*.php inc/*.php
	phpdoc -c phpdoc.ini
	touch built-docs

#
# Insert the current version number into always.php
#
inc/always.php: VERSION inc/in.always.php
	sed -e "/^ *.c->version_string *= *'[^']*' *;/ s/^ *.c->version_string *= *'[^']*' *;/\$$c->version_string = '`head -n1 VERSION`';/" <inc/in.always.php >inc/always.php

#
# Build a release .tar.gz file in the directory above us
#
release: built-docs
	-ln -s . $(package)-$(version)
	tar czf ../$(package)-$(version).tar.gz \
	    --no-recursion --dereference $(package)-$(version) \
	    $(shell git-ls-files |grep -v '.git'|sed -e s:^:$(package)-$(version)/:) \
	    $(shell find $(package)-$(version)/docs/api/ ! -name "phpdoc.ini" )
	rm $(package)-$(version)

clean:
	rm -f built-docs
	-find doc/api/* ! -name "phpdoc.ini" ! -name ".gitignore" -delete
	-find . -name "*~" -delete


.PHONY:  all clean release
