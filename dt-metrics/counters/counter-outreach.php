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
    public function __construct() {
        parent::__construct();
    } // End __construct()

    /**
     * Returns count of outreach
     * Primary 'countable'
     *
     * @param string $status
     * @param int $start
     * @param null $end
     *
     * @return int|array
     */
    public static function get_outreach_count( string $status = '', int $start = 0, $end = null ) {

        $year = dt_get_year_from_timestamp( $start );
        $status = strtolower( $status );

        if ( empty( $year ) ) {
            $year = date( 'Y' ); // default to this year
        }

        switch ( $status ) {

            case 'social_engagement':

                global $wpdb;
                $results = $wpdb->get_results( $wpdb->prepare("
                    SELECT report_source, report_subsource, SUM(meta_value) as critical_path_total 
                    FROM $wpdb->dt_reports 
                    INNER JOIN wp_dt_reportmeta ON $wpdb->dt_reports.id=wp_dt_reportmeta.report_id 
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
                    FROM $wpdb->dt_reports 
                    INNER JOIN wp_dt_reportmeta ON $wpdb->dt_reports.id=wp_dt_reportmeta.report_id 
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

            case 'manual_additions':
                global $wpdb;
                $manual_additions = $wpdb->get_results($wpdb->prepare( "
                SELECT a.report_source as source,
                  h.meta_value as total,
                  g.meta_value as section
                FROM $wpdb->dt_reports as a
                LEFT JOIN $wpdb->dt_reportmeta as e
                  ON a.id=e.report_id
                     AND e.meta_key = 'year'
                LEFT JOIN $wpdb->dt_reportmeta as h
                  ON a.id=h.report_id
                     AND h.meta_key = 'total'
                  LEFT JOIN $wpdb->dt_reportmeta as g
                    ON a.id=g.report_id
                       AND g.meta_key = 'section'
                WHERE category = 'manual'
                  AND a.id IN ( SELECT MAX( bb.report_id )
                    FROM $wpdb->dt_reportmeta as bb
                      LEFT JOIN $wpdb->dt_reportmeta as d
                        ON bb.report_id=d.report_id
                           AND d.meta_key = 'source'
                      LEFT JOIN $wpdb->dt_reportmeta as e
                        ON bb.report_id=e.report_id
                           AND e.meta_key = 'year'
                    WHERE bb.meta_key = 'submit_date'
                    GROUP BY d.meta_value, e.meta_value
                  )
                AND e.meta_value = %s
                ", $year ), ARRAY_A );

                $sources = get_option( 'dt_critical_path_sources', [] );
                $additions = [];
                foreach ( $sources as $source ){
                    foreach ( $manual_additions as $addition_i => $addition ){
                        if ( $source["key"] === $addition["source"] ){
                            $addition["label"] = $source["label"];
                            $additions[] = $addition;
                        }
                    }
                }
                return $additions;
                break;
            default: // countable outreach
                global $wpdb;
                $results = $wpdb->get_results( "
                    SELECT report_source, report_subsource, max(report_date) as latest_report, meta_value as critical_path_total 
                    FROM $wpdb->dt_reports 
                    INNER JOIN wp_dt_reportmeta 
                        ON $wpdb->dt_reports.id=wp_dt_reportmeta.report_id 
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
