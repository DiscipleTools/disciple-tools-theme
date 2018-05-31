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
            wp_schedule_event( time( 'today midnight' ), 'daily', 'update-required' );
        }
        add_action( 'update-required', [ &$this, 'find_contacts_that_need_an_update' ] );
    }

    public static function find_contacts_that_need_an_update() {


        global $wpdb;
        $update_needed_settings = [
            [ "status" => "active", "seeker_path" => "none", "days" => 30 ],
            [ "status" => "active", "seeker_path" => "attempted", "days" => 30 ],
            [ "status" => "active", "seeker_path" => "established", "days" => 30 ],
            [ "status" => "active", "seeker_path" => "scheduled", "days" => 30 ],
            [ "status" => "active", "seeker_path" => "met", "days" => 30 ],
            [ "status" => "active", "seeker_path" => "ongoing", "days" => 30 ],
            [ "status" => "active", "seeker_path" => "coaching", "days" => 30 ],
        ];
        foreach ( $update_needed_settings as $setting ){
            $date = time() - $setting["days"] * 24 * 60 * 60; // X days in seconds
            $contacts_need_update = $wpdb->get_results( $wpdb->prepare( "
                SELECT SQL_CALC_FOUND_ROWS  $wpdb->posts.ID
                FROM $wpdb->posts
                LEFT JOIN $wpdb->postmeta AS mt1 ON ($wpdb->posts.ID = mt1.post_id AND mt1.meta_key = 'requires_update' )
                LEFT JOIN $wpdb->postmeta AS mt2 ON ( $wpdb->posts.ID = mt2.post_id )
                LEFT JOIN $wpdb->postmeta AS mt3 ON ( $wpdb->posts.ID = mt3.post_id )
                LEFT JOIN $wpdb->postmeta AS mt4 ON ( $wpdb->posts.ID = mt4.post_id )
                LEFT JOIN $wpdb->postmeta AS contact_type ON ( $wpdb->posts.ID = contact_type.post_id AND contact_type.meta_key = 'type' )
                WHERE ( mt1.meta_value = 'no' OR mt1.meta_key IS NULL )
                AND ( mt2.meta_key = 'overall_status' AND mt2.meta_value = %s )
                AND ( mt3.meta_key = 'last_modified' AND mt3.meta_value <= %s )
                AND ( mt4.meta_key = 'seeker_path' AND mt4.meta_value = %s )
                AND ( contact_type.meta_value = 'media' OR contact_type.meta_value = 'next_gen' OR contact_type.meta_key IS NULL )
                AND $wpdb->posts.post_type = 'contacts' AND $wpdb->posts.post_status = 'publish'
                GROUP BY $wpdb->posts.ID ORDER BY $wpdb->posts.post_date DESC LIMIT 0, 50",
                esc_sql( $setting["status"] ),
                $date,
                esc_sql( $setting["seeker_path"] )
            ), OBJECT );
            foreach ( $contacts_need_update as $contact ) {
                update_post_meta( $contact->ID, "requires_update", "yes" );
            }
        }
    }
}
