<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Migration_0034 extends Disciple_Tools_Migration {
    public function up() {
        /**
         * Make sure contacts and groups are shared with the user assigned to them.
         * Make sure contacts are shared with the subassigned users.
         * Make sure contacts are shared with the coaches users.
         */
        global $wpdb;
        $wpdb->query("
            INSERT INTO $wpdb->dt_share (user_id, post_id )
            SELECT REPLACE( pm.meta_value, 'user-', ''), pm.post_id
            FROM $wpdb->postmeta pm
            WHERE pm.meta_key = 'assigned_to'
            AND pm.post_id NOT IN ( SELECT post_id FROM $wpdb->dt_share WHERE post_id = pm.post_id AND user_id = REPLACE( pm.meta_value, 'user-', '') )
        ");

        //subassigned
        $wpdb->query("
            INSERT INTO $wpdb->dt_share (user_id, post_id )
            SELECT pm.meta_value, sub.p2p_to
            FROM $wpdb->p2p sub
            INNER JOIN $wpdb->postmeta as pm ON ( pm.post_id = sub.p2p_from AND pm.meta_key = 'corresponds_to_user' )
            WHERE sub.p2p_type = 'contacts_to_subassigned'
            AND sub.p2p_to NOT IN ( SELECT post_id FROM $wpdb->dt_share WHERE post_id = sub.p2p_to AND user_id = pm.meta_value )
        ");

        //coaching
        $wpdb->query("
            INSERT INTO $wpdb->dt_share (user_id, post_id )
            SELECT pm.meta_value, sub.p2p_to
            FROM $wpdb->p2p sub
            INNER JOIN $wpdb->postmeta as pm ON ( pm.post_id = sub.p2p_from AND pm.meta_key = 'corresponds_to_user' )
            WHERE sub.p2p_type = 'contacts_to_contacts'
            AND sub.p2p_to NOT IN ( SELECT post_id FROM $wpdb->dt_share WHERE post_id = sub.p2p_to AND user_id = pm.meta_value )
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
