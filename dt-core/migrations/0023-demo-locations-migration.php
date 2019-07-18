<?php

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
            "barcelona" => 3336901,
            "madrid" => 3117732,
            "castilla-y-leon" => 3336900,
            "aragon" => 3336899,
            "castilla-la-mancha" => 2593111,
            "andalusia" => 2593109,
            "extremadura" => 2593112,
            "gibraltar" => 6255148,
            "portugal" => 6255148,
            "morocco" => 2542007,
            "new-york-new-york" => 5128638,
            "united-states" => 6252001
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
