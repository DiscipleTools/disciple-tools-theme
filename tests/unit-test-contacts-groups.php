<?php
/**
 * Class PostsTest
 *
 * @package Disciple.Tools
 */


class PostsTest extends WP_UnitTestCase {

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

    public function test_member_count(){
        $user_id = wp_create_user( "user3", "test", "test3@example.com" );
        wp_set_current_user( $user_id );
        $current_user = wp_get_current_user();
        $current_user->set_role( 'dispatcher' );

        $contact1 = DT_Posts::create_post( 'contacts', $this->sample_contact );
        //create group with contact1 as member
        $group1 = DT_Posts::create_post( 'groups', [
            'title'   => 'group1',
            "members" => [ "values" => [ [ "value" => $contact1["ID"] ] ] ]
        ] );
        $this->assertSame( sizeof( $group1['members'] ), 1 );
        $this->assertSame( $group1["member_count"], 1 );
        //create contact2 with group1 in groups
        $contact2_fields = [
            'title' => 'contact 2',
            "groups" => [ "values" => [ [ "value" => $group1["ID"] ] ] ]
        ];
        $contact2 = DT_Posts::create_post( 'contacts', $contact2_fields );
        //check member counts
        $contact1 = DT_Posts::get_post( 'contacts', $contact1["ID"] );
        $group1 = DT_Posts::get_post( 'groups', $group1["ID"], false );
        $this->assertSame( sizeof( $group1['members'] ), 2 );
        $this->assertSame( $group1["member_count"], 2 );

        //remove on both
        $contact2 = DT_Posts::update_post( 'contacts', $contact2["ID"], [
            'groups' => [
                "values" => [
                    [
                        "value"  => $group1["ID"],
                        "delete" => true
                    ]
                ]
            ]
        ] );
        $this->assertNotWPError( $contact2 );
        $group1 = DT_Posts::get_post( 'groups', $group1["ID"], false );
        $this->assertSame( 1, $group1["member_count"] );
        $group1 = DT_Posts::update_post( 'groups', $group1["ID"], [
            'members' => [
                "values" => [
                    [
                        "value"  => $contact1["ID"],
                        "delete" => true
                    ]
                ]
            ]
        ] );
        $this->assertSame( $group1["member_count"], 0 );

        // test force values
        $contact3 = DT_Posts::create_post( 'contacts', [
            'title' => "contact3",
            "groups" => [ "values" => [ [ "value" => $group1["ID"] ] ] ]
        ]);
        $group1 = DT_Posts::update_post( 'groups', $group1["ID"], [
            'members' => [
                "values" => [
                    [ "value"  => $contact1["ID"] ],
                    [ "value"  => $contact2["ID"] ]
                ],
                "force_values" => true
            ]
        ] );
        $this->assertSame( $group1["member_count"], 2 );

        //test removing member form manually set member count.
        DT_Posts::update_post( 'groups', $group1["ID"], [ "member_count" => 10 ] );
        $group1 = DT_Posts::update_post( 'groups', $group1["ID"], [
            'members' => [
                "values" => [
                    [
                        "value"  => $contact1["ID"],
                        "delete" => true
                    ]
                ]
            ]
        ] );
        $this->assertSame( $group1["member_count"], 9 );
    }

