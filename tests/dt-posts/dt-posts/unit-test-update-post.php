<?php

/**
 * @testdox DT_Posts::update_post
 */
class DT_Posts_DT_Posts_Update_Post extends WP_UnitTestCase {

    public static $sample_contact = [
        'title' => 'Bob',
        'overall_status' => 'active',
        'milestones' => [ "values" => [ [ "value" => 'milestone_has_bible' ], [ "value" => "milestone_baptizing" ] ] ],
        'baptism_date' => "2018-12-31",
        "location_grid" => [ "values" => [ [ "value" => '100089589' ] ] ],
        "assigned_to" => "1",
        "requires_update" => true,
        "nickname" => "Bob the builder",
        "contact_phone" => [ "values" => [ [ "value" => "798456780" ] ] ],
        "contact_email" => [ "values" => [ [ "value" => "bob@example.com" ] ] ],
        "tags" => [ "values" => [ [ "value" => "tag1" ], [ "value" => "tagToDelete" ] ] ],
    ];
    public static $contact = null;

    public static function setupBeforeClass() {
        $user_id = wp_create_user( "dispatcher1", "test", "test2@example.com" );
        wp_set_current_user( $user_id );
        $current_user = wp_get_current_user();
        $current_user->set_role( 'dispatcher' );

        self::$contact = DT_Posts::create_post( "contacts", self::$sample_contact, true, false );
    }

    /**
     * @testdox Tags: add
     */
    public function test_tags_add() {
        //force values with update
        $initial_count = sizeof( self::$contact['tags'] );
        $result = DT_Posts::update_post( 'contacts', self::$contact["ID"], [
            'tags' => [
                "values" => [
                    [ "value" => "tag2", ],
                    [ "value" => "tag3", ],
                ],
            ], //@phpcs:ignore
        ], true, false );

        $this->assertNotWPError( $result );
        $this->assertContains( "tag2", $result['tags'] );
        $this->assertContains( "tag3", $result['tags'] );
        $this->assertSame( sizeof( $result["tags"] ), $initial_count + 2 );
    }
    /**
     * @testdox Tags: remove
     */
    public function test_tags_remove() {
        //force values with update
        $initial_count = sizeof( self::$contact['tags'] );
        $result = DT_Posts::update_post( 'contacts', self::$contact["ID"], [
            'tags' => [
                "values" => [
                    [ "value" => "tagToDelete", "delete" => true, ],
                ],
            ], //@phpcs:ignore
        ], true, false );

        $this->assertNotWPError( $result );
        $this->assertNotContains( "tagToDelete", $result['tags'] );
        $this->assertSame( sizeof( $result["tags"] ), $initial_count - 1 );
    }
    /**
     * @testdox Tags: force update
     */
    public function test_tags_force() {
        //force values with update
        $result = DT_Posts::update_post( 'contacts', self::$contact["ID"], [
            'tags' => [
                "values" => [
                    [ "value" => "tag98", ],
                    [ "value" => "tag99", ],
                ],
                "force_values" => true,
            ], //@phpcs:ignore
        ], true, false );
        $this->assertNotWPError( $result );

        $this->assertContains( "tag98", $result['tags'] );
        $this->assertContains( "tag99", $result['tags'] );
        $this->assertSame( sizeof( $result["tags"] ), 2 );
    }
}
