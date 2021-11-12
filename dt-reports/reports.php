<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly


/**
 * @see   Disciple_Tools_Activity_Log_API::insert
 * @since 0.1.0
 *
 * @param array $args
 *
 * @return mixed
 */
function dt_report_insert( $args = [] ) {
    return Disciple_Tools_Reports::insert( $args );
}

/**
 * Disciple_Tools_Reports
 * This handles the insert and other functions for the table _dt_reports and _dt_reportmeta tables
 */
class Disciple_Tools_Reports
{

    /**
     * Insert Report into _reports and _reportmeta tables
     *
     * @param array $args
     * @return false|int
     */
    public static function insert( array $args ) {
        global $wpdb;
        dt_write_log( $args );
        if ( ! isset( $args['type'] ) ){
            return false;
        }

        if ( ! isset( $args['post_id'] ) ){
            $args['post_id'] = 0;
        }

        $args = wp_parse_args(
            $args,
            [
                'user_id' => null,
                'parent_id' => null,
                'post_id' => null,
                'post_type' => null,
                'type' => null, // required
                'subtype' => null,
                'payload' => null,
                'value' => 1,
                'lng' => null,
                'lat' => null,
                'level' => null,
                'label' => null,
                'grid_id' => null,
                'time_begin' => null,
                'time_end' => time(),
                'hash' => null,
                'meta_input' => [],
            ]
        );

        $args['hash'] = hash( 'sha256', maybe_serialize( $args ) );
        $args['timestamp'] = time();

        // Make sure for non duplicate.
        $check_duplicate = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT
                    `id`
                FROM
                    `$wpdb->dt_reports`
                WHERE hash = %s;",
                $args['hash']
            )
        );

        if ( $check_duplicate ) {
            return false;
        }

        $wpdb->insert(
            $wpdb->dt_reports,
            [
                'user_id' => $args['user_id'],
                'parent_id' => $args['parent_id'],
                'post_id' => $args['post_id'],
                'post_type' => $args['post_type'],
                'type' => $args['type'],
                'subtype' => $args['subtype'],
                'payload' => maybe_serialize( $args['payload'] ),
                'value' => $args['value'],
                'lng' => $args['lng'],
                'lat' => $args['lat'],
                'level' => $args['level'],
                'label' => $args['label'],
                'grid_id' => $args['grid_id'],
                'time_begin' => $args['time_begin'],
                'time_end' => $args['time_end'],
                'timestamp' => time(),
                'hash' => $args['hash'],
            ],
            [
                '%d', // user_id
                '%d', // parent_id
                '%d', // post_id
                '%s', // post_type
                '%s', // type
                '%s', // subtype
                '%s', // payload
                '%d', // value
                '%f', // lng
                '%f', // lat
                '%s', // level
                '%s', // label
                '%d', // grid_id
                '%d', // time_begin
                '%d', // time_end
                '%d', // timestamp
                '%s', // hash
            ]
        );

        $report_id = $wpdb->insert_id;

        if ( !empty( $args['meta_input'] ) ) {
            foreach ( $args['meta_input'] as $meta_key => $meta_value ) {
                self::add_meta( $report_id, $meta_key, $meta_value );
            }
        }

        if ( ! defined( 'SHORTINIT' ) ) {
            dt_activity_insert(
                [
                    'action'            => 'create_report',
                    'object_type'       => $args['type'],
                    'object_subtype'    => empty( $args['subtype'] ) ? ' ' : $args['subtype'],
                    'object_id'         => $args['post_id'],
                    'object_name'       => 'report',
                    'meta_id'           => $report_id ,
                    'meta_key'          => ' ',
                    'object_note'       => __( 'Added new report', 'disciple_tools' ),
                ]
            );

            // Final action on insert.
            do_action( 'dt_insert_report', $args );
        }

        return $report_id;
    }

    /**
     * Update Report
     *
     * @param array $args
     * @return false|int
     */
    public static function update( array $args ) {
        global $wpdb;

        if ( ! isset( $args['id'] ) ){
            return false;
        }

        $current_report = self::get( $args['id'], 'id' );
        $current_report['meta'] = self::get_meta( $args['id'] );

        $args = wp_parse_args( $args, $current_report );

        if ( isset( $args['hash'] ) ){
            unset( $args['hash'] );
        }
        $args['hash'] = hash( 'sha256', maybe_serialize( $args ) );

        $wpdb->update(
            $wpdb->dt_reports,
            [
                'user_id' => $args['user_id'],
                'post_id' => $args['post_id'],
                'post_type' => $args['post_type'],
                'type' => $args['type'],
                'subtype' => $args['subtype'],
                'payload' => maybe_serialize( $args['payload'] ),
                'value' => $args['value'],
                'lng' => $args['lng'],
                'lat' => $args['lat'],
                'level' => $args['level'],
                'label' => $args['label'],
                'grid_id' => $args['grid_id'],
                'time_begin' => $args['time_begin'],
                'time_end' => $args['time_end'],
                'timestamp' => time(),
                'hash' => $args['hash'],
            ],
            [
                'id' => $args['id'],
            ],
            [
                '%d', // user_id
                '%d', // post_id
                '%s', // post_type
                '%s', // type
                '%s', // subtype
                '%s', // payload
                '%d', // value
                '%f', // lng
                '%f', // lat
                '%s', // level
                '%s', // label
                '%d', // grid_id
                '%d', // time_begin
                '%d', // time_end
                '%d', // timestamp
                '%s', // hash
            ]
        );

        $report_id = $wpdb->insert_id;

        if ( !empty( $args['meta_input'] ) ) {
            foreach ( $args['meta_input'] as $meta_key => $meta_value ) {
                self::add_meta( $report_id, $meta_key, $meta_value );
            }
        }

        dt_activity_insert(
            [
                'action' => 'update_report',
                'object_type' => $args['type'],
                'object_subtype' => $args['subtype'],
                'object_id' => $args['post_id'],
                'object_name' => 'report',
                'meta_id'      => $report_id,
                'meta_key'     => ' ',
                'object_note'  => __( 'Updated report', 'disciple_tools' ),
            ]
        );

        // Final action on insert.
        do_action( 'dt_update_report', $args );

        return $report_id;
    }

    /**
     * Get report
     *
     * @param $value
     * @param $type string (post_id, id)
     * @return array|false|object|null
     */
    public static function get( $value, $type ) : array {
        global $wpdb;
        $report = [];
        switch ( $type ){

            case 'post_id':
                $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->dt_reports WHERE post_id = %s", $value ), ARRAY_A );
                if ( ! empty( $results ) ) {
                    foreach ( $results as $index => $result ){
                        if ( isset( $result['payload'] ) ){
                            $results[$index]['payload'] = maybe_unserialize( $result['payload'] );
                        }
                    }
                    $report = $results;
                }
                break;

            case 'id':
                $results = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->dt_reports WHERE id = %s", $value ), ARRAY_A );
                if ( ! empty( $results ) ) {
                    $results['payload'] = maybe_unserialize( $results['payload'] );
                    $report = $results;
                }
                break;

            /**
             * Returns by row id and includes any meta into ['meta_input']
             */
            case 'id_and_meta':
                $results = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->dt_reports WHERE id = %s", $value ), ARRAY_A );
                if ( ! empty( $results ) ) {
                    $results['payload'] = maybe_unserialize( $results['payload'] );
                    $report = $results;
                }
                $meta = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->dt_reportmeta WHERE report_id = %s", $value ), ARRAY_A );
                $report['meta_input'] = $meta;
                break;
        }

        return $report;
    }

    /**
     * Delete report
     *
     * @param $report_id
     */
    public static function delete( $report_id ){
        global $wpdb;
        $wpdb->delete(
            $wpdb->dt_reportmeta,
            [ 'report_id' => $report_id ],
            [ '%d' ]
        );
        return $wpdb->delete(
            $wpdb->dt_reports,
            [ 'id' => $report_id ],
            [ '%d' ]
        );
    }

    /**
     * Add Report Metadata
     *
     * @since 0.1.0
     *
     * @param  int    $report_id
     * @param  string $meta_key
     * @param  string $meta_value
     *
     * @return void
     */
    public static function add_meta( $report_id, $meta_key, $meta_value ) {
        global $wpdb;
        $wpdb->insert(
            $wpdb->dt_reportmeta,
            [
                'report_id'  => $report_id,
                'meta_key'   => $meta_key,
                'meta_value' => $meta_value,
            ],
            [ '%d', '%s', '%s' ]
        );
    }

    /**
     * Get all meta for a record
     *
     * @param $report_id
     * @return array
     */
    public static function get_meta( $report_id ) : array {
        global $wpdb;
        $meta = [];
        $results = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM $wpdb->dt_reportmeta WHERE report_id = %s", $report_id ), ARRAY_A );
        if ( ! empty( $results ) ) {
            foreach ( $results as $result ){
                $meta[$result['meta_key']] = $result[$result['meta_value']];
            }
        }
        return $meta;
    }

    public static function update_meta( $report_id, $meta_key, $meta_value ){
        global $wpdb;
        $wpdb->update(
            $wpdb->dt_reportmeta,
            [
                'meta_value' => $meta_value,
            ],
            [
                'report_id'  => $report_id,
                'meta_key'   => $meta_key,
            ],
            [ '%s' ],
            [ '%d', '%s' ]
        );


    }


    /**
     * Gets a single report including metadata by the report id
     *
     * @param  $id     int     (required) This is the report id.
     *
     * @return array
     */
    public function get_report_by_id( $id ) {
        global $wpdb;

        // Get all report detals
        $results = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT
                    *
                FROM
                    `$wpdb->dt_reports`
                WHERE
                    `id` = %s",
                $id
            ),
            ARRAY_A
        );

        // Get all metadata values for the report
        $meta_input = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT
                    *
                FROM
                    `$wpdb->dt_reportmeta`
                WHERE
                    `report_id` = %s",
                $id
            ),
            ARRAY_A
        );

        // Add meta_input to the report array and return
        $results['meta_input'] = $meta_input;

        return $results;
    }

    /**
     * Get meta_value using $id and $key
     *
     * @param $id
     * @param $key
     *
     * @return mixed
     */
    public function get_report_meta( $id, $meta_key ) {
        global $wpdb;

        // Get all metadata values for the report
        $meta_value = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT
                    meta_value
                FROM
                    `$wpdb->dt_reportmeta`
                WHERE
                    `report_id` = %s
                    AND `meta_key` = %s",
                $id,
                $meta_key
            ),
            ARRAY_A
        );

        return $meta_value['meta_value'];
    }


    //'parent_id' => null,
    //'post_id' => 0,
    //'post_type' => null,
    //'type' => null, // required
    //'subtype' => null,
    //'payload' => null,
    //'value' => 1,
    //'lng' => null,
    //'lat' => null,
    //'level' => null,
    //'label' => null,
    //'grid_id' => null,
    //'time_begin' => null,
    //'time_end' => time(),
    //'hash' => null,

    /**
     *
     * @param $data_array
     * @return array
     */
    public static function insert_public_log( $data_array ) {

        global $wpdb;
        if ( ! isset( $wpdb->dt_reports ) ) {
            $wpdb->dt_reports = $wpdb->prefix . 'dt_reports';
        }
        if ( ! isset( $wpdb->dt_location_grid ) ) {
            $wpdb->dt_location_grid = $wpdb->prefix . 'dt_location_grid';
        }
        if ( ! isset( $wpdb->dt_location_grid_meta ) ) {
            $wpdb->dt_location_grid_meta = $wpdb->prefix . 'dt_location_grid_meta';
        }

        $process_status = [];
        $process_status['start'] = microtime( true );

        foreach ( $data_array as $activity ) {

            if ( ! ( isset( $activity['type'] ) && ! empty( $activity['type'] ) ) ) {
                $process_status[] = [
                    'error' => 'no type',
                    'data' => $activity
                ];
                continue;
            }

            $data = [
                'user_id' => null,
                'parent_id' => null,
                'post_id' => null,
                'post_type' => null,
                'type' => null,
                'subtype' => null,
                'payload' => null,
                'value' => 1,
                'lng' => null,
                'lat' => null,
                'level' => null,
                'label' => null,
                'grid_id' => null,
                'time_begin' => null,
                'time_end' => null,
                'hash' => null,
            ];

            $data = wp_parse_args( $activity, $data );

            // LOCATION TYPE
            if ( ! ( isset( $activity['location_type'] ) && ! empty( $activity['location_type'] ) ) ) {
                $process_status[] = [
                    'error' => 'no location_type found. must be grid, ip, lnglat, complete, no_location',
                    'data' => $activity
                ];
                continue;
            }
            $location_type = sanitize_text_field( wp_unslash( $activity['location_type'] ) );

            // LOCATION VALUE
            if ( ! isset( $activity['location_value'] ) ) {
                $process_status[] = [
                    'error' => 'no location value found in array',
                    'data' => $activity
                ];
                continue;
            }

            // PAYLOAD
            if ( ! isset( $activity['payload'] ) || empty( $activity['payload'] ) ) {
                $activity['payload'] = [];
            }
            $data['payload'] = dt_recursive_sanitize_array( $activity['payload'] );

            // PREPARE LOCATION DATA
            switch ( $location_type ) {
                case 'ip':  /* @param string expects string containing ip address */
                    $data['payload']['location_type'] = 'ip';

                    // validate expected fields
                    if ( ! ( isset( $activity['location_value'] ) && ! empty( $activity['location_value'] ) && ! is_array( $activity['location_value'] ) ) ) {
                        $process_status[] = [
                            'error' => 'did not find all elements of location_value. (ip) location type must have an ip address as a string.',
                            'data' => $activity
                        ];
                        continue 2;
                    }

                    // sanitize string
                    $ip_address = sanitize_text_field( wp_unslash( $activity['location_value'] ) );

                    $ipstack = new DT_Ipstack_API();
                    $response = $ipstack::geocode_ip_address( $ip_address );
                    $lgm = $ipstack::convert_ip_result_to_location_grid_meta( $response );
                    if ( $lgm ) {
                        // set lng and lat
                        $data['lng'] = $lgm['lng'];
                        $data['lat'] = $lgm['lat'];
                        $data['grid_id'] = $lgm['grid_id'];
                        $data['level'] = $lgm['level'];
                        $data['label'] = $lgm['label'];
                    }

                    // set label and country
                    $country = $ipstack::parse_raw_result( $response, 'country_name' );
                    $data['payload']['country'] = $country;
                    $data['payload']['unique_id'] = hash( 'sha256', $ip_address ); // required so that same activity from same location but different people does not count as duplicate.

                    break;
                case 'grid':  /* @param string  expects string containing grid_id */
                    $data['payload']['location_type'] = 'grid';

                    // validate expected fields
                    if ( ! ( isset( $activity['location_value'] ) && ! empty( $activity['location_value'] ) ) ) {
                        $process_status[] = [
                            'error' => 'did not find all elements of location_value. (grid) location type must have (grid_id) number from location_grid database.',
                            'data' => $activity
                        ];
                        continue 2;
                    }

                    $geocoder = new Location_Grid_Geocoder();
                    $grid_response = $geocoder->query_by_grid_id( sanitize_text_field( wp_unslash( $activity['location_value'] ) ) );
                    if ( ! empty( $grid_response ) ) {
                        $data['lng'] = $grid_response['longitude'];
                        $data['lat'] = $grid_response['latitude'];
                        $data['level'] = $grid_response['level_name'];
                        $data['grid_id'] = $grid_response['grid_id'];
                        $data['label'] = $geocoder->_format_full_name( $grid_response );
                        $data['payload']['country'] = $grid_response['admin0_name'];
                    }

                    break;
                case 'lnglat': /* @param array expects associative array containing (lng, lat, level) strings */
                    $data['payload']['location_type'] = 'lnglat';

                    // validate expected fields
                    if ( ! (
                        is_array( $activity['location_value'] )
                        && isset( $activity['location_value']['lng'] ) && ! empty( $activity['location_value']['lng'] )
                        && isset( $activity['location_value']['lat'] ) && ! empty( $activity['location_value']['lat'] )
                        && isset( $activity['location_value']['level'] )
                    ) ) {
                        $process_status[] = [
                            'error' => 'did not find all elements of location_value. (lnglat) location type must have (lng, lat, level) array elements.',
                            'data' => $activity
                        ];
                        continue 2;
                    }

                    // build location section
                    $data['lng'] = sanitize_text_field( wp_unslash( $activity['location_value']['lng'] ) );
                    $data['lat'] = sanitize_text_field( wp_unslash( $activity['location_value']['lat'] ) );
                    $data['level'] = sanitize_text_field( wp_unslash( $activity['location_value']['level'] ) );

                    if ( isset( $activity['location_value']['label'] ) && ! empty( $activity['location_value']['label'] ) ) {
                        $data['label'] = sanitize_text_field( wp_unslash( $activity['location_value']['label'] ) );
                    }

                    $geocoder = new Location_Grid_Geocoder();
                    $grid_response = $geocoder->get_grid_id_by_lnglat( $data['lng'], $data['lat'], null, $data['level'] );
                    if ( ! empty( $grid_response ) ) {
                        $data['level'] = $grid_response['level_name'];
                        $data['grid_id'] = $grid_response['grid_id'];
                        $data['payload']['country'] = $grid_response['admin0_name'];

                        if ( empty( $data['label'] ) ) {
                            $data['label'] = $geocoder->_format_full_name( $grid_response );
                        }
                    }

                    break;
                case 'complete': /* @param array expects array with (lng, lat, level, label, grid_id) strings */
                    $data['payload']['location_type'] = 'complete';

                    // validate expected fields
                    if ( ! (
                        is_array( $activity['location_value'] )
                        && isset( $activity['location_value']['lng'] )
                        && isset( $activity['location_value']['lat'] )
                        && isset( $activity['location_value']['level'] )
                        && isset( $activity['location_value']['label'] )
                        && isset( $activity['location_value']['grid_id'] )
                    ) ) {
                        $process_status[] = [
                            'error' => 'did not find all elements of location_value. (Complete) location type must have (lng, lat, level, label, grid_id) array elements.',
                            'data' => $activity
                        ];
                        continue 2;
                    }

                    // build location section
                    $data['lng'] = sanitize_text_field( wp_unslash( $activity['location_value']['lng'] ) );
                    $data['lat'] = sanitize_text_field( wp_unslash( $activity['location_value']['lat'] ) );
                    $data['level'] = sanitize_text_field( wp_unslash( $activity['location_value']['level'] ) );
                    $data['label'] = sanitize_text_field( wp_unslash( $activity['location_value']['label'] ) );
                    $data['grid_id'] = sanitize_text_field( wp_unslash( $activity['location_value']['grid_id'] ) );

                    break;
                case 'no_location':
                    $data['lng'] = null;
                    $data['lat'] = null;
                    $data['level'] = null;
                    $data['label'] = null;
                    $data['grid_id'] = null;
                    break;
                default:
                    $process_status[] = [
                        'error' => 'did not find location_type. Must be ip, grid, lnglat, or complete.',
                        'data' => $activity
                    ];
                    continue 2;
            }

            $data['payload'] = serialize( $data['payload'] );

            $data['hash'] = hash( 'sha256', serialize( $data ) );

            $data['time_end'] = ( empty( $params['timestamp'] ) ) ? time() : $params['timestamp'];
            $data['timestamp'] = ( empty( $params['timestamp'] ) ) ? time() : $params['timestamp'];

            // test if duplicate
            $time = new DateTime();
            $time->modify( '-30 minutes' );
            $past_stamp = $time->format( 'U' );
            $results = $wpdb->get_col( $wpdb->prepare( "SELECT hash FROM $wpdb->dt_reports WHERE timestamp > %d", $past_stamp ) );
            if ( array_search( $data['hash'], $results ) !== false ) {
                $process_status[] = [
                    'error' => 'Duplicate',
                    'data' => $activity
                ];
                continue;
            }

            // insert log record
            $process_status[] = self::insert( $data );
        } // end loop

        $process_status['stop'] = microtime( true );

        return $process_status;
    }
}
