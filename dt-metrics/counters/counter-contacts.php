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
    public function __construct()
    {
        parent::__construct();
    } // End __construct()

    /**
     * Returns count of contacts for different statuses
     * Primary 'countable'
     *
     * @param string $status
     * @param int    $year
     *
     * @return int
     */
    public static function get_contacts_count( string $status = '', int $year = null )
    {

        $status = strtolower( $status );

        if ( empty( $year ) ) {
            $year = date( 'Y' ); // default to this year
        }

        switch ( $status ) {

            case 'new_contacts':
                $query = new WP_Query(
                    [
                        'post_type'  => 'contacts',
                        'date_query' => [ 'year' => $year ],
                    ]
                );

                return $query->found_posts;
                break;

            case 'contacts_attempted':
                $query = new WP_Query(
                    [
                        'post_type'  => 'contacts',
                        'date_query' =>
                            [
                                'year' => $year,
                            ],
                        'meta_query' => [
                            'relation' => 'OR',
                            [
                                'key' => 'seeker_path',
                                'value' => 'attempted',
                                'compare' => '=',
                            ],
                            [
                                'key' => 'seeker_path',
                                'value' => 'scheduled',
                                'compare' => '=',
                            ],
                            [
                                'key' => 'seeker_path',
                                'value' => 'met',
                                'compare' => '=',
                            ],
                            [
                                'key' => 'seeker_path',
                                'value' => 'ongoing',
                                'compare' => '=',
                            ],
                            [
                                'key' => 'seeker_path',
                                'value' => 'coaching',
                                'compare' => '=',
                            ],
                        ],
                    ]
                );

                return $query->found_posts;
                break;

            case 'contacts_established':
                $query = new WP_Query(
                    [
                        'post_type'  => 'contacts',
                        'date_query' =>
                            [
                                'year' => $year,
                            ],
                        'meta_query' => [
                            'relation' => 'OR',
                            [
                                'key' => 'seeker_path',
                                'value' => 'scheduled',
                                'compare' => '=',
                            ],
                            [
                                'key' => 'seeker_path',
                                'value' => 'met',
                                'compare' => '=',
                            ],
                            [
                                'key' => 'seeker_path',
                                'value' => 'ongoing',
                                'compare' => '=',
                            ],
                            [
                                'key' => 'seeker_path',
                                'value' => 'coaching',
                                'compare' => '=',
                            ],
                        ],
                    ]
                );

                return $query->found_posts;
                break;

            case 'first_meetings':
                $query = new WP_Query(
                    [
                        'post_type'  => 'contacts',
                        'date_query' =>
                            [
                                'year' => $year,
                            ],
                        'meta_query' => [
                            'relation' => 'OR',
                            [
                                'key' => 'seeker_path',
                                'value' => 'met',
                                'compare' => '=',
                            ],
                            [
                                'key' => 'seeker_path',
                                'value' => 'ongoing',
                                'compare' => '=',
                            ],
                            [
                                'key' => 'seeker_path',
                                'value' => 'coaching',
                                'compare' => '=',
                            ],
                        ],
                    ]
                );

                return $query->found_posts;
                break;

            case 'church_planters':
                /**
                 * Definition: A church planter is a contact whom is coaching another contact and that 'coached' contact is in an active church.
                 */
                global $wpdb;
                $result = $wpdb->get_var( "
                    SELECT COUNT( DISTINCT p2p_to ) AS church_planters
                    FROM $wpdb->p2p
                    WHERE p2p_type = 'contacts_to_contacts'
                          AND p2p_from IN (
                                SELECT p2p_from as coached
                                FROM $wpdb->p2p
                                WHERE p2p_type = 'contacts_to_groups'
                                    AND p2p_to IN (
                                        SELECT post_id AS church 
                                        FROM $wpdb->postmeta 
                                        WHERE meta_key = 'group_status' 
                                            AND meta_value = 'active_church'
                                    )
                                GROUP BY p2p_from
                          )
                    " );
                return $result;
                break;

            case 'uncountable':
                $count = wp_count_posts( 'contacts' );
                $other = $count->draft;
                $other = $other + $count->pending;
                $other = $other + $count->private;
                $other = $other + $count->trash;

                return (int) $other;
                break;

            default: // countable contacts
                $count = wp_count_posts( 'contacts' );
                $count = $count->publish;

                return $count;
                break;
        }
    }

}
