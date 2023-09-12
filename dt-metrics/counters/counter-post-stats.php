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
        dt_write_log( $wpdb->last_query );

        $cumulative_offset = self::get_number_field_cumulative_offsets( $post_type, $field, $start );

        return [
            'data' => $results,
            'cumulative_offset' => $cumulative_offset
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
            'cumulative_offset' => $cumulative_offset
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
                'cumulative_offset' => $cumulative_offset
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
            'cumulative_offset' => $cumulative_offset
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
            'data' => $results
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
            'cumulative_offset' => $cumulative_offset
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
            'data' => $results
        ];
    }

    public static function get_connection_field_by_month( $post_type, $field, $connection_type, $year ) {
        global $wpdb;

        $start = mktime( 0, 0, 0, 1, 1, $year );
        $end = mktime( 24, 60, 60, 12, 31, $year );

        // Determine total number of connections within time frame.
        $connection_posts_group = $wpdb->get_results(
            $wpdb->prepare( "
                SELECT
                    p.ID AS id,
                    COUNT( log.hist_time ) AS count,
                    MONTH( FROM_UNIXTIME( log.hist_time ) ) AS month
                FROM $wpdb->dt_activity_log AS log
                LEFT JOIN $wpdb->posts AS p ON p.ID = log.object_id
                INNER JOIN $wpdb->posts as p2 ON p2.ID = log.meta_value
                WHERE log.object_type = %s
                    AND log.object_subtype = %s
                    AND log.meta_key = %s
                    AND log.hist_time BETWEEN %s AND %s
                    AND log.action = %s
                    AND p.ID IS NOT NULL
                GROUP BY p.ID
                ORDER BY MONTH( FROM_UNIXTIME( log.hist_time ) )
            ", $post_type, $field, $connection_type, $start, $end, 'connected to' )
        );

        // Package into connection post results.
        $connection_posts_group_months = [];
        foreach ( $connection_posts_group ?? [] as $cpg ){
            if ( !isset( $connection_posts_group_months[$cpg->month] ) ){
                $connection_posts_group_months[$cpg->month] = 0;
            }
            $connection_posts_group_months[$cpg->month]++;
        }

        $connected_distinct = [];
        foreach ( $connection_posts_group_months as $cpg_month => $cpg_count ){
            $connected_distinct[] = [
                'count' => strval( $cpg_count ),
                'month' => strval( $cpg_month )
            ];
        }

        // Determine total number of disconnections within time frame.
        $disconnected_posts_group = $wpdb->get_results(
            $wpdb->prepare( "
                 SELECT
                    p.ID AS id,
                    COUNT( log.hist_time ) AS count,
                    MONTH( FROM_UNIXTIME( log.hist_time ) ) AS month
                 FROM $wpdb->dt_activity_log AS log
                 LEFT JOIN $wpdb->posts AS p ON p.ID = log.object_id
                 INNER JOIN $wpdb->posts as p2 ON p2.ID = log.meta_value
                 WHERE log.object_type = %s
                     AND log.object_subtype = %s
                     AND log.meta_key = %s
                     AND log.hist_time BETWEEN %s AND %s
                     AND log.action = %s
                     AND p.ID IS NOT NULL
                GROUP BY p.ID
                ORDER BY MONTH( FROM_UNIXTIME( log.hist_time ) )
            ", $post_type, $field, $connection_type, $start, $end, 'disconnected from' )
        );

        // Package into disconnected post results.
        $disconnected_posts_group_months = [];
        foreach ( $disconnected_posts_group ?? [] as $dpg ){
            if ( !isset( $disconnected_posts_group_months[$dpg->month] ) ){
                $disconnected_posts_group_months[$dpg->month] = 0;
            }
            $disconnected_posts_group_months[$dpg->month]++;
        }

        $disconnected_distinct = [];
        foreach ( $disconnected_posts_group_months as $dpg_month => $dpg_count ){
            $disconnected_distinct[] = [
                'count' => strval( $dpg_count ),
                'month' => strval( $dpg_month )
            ];
        }

        // Determine post id disconnected counts.
        $posts = [];
        $disconnected_post_month_groups = [];

        foreach ( $disconnected_posts_group ?? [] as $disconnected_post_group ){
            if ( !isset( $disconnected_post_month_groups[$disconnected_post_group->month] ) ){
                $disconnected_post_month_groups[$disconnected_post_group->month] = [];
            }
            $disconnected_post_month_groups[$disconnected_post_group->month][$disconnected_post_group->id] = $disconnected_post_group->count;
        }

        // Filter out post ids accordingly, by disconnected counts.
        foreach ( $connection_posts_group ?? [] as $connection_post_group ) {
            if ( isset( $connection_post_group->count, $disconnected_post_month_groups[$connection_post_group->month], $disconnected_post_month_groups[$connection_post_group->month][$connection_post_group->id] ) ) {
                $count = ( $connection_post_group->count - $disconnected_post_month_groups[$connection_post_group->month][$connection_post_group->id] );
                if ( $count > 0 ) {
                    $posts[] = $connection_post_group;
                }
            } else {
                $posts[] = $connection_post_group;
            }
        }

        // Package into unique posts.
        $unique_posts = [];
        $unique_post_ids = [];
        foreach ( $posts ?? [] as $post ){
            if ( !in_array( $post->id, $unique_post_ids ) ){
                $unique_post_ids[] = $post->id;
                $unique_posts[] = $post;
            }
        }

        // Group unique posts by months.
        $unique_post_groups = [];
        foreach ( $unique_posts as $unique_post ){
            if ( !isset( $unique_post_groups[$unique_post->month] ) ){
                $unique_post_groups[$unique_post->month] = 0;
            }
            $unique_post_groups[$unique_post->month]++;
        }

        // Package groups into required results shape.
        $unique_post_group_results = [];
        foreach ( $unique_post_groups as $unique_post_group_month => $unique_post_group_count ){
            $unique_post_group_results[] = [
                'count' => strval( $unique_post_group_count ),
                'month' => strval( $unique_post_group_month )
            ];
        }

        $cumulative_offset = self::get_connection_field_cumulative_offset( $post_type, $field, $connection_type, 0, $start );

        return [
            'data' => $unique_post_group_results,
            'cumulative_offset' => $cumulative_offset,
            'connected' => $connected_distinct,
            'disconnected' => $disconnected_distinct
        ];
    }

    public static function get_connection_field_by_year( $post_type, $field, $connection_type ) {
        global $wpdb;

        $current_year = gmdate( 'Y' );
        $start = 0;
        $end = mktime( 24, 60, 60, 12, 31, $current_year );

        // Determine total number of connections within time frame.
        $connection_posts_group = $wpdb->get_results(
            $wpdb->prepare( "
                SELECT
                    p.ID AS id,
                    COUNT( log.hist_time ) AS count,
                    YEAR( FROM_UNIXTIME( log.hist_time ) ) AS year
                FROM $wpdb->dt_activity_log AS log
                LEFT JOIN $wpdb->posts AS p ON p.ID = log.object_id
                INNER JOIN $wpdb->posts as p2 ON p2.ID = log.meta_value
                WHERE log.object_type = %s
                    AND log.object_subtype = %s
                    AND log.meta_key = %s
                    AND log.hist_time BETWEEN %s AND %s
                    AND log.action = %s
                    AND p.ID IS NOT NULL
                GROUP BY p.ID
                ORDER BY YEAR( FROM_UNIXTIME( log.hist_time ) )
            ", $post_type, $field, $connection_type, $start, $end, 'connected to' )
        );

        // Package into connection post results.
        $connection_posts_group_years = [];
        foreach ( $connection_posts_group ?? [] as $cpg ){
            if ( !isset( $connection_posts_group_years[$cpg->year] ) ){
                $connection_posts_group_years[$cpg->year] = 0;
            }
            $connection_posts_group_years[$cpg->year]++;
        }

        $connected_distinct = [];
        foreach ( $connection_posts_group_years as $cpg_year => $cpg_count ){
            $connected_distinct[] = [
                'count' => strval( $cpg_count ),
                'year' => strval( $cpg_year )
            ];
        }

        // Determine total number of disconnections within time frame.
        $disconnected_posts_group = $wpdb->get_results(
            $wpdb->prepare( "
                 SELECT
                    p.ID AS id,
                    COUNT( log.hist_time ) AS count,
                    YEAR( FROM_UNIXTIME( log.hist_time ) ) AS year
                 FROM $wpdb->dt_activity_log AS log
                 LEFT JOIN $wpdb->posts AS p ON p.ID = log.object_id
                 INNER JOIN $wpdb->posts as p2 ON p2.ID = log.meta_value
                 WHERE log.object_type = %s
                     AND log.object_subtype = %s
                     AND log.meta_key = %s
                     AND log.hist_time BETWEEN %s AND %s
                     AND log.action = %s
                     AND p.ID IS NOT NULL
                GROUP BY p.ID
                ORDER BY YEAR( FROM_UNIXTIME( log.hist_time ) )
            ", $post_type, $field, $connection_type, $start, $end, 'disconnected from' )
        );

        // Package into disconnected post results.
        $disconnected_posts_group_years = [];
        foreach ( $disconnected_posts_group ?? [] as $dpg ){
            if ( !isset( $disconnected_posts_group_years[$dpg->year] ) ){
                $disconnected_posts_group_years[$dpg->year] = 0;
            }
            $disconnected_posts_group_years[$dpg->year]++;
        }

        $disconnected_distinct = [];
        foreach ( $disconnected_posts_group_years as $dpg_year => $dpg_count ){
            $disconnected_distinct[] = [
                'count' => strval( $dpg_count ),
                'year' => strval( $dpg_year )
            ];
        }

        // Determine post id disconnected counts.
        $disconnected_post_year_groups = [];
        foreach ( $disconnected_posts_group ?? [] as $disconnected_post_group ){
            if ( !isset( $disconnected_post_year_groups[$disconnected_post_group->year] ) ){
                $disconnected_post_year_groups[$disconnected_post_group->year] = [];
            }
            $disconnected_post_year_groups[$disconnected_post_group->year][$disconnected_post_group->id] = $disconnected_post_group->count;
        }

        // Filter out post ids accordingly, by disconnected counts.
        $posts = [];
        foreach ( $connection_posts_group ?? [] as $connection_post_group ) {
            if ( isset( $connection_post_group->count, $disconnected_post_year_groups[$connection_post_group->year], $disconnected_post_year_groups[$connection_post_group->year][$connection_post_group->id] ) ) {
                $count = ( $connection_post_group->count - $disconnected_post_year_groups[$connection_post_group->year][$connection_post_group->id] );
                if ( $count > 0 ) {
                    $posts[] = $connection_post_group;
                }
            } else {
                $posts[] = $connection_post_group;
            }
        }

        // Package into unique posts.
        $unique_posts = [];
        $unique_post_ids = [];
        foreach ( $posts ?? [] as $post ){
            if ( !in_array( $post->id, $unique_post_ids ) ){
                $unique_post_ids[] = $post->id;
                $unique_posts[] = $post;
            }
        }

        // Group unique posts by years.
        $unique_post_groups = [];
        foreach ( $unique_posts as $unique_post ){
            if ( !isset( $unique_post_groups[$unique_post->year] ) ){
                $unique_post_groups[$unique_post->year] = 0;
            }
            $unique_post_groups[$unique_post->year]++;
        }

        // Package groups into required results shape.
        $unique_post_group_results = [];
        foreach ( $unique_post_groups as $unique_post_group_year => $unique_post_group_count ){
            $unique_post_group_results[] = [
                'count' => strval( $unique_post_group_count ),
                'year' => strval( $unique_post_group_year )
            ];
        }

        return [
            'data' => $unique_post_group_results,
            'connected' => $connected_distinct,
            'disconnected' => $disconnected_distinct
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
            // phpcs:disable
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
            // phpcs:enable
        );

        return $results->count;
    }

    private static function get_connection_field_cumulative_offset( $post_type, $field, $connection_type, $start, $end ) {
        global $wpdb;

        $connection_posts_group = $wpdb->get_results(
            $wpdb->prepare( "
                SELECT
                    p.ID AS id,
                    COUNT( log.hist_time ) AS count,
                    MONTH( FROM_UNIXTIME( log.hist_time ) ) AS month
                FROM $wpdb->dt_activity_log AS log
                LEFT JOIN $wpdb->posts AS p ON p.ID = log.object_id
                INNER JOIN $wpdb->posts as p2 ON p2.ID = log.meta_value
                WHERE log.object_type = %s
                    AND log.object_subtype = %s
                    AND log.meta_key = %s
                    AND log.hist_time BETWEEN %s AND %s
                    AND log.action = %s
                    AND p.ID IS NOT NULL
                GROUP BY p.ID
                ORDER BY MONTH( FROM_UNIXTIME( log.hist_time ) )
            ", $post_type, $field, $connection_type, $start, $end, 'connected to' )
        );

        $disconnected_posts_group = $wpdb->get_results(
            $wpdb->prepare( "
                 SELECT
                    p.ID AS id,
                    COUNT( log.hist_time ) AS count,
                    MONTH( FROM_UNIXTIME( log.hist_time ) ) AS month
                 FROM $wpdb->dt_activity_log AS log
                 LEFT JOIN $wpdb->posts AS p ON p.ID = log.object_id
                 INNER JOIN $wpdb->posts as p2 ON p2.ID = log.meta_value
                 WHERE log.object_type = %s
                     AND log.object_subtype = %s
                     AND log.meta_key = %s
                     AND log.hist_time BETWEEN %s AND %s
                     AND log.action = %s
                     AND p.ID IS NOT NULL
                GROUP BY p.ID
                ORDER BY MONTH( FROM_UNIXTIME( log.hist_time ) )
            ", $post_type, $field, $connection_type, $start, $end, 'disconnected from' )
        );

        // Determine post id disconnected counts.
        $posts = [];
        $disconnected_post_month_groups = [];

        foreach ( $disconnected_posts_group ?? [] as $disconnected_post_group ){
            if ( !isset( $disconnected_post_month_groups[$disconnected_post_group->month] ) ){
                $disconnected_post_month_groups[$disconnected_post_group->month] = [];
            }
            $disconnected_post_month_groups[$disconnected_post_group->month][$disconnected_post_group->id] = $disconnected_post_group->count;
        }

        // Filter out post ids accordingly, by disconnected counts.
        foreach ( $connection_posts_group ?? [] as $connection_post_group ) {
            if ( isset( $connection_post_group->count, $disconnected_post_month_groups[$connection_post_group->month], $disconnected_post_month_groups[$connection_post_group->month][$connection_post_group->id] ) ) {
                $count = ( $connection_post_group->count - $disconnected_post_month_groups[$connection_post_group->month][$connection_post_group->id] );
                if ( $count > 0 ) {
                    $posts[] = $connection_post_group;
                }
            } else {
                $posts[] = $connection_post_group;
            }
        }

        // Package into unique posts.
        $unique_posts = [];
        $unique_post_ids = [];
        foreach ( $posts ?? [] as $post ){
            if ( !in_array( $post->id, $unique_post_ids ) ){
                $unique_post_ids[] = $post->id;
                $unique_posts[] = $post;
            }
        }

        // Return cumulative total.
        return intval( count( $unique_posts ) );
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
                        // phpcs:disable
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
                        // phpcs:enable
                    } else {
                        // phpcs:disable
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
                        // phpcs:enable
                    }
                    break;
                case 'number':
                    $start = $args['start'] ?? 0;
                    $end = $args['end'] ?? time();

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
                    // phpcs:enable
                    break;
                case 'connection':
                    $start = $args['start'] ?? 0;
                    $end = $args['end'] ?? time();
                    $p2p_type = $field_settings[$field]['p2p_key'] ?? null;
                    $key = $args['key'] ?? 'cumulative';

                    if ( !empty( $p2p_type ) ){

                        // phpcs:disable
                        $connection_posts = $wpdb->get_results(
                            $wpdb->prepare( "
                                SELECT
                                    p.ID AS id, p.post_title AS name
                                FROM $wpdb->dt_activity_log AS log
                                LEFT JOIN $wpdb->posts AS p ON p.ID = log.object_id
                                INNER JOIN $wpdb->posts as p2 ON p2.ID = log.meta_value
                                WHERE log.object_type = %s
                                    AND log.object_subtype = %s
                                    AND log.meta_key = %s
                                    AND log.hist_time BETWEEN %s AND %s
                                    AND log.action = %s
                                    AND p.ID IS NOT NULL
                        ", $post_type, $field, $p2p_type, $start, $end, 'connected to' )
                        );

                        $connection_posts_group = $wpdb->get_results(
                            $wpdb->prepare( "
                                SELECT
                                    p.ID AS id,
                                    p.post_title AS name,
                                    COUNT( log.hist_time ) AS count
                                FROM $wpdb->dt_activity_log AS log
                                LEFT JOIN $wpdb->posts AS p ON p.ID = log.object_id
                                INNER JOIN $wpdb->posts as p2 ON p2.ID = log.meta_value
                                WHERE log.object_type = %s
                                    AND log.object_subtype = %s
                                    AND log.meta_key = %s
                                    AND log.hist_time BETWEEN %s AND %s
                                    AND log.action = %s
                                    AND p.ID IS NOT NULL
                                GROUP BY p.ID
                            ", $post_type, $field, $p2p_type, $start, $end, 'connected to' )
                        );

                        $disconnected_posts = $wpdb->get_results(
                            $wpdb->prepare( "
                                 SELECT
                                     p.ID AS id, p.post_title AS name
                                 FROM $wpdb->dt_activity_log AS log
                                 LEFT JOIN $wpdb->posts AS p ON p.ID = log.object_id
                                 INNER JOIN $wpdb->posts as p2 ON p2.ID = log.meta_value
                                 WHERE log.object_type = %s
                                     AND log.object_subtype = %s
                                     AND log.meta_key = %s
                                     AND log.hist_time BETWEEN %s AND %s
                                     AND log.action = %s
                                     AND p.ID IS NOT NULL
                         ", $post_type, $field, $p2p_type, $start, $end, 'disconnected from' )
                        );

                        $disconnected_posts_group = $wpdb->get_results(
                            $wpdb->prepare( "
                                 SELECT
                                    p.ID AS id,
                                    p.post_title AS name,
                                    COUNT( log.hist_time ) AS count
                                 FROM $wpdb->dt_activity_log AS log
                                 LEFT JOIN $wpdb->posts AS p ON p.ID = log.object_id
                                 INNER JOIN $wpdb->posts as p2 ON p2.ID = log.meta_value
                                 WHERE log.object_type = %s
                                     AND log.object_subtype = %s
                                     AND log.meta_key = %s
                                     AND log.hist_time BETWEEN %s AND %s
                                     AND log.action = %s
                                     AND p.ID IS NOT NULL
                                GROUP BY p.ID
                            ", $post_type, $field, $p2p_type, $start, $end, 'disconnected from' )
                        );
                        // phpcs:enable

                        // Package existing connection results.
                        $packaged_connections = [];

                        // Determine post id disconnected counts.
                        $disconnected_post_id_groups = [];
                        foreach ( $disconnected_posts_group ?? [] as $disconnected_post_group ){
                            if ( !isset( $disconnected_post_id_groups[$disconnected_post_group->id] ) ){
                                $disconnected_post_id_groups[$disconnected_post_group->id] = [];
                            }
                            $disconnected_post_id_groups[$disconnected_post_group->id] = $disconnected_post_group->count;
                        }

                        // Filter out post ids accordingly, by disconnected counts.
                        foreach ( $connection_posts_group ?? [] as $connection_post_group ) {
                            if ( isset( $connection_post_group->count, $disconnected_post_id_groups[$connection_post_group->id] ) ) {
                                $count = ( $connection_post_group->count - $disconnected_post_id_groups[$connection_post_group->id] );
                                if ( $count > 0 ) {
                                    $packaged_connections[] = $connection_post_group;
                                }
                            } else {
                                $packaged_connections[] = $connection_post_group;
                            }
                        }

                        // Determine post results payload to be returned.
                        $posts = [];
                        switch ( $key ) {
                            case 'cumulative':
                                $posts = $packaged_connections;
                                break;
                            case 'connected':
                                $posts = $connection_posts;
                                break;
                            case 'disconnected':
                                $posts = $disconnected_posts;
                                break;
                        }

                        // Remove post duplicates.
                        $unique_posts = [];
                        $unique_post_ids = [];
                        foreach ( $posts ?? [] as $post ){
                            if ( !in_array( $post->id, $unique_post_ids ) ){
                                $unique_post_ids[] = $post->id;
                                $unique_posts[] = $post;
                            }
                        }

                        // Determine results size and return capped results by specified limit.
                        $total = [ [ count( $unique_posts ) ] ];
                        $results = array_slice( $unique_posts, 0, $limit );
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
}
