<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

if ( ! class_exists( 'Location_Grid_Meta' ) ) {
    class Location_Grid_Meta
    {
        public static function convert_ip_result_to_location_grid_meta( $ip_result) {
            if (empty( $ip_result['longitude'] )) {
                return false;
            }
            $geocoder = new Location_Grid_Geocoder();

            // prioritize the smallest unit
            if ( !empty( $ip_result['city'] )) {
                $label = $ip_result['city'] . ', ' . $ip_result['region_name'] . ', ' . $ip_result['country_name'];
                $level = "district";
            } elseif ( !empty( $ip_result['region_name'] )) {
                $label = $ip_result['region_name'] . ', ' . $ip_result['country_name'];
                $level = "region";
            } elseif ( !empty( $ip_result['country_name'] )) {
                $label = $ip_result['country_name'];
                $level = "country";
            } elseif ( !empty( $ip_result['continent_name'] )) {
                $label = $ip_result['continent_name'];
                $level = 'world';
            } else {
                $label = '';
                $level = '';
            }

            $grid_id = $geocoder->get_grid_id_by_lnglat( $ip_result['longitude'], $ip_result['latitude'], $ip_result['country_code'] );

            if (empty( $label )) {
                $admin0_grid_id = Disciple_Tools_Mapping_Queries::get_by_grid_id( $grid_id['admin0_grid_id'] );
                $label = $grid_id['name'] . ', ' . $admin0_grid_id['name'];
            }

            $location_grid_meta = [
                'lng' => $ip_result['longitude'] ?? '',
                'lat' => $ip_result['latitude'] ?? '',
                'level' => $level,
                'label' => $label,
                'source' => 'ip',
                'grid_id' => $grid_id['grid_id'] ?? '',
            ];

            self::validate_location_grid_meta( $location_grid_meta );

            return $location_grid_meta;
        }

        /**
         * This filter validates the format of the location grid meta.
         *
         * @param null $location_grid_meta Can be called null and will return array.
         * @return array Returns a structured array in the location grid meta structure.
         */
        public static function validate_location_grid_meta( &$location_grid_meta = null): array
        {

            if (empty( $location_grid_meta )) {
                $location_grid_meta = [
                    'grid_meta_id' => '',
                    'post_id' => '',
                    'post_type' => '',
                    'grid_id' => '',
                    'lng' => '',
                    'lat' => '',
                    'level' => '',
                    'source' => '',
                    'label' => '',
                ];
            } else if (is_serialized( $location_grid_meta )) {
                $location_grid_meta = maybe_unserialize( $location_grid_meta );
            }

            $filtered_array = [];

            $filtered_array['grid_meta_id'] = isset( $location_grid_meta['grid_meta_id'] ) ? sanitize_text_field( wp_unslash( $location_grid_meta['grid_meta_id'] ) ) : '';
            $filtered_array['post_id'] = isset( $location_grid_meta['post_id'] ) ? sanitize_text_field( wp_unslash( $location_grid_meta['post_id'] ) ) : '';
            $filtered_array['post_type'] = isset( $location_grid_meta['post_type'] ) ? sanitize_text_field( wp_unslash( $location_grid_meta['post_type'] ) ) : '';
            $filtered_array['grid_id'] = isset( $location_grid_meta['grid_id'] ) ? sanitize_text_field( wp_unslash( $location_grid_meta['grid_id'] ) ) : '';
            $filtered_array['lng'] = isset( $location_grid_meta['lng'] ) ? sanitize_text_field( wp_unslash( $location_grid_meta['lng'] ) ) : '';
            $filtered_array['lat'] = isset( $location_grid_meta['lat'] ) ? sanitize_text_field( wp_unslash( $location_grid_meta['lat'] ) ) : '';
            $filtered_array['level'] = ( isset( $location_grid_meta['level'] ) && !empty( $location_grid_meta['level'] ) ) ? sanitize_text_field( wp_unslash( $location_grid_meta['level'] ) ) : 'place';
            $filtered_array['source'] = ( isset( $location_grid_meta['source'] ) && !empty( $location_grid_meta['source'] ) ) ? sanitize_text_field( wp_unslash( $location_grid_meta['source'] ) ) : 'user';
            $filtered_array['label'] = isset( $location_grid_meta['label'] ) ? sanitize_text_field( wp_unslash( $location_grid_meta['label'] ) ) : '';

            return $filtered_array;
        }

        public static function get_location_grid_meta_by_id( $grid_meta_id) {
            global $wpdb;
            return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->dt_location_grid_meta WHERE grid_meta_id = %d", $grid_meta_id ), ARRAY_A );
        }

        public static function add_location_grid_meta( $post_id, array $location_grid_meta, $postmeta_id_location_grid = null) {
            global $wpdb;
            $geocoder = new Location_Grid_Geocoder();

            self::validate_location_grid_meta( $location_grid_meta );

            if ( !isset( $location_grid_meta['lng'] ) || !isset( $location_grid_meta['lat'] )) {
                return new WP_Error( __METHOD__, 'Missing required lng or lat' );
            }

            if (empty( $location_grid_meta['grid_id'] )) {
                $grid = $geocoder->get_grid_id_by_lnglat( $location_grid_meta['lng'], $location_grid_meta['lat'] );
                if ($grid) {
                    $location_grid_meta['grid_id'] = $grid['grid_id'];
                } else {
                    return new WP_Error( __METHOD__, 'Invalid lng or lat. Unable to retrieve grid_id' );
                }
            }

            if ( !$postmeta_id_location_grid) {
                $postmeta_id_location_grid = add_post_meta( $post_id, 'location_grid', $location_grid_meta['grid_id'] );
            }
            if ( !$postmeta_id_location_grid) {
                return new WP_Error( __METHOD__, 'Unable to create location_grid post meta and retrieve a key.' );
            }

            $data = [
                'post_id' => $post_id,
                'post_type' => empty( $location_grid_meta['post_type'] ) ? get_post_type( $post_id ) : $location_grid_meta['post_type'],
                'postmeta_id_location_grid' => $postmeta_id_location_grid,
                'grid_id' => $location_grid_meta['grid_id'],
                'lng' => $location_grid_meta['lng'],
                'lat' => $location_grid_meta['lat'],
                'level' => empty( $location_grid_meta['level'] ) ? 'place' : $location_grid_meta['level'],
                'source' => empty( $location_grid_meta['source'] ) ? 'user' : $location_grid_meta['source'],
                'label' => $location_grid_meta['label'],
            ];

            $format = [
                '%d',
                '%s',
                '%d',
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            ];

            $wpdb->insert( $wpdb->dt_location_grid_meta, $data, $format );
            if ( !$wpdb->insert_id) {
                delete_meta( $postmeta_id_location_grid );
                return new WP_Error( __METHOD__, 'Failed to insert location_grid_meta record.' );
            }

            $location_grid_meta_mid = add_post_meta( $post_id, 'location_grid_meta', $wpdb->insert_id );
            if ( !$location_grid_meta_mid) {
                delete_meta( $postmeta_id_location_grid );
                self::delete_location_grid_meta( $post_id, 'grid_meta_id', $wpdb->insert_id );
                return new WP_Error( __METHOD__, 'Failed to add location_grid_meta' );
            }

            return $wpdb->insert_id;

        }

        public static function delete_location_grid_meta( int $post_id, $type, int $value, array $existing_post = null) {
            global $wpdb;

            $status = false;

            if ('all' === $type) {
                $wpdb->delete( $wpdb->dt_location_grid_meta, [ "post_id" => $post_id ] );
                $status = true;
            }

            if ($value) {

                switch ($type) {
                    case 'grid_meta_id':
                        $postmeta_id_location_grid = $wpdb->get_var( $wpdb->prepare( "SELECT postmeta_id_location_grid FROM $wpdb->dt_location_grid_meta WHERE grid_meta_id = %d", $value ) );

                        delete_metadata_by_mid( 'post', $postmeta_id_location_grid );
                        $wpdb->delete($wpdb->dt_location_grid_meta, [
                            "post_id" => $post_id,
                            "grid_meta_id" => $value
                        ]);
                        delete_post_meta( $post_id, "location_grid_meta", $value );
                        $status = true;
                        break;

                    default:
                        break;
                }
            }

            return $status;
        }

        public static function add_user_location_grid_meta( $user_id, $location_grid_meta, $postmeta_id_location_grid = null ) {

            global $wpdb;
            $geocoder = new Location_Grid_Geocoder();

            self::validate_location_grid_meta( $location_grid_meta );

            if ( !isset( $location_grid_meta['lng'] ) || !isset( $location_grid_meta['lat'] ) ){
                return new WP_Error( __METHOD__, 'Missing required lng or lat' );
            }

            if ( empty( $location_grid_meta['grid_id'] )) {
                if ( $location_grid_meta['level'] === 'country' ) {
                    $location_grid_meta['level'] = 'admin0';
                } else if ( $location_grid_meta['level'] === 'region' ) {
                    $location_grid_meta['level'] = 'admin1';
                }
                $grid = $geocoder->get_grid_id_by_lnglat( $location_grid_meta['lng'], $location_grid_meta['lat'], null, $location_grid_meta['level'] );
                if ($grid) {
                    $location_grid_meta['grid_id'] = $grid['grid_id'];
                    $location_grid_meta['post_type'] = 'users';
                } else {
                    return new WP_Error( __METHOD__, 'Invalid lng or lat. Unable to retrieve grid_id' );
                }
            }

            if ( !$postmeta_id_location_grid) {
                $postmeta_id_location_grid = add_user_meta( $user_id, $wpdb->prefix . 'location_grid', $location_grid_meta['grid_id'] );
            }
            if ( !$postmeta_id_location_grid) {
                return new WP_Error( __METHOD__, 'Unable to create location_grid post meta and retrieve a key.' );
            }

            $data = [
                'post_id' => $user_id,
                'post_type' => 'users',
                'postmeta_id_location_grid' => $postmeta_id_location_grid,
                'grid_id' => $location_grid_meta['grid_id'],
                'lng' => $location_grid_meta['lng'],
                'lat' => $location_grid_meta['lat'],
                'level' => empty( $location_grid_meta['level'] ) ? 'place' : $location_grid_meta['level'],
                'source' => empty( $location_grid_meta['source'] ) ? 'user' : $location_grid_meta['source'],
                'label' => $location_grid_meta['label'],
            ];

            $format = [
                '%d',
                '%s',
                '%d',
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            ];

            $wpdb->insert( $wpdb->dt_location_grid_meta, $data, $format );
            if ( !$wpdb->insert_id) {
                delete_user_meta( $user_id, $wpdb->prefix . 'location_grid', $location_grid_meta['grid_id'] );
                return new WP_Error( __METHOD__, 'Failed to insert location_grid_meta record.' );
            }

            $location_grid_meta_mid = add_user_meta( $user_id, $wpdb->prefix . 'location_grid_meta', $wpdb->insert_id );
            if ( !$location_grid_meta_mid) {
                delete_user_meta( $user_id, $wpdb->prefix . 'location_grid_meta', $wpdb->insert_id );
                self::delete_user_location_grid_meta( $user_id, 'grid_meta_id', $wpdb->insert_id ); // @todo verify if needed
                return new WP_Error( __METHOD__, 'Failed to add location_grid_meta' );
            }

            return $wpdb->insert_id;

        }

        public static function delete_user_location_grid_meta( int $user_id, $type, $grid_meta_id, array $existing_post = null ) {
            global $wpdb;

            $status = false;

            if ('all' === $type) {
                $wpdb->delete( $wpdb->dt_location_grid_meta, [
                    "post_id" => $user_id,
                    "post_type" => "users"
                ] );
                $status = true;
            }

            if ($grid_meta_id) {

                switch ($type) {
                    case 'grid_meta_id':
                        $postmeta_id_location_grid = $wpdb->get_var( $wpdb->prepare( "SELECT postmeta_id_location_grid FROM $wpdb->dt_location_grid_meta WHERE grid_meta_id = %d", $grid_meta_id ) );

                        delete_metadata_by_mid( 'user', $postmeta_id_location_grid );
                        $wpdb->delete($wpdb->dt_location_grid_meta, [
                            "post_id" => $user_id,
                            "grid_meta_id" => $grid_meta_id
                        ]);
                        $wpdb->delete($wpdb->usermeta, [
                            "user_id" => $user_id,
                            "meta_key" => $wpdb->prefix . "location_grid_meta",
                            "meta_value" => $grid_meta_id
                        ]);
                        $status = true;
                        break;

                    default:
                        break;
                }
            }

            return $status;
        }

    }
}
