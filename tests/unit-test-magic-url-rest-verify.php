<?php
/**
 * Class Magic_URL_Rest_Verify_Test
 *
 * @package Disciple.Tools
 */


class Magic_URL_Rest_Verify_Test extends WP_UnitTestCase {

    private $root = 'mltest';
    private $contact_id;

    public function set_up() {
        parent::set_up();
        add_filter( 'dt_magic_url_register_types', [ $this, 'register_types' ] );
        $this->contact_id = self::factory()->post->create( [ 'post_type' => 'contacts' ] );
        update_post_meta( $this->contact_id, 'mltest_type_a_magic_key', 'key_a' );
        update_post_meta( $this->contact_id, 'mltest_type_b_magic_key', 'key_b' );
        update_post_meta( $this->contact_id, 'mltest_type_b_magic_key_1700000000', 'key_b_instance' );
        update_post_meta( $this->contact_id, 'overall_status', 'mltest_status' );
    }

    public function tear_down() {
        remove_filter( 'dt_magic_url_register_types', [ $this, 'register_types' ] );
        unset( $_SERVER['REQUEST_URI'] );
        parent::tear_down();
    }

    public function register_types( $types ) {
        foreach ( [ 'type_a', 'type_b' ] as $type ) {
            $types[ $this->root ][ $type ] = [
                'root' => $this->root,
                'type' => $type,
                'meta_key' => $this->root . '_' . $type . '_magic_key',
                'actions' => [ '' => 'Manage' ],
                'post_type' => 'contacts',
                'instance_id' => '',
            ];
        }
        return $types;
    }

    private function verify( $type, $meta_key, $public_key, $instance_id = '' ) {
        $_SERVER['HTTP_HOST'] = wp_parse_url( get_site_url(), PHP_URL_HOST );
        $_SERVER['REQUEST_URI'] = wp_parse_url( get_site_url(), PHP_URL_PATH ) . '/wp-json/' . $this->root . '/v1/' . $type;
        $request = new WP_REST_Request( 'POST', '/' . $this->root . '/v1/' . $type );
        $request->set_body_params( [
            'parts' => [
                'root' => $this->root,
                'type' => $type,
                'meta_key' => $meta_key,
                'public_key' => $public_key,
                'post_id' => $this->contact_id,
                'instance_id' => $instance_id,
            ],
        ] );
        $magic = new DT_Magic_URL( $this->root );
        return $magic->verify_rest_endpoint_permissions_on_post( $request );
    }

    public function test_registered_meta_key_passes() {
        $this->assertTrue( $this->verify( 'type_b', 'mltest_type_b_magic_key', 'key_b' ) );
    }

    public function test_instance_meta_key_passes() {
        $this->assertTrue( $this->verify( 'type_b', 'mltest_type_b_magic_key_1700000000', 'key_b_instance', '1700000000' ) );
    }

    public function test_instance_meta_key_without_instance_id_fails() {
        $this->assertFalse( $this->verify( 'type_b', 'mltest_type_b_magic_key_1700000000', 'key_b_instance' ) );
    }

    public function test_other_type_key_fails() {
        $this->assertFalse( $this->verify( 'type_b', 'mltest_type_a_magic_key', 'key_a' ) );
    }

    public function test_arbitrary_meta_key_fails() {
        $this->assertFalse( $this->verify( 'type_b', 'overall_status', 'mltest_status' ) );
    }
}
