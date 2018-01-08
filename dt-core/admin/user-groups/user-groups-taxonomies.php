<?php

/**
 * User Groups Taxonomies
 *
 * @package Plugins/Users/Groups/Taxonomy
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 ** *******************************************************************************************************************
 * REGISTER
 */

/**
 * Register default user group taxonomies
 * This function is hooked onto WordPress's `init` action and creates two new
 * `Disciple_Tools_User_Taxonomy` objects for user "groups" and "types". It can be unhooked
 * and these taxonomies can be replaced with your own custom ones.
 *
 * @since 0.1.4
 */
function disciple_tools_register_default_user_group_taxonomy()
{
    new Disciple_Tools_User_Taxonomy(
        'user-group', 'users/group', [
            'singular' => __( 'Team', 'disciple-tools' ),
            'plural'   => __( 'Teams', 'disciple-tools' ),
        ]
    );
}

/**
 * Register default user group taxonomies
 * This function is hooked onto WordPress's `init` action and creates two new
 * `Disciple_Tools_User_Taxonomy` objects for user "groups" and "types". It can be unhooked
 * and these taxonomies can be replaced with your own custom ones.
 *
 * @since 0.1.4
 */
function disciple_tools_register_default_user_type_taxonomy()
{
    new Disciple_Tools_User_Taxonomy(
        'user-type', 'users/type', [
            'singular' => __( 'Type', 'disciple-tools' ),
            'plural'   => __( 'Types', 'disciple-tools' ),
        ]
    );
}

/**
 ** *******************************************************************************************************************
 * HOOKS
 */

// Register the default taxonomies
add_action( 'init', 'disciple_tools_register_default_user_group_taxonomy' );
//add_action( 'init', 'disciple_tools_register_default_user_type_taxonomy'  ); // TODO: Enabling this will give user groups a types category. Remove if not neccissary for MVP

// Enqueue assets
add_action( 'admin_head', 'dt_groups_admin_assets' );

// WP User Profiles
add_filter( 'disciple_tools_profiles_sections', 'disciple_tools_groups_add_profile_section' );

/**
 ** *******************************************************************************************************************
 * COMMON
 */

/**
 * Get terms for a user and a taxonomy
 *
 * @since 0.1.0
 *
 * @param mixed $user
 * @param int   $taxonomy
 *
 * @return object|bool //corrected from boolean
 */
function disciple_tools_get_terms_for_user( $user = false, $taxonomy = '' )
{

    // Verify user ID
    $user_id = is_object( $user )
        ? $user->ID
        : absint( $user );

    // Bail if empty
    if ( empty( $user_id ) ) {
        return false;
    }

    // Return user terms
    return wp_get_object_terms(
        $user_id, $taxonomy, [
            'fields' => 'all_with_object_id',
        ]
    );
}

/**
 * Save taxonomy terms for a specific user
 *
 * @since 0.1.0
 *
 * @param int     $user_id
 * @param string  $taxonomy
 * @param array   $terms
 * @param boolean $bulk
 *
 * @return boolean
 */
function disciple_tools_set_terms_for_user( $user_id, $taxonomy, $terms = [], $bulk = false )
{

    // Get the taxonomy
    $tax = get_taxonomy( $taxonomy );

    // Make sure the current user can edit the user and assign terms before proceeding.
    if ( !current_user_can( 'edit_user', $user_id ) && current_user_can( $tax->cap->assign_terms ) ) {
        return false;
    }

    /* This doesn't seem like correct code to me, why should this function read
     * $_POST? And if it does, where is the nonce verification? I commented the
     * code out. */

    /* if( empty( $terms ) && empty( $bulk ) ) { */
    /*     $terms = isset( $_POST[ $taxonomy ] ) */
    /*         ? $_POST[ $taxonomy ] */
    /*         : null; */
    /* } */

    // Delete all user terms
    if ( is_null( $terms ) || empty( $terms ) ) {
        wp_delete_object_term_relationships( $user_id, $taxonomy );
        // Set the terms
    } else {
        $_terms = array_map( 'sanitize_key', $terms );

        // Sets the terms for the user
        wp_set_object_terms( $user_id, $_terms, $taxonomy, false );
    }

    // Clean the cache
    clean_object_term_cache( $user_id, $taxonomy );
}

