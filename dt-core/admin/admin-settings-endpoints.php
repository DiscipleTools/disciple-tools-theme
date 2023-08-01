<?php
/**
 * Custom endpoints file
 */

/**
 * Class Disciple_Tools_Users_Endpoints
 */
class Disciple_Tools_Admin_Settings_Endpoints {

    private $context = 'dt-admin-settings';
    private $namespace;


    /**
     * Disciple_Tools_Users_Endpoints constructor.
     */
    public function __construct() {
        $this->namespace = $this->context;
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );

        add_action( 'after_setup_theme', function (){
            $custom_post_types = get_option( 'dt_custom_post_types', [] );

            foreach ( $custom_post_types as $post_type_key => $post_type ){
                if ( class_exists( 'Disciple_Tools_Post_Type_Template' ) ){
                    new Disciple_Tools_Post_Type_Template( $post_type_key, $post_type['label_singular'] ?? $post_type_key, $post_type['label_plural'] ?? $post_type_key );
                }
            }
        }, 100 );
    }

    /**
     * Setup for API routes
     */
    public function add_api_routes() {
        register_rest_route(
            $this->namespace, '/plugin-install', [
                'methods'  => 'POST',
                'callback' => [ $this, 'plugin_install' ],
                'permission_callback' => [ $this, 'plugin_install_permission_check' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/plugin-delete', [
                'methods'  => 'POST',
                'callback' => [ $this, 'plugin_delete' ],
                'permission_callback' => [ $this, 'plugin_install_permission_check' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/plugin-activate', [
                'methods'  => 'POST',
                'callback' => [ $this, 'plugin_activate' ],
                'permission_callback' => [ $this, 'plugin_activate_permission_check' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/plugin-deactivate', [
                'methods'  => 'POST',
                'callback' => [ $this, 'plugin_deactivate' ],
                'permission_callback' => [ $this, 'plugin_activate_permission_check' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/get-post-fields', [
                'methods' => 'POST',
                'callback' => [ $this, 'get_post_fields' ],
                'permission_callback' => [ $this, 'default_permission_check' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/create-new-post-type', [
                'methods' => 'POST',
                'callback' => [ $this, 'create_new_post_type' ],
                'permission_callback' => [ $this, 'default_permission_check' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/update-post-type', [
                'methods' => 'POST',
                'callback' => [ $this, 'update_post_type' ],
                'permission_callback' => [ $this, 'default_permission_check' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/delete-post-type', [
                'methods' => 'POST',
                'callback' => [ $this, 'delete_post_type' ],
                'permission_callback' => [ $this, 'default_permission_check' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/update-roles', [
                'methods' => 'POST',
                'callback' => [ $this, 'update_roles' ],
                'permission_callback' => [ $this, 'default_permission_check' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/create-new-tile', [
                'methods' => 'POST',
                'callback' => [ $this, 'create_new_tile' ],
                'permission_callback' => [ $this, 'default_permission_check' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/get-tile', [
                'methods' => 'POST',
                'callback' => [ $this, 'get_tile' ],
                'permission_callback' => [ $this, 'default_permission_check' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/edit-tile', [
                'methods' => 'POST',
                'callback' => [ $this, 'edit_tile' ],
                'permission_callback' => [ $this, 'default_permission_check' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/delete-tile', [
                'methods' => 'POST',
                'callback' => [ $this, 'delete_tile' ],
                'permission_callback' => [ $this, 'default_permission_check' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/edit-translations', [
                'methods' => 'POST',
                'callback' => [ $this, 'edit_translations' ],
                'permission_callback' => [ $this, 'default_permission_check' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/new-field', [
                'methods' => 'POST',
                'callback' => [ $this, 'new_field' ],
                'permission_callback' => [ $this, 'default_permission_check' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/edit-field', [
                'methods' => 'POST',
                'callback' => [ $this, 'edit_field' ],
                'permission_callback' => [ $this, 'default_permission_check' ],
                ]
        );

        register_rest_route(
            $this->namespace, '/new-field-option', [
                'methods' => 'POST',
                'callback' => [ $this, 'new_field_option' ],
                'permission_callback' => [ $this, 'default_permission_check' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/edit-field-option', [
                'methods' => 'POST',
                'callback' => [ $this, 'edit_field_option' ],
                'permission_callback' => [ $this, 'default_permission_check' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/update-tiles-and-fields-order', [
                'methods' => 'POST',
                'callback' => [ $this, 'update_tiles_and_fields_order' ],
                'permission_callback' => [ $this, 'default_permission_check' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/update-field-options-order', [
                'methods' => 'POST',
                'callback' => [ $this, 'update_field_options_order' ],
                'permission_callback' => [ $this, 'default_permission_check' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/remove-custom-field-name', [
                'methods' => 'POST',
                'callback' => [ $this, 'remove_custom_field_name' ],
                'permission_callback' => [ $this, 'default_permission_check' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/remove-custom-field-option-label', [
                'methods' => 'POST',
                'callback' => [ $this, 'remove_custom_field_option_label' ],
                'permission_callback' => [ $this, 'default_permission_check' ],
            ]
        );
    }

    public static function get_post_fields() {
        $output = [];
        $post_types = DT_Posts::get_post_types();

        foreach ( $post_types as $post_type ) {
            $post_label = DT_Posts::get_label_for_post_type( $post_type );
            $output[] = [
                'label' => $post_label,
                'post_type' => $post_type,
                'post_tile' => null,
                'post_setting' => null,
            ];
            $no_tile_elements = [];

            $post_tiles = DT_Posts::get_post_tiles( $post_type );
            foreach ( $post_tiles as $tile_key => $tile_value ) {
                $output[] = [
                    'label' => $post_label . ' > ' . $tile_value['label'],
                    'post_type' => $post_type,
                    'post_tile' => $tile_key,
                    'post_setting' => null,
                ];

                $post_settings = DT_Posts::get_post_settings( $post_type, false );

                foreach ( $post_settings['fields'] as $setting_key => $setting_value ) {
                    if ( isset( $setting_value['tile'] ) && $setting_value['tile'] === $tile_key ) {
                        $output[] = [
                            'label' => $post_label . ' > ' . $tile_value['label'] . ' > ' . $setting_value['name'],
                            'post_type' => $post_type,
                            'post_tile' => $tile_key,
                            'post_setting' => $setting_key,
                        ];
                    }
                    if ( !isset( $setting_value['tile'] ) || $setting_value['tile'] === 'no_tile' ) {
                        if ( in_array( $post_label . ' > ' . $setting_value['name'], $no_tile_elements ) ) {
                            continue;
                        }
                        $setting_value['label'] = '(No Tile)';
                            $output[] = [
                                'label' => $post_label . ' > ' . $setting_value['label'] . ' > ' . $setting_value['name'],
                                'post_type' => $post_type,
                                'post_tile' => 'no-tile-hidden',
                                'post_setting' => $setting_key,
                            ];
                            $no_tile_elements[] = $post_label . ' > ' . $setting_value['name'];
                    }
                }
            }
        }
        return $output;
    }

    public static function create_new_post_type( WP_REST_Request $request ){
        $params = $request->get_params();
        $response = [];
        if ( isset( $params['key'], $params['single'], $params['plural'] ) ){
            $key = $params['key'];
            $single = $params['single'];
            $plural = $params['plural'];

            // Create new post type.
            $custom_post_types = get_option( 'dt_custom_post_types', [] );
            if ( !isset( $custom_post_types[$key] ) && !in_array( $key, DT_Posts::get_post_types() ) ){
                $custom_post_types[$key] = [
                    'label_singular' => $single,
                    'label_plural' => $plural,
                    'hidden' => false,
                    'is_custom' => true
                ];

                update_option( 'dt_custom_post_types', $custom_post_types );

                // Return successful response.
                $response = [
                    'success' => true,
                    'post_type' => $key,
                    'post_type_label' => $plural
                ];
            } else {
                $response = [
                    'success' => false,
                    'msg' => 'Unable to create '. $key .' record type; which already exists.'
                ];
            }
        } else {
            $response = [
                'success' => false,
                'msg' => 'Unable to create record type; due to missing parameters.'
            ];
        }

        return $response;
    }

    public static function update_post_type( WP_REST_Request $request ){
        $params = $request->get_params();
        $updated = false;
        if ( isset( $params['key'], $params['single'], $params['plural'], $params['displayed'] ) ){
            $post_type = $params['key'];
            $single = $params['single'];
            $plural = $params['plural'];
            $displayed = $params['displayed'];

            // Fetch existing post type settings and update.
            $custom_post_types = get_option( 'dt_custom_post_types', [] );

            // Proceed with storing updates.
            $post_type_settings = !empty( $custom_post_types[$post_type] ) ? $custom_post_types[$post_type] : [];
            $post_type_settings['label_singular'] = $single;
            $post_type_settings['label_plural'] = $plural;
            $post_type_settings['hidden'] = ! $displayed;
            $custom_post_types[$post_type] = $post_type_settings;

            update_option( 'dt_custom_post_types', $custom_post_types );

            $updated = true;
        }

        return [
            'updated' => $updated
        ];
    }

    public static function delete_post_type( WP_REST_Request $request ){
        $params = $request->get_params();
        $deleted = false;
        $deleted_msg = '';
        if ( isset( $params['key'] ) ){
            $post_type = $params['key'];

            // Ensure current user has the ability to delete post type;
            if ( !current_user_can( 'delete_any_' . $post_type ) ){
                return [
                    'deleted' => false,
                    'msg' => 'Current user unable to delete '. $post_type .' record type.'
                ];
            }

            // Only process custom post types.
            $custom_post_types = get_option( 'dt_custom_post_types', [] );
            if ( isset( $custom_post_types[$post_type] ) ){

                // Remove custom post type.
                unset( $custom_post_types[$post_type] );
                update_option( 'dt_custom_post_types', $custom_post_types );

                // Remove field settings.
                $field_customizations = dt_get_option( 'dt_field_customizations' );
                if ( isset( $field_customizations[$post_type] ) ){
                    unset( $field_customizations[$post_type] );

                    update_option( 'dt_field_customizations', $field_customizations );
                    wp_cache_delete( $post_type . '_field_settings' );
                }

                // If specified, proceed with deletion of all associated records.
                if ( isset( $params['delete_all_records'] ) && $params['delete_all_records'] ){
                    global $wpdb;
                    $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->dt_notifications WHERE post_id IN ( SELECT ID FROM $wpdb->posts WHERE post_type = %s )", $post_type ) );
                    $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->dt_share WHERE post_id IN ( SELECT ID FROM $wpdb->posts WHERE post_type = %s )", $post_type ) );
                    $wpdb->query( $wpdb->prepare( "DELETE p, pm FROM $wpdb->p2p p LEFT JOIN $wpdb->p2pmeta pm ON pm.p2p_id = p.p2p_id WHERE ( p.p2p_to IN ( SELECT ID FROM $wpdb->posts WHERE post_type = %s ) OR p.p2p_from IN ( SELECT ID FROM $wpdb->posts WHERE post_type = %s ) )", $post_type, $post_type ) );
                    $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE post_id IN ( SELECT ID FROM $wpdb->posts WHERE post_type = %s )", $post_type ) );
                    $wpdb->query( $wpdb->prepare( "DELETE c, cm FROM $wpdb->comments c LEFT JOIN $wpdb->commentmeta cm ON cm.comment_id = c.comment_ID WHERE c.comment_post_ID IN ( SELECT ID FROM $wpdb->posts WHERE post_type = %s )", $post_type ) );
                    $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->dt_activity_log WHERE object_id IN ( SELECT ID FROM $wpdb->posts WHERE post_type = %s )", $post_type ) );
                    $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->dt_post_user_meta WHERE post_id IN ( SELECT ID FROM $wpdb->posts WHERE post_type = %s )", $post_type ) );
                    $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->posts WHERE post_type = %s", $post_type ) );
                }

                $deleted = true;
                $deleted_msg = 'Deleted post type ' . $post_type;
            } else {
                $deleted_msg = $post_type . ' does not appear to be a custom record type.';
            }
        } else {
            $deleted_msg = 'Unable to delete record type; due to missing parameters.';
        }

        return [
            'deleted' => $deleted,
            'msg' => $deleted_msg
        ];
    }

    public static function update_roles( WP_REST_Request $request ){
        $params = $request->get_params();
        $updated = false;

        if ( isset( $params['roles'] ) && current_user_can( 'edit_roles' ) ){
            $roles = $params['roles'];

            // Fetch existing roles and permissions.
            $existing_roles_permissions = Disciple_Tools_Roles::get_dt_roles_and_permissions();

            // Fetch existing custom roles.
            $existing_custom_roles = get_option( 'dt_custom_roles', [] );

            // Proceed with storing updates, accordingly overwriting existing roles.
            foreach ( $roles as $role => $capabilities ){

                // Skip administrator roles, whilst not currently an administrator.
                if ( $role == 'administrator' && !dt_is_administrator() ){
                    continue;
                }

                // Sync updated custom role with existing settings.
                $updated_custom_role = [];
                $existing_role = $existing_roles_permissions[$role] ?? [];
                $updated_custom_role['label'] = isset( $existing_roles_permissions[$role] ) ? ( $existing_role['label'] ?? '' ) : $role;
                $updated_custom_role['description'] = isset( $existing_roles_permissions[$role] ) ? ( $existing_role['description'] ?? '' ) : '';
                $updated_custom_role['slug'] = $role;
                $updated_custom_role['is_editable'] = isset( $existing_roles_permissions[$role] ) ? ( $existing_role['is_editable'] ?? false ) : true;
                $updated_custom_role['custom'] = isset( $existing_roles_permissions[$role] ) ? ( $existing_role['custom'] ?? false ) : true;

                // Identify capabilities selection states.
                $updated_capabilities = [];
                foreach ( $capabilities ?? [] as $capability ){
                    $updated_capabilities[$capability['key']] = $capability['enabled'];
                }

                // Capture capability updates.
                $updated_custom_role['capabilities'] = $updated_capabilities;

                // Save updates to custom roles option.
                $existing_custom_roles[$role] = $updated_custom_role;
            }

            // Persist updated custom roles.
            update_option( 'dt_custom_roles', $existing_custom_roles );

            $updated = true;
        }

        return [
            'updated' => $updated
        ];
    }

    public static function create_new_tile( WP_REST_Request $request ) {
        $post_submission = $request->get_params();
        if ( isset( $post_submission['new_tile_name'], $post_submission['post_type'] ) ) {
            $post_type = sanitize_text_field( wp_unslash( $post_submission['post_type'] ) );
            $new_tile_name = sanitize_text_field( wp_unslash( $post_submission['new_tile_name'] ) );
            $tile_key = dt_create_field_key( $new_tile_name );
            $tile_options = dt_get_option( 'dt_custom_tiles' );
            $post_tiles = DT_Posts::get_post_tiles( $post_type );

            if ( in_array( $tile_key, array_keys( $post_tiles ) ) ) {
                return new WP_Error( __FUNCTION__, 'Tile already exists', [ 'status' => 400 ] );
            }

            if ( !isset( $tile_options[$post_type] ) ) {
                $tile_options[$post_type] = [];
            }

            $tile_options[$post_type][$tile_key] = [ 'label' => $new_tile_name ];

            $new_tile_description = null;
            if ( isset( $post_submission['new_tile_description'] ) ) {
                $new_tile_description = sanitize_text_field( wp_unslash( $post_submission['new_tile_description'] ) );
                $tile_options[$post_type][$tile_key]['description'] = $new_tile_description;
            }

            update_option( 'dt_custom_tiles', $tile_options );
            $created_tile = [
                'post_type' => $post_type,
                'key' => $tile_key,
                'label' => $new_tile_name,
                'description' => $new_tile_description,
            ];
            return $created_tile;
        }
        return false;
    }

    public static function get_tile( WP_REST_Request $request ) {
        $params = $request->get_params();
        $post_type = sanitize_text_field( wp_unslash( $params['post_type'] ) );
        $tile_key = sanitize_text_field( wp_unslash( $params['tile_key'] ) );
        $tile_options = DT_Posts::get_post_tiles( $post_type, false );
        return $tile_options[$tile_key];
    }

    public static function edit_tile( WP_REST_Request $request ) {
        $post_submission = $request->get_params();

        $post_type = $post_submission['post_type'];
        $tile_options = dt_get_option( 'dt_custom_tiles' );
        $tile_key = $post_submission['tile_key'];

        if ( !isset( $tile_options[$post_type][$tile_key] ) ) {
            $tile_options[$post_type][$tile_key] = [];
        }

        $custom_tile = $tile_options[$post_type][$tile_key];

        if ( isset( $post_submission['tile_label'] ) && $post_submission['tile_label'] != ( $custom_tile['label'] ?? $tile_key ) ) {
            $custom_tile['label'] = $post_submission['tile_label'];
        }

        if ( isset( $post_submission['tile_description'] ) && $post_submission['tile_description'] != ( $custom_tile['description'] ?? $tile_key ) ) {
            $custom_tile['description'] = $post_submission['tile_description'];
        }

        $custom_tile['hidden'] = false;
        if ( isset( $post_submission['hide_tile'] ) ) {
            if ( $post_submission['hide_tile'] ) {
                $custom_tile['hidden'] = true;
            }
        }

        if ( isset( $post_submission['restore_tile'] ) ) {
            $custom_tile['hidden'] = false;
        }

        if ( isset( $post_submission['tile_description'] ) && $post_submission['tile_description'] != ( $custom_tile['description'] ?? '' ) ) {
            $custom_tile['description'] = $post_submission['tile_description'];
        }

        if ( !empty( $custom_tile ) ){
            $tile_options[$post_type][$tile_key] = $custom_tile;
        }
        update_option( 'dt_custom_tiles', $tile_options );
        return $tile_options[$post_type][$tile_key];
    }

    public static function delete_tile( WP_REST_Request $request ) {
        $params = $request->get_params();
        $post_type = sanitize_text_field( wp_unslash( $params['post_type'] ) );
        $tile_key = sanitize_text_field( wp_unslash( $params['tile_key'] ) );

        if ( self::is_custom_tile( $post_type, $tile_key ) === false ) {
            $tile_options = dt_get_option( 'dt_custom_tiles' );
            unset( $tile_options[$post_type][$tile_key] );
            update_option( 'dt_custom_tiles', $tile_options );

            // Move all tile fields to No Tile
            $field_customizations = dt_get_option( 'dt_field_customizations' );
            foreach ( $field_customizations[$post_type] as  $field_key => $field_settings ) {
                if ( $field_customizations[$post_type][$field_key]['tile'] === $tile_key ) {
                    $field_customizations[$post_type][$field_key]['tile'] = 'no_tile';
                }
            }
            update_option( 'dt_field_customizations', $field_customizations );
            return true;
        }
        return false;
    }

    public static function is_custom_tile( $post_type, $tile_key ) {
        $default_fields = apply_filters( 'dt_custom_fields_settings', [], $post_type );

        foreach ( $default_fields as $fields ) {
            foreach ( $fields as $field_key => $field_value ) {
                if ( $field_key === 'tile' && $field_value === $tile_key ) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function edit_translations( WP_REST_Request $request ) {
        $post_submission = $request->get_params();
        if ( isset( $post_submission['translation_type'] ) && isset( $post_submission['post_type'] ) && isset( $post_submission['tile_key'] ) && isset( $post_submission['translations'] ) ) {
            $post_type = sanitize_text_field( wp_unslash( $post_submission['post_type'] ) );
            $tile_key = sanitize_text_field( wp_unslash( $post_submission['tile_key'] ) );
            $translations = json_decode( $post_submission['translations'], true );

            $tile_options = dt_get_option( 'dt_custom_tiles' );
            $field_customizations = dt_get_option( 'dt_field_customizations' );

            switch ( $post_submission['translation_type'] ) {
                case 'tile-label':
                    $translated_element = $tile_options[$post_type][$tile_key];
                    break;

                case 'tile-description':
                    $translated_element = $tile_options[$post_type][$tile_key];
                    break;

                case 'field-label':
                    if ( !isset( $post_submission['field_key'] ) ) {
                        return false;
                    }
                    $field_key = $post_submission['field_key'];
                    $translated_element = $field_customizations[$post_type][$field_key];
                    break;

                case 'field-description':
                    if ( !isset( $post_submission['field_key'] ) ) {
                        return false;
                    }
                    $field_key = $post_submission['field_key'];
                    $translated_element = $field_customizations[$post_type][$field_key];
                        break;

                case 'field-option-label':
                    if ( !isset( $post_submission['field_key'] ) || !isset( $post_submission['field_option_key'] ) ) {
                        return false;
                    }
                    $field_key = $post_submission['field_key'];
                    $field_option_key = $post_submission['field_option_key'];
                    $translated_element = $field_customizations[$post_type][$field_key]['default'][$field_option_key];
                    break;

                case 'field-option-description':
                    if ( !isset( $post_submission['field_key'] ) || !isset( $post_submission['field_option_key'] ) ) {
                        return false;
                    }
                    $field_key = $post_submission['field_key'];
                    $field_option_key = $post_submission['field_option_key'];
                    $translated_element = $field_customizations[$post_type][$field_key]['default'][$field_option_key];
                    break;
            }

            // Check if translation is a description
            $translations_element_key = 'translations';
            if ( strpos( $post_submission['translation_type'], 'description' ) ) {
                $translations_element_key = 'description_translations';
            }

            foreach ( $translations as $lang_key => $translation_val ) {
                if ( $lang_key !== '' || !is_null( $lang_key ) ) {
                    $translated_element[$translations_element_key][$lang_key] = $translation_val;
                }
            }

            switch ( $post_submission['translation_type'] ) {
                case 'tile-label':
                    $tile_options[$post_type][$tile_key] = $translated_element;
                    update_option( 'dt_custom_tiles', $tile_options );
                    break;

                case 'tile-description':
                    $tile_options[$post_type][$tile_key] = $translated_element;
                    update_option( 'dt_custom_tiles', $tile_options );
                    break;

                case 'field-label':
                    $field_customizations[$post_type][$field_key] = $translated_element;
                    update_option( 'dt_field_customizations', $field_customizations );
                    break;

                case 'field-description':
                    $field_customizations[$post_type][$field_key] = $translated_element;
                    update_option( 'dt_field_customizations', $field_customizations );
                    break;

                case 'field-option-label':
                    $field_customizations[$post_type][$field_key]['default'][$field_option_key] = $translated_element;
                    update_option( 'dt_field_customizations', $field_customizations );
                    break;

                case 'field-option-description':
                    $field_customizations[$post_type][$field_key]['default'][$field_option_key] = $translated_element;
                    update_option( 'dt_field_customizations', $field_customizations );
                    break;
            }
            return $translations;
        }
        return false;
    }

    public function plugin_install( WP_REST_Request $request ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        $params = $request->get_params();
        $download_url = sanitize_text_field( wp_unslash( $params['download_url'] ) );
        set_time_limit( 0 );
        $folder_name = explode( '/', $download_url );
        $folder_name = get_home_path() . 'wp-content/plugins/' . $folder_name[4] . '.zip';
        if ( $folder_name != '' ) {
            //download the zip file to plugins
            file_put_contents( $folder_name, file_get_contents( $download_url ) );
            // get the absolute path to $file
            $folder_name = realpath( $folder_name );
            //unzip
            WP_Filesystem();
            $unzip = unzip_file( $folder_name, realpath( get_home_path() . 'wp-content/plugins/' ) );
            //remove the file
            unlink( $folder_name );
        }
        return true;
    }

    public function plugin_install_permission_check() {
        if ( !current_user_can( 'manage_dt' ) || !current_user_can( 'install_plugins' ) ) {
            return new WP_Error( 'forbidden', 'You are not allowed to do that.', array( 'status' => 403 ) );
        }
        return true;
    }
    public function plugin_activate_permission_check() {
        if ( !current_user_can( 'manage_dt' ) ) {
            return new WP_Error( 'forbidden', 'You are not allowed to do that.', array( 'status' => 403 ) );
        }
        return true;
    }

    public function plugin_delete( WP_REST_Request $request ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        $params = $request->get_params();
        $plugin_slug = sanitize_text_field( wp_unslash( $params['plugin_slug'] ) );
        $installed_plugins = get_plugins();
        foreach ( $installed_plugins as $index => $plugin ) {
            if ( $plugin['TextDomain'] === $plugin_slug ) {
                delete_plugins( [ $index ] );
                return true;
            }
        }
        return false;
    }

    public static function new_field( WP_REST_Request $request ) {
        $post_submission = $request->get_params();
        if ( isset( $post_submission['new_field_name'], $post_submission['new_field_type'], $post_submission['post_type'] ) ){
            $post_type = $post_submission['post_type'];
            $field_type = $post_submission['new_field_type'];
            $tile_key = $post_submission['tile_key'] ?? '';
            $field_key = dt_create_field_key( $post_submission['new_field_name'] );
            $custom_field_options = dt_get_option( 'dt_field_customizations' );

            if ( !$field_key ){
                return false;
            }

            // Field privacy
            if ( isset( $post_submission['new_field_private'] ) && $post_submission['new_field_private'] ) {
                $field_private = true;
            } else {
                $field_private = false;
            }

            $post_fields = DT_Posts::get_post_field_settings( $post_type, false, true );
            if ( isset( $post_fields[ $field_key ] ) ){
                return new WP_Error( __METHOD__, 'Field already exists', [ 'status' => 400 ] );
            }
            $new_field = [];
            if ( $field_type === 'key_select' ){
                $new_field = [
                    'name' => $post_submission['new_field_name'],
                    'default' => [],
                    'type' => 'key_select',
                    'tile' => $tile_key,
                    'customizable' => 'all',
                    'private' => $field_private
                ];
            } elseif ( $field_type === 'multi_select' ){
                $new_field = [
                    'name' => $post_submission['new_field_name'],
                    'default' => [],
                    'type' => 'multi_select',
                    'tile' => $tile_key,
                    'customizable' => 'all',
                    'private' => $field_private,
                ];
            } elseif ( $field_type === 'tags' ){
                $new_field = [
                    'name' => $post_submission['new_field_name'],
                    'default' => [],
                    'type' => 'tags',
                    'tile' => $tile_key,
                    'customizable' => 'all',
                    'private' => $field_private
                ];
            } elseif ( $field_type === 'date' ){
                $new_field = [
                    'name'        => $post_submission['new_field_name'],
                    'type'        => 'date',
                    'default'     => '',
                    'tile'     => $tile_key,
                    'customizable' => 'all',
                    'private' => $field_private
                ];
            } elseif ( $field_type === 'text' ){
                $new_field = [
                    'name'        => $post_submission['new_field_name'],
                    'type'        => 'text',
                    'default'     => '',
                    'tile'     => $tile_key,
                    'customizable' => 'all',
                    'private' => $field_private
                ];
            } elseif ( $field_type === 'textarea' ){
                $new_field = [
                    'name'        => $post_submission['new_field_name'],
                    'type'        => 'textarea',
                    'default'     => '',
                    'tile'     => $tile_key,
                    'customizable' => 'all',
                    'private' => $field_private
                ];
            } elseif ( $field_type === 'number' ){
                $new_field = [
                    'name'        => $post_submission['new_field_name'],
                    'type'        => 'number',
                    'default'     => '',
                    'tile'     => $tile_key,
                    'customizable' => 'all',
                    'private' => $field_private
                ];
            } elseif ( $field_type === 'link' ) {
                $new_field = [
                    'name'        => $post_submission['new_field_name'],
                    'type'        => 'link',
                    'default'     => [],
                    'tile'     => $tile_key,
                    'customizable' => 'all',
                    'private' => $field_private
                ];
            } elseif ( $field_type === 'connection' ){
                if ( !$post_submission['connection_target'] ){
                    return new WP_Error( __METHOD__, 'Please select a connection target', [ 'status' => 400 ] );
                }
                $p2p_key = $post_type . '_to_' . $post_submission['connection_target'];
                if ( p2p_type( $p2p_key ) !== false ){
                    $p2p_key = dt_create_field_key( $p2p_key, true );
                }

                // Connection field to the same post type
                if ( $post_type === $post_submission['connection_target'] ){
                    //default direction to "any". If not multidirectional, then from
                    $direction = 'any';
                    if ( $post_submission['multidirectional'] != 1 ) {
                        $direction = 'from';
                    }
                    $custom_field_options[$post_type][$field_key] = [
                        'name'        => $post_submission['new_field_name'],
                        'type'        => 'connection',
                        'post_type' => $post_submission['connection_target'],
                        'p2p_direction' => $direction,
                        'p2p_key' => $p2p_key,
                        'tile'     => $tile_key,
                        'customizable' => 'all',
                    ];

                    // If not multidirectional, create the reverse direction field
                    if ( $post_submission['multidirectional'] != 1 ){
                        $reverse_name = $post_submission['reverse_connection_name'] ?? $post_submission['new_field_name'];
                        $custom_field_options[$post_type][$field_key . '_reverse']  = [
                            'name'        => $reverse_name,
                            'type'        => 'connection',
                            'post_type' => $post_type,
                            'p2p_direction' => 'to',
                            'p2p_key' => $p2p_key,
                            'tile'     => 'other',
                            'customizable' => 'all',
                            'hidden' => isset( $post_submission['disable_reverse_connection'] )
                        ];
                    }
                } else {
                    $direction = 'from';
                    $custom_field_options[$post_type][$field_key] = [
                        'name'        => $post_submission['new_field_name'],
                        'type'        => 'connection',
                        'post_type' => $post_submission['connection_target'],
                        'p2p_direction' => $direction,
                        'p2p_key' => $p2p_key,
                        'tile'     => $tile_key,
                        'customizable' => 'all',
                    ];
                    // Create the reverse fields on the connection post type
                    $reverse_name = $post_submission['other_field_name'] ?? $post_submission['new_field_name'];
                    $custom_field_options[$post_submission['connection_target']][$field_key]  = [
                        'name'        => $reverse_name,
                        'type'        => 'connection',
                        'post_type' => $post_type,
                        'p2p_direction' => 'to',
                        'p2p_key' => $p2p_key,
                        'tile'     => 'other',
                        'customizable' => 'all',
                        'hidden' => isset( $post_submission['disable_other_post_type_field'] )
                    ];
                }
            }
            if ( !empty( $new_field ) ){
                $custom_field_options[$post_type][$field_key] = $new_field;
            }
            update_option( 'dt_field_customizations', $custom_field_options );
            wp_cache_delete( $post_type . '_field_settings' );
            $custom_field_options[$post_type][$field_key]['key'] = $field_key; // Key added for reference in js callback function
            return $custom_field_options[$post_type][$field_key];
        }
        return false;
    }

    public static function new_field_option( WP_REST_Request $request ) {
        $post_submission = $request->get_params();
        if ( isset( $post_submission['post_type'], $post_submission['tile_key'], $post_submission['field_key'], $post_submission['field_option_name'] ) ) {
            $field_key = $post_submission['field_key'];
            $post_type = $post_submission['post_type'];
            $new_field_option_name = $post_submission['field_option_name'];
            $new_field_option_key = dt_create_field_key( $new_field_option_name );
            $new_field_option_description = $post_submission['field_option_description'];
            $field_option_icon = $post_submission['field_option_icon'];

            $custom_field_options = dt_get_option( 'dt_field_customizations' );
            $custom_field_options[$post_type][$field_key]['default'][$new_field_option_key] = [
                    'label' => $new_field_option_name,
                    'description' => $new_field_option_description,
                ];

            if ( $field_option_icon ){
                $field_option_icon = strtolower( trim( $field_option_icon ) );
                $icon_key = ( strpos( $field_option_icon, 'mdi' ) !== 0 ) ? 'icon' : 'font-icon';
                $custom_field_options[$post_type][$field_key]['default'][$new_field_option_key][$icon_key] = $field_option_icon;

                if ( $icon_key == 'font-icon' ){
                    $custom_field_options[$post_type][$field_key]['default'][$new_field_option_key]['icon'] = '';
                }
            }

            update_option( 'dt_field_customizations', $custom_field_options );
            return $new_field_option_key;
        }
        return false;
    }

    public static function edit_field_option( WP_REST_Request $request ) {
        $post_submission = $request->get_params();
        if ( isset( $post_submission['post_type'], $post_submission['tile_key'], $post_submission['field_key'], $post_submission['field_option_key'], $post_submission['new_field_option_label'] ) ) {
            $field_key = $post_submission['field_key'];
            $post_type = $post_submission['post_type'];
            $field_option_key = $post_submission['field_option_key'];
            $new_field_option_label = $post_submission['new_field_option_label'];
            $new_field_option_description = $post_submission['new_field_option_description'];
            $field_option_icon = $post_submission['field_option_icon'];

            $field_customizations = dt_get_option( 'dt_field_customizations' );
            $custom_field_option = [
                'label' => $new_field_option_label,
                'description' => $new_field_option_description,
            ];

            if ( $field_option_icon && strpos( $field_option_icon, 'undefined' ) === false ){
                $field_option_icon = strtolower( trim( $field_option_icon ) );
                $icon_key = ( strpos( $field_option_icon, 'mdi' ) !== 0 ) ? 'icon' : 'font-icon';
                $custom_field_option[$icon_key] = $field_option_icon;

                if ( $icon_key == 'font-icon' ){
                    $custom_field_option['icon'] = '';
                }
            }

            // Create default_name to store the default field option label if it changed
            if ( self::default_field_option_label_changed( $post_type, $field_key, $field_option_key, $custom_field_option['label'] ) ) {
                $custom_field_option['default_name'] = self::get_default_field_option_label( $post_type, $field_key, $field_option_key );
            }

            $field_customizations[$post_type][$field_key]['default'][$field_option_key] = $custom_field_option;
            update_option( 'dt_field_customizations', $field_customizations );
            return $custom_field_option;
        }
    }

    public function plugin_activate( WP_REST_Request $request ) {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        $params = $request->get_params();
        $plugin_slug = sanitize_text_field( wp_unslash( $params['plugin_slug'] ) );
        $installed_plugins = get_plugins();
        foreach ( $installed_plugins as $index => $plugin ) {
            if ( $plugin['TextDomain'] === $plugin_slug ) {
                activate_plugin( $index );
                return true;
            }
        }
        return false;
    }

    public static function default_field_name_changed( $post_type, $field_key, $custom_name ) {
        $base_fields = Disciple_Tools_Post_Type_Template::get_base_post_type_fields();
        $default_fields = apply_filters( 'dt_custom_fields_settings', [], $post_type );
        $all_non_custom_fields = array_merge( $base_fields, $default_fields );
        if ( ( $all_non_custom_fields[$field_key]['name'] ?? '' ) === trim( $custom_name ) ) {
            return false;
        }
        return true;
    }
    public static function default_field_option_label_changed( $post_type, $field_key, $field_option_key, $custom_label ) {
        $base_fields = Disciple_Tools_Post_Type_Template::get_base_post_type_fields();
        $default_fields = apply_filters( 'dt_custom_fields_settings', [], $post_type );
        $all_non_custom_fields = array_merge( $base_fields, $default_fields );
        if ( $all_non_custom_fields[$field_key]['default'][$field_option_key]['label'] ?? '' == trim( $custom_label ) ) {
            return false;
        }
        return true;
    }

    public static function get_default_field_name( $post_type, $field_key ) {
        $base_fields = Disciple_Tools_Post_Type_Template::get_base_post_type_fields();
        $default_fields = apply_filters( 'dt_custom_fields_settings', [], $post_type );
        $all_non_custom_fields = array_merge( $base_fields, $default_fields );
        $default_name = $all_non_custom_fields[$field_key]['name'] ?? '';
        return $default_name;
    }

    public static function get_default_field_option_label( $post_type, $field_key, $field_option_key ) {
        $base_fields = Disciple_Tools_Post_Type_Template::get_base_post_type_fields();
        $default_fields = apply_filters( 'dt_custom_fields_settings', [], $post_type );
        $all_non_custom_fields = array_merge( $base_fields, $default_fields );
        $default_name = $all_non_custom_fields[$field_key]['default'][$field_option_key]['label'] ?? '';
        return $default_name;
    }

    public static function update_tiles_and_fields_order( WP_REST_Request $request ) {
        $post_submission = $request->get_params();
        $post_type = sanitize_text_field( wp_unslash( $post_submission['post_type'] ) );
        $dt_custom_tiles_and_fields_ordered = dt_recursive_sanitize_array( $post_submission['dt_custom_tiles_and_fields_ordered'] );
        $tile_options = dt_get_option( 'dt_custom_tiles' );

        if ( !isset( $tile_options[$post_type] ) ) {
            $tile_options[$post_type] = [];
        }

        $ordered_tile_and_fields_count = count( $dt_custom_tiles_and_fields_ordered );
        for ( $i =0; $i <$ordered_tile_and_fields_count; $i++ ) {
            foreach ( $dt_custom_tiles_and_fields_ordered as $index => $tile_key ) {
                $tile_options[$post_type][$index]['label'] = '';
                if ( !isset( $tile_options[$post_type][$index] ) ) {
                    $tile_options[$post_type][$index] = [];
                }
                $tile_options[$post_type][$index]['tile_priority'] = ( $i + 1 ) * 10;
            }
        }

        $tile_options[$post_type] = $dt_custom_tiles_and_fields_ordered;
        update_option( 'dt_custom_tiles', $tile_options );
        return $tile_options;
    }

    public static function update_field_options_order( WP_REST_Request $request ) {
        $post_submission = $request->get_params();
        $post_type = sanitize_text_field( wp_unslash( $post_submission['post_type'] ) );
        $field_key = sanitize_text_field( wp_unslash( $post_submission['field_key'] ) );

        $field_customizations = dt_get_option( 'dt_field_customizations' );
        $custom_field = $field_customizations[$post_type][$field_key] ?? [];

        $sortable_field_options_ordering = dt_recursive_sanitize_array( $post_submission['sortable_field_options_ordering'] );
        if ( !empty( $sortable_field_options_ordering ) ) {
            $custom_field['order'] = $sortable_field_options_ordering;
        }

        $field_customizations[$post_type][$field_key] = $custom_field;
        update_option( 'dt_field_customizations', $field_customizations );
        wp_cache_delete( $post_type . '_field_settings' );
        return $sortable_field_options_ordering;
    }

    public static function remove_custom_field_name( WP_REST_Request $request ) {
        $post_submission = $request->get_params();
        $post_type = sanitize_text_field( wp_unslash( $post_submission['post_type'] ) );
        $field_key = sanitize_text_field( wp_unslash( $post_submission['field_key'] ) );

        if ( !empty( $post_type ) && !empty( $field_key ) ) {
            $field_customizations = dt_get_option( 'dt_field_customizations' );
            $default_name = $field_customizations[$post_type][$field_key]['default_name'];
            $field_customizations[$post_type][$field_key]['name'] = $default_name;
            unset( $field_customizations[$post_type][$field_key]['default_name'] );
            update_option( 'dt_field_customizations', $field_customizations );
            wp_cache_delete( $post_type . '_field_settings' );
            return $default_name;
        }
        return false;
    }

    public static function remove_custom_field_option_label( WP_REST_Request $request ) {
        $post_submission = $request->get_params();
        $post_type = sanitize_text_field( wp_unslash( $post_submission['post_type'] ) );
        $field_key = sanitize_text_field( wp_unslash( $post_submission['field_key'] ) );
        $field_option_key = sanitize_text_field( wp_unslash( $post_submission['field_option_key'] ) );

        if ( !empty( $post_type ) && !empty( $field_key ) ) {
            $field_customizations = dt_get_option( 'dt_field_customizations' );
            $default_label = $field_customizations[$post_type][$field_key]['default'][$field_option_key]['default_name'];
            $field_customizations[$post_type][$field_key]['default'][$field_option_key]['label'] = $default_label;
            unset( $field_customizations[$post_type][$field_key]['default'][$field_option_key]['default_name'] );
            update_option( 'dt_field_customizations', $field_customizations );
            wp_cache_delete( $post_type . '_field_settings' );
            return $default_label;
        }
        return false;
    }

    public static function edit_field( WP_REST_Request $request ) {
        $post_submission = $request->get_params();

        if ( !isset( $post_submission['post_type'], $post_submission['field_key'] ) ) {
            return false;
        }

        $post_type = $post_submission['post_type'];
        $field_key = $post_submission['field_key'];
        $field_icon = $post_submission['field_icon'];

        $post_fields = DT_Posts::get_post_field_settings( $post_type, false, true );
        $field_customizations = dt_get_option( 'dt_field_customizations' );

        if ( isset( $post_fields[$field_key] ) ) {
            if ( !isset( $field_customizations[$post_type][$field_key] ) ){
                $field_customizations[$post_type][$field_key] = [];
            }
            $custom_field = $field_customizations[$post_type][$field_key];

            // Update name
            if ( isset( $post_submission['custom_name'] ) && !empty( $post_submission['custom_name'] ) ) {
                $custom_field['name'] = $post_submission['custom_name'];
                $custom_field['default_name'] = self::get_default_field_name( $post_type, $field_key );
            }

            // Create default_name to store the default field name if it changed
            if ( self::default_field_name_changed( $post_type, $field_key, $custom_field['name'] ) === true ) {
                $custom_field['default_name'] = self::get_default_field_name( $post_type, $field_key );
            }

            // Field privacy
            $custom_field['private'] = false;
            if ( isset( $post_submission['field_private'] ) && $post_submission['field_private'] ) {
                $custom_field['private'] = true;
            }

            // Field tile
            if ( isset( $post_submission['tile_select'] ) ) {
                $custom_field['tile'] = $post_submission['tile_select'];
            }

            // Field description
            if ( isset( $post_submission['field_description'] ) && $post_submission['field_description'] != ( $custom_field['description'] ?? '' ) ){
                $custom_field['description'] = $post_submission['field_description'];
            }

            // Field icon
            if ( isset( $post_submission['field_icon'] ) && strpos( $post_submission['field_icon'], 'undefined' ) === false ) {
                $field_icon                           = $post_submission['field_icon'];
                $field_icon_key                       = ( ! empty( $field_icon ) && strpos( $field_icon, 'mdi mdi-' ) === 0 ) ? 'font-icon' : 'icon';
                $field_null_icon_key                  = ( $field_icon_key === 'font-icon' ) ? 'icon' : 'font-icon';
                $custom_field[ $field_icon_key ]      = $field_icon;
                $custom_field[ $field_null_icon_key ] = null;
            }

            $field_customizations[$post_type][$field_key] = $custom_field;
            update_option( 'dt_field_customizations', $field_customizations );
            wp_cache_delete( $post_type . '_field_settings' );
            return $custom_field;
        }
        return new WP_Error( 'error', 'Something went wrong', [ 'status' => 500 ] );
    }

    public function default_permission_check() {
        if ( ! current_user_can( 'manage_dt' ) ) {
            return new WP_Error( 'forbidden', 'You are not allowed to do that.', array( 'status' => 403 ) );
        }
        return true;
    }

    public function plugin_deactivate( WP_REST_Request $request ) {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        $params = $request->get_params();
        $plugin_slug = sanitize_text_field( wp_unslash( $params['plugin_slug'] ) );
        $installed_plugins = get_plugins();
        foreach ( $installed_plugins as $index => $plugin ) {
            if ( $plugin['TextDomain'] === $plugin_slug ) {
                deactivate_plugins( $index );
                return true;
            }
        }
        return false;
    }
}
