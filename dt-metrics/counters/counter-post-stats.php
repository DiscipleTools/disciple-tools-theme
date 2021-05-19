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
        ];
    }

    /**
     * Return count of posts by date field with stats counted by
     * year
     */
    public static function get_date_field_by_year( string $post_type, string $field ) {
        global $wpdb;

        $current_year = gmdate( "Y" );
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
            return "COUNT( CASE WHEN log.meta_value = '" . esc_sql( $value ) . "' THEN log.meta_value END ) AS `" . esc_sql( $value ) . "`";
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
        ];
    }

    public static function get_multi_field_by_year( $post_type, $field ) {
        global $wpdb;

        $current_year = gmdate( "Y" );
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
            return "COUNT( CASE WHEN log.meta_value = '" . esc_sql( $value ) . "' THEN log.meta_value END ) AS `" . esc_sql( $value ) . "`";
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
        ];
    }

    public static function get_connection_field_by_month( $connection_type, $year ) {
        global $wpdb;

        $start = mktime( 0, 0, 0, 1, 1, $year );
        $end = mktime( 24, 60, 60, 12, 31, $year );

        $results = $wpdb->get_results(
            $wpdb->prepare( "
                SELECT
                    COUNT( meta.meta_value ) AS count,
                    MONTH( meta.meta_value ) AS month
                FROM $wpdb->p2p AS p2p
                JOIN $wpdb->p2pmeta AS meta
                    ON p2p.p2p_id = meta.p2p_id
                WHERE p2p.p2p_type = %s
                    AND meta.meta_key = 'date'
                    AND meta.meta_value >= %s
                    AND meta.meta_value <= %s
                GROUP BY MONTH( meta.meta_value )
                ORDER BY MONTH( meta.meta_value )
            ", $connection_type, gmdate( 'Y-m-d H:i:s', $start ), gmdate( 'Y-m-d H:i:s', $end ) )
        );

        $cumulative_offset = self::get_connection_field_cumulative_offset( $connection_type, $start );

        return [
            'data' => $results,
            'cumulative_offset' => $cumulative_offset,
        ];
    }

    public static function get_connection_field_by_year( $connection_type ) {
        global $wpdb;

        $current_year = gmdate( "Y" );
        $start = 0;
        $end = mktime( 24, 60, 60, 12, 31, $current_year );

        $results = $wpdb->get_results(
            $wpdb->prepare( "
                SELECT
                    COUNT( meta.meta_value ) AS count,
                    YEAR( meta.meta_value ) AS year
                FROM $wpdb->p2p AS p2p
                JOIN $wpdb->p2pmeta AS meta
                    ON p2p.p2p_id = meta.p2p_id
                WHERE p2p.p2p_type = %s
                    AND meta.meta_key = 'date'
                    AND meta.meta_value >= %s
                    AND meta.meta_value <= %s
                GROUP BY YEAR( meta.meta_value )
                ORDER BY YEAR( meta.meta_value )
            ", $connection_type, gmdate( 'Y-m-d H:i:s', $start ), gmdate( 'Y-m-d H:i:s', $end ) )
        );

        return [
            'data' => $results,
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

        $current_year = gmdate( "Y" );
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
            return "COUNT( CASE WHEN log.meta_value = '" . esc_sql( $value ) . "' THEN log.meta_value END ) AS `" . esc_sql( $value ) . "`";
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

    private static function get_connection_field_cumulative_offset( $connection_type, $timestamp ) {
        global $wpdb;

        $total = $wpdb->get_var(
            $wpdb->prepare( "
                SELECT
                    COUNT( meta.meta_value ) AS count
                FROM $wpdb->p2p AS p2p
                JOIN $wpdb->p2pmeta AS meta
                    ON p2p.p2p_id = meta.p2p_id
                WHERE p2p.p2p_type = %s
                    AND meta.meta_key = 'date'
                    AND meta.meta_value <= %s
            ", $connection_type, gmdate( 'Y-m-d H:i:s', $timestamp ) )
        );

        return $total ? intval( $total ) : 0;
    }
}
