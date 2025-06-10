<?php
/**
 * DT CSV Import Processor
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class DT_CSV_Import_Processor {

    /**
     * Generate preview data
     */
    public static function generate_preview( $csv_data, $field_mappings, $post_type, $limit = 10, $offset = 0 ) {
        $headers = array_shift( $csv_data );
        $preview_data = [];
        $field_settings = DT_Posts::get_post_field_settings( $post_type );

        // For large datasets (>1000 rows), use sampling to estimate counts
        // For smaller datasets, analyze all rows for accurate counts
        $total_rows = count( $csv_data );
        $use_sampling = $total_rows > 500;

        $total_processable_count = 0;
        $total_error_count = 0;

        if ( $use_sampling ) {
            // Sample up to 500 rows for estimation
            $sample_size = min( 500, $total_rows );
            $sample_indices = array_rand( $csv_data, $sample_size );
            if ( !is_array( $sample_indices ) ) {
                $sample_indices = [ $sample_indices ];
            }

            $sample_processable = 0;
            $sample_errors = 0;

            foreach ( $sample_indices as $row_index ) {
                $row = $csv_data[$row_index];
                $has_errors = false;
                $has_valid_data = false;

                foreach ( $field_mappings as $column_index => $mapping ) {
                    if ( empty( $mapping['field_key'] ) || $mapping['field_key'] === 'skip' ) {
                        continue;
                    }

                    $field_key = $mapping['field_key'];
                    $raw_value = $row[$column_index] ?? '';

                    try {
                        $processed_value = self::process_field_value( $raw_value, $field_key, $mapping, $post_type, true );
                        if ( $processed_value !== null && $processed_value !== '' ) {
                            $has_valid_data = true;
                        }
                    } catch ( Exception $e ) {
                        $has_errors = true;
                        break;
                    }
                }

                if ( $has_errors ) {
                    $sample_errors++;
                } else if ( $has_valid_data ) {
                    $sample_processable++;
                }
            }

            // Extrapolate from sample to total
            $total_processable_count = round( ( $sample_processable / $sample_size ) * $total_rows );
            $total_error_count = round( ( $sample_errors / $sample_size ) * $total_rows );

        } else {
            // For smaller datasets, analyze all rows for accurate counts
            foreach ( $csv_data as $row_index => $row ) {
                $has_errors = false;
                $has_valid_data = false;

                foreach ( $field_mappings as $column_index => $mapping ) {
                    if ( empty( $mapping['field_key'] ) || $mapping['field_key'] === 'skip' ) {
                        continue;
                    }

                    $field_key = $mapping['field_key'];
                    $raw_value = $row[$column_index] ?? '';

                    try {
                        $processed_value = self::process_field_value( $raw_value, $field_key, $mapping, $post_type, true );
                        if ( $processed_value !== null && $processed_value !== '' ) {
                            $has_valid_data = true;
                        }
                    } catch ( Exception $e ) {
                        $has_errors = true;
                        break;
                    }
                }

                if ( $has_errors ) {
                    $total_error_count++;
                } else if ( $has_valid_data ) {
                    $total_processable_count++;
                }
            }
        }

        // Now generate the limited preview data for display
        $data_rows = array_slice( $csv_data, $offset, $limit );
        $preview_processed_count = 0;
        $preview_skipped_count = 0;

        foreach ( $data_rows as $row_index => $row ) {
            $processed_row = [];
            $has_errors = false;
            $row_errors = [];
            $row_warnings = [];
            $duplicate_check_fields = [];

            foreach ( $field_mappings as $column_index => $mapping ) {
                if ( empty( $mapping['field_key'] ) || $mapping['field_key'] === 'skip' ) {
                    continue;
                }

                $field_key = $mapping['field_key'];
                $raw_value = $row[$column_index] ?? '';
                $field_config = $field_settings[$field_key] ?? [];

                try {
                    $processed_value = self::process_field_value( $raw_value, $field_key, $mapping, $post_type, true );

                    // Check if this field has duplicate checking enabled
                    if ( isset( $mapping['duplicate_checking'] ) && $mapping['duplicate_checking'] === true && !empty( trim( $raw_value ) ) ) {
                        $duplicate_check_fields[] = $field_key;
                    }

                    // Handle connection fields specially for preview
                    if ( $field_config['type'] === 'connection' && is_array( $processed_value ) ) {
                        $connection_display = [];
                        $has_new_connections = false;

                        foreach ( $processed_value as $connection_info ) {
                            if ( is_array( $connection_info ) ) {
                                $display_name = $connection_info['name'];
                                if ( $connection_info['will_create'] ) {
                                    $display_name .= ' (NEW)';
                                    $has_new_connections = true;
                                }
                                $connection_display[] = $display_name;
                            } else {
                                $connection_display[] = $connection_info;
                            }
                        }

                        if ( $has_new_connections ) {
                            $connection_post_type_settings = DT_Posts::get_post_settings( $field_config['post_type'] );
                            $post_type_label = $connection_post_type_settings['label_plural'] ?? $field_config['post_type'];
                            $row_warnings[] = sprintf(
                                'New %s will be created for field "%s"',
                                $post_type_label,
                                $field_config['name']
                            );
                        }

                        $formatted_value = implode( ', ', $connection_display );
                    } else {
                        $formatted_value = self::format_value_for_preview( $processed_value, $field_key, $post_type );
                    }

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

            // Note about duplicate checking for preview
            // In preview mode, we just indicate that duplicate checking will happen
            // The actual duplicate checking is handled by DT_Posts during import
            $will_update_existing = false;
            if ( !empty( $duplicate_check_fields ) ) {
                $will_update_existing = false; // We can't easily predict this in preview
                // Note: Duplicate checking is configured but we don't show warnings in preview
            }

            $preview_data[] = [
                'row_number' => $offset + $row_index + 2, // +2 for header and 0-based index
                'data' => $processed_row,
                'has_errors' => $has_errors,
                'errors' => $row_errors,
                'warnings' => $row_warnings,
                'will_update_existing' => $will_update_existing,
                'existing_post_id' => null // Not determined in preview
            ];

            if ( $has_errors ) {
                $preview_skipped_count++;
            } else {
                $preview_processed_count++;
            }
        }

        return [
            'rows' => $preview_data,
            'total_rows' => count( $csv_data ),
            'preview_count' => count( $preview_data ),
            'processable_count' => $total_processable_count,
            'error_count' => $total_error_count,
            'offset' => $offset,
            'limit' => $limit,
            'is_estimated' => $use_sampling, // Indicate if counts are estimated from sampling
            'sample_size' => $use_sampling ? $sample_size : null
        ];
    }

    /**
     * Process a single field value based on field type and mapping
     */
    public static function process_field_value( $raw_value, $field_key, $mapping, $post_type, $preview_mode = false ) {
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

        $result = null;

        switch ( $field_type ) {
            case 'text':
            case 'textarea':
                $result = sanitize_text_field( trim( $raw_value ) );
                break;

            case 'number':
                if ( !is_numeric( $raw_value ) ) {
                    throw new Exception( "Invalid number: {$raw_value}" );
                }
                $result = floatval( $raw_value );
                break;

            case 'date':
                $date_format = isset( $mapping['date_format'] ) ? $mapping['date_format'] : 'auto';
                $normalized_date = DT_CSV_Import_Utilities::normalize_date( $raw_value, $date_format );
                if ( empty( $normalized_date ) ) {
                    throw new Exception( "Invalid date format: {$raw_value}" );
                }
                $result = $normalized_date;
                break;

            case 'boolean':
                $boolean_value = DT_CSV_Import_Utilities::normalize_boolean( $raw_value );
                if ( $boolean_value === null ) {
                    throw new Exception( "Invalid boolean value: {$raw_value}" );
                }
                $result = $boolean_value;
                break;

            case 'key_select':
                $result = self::process_key_select_value( $raw_value, $mapping, $field_config );
                break;

            case 'multi_select':
                $result = self::process_multi_select_value( $raw_value, $mapping, $field_config );
                break;

            case 'tags':
                $result = self::process_tags_value( $raw_value );
                break;

            case 'communication_channel':
                $result = self::process_communication_channel_value( $raw_value, $field_key );
                // Add geolocate flag for address fields if geocoding is enabled
                if ( ( $field_key === 'contact_address' || strpos( $field_key, 'address' ) !== false ) && is_array( $result ) ) {
                    $geocode_service = $mapping['geocode_service'] ?? 'none';
                    if ( $geocode_service !== 'none' ) {
                        foreach ( $result as &$address_entry ) {
                            $address_entry['geolocate'] = true;
                        }
                    }
                }
                break;

            case 'connection':
                $result = self::process_connection_value( $raw_value, $field_config, $preview_mode );
                break;

            case 'user_select':
                $result = self::process_user_select_value( $raw_value );
                break;

            case 'location':
                $result = self::process_location_value( $raw_value, $preview_mode );
                break;

            case 'location_grid':
                $result = self::process_location_grid_value( $raw_value );
                break;

            case 'location_meta':
                $geocode_service = $mapping['geocode_service'] ?? 'none';
                $result = self::process_location_grid_meta_value( $raw_value, $geocode_service, $preview_mode );
                break;

            default:
                $result = sanitize_text_field( trim( $raw_value ) );
                break;
        }

        return $result;
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
        $values = DT_CSV_Import_Utilities::split_multi_value( $raw_value );
        $processed_values = [];
        $value_mapping = $mapping['value_mapping'] ?? [];

        foreach ( $values as $value ) {
            $value = trim( $value );

            if ( isset( $value_mapping[$value] ) ) {
                $mapped_value = $value_mapping[$value];
                // Skip processing if mapped to empty string (represents "-- Skip --")
                if ( !empty( $mapped_value ) && isset( $field_config['default'][$mapped_value] ) ) {
                    $processed_values[] = $mapped_value;
                }
                // If mapped to empty string, silently skip this value
            } elseif ( isset( $field_config['default'][$value] ) ) {
                $processed_values[] = $value;
            } else {
                // If value mapping is configured, skip unmapped invalid values silently
                // If no value mapping is configured, throw exception for invalid values
                if ( empty( $value_mapping ) ) {
                    throw new Exception( "Invalid option for multi_select field: {$value}" );
                }
                // Otherwise silently skip invalid values when value mapping is present
            }
        }

        return $processed_values;
    }

    /**
     * Process tags field value
     */
    private static function process_tags_value( $raw_value ) {
        $tags = DT_CSV_Import_Utilities::split_multi_value( $raw_value );
        return array_map(function( $tag ) {
            return sanitize_text_field( trim( $tag ) );
        }, $tags);
    }

    /**
     * Process communication channel value
     */
    private static function process_communication_channel_value( $raw_value, $field_key ) {
        $channels = DT_CSV_Import_Utilities::split_multi_value( $raw_value );
        $processed_channels = [];

        foreach ( $channels as $channel ) {
            $channel = trim( $channel );

            // No validation here - the API will handle all communication channel validation

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
    private static function process_connection_value( $raw_value, $field_config, $preview_mode = false ) {
        $connection_post_type = $field_config['post_type'] ?? '';
        if ( empty( $connection_post_type ) ) {
            throw new Exception( 'Connection field missing post_type configuration' );
        }

        $connections = DT_CSV_Import_Utilities::split_multi_value( $raw_value );
        $processed_connections = [];

        foreach ( $connections as $connection_index => $connection ) {
            $connection = trim( $connection );
            $connection_info = [
                'raw_value' => $connection,
                'id' => null,
                'name' => null,
                'exists' => false,
                'will_create' => false
            ];

            // Try to find by ID first
            if ( is_numeric( $connection ) ) {
                $post = DT_Posts::get_post( $connection_post_type, intval( $connection ), true, false );

                if ( !is_wp_error( $post ) ) {
                    $connection_info['id'] = intval( $connection );
                    $connection_info['name'] = $post['title'] ?? $post['name'] ?? "Record #{$connection}";
                    $connection_info['exists'] = true;

                    if ( $preview_mode ) {
                        $processed_connections[] = $connection_info;
                    } else {
                        $processed_connections[] = intval( $connection );
                    }

                    continue;
                }
            }

            // Try to find by title/name - but check for multiple matches first
            $posts = DT_Posts::list_posts($connection_post_type, [
                'name' => $connection,
                'limit' => 2  // Get 2 to check for duplicates
            ]);

            if ( !is_wp_error( $posts ) && !empty( $posts['posts'] ) ) {
                // Check if multiple records found with same name
                if ( count( $posts['posts'] ) > 1 ) {
                    // Skip this connection instead of failing the entire row
                    continue;
                }

                $found_post = $posts['posts'][0];
                $connection_info['id'] = $found_post['ID'];
                $connection_info['name'] = $found_post['title'] ?? $found_post['name'] ?? $connection;
                $connection_info['exists'] = true;

                if ( $preview_mode ) {
                    $processed_connections[] = $connection_info;
                } else {
                    $processed_connections[] = $found_post['ID'];
                }
            } else {
                // Record not found - will need to create it
                if ( $preview_mode ) {
                    $connection_info['name'] = $connection;
                    $connection_info['will_create'] = true;
                    $processed_connections[] = $connection_info;
                } else {
                    // Create the record during actual import
                    $new_post = self::create_connection_record( $connection_post_type, $connection );

                    if ( !is_wp_error( $new_post ) ) {
                        $connection_info['id'] = $new_post['ID'];
                        $processed_connections[] = $new_post['ID'];
                    } else {
                        throw new Exception( "Failed to create connection record: {$connection} - " . $new_post->get_error_message() );
                    }
                }
            }
        }

        return $processed_connections;
    }

    /**
     * Create a new connection record
     */
    private static function create_connection_record( $post_type, $name ) {
        $post_data = [];

        // Determine the title field name based on post type
        $field_settings = DT_Posts::get_post_field_settings( $post_type );

        if ( isset( $field_settings['title'] ) ) {
            $post_data['title'] = $name;
        } elseif ( isset( $field_settings['name'] ) ) {
            $post_data['name'] = $name;
        } else {
            // Fallback - use the first text field or 'title'
            foreach ( $field_settings as $field_key => $field_config ) {
                if ( $field_config['type'] === 'text' ) {
                    $post_data[$field_key] = $name;
                    break;
                }
            }

            if ( empty( $post_data ) ) {
                $post_data['title'] = $name; // Final fallback
            }
        }

        // Create the post
        $result = DT_Posts::create_post( $post_type, $post_data, true, false );

        return $result;
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
    private static function process_location_value( $raw_value, $preview_mode = false ) {
        $raw_value = trim( $raw_value );

        // Location field only accepts grid IDs (numeric values)
        if ( !is_numeric( $raw_value ) ) {
            throw new Exception( "Location field requires a numeric grid ID, got: {$raw_value}" );
        }

        return intval( $raw_value );
    }

    /**
     * Process location_grid field value
     */
    private static function process_location_grid_value( $raw_value ) {
        return DT_CSV_Import_Field_Handlers::handle_location_grid_field( $raw_value, [] );
    }

    /**
     * Process location_grid_meta field value using DT's native geocoding
     */
    private static function process_location_grid_meta_value( $raw_value, $geocode_service, $preview_mode = false ) {
        $import_settings = [
            'geocode_service' => $geocode_service,
            'preview_mode' => $preview_mode
        ];

        $result = DT_CSV_Import_Field_Handlers::handle_location_grid_meta( $raw_value, 'location_grid_meta', '', [], $import_settings );

        return $result;
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

            case 'location':
                // Format location data for DT_Posts API
                if ( is_numeric( $processed_value ) ) {
                    // Grid ID
                    return [
                        'values' => [
                            [ 'value' => $processed_value ]
                        ]
                    ];
                } elseif ( is_array( $processed_value ) ) {
                    // Lat/lng or address data
                    return [
                        'values' => [
                            $processed_value
                        ]
                    ];
                }
                break;

            case 'location_grid':
                // Format location grid ID for DT_Posts API
                if ( is_numeric( $processed_value ) ) {
                    return [
                        'values' => [
                            [ 'value' => $processed_value ]
                        ]
                    ];
                }
                break;

            case 'location_meta':
                // DT's native location_grid_meta format - already properly formatted by handlers
                if ( is_array( $processed_value ) && !empty( $processed_value ) ) {
                    // Check if it's already in DT's native format with 'values' key
                    if ( isset( $processed_value['values'] ) ) {
                        return $processed_value;
                    }
                    // Legacy format - convert to DT's native format
                    if ( isset( $processed_value[0] ) && is_array( $processed_value[0] ) ) {
                        // Multiple locations
                        return [
                            'values' => $processed_value,
                            'force_values' => false
                        ];
                    } else {
                        // Single location object
                        return [
                            'values' => [ $processed_value ],
                            'force_values' => false
                        ];
                    }
                }
                break;

            case 'user_select':
                // Convert single user ID to proper format
                if ( is_numeric( $processed_value ) ) {
                    return $processed_value;
                }
                break;
        }

        // Handle special field keys that might not have standard types
        if ( $field_key === 'contact_address' || strpos( $field_key, 'address' ) !== false ) {
            // contact_address is a communication channel - format as such
            if ( is_array( $processed_value ) && !empty( $processed_value ) ) {
                return [
                    'values' => $processed_value
                ];
            }
        }

        if ( $field_key === 'location_grid_meta' ) {
            // DT's native location_grid_meta format - already properly formatted by handlers
            if ( is_array( $processed_value ) && !empty( $processed_value ) ) {
                // Check if it's already in DT's native format with 'values' key
                if ( isset( $processed_value['values'] ) ) {
                    return $processed_value;
                }
            }
        }

        // For all other field types, return as-is
        return $processed_value;
    }

    /**
     * Format processed value for preview display (human-readable)
     */
    public static function format_value_for_preview( $processed_value, $field_key, $post_type ) {
        $field_settings = DT_Posts::get_post_field_settings( $post_type );

        if ( !isset( $field_settings[$field_key] ) ) {
            return $processed_value;
        }

        $field_config = $field_settings[$field_key];
        $field_type = $field_config['type'];

        // Handle null/empty values
        if ( $processed_value === null || $processed_value === '' ) {
            return '';
        }

        switch ( $field_type ) {
            case 'multi_select':
            case 'tags':
                // Convert array to comma-separated display
                if ( is_array( $processed_value ) ) {
                    return implode( ', ', $processed_value );
                }
                break;

            case 'connection':
                // Already handled in generate_preview for connection fields
                return $processed_value;

            case 'communication_channel':
                // Extract values from communication channel array
                if ( is_array( $processed_value ) ) {
                    $values = array_map( function( $channel ) {
                        return $channel['value'] ?? $channel;
                    }, $processed_value );
                    return implode( ', ', $values );
                }
                break;

            case 'location':
            case 'location_grid':
            case 'location_meta':
                // Handle location data for preview display
                if ( is_numeric( $processed_value ) ) {
                    // Grid ID - try to get the location name
                    global $wpdb;
                    $location_name = $wpdb->get_var( $wpdb->prepare(
                        "SELECT name FROM $wpdb->dt_location_grid WHERE grid_id = %d",
                        intval( $processed_value )
                    ) );
                    return $location_name ?: "Grid ID: {$processed_value}";
                } elseif ( is_array( $processed_value ) ) {
                    // Check if this is an array of multiple locations (from semicolon-separated input)
                    if ( isset( $processed_value[0] ) && is_array( $processed_value[0] ) ) {
                        // Multiple locations - format each one
                        $location_displays = [];
                        foreach ( $processed_value as $location ) {
                            $location_displays[] = self::format_single_location_for_preview( $location );
                        }
                        return implode( '; ', $location_displays );
                    } else {
                        // Single location object
                        return self::format_single_location_for_preview( $processed_value );
                    }
                }
                break;

            case 'key_select':
                // Return the label for the selected key
                if ( isset( $field_config['default'][$processed_value] ) ) {
                    return $field_config['default'][$processed_value]['label'] ?? $processed_value;
                }
                break;

            case 'user_select':
                // Get user display name
                if ( is_numeric( $processed_value ) ) {
                    $user = get_user_by( 'id', intval( $processed_value ) );
                    return $user ? $user->display_name : "User ID: {$processed_value}";
                }
                break;

            case 'date':
                // Format date for display
                if ( is_numeric( $processed_value ) ) {
                    return gmdate( 'Y-m-d', intval( $processed_value ) );
                }
                break;

            case 'boolean':
                // Convert boolean to Yes/No
                return $processed_value ? 'Yes' : 'No';
        }

        // For all other field types, return as string
        return is_array( $processed_value ) ? implode( ', ', $processed_value ) : (string) $processed_value;
    }

    /**
     * Format a single location object for preview display
     */
    private static function format_single_location_for_preview( $location ) {
        if ( !is_array( $location ) ) {
            return (string) $location;
        }

        // If this is preview mode data, just return the raw value as-is
        if ( isset( $location['preview_mode'] ) && $location['preview_mode'] === true ) {
            return $location['raw_value'] ?? $location['label'] ?? 'Unknown location';
        }

        // Handle coordinate or address arrays (for actual geocoded data)
        if ( isset( $location['lat'] ) && isset( $location['lng'] ) ) {
            return "Coordinates: {$location['lat']}, {$location['lng']}";
        } elseif ( isset( $location['address'] ) ) {
            return $location['address'];
        } elseif ( isset( $location['label'] ) ) {
            return $location['label'];
        } elseif ( isset( $location['name'] ) ) {
            return $location['name'];
        } elseif ( isset( $location['grid_id'] ) ) {
            // Try to get the location name from grid ID
            global $wpdb;
            $location_name = $wpdb->get_var( $wpdb->prepare(
                "SELECT name FROM $wpdb->dt_location_grid WHERE grid_id = %d",
                intval( $location['grid_id'] )
            ) );
            return $location_name ?: "Grid ID: {$location['grid_id']}";
        }

        // Fallback: return first non-empty value
        foreach ( $location as $key => $value ) {
            if ( !empty( $value ) && !in_array( $key, [ 'source', 'geocoding_note', 'geocoding_error', 'preview_mode', 'raw_value' ] ) ) {
                return $value;
            }
        }

        return 'Unknown location';
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
        $field_mappings = $payload['field_mappings'] ?? [];
        $import_options = $payload['import_options'] ?? [];
        $post_type = $session['post_type'];

        // Load CSV data from file (no longer stored in payload)
        $file_path = $payload['file_path'] ?? '';
        if ( empty( $file_path ) || !file_exists( $file_path ) ) {
            return new WP_Error( 'csv_file_not_found', 'CSV file not found' );
        }

        $csv_data = DT_CSV_Import_Utilities::parse_csv_file( $file_path );
        if ( is_wp_error( $csv_data ) ) {
            return new WP_Error( 'csv_parse_error', 'Failed to parse CSV file: ' . $csv_data->get_error_message() );
        }

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
                $duplicate_check_fields = [];
                foreach ( $field_mappings as $column_index => $mapping ) {
                    if ( empty( $mapping['field_key'] ) || $mapping['field_key'] === 'skip' ) {
                        continue;
                    }

                    $field_key = $mapping['field_key'];
                    $raw_value = $row[$column_index] ?? '';

                    if ( !empty( trim( $raw_value ) ) ) {
                        $processed_value = self::process_field_value( $raw_value, $field_key, $mapping, $post_type );

                        if ( $processed_value !== null ) {
                            // Special handling for location_grid_meta that returns contact_address data
                            if ( $field_key === 'location_grid_meta' && is_array( $processed_value ) && isset( $processed_value['contact_address'] ) ) {
                                // Extract contact_address data and add it to the contact_address field
                                $contact_address_data = $processed_value['contact_address'];

                                // Format for API
                                $formatted_address_value = self::format_value_for_api( $contact_address_data, 'contact_address', $post_type );

                                // Add to post_data under contact_address field
                                $post_data['contact_address'] = $formatted_address_value;

                                // Remove contact_address from processed_value to avoid duplication
                                unset( $processed_value['contact_address'] );

                                // Check if there's still location_grid_meta data to process
                                if ( isset( $processed_value['values'] ) && !empty( $processed_value['values'] ) ) {
                                    // Process the remaining location_grid_meta data
                                    $formatted_location_value = self::format_value_for_api( $processed_value, $field_key, $post_type );
                                    $post_data[$field_key] = $formatted_location_value;
                                }
                            } else {
                                // Normal field processing
                                $formatted_value = self::format_value_for_api( $processed_value, $field_key, $post_type );
                                $post_data[$field_key] = $formatted_value;
                            }

                            // Check if this field has duplicate checking enabled
                            if ( isset( $mapping['duplicate_checking'] ) && $mapping['duplicate_checking'] === true ) {
                                $duplicate_check_fields[] = $field_key;
                            }
                        }
                    }
                }

                // Apply import options as default values (only if not already set from CSV)
                if ( !empty( $import_options ) ) {
                    // Apply assigned_to if set and not already in post_data
                    if ( isset( $import_options['assigned_to'] ) && $import_options['assigned_to'] !== null && $import_options['assigned_to'] !== '' && !isset( $post_data['assigned_to'] ) ) {
                        $post_data['assigned_to'] = $import_options['assigned_to'];
                    }

                    // Apply source if set and not already in post_data
                    if ( isset( $import_options['source'] ) && $import_options['source'] !== null && $import_options['source'] !== '' && !isset( $post_data['sources'] ) ) {
                        $post_data['sources'] = [
                            'values' => [
                                [ 'value' => $import_options['source'] ]
                            ]
                        ];
                    }
                }

                // Prepare create_post arguments
                $create_args = [];
                if ( !empty( $duplicate_check_fields ) ) {
                    $create_args['check_for_duplicates'] = $duplicate_check_fields;
                }

                // Create the post (DT_Posts will handle duplicate checking internally)
                $result = DT_Posts::create_post( $post_type, $post_data, true, false, $create_args );

                if ( is_wp_error( $result ) ) {
                    $error_count++;
                    $error_message = $result->get_error_message();

                    $errors[] = [
                        'row' => $row_index + 2,
                        'message' => $error_message
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
                $error_message = $e->getMessage();

                $errors[] = [
                    'row' => $row_index + 2,
                    'message' => $error_message
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
