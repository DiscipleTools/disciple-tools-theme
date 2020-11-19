<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

class DT_Metrics_Mapping_Queries {


    private static function format_results( $results ){
        $features = [];
        foreach ( $results as $result ) {
            $features[] = array(
                'type' => 'Feature',
                'properties' => array(
                    "address" => $result['address'],
                    "post_id" => $result['post_id'],
                    "name" => $result['name']
                ),
                'geometry' => array(
                    'type' => 'Point',
                    'coordinates' => array(
                        $result['lng'],
                        $result['lat'],
                        1
                    ),
                ),
            );
        }

        $new_data = array(
            'type' => 'FeatureCollection',
            'features' => $features,
        );

        return $new_data;
    }

    public static function cluster_geojson( $post_type, $query = [] ){
        global $wpdb;
        $results = [];
        $sql = DT_Posts::fields_to_sql( $post_type, $query );
        if ( empty( $sql["where_sql"] ) ){
            $sql["where_sql"] = "1=1";
        }
        //phpcs:disable
        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT lg.label as address, p.post_title as name, lg.post_id, lg.lng, lg.lat
            FROM $wpdb->dt_location_grid_meta as lg
            JOIN $wpdb->posts as p ON p.ID=lg.post_id
            " . $sql["joins_sql"] . "
            WHERE lg.post_type = %s
            AND
            " . $sql["where_sql"] . "
            ", $post_type ), ARRAY_A
        );
        //phpcs:enable

