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
        "geonames" => [ "values" => [ [ "value" => '3017382' ] ] ]
    ];


    public function test_expected_fields(){
        $current_user = wp_get_current_user();
        $current_user->set_role( 'dispatcher' );
        $contact1 = DT_Posts::create_post( 'contacts', $this->sample_contact );
        $this->assertSame( 'Bob', $contact1['title'] );
        $this->assertSame( 'France', $contact1['geonames'][0]["label"] );
        $this->assertSame( '2018-12-31', $contact1["baptism_date"]["timestamp"] );

    }

    public function test_member_count(){
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
        $group1 = DT_Posts::create_post( 'groups', [ "title" => "group1" ] );
        $group2 = DT_Posts::create_post( 'groups', [ "title" => "group2" ] );
        $group3 = DT_Posts::create_post( 'groups', [ "title" => "group3" ] );
        $contact1 = DT_Posts::create_post( "contacts", [
            "title" => "bob",
            'milestones' => [ "values" => [ [ "value" => 'milestone_has_bible' ], [ "value" => "milestone_baptizing" ] ] ],
            'groups' => [ "values" => [ [ "value" => $group1["ID"] ], [ "value" => $group2["ID"] ] ] ],
            'geonames' => [ "values" => [ [ "value" => 3017382 ], [ "value" => 2921044 ] ] ],
            'contact_phone' => [ "values" => [ [ "value" => '123', "verified" => true ], [ "value" => "321" ] ] ]//@phpcs:ignore
        ] );
        $this->assertNotWPError( $contact1 );
        $this->assertSame( sizeof( $contact1["milestones"] ), 2 );
        $this->assertSame( sizeof( $contact1["groups"] ), 2 );
        $this->assertSame( sizeof( $contact1["geonames"] ), 2 );
        $this->assertSame( sizeof( $contact1["contact_phone"] ), 2 );

        //force values with update
        $phone_key = $contact1['contact_phone'][0]["key"];
        $contact1 = DT_Posts::update_post( 'contacts', $contact1["ID"], [
            'milestones' => [ "values" => [ [ "value" => 'milestone_has_bible' ], [ "value" => "milestone_sharing" ] ], "force_values" => true ], //@phpcs:ignore
            'groups' => [ "values" => [ [ "value" => $group1["ID"] ], [ "value" => $group3["ID"] ] ], "force_values" => true ], //@phpcs:ignore
            'geonames' => [ "values" => [ [ "value" => 3017382 ] ], "force_values" => true ], //@phpcs:ignore
            'contact_phone' => [ "values" => [ [ "key" => $phone_key, "value" => '456' ] ], "force_values" => true ], //@phpcs:ignore
        ] );
        $this->assertNotWPError( $contact1 );
        $this->assertSame( sizeof( $contact1["milestones"] ), 2 );
        $this->assertSame( sizeof( $contact1["groups"] ), 2 );
        $this->assertSame( sizeof( $contact1["geonames"] ), 1 );
        $this->assertSame( sizeof( $contact1["contact_phone"] ), 1 );
        $this->assertSame( $phone_key, $contact1['contact_phone'][0]["key"] );
        $this->assertSame( true, $contact1['contact_phone'][0]["verified"] ?? false );

        //remove all values with force_values
        $contact1 = DT_Posts::update_post( 'contacts', $contact1["ID"], [
            'milestones' => [ "values" => [], "force_values" => true ], //@phpcs:ignore
            'groups' => [ "values" => [], "force_values" => true ], //@phpcs:ignore
            'geonames' => [ "values" => [], "force_values" => true ], //@phpcs:ignore
            'contact_phone' => [ "values" => [], "force_values" => true ], //@phpcs:ignore
        ] );
        $this->assertNotWPError( $contact1 );
        $this->assertSame( sizeof( $contact1["milestones"] ?? [] ), 0 );
        $this->assertSame( sizeof( $contact1["groups"] ?? [] ), 0 );
        $this->assertSame( sizeof( $contact1["geonames"] ?? [] ), 0 );
        $this->assertSame( sizeof( $contact1["contact_phone"] ?? [] ), 0 );
    }


}
