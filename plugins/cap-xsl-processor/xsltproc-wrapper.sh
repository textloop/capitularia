#!/bin/bash

xsltproc "$@" | php /afs/rrz.uni-koeln.de/vol/www/projekt/capitularia/http/docs/wp-content/plugins/cap-xsl-processor/footnotes-post-processor.php
