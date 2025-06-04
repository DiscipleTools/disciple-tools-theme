<?php
require_once( get_template_directory() . '/tests/dt-posts/tests-setup.php' );

/**
 * @testdox DT_Posts::update_post
 */
class DT_Posts_DT_Posts_Update_Post extends WP_UnitTestCase {

    public static $sample_contact = [
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
        'tags' => [ 'values' => [ [ 'value' => 'tag1' ], [ 'value' => 'tagToDelete' ] ] ],
        'quick_button_contact_established' => '1'
    ];
    public static $contact = null;

    public static function setupBeforeClass(): void  {
        //setup custom fields for each field type and custom tile.
        $user_id = wp_create_user( 'dispatcher1', 'test', 'test2@example.com' );
        wp_set_current_user( $user_id );
        $current_user = wp_get_current_user();
        $current_user->set_role( 'dispatcher' );
        self::$sample_contact['assigned_to'] = $user_id;

        self::$contact = DT_Posts::create_post( 'contacts', self::$sample_contact, true, false );
    }

    public function test_update_on_custom_fields(){
        $user_id = wp_create_user( 'dispatcher3', 'test', 'test3@example.com' );
        wp_set_current_user( $user_id );
        $current_user = wp_get_current_user();
        $current_user->set_role( 'dispatcher' );
        $update_values = dt_test_get_sample_record_fields( $user_id );
        $result = DT_Posts::update_post( 'contacts', self::$contact['ID'], $update_values, true, false );
        $this->assertNotWPError( $result );


        //setting values on each field type
        //@todo connection field
        $this->assertSame( $result['title'], $update_values['title'] );
        $this->assertSame( (int) $result['number_test'], (int) $update_values['number_test'] ); //@todo returned value should be an int
        $this->assertSame( (int) $result['number_test_private'], (int) $update_values['number_test_private'] ); //@todo returned value should be an int
        $this->assertSame( $result['text_test'], $update_values['text_test'] );
        $this->assertSame( $result['text_test_private'], $update_values['text_test_private'] );
        $this->assertSame( $result['contact_communication_channel_test'][0]['value'], $update_values['contact_communication_channel_test']['values'][0]['value'] );
        $this->assertSame( $result['user_select_test']['id'], $update_values['user_select_test'] );
        $this->assertSame( $result['array_test'], $update_values['array_test'] );
        $this->assertSame( (int) $result['location_test'][0]['id'], (int) $update_values['location_test']['values'][0]['value'] ); //@todo returned value should be an int
        $this->assertSame( (int) $result['date_test']['timestamp'], strtotime( $update_values['date_test'] ) ); //@todo returned value should be an int
        $this->assertSame( (int) $result['date_test_private']['timestamp'], strtotime( $update_values['date_test_private'] ) ); //@todo returned value should be an int
        $this->assertSame( (int) $result['datetime_test']['timestamp'], strtotime( $update_values['datetime_test'] ) ); //@todo returned value should be an int
        $this->assertSame( (int) $result['datetime_test_private']['timestamp'], strtotime( $update_values['datetime_test_private'] ) ); //@todo returned value should be an int
        $this->assertSame( $result['boolean_test'], $update_values['boolean_test'] );
        $this->assertSame( $result['boolean_test_private'], $update_values['boolean_test_private'] );
        $this->assertSame( $result['multi_select_test'][0], $update_values['multi_select_test']['values'][0]['value'] );
        $this->assertSame( $result['multi_select_test'][1], $update_values['multi_select_test']['values'][1]['value'] );
        $this->assertSame( $result['multi_select_test_private'][0], $update_values['multi_select_test_private']['values'][0]['value'] );
        $this->assertSame( $result['multi_select_test_private'][1], $update_values['multi_select_test_private']['values'][1]['value'] );
        $this->assertSame( $result['key_select_test']['key'], $update_values['key_select_test'] );
        $this->assertSame( $result['key_select_test_private']['key'], $update_values['key_select_test_private'] );
        $this->assertSame( $result['tags_test'][0], $update_values['tags_test']['values'][0]['value'] );
        $this->assertSame( $result['tags_test_private'][0], $update_values['tags_test_private']['values'][0]['value'] );
    }

    public function test_custom_number_field_min_max_error() {
        // test that lower than the minimum creates an error
        $result1 = DT_Posts::update_post( 'contacts', self::$contact['ID'], [ 'number_test' => -1 ], true, false );
        $this->assertWPError( $result1 );

        // test that higher than the maximum creates an error
        $contact2 = DT_Posts::update_post( 'contacts', self::$contact['ID'], [ 'number_test_private' => 300 ], true, false );
        $this->assertWPError( $contact2 );
    }

