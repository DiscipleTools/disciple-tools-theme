<?php
/**
 * Role-related functions that extend the built-in WordPress Roles API.
 *
 * @package    Members
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2009 - 2016, Justin Tadlock
 * @link       http://themehybrid.com/plugins/members
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/**
 * Returns the instance of the `Disciple_Tools_Multi_Role_Factory`.
 *
 * @since  0.1.0
 * @access public
 * @param  string
 * @return object|bool
 */
function dt_multi_role_role_factory() {
    return Disciple_Tools_Multi_Role_Factory::get_instance();
}

/* ====== Multiple Role Functions ====== */

/**
 * Returns a count of all the available roles for the site.
 *
 * @since  0.1.0
 * @access public
 * @return int
 */
function dt_multi_role_get_role_count() {
    return count( $GLOBALS['wp_roles']->role_names );
}

/**
 * Returns an array of `Disciple_Tools_Multi_Role` objects.
 *
 * @since  0.1.0
 * @access public
 * @return array
 */
function dt_multi_role_get_roles() {
    return dt_multi_role_role_factory()->roles;
}

/**
 * Returns an array of role names.
 *
 * @since  0.1.0
 * @access public
 * @return array
 */
function dt_multi_role_get_role_names() {
    $roles = [];

    foreach ( dt_multi_role_role_factory()->roles as $role ) {
        $roles[ $role->slug ] = $role->name;
    }

    return $roles;
}

/**
 * Returns an array of roles.
 *
 * @since  0.1.0
 * @access public
 * @return array
 */
function dt_multi_role_get_role_slugs() {
    return array_keys( dt_multi_role_role_factory()->roles );
}

/**
 * Returns an array of the role names of roles that have users.
 *
 * @since  0.1.0
 * @access public
 * @return array
 */
function dt_multi_role_get_active_role_names() {
    $has_users = [];

    foreach ( dt_multi_role_get_active_role_slugs() as $role ) {
        $has_users[ $role ] = dt_multi_role_get_role_name( $role );
    }

    return $has_users;
}

/**
 * Returns an array of the roles that have users.
 *
 * @since  0.1.0
 * @access public
 * @return array
 */
function dt_multi_role_get_active_role_slugs() {

    $has_users = [];

    foreach ( dt_multi_role_get_role_user_count() as $role => $count ) {

        if ( 0 < $count ) {
            $has_users[] = $role;
        }
    }

    return $has_users;
}

/**
 * Returns an array of the role names of roles that do not have users.
 *
 * @since  0.1.0
 * @access public
 * @return array
 */
function dt_multi_role_get_inactive_role_names() {
    return array_diff( dt_multi_role_get_role_names(), dt_multi_role_get_active_role_names() );
}

/**
 * Returns an array of the roles that have no users.
 *
 * @since  0.1.0
 * @access public
 * @return array
 */
function dt_multi_role_get_inactive_role_slugs() {
    return array_diff( dt_multi_role_get_role_slugs(), dt_multi_role_get_active_role_slugs() );
}

/**
 * Returns an array of editable role names.
 *
 * @since  0.1.0
 * @access public
 * @return array
 */
function dt_multi_role_get_editable_role_names() {
    $editable = [];

    foreach ( dt_multi_role_role_factory()->editable as $role ) {
        $editable[ $role->slug ] = $role->name;
    }

    return $editable;
}

/**
 * Returns an array of editable roles.
 *
 * @since  0.1.0
 * @access public
 * @return array
 */
function dt_multi_role_get_editable_role_slugs() {
    return array_keys( dt_multi_role_role_factory()->editable );
}

/**
 * Returns an array of uneditable role names.
 *
 * @since  0.1.0
 * @access public
 * @return array
 */
function dt_multi_role_get_uneditable_role_names() {
    $uneditable = [];

    foreach ( dt_multi_role_role_factory()->uneditable as $role ) {
        $uneditable[ $role->slug ] = $role->name;
    }

    return $uneditable;
}

