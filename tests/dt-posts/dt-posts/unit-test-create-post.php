<?php
require_once( get_template_directory() . '/tests/dt-posts/tests-setup.php' );
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

    public function test_create_on_custom_fields(){
        $user_id = wp_create_user( "dispatcher3", "test", "test3@example.com" );
        wp_set_current_user( $user_id );
        $create_values = dt_test_get_sample_record_fields();
        $result = DT_Posts::create_post( "contacts", $create_values, true, false );
        $this->assertNotWPError( $result );

        //setting values on each field type
        $this->assertSame( $result["title"], $create_values['title'] );
        $this->assertSame( $result["number_test"], $create_values['number_test'] );
        $this->assertSame( $result["number_test_private"], $create_values['number_test_private'] );
        $this->assertSame( $result["text_test"], $create_values['text_test'] );
        $this->assertSame( $result["text_test_private"], $create_values['text_test_private'] );
        $this->assertSame( $result["contact_communication_channel_test"][0]["value"], $create_values['contact_communication_channel_test']["values"][0]["value"] );
        $this->assertSame( $result["user_select_test"]["id"], $create_values['user_select_test'] );
        $this->assertSame( $result["array_test"], $create_values['array_test'] );
        $this->assertSame( (int) $result["location_test"][0]["id"], (int) $create_values['location_test']["values"][0]["value"] ); //@todo returned value should be an int
        $this->assertSame( $result["date_test"]["timestamp"], strtotime( $create_values['date_test'] ) );
        $this->assertSame( $result["date_test_private"]["timestamp"], strtotime( $create_values['date_test_private'] ) );
        $this->assertSame( $result["boolean_test"], $create_values['boolean_test'] );
        $this->assertSame( $result["boolean_test_private"], $create_values['boolean_test_private'] );
        $this->assertSame( $result["multi_select_test"][0], $create_values['multi_select_test']["values"][0]["value"] );
        $this->assertSame( $result["multi_select_test"][1], $create_values['multi_select_test']["values"][1]["value"] );
        $this->assertSame( $result["multi_select_test_private"][0], $create_values['multi_select_test_private']["values"][0]["value"] );
        $this->assertSame( $result["multi_select_test_private"][1], $create_values['multi_select_test_private']["values"][1]["value"] );
        $this->assertSame( $result["key_select_test"]["key"], $create_values['key_select_test'] );
        $this->assertSame( $result["key_select_test_private"]["key"], $create_values['key_select_test_private'] );
        $this->assertSame( $result["tags_test"][0], $create_values['tags_test']["values"][0]["value"] );
        $this->assertSame( $result["tags_test_private"][0], $create_values['tags_test_private']["values"][0]["value"] );
    }
}
