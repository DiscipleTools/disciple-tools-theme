<?php

/**
 * Class DT_Mapping_Module_Migration_0010
 */
class DT_Mapping_Module_Migration_0010 extends DT_Mapping_Module_Migration {

    /**
     * @throws \Exception  Got error when creating table $name.
     */
    public function up() {
        /**
         * Install tables
         */
        global $wpdb;
        $expected_tables = $this->get_expected_tables();
        foreach ( $expected_tables as $name => $table) {
            $rv = $wpdb->query( $table ); // WPCS: unprepared SQL OK
            if ( $rv == false ) {
                dt_write_log( "Got error when creating table $name: $wpdb->last_error" );
            }
        }
    }

    /**
     * @throws \Exception  Got error when dropping table $name.
     */
    public function down() {
        global $wpdb;
        $expected_tables = $this->get_expected_tables();
        foreach ( $expected_tables as $name => $table ) {
            $rv = $wpdb->query( "DROP TABLE `{$name}`" ); // WPCS: unprepared SQL OK
            if ( $rv == false ) {
                throw new Exception( "Got error when dropping table $name: $wpdb->last_error" );
            }
        }
    }

    /**
     * @return array
     */
    public function get_expected_tables(): array {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        return array(
            "{$wpdb->prefix}dt_location_grid_meta" =>
                "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dt_location_grid_meta` (
                    `grid_meta_id` BIGINT(22) unsigned NOT NULL AUTO_INCREMENT,
                    `post_id` BIGINT(20) NOT NULL,
                    `post_type` VARCHAR(20) NOT NULL,
                    `postmeta_id_location_grid` BIGINT(20) NOT NULL,
                    `grid_id` BIGINT(22) NOT NULL,
                    `lng` VARCHAR(20) NOT NULL,
                    `lat` VARCHAR(20) NOT NULL,
                    `level` VARCHAR(20) NOT NULL DEFAULT 'place',
                    `source` VARCHAR(20) NOT NULL DEFAULT 'user',
                    `label` VARCHAR(255) DEFAULT NULL,
                    PRIMARY KEY (`grid_meta_id`),
                    KEY `post_id` (`post_id`),
                    KEY `post_type` (`post_type`),
                    KEY `grid_id` (`grid_id`),
                    KEY `lng` (`lng`),
                    KEY `lat` (`lat`),
                    KEY `level` (`level`),
                    KEY `source` (`source`)
                ) $charset_collate;",
        );
    }

    /**
     * Test function
     */
    public function test() {
    }

}
