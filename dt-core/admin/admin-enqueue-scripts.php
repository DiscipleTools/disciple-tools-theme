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
        wp_enqueue_style( 'material-font-icons', 'https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css' );

        // Enqueue web components for dt-multi-select and other components
        dt_theme_enqueue_script( 'web-components', 'dt-assets/build/components/index.js', array(), false );
        dt_theme_enqueue_style( 'web-components-css', 'dt-assets/build/css/light.min.css', array() );

        if ( isset( $_GET['tab'] ) && ( ( $_GET['tab'] === 'people-groups' ) || ( $_GET['tab'] === 'general' ) ) ) {
            wp_enqueue_script( 'dt_peoplegroups_scripts', get_template_directory_uri() . '/dt-people-groups/people-groups.js', [
                'jquery',
                'jquery-ui-core',
            ], filemtime( get_template_directory() . '/dt-people-groups/people-groups.js' ), true );
            wp_localize_script( 'dt_peoplegroups_scripts', 'dtPeopleGroupsAPI', build_people_groups_api_object() );
        }

        // Prepare duplicate fields data for general tab
        // Initialize as object (associative array) to ensure consistent structure
        $duplicate_fields_data = [
            'config' => [],
            'post_types' => [],
            'fields' => [],
            'defaults' => [],
        ];
        if ( isset( $_GET['tab'] ) && $_GET['tab'] === 'general' ) {
            // Check if we're processing a duplicate fields form submission
            // If so, read from POST data to get the latest values before they're saved
            $duplicates_config = null; // Use null to distinguish "no POST data" from "empty config"
            $has_post_data = false;

            if ( isset( $_POST['duplicate_fields_nonce'] ) &&
                 wp_verify_nonce( sanitize_key( wp_unslash( $_POST['duplicate_fields_nonce'] ) ), 'duplicate_fields' ) &&
                 isset( $_POST['duplicate_fields_data'] ) && !empty( $_POST['duplicate_fields_data'] ) ) {
                // Form is being submitted - read from POST to get the latest data
                $has_post_data = true;
                // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON data will be sanitized after decoding
                $decoded_data = json_decode( wp_unslash( $_POST['duplicate_fields_data'] ), true );
                if ( is_array( $decoded_data ) ) {
                    $duplicates_config = [];
                    $post_types = DT_Posts::get_post_types();
                    foreach ( $decoded_data as $post_type => $fields ) {
                        if ( in_array( $post_type, $post_types ) && is_array( $fields ) ) {
                            // Handle both empty arrays (user cleared all fields) and non-empty arrays
                            if ( !empty( $fields ) ) {
                                // Non-empty array - sanitize and save
                                $sanitized_fields = [];
                                foreach ( $fields as $field_key ) {
                                    $sanitized_field_key = sanitize_key( $field_key );
                                    $field_settings = DT_Posts::get_post_field_settings( $post_type );
                                    if ( isset( $field_settings[$sanitized_field_key] ) ) {
                                        $sanitized_fields[] = $sanitized_field_key;
                                    }
                                }
                                if ( !empty( $sanitized_fields ) ) {
                                    $duplicates_config[$post_type] = array_unique( $sanitized_fields );
                                }
                                // If sanitized_fields is empty (all invalid), don't add to config (use defaults)
                            }
                            // Empty array means user wants to revert to defaults - don't set the key
                        }
                    }
                }
            }

            // If we didn't get data from POST, read from database
            if ( !$has_post_data ) {
                // Clear cache to ensure we read fresh data
                wp_cache_delete( 'dt_site_options', 'options' );
                wp_cache_delete( 'alloptions', 'options' );
                $site_options = dt_get_option( 'dt_site_options' );
                $duplicates_config = $site_options['duplicates'] ?? [];
            } else {
                // We have POST data - use it (even if empty array, which means use defaults)
                // $duplicates_config is already set above
                if ( $duplicates_config === null ) {
                    $duplicates_config = [];
                }
            }

            $duplicate_fields_data['config'] = $duplicates_config;
            // Use array_values() to ensure sequential array keys (0, 1, 2...) for proper JSON encoding as array
            $duplicate_fields_data['post_types'] = array_values( DT_Posts::get_post_types() );

            // Pre-load field settings and defaults for all post types
            $fields_data = [];
            $defaults_data = [];
            foreach ( $duplicate_fields_data['post_types'] as $post_type ) {
                $field_settings = DT_Posts::get_post_field_settings( $post_type );
                $fields_data[$post_type] = $field_settings;

                // Get default fields for this post type
                $defaults_data[$post_type] = dt_get_duplicate_fields_defaults( $post_type );
            }
            $duplicate_fields_data['fields'] = $fields_data;
            $duplicate_fields_data['defaults'] = $defaults_data;
        }

        wp_localize_script(
            'dt_options_script', 'dtOptionAPI', array(
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'theme_uri' => get_template_directory_uri(),
                'images_uri' => disciple_tools()->admin_img_url,
                'available_languages' => dt_get_available_languages(),
                'site_options' => dt_get_option( 'dt_site_options' ),
                'contacts_field_settings' => DT_Posts::get_post_field_settings( 'contacts' ),
                'duplicate_fields' => $duplicate_fields_data,
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
