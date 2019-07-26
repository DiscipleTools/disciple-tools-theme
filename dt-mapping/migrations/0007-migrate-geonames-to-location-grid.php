<?php
/**
 * Class DT_Mapping_Module_Migration_0007
 *
 * @note    Find any geonames records and convert them automatically
 */


class DT_Mapping_Module_Migration_0007 extends DT_Mapping_Module_Migration {
    public function up() {
        global $wpdb;
        if ( ! isset( $wpdb->dt_location_grid ) ) {
            $wpdb->dt_location_grid = $wpdb->prefix . 'dt_location_grid';
        }

        /* Test and see if there are any geoname records in the database, if not end script. */
        $count = $wpdb->get_var( "
            SELECT COUNT(DISTINCT meta_value) FROM $wpdb->postmeta WHERE meta_key = 'geonames';
        " );
        if ( $count < 1 ) {
            return;
        }
        /** End Test */
        dt_write_log('geonames exist');



        /**
         * Download remote list
         */
        dt_write_log('begin remote get');
        $dir = wp_upload_dir();
        $uploads_dir = trailingslashit( $dir['basedir'] );

        // make folder and remove previous files
        if ( ! file_exists( $uploads_dir . 'location_grid_download' ) ) {
            mkdir( $uploads_dir . 'location_grid_download' );
        }
        if ( file_exists( $uploads_dir . "location_grid_download/geonames_ref_table.tsv.zip" ) ) {
            unlink( $uploads_dir . "location_grid_download/geonames_ref_table.tsv.zip" );
        }
        if ( file_exists( $uploads_dir . "location_grid_download/geonames_ref_table.tsv" ) ) {
            unlink( $uploads_dir . "location_grid_download/geonames_ref_table.tsv" );
        }

        // get mirror source file url
        require_once( get_template_directory() . '/dt-core/global-functions.php' );
        $mirror_source = dt_get_theme_data_url();

        $gn_source_url = $mirror_source . 'location_grid/geonames_ref_table.tsv.zip';

        $zip_file = $uploads_dir . "location_grid_download/geonames_ref_table.tsv.zip";

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
        if ( !$page)
        {
            error_log( "Error :- ".curl_error( $ch_start ) );
        }
        curl_close( $ch_start );

        if ( ! class_exists( 'ZipArchive' )){
            error_log( "PHP ZipArchive is not installed or enabled." );
            throw new Exception( 'PHP ZipArchive is not installed or enabled.' );
        }
        $zip = new ZipArchive();
        $extract_path = $uploads_dir . 'location_grid_download';
        if ($zip->open( $zip_file ) != "true")
        {
            error_log( "Error :- Unable to open the Zip File" );
        }

        $zip->extractTo( $extract_path );
        $zip->close();
        /** End resource download */
        dt_write_log('end remote get');
        if ( file_exists( $uploads_dir . "location_grid_download/geonames_ref_table.tsv.zip" ) ) {
            dt_write_log('file exists');
        }



        // load list to array, make geonameid key
        $geonames_ref = [];
        $geonmes_ref_raw = array_map(function($v){return str_getcsv($v, "\t");}, file($uploads_dir . "location_grid_download/geonames_ref_table.tsv" ) );
        if ( empty( $geonmes_ref_raw ) ) {
            throw new Exception( 'Failed to build array from remote file.' );
        }
        foreach ( $geonmes_ref_raw as $value ) {
            $geonames_ref[$value[1]] = [
                'grid_id' => $value[0],
                'geonameid' => $value[1],
            ];
        }
        dt_write_log($geonames_ref);



        // get list of geonames in system; count unique geonameids
        $post_geonames = $wpdb->get_results( "
            SELECT * FROM $wpdb->postmeta WHERE meta_key = 'geonames';
        ", ARRAY_A );

        $activity_log_geonames = $wpdb->get_results( "
            SELECT * FROM $wpdb->dt_activity_log WHERE meta_key = 'geonames';
        ", ARRAY_A );


        // loop and convert matching geonames

        // check if any remaining unmatched geonames


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

