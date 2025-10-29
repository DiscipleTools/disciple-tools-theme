<?php
/**
 * Home Screen Admin Integration
 *
 * Handles the admin interface for the Home Screen magic link app.
 * Integrates with the theme settings system.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class DT_Home_Admin
 *
 * Handles admin functionality for the Home Screen app.
 */
class DT_Home_Admin {

    private static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        // Hook into theme settings system
        add_action( 'admin_menu', [ $this, 'add_submenu' ], 99 );
        add_action( 'dt_settings_tab_menu', [ $this, 'add_settings_tab' ], 10, 1 );
        add_action( 'dt_settings_tab_content', [ $this, 'render_settings_content' ], 10, 1 );

        // Admin scripts and styles
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );

        // AJAX handlers for drag and drop reordering
        add_action( 'wp_ajax_dt_home_reorder_apps', [ $this, 'handle_app_reorder' ] );
        add_action( 'wp_ajax_dt_home_reorder_videos', [ $this, 'handle_video_reorder' ] );
    }

    /**
     * Add submenu item to admin menu
     */
    public function add_submenu() {
        add_submenu_page( 'dt_options', __( 'Home Screen', 'disciple_tools' ), __( 'Home Screen', 'disciple_tools' ), 'manage_dt', 'dt_options&tab=home_screen', [ 'Disciple_Tools_Settings_Menu', 'content' ] );
    }

    /**
     * Add Home Screen tab to settings menu
     */
    public function add_settings_tab( $tab ) {
        $active = ( $tab === 'home_screen' ) ? 'nav-tab-active' : '';
        ?>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=dt_options&tab=home_screen' ) ); ?>"
           class="nav-tab <?php echo esc_attr( $active ); ?>">
            <i class="mdi mdi-home" style="margin-right: 0.5rem;"></i>
            <?php esc_html_e( 'Home Screen', 'disciple_tools' ); ?>
        </a>
        <?php
    }

    /**
     * Render settings content for Home Screen tab
     */
    public function render_settings_content( $tab ) {
        if ( $tab !== 'home_screen' ) {
            return;
        }

        // Handle form submissions
        if ( isset( $_POST['dt_home_screen_settings'] ) && isset( $_POST['dt_home_screen_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dt_home_screen_nonce'] ) ), 'dt_home_screen_settings' ) ) {
            $this->handle_settings_save();
        }

        // Handle apps management
        if ( isset( $_POST['dt_home_app_action'] ) && isset( $_POST['dt_home_app_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dt_home_app_nonce'] ) ), 'dt_home_app_action' ) ) {
            $this->handle_app_action();
        }

        // Handle training videos management
        if ( isset( $_POST['dt_home_video_action'] ) && isset( $_POST['dt_home_video_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dt_home_video_nonce'] ) ), 'dt_home_video_action' ) ) {
            $this->handle_video_action();
        }

        // Get current settings
        $settings = $this->get_settings();
        ?>
        <div class="wrap">
            <h2><?php esc_html_e( 'Home Screen Settings', 'disciple_tools' ); ?></h2>

            <form method="post" action="">
                <?php wp_nonce_field( 'dt_home_screen_settings', 'dt_home_screen_nonce' ); ?>
                <input type="hidden" name="dt_home_screen_settings" value="1">
                <?php wp_nonce_field( 'dt_home_admin_nonce', 'dt_home_admin_nonce' ); ?>

                <!-- Icon Selector Dialog -->
                <?php include get_template_directory() . '/dt-core/admin/menu/tabs/dialog-icon-selector.php'; ?>

                <div class="dt-home-admin-container">
                    <!-- General Settings -->
                    <div class="dt-home-section">
                        <h3><?php esc_html_e( 'General Settings', 'disciple_tools' ); ?></h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="home_screen_title"><?php esc_html_e( 'Home Screen Title', 'disciple_tools' ); ?></label>
                                </th>
                                <td>
                                    <input type="text"
                                           id="home_screen_title"
                                           name="home_screen_title"
                                           value="<?php echo esc_attr( $settings['title'] ); ?>"
                                           class="regular-text" />
                                    <p class="description"><?php esc_html_e( 'The main title displayed on the home screen.', 'disciple_tools' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="home_screen_description"><?php esc_html_e( 'Home Screen Description', 'disciple_tools' ); ?></label>
                                </th>
                                <td>
                                    <textarea id="home_screen_description"
                                              name="home_screen_description"
                                              rows="3"
                                              class="large-text"><?php echo esc_textarea( $settings['description'] ); ?></textarea>
                                    <p class="description"><?php esc_html_e( 'A brief description shown below the title.', 'disciple_tools' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="enable_training_videos"><?php esc_html_e( 'Enable Training Videos', 'disciple_tools' ); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox"
                                           id="enable_training_videos"
                                           name="enable_training_videos"
                                           value="1"
                                           <?php checked( $settings['enable_training_videos'] ); ?> />
                                    <label for="enable_training_videos"><?php esc_html_e( 'Show training videos section on home screen', 'disciple_tools' ); ?></label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="enable_quick_actions"><?php esc_html_e( 'Enable Quick Actions', 'disciple_tools' ); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox"
                                           id="enable_quick_actions"
                                           name="enable_quick_actions"
                                           value="1"
                                           <?php checked( $settings['enable_quick_actions'] ); ?> />
                                    <label for="enable_quick_actions"><?php esc_html_e( 'Show quick actions section on home screen', 'disciple_tools' ); ?></label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="enable_roles_permissions"><?php esc_html_e( 'Enable Role-Based Access', 'disciple_tools' ); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox"
                                           id="enable_roles_permissions"
                                           name="enable_roles_permissions"
                                           value="1"
                                           <?php checked( $settings['enable_roles_permissions'] ?? true ); ?> />
                                    <label for="enable_roles_permissions"><?php esc_html_e( 'Enable role-based access control for apps', 'disciple_tools' ); ?></label>
                                    <p class="description"><?php esc_html_e( 'When enabled, you can restrict app access to specific user roles.', 'disciple_tools' ); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Debug Info --
                    <div class="dt-home-section">
                        <h3><?php esc_html_e( 'Debug Information', 'disciple_tools' ); ?></h3>
                        <p><?php esc_html_e( 'Current apps and videos data:', 'disciple_tools' ); ?></p>
                        <?php
                        $apps_manager = DT_Home_Apps::instance();
                        $training_manager = DT_Home_Training::instance();
                        $apps = $apps_manager->get_all_apps();
                        $videos = $training_manager->get_all_videos();
                        ?>
                        <p><strong>Apps (<?php echo count( $apps ); ?>):</strong></p>
                        <pre><?php print_r( $apps ); ?></pre>
                        <p><strong>Videos (<?php echo count( $videos ); ?>):</strong></p>
                        <pre><?php print_r( $videos ); ?></pre>
                    </div>
                    -- Debug Info -->

                    <!-- Apps Management -->
                    <div class="dt-home-section">
                        <h3><?php esc_html_e( 'Apps Management', 'disciple_tools' ); ?></h3>
                        <p><?php esc_html_e( 'Manage the apps that appear on the home screen.', 'disciple_tools' ); ?></p>

                        <div class="apps-management">
                            <button type="button" class="button button-secondary add-new-app-btn">
                                <i class="mdi mdi-plus" style="margin-right: 5px;"></i>
                                <?php esc_html_e( 'Add New App', 'disciple_tools' ); ?>
                            </button>

                            <div class="add-app-form" style="display: none;">
                                <?php $this->render_add_app_form(); ?>
                            </div>

                            <div class="">
                                <?php $this->render_apps_list(); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Training Videos Management -->
                    <div class="dt-home-section">
                        <h3><?php esc_html_e( 'Training Videos Management', 'disciple_tools' ); ?></h3>
                        <p><?php esc_html_e( 'Manage the training videos that appear on the home screen.', 'disciple_tools' ); ?></p>

                        <div class="training-management">
                            <button type="button" class="button button-secondary add-new-video-btn">
                                <i class="mdi mdi-plus" style="margin-right: 5px;"></i>
                                <?php esc_html_e( 'Add New Training Video', 'disciple_tools' ); ?>
                            </button>

                            <div class="add-video-form" style="display: none;">
                                <?php $this->render_add_video_form(); ?>
                            </div>

                            <div class="videos-list">
                                <?php $this->render_videos_list(); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="dt-home-section">
                        <?php submit_button( __( 'Save Settings', 'disciple_tools' ), 'primary', 'submit', false ); ?>
                    </div>
                </div>
            </form>
        </div>

        <style>
            .dt-home-admin-container {
                max-width: 1000px;
            }

            .dt-home-section {
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
                margin-bottom: 20px;
                padding: 20px;
            }

            .dt-home-section h3 {
                margin-top: 0;
                color: #1d2327;
                border-bottom: 1px solid #dcdcde;
                padding-bottom: 10px;
            }

            .apps-management,
            .training-management {
                margin-top: 15px;
            }

            .apps-list,
            .training-list {
                background: #f9f9f9;
                border: 1px solid #dcdcde;
                border-radius: 4px;
                padding: 15px;
                margin-top: 10px;
            }
        </style>
        <?php
    }

    /**
     * Handle settings form submission
     */
    private function handle_settings_save() {
        // Verify nonce first
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dt_home_screen_nonce'] ?? '' ) ), 'dt_home_screen_settings' ) ) {
            wp_die( esc_html__( 'Security check failed. Please try again.', 'disciple_tools' ) );
        }

        $settings = [
            'title' => sanitize_text_field( wp_unslash( $_POST['home_screen_title'] ?? '' ) ),
            'description' => sanitize_textarea_field( wp_unslash( $_POST['home_screen_description'] ?? '' ) ),
            'enable_training_videos' => isset( $_POST['enable_training_videos'] ) ? 1 : 0,
            'enable_quick_actions' => isset( $_POST['enable_quick_actions'] ) ? 1 : 0,
            'enable_roles_permissions' => isset( $_POST['enable_roles_permissions'] ) ? 1 : 0,
        ];

        update_option( 'dt_home_screen_settings', $settings );

        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Home Screen settings saved successfully!', 'disciple_tools' ) . '</p></div>';
        });
    }

    /**
     * Get current settings
     */
    private function get_settings() {
        $defaults = [
            'title' => __( 'Welcome to your Home Screen', 'disciple_tools' ),
            'description' => __( 'Your personalized dashboard for apps and training.', 'disciple_tools' ),
            'enable_training_videos' => 1,
            'enable_quick_actions' => 1,
            'enable_roles_permissions' => 1,
        ];

        $settings = get_option( 'dt_home_screen_settings', $defaults );
        return wp_parse_args( $settings, $defaults );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets( $hook ) {
        // Only load on our settings page
        if ( strpos( $hook, 'dt_options' ) === false ) {
            return;
        }

        // Enqueue required dependencies
        wp_enqueue_media();
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );

        // Enqueue lodash
        wp_enqueue_script( 'lodash', 'https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js', [], '4.17.21', true );

        // jQuery UI dialog styles (used by icon selector dialog)
        wp_enqueue_style( 'wp-jquery-ui-dialog' );

        wp_enqueue_style( 'dt-home-admin-style', get_template_directory_uri() . '/dt-apps/dt-home/assets/css/admin.css', [], '1.0.0' );
        // Ensure dt-options loads before our script so we can call its functions
        wp_enqueue_script( 'dt-options', get_template_directory_uri() . '/dt-core/admin/js/dt-options.js', [ 'jquery', 'jquery-ui-dialog', 'lodash' ], '1.0.0', true );
        wp_enqueue_script( 'dt-home-admin-script', get_template_directory_uri() . '/dt-apps/dt-home/assets/js/admin.js', [ 'jquery', 'dt-options' ], '1.0.0', true );

        // Localize the dt-options script with required data
        wp_localize_script(
            'dt-options', 'dt_admin_scripts', [
                'site_url'  => site_url(),
                'nonce'     => wp_create_nonce( 'wp_rest' ),
                'rest_root' => esc_url_raw( rest_url() ),
                'upload'    => [
                    'title'      => __( 'Upload Icon', 'disciple_tools' ),
                    'button_txt' => __( 'Upload', 'disciple_tools' )
                ]
            ]
        );
    }

    /**
     * Handle app actions (create, update, delete)
     */
    private function handle_app_action() {
        // Verify nonce first
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dt_home_app_nonce'] ?? '' ) ), 'dt_home_app_action' ) ) {
            wp_die( esc_html__( 'Security check failed. Please try again.', 'disciple_tools' ) );
        }

        $action = sanitize_text_field( wp_unslash( $_POST['dt_home_app_action'] ?? '' ) );
        $apps_manager = DT_Home_Apps::instance();

        switch ( $action ) {
            case 'create':
                $app_data = [
                    'title' => sanitize_text_field( wp_unslash( $_POST['app_title'] ?? '' ) ),
                    'description' => sanitize_textarea_field( wp_unslash( $_POST['app_description'] ?? '' ) ),
                    'url' => esc_url_raw( wp_unslash( $_POST['app_url'] ?? '#' ) ),
                    'icon' => sanitize_text_field( wp_unslash( $_POST['app_icon'] ?? 'mdi mdi-apps' ) ),
                    'color' => sanitize_hex_color( wp_unslash( $_POST['app_color'] ?? '#667eea' ) ),
                    'enabled' => isset( $_POST['app_enabled'] ),
                    'user_roles_type' => sanitize_text_field( wp_unslash( $_POST['app_user_roles_type'] ?? 'support_all_roles' ) ),
                    'roles' => isset( $_POST['app_roles'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['app_roles'] ) ) : []
                ];

                $result = $apps_manager->create_app( $app_data );
                if ( is_wp_error( $result ) ) {
                    add_action( 'admin_notices', function() use ( $result ) {
                        echo '<div class="notice notice-error"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
                    });
                } else {
                    add_action( 'admin_notices', function() {
                        echo '<div class="notice notice-success"><p>' . esc_html__( 'App created successfully!', 'disciple_tools' ) . '</p></div>';
                    });
                }
                break;

            case 'update':
                $app_id = sanitize_text_field( wp_unslash( $_POST['app_id'] ?? '' ) );
                $app_data = [
                    'title' => sanitize_text_field( wp_unslash( $_POST['app_title'] ?? '' ) ),
                    'description' => sanitize_textarea_field( wp_unslash( $_POST['app_description'] ?? '' ) ),
                    'url' => esc_url_raw( wp_unslash( $_POST['app_url'] ?? '#' ) ),
                    'icon' => sanitize_text_field( wp_unslash( $_POST['app_icon'] ?? 'mdi mdi-apps' ) ),
                    'color' => sanitize_hex_color( wp_unslash( $_POST['app_color'] ?? '#667eea' ) ),
                    'enabled' => isset( $_POST['app_enabled'] ),
                    'user_roles_type' => sanitize_text_field( wp_unslash( $_POST['app_user_roles_type'] ?? 'support_all_roles' ) ),
                    'roles' => isset( $_POST['app_roles'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['app_roles'] ) ) : []
                ];

                $result = $apps_manager->update_app( $app_id, $app_data );
                if ( is_wp_error( $result ) ) {
                    add_action( 'admin_notices', function() use ( $result ) {
                        echo '<div class="notice notice-error"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
                    });
                } else {
                    add_action( 'admin_notices', function() {
                        echo '<div class="notice notice-success"><p>' . esc_html__( 'App updated successfully!', 'disciple_tools' ) . '</p></div>';
                    });
                }
                break;

            case 'delete':
                $app_id = sanitize_text_field( wp_unslash( $_POST['app_id'] ?? '' ) );
                $result = $apps_manager->delete_app( $app_id );
                if ( is_wp_error( $result ) ) {
                    add_action( 'admin_notices', function() use ( $result ) {
                        echo '<div class="notice notice-error"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
                    });
                } else {
                    add_action( 'admin_notices', function() {
                        echo '<div class="notice notice-success"><p>' . esc_html__( 'App deleted successfully!', 'disciple_tools' ) . '</p></div>';
                    });
                }
                break;
        }
    }

    /**
     * Handle video actions (create, update, delete)
     */
    private function handle_video_action() {
        // Verify nonce first
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dt_home_video_nonce'] ?? '' ) ), 'dt_home_video_action' ) ) {
            wp_die( esc_html__( 'Security check failed. Please try again.', 'disciple_tools' ) );
        }

        $action = sanitize_text_field( wp_unslash( $_POST['dt_home_video_action'] ?? '' ) );
        $training_manager = DT_Home_Training::instance();

        switch ( $action ) {
            case 'create':
                $video_data = [
                    'title' => sanitize_text_field( wp_unslash( $_POST['video_title'] ?? '' ) ),
                    'description' => sanitize_textarea_field( wp_unslash( $_POST['video_description'] ?? '' ) ),
                    'video_url' => esc_url_raw( wp_unslash( $_POST['video_url'] ?? '' ) ),
                    'duration' => sanitize_text_field( wp_unslash( $_POST['video_duration'] ?? '' ) ),
                    'category' => sanitize_text_field( wp_unslash( $_POST['video_category'] ?? 'general' ) ),
                    'enabled' => isset( $_POST['video_enabled'] )
                ];

                $result = $training_manager->create_video( $video_data );
                if ( is_wp_error( $result ) ) {
                    add_action( 'admin_notices', function() use ( $result ) {
                        echo '<div class="notice notice-error"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
                    });
                } else {
                    add_action( 'admin_notices', function() {
                        echo '<div class="notice notice-success"><p>' . esc_html__( 'Video created successfully!', 'disciple_tools' ) . '</p></div>';
                    });
                }
                break;

            case 'update':
                $video_id = sanitize_text_field( wp_unslash( $_POST['video_id'] ?? '' ) );
                $video_data = [
                    'title' => sanitize_text_field( wp_unslash( $_POST['video_title'] ?? '' ) ),
                    'description' => sanitize_textarea_field( wp_unslash( $_POST['video_description'] ?? '' ) ),
                    'video_url' => esc_url_raw( wp_unslash( $_POST['video_url'] ?? '' ) ),
                    'duration' => sanitize_text_field( wp_unslash( $_POST['video_duration'] ?? '' ) ),
                    'category' => sanitize_text_field( wp_unslash( $_POST['video_category'] ?? 'general' ) ),
                    'enabled' => isset( $_POST['video_enabled'] )
                ];

                $result = $training_manager->update_video( $video_id, $video_data );
                if ( is_wp_error( $result ) ) {
                    add_action( 'admin_notices', function() use ( $result ) {
                        echo '<div class="notice notice-error"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
                    });
                } else {
                    add_action( 'admin_notices', function() {
                        echo '<div class="notice notice-success"><p>' . esc_html__( 'Video updated successfully!', 'disciple_tools' ) . '</p></div>';
                    });
                }
                break;

            case 'delete':
                $video_id = sanitize_text_field( wp_unslash( $_POST['video_id'] ?? '' ) );
                $result = $training_manager->delete_video( $video_id );
                if ( is_wp_error( $result ) ) {
                    add_action( 'admin_notices', function() use ( $result ) {
                        echo '<div class="notice notice-error"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
                    });
                } else {
                    add_action( 'admin_notices', function() {
                        echo '<div class="notice notice-success"><p>' . esc_html__( 'Video deleted successfully!', 'disciple_tools' ) . '</p></div>';
                    });
                }
                break;
        }
    }

    /**
     * Render add app form
     */
    private function render_add_app_form() {
        ?>
        <div class="add-app-form-container">
            <h4><?php esc_html_e( 'Add New App', 'disciple_tools' ); ?></h4>
            <form method="post" class="app-form" name="dt_home_app_form_create">
                <?php wp_nonce_field( 'dt_home_app_action', 'dt_home_app_nonce' ); ?>
                <input type="hidden" name="dt_home_app_action" value="create">

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Title', 'disciple_tools' ); ?></th>
                        <td><input type="text" name="app_title" required class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Description', 'disciple_tools' ); ?></th>
                        <td><textarea name="app_description" rows="3" class="large-text"></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'URL', 'disciple_tools' ); ?></th>
                        <td><input type="url" name="app_url" class="regular-text" placeholder="#" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Icon', 'disciple_tools' ); ?></th>
                        <td>
                            <input type="text" name="app_icon" class="regular-text" value="mdi mdi-apps" placeholder="mdi mdi-apps" />
                                            <button type="button" class="button change-icon-button" data-form="dt_home_app_form_create" data-icon-input="app_icon"><?php esc_html_e( 'Change Icon', 'disciple_tools' ); ?></button>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Color', 'disciple_tools' ); ?></th>
                        <td><input type="color" name="app_color" value="#667eea" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Enabled', 'disciple_tools' ); ?></th>
                        <td><input type="checkbox" name="app_enabled" checked /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'User Access', 'disciple_tools' ); ?></th>
                        <td>
                            <label>
                                <input type="radio" name="app_user_roles_type" value="support_all_roles" checked />
                                <?php esc_html_e( 'All roles have access', 'disciple_tools' ); ?>
                            </label>
                            <br>
                            <label>
                                <input type="radio" name="app_user_roles_type" value="support_specific_roles" />
                                <?php esc_html_e( 'Limit access by role', 'disciple_tools' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr class="app-roles-selection" style="display: none;">
                        <th scope="row"><?php esc_html_e( 'Select Roles', 'disciple_tools' ); ?></th>
                        <td>
                            <?php
                            $roles_permissions = DT_Home_Roles_Permissions::instance();
                            $dt_roles = $roles_permissions->get_dt_roles_and_permissions();
                            ksort( $dt_roles );
                            ?>
                            <div class="roles-checkboxes" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
                                <?php foreach ( $dt_roles as $role_key => $role_data ) : ?>
                                    <label style="display: block; margin-bottom: 5px;">
                                        <input type="checkbox" name="app_roles[]" value="<?php echo esc_attr( $role_key ); ?>" />
                                        <?php echo esc_html( $role_data['label'] ?? $role_key ); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Add App', 'disciple_tools' ); ?>" />
                    <button type="button" class="button cancel-add-form"><?php esc_html_e( 'Cancel', 'disciple_tools' ); ?></button>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Render apps list
     */
    private function render_apps_list() {
        $apps_manager = DT_Home_Apps::instance();
        $apps = $apps_manager->get_all_apps();
        ?>
        <div class="apps-list-container">
            <h4><?php esc_html_e( 'Existing Apps', 'disciple_tools' ); ?></h4>
            <?php if ( empty( $apps ) ) : ?>
                <p><?php esc_html_e( 'No apps found.', 'disciple_tools' ); ?></p>
            <?php else : ?>
                <table class="wp-list-table widefat fixed striped sortable-table" data-type="apps">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;"><?php esc_html_e( '⋮⋮', 'disciple_tools' ); ?></th>
                            <th><?php esc_html_e( 'Title', 'disciple_tools' ); ?></th>
                            <th><?php esc_html_e( 'Description', 'disciple_tools' ); ?></th>
                            <th style="text-align: center;"><?php esc_html_e( 'Icon', 'disciple_tools' ); ?></th>
                            <th><?php esc_html_e( 'URL', 'disciple_tools' ); ?></th>
                            <th style="text-align: center;"><?php esc_html_e( 'Access', 'disciple_tools' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'disciple_tools' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'disciple_tools' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $apps as $app ) : ?>
                            <tr draggable="true" data-app-id="<?php echo esc_attr( $app['id'] ); ?>" 
                                data-user-roles-type="<?php echo esc_attr( $app['user_roles_type'] ?? 'support_all_roles' ); ?>"
                                data-roles="<?php echo esc_attr( json_encode( $app['roles'] ?? [] ) ); ?>"
                                style="cursor: move;">
                                <td class="drag-handle" style="text-align: center; cursor: grab; background-color: #f9f9f9;">
                                    <span class="drag-icon" style="font-size: 14px; color: #666;">⋮⋮</span>
                                </td>
                                <td>
                                    <strong><?php echo esc_html( $app['title'] ); ?></strong>
                                </td>
                                <td><?php echo esc_html( $app['description'] ); ?></td>
                                <td>
                                    <?php if ( !empty( $app['icon'] ) ) : ?>
                                        <?php if ( strpos( $app['icon'], 'mdi ' ) === 0 ) : ?>
                                            <i class="<?php echo esc_attr( $app['icon'] ); ?>" style="font-size: 20px; vertical-align: middle;"></i>
                                        <?php else : ?>
                                            <img src="<?php echo esc_attr( $app['icon'] ); ?>" style="width: 20px; height: 20px; vertical-align: middle;" />
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html( $app['url'] ); ?></td>
                                <td style="text-align: center;">
                                    <?php if ( isset( $app['user_roles_type'] ) && $app['user_roles_type'] === 'support_specific_roles' ) : ?>
                                        <i class="mdi mdi-eye-lock-outline" style="font-size: 16px; color: #f39c12;" title="<?php esc_attr_e( 'Limited to specific roles', 'disciple_tools' ); ?>"></i>
                                        <small style="display: block; margin-top: 2px;">
                                            <?php
                                            $roles_count = count( $app['roles'] ?? [] );
                                            if ( $roles_count > 0 ) {
                                                echo esc_html( sprintf( _n( '%d role', '%d roles', $roles_count, 'disciple_tools' ), $roles_count ) );
                                            } else {
                                                echo esc_html__( 'No roles selected', 'disciple_tools' );
                                            }
                                            ?>
                                        </small>
                                    <?php else : ?>
                                        <i class="mdi mdi-eye" style="font-size: 16px; color: #27ae60;" title="<?php esc_attr_e( 'All roles have access', 'disciple_tools' ); ?>"></i>
                                        <small style="display: block; margin-top: 2px;"><?php esc_html_e( 'All roles', 'disciple_tools' ); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-<?php echo $app['enabled'] ? 'enabled' : 'disabled'; ?>">
                                        <?php echo $app['enabled'] ? esc_html__( 'Enabled', 'disciple_tools' ) : esc_html__( 'Disabled', 'disciple_tools' ); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="#" class="button button-small edit-app" data-app-id="<?php echo esc_attr( $app['id'] ); ?>">
                                        <?php esc_html_e( 'Edit', 'disciple_tools' ); ?>
                                    </a>
                                    <form method="post" style="display: inline;">
                                        <?php wp_nonce_field( 'dt_home_app_action', 'dt_home_app_nonce' ); ?>
                                        <input type="hidden" name="dt_home_app_action" value="delete">
                                        <input type="hidden" name="app_id" value="<?php echo esc_attr( $app['id'] ); ?>">
                                        <input type="submit" class="button button-small button-link-delete" value="<?php esc_attr_e( 'Delete', 'disciple_tools' ); ?>" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this app?', 'disciple_tools' ); ?>')">
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render add video form
     */
    private function render_add_video_form() {
        ?>
        <div class="add-video-form-container">
            <h4><?php esc_html_e( 'Add New Training Video', 'disciple_tools' ); ?></h4>
            <form method="post" class="video-form">
                <?php wp_nonce_field( 'dt_home_video_action', 'dt_home_video_nonce' ); ?>
                <input type="hidden" name="dt_home_video_action" value="create">

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Title', 'disciple_tools' ); ?></th>
                        <td><input type="text" name="video_title" required class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Description', 'disciple_tools' ); ?></th>
                        <td><textarea name="video_description" rows="3" class="large-text"></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Video URL', 'disciple_tools' ); ?></th>
                        <td><input type="url" name="video_url" required class="regular-text" placeholder="https://youtube.com/watch?v=..." /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Duration', 'disciple_tools' ); ?></th>
                        <td><input type="text" name="video_duration" class="regular-text" placeholder="5:30" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Category', 'disciple_tools' ); ?></th>
                        <td>
                            <select name="video_category" class="regular-text">
                                <option value="general"><?php esc_html_e( 'General', 'disciple_tools' ); ?></option>
                                <option value="basics"><?php esc_html_e( 'Basics', 'disciple_tools' ); ?></option>
                                <option value="advanced"><?php esc_html_e( 'Advanced', 'disciple_tools' ); ?></option>
                                <option value="tutorial"><?php esc_html_e( 'Tutorial', 'disciple_tools' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Enabled', 'disciple_tools' ); ?></th>
                        <td><input type="checkbox" name="video_enabled" checked /></td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Add Video', 'disciple_tools' ); ?>" />
                    <button type="button" class="button cancel-add-form"><?php esc_html_e( 'Cancel', 'disciple_tools' ); ?></button>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Render videos list
     */
    private function render_videos_list() {
        $training_manager = DT_Home_Training::instance();
        $videos = $training_manager->get_all_videos();
        ?>
        <div class="videos-list-container">
            <h4><?php esc_html_e( 'Existing Training Videos', 'disciple_tools' ); ?></h4>
            <?php if ( empty( $videos ) ) : ?>
                <p><?php esc_html_e( 'No training videos found.', 'disciple_tools' ); ?></p>
            <?php else : ?>
                <table class="wp-list-table widefat fixed striped sortable-table" data-type="videos">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;"><?php esc_html_e( '⋮⋮', 'disciple_tools' ); ?></th>
                            <th><?php esc_html_e( 'Title', 'disciple_tools' ); ?></th>
                            <th><?php esc_html_e( 'Description', 'disciple_tools' ); ?></th>
                            <th><?php esc_html_e( 'Duration', 'disciple_tools' ); ?></th>
                            <th><?php esc_html_e( 'Category', 'disciple_tools' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'disciple_tools' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'disciple_tools' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $videos as $video ) : ?>
                            <tr draggable="true" data-video-id="<?php echo esc_attr( $video['id'] ); ?>" data-video-url="<?php echo esc_attr( $video['video_url'] ?? '' ); ?>" style="cursor: move;">
                                <td class="drag-handle" style="text-align: center; cursor: grab; background-color: #f9f9f9;">
                                    <span class="drag-icon" style="font-size: 14px; color: #666;">⋮⋮</span>
                                </td>
                                <td>
                                    <strong><?php echo esc_html( $video['title'] ); ?></strong>
                                </td>
                                <td><?php echo esc_html( $video['description'] ); ?></td>
                                <td><?php echo esc_html( $video['duration'] ); ?></td>
                                <td><?php echo esc_html( ucfirst( $video['category'] ) ); ?></td>
                                <td>
                                    <span class="status-<?php echo $video['enabled'] ? 'enabled' : 'disabled'; ?>">
                                        <?php echo $video['enabled'] ? esc_html__( 'Enabled', 'disciple_tools' ) : esc_html__( 'Disabled', 'disciple_tools' ); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="#" class="button button-small edit-video" data-video-id="<?php echo esc_attr( $video['id'] ); ?>">
                                        <?php esc_html_e( 'Edit', 'disciple_tools' ); ?>
                                    </a>
                                    <form method="post" style="display: inline;">
                                        <?php wp_nonce_field( 'dt_home_video_action', 'dt_home_video_nonce' ); ?>
                                        <input type="hidden" name="dt_home_video_action" value="delete">
                                        <input type="hidden" name="video_id" value="<?php echo esc_attr( $video['id'] ); ?>">
                                        <input type="submit" class="button button-small button-link-delete" value="<?php esc_attr_e( 'Delete', 'disciple_tools' ); ?>" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this video?', 'disciple_tools' ); ?>')">
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render apps management interface
     */
    private function render_apps_management() {
        $apps_manager = DT_Home_Apps::instance();
        $apps = $apps_manager->get_all_apps();
        ?>
        <div class="apps-management-interface">
            <!-- Add New App Form -->
            <div class="add-app-form">
                <h4><?php esc_html_e( 'Add New App', 'disciple_tools' ); ?></h4>
                <form method="post" class="app-form">
                    <?php wp_nonce_field( 'dt_home_app_action', 'dt_home_app_nonce' ); ?>
                    <input type="hidden" name="dt_home_app_action" value="create">

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Title', 'disciple_tools' ); ?></th>
                            <td><input type="text" name="app_title" required class="regular-text" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Description', 'disciple_tools' ); ?></th>
                            <td><textarea name="app_description" rows="3" class="large-text"></textarea></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'URL', 'disciple_tools' ); ?></th>
                            <td><input type="url" name="app_url" class="regular-text" placeholder="#" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Icon', 'disciple_tools' ); ?></th>
                            <td>
                                <input type="text" name="app_icon" class="regular-text" value="mdi mdi-apps" placeholder="mdi mdi-apps" />
                                <button type="button" class="button change-icon-button" data-form="dt_home_app_form_create" data-icon-input="app_icon"><?php esc_html_e( 'Change Icon', 'disciple_tools' ); ?></button>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Color', 'disciple_tools' ); ?></th>
                            <td><input type="color" name="app_color" value="#667eea" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Enabled', 'disciple_tools' ); ?></th>
                            <td><input type="checkbox" name="app_enabled" checked /></td>
                        </tr>
                    </table>

                    <p class="submit">
                        <input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Add App', 'disciple_tools' ); ?>" />
                    </p>
                </form>
            </div>

            <!-- Apps List -->
            <div class="apps-list">
                <h4><?php esc_html_e( 'Existing Apps', 'disciple_tools' ); ?></h4>
                <?php if ( empty( $apps ) ) : ?>
                    <p><?php esc_html_e( 'No apps found.', 'disciple_tools' ); ?></p>
                <?php else : ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Title', 'disciple_tools' ); ?></th>
                                <th><?php esc_html_e( 'Description', 'disciple_tools' ); ?></th>
                                <th><?php esc_html_e( 'URL', 'disciple_tools' ); ?></th>
                                <th><?php esc_html_e( 'Status', 'disciple_tools' ); ?></th>
                                <th><?php esc_html_e( 'Actions', 'disciple_tools' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $apps as $app ) : ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html( $app['title'] ); ?></strong>
                                        <br>
                                        <small><?php echo esc_html( $app['icon'] ); ?></small>
                                    </td>
                                    <td><?php echo esc_html( $app['description'] ); ?></td>
                                    <td><?php echo esc_html( $app['url'] ); ?></td>
                                    <td>
                                        <span class="status-<?php echo $app['enabled'] ? 'enabled' : 'disabled'; ?>">
                                            <?php echo $app['enabled'] ? esc_html__( 'Enabled', 'disciple_tools' ) : esc_html__( 'Disabled', 'disciple_tools' ); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="#" class="button button-small edit-app" data-app-id="<?php echo esc_attr( $app['id'] ); ?>">
                                            <?php esc_html_e( 'Edit', 'disciple_tools' ); ?>
                                        </a>
                                        <form method="post" style="display: inline;">
                                            <?php wp_nonce_field( 'dt_home_app_action', 'dt_home_app_nonce' ); ?>
                                            <input type="hidden" name="dt_home_app_action" value="delete">
                                            <input type="hidden" name="app_id" value="<?php echo esc_attr( $app['id'] ); ?>">
                                            <input type="submit" class="button button-small button-link-delete" value="<?php esc_attr_e( 'Delete', 'disciple_tools' ); ?>" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this app?', 'disciple_tools' ); ?>')">
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render training videos management interface
     */
    private function render_training_management() {
        $training_manager = DT_Home_Training::instance();
        $videos = $training_manager->get_all_videos();
        ?>
        <div class="training-management-interface">
            <!-- Add New Video Form -->
            <div class="add-video-form">
                <h4><?php esc_html_e( 'Add New Training Video', 'disciple_tools' ); ?></h4>
                <form method="post" class="video-form">
                    <?php wp_nonce_field( 'dt_home_video_action', 'dt_home_video_nonce' ); ?>
                    <input type="hidden" name="dt_home_video_action" value="create">

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Title', 'disciple_tools' ); ?></th>
                            <td><input type="text" name="video_title" required class="regular-text" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Description', 'disciple_tools' ); ?></th>
                            <td><textarea name="video_description" rows="3" class="large-text"></textarea></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Video URL', 'disciple_tools' ); ?></th>
                            <td><input type="url" name="video_url" required class="regular-text" placeholder="https://youtube.com/watch?v=..." /></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Duration', 'disciple_tools' ); ?></th>
                            <td><input type="text" name="video_duration" class="regular-text" placeholder="5:30" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Category', 'disciple_tools' ); ?></th>
                            <td>
                                <select name="video_category" class="regular-text">
                                    <option value="general"><?php esc_html_e( 'General', 'disciple_tools' ); ?></option>
                                    <option value="basics"><?php esc_html_e( 'Basics', 'disciple_tools' ); ?></option>
                                    <option value="advanced"><?php esc_html_e( 'Advanced', 'disciple_tools' ); ?></option>
                                    <option value="tutorial"><?php esc_html_e( 'Tutorial', 'disciple_tools' ); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Enabled', 'disciple_tools' ); ?></th>
                            <td><input type="checkbox" name="video_enabled" checked /></td>
                        </tr>
                    </table>

                    <p class="submit">
                        <input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Add Video', 'disciple_tools' ); ?>" />
                    </p>
                </form>
            </div>

            <!-- Videos List -->
            <div class="videos-list">
                <h4><?php esc_html_e( 'Existing Training Videos', 'disciple_tools' ); ?></h4>
                <?php if ( empty( $videos ) ) : ?>
                    <p><?php esc_html_e( 'No training videos found.', 'disciple_tools' ); ?></p>
                <?php else : ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Title', 'disciple_tools' ); ?></th>
                                <th><?php esc_html_e( 'Description', 'disciple_tools' ); ?></th>
                                <th><?php esc_html_e( 'Duration', 'disciple_tools' ); ?></th>
                                <th><?php esc_html_e( 'Category', 'disciple_tools' ); ?></th>
                                <th><?php esc_html_e( 'Status', 'disciple_tools' ); ?></th>
                                <th><?php esc_html_e( 'Actions', 'disciple_tools' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $videos as $video ) : ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html( $video['title'] ); ?></strong>
                                    </td>
                                    <td><?php echo esc_html( $video['description'] ); ?></td>
                                    <td><?php echo esc_html( $video['duration'] ); ?></td>
                                    <td><?php echo esc_html( ucfirst( $video['category'] ) ); ?></td>
                                    <td>
                                        <span class="status-<?php echo $video['enabled'] ? 'enabled' : 'disabled'; ?>">
                                            <?php echo $video['enabled'] ? esc_html__( 'Enabled', 'disciple_tools' ) : esc_html__( 'Disabled', 'disciple_tools' ); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="#" class="button button-small edit-video" data-video-id="<?php echo esc_attr( $video['id'] ); ?>">
                                            <?php esc_html_e( 'Edit', 'disciple_tools' ); ?>
                                        </a>
                                        <form method="post" style="display: inline;">
                                            <?php wp_nonce_field( 'dt_home_video_action', 'dt_home_video_nonce' ); ?>
                                            <input type="hidden" name="dt_home_video_action" value="delete">
                                            <input type="hidden" name="video_id" value="<?php echo esc_attr( $video['id'] ); ?>">
                                            <input type="submit" class="button button-small button-link-delete" value="<?php esc_attr_e( 'Delete', 'disciple_tools' ); ?>" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this video?', 'disciple_tools' ); ?>')">
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Handle AJAX request for reordering apps
     */
    public function handle_app_reorder() {
        // Verify nonce
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ?? '' ) ), 'dt_home_admin_nonce' ) ) {
            wp_die( json_encode( [ 'success' => false, 'message' => 'Security check failed.' ] ) );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_dt' ) ) {
            wp_die( json_encode( [ 'success' => false, 'message' => 'Insufficient permissions.' ] ) );
        }

        $ordered_ids = isset( $_GET['ordered_ids'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_GET['ordered_ids'] ) ) ) : [];

        if ( empty( $ordered_ids ) ) {
            wp_die( json_encode( [ 'success' => false, 'message' => 'No ordered IDs provided.' ] ) );
        }

        $apps_manager = DT_Home_Apps::instance();
        $result = $apps_manager->reorder_apps( $ordered_ids );

        if ( is_wp_error( $result ) ) {
            wp_die( json_encode( [ 'success' => false, 'message' => $result->get_error_message() ] ) );
        }

        wp_die( json_encode( [ 'success' => true, 'message' => 'Apps reordered successfully.' ] ) );
    }

    /**
     * Handle AJAX request for reordering training videos
     */
    public function handle_video_reorder() {
        // Verify nonce
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ?? '' ) ), 'dt_home_admin_nonce' ) ) {
            wp_die( json_encode( [ 'success' => false, 'message' => 'Security check failed.' ] ) );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_dt' ) ) {
            wp_die( json_encode( [ 'success' => false, 'message' => 'Insufficient permissions.' ] ) );
        }

        $ordered_ids = isset( $_GET['ordered_ids'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_GET['ordered_ids'] ) ) ) : [];

        if ( empty( $ordered_ids ) ) {
            wp_die( json_encode( [ 'success' => false, 'message' => 'No ordered IDs provided.' ] ) );
        }

        $training_manager = DT_Home_Training::instance();
        $result = $training_manager->reorder_videos( $ordered_ids );

        if ( is_wp_error( $result ) ) {
            wp_die( json_encode( [ 'success' => false, 'message' => $result->get_error_message() ] ) );
        }

        wp_die( json_encode( [ 'success' => true, 'message' => 'Videos reordered successfully.' ] ) );
    }
}

// Initialize the admin class
DT_Home_Admin::instance();
