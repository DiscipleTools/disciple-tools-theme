#!/bin/bash

cd "$(dirname "${BASH_SOURCE[0]}")/../"

found_error=0

while read -d '' filename ; do

    # php -l checks the file for syntax errors
    php -l "$filename" || found_error=1

done < <(find . -name "*.php" -print0)

exit $found_error
