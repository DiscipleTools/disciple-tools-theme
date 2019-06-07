<?php
/**
 * Class SampleTest
 *
 * @package Disciple_Tools_Theme
 */

/**
 * Sample test case.
 */


class SiteLinkTest extends WP_UnitTestCase {

    public $sample_contact = [
        'title' => 'Bob',
        'overall_status' => 'active',
    ];

    /**
     * A single example test.
     */
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

    public function test_create(){
        $current_user = wp_get_current_user();
        $current_user->remove_all_caps();

        // try creating a contact with no permissions
        $create_with_no_permissions = DT_Posts::create_post( 'contacts', $this->sample_contact );
        $this->assertWPError( $create_with_no_permissions );

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

    public function test_no_permissions(){
        $this->assertSame( true, true );

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

    public static function tearDownAfterClass(){
        global $wpdb;
        $wpdb->query( "DROP TABLE IF EXISTS $wpdb->dt_activity_log" );
        $wpdb->query( "DROP TABLE IF EXISTS $wpdb->dt_geonames" );
        $wpdb->query( "DROP TABLE IF EXISTS $wpdb->dt_notifications" );
        $wpdb->query( "DROP TABLE IF EXISTS $wpdb->dt_reports" );
        $wpdb->query( "DROP TABLE IF EXISTS $wpdb->dt_reportmeta" );
        $wpdb->query( "DROP TABLE IF EXISTS $wpdb->dt_reportmeta" );
        $wpdb->query( "DROP TABLE IF EXISTS $wpdb->dt_share" );
    }
}
