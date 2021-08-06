<?php
/**
 * Class SiteLinkTest
 *
 * @package Disciple.Tools
 */


class SiteLinkTest extends WP_UnitTestCase {

    public $sample_contact = [
        'title' => 'Bob',
        'overall_status' => 'active',
        'milestones' => [ "values" => [ [ "value" => 'milestone_has_bible' ], [ "value" => "milestone_baptizing" ] ] ],
        'baptism_date' => "2018-12-31",
        "location_grid" => [ "values" => [ [ "value" => '3017382' ] ] ]
    ];



    public function test_create(){
        //set up site link with create permissions
        $site_link_id = self::create_site_link( 'create_contacts' );
        $site_link = get_post_custom( $site_link_id );
        $key = Site_Link_System::create_transfer_token_for_site( $site_link["site_key"][0] );
        $verified = Site_Link_System::verify_transfer_token( $key );
        //test site link permissions
        $this->assertTrue( current_user_can( "create_contacts" ) );
        $this->assertFalse( current_user_can( "update_any_contacts" ) );
        $this->assertFalse( current_user_can( "view_any_contacts" ) );

        // try creating a contact
        $create_with_permissions = DT_Posts::create_post( 'contacts', $this->sample_contact );
        $this->assertNotWPError( $create_with_permissions );
        $this->assertArrayHasKey( "ID", $create_with_permissions );
        $this->assertCount( 1, array_keys( $create_with_permissions ) );

        // test to make sure we can't updated the contact
        $update_contact = DT_Posts::update_post( 'contacts', $create_with_permissions["ID"], [ "title" => "test" ] );
        $this->assertWPError( $update_contact );
    }

    public function test_create_and_update() {

        //create a site link with create_update_contacts permission
        $site_link_id = self::create_site_link( 'create_update_contacts' );
        $site_link = get_post_custom( $site_link_id );
        $key = Site_Link_System::create_transfer_token_for_site( $site_link["site_key"][0] );
        $verified = Site_Link_System::verify_transfer_token( $key );

        //check permissions are set correctly
        $this->assertTrue( current_user_can( "create_contacts" ) );
        $this->assertTrue( current_user_can( "update_any_contacts" ) );
        $this->assertFalse( current_user_can( "view_any_contacts" ) );

        // try creating a post
        $create_with_permissions = DT_Posts::create_post( 'contacts', $this->sample_contact );
        $this->assertNotWPError( $create_with_permissions );

        // try updating the post
        $update_contact = DT_Posts::update_post( 'contacts', $create_with_permissions["ID"], [ "title" => "test" ] );
        $this->assertNotWPError( $update_contact );

        //check that we don't have access to the post because we don't have the view_any_contacts permission
        $this->assertArrayNotHasKey( "title", $update_contact );

        //check contact updated correctly
        if ( !is_wp_error( $update_contact ) ){
            $contact = DT_Posts::get_post( 'contacts', $create_with_permissions["ID"], false, false );
            $this->assertSame( "test", $contact["title"] );
        }
    }

    public function test_no_permissions(){
        $current_user = wp_get_current_user();
        $current_user->remove_all_caps();
        $this->assertFalse( current_user_can( "create_contacts" ) );

        // try creating a contact with no permissions
        $create_with_no_permissions = DT_Posts::create_post( 'contacts', $this->sample_contact );
        $this->assertWPError( $create_with_no_permissions );

        $contact = DT_Posts::create_post( 'contacts', $this->sample_contact, true, false );
        $contact_id = $contact["ID"];

        $contact = DT_Posts::get_post( 'contacts', $contact_id, false, false );

        //updating
        $update_with_no_permissions = DT_Posts::update_post( 'contacts', $contact_id, [ "title" => "test" ] );
        $this->assertWPError( $update_with_no_permissions );

        //listing
        $list_contacts = DT_Posts::search_viewable_post( 'contacts', [ 'text' => 'test' ] );
        $this->assertWPError( $list_contacts );

        //comments
        $comment_id = DT_Posts::add_post_comment( 'contacts', $contact_id, "hello", "comment", [], false );
        $this->assertFalse( DT_Posts::can_update( 'contacts', $contact_id ) );
        $this->assertWPError( DT_Posts::add_post_comment( 'contacts', $contact_id, "hello" ) );
        $this->assertWPError( DT_Posts::update_post_comment( $comment_id, "hello world" ) );
        $this->assertWPError( DT_Posts::delete_post_comment( $comment_id ) );
    }

    public function test_cross_post_type_scripting(){
        $user_id = wp_create_user( "user1", "test", "test@example.com" );
        wp_set_current_user( $user_id );
        $current_user = wp_get_current_user();
        $current_user->set_role( 'multiplier' );
        $contact1 = DT_Posts::create_post( 'contacts', $this->sample_contact );
        $group1 = DT_Posts::create_post( 'groups', [ "title" => "group1" ] );

        //try getting a comment but with post type: group
        $view_contact_as_group = DT_Posts::get_post( 'groups', $contact1["ID"] );
        $this->assertWPError( $view_contact_as_group );

        // try update contacts and groups with the wrong post type
        $update_contact_as_group = DT_Posts::update_post( 'groups', $contact1["ID"], [ "title" => "hacker" ] );
        $this->assertWPError( $update_contact_as_group );
        $update_group_as_contact = DT_Posts::update_post( 'contacts', $group1["ID"], [ "title" => "hacker" ] );
        $this->assertWPError( $update_group_as_contact );

        // try adding a comment with the wrong post type
        $comment_on_group_as_contact = DT_Posts::add_post_comment( 'contacts', $group1["ID"], "hacker" );
        $this->assertWPError( $comment_on_group_as_contact );

        //try using a connection with the wrong post type
        $contact2 = DT_Posts::create_post( 'contacts', $this->sample_contact );
        $wrong_connection = DT_Posts::update_post( 'contacts', $contact1["ID"], [
            'groups' => [ "values" => [ [ "value" => $contact2["ID"] ] ] ]
        ] );
        $shared_with = DT_Posts::get_shared_with( 'contacts', $contact1["ID"] );
        $good_connection = DT_Posts::update_post( 'contacts', $contact1["ID"], [
            'groups' => [ "values" => [ [ "value" => $group1["ID"] ] ] ]
        ] );
        $this->assertWPError( $wrong_connection );
        $this->assertNotWPError( $good_connection );
    }




    public static function create_site_link( $type = 'create_update_contacts' ) {
//        $current_site = get_current_site();
        $site1 = 'localhost/dt';
        $site2 = 'localhost/dt';
        $token = Site_Link_System::generate_token();
        $key = Site_Link_System::generate_key( $token, $site1, $site2 );
        $post = [
            "post_title"  => "Test",
            'post_type'   => "site_link_system",
            "post_status" => 'publish',
            "meta_input"  => [
                'site_key' => $key,
                'token' => $token,
                'site1' => $site1,
                'site2' => $site2,
                'type' => $type
            ],
        ];
        $site_link_id = wp_insert_post( $post );
        Site_Link_System::build_cached_option();
        return $site_link_id;

    }

    public static function setUpBeforeClass(){
        self::create_site_link();
    }
}
