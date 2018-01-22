<?php

class Disciple_Tools_Migration_0003 extends Disciple_Tools_Migration {
    public function up() {
        $groups_instance = new Disciple_Tools_Groups();
        $groups = $groups_instance::get_viewable_groups();
        foreach ( $groups as $group ){
            $meta_fields = get_post_custom( $group->ID );
            foreach ( $meta_fields as $meta_key => $meta_value ) {
                if ( $meta_key == 'group_status' ) {
                    if ( $meta_value[0] == "active_pre_group" ||
                         $meta_value[0] == "active_group" ||
                         $meta_value[0] == "active_church" ){
                        $new_status = "active";
                        update_post_meta( $group->ID, "group_status", $new_status );
                    }
                    if ( $meta_value[0] == "no_value" ||
                         $meta_value[0] == "inactive_pre_group" ||
                         $meta_value[0] == "inactive_group" ||
                         $meta_value[0] == "inactive_church" ){
                        $new_status = "inactive";
                        update_post_meta( $group->ID, "group_status", $new_status );
                    }
                    if ( $meta_value[0] == "active_group" ||
                         $meta_value[0] == "inactive_group" ){
                        $type = "group";
                        update_post_meta( $group->ID, "group_type", $type );
                    }
                    if ( $meta_value[0] == "active_pre_group" ||
                         $meta_value[0] == "inactive_pre_group" ||
                         $meta_value == "no_value" ){
                        $type = "pre-group";
                        update_post_meta( $group->ID, "group_type", $type );
                    }
                    if ( $meta_value[0] == "active_church" ||
                         $meta_value[0] == "inactive_church" ){
                        $type = "church";
                        update_post_meta( $group->ID, "group_type", $type );
                    }
                }
            }
        }
    }

    public function down() {
        //not required.
    }

    public function test() {
        $this->test_expected_tables();
    }


    public function get_expected_tables(): array {
        //no db alteration
        return array();
    }
}
