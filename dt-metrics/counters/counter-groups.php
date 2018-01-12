<?php
/**
 * Counts Misc Groups and Church numbers
 *
 * @package Disciple_Tools
 * @version 0.1.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Counter_Groups
 */
class Disciple_Tools_Counter_Groups extends Disciple_Tools_Counter_Base  {

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
     * @param int    $year
     *
     * @return int
     */
    public static function get_groups_count( string $status = '', int $year = null )
    {

        $status = strtolower( $status );

        if ( empty( $year ) ) {
            $year = date( 'Y' ); // default to this year
        }

        switch ( $status ) {

            case 'active_churches':
                $query = new WP_Query(
                    [
                        'post_type'  => 'groups',
                        'date_query' =>
                            [
                                'year' => $year,
                            ],
                        'meta_query' => [
                            [
                                'key' => 'group_status',
                                'value' => 'active_church',
                                'compare' => '=',
                            ]
                        ],
                    ]
                );

                return $query->found_posts;
                break;

            case 'active_groups':
                $query = new WP_Query(
                    [
                        'post_type'  => 'groups',
                        'date_query' =>
                            [
                                'year' => $year,
                            ],
                        'meta_query' => [
                            [
                                'key' => 'group_status',
                                'value' => 'active_group',
                                'compare' => '=',
                            ]
                        ],
                    ]
                );

                return $query->found_posts;
                break;

            case 'uncountable':
                $count = wp_count_posts( 'groups' );
                $other = $count->draft;
                $other = $other + $count->pending;
                $other = $other + $count->private;
                $other = $other + $count->trash;

                return (int) $other;
                break;

            default: // countable contacts
                $count = wp_count_posts( 'groups' );
                $count = $count->publish;

                return $count;
                break;
        }
    }

}
