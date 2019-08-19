<?php
/**
 * Class DT_Mapping_Module_Migration_0007
 *
 * @note Update filters
 */


class DT_Mapping_Module_Migration_0009 extends DT_Mapping_Module_Migration {
    public function up() {


        $dir = wp_upload_dir();
        $uploads_dir = trailingslashit( $dir['basedir'] );
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
        require_once( get_template_directory() . '/dt-mapping/mapping-admin.php' );
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

