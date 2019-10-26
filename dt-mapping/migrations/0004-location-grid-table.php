<?php

/**
 * Class DT_Mapping_Module_Migration_0000
 */
class DT_Mapping_Module_Migration_0004 extends DT_Mapping_Module_Migration {

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
            "{$wpdb->prefix}dt_location_grid" =>
                "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dt_location_grid` (
                  `grid_id` bigint(20) NOT NULL AUTO_INCREMENT,
                  `name` varchar(200) NOT NULL DEFAULT '',
                  `level` int(1) DEFAULT NULL,
                  `level_name` varchar(7) DEFAULT NULL,
                  `country_code` varchar(10) DEFAULT NULL,
                  `admin0_code` varchar(10) DEFAULT NULL,
                  `parent_id` bigint(20) DEFAULT NULL,
                  `admin0_grid_id` bigint(20) DEFAULT NULL,
                  `admin1_grid_id` bigint(20) DEFAULT NULL,
                  `admin2_grid_id` bigint(20) DEFAULT NULL,
                  `admin3_grid_id` bigint(20) DEFAULT NULL,
                  `admin4_grid_id` bigint(20) DEFAULT NULL,
                  `admin5_grid_id` bigint(20) DEFAULT NULL,
                  `longitude` float DEFAULT NULL,
                  `latitude` float DEFAULT NULL,
                  `north_latitude` float DEFAULT NULL,
                  `south_latitude` float DEFAULT NULL,
                  `west_longitude` float DEFAULT NULL,
                  `east_longitude` float DEFAULT NULL,
                  `population` bigint(20) NOT NULL DEFAULT '0',
                  `modification_date` date DEFAULT NULL,
                  `alt_name` varchar(200) DEFAULT NULL,
                  `alt_population` bigint(20) DEFAULT '0',
                  `is_custom_location` tinyint(1) NOT NULL DEFAULT '0',
                  `alt_name_changed` tinyint(1) NOT NULL DEFAULT '0',
                  PRIMARY KEY (`grid_id`),
                  KEY `level` (`level`),
                  KEY `latitude` (`latitude`),
                  KEY `longitude` (`longitude`),
                  KEY `admin0_code` (`admin0_code`),
                  KEY `country_code` (`country_code`),
                  KEY `north_latitude` (`north_latitude`),
                  KEY `south_latitude` (`south_latitude`),
                  KEY `parent_id` (`parent_id`),
                  KEY `west_longitude` (`west_longitude`),
                  KEY `east_longitude` (`east_longitude`),
                  KEY `admin0_grid_id` (`admin0_grid_id`),
                  KEY `admin1_grid_id` (`admin1_grid_id`),
                  KEY `admin2_grid_id` (`admin2_grid_id`),
                  KEY `admin3_grid_id` (`admin3_grid_id`),
                  KEY `admin4_grid_id` (`admin4_grid_id`),
                  KEY `admin5_grid_id` (`admin5_grid_id`),
                  KEY `level_name` (`level_name`),
                  FULLTEXT KEY `name` (`name`),
                  FULLTEXT KEY `alt_name` (`alt_name`)
                ) $charset_collate;",
        );
    }

    /**
     * Test function
     */
    public function test() {
    }

}
