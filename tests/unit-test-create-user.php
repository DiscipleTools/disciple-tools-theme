<?php

class CreateUserTest extends WP_UnitTestCase {

    public function set_up() {
        parent::set_up();
        update_option( 'dt_user_invite_setting', true );
        $multiplier_id = wp_create_user( 'inviter', 'test', 'inviter@example.com' );
        get_user_by( 'id', $multiplier_id )->set_role( 'multiplier' );
        wp_set_current_user( $multiplier_id );
    }

    public function test_optional_fields_cannot_write_capabilities() {
        global $wpdb;
        $user_id = Disciple_Tools_Users::create_user( 'invitee', 'invitee@example.com', 'Invitee', [ 'multiplier' ], null, null, false, null, [
            $wpdb->prefix . 'capabilities' => [ 'administrator' => true ],
            $wpdb->prefix . 'user_level' => 10,
            'first_name' => 'Ann',
        ] );

        $this->assertNotWPError( $user_id );
        $user = get_user_by( 'id', $user_id );
        $this->assertNotContains( 'administrator', $user->roles );
        $this->assertContains( 'multiplier', $user->roles );
        $this->assertEmpty( get_user_meta( $user_id, $wpdb->prefix . 'user_level', true ) );
        $this->assertSame( 'Ann', get_user_meta( $user_id, 'first_name', true ) );
    }

    public function test_optional_fields_not_applied_to_existing_network_user() {
        $existing_id = wp_create_user( 'networkuser', 'test', 'networkuser@example.com' );
        update_user_meta( $existing_id, 'first_name', 'Original' );
        remove_user_from_blog( $existing_id, get_current_blog_id() );

        $user_id = Disciple_Tools_Users::create_user( 'networkuser', 'networkuser@example.com', 'Network User', [ 'multiplier' ], null, null, false, null, [
            'first_name' => 'Changed',
        ] );

        $this->assertSame( $existing_id, (int) $user_id );
        $this->assertSame( 'Original', get_user_meta( $existing_id, 'first_name', true ) );
    }
}