/**
 * Get all user groups
 *
 * @uses  get_taxonomies() To get user-group taxonomies
 * @since 0.1.5
 *
 * @param array  $args     Optional. An array of `key => value` arguments to
 *                         match against the taxonomy objects. Default empty array.
 * @param string $output   Optional. The type of output to return in the array.
 *                         Accepts either taxonomy 'names' or 'objects'. Default 'names'.
 * @param string $operator Optional. The logical operation to perform.
 *                         Accepts 'and' or 'or'. 'or' means only one element from
 *                         the array needs to match; 'and' means all elements must
 *                         match. Default 'and'.
 *
 * @return array A list of taxonomy names or objects.
 */
function disciple_tools_get_user_groups( $args = [], $output = 'names', $operator = 'and' )
{

    // Parse arguments
    $r = wp_parse_args(
        $args, [
            'user_group' => true,
        ]
    );

    // Return user group taxonomies
    return get_taxonomies( $r, $output, $operator );
}

/**
 * Get all user group objects
 *
 * @uses  disciple_tools_get_user_groups() To get user group objects
 * @since 0.1.5
 *
 * @param array  $args     See disciple_tools_get_user_groups()
 * @param string $operator See disciple_tools_get_user_groups()
 *
 * @return array
 */
function disciple_tools_get_user_group_objects( $args = [], $operator = 'and' )
{
    return disciple_tools_get_user_groups( $args, 'objects', $operator );
}

/**
 * Return a list of users in a specific group
 *
 * @since 0.1.0
 */
function disciple_tools_get_users_of_group( $args = [] )
{

    // Parse arguments
    $r = wp_parse_args(
        $args, [
            'taxonomy' => 'user-type',
            'term'     => '',
            'term_by'  => 'slug',
        ]
    );

    // Get user IDs in group
    $term = get_term_by( $r['term_by'], $r['term'], $r['taxonomy'] );
    $user_ids = get_objects_in_term( $term->term_id, $r['taxonomy'] );

    // Bail if no users in this term
    if ( empty( $term ) || empty( $user_ids ) ) {
        return [];
    }

    // Return queried users
    return get_users(
        [
            'orderby' => 'display_name',
            'include' => $user_ids,
        ]
    );
}

/**
 ** *******************************************************************************************************************
 * ADMIN
 */

/**
 * Tweak admin styling for a user groups layout
 *
 * @since 0.1.4
 */
function dt_groups_admin_assets()
{
    global $pagenow;

    if ( 'users.php' === $pagenow || 'user-new.php' === $pagenow || 'user-edit.php' === $pagenow || 'edit-tags.php' === $pagenow || 'profile.php' === $pagenow ) {

        $url = disciple_tools()->plugin_url;
        $ver = disciple_tools()->version;

        wp_enqueue_style( 'disciple_tools_groups', $url . 'dt-core/admin/css/user-groups.css', false, $ver, false );
    }
}

/**
 * Add new section to User Profiles
 *
 * @since 0.1.9
 *
 * @param array $sections
 *
 * @return array
 */
function disciple_tools_groups_add_profile_section( $sections = [] )
{

    // Copy for modifying
    $new_sections = $sections;

    // Add the "Activity" section
    $new_sections['groups'] = [
        'id'    => 'groups',
        'slug'  => 'groups',
        'name'  => esc_html__( 'Groups', 'disciple_tools' ),
        'cap'   => 'edit_profile',
        'icon'  => 'dashicons-groups',
        'order' => 90,
    ];

    // Filter & return
    return apply_filters( 'disciple_tools_groups_add_profile_section', $new_sections, $sections );
}
