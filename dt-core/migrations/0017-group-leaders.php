<?php

if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Migration_0017 extends Disciple_Tools_Migration {
    public function up() {
        //find leaders that are not members of a group and add them.
        global $wpdb;
        $wpdb->query( "
            INSERT INTO $wpdb->p2p (p2p_from, p2p_to, p2p_type)
            SELECT leaders.p2p_to, leaders.p2p_from, 'contacts_to_groups'
            FROM $wpdb->p2p as leaders
            WHERE leaders.p2p_to NOT IN (
                SELECT p2p_from
                FROM $wpdb->p2p members
                WHERE members.p2p_to = leaders.p2p_from
                AND members.p2p_type = 'contacts_to_groups'
            )
            AND leaders.p2p_type = 'groups_to_leaders'
        ");
        //set member counts for all groups
        $wpdb->query("
            INSERT INTO $wpdb->postmeta ( post_id, meta_key, meta_value )
            SELECT posts.ID, 'member_count', (
                SELECT COUNT( p2p.p2p_from ) as member_count
                FROM $wpdb->p2p as p2p
                WHERE p2p.p2p_type = 'contacts_to_groups'
                AND p2p.p2p_to = posts.ID
            )
            FROM $wpdb->posts as posts
            LEFT JOIN $wpdb->postmeta as pm ON ( pm.post_id = posts.ID AND pm.meta_key = 'member_count' )
            WHERE posts.post_type = 'groups'
            AND pm.meta_key IS NULL
        ");
    }

    public function down() {
        return;
    }

    public function test() {
        $this->test_expected_tables();
    }


    public function get_expected_tables(): array {
        return array();
    }
}
