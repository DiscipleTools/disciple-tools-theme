<?php
/**
 * Home Screen Apps Management
 *
 * Handles CRUD operations for custom apps in the Home Screen.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class DT_Home_Apps
 *
 * Manages custom apps for the Home Screen.
 */
class DT_Home_Apps {

    private static $_instance = null;
    private $option_name = 'dt_home_screen_apps';
    private $ml_apps = [];
    private $home_apps = [];

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        // Initialize with default apps if none exist
        $this->initialize_default_apps();

        // Register init hook to handle filter timing
        add_action( 'init', [ $this, 'on_init' ], 20 );
    }

    /**
     * Initialize default apps if none exist
     */
    private function initialize_default_apps() {
        $apps = $this->get_all_apps();
        if ( empty( $apps ) ) {
            $default_apps = [];

            update_option( $this->option_name, $default_apps );
        }
    }

    /**
     * Called on init hook to handle filter timing
     */
    public function on_init() {
        // This is where we can safely apply filters after all classes are loaded
        $this->load_magic_link_apps();
        $this->load_home_apps();
    }

    /**
     * Load Magic Link Apps
     */
    private function load_magic_link_apps() {
        foreach ( apply_filters( 'dt_magic_url_register_types', [] ) as $coded_app ) {
            foreach ( $coded_app as $app_type => $app ) {
                $is_home_app = ! empty( $app['meta']['show_in_home_apps'] );
                $is_template = (
                    isset( $app['root'], $app['post_type'] )
                    && 'templates' === strtolower( (string) $app['root'] )
                    && 'contacts' === $app['post_type']
                );

                if ( ! $is_home_app && ! $is_template ) {
                    continue;
                }

                $this->ml_apps[ $app_type ] = [
                    'id' => $app_type,
                    'creation_type' => 'coded',
                    'type' => 'app',
                    'title' => $app['label'] ?? '',
                    'description' => $app['description'] ?? '',
                    'url' => trailingslashit( trailingslashit( site_url() ) . $app['url_base'] ),
                    'icon' => $app['meta']['icon'] ?? 'mdi mdi-apps',
                    'color' => null,
                    'enabled' => true,
                    'order' => $this->get_next_order(),
                    'created_at' => current_time( 'mysql' ),
                    'updated_at' => current_time( 'mysql' ),
                    'slug' => $app['type'],
                    'magic_link_meta' => [
                        'post_type' => $app['post_type'],
                        'root' => $app['root'],
                        'type' => $app['type'],
                        'meta_key' => $app['meta_key']
                    ]
                ];
            }
        }
    }

    /**
     * Load Home Apps
     */
    private function load_home_apps() {
        $filtered_apps = apply_filters( 'dt_home_apps', [] );

        foreach ( $filtered_apps as $app ) {
            // Skip if is_hidden is explicitly set to true
            if ( isset( $app['is_hidden'] ) && $app['is_hidden'] === true ) {
                continue;
            }

            // Validate required fields
            if ( empty( $app['slug'] ) || empty( $app['name'] ) ) {
                continue;
            }

            // Process and validate type
            $app_type = trim( strtolower( $app['type'] ?? 'link' ) );
            if ( $app_type !== 'app' && $app_type !== 'link' ) {
                continue;
            }

            $slug = $app['slug'];
            $this->home_apps[ $slug ] = [
                'id' => $slug,
                'slug' => $slug,
                'creation_type' => 'coded',
                'type' => $app_type,
                'title' => $app['name'],
                'description' => '',
                'url' => $app['url'] ?? '#',
                'icon' => $app['icon'] ?? 'mdi mdi-apps',
                'color' => null,
                'enabled' => !( $app['is_hidden'] ?? false ),
                'order' => $app['sort'] ?? $this->get_next_order(),
                'created_at' => current_time( 'mysql' ),
                'updated_at' => current_time( 'mysql' )
            ];
        }
    }

    /**
     * Get all apps
     */
    public function get_all_apps() {

        $updated_apps = [];
        $processed_ids = [];

        foreach ( get_option( $this->option_name, [] ) as $app ) {
            if ( empty( $app['id'] ) || in_array( $app['id'], $processed_ids ) ) {
                continue;
            }

            $updated_apps[] = $app;
            $processed_ids[] = $app['id'];
        }

        foreach ( $this->ml_apps as $app ) {
            if ( empty( $app['id'] ) || in_array( $app['id'], $processed_ids ) ) {
                continue;
            }

            $updated_apps[] = $app;
            $processed_ids[] = $app['id'];
        }

        foreach ( $this->home_apps as $app ) {
            if ( empty( $app['id'] ) || in_array( $app['id'], $processed_ids ) ) {
                continue;
            }

            $updated_apps[] = $app;
            $processed_ids[] = $app['id'];
        }

        // Normalize type field for all apps
        foreach ( $updated_apps as &$app ) {
            // Normalize type: trim whitespace and convert to lowercase
            if ( isset( $app['type'] ) ) {
                $app['type'] = trim( strtolower( $app['type'] ) );
            }

            // Validate type is 'app' or 'link', use fallback if invalid
            if ( ! isset( $app['type'] ) || ( $app['type'] !== 'app' && $app['type'] !== 'link' ) ) {
                $app['type'] = $this->determine_app_type( $app );
            }
        }
        unset( $app ); // Break reference

        return $updated_apps;
    }

    /**
     * Get enabled apps only
     */
    public function get_enabled_apps() {
        $apps = $this->get_all_apps();
        return array_filter( $apps, function( $app ) {
            return isset( $app['enabled'] ) && $app['enabled'] === true;
        });
    }

    /**
     * Get app by ID
     */
    public function get_app( $app_id ) {
        $apps = $this->get_all_apps();
        foreach ( $apps as $app ) {
            if ( isset( $app['id'] ) && $app['id'] === $app_id ) {
                return $app;
            }
        }
        return null;
    }

    /**
     * Create new app
     */
    public function create_app( $app_data ) {
        // Validate required fields
        if ( empty( $app_data['title'] ) ) {
            return new WP_Error( 'missing_title', __( 'App title is required.', 'disciple_tools' ) );
        }

        // Generate unique ID
        $app_id = sanitize_title( $app_data['title'] );
        $original_id = $app_id;
        $counter = 1;

        // Ensure unique ID
        while ( $this->get_app( $app_id ) !== null ) {
            $app_id = $original_id . '-' . $counter;
            $counter++;
        }

        // Generate slug for permission key
        $slug = sanitize_title( $app_data['title'] );
        $original_slug = $slug;
        $slug_counter = 1;
        // Ensure unique slug
        $existing_apps = $this->get_all_apps();
        while ( $this->slug_exists( $slug, $existing_apps ) ) {
            $slug = $original_slug . '-' . $slug_counter;
            $slug_counter++;
        }

        // Prepare app data
        $new_app = [
            'id' => $app_id,
            'slug' => $slug,
            'creation_type' => 'custom',
            'type' => sanitize_text_field( $app_data['type'] ?? 'link' ),
            'title' => sanitize_text_field( $app_data['title'] ),
            'description' => sanitize_textarea_field( $app_data['description'] ?? '' ),
            'url' => esc_url_raw( $app_data['url'] ?? '#' ),
            'icon' => sanitize_text_field( $app_data['icon'] ?? 'mdi mdi-apps' ),
            'color' => sanitize_hex_color( $app_data['color'] ?? null ),
            'enabled' => isset( $app_data['enabled'] ) ? (bool) $app_data['enabled'] : true,
            'user_roles_type' => sanitize_text_field( $app_data['user_roles_type'] ?? 'support_all_roles' ),
            'roles' => is_array( $app_data['roles'] ?? [] ) ? array_map( 'sanitize_text_field', $app_data['roles'] ) : [],
            'order' => $this->get_next_order(),
            'created_at' => current_time( 'mysql' ),
            'updated_at' => current_time( 'mysql' )
        ];

        // Add to apps array
        $apps = $this->get_all_apps();
        $apps[] = $new_app;

        // Save
        $result = update_option( $this->option_name, $apps );

        if ( $result ) {
            return $new_app;
        } else {
            return new WP_Error( 'save_failed', __( 'Failed to save app.', 'disciple_tools' ) );
        }
    }

    /**
     * Update existing app
     */
    public function update_app( $app_id, $app_data ) {
        $apps = $this->get_all_apps();
        $app_index = $this->get_app_index( $app_id );

        if ( $app_index === false ) {
            return new WP_Error( 'app_not_found', __( 'App not found.', 'disciple_tools' ) );
        }

        // Update fields
        if ( isset( $app_data['type'] ) ) {
            $apps[$app_index]['type'] = sanitize_text_field( $app_data['type'] );
        }
        if ( isset( $app_data['title'] ) ) {
            $apps[$app_index]['title'] = sanitize_text_field( $app_data['title'] );
        }
        if ( isset( $app_data['description'] ) ) {
            $apps[$app_index]['description'] = sanitize_textarea_field( $app_data['description'] );
        }
        if ( isset( $app_data['url'] ) ) {
            $apps[$app_index]['url'] = esc_url_raw( $app_data['url'] );
        }
        if ( isset( $app_data['icon'] ) ) {
            $apps[$app_index]['icon'] = sanitize_text_field( $app_data['icon'] );
        }
        if ( isset( $app_data['color'] ) ) {
            $apps[$app_index]['color'] = sanitize_hex_color( $app_data['color'] );
        }
        if ( isset( $app_data['enabled'] ) ) {
            $apps[$app_index]['enabled'] = (bool) $app_data['enabled'];
        }
        if ( isset( $app_data['order'] ) ) {
            $apps[$app_index]['order'] = (int) $app_data['order'];
        }
        if ( isset( $app_data['user_roles_type'] ) ) {
            $apps[$app_index]['user_roles_type'] = sanitize_text_field( $app_data['user_roles_type'] );
        }
        if ( isset( $app_data['roles'] ) ) {
            $apps[$app_index]['roles'] = is_array( $app_data['roles'] ) ? array_map( 'sanitize_text_field', $app_data['roles'] ) : [];
        }

        $apps[$app_index]['updated_at'] = current_time( 'mysql' );

        // Save
        $result = update_option( $this->option_name, $apps );

        if ( $result ) {
            return $apps[$app_index];
        } else {
            return new WP_Error( 'save_failed', __( 'Failed to update app.', 'disciple_tools' ) );
        }
    }

    /**
     * Delete app
     */
    public function delete_app( $app_id ) {
        $apps = $this->get_all_apps();
        $app_index = $this->get_app_index( $app_id );

        if ( $app_index === false ) {
            return new WP_Error( 'app_not_found', __( 'App not found.', 'disciple_tools' ) );
        }

        // Remove app from array
        unset( $apps[$app_index] );
        $apps = array_values( $apps ); // Re-index array

        // Save
        $result = update_option( $this->option_name, $apps );

        if ( $result ) {
            return true;
        } else {
            return new WP_Error( 'save_failed', __( 'Failed to delete app.', 'disciple_tools' ) );
        }
    }

    /**
     * Reorder apps
     */
    public function reorder_apps( $ordered_ids ) {
        $apps = $this->get_all_apps();

        // Create a lookup array for existing data
        $apps_lookup = [];
        foreach ( $apps as $app ) {
            if ( isset( $app['id'] ) ) {
                $apps_lookup[$app['id']] = $app;
            }
        }

        // Reorder based on the provided IDs and update order values
        $reordered_apps = [];
        $processed_ids = [];

        foreach ( $ordered_ids as $index => $app_id ) {
            if ( isset( $apps_lookup[$app_id] ) ) {
                $app = $apps_lookup[$app_id];
                $app['order'] = $index + 1;
                $app['updated_at'] = current_time( 'mysql' );
                $reordered_apps[] = $app;
                $processed_ids[] = $app_id;
            }
        }

        // Add any missing items to the end to prevent data loss
        foreach ( $apps as $app ) {
            if ( isset( $app['id'] ) && !in_array( $app['id'], $processed_ids ) ) {
                $app['order'] = count( $reordered_apps ) + 1;
                $app['updated_at'] = current_time( 'mysql' );
                $reordered_apps[] = $app;
            }
        }

        // Save
        $result = update_option( $this->option_name, $reordered_apps );

        if ( $result ) {
            return true;
        } else {
            return new WP_Error( 'save_failed', __( 'Failed to reorder apps.', 'disciple_tools' ) );
        }
    }

    /**
     * Get app index in array
     */
    private function get_app_index( $app_id ) {
        $apps = $this->get_all_apps();
        foreach ( $apps as $index => $app ) {
            if ( isset( $app['id'] ) && $app['id'] === $app_id ) {
                return $index;
            }
        }
        return false;
    }

    /**
     * Get next order number
     */
    private function get_next_order() {
        $apps = $this->get_all_apps();
        $max_order = 0;
        foreach ( $apps as $app ) {
            if ( isset( $app['order'] ) && $app['order'] > $max_order ) {
                $max_order = $app['order'];
            }
        }
        return $max_order + 1;
    }

    /**
     * Determine app type based on app properties
     *
     * @param array $app The app data array
     * @return string Either 'app' or 'link'
     */
    private function determine_app_type( $app ) {
        // If type property exists and is valid, use it
        if ( isset( $app['type'] ) && ( $app['type'] === 'app' || $app['type'] === 'link' ) ) {
            return $app['type'];
        }

        // If type is missing or invalid, use fallback logic
        // If creation_type is 'coded', default to 'app'
        if ( isset( $app['creation_type'] ) && $app['creation_type'] === 'coded' ) {
            return 'app';
        }

        // All other cases default to 'link'
        return 'link';
    }

    /**
     * Get apps for frontend display
     */
    public function get_apps_for_frontend() {
        $apps = $this->get_enabled_apps();

        // Enrich coded magic-link app urls.
        $enriched_apps = [];
        foreach ( $apps as $app ) {
            // Ensure creation_type is set
            if ( ! isset( $app['creation_type'] ) ) {
                // Default to 'custom' if not set
                $app['creation_type'] = 'custom';
            }

            if ( $app['creation_type'] == 'coded' ) {
                $app_meta = $app['magic_link_meta'] ?? [];
                $app_ml_root = $app_meta['root'] ?? '';
                $app_ml_type = $app_meta['type'] ?? '';

                if ( isset( $app_meta['post_type'] ) && $app_meta['post_type'] === 'user' ) {
                    $meta_key = DT_Magic_URL::get_public_key_meta_key( $app_ml_root, $app_ml_type );
                    $magic_url_key = get_user_option( $meta_key, get_current_user_id() );
                    if ( !empty( $magic_url_key ) ) {
                        $app['url'] = DT_Magic_URL::get_link_url( $app_ml_root, $app_ml_type, $magic_url_key );
                    }
                } elseif (
                    isset( $app_meta['root'], $app_meta['post_type'] )
                    && 'templates' === strtolower( (string) $app_meta['root'] )
                    && in_array( $app_meta['post_type'], [ 'contacts' ], true )
                ) {
                    $post_id = Disciple_Tools_Users::get_contact_for_user( get_current_user_id() );

                    if ( ! empty( $post_id ) ) {
                        $post = DT_Posts::get_post( $app_meta['post_type'], $post_id );
                        $meta_key = $app_meta['meta_key'] ?? '';

                        // Ensure meta_key is set - if not, construct it from root and type
                        if ( empty( $meta_key ) && ! empty( $app_ml_root ) && ! empty( $app_ml_type ) ) {
                            $meta_key = $app_ml_root . '_' . $app_ml_type . '_magic_key';
                        }

                        if ( ! is_wp_error( $post ) && ! empty( $post ) && ! empty( $meta_key ) ) {
                            if ( isset( $post[ $meta_key ] ) && ! empty( $post[ $meta_key ] ) ) {
                                $magic_url_key = $post[ $meta_key ];
                            } else {
                                $magic_url_key = dt_create_unique_key();
                                update_post_meta( $post_id, $meta_key, $magic_url_key );
                            }

                            if ( ! empty( $magic_url_key ) ) {
                                $app['url'] = DT_Magic_URL::get_link_url( $app_ml_root, $app_ml_type, $magic_url_key );
                            }
                        }
                    }
                }
            }

            // Determine and set type property
            $app['type'] = $this->determine_app_type( $app );

            $enriched_apps[] = $app;
        }

        // Sort by order
        usort( $enriched_apps, function( $a, $b ) {
            return $a['order'] <=> $b['order'];
        });

        return $enriched_apps;
    }

    /**
     * Check if slug exists in apps array
     */
    private function slug_exists( $slug, $apps ) {
        foreach ( $apps as $app ) {
            if ( isset( $app['slug'] ) && $app['slug'] === $slug ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get apps filtered by user permissions
     */
    public function get_apps_for_user( $user_id = 0 ) {
        $apps = $this->get_apps_for_frontend();
        // If roles permissions is not available, return all apps
        if ( !class_exists( 'DT_Home_Roles_Permissions' ) ) {
            return $apps;
        }

        $roles_permissions = DT_Home_Roles_Permissions::instance();
        return $roles_permissions->filter_apps_by_permissions( $apps, $user_id );
    }

    /**
     * Validate app data
     */
    public function validate_app_data( $app_data ) {
        $errors = [];

        if ( empty( $app_data['title'] ) ) {
            $errors[] = __( 'App title is required.', 'disciple_tools' );
        }

        if ( ! empty( $app_data['url'] ) && ! filter_var( $app_data['url'], FILTER_VALIDATE_URL ) && $app_data['url'] !== '#' ) {
            $errors[] = __( 'App URL must be a valid URL.', 'disciple_tools' );
        }

        if ( ! empty( $app_data['color'] ) && ! preg_match( '/^#[a-fA-F0-9]{6}$/', $app_data['color'] ) ) {
            $errors[] = __( 'App color must be a valid hex color.', 'disciple_tools' );
        }

        return $errors;
    }
}

DT_Home_Apps::instance();
