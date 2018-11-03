<?php

class Disciple_Tools_Migration_0014 extends Disciple_Tools_Migration {
    public function up() {
//        get all miltestones grouped by contact
        global $wpdb;
        $values = $wpdb->get_results("
            SELECT * 
            FROM $wpdb->postmeta 
            WHERE meta_key LIKE 'milestone_%'
            AND meta_value = 'yes'
        ", ARRAY_A);
        $sql = "INSERT INTO $wpdb->postmeta(post_id, meta_key, meta_value) VALUES ";
        foreach ( $values as $value ){
            $sql .= "('" . $value['post_id'] . "', 'milestones', '" .  $value["meta_key"]  . "'),";
        }
        $sql .= ";";
        $sql = str_replace( ",;", ";", $sql );

        $wpdb->query( $sql ); // @phpcs:ignore

        $wpdb->query( "
            DELETE FROM $wpdb->postmeta 
            WHERE `meta_key` LIKE 'milestone_%'
            AND `meta_key` != 'milestones'
        " );


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
