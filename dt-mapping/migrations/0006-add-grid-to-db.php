<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * @version_added 0.22.1
 */

class DT_Mapping_Module_Migration_0006 extends DT_Mapping_Module_Migration {
    /**
     * Install the data
     * @throws \Exception Failed to find correct records.
     */
    public function up() {
        global $wpdb;
        if ( ! isset( $wpdb->dt_location_grid ) ) {
            $wpdb->dt_location_grid = $wpdb->prefix . 'dt_location_grid';
        }

        $file = 'dt_location_grid.tsv';
        $expected = 48000;

        // TEST for expected tables\
        $wpdb->query( "SHOW TABLES LIKE '$wpdb->dt_location_grid'" );
        if ( $wpdb->num_rows < 1 ) {
            error_log( 'Failed to find ' . $wpdb->dt_location_grid );
            dt_write_log( $wpdb->num_rows );
            dt_write_log( $wpdb );
            throw new Exception( 'Failed to find ' . $wpdb->dt_location_grid );
        }

        // CHECK IF DB INSTALLED
        $rows = (int) $wpdb->get_var( "SELECT count(*) FROM $wpdb->dt_location_grid" );
        if ( $rows >= $expected ) {
            /* Test if database is already created */
            error_log( 'database already installed' );
        } elseif ( $rows < $expected ) {
            $wpdb->query( "TRUNCATE $wpdb->dt_location_grid" );

            // TEST for presence of source files
            $dir = wp_upload_dir();
            $uploads_dir = trailingslashit( $dir['basedir'] );
            if ( ! file_exists( $uploads_dir . 'location_grid_download/' . $file ) ) {
                error_log( 'Failed to find ' . $file );
                throw new Exception( 'Failed to find ' . $file );
            }

            $file_location = $uploads_dir . 'location_grid_download/' . $file;

            // LOAD location_grid data
            $fp = fopen( $file_location, 'r' );

            $query = "INSERT IGNORE INTO $wpdb->dt_location_grid VALUES ";

            $count = 0;
            while ( ! feof( $fp ) ) {
                $line = fgets( $fp, 2048 );
                $count++;

                $data = str_getcsv( $line, "\t" );

                $data_sql = dt_array_to_sql( $data );

                if ( isset( $data[24] ) ) {
                    $query .= " ( $data_sql ), ";
                }
                if ( $count === 500 ) {
                    $query .= ';';
                    $query = str_replace( ', ;', ';', $query ); //remove last comma

                    $wpdb->query( $query );  //phpcs:ignore
                    $query = "INSERT IGNORE INTO $wpdb->dt_location_grid VALUES ";
                    $count = 0;
                }
            }
            //add the last queries
            $query .= ';';
            $query = str_replace( ', ;', ';', $query ); //remove last comma
            $wpdb->query( $query );  //phpcs:ignore

        }

        $dir = wp_upload_dir();
        $uploads_dir = trailingslashit( $dir['basedir'] );
        if ( file_exists( $uploads_dir . 'location_grid_download/dt_location_grid.tsv.zip' ) ) {
            unlink( $uploads_dir . 'location_grid_download/dt_location_grid.tsv.zip' );
        }
        if ( file_exists( $uploads_dir . 'location_grid_download/dt_location_grid.tsv' ) ) {
            unlink( $uploads_dir . 'location_grid_download/dt_location_grid.tsv' );
        }
        if ( file_exists( $uploads_dir . 'location_grid_download/__MACOSX' ) ) {
            unlink( $uploads_dir . 'location_grid_download/__MACOSX' );
        }
        if ( file_exists( $uploads_dir . 'location_grid_download' ) ) {
            rmdir( $uploads_dir . 'location_grid_download' );
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

