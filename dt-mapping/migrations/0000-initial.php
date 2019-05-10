<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

require_once( 'abstract.php' );

/**
 * Class DT_Mapping_Module_Migration_0000
 */
class DT_Mapping_Module_Migration_0000 extends DT_Mapping_Module_Migration {

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
            "{$wpdb->prefix}dt_geonames" =>
                "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dt_geonames` (
                  `geonameid` BIGINT(20) unsigned NOT NULL,
                  `name` varchar(200) DEFAULT NULL,
                  `asciiname` varchar(200) DEFAULT NULL,
                  `alternatenames` varchar(200) DEFAULT NULL,
                  `latitude` float DEFAULT NULL,
                  `longitude` float DEFAULT NULL,
                  `feature_class` char(1) DEFAULT NULL,
                  `feature_code` varchar(10) DEFAULT NULL,
                  `country_code` char(2) DEFAULT NULL,
                  `cc2` varchar(200) DEFAULT NULL,
                  `admin1_code` varchar(20) DEFAULT NULL,
                  `admin2_code` varchar(80) DEFAULT NULL,
                  `admin3_code` varchar(20) DEFAULT NULL,
                  `admin4_code` varchar(20) DEFAULT NULL,
                  `population` BIGINT(20) NOT NULL DEFAULT 0,
                  `elevation` int(20) DEFAULT NULL,
                  `dem` varchar(20) DEFAULT NULL,
                  `timezone` varchar(40) DEFAULT NULL,
                  `modification_date` date DEFAULT NULL,
                  `parent_id` BIGINT(20) DEFAULT NULL,
                  `country_geonameid` BIGINT(20) DEFAULT NULL,
                  `admin1_geonameid` BIGINT(20) DEFAULT NULL,
                  `admin2_geonameid` BIGINT(20) DEFAULT NULL,
                  `admin3_geonameid` BIGINT(20) DEFAULT NULL,
                  `level` VARCHAR(50) DEFAULT NULL,
                  `north_latitude` float DEFAULT NULL,
                  `south_latitude` float DEFAULT NULL,
                  `west_longitude` float DEFAULT NULL,
                  `east_longitude` float DEFAULT NULL,
                  `alt_name` VARCHAR(200) DEFAULT NULL,
                  `alt_population` BIGINT(20) DEFAULT NULL,
                  `is_custom_location` TINYINT(1) NOT NULL DEFAULT 0,
                  `alt_name_changed` TINYINT(1) NOT NULL DEFAULT 0,
                  PRIMARY KEY (`geonameid`),
                  KEY `feature_code` (`feature_code`),
                  KEY `country_code` (`country_code`),
                  KEY `population` (`population`),
                  KEY `parent_id` (`parent_id`),
                  KEY `country_geonameid` (`country_geonameid`),
                  KEY `admin1_geonameid` (`admin1_geonameid`),
                  KEY `admin2_geonameid` (`admin2_geonameid`),
                  KEY `admin3_geonameid` (`admin3_geonameid`),
                  KEY `level` (`level`),
                  KEY `north_latitude` (`north_latitude`),
                  KEY `south_latitude` (`south_latitude`),
                  KEY `west_longitude` (`west_longitude`),
                  KEY `east_longitude` (`east_longitude`),
                  FULLTEXT KEY `name` (`name`),
                  FULLTEXT KEY `alt_name` (`alt_name`)
                ) $charset_collate;",
            "{$wpdb->prefix}dt_geonames_counter" =>
            "CREATE OR REPLACE ALGORITHM = MERGE VIEW {$wpdb->prefix}dt_geonames_counter AS    
                SELECT
                (NULL) as country_geonameid,
                (NULL) as admin1_geonameid,
                (NULL) as admin2_geonameid,
                (NULL) as admin3_geonameid,
                (NULL) as geonameid,
   				(NULL) as level,
                (NULL) as post_id,
                (NULL) as type, 
                (NULL) as status,
                (NULL) as created_date,
                (NULL) as end_date;",
        );
    }

    /**
     * Test function
     */
    public function test() {
    }

}
