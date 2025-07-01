<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly
/**
 * Hooks and configurations that customize wordpress for D.T
 */
nocache_headers();
add_action( 'init', 'dt_set_permalink_structure' );
add_action( 'update_option_permalink_structure', 'dt_permalink_structure_changed_callback' );
add_filter( 'comment_notification_recipients', 'dt_override_comment_notice_recipients', 10, 2 );
//unconditionally allow duplicate comments
add_filter( 'duplicate_comment_id', '__return_false' );
//allow multiple comments in quick succession
add_filter( 'comment_flood_filter', '__return_false' );
add_filter( 'pre_comment_approved', 'dt_filter_handler', '99', 2 );
remove_action( 'plugins_loaded', 'wp_maybe_load_widgets', 0 );  //don't load widgets as we don't use them
remove_action( 'init', 'wp_widgets_init', 1 ); // keep widgets from loading
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

function dt_is_locale_rtl( $locale = null ){
    if ( empty( $locale ) ){
        $locale = get_user_locale();
    }
    $all_languages = dt_get_global_languages_list();
    return isset( $all_languages[ $locale ]['rtl'] ) && !empty( $all_languages[ $locale ]['rtl'] );
}

function dt_custom_dir_attr( $lang ){
    /**
     * If true overrides this filter and returns $lang
     *
     * @since 1.57
     *
     * @param bool       $override Whether to override dt_custom_dir_attr or not
     */
    if ( apply_filters( 'dt_custom_dir_attr_override', false ) ) {
        return $lang;
    }

    if ( is_admin() ) {
        return $lang;
    }

    $current_user = wp_get_current_user();
    $user_language = get_user_locale( $current_user->ID );
    $dir = dt_is_locale_rtl( $user_language ) ? 'rtl' : 'ltr';

    //set the locale to be rtl globally
    if ( $dir === 'rtl' ){
        global $wp_locale;
        if ( $wp_locale instanceof WP_Locale ) {
            $wp_locale->text_direction = 'rtl';
        }
    }

    $dir_attr = 'dir="' . $dir . '"';

    return 'lang="' . $user_language .'" ' .$dir_attr;
}

function dt_wpmu_signup_blog_notification_email( $message, $domain, $path, $title, $user, $user_email, $key, $meta ){
    return str_replace( 'blog', 'site', $message );
}



function dt_cron_schedules( $schedules ) {
    $schedules['weekly'] = array(
        'interval' => 60 * 60 * 24 * 7, # 604,800, seconds in a week
        'display'  => 'Weekly'
    );
    $schedules['15min'] = array(
        'interval' => 15 * 60,
        'display'  => __( 'Once every 15 minutes' )
    );
    $schedules['5min'] = array(
        'interval' => 5 * 60,
        'display'  => __( 'Once every 5 minutes' )
    );
    return $schedules;
}

/**
 * Redirect from the wp-admin new-user page to the theme new-user page.
 */
add_action( 'admin_init', function () {
    global $pagenow;
    if ( $pagenow == 'user-new.php' ) {
        wp_redirect( home_url( '/user-management/add-user/' ) );
        exit;
    }
} );

/**
 * Log all successfully sent emails
 */
function dt_log_sent_emails( $mail_data ){
    $dt_email_logs_enabled = get_option( 'dt_email_logs_enabled' );
    if ( isset( $dt_email_logs_enabled ) && $dt_email_logs_enabled ){
        dt_activity_insert( [
            'action' => 'mail_sent',
            'object_name' => $mail_data['subject'] ?? '',
            'meta_value' => !empty( $mail_data['to'] ) ? json_encode( $mail_data['to'] ) : '',
            'object_note' => ( strlen( $mail_data['message'] ) > 100 ) ? substr( $mail_data['message'], 0, 100 ) . '...' : $mail_data['message']
        ] );
    }

    return $mail_data;
}

add_filter( 'wp_mail', 'dt_log_sent_emails', 999, 1 );
