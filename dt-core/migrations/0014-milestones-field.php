<?php

if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Migration_0014 extends Disciple_Tools_Migration {
    public function up() {
//        get all miltestones grouped by contact
        global $wpdb;
        $milestones = $wpdb->get_results("
            SELECT *
            FROM $wpdb->postmeta
            WHERE meta_key LIKE 'milestone_%'
            AND `meta_key` != 'milestones'
            AND meta_value = 'yes'
        ", ARRAY_A);
        if ( sizeof( $milestones ) > 0 ){
            $sql = "INSERT INTO $wpdb->postmeta(post_id, meta_key, meta_value) VALUES ";
            foreach ( $milestones as $value ){
                $sql .= "('" . $value['post_id'] . "', 'milestones', '" .  $value["meta_key"]  . "'),";
            }
            $sql .= ";";
            $sql = str_replace( ",;", ";", $sql );

            $wpdb->query( $sql ); // @phpcs:ignore
        }
        $wpdb->query( "
            DELETE FROM $wpdb->postmeta
            WHERE `meta_key` IN ( 'milestone_has_bible', 'milestone_reading_bible', 'milestone_belief', 'milestone_can_share', 'milestone_sharing', 'milestone_baptized', 'milestone_baptizing', 'milestone_in_group', 'milestone_planting' )
        " );

        $health_metrics = $wpdb->get_results("
            SELECT *
            FROM $wpdb->postmeta
            WHERE meta_key IN ( 'church_baptism', 'church_bible', 'church_communion', 'church_fellowship', 'church_giving', 'church_prayer', 'church_praise', 'church_sharing', 'church_leaders', 'church_commitment')
            AND meta_value = '1'
        ", ARRAY_A);
        if ( sizeof( $health_metrics ) > 0 ){
            $sql = "INSERT INTO $wpdb->postmeta(post_id, meta_key, meta_value) VALUES ";
            foreach ( $health_metrics as $value ){
                $sql .= "('" . $value['post_id'] . "', 'health_metrics', '" .  $value["meta_key"]  . "'),";
            }
            $sql .= ";";
            $sql = str_replace( ",;", ";", $sql );

            $wpdb->query( $sql ); // @phpcs:ignore
        }
        $wpdb->query( "
            DELETE FROM $wpdb->postmeta
            WHERE `meta_key` IN ( 'church_baptism', 'church_bible', 'church_communion', 'church_fellowship', 'church_giving', 'church_prayer', 'church_praise', 'church_sharing', 'church_leaders', 'church_commitment')
            AND ( `meta_value` = '1' OR `meta_value` = '0' )
        " );


        $contact_fields = DT_Posts::get_post_field_settings( 'contacts' );
        $custom_field_options = dt_get_option( "dt_field_customizations" );
        $custom_lists = dt_get_option( "dt_site_custom_lists" );
        $custom_milestones = $custom_lists["custom_milestones"] ?? [];
        foreach ( $custom_milestones as $k => $v ){
            if ( ! isset( $custom_field_options["contacts"]["milestones"] ) ) {
                $custom_field_options["contacts"]["milestones"] = [
                    "default" => []
                ];
            }
            if ( !isset( $contact_fields["milestones"]["default"][$k] ) &&
                 !isset( $custom_field_options["contacts"]["milestones"]["default"][$k] ) ){
                $custom_field_options["contacts"]["milestones"]["default"][$k] = [
                    "label" => $v["name"]
                ];
            }
        }

        $group_fields = DT_Posts::get_post_field_settings( "groups" );
        $custom_church = $custom_lists["custom_church"] ?? [];
        foreach ( $custom_church as $k => $v ){
            if ( ! isset( $custom_field_options["groups"]["health_metrics"] ) ) {
                $custom_field_options["groups"]["health_metrics"] = [
                    "default" => []
                ];
            }
            if ( !isset( $group_fields["health_metrics"]["default"][$k] ) &&
                 !isset( $custom_field_options["groups"]["health_metrics"]["default"][$k] ) ){
                $custom_field_options["groups"]["health_metrics"]["default"][$k] = [
                    "label" => $v["name"]
                ];
            }
        }

        update_option( "dt_field_customizations", $custom_field_options );
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
