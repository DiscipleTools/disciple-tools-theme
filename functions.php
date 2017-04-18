<?php

// Pre-2.6 compatibility
if ( !defined( 'WP_CONTENT_URL' ) )
    define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );

if ( !defined( 'WP_CONTENT_DIR' ) )
    define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );

if ( !defined( 'WP_PLUGIN_URL' ) )
    define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );

if ( !defined( 'WP_PLUGIN_DIR' ) )
    define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

if ( !defined( 'WP_LANG_DIR') )
    define( 'WP_LANG_DIR', WP_CONTENT_DIR . '/languages' );

if ( !defined( 'DISCIPLE_TOOLS_DIR') )
    define( 'DISCIPLE_TOOLS_DIR', WP_PLUGIN_DIR . '/disciple-tools' );


// Theme support options
require_once(get_template_directory().'/assets/functions/theme-support.php'); 

// WP Head and other cleanup functions
require_once(get_template_directory().'/assets/functions/cleanup.php'); 

// Register scripts and stylesheets
require_once(get_template_directory().'/assets/functions/enqueue-scripts.php'); 

// Register custom menus and menu walkers
require_once(get_template_directory().'/assets/functions/menu.php'); 

// Register sidebars/widget areas
require_once(get_template_directory().'/assets/functions/sidebar.php'); 

// Makes WordPress comments suck less
require_once(get_template_directory().'/assets/functions/comments.php'); 

// Replace 'older/newer' post links with numbered navigation
require_once(get_template_directory().'/assets/functions/page-navi.php'); 

// Adds support for multiple languages
require_once(get_template_directory().'/assets/translation/translation.php');

// Adds Disciple Tools functions
require_once(get_template_directory().'/assets/functions/disciple-tools-user.php');

// Adds Disciple Tools functions
require_once(get_template_directory().'/assets/functions/disciple-tools-charts.php');

// Adds Disciple Tools Page Reports
require_once(get_template_directory().'/assets/functions/page-reports.php');

// Adds Disciple Tools Page Profile
require_once(get_template_directory().'/assets/functions/page-profile.php');

// Adds Disciple Tools Page Profile
require_once(get_template_directory().'/assets/functions/page-prayer-guide.php');

require_once (get_template_directory() . '/assets/functions/page-front-page.php');
require_once (get_template_directory() . '/assets/functions/disciple-tools-contacts.php');


// Remove 4.2 Emoji Support
// require_once(get_template_directory().'/assets/functions/disable-emoji.php'); 

// Adds site styles to the WordPress editor
//require_once(get_template_directory().'/assets/functions/editor-styles.php'); 

// Related post function - no need to rely on plugins
// require_once(get_template_directory().'/assets/functions/related-posts.php'); 

// Customize the WordPress login menu
// require_once(get_template_directory().'/assets/functions/login.php'); 

// Customize the WordPress admin
// require_once(get_template_directory().'/assets/functions/admin.php'); 