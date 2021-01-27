<?php
/**
 * Class PostsTest
 *
 * @package Disciple_Tools_Theme
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
        "contact_email" => [ "values" => [ [ "value" => "bob@example.com" ] ] ]
    ];

    public $sample_group = [
        'name' => 'Bob\'s group',
        'group_type' => 'church',
        "location_grid" => [ "values" => [ [ "value" => '100089589' ] ] ],
        "member_count" => 5
    ];


    public function test_expected_fields(){
        $user_id = wp_create_user( "dispatcher1", "test", "test2@example.com" );
        wp_set_current_user( $user_id );
        $current_user = wp_get_current_user();
        $current_user->set_role( 'dispatcher' );
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

    }

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
        $this->assertSame( $group1["member_count"], '1' );
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
        $this->assertSame( $group1["member_count"], '2' );

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
        $this->assertSame( '1', $group1["member_count"] );
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
        $this->assertSame( $group1["member_count"], '0' );

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
        $this->assertSame( $group1["member_count"], '2' );

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
        $this->assertSame( $group1["member_count"], '9' );
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


    private function map_ids( $posts ){
        return array_map(  function ( $post ){
            return $post->ID;
        }, $posts );
    }

    public function test_search_fields_structure(){
        $group1 = DT_Posts::create_post( "groups", $this->sample_group, true, false );
        $group2 = DT_Posts::create_post( "groups", $this->sample_group, true, false );
        $sample_contact = DT_Posts::create_post( "contacts", $this->sample_contact, true, false );
        $contact1 = DT_Posts::create_post( 'contacts', [ "name" => "a", "groups" => [ "values" => [ [ "value" => $group1["ID"] ] ] ] ], true, false );
        $contact2 = DT_Posts::create_post( 'contacts', [ "name" => "b", "groups" => [ "values" => [ [ "value" => $group2["ID"] ] ] ] ], true, false );
        $empty_contact = DT_Posts::create_post( "contacts", [ "name" => "x" ], true, false );
        $empty_group = DT_Posts::create_post( "groups", [ "name" => "x" ], true, false );

        /**
         * connections
         */
        $res = DT_Posts::search_viewable_post( "contacts", [ "groups" => [ $group1["ID"], $group2["ID"] ] ], false );
        $this->assertCount( 2, $res["posts"] );
        $res = DT_Posts::search_viewable_post( "contacts", [ "groups" => [ $group1["ID"] ] ], false );
        $this->assertCount( 1, $res["posts"] );
        $this->assertSame( "a", $res["posts"][0]->post_title );
        $res = DT_Posts::search_viewable_post( "contacts", [ "groups" => [ '-' . $group1["ID"] ] ], false );
        $this->assertNotContains( $contact1["ID"], self::map_ids( $res["posts"] ) );
        $this->assertContains( $contact2["ID"], self::map_ids( $res["posts"] ) );
        $this->assertContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        //search for posts with no values set for field x
        $res = DT_Posts::search_viewable_post( "contacts", [ "groups" => [] ], false );
        $this->assertContains( $empty_contact["ID"], self::map_ids( $res["posts"] ) );
        $this->assertNotContains( $contact1["ID"], self::map_ids( $res["posts"] ) );
        //bad request
        $res = DT_Posts::search_viewable_post( "contacts", [ "groups" => $group1["ID"] ], false );
        $this->assertWPError( $res );


        /**
         * locations_grid
         */
        DT_Posts::create_post( 'contacts', [ "name" => "a", "location_grid" => [ "values" => [ [ "value" => 100089652 ] ] ] ], true, false );
        DT_Posts::create_post( 'contacts', [ "name" => "b", "location_grid" => [ "values" => [ [ "value" => 100089652 ] ] ] ], true, false );
        $res = DT_Posts::search_viewable_post( "contacts", [ "location_grid" => [ "100089652" ] ], false );
        $this->assertCount( 2, $res["posts"] );
        $all = DT_Posts::search_viewable_post( "contacts", [], false );
        $res = DT_Posts::search_viewable_post( "contacts", [ "location_grid" => [ -100089652 ] ], false );
        $this->assertEquals( $res["total"], $all["total"] - 2 );
        //search for posts with no values set for field x
        $res = DT_Posts::search_viewable_post( "contacts", [ "location_grid" => [] ], false );
        $this->assertContains( $empty_contact["ID"], self::map_ids( $res["posts"] ) );
        $this->assertNotContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        //bad request
        $res = DT_Posts::search_viewable_post( "contacts", [ "location_grid" => 100089652 ], false );
        $this->assertWPError( $res );

        /**
         * user_select
         */
        DT_Posts::create_post( 'contacts', [ "assigned_to" => 1 ], true, false );
        $res = DT_Posts::search_viewable_post( "contacts", [ "assigned_to" => [ 1 ] ], false );
        $this->assertCount( 1, $res["posts"] );
        $res = DT_Posts::search_viewable_post( "contacts", [ "assigned_to" => [ -1, "-2" ] ], false );
        $this->assertEquals( $res["total"], $all["total"] - 1 );
        //search for posts with no values set for field x
        $res = DT_Posts::search_viewable_post( "contacts", [ "assigned_to" => [] ], false );
        $this->assertContains( $empty_contact["ID"], self::map_ids( $res["posts"] ) );
        $this->assertNotContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        //bad request
        $res = DT_Posts::search_viewable_post( "contacts", [ "assigned_to" => 1 ], false );
        $this->assertWPError( $res );

        /**
         * Date fields
         */
        $baptism = DT_Posts::create_post( 'contacts', [ "name" => "x", "baptism_date" => "1980-01-03" ], true, false );
        $range = DT_Posts::search_viewable_post( "contacts", [ "baptism_date" => [ "start" => "1980-01-02", "end" => "1980-01-04" ] ], false );
        $exact = DT_Posts::search_viewable_post( "contacts", [ "baptism_date" => [ "start" => "1980-01-03", "end" => "1980-01-03" ] ], false );
        $start = DT_Posts::search_viewable_post( "contacts", [ "baptism_date" => [ "start" => "1980-01-03" ] ], false );
        $end = DT_Posts::search_viewable_post( "contacts", [ "baptism_date" => [ "end" => "1980-01-03" ] ], false );
        $this->assertEquals( $baptism["ID"], $range["posts"][0]->ID );
        $this->assertEquals( $baptism["ID"], $exact["posts"][0]->ID );
        $this->assertGreaterThan( 1, $start["total"] );
        $this->assertEquals( $baptism["ID"], $end["posts"][0]->ID );

        $contact = DT_Posts::create_post( "contacts", ["name" => 'x', "post_date" => "2003-01-02" ], true, false );
        $res = DT_Posts::search_viewable_post( "contacts", [ "post_date" => [ "start" => "2003-01-02", "end" => "2003-01-02" ] ], false );
        $this->assertCount( 1, $res["posts"] );
        $this->assertEquals( $contact["ID"], $res["posts"][0]->ID );
        $contact = DT_Posts::create_post( "contacts", ["name" => 'x', "post_date" => "2002-01-02" ], true, false );
        $res = DT_Posts::search_viewable_post( "contacts", [ "name" => "x", [ "post_date" => [ "start" => "2002-01-02", "end" => "2002-01-02" ] ] ], false );
        $this->assertCount( 1, $res["posts"] );
        $this->assertEquals( $contact["ID"], $res["posts"][0]->ID );
        $contact = DT_Posts::create_post( "contacts", ["name" => 'x', "baptism_date" => "2003-01-02" ], true, false );
        $res = DT_Posts::search_viewable_post( "contacts", [ "baptism_date" => [ "start" => "2003-01-02", "end" => "2003-01-02" ] ], false );
        $this->assertCount( 1, $res["posts"] );
        $this->assertEquals( $contact["ID"], $res["posts"][0]->ID );
        //search for posts with no values set for field x
        $res = DT_Posts::search_viewable_post( "contacts", [ "baptism_date" => [] ], false );
        $this->assertContains( $empty_contact["ID"], self::map_ids( $res["posts"] ) );
        $this->assertNotContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        //bad request
        $res = DT_Posts::search_viewable_post( "contacts", [ "baptism_date" => "1980-01-03" ], false );
        $this->assertWPError( $res );


        /**
         * Boolean Fields
         */
        $group = DT_Posts::create_post( "groups", $this->sample_group, true, false );
        $update_needed = DT_Posts::create_post( 'contacts', [ "name" => "x", "requires_update" => true, "groups" => [ "values" => [ [ "value" => $group["ID"] ] ] ] ], true, false );
        $update_not_needed = DT_Posts::create_post( 'contacts', [ "name" => "x", "requires_update" => false, "groups" => [ "values" => [ [ "value" => $group["ID"] ] ] ] ], true, false );
        $bool1 = DT_Posts::search_viewable_post( "contacts", [ "requires_update" => [ true ] , "groups" => [ $group["ID"] ] ], false );
        $bool2 = DT_Posts::search_viewable_post( "contacts", [ "requires_update" => [ "1" ] , "groups" => [ $group["ID"] ] ], false );
        $bool3 = DT_Posts::search_viewable_post( "contacts", [ "requires_update" => [ false ] , "groups" => [ $group["ID"] ] ], false );
        $bool4 = DT_Posts::search_viewable_post( "contacts", [ "requires_update" => [ "0" ] , "groups" => [ $group["ID"] ] ], false );
        $this->assertEquals( $update_needed["ID"], $bool1["posts"][0]->ID );
        $this->assertEquals( $update_needed["ID"], $bool2["posts"][0]->ID );
        $this->assertEquals( $update_not_needed["ID"], $bool3["posts"][0]->ID );
        $this->assertEquals( $update_not_needed["ID"], $bool4["posts"][0]->ID );
        //search for posts with no values set for field x
        $res = DT_Posts::search_viewable_post( "contacts", [ "requires_update" => [] ], false );
        $this->assertContains( $empty_contact["ID"], self::map_ids( $res["posts"] ) );
        $this->assertNotContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        //false also includes contacts with the field no set.
        $res = DT_Posts::search_viewable_post( "contacts", [ "requires_update" => [ false ] ], false );
        $this->assertContains( $contact1["ID"], self::map_ids( $res["posts"] ) );
        //bad request
        $res = DT_Posts::search_viewable_post( "contacts", [ "requires_update" => true ], false );
        $this->assertWPError( $res );


        /**
         * communication_channels
         */
        $phone_contact = DT_Posts::create_post( 'contacts', [ "name" => "x", "contact_phone" => [ "values" => [ [ "value" => "798456781" ] ] ] ], true, false );
        $phone = DT_Posts::search_viewable_post( "contacts", [ "contact_phone" => [ "798456780" ] ], false );
        $this->assertContains( $sample_contact["ID"], self::map_ids( $phone["posts"] ) );
        $phone = DT_Posts::search_viewable_post( "contacts", [ "contact_phone" => [ "79845678" ] ], false );
        $this->assertContains( $phone_contact["ID"], self::map_ids( $phone["posts"] ) );
        $phone = DT_Posts::search_viewable_post( "contacts", [ "contact_phone" => [ "-798456780" ] ], false );
        $this->assertNotContains( $sample_contact["ID"], self::map_ids( $phone["posts"] ) );
        $phone = DT_Posts::search_viewable_post( "contacts", [ "contact_phone" => [ "^798456780" ] ], false );
        $this->assertContains( $sample_contact["ID"], self::map_ids( $phone["posts"] ) );
        $phone = DT_Posts::search_viewable_post( "contacts", [ "contact_phone" => [ "^79845678" ] ], false );
        $this->assertNotContains( $sample_contact["ID"], self::map_ids( $phone["posts"] ) );
        //search for posts with no values set for field x
        $res = DT_Posts::search_viewable_post( "contacts", [ "contact_phone" => [] ], false );
        $this->assertContains( $empty_contact["ID"], self::map_ids( $res["posts"] ) );
        $this->assertNotContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        //bad request
        $res = DT_Posts::search_viewable_post( "contacts", [ "contact_phone" => "79845678" ], false );
        $this->assertWPError( $res );


        /**
         * numbers
         */
        $res = DT_Posts::search_viewable_post( "groups", [ "member_count" => [ "number" => "5" ] ], false );
        $this->assertContains( $group1["ID"], self::map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "groups", [ "member_count" => [ "number" => "5", "operator" => ">=" ] ], false );
        $this->assertContains( $group1["ID"], self::map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "groups", [ "member_count" => [ "number" => "5", "operator" => "<" ] ], false );
        $this->assertNotContains( $group1["ID"], self::map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "groups", [ "member_count" => 5 ], false );
        $this->assertContains( $group1["ID"], self::map_ids( $res["posts"] ) );
        // search for posts with no values set for field x
        $res = DT_Posts::search_viewable_post( "groups", [ "member_count" => [] ], false );
        $this->assertContains( $empty_group["ID"], self::map_ids( $res["posts"] ) );
        $this->assertNotContains( $group1["ID"], self::map_ids( $res["posts"] ) );

        /**
         * text
         */
        $nick = DT_Posts::create_post( "contacts", [ "name" => 'a', 'nickname' => "Bob the teacher"], true, false );
        $res = DT_Posts::search_viewable_post( "contacts", [ "nickname" => [ "Bob the builder" ] ], false );
        $this->assertContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "nickname" => [ "build" ] ], false );
        $this->assertContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "nickname" => [ "something", "build" ] ], false );
        $this->assertContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "nickname" => [ "something" ] ], false );
        $this->assertNotContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "nickname" => [ "-build" ] ], false );
        $this->assertNotContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        $this->assertContains( $contact1["ID"], self::map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "nickname" => [ "-this name does not exist" ] ], false );
        $this->assertContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "nickname" => [ "-build", "bob" ] ], false );
        $this->assertContains( $nick["ID"], self::map_ids( $res["posts"] ) );
        $this->assertCount( 1, $res["posts"] );
        $res = DT_Posts::search_viewable_post( "contacts", [ "nickname" => [ "^build" ] ], false );
        $this->assertCount( 0, $res["posts"] );
        $res = DT_Posts::search_viewable_post( "contacts", [ "nickname" => [ "^Bob the teacher" ] ], false );
        $this->assertCount( 1, $res["posts"] );
        $this->assertContains( $nick["ID"], self::map_ids( $res["posts"] ) );
        //search for posts with no values set for field x
        $res = DT_Posts::search_viewable_post( "contacts", [ "nickname" => [] ], false );
        $this->assertContains( $empty_contact["ID"], self::map_ids( $res["posts"] ) );
        $this->assertNotContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        //bad request
        $res = DT_Posts::search_viewable_post( "contacts", [ "nickname" => "Bob" ], false );
        $this->assertWPError( $res );
        //name
        $res = DT_Posts::search_viewable_post( "contacts", [ "name" => [ "Bob" ] ], false );
        $this->assertContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "name" => "Bob" ], false );
        $this->assertContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ [ "name" => "Bob" ] ], false );
        $this->assertContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ [ "name" => [ "Bob" ] ] ], false );
        $this->assertContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "name" => [ "^Bo" ] ], false );
        $this->assertCount( 0, $res["posts"] );
        $res = DT_Posts::search_viewable_post( "contacts", [ "name" => [ "^Bob" ] ], false );
        $this->assertContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );


        /**
         * key_select
         */
        $paused = DT_Posts::create_post( "contacts", [ "name" => "x", "overall_status" => 'paused' ], true, false );
        $res = DT_Posts::search_viewable_post( "contacts", [ "overall_status" => [ "paused", "active" ] ], false );
        $this->assertContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        $this->assertContains( $paused["ID"], self::map_ids( $res["posts"] ) );
        //negative values
        $res = DT_Posts::search_viewable_post( "contacts", [ "overall_status" => [ "-active", "paused" ] ], false );
        $this->assertNotContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        $this->assertContains( $paused["ID"], self::map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "overall_status" => [ "-closed" ] ], false );
        $this->assertContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        //empty search = with none of the field
        $res = DT_Posts::search_viewable_post( "contacts", [ "overall_status" => [] ], false );
        $this->assertNotEmpty( $res["posts"] );
        $this->assertNotContains( $paused["ID"], self::map_ids( $res["posts"] ) );
        $this->assertNotContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        $this->assertContains( $empty_contact["ID"], self::map_ids( $res["posts"] ) );
        //bad request
        $res = DT_Posts::search_viewable_post( "contacts", [ "overall_status" => "active" ], false );
        $this->assertWPError( $res );


        /*
         * multi_select
         */
        $in_group = DT_Posts::create_post( "contacts", [ "name" => "x", "milestones" => [ "values" => [ [ "value" =>'milestone_in_group' ] ] ] ], true, false );
        $res = DT_Posts::search_viewable_post( "contacts", [ "milestones" => [ "milestone_has_bible" ] ], false );
        $this->assertContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        $this->assertNotContains( $in_group["ID"], self::map_ids( $res["posts"] ) );
        //negative filter
        $res = DT_Posts::search_viewable_post( "contacts", [ "milestones" => [ "-milestone_has_bible" ] ], false );
        $this->assertNotContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        $this->assertContains( $in_group["ID"], self::map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "milestones" => [ "-milestone_planting" ] ], false );
        $this->assertContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        //empty search = with none of the field
        $res = DT_Posts::search_viewable_post( "contacts", [ "milestones" => [] ], false );
        $this->assertNotEmpty( $res["posts"] );
        $this->assertNotContains( $in_group["ID"], self::map_ids( $res["posts"] ) );
        $this->assertNotContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        $this->assertContains( $empty_contact["ID"], self::map_ids( $res["posts"] ) );
        //bad request
        $res = DT_Posts::search_viewable_post( "contacts", [ "milestones" => "active" ], false );
        $this->assertWPError( $res );


        /**
         * Default fields
         */
        $contact = DT_Posts::create_post( "contacts", [ "name" => 'dh39ent' ], true, false );
        $res = DT_Posts::search_viewable_post( "contacts", [ "name" => [ "dh39ent" ] ], false );
        $this->assertEquals( $contact["ID"], $res["posts"][0]->ID );
        $this->assertCount( 1, $res["posts"] );

        /**
         * weird
         */
        $res = DT_Posts::search_viewable_post( "contacts", [ "some_random_key" => [ $group1["ID"], $group2["ID"] ] ], false );
        $this->assertWPError( $res );
        $res = DT_Posts::search_viewable_post( "contacts_bad_type", [ "groups" => [ $group1["ID"], $group2["ID"] ] ], false );
        $this->assertWPError( $res );
        $res = DT_Posts::search_viewable_post( "contacts", [ "member_count" => [] ], false );
        $this->assertWPError( $res );


        /**
         * Search input
         */
        $res = DT_Posts::search_viewable_post( "contacts", [ "text" => "Bob" ], false );
        $this->assertContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "text" => "ob" ], false );
        $this->assertContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "text" => "798456780" ], false );
        $this->assertContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "text" => "6780" ], false );
        $this->assertContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "text" => "example.com" ], false );
        $this->assertContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "text" => "bob@example.com" ], false );
        $this->assertContains( $sample_contact["ID"], self::map_ids( $res["posts"] ) );


        /**
         * structure
         * AND / OR layers
         */
        $group = DT_Posts::create_post( "groups", [ "name" => 'this_is_a_group1' ], true, false );
        $c1 = DT_Posts::create_post( "contacts", [ "name" => 'this_is_a_test1', "assigned_to" => 1, "gender" => "male", "groups" => [ "values" => [ [ "value" => $group["ID"] ] ] ] ], true, false );
        DT_Posts::create_post( "contacts", [ "name" => 'this_is_a_test2', "assigned_to" => 1, "gender" => "male", "groups" => [ "values" => [ [ "value" => $group["ID"] ] ] ] ], true, false );
        //name1 and name 2
        $res1 = DT_Posts::search_viewable_post( "contacts", [ [ "name" => [ "this_is_a_test1" ] ], [ "name" => [ "this_is_a_test2" ] ] ], false );
        //with fields key
        $res2 = DT_Posts::search_viewable_post( "contacts", [ "fields" => [ [ "name" => [ "this_is_a_test1" ] ], [ "name" => [ "this_is_a_test2" ] ] ] ], false );
        $this->assertCount( 0, $res1["posts"] );
        $this->assertCount( 0, $res2["posts"] );
        $this->assertSame( $res1, $res2 );
        //name1 or name 2
        $res1 = DT_Posts::search_viewable_post( "contacts", [ [ [ "name" => [ "this_is_a_test1" ] ], [ "name" => [ "this_is_a_test2" ] ] ] ], false );
        //with fields key
        $res2 = DT_Posts::search_viewable_post( "contacts", [ "fields" => [ [ [ "name" => [ "this_is_a_test1" ] ], [ "name" => [ "this_is_a_test2" ] ] ] ] ], false );
        $this->assertCount( 2, $res1["posts"] );
        $this->assertCount( 2, $res2["posts"] );
        $this->assertEquals( $res1, $res2 );

        //more ANDs
        $res1 = DT_Posts::search_viewable_post( "contacts", [ "name" => [ "this_is_a_test1" ], "gender" => [ "male" ], "groups" => [ $group["ID"] ]  ], false );
        $res2 = DT_Posts::search_viewable_post( "contacts", [ "fields" => [ "name" => [ "this_is_a_test1" ], "gender" => [ "male" ], "groups" => [ $group["ID"] ] ] ], false );
        $this->assertCount( 1, $res2["posts"] );
        $this->assertEquals( $res1, $res2 );

        //mixing ANDs and ORs
        $res1 = DT_Posts::search_viewable_post( "contacts", [ [ "name" => [ "this_is_a_test1" ], "gender" => [ "male" ], ], "groups" => [ $group["ID"] ]  ], false );
        $res2 = DT_Posts::search_viewable_post( "contacts", [ "fields" => [ [ "name" => [ "this_is_a_test1" ], "gender" => [ "male" ]], [ "groups" => [ $group["ID"] ] ] ] ], false );
        $this->assertCount( 2, $res2["posts"] );
        $this->assertEquals( $res1, $res2 );
    }
}
