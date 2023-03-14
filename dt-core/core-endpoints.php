<?php
/**
 * Custom endpoints file
 */

/**
 * Class Disciple_Tools_Users_Endpoints
 */
class Disciple_Tools_Core_Endpoints {

    private $version = 1;
    private $context = 'dt-core';
    private $namespace;
    private $public_namespace = 'dt-public/dt-core/v1';

    /**
     * Disciple_Tools_Users_Endpoints constructor.
     */
    public function __construct() {
        $this->namespace = $this->context . '/v' . intval( $this->version );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    /**
     * Setup for API routes
     */
    public function add_api_routes() {
        register_rest_route(
            $this->namespace, '/settings', [
                'methods'  => 'GET',
                'callback' => [ $this, 'get_settings' ],
                'permission_callback' => '__return_true',
            ]
        );
        register_rest_route(
            $this->public_namespace, '/settings', [
                'methods'  => 'GET',
                'callback' => [ $this, 'get_public_settings' ],
                'permission_callback' => '__return_true',
            ]
        );

        register_rest_route(
            $this->namespace, '/activity', [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'log_activity' ],
                'permission_callback' => '__return_true',
            ]
        );

        register_rest_route(
            $this->namespace, '/plugin-install', [
                'methods'  => 'POST',
                'callback' => [ $this, 'plugin_install' ],
                'permission_callback' => [ $this, 'plugin_permission_check' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/plugin-delete', [
                'methods'  => 'POST',
                'callback' => [ $this, 'plugin_delete' ],
                'permission_callback' => [ $this, 'plugin_permission_check' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/plugin-activate', [
                'methods'  => 'POST',
                'callback' => [ $this, 'plugin_activate' ],
                'permission_callback' => [ $this, 'plugin_permission_check' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/plugin-deactivate', [
                'methods'  => 'POST',
                'callback' => [ $this, 'plugin_deactivate' ],
                'permission_callback' => [ $this, 'plugin_permission_check' ],
            ]
        );

        register_rest_route(
            $this->public_namespace, '/get-post-fields', [
                'methods' => 'POST',
                'callback' => [ $this, 'get_post_fields' ],
                'permission_callback' => '__return_true',
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
                'permission_callback' => '__return_true',
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
    }

    /**
     * These are settings available to any logged in user.
     */
    public static function get_settings() {
        $user = wp_get_current_user();
        if ( !$user ){
            return new WP_Error( 'get_settings', 'Something went wrong. Are you a user?', [ 'status' => 400 ] );
        }
        $available_translations = dt_get_available_languages();
        $post_types = DT_Posts::get_post_types();
        $post_types_settings = [];
        foreach ( $post_types as $post_type ){
            $post_types_settings[$post_type] = DT_Posts::get_post_settings( $post_type );
        }
        return [
            'available_translations' => $available_translations,
            'post_types' => $post_types_settings,
            'plugins' => apply_filters( 'dt_plugins', [] ),
        ];
    }

    /**
     * Expose settings publicly to world.
     * To not use unless it is for setting that must be accessed
     * before the user is logged in.
     */
    public function get_public_settings(){
        $public_settings = [
            'url' => get_home_url(),
            'login_settings' => [],
        ];
        $public_settings = apply_filters( 'dt_core_public_endpoint_settings', $public_settings );
        return $public_settings;
    }


    /**
     * Log activity to the dt_activity_log
     * @param WP_REST_Request $request
     * @return array|WP_Error
     */
    public function log_activity( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( !isset( $params['action'] ) ) {
            return new WP_Error( 'activity_param_error', 'Please provide a valid array', [ 'status' => 400 ] );
        }

        // Validate user isn't trying to log activity for a different user
        $user = wp_get_current_user();
        if ( isset( $params['user_id'] ) && $params['user_id'] != $user->ID ) {
            return new WP_Error( 'activity_param_error', 'Cannot log activity for another user', [ 'status' => 400 ] );
        }

        // If logging for a post, validate user has permission
        if ( isset( $params['object_type'] ) && !empty( $params['object_type'] ) ) {
            $type = $params['object_type'];
            $post_types = DT_Posts::get_post_types();
            if ( array_search( $type, $post_types ) !== false ) {
                $post_id = isset( $params['object_id'] ) ? $params['object_id'] : null;

                if ( !empty( $post_id ) ) {
                    $has_permission = DT_Posts::can_update( $type, $post_id );
                } else {
                    $has_permission = DT_Posts::can_access( $type );
                }

                if ( !$has_permission ) {
                    return new WP_Error( __FUNCTION__, 'You do not have permission for this', [ 'status' => 403 ] );
                }
            }
        }

        dt_activity_insert( $params );
        return [
            'logged' => true
        ];
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
                    if ( $setting_value['tile'] === $tile_key ) {
                        $output[] = [
                            'label' => $post_label . ' > ' . $tile_value['label'] . ' > ' . $setting_value['name'],
                            'post_type' => $post_type,
                            'post_tile' => $tile_key,
                            'post_setting' => $setting_key,
                        ];
                    }
                }
            }
        }
        header( 'Content-Type" => application/json' );
        echo json_encode( $output );
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
                Disciple_Tools_Customizations_Tab::admin_notice( __( 'tile already exists', 'disciple_tools' ), 'error' );
                return false;
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
        $tile_options = $tile_options = DT_Posts::get_post_tiles( $post_type, false );
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

    public function plugin_permission_check() {
        if ( ! current_user_can( 'manage_dt' ) ) {
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
            $field_tile = $post_submission['new_field_tile'] ?? '';
            $field_key = dt_create_field_key( $post_submission['new_field_name'] );
            $custom_field_options = dt_get_option( 'dt_field_customizations' );

            if ( !$field_key ){
                return false;
            }

            //field privacy
            if ( isset( $post_submission['new_field_private'] ) && $post_submission['new_field_private'] ) {
                $field_private = true;
            } else {
                $field_private = false;
            }

            $post_fields = DT_Posts::get_post_field_settings( $post_type, false, true );
            if ( isset( $post_fields[ $field_key ] ) ){
                self::admin_notice( __( 'Field already exists', 'disciple_tools' ), 'error' );
                return false;
            }
            $new_field = [];
            if ( $field_type === 'key_select' ){
                $new_field = [
                    'name' => $post_submission['new_field_name'],
                    'default' => [],
                    'type' => 'key_select',
                    'tile' => $field_tile,
                    'customizable' => 'all',
                    'private' => $field_private
                ];
            } elseif ( $field_type === 'multi_select' ){
                $new_field = [
                    'name' => $post_submission['new_field_name'],
                    'default' => [],
                    'type' => 'multi_select',
                    'tile' => $field_tile,
                    'customizable' => 'all',
                    'private' => $field_private,
                ];
            } elseif ( $field_type === 'tags' ){
                $new_field = [
                    'name' => $post_submission['new_field_name'],
                    'default' => [],
                    'type' => 'tags',
                    'tile' => $field_tile,
                    'customizable' => 'all',
                    'private' => $field_private
                ];
            } elseif ( $field_type === 'date' ){
                $new_field = [
                    'name'        => $post_submission['new_field_name'],
                    'type'        => 'date',
                    'default'     => '',
                    'tile'     => $field_tile,
                    'customizable' => 'all',
                    'private' => $field_private
                ];
            } elseif ( $field_type === 'text' ){
                $new_field = [
                    'name'        => $post_submission['new_field_name'],
                    'type'        => 'text',
                    'default'     => '',
                    'tile'     => $field_tile,
                    'customizable' => 'all',
                    'private' => $field_private
                ];
            } elseif ( $field_type === 'textarea' ){
                $new_field = [
                    'name'        => $post_submission['new_field_name'],
                    'type'        => 'textarea',
                    'default'     => '',
                    'tile'     => $field_tile,
                    'customizable' => 'all',
                    'private' => $field_private
                ];
            } elseif ( $field_type === 'number' ){
                $new_field = [
                    'name'        => $post_submission['new_field_name'],
                    'type'        => 'number',
                    'default'     => '',
                    'tile'     => $field_tile,
                    'customizable' => 'all',
                    'private' => $field_private
                ];
            } elseif ( $field_type === 'link' ) {
                $new_field = [
                    'name'        => $post_submission['new_field_name'],
                    'type'        => 'link',
                    'default'     => [],
                    'tile'     => $field_tile,
                    'customizable' => 'all',
                    'private' => $field_private
                ];
            } elseif ( $field_type === 'connection' ){
                if ( !$post_submission['connection_target'] ){
                    self::admin_notice( __( 'Please select a connection target', 'disciple_tools' ), 'error' );
                    return false;
                }
                $p2p_key = $post_type . '_to_' . $post_submission['connection_target'];
                if ( p2p_type( $p2p_key ) !== false ){
                    $p2p_key = dt_create_field_key( $p2p_key, true );
                }

                // connection field to the same post type
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
                        'tile'     => $field_tile,
                        'customizable' => 'all',
                    ];
                    //if not multidirectional, create the reverse direction field
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
                        'tile'     => $field_tile,
                        'customizable' => 'all',
                    ];
                    //create the reverse fields on the connection post type
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
            $new_field_option = $post_submission['field_option_name'];
            $new_field_option_key = dt_create_field_key( $new_field_option );
            $field_key = $post_submission['field_key'];
            $post_type = $post_submission['post_type'];

            $custom_field_options = dt_get_option( 'dt_field_customizations' );
            $custom_field_options[$post_type][$field_key]['default'][$new_field_option_key] = [ 'label' => $new_field_option ];
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

            $custom_field_options = dt_get_option( 'dt_field_customizations' );
            $custom_field_options[$post_type][$field_key]['default'][$field_option_key]['label'] = $new_field_option_label;
            $custom_field_options[$post_type][$field_key]['default'][$field_option_key]['description'] = $new_field_option_description;
            update_option( 'dt_field_customizations', $custom_field_options );
            return $custom_field_options[$post_type][$field_key]['default'][$field_option_key];
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
    }

    public static function update_field_options_order( WP_REST_Request $request ) {
        $post_submission = $request->get_params();
        $post_type = sanitize_text_field( wp_unslash( $post_submission['post_type'] ) );
        $field_key = sanitize_text_field( wp_unslash( $post_submission['field_key'] ) );

        $field_customizations = dt_get_option( 'dt_field_customizations' );
        $custom_field = $field_customizations[$post_type][$field_key] ?? [];

        $sortable_field_options_ordering = dt_recursive_sanitize_array( $post_submission['sortable_field_options_ordering'] );
        if ( ! empty( $sortable_field_options_ordering ) ) {
            $custom_field['order'] = $sortable_field_options_ordering;
        }

        $field_customizations[$post_type][$field_key] = $custom_field;
        update_option( 'dt_field_customizations', $field_customizations );
        wp_cache_delete( $post_type . '_field_settings' );
    }

    public static function edit_field( WP_REST_Request $request ) {
        $post_submission = $request->get_params();
        $post_type = $post_submission['post_type'];
        $field_key = $post_submission['field_key'];
        $tile_key = $post_submission['tile_key'];
        $custom_name = $post_submission['custom_name'];
        $field_private = $post_submission['field_private'];
        $field_icon = $post_submission['field_icon'];

        $post_fields = DT_Posts::get_post_field_settings( $post_type, false, true );
        $field_customizations = dt_get_option( 'dt_field_customizations' );

        if ( isset( $post_fields[$field_key] ) ) {
            if ( !isset( $field_customizations[$post_type][$field_key] ) ){
                $field_customizations[$post_type][$field_key] = [];
            }
            $custom_field = $field_customizations[$post_type][$field_key];
            $field = $post_fields[$field_key];

            // update name
            if ( isset( $post_submission['custom_name'] ) && !empty( $post_submission['custom_name'] ) ) {
                $custom_field['name'] = $post_submission['custom_name'];
            }

            // field privacy
            $custom_field['private'] = false;
            if ( isset( $post_submission['field_private'] ) && $post_submission['field_private'] ) {
                $custom_field['private'] = true;
            }

            // field tile
            if ( isset( $post_submission['tile_select'] ) ) {
                $custom_field['tile'] = $post_submission['tile_select'];
            }

            // field description
            if ( isset( $post_submission['field_description'] ) && $post_submission['field_description'] != ( $custom_field['description'] ?? '' ) ){
                $custom_field['description'] = $post_submission['field_description'];
            }

            // field icon
            if ( isset( $post_submission['field_icon'] ) ) {
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
        if ( ! current_user_can( 'manage_options' ) ) {
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
