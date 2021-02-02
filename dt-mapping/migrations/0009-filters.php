<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class DT_Mapping_Module_Migration_0007
 *
 * @note Update filters
 */


class DT_Mapping_Module_Migration_0009 extends DT_Mapping_Module_Migration {
    public function up() {
        global $dt_mapping;

        $dir = wp_upload_dir();
        $uploads_dir = trailingslashit( $dir['basedir'] );


        if ( ! file_exists( $uploads_dir . "location_grid_download/geonames_ref_table.tsv" ) ) {

            if ( file_exists( $uploads_dir . "location_grid_download/geonames_ref_table.tsv.zip" ) ) {
                unlink( $uploads_dir . "location_grid_download/geonames_ref_table.tsv.zip" );
            }

            // get mirror source file url
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
            if ( ! $page ) {
                error_log( "Error :- " . curl_error( $ch_start ) );
            }
            curl_close( $ch_start );

            if ( ! class_exists( 'ZipArchive' ) ) {
                error_log( "PHP ZipArchive is not installed or enabled." );
                throw new Exception( 'PHP ZipArchive is not installed or enabled.' );
            }
            $zip          = new ZipArchive();
            $extract_path = $uploads_dir . 'location_grid_download';
            if ( $zip->open( $zip_file ) != "true" ) {
                error_log( "Error :- Unable to open the Zip File" );
            }

            $zip->extractTo( $extract_path );
            $zip->close();
            /** End resource download */
            dt_write_log( 'end remote get' );
            if ( file_exists( $uploads_dir . "location_grid_download/geonames_ref_table.tsv.zip" ) ) {
                dt_write_log( 'file exists' );
            }
        }

        // load list to array, make geonameid key
        $geonames_ref = [];
        $geonmes_ref_raw = array_map( function( $v){return str_getcsv( $v, "\t" );
        }, file( $uploads_dir . "location_grid_download/geonames_ref_table.tsv" ) );
        if ( empty( $geonmes_ref_raw ) ) {
            throw new Exception( 'Failed to build array from remote file.' );
        }
        foreach ( $geonmes_ref_raw as $value ) {
            $geonames_ref[$value[1]] = [
                'grid_id' => $value[0],
                'geonameid' => $value[1],
            ];
        }

        $migrated = get_option( "dt_mapping_migration_list", [] );
        foreach ( $migrated as $location_id => &$m ){
            if ( isset( $m["selected_geoname"] ) && !isset( $m["selected_location_grid"] ) ) {
                if ( isset( $geonames_ref[ $m["selected_geoname"] ] ) ) {
                    $m["selected_location_grid"] = $geonames_ref[ $m["selected_geoname"] ]['grid_id'];
                }
            }
        }

        update_option( "dt_mapping_migration_list", $migrated );
        //update filters
        require_once( $dt_mapping['path'] . 'mapping-admin.php' );
        DT_Mapping_Module_Admin::instance()->migrate_user_filters_to_location_grid();

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