    /**
     * @testdox Tags: add
     */
    public function test_tags_add() {
        //force values with update
        $initial_count = sizeof( self::$contact['tags'] );
        $result = DT_Posts::update_post( 'contacts', self::$contact['ID'], [
            'tags' => [
                'values' => [
                    [ 'value' => 'tag2', ],
                    [ 'value' => 'tag3', ],
                ],
            ], //@phpcs:ignore
        ], true, false );

        $this->assertNotWPError( $result );
        $this->assertContains( 'tag2', $result['tags'] );
        $this->assertContains( 'tag3', $result['tags'] );
        $this->assertSame( sizeof( $result['tags'] ), $initial_count + 2 );
    }
    /**
     * @testdox Tags: remove
     */
    public function test_tags_remove() {
        //force values with update
        $initial_count = sizeof( self::$contact['tags'] );
        $result = DT_Posts::update_post( 'contacts', self::$contact['ID'], [
            'tags' => [
                'values' => [
                    [ 'value' => 'tagToDelete', 'delete' => true, ],
                ],
            ], //@phpcs:ignore
        ], true, false );

        $this->assertNotWPError( $result );
        $this->assertNotContains( 'tagToDelete', $result['tags'] );
        $this->assertSame( sizeof( $result['tags'] ), $initial_count - 1 );
    }
    /**
     * @testdox Tags: force update
     */
    public function test_tags_force() {
        //force values with update
        $result = DT_Posts::update_post( 'contacts', self::$contact['ID'], [
            'tags' => [
                'values' => [
                    [ 'value' => 'tag98', ],
                    [ 'value' => 'tag99', ],
                ],
                'force_values' => true,
            ], //@phpcs:ignore
        ], true, false );
        $this->assertNotWPError( $result );

        $this->assertContains( 'tag98', $result['tags'] );
        $this->assertContains( 'tag99', $result['tags'] );
        $this->assertSame( sizeof( $result['tags'] ), 2 );
    }


    public function test_dt_private_fields(){
        $user_id = wp_create_user( 'user_private_1', 'test', 'user_private_1@example.com' );
        wp_set_current_user( $user_id );
        $current_user = wp_get_current_user();
        $current_user->set_role( 'multiplier' );
        $create_values = dt_test_get_sample_record_fields( self::$sample_contact['assigned_to'] );
        $result = DT_Posts::create_post( 'contacts', $create_values, true, true );
        $second_id = wp_create_user( 'user_private_2', 'test', 'user_private_2@example.com' );
        wp_set_current_user( $second_id );
        $second_user = wp_get_current_user();
        $second_user->set_role( 'multiplier' );
        DT_Posts::add_shared( 'contacts', $result['ID'], $second_id, null, false, false );


        $result = DT_Posts::get_post( 'contacts', $result['ID'], true, true );
        //Second user should not see private values in the contact created by the first user
        $this->assertSame( $result['title'], $create_values['title'] );
        $this->assertSame( $result['text_test'], $create_values['text_test'] );
        $this->assertArrayNotHasKey( 'text_test_private', $result );
        $this->assertSame( $result['contact_communication_channel_test'][0]['value'], $create_values['contact_communication_channel_test']['values'][0]['value'] );
        $this->assertSame( $result['user_select_test']['id'], $create_values['user_select_test'] );
        $this->assertSame( $result['array_test'], $create_values['array_test'] );
        $this->assertSame( (int) $result['location_test'][0]['id'], (int) $create_values['location_test']['values'][0]['value'] ); //@todo returned value should be an int
        $this->assertSame( $result['date_test']['timestamp'], strtotime( $create_values['date_test'] ) );
        $this->assertArrayNotHasKey( 'date_test_private', $result );
        $this->assertSame( $result['boolean_test'], $create_values['boolean_test'] );
        $this->assertArrayNotHasKey( 'boolean_test_private', $result );
        $this->assertSame( $result['multi_select_test'][0], $create_values['multi_select_test']['values'][0]['value'] );
        $this->assertSame( $result['multi_select_test'][1], $create_values['multi_select_test']['values'][1]['value'] );
        $this->assertArrayNotHasKey( 'multi_select_test_private', $result );
        $this->assertSame( $result['key_select_test']['key'], $create_values['key_select_test'] );
        $this->assertArrayNotHasKey( 'key_select_test_private', $result );
        $this->assertSame( $result['tags_test'][0], $create_values['tags_test']['values'][0]['value'] );
        $this->assertArrayNotHasKey( 'tags_test_private', $result );
        $this->assertSame( $result['number_test'], $create_values['number_test'] );
        $this->assertArrayNotHasKey( 'number_test_private', $result );

        //Second user should not see private values in the contact updated by the first user
        $contact2 = DT_Posts::create_post( 'contacts', [ 'title' => 'empty' ], true, true );
        DT_Posts::add_shared( 'contacts', $contact2['ID'], $user_id, null, false, false );
        wp_set_current_user( $user_id );
        $res = DT_Posts::update_post( 'contacts', $contact2['ID'], $create_values );
        wp_set_current_user( $second_id );
        $contact2 = DT_Posts::get_post( 'contacts', $contact2['ID'], false, true );
        $this->assertSame( $contact2['title'], $create_values['title'] );
        $this->assertArrayNotHasKey( 'text_test_private', $contact2 );
        $this->assertArrayNotHasKey( 'date_test_private', $contact2 );
        $this->assertArrayNotHasKey( 'boolean_test_private', $contact2 );
        $this->assertArrayNotHasKey( 'multi_select_test_private', $contact2 );
        $this->assertArrayNotHasKey( 'key_select_test_private', $contact2 );
        $this->assertArrayNotHasKey( 'tags_test_private', $contact2 );
        $this->assertArrayNotHasKey( 'number_test_private', $contact2 );
    }

