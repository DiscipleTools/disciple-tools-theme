<?php
/**
 * Provides stats on post fields per time slot
 *
 * @package Disciple_Tools
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

        if ( self::isPostField( $field ) ) {
            return $wpdb->get_results(
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

            return $results;
        }

        return [];
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

        if ( self::isPostField( $field ) ) {
            return $wpdb->get_results(
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

            return $results;
        }

        return [];
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
            $results = $wpdb->get_results(
                $wpdb->prepare( "
                    SELECT
                        DISTINCT( meta_value ) AS value
                    FROM $wpdb->postmeta
                    WHERE
                        meta_key = %s
                ", $field )
            );
            $multi_values = array_map( function ( $result ) {
                return $result->value;
            }, $results );
        }

        $count_dynamic_values = array_map( function ( $value ) {
            return "COUNT( CASE WHEN log.meta_value = '$value' THEN log.meta_value END ) AS $value";
        }, $multi_values);
        $count_dynamic_values_query = implode( ', ', $count_dynamic_values );

        $results = $wpdb->get_results(
            // phpcs:disable
            $wpdb->prepare( "
                SELECT 
                    MONTH( FROM_UNIXTIME( log.hist_time ) ) AS month,
                    $count_dynamic_values_query
                FROM $wpdb->posts AS p
                JOIN $wpdb->postmeta AS pm
                    ON p.ID = pm.post_id
                JOIN $wpdb->dt_activity_log AS log
                    ON log.object_id = p.ID
                WHERE p.post_type = %s
                    AND pm.meta_key = %s
                    AND log.meta_value = pm.meta_value
                    AND log.hist_time = (
                        SELECT MAX( log2.hist_time )
                        FROM $wpdb->dt_activity_log AS log2
                        WHERE log.meta_value = log2.meta_value
                            AND log.object_id = log2.object_id
                    )
                    AND log.hist_time >= %s
                    AND log.hist_time <= %s
                GROUP BY MONTH( FROM_UNIXTIME( log.hist_time ) )
                ORDER BY MONTH( FROM_UNIXTIME( log.hist_time ) )
            ", $post_type, $field, $start, $end )
            // phpcs:enable
        );

        return $results;
    }

    public static function get_multi_field_by_year( $post_type, $field ) {
        # code...
    }

    private static function isPostField( $field ) {
        global $wpdb;
        $post_fields = $wpdb->get_col( "DESC $wpdb->posts", 0 );
        return in_array( $field, $post_fields, true );
    }
}
