<?php
/**
 * DT Import Processor
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class DT_Import_Processor {

    /**
     * Generate preview data
     */
    public static function generate_preview( $csv_data, $field_mappings, $post_type, $limit = 10, $offset = 0 ) {
        $headers = array_shift( $csv_data );
        $preview_data = [];
        $processed_count = 0;
        $skipped_count = 0;

        $data_rows = array_slice( $csv_data, $offset, $limit );

        foreach ( $data_rows as $row_index => $row ) {
            $processed_row = [];
            $has_errors = false;
            $row_errors = [];

            foreach ( $field_mappings as $column_index => $mapping ) {
                if ( empty( $mapping['field_key'] ) || $mapping['field_key'] === 'skip' ) {
                    continue;
                }

                $field_key = $mapping['field_key'];
                $raw_value = $row[$column_index] ?? '';

                try {
                    $processed_value = self::process_field_value( $raw_value, $field_key, $mapping, $post_type );
                    $formatted_value = self::format_value_for_api( $processed_value, $field_key, $post_type );
                    $processed_row[$field_key] = [
                        'raw' => $raw_value,
                        'processed' => $formatted_value,
                        'valid' => true
                    ];
                } catch ( Exception $e ) {
                    $processed_row[$field_key] = [
                        'raw' => $raw_value,
                        'processed' => null,
                        'valid' => false,
                        'error' => $e->getMessage()
                    ];
                    $has_errors = true;
                    $row_errors[] = $e->getMessage();
                }
            }

            $preview_data[] = [
                'row_number' => $offset + $row_index + 2, // +2 for header and 0-based index
                'data' => $processed_row,
                'has_errors' => $has_errors,
                'errors' => $row_errors
            ];

            if ( $has_errors ) {
                $skipped_count++;
            } else {
                $processed_count++;
            }
        }

        return [
            'rows' => $preview_data,
            'total_rows' => count( $csv_data ),
            'preview_count' => count( $preview_data ),
            'processable_count' => $processed_count,
            'error_count' => $skipped_count,
            'offset' => $offset,
            'limit' => $limit
        ];
    }

    /**
     * Process a single field value based on field type and mapping
     */
    public static function process_field_value( $raw_value, $field_key, $mapping, $post_type ) {
        $field_settings = DT_Posts::get_post_field_settings( $post_type );

        if ( !isset( $field_settings[$field_key] ) ) {
            throw new Exception( "Field {$field_key} not found" );
        }

        $field_config = $field_settings[$field_key];
        $field_type = $field_config['type'];

        // Handle empty values
        if ( empty( trim( $raw_value ) ) ) {
            return null;
        }

        switch ( $field_type ) {
            case 'text':
            case 'textarea':
                return sanitize_text_field( trim( $raw_value ) );

            case 'number':
                if ( !is_numeric( $raw_value ) ) {
                    throw new Exception( "Invalid number: {$raw_value}" );
                }
                return floatval( $raw_value );

            case 'date':
                $normalized_date = DT_Import_Utilities::normalize_date( $raw_value );
                if ( empty( $normalized_date ) ) {
                    throw new Exception( "Invalid date format: {$raw_value}" );
                }
                return $normalized_date;

            case 'boolean':
                $boolean_value = DT_Import_Utilities::normalize_boolean( $raw_value );
                if ( $boolean_value === null ) {
                    throw new Exception( "Invalid boolean value: {$raw_value}" );
                }
                return $boolean_value;

            case 'key_select':
                return self::process_key_select_value( $raw_value, $mapping, $field_config );

            case 'multi_select':
                return self::process_multi_select_value( $raw_value, $mapping, $field_config );

            case 'tags':
                return self::process_tags_value( $raw_value );

            case 'communication_channel':
                return self::process_communication_channel_value( $raw_value, $field_key );

            case 'connection':
                return self::process_connection_value( $raw_value, $field_config );

            case 'user_select':
                return self::process_user_select_value( $raw_value );

            case 'location':
                return self::process_location_value( $raw_value );

            default:
                return sanitize_text_field( trim( $raw_value ) );
        }
    }

    /**
     * Process key_select field value
     */
    private static function process_key_select_value( $raw_value, $mapping, $field_config ) {
        $value_mapping = $mapping['value_mapping'] ?? [];

        if ( isset( $value_mapping[$raw_value] ) ) {
            $mapped_value = $value_mapping[$raw_value];
            if ( isset( $field_config['default'][$mapped_value] ) ) {
                return $mapped_value;
            }
        }

        // Try direct match
        if ( isset( $field_config['default'][$raw_value] ) ) {
            return $raw_value;
        }

        throw new Exception( "Invalid option for key_select field: {$raw_value}" );
    }

    /**
     * Process multi_select field value
     */
    private static function process_multi_select_value( $raw_value, $mapping, $field_config ) {
        $values = DT_Import_Utilities::split_multi_value( $raw_value );
        $processed_values = [];
        $value_mapping = $mapping['value_mapping'] ?? [];

        foreach ( $values as $value ) {
            $value = trim( $value );

            if ( isset( $value_mapping[$value] ) ) {
                $mapped_value = $value_mapping[$value];
                if ( isset( $field_config['default'][$mapped_value] ) ) {
                    $processed_values[] = $mapped_value;
                }
            } elseif ( isset( $field_config['default'][$value] ) ) {
                $processed_values[] = $value;
            } else {
                throw new Exception( "Invalid option for multi_select field: {$value}" );
            }
        }

        return $processed_values;
    }

    /**
     * Process tags field value
     */
    private static function process_tags_value( $raw_value ) {
        $tags = DT_Import_Utilities::split_multi_value( $raw_value );
        return array_map(function( $tag ) {
            return sanitize_text_field( trim( $tag ) );
        }, $tags);
    }

    /**
     * Process communication channel value
     */
    private static function process_communication_channel_value( $raw_value, $field_key ) {
        $channels = DT_Import_Utilities::split_multi_value( $raw_value );
        $processed_channels = [];

        foreach ( $channels as $channel ) {
            $channel = trim( $channel );

            // Basic validation based on field type
            if ( strpos( $field_key, 'email' ) !== false ) {
                if ( !filter_var( $channel, FILTER_VALIDATE_EMAIL ) ) {
                    throw new Exception( "Invalid email address: {$channel}" );
                }
            } elseif ( strpos( $field_key, 'phone' ) !== false ) {
                // Basic phone validation - just check if it contains digits
                if ( !preg_match( '/\d/', $channel ) ) {
                    throw new Exception( "Invalid phone number: {$channel}" );
                }
            }

            $processed_channels[] = [
                'value' => $channel,
                'verified' => false
            ];
        }

        return $processed_channels;
    }

    /**
     * Process connection field value
     */
    private static function process_connection_value( $raw_value, $field_config ) {
        $connection_post_type = $field_config['post_type'] ?? '';
        if ( empty( $connection_post_type ) ) {
            throw new Exception( 'Connection field missing post_type configuration' );
        }

        $connections = DT_Import_Utilities::split_multi_value( $raw_value );
        $processed_connections = [];

        foreach ( $connections as $connection ) {
            $connection = trim( $connection );

            // Try to find by ID
            if ( is_numeric( $connection ) ) {
                $post = DT_Posts::get_post( $connection_post_type, intval( $connection ), true, false );
                if ( !is_wp_error( $post ) ) {
                    $processed_connections[] = intval( $connection );
                    continue;
                }
            }

            // Try to find by title
            $posts = DT_Posts::list_posts($connection_post_type, [
                'name' => $connection,
                'limit' => 1
            ]);

            if ( !is_wp_error( $posts ) && !empty( $posts['posts'] ) ) {
                $processed_connections[] = $posts['posts'][0]['ID'];
            } else {
                throw new Exception( "Connection not found: {$connection}" );
            }
        }

        return $processed_connections;
    }

    /**
     * Process user_select field value
     */
    private static function process_user_select_value( $raw_value ) {
        $user = null;

        // Try to find by ID
        if ( is_numeric( $raw_value ) ) {
            $user = get_user_by( 'id', intval( $raw_value ) );
        }

        // Try to find by email address
        if ( !$user && filter_var( $raw_value, FILTER_VALIDATE_EMAIL ) ) {
            $user = get_user_by( 'email', $raw_value );
        }

        // Try to find by username
        if ( !$user ) {
            $user = get_user_by( 'login', $raw_value );
        }

        // Try to find by display name
        if ( !$user ) {
            $user = get_user_by( 'display_name', $raw_value );
        }

        if ( !$user ) {
            throw new Exception( "User not found: {$raw_value}" );
        }

        return $user->ID;
    }

    /**
     * Process location field value
     */
    private static function process_location_value( $raw_value ) {
        $raw_value = trim( $raw_value );

        // Check if it's a grid ID
        if ( is_numeric( $raw_value ) ) {
            // Validate grid ID exists
            global $wpdb;
            $grid_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT grid_id FROM {$wpdb->prefix}dt_location_grid WHERE grid_id = %d",
                intval( $raw_value )
            ));

            if ( $grid_exists ) {
                return intval( $raw_value );
            }
        }

        // Check if it's lat,lng coordinates
        if ( preg_match( '/^-?\d+\.?\d*,-?\d+\.?\d*$/', $raw_value ) ) {
            list($lat, $lng) = explode( ',', $raw_value );
            return [
                'lat' => floatval( $lat ),
                'lng' => floatval( $lng )
            ];
        }

        // Treat as address - return as-is for geocoding later
        return [
            'address' => $raw_value
        ];
    }

    /**
     * Format processed value for DT_Posts API according to field type
     */
    public static function format_value_for_api( $processed_value, $field_key, $post_type ) {
        $field_settings = DT_Posts::get_post_field_settings( $post_type );

        if ( !isset( $field_settings[$field_key] ) ) {
            return $processed_value;
        }

        $field_config = $field_settings[$field_key];
        $field_type = $field_config['type'];

        switch ( $field_type ) {
            case 'multi_select':
            case 'tags':
                // Convert array to values format
                if ( is_array( $processed_value ) ) {
                    return [
                        'values' => array_map(function( $value ) {
                            return [ 'value' => $value ];
                        }, $processed_value)
                    ];
                }
                break;

            case 'connection':
                // Convert array of IDs to values format
                if ( is_array( $processed_value ) ) {
                    return [
                        'values' => array_map(function( $value ) {
                            return [ 'value' => $value ];
                        }, $processed_value)
                    ];
                }
                break;

            case 'communication_channel':
                // Already in correct format from processor
                if ( is_array( $processed_value ) ) {
                    return [
                        'values' => $processed_value
                    ];
                }
                break;

            case 'user_select':
                // Convert single user ID to proper format
                if ( is_numeric( $processed_value ) ) {
                    return $processed_value;
                }
                break;
        }

        // For all other field types, return as-is
        return $processed_value;
    }

    /**
     * Execute the actual import
     */
    public static function execute_import( $session_id ) {
        global $wpdb;

        // Get session data from dt_reports table
        $session = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $wpdb->dt_reports WHERE id = %d AND type = 'import_session'",
                $session_id
            ),
            ARRAY_A
        );

        if ( !$session ) {
            return new WP_Error( 'session_not_found', 'Import session not found' );
        }

        $payload = maybe_unserialize( $session['payload'] ) ?: [];
        $csv_data = $payload['csv_data'] ?? [];
        $field_mappings = $payload['field_mappings'] ?? [];
        $post_type = $session['post_type'];

        $headers = array_shift( $csv_data );
        $imported_count = 0;
        $error_count = 0;
        $errors = [];
        $imported_records = []; // Store imported record data
        $total_rows = count( $csv_data );

        // Set initial progress to 1% to show processing has started
        if ( $total_rows > 0 ) {
            $initial_payload = array_merge($payload, [
                'progress' => 1,
                'records_imported' => 0,
                'errors' => [],
                'imported_records' => []
            ]);

            $wpdb->update(
                $wpdb->dt_reports,
                [
                    'payload' => maybe_serialize( $initial_payload ),
                    'subtype' => 'import_processing',
                    'timestamp' => time()
                ],
                [ 'id' => $session_id ],
                [ '%s', '%s', '%d' ],
                [ '%d' ]
            );
        }

        foreach ( $csv_data as $row_index => $row ) {
            try {
                $post_data = [];

                foreach ( $field_mappings as $column_index => $mapping ) {
                    if ( empty( $mapping['field_key'] ) || $mapping['field_key'] === 'skip' ) {
                        continue;
                    }

                    $field_key = $mapping['field_key'];
                    $raw_value = $row[$column_index] ?? '';

                    if ( !empty( trim( $raw_value ) ) ) {
                        $processed_value = self::process_field_value( $raw_value, $field_key, $mapping, $post_type );
                        if ( $processed_value !== null ) {
                            // Format value according to field type for DT_Posts API
                            $post_data[$field_key] = self::format_value_for_api( $processed_value, $field_key, $post_type );
                        }
                    }
                }

                // Create the post
                $result = DT_Posts::create_post( $post_type, $post_data, true, false );

                if ( is_wp_error( $result ) ) {
                    $error_count++;
                    $errors[] = [
                        'row' => $row_index + 2,
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

                // Update progress in payload
                $progress = round( ( ( $row_index + 1 ) / count( $csv_data ) ) * 100 );

                // Only update progress in database for certain conditions to reduce DB load
                $should_update_progress = false;
                if ( $total_rows <= 5 ) {
                    // For very small imports (≤5 rows), only update when complete
                    $should_update_progress = ( $row_index + 1 ) === $total_rows;
                } else if ( $total_rows <= 10 ) {
                    // For small imports, update every other row or when complete
                    $should_update_progress = ( ( $row_index + 1 ) % 2 === 0 ) || ( $row_index + 1 ) === $total_rows;
                } else {
                    // For larger imports, update every 5 rows or when complete
                    $should_update_progress = ( ( $row_index + 1 ) % 5 === 0 ) || ( $row_index + 1 ) === $total_rows;
                }

                if ( $should_update_progress ) {
                    $updated_payload = array_merge($payload, [
                        'progress' => $progress,
                        'records_imported' => $imported_count,
                        'errors' => $errors,
                        'imported_records' => $imported_records
                    ]);

                    // Determine subtype based on progress
                    $subtype = $progress < 100 ? 'import_processing' : 'import_completed';

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
                }
            } catch ( Exception $e ) {
                $error_count++;
                $errors[] = [
                    'row' => $row_index + 2,
                    'message' => $e->getMessage()
                ];
            }
        }

        // Final update
        $final_subtype = $error_count > 0 ? 'import_completed_with_errors' : 'import_completed';
        $final_payload = array_merge($payload, [
            'progress' => 100,
            'records_imported' => $imported_count,
            'error_count' => $error_count,
            'errors' => $errors,
            'imported_records' => $imported_records
        ]);

        $wpdb->update(
            $wpdb->dt_reports,
            [
                'payload' => maybe_serialize( $final_payload ),
                'subtype' => $final_subtype,
                'value' => $imported_count,
                'timestamp' => time()
            ],
            [ 'id' => $session_id ],
            [ '%s', '%s', '%d', '%d' ],
            [ '%d' ]
        );

        return [
            'imported_count' => $imported_count,
            'error_count' => $error_count,
            'errors' => $errors,
            'imported_records' => $imported_records
        ];
    }
}
