<?php
/**
 * Class PostsTest
 *
 * @package Disciple.Tools
 */


class PostsTest extends WP_UnitTestCase {

    public static $sample_contact = array(
        'title' => 'Bob',
        'overall_status' => 'active',
        'milestones' => array( 'values' => array( array( 'value' => 'milestone_has_bible' ), array( 'value' => 'milestone_baptizing' ) ) ),
        'baptism_date' => '2018-12-31',
        'location_grid' => array( 'values' => array( array( 'value' => '100089589' ) ) ),
        'assigned_to' => '1',
        'requires_update' => true,
        'nickname' => 'Bob the builder',
        'contact_phone' => array( 'values' => array( array( 'value' => '798456780' ) ) ),
        'contact_email' => array( 'values' => array( array( 'value' => 'bob@example.com' ) ) ),
        'tags' => array( 'values' => array( array( 'value' => 'tag1' ) ) ),
    );

    public $sample_group = array(
        'name' => 'Bob\'s group',
        'group_type' => 'church',
        'location_grid' => array( 'values' => array( array( 'value' => '100089589' ) ) ),
        'member_count' => 5,
    );

    public static function setupBeforeClass(): void  {
        $user_id = wp_create_user( 'testcontactgroup', 'test', 'testcontactgroup@example.com' );
        $user = get_user_by( 'id', $user_id );
        $user->set_role( 'dispatcher' );
        self::$sample_contact['assigned_to'] = $user_id;
        update_option( 'dt_base_user', $user_id );
    }

    public function test_member_count(){
        $user_id = wp_create_user( 'user3', 'test', 'test3@example.com' );
        wp_set_current_user( $user_id );
        $current_user = wp_get_current_user();
        $current_user->set_role( 'dispatcher' );

        $contact1 = DT_Posts::create_post( 'contacts', self::$sample_contact );
        //create group with contact1 as member
        $group1 = DT_Posts::create_post( 'groups', array(
            'title'   => 'group1',
            'members' => array( 'values' => array( array( 'value' => $contact1['ID'] ) ) ),
        ) );
        $this->assertSame( sizeof( $group1['members'] ), 1 );
        $this->assertSame( $group1['member_count'], 1 );
        //create contact2 with group1 in groups
        $contact2_fields = array(
            'title' => 'contact 2',
            'groups' => array( 'values' => array( array( 'value' => $group1['ID'] ) ) ),
        );
        $contact2 = DT_Posts::create_post( 'contacts', $contact2_fields );
        //check member counts
        $contact1 = DT_Posts::get_post( 'contacts', $contact1['ID'] );
        $group1 = DT_Posts::get_post( 'groups', $group1['ID'], false );
        $this->assertSame( sizeof( $group1['members'] ), 2 );
        $this->assertSame( $group1['member_count'], 2 );

        //remove on both
        $contact2 = DT_Posts::update_post( 'contacts', $contact2['ID'], array(
            'groups' => array(
                'values' => array(
                    array(
                        'value'  => $group1['ID'],
                        'delete' => true,
                    ),
                ),
            ),
        ) );
        $this->assertNotWPError( $contact2 );
        $group1 = DT_Posts::get_post( 'groups', $group1['ID'], false );
        $this->assertSame( 1, $group1['member_count'] );
        $group1 = DT_Posts::update_post( 'groups', $group1['ID'], array(
            'members' => array(
                'values' => array(
                    array(
                        'value'  => $contact1['ID'],
                        'delete' => true,
                    ),
                ),
            ),
        ) );
        $this->assertSame( $group1['member_count'], 0 );

        // test force values
        $contact3 = DT_Posts::create_post( 'contacts', array(
            'title' => 'contact3',
            'groups' => array( 'values' => array( array( 'value' => $group1['ID'] ) ) ),
        ));
        $group1 = DT_Posts::update_post( 'groups', $group1['ID'], array(
            'members' => array(
                'values' => array(
                    array( 'value'  => $contact1['ID'] ),
                    array( 'value'  => $contact2['ID'] ),
                ),
                'force_values' => true,
            ),
        ) );
        $this->assertSame( $group1['member_count'], 2 );

        //test removing member form manually set member count.
        DT_Posts::update_post( 'groups', $group1['ID'], array( 'member_count' => 10 ) );
        $group1 = DT_Posts::update_post( 'groups', $group1['ID'], array(
            'members' => array(
                'values' => array(
                    array(
                        'value'  => $contact1['ID'],
                        'delete' => true,
                    ),
                ),
            ),
        ) );
        $this->assertSame( $group1['member_count'], 9 );
    }

