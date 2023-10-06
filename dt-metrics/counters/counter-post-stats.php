<?php
/**
 * Provides stats on post fields per time slot
 *
 * @package Disciple.Tools
 * @version 0.1.0
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Counter_Post_Stats
 */
class DT_Counter_Post_Stats extends Disciple_Tools_Counter_Base
{

    /**
     * Constructor function.
     *
     * @access public
     * @since  0.1.0
     */
    public function __construct() {
        parent::__construct();
    } // End __construct()

    public static function get_changed_post_counts( $post_type, $field, $start, $end, $by_month ) {
        global $wpdb;

        $added_post_changes = [];
        $deleted_post_changes = [];

        $field_type = DT_Posts::get_post_field_settings( $post_type )[$field]['type'] ?? '';

        $time_unit_sql = $by_month ? 'MONTH( FROM_UNIXTIME( log.hist_time ) )' : 'YEAR( FROM_UNIXTIME( log.hist_time ) )';

        switch ( $field_type ) {
            case 'date':

                $post_changes = [];
                $post_changes_sql = [
                    'posts.added > posts.deleted', // Added
                    'posts.added <= posts.deleted' // Deleted
                ];

                foreach ( $post_changes_sql as $changes_sql ) {
                    $post_changes[] = $wpdb->get_results( $wpdb->remove_placeholder_escape( $wpdb->prepare(
                        "
                                SELECT
                                    COUNT( posts.id ) AS count,
                                    posts.time_unit AS time_unit
                                FROM (
                                    SELECT
                                        p.ID AS id,
                                        p.post_title AS name,
                                        SUM( if ( log.object_note LIKE %s, 1, 0 ) ) AS added,
                                        SUM( if ( log.object_note LIKE %s, 1, 0 ) ) AS deleted,
                                        %1s AS time_unit
                                    FROM $wpdb->dt_activity_log AS log
                                    INNER JOIN $wpdb->posts AS p ON p.ID = log.object_id
                                    WHERE log.object_type = %s
                                        AND log.object_subtype = %s
                                        AND log.meta_key = %s
                                        AND log.field_type = %s
                                        AND log.hist_time BETWEEN %d AND %d
                                    GROUP BY p.ID, time_unit
                                ) posts
                                WHERE %1s
                                GROUP BY posts.time_unit
                                ORDER BY posts.time_unit ASC
                            ", '%Added%', '%deleted%', $time_unit_sql, $post_type, $field, $field, $field_type, $start, $end, $changes_sql
                    ) ) );
                }

                if ( count( $post_changes ) === 2 ) {
                    $added_post_changes = $post_changes[0];
                    $deleted_post_changes = $post_changes[1];
                }
                break;
            case 'tags':
            case 'multi_select':

                $post_changes = [];
                $post_changes_sql = [
                    'posts.added > posts.deleted', // Added
                    'posts.added <= posts.deleted' // Deleted
                ];

                foreach ( $post_changes_sql as $changes_sql ) {
                    $post_changes[] = $wpdb->get_results( $wpdb->remove_placeholder_escape( $wpdb->prepare(
                        "
                            SELECT
                                COUNT( posts.id ) AS count,
                                posts.selection AS selection,
                                posts.time_unit AS time_unit
                            FROM (
                                SELECT
                                    p.ID AS id,
                                    p.post_title AS name,
                                    log.meta_value AS selection,
                                    SUM( if ( log.object_note LIKE %s, 1, 0 ) ) AS added,
                                    SUM( if ( log.object_note LIKE %s, 1, 0 ) ) AS deleted,
                                    %1s AS time_unit
                                FROM $wpdb->dt_activity_log AS log
                                INNER JOIN $wpdb->posts AS p ON p.ID = log.object_id
                                WHERE log.object_type = %s
                                    AND log.object_subtype = %s
                                    AND log.meta_key = %s
                                    AND log.field_type = %s
                                    AND log.hist_time BETWEEN %d AND %d
                                GROUP BY p.ID, selection, time_unit
                            ) posts
                            WHERE %1s
                            GROUP BY posts.selection, posts.time_unit
                            ORDER BY posts.time_unit ASC
                        ", '%Added%', '%deleted%', $time_unit_sql, $post_type, $field, $field, $field_type, $start, $end, $changes_sql
                    ) ) );
                }

                if ( count( $post_changes ) === 2 ) {
                    $added_post_changes = $post_changes[0];
                    $deleted_post_changes = $post_changes[1];
                }
                break;

            case 'key_select':

                $added_post_changes = $wpdb->get_results( $wpdb->remove_placeholder_escape( $wpdb->prepare(
                    "
                            SELECT
                                COUNT( posts.id ) AS count,
                                posts.selection AS selection,
                                posts.time_unit AS time_unit
                            FROM (
                                SELECT
                                    p.ID AS id,
                                    p.post_title AS name,
                                    log.meta_value AS selection,
                                    %1s AS time_unit
                                FROM $wpdb->dt_activity_log AS log
                                INNER JOIN $wpdb->posts AS p ON p.ID = log.object_id
                                WHERE log.object_type = %s
                                    AND log.object_subtype = %s
                                    AND log.meta_key = %s
                                    AND log.field_type = %s
                                    AND log.hist_time BETWEEN %d AND %d
                                GROUP BY p.ID, selection, time_unit
                            ) posts
                            GROUP BY posts.selection, posts.time_unit
                            ORDER BY posts.time_unit ASC
                        ", $time_unit_sql, $post_type, $field, $field, $field_type, $start, $end
                ) ) );

                break;

            case 'number':

                $added_post_changes = $wpdb->get_results( $wpdb->remove_placeholder_escape( $wpdb->prepare(
                    "
                            SELECT
                                COUNT( posts.id ) AS count,
                                posts.time_unit AS time_unit
                            FROM (
                                SELECT
                                    p.ID AS id,
                                    p.post_title AS name,
                                    %1s AS time_unit
                                FROM $wpdb->dt_activity_log AS log
                                INNER JOIN $wpdb->posts AS p ON p.ID = log.object_id
                                WHERE log.object_type = %s
                                    AND log.object_subtype = %s
                                    AND log.meta_key = %s
                                    AND log.meta_value != ''
                                    AND log.field_type = %s
                                    AND log.hist_time BETWEEN %d AND %d
                                GROUP BY p.ID, time_unit
                            ) posts
                            GROUP BY posts.time_unit
                            ORDER BY posts.time_unit ASC
                        ", $time_unit_sql, $post_type, $field, $field, $field_type, $start, $end
                ) ) );

                break;
        }

        return [
            'added' => $added_post_changes,
            'deleted' => $deleted_post_changes
        ];
    }

