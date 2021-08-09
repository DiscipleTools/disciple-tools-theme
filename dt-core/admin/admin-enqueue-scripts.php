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
add_action( 'admin_enqueue_scripts', 'dt_utilities_workflows_scripts' );

/*
 * Functions
 */

/**
 * Loads scripts and styles for the contacts page.
 */
function dt_admin_pages_scripts() {
    global $pagenow, $post;

    if ( ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) ) {
        wp_enqueue_script( 'dt_shared_scripts', disciple_tools()->admin_js_url . 'dt-shared.js', [], filemtime( disciple_tools()->admin_js_path . 'dt-shared.js' ), true );
    }
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
        wp_localize_script(
            "dt_peoplegroups_scripts", "dtPeopleGroupsAPI", array(
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'theme_uri' => get_template_directory_uri(),
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
            'jquery-ui-sortable',
            'lodash'
        ], filemtime( disciple_tools()->admin_js_path . 'dt-options.js' ), true );
        wp_register_style( 'jquery-ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css' );
        wp_enqueue_style( 'jquery-ui' );

        if ( isset( $_GET["tab"] ) && $_GET["tab"] === 'people-groups' ) {
            wp_enqueue_script( 'dt_peoplegroups_scripts', get_template_directory_uri() . '/dt-people-groups/people-groups.js', [
                'jquery',
                'jquery-ui-core',
            ], filemtime( get_template_directory() . '/dt-people-groups/people-groups.js' ), true );
        }

        wp_localize_script(
            "dt_options_script", "dtOptionAPI", array(
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

/**
 * Loads scripts and styles for dt utilities workflows.
 */
function dt_utilities_workflows_scripts() {
    if ( isset( $_GET["page"] ) && ( $_GET["page"] === 'dt_utilities' ) ) {
        if ( isset( $_GET["tab"] ) && $_GET["tab"] === 'workflows' ) {
            wp_register_style( 'bootstrap-5-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css' );
            wp_enqueue_style( 'bootstrap-5-css' );

            wp_register_style( 'bootstrap-5-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css' );
            wp_enqueue_style( 'bootstrap-5-icons' );

            wp_register_style( 'typeahead-bootstrap', 'https://cdn.jsdelivr.net/npm/typeahead.js-bootstrap-css@1.2.1/typeaheadjs.css' );
            wp_enqueue_style( 'typeahead-bootstrap' );
            wp_enqueue_script( 'typeahead-bundle', 'https://cdn.jsdelivr.net/npm/typeahead.js@0.11.1/dist/typeahead.bundle.min.js', [
                'jquery',
            ], '0.11.1', true );

            wp_register_style( 'daterangepicker-css', 'https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css' );
            wp_enqueue_style( 'daterangepicker-css' );
            wp_enqueue_script( 'daterangepicker-js', 'https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.js', [], '3.1.0', true );

            wp_enqueue_script( 'dt_utilities_workflows_script', disciple_tools()->admin_js_url . 'dt-utilities-workflows.js', [
                'jquery',
                'lodash',
                'typeahead-bundle',
                'daterangepicker-js'
            ], filemtime( disciple_tools()->admin_js_path . 'dt-utilities-workflows.js' ), true );
        }
    }
}