    public function test_force_values() {
        //create values for multi_select, connection, location and details fields
        $group1 = DT_Posts::create_post( 'groups', [ "title" => "group1" ], true, false );
        $group2 = DT_Posts::create_post( 'groups', [ "title" => "group2" ], true, false );
        $group3 = DT_Posts::create_post( 'groups', [ "title" => "group3" ], true, false );
        $contact1 = DT_Posts::create_post( "contacts", [
            "title" => "bob",
            'milestones' => [ "values" => [ [ "value" => 'milestone_has_bible' ], [ "value" => "milestone_baptizing" ] ] ],
            'groups' => [ "values" => [ [ "value" => $group1["ID"] ], [ "value" => $group2["ID"] ] ] ],
            'location_grid' => [ "values" => [ [ "value" => 100089589 ], [ "value" => 100056133 ] ] ],
            'contact_phone' => [ "values" => [ [ "value" => '123', "verified" => true ], [ "value" => "321" ] ] ]
        ], true, false );
        $this->assertNotWPError( $contact1 );
        $this->assertSame( sizeof( $contact1["milestones"] ), 2 );
        $this->assertSame( sizeof( $contact1["groups"] ), 2 );
        $this->assertSame( sizeof( $contact1["location_grid"] ), 2 );
        $this->assertSame( sizeof( $contact1["contact_phone"] ), 2 );

        //force values with update
        $phone_key = $contact1['contact_phone'][0]["key"];
        $contact1 = DT_Posts::update_post( 'contacts', $contact1["ID"], [
            'milestones' => [ "values" => [ [ "value" => 'milestone_has_bible' ], [ "value" => "milestone_sharing" ] ], "force_values" => true ], //@phpcs:ignore
            'groups' => [ "values" => [ [ "value" => $group1["ID"] ], [ "value" => $group3["ID"] ] ], "force_values" => true ],
            'location_grid' => [ "values" => [ [ "value" => 100089589 ] ], "force_values" => true ],
            'contact_phone' => [ "values" => [ [ "key" => $phone_key, "value" => '456' ] ], "force_values" => true ],
        ], true, false );
        $this->assertNotWPError( $contact1 );
        $this->assertSame( sizeof( $contact1["milestones"] ), 2 );
        $this->assertSame( sizeof( $contact1["groups"] ), 2 );
        $this->assertSame( sizeof( $contact1["location_grid"] ), 1 );
        $this->assertSame( sizeof( $contact1["contact_phone"] ), 1 );
        $this->assertSame( $phone_key, $contact1['contact_phone'][0]["key"] );
        $this->assertSame( true, $contact1['contact_phone'][0]["verified"] ?? false );

        //remove all values with force_values
        $contact1 = DT_Posts::update_post( 'contacts', $contact1["ID"], [
            'milestones' => [ "values" => [], "force_values" => true ],
            'groups' => [ "values" => [], "force_values" => true ],
            'location_grid' => [ "values" => [], "force_values" => true ],
            'contact_phone' => [ "values" => [], "force_values" => true ],
        ], true, false );
        $this->assertNotWPError( $contact1 );
        $this->assertSame( sizeof( $contact1["milestones"] ?? [] ), 0 );
        $this->assertSame( sizeof( $contact1["groups"] ?? [] ), 0 );
        $this->assertSame( sizeof( $contact1["location_grid"] ?? [] ), 0 );
        $this->assertSame( sizeof( $contact1["contact_phone"] ?? [] ), 0 );
    }


