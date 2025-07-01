<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly


/**
 * @see   Disciple_Tools_Activity_Log_API::insert
 * @since 0.1.0
 *
 * @param array $args
 * @param bool $save_hash
 * @param bool $duplicate_check
 *
 * @return false|int
 */
function dt_report_insert( array $args = [], bool $save_hash = true, bool $duplicate_check = true ) {
    return Disciple_Tools_Reports::insert( $args, $save_hash, $duplicate_check );
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
     * @param bool $save_hash
     * @param bool $duplicate_check
     * @return false|int
     */
    public static function insert( array $args, bool $save_hash = true, bool $duplicate_check = true, $create_activity_log = false ) {
        global $wpdb;
        if ( ! isset( $args['type'] ) ){
            return false;
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
                'time_end' => null,
                'hash' => null,
                'meta_input' => [],
            ]
        );

        if ( $save_hash ) {
            if ( empty( $args['hash'] ) ) {
                $args['hash'] = hash( 'sha256', maybe_serialize( $args ) );
            }

            if ( $duplicate_check ) {
                // Make sure no duplicate is found.
                $duplicate_found = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT
                    `id`
                FROM
                    `$wpdb->dt_reports`
                WHERE hash = %s AND hash IS NOT NULL;",
                        $args['hash']
                    )
                );
                if ( $duplicate_found ) {
                    return false;
                }
            }
        }

        $args['timestamp'] = time();

        if ( is_array( $args['payload'] ) || is_object( $args['payload'] ) ) {
            $args['payload'] = serialize( $args['payload'] );
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
                'payload' => $args['payload'],
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
        if ( ! $report_id ) {
            return $report_id;
        }
        else {
            $args['id'] = $report_id;
        }

        if ( ! empty( $args['meta_input'] ) ) {
            foreach ( $args['meta_input'] as $meta_key => $meta_value ) {
                self::add_meta( $report_id, $meta_key, $meta_value );
            }
        }

        if ( $create_activity_log ){
            dt_activity_insert(
                [
                    'action' => 'create_report',
                    'object_type' => $args['type'],
                    'object_subtype' => empty( $args['subtype'] ) ? ' ' : $args['subtype'],
                    'object_id' => $args['post_id'],
                    'object_name' => 'report',
                    'meta_id' => $report_id,
                    'meta_key' => ' ',
                    'object_note' => __( 'Added new report', 'disciple_tools' ),
                ]
            );
        }

        // Final action on insert.
        do_action( 'dt_insert_report', $args );

        return $report_id;
    }

    /**
     * Update Report
     *
     * @param array $args
     * @return false|int
     */
    public static function update( array $args, $create_activity_log = false ) {
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

        if ( $create_activity_log ){
            dt_activity_insert(
                [
                    'action' => 'update_report',
                    'object_type' => $args['type'],
                    'object_subtype' => $args['subtype'],
                    'object_id' => $args['post_id'],
                    'object_name' => 'report',
                    'meta_id' => $report_id,
                    'meta_key' => ' ',
                    'object_note' => __( 'Updated report', 'disciple_tools' ),
                ]
            );
        }

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
     * List reports filtered by where clauses
     *
     * @param array $where An assoc array of key, value pairs
     *
     * The value parts of the array should be arrays, with 'value' => $value. This allows for adding functionality like operators etc.
     */
    public static function list( $where = [] ) {
        global $wpdb;
        $report = [];

        $sql = '';
        $args = [];

        foreach ( $where as $key => $details ) {
            if ( !is_array( $details ) || !isset( $details['value'] ) ) {
                return new WP_Error( 'missing-value-type', 'value key is missing for ' . $key );
            }
            $value = $details['value'];
            $new_sql = '';
            if ( !empty( $sql ) ) {
                $new_sql .= ' AND';
            } else {
                $new_sql .= ' WHERE';
            }

            $new_sql .= $wpdb->prepare( ' %1$s = ', $key );

            if ( is_numeric( $value ) ) {
                $new_sql .= '%d';
            } else if ( is_string( $value ) ) {
                $new_sql .= '%s';
            } else {
                return new WP_Error( 'invalid-value-type', ' $value is of type ' . gettype( $value ) . ' but should be a string or number' );
            }
            $sql .= $new_sql;
            $args[] = $value;
        }

        //phpcs:ignore
        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->dt_reports $sql", $args ), ARRAY_A );
        if ( ! empty( $results ) ) {
            foreach ( $results as $index => $result ){
                if ( isset( $result['payload'] ) ){
                    $results[$index]['payload'] = maybe_unserialize( $result['payload'] );
                }
            }
            $report = $results;
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
                $meta[$result['meta_key']] = $result['meta_value'];
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
}
