<?php
/**
 * Counts Outreach Sources
 *
 * @package Disciple.Tools
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
     */
    public static function get_outreach_count( string $status = '', int $start = 0, $end = null ) {

        $year = dt_get_year_from_timestamp( $start );
        $status = strtolower( $status );

        if ( empty( $year ) ) {
            $year = gmdate( 'Y' ); // default to this year
        }

        switch ( $status ) {


            case 'manual_additions':
//                global $wpdb;
//
//                $manual_additions = $wpdb->get_results($wpdb->prepare( "
//                SELECT a.type as source,
//                  h.meta_value as total,
//                  g.meta_value as section
//                FROM $wpdb->dt_reports as a
//                LEFT JOIN $wpdb->dt_reportmeta as e
//                  ON a.id=e.report_id
//                     AND e.meta_key = 'year'
//                LEFT JOIN $wpdb->dt_reportmeta as h
//                  ON a.id=h.report_id
//                     AND h.meta_key = 'total'
//                  LEFT JOIN $wpdb->dt_reportmeta as g
//                    ON a.id=g.report_id
//                       AND g.meta_key = 'section'
//                WHERE type = 'monthly_report'
//                  AND a.id IN ( SELECT MAX( bb.report_id )
//                    FROM $wpdb->dt_reportmeta as bb
//                      LEFT JOIN $wpdb->dt_reportmeta as d
//                        ON bb.report_id=d.report_id
//                           AND d.meta_key = 'source'
//                      LEFT JOIN $wpdb->dt_reportmeta as e
//                        ON bb.report_id=e.report_id
//                           AND e.meta_key = 'year'
//                    WHERE bb.meta_key = 'submit_date'
//                    GROUP BY d.meta_value, e.meta_value
//                  )
//                AND e.meta_value = %s
//                ", $year ), ARRAY_A );

            /*
                $manual_additions = $wpdb->get_results($wpdb->prepare( "
                SELECT  e.meta_key as source,
                  e.meta_value as total,
                  ('outreach') as section
                FROM $wpdb->dt_reports as a
                LEFT JOIN $wpdb->dt_reportmeta as e
                ON a.id=e.report_id
                WHERE type = 'monthly_report';
                ", $year ), ARRAY_A );
            */


//                $sources = get_option( 'dt_critical_path_sources', [] );
//                $additions = [];
//                foreach ( $sources as $source ){
//                    foreach ( $manual_additions as $addition_i => $addition ){
//                        if ( $source["key"] === $addition["source"] ){
//                            $addition["label"] = $source["label"];
//                            $additions[] = $addition;
//                        }
//                    }
//                }
//                return $additions;
                break;
            default: // countable outreach
//                global $wpdb;
//                $results = $wpdb->get_results( "
//                    SELECT type, report_subsource, max(report_date) as latest_report, meta_value as critical_path_total
//                    FROM $wpdb->dt_reports
//                    INNER JOIN $wpdb->dt_reportmeta rm
//                        ON $wpdb->dt_reports.id = rm.report_id
//                    WHERE focus = 'outreach'
//                        AND meta_key = 'critical_path_total'
//                    GROUP BY type, report_subsource
//                    ORDER BY report_date DESC
//                    ", ARRAY_A );
//                $sum = 0;
//                foreach ( $results as $result ) {
//                    $sum += $result['critical_path_total'];
//                }

//                return $sum;
                break;
        }
    }


    public static function get_monthly_reports_count( $start, $end ){
        global $wpdb;
        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT report.id, FROM_UNIXTIME(report.time_end) as report_date, rm.meta_key, rm.meta_value
            FROM $wpdb->dt_reports as report
            JOIN $wpdb->dt_reportmeta as rm ON ( rm.report_id = report.id )
            WHERE report.type = 'monthly_report'
            AND report.time_end >= %s
            AND report.time_end < %s
            ORDER BY report.time_end DESC
        ",
            $start,
            $end
        ), ARRAY_A );
        $sources = get_option( 'dt_critical_path_sources', [] );
        $reports = [];
        foreach ( $sources as $source ){
            $reports[ $source["key"] ] = [
                "label" => $source["label"],
                "section" => $source["section"] ?? 'none',
                "description" => $source["description"] ?? '',
                "sum" => 0,
                "latest" => null
            ];
        }
        foreach ( $results as $res ){
            if ( isset( $reports[ $res["meta_key"] ] ) ) {
                $reports[$res["meta_key"]]["sum"] += (int) $res["meta_value"];
                if ( $reports[$res["meta_key"]]["latest"] === null ){
                    $reports[$res["meta_key"]]["latest"] = (int) $res["meta_value"];
                }
            }
        }

        return $reports;
    }
}
