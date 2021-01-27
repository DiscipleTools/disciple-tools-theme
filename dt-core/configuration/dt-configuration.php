<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly
/**
 * Hooks and configurations that customize wordpress for D.T
 */

add_action( 'init', 'dt_set_permalink_structure' );
add_action( 'update_option_permalink_structure', 'dt_permalink_structure_changed_callback' );
add_filter( 'comment_notification_recipients', 'dt_override_comment_notice_recipients', 10, 2 );
//unconditionally allow duplicate comments
add_filter( 'duplicate_comment_id', '__return_false' );
//allow multiple comments in quick succession
add_filter( 'comment_flood_filter', '__return_false' );
add_filter( 'pre_comment_approved', 'dt_filter_handler', '99', 2 );
remove_action( 'plugins_loaded', 'wp_maybe_load_widgets', 0 );  //don't load widgets as we don't use them
remove_action( "init", "wp_widgets_init", 1 ); // keep widgets from loading
add_filter( 'wpmu_signup_blog_notification_email', 'dt_wpmu_signup_blog_notification_email', 10, 8 );
add_filter( 'cron_schedules', 'dt_cron_schedules' );
/**
 * Set default premalink structure
 * Needed for the rest api url structure (for wp-json to work)
 */
function dt_set_permalink_structure() {
    global $wp_rewrite;
    $wp_rewrite->set_permalink_structure( '/%postname%/' );
    flush_rewrite_rules();
}

function dt_warn_user_about_permalink_settings() {
    ?>
    <div class="error notices">
        <p>You may only set your permalink settings to "Post name"'</p>
    </div>
    <?php
}

/**
 * Notification that 'posttype' is the only permalink structure available.
 *
 * @param $permalink_structure
 */
function dt_permalink_structure_changed_callback( $permalink_structure ) {
    global $wp_rewrite;
    if ( $permalink_structure !== '/%postname%/' ) {
        add_action( 'admin_notices', 'dt_warn_user_about_permalink_settings' );
    }
}

function dt_override_comment_notice_recipients() {
    return [];
}

/**
 * @param $approved
 * @param $commentdata
 *
 * @return int
 */
function dt_filter_handler( $approved, $commentdata ){
    // inspect $commentdata to determine approval, disapproval, or spam status
    //approve all comments.
    return 1;
}

function dt_custom_dir_attr( $lang ){
    if (is_admin()) {
        return $lang;
    }

    $current_user = wp_get_current_user();
    $user_language = get_user_locale( $current_user->ID );
    /* translators: If your language is written right to left make this translation as 'rtl', if it is written ltr make the translated text 'ltr' or leave it blank */
    $dir = _x( 'ltr', 'either rtl or ltr', 'disciple_tools' );

    //default direction to ltr
    if ( $dir !== "rtl" ){
        $dir = "ltr";
    }

    $dir_attr = 'dir="' . $dir . '"';

    return 'lang="' . $user_language .'" ' .$dir_attr;
}

function dt_wpmu_signup_blog_notification_email( $message, $domain, $path, $title, $user, $user_email, $key, $meta ){
    return str_replace( "blog", "site", $message );
}



function dt_cron_schedules( $schedules ) {
    $schedules['weekly'] = array(
        'interval' => 60 * 60 * 24 * 7, # 604,800, seconds in a week
        'display'  => 'Weekly'
    );
    return $schedules;
}
