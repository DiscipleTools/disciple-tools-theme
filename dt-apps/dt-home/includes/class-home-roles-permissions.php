<?php
/**
 * Home Screen Roles & Permissions Management
 *
 * Handles role-based access control for the Home Screen app.
 * Integrates with the Disciple.Tools roles system.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class DT_Home_Roles_Permissions
 *
 * Manages roles and permissions for the Home Screen app.
 */
class DT_Home_Roles_Permissions {

    private static $_instance = null;
    public const CAPABILITIES_SOURCE = 'Home Screen';
    public const OPTION_KEY_CUSTOM_ROLES = 'dt_custom_roles';
    public const OPTION_KEY_USE_CAPABILITIES = 'dt_home_use_capabilities';

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        // Initialize hooks
        add_action( 'init', [ $this, 'init' ], 20 );
    }

    /**
     * Initialize and hook into various filters and actions.
     */
    public function init() {
        add_filter( 'dt_capabilities', [ $this, 'dt_capabilities' ], 50, 1 );
        add_filter( 'dt_set_roles_and_permissions', [ $this, 'dt_set_roles_and_permissions' ], 10, 1 );
    }

    /**
     * Determine if roles & permissions enforcement is enabled.
     * @return bool
     */
    public function is_enabled(): bool {
        $settings = get_option( 'dt_home_screen_settings', [] );
        return isset( $settings['enable_roles_permissions'] ) ? (bool) $settings['enable_roles_permissions'] : true;
    }

    /**
     * Update roles & permissions enforcement enabled state.
     * @param bool $enable
     * @return bool
     */
    public function enabled( bool $enable ): bool {
        $settings = get_option( 'dt_home_screen_settings', [] );
        $settings['enable_roles_permissions'] = $enable;
        return update_option( 'dt_home_screen_settings', $settings );
    }

    /**
     * Default D.T Home Screen capabilities.
     * @return array
     */
    private function default_capabilities(): array {
        $capabilities = [
            'can_access_home_screen' => [
                'source' => self::CAPABILITIES_SOURCE,
                'description' => __( 'Access to the Home Screen dashboard', 'disciple_tools' )
            ]
        ];

        // Capture available apps and build associated capabilities.
        $apps_manager = DT_Home_Apps::instance();
        foreach ( $apps_manager->get_all_apps() as $app ) {
            if ( isset( $app['slug'] ) ) {
                $capabilities[ $this->generate_permission_key( $app['slug'] ) ] = [
                    'source' => self::CAPABILITIES_SOURCE,
                    'description' => sprintf( __( 'Access to %s app', 'disciple_tools' ), $app['title'] ?? $app['slug'] ),
                    'user_roles_type' => $app['user_roles_type'] ?? 'support_all_roles'
                ];
            }
        }

        return $capabilities;
    }

    /**
     * Register plugin specific D.T Capabilities.
     * @param array $capabilities
     * @return array
     */
    public function dt_capabilities( array $capabilities ): array {
        if ( $this->is_enabled() ) {
            $capabilities = array_merge( $capabilities, $this->default_capabilities() );
        }

        return $capabilities;
    }

    /**
     * Default D.T Home Screen role and permission assignments.
     * @return array
     */
    private function default_roles_and_permissions(): array {
        $default_roles = [
            'administrator',
            'custom_developer',
            'dispatcher',
            'dt_admin',
            'multiplier'
        ];

        // Pair default roles with capabilities; in an initial selected state.
        $default_roles_and_permissions = [];
        $default_capabilities = $this->default_capabilities();
        foreach ( $default_roles as $role ) {
            $default_roles_and_permissions[ $role ] = [];
            foreach ( $default_capabilities as $key => $capability ) {
                $default_roles_and_permissions[ $role ][ $key ] = $capability;
            }
        }

        return $default_roles_and_permissions;
    }

    /**
     * Register plugin specific D.T Roles & Permissions.
     * @param array $expected_roles
     * @return array
     */
    public function dt_set_roles_and_permissions( array $expected_roles ): array {
        if ( $this->is_enabled() ) {
            $dt_custom_roles = get_option( self::OPTION_KEY_CUSTOM_ROLES, [] );

            foreach ( $this->default_roles_and_permissions() as $role => $permissions ) {
                if ( !isset( $expected_roles[$role] ) || !is_array( $expected_roles[$role]['permissions'] ) ) {
                    $expected_roles[$role]['permissions'] = [];
                }

                /**
                 * Ensure selected flag is set accordingly, based on saved
                 * custom role settings; which take priority and overall
                 * payload settings.
                 */

                foreach ( $permissions as $permission => $payload ) {

                    /**
                     * If no user_roles_type is set; or it's set, with the value support_all_roles;
                     * then access will be granted.
                     *
                     * If no custom setting is detected; then access is automatically granted.
                     *
                     * Else; access is granted accordingly; by specified custom setting.
                     */

                    if ( ! isset( $payload['user_roles_type'] ) || $payload['user_roles_type'] === 'support_all_roles' ) {
                        $expected_roles[$role]['permissions'][$permission] = true;
                    } else if ( ! isset( $dt_custom_roles[$role]['capabilities'][$permission] ) ) {
                        $expected_roles[$role]['permissions'][$permission] = true;
                    } else {
                        $expected_roles[$role]['permissions'][$permission] = $dt_custom_roles[$role]['capabilities'][$permission];
                    }
                }
            }
        }

        return $expected_roles;
    }

    /**
     * Get D.T Roles & Permissions.
     */
    public function get_dt_roles_and_permissions(): array {
        return Disciple_Tools_Roles::get_dt_roles_and_permissions();
    }

    /**
     * Build associated permission key, based on specified slug and type.
     *
     * @param string $slug
     * @param string $type
     * @return string
     */
    public function generate_permission_key( string $slug, string $type = 'access' ): string {
        switch ( $type ) {
            case 'access':
            default:
                return 'can_access_'. $slug .'_app';
        }
    }

    /**
     * Update global user roles for specified permissions.
     *
     * @param string $app_slug
     * @param array $permissions
     * @param array $roles
     * @param array $deleted_roles
     * @return bool
     */
    public function update( string $app_slug, array $permissions, array $roles = [], array $deleted_roles = [] ): bool {
        $dt_custom_roles = array_map( function ( $custom_role ) use ( $permissions, $roles, $deleted_roles ) {
            if ( isset( $custom_role['slug'] ) ) {
                $custom_role_slug = $custom_role['slug'];

                // Update specified role permissions.
                if ( in_array( $custom_role_slug, $roles ) ) {
                    if ( !isset( $custom_role['capabilities'] ) ) {
                        $custom_role['capabilities'] = [];
                    }

                    foreach ( $permissions as $permission ) {
                        $custom_role['capabilities'][$permission] = true;
                    }
                }

                // Delete specified role permissions.
                if ( in_array( $custom_role_slug, $deleted_roles ) ) {
                    if ( isset( $custom_role['capabilities'] ) ) {
                        foreach ( $permissions as $permission ) {
                            $custom_role['capabilities'][$permission] = false;
                        }
                    }
                }
            }

            return $custom_role;
        }, get_option( self::OPTION_KEY_CUSTOM_ROLES, [] ) );

        // Persist updated global custom roles.
        return update_option( self::OPTION_KEY_CUSTOM_ROLES, $dt_custom_roles );
    }

    /**
     * Determine if specified user has permission to access given app.
     *
     * @param array $app
     * @param int $user_id
     * @param array $dt_custom_roles
     * @return bool
     */
    public function has_permission( array $app, int $user_id = 0, array $dt_custom_roles = [] ): bool {

        /**
         * Default to true if roles & permissions enforcement is currently disabled; or
         * user_roles_type is not set; or it is set to support_all_roles.
         */

        if ( !$this->is_enabled() || !isset( $app['user_roles_type'] ) || $app['user_roles_type'] === 'support_all_roles' ) {
            return true;
        }

        /**
         * Determine if user has a valid role for the specified app;
         * ensuring globally set $dt_custom_roles take priority.
         */

        $has_permission = false;

        // Capture user id to be validated against.
        if ( $user_id === 0 ) {
            $user_id = get_current_user_id();
        }

        // Determine permission to be validated against.
        $app_slug = $app['slug'] ?? '';
        $permission = $this->generate_permission_key( $app_slug );

        // Capture user's currently assigned roles and determine if they have relevant permission.
        $user = new WP_User( $user_id );
        if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
            $dt_custom_roles_checked = false;

            // Determine if any of user's current roles has been set within custom dt roles.
            foreach ( $user->roles as $role ) {
                if ( !$dt_custom_roles_checked && isset( $dt_custom_roles[ $role ]['capabilities'][ $permission ] ) ) {
                    $dt_custom_roles_checked = true;
                    $has_permission = $dt_custom_roles[ $role ]['capabilities'][ $permission ];
                }
            }

            // If custom roles were not checked, then attempt to validate against existing app settings.
            if ( !$dt_custom_roles_checked ) {
                if ( isset( $app['roles'] ) && is_array( $app['roles'] ) ) {
                    foreach ( $user->roles as $role ) {
                        if ( !$has_permission ) {
                            $has_permission = in_array( $role, $app['roles'] );
                        }
                    }
                }
            }
        }

        return $has_permission;
    }

    /**
     * Determine if specified user is allowed to access plugin.
     *
     * @param int $user_id
     * @return bool
     */
    public function can_access_plugin( int $user_id = 0 ): bool {

        // Default to true if roles & permissions enforcement is currently disabled.
        if ( !$this->is_enabled() ) {
            return true;
        }

        $can_access_plugin = false;

        // Capture user id to be validated against.
        if ( $user_id === 0 ) {
            $user_id = get_current_user_id();
        }

        /**
         * To avoid breaks in existing flows (i.e. logouts), ensure zero (0) user ids, are ignored
         * and true is returned
         */

        if ( $user_id === 0 ) {
            return true;
        }

        // Capture user's currently assigned roles and determine if they have relevant permission.
        $user = new WP_User( $user_id );
        if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
            $permission = 'can_access_home_screen';
            $dt_custom_roles = get_option( $this::OPTION_KEY_CUSTOM_ROLES, [] );

            // Determine if any of user's current roles match the can_access permission.
            foreach ( $user->roles as $role ) {
                if ( !$can_access_plugin && isset( $dt_custom_roles[ $role ]['capabilities'][ $permission ] ) && $dt_custom_roles[ $role ]['capabilities'][ $permission ] ) {
                    $can_access_plugin = true;
                }
            }
        }

        return $can_access_plugin;
    }

    /**
     * Get apps filtered by user permissions.
     *
     * @param array $apps
     * @param int $user_id
     * @return array
     */
    public function filter_apps_by_permissions( array $apps, int $user_id = 0 ): array {
        if ( !$this->is_enabled() ) {
            return $apps;
        }

        $dt_custom_roles = get_option( self::OPTION_KEY_CUSTOM_ROLES, [] );
        $filtered_apps = array_filter( $apps, function( $app ) use ( $user_id, $dt_custom_roles ) {
            try {
                return $this->has_permission( $app, $user_id, $dt_custom_roles );
            } catch ( Exception $e ) {
                error_log( 'Error in has_permission for app: ' . print_r( $app, true ) . ' Error: ' . $e->getMessage() );
                return false; // Exclude app if there's an error
            }
        });
        // Reindex the array to ensure it's a proper indexed array (not associative)
        $filtered_apps = array_values( $filtered_apps );
        // Debug: Ensure we return an array
        if ( !is_array( $filtered_apps ) ) {
            error_log( 'ERROR: filter_apps_by_permissions did not return an array. Type: ' . gettype( $filtered_apps ) );
            return [];
        }

        return $filtered_apps;
    }
}

// Initialize the class
DT_Home_Roles_Permissions::instance();
