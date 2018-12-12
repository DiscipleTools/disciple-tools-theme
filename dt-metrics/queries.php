<?php
/**
 * Query factory for metrics
 *
 * @package Disciple_Tools
 * @version 0.1.0
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

function dt_queries() {
    return Disciple_Tools_Metrics_Queries::instance();
}

/**
 * Class Disciple_Tools_Counter_Factory
 */
class Disciple_Tools_Metrics_Queries
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
                return $query;
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
                return $query;
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
                return $query;
                break;

            case 'multiplying_groups_only':
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
                      (SELECT gsmeta.meta_value FROM $wpdb->postmeta as gsmeta WHERE gsmeta.post_id = p.p2p_from AND gsmeta.meta_key = 'group_type' LIMIT 1 ) as group_type
                    FROM $wpdb->p2p as p
                    WHERE p.p2p_type = 'groups_to_groups'
                ", ARRAY_A );
                return $query;
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
                ", ARRAY_A );
                return $query;
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
                return $query;
                break;

            case 'locations':
                $query = $wpdb->get_results("
                    SELECT
                      a.ID as id,
                      a.post_parent  as parent_id,
                      a.post_title as name
                    FROM $wpdb->posts as a
                    WHERE a.post_status = 'publish'
                    AND a.post_type = 'locations'
                ", ARRAY_A );
                return $query;
                break;

            default:
                return false;
                break;
        }
    }

}
