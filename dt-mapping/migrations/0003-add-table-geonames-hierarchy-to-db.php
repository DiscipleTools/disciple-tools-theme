<?php

class DT_Mapping_Module_Migration_0003 extends DT_Mapping_Module_Migration {
    /**
     * @throws \Exception
     */
    public function up() {
        if (false) { // @todo remove or keep

            $ms_migration_number = false;
            if ( is_multisite() ) {
                $ms_migration_number = (int) get_site_option( 'dt_mapping_module_multisite_migration_number', true );
            }

            if ( ! is_multisite() || $ms_migration_number < 3 ) {
                global $wpdb;
                $table = 'dt_geonames_hierarchy';
                $file = 'dt_geonames_hierarchy.tsv';
                $expected = 49111;

                // TEST for expected tables\
                $wpdb->query( "SHOW TABLES LIKE '$table'" );
                if ( $wpdb->num_rows < 1 ) {
                    error_log( 'Failed to find ' . $table );
                    dt_write_log( $wpdb->num_rows );
                    dt_write_log( $wpdb );
                    throw new Exception( 'Failed to find ' . $table );
                }

                // CHECK IF DB INSTALLED
                $rows = (int) $wpdb->get_var( "SELECT count(*) FROM $table" );
                if ( $rows >= $expected ) {
                    /* Test if database is already created */
                    error_log( 'database already installed' );
                } elseif ( $rows < $expected ) {
                    $wpdb->query( "TRUNCATE $table" );

                    // TEST for presence of source files
                    $dir = wp_upload_dir();
                    $uploads_dir = trailingslashit( $dir['basedir'] );
                    if ( ! file_exists( $uploads_dir . "geonames/" . $file ) ) {
                        error_log( 'Failed to find' . $file );
                        throw new Exception( 'Failed to find' . $file );
                    }

                    // LOAD geonames data
                    $wpdb->query( "
                        LOAD DATA LOCAL INFILE '{$uploads_dir}geonames/{$file}' 
                        INTO TABLE $table
                        FIELDS TERMINATED BY '\t'
                        LINES TERMINATED BY '\n'
                        (parent_id,geonameid,country_geonameid,admin1_geonameid,admin2_geonameid,admin3_geonameid)
                    " );

                    // TEST
                    $rows = (int) $wpdb->get_var( "SELECT count(*) FROM $table" );
                    if ( $rows === $expected ) {
                        error_log( 'success install of geonames data' );
                    } elseif ( $rows > $expected ) {
                        error_log( 'success, but additional records found' );
                    } elseif ( $rows < $expected ) {
                        error_log( 'fail. missing minimum records expected. ' . $expected . ' expected. ' . $rows . ' found.' );
                        throw new Exception( 'fail. missing minimum records expected. ' . $expected . ' expected. ' . $rows . ' found.' );
                    }
                }

                if ( is_multisite() ) {
                    update_site_option( 'dt_mapping_module_multisite_migration_number', 3 );
                }
            }
        }
    }

    public function down() {
        return;
    }

    public function test() {
    }

    public function get_expected_tables(): array {
        return array();
    }
}