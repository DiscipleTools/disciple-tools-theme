<?php
/**
 * Presenter template for theme support
 *
 * @package  Disciple_Tools
 * @category Plugin
 * @author   Chasm.Solutions & Kingdom.Training
 * @since    0.1.0
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

/** Functions to output data for the theme. @see Buddypress bp-members-template.php or bp-groups-template.php for an example of the role of this page */

/**
 * Prepares the keys of user connections for WP_Query
 * This function builds the array for the meta_query used in WP_Query to retrieve only records associated with
 * the user or the teams the user is connected to.
 * Example return:
 * Array
 *   (
 *       [relation] => OR
 * [0] => Array
 * (
 * [key] => assigned_to
 * [value] => user-1
 * )
 * [1] => Array
 * (
 * [key] => assigned_to
 * [value] => group-1
 * )
 * )
 *
 * @return array
 */
function dt_get_user_associations()
{

    // Set variables
    global $wpdb;
    $user_connections = [];

    // Set constructor
    $user_connections['relation'] = 'OR';

    // Get current user ID and build meta_key for current user
    $user_id = get_current_user_id();
    $user_key_value = 'user-' . $user_id;
    $user_connections[] = [ 'key' => 'assigned_to', 'value' => $user_key_value ];

    // Build arrays for current groups connected to user
    $results = $wpdb->get_results( $wpdb->prepare( "SELECT
            `$wpdb->term_relationships`.`term_taxonomy_id`
        FROM
            `$wpdb->term_relationships`
        WHERE
            object_id = %d", $user_id ), ARRAY_A );

    foreach ( $results as $result ) {
        $user_connections[] = [ 'key' => 'assigned_to', 'value' => 'group-' . $result['term_taxonomy_id'] ];
    }

    // Return array to the meta_query
    return $user_connections;
}

/**
 * Gets team contacts for a specified user_id
 * Example return:
 * Array
 * (
 * [relation] => OR
 * [0] => Array
 * (
 * [key] => assigned_to
 * [value] => user-1
 * )
 * [1] => Array
 * (
 * [key] => assigned_to
 * [value] => group-1
 * )
 * )
 *
 * @param $user_id
 *
 * @return array
 */
function dt_get_team_contacts( $user_id )
{
    // get variables
    global $wpdb;
    $user_connections = [];
    $user_connections['relation'] = 'OR';
    $members = [];

    // First Query
    // Build arrays for current groups connected to user
    $results = $wpdb->get_results( $wpdb->prepare( "SELECT
            DISTINCT `$wpdb->term_relationships`.`term_taxonomy_id`
        FROM
            `$wpdb->term_relationships`
        INNER JOIN
            `$wpdb->term_taxonomy`
        ON
            `$wpdb->term_relationships`.`term_taxonomy_id` = `$wpdb->term_taxonomy`.`term_taxonomy_id`
        WHERE
            object_id  = %d
            AND taxonomy = 'user-group'", $user_id ), ARRAY_A );

    // Loop
    foreach ( $results as $result ) {
        // create the meta query for the group
        $user_connections[] = [ 'key' => 'assigned_to', 'value' => 'group-' . $result['term_taxonomy_id'] ];

        // Second Query
        // query a member list for this group
        // build list of member ids who are part of the team
        $results2 = $wpdb->get_results( $wpdb->prepare( "SELECT
                `$wpdb->term_relationships`.object_id
            FROM
                `$wpdb->term_relationships`
            WHERE
                term_taxonomy_id = %d", $result['term_taxonomy_id'] ), ARRAY_A );

        // Inner Loop
        foreach ( $results2 as $result2 ) {

            if ( $result2['object_id'] != $user_id ) {
                $members[] = $result2['object_id'];
            }
        }
    }

    $members = array_unique( $members );

    foreach ( $members as $member ) {
        $user_connections[] = [ 'key' => 'assigned_to', 'value' => 'user-' . $member ];
    }

    // return
    return $user_connections;
}

/**
 * Gets the current site defaults defined in the notifications config section in wp-admin
 *
 * @return array
 */
function dt_get_site_notification_defaults()
{
    $site_options = dt_get_option( 'dt_site_options' );

    return $site_options['user_notifications'];
}

/**
 * Returns the site default user fields
 *
 * @return array
 */
function dt_get_site_default_user_fields(): array
{
    $site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
    if ( ! $site_custom_lists ) {
        return [];
    }

    return $site_custom_lists['user_fields'];
}

/**
 * Echos user display name
 *
 * @param int $user_id
 */
function dt_user_display_name( int $user_id )
{
    echo esc_html( dt_get_user_display_name( $user_id ) );
}

/**
 * Returns user display name
 *
 * @param $user_id
 *
 * @return string|WP_Error
 */
function dt_get_user_display_name( $user_id )
{
    $user = get_userdata( $user_id );

    if ( ! $user ) {
        return ''; // return blank if user id does not exist
    }

    $display_name = $user->display_name;
    if ( empty( $display_name ) ) {
        $display_name = $user->nickname;
        if ( empty( $display_name ) ) {
            $display_name = $user->user_login;
        }
    }

    return $display_name;
}

/**
 * @param $profile_fields
 *
 * @return mixed
 */
function dt_modify_profile_fields( $profile_fields )
{

    $site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
    if ( ! $site_custom_lists ) {
        return $profile_fields;
    }
    $user_fields = $site_custom_lists['user_fields'];

    foreach ( $user_fields as $field ) {
        if ( $field['enabled'] ) {
            $profile_fields[ $field['key'] ] = $field['label'];
        }
    }

    return $profile_fields;
}

if ( is_admin() ) {
    // Add elements to the contact section of the profile.
    add_filter( 'user_contactmethods', 'dt_modify_profile_fields' );
}

/**
 * Compares the user_metadata array with the site user fields and returns a combined array limited to site_user_fields.
 * This is used in the theme template to display the user profile.
 *
 * @param array $usermeta
 *
 * @return array
 */
function dt_build_user_fields_display( array $usermeta ): array
{
    $fields = [];

    $site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
    if ( ! $site_custom_lists ) {
        return [];
    }
    $site_user_fields = $site_custom_lists['user_fields'];

    foreach ( $site_user_fields as $key => $value ) {
        if ( $value['enabled'] ) { // if the site field is enabled
            $i = 0;

            foreach ( $usermeta as $k => $v ) {
                if ( $key == $k ) {
                    $fields[] = array_merge( $value, [ 'value' => $v[0] ] );
                    $i++;
                }
            }

            if ( $i === 0 ) { // if usermeta has no matching field to the standard site fields, then set value to empty string.
                $fields[] = array_merge( $value, [ 'value' => '' ] );
            }
        }
    }

    return $fields;
}

/**
 * @param int $user_id
 *
 * @return array|bool
 */
function dt_get_user_locations_list( int $user_id )
{
    global $wpdb;

    // get connected location ids to user
    $location_ids = $wpdb->get_col(
        $wpdb->prepare(
        "SELECT p2p_from as location_id FROM  $wpdb->p2p WHERE p2p_to = '%d' AND p2p_type = 'team_member_locations';", $user_id )
    );

    // check if null return
    if ( empty( $location_ids ) ) {
        return false;
    }

    // get location posts from connected array
    $location_posts = new WP_Query( [ 'post__in' => $location_ids, 'post_type' => 'locations' ] );

    return $location_posts->posts;
}

/**
 * Gets an array of teams populated with an array of members for each team
 * array(
 *      team_id
 *      team_name
 *      team_members array(
 *              ID
 *              display_name
 *              user_email
 *              user_url
 *
 * @param int $user_id
 *
 * @return array|bool
 */
function dt_get_user_team_members_list( int $user_id )
{

    $team_members_list = [];

    $teams = wp_get_object_terms( $user_id, 'user-group' );
    if ( empty( $teams ) || is_wp_error( $teams ) ) {
        return false;
    }

    foreach ( $teams as $team ) {

        $team_id = $team->term_id;
        $team_name = $team->name;

        $members_list = [];
        $args = [
            'taxonomy' => 'user-group',
            'term'     => $team_id,
            'term_by'  => 'id',
        ];
        $results = disciple_tools_get_users_of_group( $args );
        if ( !empty( $results ) ) {
            foreach ( $results as $result ) {
                if ( !( $user_id == $result->data->ID ) ) {
                    $members_list[] = [
                        'ID'           => $result->data->ID,
                        'display_name' => $result->data->display_name,
                        'user_email'   => $result->data->user_email,
                        'user_url'     => $result->data->user_url,
                    ];
                }
            }
        }

        $team_members_list[] = [
            'team_id'      => $team_id,
            'team_name'    => $team_name,
            'team_members' => $members_list,
        ];
    }

    return $team_members_list;
}

/**
 * Tests if a user notification is enabled.
 *
 *
 * @param string   $notification_name
 * @param array|null $user_meta_data
 * @param int|null $user_id
 *
 * @return bool
 */
function dt_user_notification_is_enabled( string $notification_name, array $user_meta_data = null, int $user_id = null ): bool
{
    if ( empty( $user_id ) ) {
        $user_id = get_current_user_id();
    }

    // Check status of site defined defaults
    $site_defaults = dt_get_site_notification_defaults();
    if ( $site_defaults[ $notification_name ] ) { // This checks to see if the site has required this notification to be true. If true, then personal preference is not checked.
        return true;
    }

    if ( empty( $user_meta_data ) ) {
        $user_meta_data = get_user_meta( $user_id );
    }

    if ( isset( $user_meta_data[ $notification_name ] ) && $user_meta_data[ $notification_name ][0] == true ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Get base user
 *
 * @param bool $id_only
 *
 * @return array|false|\WP_Error|\WP_User
 */
function dt_get_base_user( $id_only = false ) {
    return Disciple_Tools_Users::get_base_user( $id_only );
}

