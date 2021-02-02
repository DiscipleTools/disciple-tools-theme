<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * move list filters from usermeta to user options
 *
 */
class Disciple_Tools_Migration_0024 extends Disciple_Tools_Migration
{
    public function up() {
        global $wpdb;

        //get users with saved filters
        //save those filters to options instead
        $users = get_users( [ 'meta_key' => 'saved_filters' ] );
        foreach ( $users as $user ) {
            $filters = get_user_meta( $user->ID, "saved_filters", true );
            update_user_option( $user->ID, "saved_filters", $filters );
            delete_user_meta( $user->ID, "saved_filters" );
        }
    }

    public function down() {
        return;
    }

    public function test() {
    }

    public function get_expected_tables(): array
    {
        return [];
    }
}
