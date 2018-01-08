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

/**
 * Class Disciple_Tools_Counter_Factory
 */
class Disciple_Tools_Counter
{

    /**
     * Disciple_Tools_Counter_Factory The single instance of Disciple_Tools_Counter_Factory.
     *
     * @var    object
     * @access private
     * @since  0.1.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_Counter_Factory Instance
     * Ensures only one instance of Disciple_Tools_Counter_Factory is loaded or can be loaded.
     *
     * @since  0.1.0
     * @static
     * @return Disciple_Tools_Counter
     */
    public static function instance()
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    } // End instance()

    /**
     * Constructor function
     *
     * @access public
     * @since  0.1.0
     */
    public function __construct()
    {

        // Load required files
        require_once( 'counters/abstract-class-counter.php' );
        require_once( 'counters/counter-connected.php' );
        require_once( 'counters/counter-generations-status.php' );
        require_once( 'counters/counter-baptism.php' );
        require_once( 'counters/counter-groups.php' );
        require_once( 'counters/counter-contacts.php' );
        require_once( 'counters/counter-prayer.php' );
        require_once( 'counters/counter-outreach.php' );
    } // End __construct

    /**
     * Gets the critical path.
     * The steps of the critical path can be called direction, or the entire array for the critical path can be called with 'full'.
     *
     * @param string $step_name
     *
     * @return int|array
     */
    public static function critical_path( string $step_name = '' )
    {

        $step_name = strtolower( $step_name );

        switch ( $step_name ) {

            case 'prayer':
                return Disciple_Tools_Counter_Prayer::get_prayer_count( 'prayer_network' );
                break;
            case 'social_engagement':
                return Disciple_Tools_Counter_Outreach::get_outreach_count( 'social_engagement' );
                break;
            case 'website_visitors':
                return Disciple_Tools_Counter_Outreach::get_outreach_count( 'website_visitors' );
                break;
            case 'new_contacts':
                return Disciple_Tools_Counter_Contacts::get_contacts_count( 'new_contacts' );
                break;
            case 'contacts_attempted':
                return Disciple_Tools_Counter_Contacts::get_contacts_count( 'contacts_attempted' );
                break;
            case 'contacts_established':
                return Disciple_Tools_Counter_Contacts::get_contacts_count( 'contacts_established' );
                break;
            case 'first_meetings':
                return Disciple_Tools_Counter_Contacts::get_contacts_count( 'first_meetings' );
                break;
            case 'baptisms':
                return Disciple_Tools_Counter_Baptism::get_number_of_baptisms();
                break;
            case 'baptizers':
                return Disciple_Tools_Counter_Baptism::get_number_of_baptizers();
                break;
            case 'active_groups':
                return Disciple_Tools_Counter_Groups::get_groups_count( 'active_groups' );
                break;
            case 'active_churches':
                return Disciple_Tools_Counter_Groups::get_groups_count( 'active_churches' );
                break;
            case 'church_planters':
                return Disciple_Tools_Counter_Contacts::get_contacts_count( 'church_planters' );
                break;
            default:
                return [
                    // Prayer
                    'prayer'               => self::critical_path( 'prayer' ),
                    // Outreach
                    'social_engagement'    => self::critical_path( 'social_engagement' ),
                    'website_visitors'     => self::critical_path( 'website_visitors' ),
                    // Follow-up
                    'new_contacts'         => self::critical_path( 'new_contacts' ),
                    'contacts_attempted'   => self::critical_path( 'contacts_attempted' ),
                    'contacts_established' => self::critical_path( 'contacts_established' ),
                    'first_meetings'       => self::critical_path( 'first_meetings' ),
                    // Multiplication
                    'baptisms'             => self::critical_path( 'baptisms' ),
                    'baptizers'            => self::critical_path( 'baptizers' ),
                    'active_groups'        => self::critical_path( 'active_groups' ),
                    'active_churches'      => self::critical_path( 'active_churches' ),
                    'church_planters'      => self::critical_path( 'church_planters' ),
                ];
                break;
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
    public function connection_type_counter( $type, $meta_value )
    {
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
    public function contacts_meta_counter( $meta_key, $meta_value )
    {
        $query = new WP_Query( [ 'meta_key' => $meta_key, 'meta_value' => $meta_value, 'post_type' => 'contacts', ] );

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
    public function groups_meta_counter( $meta_key, $meta_value )
    {
        $query = new WP_Query( [ 'meta_key' => $meta_key, 'meta_value' => $meta_value, 'post_type' => 'groups', ] );

        return $query->found_posts;
    }

    /**
     * Counts baptisms
     *
     * @param $type
     *
     * @return null|string
     */
    public function get_baptisms( $type )
    {
        switch ( $type ) {
            case 'baptisms':
                $count = new Disciple_Tools_Counter_Baptism();
                $result = $count->get_number_of_baptisms();
                break;
            case 'baptizers':
                $count = new Disciple_Tools_Counter_Baptism();
                $result = $count->get_number_of_baptizers();
                break;
            default:
                $result = '';
                break;
        }

        return $result;
    }

    /**
     * Contact generations counting factory
     *
     * @param         $generation_number 1,2,3 etc for generation number
     * @param  string $type              contacts or groups or baptisms
     *
     * @return number
     */
    public function get_generation( $generation_number, $type = 'contacts' )
    {

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
                $count = $gen_object->generation_status_list();
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
    protected function set_connection_type( $type )
    {
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
