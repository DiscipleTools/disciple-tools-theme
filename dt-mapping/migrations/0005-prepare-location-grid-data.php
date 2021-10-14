<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
/**
 * Class DT_Mapping_Module_Migration_0005
 *
 * @note    This migration gets, unzips, and prepares the location_grid source data for installation in the next two
 *          migrations.
 *
 */
class DT_Mapping_Module_Migration_0005 extends DT_Mapping_Module_Migration {

    public function up() {

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

        try {
            $this->test();
        } catch ( Exception $e ) {
            dt_write_log( $e );
        }
    }

    public function down() {
        return;
    }

    /**
     * Testing
     * @throws \Exception Did not find files.
     */
    public function test() {
        $dir = wp_upload_dir();
        $uploads_dir = trailingslashit( $dir['basedir'] );

        if ( ! file_exists( $uploads_dir . "location_grid_download/dt_location_grid.tsv.zip" ) ) {
            error_log( 'Failed to find dt_location_grid.tsv.zip' );
            throw new Exception( 'Failed to find dt_location_grid.tsv.zip' );
        }
        if ( ! file_exists( $uploads_dir . "location_grid_download/dt_location_grid.tsv" ) ) {
            error_log( 'Failed to find dt_location_grid.tsv' );
            throw new Exception( 'Failed to find dt_location_grid.tsv' );
        }
    }

    public function get_expected_tables(): array {
        return array();
    }
}
