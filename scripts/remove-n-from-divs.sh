#!/bin/bash

ROOT=~/uni/prj/capitularia
# ROOT=~/uni/capitularia/http/docs

for i in `find $ROOT/cap/publ/mss -maxdepth 1 -name "*.xml"`
# for i in `find $ROOT/cap/intern/InArbeit/ -name "*.xml"`
do
    DEST=/tmp/$(basename $i)

    xsltproc scripts/remove-n-from-divs.xsl "$i" > "$DEST"
    if [ "$?" = 0 ]; then
        cp --backup=numbered "$DEST" "$i"
        echo OK "$i"
    else
        echo ERRORS "$i"
    fi
    rm "$DEST"
done
