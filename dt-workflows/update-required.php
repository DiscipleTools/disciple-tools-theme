<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Update_Needed
 */
class Disciple_Tools_Update_Needed {

    public function __construct() {
//        https://codex.wordpress.org/Function_Reference/wp_schedule_event
        if ( ! wp_next_scheduled( 'update-required' ) ) {
            wp_schedule_event( strtotime( 'today 1am' ), 'daily', 'update-required' );
        }
        add_action( 'update-required', [ $this, 'find_contacts_that_need_an_update' ] );
    }

    public static function find_contacts_that_need_an_update(){
        do_action( "dt_find_contacts_that_need_an_update" );
    }

}

class Disciple_Tools_Update_Needed_Async extends Disciple_Tools_Async_Task {

    protected $action = 'dt_find_contacts_that_need_an_update';

    protected function prepare_data( $data ) {
        return $data;
    }

    protected function run_action() {
        global $wpdb;
        $site_options           = dt_get_option( "dt_site_options" );
        $update_needed_settings = $site_options["update_required"];
        if ( $update_needed_settings["enabled"] === true ) {
            wp_set_current_user( 0 ); // to keep the update needed notifications from coming from a specific user.
            $current_user = wp_get_current_user();
            $current_user->add_cap( "access_contacts" );
            $current_user->add_cap( "dt_all_access_contacts" );
            foreach ( $update_needed_settings["options"] as $setting ) {
                $date                 = time() - $setting["days"] * 24 * 60 * 60; // X days in seconds
                $contacts_need_update = $wpdb->get_results( $wpdb->prepare( "
                    SELECT $wpdb->posts.ID
                    FROM $wpdb->posts
                    LEFT JOIN $wpdb->postmeta AS requires_update_field ON ($wpdb->posts.ID = requires_update_field.post_id AND requires_update_field.meta_key = 'requires_update' )
                    LEFT JOIN $wpdb->postmeta AS overall_status_field ON ( $wpdb->posts.ID = overall_status_field.post_id AND overall_status_field.meta_key = 'overall_status')
                    LEFT JOIN $wpdb->postmeta AS seeker_path_field ON ( $wpdb->posts.ID = seeker_path_field.post_id AND seeker_path_field.meta_key = 'seeker_path' )
                    INNER JOIN $wpdb->postmeta AS type_field ON ( $wpdb->posts.ID = type_field.meta_key = 'type' AND type_field.meta_value = 'access' )
                    WHERE ( requires_update_field.meta_value = '' OR requires_update_field.meta_value = '0' OR requires_update_field.meta_key IS NULL )
                    AND overall_status_field.meta_value = %s
                    AND seeker_path_field.meta_value = %s
                    AND %d >= ( SELECT MAX( hist_time ) FROM $wpdb->dt_activity_log WHERE object_id = $wpdb->posts.ID and user_id != 0 )
                    AND $wpdb->posts.post_type = 'contacts' AND $wpdb->posts.post_status = 'publish'
                    GROUP BY $wpdb->posts.ID ORDER BY $wpdb->posts.post_date DESC LIMIT 0, 50",
                    esc_sql( $setting["status"] ),
                    esc_sql( $setting["seeker_path"] ),
                    $date
                ), OBJECT );
                foreach ( $contacts_need_update as $contact ) {
                    $user_name    = ( "@" . dt_get_assigned_name( $contact->ID, true ) . " " ) ?? "";
                    $comment_html = esc_html( $user_name . $setting["comment"] );
                    DT_Posts::add_post_comment( "contacts", $contact->ID, $comment_html, "comment", [
                        "user_id" => 0,
                        "comment_author" => __( "Updated Needed", 'disciple_tools' )
                    ], false, true );
                    DT_Posts::update_post( "contacts", $contact->ID, [ "requires_update" => true ], false );
                }
            }
        }

        /**
         * groups
         */
        $group_update_needed_settings = $site_options["group_update_required"];
        if ( $group_update_needed_settings["enabled"] === true ) {
            wp_set_current_user( 0 ); // to keep the update needed notifications from coming from a specific user.
            $current_user = wp_get_current_user();
            $current_user->add_cap( "access_groups" );
            $current_user->add_cap( "view_any_groups" );
            $current_user->add_cap( "update_any_groups" );

            foreach ( $group_update_needed_settings["options"] as $setting ) {
                $date                 = time() - $setting["days"] * 24 * 60 * 60; // X days in seconds
                $groups_need_update = $wpdb->get_results( $wpdb->prepare( "
                    SELECT $wpdb->posts.ID
                    FROM $wpdb->posts
                    LEFT JOIN $wpdb->postmeta AS requires_update_field ON ($wpdb->posts.ID = requires_update_field.post_id AND requires_update_field.meta_key = 'requires_update' )
                    LEFT JOIN $wpdb->postmeta AS group_status_field ON ( $wpdb->posts.ID = group_status_field.post_id AND group_status_field.meta_key = 'group_status' )
                    WHERE ( requires_update_field.meta_value = '' OR requires_update_field.meta_value = '0' OR requires_update_field.meta_value IS NULL )
                    AND group_status_field.meta_value = %s
                    AND %d >= ( SELECT MAX( hist_time ) FROM $wpdb->dt_activity_log WHERE object_id = $wpdb->posts.ID and user_id != 0 )
                    AND $wpdb->posts.post_type = 'groups' AND $wpdb->posts.post_status = 'publish'
                    GROUP BY $wpdb->posts.ID ORDER BY $wpdb->posts.post_date DESC LIMIT 0, 50",
                    esc_sql( $setting["status"] ),
                    $date
                ), OBJECT );
                foreach ( $groups_need_update as $group ) {
                    $user_name    = ( "@" . dt_get_assigned_name( $group->ID, true ) . " " ) ?? "";
                    $comment_html = esc_html( $user_name . $setting["comment"] );
                    DT_Posts::add_post_comment( "groups", $group->ID, $comment_html, "updated_needed", [
                        "user_id" => 0,
                        "comment_author" => __( "Updated Needed", 'disciple_tools' )
                    ], false, true );
                    DT_Posts::update_post( "groups", $group->ID, [ "requires_update" => true ], false );
                }
            }
        }
    }
}
