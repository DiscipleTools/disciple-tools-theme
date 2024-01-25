<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class DT_Mapping_Module_Migration_0020
 * and and index to the dt_location_grid_meta column
 */
class DT_Mapping_Module_Migration_0020 extends DT_Mapping_Module_Migration {

    /**
     * @throws \Exception  Got error when creating table $name.
     */
    public function up() {
        global $wpdb;
        if ( ! isset( $wpdb->dt_location_grid_meta ) ) {
            $wpdb->dt_location_grid_meta = $wpdb->prefix . 'dt_location_grid_meta';
        }

        //check if index exists
        $index_exists = $wpdb->query( $wpdb->prepare("
                select distinct index_name
                from information_schema.statistics
                where table_schema = %s
                and table_name = '$wpdb->dt_location_grid_meta'
                and index_name like %s
            ", DB_NAME, 'postmeta_id_location_grid' ));
        if ( $index_exists === 0 ){
            $wpdb->query( "ALTER TABLE `$wpdb->dt_location_grid_meta` ADD INDEX postmeta_id_location_grid (postmeta_id_location_grid)" );
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
