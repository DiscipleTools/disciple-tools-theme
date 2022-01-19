<?php

class CapabilitiesTest extends WP_UnitTestCase {
    public function test_capabilities_setup() {
        $factory = Disciple_Tools_Capability_Factory::get_instance();
        $capabilities = $factory->get_capabilities();
        $slugs = dt_multi_role_get_capabilities();

        foreach ( $slugs as $slug ) {
            $this->assertArrayHasKey( $slug, $capabilities );
        }

        foreach ( $capabilities as $capability ) {
            $this->assertInstanceOf( Disciple_Tools_Capability::class, $capability );
        }
    }

    public function test_filter_capabilities() {
        $slugs = [ 'update_any_groups', 'unfiltered_upload' ];
        $factory = Disciple_Tools_Capability_Factory::get_instance();
        $capabilities = $factory->get_capabilities( $slugs );

        $this->assertEquals( 2, count( $capabilities ) );

        foreach ( $slugs as $slug ) {
            $this->assertArrayHasKey( $slug, $capabilities );
        }
    }
}

