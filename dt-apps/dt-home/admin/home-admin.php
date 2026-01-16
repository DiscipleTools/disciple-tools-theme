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

        // AJAX handler for refreshing apps and videos data
        add_action( 'wp_ajax_dt_home_refresh_data', [ $this, 'handle_refresh_data' ] );
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

            <form method="post" action="" id="dt-home-settings-form">
                <?php wp_nonce_field( 'dt_home_screen_settings', 'dt_home_screen_nonce' ); ?>
                <input type="hidden" name="dt_home_screen_settings" value="1">
                <?php wp_nonce_field( 'dt_home_admin_nonce', 'dt_home_admin_nonce' ); ?>

                <!-- Icon Selector Dialog -->
                <?php include get_template_directory() . '/dt-core/admin/menu/tabs/dialog-icon-selector.php'; ?>

                <!-- App Edit Modal Dialog -->
                <div id="dt-app-edit-dialog" style="display: none;" title="<?php esc_attr_e( 'Edit App', 'disciple_tools' ); ?>">
                    <form id="dt-app-edit-form" name="dt-app-edit-form" method="post" action="" class="app-edit-form">
                        <?php wp_nonce_field( 'dt_home_app_action', 'dt_home_app_nonce' ); ?>
                        <input type="hidden" name="dt_home_app_action" value="update">
                        <input type="hidden" name="app_id" id="app-edit-id" value="">
                        <div id="dt-app-edit-form-content">
                            <!-- Form content will be dynamically inserted here -->
                        </div>
                    </form>
                </div>

                <!-- Video Edit Modal Dialog -->
                <div id="dt-video-edit-dialog" style="display: none;" title="<?php esc_attr_e( 'Edit Video', 'disciple_tools' ); ?>">
                    <form id="dt-video-edit-form" name="dt-video-edit-form" method="post" action="" class="video-edit-form">
                        <?php wp_nonce_field( 'dt_home_video_action', 'dt_home_video_nonce' ); ?>
                        <input type="hidden" name="dt_home_video_action" value="update">
                        <input type="hidden" name="video_id" id="video-edit-id" value="">
                        <div id="dt-video-edit-form-content">
                            <!-- Form content will be dynamically inserted here -->
                        </div>
                    </form>
                </div>

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
                                    <label for="invite_others"><?php esc_html_e( 'Allow users to invite others', 'disciple_tools' ); ?></label>
                                </th>
                                <td>
                                    <?php
                                    $registration_enabled = function_exists( 'dt_can_users_register' ) && dt_can_users_register();
                                    // Get invite_others setting, default to 1 (true) if not set
                                    $invite_others_value = isset( $settings['invite_others'] ) ? (int) $settings['invite_others'] : 1;
                                    $invite_others_enabled = (bool) $invite_others_value;
                                    ?>
                                    <input type="checkbox"
                                           id="invite_others"
                                           name="invite_others"
                                           value="1"
                                           <?php checked( $invite_others_enabled, true ); ?>
                                           <?php disabled( ! $registration_enabled ); ?> />
                                    <label for="invite_others"><?php esc_html_e( 'Allow users to invite others', 'disciple_tools' ); ?></label>
                                    <p class="description">
                                        <?php if ( $registration_enabled ) : ?>
                                            <?php esc_html_e( 'When enabled, users can share an invite link to allow others to register and access their own Home Screen.', 'disciple_tools' ); ?>
                                        <?php else : ?>
                                            <?php
                                            if ( is_multisite() ) {
                                                $settings_link = network_admin_url( 'settings.php' );
                                                $settings_text = __( 'Network Settings', 'disciple_tools' );
                                            } else {
                                                $settings_link = admin_url( 'options-general.php' );
                                                $settings_text = __( 'General Settings', 'disciple_tools' );
                                            }
                                            ?>
                                            <strong style="color: #d63638;">
                                                <?php esc_html_e( 'User registration must be enabled to use this feature.', 'disciple_tools' ); ?>
                                            </strong>
                                            <br>
                                            <?php
                                            $dt_general_settings_link = admin_url( 'admin.php?page=dt_options&tab=general#user-preferences' );
                                            printf(
                                                esc_html__( 'To enable user registration, you must enable it in both WordPress settings (%1$s) and Disciple Tools settings (%2$s).', 'disciple_tools' ),
                                                '<a href="' . esc_url( $settings_link ) . '">' . esc_html( $settings_text ) . '</a>',
                                                '<a href="' . esc_url( $dt_general_settings_link ) . '">' . esc_html__( 'General Settings', 'disciple_tools' ) . '</a>'
                                            );
                                            ?>
                                        <?php endif; ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="require_login"><?php esc_html_e( 'Require users to login', 'disciple_tools' ); ?></label>
                                </th>
                                <td>
                                    <?php
                                    // Get require_login setting, default to 1 (true) if not set
                                    $require_login_value = isset( $settings['require_login'] ) ? (int) $settings['require_login'] : 1;
                                    $require_login_enabled = (bool) $require_login_value;
                                    ?>
                                    <input type="checkbox"
                                           id="require_login"
                                           name="require_login"
                                           value="1"
                                           <?php checked( $require_login_enabled, true ); ?> />
                                    <label for="require_login"><?php esc_html_e( 'Require users to login to access the home screen magic link?', 'disciple_tools' ); ?></label>
                                    <p class="description">
                                        <?php esc_html_e( 'When enabled, users must be logged in to access their Home Screen. When disabled, magic links can be accessed without login.', 'disciple_tools' ); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>

                        <!-- Registration Status Info -->
                        <div class="registration-status" style="margin-top: 20px; padding: 15px; background: #f0f6fc; border-left: 4px solid #2271b1; border-radius: 4px;">
                            <p style="margin: 0;">
                                <strong><?php esc_html_e( 'User Registration Status:', 'disciple_tools' ); ?></strong>
                                <?php
                                if ( $registration_enabled ) {
                                    echo '<span style="color: #00a32a; font-weight: 600;">' . esc_html__( 'Enabled', 'disciple_tools' ) . '</span>';
                                } else {
                                    echo '<span style="color: #d63638; font-weight: 600;">' . esc_html__( 'Disabled', 'disciple_tools' ) . '</span>';
                                }
                                ?>
                                <br>
                                <?php
                                if ( is_multisite() ) {
                                    $settings_link = network_admin_url( 'settings.php' );
                                    $settings_text = __( 'Network Settings', 'disciple_tools' );
                                } else {
                                    $settings_link = admin_url( 'options-general.php' );
                                    $settings_text = __( 'General Settings', 'disciple_tools' );
                                }
                                $dt_general_settings_link = admin_url( 'admin.php?page=dt_options&tab=general#user-preferences' );
                                printf(
                                    esc_html__( 'To change registration settings, visit WordPress %1$s and Disciple Tools %2$s.', 'disciple_tools' ),
                                    '<a href="' . esc_url( $settings_link ) . '">' . esc_html( $settings_text ) . '</a>',
                                    '<a href="' . esc_url( $dt_general_settings_link ) . '">' . esc_html__( 'General Settings', 'disciple_tools' ) . '</a>'
                                );
                                ?>
                            </p>
                        </div>

                        <!-- Save Settings Button -->
                        <p class="submit">
                            <input type="submit" name="submit" id="save-settings-top" class="button" value="<?php esc_attr_e( 'Save Settings', 'disciple_tools' ); ?>" />
                        </p>
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
                            <button type="button" class="button add-new-app-btn">
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
                            <button type="button" class="button add-new-video-btn">
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

            /* Modal dialog styles to prevent horizontal scrollbars */
            #dt-app-edit-dialog {
                overflow-x: hidden;
            }

            #dt-app-edit-dialog .ui-dialog-content {
                overflow-x: hidden !important;
                padding: 20px;
                box-sizing: border-box;
            }

            #dt-app-edit-form-content {
                width: 100%;
                box-sizing: border-box;
            }

            #dt-app-edit-form-content .form-table {
                width: 100%;
                box-sizing: border-box;
            }

            #dt-app-edit-form-content .form-table th,
            #dt-app-edit-form-content .form-table td {
                padding: 8px 10px;
                box-sizing: border-box;
            }

            #dt-app-edit-form-content .form-table th {
                width: 150px;
                min-width: 150px;
            }

            #dt-app-edit-form-content .form-table td {
                width: auto;
            }

            #dt-app-edit-form-content input[type="text"],
            #dt-app-edit-form-content input[type="url"],
            #dt-app-edit-form-content textarea,
            #dt-app-edit-form-content select {
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }

            #dt-app-edit-form-content .large-text {
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }

            /* Video Edit Modal Dialog Styles */
            #dt-video-edit-dialog {
                overflow-x: hidden;
            }

            #dt-video-edit-dialog .ui-dialog-content {
                overflow-x: hidden !important;
                padding: 20px;
                box-sizing: border-box;
            }

            #dt-video-edit-form-content {
                width: 100%;
                box-sizing: border-box;
            }

            #dt-video-edit-form-content .form-table {
                width: 100%;
                box-sizing: border-box;
            }

            #dt-video-edit-form-content .form-table th,
            #dt-video-edit-form-content .form-table td {
                padding: 8px 10px;
                box-sizing: border-box;
            }

            #dt-video-edit-form-content .form-table th {
                width: 150px;
                min-width: 150px;
            }

            #dt-video-edit-form-content .form-table td {
                width: auto;
            }

            #dt-video-edit-form-content input[type="text"],
            #dt-video-edit-form-content input[type="url"],
            #dt-video-edit-form-content textarea,
            #dt-video-edit-form-content select {
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }

            #dt-video-edit-form-content .large-text {
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }

            /* Add Form Styles - Consistent Field Widths and Alignment */
            .add-app-form-container .form-table,
            .add-video-form-container .form-table {
                width: 100%;
                box-sizing: border-box;
            }

            .add-app-form-container .form-table th,
            .add-video-form-container .form-table th {
                width: 150px;
                min-width: 150px;
                padding: 10px 15px 10px 0;
                vertical-align: top;
            }

            .add-app-form-container .form-table td,
            .add-video-form-container .form-table td {
                padding: 10px 0;
                width: auto;
            }

            /* Consistent input widths */
            .add-app-form-container input[type="text"],
            .add-app-form-container input[type="url"],
            .add-app-form-container textarea,
            .add-app-form-container select,
            .add-video-form-container input[type="text"],
            .add-video-form-container input[type="url"],
            .add-video-form-container textarea,
            .add-video-form-container select {
                width: 100% !important;
                max-width: 400px !important;
                box-sizing: border-box;
            }

            /* Override WordPress regular-text class width */
            .add-app-form-container .regular-text,
            .add-video-form-container .regular-text {
                width: 100% !important;
                max-width: 400px !important;
            }

            /* Color input - make it wider but consistent */
            .add-app-form-container input[type="color"] {
                width: 80px;
                height: 35px;
                padding: 2px;
                box-sizing: border-box;
                cursor: pointer;
            }

            /* Icon field with button - use flexbox for alignment */
            .add-app-form-container .icon-field-wrapper {
                display: flex;
                align-items: center;
                gap: 8px;
                width: 100%;
                max-width: 400px;
            }

            .add-app-form-container .icon-field-wrapper input[type="text"] {
                flex: 1;
                min-width: 0;
                max-width: none;
            }

            .add-app-form-container .icon-field-wrapper .button {
                flex-shrink: 0;
            }

            /* Checkbox and radio button alignment */
            .add-app-form-container input[type="checkbox"],
            .add-video-form-container input[type="checkbox"] {
                width: auto;
                margin-right: 5px;
            }

            .add-app-form-container label,
            .add-video-form-container label {
                display: inline-flex;
                align-items: center;
                margin-bottom: 5px;
            }

            .add-app-form-container .radio-group label,
            .add-video-form-container .radio-group label {
                display: block;
                margin-bottom: 8px;
            }

            /* Description textarea - make it consistent width */
            .add-app-form-container textarea.large-text,
            .add-video-form-container textarea.large-text {
                width: 100%;
                max-width: 400px;
                min-height: 80px;
                box-sizing: border-box;
            }

            /* Submit button area */
            .add-app-form-container .submit,
            .add-video-form-container .submit {
                margin-top: 15px;
                padding-top: 15px;
                border-top: 1px solid #ddd;
            }

            .add-app-form-container .submit .button,
            .add-video-form-container .submit .button {
                margin-right: 10px;
            }

            /* Ensure row borders extend fully across both tables */
            .sortable-table {
                border-collapse: collapse;
                width: 100%;
            }

            .sortable-table tbody tr {
                border-bottom: 1px solid #dcdcde;
            }

            .sortable-table tbody tr:last-child {
                border-bottom: none;
            }

            .sortable-table tbody td,
            .sortable-table tbody th {
                border-bottom: none;
                padding: 8px 10px;
            }

            .sortable-table thead th {
                border-bottom: 1px solid #dcdcde;
            }

            /* Drag and drop visual feedback */
            .sortable-table tbody tr.dragging {
                opacity: 0.5;
                background-color: #f0f0f1;
            }

            /* Highlight bottom border of row above the drop position */
            .sortable-table tbody tr.drop-indicator-bottom {
                border-bottom: 2px solid #2271b1 !important;
            }

            /* Highlight top border of first row when dropping at the top */
            .sortable-table tbody tr.drop-indicator-first {
                border-top: 2px solid #2271b1 !important;
            }

            /* Highlight bottom border of last row when dropping at the end */
            .sortable-table tbody tr.drop-indicator-last {
                border-bottom: 2px solid #2271b1 !important;
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

        // Check if registration is enabled (if not, invite_others checkbox will be disabled and won't submit)
        $registration_enabled = function_exists( 'dt_can_users_register' ) && dt_can_users_register();

        $settings = [
            'title' => sanitize_text_field( wp_unslash( $_POST['home_screen_title'] ?? '' ) ),
            'description' => sanitize_textarea_field( wp_unslash( $_POST['home_screen_description'] ?? '' ) ),
            'enable_roles_permissions' => 1, // Always enabled by default
        ];

        // Handle require_login (checkbox sends "1" when checked, "0" when unchecked via hidden field)
        $require_login_post = isset( $_POST['require_login'] ) ? sanitize_text_field( wp_unslash( $_POST['require_login'] ) ) : '';
        $settings['require_login'] = ( $require_login_post === '1' ) ? 1 : 0;

        // Only update invite_others if registration is enabled (checkbox will be enabled and can submit)
        // If registration is disabled, the checkbox is disabled and won't be in POST, so preserve existing value
        if ( $registration_enabled ) {
            // Checkbox sends "1" when checked, nothing when unchecked
            $invite_others_post = isset( $_POST['invite_others'] ) ? sanitize_text_field( wp_unslash( $_POST['invite_others'] ) ) : '';
            $settings['invite_others'] = ( $invite_others_post === '1' ) ? 1 : 0;
        }

        // Merge with existing settings to preserve any other settings that might exist
        $existing_settings = get_option( 'dt_home_screen_settings', [] );

        // Merge: existing settings as base, new settings override (array_merge ensures new values take precedence)
        $settings = array_merge( $existing_settings, $settings );

        // Ensure invite_others is stored as integer (preserve existing if not updated)
        if ( isset( $settings['invite_others'] ) ) {
            $settings['invite_others'] = (int) $settings['invite_others'];
        }

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
            'enable_roles_permissions' => 1,
            'invite_others' => 1, // Default to true
            'require_login' => 1, // Default to true
        ];

        $settings = get_option( 'dt_home_screen_settings', $defaults );
        $settings = wp_parse_args( $settings, $defaults );

        // Ensure invite_others is an integer (0 or 1)
        if ( isset( $settings['invite_others'] ) ) {
            $settings['invite_others'] = (int) $settings['invite_others'];
        }

        // Ensure require_login is an integer (0 or 1)
        if ( isset( $settings['require_login'] ) ) {
            $settings['require_login'] = (int) $settings['require_login'];
        }

        return $settings;
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

        // Localize the dt-home-admin-script with apps data, videos data and roles
        $apps_manager = DT_Home_Apps::instance();
        $apps = $apps_manager->get_all_apps();

        $training_manager = DT_Home_Training::instance();
        $videos = $training_manager->get_all_videos();

        $roles_permissions = DT_Home_Roles_Permissions::instance();
        $dt_roles = $roles_permissions->get_dt_roles_and_permissions();
        ksort( $dt_roles );

        wp_localize_script(
            'dt-home-admin-script',
            'dtHomeAdmin',
            [
                'apps' => $apps,
                'videos' => $videos,
                'roles' => $dt_roles,
                'nonce' => wp_create_nonce( 'dt_home_admin_nonce' ),
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'strings' => [
                    'edit_app' => __( 'Edit App', 'disciple_tools' ),
                    'update_app' => __( 'Update App', 'disciple_tools' ),
                    'edit_video' => __( 'Edit Video', 'disciple_tools' ),
                    'update_video' => __( 'Update Video', 'disciple_tools' ),
                    'cancel' => __( 'Cancel', 'disciple_tools' ),
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
                // Handle color: if empty or #cccccc (placeholder), set to empty string (no custom color)
                // Use empty string instead of null to ensure it's preserved in WordPress options
                $submitted_color = sanitize_text_field( wp_unslash( $_POST['app_color'] ?? '#667eea' ) );
                $color_value = '';
                if ( ! empty( $submitted_color ) && $submitted_color !== '#cccccc' ) {
                    $color_value = sanitize_hex_color( $submitted_color );
                    // If sanitize_hex_color returns empty (invalid), set to empty string
                    if ( empty( $color_value ) ) {
                        $color_value = '';
                    }
                }

                $app_data = [
                    'type' => sanitize_text_field( wp_unslash( $_POST['app_type'] ?? 'link' ) ),
                    'title' => sanitize_text_field( wp_unslash( $_POST['app_title'] ?? '' ) ),
                    'description' => sanitize_textarea_field( wp_unslash( $_POST['app_description'] ?? '' ) ),
                    'url' => esc_url_raw( wp_unslash( $_POST['app_url'] ?? '#' ) ),
                    'icon' => sanitize_text_field( wp_unslash( $_POST['app_icon'] ?? 'mdi mdi-apps' ) ),
                    'color' => $color_value,
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

                // Get the original app to check if it's a coded app and its current color state
                $original_app = $apps_manager->get_app( $app_id );
                $is_coded_app = isset( $original_app['creation_type'] ) && $original_app['creation_type'] === 'coded';
                $original_color = $original_app['color'] ?? '';

                // Get submitted color and original color from form
                $submitted_color = sanitize_text_field( wp_unslash( $_POST['app_color'] ?? '' ) );
                $form_original_color = sanitize_text_field( wp_unslash( $_POST['app_color_original'] ?? '' ) );

                // Debug logging (remove in production)
                error_log( 'DT Home: App color update - Submitted: ' . $submitted_color . ', Original: ' . ( $original_color ?: 'empty' ) . ', Form Original: ' . $form_original_color );

                // Determine if we should update the color
                // If original had no color (empty string) and submitted is #cccccc (placeholder), don't update
                // Otherwise, update the color
                // Use empty string instead of null to ensure it's preserved in WordPress options
                $should_update_color = true;
                $color_to_save = '';

                if ( empty( $original_color ) || trim( $original_color ) === '' ) {
                    // App originally had no color
                    if ( $submitted_color === '#cccccc' || empty( $submitted_color ) ) {
                        // User didn't change it (still placeholder or empty), don't update
                        $should_update_color = false;
                    } else {
                        // User set a color, update it
                        $color_to_save = sanitize_hex_color( $submitted_color );
                        // If sanitization fails, set to empty string
                        if ( empty( $color_to_save ) ) {
                            $color_to_save = '';
                        }
                    }
                } else {
                    // App originally had a color
                    if ( ! empty( $submitted_color ) ) {
                        if ( $submitted_color === '#cccccc' && $original_color !== '#cccccc' ) {
                            // User set it to placeholder and original was different, clear the color (use default)
                            $color_to_save = '';
                        } else {
                            // Update with submitted color (including if it's #cccccc and original was also #cccccc)
                            $color_to_save = sanitize_hex_color( $submitted_color );
                            // If sanitization fails, set to empty string
                            if ( empty( $color_to_save ) ) {
                                $color_to_save = '';
                            }
                        }
                    } else {
                        // Submitted is empty, set to empty string (user cleared it)
                        $color_to_save = '';
                    }
                }

                // Build app data - for coded apps, exclude title, description, and url
                $app_data = [
                    'type' => sanitize_text_field( wp_unslash( $_POST['app_type'] ?? 'link' ) ),
                    'icon' => sanitize_text_field( wp_unslash( $_POST['app_icon'] ?? 'mdi mdi-apps' ) ),
                    'enabled' => isset( $_POST['app_enabled'] ),
                    'user_roles_type' => sanitize_text_field( wp_unslash( $_POST['app_user_roles_type'] ?? 'support_all_roles' ) ),
                    'roles' => isset( $_POST['app_roles'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['app_roles'] ) ) : []
                ];

                // Only include title, description, and url for non-coded apps
                if ( ! $is_coded_app ) {
                    $app_data['title'] = sanitize_text_field( wp_unslash( $_POST['app_title'] ?? '' ) );
                    $app_data['description'] = sanitize_textarea_field( wp_unslash( $_POST['app_description'] ?? '' ) );
                    $app_data['url'] = esc_url_raw( wp_unslash( $_POST['app_url'] ?? '#' ) );
                }

                // Handle color update
                // Always include color if it's in the form data, especially for coded apps
                if ( isset( $_POST['app_color'] ) ) {
                    if ( $should_update_color ) {
                        $app_data['color'] = $color_to_save;
                    } else {
                        // Even if $should_update_color is false, if color is in POST, we should include it
                        // This handles cases where reset button was clicked or color was changed
                        $submitted_color_check = sanitize_text_field( wp_unslash( $_POST['app_color'] ?? '' ) );
                        if ( $submitted_color_check === '#cccccc' || empty( $submitted_color_check ) ) {
                            // User reset or cleared color - save as empty string
                            $app_data['color'] = '';
                        } else {
                            // User set a color - save it
                            $sanitized = sanitize_hex_color( $submitted_color_check );
                            $app_data['color'] = ! empty( $sanitized ) ? $sanitized : '';
                        }
                    }
                }

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

                // Check if app is a coded app - prevent deletion
                $app = $apps_manager->get_app( $app_id );
                if ( $app && isset( $app['creation_type'] ) && $app['creation_type'] === 'coded' ) {
                    add_action( 'admin_notices', function() {
                        echo '<div class="notice notice-error"><p>' . esc_html__( 'Coded apps cannot be deleted.', 'disciple_tools' ) . '</p></div>';
                    });
                    break;
                }

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
                        <th scope="row"><?php esc_html_e( 'Type', 'disciple_tools' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="app_type_link" id="app_type_link" checked />
                                <?php esc_html_e( 'Open as Link (opens in new tab)', 'disciple_tools' ); ?>
                            </label>
                            <input type="hidden" name="app_type" id="app_type" value="link" />
                            <p class="description" id="app-type-description-add">
                                <?php esc_html_e( 'Open the app in a new browser tab. Use this for external websites or resources.', 'disciple_tools' ); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Title', 'disciple_tools' ); ?></th>
                        <td><input type="text" name="app_title" required /></td>
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
                            <div class="icon-field-wrapper">
                                <input type="text" name="app_icon" value="mdi mdi-apps" placeholder="mdi mdi-apps" />
                                <button type="button" class="button change-icon-button" data-form="" data-icon-input="app_icon"><?php esc_html_e( 'Change Icon', 'disciple_tools' ); ?></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Color', 'disciple_tools' ); ?></th>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <input type="color" name="app_color" id="add-app-color" value="#cccccc" style="flex-shrink: 0;" />
                                <button type="button" class="button button-small reset-color-button" id="add-app-reset-color" style="flex-shrink: 0;">
                                    <?php esc_html_e( 'Reset to Default', 'disciple_tools' ); ?>
                                </button>
                            </div>
                            <p class="description" id="add-app-color-description" style="margin-top: 5px;">
                                <?php esc_html_e( 'Using default theme-aware color. The icon will automatically switch between black (light mode) and white (dark mode). Set a custom color to override.', 'disciple_tools' ); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Enabled', 'disciple_tools' ); ?></th>
                        <td><input type="checkbox" name="app_enabled" checked /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'User Access', 'disciple_tools' ); ?></th>
                        <td>
                            <div class="radio-group">
                                <label>
                                    <input type="radio" name="app_user_roles_type" value="support_all_roles" checked />
                                    <?php esc_html_e( 'All roles have access', 'disciple_tools' ); ?>
                                </label>
                                <label>
                                    <input type="radio" name="app_user_roles_type" value="support_specific_roles" />
                                    <?php esc_html_e( 'Limit access by role', 'disciple_tools' ); ?>
                                </label>
                            </div>
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
                    <input type="submit" class="button" value="<?php esc_attr_e( 'Add App', 'disciple_tools' ); ?>" />
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
                                data-color="<?php echo esc_attr( $app['color'] ?? '#667eea' ); ?>"
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
                                        <?php
                                        // Check if custom color is set
                                        $has_custom_color = !empty( $app['color'] ) && is_string( $app['color'] ) && trim( $app['color'] ) !== '';
                                        $app_color = $has_custom_color ? $app['color'] : '#0a0a0a'; // Default to black (light mode default)

                                        if ( strpos( $app['icon'], 'mdi ' ) === 0 ) : ?>
                                            <i class="<?php echo esc_attr( $app['icon'] ); ?> admin-app-icon"
                                               style="font-size: 20px; vertical-align: middle; color: <?php echo esc_attr( $app_color ); ?>;"
                                               data-has-custom-color="<?php echo $has_custom_color ? 'true' : 'false'; ?>"
                                               data-custom-color="<?php echo $has_custom_color ? esc_attr( $app['color'] ) : ''; ?>"></i>
                                        <?php else : ?>
                                            <img src="<?php echo esc_attr( $app['icon'] ); ?>" style="width: 20px; height: 20px; vertical-align: middle; filter: drop-shadow(0 0 2px <?php echo esc_attr( $app_color ); ?>);" />
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
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
                                    <?php
                                    $is_coded_app = isset( $app['creation_type'] ) && $app['creation_type'] === 'coded';
                                    if ( ! $is_coded_app ) : ?>
                                        <form method="post" style="display: inline;">
                                            <?php wp_nonce_field( 'dt_home_app_action', 'dt_home_app_nonce' ); ?>
                                            <input type="hidden" name="dt_home_app_action" value="delete">
                                            <input type="hidden" name="app_id" value="<?php echo esc_attr( $app['id'] ); ?>">
                                            <input type="submit" class="button button-small button-link-delete" value="<?php esc_attr_e( 'Delete', 'disciple_tools' ); ?>" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this app?', 'disciple_tools' ); ?>')">
                                        </form>
                                    <?php else : ?>
                                        <button type="button" class="button button-small button-link-delete" disabled style="opacity: 0.5; cursor: not-allowed;" title="<?php esc_attr_e( 'Coded apps cannot be deleted', 'disciple_tools' ); ?>">
                                            <?php esc_html_e( 'Delete', 'disciple_tools' ); ?>
                                        </button>
                                    <?php endif; ?>
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
                            <select name="video_category">
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
                    <input type="submit" class="button" value="<?php esc_attr_e( 'Add Video', 'disciple_tools' ); ?>" />
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
                            <th scope="row"><?php esc_html_e( 'Type', 'disciple_tools' ); ?></th>
                            <td>
                                <select style="min-width: 100%;" name="app_type" id="app_type" required>
                                    <option value="link"><?php esc_html_e( 'Link', 'disciple_tools' ) ?></option>
                                    <option value="app"><?php esc_html_e( 'App', 'disciple_tools' ) ?></option>
                                </select>
                            </td>
                        </tr>
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
                                <button type="button" class="button change-icon-button" data-form="" data-icon-input="app_icon"><?php esc_html_e( 'Change Icon', 'disciple_tools' ); ?></button>
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
                        <input type="submit" class="button" value="<?php esc_attr_e( 'Add App', 'disciple_tools' ); ?>" />
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
                        <input type="submit" class="button" value="<?php esc_attr_e( 'Add Video', 'disciple_tools' ); ?>" />
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

    /**
     * Handle AJAX request for refreshing apps and videos data
     */
    public function handle_refresh_data() {
        // Verify nonce
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ?? '' ) ), 'dt_home_admin_nonce' ) ) {
            wp_send_json_error( [ 'message' => 'Security check failed.' ] );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_dt' ) ) {
            wp_send_json_error( [ 'message' => 'Insufficient permissions.' ] );
        }

        // Get fresh apps and videos data
        $apps_manager = DT_Home_Apps::instance();
        $apps = $apps_manager->get_all_apps();

        $training_manager = DT_Home_Training::instance();
        $videos = $training_manager->get_all_videos();

        // Return the data
        wp_send_json_success( [
            'apps' => $apps,
            'videos' => $videos,
        ] );
    }
}

// Initialize the admin class
DT_Home_Admin::instance();
