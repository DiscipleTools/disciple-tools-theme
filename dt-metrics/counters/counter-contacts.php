<?php
/**
 * Counts Misc Contacts numbers
 *
 * @package Disciple_Tools
 * @version 0.1.0
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Counter_Contacts
 */
class Disciple_Tools_Counter_Contacts extends Disciple_Tools_Counter_Base
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
     * Returns count of contacts for different statuses
     * Primary 'countable'
     *
     * @param string $status
     * @param int $start
     * @param null $end
     *
     * @return int
     */
    public static function get_contacts_count( string $status = '', $start = 0, $end = null ) {
        global $wpdb;
        $status = strtolower( $status );
        if ( !$end || $end === PHP_INT_MAX ) {
            $end = strtotime( "2100-01-01" );
        }

        switch ( $status ) {

            case 'new_contacts':
                $res = $wpdb->get_var( $wpdb->prepare( "
                SELECT count(ID) as count
                FROM $wpdb->posts
                WHERE post_type = 'contacts'
                  AND post_status = 'publish'
                  AND post_date >= %s
                  AND post_date < %s
                  AND ID NOT IN (
                    SELECT post_id
                    FROM $wpdb->postmeta
                    WHERE meta_key = 'type' AND  meta_value = 'user'
                    GROUP BY post_id
                )", dt_format_date( $start, 'Y-m-d' ), dt_format_date( $end, 'Y-m-d' ) ));
                return $res;
                break;

            case 'contacts_attempted':

                return 0;
                break;

            case 'contacts_established':

                return 0;
                break;

            case 'first_meetings':
                $res = $wpdb->get_var( $wpdb->prepare( "
                SELECT count(DISTINCT(a.ID)) as count
                FROM $wpdb->posts as a
                JOIN ( 
                    SELECT object_id, MIN( c.hist_time ) min_time 
                        FROM $wpdb->dt_activity_log c
                        WHERE c.object_type = 'contacts'
                        AND c.meta_key = 'seeker_path'
                        AND ( c.meta_value = 'met' OR c.meta_value = 'ongoing' OR c.meta_value = 'coaching' )
                        GROUP BY c.object_id  
                ) b 
                ON a.ID = b.object_id
                WHERE a.post_status = 'publish'
                  AND b.min_time  BETWEEN %s and %s 
                  AND a.post_type = 'contacts'
                  AND a.ID NOT IN (
                    SELECT post_id
                    FROM $wpdb->postmeta
                    WHERE meta_key = 'type' AND  meta_value = 'user'
                    GROUP BY post_id
                  )", $start, $end ));
                return $res;
                break;

            case 'ongoing_meetings':
                $res = $wpdb->get_var( $wpdb->prepare( "
                SELECT
                count(DISTINCT(a.ID))  as count
                FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as b
                ON a.ID = b.post_id
                   AND b.meta_key = 'seeker_path'
                   AND ( b.meta_value = 'ongoing' OR b.meta_value = 'coaching' )
                JOIN $wpdb->dt_activity_log time 
                ON
                    time.object_id = a.ID
                    AND time.object_type = 'contacts'
                    AND time.meta_key = 'seeker_path'
                    AND ( time.meta_value = 'ongoing' OR time.meta_value = 'coaching' )
                    AND time.hist_time < %s  
                LEFT JOIN $wpdb->postmeta as d
                   ON a.ID=d.post_id
                   AND d.meta_key = 'overall_status'
                LEFT JOIN ( 
                    SELECT object_id, MAX( c.hist_time ) max_time 
                        FROM $wpdb->dt_activity_log c
                        WHERE c.object_type = 'contacts'
                        AND c.meta_key = 'overall_status'
                        AND c.old_value = 'active'
                        GROUP BY c.object_id  
                ) close
                ON close.object_id = a.ID
                WHERE a.post_status = 'publish'
                  AND a.post_type = 'contacts'
                  AND ( d.meta_value = 'active' OR close.max_time > %s ) 
                  AND a.ID NOT IN (
                    SELECT post_id
                    FROM $wpdb->postmeta
                    WHERE meta_key = 'type' AND  meta_value = 'user'
                    GROUP BY post_id
                )", $end, $start ));
                return $res;

            case 'church_planters':
//                @todo implement church planter field on group/church
                return 0;
                break;
            default:
                return 0;
                break;
        }
    }

}
