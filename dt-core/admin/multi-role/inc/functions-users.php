<?php
/**
 * User-related functions and filters.
 *
 * @package    Members
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2009 - 2016, Justin Tadlock
 * @link       http://themehybrid.com/plugins/members
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

// Filter `user_has_cap` if denied caps should take precedence.
if ( dt_multi_role_explicitly_deny_caps() ) {
    add_filter( 'user_has_cap', 'dt_user_has_cap_filter', 10, 4 );
}

/**
 * Filter on `user_has_cap` to explicitly deny caps if there are conflicting caps when a
 * user has multiple roles.  WordPress doesn't consistently handle two or more roles that
 * have the same capability but a conflict between being granted or denied.  Core WP
 * merges the role caps so that the last role the user has will take precedence.  This
 * has the potential for granting permission for things that a user shouldn't have
 * permission to do.
 *
 * @since  0.1.0
 * @access public
 * @param  array  $allcaps
 * @param  array  $caps
 * @param  array  $args
 * @param  object $user
 * @return array
 */
function dt_user_has_cap_filter( $allcaps, $caps, $args, $user ) {

    // If the user doesn't have more than one role, bail.
    if ( 1 >= count( (array) $user->roles ) ) {
        return $allcaps;
    }

    // Get the denied caps.
    $denied_caps = array_keys( $allcaps, false );

    // Loop through the user's roles and find any denied caps.
    foreach ( (array) $user->roles as $role ) {

        // Get the role object.
        $role_obj = get_role( $role );

        // If we have an object, merge it's denied caps.
        if ( ! is_null( $role_obj ) ) {
            $denied_caps = array_merge( $denied_caps, array_keys( $role_obj->capabilities, false ) );
        }
    }

    // If there are any denied caps, make sure they take precedence.
    if ( $denied_caps ) {

        foreach ( $denied_caps as $denied_cap ) {
            $allcaps[ $denied_cap ] = false;
        }
    }

    // Return all the user caps.
    return $allcaps;
}

/**
 * Conditional tag to check whether a user has a specific role.
 *
 * @since  0.1.0
 * @access public
 * @param  int     $user_id
 * @param  string  $role
 * @return bool
 */
function dt_user_has_role( $user_id, $role ) {

    $user = new WP_User( $user_id );

    return in_array( $role, (array) $user->roles );
}

/**
 * Conditional tag to check whether the currently logged-in user has a specific role.
 *
 * @since  0.1.0
 * @access public
 * @param  string  $role
 * @return bool
 */
function dt_current_user_has_role( $role ) {

    return is_user_logged_in() ? dt_user_has_role( get_current_user_id(), $role ) : false;
}


function dt_is_administrator(){
    if ( dt_current_user_has_role( 'administrator' ) ){
        return true;
    }
    if ( is_multisite() && is_super_admin() ){
        return true;
    }
    return false;
}

/**
 * Returns an array of the role names a user has.
 *
 * @since  0.1.0
 * @access public
 * @param  int    $user_id
 * @return array
 */
function dt_get_user_role_names( $user_id ) {

    $user = new WP_User( $user_id );

    $names = [];

    foreach ( $user->roles as $role ) {
        $names[ $role ] = dt_multi_role_get_role_name( $role );
    }

    return $names;
}


function dt_list_roles( $use_cache = true ){
    $can_not_promote_to_roles = [];
    if ( !dt_is_administrator() ){
        $can_not_promote_to_roles = array_merge( $can_not_promote_to_roles, [ 'administrator' ] );
    }
    if ( !current_user_can( 'manage_dt' ) ){
        $can_not_promote_to_roles = array_merge( $can_not_promote_to_roles, dt_multi_role_get_cap_roles( 'manage_dt' ) );
    }

    $expected_roles = Disciple_Tools_Roles::get_dt_roles_and_permissions( $use_cache );

    uasort( $expected_roles, function ( $item1, $item2 ){
        return ( $item1['order'] ?? 50 ) <=> ( $item2['order'] ?? 50 );
    } );

    foreach ( $expected_roles as $role_key => $role ){
        $expected_roles[$role_key]['disabled'] = in_array( $role_key, $can_not_promote_to_roles );
    }

    return $expected_roles;
}