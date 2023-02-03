<?php
require_once( get_template_directory() . '/tests/dt-posts/tests-setup.php' );
/**
 * @testdox DT_Posts::create_post
 */
class DT_Posts_DT_Posts_Create_Post extends WP_UnitTestCase {

    public $sample_contact = [
        'title' => 'Bob',
        'overall_status' => 'active',
        'milestones' => [ 'values' => [ [ 'value' => 'milestone_has_bible' ], [ 'value' => 'milestone_baptizing' ] ] ],
        'baptism_date' => '2018-12-31',
        'location_grid' => [ 'values' => [ [ 'value' => '100089589' ] ] ],
        'assigned_to' => '1',
        'requires_update' => true,
        'nickname' => 'Bob the builder',
        'contact_phone' => [ 'values' => [ [ 'value' => '798456780' ] ] ],
        'contact_email' => [ 'values' => [ [ 'value' => 'bob@example.com' ] ] ],
        'tags' => [ 'values' => [ [ 'value' => 'tag1' ] ] ],
    ];

    public $sample_group = [
        'name' => 'Bob\'s group',
        'group_type' => 'church',
        'location_grid' => [ 'values' => [ [ 'value' => '100089589' ] ] ],
        'member_count' => 5
    ];

    public static function setupBeforeClass(): void  {
        $user_id = wp_create_user( 'dispatcher1', 'test', 'test2@example.com' );
        wp_set_current_user( $user_id );
        $current_user = wp_get_current_user();
        $current_user->set_role( 'dispatcher' );
    }

    /**
     * @testdox Expected fields
     */
    public function test_expected_fields() {
        $group1 = DT_Posts::create_post( 'groups', $this->sample_group );
        $this->sample_contact['groups'] = [ 'values' => [ [ 'value' => $group1['ID'] ] ] ];
        $contact1 = DT_Posts::create_post( 'contacts', $this->sample_contact );
        $this->assertSame( 'Bob', $contact1['title'] );
        $this->assertSame( 'Bob', $contact1['name'] );
        $this->assertSame( 'Bob the builder', $contact1['nickname'] );
        $this->assertSame( 'France', $contact1['location_grid'][0]['label'] );
        $this->assertSame( (int) '1546214400', (int) $contact1['baptism_date']['timestamp'] );
        $this->assertSame( '798456780', $contact1['contact_phone'][0]['value'] );
        $this->assertSame( '1', $contact1['assigned_to']['id'] );
        $this->assertSame( "Bob's group", $contact1['groups'][0]['post_title'] );
        $this->assertSame( 'tag1', $contact1['tags'][0] );
    }

    public function test_create_on_custom_fields(){
        $user_id = wp_create_user( 'dispatcher3', 'test', 'test3@example.com' );
        wp_set_current_user( $user_id );
        $current_user = wp_get_current_user();
        $current_user->set_role( 'dispatcher' );
        $create_values = dt_test_get_sample_record_fields();
        $result = DT_Posts::create_post( 'contacts', $create_values, true, false );
        $this->assertNotWPError( $result );

        //setting values on each field type
        $this->assertSame( $result['title'], $create_values['title'] );
        $this->assertSame( $result['number_test'], $create_values['number_test'] );
        $this->assertSame( $result['number_test_private'], $create_values['number_test_private'] );
        $this->assertSame( $result['text_test'], $create_values['text_test'] );
        $this->assertSame( $result['text_test_private'], $create_values['text_test_private'] );
        $this->assertSame( $result['contact_communication_channel_test'][0]['value'], $create_values['contact_communication_channel_test']['values'][0]['value'] );
        $this->assertSame( $result['user_select_test']['id'], $create_values['user_select_test'] );
        $this->assertSame( $result['array_test'], $create_values['array_test'] );
        $this->assertSame( (int) $result['location_test'][0]['id'], (int) $create_values['location_test']['values'][0]['value'] ); //@todo returned value should be an int
        $this->assertSame( $result['date_test']['timestamp'], strtotime( $create_values['date_test'] ) );
        $this->assertSame( $result['date_test_private']['timestamp'], strtotime( $create_values['date_test_private'] ) );
        $this->assertSame( $result['boolean_test'], $create_values['boolean_test'] );
        $this->assertSame( $result['boolean_test_private'], $create_values['boolean_test_private'] );
        $this->assertSame( $result['multi_select_test'][0], $create_values['multi_select_test']['values'][0]['value'] );
        $this->assertSame( $result['multi_select_test'][1], $create_values['multi_select_test']['values'][1]['value'] );
        $this->assertSame( $result['multi_select_test_private'][0], $create_values['multi_select_test_private']['values'][0]['value'] );
        $this->assertSame( $result['multi_select_test_private'][1], $create_values['multi_select_test_private']['values'][1]['value'] );
        $this->assertSame( $result['key_select_test']['key'], $create_values['key_select_test'] );
        $this->assertSame( $result['key_select_test_private']['key'], $create_values['key_select_test_private'] );
        $this->assertSame( $result['tags_test'][0], $create_values['tags_test']['values'][0]['value'] );
        $this->assertSame( $result['tags_test_private'][0], $create_values['tags_test_private']['values'][0]['value'] );
    }

