<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * @see   Disciple_Tools_Activity_Log_API::insert
 * @since 0.1.0
 *
 * @param array $args
 *
 * @return mixed
 */
function dt_report_insert( $args = [] )
{
    return disciple_tools()->logging_reports_api->insert( $args );
}

/**
 * Disciple_Tools_Reports_API
 * This handles the insert and other functions for the table _dt_reports and _dt_reportmeta tables
 */
class Disciple_Tools_Reports_API
{

    /**
     * Insert Report into _reports and _reportmeta tables
     *
     * @since  0.1.0
     *
     * @param  array $args
     * @param        date   'report_date'
     * @param        string 'report_source'
     * @param        string 'report_subsource'
     * @param        array  'meta_input' this is an array of meta_key and meta_value
     *
     * @return int/bool
     */
    public function insert( $args )
    {
        global $wpdb;

        $args = wp_parse_args(
            $args,
            [
                'report_date'      => date( 'Y-m-d' ),
                'report_source'    => '',
                'report_subsource' => '',
                'focus'            => '',
                'category'         => '',
                'meta_input'       => [],
            ]
        );

        $args['report_date'] = date_create( $args['report_date'] ); // Format submitted date
        $args['report_date'] = date_format( $args['report_date'], "Y-m-d" );

        // Make sure for non duplicate.
        $check_duplicate = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT
                    `id`
                FROM
                    `$wpdb->dt_reports`
                WHERE
                    `report_date` = %s
                    AND `report_source` = %s
                    AND `report_subsource` = %s
                    AND `focus` = %s
                    AND `category` = %s",
                $args['report_date'],
                $args['report_source'],
                $args['report_subsource'],
                $args['focus'],
                $args['category']
            )
        );

        if ( $check_duplicate ) {
            return false;
        }

        $wpdb->insert(
            $wpdb->dt_reports,
            [
                'report_date'      => $args['report_date'],
                'report_source'    => $args['report_source'],
                'report_subsource' => $args['report_subsource'],
                'focus'            => $args['focus'],
                'category'         => $args['category'],
            ],
            [ '%s', '%s', '%s', '%s', '%s' ]
        );

        $report_id = $wpdb->insert_id;

        if ( !empty( $args['meta_input'] ) ) {
            foreach ( $args['meta_input'] as $field => $value ) {
                $this->add_report_meta( $report_id, $field, $value );
            }
        }

        // Final action on insert.
        do_action( 'dt_insert_report', $args );

        return $report_id;
    }

    /**
     * Add Report Metadata
     *
     * @since 0.1.0
     *
     * @param  int    $report_id
     * @param  string $field
     * @param  string $value
     *
     * @return void
     */
    private function add_report_meta( $report_id, $field, $value )
    {
        global $wpdb;

        $wpdb->insert(
            $wpdb->dt_reportmeta,
            [
                'report_id'  => $report_id,
                'meta_key'   => $field,
                'meta_value' => $value,
            ],
            [ '%d', '%s', '%s' ]
        );
    }

    /**
     * @param $report_source
     *
     * @return array|null|object
     */
    public function get_reports_by_source( $report_source )
    {
        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT
                    *
                FROM
                    `$wpdb->dt_reports`
                WHERE
                    `report_source` = %s",
                $report_source
            )
        );

        return $results;
    }

    /**
     * Gets a single report including metadata by the report id
     *
     * @param  $id     int     (required) This is the report id.
     *
     * @return array
     */
    public function get_report_by_id( $id )
    {
        global $wpdb;

        // Get all report detals
        $results = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT
                    *
                FROM
                    `$wpdb->dt_reports`
                WHERE
                    `id` = %s",
                $id
            ),
            ARRAY_A
        );

        // Get all metadata values for the report
        $meta_input = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT
                    *
                FROM
                    `$wpdb->dt_reportmeta`
                WHERE
                    `report_id` = %s",
                $id
            ),
            ARRAY_A
        );

        // Add meta_input to the report array and return
        $results['meta_input'] = $meta_input;

        return $results;
    }

    /**
     * Get meta_value using $id and $key
     *
     * @param $id
     * @param $key
     *
     * @return mixed
     */
    public function get_meta_value( $id, $key )
    {
        global $wpdb;

        // Get all metadata values for the report
        $meta_value = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT
                    meta_value
                FROM
                    `$wpdb->dt_reportmeta`
                WHERE
                    `report_id` = %s
                    AND `meta_key` = %s",
                $id,
                $key
            ),
            ARRAY_A
        );

        return $meta_value['meta_value'];
    }

    /**
     * Get meta key total
     *
     * @param        $date
     * @param        $source
     * @param        $meta_key
     * @param string $type
     *
     * @return int
     * @throws \Exception Type should be one of sum max min and average.
     */
    public function get_meta_key_total( $date, $source, $meta_key, $type = 'sum' )
    {
        global $wpdb;
        $results_int = 0;

        if ( !in_array( strtolower( $type ), [ 'sum', 'max', 'min', 'average' ], true ) ) {
            throw new Exception( "Type should be one of sum max min and average" );
        }

        if ( !preg_match( '/^[a-zA-Z_]+$/', $meta_key ) ) {
            throw new Exception( "To protect against SQL injection attacks, only [a-zA-Z_]+ meta_key arguments are accepted, not $meta_key" );
        }

        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT " // @codingStandardsIgnoreLine
            . " $type(`meta_value`) AS `$meta_key`
                FROM
                    `$wpdb->dt_reports`
                RIGHT JOIN
                    `$wpdb->dt_reportmeta`
                ON
                    `$wpdb->dt_reports`.id = `$wpdb->dt_reportmeta`.report_id
                WHERE
                    `$wpdb->dt_reports`.report_date LIKE %s
                    AND `$wpdb->dt_reports`.report_source = %s
                    AND `$wpdb->dt_reportmeta`.meta_key = %s",
            $wpdb->esc_like( $date ) . '%',
            $source,
            $meta_key
        ), ARRAY_A );

        if ( isset( $results[0] ) ) {
            $results_int = $results[0][ $meta_key ];
        }

        return (int) $results_int;
    }

    /**
     * Gets report ids by date
     *
     * @param  $date      string     This is the supplied date for the report date('Y-m-d') format
     * @param  $source    string    (optional) This argument limits the results to a certain source
     * @param  $subsource string (optional) This argument further limits the results to a specific subsource of the source. Source is still required, in case of subsource naming conflicts.
     *
     * @return array            Returns list of ids that match date and other arguments.
     */
    public function get_report_ids_by_date( $date, $source = null, $subsource = null )
    {
        global $wpdb;

        if ( !empty( $subsource ) && !empty( $source ) ) {
            // Build full query
            $results = $wpdb->get_results( $wpdb->prepare(
                "SELECT
                    id
                FROM
                    `$wpdb->dt_reports`
                WHERE
                    `report_date` LIKE %s,
                    AND `report_source` = %s,
                    AND `report_subsource` = %s",
                $wpdb->esc_like( $date ) . '%',
                $source,
                $subsource
            ), ARRAY_A );
        } elseif ( !empty( $source ) ) {
            // Build limited query
            $results = $wpdb->get_results( $wpdb->prepare(
                "SELECT
                    id
                FROM
                    `$wpdb->dt_reports`
                WHERE
                    `report_date` LIKE %s
                    AND `report_source` = %s",
                $wpdb->esc_like( $date ) . '%',
                $source
            ), ARRAY_A );
        } else {
            // Build date query
            $results = $wpdb->get_results( $wpdb->prepare(
                "SELECT
                    id
                FROM
                    `$wpdb->dt_reports`
                WHERE
                    `report_date` LIKE %s",
                $wpdb->esc_like( $date ) . '%'
            ), ARRAY_A );
        }

        return $results;
    }

    /**
     * Gets full reports with metadata for a single date, and can be filtered by source and subsource
     *
     * @param  $date       string      (required) This is a date formated '2017-03-22'
     * @param  $source     string      (optional) This is the source
     * @param  $subsource  string  (optional) If this is supplied, the source must also be supplied.
     *
     * @return array
     */
    public function get_reports_by_date( $date, $source = null, $subsource = null )
    {
        $report = [];
        $i = 0;

        // get the ids
        $results = $this->get_report_ids_by_date( $date, $source, $subsource );

        // build full record by the id
        foreach ( $results as $result ) {
            $report[ $i ] = $this->get_report_by_id( $result['id'] );
            $i++;
        }

        return $report;
    }

    /**
     * Get the reports for a year, month, and day ranges based on source and optional subsource
     *
     * @param  $date       string  (required)  The month is a formated year and month. 2017-03
     * @param  $source     string  (required)  The source
     * @param  $subsource  string  (optional)  The subsource
     * @param  $id_only    boolean (optional)  By default this is true and will return the ids records, but if set to true it will return only IDs of reports in this date range.
     *
     * @return array
     */
    public function get_month_by_source( $date, $source, $subsource = '', $id_only = true )
    {

        global $wpdb;
        $results = [];

        // check required fields
        if ( empty( $date ) || empty( $source ) ) {
            $results['error'] = 'required fields error';

            return $results;
        }

        // prepare sql
        if ( !empty( $subsource ) ) {
            // Build full query
            $results = $wpdb->get_results( $wpdb->prepare(
                "SELECT "
                // @codingStandardsIgnoreLine
                . ( $id_only ? "id " : "* " )
                . " FROM
                    `$wpdb->dt_reports`
                WHERE
                    `report_date` LIKE %s
                    AND `report_source` = %s
                    AND `report_subsource` = %s",
                $wpdb->esc_like( $date ) . '%',
                $source,
                $subsource
            ), ARRAY_A );
        } else {
            // Build full query
            $results = $wpdb->get_results( $wpdb->prepare(
                "SELECT "
                // @codingStandardsIgnoreLine
                . ( $id_only ? "id " : "* " )
                . " FROM
                    `$wpdb->dt_reports`
                WHERE
                    `report_date` LIKE %s
                    AND `report_source` = %s",
                $wpdb->esc_like( $date ) . '%',
                $source
            ), ARRAY_A );
        }

        return $results;
    }

    /**
     * Gets full reports with metadata for a single date, and can be filtered by source and subsource
     *
     * @param  $date       string      (required) This is a date formated '2017-03-22'
     * @param  $source     string      (optional) This is the source
     * @param  $subsource  string  (optional) If this is supplied, the source must also be supplied.
     *
     * @return mixed
     */
    public function get_month_by_source_full( $date, $source, $subsource )
    {
        $report = [];
        $i = 0;
        $results = $this->get_month_by_source( $date, $source, $subsource, true );

        foreach ( $results as $result ) {
            $report[ $i ] = $this->get_report_by_id( $result['id'] );
            $i++;
        }

        return $report;
    }

    /**
     * Get last value
     *
     * @param        $source
     * @param        $meta_key
     * @param string $subsource
     *
     * @return bool|int
     */
    public function get_last_value( $source, $meta_key, $subsource = '' )
    {

        //        global $wpdb;
        //        $today = date( 'Y-m-d' );

        if ( empty( $source ) || empty( $meta_key ) ) {
            return false;
        }

        // check for recent date
        if ( !empty( $subsource ) ) {
            // loop date to find match with source and subsource

            // select meta value
            $count = 0;
        } else {
            // loop date to find all matches with source

            // select meta values and add
            $count = 0;
        }

        return $count;
    }

    /**
     * @param $source
     *
     * @return bool
     */
    public static function get_last_record_of_source( $source )
    {
        global $wpdb;
        if ( empty( $source ) ) {
            return false;
        }

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT
                    *
                FROM
                    `$wpdb->dt_reports`
                WHERE
                    `report_source` = %s
                    AND report_date = (select max(report_date) from `$wpdb->dt_reports` where `report_source` = %s)",
                $source,
                $source
            )
        );

        if ( sizeof( $results ) > 0 ) {
            return $results[0];
        } else {
            return false;
        }
    }

    /**
     * @param $source
     * @param $subsource
     *
     * @return bool
     */
    public static function get_last_record_of_source_and_subsource( $source, $subsource )
    {
        global $wpdb;
        if ( empty( $source ) ) {
            return false;
        }

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT
                    *
                FROM
                    `$wpdb->dt_reports`
                WHERE
                    `report_source` = %s
                    AND `report_subsource` = %s
                    AND report_date = (select max(report_date) from `$wpdb->dt_reports` where `report_source` = %s)",
                $source,
                $subsource,
                $source
            )
        );

        if ( sizeof( $results ) > 0 ) {
            return $results[0];
        } else {
            return false;
        }
    }

}
