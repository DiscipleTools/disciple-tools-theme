<?php

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Update_Needed {

    public function __construct() {
//        @todo set the cron on plugin activation
//        https://codex.wordpress.org/Function_Reference/wp_schedule_event
        if ( !wp_next_scheduled( 'update-required' )){
            wp_schedule_event( time( 'today midnight' ) , 'daily', 'update-required' );
        }
        add_action( 'update-required', [ &$this, 'find_contacts_that_need_an_update' ] );
    }

    public static function find_contacts_that_need_an_update(){

        $month_ago = time() - 30 * 24 * 60 * 60; // 30 days in seconds
        $args = [
            'post_type'  => 'contacts',
            'relation'   => 'AND',
            'meta_query' => [
                [ 'key' => "overall_status", "value" => "active" ],
                [ 'key' => "last_modified", "value" => $month_ago,  "compare" => '<='],
                [
                    'relation' => "OR",
                    [ 'key' => "requires_update", "value" => 'no'],
                    [ 'key' => "requires_update", "compare" => 'NOT EXISTS']
                ]
            ],
        ];
        $contacts = new WP_Query( $args );
        foreach ($contacts->posts as $contact){
            update_post_meta( $contact->ID, "requires_update", "yes" );
        }
    }
}
