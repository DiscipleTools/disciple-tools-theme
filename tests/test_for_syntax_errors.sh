#!/bin/bash

cd "$(dirname "${BASH_SOURCE[0]}")/../"

if [ "$(php -r 'echo version_compare( phpversion(), "7.0", ">=" ) ? 1 : 0;')" != 1 ] ; then
    php -l ../functions.php
    exit
fi

found_error=0

while read -d '' php_filename ; do

    # php -l checks the file for syntax errors
    php -l "$php_filename" || found_error=1

done < <(find . -path ./vendor -prune -o -name "*.php" -print0)


while read -d '' js_filename ; do

    echo "Checking Javascript syntax of $js_filename"
    # node -c checks the file for syntax errors
    node -c "$js_filename" || found_error=1

done < <(find . -path ./vendor -prune -path ./dt-core/dependencies -prune -o -path ./node_modules -prune -o -path ./dependencies -prune -o -name "*.js" -print0)


exit $found_error
