<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * find and the missed seeker path activity and create records for them.
 *
 */
class Disciple_Tools_Migration_0023 extends Disciple_Tools_Migration
{
    public function up() {
        global $wpdb;

        $sample_locations = $wpdb->get_results("
            SELECT * from $wpdb->posts as post
            JOIN $wpdb->postmeta pm ON ( post.ID = pm.post_id AND pm.meta_key = '_sample' AND pm.meta_value = 'prepared' )
            WHERE post.post_type = 'locations'
        ", ARRAY_A );
        $map = [
            "barcelona" => 100074621,
            "madrid" => 100074627,
            "castilla-y-leon" => 100074581,
            "aragon" => 100074578,
            "castilla-la-mancha" => 100074580,
            "andalusia" => 100074577,
            "extremadura" => 100074587,
            "gibraltar" => 100131318,
            "portugal" => 100131318,
            "morocco" => 100241761,
            "new-york-new-york" => 100364232,
            "united-states" => 100130478
        ];

        foreach ( $sample_locations as $location ){
            if ( isset( $map[$location["post_name"] ] ) ){
                DT_Mapping_Module_Admin::instance()->convert_location_to_location_grid( $location["ID"], $map[ $location["post_name"] ] );
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
