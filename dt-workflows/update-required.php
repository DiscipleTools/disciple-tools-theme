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
        $site_options = dt_get_option( "dt_site_options" );
        $update_needed_settings = $site_options["update_required"];
        if ( $update_needed_settings["enabled"] === true ){
            foreach ( $update_needed_settings["options"] as $setting ){
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
                    Disciple_Tools_contacts::update_contact( $contact->ID, [ "requires_update" => "yes" ], false );
                }
            }
        }
    }
}