    /**
     * @testdox do_not_overwrite_existing_fields: update with protection enabled
     */
    public function test_do_not_overwrite_existing_fields_update() {
        // Create a contact with initial values
        $initial_fields = self::$sample_contact;
        $initial_fields['name'] = 'Update Test';
        $initial_fields['nickname'] = 'Original Nick';
        $initial_fields['overall_status'] = 'active';
        $initial_fields['contact_phone'] = [ 'values' => [ [ 'value' => '111-222-3333' ] ] ];
        $initial_fields['tags'] = [ 'values' => [ [ 'value' => 'updating_tag' ] ] ];

        $contact = DT_Posts::create_post( 'contacts', $initial_fields, true, false );
        $this->assertNotWPError( $contact );

        // Update with do_not_overwrite_existing_fields = true
        $update_fields = [
            'overall_status' => 'paused', // Try to change existing field
            'nickname' => 'New Nick', // Try to change existing field
            'contact_email' => [ 'values' => [ [ 'value' => 'test@example.com' ] ] ], // Add new field
            'tags' => [ 'values' => [ [ 'value' => 'updated_tag' ] ] ]
        ];

        $result = DT_Posts::update_post('contacts', $contact['ID'], $update_fields, true, false, [
            'do_not_overwrite_existing_fields' => true
        ]);

        $this->assertNotWPError( $result );
        // Should still only have one contact_phone entry.
        $this->assertSame( 1, count( $result['contact_phone'] ) );
        // Should update to new values, as they are different.
        $this->assertSame( 'paused', $result['overall_status']['key'] );
        $this->assertSame( 'New Nick', $result['nickname'] );
        // Should add new fields
        $this->assertSame( 'test@example.com', $result['contact_email'][1]['value'] );
        // Extra assertion sanity checks.
        $this->assertSame( 2, count( $result['tags'] ) );
        $this->assertSame( $initial_fields['baptism_date'], $result['baptism_date']['formatted'] );
    }

    /**
     * @testdox do_not_overwrite_existing_fields: update with protection disabled
     */
    public function test_overwrite_existing_fields_update() {
        // Create a contact with initial values
        $initial_fields = self::$sample_contact;
        $initial_fields['name'] = 'Overwrite Test';
        $initial_fields['nickname'] = 'Original Nick';
        $initial_fields['overall_status'] = 'active';

        $contact = DT_Posts::create_post( 'contacts', $initial_fields, true, false );
        $this->assertNotWPError( $contact );

        // Update with do_not_overwrite_existing_fields = false (default behavior)
        $update_fields = [
            'overall_status' => 'paused',
            'nickname' => 'New Nick',
            'contact_email' => [ 'values' => [ [ 'value' => 'overwrite@example.com' ] ] ],
            'location_grid' => [ 'values' => [ [ 'value' => '100089589' ] ] ]
        ];

        $result = DT_Posts::update_post('contacts', $contact['ID'], $update_fields, true, false, [
            'do_not_overwrite_existing_fields' => false
        ]);

        $this->assertNotWPError( $result );
        // Should update existing fields with new values
        $this->assertSame( 'paused', $result['overall_status']['key'] );
        $this->assertSame( 'New Nick', $result['nickname'] );
        // Should add new fields
        $this->assertSame( 'overwrite@example.com', $result['contact_email'][1]['value'] );
        // Extra assertion sanity checks.
        $this->assertSame( 1, count( $result['location_grid'] ) );
    }

