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

        // Register filter hooks for external code
        add_filter( 'dt_home_screen_apps', [ $this, 'filter_home_screen_apps' ] );
        add_filter( 'dt_home_screen_links', [ $this, 'filter_home_screen_links' ] );
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
                    'color' => '', // Empty string represents "no custom color" (preserved in WordPress options)
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
                'color' => '', // Empty string represents "no custom color" (preserved in WordPress options)
                'enabled' => !( $app['is_hidden'] ?? false ),
                'order' => $app['sort'] ?? $this->get_next_order(),
                'created_at' => current_time( 'mysql' ),
                'updated_at' => current_time( 'mysql' )
            ];
        }
    }

    /**
     * Get all apps
     *
     * Merges coded apps with their customizations from the database.
     * For coded apps, only customization fields (type, icon, color, enabled, order, user_roles_type, roles)
     * are stored in the database and merged back into the coded app.
     */
    public function get_all_apps() {

        // Step 1: Load coded apps first (these are the base)
        $coded_apps = [];
        $coded_app_ids = [];

        // Collect all coded apps
        foreach ( $this->ml_apps as $app ) {
            if ( ! empty( $app['id'] ) ) {
                $coded_apps[ $app['id'] ] = $app;
                $coded_app_ids[] = $app['id'];
            }
        }

        foreach ( $this->home_apps as $app ) {
            if ( ! empty( $app['id'] ) && ! isset( $coded_apps[ $app['id'] ] ) ) {
                $coded_apps[ $app['id'] ] = $app;
                $coded_app_ids[] = $app['id'];
            }
        }

        // Step 2: Load database apps and separate into customizations and full custom apps
        $db_apps = get_option( $this->option_name, [] );
        $customizations = []; // Customizations for coded apps (keyed by app ID)
        $custom_apps = []; // Full custom apps (non-coded)

        foreach ( $db_apps as $db_app ) {
            if ( empty( $db_app['id'] ) ) {
                continue;
            }

            // Check if this is a customization entry for a coded app
            $is_customization = (
                isset( $db_app['creation_type'] ) && $db_app['creation_type'] === 'coded'
            ) || in_array( $db_app['id'], $coded_app_ids );

            if ( $is_customization ) {
                // This is a customization entry - store it for merging
                $customizations[ $db_app['id'] ] = $db_app;
            } else {
                // This is a full custom app (non-coded)
                $custom_apps[] = $db_app;
            }
        }

        // Step 3: Merge customizations into coded apps
        $merged_apps = [];
        foreach ( $coded_apps as $app_id => $coded_app ) {
            $merged_app = $coded_app;

            // If there's a customization entry, merge only customization fields
            if ( isset( $customizations[ $app_id ] ) ) {
                $customization = $customizations[ $app_id ];

                // Merge only customization fields (don't overwrite title, description, url, magic_link_meta, etc.)
                if ( isset( $customization['type'] ) ) {
                    $merged_app['type'] = $customization['type'];
                }
                if ( isset( $customization['icon'] ) ) {
                    $merged_app['icon'] = $customization['icon'];
                }
                if ( isset( $customization['color'] ) ) {
                    $merged_app['color'] = $customization['color'];
                }
                if ( isset( $customization['enabled'] ) ) {
                    $merged_app['enabled'] = $customization['enabled'];
                }
                if ( isset( $customization['order'] ) ) {
                    // Ensure order is cast to integer for proper sorting
                    $merged_app['order'] = (int) $customization['order'];
                }
                if ( isset( $customization['user_roles_type'] ) ) {
                    $merged_app['user_roles_type'] = $customization['user_roles_type'];
                }
                if ( isset( $customization['roles'] ) ) {
                    $merged_app['roles'] = $customization['roles'];
                }
                if ( isset( $customization['updated_at'] ) ) {
                    $merged_app['updated_at'] = $customization['updated_at'];
                }
            }

            $merged_apps[] = $merged_app;
        }

        // Step 4: Add full custom apps (non-coded)
        foreach ( $custom_apps as $custom_app ) {
            $merged_apps[] = $custom_app;
        }

        // Step 5: Normalize type field for all apps
        foreach ( $merged_apps as &$app ) {
            // Normalize type: trim whitespace and convert to lowercase
            if ( isset( $app['type'] ) ) {
                $app['type'] = trim( strtolower( $app['type'] ) );
            }

            // Validate type is 'app' or 'link', use fallback if invalid
            if ( ! isset( $app['type'] ) || ( $app['type'] !== 'app' && $app['type'] !== 'link' ) ) {
                $app['type'] = $this->determine_app_type( $app );
            }

            // Ensure order field exists (default to 0 if missing)
            if ( ! isset( $app['order'] ) ) {
                $app['order'] = 0;
            }
        }
        unset( $app ); // Break reference

        // Step 6: Sort apps by order field
        usort( $merged_apps, function( $a, $b ) {
            $order_a = isset( $a['order'] ) ? (int) $a['order'] : 0;
            $order_b = isset( $b['order'] ) ? (int) $b['order'] : 0;
            return $order_a <=> $order_b;
        });

        return $merged_apps;
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
        // Normalize app_id to string for comparison (handles both string and integer IDs)
        $app_id_normalized = (string) $app_id;
        foreach ( $apps as $app ) {
            if ( isset( $app['id'] ) ) {
                // Normalize app ID to string for comparison
                $app_id_in_array = (string) $app['id'];
                if ( $app_id_in_array === $app_id_normalized ) {
                    return $app;
                }
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
            'color' => ! empty( $app_data['color'] ) ? sanitize_hex_color( $app_data['color'] ) : '', // Empty string for no custom color
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
     *
     * For coded apps, only saves customization fields (type, icon, color, enabled, order, user_roles_type, roles).
     * For custom apps, saves all fields.
     */
    public function update_app( $app_id, $app_data ) {
        $apps = $this->get_all_apps();
        $app_index = $this->get_app_index( $app_id );

        if ( $app_index === false ) {
            return new WP_Error( 'app_not_found', __( 'App not found.', 'disciple_tools' ) );
        }

        $app = $apps[$app_index];
        $is_coded_app = isset( $app['creation_type'] ) && $app['creation_type'] === 'coded';

        // For coded apps, remove title, description, and url from $app_data if present
        // (they shouldn't be there, but we'll be defensive)
        if ( $is_coded_app ) {
            unset( $app_data['title'] );
            unset( $app_data['description'] );
            unset( $app_data['url'] );
        }

        // Load existing database apps
        $db_apps = get_option( $this->option_name, [] );

        if ( $is_coded_app ) {
            // For coded apps, save only customization fields
            // Find or create customization entry in database
            $customization_index = false;
            foreach ( $db_apps as $index => $db_app ) {
                if ( isset( $db_app['id'] ) && $db_app['id'] === $app_id ) {
                    $customization_index = $index;
                    break;
                }
            }

            // Prepare customization entry (only customization fields)
            $customization = [
                'id' => $app_id,
                'creation_type' => 'coded',
                'updated_at' => current_time( 'mysql' )
            ];

            // Add only customization fields that are being updated
            if ( isset( $app_data['type'] ) ) {
                $customization['type'] = sanitize_text_field( $app_data['type'] );
            }
            if ( isset( $app_data['icon'] ) ) {
                $customization['icon'] = sanitize_text_field( $app_data['icon'] );
            }
            if ( isset( $app_data['color'] ) ) {
                // Handle empty string (no custom color) vs valid hex color
                if ( $app_data['color'] === '' || $app_data['color'] === null ) {
                    $customization['color'] = '';
                } else {
                    $sanitized_color = sanitize_hex_color( $app_data['color'] );
                    $customization['color'] = ! empty( $sanitized_color ) ? $sanitized_color : '';
                }
            }
            if ( isset( $app_data['enabled'] ) ) {
                $customization['enabled'] = (bool) $app_data['enabled'];
            }
            if ( isset( $app_data['order'] ) ) {
                $customization['order'] = (int) $app_data['order'];
            }
            if ( isset( $app_data['user_roles_type'] ) ) {
                $customization['user_roles_type'] = sanitize_text_field( $app_data['user_roles_type'] );
            }
            if ( isset( $app_data['roles'] ) ) {
                $customization['roles'] = is_array( $app_data['roles'] ) ? array_map( 'sanitize_text_field', $app_data['roles'] ) : [];
            }

            // Update or add customization entry
            if ( $customization_index !== false ) {
                // Merge with existing customization (preserve any existing fields, especially order)
                // Preserve order from existing customization if not being updated
                if ( ! isset( $customization['order'] ) && isset( $db_apps[$customization_index]['order'] ) ) {
                    $customization['order'] = (int) $db_apps[$customization_index]['order'];
                }
                $db_apps[$customization_index] = array_merge( $db_apps[$customization_index], $customization );
            } else {
                // Add new customization entry - preserve order from merged app if not in $app_data
                if ( ! isset( $customization['order'] ) && isset( $app['order'] ) ) {
                    $customization['order'] = (int) $app['order'];
                }
                $db_apps[] = $customization;
            }

            // Save only the database apps (customizations + custom apps)
            $result = update_option( $this->option_name, $db_apps );

            if ( $result ) {
                // Return the merged app (coded app + customizations)
                return $this->get_app( $app_id );
            } else {
                return new WP_Error( 'save_failed', __( 'Failed to update app.', 'disciple_tools' ) );
            }
        } else {
            // For custom apps, save all fields (existing behavior)
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
                // Handle empty string (no custom color) vs valid hex color
                if ( $app_data['color'] === '' || $app_data['color'] === null ) {
                    $apps[$app_index]['color'] = '';
                } else {
                    $sanitized_color = sanitize_hex_color( $app_data['color'] );
                    $apps[$app_index]['color'] = ! empty( $sanitized_color ) ? $sanitized_color : '';
                }
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

            // For custom apps, we need to save only custom apps (not coded app customizations)
            // Filter out coded app customizations and save only custom apps
            $db_apps = get_option( $this->option_name, [] );
            $coded_app_ids = [];
            foreach ( $this->ml_apps as $ml_app ) {
                if ( ! empty( $ml_app['id'] ) ) {
                    $coded_app_ids[] = $ml_app['id'];
                }
            }
            foreach ( $this->home_apps as $home_app ) {
                if ( ! empty( $home_app['id'] ) ) {
                    $coded_app_ids[] = $home_app['id'];
                }
            }

            // Remove coded app customizations from db_apps
            $custom_apps_only = [];
            foreach ( $db_apps as $db_app ) {
                if ( empty( $db_app['id'] ) ) {
                    continue;
                }
                // Keep if it's not a coded app customization
                if ( ! ( isset( $db_app['creation_type'] ) && $db_app['creation_type'] === 'coded' ) && ! in_array( $db_app['id'], $coded_app_ids ) ) {
                    $custom_apps_only[] = $db_app;
                }
            }

            // Update the custom app in the array
            $custom_app_index = false;
            foreach ( $custom_apps_only as $index => $custom_app ) {
                if ( isset( $custom_app['id'] ) && $custom_app['id'] === $app_id ) {
                    $custom_app_index = $index;
                    break;
                }
            }

            if ( $custom_app_index !== false ) {
                $custom_apps_only[$custom_app_index] = $apps[$app_index];
            } else {
                $custom_apps_only[] = $apps[$app_index];
            }

            // Merge back with coded app customizations
            $final_db_apps = $custom_apps_only;
            foreach ( $db_apps as $db_app ) {
                if ( ! empty( $db_app['id'] ) && ( ( isset( $db_app['creation_type'] ) && $db_app['creation_type'] === 'coded' ) || in_array( $db_app['id'], $coded_app_ids ) ) ) {
                    // Check if we already have this customization
                    $exists = false;
                    foreach ( $final_db_apps as $final_app ) {
                        if ( isset( $final_app['id'] ) && $final_app['id'] === $db_app['id'] ) {
                            $exists = true;
                            break;
                        }
                    }
                    if ( ! $exists ) {
                        $final_db_apps[] = $db_app;
                    }
                }
            }

            // Save
            $result = update_option( $this->option_name, $final_db_apps );

            if ( $result ) {
                return $apps[$app_index];
            } else {
                return new WP_Error( 'save_failed', __( 'Failed to update app.', 'disciple_tools' ) );
            }
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
        // Normalize app_id to string for comparison (handles both string and integer IDs)
        $app_id_normalized = (string) $app_id;
        foreach ( $apps as $index => $app ) {
            if ( isset( $app['id'] ) ) {
                // Normalize app ID to string for comparison
                $app_id_in_array = (string) $app['id'];
                if ( $app_id_in_array === $app_id_normalized ) {
                    return $index;
                }
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
     * Filter callback for dt_home_screen_apps
     *
     * Returns only apps of type 'app' when external code calls apply_filters('dt_home_screen_apps', []).
     *
     * @param array $default_value Default value passed to apply_filters
     * @return array Filtered apps of type 'app'
     */
    public function filter_home_screen_apps( $default_value ) {
        $apps = $this->get_apps_for_frontend();
        $filtered = array_filter( $apps, function( $app ) {
            return isset( $app['type'] ) && strtolower( trim( $app['type'] ) ) === 'app';
        });
        return $filtered;
    }

    /**
     * Filter callback for dt_home_screen_links
     *
     * Returns only apps of type 'link' when external code calls apply_filters('dt_home_screen_links', []).
     *
     * @param array $default_value Default value passed to apply_filters
     * @return array Filtered apps of type 'link'
     */
    public function filter_home_screen_links( $default_value ) {
        $apps = $this->get_apps_for_frontend();
        $filtered = array_filter( $apps, function( $app ) {
            return isset( $app['type'] ) && strtolower( trim( $app['type'] ) ) === 'link';
        });
        return $filtered;
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
