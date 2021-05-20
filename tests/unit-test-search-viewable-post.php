<?php
/**
 * Class PostsTest
 *
 * @package Disciple.Tools
 */


class DT_Posts_DT_Posts_Search_Viewable_Posts extends WP_UnitTestCase {

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
        $this->assertNotContains( $contact1["ID"], $this->map_ids( $res["posts"] ) );
        $this->assertContains( $contact2["ID"], $this->map_ids( $res["posts"] ) );
        $this->assertContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        //search for posts with no values set for field x
        $res = DT_Posts::search_viewable_post( "contacts", [ "groups" => [] ], false );
        $this->assertContains( $empty_contact["ID"], $this->map_ids( $res["posts"] ) );
        $this->assertNotContains( $contact1["ID"], $this->map_ids( $res["posts"] ) );
        // search for all posts with a value set for field x
        $res = DT_Posts::search_viewable_post( "contacts", [ "groups" => [ '*' ] ], false );
        $this->assertNotContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        $this->assertContains( $contact1["ID"], $this->map_ids( $res["posts"] ) );
        $this->assertContains( $contact2["ID"], $this->map_ids( $res["posts"] ) );
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
        $this->assertContains( $empty_contact["ID"], $this->map_ids( $res["posts"] ) );
        $this->assertNotContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
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
        $this->assertContains( $empty_contact["ID"], $this->map_ids( $res["posts"] ) );
        $this->assertNotContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
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
        $this->assertContains( $empty_contact["ID"], $this->map_ids( $res["posts"] ) );
        $this->assertNotContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
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
        $this->assertContains( $empty_contact["ID"], $this->map_ids( $res["posts"] ) );
        $this->assertNotContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        //false also includes contacts with the field no set.
        $res = DT_Posts::search_viewable_post( "contacts", [ "requires_update" => [ false ] ], false );
        $this->assertContains( $contact1["ID"], $this->map_ids( $res["posts"] ) );
        //bad request
        $res = DT_Posts::search_viewable_post( "contacts", [ "requires_update" => true ], false );
        $this->assertWPError( $res );


        /**
         * communication_channels
         */
        $phone_contact = DT_Posts::create_post( 'contacts', [ "name" => "x", "contact_phone" => [ "values" => [ [ "value" => "798456781" ] ] ] ], true, false );
        $phone = DT_Posts::search_viewable_post( "contacts", [ "contact_phone" => [ "798456780" ] ], false );
        $this->assertContains( $sample_contact["ID"], $this->map_ids( $phone["posts"] ) );
        $phone = DT_Posts::search_viewable_post( "contacts", [ "contact_phone" => [ "79845678" ] ], false );
        $this->assertContains( $phone_contact["ID"], $this->map_ids( $phone["posts"] ) );
        $phone = DT_Posts::search_viewable_post( "contacts", [ "contact_phone" => [ "-798456780" ] ], false );
        $this->assertNotContains( $sample_contact["ID"], $this->map_ids( $phone["posts"] ) );
        $phone = DT_Posts::search_viewable_post( "contacts", [ "contact_phone" => [ "^798456780" ] ], false );
        $this->assertContains( $sample_contact["ID"], $this->map_ids( $phone["posts"] ) );
        $phone = DT_Posts::search_viewable_post( "contacts", [ "contact_phone" => [ "^79845678" ] ], false );
        $this->assertNotContains( $sample_contact["ID"], $this->map_ids( $phone["posts"] ) );
        //search for posts with no values set for field x
        $res = DT_Posts::search_viewable_post( "contacts", [ "contact_phone" => [] ], false );
        $this->assertContains( $empty_contact["ID"], $this->map_ids( $res["posts"] ) );
        $this->assertNotContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        //bad request
        $res = DT_Posts::search_viewable_post( "contacts", [ "contact_phone" => "79845678" ], false );
        $this->assertWPError( $res );


