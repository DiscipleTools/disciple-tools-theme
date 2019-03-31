<?php

/**
 * Class DT_Mapping_Module_Migration_0001
 *
 * @note    This migration gets, unzips, and prepares the geonames source data for installation in the next two
 *          migrations.
 *
 */
class DT_Mapping_Module_Migration_0001 extends DT_Mapping_Module_Migration {

    /**
     * @throws \Exception
     */
    public function up() {

        // get uploads director
        $dir = wp_upload_dir();
        $uploads_dir = trailingslashit( $dir['basedir'] );

        // make folder
        if ( ! file_exists( $uploads_dir . 'geonames' ) ) {
            mkdir( $uploads_dir . 'geonames' );
        }

        // get mirror source file url
        $mirror = get_option( 'dt_mapping_module_polygon_mirror' );
        if ( empty( $mirror ) ) {
            $array = [
                'key' => 'github',
                'label' => 'GitHub',
                'url' => 'https://raw.githubusercontent.com/DiscipleTools/dt-geojson/master/'
            ];
            update_option( 'dt_mapping_module_polygon_mirror', $array, true );
            $mirror = $array;
        }
        $mirror_source = $mirror['url'];

        $gn_source_url = $mirror_source . 'data_source/geonames.zip';

        $zip_file = $uploads_dir . "geonames/geonames.zip";

        $zip_resource = fopen( $zip_file, "w" );

        $ch_start = curl_init();
        curl_setopt( $ch_start, CURLOPT_URL, $gn_source_url );
        curl_setopt( $ch_start, CURLOPT_FAILONERROR, true );
        curl_setopt( $ch_start, CURLOPT_HEADER, 0 );
        curl_setopt( $ch_start, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $ch_start, CURLOPT_AUTOREFERER, true );
        curl_setopt( $ch_start, CURLOPT_BINARYTRANSFER, true );
        curl_setopt( $ch_start, CURLOPT_TIMEOUT, 10 );
        curl_setopt( $ch_start, CURLOPT_SSL_VERIFYHOST, 0 );
        curl_setopt( $ch_start, CURLOPT_SSL_VERIFYPEER, 0 );
        curl_setopt( $ch_start, CURLOPT_FILE, $zip_resource );
        $page = curl_exec( $ch_start );
        if ( !$page)
        {
            error_log( "Error :- ".curl_error( $ch_start ) );
        }
        curl_close( $ch_start );

        $zip = new ZipArchive();
        $extractPath = $uploads_dir . 'geonames';
        if ($zip->open( $zip_file ) != "true")
        {
            error_log( "Error :- Unable to open the Zip File" );
        }

        $zip->extractTo( $extractPath );
        $zip->close();

        $this->test();

    }

    public function down() {
        return;
    }

    /**
     * @throws \Exception
     */
    public function test() {
        $dir = wp_upload_dir();
        $uploads_dir = trailingslashit( $dir['basedir'] );

        if ( ! file_exists( $uploads_dir . "geonames/geonames.zip" ) ) {
            error_log( 'Failed to find geonames.zip' );
            throw new Exception( 'Failed to find geonames.zip' );
        }
        if ( ! file_exists( $uploads_dir . "geonames/dt_geonames.tsv" ) ) {
            error_log( 'Failed to find dt_geonames.tsv' );
            throw new Exception( 'Failed to find dt_geonames.tsv' );
        }
    }

    public function get_expected_tables(): array {
        return array();
    }
}