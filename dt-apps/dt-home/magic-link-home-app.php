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
            add_action( 'dt_blank_head', [ $this, 'maybe_enqueue_launcher_nav_css' ], 10 );
            add_action( 'dt_blank_footer', [ $this, 'maybe_include_launcher_nav' ], 10 );
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

        // REST API endpoints are registered in add_endpoints() method
    }

    public function wp_enqueue_scripts() {
        // Enqueue Material Design Icons for magic link apps
        wp_enqueue_style( 'material-font-icons-css', 'https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css', [], '7.4.47' );

        // Enqueue home screen specific styles (includes launcher nav styles)
        wp_enqueue_style( 'dt-home-style', get_template_directory_uri() . '/dt-apps/dt-home/assets/css/home-screen.css', [], '1.0.19' );

        // Enqueue theme toggle JavaScript
        wp_enqueue_script( 'dt-home-theme-toggle', get_template_directory_uri() . '/dt-apps/dt-home/assets/js/theme-toggle.js', [], '1.0.0', true );

        // Enqueue menu toggle JavaScript
        wp_enqueue_script( 'dt-home-menu-toggle', get_template_directory_uri() . '/dt-apps/dt-home/assets/js/menu-toggle.js', [], '1.0.0', true );

        // Pass logout URL to menu toggle script
        if ( function_exists( 'dt_home_get_logout_url' ) ) {
            wp_localize_script(
                'dt-home-menu-toggle',
                'dtHomeMenuToggleSettings',
                [
                    'logoutUrl' => dt_home_get_logout_url(),
                ]
            );
        }
    }

    public function dt_magic_url_base_allowed_js( $allowed_js ) {
        $allowed_js[] = 'dt-home-theme-toggle';
        $allowed_js[] = 'dt-home-menu-toggle';
        return $allowed_js;
    }

    public function dt_magic_url_base_allowed_css( $allowed_css ) {
        // @todo add or remove css files with this filter
        $allowed_css[] = 'material-font-icons-css';
        $allowed_css[] = 'dt-home-style'; // Include launcher nav styles for other magic link apps

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
        ?>
        <style>
            body {
                background-color: #f8f9fa;
                padding: 1em;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            }

            .home-screen-container {
                width: 100%;
                margin: 0 auto;
                background: transparent !important;
                box-sizing: border-box;
            }

            /* Desktop: Narrower max-width for better readability */
            @media (min-width: 768px) {
                .home-screen-container {
                    max-width: 480px;
                }
            }

            /* Larger desktop screens: Slightly wider but still narrow */
            @media (min-width: 1024px) {
                .home-screen-container {
                    max-width: 520px;
                }
            }

            /* Mobile: Full width (no max-width constraint) */
            @media (max-width: 767px) {
                .home-screen-container {
                    max-width: 100%;
                    width: 100%;
                }
            }
                /*border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);*/
                overflow: hidden;
            }

            .home-screen-header {
                background: transparent;
                padding: 1.5rem 2rem;
            }

            .header-table,
            .header-table tbody,
            .header-table tr,
            .header-table th,
            .header-table td {
                background-color: transparent !important;
                background: transparent !important;
                border: none !important;
                border-color: transparent !important;
                border-width: 0 !important;
            }

            .header-table {
                width: 100% !important;
                border-collapse: collapse !important;
                border-spacing: 0 !important;
                border: none !important;
            }

            .header-table tbody {
                border: none !important;
                border-color: transparent !important;
                border-top: none !important;
                border-bottom: none !important;
                border-left: none !important;
                border-right: none !important;
            }

            .header-table tr {
                border: none !important;
                border-color: transparent !important;
                border-top: none !important;
                border-bottom: none !important;
                border-left: none !important;
                border-right: none !important;
            }

            .header-table th,
            .header-table td {
                border-top: none !important;
                border-bottom: none !important;
                border-left: none !important;
                border-right: none !important;
            }

            .header-table td,
            .header-table td.header-content-cell,
            td.header-content-cell {
                vertical-align: top !important;
                width: auto !important;
                padding: 0 !important;
                background-color: transparent !important;
                background: transparent !important;
                border: none !important;
            }

            .header-table td.header-controls-cell,
            td.header-controls-cell {
                vertical-align: top !important;
                text-align: right !important;
                width: 1% !important;
                padding: 0 !important;
                padding-left: 2rem !important;
                white-space: nowrap !important;
                background-color: transparent !important;
                background: transparent !important;
                border: none !important;
            }

            .header-controls {
                display: flex !important;
                align-items: flex-start !important;
                justify-content: flex-end !important;
                gap: 0.75rem !important;
                flex-wrap: nowrap !important;
                flex-shrink: 0 !important;
            }

            /* Inline button styles to ensure they are visible even if external CSS is overridden */
            .theme-toggle-button,
            .menu-toggle-button {
                background: transparent !important;
                border: 1px solid var(--border-color, #e1e5e9) !important;
                border-radius: 50% !important;
                width: 40px !important;
                height: 40px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                cursor: pointer !important;
                transition: all 0.3s ease !important;
                position: relative !important;
                flex-shrink: 0 !important;
                white-space: nowrap !important;
                appearance: none !important;
                -webkit-appearance: none !important;
                -moz-appearance: none !important;
                background-image: none !important;
                background-size: none !important;
            }

            .theme-toggle-button::before,
            .theme-toggle-button::after,
            .menu-toggle-button::before,
            .menu-toggle-button::after {
                display: none !important;
                content: none !important;
            }

            .theme-toggle-button:hover,
            .menu-toggle-button:hover {
                background: var(--surface-2, #f1f3f5) !important;
                border-color: var(--primary-color, #667eea) !important;
                transform: scale(1.05);
            }

            .theme-toggle-button:active,
            .menu-toggle-button:active {
                transform: scale(0.95);
            }

            .theme-icon,
            .dt-menu-icon {
                color: var(--text-color, #0a0a0a) !important;
                font-size: 24px !important;
                transition: all 0.3s ease !important;
                display: inline-block !important;
                line-height: 1 !important;
                position: relative !important;
                z-index: 1 !important;
            }

            .theme-toggle-button:hover .theme-icon,
            .menu-toggle-button:hover .dt-menu-icon {
                color: var(--primary-color, #667eea) !important;
                transform: scale(1.1);
            }

            /* Floating Menu Inline Styles */
            .floating-menu {
                position: fixed !important;
                display: none !important;
                flex-direction: column !important;
                background: var(--surface-1, #ffffff) !important;
                border: 2px solid var(--border-color, #e1e5e9) !important;
                border-radius: 8px !important;
                box-shadow: 0 4px 12px var(--shadow-color, rgba(0,0,0,0.1)) !important;
                min-width: 220px !important;
                max-width: 280px !important;
                z-index: 1000 !important;
                opacity: 0 !important;
                visibility: hidden !important;
                padding: 0.5rem 0 !important;
                pointer-events: none !important;
                overflow: hidden !important;
            }

            .floating-menu.active {
                display: flex !important;
                opacity: 1 !important;
                visibility: visible !important;
                pointer-events: auto !important;
            }

            .menu-item {
                display: flex !important;
                flex-direction: row !important;
                align-items: center !important;
                justify-content: flex-start !important;
                gap: 0.75rem !important;
                width: 100% !important;
                padding: 1rem 1.5rem !important;
                margin: 0 !important;
                background: transparent !important;
                border: none !important;
                border-bottom: 1px solid var(--border-color, #e1e5e9) !important;
                color: var(--text-color, #0a0a0a) !important;
                text-decoration: none !important;
                font-size: 0.95rem !important;
                cursor: pointer !important;
                text-align: left !important;
                box-sizing: border-box !important;
                line-height: 1.5 !important;
            }

            .menu-item:last-child {
                border-bottom: none !important;
            }

            .menu-item:hover {
                background: var(--surface-2, #f1f3f5) !important;
                color: var(--primary-color, #667eea) !important;
            }

            .menu-item i {
                font-size: 20px !important;
                width: 24px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                flex-shrink: 0 !important;
            }

            .menu-item span {
                flex: 1 !important;
                text-align: left !important;
            }

            .home-screen-header h1 {
                margin: 0;
                font-size: 2rem;
                font-weight: 800;
                text-align: left;
            }

            .home-screen-header p {
                margin: 0.5rem 0 0 0;
                opacity: 0.85;
                font-size: 0.9rem;
                text-align: left;
            }

            .home-screen-content {
                padding: 2rem;
            }

            /* Training Video Card Inline Styles */
            .training-card {
                background: var(--surface-1, #ffffff) !important;
                border: 1px solid var(--border-color, #e1e5e9) !important;
                border-radius: 12px !important;
                overflow: hidden !important;
                transition: all 0.3s ease !important;
                cursor: pointer !important;
                display: flex !important;
                flex-direction: column !important;
                width: 100% !important;
                max-width: 100% !important;
                min-width: 0 !important;
                margin: 0 !important;
                box-shadow: 0 2px 8px var(--shadow-color, rgba(0,0,0,0.1)) !important;
            }

            .training-card:hover {
                transform: translateY(-2px) !important;
                box-shadow: 0 4px 12px var(--shadow-color, rgba(0,0,0,0.15)) !important;
                border-color: var(--primary-color, #667eea) !important;
            }

            .training-card.playing {
                box-shadow: 0 4px 16px var(--shadow-color, rgba(0,0,0,0.2)) !important;
                border-color: var(--primary-color, #667eea) !important;
            }

            /* Video Info Header - Title and Duration on same line */
            .training-video-info {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                padding: 0.75rem 1rem !important;
                background: var(--surface-2, #f8f9fa) !important;
                border-bottom: 1px solid var(--border-color, #e1e5e9) !important;
                gap: 1rem !important;
            }

            .training-video-title-text {
                flex: 1 !important;
                font-size: 0.95rem !important;
                font-weight: 600 !important;
                color: var(--text-color, #0a0a0a) !important;
                margin: 0 !important;
                text-align: left !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                white-space: nowrap !important;
            }

            .training-video-duration-badge {
                display: inline-flex !important;
                align-items: center !important;
                padding: 0.25rem 0.5rem !important;
                background: var(--surface-1, #ffffff) !important;
                border: 1px solid var(--border-color, #e1e5e9) !important;
                border-radius: 4px !important;
                font-size: 0.75rem !important;
                font-weight: 500 !important;
                color: var(--text-color, #0a0a0a) !important;
                flex-shrink: 0 !important;
                white-space: nowrap !important;
            }

            /* Thumbnail Container with Splash Screen */
            .training-video-thumbnail-container {
                position: relative !important;
                width: 100% !important;
                padding-bottom: 56.25% !important; /* 16:9 aspect ratio */
                background: #000 !important;
                overflow: hidden !important;
                border-radius: 0 0 12px 12px !important;
            }

            .training-video-thumbnail {
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
                width: 100% !important;
                height: 100% !important;
                object-fit: cover !important;
                border-radius: 0 !important;
            }

            .training-video-overlay {
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                bottom: 0 !important;
                background: rgba(0, 0, 0, 0.3) !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                transition: background 0.3s ease !important;
                z-index: 2 !important;
            }

            .training-card:hover .training-video-overlay {
                background: rgba(0, 0, 0, 0.5) !important;
            }

            .training-play-button {
                width: 60px !important;
                height: 60px !important;
                background: rgba(255, 255, 255, 0.95) !important;
                border-radius: 50% !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                color: var(--primary-color, #667eea) !important;
                font-size: 1.75rem !important;
                transition: all 0.3s ease !important;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3) !important;
                border: 3px solid rgba(255, 255, 255, 0.8) !important;
            }

            .training-card:hover .training-play-button {
                transform: scale(1.1) !important;
                background: white !important;
                box-shadow: 0 6px 16px rgba(0, 0, 0, 0.4) !important;
            }

            /* Old duration and title positions (hidden) */
            .training-video-duration {
                display: none !important;
            }

            .training-video-title {
                display: none !important;
            }

            /* Embedded Video Container */
            .training-video-embed {
                position: relative !important;
                width: 100% !important;
                padding-bottom: 56.25% !important; /* 16:9 aspect ratio */
                background: #000 !important;
                border-radius: 0 0 12px 12px !important;
                overflow: hidden !important;
            }

            .training-video-embed iframe {
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
                width: 100% !important;
                height: 100% !important;
                border: none !important;
                border-radius: 0 0 12px 12px !important;
            }

            .training-video-external-link {
                position: absolute !important;
                top: 0.75rem !important;
                right: 0.75rem !important;
                z-index: 10 !important;
            }

            .external-link-button {
                display: inline-flex !important;
                align-items: center !important;
                gap: 0.25rem !important;
                background: rgba(0, 0, 0, 0.8) !important;
                color: white !important;
                padding: 0.4rem 0.6rem !important;
                border-radius: 6px !important;
                text-decoration: none !important;
                font-size: 0.75rem !important;
                font-weight: 500 !important;
                transition: all 0.2s ease !important;
                backdrop-filter: blur(4px) !important;
                border: 1px solid rgba(255, 255, 255, 0.2) !important;
            }

            .external-link-button:hover {
                background: rgba(0, 0, 0, 0.9) !important;
                color: white !important;
                text-decoration: none !important;
                transform: translateY(-1px) !important;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3) !important;
            }

            .external-link-button i {
                font-size: 0.9rem !important;
            }

            /* Training Grid - One video per line with increased width */
            .training-grid {
                display: grid !important;
                grid-template-columns: 1fr !important;
                gap: 1.5rem !important;
                margin-bottom: 2rem !important;
                padding: 0 !important;
                width: 100% !important;
            }

            .training-grid .training-card {
                width: 100% !important;
                max-width: 100% !important;
                height: auto !important;
            }

            @media (max-width: 768px) {
                .training-grid {
                    grid-template-columns: 1fr !important;
                    gap: 1rem !important;
                }

                .training-card {
                    width: 100% !important;
                    max-width: 100% !important;
                }

                .training-play-button {
                    width: 50px !important;
                    height: 50px !important;
                    font-size: 1.5rem !important;
                }
            }

            @media (max-width: 480px) {
                .training-video-info {
                    padding: 0.5rem 0.75rem !important;
                }

                .training-video-title-text {
                    font-size: 0.85rem !important;
                }

                .training-video-duration-badge {
                    font-size: 0.7rem !important;
                    padding: 0.2rem 0.4rem !important;
                }

            .external-link-button span {
                display: none !important;
            }
        }

        /* Invite Modal Inline Styles */
        .invite-modal-overlay {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            background: rgba(0, 0, 0, 0.35) !important;
            z-index: 9999 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 20px !important;
            box-sizing: border-box !important;
        }

        .invite-modal-content {
            background: var(--surface-1, #fff) !important;
            color: var(--text-color, #000) !important;
            border-radius: 12px !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.18) !important;
            min-width: 320px !important;
            max-width: 95vw !important;
            width: 100% !important;
            max-width: 400px !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
            position: relative !important;
        }

        .theme-dark .invite-modal-content,
        .invite-modal-content.theme-dark {
            background: var(--surface-1, #2a2a2a) !important;
            color: var(--text-color, #f5f5f5) !important;
        }

        .invite-modal-header {
            padding: 20px 20px 0 20px !important;
        }

        .invite-modal-title {
            margin: 0 !important;
            font-size: 1.25rem !important;
            font-weight: 700 !important;
            color: var(--text-color, #000) !important;
            margin-bottom: 0 !important;
        }

        .theme-dark .invite-modal-title,
        .invite-modal-content.theme-dark .invite-modal-title {
            color: var(--text-color, #f5f5f5) !important;
        }

        .invite-modal-separator {
            height: 1px !important;
            background: var(--border-color, #e1e5e9) !important;
            margin-top: 12px !important;
            margin-bottom: 0 !important;
        }

        .theme-dark .invite-modal-separator,
        .invite-modal-content.theme-dark .invite-modal-separator {
            background: var(--border-color, #404040) !important;
        }

        .invite-modal-body {
            padding: 20px !important;
            flex: 1 !important;
        }

        .invite-explanation-text {
            margin: 0 0 20px 0 !important;
            color: var(--text-color, #666) !important;
            line-height: 1.5 !important;
            font-size: 0.95rem !important;
        }

        .theme-dark .invite-explanation-text,
        .invite-modal-content.theme-dark .invite-explanation-text {
            color: var(--text-color, #ffffff) !important;
        }

        .invite-share-link-container {
            display: flex !important;
            gap: 10px !important;
            align-items: center !important;
            margin-bottom: 20px !important;
        }

        .invite-url-input {
            flex: 1 !important;
            padding: 10px 12px !important;
            border: 1px solid var(--border-color, #e1e5e9) !important;
            border-radius: 6px !important;
            font-size: 0.9rem !important;
            font-family: inherit !important;
            background: var(--surface-0, #fff) !important;
            color: var(--text-color, #000) !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            box-sizing: border-box !important;
            min-width: 0 !important;
        }

        .theme-dark .invite-url-input,
        .invite-modal-content.theme-dark .invite-url-input {
            background: var(--surface-0, #2a2a2a) !important;
            border-color: var(--border-color, #404040) !important;
            color: var(--text-color, #f5f5f5) !important;
        }

        .invite-url-input:focus {
            outline: 2px solid var(--primary-color, #667eea) !important;
            outline-offset: -1px !important;
        }

        .invite-copy-button {
            flex-shrink: 0 !important;
            padding: 10px 20px !important;
            background: var(--primary-color, #667eea) !important;
            color: #fff !important;
            border: none !important;
            border-radius: 6px !important;
            font-size: 0.9rem !important;
            font-weight: 600 !important;
            font-family: inherit !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
            white-space: nowrap !important;
        }

        .invite-copy-button:hover {
            background: var(--primary-color, #5568d3) !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3) !important;
        }

        .invite-copy-button:active {
            transform: translateY(0) !important;
        }

        .invite-copy-button:focus {
            outline: 2px solid var(--primary-color, #667eea) !important;
            outline-offset: 2px !important;
        }

        .theme-dark .invite-copy-button,
        .invite-modal-content.theme-dark .invite-copy-button {
            background: var(--primary-color, #4a9eff) !important;
        }

        .theme-dark .invite-copy-button:hover,
        .invite-modal-content.theme-dark .invite-copy-button:hover {
            background: #3a8eef !important;
        }

        .invite-success-message {
            color: #4caf50 !important;
            font-size: 0.875rem !important;
            margin-top: 10px !important;
            display: none !important;
            font-weight: 500 !important;
        }

        .invite-success-message.show {
            display: block !important;
            animation: fadeIn 0.3s ease !important;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .invite-modal-footer {
            display: flex !important;
            justify-content: flex-end !important;
            padding: 0 20px 20px 20px !important;
            gap: 10px !important;
        }

        .invite-close-button {
            padding: 10px 20px !important;
            background: var(--surface-2, #f5f5f5) !important;
            color: var(--text-color, #000) !important;
            border: 1px solid var(--border-color, #e1e5e9) !important;
            border-radius: 6px !important;
            font-size: 0.9rem !important;
            font-weight: 500 !important;
            font-family: inherit !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
        }

        .invite-close-button:hover {
            background: var(--surface-2, #e5e5e5) !important;
            border-color: var(--border-color, #d1d5d9) !important;
        }

        .invite-close-button:focus {
            outline: 2px solid var(--primary-color, #667eea) !important;
            outline-offset: 2px !important;
        }

        .theme-dark .invite-close-button,
        .invite-modal-content.theme-dark .invite-close-button {
            background: var(--surface-2, #333333) !important;
            border-color: var(--border-color, #404040) !important;
            color: var(--text-color, #f5f5f5) !important;
        }

        .theme-dark .invite-close-button:hover,
        .invite-modal-content.theme-dark .invite-close-button:hover {
            background: var(--surface-2, #3a3a3a) !important;
        }

        /* Responsive adjustments for invite modal */
        @media (max-width: 480px) {
            .invite-modal-content {
                min-width: 280px !important;
                max-width: 95vw !important;
            }

            .invite-modal-header,
            .invite-modal-body,
            .invite-modal-footer {
                padding-left: 16px !important;
                padding-right: 16px !important;
            }

            .invite-share-link-container {
                flex-direction: column !important;
                gap: 8px !important;
            }

            .invite-url-input {
                width: 100% !important;
            }

            .invite-copy-button {
                width: 100% !important;
            }
        }

        .apps-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 1.5rem;
                margin-bottom: 2rem;
            }

            /* CSS Variables for theme-aware app cards */
            :root {
                --app-card-bg: #ffffff;
                --app-card-border: #e1e5e9;
                --app-card-text: #0a0a0a;
                --app-card-shadow: rgba(0,0,0,0.1);
                --app-card-hover-border: #667eea;
            }

            /* Support theme class on html element for early initialization */
            html.theme-dark,
            .theme-dark {
                --app-card-bg: #2a2a2a;
                --app-card-border: #404040;
                --app-card-text: #f5f5f5;
                --app-card-shadow: rgba(0,0,0,0.3);
                --app-card-hover-border: #4a9eff;
            }

            body.theme-dark,
            html.theme-dark body {
                --app-card-bg: #2a2a2a;
                --app-card-border: #404040;
                --app-card-text: #f5f5f5;
                --app-card-shadow: rgba(0,0,0,0.3);
                --app-card-hover-border: #4a9eff;
            }

            .app-card {
                background: var(--app-card-bg, #ffffff) !important;
                border: 1px solid var(--app-card-border, #e1e5e9) !important;
                color: var(--app-card-text, #0a0a0a) !important;
                border-radius: 8px !important;
                padding: 1.5rem !important;
                text-align: center !important;
                transition: all 0.3s ease !important;
                cursor: pointer !important;
            }

            .app-card:hover {
                transform: translateY(-2px) !important;
                box-shadow: 0 4px 20px var(--app-card-shadow, rgba(0,0,0,0.1)) !important;
                border-color: var(--app-card-hover-border, #667eea) !important;
            }

            /* Dark mode styles for app cards (backup for theme-dark class) */
            .theme-dark .app-card,
            body.theme-dark .app-card {
                background: var(--app-card-bg, #2a2a2a) !important;
                border-color: var(--app-card-border, #404040) !important;
                color: var(--app-card-text, #f5f5f5) !important;
            }

            .theme-dark .app-card:hover,
            body.theme-dark .app-card:hover {
                box-shadow: 0 4px 20px var(--app-card-shadow, rgba(0,0,0,0.3)) !important;
                border-color: var(--app-card-hover-border, #4a9eff) !important;
            }

            .app-icon {
                font-size: 3rem;
                margin-bottom: 1rem;
                color: #0a0a0a; /* Black default for light mode - JavaScript will override with theme-aware defaults or custom colors */
                border-radius: 0 !important;
                background: transparent !important;
            }

            /* Note: Dark mode colors are handled by JavaScript to preserve custom colors */

            .app-title {
                font-size: 1.2rem;
                font-weight: 600;
                margin-bottom: 0.5rem;
                color: #2c3e50;
            }

            /* Dark mode styles for app titles */
            .theme-dark .app-title,
            body.theme-dark .app-title {
                color: #f5f5f5 !important;
            }

            .app-description {
                color: #6c757d;
                font-size: 0.9rem;
                line-height: 1.4;
            }

            .training-section {
                margin-top: 0;
                padding-top: 0;
            }

            .section-title {
                font-size: 1.75rem;
                font-weight: 700;
                margin-bottom: 2rem;
                color: #2c3e50;
            }

            .loading-spinner {
                display: inline-block;
                width: 32px;
                height: 32px;
                border: 3px solid rgba(102, 126, 234, 0.2);
                border-top: 3px solid #667eea;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            /* Links Section Styles */
            .links-list {
                display: flex !important;
                flex-direction: column !important;
                gap: 16px !important;
                padding: 20px 0 !important;
            }

            .link-item {
                display: flex !important;
                align-items: center !important;
                padding: 16px !important;
                border-radius: 12px !important;
                background-color: var(--dt-tile-background-color, #ffffff) !important;
                box-shadow: var(--shadow-0, 0 4px 12px rgba(0, 0, 0, 0.08)) !important;
                transition: all 0.3s ease !important;
                position: relative !important;
                overflow: hidden !important;
                cursor: pointer !important;
            }

            .link-item:hover {
                transform: translateY(-2px) !important;
                box-shadow: var(--shadow-1, 0 6px 16px rgba(0, 0, 0, 0.12)) !important;
            }

            .link-item:before {
                content: '' !important;
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                height: 100% !important;
                width: 4px !important;
                background: linear-gradient(45deg, #4a90e2, #63b3ed) !important;
                border-radius: 4px 0 0 4px !important;
            }

            .link-item__icon {
                width: 40px !important;
                height: 40px !important;
                margin-right: 16px !important;
                flex-shrink: 0 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }

            .link-item__icon img {
                max-width: 100% !important;
                max-height: 100% !important;
                object-fit: contain !important;
                filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1)) !important;
            }

            .link-item__icon i {
                font-size: 24px !important;
                filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1)) !important;
            }

            .link-item__content {
                flex: 1 !important;
                min-width: 0 !important;
                padding: 0 8px !important;
            }

            .link-item__title {
                font-size: 16px !important;
                font-weight: 600 !important;
                color: var(--text-color, #1a202c) !important;
                margin-bottom: 4px !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                transition: color 0.3s ease !important;
            }

            .link-item__url {
                color: var(--gray-0, #718096) !important;
                text-decoration: none !important;
                font-size: 13px !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                display: block !important;
                transition: color 0.3s ease !important;
            }

            .link-item__url:hover {
                color: var(--primary-color, #4a90e2) !important;
            }

            .link-item__copy {
                background: linear-gradient(45deg, var(--primary-color, #4a90e2), var(--primary-color-light-1, #63b3ed)) !important;
                color: var(--text-color-inverse, white) !important;
                border: none !important;
                width: 40px !important;
                height: 40px !important;
                border-radius: 50% !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                cursor: pointer !important;
                margin-left: 16px !important;
                flex-shrink: 0 !important;
                transition: all 0.3s ease !important;
                box-shadow: var(--shadow-0, 0 2px 6px rgba(74, 144, 226, 0.3)) !important;
            }

            .link-item__copy i {
                font-size: 18px !important;
            }

            .link-item__copy:hover {
                transform: translateY(-1px) rotate(5deg) !important;
                box-shadow: var(--shadow-1, 0 4px 8px rgba(74, 144, 226, 0.4)) !important;
            }

            .link-item__copy.copied {
                background: linear-gradient(45deg, #38a169, #68d391) !important;
            }

            /* Dark mode styles for links */
            .theme-dark .link-item,
            body.theme-dark .link-item {
                background-color: var(--surface-1, #2a2a2a) !important;
            }

            .theme-dark .link-item__title,
            body.theme-dark .link-item__title {
                color: var(--text-color, #f5f5f5) !important;
            }

            .theme-dark .link-item__url,
            body.theme-dark .link-item__url {
                color: var(--gray-0, #a0aec0) !important;
            }
        </style>
        <?php
    }

    /**
     * Writes javascript to the header
     *
     * @see DT_Magic_Url_Base()->header_javascript() for default state
     */
    public function header_javascript() {
        ?>
        <script>
            // Initialize theme CSS variables immediately before page render
            (function() {
                // Get saved theme preference or system preference
                const savedTheme = localStorage.getItem('dt-home-theme');
                const systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                const initialTheme = savedTheme || (systemPrefersDark ? 'dark' : 'light');

                // Set CSS variables immediately on root
                const root = document.documentElement;
                if (initialTheme === 'dark') {
                    root.classList.add('theme-dark');
                    root.classList.remove('theme-light');
                    root.style.setProperty('--app-card-bg', '#2a2a2a');
                    root.style.setProperty('--app-card-border', '#404040');
                    root.style.setProperty('--app-card-text', '#f5f5f5');
                    root.style.setProperty('--app-card-shadow', 'rgba(0,0,0,0.3)');
                    root.style.setProperty('--app-card-hover-border', '#4a9eff');
                } else {
                    root.classList.add('theme-light');
                    root.classList.remove('theme-dark');
                    root.style.setProperty('--app-card-bg', '#ffffff');
                    root.style.setProperty('--app-card-border', '#e1e5e9');
                    root.style.setProperty('--app-card-text', '#0a0a0a');
                    root.style.setProperty('--app-card-shadow', 'rgba(0,0,0,0.1)');
                    root.style.setProperty('--app-card-hover-border', '#667eea');
                }

                // Also set on body when it becomes available
                if (document.body) {
                    document.body.classList.remove('theme-light', 'theme-dark');
                    document.body.classList.add(`theme-${initialTheme}`);
                } else {
                    // Wait for body to be available
                    const observer = new MutationObserver(function(mutations) {
                        if (document.body) {
                            document.body.classList.remove('theme-light', 'theme-dark');
                            document.body.classList.add(`theme-${initialTheme}`);
                            observer.disconnect();
                        }
                    });
                    observer.observe(document.documentElement, { childList: true, subtree: true });
                }
            })();
        </script>
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
                ]
            ] ) ?>][0];

            /**
             * Initialize home screen
             */
            jQuery(document).ready(function($) {
                // Initialize theme system
                initializeTheme();

                // Initialize collapsible sections (no-ops if elements missing)
                initializeCollapsibleSections();

                // Check for login errors in URL parameters
                const urlParams = new URLSearchParams(window.location.search);
                const loginError = urlParams.get('login') === 'failed' || urlParams.get('error') === 'incorrect_password';
                
                // Check authentication before loading content
                if (jsObject.user_id === 0) {
                    // User not authenticated - show login modal
                    showLoginModal();
                    
                    // If there's a login error, show it
                    if (loginError) {
                        setTimeout(() => {
                            showLoginError('Invalid username or password. Please try again.');
                        }, 100);
                    }
                    
                    // Don't load apps/links until authenticated
                } else {
                    // User authenticated - proceed with normal flow
                    // Determine current view from URL (?view=apps|training)
                    const params = new URLSearchParams(window.location.search);
                    const view = (params.get('view') || 'apps').toLowerCase();

                    if (view === 'training') {
                        loadTrainingVideos();
                    } else {
                        // default to apps
                        loadApps();
                    }
                }
            });

            /**
             * Initialize theme system
             */
            function initializeTheme() {
                // Apply initial theme based on saved preference or system preference
                const savedTheme = localStorage.getItem('dt-home-theme');
                const systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                const initialTheme = savedTheme || (systemPrefersDark ? 'dark' : 'light');

                // Apply theme to html element (for early initialization)
                const html = document.documentElement;
                html.classList.remove('theme-light', 'theme-dark');
                html.classList.add(`theme-${initialTheme}`);

                // Apply theme to body
                if (document.body) {
                    document.body.classList.remove('theme-light', 'theme-dark');
                    document.body.classList.add(`theme-${initialTheme}`);
                }

                // Set CSS custom properties
                const root = document.documentElement;
                if (initialTheme === 'dark') {
                    root.style.setProperty('--theme-mode', 'dark');
                    root.style.setProperty('--body-background-color', '#1a1a1a');
                    root.style.setProperty('--text-color', '#f5f5f5');
                    root.style.setProperty('--surface-0', '#2a2a2a');
                    root.style.setProperty('--surface-1', '#1a1a1a');
                    root.style.setProperty('--surface-2', '#333333');
                    root.style.setProperty('--primary-color', '#4a9eff');
                    root.style.setProperty('--border-color', '#404040');
                    root.style.setProperty('--shadow-color', 'rgba(0,0,0,0.3)');
                    // App card CSS variables for dark mode
                    root.style.setProperty('--app-card-bg', '#2a2a2a');
                    root.style.setProperty('--app-card-border', '#404040');
                    root.style.setProperty('--app-card-text', '#f5f5f5');
                    root.style.setProperty('--app-card-shadow', 'rgba(0,0,0,0.3)');
                    root.style.setProperty('--app-card-hover-border', '#4a9eff');
                } else {
                    root.style.setProperty('--theme-mode', 'light');
                    root.style.setProperty('--body-background-color', '#e2e2e2');
                    root.style.setProperty('--text-color', '#0a0a0a');
                    root.style.setProperty('--surface-0', '#e2e2e2');
                    root.style.setProperty('--surface-1', 'hsla(0, 0%, 90%, 1)');
                    root.style.setProperty('--surface-2', '#c2bfbf');
                    root.style.setProperty('--primary-color', '#667eea');
                    root.style.setProperty('--border-color', '#e1e5e9');
                    root.style.setProperty('--shadow-color', 'rgba(0,0,0,0.1)');
                    // App card CSS variables for light mode
                    root.style.setProperty('--app-card-bg', '#ffffff');
                    root.style.setProperty('--app-card-border', '#e1e5e9');
                    root.style.setProperty('--app-card-text', '#0a0a0a');
                    root.style.setProperty('--app-card-shadow', 'rgba(0,0,0,0.1)');
                    root.style.setProperty('--app-card-hover-border', '#667eea');
                }

                // Also set on document root for broader compatibility
                document.documentElement.style.setProperty('--theme-mode', initialTheme);

                // Force body background color change
                document.body.style.backgroundColor = initialTheme === 'dark' ? '#1a1a1a' : '#e2e2e2';

                // Apply direct styles for initial theme
                applyDirectStyles(initialTheme);
            }

            /**
             * Apply direct styles to elements
             */
            function applyDirectStyles(theme) {
                const container = document.querySelector('.home-screen-container');
                const content = document.querySelector('.home-screen-content');
                const appCards = document.querySelectorAll('.app-card');
                const appTitles = document.querySelectorAll('.app-title');
                const sectionTitles = document.querySelectorAll('.section-title');
                const sectionToggles = document.querySelectorAll('.section-toggle');

                if (theme === 'dark') {
                    // Dark mode styles
                    if (container) {
                        container.style.backgroundColor = '#1a1a1a';
                        container.style.color = '#f5f5f5';
                    }
                    if (content) {
                        //content.style.backgroundColor = '#1a1a1a';
                        content.style.color = '#f5f5f5';
                    }
                    appCards.forEach(card => {
                        card.style.setProperty('background-color', '#2a2a2a', 'important');
                        card.style.setProperty('border-color', '#404040', 'important');
                        card.style.setProperty('color', '#f5f5f5', 'important');
                        card.style.setProperty('box-shadow', '0 1px 3px rgba(0,0,0,0.3)', 'important');
                    });
                    appTitles.forEach(title => {
                        title.style.color = '#f5f5f5';
                    });
                    sectionTitles.forEach(title => {
                        title.style.color = '#f5f5f5';
                    });
                    sectionToggles.forEach(toggle => {
                        toggle.style.color = '#4a9eff';
                    });
                } else {
                    // Light mode styles
                    if (container) {
                        container.style.backgroundColor = 'hsla(0, 0%, 90%, 1)';
                        container.style.color = '#0a0a0a';
                    }
                    if (content) {
                        //content.style.backgroundColor = 'hsla(0, 0%, 90%, 1)';
                        content.style.color = '#0a0a0a';
                    }
                    appCards.forEach(card => {
                        card.style.setProperty('background-color', '#ffffff', 'important');
                        card.style.setProperty('border-color', '#e1e5e9', 'important');
                        card.style.setProperty('color', '#0a0a0a', 'important');
                        card.style.setProperty('box-shadow', '0 1px 3px rgba(0,0,0,0.1)', 'important');
                    });
                    appTitles.forEach(title => {
                        title.style.color = '#0a0a0a';
                    });
                    sectionTitles.forEach(title => {
                        title.style.color = '#0a0a0a';
                    });
                    sectionToggles.forEach(toggle => {
                        toggle.style.color = '#667eea';
                    });
                }
            }

            /**
             * Update icon colors when theme changes (only for icons without custom colors)
             * Handles both app icons and link icons
             */
            function updateIconColorsForTheme(theme) {
                const defaultColor = theme === 'dark' ? '#ffffff' : '#0a0a0a';

                // Update app icons
                const appIcons = document.querySelectorAll('.app-icon');
                appIcons.forEach(icon => {
                    // Only update icons that don't have custom colors
                    const hasCustomColor = icon.getAttribute('data-has-custom-color') === 'true';
                    if (!hasCustomColor) {
                        // Set inline style to override CSS default
                        icon.style.setProperty('color', defaultColor, 'important');
                    }
                    // Icons with custom colors are left unchanged
                });

                // Update link icons (same logic for consistency)
                const linkIcons = document.querySelectorAll('.link-item__icon i');
                linkIcons.forEach(icon => {
                    // Only update icons that don't have custom colors
                    const hasCustomColor = icon.getAttribute('data-has-custom-color') === 'true';
                    if (!hasCustomColor) {
                        // Set inline style to override CSS default
                        icon.style.setProperty('color', defaultColor, 'important');
                    }
                    // Icons with custom colors are left unchanged
                });
            }

            /**
             * Listen for theme changes from the toggle component
             */
            document.addEventListener('theme-changed', function(event) {
                // Apply direct styles when theme changes
                applyDirectStyles(event.detail.theme);
                // Update icon colors for theme change
                updateIconColorsForTheme(event.detail.theme);
            });

            /**
             * Initialize collapsible sections with default states
             */
            function initializeCollapsibleSections() {
                // Apps section - Expanded (default)
                const appsContent = document.getElementById('apps-content');
                const appsToggle = document.getElementById('apps-toggle');
                if (appsContent && appsToggle) {
                    appsContent.classList.remove('collapsed');
                    appsContent.classList.add('expanded');
                    appsContent.style.display = 'block';
                    appsToggle.innerHTML = '<i class="mdi mdi-chevron-down"></i>';
                }

                // Training section - Collapsed (default)
                const trainingContent = document.getElementById('training-content');
                const trainingToggle = document.getElementById('training-toggle');
                if (trainingContent && trainingToggle) {
                    trainingContent.classList.remove('expanded');
                    trainingContent.classList.add('collapsed');
                    trainingContent.style.display = 'none';
                    trainingToggle.innerHTML = '<i class="mdi mdi-chevron-right"></i>';
                }

                // Links section - Collapsed (default)
                const linksContent = document.getElementById('links-content');
                const linksToggle = document.getElementById('links-toggle');
                if (linksContent && linksToggle) {
                    linksContent.classList.remove('expanded');
                    linksContent.classList.add('collapsed');
                    linksContent.style.display = 'none';
                    linksToggle.innerHTML = '<i class="mdi mdi-chevron-right"></i>';
                }
            }

            /**
             * Toggle section visibility
             */
            function toggleSection(sectionName) {
                const content = document.getElementById(sectionName + '-content');
                const toggle = document.getElementById(sectionName + '-toggle');

                if (!content || !toggle) return;

                const isCollapsed = content.classList.contains('collapsed');

                if (isCollapsed) {
                    // Expand section
                    content.classList.remove('collapsed');
                    content.classList.add('expanded');
                    content.style.display = 'block';
                    toggle.innerHTML = '<i class="mdi mdi-chevron-down"></i>';
                } else {
                    // Collapse section
                    content.classList.remove('expanded');
                    content.classList.add('collapsed');
                    content.style.display = 'none';
                    toggle.innerHTML = '<i class="mdi mdi-chevron-right"></i>';
                }
            }

            /**
             * Load apps from the server
             */
            function loadApps() {
                // Use REST API endpoint
                $.ajax({
                    type: "GET",
                    data: {
                        action: 'get_apps',
                        parts: jsObject.parts
                    },
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    url: jsObject.root + jsObject.parts.root + '/v1/' + jsObject.parts.type,
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', jsObject.nonce);
                    }
                })
                .done(function (data) {
                    if (data.success && data.apps) {
                        // Split apps into apps and links arrays based on type property
                        const appsArray = [];
                        const linksArray = [];

                        data.apps.forEach(function(app) {
                            // Determine type: if type exists use it, otherwise use fallback logic
                            let appType = app.type;
                            if (!appType || (appType !== 'app' && appType !== 'link')) {
                                // Fallback logic: if creation_type is 'coded', default to 'app', otherwise 'link'
                                appType = (app.creation_type === 'coded') ? 'app' : 'link';
                            }

                            if (appType === 'link') {
                                linksArray.push(app);
                            } else {
                                appsArray.push(app);
                            }
                        });

                        // Display apps and links separately
                        displayApps(appsArray);
                        displayLinks(linksArray);
                    } else {
                        showError('Failed to load apps: ' + (data.message || 'Unknown error'));
                    }
                })
                .fail(function (e) {
                    showError('Error loading apps: ' + e.statusText);
                });
            }

            /**
             * Show login modal for unauthenticated users
             */
            function showLoginModal() {
                // Check if modal already exists
                if (document.getElementById('dt-home-login-modal')) {
                    return;
                }

                // Hide loading spinners and section titles
                const appsSection = document.querySelector('.apps-section');
                const linksSection = document.querySelector('.links-section');
                
                if (appsSection) {
                    appsSection.style.display = 'none';
                }
                if (linksSection) {
                    linksSection.style.display = 'none';
                }

                // Create modal overlay
                const modalOverlay = document.createElement('div');
                modalOverlay.id = 'dt-home-login-modal';
                modalOverlay.className = 'dt-home-login-modal-overlay';
                modalOverlay.setAttribute('role', 'dialog');
                modalOverlay.setAttribute('aria-labelledby', 'dt-home-login-modal-title');
                modalOverlay.setAttribute('aria-modal', 'true');

                // Detect dark mode
                const isDark = document.body.classList.contains('theme-dark') || 
                               document.documentElement.classList.contains('theme-dark');
                const themeClass = isDark ? ' theme-dark' : '';

                // Create modal HTML
                modalOverlay.innerHTML = `
                    <div class="dt-home-login-modal-content${themeClass}">
                        <div class="dt-home-login-modal-header">
                            <h2 id="dt-home-login-modal-title" class="dt-home-login-modal-title">
                                ${jsObject.translations.login_title || 'Login Required'}
                            </h2>
                            <div class="dt-home-login-modal-separator"></div>
                        </div>
                        <div class="dt-home-login-modal-body">
                            <p class="dt-home-login-explanation-text">
                                ${jsObject.translations.login_message || 'Please log in to access your Home Screen.'}
                            </p>
                            <form id="dt-home-login-form" class="dt-home-login-form">
                                <div class="dt-home-login-error" id="dt-home-login-error" style="display: none;"></div>
                                <div class="dt-home-login-field">
                                    <label for="dt-home-login-username">${jsObject.translations.username_label || 'Username or Email'}</label>
                                    <input 
                                        type="text" 
                                        id="dt-home-login-username" 
                                        name="username" 
                                        class="dt-home-login-input"
                                        required 
                                        autocomplete="username"
                                        aria-label="Username or Email Address"
                                    />
                                </div>
                                <div class="dt-home-login-field">
                                    <label for="dt-home-login-password">${jsObject.translations.password_label || 'Password'}</label>
                                    <input 
                                        type="password" 
                                        id="dt-home-login-password" 
                                        name="password" 
                                        class="dt-home-login-input"
                                        required 
                                        autocomplete="current-password"
                                        aria-label="Password"
                                    />
                                </div>
                                <div class="dt-home-login-actions">
                                    <button 
                                        type="submit" 
                                        class="dt-home-login-submit-button"
                                        id="dt-home-login-submit"
                                    >
                                        <span class="dt-home-login-submit-text">${jsObject.translations.login_button || 'Log In'}</span>
                                        <span class="dt-home-login-spinner" style="display: none;">
                                            <i class="mdi mdi-loading mdi-spin"></i>
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                `;

                // Append to body
                document.body.appendChild(modalOverlay);

                // Bind form submission
                const loginForm = document.getElementById('dt-home-login-form');
                if (loginForm) {
                    loginForm.addEventListener('submit', handleLoginSubmit);
                }

                // Focus username field for accessibility
                setTimeout(() => {
                    const usernameField = document.getElementById('dt-home-login-username');
                    if (usernameField) {
                        usernameField.focus();
                    }
                }, 100);
            }

            /**
             * Handle login form submission
             */
            function handleLoginSubmit(e) {
                e.preventDefault();

                const username = document.getElementById('dt-home-login-username').value;
                const password = document.getElementById('dt-home-login-password').value;
                const errorDiv = document.getElementById('dt-home-login-error');
                const submitButton = document.getElementById('dt-home-login-submit');
                const submitText = submitButton.querySelector('.dt-home-login-submit-text');
                const spinner = submitButton.querySelector('.dt-home-login-spinner');

                // Hide previous errors
                errorDiv.style.display = 'none';
                errorDiv.textContent = '';

                // Validate inputs
                if (!username || !password) {
                    showLoginError('Please enter both username and password.');
                    return;
                }

                // Show loading state
                submitButton.disabled = true;
                submitText.style.display = 'none';
                spinner.style.display = 'inline-block';

                // Submit login via fetch - WordPress will set cookies on success
                const loginUrl = '<?php echo esc_url( wp_login_url() ); ?>';
                const formData = new URLSearchParams();
                formData.append('log', username);
                formData.append('pwd', password);
                formData.append('rememberme', 'forever');
                formData.append('redirect_to', window.location.href);
                formData.append('wp-submit', 'Log In');

                fetch(loginUrl, {
                    method: 'POST',
                    credentials: 'include', // Important: include cookies
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData,
                    redirect: 'manual' // Don't follow redirects automatically
                })
                .then(response => {
                    // WordPress login redirects on success (3xx status)
                    // On failure, returns 200 with error page
                    if (response.status >= 300 && response.status < 400) {
                        // Redirect detected - likely success
                        // Wait a moment for cookies to be set, then reload
                        setTimeout(() => {
                            window.location.reload();
                        }, 300);
                        return;
                    }

                    // Check response for error indicators
                    return response.text().then(html => {
                        // Check for WordPress login error indicators
                        const hasError = html.includes('incorrect_password') || 
                                        html.includes('invalid_username') ||
                                        html.includes('ERROR') ||
                                        html.toLowerCase().includes('error') ||
                                        html.includes('Lost your password');

                        if (hasError) {
                            // Login failed
                            showLoginError('Invalid username or password. Please try again.');
                            submitButton.disabled = false;
                            submitText.style.display = 'inline';
                            spinner.style.display = 'none';
                        } else {
                            // No clear error - might be success, reload to check
                            setTimeout(() => {
                                window.location.reload();
                            }, 300);
                        }
                    });
                })
                .catch(error => {
                    console.error('Login error:', error);
                    // On error, try reloading anyway (cookie might be set)
                    setTimeout(() => {
                        window.location.reload();
                    }, 300);
                });
            }

            /**
             * Show login error message
             */
            function showLoginError(message) {
                const errorDiv = document.getElementById('dt-home-login-error');
                if (errorDiv) {
                    errorDiv.textContent = message;
                    errorDiv.style.display = 'block';
                }
            }

            /**
             * Display apps in the grid with new icon button layout
             */
            function displayApps(apps) {
                let html = '';

                // Safety check: ensure apps is an array
                if (!Array.isArray(apps)) {
                    console.error('Apps is not an array:', apps);
                    html = '<div class="app-card-wrapper"><div class="app-card"><div class="app-icon"><i class="mdi mdi-alert"></i></div></div><div class="app-title">Error loading apps.</div></div>';
                } else if (apps.length === 0) {
                    html = '<div class="app-card-wrapper"><div class="app-card"><div class="app-icon"><i class="mdi mdi-information"></i></div></div><div class="app-title">No apps available.</div></div>';
                } else {
                    apps.forEach(function(app) {
                        // Trim title to max 12 characters with ellipsis to fit under card
                        const trimmedTitle = app.title.length > 12 ? app.title.substring(0, 12) + '...' : app.title;

                        // Determine app type: if type exists use it, otherwise use fallback logic
                        let appType = app.type;
                        if (!appType || (appType !== 'app' && appType !== 'link')) {
                            // Fallback logic: if creation_type is 'coded', default to 'app', otherwise 'link'
                            appType = (app.creation_type === 'coded') ? 'app' : 'link';
                        }

                        // App-type apps navigate in same tab with launcher parameter, link-type apps open in new tab
                        let onClickHandler = '';
                        if (appType === 'app') {
                            // Check if app is cross-domain (different domain than current)
                            const currentHost = window.location.hostname;
                            const appUrlObj = new URL(app.url, window.location.origin);
                            const appHost = appUrlObj.hostname;
                            const isCrossDomain = appHost !== currentHost && appHost !== window.location.hostname;

                            if (isCrossDomain) {
                                // For cross-domain apps, use WordPress wrapper URL with app URL as parameter
                                const wrapperUrl = '<?php echo esc_js( dt_home_magic_url( '' ) ); ?>' + '?launcher=1&app_url=' + encodeURIComponent(app.url);
                                onClickHandler = `onclick="window.location.href = '${wrapperUrl}'; return false;"`;
                            } else {
                                // For same-domain apps, add launcher=1 parameter
                                const separator = app.url.includes('?') ? '&' : '?';
                                const appUrlWithLauncher = app.url + separator + 'launcher=1';
                                onClickHandler = `onclick="window.location.href = '${appUrlWithLauncher}'; return false;"`;
                            }
                        } else {
                            // Open in new tab (link-type)
                            onClickHandler = `onclick="window.open('${app.url}', '_blank'); return false;"`;
                        }

                        // Determine default icon color based on theme
                        // Custom colors override defaults
                        let iconColor = null;
                        const hasCustomColor = app.color && app.color.trim() !== '';

                        if (hasCustomColor) {
                            // Use custom color if specified
                            iconColor = app.color.trim();
                        } else {
                            // Use theme-aware default if no custom color
                            const isDarkMode = document.body.classList.contains('theme-dark') ||
                                             document.documentElement.classList.contains('theme-dark') ||
                                             document.body.classList.contains('dark') ||
                                             document.documentElement.classList.contains('dark');
                            iconColor = isDarkMode ? '#ffffff' : '#0a0a0a';
                        }

                        const appHtml = `
                            <div class="app-card-wrapper">
                                <div class="app-card" ${onClickHandler} title="${app.title}">
                                    <div class="app-icon" style="color: ${iconColor};" data-has-custom-color="${hasCustomColor}">
                                        <i class="${app.icon}"></i>
                                    </div>
                                </div>
                                <div class="app-title">${trimmedTitle}</div>
                            </div>
                        `;

                        //console.log('Generated HTML for app "' + app.title + '":', appHtml);
                        html += appHtml;
                    });
                }

                //console.log('Final HTML being inserted:', html);
                $('#apps-grid').html(html);

                // Ensure icon colors are correctly applied after HTML insertion
                // This handles theme-aware defaults and preserves custom colors
                setTimeout(function() {
                    const appIcons = document.querySelectorAll('#apps-grid .app-icon');
                    appIcons.forEach(function(icon) {
                        const hasCustomColor = icon.getAttribute('data-has-custom-color') === 'true';
                        const currentColor = icon.style.color;

                        if (!hasCustomColor) {
                            // Apply theme-aware default for icons without custom colors
                            const isDarkMode = document.body.classList.contains('theme-dark') ||
                                             document.documentElement.classList.contains('theme-dark');
                            const defaultColor = isDarkMode ? '#ffffff' : '#0a0a0a';
                            icon.style.setProperty('color', defaultColor, 'important');
                        } else if (currentColor) {
                            // Ensure custom color is preserved with important flag
                            icon.style.setProperty('color', currentColor, 'important');
                        }
                    });
                }, 100);

                // Reapply theme styles to newly loaded app cards
                const currentTheme = document.body.classList.contains('theme-dark') ? 'dark' : 'light';

                // Use theme toggle instance if available, otherwise fall back to local function
                if (window.themeToggleInstance) {
                    window.themeToggleInstance.reapplyStyles();
                } else {
                    applyDirectStyles(currentTheme);
                }

                // Re-apply icon colors after theme styles are reapplied
                setTimeout(function() {
                    const appIcons = document.querySelectorAll('#apps-grid .app-icon');
                    appIcons.forEach(function(icon) {
                        const hasCustomColor = icon.getAttribute('data-has-custom-color') === 'true';

                        if (!hasCustomColor) {
                            // Apply theme-aware default
                            const isDarkMode = document.body.classList.contains('theme-dark') ||
                                             document.documentElement.classList.contains('theme-dark');
                            const defaultColor = isDarkMode ? '#ffffff' : '#0a0a0a';
                            icon.style.setProperty('color', defaultColor, 'important');
                        }
                        // Custom colors are already set inline, so they should be preserved
                    });
                }, 200);
            }

            /**
             * Display links in the links list with link widget layout
             */
            function displayLinks(links) {
                let html = '';

                // Safety check: ensure links is an array
                if (!Array.isArray(links)) {
                    console.error('Links is not an array:', links);
                    html = '<div class="link-item"><div class="link-item__title">Error loading links.</div></div>';
                } else if (links.length === 0) {
                    html = '<div class="link-item"><div class="link-item__title">No links available.</div></div>';
                } else {
                    links.forEach(function(link) {
                        // Escape HTML to prevent XSS
                        const safeUrl = (link.url || '#').replace(/'/g, "\\'").replace(/"/g, '&quot;');
                        const safeUrlAttr = (link.url || '#').replace(/"/g, '&quot;');
                        const safeTitle = (link.title || 'Link').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                        const safeIcon = link.icon || 'mdi mdi-link';

                        // Determine default icon color based on theme (same logic as apps)
                        // Custom colors override defaults
                        let iconColor = null;
                        const hasCustomColor = link.color && typeof link.color === 'string' && link.color.trim() !== '';

                        if (hasCustomColor) {
                            // Validate hex color format (#rrggbb or #rgb)
                            const hexColorPattern = /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/;
                            if (hexColorPattern.test(link.color.trim())) {
                                iconColor = link.color.trim();
                            }
                        }

                        // If no valid custom color, use theme-aware default
                        if (!iconColor) {
                            const isDarkMode = document.body.classList.contains('theme-dark') ||
                                             document.documentElement.classList.contains('theme-dark') ||
                                             document.body.classList.contains('dark') ||
                                             document.documentElement.classList.contains('dark');
                            iconColor = isDarkMode ? '#ffffff' : '#0a0a0a';
                        }

                        // Determine icon display: image or icon class
                        let iconHtml = '';
                        if (link.icon && (link.icon.startsWith('http') || link.icon.startsWith('/'))) {
                            const safeIconUrl = link.icon.replace(/"/g, '&quot;');
                            iconHtml = `<img src="${safeIconUrl}" alt="${safeTitle}" />`;
                        } else {
                            // Apply color to icon using inline style with data attribute for theme updates
                            iconHtml = `<i class="${safeIcon}" aria-hidden="true" style="color: ${iconColor};" data-has-custom-color="${hasCustomColor}"></i>`;
                        }

                        const linkHtml = `
                            <div class="link-item" onclick="window.open('${safeUrl}', '_blank')" title="${safeTitle}">
                                <div class="link-item__icon">
                                    ${iconHtml}
                                </div>
                                <div class="link-item__content">
                                    <div class="link-item__title">${safeTitle}</div>
                                    <a href="${safeUrlAttr}" class="link-item__url" target="_blank" rel="noopener noreferrer" onclick="event.stopPropagation();">
                                        ${safeUrlAttr}
                                    </a>
                                </div>
                                <button class="link-item__copy" onclick="event.stopPropagation(); event.preventDefault(); copyLinkUrl('${safeUrl}', this, event);" title="Copy link">
                                    <i class="mdi mdi-content-copy"></i>
                                </button>
                            </div>
                        `;

                        html += linkHtml;
                    });
                }

                $('#links-list').html(html);

                // Ensure icon colors are correctly applied after HTML insertion (same as apps)
                // This handles theme-aware defaults and preserves custom colors
                setTimeout(function() {
                    const linkIcons = document.querySelectorAll('#links-list .link-item__icon i');
                    linkIcons.forEach(function(icon) {
                        const hasCustomColor = icon.getAttribute('data-has-custom-color') === 'true';
                        const currentColor = icon.style.color;

                        if (!hasCustomColor) {
                            // Apply theme-aware default for icons without custom colors
                            const isDarkMode = document.body.classList.contains('theme-dark') ||
                                             document.documentElement.classList.contains('theme-dark');
                            const defaultColor = isDarkMode ? '#ffffff' : '#0a0a0a';
                            icon.style.setProperty('color', defaultColor, 'important');
                        } else if (currentColor) {
                            // Ensure custom color is preserved with important flag
                            icon.style.setProperty('color', currentColor, 'important');
                        }
                    });
                }, 100);
            }

            /**
             * Copy link URL to clipboard
             */
            function copyLinkUrl(url, button, evt) {
                // Prevent event bubbling
                if (evt) {
                    evt.stopPropagation();
                    evt.preventDefault();
                }

                // Store original button state
                const originalIcon = button.innerHTML;
                const originalClass = button.className;

                // Try modern clipboard API first
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(url).then(function() {
                        showCopyFeedback(button, originalIcon, originalClass);
                    }).catch(function(err) {
                        console.error('Failed to copy:', err);
                        fallbackCopy(url, button, originalIcon, originalClass);
                    });
                } else {
                    // Fallback for older browsers
                    fallbackCopy(url, button, originalIcon, originalClass);
                }
            }

            /**
             * Fallback copy method for older browsers
             */
            function fallbackCopy(url, button, originalIcon, originalClass) {
                const textArea = document.createElement('textarea');
                textArea.value = url;
                textArea.style.position = 'fixed';
                textArea.style.opacity = '0';
                document.body.appendChild(textArea);
                textArea.select();

                try {
                    document.execCommand('copy');
                    showCopyFeedback(button, originalIcon, originalClass);
                } catch (err) {
                    console.error('Fallback copy failed:', err);
                }

                document.body.removeChild(textArea);
            }

            /**
             * Show visual feedback when URL is copied
             */
            function showCopyFeedback(button, originalIcon, originalClass) {
                // Change to checkmark icon and success color
                button.innerHTML = '<i class="mdi mdi-check"></i>';
                button.classList.add('copied');

                // Reset after animation
                setTimeout(function() {
                    button.innerHTML = originalIcon;
                    button.className = originalClass;
                }, 1500);
            }

            /**
             * Load training videos from the server
             */
            function loadTrainingVideos() {
                // Use REST API endpoint
                $.ajax({
                    type: "GET",
                    data: {
                        action: 'get_training',
                        parts: jsObject.parts
                    },
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    url: jsObject.root + jsObject.parts.root + '/v1/' + jsObject.parts.type,
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', jsObject.nonce);
                    }
                })
                .done(function (data) {
                    if (data.success && data.training_videos) {
                        displayTrainingVideos(data.training_videos);
                    } else {
                        showError('Failed to load training videos: ' + (data.message || 'Unknown error'));
                    }
                })
                .fail(function (e) {
                    showError('Error loading training videos: ' + e.statusText);
                });
            }

            /**
             * Display training videos in the grid with video previews
             */
            function displayTrainingVideos(videos) {
                let html = '';

                if (videos.length === 0) {
                    html = '<div class="training-card"><div class="training-video-title">No training videos available.</div></div>';
                } else {
                    videos.forEach(function(video) {
                        // Extract YouTube video ID for embedded preview
                        const videoId = extractYouTubeVideoId(video.video_url);
                        const thumbnailUrl = video.thumbnail_url || (videoId ? `https://img.youtube.com/vi/${videoId}/maxresdefault.jpg` : '');

                        // Get video duration if available
                        const duration = video.duration || '0:00';

                        const videoHtml = `
                            <div class="training-card" data-video-id="${videoId || ''}" data-video-url="${video.video_url}" data-video-title="${video.title}" onclick="toggleVideoPlayback(this)" title="${video.title}">
                                <!-- Video Info Header: Title and Duration on same line -->
                                <div class="training-video-info">
                                    <div class="training-video-title-text">${video.title}</div>
                                    <div class="training-video-duration-badge">${duration}</div>
                                </div>
                                <!-- Thumbnail Container with Splash Screen -->
                                <div class="training-video-thumbnail-container">
                                    <img src="${thumbnailUrl}" alt="${video.title}" class="training-video-thumbnail" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjE1MCIgdmlld0JveD0iMCAwIDIwMCAxNTAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMTUwIiBmaWxsPSIjRjVGNUY1Ii8+CjxwYXRoIGQ9Ik04MCA2MEwxMjAgOTBMMTgwIDYwVjkwSDEyMFY2MEg4MFoiIGZpbGw9IiM2Nzc5RUEiLz4KPC9zdmc+'" />
                                    <div class="training-video-overlay">
                                        <div class="training-play-button">
                                            <i class="mdi mdi-play"></i>
                                        </div>
                                    </div>
                                </div>
                                <!-- Embedded Video Container (hidden initially) -->
                                <div class="training-video-embed" style="display: none;">
                                    <iframe width="100%" height="100%" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                    <div class="training-video-external-link">
                                        <a href="${video.video_url}" target="_blank" rel="noopener noreferrer" class="external-link-button">
                                            <i class="mdi mdi-open-in-new"></i>
                                            <span>Watch on ${video.video_url.includes('youtube.com') || video.video_url.includes('youtu.be') ? 'YouTube' : 'Vimeo'}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        `;

                        html += videoHtml;
                    });
                }

                $('#training-grid').html(html);
            }

            /**
             * Extract YouTube video ID from URL
             */
            function extractYouTubeVideoId(url) {
                const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
                const match = url.match(regExp);
                return (match && match[2].length === 11) ? match[2] : null;
            }

            /**
             * Toggle video playback between thumbnail and embedded player
             */
            function toggleVideoPlayback(cardElement) {
                const thumbnailContainer = cardElement.querySelector('.training-video-thumbnail-container');
                const embedContainer = cardElement.querySelector('.training-video-embed');
                const iframe = embedContainer.querySelector('iframe');
                const videoId = cardElement.getAttribute('data-video-id');
                const videoUrl = cardElement.getAttribute('data-video-url');

                if (thumbnailContainer.style.display !== 'none') {
                    // Switch to embedded player
                    thumbnailContainer.style.display = 'none';
                    embedContainer.style.display = 'block';
                    cardElement.classList.add('playing');

                    // Set up the iframe source
                    if (videoId) {
                        // YouTube video
                        iframe.src = `https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0&modestbranding=1`;
                    } else if (videoUrl.includes('vimeo.com')) {
                        // Vimeo video
                        const vimeoId = extractVimeoVideoId(videoUrl);
                        if (vimeoId) {
                            iframe.src = `https://player.vimeo.com/video/${vimeoId}?autoplay=1&title=0&byline=0&portrait=0`;
                        }
                    } else {
                        // Fallback to external link
                        window.open(videoUrl, '_blank');
                        return;
                    }
                } else {
                    // Switch back to thumbnail
                    embedContainer.style.display = 'none';
                    thumbnailContainer.style.display = 'block';
                    cardElement.classList.remove('playing');
                    iframe.src = ''; // Stop the video
                }
            }

            /**
             * Extract Vimeo video ID from URL
             */
            function extractVimeoVideoId(url) {
                const regExp = /vimeo\.com\/(\d+)/;
                const match = url.match(regExp);
                return match ? match[1] : null;
            }

            /**
             * Open video in new tab or modal (legacy function)
             */
            function openVideo(videoUrl, title) {
                // Check if it's a YouTube video and open in embedded modal
                const videoId = extractYouTubeVideoId(videoUrl);
                if (videoId) {
                    openVideoModal(videoId, title);
                } else {
                    // Open external video in new tab
                    window.open(videoUrl, '_blank');
                }
            }

            /**
             * Open video in modal overlay
             */
            function openVideoModal(videoId, title) {
                // Create modal overlay
                const modal = document.createElement('div');
                modal.className = 'video-modal-overlay';
                modal.innerHTML = `
                    <div class="video-modal">
                        <div class="video-modal-header">
                            <h3>${title}</h3>
                            <button class="video-modal-close" onclick="closeVideoModal()">
                                <i class="mdi mdi-close"></i>
                            </button>
                        </div>
                        <div class="video-modal-content">
                            <iframe
                                src="https://www.youtube.com/embed/${videoId}?autoplay=1"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen>
                            </iframe>
                        </div>
                    </div>
                `;

                document.body.appendChild(modal);
                document.body.style.overflow = 'hidden';
            }

            /**
             * Close video modal
             */
            function closeVideoModal() {
                const modal = document.querySelector('.video-modal-overlay');
                if (modal) {
                    modal.remove();
                    document.body.style.overflow = 'auto';
                }
            }

            /**
             * Show error message
             */
            function showError(message) {
                console.error('Home Screen Error:', message);
                // You could also display this in the UI if needed
            }
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
     * Determine if the current request is being loaded inside the launcher iframe.
     *
     * @return bool
     */
    private function is_launcher_iframe_request() {
        if ( isset( $_GET['launcher_iframe'] ) && $_GET['launcher_iframe'] === '1' ) {
            error_log( 'DT Home: Detected launcher_iframe query parameter.' );
            return true;
        }

        $sec_fetch_dest = isset( $_SERVER['HTTP_SEC_FETCH_DEST'] )
            ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_SEC_FETCH_DEST'] ) ) )
            : '';

        if ( $sec_fetch_dest === 'iframe' ) {
            error_log( 'DT Home: Detected Sec-Fetch-Dest: iframe header.' );
            return true;
        }

        return false;
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
     * Check if current page is an app-type app (not home screen)
     *
     * @return bool True if on an app-type app page, false otherwise
     */
    private function is_app_type_page() {
        // Debug logging
        error_log( 'DT Home: is_app_type_page() called' );

        if ( $this->is_launcher_iframe_request() ) {
            error_log( 'DT Home: Request identified as launcher iframe. Treating as non app-type page.' );
            return false;
        }

        // Check for launcher parameter first - if present, always show launcher
        // This handles both coded apps and custom apps (including cross-domain)
        if ( $this->has_launcher_parameter() ) {
            error_log( 'DT Home: launcher=1 parameter detected, returning true' );
            return true;
        }

        // Get current URL path
        $url_path = dt_get_url_path();
        error_log( 'DT Home: Current URL path: ' . $url_path );

        // Parse URL path manually to extract root/type
        // Magic link URL format: /{root}/{type}/{key}/{action}
        $url_parts = explode( '/', trim( $url_path, '/' ) );
        error_log( 'DT Home: URL parts array: ' . print_r( $url_parts, true ) );

        if ( count( $url_parts ) < 3 ) {
            error_log( 'DT Home: URL path too short, returning false' );
            return false;
        }

        $current_root = $url_parts[0] ?? '';
        $current_type = $url_parts[1] ?? '';

        error_log( 'DT Home: Parsed root: ' . $current_root . ', type: ' . $current_type );

        // If we're on the home screen itself, return false
        if ( $current_root === 'apps' && $current_type === 'launcher' ) {
            // Check if there's an action in the URL (e.g., /app/{slug})
            $action = $url_parts[3] ?? '';
            if ( empty( $action ) || ! str_starts_with( $action, 'app/' ) ) {
                error_log( 'DT Home: On home screen (no action or action not app/*), returning false' );
                return false; // We're on the home screen
            }
        }

        // Get all registered magic link apps
        $apps_manager = DT_Home_Apps::instance();
        $apps = $apps_manager->get_apps_for_user( get_current_user_id() );

        error_log( 'DT Home: Checking ' . count( $apps ) . ' apps' );
        error_log( 'DT Home: Current URL root: ' . $current_root . ', type: ' . $current_type );

        // Get current full URL for comparison
        $http_host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
        if ( empty( $http_host ) || empty( $request_uri ) ) {
            return false;
        }
        $current_full_url = ( is_ssl() ? 'https://' : 'http://' ) . $http_host . $request_uri;
        // Remove launcher parameter for comparison
        $current_full_url_no_launcher = remove_query_arg( 'launcher', $current_full_url );

        // Check if current URL matches a registered app-type app
        foreach ( $apps as $app ) {
            // Only check app-type apps
            if ( ! isset( $app['type'] ) || $app['type'] !== 'app' ) {
                continue;
            }

            // For coded apps, check if URL matches magic_link_meta
            if ( isset( $app['magic_link_meta'] ) && ! empty( $app['magic_link_meta'] ) ) {
                // Check if current URL matches this app's root/type
                $app_meta = $app['magic_link_meta'];
                $app_root = $app_meta['root'] ?? '';
                $app_type = $app_meta['type'] ?? '';

                error_log( 'DT Home: Checking coded app - root: ' . $app_root . ', type: ' . $app_type . ', title: ' . ( $app['title'] ?? 'not set' ) );

                if ( $current_root === $app_root && $current_type === $app_type ) {
                    // Make sure it's not the home screen app
                    if ( $current_root === 'apps' && $current_type === 'launcher' ) {
                        error_log( 'DT Home: Matched but it\'s home screen app, skipping' );
                        continue;
                    }
                    error_log( 'DT Home: MATCH FOUND! This is a coded app-type page: ' . ( $app['title'] ?? 'unknown' ) );
                    return true;
                }
            } else {
                // For custom apps (no magic_link_meta), check if URL matches the app's stored URL
                $app_url = $app['url'] ?? '';
                if ( ! empty( $app_url ) ) {
                    // Remove launcher parameter from app URL for comparison
                    $app_url_no_launcher = remove_query_arg( 'launcher', $app_url );

                    error_log( 'DT Home: Checking custom app - app URL: ' . $app_url_no_launcher . ', current URL: ' . $current_full_url_no_launcher . ', title: ' . ( $app['title'] ?? 'not set' ) );

                    // Check if current URL matches the app's URL (compare paths and domains)
                    $app_url_parts = parse_url( $app_url_no_launcher );
                    $current_url_parts = parse_url( $current_full_url_no_launcher );

                    // Compare paths (normalize by removing leading/trailing slashes)
                    $app_path = trim( $app_url_parts['path'] ?? '', '/' );
                    $current_path = trim( $current_url_parts['path'] ?? '', '/' );

                    // Compare domains
                    $app_host = $app_url_parts['host'] ?? '';
                    $current_host = $current_url_parts['host'] ?? '';

                    if ( ! empty( $app_path ) && ! empty( $current_path ) ) {
                        // Check if paths match (exact match or current path starts with app path)
                        if ( $app_path === $current_path || strpos( $current_path, $app_path ) === 0 ) {
                            // Also check if hosts match (or app URL is relative)
                            if ( empty( $app_host ) || $app_host === $current_host ) {
                                error_log( 'DT Home: MATCH FOUND! This is a custom app-type page: ' . ( $app['title'] ?? 'unknown' ) );
                                return true;
                            }
                        }
                    }
                }
            }
        }

        error_log( 'DT Home: No match found, returning false' );
        return false;
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
     * Maybe enqueue launcher nav CSS for other magic link apps
     * This is called via dt_blank_head hook
     */
    public function maybe_enqueue_launcher_nav_css() {
        error_log( 'DT Home: maybe_enqueue_launcher_nav_css() called' );
        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : 'not set';
        $http_host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : 'not set';
        error_log( 'DT Home: Current REQUEST_URI: ' . $request_uri );
        error_log( 'DT Home: Current HTTP_HOST: ' . $http_host );

        if ( $this->is_launcher_iframe_request() ) {
            error_log( 'DT Home: Detected launcher iframe request, skipping launcher nav CSS enqueue.' );
            return;
        }

        // Only enqueue if we're on an app-type app page (not home screen)
        if ( $this->is_app_type_page() ) {
            error_log( 'DT Home: Enqueuing launcher nav CSS' );
            // Ensure CSS is enqueued for other magic link apps
            if ( ! wp_style_is( 'dt-home-style', 'enqueued' ) ) {
                wp_enqueue_style( 'dt-home-style', get_template_directory_uri() . '/dt-apps/dt-home/assets/css/home-screen.css', [], '1.0.19' );
            }
            if ( ! wp_style_is( 'material-font-icons-css', 'enqueued' ) ) {
                wp_enqueue_style( 'material-font-icons-css', 'https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css', [], '7.4.47' );
            }
        } else {
            error_log( 'DT Home: Not an app-type page, skipping CSS enqueue' );
        }
    }

    /**
     * Maybe include launcher nav in footer (for other app-type apps)
     * This is called via dt_blank_footer hook
     */
    public function maybe_include_launcher_nav() {
        error_log( 'DT Home: maybe_include_launcher_nav() called' );

        if ( $this->is_launcher_iframe_request() ) {
            error_log( 'DT Home: Detected launcher iframe request, skipping launcher nav inclusion.' );
            return;
        }

        // Don't include launcher nav if we're showing the wrapper (it's already included there)
        if ( $this->has_launcher_parameter() ) {
            error_log( 'DT Home: launcher=1 detected, skipping footer nav (wrapper already shown)' );
            return;
        }

        // Only include if we're on an app-type app page (not home screen)
        if ( $this->is_app_type_page() ) {
            error_log( 'DT Home: Including launcher nav' );
            $this->include_launcher_nav();
        } else {
            error_log( 'DT Home: Not an app-type page, skipping launcher nav inclusion' );
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
     * Include launcher bottom nav partial
     */
    private function include_launcher_nav() {
        error_log( 'DT Home: include_launcher_nav() called' );
        // Get all apps for the apps selector
        $apps_manager = DT_Home_Apps::instance();
        $apps = $apps_manager->get_apps_for_user( get_current_user_id() );

        error_log( 'DT Home: Found ' . count( $apps ) . ' apps for launcher nav' );

        // Include the launcher nav partial
        $partial_path = get_template_directory() . '/dt-apps/dt-home/frontend/partials/launcher-bottom-nav.php';
        if ( file_exists( $partial_path ) ) {
            error_log( 'DT Home: Including launcher nav partial from: ' . $partial_path );
            include $partial_path;
        } else {
            error_log( 'DT Home: ERROR - Launcher nav partial not found at: ' . $partial_path );
        }
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
