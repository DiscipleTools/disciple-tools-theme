<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

// Include required classes
require_once get_template_directory() . '/dt-apps/dt-home/includes/class-home-apps.php';
require_once get_template_directory() . '/dt-apps/dt-home/includes/class-home-training.php';
require_once get_template_directory() . '/dt-apps/dt-home/includes/class-home-roles-permissions.php';
require_once get_template_directory() . '/dt-apps/dt-home/includes/class-home-migration.php';

/**
 * Class DT_Home_Magic_Link_App
 *
 * Main magic link app for the Home Screen functionality.
 * Provides a centralized dashboard for users to access apps and training videos.
 */
class DT_Home_Magic_Link_App extends DT_Magic_Url_Base {

    public $page_title = 'Home Screen';
    public $page_description = 'Your personalized dashboard for apps and training.';
    public $root = 'smart_links';
    public $type = 'home_screen';
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

        // Enqueue home screen specific styles
        wp_enqueue_style( 'dt-home-style', get_template_directory_uri() . '/dt-apps/dt-home/assets/css/home-screen.css', [], '1.0.1' );
        // JavaScript is handled in footer_javascript() method
    }

    public function dt_magic_url_base_allowed_js( $allowed_js ) {
        // @todo add or remove js files with this filter
        return $allowed_js;
    }

    public function dt_magic_url_base_allowed_css( $allowed_css ) {
        // @todo add or remove css files with this filter
        $allowed_css[] = 'material-font-icons-css';

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
                max-width: 1200px;
                margin: 0 auto;
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                overflow: hidden;
            }

            .home-screen-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 2rem;
                text-align: center;
            }

            .home-screen-header h1 {
                margin: 0;
                font-size: 2.5rem;
                font-weight: 300;
            }

            .home-screen-header p {
                margin: 0.5rem 0 0 0;
                opacity: 0.9;
                font-size: 1.1rem;
            }

            .home-screen-content {
                padding: 2rem;
            }

            .apps-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 1.5rem;
                margin-bottom: 2rem;
            }

            .app-card {
                background: white;
                border: 1px solid #e1e5e9;
                border-radius: 8px;
                padding: 1.5rem;
                text-align: center;
                transition: all 0.3s ease;
                cursor: pointer;
            }

            .app-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 20px rgba(0,0,0,0.1);
                border-color: #667eea;
            }

            .app-icon {
                font-size: 3rem;
                margin-bottom: 1rem;
                color: #667eea;
            }

            .app-title {
                font-size: 1.2rem;
                font-weight: 600;
                margin-bottom: 0.5rem;
                color: #2c3e50;
            }

            .app-description {
                color: #6c757d;
                font-size: 0.9rem;
                line-height: 1.4;
            }

            .training-section {
                margin-top: 2rem;
                padding-top: 2rem;
                border-top: 1px solid #e1e5e9;
            }

            .section-title {
                font-size: 1.5rem;
                font-weight: 600;
                margin-bottom: 1rem;
                color: #2c3e50;
            }

            .loading-spinner {
                display: inline-block;
                width: 20px;
                height: 20px;
                border: 3px solid #f3f3f3;
                border-top: 3px solid #667eea;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
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
            // Home screen JavaScript will be loaded via wp_enqueue_scripts
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
                    'loading' => __( 'Loading...', 'disciple_tools' )
                ]
            ] ) ?>][0];

            /**
             * Initialize home screen
             */
            jQuery(document).ready(function($) {
                console.log('Home Screen initialized');
                console.log('jsObject:', jsObject);

                // Initialize collapsible sections
                initializeCollapsibleSections();

                // Load apps and training content
                loadApps();
                loadTrainingVideos();
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

                // Quick Actions section - Collapsed (default)
                const quickActionsContent = document.getElementById('quick-actions-content');
                const quickActionsToggle = document.getElementById('quick-actions-toggle');
                if (quickActionsContent && quickActionsToggle) {
                    quickActionsContent.classList.remove('expanded');
                    quickActionsContent.classList.add('collapsed');
                    quickActionsContent.style.display = 'none';
                    quickActionsToggle.innerHTML = '<i class="mdi mdi-chevron-right"></i>';
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
                console.log('Loading apps...');

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
                    console.log('Apps response:', data);
                    if (data.success && data.apps) {
                        console.log('Displaying apps:', data.apps);
                        // Debug: Log each app's icon value
                        /*data.apps.forEach(function(app, index) {
                            console.log('App ' + index + ' icon:', app.icon, 'color:', app.color);
                        });*/
                        displayApps(data.apps);
                    } else {
                        console.log('Failed to load apps - response:', data);
                        showError('Failed to load apps: ' + (data.message || 'Unknown error'));
                    }
                })
                .fail(function (e) {
                    console.log('Error loading apps:', e);
                    showError('Error loading apps: ' + e.statusText);
                });
            }

            /**
             * Display apps in the grid with new icon button layout
             */
            function displayApps(apps) {
                let html = '';

                // Safety check: ensure apps is an array
                if (!Array.isArray(apps)) {
                    console.error('Apps is not an array:', apps);
                    html = '<div class="app-card"><div class="app-title">Error loading apps.</div></div>';
                } else if (apps.length === 0) {
                    html = '<div class="app-card"><div class="app-title">No apps available.</div></div>';
                } else {
                    apps.forEach(function(app) {
                        // Trim title to max 15 characters with ellipsis
                        const trimmedTitle = app.title.length > 15 ? app.title.substring(0, 15) + '...' : app.title;

                        const appHtml = `
                            <div class="app-card" onclick="window.open('${app.url}', '_blank')" title="${app.title}">
                                <div class="app-icon" style="color: ${app.color || '#667eea'}">
                                    <i class="${app.icon}"></i>
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
            }

            /**
             * Load training videos from the server
             */
            function loadTrainingVideos() {
                console.log('Loading training videos...');

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
                    console.log('Training videos response:', data);
                    if (data.success && data.training_videos) {
                        console.log('Displaying training videos:', data.training_videos);
                        displayTrainingVideos(data.training_videos);
                    } else {
                        console.log('Failed to load training videos - response:', data);
                        showError('Failed to load training videos: ' + (data.message || 'Unknown error'));
                    }
                })
                .fail(function (e) {
                    console.log('Error loading training videos:', e);
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
                                <div class="training-video-thumbnail-container">
                                    <img src="${thumbnailUrl}" alt="${video.title}" class="training-video-thumbnail" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjE1MCIgdmlld0JveD0iMCAwIDIwMCAxNTAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMTUwIiBmaWxsPSIjRjVGNUY1Ii8+CjxwYXRoIGQ9Ik04MCA2MEwxMjAgOTBMMTgwIDYwVjkwSDEyMFY2MEg4MFoiIGZpbGw9IiM2Njc5RUEiLz4KPC9zdmc+'" />
                                    <div class="training-video-overlay">
                                        <div class="training-play-button">
                                            <i class="mdi mdi-play"></i>
                                        </div>
                                    </div>
                                    <div class="training-video-duration">${duration}</div>
                                </div>
                                <div class="training-video-embed" style="display: none;">
                                    <iframe width="100%" height="100%" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                    <div class="training-video-external-link">
                                        <a href="${video.video_url}" target="_blank" rel="noopener noreferrer" class="external-link-button">
                                            <i class="mdi mdi-open-in-new"></i>
                                            <span>Watch on ${video.video_url.includes('youtube.com') || video.video_url.includes('youtu.be') ? 'YouTube' : 'Vimeo'}</span>
                                        </a>
                                    </div>
                                </div>
                                <div class="training-video-title">${video.title}</div>
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

    public function body() {
        // Revert back to dt translations
        $this->hard_switch_to_default_dt_text_domain();

        // Include the home screen template
        include get_template_directory() . '/dt-apps/dt-home/frontend/home-screen.php';
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
