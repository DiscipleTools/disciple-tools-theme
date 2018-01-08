<?php
/**
 * Counts Prayer Sources
 *
 * @package Disciple_Tools
 * @version 0.1.0
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Counter_Prayer
 */
class Disciple_Tools_Counter_Prayer extends Disciple_Tools_Counter_Base
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
     * Returns count of prayer
     * Primary 'countable'
     *
     * @param string $status
     * @param int    $year
     *
     * @return int
     */
    public static function get_prayer_count( string $status = '', int $year = null )
    {

        $status = strtolower( $status );

        if ( empty( $year ) ) {
            $year = date( 'Y' ); // default to this year
        }

        switch ( $status ) {

            default: // prayer network
                global $wpdb;
                $results = $wpdb->get_results( "
                    SELECT report_source, report_subsource, max(report_date) AS latest_report, meta_value AS critical_path_total 
                    FROM $wpdb->dt_reports 
                    INNER JOIN $wpdb->dt_reportmeta ON $wpdb->dt_reports.id=$wpdb->dt_reportmeta.report_id 
                    WHERE focus = 'prayer' 
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
