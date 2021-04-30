<?php

/**
 * @testdox DT_Posts::create_post
 */
class DT_Posts_DT_Posts_Create_Post extends WP_UnitTestCase {

    public $sample_contact = [
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
        "tags" => [ "values" => [ [ "value" => "tag1" ] ] ],
    ];

    public $sample_group = [
        'name' => 'Bob\'s group',
        'group_type' => 'church',
        "location_grid" => [ "values" => [ [ "value" => '100089589' ] ] ],
        "member_count" => 5
    ];

    public static function setupBeforeClass() {
        $user_id = wp_create_user( "dispatcher1", "test", "test2@example.com" );
        wp_set_current_user( $user_id );
        $current_user = wp_get_current_user();
        $current_user->set_role( 'dispatcher' );
    }

    /**
     * @testdox Expected fields
     */
    public function test_expected_fields() {
        $group1 = DT_Posts::create_post( "groups", $this->sample_group );
        $this->sample_contact["groups"] = [ "values" => [ [ "value" => $group1["ID"] ] ] ];
        $contact1 = DT_Posts::create_post( 'contacts', $this->sample_contact );
        $this->assertSame( 'Bob', $contact1['title'] );
        $this->assertSame( 'Bob', $contact1['name'] );
        $this->assertSame( 'Bob the builder', $contact1['nickname'] );
        $this->assertSame( 'France', $contact1['location_grid'][0]["label"] );
        $this->assertSame( (int) '1546214400', (int) $contact1["baptism_date"]["timestamp"] );
        $this->assertSame( "798456780", $contact1['contact_phone'][0]["value"] );
        $this->assertSame( "1", $contact1['assigned_to']["id"] );
        $this->assertSame( "Bob's group", $contact1['groups'][0]["post_title"] );
        $this->assertSame( "tag1", $contact1['tags'][0] );
    }
}
