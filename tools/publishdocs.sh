#!/bin/bash
cd "$(dirname "$0")"

#rm -Rf .tmp
#mkdir -p .tmp/docs
#apigen --source ../lib/Intermesh/ --destination .tmp/docs

#scp -r .tmp/docs mschering@web1.imfoss.nl:/var/www/intermesh.io/html/php/
#rm -Rf .tmp

rsync -av --delete -e ssh ../docs/apigen/ mschering@web1.imfoss.nl:/var/www/intermesh.io/html/php/docs/