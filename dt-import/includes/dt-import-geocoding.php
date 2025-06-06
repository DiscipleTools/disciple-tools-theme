<?php
/**
 * DT Import Geocoding Service
 * Handles geocoding for location fields during CSV import
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class DT_CSV_Import_Geocoding {

    /**
     * Available geocoding services
     */
    const AVAILABLE_SERVICES = [ 'google', 'mapbox' ];

    /**
     * Check which geocoding services are available
     */
    public static function get_available_geocoding_services() {
        $available = [];

        if ( class_exists( 'Disciple_Tools_Google_Geocode_API' ) && Disciple_Tools_Google_Geocode_API::get_key() ) {
            $available[] = 'google';
        }

        if ( class_exists( 'DT_Mapbox_API' ) && DT_Mapbox_API::get_key() ) {
            $available[] = 'mapbox';
        }

        return $available;
    }

    /**
     * Process location grid meta data during import
     */
    public static function process_location_grid_meta( $data, $geocode_service = 'none' ) {
        if ( empty( $data ) ) {
            return null;
        }

        // If data is already processed (array with type), return as-is
        if ( is_array( $data ) && isset( $data['type'] ) ) {
            return $data;
        }

        // Convert to location grid meta format
        $location_meta = self::convert_to_location_meta( $data, $geocode_service );

        return $location_meta;
    }

    /**
     * Convert raw location data to location grid meta format
     */
    private static function convert_to_location_meta( $data, $geocode_service ) {
        $location_meta = [];

        if ( isset( $data['grid_id'] ) ) {
            $location_meta['grid_id'] = $data['grid_id'];
        }

        if ( isset( $data['lat'], $data['lng'] ) ) {
            $location_meta['lng'] = $data['lng'];
            $location_meta['lat'] = $data['lat'];
        }

        if ( isset( $data['level'] ) ) {
            $location_meta['level'] = $data['level'];
        }

        if ( isset( $data['label'] ) ) {
            $location_meta['label'] = $data['label'];
        } elseif ( isset( $data['address'] ) ) {
            $location_meta['label'] = $data['address'];
        } elseif ( isset( $data['lat'], $data['lng'] ) ) {
            $location_meta['label'] = $data['lat'] . ', ' . $data['lng'];
        }

        // Add source information
        $location_meta['source'] = 'csv_import';

        return $location_meta;
    }

    /**
     * Geocode an address using the specified service
     */
    public static function geocode_address( $address, $geocode_service, $country_code = null ) {
        $address = trim( $address );

        if ( empty( $address ) ) {
            throw new Exception( 'Address cannot be empty' );
        }

        switch ( strtolower( $geocode_service ) ) {
            case 'google':
                return self::geocode_with_google( $address, $country_code );

            case 'mapbox':
                return self::geocode_with_mapbox( $address, $country_code );

            default:
                throw new Exception( "Unsupported geocoding service: {$geocode_service}" );
        }
    }

    /**
     * Reverse geocode coordinates using the specified service
     */
    public static function reverse_geocode( $lat, $lng, $geocode_service ) {
        if ( !is_numeric( $lat ) || !is_numeric( $lng ) ) {
            throw new Exception( 'Invalid coordinates for reverse geocoding' );
        }

        // Validate coordinate ranges
        if ( $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180 ) {
            throw new Exception( 'Coordinates out of valid range' );
        }

        switch ( strtolower( $geocode_service ) ) {
            case 'google':
                return self::reverse_geocode_with_google( $lat, $lng );

            case 'mapbox':
                return self::reverse_geocode_with_mapbox( $lat, $lng );

            default:
                throw new Exception( "Unsupported geocoding service: {$geocode_service}" );
        }
    }

    /**
     * Get location grid ID from coordinates
     */
    public static function get_grid_id_from_coordinates( $lat, $lng, $country_code = null ) {
        if ( !class_exists( 'Location_Grid_Geocoder' ) ) {
            throw new Exception( 'Location_Grid_Geocoder class not available' );
        }

        $geocoder = new Location_Grid_Geocoder();
        $result = $geocoder->get_grid_id_by_lnglat( $lng, $lat, $country_code );

        if ( empty( $result ) ) {
            throw new Exception( 'Could not find location grid for coordinates' );
        }

        return $result;
    }

    /**
     * Validate location grid ID exists
     */
    public static function validate_grid_id( $grid_id ) {
        global $wpdb;

        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT grid_id FROM $wpdb->dt_location_grid WHERE grid_id = %d",
            intval( $grid_id )
        ) );

        return !empty( $exists );
    }

    /**
     * Geocode with Google API
     */
    private static function geocode_with_google( $address, $country_code = null ) {
        if ( !class_exists( 'Disciple_Tools_Google_Geocode_API' ) ) {
            throw new Exception( 'Google Geocoding API not available' );
        }

        if ( !Disciple_Tools_Google_Geocode_API::get_key() ) {
            throw new Exception( 'Google API key not configured' );
        }

        if ( $country_code ) {
            $result = Disciple_Tools_Google_Geocode_API::query_google_api_with_components(
                $address,
                [ 'country' => $country_code ]
            );
        } else {
            $result = Disciple_Tools_Google_Geocode_API::query_google_api( $address );
        }

        if ( !$result || !isset( $result['results'][0] ) ) {
            throw new Exception( 'Google geocoding failed for address: ' . $address );
        }

        $location = $result['results'][0]['geometry']['location'];
        $formatted_address = $result['results'][0]['formatted_address'];

        return [
            'lat' => $location['lat'],
            'lng' => $location['lng'],
            'formatted_address' => $formatted_address,
            'service' => 'google',
            'raw' => $result
        ];
    }

    /**
     * Geocode with Mapbox API
     */
    private static function geocode_with_mapbox( $address, $country_code = null ) {
        if ( !class_exists( 'DT_Mapbox_API' ) ) {
            throw new Exception( 'Mapbox API not available' );
        }

        if ( !DT_Mapbox_API::get_key() ) {
            throw new Exception( 'Mapbox API key not configured' );
        }

        $result = DT_Mapbox_API::forward_lookup( $address, $country_code );

        if ( !$result || empty( $result['features'] ) ) {
            throw new Exception( 'Mapbox geocoding failed for address: ' . $address );
        }

        $feature = $result['features'][0];
        $center = $feature['center'];

        return [
            'lat' => $center[1],
            'lng' => $center[0],
            'formatted_address' => $feature['place_name'],
            'relevance' => $feature['relevance'] ?? 1.0,
            'service' => 'mapbox',
            'raw' => $result
        ];
    }

    /**
     * Reverse geocode with Google API
     */
    private static function reverse_geocode_with_google( $lat, $lng ) {
        if ( !class_exists( 'Disciple_Tools_Google_Geocode_API' ) ) {
            throw new Exception( 'Google Geocoding API not available' );
        }

        if ( !Disciple_Tools_Google_Geocode_API::get_key() ) {
            throw new Exception( 'Google API key not configured' );
        }

        $result = Disciple_Tools_Google_Geocode_API::query_google_api_reverse( "{$lat},{$lng}" );

        if ( !$result || !isset( $result['results'][0] ) ) {
            throw new Exception( 'Google reverse geocoding failed' );
        }

        return [
            'address' => $result['results'][0]['formatted_address'],
            'service' => 'google',
            'raw' => $result
        ];
    }

    /**
     * Reverse geocode with Mapbox API
     */
    private static function reverse_geocode_with_mapbox( $lat, $lng ) {
        if ( !class_exists( 'DT_Mapbox_API' ) ) {
            throw new Exception( 'Mapbox API not available' );
        }

        if ( !DT_Mapbox_API::get_key() ) {
            throw new Exception( 'Mapbox API key not configured' );
        }

        $result = DT_Mapbox_API::reverse_lookup( $lng, $lat );

        if ( !$result || empty( $result['features'] ) ) {
            throw new Exception( 'Mapbox reverse geocoding failed' );
        }

        return [
            'address' => $result['features'][0]['place_name'],
            'service' => 'mapbox',
            'raw' => $result
        ];
    }

    /**
     * Process location data for import
     * Combines geocoding with location grid assignment
     * Supports multiple addresses separated by semicolons
     */
    public static function process_for_import( $value, $geocode_service = 'none', $country_code = null, $preview_mode = false ) {
        $value = trim( $value );

        if ( empty( $value ) ) {
            return null;
        }

        // In preview mode, don't perform actual geocoding - just return the raw values formatted for display
        if ( $preview_mode ) {
            return self::process_for_preview( $value );
        }

        // Check if value contains multiple addresses separated by semicolons
        if ( strpos( $value, ';' ) !== false ) {
            return self::process_multiple_addresses( $value, $geocode_service, $country_code );
        }

        // Process single address/location
        return self::process_single_location( $value, $geocode_service, $country_code );
    }

    /**
     * Process location data for preview mode (no geocoding)
     * Just formats the raw values for display
     */
    private static function process_for_preview( $value ) {
        // Check if value contains multiple addresses separated by semicolons
        if ( strpos( $value, ';' ) !== false ) {
            $addresses = explode( ';', $value );
            $processed_locations = [];

            foreach ( $addresses as $address ) {
                $address = trim( $address );
                if ( empty( $address ) ) {
                    continue;
                }

                // For preview, just return the raw address/value with minimal processing
                if ( is_numeric( $address ) ) {
                    $processed_locations[] = [
                        'label' => "Grid ID: {$address}",
                        'raw_value' => $address,
                        'preview_mode' => true
                    ];
                } elseif ( self::parse_dms_coordinates( $address ) !== null ) {
                    $processed_locations[] = [
                        'label' => "DMS Coordinates: {$address}",
                        'raw_value' => $address,
                        'preview_mode' => true
                    ];
                } elseif ( preg_match( '/^-?\d+\.?\d*\s*,\s*-?\d+\.?\d*$/', $address ) ) {
                    $processed_locations[] = [
                        'label' => "Decimal Coordinates: {$address}",
                        'raw_value' => $address,
                        'preview_mode' => true
                    ];
                } else {
                    $processed_locations[] = [
                        'label' => $address,
                        'raw_value' => $address,
                        'preview_mode' => true
                    ];
                }
            }

            return count( $processed_locations ) > 1 ? $processed_locations : ( $processed_locations[0] ?? null );
        } else {
            // Single address/location for preview
            if ( is_numeric( $value ) ) {
                return [
                    'label' => "Grid ID: {$value}",
                    'raw_value' => $value,
                    'preview_mode' => true
                ];
            } elseif ( self::parse_dms_coordinates( $value ) !== null ) {
                return [
                    'label' => "DMS Coordinates: {$value}",
                    'raw_value' => $value,
                    'preview_mode' => true
                ];
            } elseif ( preg_match( '/^-?\d+\.?\d*\s*,\s*-?\d+\.?\d*$/', $value ) ) {
                return [
                    'label' => "Decimal Coordinates: {$value}",
                    'raw_value' => $value,
                    'preview_mode' => true
                ];
            } else {
                return [
                    'label' => $value,
                    'raw_value' => $value,
                    'preview_mode' => true
                ];
            }
        }
    }

    /**
     * Process multiple addresses separated by semicolons
     */
    private static function process_multiple_addresses( $value, $geocode_service = 'none', $country_code = null ) {
        $addresses = explode( ';', $value );
        $processed_locations = [];
        $errors = [];

        foreach ( $addresses as $address ) {
            $address = trim( $address );
            if ( empty( $address ) ) {
                continue;
            }

            try {
                $location_result = self::process_single_location( $address, $geocode_service, $country_code );
                if ( $location_result !== null ) {
                    $processed_locations[] = $location_result;
                }

                // Add a small delay to avoid rate limiting for geocoding services
                if ( $geocode_service !== 'none' && count( $addresses ) > 1 ) {
                    usleep( 100000 ); // 0.1 second delay
                }
            } catch ( Exception $e ) {
                $errors[] = [
                    'address' => $address,
                    'error' => $e->getMessage()
                ];

                // Still add the address with error info
                $processed_locations[] = [
                    'label' => $address,
                    'source' => 'csv_import',
                    'geocoding_error' => $e->getMessage()
                ];
            }
        }

        // If we have multiple locations, return them as an array
        if ( count( $processed_locations ) > 1 ) {
            return $processed_locations;
        } elseif ( count( $processed_locations ) === 1 ) {
            return $processed_locations[0];
        } else {
            throw new Exception( 'No valid addresses found in: ' . $value );
        }
    }

    /**
     * Process a single location (address, coordinates, or grid ID)
     */
    private static function process_single_location( $value, $geocode_service = 'none', $country_code = null ) {
        try {
            // If it's a numeric grid ID, validate and return
            if ( is_numeric( $value ) ) {
                $grid_id = intval( $value );
                if ( self::validate_grid_id( $grid_id ) ) {
                    return [
                        'grid_id' => $grid_id,
                        'source' => 'csv_import'
                    ];
                } else {
                    throw new Exception( "Invalid location grid ID: {$grid_id}" );
                }
            }

            // Check if it's coordinates in DMS format (degrees, minutes, seconds)
            $dms_coords = self::parse_dms_coordinates( $value );
            if ( $dms_coords !== null ) {
                $lat = $dms_coords['lat'];
                $lng = $dms_coords['lng'];

                // Validate coordinates
                if ( $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180 ) {
                    throw new Exception( "Invalid DMS coordinates: {$value}" );
                }

                // Try to get grid ID from coordinates
                try {
                    $grid_result = self::get_grid_id_from_coordinates( $lat, $lng, $country_code );

                    $location_meta = [
                        'lng' => $lng,
                        'lat' => $lat,
                        'source' => 'csv_import'
                    ];

                    if ( isset( $grid_result['grid_id'] ) ) {
                        $location_meta['grid_id'] = $grid_result['grid_id'];
                    }

                    if ( isset( $grid_result['level'] ) ) {
                        $location_meta['level'] = $grid_result['level'];
                    }

                    // Try to get address if geocoding service is available
                    if ( $geocode_service !== 'none' ) {
                        try {
                            $reverse_result = self::reverse_geocode( $lat, $lng, $geocode_service );
                            $location_meta['label'] = $reverse_result['address'];
                        } catch ( Exception $e ) {
                            $location_meta['label'] = "{$lat}, {$lng}";
                        }
                    } else {
                        $location_meta['label'] = "{$lat}, {$lng}";
                    }

                    return $location_meta;

                } catch ( Exception $e ) {
                    // If grid lookup fails, return coordinates anyway
                    $location_meta = [
                        'lng' => $lng,
                        'lat' => $lat,
                        'label' => "{$lat}, {$lng}",
                        'source' => 'csv_import',
                        'geocoding_note' => 'Could not assign to location grid: ' . $e->getMessage()
                    ];

                    return $location_meta;
                }
            }

            // Check if it's coordinates in decimal format (lat,lng)
            if ( preg_match( '/^-?\d+\.?\d*\s*,\s*-?\d+\.?\d*$/', $value ) ) {
                $coords = array_map( 'trim', explode( ',', $value ) );
                $lat = floatval( $coords[0] );
                $lng = floatval( $coords[1] );

                // Validate coordinates
                if ( $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180 ) {
                    throw new Exception( "Invalid coordinates: {$value}" );
                }

                // Try to get grid ID from coordinates
                try {
                    $grid_result = self::get_grid_id_from_coordinates( $lat, $lng, $country_code );

                    $location_meta = [
                        'lng' => $lng,
                        'lat' => $lat,
                        'source' => 'csv_import'
                    ];

                    if ( isset( $grid_result['grid_id'] ) ) {
                        $location_meta['grid_id'] = $grid_result['grid_id'];
                    }

                    if ( isset( $grid_result['level'] ) ) {
                        $location_meta['level'] = $grid_result['level'];
                    }

                    // Try to get address if geocoding service is available
                    if ( $geocode_service !== 'none' ) {
                        try {
                            $reverse_result = self::reverse_geocode( $lat, $lng, $geocode_service );
                            $location_meta['label'] = $reverse_result['address'];
                        } catch ( Exception $e ) {
                            $location_meta['label'] = "{$lat}, {$lng}";
                        }
                    } else {
                        $location_meta['label'] = "{$lat}, {$lng}";
                    }

                    return $location_meta;

                } catch ( Exception $e ) {
                    // If grid lookup fails, return coordinates anyway
                    $location_meta = [
                        'lng' => $lng,
                        'lat' => $lat,
                        'label' => "{$lat}, {$lng}",
                        'source' => 'csv_import',
                        'geocoding_note' => 'Could not assign to location grid: ' . $e->getMessage()
                    ];

                    return $location_meta;
                }
            }

            // Treat as address
            if ( $geocode_service === 'none' ) {
                return [
                    'label' => $value,
                    'source' => 'csv_import',
                    'geocoding_note' => 'Address not geocoded - no geocoding service selected'
                ];
            }

            // Geocode the address
            $geocode_result = self::geocode_address( $value, $geocode_service, $country_code );

            $location_meta = [
                'lng' => $geocode_result['lng'],
                'lat' => $geocode_result['lat'],
                'label' => $geocode_result['formatted_address'],
                'source' => 'csv_import'
            ];

            // Try to get grid ID from the geocoded coordinates
            try {
                $grid_result = self::get_grid_id_from_coordinates(
                    $geocode_result['lat'],
                    $geocode_result['lng'],
                    $country_code
                );

                if ( isset( $grid_result['grid_id'] ) ) {
                    $location_meta['grid_id'] = $grid_result['grid_id'];
                }

                if ( isset( $grid_result['level'] ) ) {
                    $location_meta['level'] = $grid_result['level'];
                }
            } catch ( Exception $e ) {
                $location_meta['geocoding_note'] = 'Could not assign to location grid: ' . $e->getMessage();
            }

            return $location_meta;

        } catch ( Exception $e ) {
            error_log( 'DT CSV Import Geocoding Error: ' . $e->getMessage() );

            return [
                'label' => $value,
                'source' => 'csv_import',
                'geocoding_error' => $e->getMessage()
            ];
        }
    }

    /**
     * Parse DMS (Degrees, Minutes, Seconds) coordinates to decimal degrees
     * Supports formats like: 35°50′40.9″N, 103°27′7.5″E
     */
    private static function parse_dms_coordinates( $value ) {
        // Pattern to match DMS coordinates
        // Supports various symbols: ° ' " or d m s or deg min sec
        // Supports both N/S/E/W notation
        // Using Unicode-aware regex with proper UTF-8 character classes
        $pattern = '/^
            \s*
            (\d{1,3})                           # degrees
            [°d]?                               # degree symbol (optional) - ° or d
            \s*
            (\d{1,2})                           # minutes
            [′\'m]?                             # minute symbol (optional) - ′ or \' or m
            \s*
            ([\d.]+)                            # seconds (can be decimal)
            [″"s]?                              # second symbol (optional) - ″ or " or s
            \s*
            ([NSEW])                            # direction (required)
            \s*,?\s*                            # comma separator (optional)
            (\d{1,3})                           # degrees
            [°d]?                               # degree symbol (optional) - ° or d
            \s*
            (\d{1,2})                           # minutes
            [′\'m]?                             # minute symbol (optional) - ′ or \' or m
            \s*
            ([\d.]+)                            # seconds (can be decimal)
            [″"s]?                              # second symbol (optional) - ″ or " or s
            \s*
            ([NSEW])                            # direction (required)
            \s*
        $/xu';

        if ( !preg_match( $pattern, $value, $matches ) ) {
            return null;
        }

        $lat_deg = intval( $matches[1] );
        $lat_min = intval( $matches[2] );
        $lat_sec = floatval( $matches[3] );
        $lat_dir = strtoupper( $matches[4] );

        $lng_deg = intval( $matches[5] );
        $lng_min = intval( $matches[6] );
        $lng_sec = floatval( $matches[7] );
        $lng_dir = strtoupper( $matches[8] );

        // Convert DMS to decimal degrees
        $lat = $lat_deg + ( $lat_min / 60 ) + ( $lat_sec / 3600 );
        $lng = $lng_deg + ( $lng_min / 60 ) + ( $lng_sec / 3600 );

        // Apply direction (negative for South and West)
        if ( $lat_dir === 'S' ) {
            $lat = -$lat;
        }
        if ( $lng_dir === 'W' ) {
            $lng = -$lng;
        }

        // Validate that we have proper direction indicators
        if ( !in_array( $lat_dir, [ 'N', 'S' ] ) || !in_array( $lng_dir, [ 'E', 'W' ] ) ) {
            return null;
        }

        // Validate ranges
        if ( $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180 ) {
            return null;
        }

        return [
            'lat' => $lat,
            'lng' => $lng
        ];
    }

    /**
     * Batch process multiple location values
     */
    public static function batch_process( $values, $geocode_service = 'none', $country_code = null ) {
        $results = [];
        $errors = [];

        foreach ( $values as $index => $value ) {
            try {
                $result = self::process_for_import( $value, $geocode_service, $country_code );
                $results[$index] = $result;

                // Add a small delay to avoid rate limiting
                if ( $geocode_service !== 'none' && count( $values ) > 10 ) {
                    usleep( 100000 ); // 0.1 second delay
                }
            } catch ( Exception $e ) {
                $errors[$index] = [
                    'value' => $value,
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'results' => $results,
            'errors' => $errors
        ];
    }
}
