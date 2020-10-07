<?php
/**
 * This file is only called from the wp-admin and so won't be run on every request.
 */


/**
 * make sure roles and permissions conform to the values set by the modules
 */
add_action( 'init', "dt_setup_roles_and_permissions" );

function dt_setup_roles_and_permissions(){
    $expected_roles = apply_filters( 'dt_set_roles_and_permissions', [] );
    $role_keys = dt_multi_role_get_role_slugs();
    foreach ( $expected_roles as $role_key => $role_values ){
        if ( !in_array( $role_key, $role_keys )){
            add_role( $role_key, $role_values["label"] ?? "", $role_values["permissions"] ?? [] );
        }
    }
    //get all the roles
    $roles = dt_multi_role_get_roles();

    foreach ( $roles as $role_key => $role_value ){
        if ( in_array( $role_key, [ "administrator", "registered" ] ) ){
            continue;
        }
        $role = get_role( $role_key );
        if ( isset( $expected_roles[$role_key] ) ){
            //add permissions to roles
            foreach ( $expected_roles[$role_key]["permissions"] as $cap_key => $cap_grant ){
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
            //remove permissions if they are set by the $expected_roles
            foreach ( $role->capabilities as $cap_key => $cap_grant ){
                if ( $cap_grant === true && !isset( $expected_roles[$role_key]["permissions"][$cap_key] ) ){
                    $role->remove_cap( $cap_key );
                }
            }
        } else {
            // remove roles that are no longer defined.
            remove_role( $role_key );
        }
    }
}
