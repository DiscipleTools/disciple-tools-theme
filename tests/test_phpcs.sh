#!/bin/bash

set -e

if [[ $# = 0 ]]; then
    echo "No arguments given: skipping test"
    echo "If you meant to run phpcs on the entire codebase then run"
    echo
    echo "$0 --test-all"
    exit 0
fi

cd "$(dirname "${BASH_SOURCE[0]}")/../"

if [ "$(php -r 'echo version_compare( phpversion(), "7.0", ">=" ) ? 1 : 0;')" != 1 ] ; then
    vendor/bin/phpcs prayer-global-porch.php
    exit
fi

args=$@
if [[ "$1" = "--test-all" ]]; then
    args=""
fi

eval vendor/bin/phpcs $args
