<?php

class DT_Mapping_Module_Migration_0002 extends DT_Mapping_Module_Migration {
    /**
     * Install the data
     * @throws \Exception Failed to find correct records.
     */
    public function up() {
        global $wpdb;
        if ( ! isset( $wpdb->dt_geonames ) ) {
            $wpdb->dt_geonames = $wpdb->prefix . 'dt_geonames';
        }

        $file = 'geonames.tsv';
        $expected = 48000;

        // TEST for expected tables\
        $wpdb->query( "SHOW TABLES LIKE '$wpdb->dt_geonames'" );
        if ( $wpdb->num_rows < 1 ) {
            error_log( 'Failed to find ' . $wpdb->dt_geonames );
            dt_write_log( $wpdb->num_rows );
            dt_write_log( $wpdb );
            throw new Exception( 'Failed to find ' . $wpdb->dt_geonames );
        }

        // CHECK IF DB INSTALLED
        $rows = (int) $wpdb->get_var( "SELECT count(*) FROM $wpdb->dt_geonames" );
        if ( $rows >= $expected ) {
            /* Test if database is already created */
            error_log( 'database already installed' );
        } elseif ( $rows < $expected ) {
            $wpdb->query( "TRUNCATE $wpdb->dt_geonames" );

            // TEST for presence of source files
            $dir = wp_upload_dir();
            $uploads_dir = trailingslashit( $dir['basedir'] );
            if ( ! file_exists( $uploads_dir . "geonames/" . $file ) ) {
                error_log( 'Failed to find ' . $file );
                throw new Exception( 'Failed to find ' . $file );
            }

            $file_location = $uploads_dir . 'geonames/' . $file;

            // LOAD geonames data
            $wpdb->query( $wpdb->prepare( "
                LOAD DATA LOCAL INFILE %s
                INTO TABLE $wpdb->dt_geonames
                FIELDS TERMINATED BY '\t'
                LINES TERMINATED BY '\n'
                (geonameid,name,asciiname,alternatenames,latitude,longitude,feature_class,feature_code,country_code,cc2,admin1_code,admin2_code,admin3_code,admin4_code,population,elevation,dem,timezone,modification_date,parent_id,country_geonameid,admin1_geonameid,admin2_geonameid,admin3_geonameid,level,north_latitude,south_latitude,west_longitude,east_longitude,alt_name,alt_population,is_custom_location,alt_name_changed,has_polygon)
                ", $file_location ) );


            // TEST
            $rows = (int) $wpdb->get_var( "SELECT count(*) FROM $wpdb->dt_geonames" );
            if ( $rows >= $expected ) {
                error_log( 'success, but additional records found' );
            } elseif ( $rows < $expected ) {
                 // fail over install

                require_once( get_template_directory() . '/dt-mapping/mapping-admin.php' );
                DT_Mapping_Module_Admin::instance()->rebuild_geonames();
                $rows = (int) $wpdb->get_var( "SELECT count(*) FROM $wpdb->dt_geonames" );

                if ( $rows === $expected ) {
                    error_log( 'success install of geonames data' );
                } elseif ( $rows > $expected ) {
                    error_log( 'success, but additional records found' );
                } elseif ( $rows < $expected ) {
                    error_log( 'fail. missing minimum records expected. ' . $expected . ' expected. ' . $rows . ' found.' );
                    throw new Exception( 'fail. missing minimum records expected. ' . $expected . ' expected. ' . $rows . ' found.' );
                }
            } // failed to find records.
        }
    }

    public function down() {
        return;
    }

    public function test() {
    }

    public function get_expected_tables(): array {
        return [];
    }
}

