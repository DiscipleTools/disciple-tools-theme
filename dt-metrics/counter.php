<?php

/**
 * Counter factory for reporting
 *
 * @package Disciple_Tools
 * @version 0.1.0
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

function dt_counter() {
    return Disciple_Tools_Counter::instance();
}

/**
 * Class Disciple_Tools_Counter_Factory
 */
class Disciple_Tools_Counter
{
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        // Load required files
        require_once( 'counters/abstract-class-counter.php' );
        require_once( 'counters/counter-connected.php' );
        require_once( 'counters/counter-generations-status.php' );
        require_once( 'counters/counter-baptism.php' );
        require_once( 'counters/counter-groups.php' );
        require_once( 'counters/counter-contacts.php' );
        require_once( 'counters/counter-outreach.php' );
    } // End __construct

    /**
     * Gets the critical path.
     * The steps of the critical path can be called direction, or the entire array for the critical path can be called with 'full'.
     *
     * @param string $step_name
     * @param null $start , unix time stamp
     * @param null $end
     *
     * @return int|array
     */
    public static function critical_path( string $step_name = 'all', $start = null, $end = null ) {

        if ( $start === null){
            $start = 0;
        }
        if ( $end === null ){
            $end = PHP_INT_MAX;
        }

        $step_name = strtolower( $step_name );

        switch ( $step_name ) {
            case 'new_contacts':
                return Disciple_Tools_Counter_Contacts::get_contacts_count( 'new_contacts', $start, $end );
                break;
            case 'contacts_attempted':
//                return Disciple_Tools_Counter_Contacts::get_contacts_count( 'contacts_attempted' );
                break;
            case 'contacts_established':
//                return Disciple_Tools_Counter_Contacts::get_contacts_count( 'contacts_established' );
                break;
            case 'first_meetings':
                return Disciple_Tools_Counter_Contacts::get_contacts_count( 'first_meetings', $start, $end );
                break;
            case 'ongoing_meetings':
                return Disciple_Tools_Counter_Contacts::get_contacts_count( 'ongoing_meetings', $start, $end );
                break;
            case 'baptisms':
                return Disciple_Tools_Counter_Baptism::get_number_of_baptisms( $start, $end );
                break;
            case 'baptism_generations':
                return Disciple_Tools_Counter_Baptism::get_baptism_generations( $start, $end );
                break;
            case 'baptizers':
                return Disciple_Tools_Counter_Baptism::get_number_of_baptizers( $start, $end );
                break;
            case 'churches_and_groups':
                return Disciple_Tools_Counter_Groups::get_groups_count( 'churches_and_groups', $start, $end );
                break;
            case 'active_groups':
                return Disciple_Tools_Counter_Groups::get_groups_count( 'active_groups', $start, $end );
                break;
            case 'active_churches':
                return Disciple_Tools_Counter_Groups::get_groups_count( 'active_churches', $start, $end );
                break;
            case 'church_generations':
                return Disciple_Tools_Counter_Groups::get_groups_count( 'church_generations', $start, $end );
                break;
            case 'all_group_generations':
                return Disciple_Tools_Counter_Groups::get_groups_count( 'generations', $start, $end );
                break;
            case 'church_planters':
                return Disciple_Tools_Counter_Contacts::get_contacts_count( 'church_planters', $start, $end );
                break;
            case 'people_groups':

            case 'manual_additions':
                return Disciple_Tools_Counter_Outreach::get_outreach_count( 'manual_additions', $start, $end );
            case 'all':
                $manual_additions = self::critical_path( 'manual_additions', $start, $end );
                $data = [];
                foreach ( $manual_additions as $addition ){
                    if ( $addition["section"] == "before") {
                        $data[] = [
                            "key" => $addition["source"],
                            "label" => $addition["label"],
                            "value" => $addition["total"]
                        ];
                    }
                }
                $data[] = [
                    "key" => "new_contacts",
                    "label" => __( "New Contacts", "disciple_tools" ),
                    "value" => self::critical_path( 'new_contacts', $start, $end )
                ];
                $data[] = [
                    "key" => "first_meetings",
                    "label" => __( "First Meetings", "disciple_tools" ),
                    "value" => self::critical_path( 'first_meetings', $start, $end )
                    ];
                $data[] = [
                    "key" => "ongoing_meetings",
                    "label" => __( "Ongoing Meetings", "disciple_tools" ),
                    "value" => self::critical_path( 'ongoing_meetings', $start, $end )
                    ];
                $data[] = [
                    "key" => "baptisms",
                    "label" => __( "Total Baptisms", "disciple_tools" ),
                    "value" => self::critical_path( 'baptisms', $start, $end )
                ];
                $data[] = [
                    "key" => "baptizers",
                    "label" => __( "Total Baptizers", "disciple_tools" ),
                    "value" => self::critical_path( 'baptizers', $start, $end )
                ];
                $baptism_generations = self::critical_path( 'baptism_generations', $start, $end );
                foreach ( $baptism_generations as $generation => $num ){
                    $data[] = [
                        "key" => "baptism_generation_$generation",
                        "label" => __( "Baptism Generation", "disciple_tools" ) . ' ' . $generation,
                        "value" => $num
                    ];
                }
                $data[] = [
                    "key" => "churches_and_groups",
                    "label" => __( "Total Churches and Groups", "disciple_tools" ),
                    "value" => self::critical_path( 'churches_and_groups', $start, $end )
                ];
                $data[] = [
                    "key" => "active_groups",
                    "label" => __( "Active Groups", "disciple_tools" ),
                    "value" => self::critical_path( 'active_groups', $start, $end )
                ];
                $data[] = [
                    "key" => "active_churches",
                    "label" => __( "Active Churches", "disciple_tools" ),
                    "value" => self::critical_path( 'active_churches', $start, $end )
                ];
                $church_generations = self::critical_path( 'church_generations', $start, $end );
                foreach ( $church_generations as $generation => $num ){
                    $data[] = [
                        "key" => "church_generation_$generation",
                        "label" => __( "Church Generation", "disciple_tools" ) . ' ' . $generation,
                        "value" => $num
                    ];
                }
//                @todo church planters && ppl groups
                foreach ( $manual_additions as $addition ){
                    if ( $addition["section"] == "after") {
                        $data[] = [
                            "key" => $addition["source"],
                            "label" => $addition["label"],
                            "value" => $addition["total"]
                        ];
                    }
                }
                return $data;
                break;
            default:
                return 0;
        }
    }


    /**
     * Counts the meta_data attached to a P2P connection
     *
     * @param $type
     * @param $meta_value
     *
     * @return null|string
     */
    public function connection_type_counter( $type, $meta_value ) {
        $type = $this->set_connection_type( $type );
        $count = new Disciple_Tools_Counter_Connected();
        $result = $count->has_meta_value( $type, $meta_value );

        return $result;
    }

    /**
     * Counts Contacts with matching $meta_key and $meta_value provided.
     * Used to retrieve the number of contacts that match the meta_key and meta_value supplied.
     * Example usage: How many contacts have the "unassigned" status? or How many contacts have a "Contact Attempted" status?
     *
     * @param $meta_key
     * @param $meta_value
     *
     * @return int
     */
    public function contacts_meta_counter( $meta_key, $meta_value ) {
        $query = new WP_Query( [
            'meta_key' => $meta_key,
            'meta_value' => $meta_value,
            'post_type' => 'contacts',
        ] );

        return $query->found_posts;
    }

    /**
     * Counts Contacts with matching $meta_key and $meta_value provided.
     * Used to retrieve the number of contacts that match the meta_key and meta_value supplied.
     * Example usage: How many contacts have the "unassigned" status? or How many contacts have a "Contact Attempted" status?
     *
     * @param $meta_key
     * @param $meta_value
     *
     * @return int
     */
    public function groups_meta_counter( $meta_key, $meta_value ) {
        $query = new WP_Query( [
            'meta_key' => $meta_key,
            'meta_value' => $meta_value,
            'post_type' => 'groups',
        ] );

        return $query->found_posts;
    }

    /**
     * Contact generations counting factory
     *
     * @param         $generation_number 1,2,3 etc for generation number
     * @param  string $type              contacts or groups or baptisms
     *
     * @return number
     */
    public function get_generation( $generation_number, $type = 'contacts' ) {

        // Set the P2P type for selecting group or contacts
        $type = $this->set_connection_type( $type );

        switch ( $generation_number ) {

            case 'has_one_or_more':
                $gen_object = new Disciple_Tools_Counter_Connected();
                $count = $gen_object->has_at_least( 1, $type );
                break;

            case 'has_two_or_more':
                $gen_object = new Disciple_Tools_Counter_Connected();
                $count = $gen_object->has_at_least( 2, $type );
                break;

            case 'has_three_or_more':
                $gen_object = new Disciple_Tools_Counter_Connected();
                $count = $gen_object->has_at_least( 3, $type );
                break;

            case 'has_0':
                $gen_object = new Disciple_Tools_Counter_Connected();
                $count = $gen_object->has_zero( $type );
                break;

            case 'has_1':
                $gen_object = new Disciple_Tools_Counter_Connected();
                $count = $gen_object->has_exactly( 1, $type );
                break;

            case 'has_2':
                $gen_object = new Disciple_Tools_Counter_Connected();
                $count = $gen_object->has_exactly( 2, $type );
                break;

            case 'has_3':
                $gen_object = new Disciple_Tools_Counter_Connected();
                $count = $gen_object->has_exactly( 3, $type );
                break;

            case 'generation_list':
                $gen_object = new Disciple_Tools_Counter_Generations();
                $count = $gen_object->generation_status_list( $type );
                break;

            case 'at_zero':
                $gen_object = new Disciple_Tools_Counter_Generations();
                $count = $gen_object->gen_level( 0, $type );
                break;

            case 'at_first':
                $gen_object = new Disciple_Tools_Counter_Generations();
                $count = $gen_object->gen_level( 1, $type );
                break;

            case 'at_second':
                $gen_object = new Disciple_Tools_Counter_Generations();
                $count = $gen_object->gen_level( 2, $type );
                break;

            case 'at_third':
                $gen_object = new Disciple_Tools_Counter_Generations();
                $count = $gen_object->gen_level( 3, $type );
                break;

            case 'at_fourth':
                $gen_object = new Disciple_Tools_Counter_Generations();
                $count = $gen_object->gen_level( 4, $type );
                break;

            case 'at_fifth':
                $gen_object = new Disciple_Tools_Counter_Generations();
                $count = $gen_object->gen_level( 5, $type );
                break;

            default:
                $count = null;
                break;
        }

        return $count;
    }

    /**
     * Sets the p2p_type for the where statement
     *
     * @param  string = 'contacts' or 'groups' or 'baptisms'
     *
     * @return string
     */
    protected function set_connection_type( $type ) {
        if ( $type == 'contacts' ) {
            $type = 'contacts_to_contacts';
        } elseif ( $type == 'groups' ) {
            $type = 'groups_to_groups';
        } elseif ( $type == 'baptisms' ) {
            $type = 'baptizer_to_baptized';
        } elseif ( $type == 'participation' ) {
            $type = 'contacts_to_groups';
        } else {
            $type = '';
        }

        return $type;
    }

}
