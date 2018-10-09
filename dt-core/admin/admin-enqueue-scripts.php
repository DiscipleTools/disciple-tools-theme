<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly
/**
 * Enqueue Scripts for the site.
 *
 * @author  Chasm Solutions
 * @package Disciple_Tools
 */

/*
 * Action and Filters
 */

add_action( 'admin_enqueue_scripts', 'dt_contact_page_scripts' );
add_action( 'admin_enqueue_scripts', 'dt_group_page_scripts' );
add_action( 'admin_enqueue_scripts', 'dt_dashboard_page_scripts' );
add_action( 'admin_enqueue_scripts', 'dt_location_page_scripts' );
add_action( 'admin_enqueue_scripts', 'dt_dismiss_notice_callback_script' );
add_action( 'admin_enqueue_scripts', 'dt_people_groups_post_type_scripts' );
add_action( 'admin_enqueue_scripts', 'dt_options_scripts' );
add_action( 'admin_enqueue_scripts', 'dt_new_user_scripts' );

/*
 * Functions
 */

/**
 * Loads scripts and styles for the contacts page.
 */
function dt_contact_page_scripts() {
    global $pagenow, $post;

    if ( ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) && 'contacts' === get_post_type( $post ) ) {

        wp_register_style( 'dt_admin_css', disciple_tools()->admin_css_url . 'disciple-tools-admin-styles.css', [], filemtime( disciple_tools()->admin_css_path . 'disciple-tools-admin-styles.css' ) );
        wp_enqueue_style( 'dt_admin_css' );

        wp_enqueue_script( 'dt_contact_scripts', disciple_tools()->admin_js_url . 'dt-contacts.js', [
            'jquery',
            'jquery-ui-core',
        ], filemtime( disciple_tools()->admin_js_path . 'dt-contacts.js' ), true );
        wp_enqueue_script( 'dt_shared_scripts', disciple_tools()->admin_js_url . 'dt-shared.js', [], filemtime( disciple_tools()->admin_js_path . 'dt-shared.js' ), true );
    }
}

/**
 * Loads scripts and styles for the groups page.
 */
function dt_group_page_scripts() {
    global $pagenow, $post;

    if ( ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) && 'groups' === get_post_type( $post ) ) {

        wp_register_style( 'dt_admin_css', disciple_tools()->admin_css_url . 'disciple-tools-admin-styles.css', [], filemtime( disciple_tools()->admin_css_path . 'disciple-tools-admin-styles.css' ) );
        wp_enqueue_style( 'dt_admin_css' );

        wp_enqueue_script( 'dt_group_scripts', disciple_tools()->admin_js_url . 'dt-groups.js', [
            'jquery',
            'jquery-ui-core',
        ], filemtime( disciple_tools()->admin_js_path . 'dt-groups.js' ), true );
        wp_enqueue_script( 'dt_shared_scripts', disciple_tools()->admin_js_url . 'dt-shared.js', [], filemtime( disciple_tools()->admin_js_path . 'dt-shared.js' ), true );

        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-ui-datepicker' );

        // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
        wp_register_style( 'jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css' );
        wp_enqueue_style( 'jquery-ui' );
    }
}

/**
 * Loads scripts and styles for the groups page.
 */
function dt_dashboard_page_scripts() {
    global $pagenow;

    if ( is_admin() && 'index.php' === $pagenow ) {

        // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NoExplicitVersion
        wp_enqueue_script( 'google-charts', 'https://www.gstatic.com/charts/loader.js', array(), false );
        wp_enqueue_script( 'dt_shared_scripts', disciple_tools()->admin_js_url . 'dt-shared.js', [], filemtime( disciple_tools()->admin_js_path . 'dt-shared.js' ), true );
        wp_localize_script(
            'dt_dashboard_scripts', 'wpApiDashboard', array(
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' )
            )
        );
    }
}

/**
 * Loads scripts and styles for the locations page.
 */
