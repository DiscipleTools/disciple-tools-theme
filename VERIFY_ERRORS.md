### 02_phpunit.sh (exit 4)
=== PHPUnit Tests ===
  WP_TESTS_DIR: /workspace/wp-data/wordpress-tests-lib
  DB host:      mysql / wordpress_test
PHPUnit not found in vendor/ — running composer install...
Installing dependencies from lock file (including require-dev)
Verifying lock file contents can be installed on current platform.
Warning: The lock file is not up to date with the latest changes in composer.json. You may be getting outdated dependencies. It is recommended that you run `composer update` or `composer update <package name>`.
- Required package "kucrut/vite-for-wp" is not present in the lock file.
- Required (in require-dev) package "phpunit/phpunit" is not present in the lock file.
- Required (in require-dev) package "wp-coding-standards/wpcs" is in the lock file as "3.1.0" but that does not satisfy your constraint "^3.3".
- Required (in require-dev) package "yoast/phpunit-polyfills" is not present in the lock file.
This usually happens when composer files are incorrectly merged or the composer.json file is manually edited.
Read more about correctly resolving merge conflicts https://getcomposer.org/doc/articles/resolving-merge-conflicts.md
and prefer using the "require" command over editing the composer.json file directly https://getcomposer.org/doc/03-cli.md#require-r

