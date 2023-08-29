<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly
/**
 * Enqueue Scripts for the site.
 *
 * @author  Disciple.Tools
 * @package Disciple.Tools
 */

/*
 * Action and Filters
 */

add_action( 'admin_enqueue_scripts', 'dt_admin_pages_scripts' );
add_action( 'admin_enqueue_scripts', 'dt_people_groups_post_type_scripts' );
add_action( 'admin_enqueue_scripts', 'dt_options_scripts' );
add_action( 'admin_enqueue_scripts', 'dt_new_user_scripts' );

/*
 * Functions
 */

/**
 * Loads scripts and styles for the contacts page.
 */
function dt_admin_pages_scripts() {
    wp_enqueue_script( 'dt_shared_scripts', disciple_tools()->admin_js_url . 'dt-shared.js', [ 'jquery' ], filemtime( disciple_tools()->admin_js_path . 'dt-shared.js' ), true );
    wp_register_style( 'dt_admin_css', disciple_tools()->admin_css_url . 'disciple-tools-admin-styles.css', [], filemtime( disciple_tools()->admin_css_path . 'disciple-tools-admin-styles.css' ) );
    wp_enqueue_style( 'dt_admin_css' );
}

/**
 * Loads scripts and styles for the assets page.
 */
function dt_people_groups_post_type_scripts() {
    global $pagenow, $post;

    if ( ( ( 'post.php' === $pagenow || 'post-new.php' === $pagenow || 'edit.php' === $pagenow )
        && 'peoplegroups' === get_post_type( $post ) ) || ( isset( $_GET['tab'] ) && $_GET['tab'] === 'people-groups' ) ) {

        wp_enqueue_script( 'dt_peoplegroups_scripts', get_template_directory_uri() . '/dt-people-groups/people-groups.js', [
            'jquery',
            'jquery-ui-core',
        ], filemtime( get_template_directory() . '/dt-people-groups/people-groups.js' ), true );
        wp_localize_script( 'dt_peoplegroups_scripts', 'dtPeopleGroupsAPI', build_people_groups_api_object() );
    }
}

function build_people_groups_api_object() {
    return [
        'root'               => esc_url_raw( rest_url() ),
        'nonce'              => wp_create_nonce( 'wp_rest' ),
        'current_user_login' => wp_get_current_user()->user_login,
        'current_user_id'    => get_current_user_id(),
        'theme_uri'          => get_template_directory_uri(),
        'images_uri'         => disciple_tools()->admin_img_url
    ];
}

/**
 * Loads scripts and styles for the assets page.
 */
function dt_options_scripts() {
    $allowed_pages = [
        'dt_options',
        'dt_utilities',
        'dt_extensions',
    ];

    $allowed_pages = apply_filters( 'dt_options_script_pages', $allowed_pages );

    if ( isset( $_GET['page'] ) && ( in_array( $_GET['page'], $allowed_pages, true ) ) ) {

        wp_register_script( 'jquery-ui-js', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', [ 'jquery' ], '1.12.1', true );
        wp_enqueue_script( 'jquery-ui-js' );

        wp_enqueue_script( 'dt_options_script', disciple_tools()->admin_js_url . 'dt-options.js', [
            'jquery',
            'jquery-ui-core',
            'jquery-ui-sortable',
            'jquery-ui-dialog',
            'lodash',
            'jquery-ui-js'
        ], filemtime( disciple_tools()->admin_js_path . 'dt-options.js' ), true );
        wp_register_style( 'jquery-ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css' );
        wp_enqueue_style( 'jquery-ui' );

        dt_theme_enqueue_style( 'material-font-icons-local', 'dt-core/dependencies/mdi/css/materialdesignicons.min.css', array() );
        wp_enqueue_style( 'material-font-icons', 'https://cdn.jsdelivr.net/npm/@mdi/font@6.6.96/css/materialdesignicons.min.css' );

        if ( isset( $_GET['tab'] ) && ( ( $_GET['tab'] === 'people-groups' ) || ( $_GET['tab'] === 'general' ) ) ) {
            wp_enqueue_script( 'dt_peoplegroups_scripts', get_template_directory_uri() . '/dt-people-groups/people-groups.js', [
                'jquery',
                'jquery-ui-core',
            ], filemtime( get_template_directory() . '/dt-people-groups/people-groups.js' ), true );
            wp_localize_script( 'dt_peoplegroups_scripts', 'dtPeopleGroupsAPI', build_people_groups_api_object() );
        }

        wp_localize_script(
            'dt_options_script', 'dtOptionAPI', array(
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'theme_uri' => get_template_directory_uri(),
                'images_uri' => disciple_tools()->admin_img_url,
            )
        );
        wp_register_style( 'dt_admin_css', disciple_tools()->admin_css_url . 'disciple-tools-admin-styles.css', [], filemtime( disciple_tools()->admin_css_path . 'disciple-tools-admin-styles.css' ) );
        wp_enqueue_style( 'dt_admin_css' );
    }
}



function dt_new_user_scripts(){
    global $pagenow;
    if ( is_admin() && ( $pagenow === 'user-new.php' || $pagenow === 'user-edit.php' || $pagenow === 'profile.php' ) ) {
        wp_enqueue_script( 'jquery' );

        // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
        wp_register_style( 'jquery-ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css' );
        wp_enqueue_style( 'jquery-ui' );
        wp_enqueue_script( 'jquery-ui-autocomplete' );
    }
}


