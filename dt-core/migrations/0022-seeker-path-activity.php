<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
/**
 * find and the missed seeker path activity and create records for them.
 *
 */
class Disciple_Tools_Migration_0022 extends Disciple_Tools_Migration
{
    public function up() {
        global $wpdb;
        $field_settings = DT_Posts::get_post_field_settings( "contacts" );
        $seeker_path_options = $field_settings["seeker_path"]["default"];
        $option_keys = array_keys( $seeker_path_options );

        foreach ( array_reverse( $seeker_path_options ) as $option_key => $option_value ){
            $current_index = array_search( $option_key, $option_keys );
            if ( $current_index != 0 ){
                $prev_key = $option_keys[ $current_index - 1 ];
                $res = $wpdb->get_results( $wpdb->prepare("
                    SELECT log.object_id, log.hist_time, log.user_id, log.meta_id
                    FROM $wpdb->dt_activity_log log
                    WHERE log.meta_key = 'seeker_path'
                    AND log.meta_value = %s
                    AND log.object_id NOT IN (
                        SELECT object_id
                        FROM $wpdb->dt_activity_log
                        WHERE meta_key = 'seeker_path'
                        AND meta_value = %s
                    )
                ", $option_key, $prev_key), ARRAY_A
                );
                if ( sizeof( $res ) > 0 ){
                    $query = " INSERT INTO $wpdb->dt_activity_log
                        ( action, object_type, object_subtype, object_id, user_id, hist_time, meta_id, meta_key, meta_value, field_type )
                        VALUES ";
                    foreach ( $res as $r ){
                        $query .= $wpdb->prepare( "( 'field_update', 'contacts', 'seeker_path', %s, %d, %d, %d, 'seeker_path', %s, 'key_select' ), ", $r["object_id"], $r["user_id"], $r["hist_time"] - 1, $r["meta_id"], $prev_key );
                    }
                    $query .= ';';
                    $query = str_replace( ", ;", ";", $query ); //remove last comma
                    $wpdb->query( $query ); //phpcs:ignore
                }
            }
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
