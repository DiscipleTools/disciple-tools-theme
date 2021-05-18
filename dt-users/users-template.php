<?php
/**
 * Presenter template for theme support
 *
 * @package  Disciple.Tools
 * @category Plugin
 * @author   Disciple.Tools
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
function dt_get_user_associations() {

    // Set variables
    global $wpdb;
    $user_connections = [];

    // Set constructor
    $user_connections['relation'] = 'OR';

    // Get current user ID and build meta_key for current user
    $user_id = get_current_user_id();
    $user_key_value = 'user-' . $user_id;
    $user_connections[] = [
    'key' => 'assigned_to',
    'value' => $user_key_value
    ];

    // Build arrays for current groups connected to user
    $results = $wpdb->get_results( $wpdb->prepare( "SELECT
            `$wpdb->term_relationships`.`term_taxonomy_id`
        FROM
            `$wpdb->term_relationships`
        WHERE
            `object_id` = %d ",
    $user_id ), ARRAY_A );

    foreach ( $results as $result ) {
        $user_connections[] = [
        'key' => 'assigned_to',
        'value' => 'group-' . $result['term_taxonomy_id']
        ];
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
function dt_get_team_contacts( $user_id ) {
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
        $user_connections[] = [
        'key' => 'assigned_to',
        'value' => 'group-' . $result['term_taxonomy_id']
        ];

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
        $user_connections[] = [
        'key' => 'assigned_to',
        'value' => 'user-' . $member
        ];
    }

    // return
    return $user_connections;
}

/**
 * Gets the current site defaults defined in the notifications config section in wp-admin
 *
 * @return array
 */
function dt_get_site_notification_defaults(){
    $site_options = dt_get_option( 'dt_site_options' );
    $default_notifications = dt_get_site_options_defaults()["notifications"];
    //get translated value
    foreach ( $site_options["notifications"]['types'] as $notification_key => &$value ){
        if ( isset( $default_notifications['types'][$notification_key]["label"] ) ){
            $value["label"] = $default_notifications["types"][$notification_key]["label"];
        }
    }
    foreach ( $site_options["notifications"]["channels"] as $channel_key => &$channel ){
        if ( isset( $default_notifications['channels'][$channel_key]["label"] ) ){
            $channel["label"] = $default_notifications["channels"][$channel_key]["label"];
        }
    }
    $notifications = apply_filters( "dt_get_site_notification_options", $site_options["notifications"] );

    return $notifications;
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
 * Returns the corresponding id for either user or contact.
 *
 * @param        $id
 * @param string $id_type
 *
 * @return bool|mixed
 */
function dt_get_associated_user_id( $id, $id_type = 'user' ) {
    if ( $id_type === 'user' ) {
        return get_user_option( "corresponds_to_contact", $id );
    } else if ( $id_type === 'contact' ) {
        return get_post_meta( $id, 'corresponds_to_user', true );
    } else {
        return false;
    }
}

/**
 * Echos user display name
 *
 * @param int $user_id
 */
function dt_user_display_name( int $user_id ) {
    echo esc_html( dt_get_user_display_name( $user_id ) );
}

/**
 * Returns user display name
 *
 * @param $user_id
 *
 * @return string|WP_Error
 */
function dt_get_user_display_name( $user_id ) {
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

function dt_get_user_id_from_assigned_to( $user_meta ){
    $meta_array = explode( '-', $user_meta ); // Separate the type and id
    if ( isset( $meta_array[1] ) ) {
        return (int) $meta_array[1];
    }
    return '';
}


/**
 * @param $profile_fields
 *
 * @return mixed
 */
function dt_modify_profile_fields( $profile_fields ) {

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

    $default_user_fields = dt_get_site_custom_lists()["user_fields"];
    $site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
    if ( ! $site_custom_lists ) {
        return [];
    }
    $site_user_fields = $site_custom_lists['user_fields'];

    foreach ( $site_user_fields as $key => $value ) {
        if ( $value['enabled'] ) { // if the site field is enabled
            $i = 0;
            if ( isset( $default_user_fields[$key]["label"] ) ){
                $value["label"] = $default_user_fields[$key]["label"];
            }

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
 * Tests if a user notification is enabled.
 *
 *
 * @param string   $notification_name
 * @param string   $channel
 * @param array|null $user_meta_data
 * @param int|null $user_id
 *
 * @return bool
 */
function dt_user_notification_is_enabled( string $notification_name, string $channel, array $user_meta_data = null, int $user_id = null ): bool
{
    if ( empty( $user_id ) ) {
        $user_id = get_current_user_id();
    }

    // Check status of site defined defaults
    $notification_settings = dt_get_site_notification_defaults();
    // This checks to see if the site has required this notification to be true. If true, then personal preference is not checked.
    if ( isset( $notification_settings["types"][ $notification_name ][ $channel ] ) && $notification_settings["types"][ $notification_name ][ $channel ] ) {
        return true;
    }

    if ( empty( $user_meta_data ) ) {
        $user_meta_data = get_user_meta( $user_id );
    }

    //by default a notification is enabled unless set to false
    return isset( $user_meta_data[ $notification_name . '_' . $channel ] ) ? $user_meta_data[ $notification_name . '_' . $channel ][0] == true : true;

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

