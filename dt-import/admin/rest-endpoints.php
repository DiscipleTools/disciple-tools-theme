<?php
/**
 * DT CSV Import REST API Endpoints
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class DT_CSV_Import_Ajax
 */
class DT_CSV_Import_Ajax {

    /**
     * @var object Instance variable
     */
    private static $_instance = null;

    /**
     * Instance. Ensures only one instance is loaded or can be loaded.
     *
     * @return DT_CSV_Import_Ajax instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * The REST API variables
     */
    private $version = 2;
    private $context = 'dt-csv-import';
    private $namespace;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->namespace = $this->context . '/v' . intval( $this->version );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    /**
     * Add the API routes
     */
    public function add_api_routes() {
        $arg_schemas = [
            'post_type' => [
                'description' => 'The post type to import',
                'type' => 'string',
                'required' => true,
                'validate_callback' => [ $this, 'validate_args' ]
            ],
            'session_id' => [
                'description' => 'The import session ID',
                'type' => 'integer',
                'required' => true,
                'validate_callback' => [ $this, 'validate_args' ]
            ]
        ];

        // Get field settings for post type
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/field-settings', [
                [
                    'methods' => 'GET',
                    'callback' => [ $this, 'get_field_settings' ],
                    'args' => [
                        'post_type' => $arg_schemas['post_type'],
                    ],
                    'permission_callback' => [ $this, 'check_import_permissions' ],
                ]
            ]
        );

        // Upload CSV file
        register_rest_route(
            $this->namespace, '/upload', [
                [
                    'methods' => 'POST',
                    'callback' => [ $this, 'upload_csv' ],
                    'permission_callback' => [ $this, 'check_import_permissions' ],
                ]
            ]
        );

        // Analyze CSV and suggest field mappings
        register_rest_route(
            $this->namespace, '/(?P<session_id>\d+)/analyze', [
                [
                    'methods' => 'POST',
                    'callback' => [ $this, 'analyze_csv' ],
                    'args' => [
                        'session_id' => $arg_schemas['session_id'],
                    ],
                    'permission_callback' => [ $this, 'check_import_permissions' ],
                ]
            ]
        );

        // Save field mappings
        register_rest_route(
            $this->namespace, '/(?P<session_id>\d+)/mapping', [
                [
                    'methods' => 'POST',
                    'callback' => [ $this, 'save_mapping' ],
                    'args' => [
                        'session_id' => $arg_schemas['session_id'],
                    ],
                    'permission_callback' => [ $this, 'check_import_permissions' ],
                ]
            ]
        );

        // Preview import data
        register_rest_route(
            $this->namespace, '/(?P<session_id>\d+)/preview', [
                [
                    'methods' => 'GET',
                    'callback' => [ $this, 'preview_import' ],
                    'args' => [
                        'session_id' => $arg_schemas['session_id'],
                    ],
                    'permission_callback' => [ $this, 'check_import_permissions' ],
                ]
            ]
        );

        // Execute import
        register_rest_route(
            $this->namespace, '/(?P<session_id>\d+)/execute', [
                [
                    'methods' => 'POST',
                    'callback' => [ $this, 'execute_import' ],
                    'args' => [
                        'session_id' => $arg_schemas['session_id'],
                    ],
                    'permission_callback' => [ $this, 'check_import_permissions' ],
                ]
            ]
        );

        // Get import status/progress
        register_rest_route(
            $this->namespace, '/(?P<session_id>\d+)/status', [
                [
                    'methods' => 'GET',
                    'callback' => [ $this, 'get_import_status' ],
                    'args' => [
                        'session_id' => $arg_schemas['session_id'],
                    ],
                    'permission_callback' => [ $this, 'check_import_permissions' ],
                ]
            ]
        );

        // Delete import session
        register_rest_route(
            $this->namespace, '/(?P<session_id>\d+)', [
                [
                    'methods' => 'DELETE',
                    'callback' => [ $this, 'delete_session' ],
                    'args' => [
                        'session_id' => $arg_schemas['session_id'],
                    ],
                    'permission_callback' => [ $this, 'check_import_permissions' ],
                ]
            ]
        );



        // Get field options for key_select and multi_select fields
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/field-options', [
                [
                    'methods' => 'GET',
                    'callback' => [ $this, 'get_field_options' ],
                    'args' => [
                        'post_type' => $arg_schemas['post_type'],
                        'field_key' => [
                            'description' => 'The field key to get options for',
                            'type' => 'string',
                            'required' => true,
                            'validate_callback' => [ $this, 'validate_args' ]
                        ]
                    ],
                    'permission_callback' => [ $this, 'check_import_permissions' ],
                ]
            ]
        );

        // Get CSV column data for value mapping
        register_rest_route(
            $this->namespace, '/(?P<session_id>\d+)/column-data', [
                [
                    'methods' => 'GET',
                    'callback' => [ $this, 'get_column_data' ],
                    'args' => [
                        'session_id' => $arg_schemas['session_id'],
                        'column_index' => [
                            'description' => 'The column index to get data for',
                            'type' => 'integer',
                            'required' => true,
                            'validate_callback' => [ $this, 'validate_args' ]
                        ]
                    ],
                    'permission_callback' => [ $this, 'check_import_permissions' ],
                ]
            ]
        );
    }

    /**
     * Validate arguments
     */
    public function validate_args( $value, $request, $param ) {
        $attributes = $request->get_attributes();

        if ( isset( $attributes['args'][$param] ) ) {
            $argument = $attributes['args'][$param];

            // Check to make sure our argument is a string.
            if ( 'string' === $argument['type'] && !is_string( $value ) ) {
                return new WP_Error( 'rest_invalid_param', sprintf( '%1$s is not of type %2$s', $param, 'string' ), [ 'status' => 400 ] );
            }
            if ( 'integer' === $argument['type'] && !is_numeric( $value ) ) {
                return new WP_Error( 'rest_invalid_param', sprintf( '%1$s is not of type %2$s', $param, 'integer' ), [ 'status' => 400 ] );
            }
            if ( $param === 'post_type' ) {
                $post_types = DT_Posts::get_post_types();
                if ( !in_array( $value, $post_types ) ) {
                    return new WP_Error( 'rest_invalid_param', sprintf( '%1$s is not a valid post type', $value ), [ 'status' => 400 ] );
                }
            }
        } else {
            return new WP_Error( 'rest_invalid_param', sprintf( '%s was not registered as a request argument.', $param ), [ 'status' => 400 ] );
        }

        return true;
    }

    /**
     * Check import permissions
     */
    public function check_import_permissions() {
        return current_user_can( 'manage_dt' );
    }



    /**
     * Get field settings for a post type
     */
    public function get_field_settings( WP_REST_Request $request ) {
        $url_params = $request->get_url_params();
        $post_type = $url_params['post_type'];

        if ( !DT_Posts::can_access( $post_type ) ) {
            return new WP_Error( 'no_permission', 'No permission to access this post type', [ 'status' => 403 ] );
        }

        $field_settings = DT_Posts::get_post_field_settings( $post_type );

        return [
            'success' => true,
            'data' => $field_settings
        ];
    }

    /**
     * Upload CSV file
     */
    public function upload_csv( WP_REST_Request $request ) {
        // Check nonce for security
        if ( ! wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
            return new WP_Error( 'invalid_nonce', 'Invalid security token', [ 'status' => 403 ] );
        }

        $post_type = $request->get_param( 'post_type' );

        if ( empty( $post_type ) ) {
            return new WP_Error( 'missing_post_type', 'Post type is required', [ 'status' => 400 ] );
        }

        if ( !isset( $_FILES['csv_file'] ) ) {
            return new WP_Error( 'no_file', 'No file uploaded', [ 'status' => 400 ] );
        }

        // Sanitize file data carefully - preserve tmp_name for file operations
        $file = $_FILES['csv_file']; //phpcs:ignore
        $sanitized_file = [
            'name' => sanitize_file_name( $file['name'] ),
            'type' => sanitize_text_field( $file['type'] ),
            'tmp_name' => $file['tmp_name'], // Don't sanitize - needed for file operations
            'error' => intval( $file['error'] ),
            'size' => intval( $file['size'] )
        ];

        // Validate file
        $validation_errors = DT_CSV_Import_Utilities::validate_file_upload( $sanitized_file );
        if ( !empty( $validation_errors ) ) {
            return new WP_Error( 'file_validation_failed', implode( ', ', $validation_errors ), [ 'status' => 400 ] );
        }

        // Save file to temporary location
        $file_path = DT_CSV_Import_Utilities::save_uploaded_file( $sanitized_file );
        if ( !$file_path ) {
            return new WP_Error( 'file_save_failed', 'Failed to save uploaded file', [ 'status' => 500 ] );
        }

        // Parse CSV data
        $csv_data = DT_CSV_Import_Utilities::parse_csv_file( $file_path );
        if ( is_wp_error( $csv_data ) ) {
            return $csv_data;
        }

        // Create import session
        $session_id = $this->create_import_session( $post_type, $file_path, $csv_data );
        if ( is_wp_error( $session_id ) ) {
            return $session_id;
        }

        return [
            'success' => true,
            'data' => [
                'session_id' => $session_id,
                'file_name' => $sanitized_file['name'],
                'row_count' => count( $csv_data ) - 1, // Subtract header row
                'column_count' => count( $csv_data[0] ?? [] ),
                'headers' => $csv_data[0] ?? []
            ]
        ];
    }

    /**
     * Analyze CSV and suggest field mappings
     */
    public function analyze_csv( WP_REST_Request $request ) {
        $url_params = $request->get_url_params();
        $session_id = intval( $url_params['session_id'] );

        $session = $this->get_import_session( $session_id );
        if ( is_wp_error( $session ) ) {
            return $session;
        }

        $csv_data = $session['csv_data'];
        $post_type = $session['post_type'];

        // Generate field mapping suggestions
        $mapping_suggestions = DT_CSV_Import_Mapping::analyze_csv_columns( $csv_data, $post_type );

        // Update session with mapping suggestions
        $this->update_import_session($session_id, [
            'mapping_suggestions' => $mapping_suggestions,
            'status' => 'analyzed'
        ]);

        // Include saved field mappings and do_not_import_columns if they exist
        $response_data = [
            'mapping_suggestions' => $mapping_suggestions
        ];

        if ( !empty( $session['field_mappings'] ) ) {
            $response_data['saved_field_mappings'] = $session['field_mappings'];
        }

        if ( !empty( $session['do_not_import_columns'] ) ) {
            $response_data['saved_do_not_import_columns'] = $session['do_not_import_columns'];
        }

        return [
            'success' => true,
            'data' => $response_data
        ];
    }

    /**
     * Save field mappings
     */
    public function save_mapping( WP_REST_Request $request ) {
        $url_params = $request->get_url_params();
        $session_id = intval( $url_params['session_id'] );
        $body_params = $request->get_json_params() ?? $request->get_body_params();

        $mappings = $body_params['mappings'] ?? [];
        $do_not_import_columns = $body_params['do_not_import_columns'] ?? [];
        $import_options = $body_params['import_options'] ?? [];

        $session = $this->get_import_session( $session_id );
        if ( is_wp_error( $session ) ) {
            return $session;
        }

        // Validate mappings
        $validation_errors = DT_CSV_Import_Mapping::validate_mapping( $mappings, $session['post_type'] );
        if ( !empty( $validation_errors ) ) {
            return new WP_Error( 'mapping_validation_failed', implode( ', ', $validation_errors ), [ 'status' => 400 ] );
        }

        // Update session with mappings and import options
        $update_data = [
            'field_mappings' => $mappings,
            'do_not_import_columns' => $do_not_import_columns,
            'status' => 'mapped'
        ];

        if ( !empty( $import_options ) ) {
            $update_data['import_options'] = $import_options;
        }

        $this->update_import_session( $session_id, $update_data );

        return [
            'success' => true,
            'data' => [
                'message' => 'Field mappings saved successfully'
            ]
        ];
    }

    /**
     * Preview import data
     */
    public function preview_import( WP_REST_Request $request ) {
        $url_params = $request->get_url_params();
        $session_id = intval( $url_params['session_id'] );
        $get_params = $request->get_query_params();

        $limit = intval( $get_params['limit'] ?? 10 );
        $offset = intval( $get_params['offset'] ?? 0 );

        $session = $this->get_import_session( $session_id );
        if ( is_wp_error( $session ) ) {
            return $session;
        }

        if ( empty( $session['field_mappings'] ) ) {
            return new WP_Error( 'no_mappings', 'Field mappings not configured', [ 'status' => 400 ] );
        }

        // Generate preview data
        $preview_data = DT_CSV_Import_Processor::generate_preview(
            $session['csv_data'],
            $session['field_mappings'],
            $session['post_type'],
            $limit,
            $offset
        );

        return [
            'success' => true,
            'data' => $preview_data
        ];
    }

    /**
     * Execute import
     */
    public function execute_import( WP_REST_Request $request ) {
        $url_params = $request->get_url_params();
        $session_id = intval( $url_params['session_id'] );

        $session = $this->get_import_session( $session_id );
        if ( is_wp_error( $session ) ) {
            return $session;
        }

        if ( empty( $session['field_mappings'] ) ) {
            return new WP_Error( 'no_mappings', 'Field mappings not configured', [ 'status' => 400 ] );
        }

        // Update session status to processing and reset progress
        $this->update_import_session($session_id, [
            'status' => 'processing',
            'progress' => 0,
            'records_imported' => 0,
            'error_count' => 0,
            'errors' => [],
            'rows_processed' => 0
        ]);

        // Try to start import immediately
        $immediate_result = $this->try_immediate_import( $session_id );

        if ( $immediate_result['started'] ) {
            return [
                'success' => true,
                'data' => [
                    'message' => 'Import started immediately',
                    'session_id' => $session_id,
                    'immediate_start' => true
                ]
            ];
        } else {
            // Fallback to scheduled execution
            wp_schedule_single_event( time(), 'dt_csv_import_execute', [ $session_id ] );

            return [
                'success' => true,
                'data' => [
                    'message' => 'Import scheduled',
                    'session_id' => $session_id,
                    'immediate_start' => false,
                    'reason' => $immediate_result['reason']
                ]
            ];
        }
    }

    /**
     * Get import status and continue processing if needed
     */
    public function get_import_status( WP_REST_Request $request ) {
        $url_params = $request->get_url_params();
        $session_id = intval( $url_params['session_id'] );

        // Don't load CSV data for status checks - we only need metadata
        $session = $this->get_import_session( $session_id, false );
        if ( is_wp_error( $session ) ) {
            return $session;
        }

        // If import is stuck in processing state, try to continue it
        if ( $session['subtype'] === 'import_processing' ) {
            $payload = maybe_unserialize( $session['payload'] ) ?: [];
            $current_progress = $payload['progress'] ?? 0;
            $rows_processed = $payload['rows_processed'] ?? 0;
            $total_rows = $session['row_count'] ?? 0;

            // If we're using chunked processing and have more rows to process
            if ( $this->should_use_chunked_processing( $session_id ) && $rows_processed < $total_rows ) {
                // Continue processing next chunk
                $this->process_import_chunk( $session_id, $rows_processed, 25 );

                // Re-fetch session after processing
                $session = $this->get_import_session( $session_id );
                if ( is_wp_error( $session ) ) {
                    return $session;
                }
            } else {
                // Try standard continuation logic
                $this->try_continue_import( $session_id );

                // Re-fetch session after potential processing
                $session = $this->get_import_session( $session_id );
                if ( is_wp_error( $session ) ) {
                    return $session;
                }
            }
        }

        // Map subtype back to status for backward compatibility
        $status_map = [
            'csv_upload' => 'uploaded',
            'field_analysis' => 'analyzed',
            'field_mapping' => 'mapped',
            'import_processing' => 'processing',
            'import_completed' => 'completed',
            'import_completed_with_errors' => 'completed_with_errors',
            'import_failed' => 'failed'
        ];
        $status = $status_map[$session['subtype']] ?? $session['status'] ?? 'pending';

        $payload = maybe_unserialize( $session['payload'] ) ?: [];
        $progress = $payload['progress'] ?? 0;
        $records_imported = $payload['records_imported'] ?? 0;
        $error_count = $payload['error_count'] ?? 0;
        $errors = $payload['errors'] ?? [];
        $imported_records = $payload['imported_records'] ?? [];

        return [
            'success' => true,
            'data' => [
                'status' => $status,
                'progress' => $progress,
                'records_imported' => $records_imported,
                'error_count' => $error_count,
                'errors' => $errors,
                'imported_records' => $imported_records
            ]
        ];
    }

    /**
     * Delete import session
     */
    public function delete_session( WP_REST_Request $request ) {
        $url_params = $request->get_url_params();
        $session_id = intval( $url_params['session_id'] );

        $result = $this->delete_import_session( $session_id );
        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return [
            'success' => true,
            'data' => [
                'message' => 'Import session deleted'
            ]
        ];
    }

    /**
     * Get field options for key_select and multi_select fields
     */
    public function get_field_options( WP_REST_Request $request ) {
        $url_params = $request->get_url_params();
        $post_type = $url_params['post_type'];
        $query_params = $request->get_query_params();
        $field_key = sanitize_text_field( $query_params['field_key'] ?? '' );

        if ( !$field_key ) {
            return new WP_Error( 'missing_field_key', 'Field key is required', [ 'status' => 400 ] );
        }

        $field_settings = DT_Posts::get_post_field_settings( $post_type );

        if ( !isset( $field_settings[$field_key] ) ) {
            return new WP_Error( 'field_not_found', 'Field not found', [ 'status' => 404 ] );
        }

        $field_config = $field_settings[$field_key];

        if ( !in_array( $field_config['type'], [ 'key_select', 'multi_select' ] ) ) {
            return new WP_Error( 'invalid_field_type', 'Field is not a select type', [ 'status' => 400 ] );
        }

        $options = $field_config['default'] ?? [];

        // Convert to label format if available
        $formatted_options = [];
        foreach ( $options as $key => $value ) {
            $formatted_options[$key] = isset( $value['label'] ) ? $value['label'] : $value;
        }

        return [
            'success' => true,
            'data' => $formatted_options
        ];
    }

    /**
     * Get CSV column data for value mapping
     */
    public function get_column_data( WP_REST_Request $request ) {
        $url_params = $request->get_url_params();
        $session_id = intval( $url_params['session_id'] );
        $query_params = $request->get_query_params();
        $column_index = intval( $query_params['column_index'] ?? -1 );

        if ( $column_index < 0 ) {
            return new WP_Error( 'invalid_column_index', 'Valid column index is required', [ 'status' => 400 ] );
        }

        // First get session without CSV data to check if it exists
        $session = $this->get_import_session( $session_id, false );
        if ( is_wp_error( $session ) ) {
            return $session;
        }

        // Now load CSV data from file
        if ( empty( $session['file_path'] ) || !file_exists( $session['file_path'] ) ) {
            return new WP_Error( 'no_csv_file', 'CSV file not found', [ 'status' => 404 ] );
        }

        $csv_data = DT_CSV_Import_Utilities::parse_csv_file( $session['file_path'] );
        if ( is_wp_error( $csv_data ) ) {
            return new WP_Error( 'csv_parse_error', 'Failed to parse CSV file', [ 'status' => 500 ] );
        }

        // Skip the header row for unique value extraction
        $data_rows = array_slice( $csv_data, 1 );

        // Get unique values from the column (excluding header)
        $unique_values = DT_CSV_Import_Mapping::get_unique_column_values( $data_rows, $column_index );

        // Also get sample data for preview (also excluding header)
        $sample_data = DT_CSV_Import_Utilities::get_sample_data( $data_rows, $column_index, 10 );

        return [
            'success' => true,
            'data' => [
                'unique_values' => $unique_values,
                'sample_data' => $sample_data,
                'total_unique' => count( $unique_values )
            ]
        ];
    }

    /**
     * Create new field
     */


    /**
     * Create import session
     */
    private function create_import_session( $post_type, $file_path, $csv_data ) {
        $user_id = get_current_user_id();

        // Store only metadata, not the entire CSV data
        $session_data = [
            'headers' => $csv_data[0] ?? [],
            'row_count' => count( $csv_data ) - 1,
            'file_path' => $file_path,
            'status' => 'uploaded'
        ];

        $report_id = dt_report_insert([
            'user_id' => $user_id,
            'post_type' => $post_type,
            'type' => 'import_session',
            'subtype' => 'csv_upload',
            'payload' => $session_data,
            'value' => count( $csv_data ) - 1, // row count
            'label' => basename( $file_path ),
            'timestamp' => time()
        ]);

        if ( !$report_id ) {
            return new WP_Error( 'session_creation_failed', 'Failed to create import session', [ 'status' => 500 ] );
        }

        return $report_id;
    }

    /**
     * Get import session
     */
    private function get_import_session( $session_id, $load_csv_data = true ) {
        global $wpdb;

        $user_id = get_current_user_id();

        $session = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $wpdb->dt_reports WHERE id = %d AND user_id = %d AND type = 'import_session'",
                $session_id,
                $user_id
            ),
            ARRAY_A
        );

        if ( !$session ) {
            return new WP_Error( 'session_not_found', 'Import session not found', [ 'status' => 404 ] );
        }

        // Decode payload data and merge with session
        $payload = maybe_unserialize( $session['payload'] ) ?: [];
        $session = array_merge( $session, $payload );

        // Load CSV data from file if requested and file exists
        if ( $load_csv_data && !empty( $session['file_path'] ) && file_exists( $session['file_path'] ) ) {
            $csv_data = DT_CSV_Import_Utilities::parse_csv_file( $session['file_path'] );
            if ( !is_wp_error( $csv_data ) ) {
                $session['csv_data'] = $csv_data;
            }
        }

        return $session;
    }

    /**
     * Update import session
     */
    private function update_import_session( $session_id, $data ) {
        global $wpdb;

        $user_id = get_current_user_id();

        // Get current session data
        $current_session = $this->get_import_session( $session_id );
        if ( is_wp_error( $current_session ) ) {
            return $current_session;
        }

        // Get current payload
        $current_payload = maybe_unserialize( $current_session['payload'] ) ?: [];

        // Merge with new data
        $updated_payload = array_merge( $current_payload, $data );

        // Remove status from payload if it exists there
        $status = $data['status'] ?? $current_payload['status'] ?? 'pending';
        unset( $updated_payload['status'] );

        // Determine subtype based on status
        $subtype_map = [
            'uploaded' => 'csv_upload',
            'analyzed' => 'field_analysis',
            'mapped' => 'field_mapping',
            'processing' => 'import_processing',
            'completed' => 'import_completed',
            'completed_with_errors' => 'import_completed_with_errors',
            'failed' => 'import_failed'
        ];
        $subtype = $subtype_map[$status] ?? 'csv_upload';

        $result = $wpdb->update(
            $wpdb->dt_reports,
            [
                'payload' => maybe_serialize( $updated_payload ),
                'subtype' => $subtype,
                'value' => $updated_payload['records_imported'] ?? $current_session['value'] ?? 0,
                'timestamp' => time()
            ],
            [
                'id' => $session_id,
                'user_id' => $user_id,
                'type' => 'import_session'
            ],
            [ '%s', '%s', '%d', '%d' ],
            [ '%d', '%d', '%s' ]
        );

        if ( $result === false ) {
            return new WP_Error( 'session_update_failed', 'Failed to update import session', [ 'status' => 500 ] );
        }

        return true;
    }

    /**
     * Delete import session
     */
    private function delete_import_session( $session_id ) {
        global $wpdb;

        $user_id = get_current_user_id();

        // Get session to clean up file - don't load CSV data, just need file path
        $session = $this->get_import_session( $session_id, false );
        if ( !is_wp_error( $session ) && !empty( $session['file_path'] ) ) {
            if ( file_exists( $session['file_path'] ) ) {
                unlink( $session['file_path'] );
            }
        }

        $result = $wpdb->delete(
            $wpdb->dt_reports,
            [
                'id' => $session_id,
                'user_id' => $user_id,
                'type' => 'import_session'
            ],
            [ '%d', '%d', '%s' ]
        );

        if ( $result === false ) {
            return new WP_Error( 'session_deletion_failed', 'Failed to delete import session', [ 'status' => 500 ] );
        }

        return true;
    }

    /**
     * Try to start import immediately
     */
    private function try_immediate_import( $session_id ) {
        // Check if we can safely start the import now
        $can_start = $this->can_start_import_now( $session_id );

        if ( !$can_start['allowed'] ) {
            return [
                'started' => false,
                'reason' => $can_start['reason']
            ];
        }

        $should_chunk = $this->should_use_chunked_processing( $session_id );

        // Start the import in a way that won't timeout the request
        if ( $should_chunk ) {
            // For large imports, start first chunk only
            $result = $this->process_import_chunk( $session_id, 0, 50 );

            return [
                'started' => $result !== false,
                'reason' => $result === false ? 'chunk_processing_failed' : 'chunked_processing_started'
            ];
        } else {
            // For small imports, process completely
            $result = DT_CSV_Import_Processor::execute_import( $session_id );

            return [
                'started' => !is_wp_error( $result ),
                'reason' => is_wp_error( $result ) ? $result->get_error_message() : 'completed_immediately'
            ];
        }
    }

    /**
     * Check if we can start import immediately
     */
    private function can_start_import_now( $session_id ) {
        // Check memory limits
        $memory_limit = wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) );
        $memory_usage = memory_get_usage( true );
        $available_memory = $memory_limit - $memory_usage;

        if ( $available_memory < ( 50 * 1024 * 1024 ) ) { // Less than 50MB available
            return [
                'allowed' => false,
                'reason' => 'insufficient_memory'
            ];
        }

        // Check if import is already running
        $session = $this->get_import_session( $session_id );
        if ( is_wp_error( $session ) ) {
            return [
                'allowed' => false,
                'reason' => 'session_not_found'
            ];
        }

        // Only consider it "already processing" if it was updated very recently (within 10 seconds)
        // and has made actual progress
        if ( $session['subtype'] === 'import_processing' ) {
            $last_update = $session['timestamp'] ?? 0;
            $time_since_update = time() - $last_update;
            $payload = maybe_unserialize( $session['payload'] ) ?: [];
            $current_progress = $payload['progress'] ?? 0;
            $records_imported = $payload['records_imported'] ?? 0;

            // Only block if recently updated AND has actual progress/imports
            if ( $time_since_update < 10 && ( $current_progress > 0 || $records_imported > 0 ) ) {
                return [
                    'allowed' => false,
                    'reason' => 'already_processing'
                ];
            }
        }

        return [
            'allowed' => true,
            'reason' => 'ready_to_start'
        ];
    }

    /**
     * Determine if we should use chunked processing
     */
    private function should_use_chunked_processing( $session_id ) {
        $session = $this->get_import_session( $session_id );
        if ( is_wp_error( $session ) ) {
            return false;
        }

        $row_count = $session['row_count'] ?? 0;

        // Use chunked processing for more than 100 records
        return $row_count > 100;
    }

    /**
     * Process a chunk of import records
     */
    private function process_import_chunk( $session_id, $start_row, $chunk_size ) {
        $session = $this->get_import_session( $session_id );
        if ( is_wp_error( $session ) ) {
            return false;
        }

        $payload = maybe_unserialize( $session['payload'] ) ?: [];
        $csv_data = $payload['csv_data'] ?? [];
        $field_mappings = $payload['field_mappings'] ?? [];
        $post_type = $session['post_type'];

        if ( empty( $csv_data ) || empty( $field_mappings ) ) {
            return false;
        }

        // Remove headers from CSV data for processing
        $headers = array_shift( $csv_data );
        $total_rows = count( $csv_data );

        // Get current progress
        $imported_count = $payload['records_imported'] ?? 0;
        $error_count = $payload['error_count'] ?? 0;
        $errors = $payload['errors'] ?? [];
        $imported_records = $payload['imported_records'] ?? [];

        // Process chunk
        $end_row = min( $start_row + $chunk_size, $total_rows );

        for ( $i = $start_row; $i < $end_row; $i++ ) {
            if ( !isset( $csv_data[$i] ) ) {
                continue;
            }

            $row = $csv_data[$i];

            try {
                $post_data = [];

                foreach ( $field_mappings as $column_index => $mapping ) {
                    if ( empty( $mapping['field_key'] ) || $mapping['field_key'] === 'skip' ) {
                        continue;
                    }

                    $field_key = $mapping['field_key'];
                    $raw_value = $row[$column_index] ?? '';

                    if ( !empty( trim( $raw_value ) ) ) {
                        $processed_value = DT_CSV_Import_Processor::process_field_value( $raw_value, $field_key, $mapping, $post_type );
                        if ( $processed_value !== null ) {
                            // Format value according to field type for DT_Posts API
                            $post_data[$field_key] = DT_CSV_Import_Processor::format_value_for_api( $processed_value, $field_key, $post_type );
                        }
                    }
                }

                // Create the post
                $result = DT_Posts::create_post( $post_type, $post_data, true, false );

                if ( is_wp_error( $result ) ) {
                    $error_count++;
                    $errors[] = [
                        'row' => $i + 2, // +2 for header and 0-based index
                        'message' => $result->get_error_message()
                    ];
                } else {
                    $imported_count++;

                    // Store imported record data (limit to first 100 records for performance)
                    if ( count( $imported_records ) < 100 ) {
                        $record_name = $result['title'] ?? $result['name'] ?? "Record #{$result['ID']}";
                        $record_permalink = site_url() . '/' . $post_type . '/' . $result['ID'];

                        $imported_records[] = [
                            'id' => $result['ID'],
                            'name' => $record_name,
                            'permalink' => $record_permalink
                        ];
                    }
                }
            } catch ( Exception $e ) {
                $error_count++;
                $errors[] = [
                    'row' => $i + 2,
                    'message' => $e->getMessage()
                ];
            }
        }

        // Update progress
        $progress = round( ( $end_row / $total_rows ) * 100 );
        $is_complete = $end_row >= $total_rows;

        $updated_payload = array_merge($payload, [
            'progress' => $progress,
            'records_imported' => $imported_count,
            'error_count' => $error_count,
            'errors' => $errors,
            'rows_processed' => $end_row,
            'imported_records' => $imported_records
        ]);

        // Determine status
        if ( $is_complete ) {
            $subtype = $error_count > 0 ? 'import_completed_with_errors' : 'import_completed';
        } else {
            $subtype = 'import_processing';
        }

        // Update session
        global $wpdb;
        $wpdb->update(
            $wpdb->dt_reports,
            [
                'payload' => maybe_serialize( $updated_payload ),
                'subtype' => $subtype,
                'value' => $imported_count,
                'timestamp' => time()
            ],
            [ 'id' => $session_id ],
            [ '%s', '%s', '%d', '%d' ],
            [ '%d' ]
        );

        return $end_row;
    }

    /**
     * Try to continue a stalled import
     */
    private function try_continue_import( $session_id ) {
        // Check if import has been stalled too long or needs continuation
        $session = $this->get_import_session( $session_id );
        if ( is_wp_error( $session ) ) {
            return false;
        }

        $payload = maybe_unserialize( $session['payload'] ) ?: [];
        $last_update = $session['timestamp'] ?? 0;
        $current_progress = $payload['progress'] ?? 0;

        // If no progress update in last 30 seconds and not complete, try to continue
        if ( ( time() - $last_update ) > 30 && $current_progress < 100 ) {
            if ( $this->should_use_chunked_processing( $session_id ) ) {
                // Continue with chunked processing from where we left off
                $rows_processed = $payload['rows_processed'] ?? 0;
                $this->process_import_chunk( $session_id, $rows_processed, 25 );
            } else {
                // Restart the full import
                DT_CSV_Import_Processor::execute_import( $session_id );
            }
            return true;
        }

        return false;
    }
}

DT_CSV_Import_Ajax::instance();
