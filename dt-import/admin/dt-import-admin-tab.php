<?php
/**
 * DT CSV Import Admin Tab Integration
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class DT_CSV_Import_Admin_Tab extends Disciple_Tools_Abstract_Menu_Base {
    private static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_submenu' ], 110 );
        add_action( 'dt_utilities_tab_menu', [ $this, 'add_tab' ], 110, 1 );
        add_action( 'dt_utilities_tab_content', [ $this, 'content' ], 110, 1 );
        parent::__construct();
    }

    public function add_submenu() {
        add_submenu_page(
            'dt_utilities',
            __( 'CSV Import', 'disciple_tools' ),
            __( 'CSV Import', 'disciple_tools' ),
            'manage_dt',
            'dt_utilities&tab=csv_import',
            [ 'DT_CSV_Import_Admin_Tab', 'content' ]
        );
    }

    public function add_tab( $tab ) {
        ?>
        <a href="<?php echo esc_url( admin_url() ) ?>admin.php?page=dt_utilities&tab=csv_import"
           class="nav-tab <?php echo esc_html( $tab == 'csv_import' ? 'nav-tab-active' : '' ) ?>">
            <?php echo esc_html__( 'CSV Import', 'disciple_tools' ) ?>
        </a>
        <?php
    }

    public function content( $tab ) {
        if ( 'csv_import' !== $tab ) {
            return;
        }

        // Check permissions
        if ( !current_user_can( 'manage_dt' ) ) {
            wp_die( 'You do not have sufficient permissions to access this page.' );
        }

        // Enqueue scripts and styles for this page
        $this->enqueue_admin_scripts();

        // Display the import interface
        $this->display_import_interface();
    }

    private function enqueue_admin_scripts() {
        // Enqueue DT Web Components if available
        if ( function_exists( 'dt_theme_enqueue_script' ) ) {
            dt_theme_enqueue_script( 'web-components', 'dt-assets/build/components/index.js', [], false );
            dt_theme_enqueue_style( 'web-components-css', 'dt-assets/build/css/light.min.css', [] );
        }

        // Enqueue Toastify for toast notifications
        wp_enqueue_style( 'toastify-css', 'https://cdn.jsdelivr.net/npm/toastify-js@1.12.0/src/toastify.min.css', [], '1.12.0' );
        wp_enqueue_script( 'toastify-js', 'https://cdn.jsdelivr.net/npm/toastify-js@1.12.0/src/toastify.min.js', [], '1.12.0', true );

        // Enqueue our custom import scripts
        wp_enqueue_script(
            'dt-import-js',
            get_template_directory_uri() . '/dt-import/assets/js/dt-import.js',
            [ 'jquery', 'toastify-js' ],
            '1.0.0',
            true
        );

        // Enqueue modal handling script
        wp_enqueue_script(
            'dt-import-modals-js',
            get_template_directory_uri() . '/dt-import/assets/js/dt-import-modals.js',
            [ 'dt-import-js' ],
            '1.0.0',
            true
        );

        // Enqueue our custom import styles
        wp_enqueue_style(
            'dt-import-css',
            get_template_directory_uri() . '/dt-import/assets/css/dt-import.css',
            [],
            '1.0.0'
        );

        // Localize script with necessary data
        wp_localize_script('dt-import-js', 'dtImport', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'restUrl' => rest_url( 'dt-csv-import/v2/' ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'postTypes' => $this->get_available_post_types(),
            'translations' => $this->get_translations(),
            'fieldTypes' => $this->get_field_types(),
            'geocodingServices' => $this->get_geocoding_services(),
            'maxFileSize' => $this->get_max_file_size(),
            'allowedFileTypes' => [ 'text/csv', 'application/csv', 'text/plain' ]
        ]);
    }

    private function get_available_post_types() {
        $post_types = DT_Posts::get_post_types();
        $formatted_types = [];

        foreach ( $post_types as $post_type ) {
            $post_settings = DT_Posts::get_post_settings( $post_type );
            $formatted_types[] = [
                'key' => $post_type,
                'label_singular' => $post_settings['label_singular'] ?? ucfirst( $post_type ),
                'label_plural' => $post_settings['label_plural'] ?? ucfirst( $post_type ) . 's',
                'description' => $this->get_post_type_description( $post_type )
            ];
        }

        return $formatted_types;
    }

    private function get_post_type_description( $post_type ) {
        $descriptions = [
            'contacts' => __( 'Individual people you are reaching or discipling', 'disciple_tools' ),
            'groups' => __( 'Groups, churches, or gatherings of people', 'disciple_tools' ),
        ];

        return $descriptions[$post_type] ?? '';
    }

    private function display_csv_examples_sidebar() {
        ?>
        <div class="postbox">
            <div class="postbox-header">
                <h2 class="hndle"><?php esc_html_e( 'CSV Examples', 'disciple_tools' ); ?></h2>
            </div>
            <div class="inside">
                <p><?php esc_html_e( 'Download example CSV files to help you format your data correctly.', 'disciple_tools' ); ?></p>

                <h4><?php esc_html_e( 'Basic Examples', 'disciple_tools' ); ?></h4>
                <ul>
                    <li>
                        <a href="<?php echo esc_url( get_template_directory_uri() . '/dt-import/assets/example_contacts.csv' ); ?>"
                           download="example_contacts.csv" class="button button-secondary">
                            <span class="dashicons dashicons-download"></span>
                            <?php esc_html_e( 'Basic Contacts CSV', 'disciple_tools' ); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo esc_url( get_template_directory_uri() . '/dt-import/assets/example_groups.csv' ); ?>"
                           download="example_groups.csv" class="button button-secondary">
                            <span class="dashicons dashicons-download"></span>
                            <?php esc_html_e( 'Basic Groups CSV', 'disciple_tools' ); ?>
                        </a>
                    </li>
                </ul>

                <h4><?php esc_html_e( 'Comprehensive Examples', 'disciple_tools' ); ?></h4>
                <ul>
                    <li>
                        <a href="<?php echo esc_url( get_template_directory_uri() . '/dt-import/assets/example_contacts_comprehensive.csv' ); ?>"
                           download="example_contacts_comprehensive.csv" class="button button-secondary">
                            <span class="dashicons dashicons-download"></span>
                            <?php esc_html_e( 'Comprehensive Contacts CSV', 'disciple_tools' ); ?>
                        </a>
                        <p class="description">
                            <?php esc_html_e( 'Includes all contact field types and milestone options with realistic examples.', 'disciple_tools' ); ?>
                        </p>
                    </li>
                    <li>
                        <a href="<?php echo esc_url( get_template_directory_uri() . '/dt-import/assets/example_groups_comprehensive.csv' ); ?>"
                           download="example_groups_comprehensive.csv" class="button button-secondary">
                            <span class="dashicons dashicons-download"></span>
                            <?php esc_html_e( 'Comprehensive Groups CSV', 'disciple_tools' ); ?>
                        </a>
                        <p class="description">
                            <?php esc_html_e( 'Includes all group types, health metrics, and relationship examples.', 'disciple_tools' ); ?>
                        </p>
                    </li>
                </ul>

                <h4><?php esc_html_e( 'Import Tips', 'disciple_tools' ); ?></h4>
                <ul class="ul-disc">
                    <li><?php esc_html_e( 'Use semicolons (;) to separate multiple values in a single field', 'disciple_tools' ); ?></li>
                    <li><?php esc_html_e( 'Date format should be YYYY-MM-DD (e.g., 2024-01-15)', 'disciple_tools' ); ?></li>
                    <li><?php esc_html_e( 'Leave cells empty if no data is available', 'disciple_tools' ); ?></li>
                    <li><?php esc_html_e( 'Use exact field value keys for dropdown options', 'disciple_tools' ); ?></li>
                </ul>

                <div style="margin-top: 15px; padding: 10px; background: #f0f8ff; border-left: 4px solid #0073aa;">
                    <p style="margin: 0; font-size: 12px;">
                        <strong><?php esc_html_e( 'Note:', 'disciple_tools' ); ?></strong>
                        <?php esc_html_e( 'The comprehensive examples show all available field options. You only need to include the columns relevant to your data.', 'disciple_tools' ); ?>
                    </p>
                </div>
            </div>
        </div>
        <?php
    }

    private function display_documentation_sidebar() {
        ?>
        <div class="postbox">
            <div class="postbox-header">
                <h2 class="hndle"><?php esc_html_e( 'Import Documentation', 'disciple_tools' ); ?></h2>
            </div>
            <div class="inside">
                <p><?php esc_html_e( 'Complete guide to CSV import field types and formatting.', 'disciple_tools' ); ?></p>

                <div class="dt-import-doc-actions">
                    <button type="button" class="button button-primary dt-import-view-docs" id="dt-import-view-docs">
                        <span class="dashicons dashicons-book-alt"></span>
                        <?php esc_html_e( 'View Full Documentation', 'disciple_tools' ); ?>
                    </button>
                </div>

                <div style="margin-top: 15px; padding: 8px; background: #fff3cd; border-left: 4px solid #ffc107;">
                    <p style="margin: 0; font-size: 12px;">
                        <strong><?php esc_html_e( 'Tip:', 'disciple_tools' ); ?></strong>
                        <?php esc_html_e( 'Start with a small test file to verify your formatting before importing large datasets.', 'disciple_tools' ); ?>
                    </p>
                </div>
            </div>
        </div>
        <?php
    }

    private function get_translations() {
        return [
            'selectPostType' => __( 'Select Record Type', 'disciple_tools' ),
            'uploadCsv' => __( 'Upload CSV', 'disciple_tools' ),
            'mapFields' => __( 'Map Fields', 'disciple_tools' ),
            'previewImport' => __( 'Preview & Import', 'disciple_tools' ),
            'next' => __( 'Next', 'disciple_tools' ),
            'back' => __( 'Back', 'disciple_tools' ),
            'upload' => __( 'Upload', 'disciple_tools' ),
            'csv_import' => __( 'CSV Import', 'disciple_tools' ),
            'cancel' => __( 'Cancel', 'disciple_tools' ),
            'skipColumn' => __( 'Skip this column', 'disciple_tools' ),
            'createField' => __( 'Create New Field', 'disciple_tools' ),
            'chooseFile' => __( 'Choose a file...', 'disciple_tools' ),
            'dragDropFile' => __( 'or drag and drop it here', 'disciple_tools' ),
            'fileUploaded' => __( 'File uploaded successfully!', 'disciple_tools' ),
            'uploadError' => __( 'Error uploading file', 'disciple_tools' ),
            'processingFile' => __( 'Processing file...', 'disciple_tools' ),
            'invalidFileType' => __( 'Invalid file type. Please upload a CSV file.', 'disciple_tools' ),
            'fileTooLarge' => __( 'File is too large. Maximum size is', 'disciple_tools' ),
            'noFileSelected' => __( 'Please select a file to upload.', 'disciple_tools' ),
            'mappingComplete' => __( 'Field mapping completed successfully!', 'disciple_tools' ),
            'importProgress' => __( 'Importing records...', 'disciple_tools' ),
            'importComplete' => __( 'Import completed successfully!', 'disciple_tools' ),
            'importFailed' => __( 'Import failed. Please check the error log.', 'disciple_tools' ),
            'recordsImported' => __( 'records imported', 'disciple_tools' ),
            'errorsFound' => __( 'errors found', 'disciple_tools' ),
            // CSV delimiter options
            'comma' => __( 'Comma (,)', 'disciple_tools' ),
            'semicolon' => __( 'Semicolon (;)', 'disciple_tools' ),
            'tab' => __( 'Tab', 'disciple_tools' ),
            'pipe' => __( 'Pipe (|)', 'disciple_tools' ),

            // Value mapping translations
            'mapValues' => __( 'Map Values', 'disciple_tools' ),
            'csvValue' => __( 'CSV Value', 'disciple_tools' ),
            'dtFieldValue' => __( 'DT Field Value', 'disciple_tools' ),
            'skipValue' => __( '-- Skip this value --', 'disciple_tools' ),
            'autoMapSimilar' => __( 'Auto-map Similar Values', 'disciple_tools' ),
            'clearAllMappings' => __( 'Clear All Mappings', 'disciple_tools' ),
            'saveMappings' => __( 'Save Mapping', 'disciple_tools' ),

            // Field creation translations
            'createNewField' => __( 'Create New Field', 'disciple_tools' ),
            'fieldName' => __( 'Field Name', 'disciple_tools' ),
            'fieldType' => __( 'Field Type', 'disciple_tools' ),
            'fieldDescription' => __( 'Description', 'disciple_tools' ),
            'creating' => __( 'Creating...', 'disciple_tools' ),
            'fieldCreatedSuccess' => __( 'Field created successfully!', 'disciple_tools' ),
            'fieldCreationError' => __( 'Error creating field', 'disciple_tools' ),
            'ajaxError' => __( 'An error occurred. Please try again.', 'disciple_tools' ),
            'fillRequiredFields' => __( 'Please fill in all required fields.', 'disciple_tools' ),

            // Warning translations
            'warnings' => __( 'Warnings', 'disciple_tools' ),
            'importWarnings' => __( 'Import Warnings', 'disciple_tools' ),
            'newRecordsWillBeCreated' => __( 'Some records will create new connection records. Review the preview below for details.', 'disciple_tools' ),
            'newRecordIndicator' => __( '(NEW)', 'disciple_tools' ),

            // Geocoding translations
            'geocodingService' => __( 'Geocoding', 'disciple_tools' ),
            'enableGeocoding' => __( 'Enable geocoding for addresses', 'disciple_tools' ),
            'geocodingNote' => __( 'When enabled, addresses will be automatically converted to coordinates and location grid data', 'disciple_tools' ),
            'geocodingOptional' => __( 'Geocoding is optional - you can import addresses without converting them', 'disciple_tools' ),
            'locationInfo' => __( 'location fields accept grid IDs, coordinates (lat,lng), or addresses', 'disciple_tools' ),
            'locationMetaInfo' => __( 'location_meta fields accept grid IDs, coordinates (lat,lng), or addresses', 'disciple_tools' ),
            'noGeocodingService' => __( 'No geocoding service is available. Please configure Google Maps or Mapbox API keys.', 'disciple_tools' ),

            // Duplicate checking translations
            'duplicateChecking' => __( 'Duplicate Checking', 'disciple_tools' ),
            'enableDuplicateChecking' => __( 'Check for duplicates and merge with existing records', 'disciple_tools' ),
            'duplicateCheckingNote' => __( 'When enabled, the import will look for existing records with the same value in this field and update them instead of creating duplicates.', 'disciple_tools' )
        ];
    }

    private function get_field_types() {
        return [
            'text' => __( 'Text', 'disciple_tools' ),
            'textarea' => __( 'Text Area', 'disciple_tools' ),
            'number' => __( 'Number', 'disciple_tools' ),
            'date' => __( 'Date', 'disciple_tools' ),
            'boolean' => __( 'Boolean', 'disciple_tools' ),
            'key_select' => __( 'Dropdown', 'disciple_tools' ),
            'multi_select' => __( 'Multi Select', 'disciple_tools' ),
            'tags' => __( 'Tags', 'disciple_tools' ),
            'communication_channel' => __( 'Communication Channel', 'disciple_tools' ),
            'connection' => __( 'Connection', 'disciple_tools' ),
            'user_select' => __( 'User Select', 'disciple_tools' ),
            'location' => __( 'Location', 'disciple_tools' ),
            'location_meta' => __( 'Location Meta', 'disciple_tools' )
        ];
    }

    private function get_geocoding_services() {
        // Since frontend is just a checkbox, we only need to know if geocoding is available
        $is_available = DT_CSV_Import_Geocoding::is_geocoding_available();

        return [
            'available' => $is_available
        ];
    }

    private function get_max_file_size() {
        return wp_max_upload_size();
    }

    private function display_import_interface() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <!-- Main Column -->
                        <div class="dt-import-container">
                            <h1><?php esc_html_e( 'Import Data', 'disciple_tools' ); ?></h1>

                            <!-- Progress Indicator -->
                            <div class="dt-import-progress">
                                <ul class="dt-import-steps">
                                    <li class="step active" data-step="1">
                                        <span class="step-number">1</span>
                                        <span class="step-name"><?php esc_html_e( 'Select Record Type', 'disciple_tools' ) ?></span>
                                    </li>
                                    <li class="step" data-step="2">
                                        <span class="step-number">2</span>
                                        <span class="step-name"><?php esc_html_e( 'Upload CSV', 'disciple_tools' ) ?></span>
                                    </li>
                                    <li class="step" data-step="3">
                                        <span class="step-number">3</span>
                                        <span class="step-name"><?php esc_html_e( 'Map Fields', 'disciple_tools' ) ?></span>
                                    </li>
                                    <li class="step" data-step="4">
                                        <span class="step-number">4</span>
                                        <span class="step-name"><?php esc_html_e( 'Preview & Import', 'disciple_tools' ) ?></span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Step Content Container -->
                            <div class="dt-import-step-content">
                                <!-- Initial step content will be loaded here -->
                                <div class="dt-import-initial-content">
                                    <h2><?php esc_html_e( 'Step 1: Select Record Type', 'disciple_tools' ) ?></h2>
                                    <p><?php esc_html_e( 'Choose the type of records you want to import from your CSV file.', 'disciple_tools' ) ?></p>

                                    <div class="post-type-grid">
                                        <!-- Post type cards will be dynamically populated -->
                                    </div>
                                </div>
                            </div>

                            <!-- Navigation -->
                            <div class="dt-import-navigation">
                                <button type="button" class="button dt-import-back" style="display: none;">
                                    <?php esc_html_e( '← Back', 'disciple_tools' ) ?>
                                </button>
                                <button type="button" class="button button-primary dt-import-next" disabled>
                                    <?php esc_html_e( 'Next →', 'disciple_tools' ) ?>
                                </button>
                            </div>

                            <!-- Error container -->
                            <div class="dt-import-errors" style="display: none;"></div>
                        </div>
                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->
                    <div id="postbox-container-1" class="postbox-container">
                        <!-- Right Column -->
                        <?php $this->display_documentation_sidebar(); ?>
                        <?php $this->display_csv_examples_sidebar(); ?>
                        <!-- End Right Column -->
                    </div><!-- postbox-container 1 -->
                    <div id="postbox-container-2" class="postbox-container">
                    </div><!-- postbox-container 2 -->
                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->

        <!-- Include Documentation Modal -->
        <?php include( plugin_dir_path( __FILE__ ) . 'documentation-modal.php' ); ?>
        <?php
    }
}
