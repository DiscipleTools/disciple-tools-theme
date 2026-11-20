<?php

/**
 * @testdox Disciple_Tools_Mapping_Queries filters
 */
class DT_Mapping_Queries_Filter_Test extends WP_UnitTestCase {

    public function test_invalid_filter_returns_an_error(){
        $query = [ 'baptism_date' => [ 'start' => 'not a date' ] ];

        $this->assertWPError( Disciple_Tools_Mapping_Queries::cluster_geojson( 'contacts', $query ) );
        $this->assertWPError( Disciple_Tools_Mapping_Queries::points_geojson( 'contacts', $query ) );
        $this->assertWPError( Disciple_Tools_Mapping_Queries::query_location_grid_meta_totals( 'contacts', $query ) );
        $this->assertWPError( Disciple_Tools_Mapping_Queries::query_under_location_grid_meta_id( 'contacts', 100089589, $query ) );
    }

    public function test_valid_filter_returns_results(){
        $query = [ 'baptism_date' => [ 'start' => '2000-01-01' ] ];

        $this->assertNotWPError( Disciple_Tools_Mapping_Queries::cluster_geojson( 'contacts', $query ) );
        $this->assertIsArray( Disciple_Tools_Mapping_Queries::query_location_grid_meta_totals( 'contacts', $query ) );
    }
}
