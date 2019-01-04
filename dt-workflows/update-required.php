<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Update_Needed
 */
class Disciple_Tools_Update_Needed {

    public function __construct() {
//        @todo set the cron on plugin activation
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
            foreach ( $update_needed_settings["options"] as $setting ) {
                $date                 = time() - $setting["days"] * 24 * 60 * 60; // X days in seconds
                $contacts_need_update = $wpdb->get_results( $wpdb->prepare( "
                    SELECT SQL_CALC_FOUND_ROWS  $wpdb->posts.ID
                    FROM $wpdb->posts
                    LEFT JOIN $wpdb->postmeta AS mt1 ON ($wpdb->posts.ID = mt1.post_id AND mt1.meta_key = 'requires_update' )
                    LEFT JOIN $wpdb->postmeta AS mt2 ON ( $wpdb->posts.ID = mt2.post_id )
                    LEFT JOIN $wpdb->postmeta AS mt3 ON ( $wpdb->posts.ID = mt3.post_id )
                    LEFT JOIN $wpdb->postmeta AS mt4 ON ( $wpdb->posts.ID = mt4.post_id )
                    LEFT JOIN $wpdb->postmeta AS contact_type ON ( $wpdb->posts.ID = contact_type.post_id AND contact_type.meta_key = 'type' )
                    WHERE ( mt1.meta_value = '' OR mt1.meta_value = '0' OR mt1.meta_key IS NULL )
                    AND ( mt2.meta_key = 'overall_status' AND mt2.meta_value = %s )
                    AND ( mt3.meta_key = 'last_modified' AND mt3.meta_value <= %d )
                    AND ( mt4.meta_key = 'seeker_path' AND mt4.meta_value = %s )
                    AND ( contact_type.meta_value = 'media' OR contact_type.meta_value = 'next_gen' OR contact_type.meta_key IS NULL )
                    AND $wpdb->posts.post_type = 'contacts' AND $wpdb->posts.post_status = 'publish'
                    GROUP BY $wpdb->posts.ID ORDER BY $wpdb->posts.post_date DESC LIMIT 0, 50",
                    esc_sql( $setting["status"] ),
                    $date,
                    esc_sql( $setting["seeker_path"] )
                ), OBJECT );
                foreach ( $contacts_need_update as $contact ) {
                    $user_name    = ( "@" . dt_get_assigned_name( $contact->ID, true ) . " " ) ?? "";
                    $comment_html = esc_html( $user_name . $setting["comment"] );
                    Disciple_Tools_Contacts::add_comment( $contact->ID, $comment_html, "comment", [ "user_id" => 0 ], false, true );
                    Disciple_Tools_contacts::update_contact( $contact->ID, [ "requires_update" => true ], false );
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
            $current_user->add_cap( "view_any_groups" );
            $current_user->add_cap( "update_any_groups" );

            foreach ( $group_update_needed_settings["options"] as $setting ) {
                $date                 = time() - $setting["days"] * 24 * 60 * 60; // X days in seconds
                $groups_need_update = $wpdb->get_results( $wpdb->prepare( "
                    SELECT SQL_CALC_FOUND_ROWS  $wpdb->posts.ID
                    FROM $wpdb->posts
                    LEFT JOIN $wpdb->postmeta AS mt1 ON ($wpdb->posts.ID = mt1.post_id AND mt1.meta_key = 'requires_update' )
                    LEFT JOIN $wpdb->postmeta AS mt2 ON ( $wpdb->posts.ID = mt2.post_id )
                    LEFT JOIN $wpdb->postmeta AS mt3 ON ( $wpdb->posts.ID = mt3.post_id )
                    WHERE ( mt1.meta_value = '' OR mt1.meta_value = '0' OR mt1.meta_value IS NULL )
                    AND ( mt2.meta_key = 'group_status' AND mt2.meta_value = %s )
                    AND ( mt3.meta_key = 'last_modified' AND mt3.meta_value <= %d )
                    AND $wpdb->posts.post_type = 'groups' AND $wpdb->posts.post_status = 'publish'
                    GROUP BY $wpdb->posts.ID ORDER BY $wpdb->posts.post_date DESC LIMIT 0, 50",
                    esc_sql( $setting["status"] ),
                    $date
                ), OBJECT );
                foreach ( $groups_need_update as $group ) {
                    $user_name    = ( "@" . dt_get_assigned_name( $group->ID, true ) . " " ) ?? "";
                    $comment_html = esc_html( $user_name . $setting["comment"] );
                    Disciple_Tools_Groups::add_comment( $group->ID, $comment_html, "updated_needed", [
                        "user_id" => 0,
                        "comment_author" => __( "Updated Needed", 'disciple_tools' )
                    ], false, true );
                    Disciple_Tools_Groups::update_group( $group->ID, [ "requires_update" => true ], false );
                }
            }
        }
    }
}
