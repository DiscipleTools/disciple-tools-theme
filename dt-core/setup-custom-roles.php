<?php

/**
 * Add custom roles and permissions from the roles manager.
 */
add_filter( 'dt_set_roles_and_permissions', 'dt_setup_custom_roles_and_permissions', 11, 1 );

function dt_setup_custom_roles_and_permissions( $roles ) {
    $custom_roles = get_option( 'dt_custom_roles', [] );
    foreach ( $custom_roles as $role ) {
        $permission_keys = $role['capabilities'];
        if ( is_array( $permission_keys ) ) {
            $permissions = array_reduce($permission_keys, function( $permissions, $key ) {
                $permissions[$key] = true;
                return $permissions;
            }, []);
        } else {
            $permissions = [];
        }
        $roles[$role['slug']] = [
            'label' => $role['label'],
            'permissions' => $permissions,
            'description' => $role['description'],
            'is_editable' => true,
            'custom' => true
        ];
    }

    return $roles;
}
