#!/bin/bash

set -e

cd "$(dirname "${BASH_SOURCE[0]}")/../../"

if [ "$(php -r 'echo version_compare( phpversion(), "7.0", ">=" ) ? 1 : 0;')" != 1 ] ; then
    vendor/bin/phpcs disciple-tools.php
    exit
fi

eval vendor/bin/phpcs
