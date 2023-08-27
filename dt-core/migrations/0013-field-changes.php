<?php

if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Migration_0013 extends Disciple_Tools_Migration {
    public function up() {
        //skip this migration on a new install
        if ( dt_get_initial_install_meta( 'migration_number' ) > 13 ){
            return;
        }

        $contact_fields = DT_Posts::get_post_field_settings( 'contacts' );
        $group_fields = DT_Posts::get_post_field_settings( 'groups' );
        $custom_lists = dt_get_option( 'dt_site_custom_lists' );
        $custom_field_options = dt_get_option( 'dt_field_customizations' );

        foreach ( $custom_lists as $list_key => $list_field ){
            if ( in_array( $list_key, array( 'seeker_path', 'custom_status', 'custom_reason_closed', 'custom_reason_pause', 'custom_reason_unassignable' ) ) ){
                $list_key = str_replace( 'custom_status', 'overall_status', $list_key );
                $key = str_replace( 'custom_', '', $list_key );
                if ( !isset( $custom_field_options['contacts'][$key] ) ){
                    $custom_field_options['contacts'][$key] = array(
                        'default' => array(),
                    );
                }
                foreach ( $list_field as $option_key => $option_value ){
                    if ( !isset( $contact_fields[$key]['default'][$option_key] ) ){
                        $custom_field_options['contacts'][$key]['default'][$option_key] = array( 'label' => $option_value );
                    }
                }
            }
            if ( in_array( $list_key, array( 'custom_church' ) ) ){
                $key = str_replace( 'custom_', '', $list_key );
                if ( !isset( $custom_field_options['groups'][$key] ) ){
                    $custom_field_options['groups'][$key] = array(
                        'default' => array(),
                    );
                }
                foreach ( $list_field as $option_key => $option_value ){
                    if ( !isset( $group_fields[$key]['default'][$option_key] ) ){
                        $custom_field_options['groups'][$key]['default'][$option_key] = array( 'label' => $option_value );
                    }
                }
            }
        }

        $custom_dropdowns = $custom_lists['custom_dropdown_contact_options'] ?? array();
        foreach ( $custom_dropdowns as $k => $v ){
            if ( ! isset( $contact_fields[$k] ) ){
                unset( $v['label'] );
                $default = array();
                foreach ( $v as $option_key => $option_value ){
                    $default[$option_key] = array( 'label' => $option_value );
                }
                $custom_field_options['contacts'][$k] = array(
                    'name'        => $k,
                    'type'        => 'key_select',
                    'default'     => $default,
                    'customizable' => 'all',
                );
            }
        }


        update_option( 'dt_field_customizations', $custom_field_options );
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
