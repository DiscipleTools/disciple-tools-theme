<?php
/**
 * DT Import Field Handlers
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class DT_CSV_Import_Field_Handlers {

    /**
     * Handle text field processing
     */
    public static function handle_text_field( $value, $field_config ) {
        return sanitize_text_field( trim( $value ) );
    }

    /**
     * Handle textarea field processing
     */
    public static function handle_textarea_field( $value, $field_config ) {
        return sanitize_textarea_field( trim( $value ) );
    }

    /**
     * Handle number field processing
     */
    public static function handle_number_field( $value, $field_config ) {
        if ( !is_numeric( $value ) ) {
            throw new Exception( "Invalid number: {$value}" );
        }
        return floatval( $value );
    }

    /**
     * Handle date field processing
     */
    public static function handle_date_field( $value, $field_config, $date_format = 'auto' ) {
        $normalized_date = DT_CSV_Import_Utilities::normalize_date( $value, $date_format );
        if ( empty( $normalized_date ) ) {
            throw new Exception( "Invalid date format: {$value}" );
        }
        return $normalized_date;
    }

    /**
     * Handle boolean field processing
     */
    public static function handle_boolean_field( $value, $field_config ) {
        $boolean_value = DT_CSV_Import_Utilities::normalize_boolean( $value );
        if ( $boolean_value === null ) {
            throw new Exception( "Invalid boolean value: {$value}" );
        }
        return $boolean_value;
    }

    /**
     * Handle key_select field processing
     */
    public static function handle_key_select_field( $value, $field_config, $value_mapping = [] ) {
        if ( isset( $value_mapping[$value] ) ) {
            $mapped_value = $value_mapping[$value];
            if ( isset( $field_config['default'][$mapped_value] ) ) {
                return $mapped_value;
            }
        }

        // Try direct match
        if ( isset( $field_config['default'][$value] ) ) {
            return $value;
        }

        throw new Exception( "Invalid option for key_select field: {$value}" );
    }

    /**
     * Handle multi_select field processing
     */
    public static function handle_multi_select_field( $value, $field_config, $value_mapping = [] ) {
        $values = DT_CSV_Import_Utilities::split_multi_value( $value );
        $processed_values = [];

        foreach ( $values as $val ) {
            $val = trim( $val );

            if ( isset( $value_mapping[$val] ) ) {
                $mapped_value = $value_mapping[$val];
                // Skip processing if mapped to empty string (represents "-- Skip --")
                if ( !empty( $mapped_value ) && isset( $field_config['default'][$mapped_value] ) ) {
                    $processed_values[] = $mapped_value;
                }
            } elseif ( isset( $field_config['default'][$val] ) ) {
                $processed_values[] = $val;
            } else {
                throw new Exception( "Invalid option for multi_select field: {$val}" );
            }
        }

        return $processed_values;
    }

    /**
     * Handle tags field processing
     */
    public static function handle_tags_field( $value, $field_config ) {
        $tags = DT_CSV_Import_Utilities::split_multi_value( $value );
        return array_map(function( $tag ) {
            return sanitize_text_field( trim( $tag ) );
        }, $tags);
    }

    /**
     * Handle communication channel field processing
     */
    public static function handle_communication_channel_field( $value, $field_config, $field_key ) {
        $channels = DT_CSV_Import_Utilities::split_multi_value( $value );
        $processed_channels = [];

        foreach ( $channels as $channel ) {
            $channel = trim( $channel );

            // Basic validation based on field type
            if ( strpos( $field_key, 'email' ) !== false ) {
                if ( !filter_var( $channel, FILTER_VALIDATE_EMAIL ) ) {
                    throw new Exception( "Invalid email address: {$channel}" );
                }
            } elseif ( strpos( $field_key, 'phone' ) !== false ) {
                // Basic phone validation
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
     * Handle connection field processing
     */
    public static function handle_connection_field( $value, $field_config ) {
        $connection_post_type = $field_config['post_type'] ?? '';
        if ( empty( $connection_post_type ) ) {
            throw new Exception( 'Connection field missing post_type configuration' );
        }

        $connections = DT_CSV_Import_Utilities::split_multi_value( $value );
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
     * Handle user_select field processing
     */
    public static function handle_user_select_field( $value, $field_config ) {
        $user = null;

        // Try to find by ID
        if ( is_numeric( $value ) ) {
            $user = get_user_by( 'id', intval( $value ) );
        }

        // Try to find by username
        if ( !$user ) {
            $user = get_user_by( 'login', $value );
        }

        // Try to find by display name
        if ( !$user ) {
            $user = get_user_by( 'display_name', $value );
        }

        if ( !$user ) {
            throw new Exception( "User not found: {$value}" );
        }

        return $user->ID;
    }

    /**
     * Handle location_grid field processing (requires numeric grid ID)
     */
    public static function handle_location_grid_field( $value, $field_config ) {
        $value = trim( $value );

        // location_grid must be a numeric grid ID
        if ( !is_numeric( $value ) ) {
            throw new Exception( "location_grid field requires a numeric grid ID, got: {$value}" );
        }

        $grid_id = intval( $value );

        // Validate that the grid ID exists
        $is_valid = DT_CSV_Import_Geocoding::validate_grid_id( $grid_id );

        if ( !$is_valid ) {
            throw new Exception( "Invalid location grid ID: {$grid_id}" );
        }

        return $grid_id;
    }

    /**
     * Handle location_grid_meta field processing (supports numeric ID, coordinates, or address)
     */
    public static function handle_location_grid_meta( $value, $field_key, $post_type, $field_settings, $import_settings ) {
        if ( empty( $value ) ) {
            return null;
        }

        $geocode_service = $import_settings['geocode_service'] ?? 'none';
        $country_code = $import_settings['country_code'] ?? null;
        $preview_mode = $import_settings['preview_mode'] ?? false;

        try {
            $location_result = DT_CSV_Import_Geocoding::process_for_import( $value, $geocode_service, $country_code, $preview_mode );

            if ( $location_result === null ) {
                return null;
            }

            // Handle different result formats from the geocoding processor
            if ( isset( $location_result['location_grid_meta'] ) ) {
                // Multiple locations with grid IDs or coordinates
                $result = $location_result['location_grid_meta'];

                // Check if we also have addresses to process
                if ( isset( $location_result['contact_address'] ) ) {
                    // Mixed data: both coordinates/grid IDs AND addresses
                    // Return both in the result so the processor can handle them
                    $result['contact_address'] = $location_result['contact_address'];
                }
            } elseif ( isset( $location_result['grid_id'] ) ) {
                // Single grid ID
                $result = [
                    'values' => [
                        [
                            'grid_id' => $location_result['grid_id']
                        ]
                    ],
                    'force_values' => false
                ];
            } elseif ( isset( $location_result['lat'], $location_result['lng'] ) ) {
                // Single coordinate pair
                $result = [
                    'values' => [
                        [
                            'lng' => $location_result['lng'],
                            'lat' => $location_result['lat']
                        ]
                    ],
                    'force_values' => false
                ];
            } elseif ( isset( $location_result['address_for_geocoding'] ) ) {
                // Single address mapped to location_grid_meta - create contact_address with geocoding
                $result = [
                    'contact_address' => [
                        [
                            'value' => $location_result['address_for_geocoding'],
                            'geolocate' => $geocode_service !== 'none'
                        ]
                    ]
                ];
            } elseif ( isset( $location_result['contact_address'] ) ) {
                // Multiple addresses mapped to location_grid_meta - use existing contact_address format
                $result = [
                    'contact_address' => $location_result['contact_address']
                ];
            } else {
                // Unknown format
                return null;
            }

            return $result;

        } catch ( Exception $e ) {

            return [
                'error' => 'Location processing failed: ' . $e->getMessage()
            ];
        }
    }



    /**
     * Handle location field processing (legacy)
     */
    public static function handle_location_field( $value, $field_config ) {
        $value = trim( $value );

        $result = null;

        // Check if it's a grid ID
        if ( is_numeric( $value ) ) {
            $result = self::handle_location_grid_field( $value, $field_config );
        } else {
            // For non-numeric values, treat as location_grid_meta
            $result = self::handle_location_grid_meta( $value, '', '', [], [] );
        }

        return $result;
    }
}
