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
class Disciple_Tools_Counter_Post_Stats extends Disciple_Tools_Counter_Base
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

        // sanitise input
        // is post_type in the available post_types
        // is the field in the fieldSettings list
        // is the year <= current year

        $start = mktime( 0, 0, 0, 1, 1, $year );
        $end = mktime( 24, 60, 60, 12, 31, $year );

        // first we need to know if the $field is in the post, or postmeta before we can do a query
        $post_fields = $wpdb->get_col( "DESC $wpdb->posts", 0 );

        if ( in_array( $field, $post_fields, true ) ) {
            return $wpdb->get_results(
                $wpdb->prepare( "
                    SELECT
                        MONTH( $field ) AS month,
                        COUNT( $field ) AS count
                    FROM $wpdb->posts
                    WHERE post_type = %s
                        AND $field >= %s
                        AND $field <= %s
                    GROUP BY MONTH( $field )
                    ORDER BY MONTH( $field )
                ", $post_type, gmdate( 'Y-m-d H:i:s', $start ), gmdate( 'Y-m-d H:i:s', $end ) )
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
}