        /**
         * numbers
         */
        $res = DT_Posts::search_viewable_post( "groups", [ "member_count" => [ "number" => "5" ] ], false );
        $this->assertContains( $group1["ID"], $this->map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "groups", [ "member_count" => [ "number" => "5", "operator" => ">=" ] ], false );
        $this->assertContains( $group1["ID"], $this->map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "groups", [ "member_count" => [ "number" => "5", "operator" => "<" ] ], false );
        $this->assertNotContains( $group1["ID"], $this->map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "groups", [ "member_count" => 5 ], false );
        $this->assertContains( $group1["ID"], $this->map_ids( $res["posts"] ) );
        // search for posts with no values set for field x
        $res = DT_Posts::search_viewable_post( "groups", [ "member_count" => [] ], false );
        $this->assertContains( $empty_group["ID"], $this->map_ids( $res["posts"] ) );
        $this->assertNotContains( $group1["ID"], $this->map_ids( $res["posts"] ) );

        /**
         * text
         */
        $nick = DT_Posts::create_post( "contacts", [ "name" => 'a', 'nickname' => "Bob the teacher"], true, false );
        $res = DT_Posts::search_viewable_post( "contacts", [ "nickname" => [ "Bob the builder" ] ], false );
        $this->assertContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "nickname" => [ "build" ] ], false );
        $this->assertContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "nickname" => [ "something", "build" ] ], false );
        $this->assertContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "nickname" => [ "something" ] ], false );
        $this->assertNotContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "nickname" => [ "-build" ] ], false );
        $this->assertNotContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        $this->assertContains( $contact1["ID"], $this->map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "nickname" => [ "-this name does not exist" ] ], false );
        $this->assertContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "nickname" => [ "-build", "bob" ] ], false );
        $this->assertContains( $nick["ID"], $this->map_ids( $res["posts"] ) );
        $this->assertCount( 1, $res["posts"] );
        $res = DT_Posts::search_viewable_post( "contacts", [ "nickname" => [ "^build" ] ], false );
        $this->assertCount( 0, $res["posts"] );
        $res = DT_Posts::search_viewable_post( "contacts", [ "nickname" => [ "^Bob the teacher" ] ], false );
        $this->assertCount( 1, $res["posts"] );
        $this->assertContains( $nick["ID"], $this->map_ids( $res["posts"] ) );
        //search for posts with no values set for field x
        $res = DT_Posts::search_viewable_post( "contacts", [ "nickname" => [] ], false );
        $this->assertContains( $empty_contact["ID"], $this->map_ids( $res["posts"] ) );
        $this->assertNotContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        //bad request
        $res = DT_Posts::search_viewable_post( "contacts", [ "nickname" => "Bob" ], false );
        $this->assertWPError( $res );
        //name
        $res = DT_Posts::search_viewable_post( "contacts", [ "name" => [ "Bob" ] ], false );
        $this->assertContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "name" => "Bob" ], false );
        $this->assertContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ [ "name" => "Bob" ] ], false );
        $this->assertContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ [ "name" => [ "Bob" ] ] ], false );
        $this->assertContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "name" => [ "^Bo" ] ], false );
        $this->assertCount( 0, $res["posts"] );
        $res = DT_Posts::search_viewable_post( "contacts", [ "name" => [ "^Bob" ] ], false );
        $this->assertContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );


        /**
         * key_select
         */
        $paused = DT_Posts::create_post( "contacts", [ "name" => "x", "overall_status" => 'paused' ], true, false );
        $res = DT_Posts::search_viewable_post( "contacts", [ "overall_status" => [ "paused", "active" ] ], false );
        $this->assertContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        $this->assertContains( $paused["ID"], $this->map_ids( $res["posts"] ) );
        //negative values
        $res = DT_Posts::search_viewable_post( "contacts", [ "overall_status" => [ "-active", "paused" ] ], false );
        $this->assertNotContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        $this->assertContains( $paused["ID"], $this->map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "overall_status" => [ "-closed" ] ], false );
        $this->assertContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        //empty search = with none of the field
        $res = DT_Posts::search_viewable_post( "contacts", [ "overall_status" => [] ], false );
        $this->assertNotEmpty( $res["posts"] );
        $this->assertNotContains( $paused["ID"], $this->map_ids( $res["posts"] ) );
        $this->assertNotContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        $this->assertContains( $empty_contact["ID"], $this->map_ids( $res["posts"] ) );
        //bad request
        $res = DT_Posts::search_viewable_post( "contacts", [ "overall_status" => "active" ], false );
        $this->assertWPError( $res );

        //check that the paused contact doesn't show up in the "none" search
        $res = DT_Posts::search_viewable_post( "contacts", [ "overall_status" => [ 'none' ] ], false );
        $this->assertNotContains( $paused["ID"], self::map_ids( $res["posts"] ) );
        delete_post_meta( $paused["ID"], "overall_status" );
        //check that the contact does show up in the "none" search not that the meta is removed
        $res = DT_Posts::search_viewable_post( "contacts", [ "overall_status" => [ 'none' ] ], false );
        $this->assertContains( $paused["ID"], self::map_ids( $res["posts"] ) );

        /*
         * multi_select
         */
        $in_group = DT_Posts::create_post( "contacts", [ "name" => "x", "milestones" => [ "values" => [ [ "value" =>'milestone_in_group' ] ] ] ], true, false );
        $res = DT_Posts::search_viewable_post( "contacts", [ "milestones" => [ "milestone_has_bible" ] ], false );
        $this->assertContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        $this->assertNotContains( $in_group["ID"], $this->map_ids( $res["posts"] ) );
        //negative filter
        $res = DT_Posts::search_viewable_post( "contacts", [ "milestones" => [ "-milestone_has_bible" ] ], false );
        $this->assertNotContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        $this->assertContains( $in_group["ID"], $this->map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "milestones" => [ "-milestone_planting" ] ], false );
        $this->assertContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        //empty search = with none of the field
        $res = DT_Posts::search_viewable_post( "contacts", [ "milestones" => [] ], false );
        $this->assertNotEmpty( $res["posts"] );
        $this->assertNotContains( $in_group["ID"], $this->map_ids( $res["posts"] ) );
        $this->assertNotContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        $this->assertContains( $empty_contact["ID"], $this->map_ids( $res["posts"] ) );
        //bad request
        $res = DT_Posts::search_viewable_post( "contacts", [ "milestones" => "active" ], false );
        $this->assertWPError( $res );

        /*
         * tags
         */
        $in_group = DT_Posts::create_post( "contacts", [ "name" => "x", "tags" => [ "values" => [ [ "value" =>'in_group1' ] ] ] ], true, false );
        $res = DT_Posts::search_viewable_post( "contacts", [ "tags" => [ "tag1" ] ], false );
        $this->assertContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        $this->assertNotContains( $in_group["ID"], $this->map_ids( $res["posts"] ) );
        //negative filter
        $res = DT_Posts::search_viewable_post( "contacts", [ "tags" => [ "-tag1" ] ], false );
        $this->assertNotContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        $this->assertContains( $in_group["ID"], $this->map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "tags" => [ "-in_group1" ] ], false );
        $this->assertContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        //empty search = with none of the field
        $res = DT_Posts::search_viewable_post( "contacts", [ "tags" => [] ], false );
        $this->assertNotEmpty( $res["posts"] );
        $this->assertNotContains( $in_group["ID"], $this->map_ids( $res["posts"] ) );
        $this->assertNotContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        $this->assertContains( $empty_contact["ID"], $this->map_ids( $res["posts"] ) );
        //bad request
        $res = DT_Posts::search_viewable_post( "contacts", [ "tags" => "in_group1" ], false );
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
        $this->assertContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "text" => "ob" ], false );
        $this->assertContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "text" => "798456780" ], false );
        $this->assertContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "text" => "6780" ], false );
        $this->assertContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "text" => "example.com" ], false );
        $this->assertContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );
        $res = DT_Posts::search_viewable_post( "contacts", [ "text" => "bob@example.com" ], false );
        $this->assertContains( $sample_contact["ID"], $this->map_ids( $res["posts"] ) );


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
