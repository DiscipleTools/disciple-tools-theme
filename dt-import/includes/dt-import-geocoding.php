<?php
/**
 * DT Import Location Processing
 * Handles location field processing for CSV import
 * Note: Actual geocoding is handled by DT core system via geolocate flag
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class DT_CSV_Import_Geocoding {

    /**
     * Check if geocoding is available in DT core
     */
    public static function is_geocoding_available() {
        // Check if any geocoding service is available
        $google_available = class_exists( 'Disciple_Tools_Google_Geocode_API' ) && Disciple_Tools_Google_Geocode_API::get_key();
        $mapbox_available = class_exists( 'DT_Mapbox_API' ) && DT_Mapbox_API::get_key();

        return $google_available || $mapbox_available;
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
     * Process location data for import
     * Note: This doesn't do actual geocoding - it just formats data and sets geolocate flag
     * DT core handles the actual geocoding when geolocate=true
     */
    public static function process_for_import( $value, $geocode_service = 'none', $country_code = null, $preview_mode = false ) {
        // Convert boolean geocoding flag to service name for backwards compatibility
        if ( $geocode_service === true || $geocode_service === 'auto' ) {
            $geocode_service = self::is_geocoding_available() ? 'enabled' : 'none';
        } elseif ( $geocode_service === false ) {
            $geocode_service = 'none';
        }

        $value = trim( $value );

        if ( empty( $value ) ) {
            return null;
        }

        $result = null;

        // In preview mode, don't perform actual geocoding - just return the raw values formatted for display
        if ( $preview_mode ) {
            $result = self::process_for_preview( $value );
        } else {
            // Check if value contains multiple addresses separated by semicolons
            if ( strpos( $value, ';' ) !== false ) {
                $result = self::process_multiple_locations_for_dt( $value, $geocode_service, $country_code );
            } else {
                // Process single location using DT's built-in capabilities
                $result = self::process_single_location_for_dt( $value, $geocode_service, $country_code );
            }
        }

        return $result;
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
     * Process multiple locations using DT's built-in capabilities
     */
    private static function process_multiple_locations_for_dt( $value, $geocode_service = 'none', $country_code = null ) {
        $addresses = explode( ';', $value );
        $location_grid_values = [];
        $address_values = [];

        foreach ( $addresses as $index => $address ) {
            $address = trim( $address );
            if ( empty( $address ) ) {
                continue;
            }

            try {
                $location_result = self::process_single_location_for_dt( $address, $geocode_service, $country_code );

                if ( $location_result !== null ) {
                    if ( isset( $location_result['grid_id'] ) ) {
                        // Grid ID
                        $location_grid_values[] = [
                            'grid_id' => $location_result['grid_id']
                        ];
                    } elseif ( isset( $location_result['lat'], $location_result['lng'] ) ) {
                        // Coordinates
                        $location_grid_values[] = [
                            'lng' => $location_result['lng'],
                            'lat' => $location_result['lat']
                        ];
                    } elseif ( isset( $location_result['address_for_geocoding'] ) ) {
                        // Address for geocoding - let DT core handle the geocoding
                        $address_values[] = [
                            'value' => $location_result['address_for_geocoding'],
                            'geolocate' => $geocode_service !== 'none'
                        ];
                    }
                }
            } catch ( Exception $e ) {
                // Add as regular address without geocoding
                $address_values[] = [
                    'value' => $address,
                    'geolocate' => false
                ];
            }
        }

        $result = [];

        // Return location_grid_meta if we have grid IDs or coordinates
        if ( !empty( $location_grid_values ) ) {
            $result['location_grid_meta'] = [
                'values' => $location_grid_values,
                'force_values' => false
            ];
        }

        // Return contact_address if we have addresses to geocode
        if ( !empty( $address_values ) ) {
            $result['contact_address'] = $address_values;
        }

        return $result;
    }

    /**
     * Process a single location using DT's built-in capabilities
     */
    private static function process_single_location_for_dt( $value, $geocode_service = 'none', $country_code = null ) {
        try {
            // If it's a numeric grid ID, return grid format
            if ( is_numeric( $value ) ) {
                $grid_id = intval( $value );
                if ( self::validate_grid_id( $grid_id ) ) {
                    return [
                        'grid_id' => $grid_id
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

                return [
                    'lng' => $lng,
                    'lat' => $lat
                ];
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

                return [
                    'lng' => $lng,
                    'lat' => $lat
                ];
            }

            // Treat as address - let DT handle the geocoding
            return [
                'address_for_geocoding' => $value
            ];

        } catch ( Exception $e ) {
            // Return as regular address without geocoding on error
            return [
                'address_for_geocoding' => $value
            ];
        }
    }

    /**
     * Parse DMS (Degrees, Minutes, Seconds) coordinates to decimal degrees
     * Supports formats like: 35°50′40.9″N, 103°27′7.5″E
     */
    public static function parse_dms_coordinates( $value ) {
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
}
