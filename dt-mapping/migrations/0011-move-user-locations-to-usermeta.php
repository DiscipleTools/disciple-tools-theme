<?php

/**
 * Class DT_Mapping_Module_Migration_0010
 * Migrates contact user locations to usermeta table for new user locations system.
 */
class DT_Mapping_Module_Migration_0011 extends DT_Mapping_Module_Migration {

    /**
     * @throws \Exception  Got error when creating table $name.
     */
    public function up() {
        global $wpdb;
        // get all user contact locations_grids

        $mapbox_key = $wpdb->get_var( "SELECT option_value FROM $wpdb->options WHERE option_name = 'dt_mapbox_api_key' " );

        $lgm = [];
        $list = $wpdb->get_results( "SELECT * FROM $wpdb->dt_location_grid_meta WHERE post_type = 'contacts' ", ARRAY_A );
        foreach ( $list as $row ) {
            $lgm[$row['grid_meta_id']] = $row;
        }

        if ( ! empty( $mapbox_key ) ) {
            $results = $wpdb->get_results("
            SELECT p.ID as contact_id, pm1.meta_value as user_id, pm3.meta_id as lgm_postmeta_id, pm3.meta_value as grid_meta_id
            FROM $wpdb->posts as p
            JOIN $wpdb->postmeta as pm1 ON p.ID=pm1.post_id AND pm1.meta_key = 'corresponds_to_user'
            JOIN $wpdb->postmeta as pm3 ON p.ID=pm3.post_id AND pm3.meta_key = 'location_grid_meta';", ARRAY_A );

            foreach ( $results as $result ) {
                // if lgm is set
                if ( ! empty( $result['grid_meta_id'] ) ) {
                    $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->dt_location_grid_meta WHERE grid_meta_id = %s", $result['grid_meta_id'] ), ARRAY_A );

                    $postmeta_id_location_grid = add_user_meta( $result['user_id'], $wpdb->prefix . 'location_grid', $row['grid_id'] );
                    $data = [
                        'post_id' => $result['user_id'],
                        'post_type' => 'users',
                        'postmeta_id_location_grid' => $postmeta_id_location_grid,
                        'grid_id' => $row['grid_id'],
                        'lng' => $row['lng'],
                        'lat' => $row['lat'],
                        'level' => $row['level'],
                        'source' => $row['source'],
                        'label' => $row['label'],
                    ];

                    $format = [
                        '%d',
                        '%s',
                        '%d',
                        '%d',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s'
                    ];

                    $wpdb->insert( $wpdb->dt_location_grid_meta, $data, $format );
                    $location_grid_meta_mid = add_user_meta( $result['user_id'], $wpdb->prefix . 'location_grid_meta', $wpdb->insert_id );
                    error_log( $location_grid_meta_mid );

                }
            }
        } else {
            $results = $wpdb->get_results("
            SELECT p.ID as contact_id, pm1.meta_value as user_id, pm2.meta_id as lg_post_meta_id, pm2.meta_value as grid_id
            FROM $wpdb->posts as p
            JOIN $wpdb->postmeta as pm1 ON p.ID=pm1.post_id AND pm1.meta_key = 'corresponds_to_user'
            JOIN $wpdb->postmeta as pm2 ON p.ID=pm2.post_id AND pm2.meta_key = 'location_grid';", ARRAY_A );

            foreach ( $results as $result ) {
                if ( isset( $result['grid_id'] ) ) {
                    add_user_meta( $result['user_id'], $wpdb->prefix . 'location_grid', $result['grid_id'] );
                }
            }
        }
    }

    /**
     * @throws \Exception  Got error when dropping table $name.
     */
    public function down() {
    }

    /**
     * @return array
     */
    public function get_expected_tables(): array {
        return [];
    }

    /**
     * Test function
     */
    public function test() {
    }

}
