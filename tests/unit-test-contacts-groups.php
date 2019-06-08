<?php
/**
 * Class SampleTest
 *
 * @package Disciple_Tools_Theme
 */

/**
 * Sample test case.
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

}
