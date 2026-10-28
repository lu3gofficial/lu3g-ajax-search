#!/bin/sh
# Crea dist/lu3g-ajax-search.zip (cartella lu3g-ajax-search/ all'interno), il pacchetto che WordPress installa e aggiorna.
# Esclude i file che servono solo allo sviluppo (git, script, automazioni).
set -e
cd "$(dirname "$0")/.."
rm -rf dist && mkdir -p dist/lu3g-ajax-search
cp -R lu3g-ajax-search.php includes widgets assets dist/lu3g-ajax-search/
(cd dist && zip -rq lu3g-ajax-search.zip lu3g-ajax-search -x '*.DS_Store')
rm -rf dist/lu3g-ajax-search
echo "dist/lu3g-ajax-search.zip"
