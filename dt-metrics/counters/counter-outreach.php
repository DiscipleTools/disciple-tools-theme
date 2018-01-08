<?php
/**
 * Counts Outreach Sources
 *
 * @package Disciple_Tools
 * @version 0.1.0
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Counter_Outreach
 */
class Disciple_Tools_Counter_Outreach extends Disciple_Tools_Counter_Base
{

    /**
     * Constructor function.
     *
     * @access public
     * @since  0.1.0
     */
    public function __construct()
    {
        parent::__construct();
    } // End __construct()

    /**
     * Returns count of outreach
     * Primary 'countable'
     *
     * @param string $status
     * @param int    $year
     *
     * @return int
     */
    public static function get_outreach_count( string $status = '', int $year = null )
    {

        $status = strtolower( $status );

        if ( empty( $year ) ) {
            $year = date( 'Y' ); // default to this year
        }

        switch ( $status ) {

            case 'social_engagement':

                global $wpdb;
                $results = $wpdb->get_results( $wpdb->prepare("
                    SELECT report_source, report_subsource, SUM(meta_value) as critical_path_total 
                    FROM wp_dt_reports 
                    INNER JOIN wp_dt_reportmeta ON wp_dt_reports.id=wp_dt_reportmeta.report_id 
                    WHERE focus = 'outreach' 
                    AND category = 'social' 
                    AND report_date LIKE %s 
                    AND meta_key = 'critical_path_total' 
                    GROUP BY report_source, report_subsource 
                    ",
                    $wpdb->esc_like( $year ) . '%'
                ), ARRAY_A );

                $sum = 0;
                foreach ( $results as $result ) {
                    $sum += $result['critical_path_total'];
                }

                return $sum;
                break;

            case 'website_visitors':

                global $wpdb;
                $results = $wpdb->get_results( $wpdb->prepare("
                    SELECT report_source, report_subsource, SUM(meta_value) as critical_path_total 
                    FROM wp_dt_reports 
                    INNER JOIN wp_dt_reportmeta ON wp_dt_reports.id=wp_dt_reportmeta.report_id 
                    WHERE focus = 'outreach' 
                    AND category = 'website' 
                    AND report_date LIKE %s 
                    AND meta_key = 'critical_path_total' 
                    GROUP BY report_source, report_subsource 
                    ",
                    $wpdb->esc_like( $year ) . '%'
                ), ARRAY_A );

                $sum = 0;
                foreach ( $results as $result ) {
                    $sum += $result['critical_path_total'];
                }

                return $sum;
                break;

            default: // countable outreach
                global $wpdb;
                $results = $wpdb->get_results( "
                    SELECT report_source, report_subsource, max(report_date) as latest_report, meta_value as critical_path_total 
                    FROM wp_dt_reports 
                    INNER JOIN wp_dt_reportmeta 
                        ON wp_dt_reports.id=wp_dt_reportmeta.report_id 
                    WHERE focus = 'outreach'
                        AND meta_key = 'critical_path_total' 
                    GROUP BY report_source, report_subsource 
                    ORDER BY report_date DESC
                    ", ARRAY_A );
                $sum = 0;
                foreach ( $results as $result ) {
                    $sum += $result['critical_path_total'];
                }

                return $sum;
                break;
        }
    }

}
