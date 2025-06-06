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

        // Enqueue our custom import scripts
        wp_enqueue_script(
            'dt-import-js',
            get_template_directory_uri() . '/dt-import/assets/js/dt-import.js',
            [ 'jquery' ],
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
            'geocodingService' => __( 'Geocoding Service', 'disciple_tools' ),
            'selectGeocodingService' => __( 'Select a geocoding service to convert addresses to coordinates', 'disciple_tools' ),
            'geocodingNote' => __( 'Note: Geocoding will be applied to location_meta fields that contain addresses or coordinates', 'disciple_tools' ),
            'geocodingOptional' => __( 'Geocoding is optional - you can import without it', 'disciple_tools' ),
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
        $available_services = DT_CSV_Import_Geocoding::get_available_geocoding_services();

        $services = [
            'none' => __( 'No Geocoding', 'disciple_tools' )
        ];

        if ( in_array( 'google', $available_services ) ) {
            $services['google'] = __( 'Google Maps', 'disciple_tools' );
        }

        if ( in_array( 'mapbox', $available_services ) ) {
            $services['mapbox'] = __( 'Mapbox', 'disciple_tools' );
        }

        return $services;
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
                                    <h2><?php esc_html_e( 'Step 1: Select Post Type', 'disciple_tools' ) ?></h2>
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
        <?php include( plugin_dir_path( __FILE__ ) . '../templates/documentation-modal.php' ); ?>
        <?php
    }

    /**
     * Display field mapping step
     */
    public function display_field_mapping_step( $session_data ) {
        $csv_data = $session_data['csv_data'];
        $post_type = $session_data['post_type'];

        // Analyze CSV columns with enhanced field detection
        $mapping_suggestions = DT_CSV_Import_Mapping::analyze_csv_columns( $csv_data, $post_type );
        $field_settings = DT_Posts::get_post_field_settings( $post_type );

        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Map CSV Columns to Fields', 'disciple_tools' ); ?></h1>
            
            <div class="notice notice-info">
                <p><?php esc_html_e( 'We have automatically suggested field mappings based on your column headers. Please review and adjust as needed.', 'disciple_tools' ); ?></p>
            </div>

            <form method="post" id="field-mapping-form">
                <?php wp_nonce_field( 'dt_csv_import_mapping', 'dt_csv_import_mapping_nonce' ); ?>
                <input type="hidden" name="action" value="process_mapping" />
                <input type="hidden" name="session_id" value="<?php echo esc_attr( $session_data['session_id'] ); ?>" />

                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 25%;"><?php esc_html_e( 'CSV Column', 'disciple_tools' ); ?></th>
                            <th style="width: 25%;"><?php esc_html_e( 'Map to Field', 'disciple_tools' ); ?></th>
                            <th style="width: 25%;"><?php esc_html_e( 'Sample Data', 'disciple_tools' ); ?></th>
                            <th style="width: 25%;"><?php esc_html_e( 'Configuration', 'disciple_tools' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $mapping_suggestions as $column_index => $suggestion ): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html( $suggestion['column_name'] ); ?></strong>
                                    <?php if ( $suggestion['has_match'] ): ?>
                                        <br><small class="description">
                                            <?php esc_html_e( 'Auto-detected', 'disciple_tools' ); ?>
                                        </small>
                                    <?php else : ?>
                                        <br><small class="description" style="color: #999;">
                                            <?php esc_html_e( 'No match found', 'disciple_tools' ); ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <select name="field_mappings[<?php echo esc_attr( $column_index ); ?>][field_key]" 
                                            class="field-select" 
                                            data-column-index="<?php echo esc_attr( $column_index ); ?>">
                                        <option value=""><?php esc_html_e( 'Skip this column', 'disciple_tools' ); ?></option>
                                        
                                        <?php if ( !$suggestion['has_match'] ): ?>
                                            <option value="" disabled selected style="color: #999;">
                                                <?php esc_html_e( 'No match found - select manually', 'disciple_tools' ); ?>
                                            </option>
                                        <?php endif; ?>

                                        <?php
                                        $field_groups = [
                                            'core' => __( 'Core Fields', 'disciple_tools' ),
                                            'communication' => __( 'Communication', 'disciple_tools' ),
                                            'other' => __( 'Other Fields', 'disciple_tools' )
                                        ];

                                        foreach ( $field_groups as $group_key => $group_label ):
                                            $group_fields = self::get_fields_by_group( $field_settings, $group_key );
                                            if ( empty( $group_fields ) ) { continue;
                                            }
                                            ?>
                                            <optgroup label="<?php echo esc_attr( $group_label ); ?>">
                                                <?php foreach ( $group_fields as $field_key => $field_config ): ?>
                                                    <option value="<?php echo esc_attr( $field_key ); ?>" 
                                                            <?php
                                                            // Auto-select if this is the suggested field
                                                            if ( $suggestion['suggested_field'] === $field_key ) {
                                                                echo 'selected';
                                                            }
                                                            ?>>
                                                        <?php echo esc_html( $field_config['name'] ); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <div class="sample-data">
                                        <?php if ( !empty( $suggestion['sample_data'] ) ): ?>
                                            <?php foreach ( array_slice( $suggestion['sample_data'], 0, 3 ) as $sample ): ?>
                                                <div class="sample-item"><?php echo esc_html( $sample ); ?></div>
                                            <?php endforeach; ?>
                                            <?php if ( count( $suggestion['sample_data'] ) > 3 ): ?>
                                                <div class="sample-item">
                                                    <em><?php
                                                    /* translators: %d: number of additional samples */
                                                    printf( esc_html__( '+%d more...', 'disciple_tools' ), count( $suggestion['sample_data'] ) - 3 );
                                                    ?></em>
                                                </div>
                                            <?php endif; ?>
                                        <?php else : ?>
                                            <em><?php esc_html_e( 'No sample data', 'disciple_tools' ); ?></em>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="field-configuration" id="config-<?php echo esc_attr( $column_index ); ?>">
                                        <!-- Field-specific configuration will be loaded here via JavaScript -->
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <p class="submit">
                    <input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e( 'Preview Import', 'disciple_tools' ); ?>" />
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=dt_import' ) ); ?>" class="button"><?php esc_html_e( 'Start Over', 'disciple_tools' ); ?></a>
                </p>
            </form>
        </div>

        <style>
            .sample-data {
                font-size: 12px;
            }
            .sample-item {
                padding: 2px 0;
                border-bottom: 1px solid #eee;
            }
            .sample-item:last-child {
                border-bottom: none;
            }
            .field-configuration {
                min-height: 40px;
            }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Handle field mapping changes
            $('.field-select').on('change', function() {
                var columnIndex = $(this).data('column-index');
                var selectedField = $(this).val();
                var configContainer = $('#config-' + columnIndex);
                
                // Clear previous configuration
                configContainer.html('');
                
                if (selectedField) {
                    // Load field-specific configuration
                    loadFieldConfiguration(columnIndex, selectedField, configContainer);
                }
            });
            
            // Load initial configurations for auto-detected fields
            $('.field-select option:selected').each(function() {
                if ($(this).val()) {
                    var select = $(this).parent();
                    var columnIndex = select.data('column-index');
                    var selectedField = $(this).val();
                    var configContainer = $('#config-' + columnIndex);
                    loadFieldConfiguration(columnIndex, selectedField, configContainer);
                }
            });
            
            function loadFieldConfiguration(columnIndex, fieldKey, container) {
                // This would load field-specific configuration via AJAX
                // For now, show a simple message
                container.html('<em><?php esc_html_e( 'Configuration will be loaded here', 'disciple_tools' ); ?></em>');
            }
        });
        </script>
        <?php
    }

    /**
     * Get fields grouped by category
     */
    private static function get_fields_by_group( $field_settings, $group ) {
        $grouped_fields = [];

        foreach ( $field_settings as $field_key => $field_config ) {
            $field_group = 'other'; // default group

            // Categorize fields
            if ( in_array( $field_key, [ 'name', 'title', 'overall_status', 'assigned_to' ] ) ) {
                $field_group = 'core';
            } elseif ( strpos( $field_key, 'contact_' ) === 0 || $field_config['type'] === 'communication_channel' ) {
                $field_group = 'communication';
            }

            if ( $field_group === $group ) {
                $grouped_fields[$field_key] = $field_config;
            }
        }

        return $grouped_fields;
    }
}