    public function test_force_values() {
        //create values for multi_select, connection, location and details fields
        $group1 = DT_Posts::create_post( 'groups', array( 'title' => 'group1' ), true, false );
        $group2 = DT_Posts::create_post( 'groups', array( 'title' => 'group2' ), true, false );
        $group3 = DT_Posts::create_post( 'groups', array( 'title' => 'group3' ), true, false );
        $contact1 = DT_Posts::create_post( 'contacts', array(
            'title' => 'bob',
            'milestones' => array( 'values' => array( array( 'value' => 'milestone_has_bible' ), array( 'value' => 'milestone_baptizing' ) ) ),
            'groups' => array( 'values' => array( array( 'value' => $group1['ID'] ), array( 'value' => $group2['ID'] ) ) ),
            'location_grid' => array( 'values' => array( array( 'value' => 100089589 ), array( 'value' => 100056133 ) ) ),
            'contact_phone' => array( 'values' => array( array( 'value' => '123', 'verified' => true ), array( 'value' => '321' ) ) ),
        ), true, false );
        $this->assertNotWPError( $contact1 );
        $this->assertSame( sizeof( $contact1['milestones'] ), 2 );
        $this->assertSame( sizeof( $contact1['groups'] ), 2 );
        $this->assertSame( sizeof( $contact1['location_grid'] ), 2 );
        $this->assertSame( sizeof( $contact1['contact_phone'] ), 2 );

        //force values with update
        $phone_key = $contact1['contact_phone'][0]['key'];
        $contact1 = DT_Posts::update_post( 'contacts', $contact1['ID'], array(
            'milestones' => [ "values" => [ [ "value" => 'milestone_has_bible' ], [ "value" => "milestone_sharing" ] ], "force_values" => true ], //@phpcs:ignore
            'groups' => array( 'values' => array( array( 'value' => $group1['ID'] ), array( 'value' => $group3['ID'] ) ), 'force_values' => true ),
            'location_grid' => array( 'values' => array( array( 'value' => 100089589 ) ), 'force_values' => true ),
            'contact_phone' => array( 'values' => array( array( 'key' => $phone_key, 'value' => '456' ) ), 'force_values' => true ),
        ), true, false );
        $this->assertNotWPError( $contact1 );
        $this->assertSame( sizeof( $contact1['milestones'] ), 2 );
        $this->assertSame( sizeof( $contact1['groups'] ), 2 );
        $this->assertSame( sizeof( $contact1['location_grid'] ), 1 );
        $this->assertSame( sizeof( $contact1['contact_phone'] ), 1 );
        $this->assertSame( $phone_key, $contact1['contact_phone'][0]['key'] );
        $this->assertSame( true, $contact1['contact_phone'][0]['verified'] ?? false );

        //remove all values with force_values
        $contact1 = DT_Posts::update_post( 'contacts', $contact1['ID'], array(
            'milestones' => array( 'values' => array(), 'force_values' => true ),
            'groups' => array( 'values' => array(), 'force_values' => true ),
            'location_grid' => array( 'values' => array(), 'force_values' => true ),
            'contact_phone' => array( 'values' => array(), 'force_values' => true ),
        ), true, false );
        $this->assertNotWPError( $contact1 );
        $this->assertSame( sizeof( $contact1['milestones'] ?? array() ), 0 );
        $this->assertSame( sizeof( $contact1['groups'] ?? array() ), 0 );
        $this->assertSame( sizeof( $contact1['location_grid'] ?? array() ), 0 );
        $this->assertSame( sizeof( $contact1['contact_phone'] ?? array() ), 0 );
    }