    /**
     * @testdox do_not_overwrite_existing_fields: empty vs non-empty fields
     */
    public function test_do_not_overwrite_empty_fields_update() {
        // Create a contact with some empty fields
        $initial_fields = self::$sample_contact;
        $initial_fields['name'] = 'Overwrite Test';
        $initial_fields['nickname'] = null;
        $initial_fields['overall_status'] = 'active';

        $contact = DT_Posts::create_post( 'contacts', $initial_fields, true, false );
        $this->assertNotWPError( $contact );

        // Update with do_not_overwrite_existing_fields = true
        $update_fields = [
            'overall_status' => 'paused', // Try to change existing field
            'nickname' => 'Should Be Added' // Add to empty field
        ];

        $result = DT_Posts::update_post('contacts', $contact['ID'], $update_fields, true, false, [
            'do_not_overwrite_existing_fields' => true
        ]);

        $this->assertNotWPError( $result );
        // Should update to new values, as they are different and not duplicates.
        $this->assertSame( 'paused', $result['overall_status']['key'] );
        // Should add value to empty field
        $this->assertSame( 'Should Be Added', $result['nickname'] );
    }

    /**
     * @testdox do_not_overwrite_existing_fields: communication channel fields
     */
    public function test_do_not_overwrite_communication_channels_update() {
        // Create a contact with phone number
        $initial_fields = self::$sample_contact;
        $initial_fields['name'] = 'Communication Test';
        $initial_fields['contact_phone'] = [ 'values' => [ [ 'value' => '444-555-6666' ] ] ];

        $contact = DT_Posts::create_post( 'contacts', $initial_fields, true, false );
        $this->assertNotWPError( $contact );

        // Update with additional phone and email
        $update_fields = [
            'contact_phone' => [ 'values' => [ [ 'value' => '444-555-6666' ], [ 'value' => '777-888-9999' ] ] ],
            'contact_email' => [ 'values' => [ [ 'value' => 'comm@example.com' ] ] ],
            'baptism_date' => $initial_fields['baptism_date']
        ];

        $result = DT_Posts::update_post('contacts', $contact['ID'], $update_fields, true, false, [
            'do_not_overwrite_existing_fields' => true
        ]);

        $this->assertNotWPError( $result );
        // Should keep existing phone and add new one
        $phone_values = array_column( $result['contact_phone'], 'value' );
        $this->assertContains( '444-555-6666', $phone_values );
        $this->assertContains( '777-888-9999', $phone_values );
        // Should add new email
        $this->assertSame( 'comm@example.com', $result['contact_email'][1]['value'] );
        // Extra assertion sanity checks.
        $this->assertSame( 2, count( $result['contact_email'] ) );
        $this->assertSame( 2, count( $result['milestones'] ) );
        $this->assertSame( $initial_fields['baptism_date'], $result['baptism_date']['formatted'] );
    }

    /**
     * @testdox do_not_overwrite_existing_fields: date fields
     */
    public function test_do_not_overwrite_date_fields_update() {
        // Create a contact with baptism date
        $initial_fields = self::$sample_contact;
        $initial_fields['name'] = 'Date Test';
        $initial_fields['baptism_date'] = '2020-01-01';

        $contact = DT_Posts::create_post( 'contacts', $initial_fields, true, false );
        $this->assertNotWPError( $contact );

        // Try to update baptism date
        $update_fields = [
            'baptism_date' => '2023-12-25',
            'tags' => [ 'values' => [ [ 'value' => 'tag1' ], [ 'value' => 'tag2' ] ] ],
            'milestones' => [ 'values' => [ [ 'value' => 'mature_christian' ] ] ]
        ];

        $result = DT_Posts::update_post('contacts', $contact['ID'], $update_fields, true, false, [
            'do_not_overwrite_existing_fields' => true
        ]);

        $this->assertNotWPError( $result );
        // Should update to new values, as they are different and not duplicates.
        $this->assertSame( $update_fields['baptism_date'], $result['baptism_date']['formatted'] );
        // Extra assertion sanity checks.
        $this->assertContains( 'tag2', $result['tags'] );
        $this->assertSame( 3, count( $result['tags'] ) );
        $this->assertSame( 2, count( $result['milestones'] ) );
    }

