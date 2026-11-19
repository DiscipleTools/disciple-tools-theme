<?php

/**
 * @testdox DT_Login_Fields::update
 */
class DT_Login_Fields_Update_Test extends WP_UnitTestCase {

    private function set_current_user( $role, $super_admin = false ){
        $user_id = wp_create_user( 'login_fields_' . $role . ( $super_admin ? '_super' : '' ), 'test', 'login_fields_' . $role . ( $super_admin ? '_super' : '' ) . '@example.com' );
        $user = get_user_by( 'id', $user_id );
        $user->set_role( $role );
        if ( $super_admin ){
            grant_super_admin( $user_id );
        }
        wp_set_current_user( $user_id );
    }

    public function test_site_admin_cannot_change_network_fields(){
        if ( !is_multisite() ){
            $this->markTestSkipped( 'Network fields only exist on multisite' );
        }
        $this->set_current_user( 'dt_admin' );
        DT_Login_Fields::update( [ 'firebase_api_key' => 'site-admin-key', 'login_length' => '7' ] );

        $this->assertSame( '', DT_Login_Fields::get( 'firebase_api_key' ) );
        $this->assertSame( '7', DT_Login_Fields::get( 'login_length' ) );
    }

    public function test_super_admin_can_change_network_fields(){
        if ( !is_multisite() ){
            $this->markTestSkipped( 'Network fields only exist on multisite' );
        }
        $this->set_current_user( 'administrator', true );
        DT_Login_Fields::update( [ 'firebase_api_key' => 'super-admin-key' ] );

        $this->assertSame( 'super-admin-key', DT_Login_Fields::get( 'firebase_api_key' ) );
    }

    public function test_default_role_must_be_assignable(){
        $this->set_current_user( 'dt_admin' );

        DT_Login_Fields::update( [ 'default_role' => 'administrator' ] );
        $this->assertSame( 'multiplier', DT_Login_Fields::get( 'default_role' ) );

        DT_Login_Fields::update( [ 'default_role' => 'not_a_role' ] );
        $this->assertSame( 'multiplier', DT_Login_Fields::get( 'default_role' ) );

        DT_Login_Fields::update( [ 'default_role' => 'strategist' ] );
        $this->assertSame( 'strategist', DT_Login_Fields::get( 'default_role' ) );
    }
}
