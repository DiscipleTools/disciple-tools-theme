#!/bin/bash

# Test installing this theme using the Wordpress CLI, with a command like this
# one:
#
# wp theme install --activate https://github.com/DiscipleTools/disciple-tools-theme/archive/master.zip
#
# If this fails, we know we have an issue that we need to fix to make the theme
# installable again


set -x
set -e

if [ "$GITHUB_REPOSITORY" = "" ] ; then
    echo "GITHUB_REPOSITORY env variable not set" >&2
    exit 1
fi

tmpdir=$(mktemp -d)

cd "$tmpdir"

curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
php wp-cli.phar --info
chmod +x wp-cli.phar

# Set up basic Wordpress installation:
./wp-cli.phar core download
./wp-cli.phar config create --force --dbname=testdb --dbuser=user --dbhost=127.0.0.0 --dbpass=password
./wp-cli.phar core install --url=localhost --title=test --admin_user=admin --admin_email=example@example.com

# Install theme
./wp-cli.phar theme install --activate "https://github.com/$GITHUB_REPOSITORY/archive/$GITHUB_SHA.zip"