/**
 * Returns an array of uneditable roles.
 *
 * @since  0.1.0
 * @access public
 * @return array
 */
function dt_multi_role_get_uneditable_role_slugs() {
    return array_keys( dt_multi_role_role_factory()->uneditable );
}

/**
 * Returns an array of core WordPress role names.
 *
 * @since  0.1.0
 * @access public
 * @return array
 */
function dt_multi_role_get_wordpress_role_names() {
    $names = [];

    foreach ( dt_multi_role_role_factory()->wordpress as $role ) {
        $names[ $role->slug ] = $role->name;
    }

    return $names;
}

/**
 * Returns an array of core WP roles.
 *
 * @since  0.1.0
 * @access public
 * @return array
 */
function dt_multi_role_get_wordpress_role_slugs() {
    return array_keys( dt_multi_role_role_factory()->wordpress );
}

/* ====== Single Role Functions ====== */

/**
 * Conditional tag to check if a role exists.
 *
 * @since  0.1.0
 * @access public
 * @param  string
 * @return bool
 */
function dt_multi_role_role_exists( $role ) {
    return $GLOBALS['wp_roles']->is_role( $role );
}

/**
 * Gets a Members role object.
 *
 * @see    Disciple_Tools_Multi_Role
 * @since  0.1.0
 * @access public
 * @param  string
 * @return object
 */
function dt_multi_role_get_role( $role ) {
    return dt_multi_role_role_factory()->get_role( $role );
}

/**
 * Sanitizes a role name.  This is a wrapper for the `sanitize_key()` WordPress function.  Only
 * alphanumeric characters and underscores are allowed.  Hyphens are also replaced with underscores.
 *
 * @since  0.1.0
 * @access public
 * @return int
 */
function dt_multi_role_sanitize_role( $role ) {
    $_role = strtolower( $role );
    $_role = preg_replace( '/[^a-z0-9_\-\s]/', '', $_role );
    return apply_filters( 'dt_multi_role_sanitize_role', str_replace( ' ', '_', $_role ), $role );
}

/**
 * WordPress provides no method of translating custom roles other than filtering the
 * `translate_with_gettext_context` hook, which is very inefficient and is not the proper
 * method of translating.  This is a method that allows plugin authors to hook in and add
 * their own translations.
 *
 * Note the core WP `translate_user_role()` function only translates core user roles.
 *
 * @since  0.1.0
 * @access public
 * @param  string  $role
 * @return string
 */
function dt_multi_role_translate_role( $role ) {
    global $wp_roles;

    return apply_filters( 'dt_multi_role_translate_role', translate_user_role( $wp_roles->role_names[ $role ] ), $role );
}

/**
 * Conditional tag to check if a role has any users.
 *
 * @since  0.1.0
 * @access public
 * @return bool
 */
function dt_multi_role_role_has_users( $role ) {
    return in_array( $role, dt_multi_role_get_active_role_slugs() );
}

/**
 * Conditional tag to check if a role has any capabilities.
 *
 * @since  0.1.0
 * @access public
 * @return bool
 */
function dt_multi_role_role_has_caps( $role ) {
    return dt_multi_role_role_factory()->get_role( $role )->has_caps;
}

/**
 * Counts the number of users for all roles on the site and returns this as an array.  If
 * the `$role` parameter is given, the return value will be the count just for that particular role.
 *
 * @since  0.2.0
 * @access public
 * @param  string     $role
 * @return int|array
 */
function dt_multi_role_get_role_user_count( $role = '' ) {

    // If the count is not already set for all roles, let's get it.
    if ( empty( disciple_tools()->multi->role_user_count ) ) {

        // Count users.
        $user_count = count_users();

        // Loop through the user count by role to get a count of the users with each role.
        foreach ( $user_count['avail_roles'] as $_role => $count ) {
            disciple_tools()->multi->role_user_count[ $_role ] = $count;
        }
    }

    // Return the role count.
    if ( $role ) {
        return isset( disciple_tools()->multi->role_user_count[ $role ] ) ? disciple_tools()->multi->role_user_count[ $role ] : 0;
    }

    // If the `$role` parameter wasn't passed into this function, return the array of user counts.
    return disciple_tools()->multi->role_user_count;
}

