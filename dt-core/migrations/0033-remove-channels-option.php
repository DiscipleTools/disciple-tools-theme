<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Migration_0033 extends Disciple_Tools_Migration {
    public function up() {
        /**
         * Remove contact channels that are now saved with the fields settings
         */

        $custom_channels = get_option( "dt_custom_channels" );
        if ( $custom_channels ){
            DT_Posts::get_post_field_settings( "contacts" );
            $custom_field_options = dt_get_option( "dt_field_customizations" );
            foreach ( $custom_channels as $custom_key => $custom_value ){
                if ( !isset( $custom_field_options["contacts"]["contact_" . $custom_key] ) ){
                    $custom_field_options["contacts"]["contact_" . $custom_key] = $custom_value;
                    if ( isset( $custom_field_options["contacts"]["contact_" . $custom_key]["label"] ) ){
                        $custom_field_options["contacts"]["contact_" . $custom_key]["name"] = $custom_field_options["contacts"]["contact_" . $custom_key]["label"];
                        unset( $custom_field_options["contacts"]["contact_" . $custom_key]["label"] );
                    }
                } else {
                    $custom_field_options["contacts"]["contact_" . $custom_key] = array_merge( $custom_field_options["contacts"]["contact_" . $custom_key], $custom_value );
                }
                $custom_field_options["contacts"]["contact_" . $custom_key]["type"] = "communication_channel";
            }
            delete_option( "dt_custom_channels" );
            update_option( "dt_field_customizations", $custom_field_options );
        }
    }

    public function down() {
    }

    public function test() {
    }


    public function get_expected_tables(): array {
        return [];
    }
}
