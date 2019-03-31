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
        $ms_migration_number = false;
        if ( is_multisite() ) {
            $ms_migration_number = get_site_option( 'dt_mapping_module_multisite_migration_number', true );
        }

        /* if single site, or ms migration is not set, or ms migration is less than this migration */
        if ( ! is_multisite() || $ms_migration_number === false || $ms_migration_number < 0 ) {
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

            if ( is_multisite() ) {
                update_site_option( 'dt_mapping_module_multisite_migration_number', 0 );
            }
        }
    }

    /**
     * @throws \Exception  Got error when dropping table $name.
     */
    public function down() {
        if ( ! is_multisite() ) {
            global $wpdb;
            $expected_tables = $this->get_expected_tables();
            foreach ( $expected_tables as $name => $table ) {
                $rv = $wpdb->query( "DROP TABLE `{$name}`" ); // WPCS: unprepared SQL OK
                if ( $rv == false ) {
                    throw new Exception( "Got error when dropping table $name: $wpdb->last_error" );
                }
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
            "dt_geonames" =>
                "CREATE TABLE IF NOT EXISTS `dt_geonames` (
                  `geonameid` bigint(20) unsigned NOT NULL,
                  `name` varchar(200) DEFAULT NULL,
                  `asciiname` varchar(200) DEFAULT NULL,
                  `alternatenames` varchar(10000) DEFAULT NULL,
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
                  `population` bigint(20) DEFAULT 0,
                  `elevation` int(80) DEFAULT NULL,
                  `dem` varchar(80) DEFAULT NULL,
                  `timezone` varchar(40) DEFAULT NULL,
                  `modification_date` date DEFAULT NULL,
                  `parent_id` BIGINT(20) DEFAULT NULL,
                  `country_geonameid` BIGINT(20) DEFAULT NULL,
                  `admin1_geonameid` BIGINT(20) DEFAULT NULL,
                  `admin2_geonameid` BIGINT(20) DEFAULT NULL,
                  `admin3_geonameid` BIGINT(20) DEFAULT NULL,
                  `level` VARCHAR(50) DEFAULT NULL,
                  PRIMARY KEY (`geonameid`),
                  KEY `feature_class` (`feature_class`),
                  KEY `feature_code` (`feature_code`),
                  KEY `country_code` (`country_code`),
                  KEY `admin1_code` (`admin1_code`),
                  KEY `admin2_code` (`admin2_code`),
                  KEY `population` (`population`),
                  KEY `parent_id` (`parent_id`),
                  KEY `country_geonameid` (`country_geonameid`),
                  KEY `admin1_geonameid` (`admin1_geonameid`),
                  KEY `admin2_geonameid` (`admin2_geonameid`),
                  KEY `admin3_geonameid` (`admin3_geonameid`),
                  FULLTEXT KEY `name` (`name`)
                ) $charset_collate;",
        );
    }

    /**
     * Test function
     */
    public function test() {
    }

}