    /**
     * Make sure post is share with the user selected in the user_select field
     */
    public function test_user_select_field(){
        $user_id = wp_create_user( 'multiplier_user_select', 'test', 'multiplier_user_select@example.com' );
        $user = get_user_by( 'ID', $user_id );
        $user->set_role( 'multiplier' );
        $contact_fields = $this->sample_contact;
        $contact_fields['assigned_to'] = $user_id;
        $contact = DT_Posts::create_post( 'contacts', $contact_fields, true, false );
        $group_fields = $this->sample_group;
        $group_fields['assigned_to'] = $user_id;
        $group = DT_Posts::create_post( 'groups', $group_fields, true, false );

        //test contacts create
        $contact_shared_with = DT_Posts::get_shared_with( 'contacts', $contact['ID'], false );
        $user_ids = array_map(  function ( $post ){
            return (int) $post['user_id'];
        }, $contact_shared_with );
        $this->assertContains( (int) $user_id, $user_ids );

        //test group create
        $group_shared_with = DT_Posts::get_shared_with( 'groups', $group['ID'], false );
        $user_ids = array_map(  function ( $post ){
            return (int) $post['user_id'];
        }, $group_shared_with );
        $this->assertContains( (int) $user_id, $user_ids );

        //test contact update
        $user_2 = wp_create_user( 'multiplier_user_select2', 'test', 'multiplier_user_select2@example.com' );
        $user2 = get_user_by( 'ID', $user_2 );
        $user2->set_role( 'multiplier' );
        $update = DT_Posts::update_post( 'contacts', $contact['ID'], [ 'assigned_to' => $user_2 ], true, false );
        $this->assertNotWPError( $update );
        $contact_shared_with = DT_Posts::get_shared_with( 'contacts', $contact['ID'], false );
        $user_ids = array_map(  function ( $post ){
            return (int) $post['user_id'];
        }, $contact_shared_with );
        $this->assertContains( (int) $user_id, $user_ids );
        $this->assertContains( (int) $user_2, $user_ids );
    }

    public function test_connection_creation_via_additional_meta(){
        $contact1 = DT_Posts::create_post( 'contacts', [ 'name' => 'one' ], true, false );
        $this->assertNotWPError( $contact1 );
        //indicated that the new contact is created from the "baptized" field
        $contact2 = DT_Posts::create_post( 'contacts', [ 'name' => 'two', 'additional_meta' => [ 'created_from' => $contact1['ID'], 'add_connection' => 'baptized' ] ], true, false );
        $this->assertNotWPError( $contact2 );
        //check that the new contact has the "baptized_by" field set correctly
        $this->assertSame( (int) $contact2['baptized_by'][0]['ID'], (int) $contact1['ID'] );

        //indicate the the new group is created from the contact's groups field
        $group1 = DT_Posts::create_post( 'groups', [ 'name' => 'group1', 'additional_meta' => [ 'created_from' => $contact1['ID'], 'add_connection' => 'groups' ] ], true, false );
        $this->assertNotWPError( $group1 );
        //check that the new group has the contact in it's members field
        $this->assertSame( (int) $group1['members'][0]['ID'], (int) $contact1['ID'] );

        //check that the initial contact has the group and the baptized field correctly filled out
        $contact1 = DT_Posts::get_post( 'contacts', $contact1['ID'], false, false );
        $this->assertNotWPError( $contact1 );
        $this->assertSame( (int) $contact1['groups'][0]['ID'], (int) $group1['ID'] );
        $this->assertSame( (int) $contact1['baptized'][0]['ID'], (int) $contact2['ID'] );

    }

    public function test_custom_number_field_min_max_error() {
        // test that lower than the minimum creates an error
        $contact1 = DT_Posts::create_post( 'contacts', [ 'name' => 'one', 'number_test' => 0 ], true, false );
        $this->assertWPError( $contact1 );

        // test that higher than the maximum creates an error
        $contact2 = DT_Posts::create_post( 'contacts', [ 'name' => 'one', 'number_test_private' => 300 ], true, false );
        $this->assertWPError( $contact2 );

    }

}
