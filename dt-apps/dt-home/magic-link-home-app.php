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
                ]
            ] ) ?>][0];

            /**
             * Initialize home screen
             */
            jQuery(document).ready(function($) {

                // Check authentication before loading content
                // If user is not authenticated, they will be redirected by PHP before this JavaScript runs
                if (jsObject.user_id === 0) {
                    // This should not happen as PHP redirects unauthenticated users
                    // But as a fallback, redirect to login
                    const currentUrl = window.location.href;
                    const loginUrl = '<?php echo esc_url( wp_login_url() ); ?>';
                    window.location.href = loginUrl + '?redirect_to=' + encodeURIComponent(currentUrl);
                    return;
                }

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
            });

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

                        // Determine icon display: image or icon class
                        let iconHtml = '';
                        const isImageIcon = app.icon && (app.icon.startsWith('http') || app.icon.startsWith('/'));

                        if (isImageIcon) {
                            // Render image icon
                            const safeIconUrl = app.icon.replace(/"/g, '&quot;');
                            const safeTitle = trimmedTitle.replace(/"/g, '&quot;');
                            iconHtml = `<img src="${safeIconUrl}" alt="${safeTitle}" />`;
                        } else {
                            // Render icon class with color support
                            // Determine default icon color based on theme
                            // Custom colors override defaults
                            let iconColor = 'inherit';
                            const hasCustomColor = app.color && app.color.trim() !== '';

                            if (hasCustomColor) {
                                // Use custom color if specified
                                iconColor = app.color.trim();
                            }
                            iconHtml = `<i class="${app.icon}" style="color: ${iconColor};" data-has-custom-color="${hasCustomColor}"></i>`;
                        }

                        const appHtml = `
                            <div class="app-card-wrapper">
                                <div class="app-card" ${onClickHandler} title="${app.title}">
                                    <div class="app-icon">
                                        ${iconHtml}
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
                        let iconColor = 'inherit';
                        const hasCustomColor = link.color && typeof link.color === 'string' && link.color.trim() !== '';

                        if (hasCustomColor) {
                            // Validate hex color format (#rrggbb or #rgb)
                            const hexColorPattern = /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/;
                            if (hexColorPattern.test(link.color.trim())) {
                                iconColor = link.color.trim();
                            }
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
                    html = '<div class="training-card"><div class="training-video-title-text">No training videos available.</div></div>';
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
