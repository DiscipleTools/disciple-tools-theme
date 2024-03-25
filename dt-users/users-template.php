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


/**
 * Gets the current site defaults defined in the notifications config section in wp-admin
 *
 * @return array
 */
function dt_get_site_notification_defaults(){
    $site_options = dt_get_option( 'dt_site_options' );
    $default_notifications = dt_get_site_options_defaults()['notifications'];
    //get translated value
    foreach ( $site_options['notifications']['types'] as $notification_key => &$value ){
        if ( isset( $default_notifications['types'][$notification_key]['label'] ) ){
            $value['label'] = $default_notifications['types'][$notification_key]['label'];
        }
    }
    foreach ( $site_options['notifications']['channels'] as $channel_key => &$channel ){
        if ( isset( $default_notifications['channels'][$channel_key]['label'] ) ){
            $channel['label'] = $default_notifications['channels'][$channel_key]['label'];
        }
    }
    return apply_filters( 'dt_get_site_notification_options', $site_options['notifications'] );
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

function dt_get_user_mention_syntax( $user_id ){
    $user = get_userdata( $user_id );
    if ( ! $user ) {
        return ''; // return blank if user id does not exist
    }
    return '@[' . $user->display_name . '](' . $user_id . ')';
}

function dt_get_user_id_from_assigned_to( $user_meta ){
    if ( is_numeric( $user_meta ) ) {
        return (int) $user_meta;
    }
    if ( empty( $user_meta ) || !is_string( $user_meta ) ){
        return '';
    }
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

    $default_user_fields = dt_get_site_custom_lists()['user_fields'];
    $site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
    if ( ! $site_custom_lists ) {
        return [];
    }
    $site_user_fields = $site_custom_lists['user_fields'];

    foreach ( $site_user_fields as $key => $value ) {
        if ( $value['enabled'] ) { // if the site field is enabled
            $i = 0;
            if ( isset( $default_user_fields[$key]['label'] ) ){
                $value['label'] = $default_user_fields[$key]['label'];
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
    if ( isset( $notification_settings['types'][ $notification_name ][ $channel ] ) && $notification_settings['types'][ $notification_name ][ $channel ] ) {
        return true;
    }

    if ( empty( $user_meta_data ) ) {
        $user_meta_data = get_user_meta( $user_id );
    }

    $channel_notification_key = $notification_name . '_' . $channel;

    //if user preference is set, then use it
    if ( isset( $user_meta_data[$channel_notification_key] ) ){
        return !empty( $user_meta_data[$channel_notification_key][0] );
    }
    //default to yes if email or web, otherwise no
    return $channel === 'email' || $channel === 'web';
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

