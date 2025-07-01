<?php
/**
 * DT Import Field Handlers
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class DT_CSV_Import_Field_Handlers {

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
}
