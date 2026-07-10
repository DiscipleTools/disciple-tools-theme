### 02_phpunit.sh (exit 1)
=== PHPUnit Tests ===
  WP_TESTS_DIR: /workspace/wp-data/wordpress-tests-lib
  DB host:      mysql / wordpress_test

Running PHPUnit (WP_MULTISITE=1)...
PHP Warning:  mysqli_real_connect(): (HY000/2002): No such file or directory in /workspace/wp-data/wordpress/wp-includes/class-wpdb.php on line 1988
[0;31m
wp_die() called
Message: No such file or directory
Error establishing a database connection
This either means that the username and password information in your wp-config.php file is incorrect or that contact with the database server at localhost could not be established. This could mean your host’s database server is down.

Are you sure you have the correct username and password?
Are you sure you have typed the correct hostname?
Are you sure the database server is running?

If you are unsure what these terms mean you should probably contact your host. If you still need help you can always visit the WordPress support forums.

[0mTitle: WordPress › Error
Args:
	response: 500
	code: wp_die
	exit: 1
	back_link: 
	link_url: 
	link_text: 
	text_direction: ltr
	charset: UTF-8
	additional_errors: array (
)