function dt_location_page_scripts() {
    global $pagenow, $post;

    if ( ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) && 'locations' === get_post_type( $post ) ) {

        wp_register_style( 'dt_admin_css', disciple_tools()->admin_css_url . 'disciple-tools-admin-styles.css', [], filemtime( disciple_tools()->admin_css_path . 'disciple-tools-admin-styles.css' ) );
        wp_enqueue_style( 'dt_admin_css' );

        wp_enqueue_script( 'dt_locations_scripts', disciple_tools()->admin_js_url . 'dt-locations.js', [
            'jquery',
            'jquery-ui-core',
        ], filemtime( disciple_tools()->admin_js_path . 'dt-locations.js' ), true );
        wp_localize_script(
            "dt_locations_scripts", "dtLocAPI", array(
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'theme_uri' => get_stylesheet_directory_uri(),
                'images_uri' => disciple_tools()->admin_img_url,
                'spinner' => ' <img src="'.disciple_tools()->admin_img_url.'spinner.svg" width="12px" />'
            )
        );
        wp_enqueue_script( 'dt_shared_scripts', disciple_tools()->admin_js_url . 'dt-shared.js', [], filemtime( disciple_tools()->admin_js_path . 'dt-shared.js' ), true );
    }
}

/**
 * Loads scripts and styles for the assets page.
 */
function dt_people_groups_post_type_scripts() {
    global $pagenow, $post;

    if ( ( ( 'post.php' === $pagenow || 'post-new.php' === $pagenow || 'edit.php' === $pagenow )
        && 'peoplegroups' === get_post_type( $post ) ) || ( isset( $_GET['tab'] ) && $_GET['tab'] === 'people-groups' ) ) {

        wp_enqueue_script( 'dt_peoplegroups_scripts', get_stylesheet_directory_uri() . '/dt-people-groups/people-groups.js', [
            'jquery',
            'jquery-ui-core',
        ], filemtime( get_template_directory() . '/dt-people-groups/people-groups.js' ), true );
        wp_localize_script(
            "dt_peoplegroups_scripts", "dtPeopleGroupsAPI", array(
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'theme_uri' => get_stylesheet_directory_uri(),
                'images_uri' => disciple_tools()->admin_img_url,
            )
        );
        wp_enqueue_script( 'dt_shared_scripts', disciple_tools()->admin_js_url . 'dt-shared.js', [], filemtime( disciple_tools()->admin_js_path . 'dt-shared.js' ), true );
    }
}

/**
 * Loads scripts and styles for the assets page.
 */
function dt_options_scripts() {
    if ( isset( $_GET["page"] ) && ( $_GET["page"] === 'dt_options' || $_GET["page"] === 'dt_utilities' || $_GET["page"] === 'dt_extensions' ) ) {
        wp_enqueue_script( 'dt_options_script', disciple_tools()->admin_js_url . 'dt-options.js', [
            'jquery',
            'jquery-ui-core',
        ], filemtime( disciple_tools()->admin_js_path . 'dt-options.js' ), true );
        wp_enqueue_script( 'dt_shared_scripts', disciple_tools()->admin_js_url . 'dt-shared.js', [], filemtime( disciple_tools()->admin_js_path . 'dt-shared.js' ), true );
        wp_localize_script(
            "dt_options_script", "dtOptionAPI", array(
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'theme_uri' => get_stylesheet_directory_uri(),
                'images_uri' => disciple_tools()->admin_img_url,
            )
        );
        wp_register_style( 'dt_admin_css', disciple_tools()->admin_css_url . 'disciple-tools-admin-styles.css', [], filemtime( disciple_tools()->admin_css_path . 'disciple-tools-admin-styles.css' ) );
        wp_enqueue_style( 'dt_admin_css' );
    }
}

/**
 *
 */
function dt_dismiss_notice_callback_script() {
    global $pagenow;
    if ( is_admin() && $pagenow === 'options-general.php' ) {
        wp_enqueue_script( 'disciple-tools-admin_script', disciple_tools()->admin_js_url . 'disciple-tools-admin.js', [ 'jquery' ], filemtime( disciple_tools()->admin_js_path . 'disciple-tools-admin.js' ), true );
    }
}

function dt_new_user_scripts(){
    global $pagenow;
    if ( is_admin() && ( $pagenow === 'user-new.php' || $pagenow === 'user-edit.php' ) ) {
        wp_enqueue_script( 'jquery' );

        // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
        wp_register_style( 'jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css' );
        wp_enqueue_style( 'jquery-ui' );
        wp_enqueue_script( 'jquery-ui-autocomplete' );
    }
}
