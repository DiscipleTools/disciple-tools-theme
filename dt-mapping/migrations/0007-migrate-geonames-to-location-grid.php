<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
/**
 * Class DT_Mapping_Module_Migration_0007
 *
 * @note    Find any geonames records and convert them automatically
 */


class DT_Mapping_Module_Migration_0007 extends DT_Mapping_Module_Migration {
    public function up() {
        global $wpdb, $dt_mapping;
        if ( ! isset( $wpdb->dt_location_grid ) ) {
            $wpdb->dt_location_grid = $wpdb->prefix . 'dt_location_grid';
        }
        if ( ! isset( $wpdb->dt_geonames ) ) {
            $wpdb->dt_geonames = $wpdb->prefix . 'dt_geonames';
        }
        $unmatched = [];

        /* Test and see if there are any geoname records in the database, if not end script. */
        $count = $wpdb->get_var( "
            SELECT COUNT(DISTINCT meta_value) FROM $wpdb->postmeta WHERE meta_key = 'geonames';
        " );
        $count_activity = $wpdb->get_var( "
            SELECT COUNT(DISTINCT meta_value) FROM $wpdb->dt_activity_log WHERE meta_key = 'geonames';
        " );
        if ( $count > 0 || $count_activity > 0 ) {

            /** End Test */
            dt_write_log( 'geonames exist' );

            /**
             * Download remote list
             */
            dt_write_log( 'begin remote get' );
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
            dt_write_log( 'end remote get' );
            if ( file_exists( $uploads_dir . "location_grid_download/geonames_ref_table.tsv.zip" ) ) {
                dt_write_log( 'file exists' );
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



            $used_geonames = $wpdb->get_results( "SELECT DISTINCT meta_value FROM $wpdb->postmeta where meta_key = 'geonames'", ARRAY_A );
            foreach ( $used_geonames as $ug ){
                if ( isset( $geonames_ref[$ug["meta_value"]] ) ) {
                    $wpdb->query( $wpdb->prepare( "
                        UPDATE $wpdb->postmeta
                        SET meta_key = 'location_grid',
                            meta_value = %s
                        WHERE meta_key = 'geonames' and meta_value = %s
                        ", $geonames_ref[$ug["meta_value"]]['grid_id'], $ug["meta_value"]
                    ) );
                }
            }
            $used_activity_geonames = $wpdb->get_results( "SELECT DISTINCT meta_value FROM $wpdb->dt_activity_log where meta_key = 'geonames'", ARRAY_A );
            foreach ( $used_activity_geonames as $ug ){
                if ( isset( $geonames_ref[$ug["meta_value"]] ) ) {
                    $wpdb->query( $wpdb->prepare( "
                        UPDATE $wpdb->dt_activity_log
                        SET meta_key = 'location_grid',
                            meta_value = %s
                        WHERE meta_key = 'geonames' and meta_value = %s
                        ", $geonames_ref[$ug["meta_value"]]['grid_id'], $ug["meta_value"]
                    ) );
                }
            }


            // get list of geonames in system; count unique geonameids

            // convert posts
            $post_geonames = $wpdb->get_results( "
                SELECT * FROM $wpdb->postmeta WHERE meta_key = 'geonames';
            ", ARRAY_A );
            foreach ( $post_geonames as $value ) {
                if ( isset( $geonames_ref[$value['meta_value']] ) ) {
                    $wpdb->update(
                        $wpdb->postmeta,
                        [
                            'meta_key' => 'location_grid',
                            'meta_value' => $geonames_ref[$value['meta_value']]['grid_id'],

                        ],
                        [
                            'post_id' => $value['post_id']
                        ],
                        [
                            '%s',
                            '%d'
                        ],
                        [
                            '%d'
                        ]
                    );

                    dt_write_log( 'match post_geonames: ' . $value['post_id'] );
                } else {
                    if ( ! isset( $unmatched[$value['meta_value']] ) ) {
                        $unmatched[$value['meta_value']] = [];
                    }
                    $unmatched[$value['meta_value']][] = [
                        'type' => 'post',
                        'id' => $value['post_id'],
                        'geonameid' => $value['meta_value'],
                        'geoname_row' => $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->dt_geonames WHERE geonameid = %s", $value['meta_value'] ), ARRAY_A ),
                    ];

                    dt_write_log( 'unmatch post_geonames: ' . $value['post_id'] );
                }
            }

            // convert activity log
            $activity_log_geonames = $wpdb->get_results( "
                SELECT * FROM $wpdb->dt_activity_log WHERE meta_key = 'geonames';
            ", ARRAY_A );
            foreach ( $activity_log_geonames as $value ) {
                if ( isset( $geonames_ref[$value['meta_value']] ) ) {
                    $wpdb->update(
                        $wpdb->dt_activity_log,
                        [
                            'meta_key' => 'location_grid',
                            'meta_value' => $geonames_ref[$value['meta_value']]['grid_id'],

                        ],
                        [
                            'histid' => $value['histid']
                        ],
                        [
                            '%s',
                            '%d'
                        ],
                        [
                            '%d'
                        ]
                    );

                    dt_write_log( 'match activity log: ' . $value['histid'] );
                } else {
                    if ( ! isset( $unmatched[$value['meta_value']] ) ) {
                        $unmatched[$value['meta_value']] = [];
                    }
                    $unmatched[$value['meta_value']][] = [
                        'type' => 'activity',
                        'id' => $value['histid'],
                        'geonameid' => $value['meta_value'],
                        'geoname_row' => $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->dt_geonames WHERE geonameid = %s", $value['meta_value'] ), ARRAY_A ),
                    ];

                    dt_write_log( 'unmatch activity log: ' . $value['histid'] );
                }
            }

            // migrate focus
            $default_map_settings = get_option( 'dt_mapping_module_starting_map_level' );
            if ( !empty( $default_map_settings["children"] ) ){
                $new_children = [];
                foreach ( $default_map_settings["children"] as $c ){
                    if ( isset( $geonames_ref[$c] ) ) {
                        $new_children[] = $geonames_ref[$c]["grid_id"];
                    }
                }
                $default_map_settings["children"] = $new_children;
                update_option( 'dt_mapping_module_starting_map_level', $default_map_settings, false );
            }



            // migrate custom locations
            $custom_locations = $wpdb->get_results( "
                SELECT * FROM $wpdb->dt_geonames WHERE is_custom_location = 1 OR geonameid >= 1000000000;
            ", ARRAY_A );
            if ( ! empty( $custom_locations ) && ! is_wp_error( $custom_locations ) ) {
                // loop

                // match the parent_id geonameid and convert it.

                // save new custom location to location_grid
                foreach ( $custom_locations as $value ) {
                    if ( isset( $geonames_ref[ $value['parent_id'] ] ) ) {
                        $parent_location_grid_id = $geonames_ref[ $value['parent_id'] ]["grid_id"];
                        $level                   = 10;
                        $level_name              = 'place';

                        if ( $value['parent_id'] < 1000000000 ) {
                            // not custom
                            $parent_record = $wpdb->get_row( $wpdb->prepare( "
                            SELECT * FROM $wpdb->dt_location_grid
                            WHERE grid_id = %s",
                                $parent_location_grid_id
                            ), ARRAY_A );
                        } else {
                            // custom
                            continue; // abandon sub, sub nested custom locations
                        }

                        $max_id         = (int) $wpdb->get_var( "SELECT MAX(grid_id) FROM $wpdb->dt_location_grid" );
                        $max_id         = max( $max_id, 1000000000 );
                        $custom_grid_id = $max_id + 1;

                        $wpdb->insert(
                            $wpdb->dt_location_grid,
                            [
                                'grid_id'            => $custom_grid_id,
                                'name'               => $value['name'],
                                'level'              => $level,
                                'level_name'         => $level_name,
                                'country_code'       => $parent_record['country_code'],
                                'admin0_code'        => $parent_record['admin0_code'],
                                'parent_id'          => $parent_location_grid_id,
                                'admin0_grid_id'     => $parent_record['admin0_grid_id'],
                                'admin1_grid_id'     => $parent_record['admin1_grid_id'],
                                'admin2_grid_id'     => $parent_record['admin2_grid_id'],
                                'admin3_grid_id'     => $parent_record['admin3_grid_id'],
                                'admin4_grid_id'     => $parent_record['admin4_grid_id'],
                                'admin5_grid_id'     => $parent_record['admin5_grid_id'],
                                'longitude'          => $parent_record['longitude'],
                                'latitude'           => $parent_record['latitude'],
                                'north_latitude'     => '',
                                'south_latitude'     => '',
                                'west_longitude'     => '',
                                'east_longitude'     => '',
                                'population'         => $value['population'],
                                'modification_date'  => current_time( 'mysql' ),
                                'alt_name'           => $value['name'],
                                'alt_population'     => empty( $value['alt_population'] ) ? $value['population'] : $value['alt_population'],
                                'is_custom_location' => 1,
                                'alt_name_changed'   => $value['alt_name_changed'],
                            ],
                            [
                                '%d',
                                '%s', // name
                                '%d', // level
                                '%s', // level_name
                                '%s',
                                '%s',
                                '%d', // parent_id
                                '%d',
                                '%d',
                                '%d',
                                '%d',
                                '%d',
                                '%d', // admin5
                                '%s', // longitude
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%s', // east_longitude
                                '%d', // population
                                '%s', // modification_date
                                '%s',
                                '%s', // alt_name
                                '%d',
                                '%d',
                            ]
                        );

                        dt_write_log( 'match custom location: ' . $custom_grid_id );
                    } else {
                        if ( !isset( $unmatched[ $value['geonameid'] ] ) ) {
                            $unmatched[ $value['geonameid'] ] = [];
                        }
                        $unmatched[ $value['geonameid'] ][] = [
                            'type'        => 'custom_location',
                            'id'          => $value['geonameid'],
                            'geonameid'   => $value['geonameid'],
                            'geoname_row' => $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->dt_geonames WHERE geonameid = %s", $value['geonameid'] ), ARRAY_A ),
                        ];

                        dt_write_log( 'unmatch custom location: ' . $value['geonameid'] );
                    }
                }
            }

            // migrate custom populations
            $custom_populations = $wpdb->get_results( "
                SELECT * FROM $wpdb->dt_geonames WHERE alt_population = 1;
            ", ARRAY_A );
            if ( !empty( $custom_populations ) && !is_wp_error( $custom_populations ) ) {

                foreach ( $custom_populations as $value ) {
                    if ( isset( $geonames_ref[ $value['geonameid'] ] ) ) {
                        $wpdb->update(
                            $wpdb->dt_location_grid,
                            [
                                'alt_population' => $value['alt_population'],
                            ],
                            [
                                'grid_id' => $geonames_ref[ $value['geonameid'] ]['grid_id']
                            ],
                            [
                                '%d'
                            ],
                            [
                                '%d'
                            ]
                        );

                        dt_write_log( 'match custom population: ' . $geonames_ref[ $value['geonameid'] ]['grid_id'] );
                    } else {
                        if ( !isset( $unmatched[ $value['geonameid'] ] ) ) {
                            $unmatched[ $value['geonameid'] ] = [];
                        }
                        $unmatched[ $value['geonameid'] ][] = [
                            'type'        => 'population',
                            'id'          => $value['geonameid'],
                            'geonameid'   => $value['geonameid'],
                            'geoname_row' => $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->dt_geonames WHERE geonameid = %s", $value['geonameid'] ), ARRAY_A ),
                        ];

                        dt_write_log( 'unmatch custom population: ' . $value['geonameid'] );
                    }
                }
            }


            // migrate custom names
            $custom_names = $wpdb->get_results( "
                SELECT * FROM $wpdb->dt_geonames WHERE alt_name_changed = 1;
            ", ARRAY_A );
            if ( !empty( $custom_names ) && !is_wp_error( $custom_names ) ) {

                foreach ( $custom_populations as $value ) {
                    if ( isset( $geonames_ref[ $value['geonameid'] ] ) ) {
                        $wpdb->update(
                            $wpdb->dt_location_grid,
                            [
                                'alt_name' => $value['alt_name'],
                            ],
                            [
                                'grid_id' => $geonames_ref[ $value['geonameid'] ]['grid_id']
                            ],
                            [
                                '%d'
                            ],
                            [
                                '%d'
                            ]
                        );

                        dt_write_log( 'match custom names: ' . $geonames_ref[ $value['geonameid'] ]['grid_id'] );
                    } else {
                        if ( !isset( $unmatched[ $value['geonameid'] ] ) ) {
                            $unmatched[ $value['geonameid'] ] = [];
                        }
                        $unmatched[ $value['geonameid'] ][] = [
                            'type'        => 'names',
                            'id'          => $value['geonameid'],
                            'geonameid'   => $value['geonameid'],
                            'geoname_row' => $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->dt_geonames WHERE geonameid = %s", $value['geonameid'] ), ARRAY_A ),
                        ];

                        dt_write_log( 'unmatch custom names: ' . $value['geonameid'] );
                    }
                }
            }


            // check if any remaining unmatched geonames
            update_option( 'dt_unmatched_geonames', $unmatched, false );
        }

        dt_write_log( get_option( 'dt_unmatched_geonames' ) );

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

