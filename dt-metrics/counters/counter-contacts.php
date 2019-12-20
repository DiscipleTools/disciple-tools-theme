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

            default:
                return 0;
                break;
        }
    }


    public static function new_contact_count( int $start, int $end ){
        global $wpdb;
        $res = $wpdb->get_var(
            $wpdb->prepare( "
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
                )",
                dt_format_date( $start, 'Y-m-d' ),
                dt_format_date( $end, 'Y-m-d' )
            )
        );
        return $res;
    }

    public static function assigned_contacts_count( int $start, int $end ){
        global $wpdb;
        $res = $wpdb->get_var(
            $wpdb->prepare( "
                SELECT COUNT( DISTINCT(log.object_id) ) as `value`
                FROM $wpdb->dt_activity_log log
                INNER JOIN $wpdb->postmeta as type ON ( log.object_id = type.post_id AND type.meta_key = 'type' AND type.meta_value != 'user' )
                INNER JOIN $wpdb->posts post
                ON ( 
                    post.ID = log.object_id
                    AND post.post_type = 'contacts'
                    AND post.post_status = 'publish'
                )
                WHERE log.meta_key = 'overall_status'
                AND log.meta_value = 'assigned'
                AND log.object_type = 'contacts' 
                AND log.hist_time > %s
                AND log.hist_time < %s
            ", $start, $end
            )
        );
        return $res;
    }

    public static function active_contacts_count( int $start, int $end ){
        global $wpdb;
        $res = $wpdb->get_var(
            $wpdb->prepare( "
                SELECT COUNT( DISTINCT(log.object_id) ) as `value`
                FROM $wpdb->dt_activity_log log
                INNER JOIN $wpdb->postmeta as type ON ( log.object_id = type.post_id AND type.meta_key = 'type' AND type.meta_value != 'user' )
                INNER JOIN $wpdb->posts post
                ON ( 
                    post.ID = log.object_id
                    AND post.post_type = 'contacts'
                    AND post.post_status = 'publish'
                )
                WHERE log.meta_key = 'overall_status'
                AND log.meta_value = 'active'
                AND log.object_type = 'contacts' 
                AND log.hist_time > %s
                AND log.hist_time < %s
            ", $start, $end
            )
        );
        return $res;
    }

    /**
     * @param int $start timestamp
     * @param int $end timestamp
     * @return array
     */
    public static function seeker_path_activity( int $start = 0, int $end = 0 ){
        global $wpdb;
        $res = $wpdb->get_results( $wpdb->prepare( "
            SELECT COUNT( DISTINCT(log.object_id) ) as `value`, log.meta_value as seeker_path
            FROM $wpdb->dt_activity_log log
            INNER JOIN $wpdb->postmeta as type ON ( log.object_id = type.post_id AND type.meta_key = 'type' AND type.meta_value != 'user' )
            INNER JOIN $wpdb->posts post
            ON ( 
                post.ID = log.object_id
                AND log.meta_key = 'seeker_path'
                AND log.object_type = 'contacts' 
            )
            INNER JOIN $wpdb->postmeta pm
            ON (
                pm.post_id = post.ID
                AND pm.meta_key = 'seeker_path'
            )
            WHERE post.post_type = 'contacts'
            AND log.hist_time > %s
            AND log.hist_time < %s
            AND post.post_status = 'publish'
            GROUP BY log.meta_value
        ", $start, $end ), ARRAY_A );

        $field_settings = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings();
        $seeker_path_options = $field_settings["seeker_path"]["default"];
        $seeker_path_data = [];
        foreach ( $seeker_path_options as $option_key => $option_value ){
            $value = 0;
            foreach ( $res as $r ){
                if ( $r["seeker_path"] === $option_key ){
                    $value = $r["value"];
                }
            }
            $seeker_path_data[$option_key] = [
                "label" => $option_value["label"],
                "value" => $value
            ];
        }

        return $seeker_path_data;
    }

    /**
     * Get the snapshot for each seeker path at a certain date.
     * @param int $end
     *
     * @return array
     */
    public static function seeker_path_at_date( int $end ){
        global $wpdb;
        $res = $wpdb->get_results(
            $wpdb->prepare( "
                SELECT count( DISTINCT( log.object_id ) ) as value, log.meta_value as seeker_path
                FROM $wpdb->dt_activity_log log
                JOIN (
                    SELECT MAX( hist_time ) as hist_time, object_id
                    FROM  $wpdb->dt_activity_log
                    WHERE meta_key = 'seeker_path'
                    AND hist_time < %d
                    GROUP BY object_id
                ) as b ON (
                    log.hist_time = b.hist_time
                    AND log.object_id = b.object_id
                )
                JOIN $wpdb->dt_activity_log as sl ON (
                    sl.object_type = 'contacts' 
                    AND sl.object_id = log.object_id
                    AND sl.meta_key = 'overall_status'
                    AND sl.meta_value = 'active'
                    AND sl.hist_time = (
                        SELECT MAX( hist_time ) as hist_time
                        FROM $wpdb->dt_activity_log
                        WHERE meta_key = 'overall_status'
                        AND hist_time < %d
                        AND object_id = log.object_id
                    )
                )
                WHERE log.meta_key = 'seeker_path'
                AND log.object_id NOT IN (
                    SELECT post_id FROM $wpdb->postmeta
                    WHERE meta_key = 'type' AND meta_value = 'user'
                    GROUP BY post_id
                )
                GROUP BY log.meta_value
            ", $end, $end
            ), ARRAY_A
        );
        $field_settings = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings();
        $seeker_path_options = $field_settings["seeker_path"]["default"];
        $seeker_path_data = [];
        foreach ( $seeker_path_options as $option_key => $option_value ){
            $value = 0;
            foreach ( $res as $r ){
                if ( $r["seeker_path"] === $option_key ){
                    $value = $r["value"];
                }
            }
            $seeker_path_data[$option_key] = [
                "label" => $option_value["label"],
                "value" => $value
            ];
        }

        return $seeker_path_data;
    }

    /**
     * Get a snapshot of each status at a certain date
     *
     * @param int $end
     *
     * @return array
     */
    public static function overall_status_at_date( int $end ){
        global $wpdb;
        $res = $wpdb->get_results(
            $wpdb->prepare( "
                SELECT count( DISTINCT( log.object_id ) ) as value, log.meta_value as overall_status
                FROM $wpdb->dt_activity_log log
                INNER JOIN $wpdb->posts post ON ( 
                    post.ID = log.object_id
                    AND post.post_type = 'contacts' 
                )
                JOIN (
                    SELECT MAX( hist_time ) as hist_time, object_id
                    FROM  $wpdb->dt_activity_log
                    WHERE meta_key = 'overall_status'
                    AND hist_time < %d
                    GROUP BY object_id
                ) as b ON (
                    log.hist_time = b.hist_time
                    AND log.object_id = b.object_id
                )
                WHERE log.meta_key = 'overall_status'
                AND log.object_id NOT IN (
                    SELECT post_id FROM $wpdb->postmeta
                    WHERE meta_key = 'type' AND meta_value = 'user'
                    GROUP BY post_id
                )
                GROUP BY log.meta_value
            ", $end
            ), ARRAY_A
        );
        $field_settings = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings();
        $overall_status_options = $field_settings["overall_status"]["default"];
        $overall_status_data = [];
        foreach ( $overall_status_options as $option_key => $option_value ){
            $value = 0;
            foreach ( $res as $r ){
                if ( $r["overall_status"] === $option_key ){
                    $value = $r["value"];
                }
            }
            $overall_status_data[$option_key] = [
                "label" => $option_value["label"],
                "value" => $value
            ];
        }

        return $overall_status_data;
    }
}
