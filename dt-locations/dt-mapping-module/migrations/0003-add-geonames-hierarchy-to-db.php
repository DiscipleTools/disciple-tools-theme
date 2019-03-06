<?php

class DT_Mapping_Module_Migration_0003 extends DT_Mapping_Module_Migration {
    /**
     * @throws \Exception
     */
    public function up() {
        global $wpdb;
        $table = 'dt_geonames_hierarchy';
        $file = 'dt_geonames_hierarchy.tsv';
        $expected = 53206;

        // TEST for expected tables\
        $wpdb->query( "SHOW TABLES LIKE '$table'");
        if ( $wpdb->num_rows < 1 ) {
            error_log('Failed to find ' . $table );
            dt_write_log( $wpdb->num_rows);
            dt_write_log( $wpdb);
            throw new Exception('Failed to find ' . $table );
        }

        // CHECK IF DB INSTALLED
        $rows = (int) $wpdb->get_var("SELECT count(*) FROM $table");
        if ( $rows >= $expected ) {
            /* Test if database is already created */
            error_log('database already installed');
        }
        else if ( $rows < $expected ) {
            $wpdb->query( "TRUNCATE $table");

            // TEST for presence of source files
            $dir = wp_upload_dir();
            $uploads_dir = trailingslashit($dir['basedir']);
            if ( ! file_exists( $uploads_dir . "geonames/" . $file ) ) {
                error_log('Failed to find' . $file );
                throw new Exception('Failed to find' . $file );
            }

            // LOAD geonames data
            $wpdb->query("
                LOAD DATA LOCAL INFILE '{$uploads_dir}geonames/{$file}' 
                INTO TABLE $table
                FIELDS TERMINATED BY '\t'
                LINES TERMINATED BY '\n'
                (parent_id,id,name)
            ");

            // TEST
            $rows = (int) $wpdb->get_var("SELECT count(*) FROM $table");
            if ( $rows === $expected ) {
                error_log('success install of geonames data');
            }
            else if ( $rows > $expected ) {
                error_log( 'success, but additional records found');
            }
            else if ( $rows < $expected ) {
                error_log( 'fail. missing minimum records expected. '.$expected.' expected. ' . $rows . ' found.');
                throw new Exception('fail. missing minimum records expected. '.$expected.' expected. ' . $rows . ' found.');
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