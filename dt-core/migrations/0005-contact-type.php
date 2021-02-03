<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Migration_0005 extends Disciple_Tools_Migration {
    public function up() {
        $query_args = [
            'post_type' => 'contacts',
            'nopaging'  => true,
            'meta_query' => [
                [
                    'key' => 'is_a_user',
                    'value' => "yes",
                    'compare' => '='
                ],
            ],
        ];
        $queried_contacts = new WP_Query( $query_args );
        foreach ( $queried_contacts->posts as $user_contact ){
            update_post_meta( $user_contact->ID, "type", "user" );
        }

    }

    public function down() {
        $query_args       = [
            'post_type'  => 'contacts',
            'nopaging'   => true,
            'meta_query' => [
                [
                    'key'     => 'type',
                    'value'   => "user",
                    'compare' => '='
                ],
            ],
        ];
        $queried_contacts = new WP_Query( $query_args );
        foreach ( $queried_contacts->posts as $user_contact ) {
            update_post_meta( $user_contact->ID, "is_a_user", "yes" );
        }
    }

    public function test() {
        $this->test_expected_tables();
    }


    public function get_expected_tables(): array {
        //no db alteration
        return array();
    }
}
