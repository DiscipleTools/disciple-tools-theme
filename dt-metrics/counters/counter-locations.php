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
class Disciple_Tools_Counter_Locations extends Disciple_Tools_Counter_Base
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

    public static function count( string $status = '', int $start = 0, $end = null ) {

        $year = dt_get_year_from_timestamp( $start );
        $status = strtolower( $status );

        if ( empty( $year ) ) {
            $year = gmdate( 'Y' ); // default to this year
        }

        switch ( $status ) {

            case 'total':

                global $wpdb;
                $results = $wpdb->get_results( $wpdb->prepare("
                    
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
                return [];
                break;
        }
    }

}