    public function test_do_not_overwrite_text_fields_update() {
        $initial_fields = self::$sample_contact;
        $initial_fields['contact_phone'] = [ 'values' => [ [ 'value' => '123-456-7890' ] ] ];
        $initial_fields['nickname'] = 'Johnny';

        $contact = DT_Posts::create_post( 'contacts', $initial_fields, true, false );
        $this->assertNotWPError( $contact );

        $update_fields = [
            'name' => 'John Doe',
            'contact_phone' => [ 'values' => [ [ 'value' => '123-456-7890' ] ] ],
            'nickname' => 'John', // Different value
        ];

        $result = DT_Posts::update_post('contacts', $contact['ID'], $update_fields, true, false, [
            'do_not_overwrite_existing_fields' => true
        ]);

        $this->assertNotWPError( $result );

        $this->assertSame( 'John', $result['nickname'] );
    }

    public function test_do_not_overwrite_number_fields_update() {
        $initial_fields = self::$sample_contact;

        $contact = DT_Posts::create_post( 'contacts', $initial_fields, true, false );
        $this->assertNotWPError( $contact );

        $update_fields = [
            'title' => $initial_fields['title'],
            'contact_phone' => $initial_fields['contact_phone'],
            'quick_button_contact_established' => 1
        ];

        $result = DT_Posts::update_post('contacts', $contact['ID'], $update_fields, true, false, [
            'do_not_overwrite_existing_fields' => true
        ]);

        $this->assertNotWPError( $result );

        $this->assertSame( $update_fields['quick_button_contact_established'], $result['quick_button_contact_established'] );
    }

    public function test_do_not_overwrite_boolean_fields_update() {
        $initial_fields = self::$sample_contact;
        $initial_fields['requires_update'] = true;

        $contact = DT_Posts::create_post( 'contacts', $initial_fields, true, false );
        $this->assertNotWPError( $contact );

        $update_fields = [
            'title' => $initial_fields['title'],
            'contact_phone' => $initial_fields['contact_phone'],
            'requires_update' => false
        ];

        $result = DT_Posts::update_post('contacts', $contact['ID'], $update_fields, true, false, [
            'do_not_overwrite_existing_fields' => true
        ]);

        $this->assertNotWPError( $result );

        $this->assertSame( true, empty( $result['requires_update'] ) );
    }

    public function test_do_not_overwrite_key_select_fields_update() {
        $initial_fields = self::$sample_contact;
        $initial_fields['overall_status'] = 'active';

        $contact = DT_Posts::create_post( 'contacts', $initial_fields, true, false );
        $this->assertNotWPError( $contact );

        $update_fields = [
            'name' => 'John Doe',
            'contact_phone' => $initial_fields['contact_phone'],
            'overall_status' => 'paused', // Different value
        ];

        $result = DT_Posts::update_post('contacts', $contact['ID'], $update_fields, true, false, [
            'do_not_overwrite_existing_fields' => true
        ]);

        $this->assertNotWPError( $result );

        $this->assertSame( 'paused', $result['overall_status']['key'] );
    }

    public function test_do_not_overwrite_tags_fields_update() {
        $initial_fields = self::$sample_contact;
        $initial_fields['tags'] = [ 'values' => [ [ 'value' => 'existing_tag' ] ] ];

        $contact = DT_Posts::create_post( 'contacts', $initial_fields, true, false );
        $this->assertNotWPError( $contact );

        $update_fields = [
            'name' => 'Tags Test',
            'contact_phone' => $initial_fields['contact_phone'],
            'tags' => [ 'values' => [ [ 'value' => 'existing_tag' ], [ 'value' => 'new_tag' ] ] ]
        ];

        $result = DT_Posts::update_post('contacts', $contact['ID'], $update_fields, true, false, [
            'do_not_overwrite_existing_fields' => true
        ]);

        $this->assertNotWPError( $result );

        $this->assertSame( 2, count( $result['tags'] ) );
        $this->assertContains( 'existing_tag', $result['tags'] );
        $this->assertContains( 'new_tag', $result['tags'] );
    }

    public function test_do_not_overwrite_location_fields_update() {
        $initial_fields = self::$sample_contact;

        $contact = DT_Posts::create_post( 'contacts', $initial_fields, true, false );
        $this->assertNotWPError( $contact );

        $update_fields = [
            'title' => $initial_fields['title'],
            'contact_phone' => $initial_fields['contact_phone'],
            'location_grid' => [ 'values' => [ [ 'value' => '100089589' ] ] ]
        ];

        $result = DT_Posts::update_post('contacts', $contact['ID'], $update_fields, true, false, [
            'do_not_overwrite_existing_fields' => false
        ]);

        $this->assertNotWPError( $result );

        $this->assertSame( 1, count( $result['location_grid'] ) );
    }
}
