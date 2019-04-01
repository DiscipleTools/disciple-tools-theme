<?php

/**
 * Class DT_Mapping_Module_Migration_0003
 *
 * @note    Add a custom table for the site to hold geonames metadata, like custom names/translations and populations
 *
 */
class DT_Mapping_Module_Migration_0003 extends DT_Mapping_Module_Migration {

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
        foreach ( $expected_tables as $name => $table) {
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
            "{$wpdb->prefix}dt_geonames_meta" =>
                "CREATE TABLE  IF NOT EXISTS `{$wpdb->prefix}dt_geonames_meta` (
                  `id` BIGINT(22) unsigned NOT NULL AUTO_INCREMENT,
                  `geonameid` BIGINT(22) unsigned NOT NULL,
                  `meta_key` VARCHAR(50) DEFAULT NULL,
                  `meta_value` LONGTEXT,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `unique_key_constraint` (`geonameid`,`meta_key`),
                  KEY `geonameid` (`geonameid`),
                  KEY `meta_key` (`meta_key`)
                ) $charset_collate;",
            "{$wpdb->prefix}dt_geonames_sublocations" =>
                "CREATE TABLE  IF NOT EXISTS `{$wpdb->prefix}dt_geonames_sublocations` (
                  `parent_id` bigint(20) unsigned NOT NULL,
                  `geonameid` bigint(20) unsigned NOT NULL,
                  `name` varchar(200) DEFAULT NULL,
                  `latitude` float DEFAULT NULL,
                  `longitude` float DEFAULT NULL,
                  `population` bigint(20) DEFAULT '0',
                  `modification_date` date DEFAULT NULL,
                  PRIMARY KEY (`geonameid`),
                  KEY `parent_id` (`parent_id`),
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
