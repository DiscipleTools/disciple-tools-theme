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
        'contact_phone' => [ [ 'value' => '798456780' ] ],
        'contact_email' => [ [ 'value' => 'bob@example.com' ] ],
        'tags' => [ 'values' => [ [ 'value' => 'tag1' ] ] ],
        'quick_button_contact_established' => '1'
    ];

    public $sample_group = [
        'name' => 'Bob\'s group',
        'group_type' => 'church',
        'location_grid' => [ 'values' => [ [ 'value' => '100089589' ] ] ],
        'member_count' => 5
    ];

    public static function setupBeforeClass(): void  {
        $user_id = wp_create_user( 'dispatcher1', 'test', 'test2@example.com' );
        update_option( 'dt_base_user', $user_id, false );
        wp_set_current_user( $user_id );
        $current_user = wp_get_current_user();
        $current_user->set_role( 'dispatcher' );
    }

    /**
     * @testdox Expected fields
     */
    public function test_expected_fields() {
        $base_user = get_option( 'dt_base_user' );
        $this->sample_contact['assigned_to'] = $base_user;
        $group1 = DT_Posts::create_post( 'groups', $this->sample_group );
        $this->sample_contact['groups'] = [ 'values' => [ [ 'value' => $group1['ID'] ] ] ];
        $contact1 = DT_Posts::create_post( 'contacts', $this->sample_contact );
        $this->assertSame( 'Bob', $contact1['title'] );
        $this->assertSame( 'Bob', $contact1['name'] );
        $this->assertSame( 'Bob the builder', $contact1['nickname'] );
        $this->assertSame( 'France', $contact1['location_grid'][0]['label'] );
        $this->assertSame( (int) '1546214400', (int) $contact1['baptism_date']['timestamp'] );
        $this->assertSame( '798456780', $contact1['contact_phone'][0]['value'] );
        $this->assertSame( $base_user, $contact1['assigned_to']['id'] );
        $this->assertSame( "Bob's group", $contact1['groups'][0]['post_title'] );
        $this->assertSame( 'tag1', $contact1['tags'][0] );
    }

    public function test_create_on_custom_fields(){
        $user_id = wp_create_user( 'dispatcher3', 'test', 'test3@example.com' );
        wp_set_current_user( $user_id );
        $current_user = wp_get_current_user();
        $current_user->set_role( 'dispatcher' );
        $create_values = dt_test_get_sample_record_fields( get_option( 'dt_base_user' ) );
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
        $this->assertSame( $result['datetime_test']['timestamp'], strtotime( $create_values['datetime_test'] ) );
        $this->assertSame( $result['datetime_test_private']['timestamp'], strtotime( $create_values['datetime_test_private'] ) );
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
        $contact1 = DT_Posts::create_post( 'contacts', [ 'name' => 'one', 'number_test' => -1 ], true, false );
        $this->assertWPError( $contact1 );

        // test that lower than the minimum creates an error
        $contact2 = DT_Posts::create_post( 'contacts', [ 'name' => 'one', 'number_test' => 0 ], true, false );
        $this->assertWPError( $contact2 );

        // test that higher than the maximum creates an error
        $contact3 = DT_Posts::create_post( 'contacts', [ 'name' => 'one', 'number_test_private' => 300 ], true, false );
        $this->assertWPError( $contact3 );

        // test that clearing the field is not an error
        $contact4 = DT_Posts::create_post( 'contacts', [ 'name' => 'one', 'number_test' => '' ], true, false );
        $this->assertNotWPError( $contact4 );
    }

    /**
     * @testdox do_not_overwrite_existing_fields: create with duplicate detection
     */
    public function test_do_not_overwrite_existing_fields_create() {
        $base_user = get_option( 'dt_base_user' );

        // Create initial contact
        $initial_fields = $this->sample_contact;
        $initial_fields['assigned_to'] = $base_user;
        $initial_fields['name'] = 'John Doe';
        $initial_fields['contact_phone'] = [ [ 'value' => '123-456-7890' ] ];
        $initial_fields['overall_status'] = 'active';
        $initial_fields['nickname'] = 'Johnny';

        $initial_contact = DT_Posts::create_post( 'contacts', $initial_fields, true, false );
        $this->assertNotWPError( $initial_contact );

        // Try to create duplicate with do_not_overwrite_existing_fields = true
        $duplicate_fields = [
            'assigned_to' => $base_user,
            'name' => 'John Doe',
            'contact_phone' => [ [ 'value' => '123-456-7890' ] ],
            'overall_status' => 'paused', // Different value
            'nickname' => 'John', // Different value
            'contact_email' => [ [ 'value' => 'john@example.com' ] ] // New field
        ];

        $result = DT_Posts::create_post('contacts', $duplicate_fields, true, false, [
            'check_for_duplicates' => [ 'contact_phone' ],
            'do_not_overwrite_existing_fields' => true
        ]);

        $this->assertNotWPError( $result );
        // Should still only have one contact_phone entry.
        $this->assertSame( 1, count( $result['contact_phone'] ) );
        // Should update to new values, as they are different.
        $this->assertSame( 'active', $result['overall_status']['key'] );
        $this->assertSame( 'Johnny', $result['nickname'] );
        // Should add new fields.
        $this->assertSame( 'john@example.com', $result['contact_email'][1]['value'] );
        // Extra assertion sanity checks.
        $this->assertContains( 'tag1', $result['tags'] );
        $this->assertSame( 1, count( $result['location_grid'] ) );
        $this->assertSame( 2, count( $result['milestones'] ) );
        $this->assertSame( true, $result['requires_update'] );
    }

    /**
     * @testdox do_not_overwrite_existing_fields: create with overwrite enabled
     */
    public function test_overwrite_existing_fields_create() {
        $base_user = get_option( 'dt_base_user' );

        // Create initial contact
        $initial_fields = $this->sample_contact;
        $initial_fields['assigned_to'] = $base_user;
        $initial_fields['name'] = 'John Doe';
        $initial_fields['contact_phone'] = [ 'values' => [ [ 'value' => '987-654-3210' ] ] ];
        $initial_fields['overall_status'] = 'active';
        $initial_fields['nickname'] = 'Janie';

        $initial_contact = DT_Posts::create_post( 'contacts', $initial_fields, true, false );
        $this->assertNotWPError( $initial_contact );

        // Try to create duplicate with do_not_overwrite_existing_fields = false
        $duplicate_fields = [
            'assigned_to' => $base_user,
            'name' => 'Jane Doe',
            'contact_phone' => [ 'values' => [ [ 'value' => '987-654-3210' ] ] ],
            'overall_status' => 'paused', // Different value
            'nickname' => 'Jane', // Different value
            'contact_email' => [ 'values' => [ [ 'value' => 'jane@example.com' ] ] ], // New field
            'milestones' => [ 'values' => [ [ 'value' => 'milestone_has_bible' ], [ 'value' => 'mature_christian' ] ] ]
        ];

        $result = DT_Posts::create_post('contacts', $duplicate_fields, true, false, [
            'check_for_duplicates' => [ 'contact_phone' ],
            'do_not_overwrite_existing_fields' => false
        ]);

        $this->assertNotWPError( $result );
        // Should update existing fields with new values
        $this->assertSame( 'paused', $result['overall_status']['key'] );
        $this->assertSame( 'Jane', $result['nickname'] );
        // Should add new fields
        $this->assertSame( 'jane@example.com', $result['contact_email'][1]['value'] );
        // Extra assertion sanity checks.
        $this->assertSame( 1, $result['quick_button_contact_established'] );
    }

    /**
     * @testdox do_not_overwrite_existing_fields: multi-select fields
     */
    public function test_do_not_overwrite_multi_select_fields_create() {
        $base_user = get_option( 'dt_base_user' );

        // Create initial contact with tags
        $initial_fields = $this->sample_contact;
        $initial_fields['assigned_to'] = $base_user;
        $initial_fields['name'] = 'Multi Test';
        $initial_fields['contact_phone'] = [ [ 'value' => '555-0001' ] ];
        $initial_fields['tags'] = [ 'values' => [ [ 'value' => 'existing_tag' ] ] ];

        $initial_contact = DT_Posts::create_post( 'contacts', $initial_fields, true, false );
        $this->assertNotWPError( $initial_contact );

        // Try to create duplicate with additional tags
        $duplicate_fields = [
            'assigned_to' => $base_user,
            'name' => 'Multi Test',
            'contact_phone' => [ [ 'value' => '555-0001' ] ],
            'tags' => [ 'values' => [ [ 'value' => 'existing_tag' ], [ 'value' => 'new_tag' ] ] ],
            'baptism_date' => '2025-01-01'
        ];

        $result = DT_Posts::create_post('contacts', $duplicate_fields, true, false, [
            'check_for_duplicates' => [ 'contact_phone' ],
            'do_not_overwrite_existing_fields' => true
        ]);

        $this->assertNotWPError( $result );
        // Should only add new tags, not duplicate existing ones
        $this->assertContains( 'existing_tag', $result['tags'] );
        $this->assertContains( 'new_tag', $result['tags'] );
        $this->assertSame( 2, count( $result['tags'] ) );
        // Extra assertion sanity checks.
        $this->assertSame( $initial_fields['baptism_date'], $result['baptism_date']['formatted'] );
    }

    public function test_do_not_overwrite_text_fields_create() {
        $base_user = get_option( 'dt_base_user' );

        $initial_fields = $this->sample_contact;
        $initial_fields['assigned_to'] = $base_user;
        $initial_fields['contact_phone'] = [ [ 'value' => '123-456-7890' ] ];
        $initial_fields['nickname'] = 'Johnny';

        $initial_contact = DT_Posts::create_post( 'contacts', $initial_fields, true, false );
        $this->assertNotWPError( $initial_contact );

        $duplicate_fields = [
            'assigned_to' => $base_user,
            'name' => 'John Doe',
            'contact_phone' => [ [ 'value' => '123-456-7890' ] ],
            'nickname' => 'John', // Different value
        ];

        $result = DT_Posts::create_post('contacts', $duplicate_fields, true, false, [
            'check_for_duplicates' => [ 'contact_phone' ],
            'do_not_overwrite_existing_fields' => true
        ]);
        $this->assertNotWPError( $result );

        $this->assertSame( 'Johnny', $result['nickname'] );
    }

    public function test_do_not_overwrite_number_fields_create() {
        $base_user = get_option( 'dt_base_user' );

        $initial_fields = $this->sample_contact;
        $initial_fields['assigned_to'] = $base_user;

        $initial_contact = DT_Posts::create_post( 'contacts', $initial_fields, true, false );
        $this->assertNotWPError( $initial_contact );

        $duplicate_fields = [
            'assigned_to' => $base_user,
            'title' => $initial_fields['title'],
            'contact_phone' => $initial_fields['contact_phone'],
            'quick_button_contact_established' => 1
        ];

        $result = DT_Posts::create_post('contacts', $duplicate_fields, true, false, [
            'check_for_duplicates' => [ 'contact_phone' ],
            'do_not_overwrite_existing_fields' => true
        ]);
        $this->assertNotWPError( $result );

        $this->assertSame( $duplicate_fields['quick_button_contact_established'], $result['quick_button_contact_established'] );
    }

    public function test_do_not_overwrite_boolean_fields_create() {
        $base_user = get_option( 'dt_base_user' );

        $initial_fields = $this->sample_contact;
        $initial_fields['requires_update'] = true;
        $initial_fields['assigned_to'] = $base_user;

        $initial_contact = DT_Posts::create_post( 'contacts', $initial_fields, true, false );
        $this->assertNotWPError( $initial_contact );

        $duplicate_fields = [
            'assigned_to' => $base_user,
            'title' => $initial_fields['title'],
            'contact_phone' => $initial_fields['contact_phone'],
            'requires_update' => false
        ];

        $result = DT_Posts::create_post('contacts', $duplicate_fields, true, false, [
            'check_for_duplicates' => [ 'contact_phone' ],
            'do_not_overwrite_existing_fields' => false
        ]);
        $this->assertNotWPError( $result );

        $this->assertSame( true, empty( $result['requires_update'] ) );
    }

    public function test_do_not_overwrite_date_fields_create() {
        $base_user = get_option( 'dt_base_user' );

        $initial_fields = $this->sample_contact;
        $initial_fields['assigned_to'] = $base_user;

        $initial_contact = DT_Posts::create_post( 'contacts', $initial_fields, true, false );
        $this->assertNotWPError( $initial_contact );

        $duplicate_fields = [
            'assigned_to' => $base_user,
            'name' => 'Frank',
            'contact_phone' => $initial_fields['contact_phone'],
            'baptism_date' => '2025-01-01'
        ];

        $result = DT_Posts::create_post('contacts', $duplicate_fields, true, false, [
            'check_for_duplicates' => [ 'contact_phone' ],
            'do_not_overwrite_existing_fields' => true
        ]);
        $this->assertNotWPError( $result );

        $this->assertSame( $initial_fields['baptism_date'], $result['baptism_date']['formatted'] );
    }

    public function test_do_not_overwrite_key_select_fields_create() {
        $base_user = get_option( 'dt_base_user' );

        $initial_fields = $this->sample_contact;
        $initial_fields['overall_status'] = 'active';
        $initial_fields['assigned_to'] = $base_user;

        $initial_contact = DT_Posts::create_post( 'contacts', $initial_fields, true, false );
        $this->assertNotWPError( $initial_contact );

        $duplicate_fields = [
            'assigned_to' => $base_user,
            'name' => 'John Doe',
            'contact_phone' => $initial_fields['contact_phone'],
            'overall_status' => 'paused', // Different value
        ];

        $result = DT_Posts::create_post('contacts', $duplicate_fields, true, false, [
            'check_for_duplicates' => [ 'contact_phone' ],
            'do_not_overwrite_existing_fields' => true
        ]);
        $this->assertNotWPError( $result );

        $this->assertSame( 'active', $result['overall_status']['key'] );
    }

    public function test_do_not_overwrite_tags_fields_create() {
        $base_user = get_option( 'dt_base_user' );

        $initial_fields = $this->sample_contact;
        $initial_fields['assigned_to'] = $base_user;
        $initial_fields['tags'] = [ 'values' => [ [ 'value' => 'existing_tag' ] ] ];

        $initial_contact = DT_Posts::create_post( 'contacts', $initial_fields, true, false );
        $this->assertNotWPError( $initial_contact );

        $duplicate_fields = [
            'assigned_to' => $base_user,
            'name' => 'Tags Test',
            'contact_phone' => $initial_fields['contact_phone'],
            'tags' => [ 'values' => [ [ 'value' => 'existing_tag' ], [ 'value' => 'new_tag' ] ] ]
        ];

        $result = DT_Posts::create_post('contacts', $duplicate_fields, true, false, [
            'check_for_duplicates' => [ 'contact_phone' ],
            'do_not_overwrite_existing_fields' => true
        ]);
        $this->assertNotWPError( $result );

        $this->assertSame( 2, count( $result['tags'] ) );
        $this->assertContains( 'existing_tag', $result['tags'] );
        $this->assertContains( 'new_tag', $result['tags'] );
    }

    public function test_do_not_overwrite_location_fields_create() {
        $base_user = get_option( 'dt_base_user' );

        $initial_fields = $this->sample_contact;
        $initial_fields['assigned_to'] = $base_user;

        $contact = DT_Posts::create_post( 'contacts', $initial_fields, true, false );
        $this->assertNotWPError( $contact );

        $duplicate_fields = [
            'assigned_to' => $base_user,
            'title' => $initial_fields['title'],
            'contact_phone' => $initial_fields['contact_phone'],
            'location_grid' => [ 'values' => [ [ 'value' => '100089589' ] ] ]
        ];

        $result = DT_Posts::create_post('contacts', $duplicate_fields, true, false, [
            'check_for_duplicates' => [ 'contact_phone' ],
            'do_not_overwrite_existing_fields' => false
        ]);
        $this->assertNotWPError( $result );

        $this->assertSame( 1, count( $result['location_grid'] ) );
    }

    public function test_do_not_overwrite_comms_channel_fields_create() {
        $base_user = get_option( 'dt_base_user' );

        $initial_fields = $this->sample_contact;
        $initial_fields['assigned_to'] = $base_user;
        $initial_fields['contact_phone'] = [ [ 'value' => '123-456-7890' ] ];

        $initial_contact = DT_Posts::create_post( 'contacts', $initial_fields, true, false );
        $this->assertNotWPError( $initial_contact );

        $duplicate_fields = [
            'assigned_to' => $base_user,
            'name' => 'John Doe',
            'contact_phone' => [ [ 'value' => '123-456-7890' ] ],
            'contact_email' => [ [ 'value' => 'john@example.com' ] ]
        ];

        $result = DT_Posts::create_post('contacts', $duplicate_fields, true, false, [
            'check_for_duplicates' => [ 'contact_phone' ],
            'do_not_overwrite_existing_fields' => true
        ]);
        $this->assertNotWPError( $result );

        $this->assertSame( 1, count( $result['contact_phone'] ) );
        $this->assertSame( 'john@example.com', $result['contact_email'][1]['value'] );
    }
}