    public function test_post_user_meta_fields(){
        $user1_id = wp_create_user( 'user1m', 'test', 'test1m@example.com' );
        wp_set_current_user( $user1_id );
        $user1 = wp_get_current_user();
        $user1->set_role( 'multiplier' );

        $user2_id = wp_create_user( 'user2m', 'test', 'user2m@example.com' );
        $user2 = get_user_by( 'id', $user2_id );



        $contact1 = DT_Posts::create_post( 'contacts', array(
            'title'     => 'contact1',
            'tasks' => array(
                'values' => array(
                    array(
                        'value' => 'hello',
                        'date' => '2018-01-01',
                    ),
                ),
            ),
        ) );
        $this->assertNotWPError( $contact1 );
        $this->assertArrayHasKey( 'tasks', $contact1 );
        $this->assertCount( 1, $contact1['tasks'] );
        $this->assertSame( 'hello', $contact1['tasks'][0]['value'] );

        $task_id = $contact1['tasks'][0]['id'];
        $contact = DT_Posts::update_post( 'contacts', $contact1['ID'], array(
            'tasks' => array(
                'values' => array(
                    array(
                        'id' => $task_id,
                        'value' => 'a new value',
                        'date'  => '2017-01-01',
                    ),
                ),
            ),
        ) );
        $this->assertNotWPError( $contact );
        $this->assertCount( 1, $contact['tasks'] );
        $this->assertSame( 'a new value', $contact['tasks'][0]['value'] );
        $this->assertNotSame( $contact1['tasks'][0]['date'], $contact['tasks'][0]['date'] );

        $this->assertNotWPError( $contact );
        $deleted = DT_Posts::update_post( 'contacts', $contact1['ID'], array(
            'tasks' => array(
                'values' => array(
                    array(
                        'id' => $task_id,
                        'delete' => true,
                    ),
                ),
            ),
        ) );
        $this->assertNotWPError( $deleted );
        $this->assertArrayNotHasKey( 'tasks', $deleted );

        //now try to break things
        $contact2 = DT_Posts::create_post( 'contacts', array(
            'title'     => 'contact2',
            'tasks' => array(
                'values' => array(
                    array(
                        'value' => 'hello contact 2',
                        'date' => '2018-01-01',
                    ),
                ),
            ),
        ) );
        $contact2_task_id = $contact2['tasks'][0]['id'];

        $update_non_existing_id = DT_Posts::update_post( 'contacts', $contact2['ID'], array(
            'tasks' => array(
                'values' => array(
                    array(
                        'id' => 1000,
                        'value' => 'a new value',
                        'date'  => '2017-01-01',
                    ),
                ),
            ),
        ) );
        $this->assertWPError( $update_non_existing_id );
        $delete_non_existing_id = DT_Posts::update_post( 'contacts', $contact2['ID'], array(
            'tasks' => array(
                'values' => array(
                    array(
                        'id' => 1000,
                        'delete' => true,
                    ),
                ),
            ),
        ) );
        $this->assertWPError( $delete_non_existing_id );
        $bad_delete = DT_Posts::update_post( 'contacts', $contact2['ID'], array(
            'tasks' => array(
                'values' => array(
                    array(
                        'delete' => true,
                    ),
                ),
            ),
        ) );
        $this->assertWPError( $bad_delete );

        // update ids of another post
        $contact3 = DT_Posts::create_post( 'contacts', array(
            'title'     => 'contact3',
            'tasks' => array(
                'values' => array(
                    array(
                        'value' => 'hello from contact 3',
                        'date' => '2018-01-01',
                    ),
                ),
            ),
        ) );
        $update_another_post = DT_Posts::update_post( 'contacts', $contact3['ID'], array(
            'tasks' => array(
                'values' => array(
                    array(
                        'id'    => $contact2_task_id,
                        'value' => 'a new value',
                        'date'  => '2017-01-01',
                    ),
                ),
            ),
        ) );
        $this->assertWPError( $update_another_post );
        $this->assertCount( 1, $contact2['tasks'] );

        //switch to user2
        wp_set_current_user( $user2_id );
        $user2 = wp_get_current_user();
        $user2->set_role( 'dispatcher' );
        $dispatch_contact_2 = DT_Posts::get_post( 'contacts', $contact2['ID'], true, false );
        $this->assertNotWPError( $dispatch_contact_2 );
        //access user1's tasks
        $this->assertArrayNotHasKey( 'tasks', $dispatch_contact_2 );
        //update user1's tasks
        $update_anothers_task = DT_Posts::update_post( 'contacts', $contact2['ID'], array(
            'tasks' => array(
                'values' => array(
                    array(
                        'id' => $contact2_task_id,
                        'value' => 'a new value',
                        'date'  => '2017-01-01',
                    ),
                ),
            ),
        ), true, false );
        $this->assertWPError( $update_anothers_task );
        $delete_anothers_task = DT_Posts::update_post( 'contacts', $contact2['ID'], array(
            'tasks' => array(
                'values' => array(
                    array(
                        'id' => $contact2_task_id,
                        'value' => 'a new value',
                        'date'  => '2017-01-01',
                        'delete' => true,
                    ),
                ),
            ),
        ), true, false );
        $this->assertWPError( $delete_anothers_task );
    }
}