/**
 * Returns the number of granted capabilities that a role has.
 *
 * @since  0.1.0
 * @access public
 * @param  string
 * @return int
 */
function dt_multi_role_get_role_granted_cap_count( $role ) {
    return dt_multi_role_role_factory()->get_role( $role )->granted_cap_count;
}

/**
 * Returns the number of denied capabilities that a role has.
 *
 * @since  0.1.0
 * @access public
 * @param  string
 * @return int
 */
function dt_multi_role_get_role_denied_cap_count( $role ) {
    return dt_multi_role_role_factory()->get_role( $role )->denied_cap_count;
}

/**
 * Returns the human-readable role name.
 *
 * @since  0.1.0
 * @access public
 * @param  string  $role
 * @return string
 */
function dt_multi_role_get_role_name( $role ) {
    return dt_multi_role_role_factory()->get_role( $role )->name;
}

/**
 * Conditional tag to check whether a role can be edited.
 *
 * @since  0.1.0
 * @access public
 * @param  string  $role
 * @return bool
 */
function dt_multi_role_is_role_editable( $role ) {
    return dt_multi_role_role_factory()->get_role( $role )->is_editable;
}

/**
 * Conditional tag to check whether a role is a core WordPress URL.
 *
 * @since  0.1.0
 * @access public
 * @param  string  $role
 * @return bool
 */
function dt_multi_role_is_wordpress_role( $role ) {
    return in_array( $role, [ 'administrator', 'editor', 'author', 'contributor', 'subscriber' ] );
}

/* ====== URLs ====== */

/**
 * Returns the URL for the add-new role admin screen.
 *
 * @since  0.1.0
 * @access public
 * @return string
 */
function dt_multi_role_get_new_role_url() {
    return add_query_arg( 'page', 'role-new', admin_url( 'users.php' ) );
}

/**
 * Returns the URL for the clone role admin screen.
 *
 * @since  0.1.0
 * @access public
 * @param  string  $role
 * @return string
 */
function dt_multi_role_get_clone_role_url( $role ) {
    return add_query_arg( 'clone', $role, dt_multi_role_get_new_role_url() );
}

/**
 * Returns the URL for the edit roles admin screen.
 *
 * @since  0.1.0
 * @access public
 * @return string
 */
function dt_multi_role_get_edit_roles_url() {
    return add_query_arg( 'page', 'roles', admin_url( 'users.php' ) );
}

/**
 * Returns the URL for the edit "mine" roles admin screen.
 *
 * @since  0.1.0
 * @access public
 * @param  string  $view
 * @return string
 */
function dt_multi_role_get_role_view_url( $view ) {
    return add_query_arg( 'role_view', $view, dt_multi_role_get_edit_roles_url() );
}

/**
 * Returns the URL for the edit role admin screen.
 *
 * @since  0.1.0
 * @access public
 * @param  string  $role
 * @return string
 */
function dt_multi_role_get_edit_role_url( $role ) {
    return add_query_arg( [ 'action' => 'edit', 'role' => $role ], dt_multi_role_get_edit_roles_url() );
}

/**
 * Returns the URL to permanently delete a role (edit roles screen).
 *
 * @since  0.1.0
 * @access public
 * @param  string  $role
 * @return string
 */
function dt_multi_role_get_delete_role_url( $role ) {
    $url = add_query_arg( [ 'action' => 'delete', 'role' => $role ], dt_multi_role_get_edit_roles_url() );

    return wp_nonce_url( $url, 'delete_role', 'dt_multi_role_delete_role_nonce' );
}

/**
 * Returns the URL for the users admin screen specific to a role.
 *
 * @since  0.1.0
 * @access public
 * @param  string  $role
 * @return string
 */
function dt_multi_role_get_role_users_url( $role ) {
    return admin_url( add_query_arg( 'role', $role, 'users.php' ) );
}
