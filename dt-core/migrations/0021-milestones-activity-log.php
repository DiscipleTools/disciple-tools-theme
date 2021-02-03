<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Migration_0021 extends Disciple_Tools_Migration
{
    public function up() {
        global $wpdb;
        $wpdb->query(
            "UPDATE $wpdb->dt_activity_log
            SET meta_value = $wpdb->dt_activity_log.meta_key,
                meta_key = 'milestones',
                field_type = 'multi_select'
            WHERE meta_key LIKE 'milestone\_%'
            AND meta_value = 'yes'
            "
        );
        $wpdb->query(
            "UPDATE $wpdb->dt_activity_log
            SET old_value = $wpdb->dt_activity_log.meta_key,
                meta_value = 'value_deleted',
                meta_key = 'milestones',
                field_type = 'multi_select'
            WHERE meta_key LIKE 'milestone\_%'
            AND meta_value = 'no'
            "
        );
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
