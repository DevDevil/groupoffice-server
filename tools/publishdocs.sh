#!/bin/bash
cd "$(dirname "$0")"

#apigen generate --source "../lib" --destination "../docs/apigen" --title "GroupOffice API" --charset "UTF-8" --exclude "*/vendor/*" --access-levels "public,protected" --internal "no" --php "yes" --tree "yes" --deprecated "no" --todo "no" --download "no" --source-code "yes" --colors "yes"

apigen generate --source "../lib" --destination "../docs/apigen" --title "GroupOffice API" --exclude "*/vendor/*"

#rm -Rf .tmp
#mkdir -p .tmp/docs
#apigen --source ../lib/Intermesh/ --destination .tmp/docs

#scp -r .tmp/docs mschering@web1.imfoss.nl:/var/www/intermesh.io/html/php/
#rm -Rf .tmp

rsync -av --delete -e ssh ../docs/apigen/ mschering@web1.imfoss.nl:/var/www/intermesh.io/html/php/docs/