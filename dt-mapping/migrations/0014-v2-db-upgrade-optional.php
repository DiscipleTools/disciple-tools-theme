<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class DT_Mapping_Module_Migration_0014
 *
 * @version_added 1.30.2
 */
class DT_Mapping_Module_Migration_0014 extends DT_Mapping_Module_Migration {

    /**
     * @throws \Exception  Got error when creating table $name.
     */
    public function up() {
        global $wpdb;
        // test for a specific feature that was changed in v2 to see if the v2 dataset was installed in 0006. This will be true for installs after 1.30.2, and not true installs before 1.30.2.
        $is_v2 = $wpdb->get_var("SELECT grid_id FROM {$wpdb->prefix}dt_location_grid WHERE grid_id = 100364199 AND latitude = 39.8097 " );
        if ( ! $is_v2 ) {
            // get uploads director
            $dir = wp_upload_dir();
            $uploads_dir = trailingslashit( $dir['basedir'] );

            // make folder
            if ( ! file_exists( $uploads_dir . 'location_grid_download' ) ) {
                mkdir( $uploads_dir . 'location_grid_download' );
            }
            if ( file_exists( $uploads_dir . "location_grid_download/dt_location_grid.tsv.zip" ) ) {
                unlink( $uploads_dir . "location_grid_download/dt_location_grid.tsv.zip" );
            }
            if ( file_exists( $uploads_dir . "location_grid_download/dt_location_grid.tsv" ) ) {
                unlink( $uploads_dir . "location_grid_download/dt_location_grid.tsv" );
            }

            // get mirror source file url
            $mirror_source = dt_get_theme_data_url();

            $gn_source_url = $mirror_source . 'location_grid/dt_location_grid.tsv.zip';

            $zip_file = $uploads_dir . "location_grid_download/dt_location_grid.tsv.zip";

            $zip_resource = fopen( $zip_file, "w" );

            $ch_start = curl_init();
            curl_setopt( $ch_start, CURLOPT_URL, $gn_source_url );
            curl_setopt( $ch_start, CURLOPT_FAILONERROR, true );
            curl_setopt( $ch_start, CURLOPT_HEADER, 0 );
            curl_setopt( $ch_start, CURLOPT_FOLLOWLOCATION, true );
            curl_setopt( $ch_start, CURLOPT_AUTOREFERER, true );
            curl_setopt( $ch_start, CURLOPT_BINARYTRANSFER, true );
            curl_setopt( $ch_start, CURLOPT_TIMEOUT, 30 );
            curl_setopt( $ch_start, CURLOPT_SSL_VERIFYHOST, 0 );
            curl_setopt( $ch_start, CURLOPT_SSL_VERIFYPEER, 0 );
            curl_setopt( $ch_start, CURLOPT_FILE, $zip_resource );
            $page = curl_exec( $ch_start );
            if ( !$page )
            {
                error_log( "Error :- ".curl_error( $ch_start ) );
            }
            curl_close( $ch_start );

            if ( !class_exists( 'ZipArchive' ) ){
                error_log( "PHP ZipArchive is not installed or enabled." );
                throw new Exception( 'PHP ZipArchive is not installed or enabled.' );
            }
            $zip = new ZipArchive();
            $extract_path = $uploads_dir . 'location_grid_download';
            if ( $zip->open( $zip_file ) != "true" )
            {
                error_log( "Error :- Unable to open the Zip File" );
            }

            $zip->extractTo( $extract_path );
            $zip->close();


            if ( ! isset( $wpdb->dt_location_grid ) ) {
                $wpdb->dt_location_grid = $wpdb->prefix . 'dt_location_grid';
            }

            $file = 'dt_location_grid.tsv';
            $expected = 48000;

//            // TEST for expected tables\
//            $wpdb->query( "SHOW TABLES LIKE '$wpdb->dt_location_grid'" );
//            if ( $wpdb->num_rows < 1 ) {
//                error_log( 'Failed to find ' . $wpdb->dt_location_grid );
//                dt_write_log( $wpdb->num_rows );
//                dt_write_log( $wpdb );
//                throw new Exception( 'Failed to find ' . $wpdb->dt_location_grid );
//            }

            $custom_rows = $wpdb->query( "" );
            $wpdb->query( "TRUNCATE $wpdb->dt_location_grid" );

            // TEST for presence of source files
            $dir = wp_upload_dir();
            $uploads_dir = trailingslashit( $dir['basedir'] );
            if ( ! file_exists( $uploads_dir . "location_grid_download/" . $file ) ) {
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
                    $query = str_replace( ", ;", ";", $query ); //remove last comma

                    $wpdb->query( $query );  //phpcs:ignore
                    $query = "INSERT IGNORE INTO $wpdb->dt_location_grid VALUES ";
                    $count = 0;
                }
            }
            //add the last queries
            $query .= ';';
            $query = str_replace( ", ;", ";", $query ); //remove last comma
            $wpdb->query( $query );  //phpcs:ignore
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
