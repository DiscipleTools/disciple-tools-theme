<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

// Include required classes
require_once get_template_directory() . '/dt-apps/dt-home/includes/class-home-apps.php';
require_once get_template_directory() . '/dt-apps/dt-home/includes/class-home-training.php';
require_once get_template_directory() . '/dt-apps/dt-home/includes/class-home-roles-permissions.php';
require_once get_template_directory() . '/dt-apps/dt-home/includes/class-home-migration.php';
require_once get_template_directory() . '/dt-apps/dt-home/includes/class-home-helpers.php';

/**
 * Class DT_Home_Magic_Link_App
 *
 * Main magic link app for the Home Screen functionality.
 * Provides a centralized dashboard for users to access apps and training videos.
 */
class DT_Home_Magic_Link_App extends DT_Magic_Url_Base {

    public $page_title = 'Home Screen';
    public $page_description = 'Your personalized dashboard for apps and training.';
    public $root = 'apps';
    public $type = 'launcher';
    public $post_type = 'user';
    private $meta_key = '';

    private static $_instance = null;
    public $meta = []; // Allows for instance specific data.
    public $translatable = [
        'query',
        'user'
    ]; // Order of translatable flags to be checked. Translate on first hit..!

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        /**
         * Specify metadata structure, specific to the processing of current
         * magic link type.
         *
         * - meta:              Magic link plugin related data.
         *      - app_type:     Flag indicating type to be processed by magic link plugin.
         *      - post_type     Magic link type post type.
         *      - contacts_only:    Boolean flag indicating how magic link type user assignments are to be handled within magic link plugin.
         *                          If True, lookup field to be provided within plugin for contacts only searching.
         *                          If false, Dropdown option to be provided for user, team or group selection.
         *      - fields:       List of fields to be displayed within magic link frontend form.
         *      - field_refreshes:  Support field label updating.
         */
        $this->meta = [
            'app_type'       => 'magic_link',
            'post_type'      => $this->post_type,
            'contacts_only'  => false,
            'supports_create' => false,
            'fields'         => [
                [
                    'id'    => 'name',
                    'label' => __( 'Name', 'disciple_tools' )
                ]
            ],
            'fields_refresh' => [
                'enabled'    => false,
                'post_type'  => 'contacts',
                'ignore_ids' => []
            ],
            'icon'           => 'mdi mdi-home',
            'show_in_home_apps' => false,
        ];

        /**
         * Once adjustments have been made, proceed with parent instantiation!
         */
        $this->meta_key = $this->root . '_' . $this->type . '_magic_key';
        parent::__construct();

        /**
         * user_app and module section
         */
        add_filter( 'dt_settings_apps_list', [ $this, 'dt_settings_apps_list' ], 10, 1 );
        add_action( 'rest_api_init', [ $this, 'add_endpoints' ] );

        if ( $this->should_register_launcher_hooks() ) {
            // Add launcher nav to other app-type magic link apps (not home screen)
            // Check for launcher parameter first (before head renders)
            add_action( 'dt_blank_head', [ $this, 'maybe_show_launcher_wrapper' ], 5 );
        }

        /**
         * tests if other URL
         */
        $url = dt_get_url_path();
        if ( strpos( $url, $this->root . '/' . $this->type ) === false ) {
            return;
        }
        /**
         * tests magic link parts are registered and have valid elements
         */
        if ( ! $this->check_parts_match() ) {
            return;
        }

