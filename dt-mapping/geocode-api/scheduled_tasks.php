<?php

/**
 * Background process to geocode location grid meta if it is missing
 * for posts and users
 */

// Schedule Trash collection.
if ( ! wp_next_scheduled( 'dt_location_meta_create' ) ) {
    wp_schedule_event( time(), 'daily', 'dt_location_meta_create' );
}

function dt_location_meta_create(){
    if ( dt_is_rest() ){
        return;
    }
    //check mapbox api key
    $mapbox_key = DT_Mapbox_API::get_key();
    if ( empty( $mapbox_key ) ){
        return;
    }
    $site_health = get_option( 'dt_site_health', [] );
    $last_checked_location_grid = !empty( $site_health['last_checked_location_grid'] ) ? $site_health['last_checked_location_grid'] : 0;

    global $wpdb;
    //find all location grid meta that have not been geocoded
    $query = $wpdb->get_results( $wpdb->prepare( "
        SELECT lgm.postmeta_id_location_grid, pm.meta_id, pm.meta_value, pm.post_id
        FROM $wpdb->postmeta pm
        LEFT JOIN $wpdb->dt_location_grid_meta lgm ON ( lgm.postmeta_id_location_grid = pm.meta_id )
        WHERE meta_key = 'location_grid'
        AND meta_id > %d
        AND meta_value >= 100000000
        ORDER BY meta_id ASC
        LIMIT 1000",
        $last_checked_location_grid
    ), ARRAY_A);

    $new_last_checked_location_grid = null;
    $locations_to_update = [];
    foreach ( $query as $row ){
        if ( empty( $row['postmeta_id_location_grid'] ) ){
            $locations_to_update[] = $row;
        }
        if ( count( $locations_to_update ) >= 100 ){
            $new_last_checked_location_grid = $row['meta_id'];
            break;
        }
    }
    $geocoder = new Location_Grid_Geocoder();
    foreach ( $locations_to_update as $row ){
        $grid = $geocoder->query_by_grid_id( $row['meta_value'] );
        if ( $grid ){
            $location_meta_grid = [];

            Location_Grid_Meta::validate_location_grid_meta( $location_meta_grid );
            $location_meta_grid['post_id'] = $row['post_id'];
            $location_meta_grid['post_type'] = get_post_type( $row['post_id'] );
            $location_meta_grid['grid_id'] = $row['meta_value'];
            $location_meta_grid['lng'] = $grid['longitude'];
            $location_meta_grid['lat'] = $grid['latitude'];
            $location_meta_grid['level'] = $grid['level_name'];
            $location_meta_grid['label'] = $geocoder->_format_full_name( $grid );

            $potential_error = Location_Grid_Meta::add_location_grid_meta( $row['post_id'], $location_meta_grid, $row['meta_id'] );
        }
    }

    //update $last_checked_location_grid
    if ( empty( $new_last_checked_location_grid ) && !empty( $query ) ){
        //last meta_id
        $new_last_checked_location_grid = $query[ count( $query ) - 1 ]['meta_id'];
    }
    if ( !empty( $new_last_checked_location_grid ) ){
        $site_health['last_checked_location_grid'] = $new_last_checked_location_grid;
        update_option( 'dt_site_health', $site_health, false );
    }

    $bob = 'bob';

    //users
    $geocoder = new Location_Grid_Geocoder();
    $query = $wpdb->get_results( $wpdb->prepare( "
        SELECT *
        FROM $wpdb->usermeta
        WHERE meta_key = %s
        AND umeta_id NOT IN (
            SELECT DISTINCT( postmeta_id_location_grid )
            FROM $wpdb->dt_location_grid_meta)
        AND meta_value >= 100000000
        LIMIT %d",
        $wpdb->prefix . 'location_grid',
        50
    ), ARRAY_A);
    if ( ! empty( $query ) ) {
        foreach ( $query as $row ) {
            $grid = $geocoder->query_by_grid_id( $row['meta_value'] );
            if ( $grid ) {
                $location_meta_grid = [];
                Location_Grid_Meta::validate_location_grid_meta( $location_meta_grid );
                $location_meta_grid['post_id'] = $row['user_id'];
                $location_meta_grid['post_type'] = 'users';
                $location_meta_grid['grid_id'] = $row['meta_value'];
                $location_meta_grid['lng'] = $grid['longitude'];
                $location_meta_grid['lat'] = $grid['latitude'];
                $location_meta_grid['level'] = $grid['level_name'];
                $location_meta_grid['label'] = $geocoder->_format_full_name( $grid );
                $potential_error = Location_Grid_Meta::add_user_location_grid_meta( $row['user_id'], $location_meta_grid, $row['umeta_id'] );
            }
        }
    }
}