        return self::format_results( $results );
    }

    public static function points_geojson( $post_type, $query = [] ){
        global $wpdb;
        $results = [];
        $sql = DT_Posts::fields_to_sql( $post_type, $query );
        if ( empty( $sql["where_sql"] ) ){
            $sql["where_sql"] = "1=1";
        }
        //phpcs:disable
        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT lgm.label as l, p.post_title as n, lgm.post_id as pid, lgm.lng, lgm.lat, lg.admin0_grid_id as a0, lg.admin1_grid_id as a1
            FROM $wpdb->dt_location_grid_meta as lgm
            LEFT JOIN $wpdb->posts as p ON p.ID=lgm.post_id
            LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
            " . $sql["joins_sql"] . "
            JOIN $wpdb->postmeta as pm ON pm.post_id=lgm.post_id AND meta_key = 'overall_status' AND meta_value = %s
            WHERE lgm.post_type = %s
            AND " . $sql["where_sql"] . "
            LIMIT 40000;
            ", $post_type ), ARRAY_A
        );
        //phpcs:enable

        return self::format_results( $results );
    }


    public static function query_location_grid_meta_totals( $post_type, $query ) {
        global $wpdb;
        $sql = DT_Posts::fields_to_sql( $post_type, $query );
        if ( empty( $sql["where_sql"] ) ){
            $sql["where_sql"] = "1=1";
        }

        //phpcs:disable
        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT t0.admin0_grid_id as grid_id, count(t0.admin0_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                INNER JOIN $wpdb->posts as p ON ( p.ID = lgm.post_id )
                " . $sql["joins_sql"] . "
                WHERE p.post_type = %s
                AND " . $sql["where_sql"] . "
            ) as t0
            GROUP BY t0.admin0_grid_id
            UNION
            SELECT t1.admin1_grid_id as grid_id, count(t1.admin1_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                INNER JOIN $wpdb->posts as p ON ( p.ID = lgm.post_id )
                " . $sql["joins_sql"] . "
                WHERE p.post_type = %s
                AND " . $sql["where_sql"] . "
            ) as t1
            GROUP BY t1.admin1_grid_id
            UNION
            SELECT t2.admin2_grid_id as grid_id, count(t2.admin2_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                INNER JOIN $wpdb->posts as p ON ( p.ID = lgm.post_id )
                " . $sql["joins_sql"] . "
                WHERE p.post_type = %s
                AND " . $sql["where_sql"] . "
            ) as t2
            GROUP BY t2.admin2_grid_id
            UNION
            SELECT t3.admin3_grid_id as grid_id, count(t3.admin3_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                INNER JOIN $wpdb->posts as p ON ( p.ID = lgm.post_id )
                " . $sql["joins_sql"] . "
                WHERE p.post_type = %s
                AND " . $sql["where_sql"] . "
            ) as t3
            GROUP BY t3.admin3_grid_id
            UNION
            SELECT t4.admin4_grid_id as grid_id, count(t4.admin4_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                INNER JOIN $wpdb->posts as p ON ( p.ID = lgm.post_id )
                " . $sql["joins_sql"] . "
                WHERE p.post_type = %s
                AND " . $sql["where_sql"] . "
            ) as t4
            GROUP BY t4.admin4_grid_id
            UNION
            SELECT t5.admin5_grid_id as grid_id, count(t5.admin5_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                INNER JOIN $wpdb->posts as p ON ( p.ID = lgm.post_id )
                " . $sql["joins_sql"] . "
                WHERE p.post_type = %s
                AND " . $sql["where_sql"] . "
            ) as t5
            GROUP BY t5.admin5_grid_id;
            ", $post_type, $post_type, $post_type, $post_type, $post_type, $post_type ), ARRAY_A );
        //phpcs:enable


        $list = [];
        if ( is_array( $results ) ) {
            foreach ( $results as $result ) {
                $list[$result['grid_id']] = $result;
            }
        }

        return $list;
    }

    public static function query_under_location_grid_meta_id( $post_type, $grid_id, $query ) {
        global $wpdb;
        $sql = DT_Posts::fields_to_sql( $post_type, $query );
        if ( empty( $sql["where_sql"] ) ){
            $sql["where_sql"] = "1=1";
        }

        //phpcs:disable
        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT DISTINCT t0.post_title, t0.post_id FROM (
                SELECT p.post_title, lgm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                INNER JOIN $wpdb->posts as p ON p.ID=lgm.post_id
                " . $sql["joins_sql"] . "
                WHERE p.post_type = %s
                AND " . $sql["where_sql"] . "
            ) as t0
            WHERE t0.admin0_grid_id = %d
            UNION
            SELECT DISTINCT t1.post_title, t1.post_id FROM (
                SELECT p.post_title, lgm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                INNER JOIN $wpdb->posts as p ON p.ID=lgm.post_id
                " . $sql["joins_sql"] . "
                WHERE p.post_type = %s
                AND " . $sql["where_sql"] . "
            ) as t1
            WHERE t1.admin1_grid_id = %d
            UNION
            SELECT DISTINCT t2.post_title, t2.post_id  FROM (
                SELECT p.post_title, lgm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                INNER JOIN $wpdb->posts as p ON p.ID=lgm.post_id
                " . $sql["joins_sql"] . "
                WHERE p.post_type = %s
                AND " . $sql["where_sql"] . "
            ) as t2
            WHERE t2.admin2_grid_id = %d
            UNION
            SELECT DISTINCT t3.post_title, t3.post_id  FROM (
                SELECT p.post_title, lgm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                INNER JOIN $wpdb->posts as p ON p.ID=lgm.post_id
                " . $sql["joins_sql"] . "
                WHERE p.post_type = %s
                AND " . $sql["where_sql"] . "
            ) as t3
            WHERE t3.admin3_grid_id = %d
            UNION
            SELECT DISTINCT t4.post_title, t4.post_id  FROM (
                SELECT p.post_title, lgm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                INNER JOIN $wpdb->posts as p ON p.ID=lgm.post_id
                " . $sql["joins_sql"] . "
                WHERE p.post_type = %s
                AND " . $sql["where_sql"] . "
            ) as t4
            WHERE t4.admin4_grid_id = %d
            UNION
            SELECT DISTINCT t5.post_title, t5.post_id  FROM (
                SELECT p.post_title, lgm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                INNER JOIN $wpdb->posts as p ON p.ID=lgm.post_id
                " . $sql["joins_sql"] . "
                WHERE p.post_type = %s
                AND " . $sql["where_sql"] . "
            ) as t5
            WHERE t5.admin5_grid_id = %d;
            ", $post_type, $grid_id, $post_type, $grid_id, $post_type, $grid_id, $post_type, $grid_id, $post_type, $grid_id, $post_type, $grid_id ), ARRAY_A )
        ;
        //phpcs:enable

        return $results;
    }
}
