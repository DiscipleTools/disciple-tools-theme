<?php

/**
 * Counter factory for reporting
 *
 * @package Disciple.Tools
 * @version 0.1.0
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

function dt_counter() {
    return Disciple_Tools_Counter::instance();
}
function dt_queries() {
    return Disciple_Tools_Queries::instance();
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
        require_once( 'counters/counter-locations.php' );
        require_once( 'counters/counter-post-stats.php' );
    } // End __construct

    /**
     * Gets the critical path.
     * The steps of the critical path can be called direction, or the entire array for the critical path can be called with 'full'.
     *
     * @param string $step_name
     * @param null $start , unix time stamp
     * @param null $end
     * @param array $args
     *
     * @return int|array
     */
    public static function critical_path( string $step_name = 'all', $start = null, $end = null, $args = [] ) {

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
                return Disciple_Tools_Counter_Groups::get_groups_count( 'generations', $start, $end, $args );
                break;
            case 'church_planters':
                return Disciple_Tools_Counter_Groups::get_groups_count( 'church_planters', $start, $end );
                break;
            case 'people_groups':
                break;
//            case 'manual_additions':
//                return Disciple_Tools_Counter_Outreach::get_outreach_count( 'manual_additions', $start, $end );
            case 'all':
//                $manual_additions = self::critical_path( 'manual_additions', $start, $end );
//                $data = [];
//                foreach ( $manual_additions as $addition ){
//                    if ( $addition["section"] == "before") {
//                        $data[] = [
//                            "key" => $addition["source"],
//                            "label" => $addition["label"],
//                            "value" => $addition["total"]
//                        ];
//                    }
//                }
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
                $data[] = [
                    "key" => "church_planters",
                    "label" => __( "Church Planters", "disciple_tools" ),
                    "value" => self::critical_path( 'church_planters', $start, $end )
                ];
//                @todo ppl groups
//                foreach ( $manual_additions as $addition ){
//                    if ( $addition["section"] == "after") {
//                        $data[] = [
//                            "key" => $addition["source"],
//                            "label" => $addition["label"],
//                            "value" => $addition["total"]
//                        ];
//                    }
//                }
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
Disciple_Tools_Counter::instance();



/**
 * Class Disciple_Tools_Counter_Factory
 */
class Disciple_Tools_Queries
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
    } // End __construct

    public function tree( $query_name, $args = [] ) {
        global $wpdb;
        $query = [];

        switch ( $query_name ) {

            case 'baptisms_all':
                /**
                 * Query returns a generation tree with all baptisms in the system, whether multiplying or not.
                 * @columns id
                 *          parent_id
                 *          name
                 */
                $query = $wpdb->get_results("
                    SELECT
                      a.ID         as id,
                      0            as parent_id,
                      a.post_title as name
                    FROM $wpdb->posts as a
                    WHERE a.post_status = 'publish'
                    AND a.post_type = 'contacts'
                    AND a.ID NOT IN (
                    SELECT DISTINCT (p2p_from)
                    FROM $wpdb->p2p
                    WHERE p2p_type = 'baptizer_to_baptized'
                    GROUP BY p2p_from)
                    UNION
                    SELECT
                      p.p2p_from  as id,
                      p.p2p_to    as parent_id,
                      (SELECT sub.post_title FROM $wpdb->posts as sub WHERE sub.ID = p.p2p_from ) as name
                    FROM $wpdb->p2p as p
                    WHERE p.p2p_type = 'baptizer_to_baptized'
                ", ARRAY_A );
                break;

            case 'multiplying_baptisms_only':
                $query = $wpdb->get_results("
                    SELECT
                      a.ID         as id,
                      0            as parent_id,
                      a.post_title as name
                    FROM $wpdb->posts as a
                    WHERE a.post_status = 'publish'
                    AND a.post_type = 'contacts'
                    AND a.ID NOT IN (
                      SELECT DISTINCT (p2p_from)
                      FROM $wpdb->p2p
                      WHERE p2p_type = 'baptizer_to_baptized'
                      GROUP BY p2p_from
                    )
                      AND a.ID IN (
                      SELECT DISTINCT (p2p_to)
                      FROM $wpdb->p2p
                      WHERE p2p_type = 'baptizer_to_baptized'
                      GROUP BY p2p_to
                    )
                    UNION
                    SELECT
                      p.p2p_from  as id,
                      p.p2p_to    as parent_id,
                      (SELECT sub.post_title FROM $wpdb->posts as sub WHERE sub.ID = p.p2p_from ) as name
                    FROM $wpdb->p2p as p
                    WHERE p.p2p_type = 'baptizer_to_baptized'
                ", ARRAY_A );
                break;

            case 'group_all':
                $query = $wpdb->get_results("
                    SELECT
                      a.ID         as id,
                      0            as parent_id,
                      a.post_title as name,
                      gs1.meta_value as group_status,
                      type1.meta_value as group_type
                    FROM $wpdb->posts as a
                      LEFT JOIN $wpdb->postmeta as gs1
                      ON gs1.post_id=a.ID
                      AND gs1.meta_key = 'group_status'
                      LEFT JOIN $wpdb->postmeta as type1
                      ON type1.post_id=a.ID
                      AND type1.meta_key = 'group_type'
                    WHERE a.post_status = 'publish'
                    AND a.post_type = 'groups'
                    AND a.ID NOT IN (
                    SELECT DISTINCT (p2p_from)
                    FROM $wpdb->p2p
                    WHERE p2p_type = 'groups_to_groups'
                    GROUP BY p2p_from)
                    UNION
                    SELECT
                      p.p2p_from                          as id,
                      p.p2p_to                            as parent_id,
                      (SELECT sub.post_title FROM $wpdb->posts as sub WHERE sub.ID = p.p2p_from ) as name,
                      (SELECT gsmeta.meta_value FROM $wpdb->postmeta as gsmeta WHERE gsmeta.post_id = p.p2p_from AND gsmeta.meta_key = 'group_status' LIMIT 1 ) as group_status,
                      (SELECT gsmeta.meta_value FROM $wpdb->postmeta as gsmeta WHERE gsmeta.post_id = p.p2p_from AND gsmeta.meta_key = 'group_type' LIMIT 1 ) as group_type
                    FROM $wpdb->p2p as p
                    WHERE p.p2p_type = 'groups_to_groups'
                ", ARRAY_A );
                break;

            case 'multiplying_groups_only':
                $query = $wpdb->get_results( "
                    SELECT
                      a.ID         as id,
                      0            as parent_id,
                      a.post_title as name,
                      gs1.meta_value as group_status,
                      type1.meta_value as group_type,
                      coach1.post_title as coach,
                      location1.name as location_name,
                      startdate1.meta_value as start_date,
                      enddate1.meta_value as end_date,
                      IFNULL(members1.meta_value, 0) as total_members,
                      IFNULL(believers1.meta_value, 0) as total_believers,
                      IFNULL(baptized1.meta_value, 0) as total_baptized,
                      IFNULL(baptized2.meta_value, 0) as total_baptized_by_group,
                      (SELECT EXISTS (SELECT 1 FROM $wpdb->postmeta as metricbaptized1 WHERE metricbaptized1.post_id = a.ID AND metricbaptized1.meta_key = 'health_metrics' AND metricbaptized1.meta_value = 'church_baptism')) as health_metrics_baptism,
                      (SELECT EXISTS (SELECT 1 FROM $wpdb->postmeta as metricfellowship1 WHERE metricfellowship1.post_id = a.ID AND metricfellowship1.meta_key = 'health_metrics' AND metricfellowship1.meta_value = 'church_fellowship')) as health_metrics_fellowship,
                      (SELECT EXISTS (SELECT 1 FROM $wpdb->postmeta as metricsharing1 WHERE metricsharing1.post_id = a.ID AND metricsharing1.meta_key = 'health_metrics' AND metricsharing1.meta_value = 'church_sharing')) as health_metrics_sharing,
                      (SELECT EXISTS (SELECT 1 FROM $wpdb->postmeta as metricpraise1 WHERE metricpraise1.post_id = a.ID AND metricpraise1.meta_key = 'health_metrics' AND metricpraise1.meta_value = 'church_praise')) as health_metrics_praise,
                      (SELECT EXISTS (SELECT 1 FROM $wpdb->postmeta as metricleaders1 WHERE metricleaders1.post_id = a.ID AND metricleaders1.meta_key = 'health_metrics' AND metricleaders1.meta_value = 'church_leaders')) as health_metrics_leaders,
                      (SELECT EXISTS (SELECT 1 FROM $wpdb->postmeta as metriccommitment1 WHERE metriccommitment1.post_id = a.ID AND metriccommitment1.meta_key = 'health_metrics' AND metriccommitment1.meta_value = 'church_commitment')) as health_metrics_commitment,
                      (SELECT EXISTS (SELECT 1 FROM $wpdb->postmeta as metricbible1 WHERE metricbible1.post_id = a.ID AND metricbible1.meta_key = 'health_metrics' AND metricbible1.meta_value = 'church_bible')) as health_metrics_bible,
                      (SELECT EXISTS (SELECT 1 FROM $wpdb->postmeta as metriccommunion1 WHERE metriccommunion1.post_id = a.ID AND metriccommunion1.meta_key = 'health_metrics' AND metriccommunion1.meta_value = 'church_communion')) as health_metrics_communion,
                      (SELECT EXISTS (SELECT 1 FROM $wpdb->postmeta as metricgiving1 WHERE metricgiving1.post_id = a.ID AND metricgiving1.meta_key = 'health_metrics' AND metricgiving1.meta_value = 'church_giving')) as health_metrics_giving,
                      (SELECT EXISTS (SELECT 1 FROM $wpdb->postmeta as metricprayer1 WHERE metricprayer1.post_id = a.ID AND metricprayer1.meta_key = 'health_metrics' AND metricprayer1.meta_value = 'church_prayer')) as health_metrics_prayer,
                      (SELECT EXISTS (SELECT 1 FROM $wpdb->postmeta as metriccommitment1 WHERE metriccommitment1.post_id = a.ID AND metriccommitment1.meta_key = 'health_metrics' AND metriccommitment1.meta_value = 'church_commitment')) as health_metrics_commitment
                    FROM $wpdb->posts as a
                    LEFT JOIN $wpdb->postmeta as members1
                      ON members1.post_id=a.ID
                      AND members1.meta_key = 'member_count'
                    LEFT JOIN $wpdb->postmeta as believers1
                      ON believers1.post_id=a.ID
                      AND believers1.meta_key = 'believer_count'
                    LEFT JOIN $wpdb->postmeta as baptized1
                      ON baptized1.post_id=a.ID
                      AND baptized1.meta_key = 'baptized_count'
                    LEFT JOIN $wpdb->postmeta as baptized2
                      ON baptized2.post_id=a.ID
                      AND baptized2.meta_key = 'baptized_in_group_count'
                    LEFT JOIN $wpdb->postmeta as gs1
                      ON gs1.post_id=a.ID
                      AND gs1.meta_key = 'group_status'
                    LEFT JOIN $wpdb->postmeta as type1
                      ON type1.post_id=a.ID
                      AND type1.meta_key = 'group_type'
                    LEFT JOIN $wpdb->p2p as groupcoach1
                      ON groupcoach1.p2p_from=a.ID
                      AND groupcoach1.p2p_type = 'groups_to_coaches'
                    LEFT JOIN $wpdb->posts as coach1
                      ON coach1.ID=groupcoach1.p2p_to
                    LEFT JOIN $wpdb->postmeta as grouplocation1
                      ON grouplocation1.post_id=a.ID
                      AND grouplocation1.meta_key = 'location_grid'
                    LEFT JOIN $wpdb->dt_location_grid as location1
                      ON location1.grid_id=grouplocation1.meta_value
                    LEFT JOIN $wpdb->postmeta as startdate1
                      ON startdate1.post_id=a.ID
                      AND startdate1.meta_key = 'church_start_date'
                    LEFT JOIN $wpdb->postmeta as enddate1
                      ON enddate1.post_id=a.ID
                      AND enddate1.meta_key = 'end_date'
                    WHERE a.post_status = 'publish'
                    AND a.post_type = 'groups'
                    AND a.ID NOT IN (
                      SELECT DISTINCT (p2p_from)
                      FROM $wpdb->p2p
                      WHERE p2p_type = 'groups_to_groups'
                      GROUP BY p2p_from
                    )
                      AND a.ID IN (
                      SELECT DISTINCT (p2p_to)
                      FROM $wpdb->p2p
                      WHERE p2p_type = 'groups_to_groups'
                      GROUP BY p2p_to
                    )
                    UNION
                    SELECT
                      p.p2p_from  as id,
                      p.p2p_to    as parent_id,
                      (SELECT sub.post_title FROM $wpdb->posts as sub WHERE sub.ID = p.p2p_from ) as name,
                      (SELECT gsmeta.meta_value FROM $wpdb->postmeta as gsmeta WHERE gsmeta.post_id = p.p2p_from AND gsmeta.meta_key = 'group_status' LIMIT 1 ) as group_status,
                      (SELECT gsmeta.meta_value FROM $wpdb->postmeta as gsmeta WHERE gsmeta.post_id = p.p2p_from AND gsmeta.meta_key = 'group_type' LIMIT 1 ) as group_type,
                      gcoach1.post_title as coach,
                      glocation1.name as location,
                      gstartdate1.meta_value as start_date,
                      genddate1.meta_value as end_date,
                      IFNULL(gmembers1.meta_value, 0) as total_members,
                      IFNULL(gbelievers1.meta_value, 0) as total_believers,
                      IFNULL(gbaptized1.meta_value, 0) as total_believers,
                      IFNULL(gbaptized2.meta_value, 0) as total_baptized_by_group,
                      (SELECT EXISTS (SELECT 1 FROM $wpdb->postmeta as gmetricbaptized1 WHERE gmetricbaptized1.post_id = p.p2p_from AND gmetricbaptized1.meta_key = 'health_metrics' AND gmetricbaptized1.meta_value = 'church_baptism')) as health_metrics_baptism,
                      (SELECT EXISTS (SELECT 1 FROM $wpdb->postmeta as gmetricfellowship1 WHERE gmetricfellowship1.post_id = p.p2p_from AND gmetricfellowship1.meta_key = 'health_metrics' AND gmetricfellowship1.meta_value = 'church_fellowship')) as health_metrics_fellowship,
                      (SELECT EXISTS (SELECT 1 FROM $wpdb->postmeta as gmetricsharing1 WHERE gmetricsharing1.post_id = p.p2p_from AND gmetricsharing1.meta_key = 'health_metrics' AND gmetricsharing1.meta_value = 'church_sharing')) as health_metrics_sharing,
                      (SELECT EXISTS (SELECT 1 FROM $wpdb->postmeta as gmetricpraise1 WHERE gmetricpraise1.post_id = p.p2p_from AND gmetricpraise1.meta_key = 'health_metrics' AND gmetricpraise1.meta_value = 'church_praise')) as health_metrics_praise,
                      (SELECT EXISTS (SELECT 1 FROM $wpdb->postmeta as gmetricleaders1 WHERE gmetricleaders1.post_id = p.p2p_from AND gmetricleaders1.meta_key = 'health_metrics' AND gmetricleaders1.meta_value = 'church_leaders')) as health_metrics_leaders,
                      (SELECT EXISTS (SELECT 1 FROM $wpdb->postmeta as gmetriccommitment1 WHERE gmetriccommitment1.post_id = p.p2p_from AND gmetriccommitment1.meta_key = 'health_metrics' AND gmetriccommitment1.meta_value = 'church_commitment')) as health_metrics_commitment,
                      (SELECT EXISTS (SELECT 1 FROM $wpdb->postmeta as gmetricbible1 WHERE gmetricbible1.post_id = p.p2p_from AND gmetricbible1.meta_key = 'health_metrics' AND gmetricbible1.meta_value = 'church_bible')) as health_metrics_bible,
                      (SELECT EXISTS (SELECT 1 FROM $wpdb->postmeta as gmetriccommunion1 WHERE gmetriccommunion1.post_id = p.p2p_from AND gmetriccommunion1.meta_key = 'health_metrics' AND gmetriccommunion1.meta_value = 'church_communion')) as health_metrics_communion,
                      (SELECT EXISTS (SELECT 1 FROM $wpdb->postmeta as gmetricgiving1 WHERE gmetricgiving1.post_id = p.p2p_from AND gmetricgiving1.meta_key = 'health_metrics' AND gmetricgiving1.meta_value = 'church_giving')) as health_metrics_giving,
                      (SELECT EXISTS (SELECT 1 FROM $wpdb->postmeta as gmetricprayer1 WHERE gmetricprayer1.post_id = p.p2p_from AND gmetricprayer1.meta_key = 'health_metrics' AND gmetricprayer1.meta_value = 'church_prayer')) as health_metrics_prayer,
                      (SELECT EXISTS (SELECT 1 FROM $wpdb->postmeta as gmetriccommitment1 WHERE gmetriccommitment1.post_id = p.p2p_from AND gmetriccommitment1.meta_key = 'health_metrics' AND gmetriccommitment1.meta_value = 'church_commitment')) as health_metrics_commitment
                    FROM $wpdb->p2p as p
                    LEFT JOIN $wpdb->postmeta as gmembers1
                      ON gmembers1.post_id=p.p2p_from
                      AND gmembers1.meta_key = 'member_count'
                    LEFT JOIN $wpdb->postmeta as gbelievers1
                      ON gbelievers1.post_id=p.p2p_from
                      AND gbelievers1.meta_key = 'believer_count'
                    LEFT JOIN $wpdb->postmeta as gbaptized1
                      ON gbaptized1.post_id=p.p2p_from
                      AND gbaptized1.meta_key = 'baptized_count'
                    LEFT JOIN $wpdb->postmeta as gbaptized2
                      ON gbaptized2.post_id=p.p2p_from
                      AND gbaptized2.meta_key = 'baptized_in_group_count'
                    LEFT JOIN $wpdb->p2p as ggroupcoach1
                      ON ggroupcoach1.p2p_from=p.p2p_from
                      AND ggroupcoach1.p2p_type = 'groups_to_coaches'
                    LEFT JOIN $wpdb->posts as gcoach1
                      ON gcoach1.ID=ggroupcoach1.p2p_to
                    LEFT JOIN $wpdb->postmeta as ggrouplocation1
                      ON ggrouplocation1.post_id=p.p2p_from
                      AND ggrouplocation1.meta_key = 'location_grid'
                    LEFT JOIN $wpdb->dt_location_grid as glocation1
                      ON glocation1.grid_id=ggrouplocation1.meta_value
                    LEFT JOIN $wpdb->postmeta as gstartdate1
                      ON gstartdate1.post_id=p.p2p_from
                      AND gstartdate1.meta_key = 'start_date'
                    LEFT JOIN $wpdb->postmeta as genddate1
                      ON genddate1.post_id=p.p2p_from
                      AND genddate1.meta_key = 'end_date'
                    WHERE p.p2p_type = 'groups_to_groups'
                ", ARRAY_A );
                break;

            case 'coaching_all':
                $query = $wpdb->get_results("
                    SELECT
                      a.ID         as id,
                      0            as parent_id,
                      a.post_title as name
                    FROM $wpdb->posts as a
                    WHERE a.post_status = 'publish'
                    AND a.post_type = 'contacts'
                    AND a.ID NOT IN (
                    SELECT DISTINCT (p2p_from)
                    FROM $wpdb->p2p
                    WHERE p2p_type = 'contacts_to_contacts'
                    GROUP BY p2p_from)
                    UNION
                    SELECT
                      p.p2p_from                          as id,
                      p.p2p_to                            as parent_id,
                      (SELECT sub.post_title FROM $wpdb->posts as sub WHERE sub.ID = p.p2p_from ) as name
                    FROM $wpdb->p2p as p
                    WHERE p.p2p_type = 'contacts_to_contacts'
                ", ARRAY_A);
                break;

            case 'multiplying_coaching_only':
                $query = $wpdb->get_results("
                    SELECT
                      a.ID         as id,
                      0            as parent_id,
                      a.post_title as name
                    FROM $wpdb->posts as a
                    WHERE a.post_status = 'publish'
                    AND a.post_type = 'contacts'
                    AND a.ID NOT IN (
                      SELECT DISTINCT (p2p_from)
                      FROM $wpdb->p2p
                      WHERE p2p_type = 'contacts_to_contacts'
                      GROUP BY p2p_from
                    )
                      AND a.ID IN (
                      SELECT DISTINCT (p2p_to)
                      FROM $wpdb->p2p
                      WHERE p2p_type = 'contacts_to_contacts'
                      GROUP BY p2p_to
                    )
                    UNION
                    SELECT
                      p.p2p_from  as id,
                      p.p2p_to    as parent_id,
                      (SELECT sub.post_title FROM $wpdb->posts as sub WHERE sub.ID = p.p2p_from ) as name
                    FROM $wpdb->p2p as p
                    WHERE p.p2p_type = 'contacts_to_contacts'
                ", ARRAY_A );
                break;

            case 'user_hero_stats':
                $query = $wpdb->get_row("
                    SELECT
                      (
                        SELECT
                          COUNT( a.ID )
                        FROM $wpdb->posts as a
                                    WHERE a.post_status = 'publish'
                                    AND a.post_type = 'locations'
                      ) as total_locations,
                      (
                        SELECT
                          COUNT( DISTINCT a.ID )
                        FROM $wpdb->posts as a
                        WHERE a.post_status = 'publish'
                              AND a.ID IN ( SELECT p2p_to FROM $wpdb->p2p WHERE p2p_type = 'contacts_to_locations' OR p2p_type = 'groups_to_locations' )
                              AND a.post_type = 'locations'
                      ) as total_active_locations,
                      (
                        SELECT
                          COUNT( DISTINCT a.ID )
                        FROM $wpdb->posts as a
                        WHERE a.post_status = 'publish'
                              AND a.ID NOT IN ( SELECT p2p_to FROM $wpdb->p2p WHERE p2p_type = 'contacts_to_locations' OR p2p_type = 'groups_to_locations' )
                              AND a.post_type = 'locations'
                      ) as total_inactive_locations,
                      (
                        SELECT
                          COUNT( DISTINCT a.ID )
                        FROM $wpdb->posts as a
                        WHERE a.post_status = 'publish'
                              AND a.ID IN ( SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'types' AND meta_value = 'country' )
                              AND a.post_type = 'locations'
                      ) as total_countries,
                      (
                        SELECT
                          COUNT( DISTINCT a.ID )
                        FROM $wpdb->posts as a
                        WHERE a.post_status = 'publish'
                              AND a.ID IN ( SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'types' AND meta_value = 'administrative_area_level_1' )
                              AND a.post_type = 'locations'
                      ) as total_states,
                      (
                        SELECT
                          COUNT( DISTINCT a.ID )
                        FROM $wpdb->posts as a
                        WHERE a.post_status = 'publish'
                              AND a.ID IN ( SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'types' AND meta_value = 'administrative_area_level_2' )
                              AND a.post_type = 'locations'
                      ) as total_counties;
                ");
                break;

            default:
                break;
        }


        return $this->check_tree_health( $query );


    }

    /**
     * Check the health of a generational tree
     * Find circular structure looping on itself
     * Find orphaned groups
     * @param array $tree
     * @return array|WP_Error True for healthy tree. WP_Error when there are issues.
     */
    public function check_tree_health( array $tree ){

        $nodes = $tree;
        $not_circular = $this->check_circular_logic( $nodes );
        if ( is_numeric( $not_circular ) ){
            return new WP_Error( 500, "Circular tree structure detected with record: " . $not_circular . ".", [ "record" => $not_circular, "link" => get_permalink( $not_circular ) ] );
        }
        foreach ( $nodes as $node ){
            if ( !isset( $node["done"] ) ){
                return new WP_Error( 500, "Orphaned tree structure detected with record: " . $node['id'] . ".", [ "record" => $node["id"], "link" => get_permalink( $node["id"] ) ] );
            }
        }
        return $tree;
    }


    /**
     * Check to see if there is any circular logic in the tree
     * @param $nodes
     * @param int[] $node_ids
     * @param array $parents
     * @return bool|int true if good, int of the record ID if circular tree detected
     */
    private function check_circular_logic( &$nodes, $node_ids = [ 0 ], $parents = [] ){

        foreach ( $nodes as &$node ){
            $parent_id = $node["parent_id"] ?? $node["parentId"];
            if ( in_array( (int) $parent_id, $node_ids ) ){
                //if the node is already in the tree
                if ( in_array( (int) $node["id"], $parents, true )){
                    return (int) $node["id"]; //return the ID of the node
                }
                $node["done"] = true; //lets us look at what nodes where not processed later
                //continue with children
                $d = $this->check_circular_logic( $nodes, [ (int) $node["id"] ], array_merge( $parents, [ (int) $node["id"] ] ) );
                if ( is_numeric( $d ) ){
                    return $d;
                }
            }
        }
        return true;
    }


    public function get_node_descendants( $nodes, $node_ids ){
        $descendants = [];
        $children = [];
        foreach ( $nodes as $node ){
            $parent_id = $node["parent_id"] ?? $node["parentId"];
            if ( in_array( $parent_id, $node_ids ) ){
                $descendants[] = $node;
                $children[] = $node["id"];
            }
        }
        if ( sizeof( $children ) > 0 ){
            $descendants = array_merge( $descendants, $this->get_node_descendants( $nodes, $children ) );
        }
        return $descendants;
    }

}