        // load if valid url
        add_action( 'dt_blank_body', [ $this, 'body' ] );
        add_filter( 'dt_magic_url_base_allowed_css', [ $this, 'dt_magic_url_base_allowed_css' ], 10, 1 );
        add_filter( 'dt_magic_url_base_allowed_js', [ $this, 'dt_magic_url_base_allowed_js' ], 10, 1 );
        add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ], 100 );

        // Add redirect hooks for WordPress login/registration
        add_filter( 'login_redirect', [ $this, 'redirect_to_home_after_login' ], 10, 3 );
        add_action( 'user_register', [ $this, 'redirect_to_home_after_registration' ], 10, 1 );
        add_filter( 'register_url', [ $this, 'add_redirect_to_registration_url' ], 10, 1 );

        // REST API endpoints are registered in add_endpoints() method
    }

    public function wp_enqueue_scripts() {
        // Enqueue Material Design Icons for magic link apps
        wp_enqueue_style( 'material-font-icons-css', 'https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css', [], '7.4.47' );

        // Enqueue home screen specific styles (includes launcher nav styles)
        wp_enqueue_style( 'dt-home-style', get_template_directory_uri() . '/dt-apps/dt-home/assets/css/home-screen.css', [], '1.1.0' );

        // Enqueue theme toggle JavaScript
        wp_enqueue_script( 'dt-home-theme-toggle', get_template_directory_uri() . '/dt-apps/dt-home/assets/js/theme-toggle.js', [], '1.0.0', true );

        // Enqueue menu toggle JavaScript
        wp_enqueue_script( 'dt-home-menu-toggle', get_template_directory_uri() . '/dt-apps/dt-home/assets/js/menu-toggle.js', [], '1.0.0', true );

        wp_enqueue_script( 'dt-home-app', get_template_directory_uri() . '/dt-apps/dt-home/assets/js/home-app.js', [], '1.0.0', true );

        // Pass logout URL and invite settings to menu toggle script
        if ( function_exists( 'dt_home_get_logout_url' ) ) {
            wp_localize_script(
                'dt-home-menu-toggle',
                'dtHomeMenuToggleSettings',
                [
                    'logoutUrl' => dt_home_get_logout_url(),
                    'inviteEnabled' => function_exists( 'homescreen_invite_users_enabled' ) ? homescreen_invite_users_enabled() : false,
                ]
            );
        }
    }

    public function dt_magic_url_base_allowed_js( $allowed_js ) {
        // Start with empty array to prevent theme JS from loading
        // This eliminates conflicts and reduces page load size
        $allowed_js = [];

        // Only add scripts needed for home screen functionality
        $allowed_js[] = 'jquery'; // Required for $.ajax() calls to load apps and training videos
        $allowed_js[] = 'dt-home-theme-toggle';
        $allowed_js[] = 'dt-home-menu-toggle';
        $allowed_js[] = 'dt-home-app';

        return $allowed_js;
    }

    public function dt_magic_url_base_allowed_css( $allowed_css ) {
        // Start with empty array to prevent theme CSS from loading
        // This eliminates the need for most !important declarations
        $allowed_css = [];

        // Only add styles needed for home screen functionality
        $allowed_css[] = 'material-font-icons-css';
        $allowed_css[] = 'dt-home-style'; // Includes launcher nav styles for other magic link apps

        return $allowed_css;
    }

    /**
     * Builds magic link type settings payload:
     * - key:               Unique magic link type key; which is usually composed of root, type and _magic_key suffix.
     * - url_base:          URL path information to map with parent magic link type.
     * - label:             Magic link type name.
     * - description:       Magic link type description.
     * - settings_display:  Boolean flag which determines if magic link type is to be listed within frontend user profile settings.
     *
     * @param array $apps_list
     *
     * @return mixed
     */
    public function dt_settings_apps_list( $apps_list ) {
        $apps_list[ $this->meta_key ] = [
            'key'              => $this->meta_key,
            'url_base'         => $this->root . '/' . $this->type,
            'label'            => $this->page_title,
            'description'      => $this->page_description,
            'settings_display' => true
        ];

        return $apps_list;
    }

    /**
     * Writes custom styles to header
     *
     * @see DT_Magic_Url_Base()->header_style() for default state
     */
    public function header_style() {
        // All styles moved to home-screen.css since theme CSS is blocked via dt_magic_url_base_allowed_css filter
        // No inline styles needed - home-screen.css has full control
    }

    /**
     * Writes javascript to the header
     *
     * @see DT_Magic_Url_Base()->header_javascript() for default state
     */
    public function header_javascript() {
        ?>
        <?php
    }

    /**
     * Writes javascript to the footer
     *
     * @see DT_Magic_Url_Base()->footer_javascript() for default state
     */
    public function footer_javascript() {
        ?>
        <script>
            let jsObject = [<?php echo json_encode( [
                'root'                    => esc_url_raw( rest_url() ),
                'nonce'                   => wp_create_nonce( 'wp_rest' ),
                'parts'                   => $this->parts,
                'user_id'                 => get_current_user_id(),
                'invite_enabled'          => function_exists( 'homescreen_invite_users_enabled' ) ? homescreen_invite_users_enabled() : false,
                'translations'            => [
                    'welcome' => __( 'Welcome to your Home Screen', 'disciple_tools' ),
                    'apps' => __( 'Your Apps', 'disciple_tools' ),
                    'training' => __( 'Training Videos', 'disciple_tools' ),
                    'loading' => __( 'Loading...', 'disciple_tools' ),
                    'login_title' => __( 'Login Required', 'disciple_tools' ),
                    'login_message' => __( 'Please log in to access your Home Screen.', 'disciple_tools' ),
                    'username_label' => __( 'Username or Email', 'disciple_tools' ),
                    'password_label' => __( 'Password', 'disciple_tools' ),
                    'login_button' => __( 'Log In', 'disciple_tools' )
                ],
                'dt_home_magic_url'       => esc_js( dt_home_magic_url( '' ) ),
                'wp_login_url'            => esc_url( wp_login_url() ),
            ] ) ?>][0];
        </script>
        <?php
        return true;
    }

    /**
     * Check if launcher=1 parameter is present in the URL
     *
     * @return bool True if launcher parameter is present, false otherwise
     */
    private function has_launcher_parameter() {
        return isset( $_GET['launcher'] ) && $_GET['launcher'] === '1';
    }

    /**
     * Determine if launcher hooks should run for current request.
     *
     * Hooks are only needed when viewing the home screen app or when the
     * special launcher query parameter is present (so the wrapper can take over).
     *
     * @return bool
     */
    private function should_register_launcher_hooks() {
        if ( $this->has_launcher_parameter() ) {
            return true;
        }

        $url_path = trim( dt_get_url_path(), '/' );
        $home_path = trim( $this->root . '/' . $this->type, '/' );

        return str_starts_with( $url_path, $home_path );
    }

    /**
     * Get target app URL without launcher parameter
     * For cross-domain custom apps, gets the app URL from the app_url parameter
     *
     * @return string The target app URL without launcher parameter
     */
    private function get_target_app_url() {
        // Check if app_url parameter is present (for cross-domain custom apps)
        if ( isset( $_GET['app_url'] ) && ! empty( $_GET['app_url'] ) ) {
            $app_url = esc_url_raw( wp_unslash( $_GET['app_url'] ) );
            error_log( 'DT Home: Using app_url parameter for cross-domain app: ' . $app_url );
            return $app_url;
        }

        // Get current URL and remove launcher parameter
        // This handles coded apps and same-domain custom apps
        $http_host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
        if ( empty( $http_host ) || empty( $request_uri ) ) {
            return '';
        }
        $current_url = ( is_ssl() ? 'https://' : 'http://' ) . $http_host . $request_uri;
        $url_parts = parse_url( $current_url );
        $query_params = [];
        if ( ! empty( $url_parts['query'] ) ) {
            parse_str( $url_parts['query'], $query_params );
        }

        // Remove launcher and app_url parameters
        unset( $query_params['launcher'] );
        unset( $query_params['app_url'] );

        // Rebuild URL
        $target_url = $url_parts['scheme'] . '://' . $url_parts['host'];
        if ( ! empty( $url_parts['port'] ) ) {
            $target_url .= ':' . $url_parts['port'];
        }
        $target_url .= $url_parts['path'];

        if ( ! empty( $query_params ) ) {
            $target_url .= '?' . http_build_query( $query_params );
        }

        return $target_url;
    }

    /**
     * Maybe show launcher wrapper page (if launcher=1 parameter is present)
     * This is called via dt_blank_head hook (early priority)
     */
    public function maybe_show_launcher_wrapper() {
        // Check if launcher parameter is present - if so, show wrapper page instead
        if ( $this->has_launcher_parameter() ) {
            error_log( 'DT Home: launcher=1 detected in dt_blank_head, showing wrapper page' );
            $this->show_launcher_wrapper();
            // show_launcher_wrapper() will exit, so this won't continue
        }
    }

    /**
     * Show launcher wrapper page with iframe
     */
    private function show_launcher_wrapper() {
        $target_app_url = $this->get_target_app_url();
        error_log( 'DT Home: Showing launcher wrapper for URL: ' . $target_app_url );

        // Get all apps for the apps selector
        $apps_manager = DT_Home_Apps::instance();
        $apps = $apps_manager->get_apps_for_user( get_current_user_id() );

        // Output complete HTML page
        $target_app_url = $this->get_target_app_url();

        // Include wrapper template
        $wrapper_path = get_template_directory() . '/dt-apps/dt-home/frontend/partials/launcher-wrapper.php';
        if ( file_exists( $wrapper_path ) ) {
            // Set variables for template
            $target_app_url = $this->get_target_app_url();
            include $wrapper_path;
            die(); // Stop all further processing
        } else {
            error_log( 'DT Home: ERROR - Launcher wrapper template not found at: ' . $wrapper_path );
            die( 'Launcher wrapper template not found' );
        }
    }

    public function body() {
        // Check if user is authenticated, redirect to WordPress login if not and require_login is enabled
        if ( ! is_user_logged_in() && function_exists( 'homescreen_require_login' ) && homescreen_require_login() ) {
            $current_url = dt_get_url_path();
            $login_url = wp_login_url( $current_url );
            wp_redirect( $login_url );
            exit;
        }

        // Revert back to dt translations
        $this->hard_switch_to_default_dt_text_domain();

        // Route between Apps (default) and Training view via ?view= param
        $view = isset( $_GET['view'] ) ? strtolower( sanitize_text_field( wp_unslash( $_GET['view'] ) ) ) : 'apps';
        if ( $view === 'training' ) {
            $template = get_template_directory() . '/dt-apps/dt-home/frontend/training-screen.php';
            if ( file_exists( $template ) ) {
                include $template;
                return;
            }
        }

        // Fallback/default: apps view
        include get_template_directory() . '/dt-apps/dt-home/frontend/home-screen.php';
    }

    /**
     * Redirect to dt-home after successful login
     *
     * @param string $redirect_to The redirect destination URL.
     * @param string $requested_redirect_to The requested redirect destination URL passed as a parameter.
     * @param WP_User|WP_Error $user WP_User object if login was successful, WP_Error object otherwise.
     * @return string The redirect URL.
     */
    public function redirect_to_home_after_login( $redirect_to, $requested_redirect_to, $user ) {
        // If there's an error, don't redirect
        if ( is_wp_error( $user ) ) {
            return $redirect_to;
        }

        // If redirect_to points to dt-home, use it
        if ( ! empty( $requested_redirect_to ) && strpos( $requested_redirect_to, 'apps/launcher' ) !== false ) {
            return $requested_redirect_to;
        }

        // Check if user was coming from dt-home before login
        $referer = wp_get_referer();
        if ( $referer && strpos( $referer, 'apps/launcher' ) !== false ) {
            return $referer;
        }

        return $redirect_to;
    }

    /**
     * Redirect to dt-home after successful registration
     *
     * @param int $user_id The newly registered user ID.
     */
    public function redirect_to_home_after_registration( $user_id ) {
        // Check if registration came from dt-home
        $referer = wp_get_referer();
        if ( $referer && strpos( $referer, 'apps/launcher' ) !== false ) {
            // Set auth cookie and redirect
            wp_set_current_user( $user_id );
            wp_set_auth_cookie( $user_id );

            // Get the magic link URL for the newly registered user
            $magic_key = get_user_option( $this->meta_key, $user_id );
            if ( empty( $magic_key ) ) {
                // Generate magic key if it doesn't exist
                $magic_key = dt_create_unique_key();
                update_user_option( $user_id, $this->meta_key, $magic_key );
            }

            $home_url = DT_Magic_URL::get_link_url( $this->root, $this->type, $magic_key );
            wp_safe_redirect( $home_url );
            exit;
        }
    }

    /**
     * Add redirect_to parameter to registration URL if coming from dt-home
     *
     * @param string $url The registration URL.
     * @return string The modified registration URL.
     */
    public function add_redirect_to_registration_url( $url ) {
        $referer = wp_get_referer();
        if ( $referer && strpos( $referer, 'apps/launcher' ) !== false ) {
            return add_query_arg( 'redirect_to', urlencode( $referer ), $url );
        }
        return $url;
    }

    /**
     * Register REST Endpoints
     * @link https://github.com/DiscipleTools/disciple-tools-theme/wiki/Site-to-Site-Link for outside of wordpress authentication
     */
    public function add_endpoints() {
        $namespace = $this->root . '/v1';

        register_rest_route(
            $namespace, '/' . $this->type, [
                [
                    'methods'             => 'GET',
                    'callback'            => [ $this, 'endpoint_get' ],
                    'permission_callback' => function ( WP_REST_Request $request ) {
                        $magic = new DT_Magic_URL( $this->root );
                        return $magic->verify_rest_endpoint_permissions_on_post( $request );
                    },
                ],
            ]
        );
    }

    public function endpoint_get( WP_REST_Request $request ) {
        $params = $request->get_params();

        // Debug logging
        error_log( 'DT Home Screen REST endpoint called' );
        error_log( 'Request params: ' . print_r( $params, true ) );

        if ( ! isset( $params['parts'], $params['action'] ) ) {
            error_log( 'Missing parameters - parts: ' . ( isset( $params['parts'] ) ? 'yes' : 'no' ) . ', action: ' . ( isset( $params['action'] ) ? 'yes' : 'no' ) );
            return new WP_Error( __METHOD__, 'Missing parameters', [ 'status' => 400 ] );
        }

        // Sanitize and fetch user id
        $params  = dt_recursive_sanitize_array( $params );
        $user_id = $params['parts']['post_id'];
        $action = $params['action'];

        // Handle different actions
        if ( $action === 'get_apps' ) {
            // Get apps only (filtered by user permissions)
            $apps_manager = DT_Home_Apps::instance();
            $apps = $apps_manager->get_apps_for_user( $user_id );

            error_log( 'Apps found: ' . count( $apps ) );

            return [
                'success' => true,
                'apps' => $apps,
                'message' => 'Apps loaded successfully'
            ];

        } elseif ( $action === 'get_training' ) {
            // Get training videos only
            $training_manager = DT_Home_Training::instance();
            $training_videos = $training_manager->get_videos_for_frontend();

            error_log( 'Training videos found: ' . count( $training_videos ) );

            return [
                'success' => true,
                'training_videos' => $training_videos,
                'message' => 'Training videos loaded successfully'
            ];

        } else {
            // Default: return both apps and training videos
            $apps_manager = DT_Home_Apps::instance();
            $training_manager = DT_Home_Training::instance();

            // Get apps filtered by user permissions
            $apps = $apps_manager->get_apps_for_user( $user_id );
            $training_videos = $training_manager->get_videos_for_frontend();

            error_log( 'Apps found: ' . count( $apps ) );
            error_log( 'Training videos found: ' . count( $training_videos ) );

            return [
                'success' => true,
                'user_id' => $user_id,
                'apps' => $apps,
                'training_videos' => $training_videos,
                'message' => 'Home screen data loaded successfully'
            ];
        }
    }
}

DT_Home_Magic_Link_App::instance();
