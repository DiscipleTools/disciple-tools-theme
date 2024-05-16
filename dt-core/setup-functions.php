<?php
/**
 * This file is only called from the wp-admin and so won't be run on every request.
 */


/**
 * make sure roles and permissions conform to the values set by the modules
 */
add_action( 'init', 'dt_setup_roles_and_permissions' );

function dt_setup_roles_and_permissions(){
    $default_role = get_option( 'default_role' );
    if ( $default_role === 'subscriber' || empty( $default_role ) ){
        update_option( 'default_role', 'multiplier' );
    }

    $expected_roles_options = get_option( 'dt_options_roles_and_permissions', [] );
    $expected_roles = Disciple_Tools_Roles::get_dt_roles_and_permissions( false );
    $expected_roles = dt_array_merge_recursive_distinct( $expected_roles, $expected_roles_options );
    $dt_roles = array_map( function ( $a ){
        return array_keys( $a['permissions'] );
    }, $expected_roles );
    $dt_permissions = array_merge( ...array_values( $dt_roles ) );

    $role_keys = dt_multi_role_get_role_slugs();
    foreach ( $expected_roles as $role_key => $role_values ){
        if ( !in_array( $role_key, $role_keys, true ) ){
            add_role( $role_key, $role_values['label'] ?? '', $role_values['permissions'] ?? [] );
        }
    }
    //get all the roles
    $roles = dt_multi_role_get_roles();

    foreach ( $roles as $role_key => $role_value ){
        if ( in_array( $role_key, [ 'registered' ] ) ){
            continue;
        }
        $role = get_role( $role_key );
        if ( $role && isset( $expected_roles[$role_key] ) ){
            //add permissions to roles
            foreach ( $expected_roles[$role_key]['permissions'] as $cap_key => $cap_grant ){
                if ( empty( $cap_key ) ){
                    continue;
                }
                if ( !isset( $role_value->caps[$cap_key] ) ){
                    $role->add_cap( $cap_key, $cap_grant );
                } else if ( $role_value->caps[$cap_key] !== $cap_grant ){
                    if ( $cap_grant === false ){
                        $role->remove_cap( $cap_key );
                    } else {
                        $role->add_cap( $cap_key );
                    }
                }
            }
            //remove permissions if they are not set by the $expected_roles
            foreach ( $role->capabilities as $cap_key => $cap_grant ){
                if ( $cap_grant === true && !isset( $expected_roles[$role_key]['permissions'][$cap_key] ) ){
                    $wp_capabilities = dt_multi_role_get_wp_capabilities();
                    if ( in_array( $role_key, [ 'administrator' ], true ) && ( !in_array( $cap_key, $dt_permissions, true ) || in_array( $cap_key, $wp_capabilities, true ) ) ){
                        continue; //don't remove a non D.T cap from the administrator
                    }
                    $role->remove_cap( $cap_key );
                }
            }
        } else {
            if ( !in_array( $role_key, [ 'administrator' ], true ) ){
                // remove roles that are no longer defined.
                remove_role( $role_key );
            }
        }
    }
}

add_action( 'switch_blog', 'dt_set_up_wpdb_tables', 99, 2 );
function dt_set_up_wpdb_tables(){
    global $wpdb;
    $wpdb->dt_activity_log = $wpdb->prefix . 'dt_activity_log'; // Prepare database table names
    $wpdb->dt_reports = $wpdb->prefix . 'dt_reports';
    $wpdb->dt_reportmeta = $wpdb->prefix . 'dt_reportmeta';
    $wpdb->dt_share = $wpdb->prefix . 'dt_share';
    $wpdb->dt_notifications = $wpdb->prefix . 'dt_notifications';
    $wpdb->dt_notifications_queue = $wpdb->prefix . 'dt_notifications_queue';
    $wpdb->dt_post_user_meta = $wpdb->prefix . 'dt_post_user_meta';
    $wpdb->dt_location_grid = $wpdb->prefix . 'dt_location_grid';
    $wpdb->dt_location_grid_meta = $wpdb->prefix . 'dt_location_grid_meta';

    $more_tables = apply_filters( 'dt_custom_tables', [] );
    foreach ( $more_tables as $table ){
        $wpdb->$table = $wpdb->prefix . $table;
    }
}

/**
 * Route Front Page depending on login role
 */
function dt_route_front_page() {
    if ( current_user_can( 'access_disciple_tools' ) || current_user_can( 'access_contacts' ) ) {
        /**
         * Use this filter to add a new landing page for logged in users with 'access_contacts' capabilities
         */
        if ( current_user_can( 'access_contacts' ) ){
            wp_safe_redirect( apply_filters( 'dt_front_page', home_url( '/contacts' ) ) );
        } else {
            wp_safe_redirect( apply_filters( 'dt_front_page', home_url( '/settings' ) ) );
        }
    }
    else if ( ! is_user_logged_in() ) {
        dt_please_log_in();
    }
    else {
        /**
         * Use this filter to give a front page for logged in users who do not have basic 'access_contacts' capabilities
         * This is used for specific custom roles that are not intended to see the basic framework of DT.
         * Use this to create a dedicated landing page for partners, donors, or subscribers.
         */
        wp_safe_redirect( apply_filters( 'dt_non_standard_front_page', home_url( '/registered' ) ) );
    }
}