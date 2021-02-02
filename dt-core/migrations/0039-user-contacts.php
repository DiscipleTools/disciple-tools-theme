<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Migration_0039 extends Disciple_Tools_Migration {
    public function up() {
        global $wpdb;

        //unshared the user-contact with the user it corresponds to.
        //these contacts were previously hidden.
        $wpdb->query( "
            DELETE s FROM $wpdb->dt_share as s
            INNER JOIN $wpdb->postmeta as pm ON  ( s.post_id = pm.post_id AND pm.meta_key = 'corresponds_to_user' )
            WHERE pm.meta_value = s.user_id"
        );

        // make sure the user-contact is shared with the user who created it.
        $wpdb->query("
            INSERT INTO $wpdb->dt_share (user_id, post_id )
            SELECT p.post_author, p.ID
            FROM $wpdb->posts p
            INNER JOIN $wpdb->postmeta as pm ON ( pm.post_id = p.ID AND pm.meta_key = 'corresponds_to_user' )
        ");

    }

    public function down() {
    }

    public function test() {
    }

    public function get_expected_tables(): array {
        return [];
    }
}