    public function test_post_user_meta_fields(){
        $user1_id = wp_create_user( "user1m", "test", "test1m@example.com" );
        wp_set_current_user( $user1_id );
        $user1 = wp_get_current_user();
        $user1->set_role( 'multiplier' );

        $user2_id = wp_create_user( "user2m", "test", "user2m@example.com" );
        $user2 = get_user_by( "id", $user2_id );



        $contact1 = DT_Posts::create_post( "contacts", [
            "title"     => "contact1",
            "tasks" => [
                "values" => [
                    [
                        "value" => "hello",
                        "date" => "2018-01-01"
                    ]
                ]
            ]
        ] );
        $this->assertNotWPError( $contact1 );
        $this->assertArrayHasKey( 'tasks', $contact1 );
        $this->assertCount( 1, $contact1['tasks'] );
        $this->assertSame( "hello", $contact1["tasks"][0]["value"] );

        $task_id = $contact1["tasks"][0]["id"];
        $contact = DT_Posts::update_post( "contacts", $contact1["ID"], [
            "tasks" => [
                "values" => [
                    [
                        "id" => $task_id,
                        "value" => "a new value",
                        "date"  => "2017-01-01",
                    ]
                ]
            ]
        ] );
        $this->assertNotWPError( $contact );
        $this->assertCount( 1, $contact['tasks'] );
        $this->assertSame( "a new value", $contact["tasks"][0]["value"] );
        $this->assertNotSame( $contact1["tasks"][0]["date"], $contact["tasks"][0]["date"] );

        $this->assertNotWPError( $contact );
        $deleted = DT_Posts::update_post( "contacts", $contact1["ID"], [
            "tasks" => [
                "values" => [
                    [
                        "id" => $task_id,
                        "delete" => true
                    ]
                ]
            ]
        ] );
        $this->assertNotWPError( $deleted );
        $this->assertArrayNotHasKey( 'tasks', $deleted );

        //now try to break things
        $contact2 = DT_Posts::create_post( "contacts", [
            "title"     => "contact2",
            "tasks" => [
                "values" => [
                    [
                        "value" => "hello contact 2",
                        "date" => "2018-01-01"
                    ]
                ]
            ]
        ] );
        $contact2_task_id = $contact2["tasks"][0]["id"];

        $update_non_existing_id = DT_Posts::update_post( "contacts", $contact2["ID"], [
            "tasks" => [
                "values" => [
                    [
                        "id" => 1000,
                        "value" => "a new value",
                        "date"  => "2017-01-01",
                    ]
                ]
            ]
        ] );
        $this->assertWPError( $update_non_existing_id );
        $delete_non_existing_id = DT_Posts::update_post( "contacts", $contact2["ID"], [
            "tasks" => [
                "values" => [
                    [
                        "id" => 1000,
                        "delete" => true
                    ]
                ]
            ]
        ] );
        $this->assertWPError( $delete_non_existing_id );
        $bad_delete = DT_Posts::update_post( "contacts", $contact2["ID"], [
            "tasks" => [
                "values" => [
                    [
                        "delete" => true
                    ]
                ]
            ]
        ] );
        $this->assertWPError( $bad_delete );

        // update ids of another post
        $contact3 = DT_Posts::create_post( "contacts", [
            "title"     => "contact3",
            "tasks" => [
                "values" => [
                    [
                        "value" => "hello from contact 3",
                        "date" => "2018-01-01"
                    ]
                ]
            ]
        ] );
        $update_another_post = DT_Posts::update_post( "contacts", $contact3["ID"], [
            "tasks" => [
                "values" => [
                    [
                        "id"    => $contact2_task_id,
                        "value" => "a new value",
                        "date"  => "2017-01-01",
                    ]
                ]
            ]
        ] );
        $this->assertWPError( $update_another_post );
        $this->assertCount( 1, $contact2["tasks"] );

        //switch to user2
        wp_set_current_user( $user2_id );
        $user2 = wp_get_current_user();
        $user2->set_role( 'dispatcher' );
        $dispatch_contact_2 = DT_Posts::get_post( "contacts", $contact2["ID"], true, false );
        $this->assertNotWPError( $dispatch_contact_2 );
        //access user1's tasks
        $this->assertArrayNotHasKey( 'tasks', $dispatch_contact_2 );
        //update user1's tasks
        $update_anothers_task = DT_Posts::update_post( "contacts", $contact2["ID"], [
            "tasks" => [
                "values" => [
                    [
                        "id" => $contact2_task_id,
                        "value" => "a new value",
                        "date"  => "2017-01-01",
                    ]
                ]
            ]
        ], true, false );
        $this->assertWPError( $update_anothers_task );
        $delete_anothers_task = DT_Posts::update_post( "contacts", $contact2["ID"], [
            "tasks" => [
                "values" => [
                    [
                        "id" => $contact2_task_id,
                        "value" => "a new value",
                        "date"  => "2017-01-01",
                        "delete" => true
                    ]
                ]
            ]
        ], true, false );
        $this->assertWPError( $delete_anothers_task );
    }

}
