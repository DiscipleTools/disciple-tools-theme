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
add_action( 'admin_enqueue_scripts', 'dt_asset_page_scripts' );
add_action( 'admin_enqueue_scripts', 'dt_dismiss_notice_callback_script' );
add_action( 'admin_enqueue_scripts', 'dt_people_groups_post_type_scripts' );
add_action( 'admin_enqueue_scripts', 'dt_options_scripts' );

/*
 * Functions
 */

/**
 * Loads scripts and styles for the contacts page.
 */
function dt_contact_page_scripts()
{
    global $pagenow, $post;

    if ( ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) && 'contacts' === get_post_type( $post ) ) {

        wp_register_style( 'dt_admin_css', disciple_tools()->plugin_css_url . 'disciple-tools-admin-styles.css', [], filemtime( disciple_tools()->plugin_css_path . 'disciple-tools-admin-styles.css' ) );
        wp_enqueue_style( 'dt_admin_css' );

        wp_enqueue_script( 'dt_contact_scripts', disciple_tools()->plugin_js_url . 'dt-contacts.js', [
            'jquery',
            'jquery-ui-core',
        ], filemtime( disciple_tools()->plugin_js_path . 'dt-contacts.js' ), true );
        wp_enqueue_script( 'dt_shared_scripts', disciple_tools()->plugin_js_url . 'dt-shared.js', [], filemtime( disciple_tools()->plugin_js_path . 'dt-shared.js' ), true );
    }
}

/**
 * Loads scripts and styles for the groups page.
 */
function dt_group_page_scripts()
{
    global $pagenow, $post;

    if ( ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) && 'groups' === get_post_type( $post ) ) {

        wp_register_style( 'dt_admin_css', disciple_tools()->plugin_css_url . 'disciple-tools-admin-styles.css', [], filemtime( disciple_tools()->plugin_css_path . 'disciple-tools-admin-styles.css' ) );
        wp_enqueue_style( 'dt_admin_css' );

        wp_enqueue_script( 'dt_group_scripts', disciple_tools()->plugin_js_url . 'dt-groups.js', [
            'jquery',
            'jquery-ui-core',
        ], filemtime( disciple_tools()->plugin_js_path . 'dt-groups.js' ), true );
        wp_enqueue_script( 'dt_shared_scripts', disciple_tools()->plugin_js_url . 'dt-shared.js', [], filemtime( disciple_tools()->plugin_js_path . 'dt-shared.js' ), true );

        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-ui-datepicker', [ 'jquery' ] );

        wp_register_style( 'jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css' );
        wp_enqueue_style( 'jquery-ui' );
    }
}

/**
 * Loads scripts and styles for the groups page.
 */
function dt_dashboard_page_scripts()
{
    global $pagenow;

    if ( is_admin() && 'index.php' === $pagenow ) {

        wp_enqueue_script( 'google-charts', 'https://www.gstatic.com/charts/loader.js', array(),  false );
        wp_enqueue_script( 'dt_shared_scripts', disciple_tools()->plugin_js_url . 'dt-shared.js', [], filemtime( disciple_tools()->plugin_js_path . 'dt-shared.js' ), true );
        wp_enqueue_script( 'dt_dashboard_scripts', disciple_tools()->plugin_js_url . 'dt-dashboard.js', [], filemtime( disciple_tools()->plugin_js_path . 'dt-dashboard.js' ), true );
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
function dt_location_page_scripts()
{
    global $pagenow, $post;

    if ( ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) && 'locations' === get_post_type( $post ) ) {

        wp_register_style( 'dt_admin_css', disciple_tools()->plugin_css_url . 'disciple-tools-admin-styles.css', [], filemtime( disciple_tools()->plugin_css_path . 'disciple-tools-admin-styles.css' ) );
        wp_enqueue_style( 'dt_admin_css' );

        wp_enqueue_script( 'dt_locations_scripts', disciple_tools()->plugin_js_url . 'dt-locations.js', [
            'jquery',
            'jquery-ui-core',
        ], filemtime( disciple_tools()->plugin_js_path . 'dt-locations.js' ), true );
        wp_enqueue_script( 'dt_shared_scripts', disciple_tools()->plugin_js_url . 'dt-shared.js', [], filemtime( disciple_tools()->plugin_js_path . 'dt-shared.js' ), true );
    }
}

/**
 * Loads scripts and styles for the assets page.
 */
function dt_asset_page_scripts()
{
    global $pagenow, $post;

    if ( ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) && 'assets' === get_post_type( $post ) ) {

        wp_register_style( 'dt_admin_css', disciple_tools()->plugin_css_url . 'disciple-tools-admin-styles.css', [], filemtime( disciple_tools()->plugin_css_path . 'disciple-tools-admin-styles.css' ) );
        wp_enqueue_style( 'dt_admin_css' );

        wp_enqueue_script( 'dt_assets_scripts', disciple_tools()->plugin_js_url . 'dt-assets.js', [
            'jquery',
            'jquery-ui-core',
        ], filemtime( disciple_tools()->plugin_js_path . 'dt-assets.js' ), true );
        wp_enqueue_script( 'dt_shared_scripts', disciple_tools()->plugin_js_url . 'dt-shared.js', [], filemtime( disciple_tools()->plugin_js_path . 'dt-shared.js' ), true );
    }
}

/**
 * Loads scripts and styles for the assets page.
 */
function dt_people_groups_post_type_scripts()
{
    global $pagenow, $post;

    if ( ( 'post.php' === $pagenow || 'post-new.php' === $pagenow || 'edit.php' === $pagenow ) && 'peoplegroups' === get_post_type( $post ) ) {

        wp_enqueue_script( 'dt_peoplegroups_scripts', disciple_tools()->plugin_js_url . 'dt-peoplegroups.js', [
            'jquery',
            'jquery-ui-core',
        ], filemtime( disciple_tools()->plugin_js_path . 'dt-peoplegroups.js' ), true );
        wp_enqueue_script( 'dt_shared_scripts', disciple_tools()->plugin_js_url . 'dt-shared.js', [], filemtime( disciple_tools()->plugin_js_path . 'dt-shared.js' ), true );
    }
}

/**
 * Loads scripts and styles for the assets page.
 */
function dt_options_scripts()
{
    if ( isset( $_GET["page"] ) && $_GET["page"] === 'dt_options' ) {
        wp_enqueue_script( 'dt_options_script', disciple_tools()->plugin_js_url . 'dt-options.js', [
            'jquery',
            'jquery-ui-core',
        ], filemtime( disciple_tools()->plugin_js_path . 'dt-options.js' ), true );
        wp_enqueue_script( 'dt_shared_scripts', disciple_tools()->plugin_js_url . 'dt-shared.js', [], filemtime( disciple_tools()->plugin_js_path . 'dt-shared.js' ), true );

        wp_register_style( 'dt_admin_css', disciple_tools()->plugin_css_url . 'disciple-tools-admin-styles.css', [], filemtime( disciple_tools()->plugin_css_path . 'disciple-tools-admin-styles.css' ) );
        wp_enqueue_style( 'dt_admin_css' );
    }
}

/**
 *
 */
function dt_dismiss_notice_callback_script()
{
    global $pagenow;
    if ( is_admin() && $pagenow === 'options-general.php' ) {
        wp_enqueue_script( 'disciple-tools-admin_script', disciple_tools()->plugin_js_url . 'disciple-tools-admin.js', [ 'jquery' ], filemtime( disciple_tools()->plugin_js_path . 'disciple-tools-admin.js' ), true );
    }
}