    /**
     * Return count of posts by date field with stats counted by
     * month
     */
    public static function get_number_field_by_month( string $post_type, string $field, int $year ) {
        global $wpdb;

        $start = mktime( 0, 0, 0, 1, 1, $year );
        $end = mktime( 24, 60, 60, 12, 31, $year );

        $results = $wpdb->get_results(
            $wpdb->prepare( "
                SELECT
                    MONTH( FROM_UNIXTIME( log.hist_time ) ) AS month,
                    SUM( log.meta_value ) AS count
                FROM $wpdb->posts AS p
                JOIN $wpdb->postmeta AS pm
                    ON p.ID = pm.post_id
                JOIN $wpdb->dt_activity_log AS log
                    ON log.object_id = p.ID
                    AND log.meta_key = %s
                WHERE p.post_type = %s
                    AND pm.meta_key = %s
                    AND log.meta_value = pm.meta_value
                    AND log.hist_time = (
                        SELECT MAX( log2.hist_time )
                        FROM $wpdb->dt_activity_log AS log2
                        WHERE log.meta_value = log2.meta_value
                        AND log.object_id = log2.object_id
                        AND log2.hist_time >= %s
                        AND log2.hist_time <= %s
                        AND log2.meta_key = %s
                    )
                    AND log.object_type = %s
                    AND log.hist_time >= %s
                    AND log.hist_time <= %s
                GROUP BY MONTH( FROM_UNIXTIME( log.hist_time ) )
                ORDER BY MONTH( FROM_UNIXTIME( log.hist_time ) )
            ", $field, $post_type, $field, $start, $end, $field, $post_type, $start, $end )
        );

        $cumulative_offset = self::get_number_field_cumulative_offsets( $post_type, $field, $start );

        return [
            'data' => $results,
            'cumulative_offset' => $cumulative_offset,
            'changes' => self::get_changed_post_counts( $post_type, $field, $start, $end, true )
        ];
    }



    public static function get_number_field_by_year( string $post_type, string $field ) {
        global $wpdb;

        $current_year = gmdate( 'Y' );
        $start = 0;
        $end = mktime( 24, 60, 60, 12, 31, $current_year );

        $results = $wpdb->get_results(
            $wpdb->prepare( "
                SELECT
                    YEAR( FROM_UNIXTIME( log.hist_time ) ) AS year,
                    SUM( log.meta_value ) AS count
                FROM $wpdb->posts AS p
                JOIN $wpdb->postmeta AS pm
                    ON p.ID = pm.post_id
                JOIN $wpdb->dt_activity_log AS log
                    ON log.object_id = p.ID
                    AND log.meta_key = %s
                WHERE p.post_type = %s
                    AND pm.meta_key = %s
                    AND log.meta_value = pm.meta_value
                    AND log.hist_time = (
                        SELECT MAX( log2.hist_time )
                        FROM $wpdb->dt_activity_log AS log2
                        WHERE log.meta_value = log2.meta_value
                        AND log.object_id = log2.object_id
                        AND log2.hist_time >= %s
                        AND log2.hist_time <= %s
                        AND log2.meta_key = %s
                    )
                    AND log.object_type = %s
                    AND log.hist_time >= %s
                    AND log.hist_time <= %s
                GROUP BY YEAR( FROM_UNIXTIME( log.hist_time ) )
                ORDER BY YEAR( FROM_UNIXTIME( log.hist_time ) )
            ", $field, $post_type, $field, $start, $end, $field, $post_type, $start, $end )
        );

        $cumulative_offset = self::get_number_field_cumulative_offsets( $post_type, $field, $start );

        return [
            'data' => $results,
            'cumulative_offset' => $cumulative_offset,
            'changes' => self::get_changed_post_counts( $post_type, $field, $start, $end, false )
        ];
    }

    /**
     * Return count of posts by date field with stats counted by
     * month
     */
    public static function get_date_field_by_month( string $post_type, string $field, int $year ) {
        global $wpdb;

        $start = mktime( 0, 0, 0, 1, 1, $year );
        $end = mktime( 24, 60, 60, 12, 31, $year );

        $results = [];
        $cumulative_offset = 0;

        if ( self::isPostField( $field ) ) {
            $results = $wpdb->get_results(
                $wpdb->prepare( "
                    SELECT
                        MONTH( %1s ) AS month,
                        COUNT( %1s ) AS count
                    FROM $wpdb->posts
                    WHERE post_type = %s
                        AND %1s >= %s
                        AND %1s <= %s
                    GROUP BY MONTH( %1s )
                    ORDER BY MONTH( %1s )
                ", $field, $field, $post_type, $field, gmdate( 'Y-m-d H:i:s', $start ), $field, gmdate( 'Y-m-d H:i:s', $end ), $field, $field )
            );

            $cumulative_offset = self::get_date_field_cumulative_offset( $post_type, $field, $start );

            return [
                'data' => $results,
                'cumulative_offset' => $cumulative_offset,
                'changes' => self::get_changed_post_counts( $post_type, $field, $start, $end, true )
            ];
        } else {
            $results = $wpdb->get_results(
                $wpdb->prepare( "
                    SELECT
                        MONTH( FROM_UNIXTIME( pm.meta_value ) ) AS month,
                        COUNT( pm.meta_value ) AS count
                    FROM $wpdb->posts AS p
                    INNER JOIN $wpdb->postmeta AS pm
                        ON p.ID = pm.post_id
                    WHERE p.post_type = %s
                        AND pm.meta_key = %s
                        AND pm.meta_value >= %s
                        AND pm.meta_value <= %s
                    GROUP BY MONTH( FROM_UNIXTIME( pm.meta_value ) )
                    ORDER BY MONTH( FROM_UNIXTIME( pm.meta_value ) )
                ", $post_type, $field, $start, $end
                )
            );

            $cumulative_offset = self::get_date_field_cumulative_offset( $post_type, $field, $start, $meta = true );

        }

        return [
            'data' => $results,
            'cumulative_offset' => $cumulative_offset,
            'changes' => self::get_changed_post_counts( $post_type, $field, $start, $end, true )
        ];
    }

    /**
     * Return count of posts by date field with stats counted by
     * year
     */
    public static function get_date_field_by_year( string $post_type, string $field ) {
        global $wpdb;

        $current_year = gmdate( 'Y' );
        $start = 0;
        $end = mktime( 24, 60, 60, 12, 31, $current_year );

        $results = [];
        if ( self::isPostField( $field ) ) {
            $results = $wpdb->get_results(
                $wpdb->prepare( "
                    SELECT
                        YEAR( %1s ) AS year,
                        COUNT( %1s ) AS count
                    FROM $wpdb->posts
                    WHERE post_type = %s
                        AND %1s >= %s
                        AND %1s <= %s
                    GROUP BY YEAR( %1s )
                    ORDER BY YEAR( %1s )
                ", $field, $field, $post_type, $field, gmdate( 'Y-m-d H:i:s', $start ), $field, gmdate( 'Y-m-d H:i:s', $end ), $field, $field )
            );
        } else {
            $results = $wpdb->get_results(
                $wpdb->prepare( "
                    SELECT
                        YEAR( FROM_UNIXTIME( pm.meta_value ) ) AS year,
                        COUNT( pm.meta_value ) AS count
                    FROM $wpdb->posts AS p
                    INNER JOIN $wpdb->postmeta AS pm
                        ON p.ID = pm.post_id
                    WHERE p.post_type = %s
                        AND pm.meta_key = %s
                        AND pm.meta_value >= %s
                        AND pm.meta_value <= %s
                    GROUP BY YEAR( FROM_UNIXTIME( pm.meta_value ) )
                    ORDER BY YEAR( FROM_UNIXTIME( pm.meta_value ) )
                ", $post_type, $field, $start, $end
                )
            );
        }

        return [
            'data' => $results,
            'changes' => self::get_changed_post_counts( $post_type, $field, $start, $end, false )
        ];
    }

    public static function get_multi_field_by_month( $post_type, $field, $year ) {
        global $wpdb;

        $start = mktime( 0, 0, 0, 1, 1, $year );
        $end = mktime( 24, 60, 60, 12, 31, $year );

        // get possible values of multi select field
        $multi_values = [];

        $field_settings = DT_Posts::get_post_field_settings( $post_type );
        $default_values = array_key_exists( $field, $field_settings ) ? $field_settings[$field]['default'] : [];

        if ( !empty( $default_values ) ) {
            $multi_values = array_keys( $default_values );
        } else {
            // there are no defaults hardcoded, so we will need to get them
            // from the metadata
            $multi_values = self::get_meta_values( $field );
        }

        // Build dynamic sql for counting meta_values
        $count_dynamic_values = array_map( function ( $value ) {
            return "COUNT( CASE WHEN log.meta_value = '" . esc_sql( $value ) . "' THEN log.meta_value END ) AS `" . esc_sql( $value ) . '`';
        }, $multi_values);
        $count_dynamic_values_query = implode( ', ', $count_dynamic_values );
        if ( strlen( $count_dynamic_values_query ) !== 0 ) {
            $count_dynamic_values_query = ", $count_dynamic_values_query";
        }

        $results = $wpdb->get_results(
            // phpcs:disable
            $wpdb->prepare( "
                SELECT
                    MONTH( FROM_UNIXTIME( log.hist_time ) ) AS month
                    $count_dynamic_values_query
                FROM $wpdb->posts AS p
                JOIN $wpdb->postmeta AS pm
                    ON p.ID = pm.post_id
                JOIN $wpdb->dt_activity_log AS log
                    ON log.object_id = p.ID
                    AND log.meta_key = %s
                WHERE p.post_type = %s
                    AND pm.meta_key = %s
                    AND log.meta_value = pm.meta_value
                    AND log.hist_time = (
                        SELECT MAX( log2.hist_time )
                        FROM $wpdb->dt_activity_log AS log2
                        WHERE log.meta_value = log2.meta_value
                        AND log.object_id = log2.object_id
                        AND log2.hist_time >= %s
                        AND log2.hist_time <= %s
                        AND log2.meta_key = %s
                    )
                    AND log.object_type = %s
                    AND log.hist_time >= %s
                    AND log.hist_time <= %s
                GROUP BY MONTH( FROM_UNIXTIME( log.hist_time ) )
                ORDER BY MONTH( FROM_UNIXTIME( log.hist_time ) )
            ", $field, $post_type, $field, $start, $end, $field, $post_type, $start, $end )
            // phpcs:enable
        );

        $cumulative_offset = self::get_multi_field_cumulative_offsets( $post_type, $field, $start, $multi_values );

        return [
            'data' => $results,
            'cumulative_offset' => $cumulative_offset,
            'changes' => self::get_changed_post_counts( $post_type, $field, $start, $end, true )
        ];
    }

    public static function get_multi_field_by_year( $post_type, $field ) {
        global $wpdb;

        $current_year = gmdate( 'Y' );
        $start = 0;
        $end = mktime( 24, 60, 60, 12, 31, $current_year );

        // get possible values of multi select field
        $multi_values = [];

        $field_settings = DT_Posts::get_post_field_settings( $post_type );
        $default_values = array_key_exists( $field, $field_settings ) ? $field_settings[$field]['default'] : [];

        if ( !empty( $default_values ) ) {
            $multi_values = array_keys( $default_values );
        } else {
            // there are no defaults hardcoded, so we will need to get them
            // from the metadata
            $multi_values = self::get_meta_values( $field );
        }

        $count_dynamic_values = array_map( function ( $value ) {
            return "COUNT( CASE WHEN log.meta_value = '" . esc_sql( $value ) . "' THEN log.meta_value END ) AS `" . esc_sql( $value ) . '`';
        }, $multi_values);
        $count_dynamic_values_query = implode( ', ', $count_dynamic_values );
        if ( strlen( $count_dynamic_values_query ) !== 0 ) {
            $count_dynamic_values_query = ", $count_dynamic_values_query";
        }

        $results = $wpdb->get_results(
            // phpcs:disable
            $wpdb->prepare( "
                SELECT
                    YEAR( FROM_UNIXTIME( log.hist_time ) ) AS year
                    $count_dynamic_values_query
                FROM $wpdb->posts AS p
                JOIN $wpdb->postmeta AS pm
                    ON p.ID = pm.post_id
                JOIN $wpdb->dt_activity_log AS log
                    ON log.object_id = p.ID
                    AND log.meta_key = %s
                WHERE p.post_type = %s
                    AND pm.meta_key = %s
                    AND log.meta_value = pm.meta_value
                    AND log.hist_time = (
                        SELECT MAX( log2.hist_time )
                        FROM $wpdb->dt_activity_log AS log2
                        WHERE log.meta_value = log2.meta_value
                        AND log.object_id = log2.object_id
                        AND log2.hist_time >= %s
                        AND log2.hist_time <= %s
                        AND log2.meta_key = %s
                    )
                    AND log.object_type = %s
                    AND log.hist_time >= %s
                    AND log.hist_time <= %s
                GROUP BY YEAR( FROM_UNIXTIME( log.hist_time ) )
                ORDER BY YEAR( FROM_UNIXTIME( log.hist_time ) )
            ", $field, $post_type, $field, $start, $end, $field, $post_type, $start, $end )
            // phpcs:enable
        );

        return [
            'data' => $results,
            'changes' => self::get_changed_post_counts( $post_type, $field, $start, $end, false )
        ];
    }

    public static function get_connection_field_by_state_month( $post_type, $field, $connection_type, $start, $end ) {
        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare( "
                SELECT
                    p.ID AS id,
                    p.post_title AS name,
                    SUM( if ( log.action = 'connected to', 1, 0 ) ) AS connected,
                    SUM( if ( log.action = 'disconnected from', 1, 0 ) ) AS disconnected,
                    MONTH( FROM_UNIXTIME( log.hist_time ) ) AS month
                FROM $wpdb->dt_activity_log AS log
                INNER JOIN $wpdb->posts AS p ON p.ID = log.object_id
                INNER JOIN $wpdb->posts as p2 ON p2.ID = log.meta_value
                WHERE log.object_type = %s
                    AND log.object_subtype = %s
                    AND log.meta_key = %s
                    AND log.hist_time BETWEEN %s AND %s
                GROUP BY MONTH( FROM_UNIXTIME( log.hist_time ) ), p.ID
                ORDER BY MONTH( FROM_UNIXTIME( log.hist_time ) )
            ", $post_type, $field, $connection_type, $start, $end ), ARRAY_A
        );
        foreach ( $results as $index => $result ){
            $results[$index]['state'] = $result['connected'] - $result['disconnected'];
        }
        return $results;
    }

    public static function get_connection_field_by_state_year( $post_type, $field, $connection_type, $start, $end ) {
        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare( "
                SELECT
                    p.ID AS id,
                    p.post_title AS name,
                    SUM( if ( log.action = 'connected to', 1, 0 ) ) AS connected,
                    SUM( if ( log.action = 'disconnected from', 1, 0 ) ) AS disconnected,
                    YEAR( FROM_UNIXTIME( log.hist_time ) ) AS year
                FROM $wpdb->dt_activity_log AS log
                INNER JOIN $wpdb->posts AS p ON p.ID = log.object_id
                INNER JOIN $wpdb->posts as p2 ON p2.ID = log.meta_value
                WHERE log.object_type = %s
                    AND log.object_subtype = %s
                    AND log.meta_key = %s
                    AND log.hist_time BETWEEN %s AND %s
                GROUP BY YEAR( FROM_UNIXTIME( log.hist_time ) ), p.ID
                ORDER BY YEAR( FROM_UNIXTIME( log.hist_time ) )
            ", $post_type, $field, $connection_type, $start, $end ), ARRAY_A
        );
        foreach ( $results as $index => $result ){
            $results[$index]['state'] = $result['connected'] - $result['disconnected'];
        }
        return $results;
    }

    public static function get_connection_field_cumulative_id_offsets( $post_type, $field, $connection_type, $start, $end ) {
        $offset = [];
        $offsets_from_origin = self::get_connection_field_by_state_year( $post_type, $field, $connection_type, $start, $end );
        foreach ( $offsets_from_origin ?? [] as $record ) {
            if ( !array_key_exists( $record['id'], $offset ) ) {
                $offset[$record['id']] = [
                    'offset' => 0,
                    'record' => $record
                ];
            }
            $offset[$record['id']]['offset'] += $record['state'];
        }

        return $offset;
    }

    public static function update_connection_field_cumulative_id_offsets( $offsets, $records, $date_type ) {
        $updated_offsets = [];
        $processed_offsets = [];
        foreach ( $records as $record ) {
            if ( !isset( $updated_offsets[$record[$date_type]] ) ) {
                $updated_offsets[$record[$date_type]] = [];
            }

            if ( !isset( $updated_offsets[$record[$date_type]][$record['id']] ) ) {
                $updated_offsets[$record[$date_type]][$record['id']] = $record;
            }

            // Apply offsets accordingly.
            if ( array_key_exists( $record['id'], $offsets ) && !in_array( $record['id'], $processed_offsets ) ) {
//                $updated_offsets[$record[$date_type]][$record['id']]['state'] = ( ( $offsets[$record['id']]['offset'] ) + ( $record['state'] ) );
                $processed_offsets[] = $record['id'];
            }
        }

        return $updated_offsets;
    }

    public static function get_connection_field_counts( $offsets, $date_group_records ) {

        // Determine cumulative totals.
        $cumulative_totals = [
            'cumulative_count' => 0,
        ];
        //state for each record starting out.
        $record_cumulative_states = [];
        foreach ( $offsets as $record_id => $values ){
            $record_cumulative_states[$record_id] = $values['offset'];
            //count the records with a connected state
            if ( $values['offset'] > 0 ){
                $cumulative_totals['cumulative_count']++;
            }
        }

        // Determine date group record counts.
        $date_group_record_counts = [];
        foreach ( $date_group_records ?? [] as $date_unit => $date_unit_array ) {
            $date_group_record_counts[$date_unit] = [
                'new_connected' => 0, //number of records that gained a new 'connected' state
                'connected' => 0,
                'new_disconnected' => 0, //number of records lost the 'connected' state
                'disconnected' => 0,
                'cumulative_count' => 0,
            ];
            //update the current state for each record
            foreach ( $date_unit_array ?? [] as $record ) {
                //if the record is now connected and previously was disconnected
                if ( empty( $record_cumulative_states[$record['id']] ) && !empty( $record['connected'] ) && !empty( $record['state'] ) ){
                    $date_group_record_counts[$date_unit]['new_connected']++;
                }
                if ( !empty( $record['connected'] ) ){
                    $date_group_record_counts[$date_unit]['connected']++;
                }
                //if the record was connected and is now disconnected
                if ( !empty( $record_cumulative_states[$record['id']] ) && !empty( $record['disconnected'] ) && empty( $record['state'] ) ){
                    $date_group_record_counts[$date_unit]['new_disconnected']++;
                }
                if ( !empty( $record['disconnected'] ) ){
                    $date_group_record_counts[$date_unit]['disconnected']++;
                }
                //update the record state
                $record_cumulative_states[$record['id']] = ( $record_cumulative_states[$record['id']] ?? 0 ) + $record['state'];
            }
            //count the records in connected state
            foreach ( $record_cumulative_states as $state ){
                if ( $state > 0 ){
                    $date_group_record_counts[$date_unit]['cumulative_count']++;
                }
            }
        }

        return [
            'cumulative_totals' => $cumulative_totals,
            'records' => $date_group_record_counts
        ];
    }

    public static function get_connection_field_by_month( $post_type, $field, $connection_type, $year ) {
        $start = mktime( 0, 0, 0, 1, 1, $year );
        $end = mktime( 24, 60, 60, 12, 31, $year );

        // Determine id offsets from start of time to start of date range.
        $offsets = self::get_connection_field_cumulative_id_offsets( $post_type, $field, $connection_type, 0, $start );

        // Fetch id states for given date range.
        $monthly_states = self::get_connection_field_by_state_month( $post_type, $field, $connection_type, $start, $end );

        // Update id offsets based on given date range activity.
        $updated_offset_states = self::update_connection_field_cumulative_id_offsets( $offsets, $monthly_states, 'month' );

        // Determine various counts.
        $result_counts = self::get_connection_field_counts( $offsets, $updated_offset_states );

        return [
            'data' => $result_counts
        ];
    }

    public static function get_connection_field_by_year( $post_type, $field, $connection_type ) {
        $current_year = gmdate( 'Y' );
        $start = 0;
        $end = mktime( 24, 60, 60, 12, 31, $current_year );

        // Determine id offsets from start of time to start of date range.
        $offsets = self::get_connection_field_cumulative_id_offsets( $post_type, $field, $connection_type, 0, $start );

        // Fetch id states for given date range.
        $yearly_states = self::get_connection_field_by_state_year( $post_type, $field, $connection_type, $start, $end );

        // Update id offsets based on given date range activity.
        $updated_offset_states = self::update_connection_field_cumulative_id_offsets( $offsets, $yearly_states, 'year' );

        // Determine various counts.
        $result_counts = self::get_connection_field_counts( $offsets, $updated_offset_states );

        return [
            'data' => $result_counts
        ];
    }

    /**
     * Get the year of the earliest post in the db.
     *
     * This can then be used in date pickers etc.
     */
    public static function get_earliest_year() {
        global $wpdb;
        $result = $wpdb->get_var("
                SELECT MIN( year )
                FROM (
                    SELECT
                        MIN( YEAR( FROM_UNIXTIME( log.meta_value ) ) ) AS year
                    FROM
                        $wpdb->dt_activity_log AS log
                    WHERE log.field_type = 'date'
                        AND log.meta_value REGEXP '^[1-9][0-9]+$'
                    UNION
                    SELECT
                        MIN( YEAR( p2pmeta.meta_value ) ) AS year
                    FROM
                        $wpdb->p2pmeta AS p2pmeta
                    WHERE p2pmeta.meta_key = 'date'
                ) AS subQuery
            " );

        $current_year = gmdate( 'Y' );
        $year = $result ? intval( $result ) : intval( $current_year );

        return $year;
    }

    private static function get_meta_values( $field ) {
        global $wpdb;
        $results = $wpdb->get_results(
            $wpdb->prepare( "
                SELECT
                    DISTINCT( meta_value ) AS value
                FROM $wpdb->postmeta
                WHERE
                    meta_key = %s
            ", $field )
        );
        $meta_values = array_map( function ( $result ) {
            return $result->value;
        }, $results );
        return $meta_values;
    }

    private static function isPostField( $field ) {
        global $wpdb;
        $post_fields = $wpdb->get_col( "DESC $wpdb->posts", 0 );
        return in_array( $field, $post_fields, true );
    }

    private static function get_date_field_cumulative_offset( $post_type, $field, $timestamp, $meta = false ) {
        global $wpdb;

        $total = 0;

        if ( $meta ) {
            $total = $wpdb->get_var(
                $wpdb->prepare( "
                    SELECT
                        COUNT( pm.meta_value ) AS count
                    FROM $wpdb->posts AS p
                    INNER JOIN $wpdb->postmeta AS pm
                        ON p.ID = pm.post_id
                    WHERE p.post_type = %s
                        AND pm.meta_key = %s
                        AND pm.meta_value <= %s
                ", $post_type, $field, $timestamp
                )
            );
        } else {
            $total = $wpdb->get_var(
                $wpdb->prepare( "
                    SELECT
                        COUNT( %1s ) AS total
                    FROM $wpdb->posts
                    WHERE post_type = %s
                        AND %1s <= %s
                ", $field, $post_type, $field, gmdate( 'Y-m-d H:i:s', $timestamp ) )
            );
        }

        return intval( $total );
    }

    private static function get_multi_field_cumulative_offsets( $post_type, $field, $timestamp, $multi_values ) {
        global $wpdb;

        $count_dynamic_values = array_map( function ( $value ) {
            return "COUNT( CASE WHEN log.meta_value = '" . esc_sql( $value ) . "' THEN log.meta_value END ) AS `" . esc_sql( $value ) . '`';
        }, $multi_values);
        $count_dynamic_values_query = implode( ', ', $count_dynamic_values );

        $results = $wpdb->get_row(
            // phpcs:disable
            $wpdb->prepare( "
                SELECT
                    $count_dynamic_values_query
                FROM $wpdb->posts AS p
                JOIN $wpdb->postmeta AS pm
                    ON p.ID = pm.post_id
                JOIN $wpdb->dt_activity_log AS log
                    ON log.object_id = p.ID
                    AND log.meta_key = %s
                WHERE p.post_type = %s
                    AND pm.meta_key = %s
                    AND log.meta_value = pm.meta_value
                    AND log.hist_time = (
                        SELECT MAX( log2.hist_time )
                        FROM $wpdb->dt_activity_log AS log2
                        WHERE log.meta_value = log2.meta_value
                        AND log.object_id = log2.object_id
                        AND log2.hist_time <= %s
                        AND log2.meta_key = %s
                    )
                    AND log.object_type = %s
                    AND log.hist_time <= %s
            ", $field, $post_type, $field, $timestamp, $field, $post_type, $timestamp )
            // phpcs:enable
        );

        return $results;
    }

    private static function get_number_field_cumulative_offsets( $post_type, $field, $timestamp ) {
        global $wpdb;

        $results = $wpdb->get_row(
            $wpdb->prepare( "
                SELECT SUM( log.meta_value ) AS count
                FROM $wpdb->posts AS p
                JOIN $wpdb->postmeta AS pm
                    ON p.ID = pm.post_id
                JOIN $wpdb->dt_activity_log AS log
                    ON log.object_id = p.ID
                    AND log.meta_key = %s
                WHERE p.post_type = %s
                    AND pm.meta_key = %s
                    AND log.meta_value = pm.meta_value
                    AND log.hist_time = (
                        SELECT MAX( log2.hist_time )
                        FROM $wpdb->dt_activity_log AS log2
                        WHERE log.meta_value = log2.meta_value
                        AND log.object_id = log2.object_id
                        AND log2.hist_time <= %s
                        AND log2.meta_key = %s
                    )
                    AND log.object_type = %s
                    AND log.hist_time <= %s
            ", $field, $post_type, $field, $timestamp, $field, $post_type, $timestamp )
        );

        return $results->count;
    }

    public static function get_posts_by_field_in_date_range( $post_type, $field, $args = [] ){
        global $wpdb;

        $field_settings = DT_Posts::get_post_field_settings( $post_type );
        if ( isset( $field_settings[$field]['type'] ) ){

            $limit = $args['limit'] ?? 100;

            // Prepare SQL statements to be executed.
            $total = null;
            $results = [];
            $field_type = $field_settings[$field]['type'];
            switch ( $field_type ){
                case 'tags':
                case 'multi_select':
                case 'key_select':
                    $start = $args['start'] ?? 0;
                    $end = $args['end'] ?? time();
                    $key_query = !empty( $args['key'] ) ? "AND pm.meta_value = '". $args['key'] ."'" : '';

                    // phpcs:disable
                    $results = $wpdb->get_results(
                        $wpdb->prepare( "
                            SELECT DISTINCT
                                p.ID AS id, p.post_title AS name, pm.meta_value AS value
                            FROM $wpdb->posts AS p
                            JOIN $wpdb->postmeta AS pm
                                ON p.ID = pm.post_id
                            JOIN $wpdb->dt_activity_log AS log
                                ON log.object_id = p.ID
                                AND log.meta_key = %s
                            WHERE p.post_type = %s
                                AND pm.meta_key = %s
                                AND log.meta_value = pm.meta_value
                                AND log.hist_time = (
                                    SELECT MAX( log2.hist_time )
                                    FROM $wpdb->dt_activity_log AS log2
                                    WHERE log.meta_value = log2.meta_value
                                    AND log.object_id = log2.object_id
                                    AND log2.hist_time >= %s
                                    AND log2.hist_time <= %s
                                    AND log2.meta_key = %s
                                )
                                AND log.object_type = %s
                                AND log.hist_time >= %s
                                AND log.hist_time <= %s
                                $key_query
                            LIMIT %d
                            ", $field, $post_type, $field, $start, $end, $field, $post_type, $start, $end, $limit ) );
                    $total = $wpdb->get_results(
                        $wpdb->prepare( "
                            SELECT COUNT(DISTINCT
                                p.ID, p.post_title, pm.meta_value) AS total
                            FROM $wpdb->posts AS p
                            JOIN $wpdb->postmeta AS pm
                                ON p.ID = pm.post_id
                            JOIN $wpdb->dt_activity_log AS log
                                ON log.object_id = p.ID
                                AND log.meta_key = %s
                            WHERE p.post_type = %s
                                AND pm.meta_key = %s
                                AND log.meta_value = pm.meta_value
                                AND log.hist_time = (
                                    SELECT MAX( log2.hist_time )
                                    FROM $wpdb->dt_activity_log AS log2
                                    WHERE log.meta_value = log2.meta_value
                                    AND log.object_id = log2.object_id
                                    AND log2.hist_time >= %s
                                    AND log2.hist_time <= %s
                                    AND log2.meta_key = %s
                                )
                                AND log.object_type = %s
                                AND log.hist_time >= %s
                                AND log.hist_time <= %s
                                $key_query
                            ", $field, $post_type, $field, $start, $end, $field, $post_type, $start, $end, $limit ), ARRAY_N );
                    // phpcs:enable
                    break;
                case 'date':
                    $start = $args['start'] ?? 0;
                    $end = $args['end'] ?? time();

                    if ( self::isPostField( $field ) ) {
                        $results = $wpdb->get_results(
                            $wpdb->prepare( "
                            SELECT DISTINCT
                                p.ID AS id, p.post_title AS name
                            FROM $wpdb->posts AS p
                            WHERE post_type = %s
                                AND %1s >= %s
                                AND %1s <= %s
                            LIMIT %d
                        ", $post_type, $field, gmdate( 'Y-m-d H:i:s', $start ), $field, gmdate( 'Y-m-d H:i:s', $end ), $limit )
                        );
                        $total = $wpdb->get_results(
                            $wpdb->prepare( "
                            SELECT COUNT(DISTINCT
                                p.ID, p.post_title) AS total
                            FROM $wpdb->posts AS p
                            WHERE post_type = %s
                                AND %1s >= %s
                                AND %1s <= %s
                        ", $post_type, $field, gmdate( 'Y-m-d H:i:s', $start ), $field, gmdate( 'Y-m-d H:i:s', $end ) ), ARRAY_N
                        );
                    } else {
                        $results = $wpdb->get_results(
                            $wpdb->prepare( "
                            SELECT DISTINCT
                                p.ID AS id, p.post_title AS name
                            FROM $wpdb->posts AS p
                            INNER JOIN $wpdb->postmeta AS pm
                                ON p.ID = pm.post_id
                            WHERE p.post_type = %s
                                AND pm.meta_key = %s
                                AND pm.meta_value >= %s
                                AND pm.meta_value <= %s
                            LIMIT %d
                            ", $post_type, $field, $start, $end, $limit )
                        );
                        $total = $wpdb->get_results(
                            $wpdb->prepare( "
                            SELECT COUNT(DISTINCT
                                p.ID, p.post_title) AS total
                            FROM $wpdb->posts AS p
                            INNER JOIN $wpdb->postmeta AS pm
                                ON p.ID = pm.post_id
                            WHERE p.post_type = %s
                                AND pm.meta_key = %s
                                AND pm.meta_value >= %s
                                AND pm.meta_value <= %s
                            ", $post_type, $field, $start, $end, $limit ), ARRAY_N
                        );
                    }
                    break;
                case 'number':
                    $start = $args['start'] ?? 0;
                    $end = $args['end'] ?? time();

                    $results = $wpdb->get_results(
                        $wpdb->prepare( "
                            SELECT DISTINCT
                                p.ID AS id, p.post_title AS name, pm.meta_value AS value
                            FROM $wpdb->posts AS p
                            JOIN $wpdb->postmeta AS pm
                                ON p.ID = pm.post_id
                            JOIN $wpdb->dt_activity_log AS log
                                ON log.object_id = p.ID
                                AND log.meta_key = %s
                            WHERE p.post_type = %s
                                AND pm.meta_key = %s
                                AND log.meta_value = pm.meta_value
                                AND log.hist_time = (
                                    SELECT MAX( log2.hist_time )
                                    FROM $wpdb->dt_activity_log AS log2
                                    WHERE log.meta_value = log2.meta_value
                                    AND log.object_id = log2.object_id
                                    AND log2.hist_time >= %s
                                    AND log2.hist_time <= %s
                                    AND log2.meta_key = %s
                                )
                                AND log.object_type = %s
                                AND log.hist_time >= %s
                                AND log.hist_time <= %s
                            ", $field, $post_type, $field, $start, $end, $field, $post_type, $start, $end )
                    );

                    $total = $wpdb->get_results(
                        $wpdb->prepare( "
                            SELECT COUNT(DISTINCT
                                p.ID, p.post_title, pm.meta_value) AS total
                            FROM $wpdb->posts AS p
                            JOIN $wpdb->postmeta AS pm
                                ON p.ID = pm.post_id
                            JOIN $wpdb->dt_activity_log AS log
                                ON log.object_id = p.ID
                                AND log.meta_key = %s
                            WHERE p.post_type = %s
                                AND pm.meta_key = %s
                                AND log.meta_value = pm.meta_value
                                AND log.hist_time = (
                                    SELECT MAX( log2.hist_time )
                                    FROM $wpdb->dt_activity_log AS log2
                                    WHERE log.meta_value = log2.meta_value
                                    AND log.object_id = log2.object_id
                                    AND log2.hist_time >= %s
                                    AND log2.hist_time <= %s
                                    AND log2.meta_key = %s
                                )
                                AND log.object_type = %s
                                AND log.hist_time >= %s
                                AND log.hist_time <= %s
                            ", $field, $post_type, $field, $start, $end, $field, $post_type, $start, $end, $limit ), ARRAY_N
                    );

                    break;
                case 'connection':
                    $start = $args['start'] ?? 0;
                    $end = $args['end'] ?? time();
                    $p2p_type = $field_settings[$field]['p2p_key'] ?? null;
                    $key = $args['key'] ?? 'cumulative';

                    if ( !empty( $p2p_type ) ){

                        if ( $key === 'cumulative' ){
                            $results = $wpdb->get_results(
                                $wpdb->prepare( "
                                    SELECT
                                        posts.id as id,
                                        posts.name as name
                                    FROM (
                                        SELECT
                                            p.ID AS id,
                                            p.post_title AS name,
                                            SUM( if ( log.action = 'connected to', 1, 0 ) ) AS connected,
                                            SUM( if ( log.action = 'disconnected from', 1, 0 ) ) AS disconnected
                                        FROM $wpdb->dt_activity_log AS log
                                        INNER JOIN $wpdb->posts AS p ON p.ID = log.object_id
                                        INNER JOIN $wpdb->posts as p2 ON p2.ID = log.meta_value
                                        WHERE log.object_type = %s
                                            AND log.object_subtype = %s
                                            AND log.meta_key = %s
                                            AND log.hist_time < %s
                                        GROUP BY p.ID
                                    ) posts
                                    WHERE
                                        posts.connected > posts.disconnected
                                    LIMIT %d
                                ", $post_type, $field, $p2p_type, $end, $limit ), ARRAY_A
                            );
                        } else {
                            // Determine id offsets from start of time to start of clicked date range.
                            $results = $wpdb->get_results(
                                $wpdb->prepare( "
                                    SELECT
                                        p.ID AS id,
                                        p.post_title AS name
                                    FROM $wpdb->dt_activity_log AS log
                                    INNER JOIN $wpdb->posts AS p ON p.ID = log.object_id
                                    INNER JOIN $wpdb->posts as p2 ON p2.ID = log.meta_value
                                    WHERE log.object_type = %s
                                        AND log.object_subtype = %s
                                        AND log.meta_key = %s
                                        AND log.hist_time BETWEEN %s AND %s
                                        AND log.action = %s
                                    GROUP BY p.ID
                                    LIMIT %d
                            ", $post_type, $field, $p2p_type, $start, $end, $key === 'connected' ? 'connected to' : 'disconnected from', $limit ), ARRAY_A
                            );
                        }
                    }
                    break;
                default:
                    break;
            }

            return [
                'total' => $total[0][0] ?? null,
                'data' => $results
            ];
        }
        return [];
    }

    public static function get_posts_by_field_in_date_range_changes( $post_type, $field, $args = [] ){
        global $wpdb;

        $field_settings = DT_Posts::get_post_field_settings( $post_type );
        if ( isset( $field_settings[$field]['type'] ) ){

            $start = $args['start'] ?? 0;
            $end = $args['end'] ?? time();
            $key = $args['key'] ?? 'added';
            $limit = $args['limit'] ?? 100;

            // Prepare SQL statements to be executed.
            $results = [];
            $field_type = $field_settings[$field]['type'];
            switch ( $field_type ) {
                case 'date':

                    $changes_sql = ( $key === 'added' ) ? 'posts.added > posts.deleted' : 'posts.added <= posts.deleted';

                    $results = $wpdb->get_results(
                        $wpdb->remove_placeholder_escape( $wpdb->prepare( "
                            SELECT
                                posts.id AS id,
                                posts.name AS name
                            FROM (
                                SELECT
                                    p.ID AS id,
                                    p.post_title AS name,
                                    SUM( if ( log.object_note LIKE %s, 1, 0 ) ) AS added,
                                    SUM( if ( log.object_note LIKE %s, 1, 0 ) ) AS deleted
                                FROM $wpdb->dt_activity_log AS log
                                INNER JOIN $wpdb->posts AS p ON p.ID = log.object_id
                                WHERE log.object_type = %s
                                    AND log.object_subtype = %s
                                    AND log.meta_key = %s
                                    AND log.field_type = %s
                                    AND log.hist_time BETWEEN %d AND %d
                                GROUP BY p.ID
                            ) posts
                            WHERE %1s
                        ", '%Added%', '%deleted%', $post_type, $field, $field, $field_type, $start, $end, $changes_sql
                        ) )
                    );

                    break;

                case 'tags':
                case 'multi_select':

                    $changes_sql = 'posts.added > posts.deleted';

                    $results = $wpdb->get_results(
                        $wpdb->remove_placeholder_escape( $wpdb->prepare( "
                            SELECT
                                posts.id AS id,
                                posts.name AS name
                            FROM (
                                SELECT
                                    p.ID AS id,
                                    p.post_title AS name,
                                    SUM( if ( log.object_note LIKE %s, 1, 0 ) ) AS added,
                                    SUM( if ( log.object_note LIKE %s, 1, 0 ) ) AS deleted
                                FROM $wpdb->dt_activity_log AS log
                                INNER JOIN $wpdb->posts AS p ON p.ID = log.object_id
                                WHERE log.object_type = %s
                                    AND log.object_subtype = %s
                                    AND log.meta_key = %s
                                    AND log.meta_value = %s
                                    AND log.field_type = %s
                                    AND log.hist_time BETWEEN %d AND %d
                                GROUP BY p.ID
                            ) posts
                            WHERE %1s
                        ", '%Added%', '%deleted%', $post_type, $field, $field, $key, $field_type, $start, $end, $changes_sql
                        ) )
                    );

                    break;

                case 'key_select':

                    $results = $wpdb->get_results(
                        $wpdb->remove_placeholder_escape( $wpdb->prepare( "
                            SELECT
                                posts.id AS id,
                                posts.name AS name
                            FROM (
                                SELECT
                                    p.ID AS id,
                                    p.post_title AS name
                                FROM $wpdb->dt_activity_log AS log
                                INNER JOIN $wpdb->posts AS p ON p.ID = log.object_id
                                WHERE log.object_type = %s
                                    AND log.object_subtype = %s
                                    AND log.meta_key = %s
                                    AND log.meta_value = %s
                                    AND log.field_type = %s
                                    AND log.hist_time BETWEEN %d AND %d
                                GROUP BY p.ID
                            ) posts
                        ", $post_type, $field, $field, $key, $field_type, $start, $end
                        ) )
                    );

                    break;

                case 'number':

                    $results = $wpdb->get_results(
                        $wpdb->remove_placeholder_escape( $wpdb->prepare( "
                            SELECT
                                posts.id AS id,
                                posts.name AS name
                            FROM (
                                SELECT
                                    p.ID AS id,
                                    p.post_title AS name
                                FROM $wpdb->dt_activity_log AS log
                                INNER JOIN $wpdb->posts AS p ON p.ID = log.object_id
                                WHERE log.object_type = %s
                                    AND log.object_subtype = %s
                                    AND log.meta_key = %s
                                    AND log.meta_value != ''
                                    AND log.field_type = %s
                                    AND log.hist_time BETWEEN %d AND %d
                                GROUP BY p.ID
                            ) posts
                        ", $post_type, $field, $field, $field_type, $start, $end
                        ) )
                    );

                    break;

                case 'connection':
                    $p2p_type = $field_settings[$field]['p2p_key'] ?? null;
                    if ( !empty( $p2p_type ) ){
                        $results = $wpdb->get_results(
                            $wpdb->prepare( "
                                    SELECT
                                        p.ID AS id,
                                        p.post_title AS name
                                    FROM $wpdb->dt_activity_log AS log
                                    INNER JOIN $wpdb->posts AS p ON p.ID = log.object_id
                                    INNER JOIN $wpdb->posts as p2 ON p2.ID = log.meta_value
                                    WHERE log.object_type = %s
                                        AND log.object_subtype = %s
                                        AND log.meta_key = %s
                                        AND log.hist_time BETWEEN %s AND %s
                                        AND log.action = %s
                                    GROUP BY p.ID
                            ", $post_type, $field, $p2p_type, $start, $end, $key === 'connected' ? 'connected to' : 'disconnected from' ), ARRAY_A
                        );
                    }
                    break;

                default:
                    break;
            }

            return [
                'total' => count( $results ),
                'data' => array_splice( $results, 0, $limit )
            ];
        }
        return [];
    }
}
