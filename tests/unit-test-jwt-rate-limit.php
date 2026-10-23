<?php
/**
 * Class JWT_Rate_Limit_Test
 *
 * @package Disciple.Tools
 */


class JWT_Rate_Limit_Test extends WP_UnitTestCase {

    public function set_up() {
        parent::set_up();
        dt_jwt_throttle_dispatching_token( false );
        delete_transient( dt_jwt_throttle_key() );
    }

    public function tear_down() {
        dt_jwt_throttle_dispatching_token( false );
        delete_transient( dt_jwt_throttle_key() );
        unset( $_SERVER['REQUEST_URI'] );
        parent::tear_down();
    }

    private function dispatch_failed_token_request() {
        $request = new WP_REST_Request( 'POST', '/jwt-auth/v1/token' );
        $request->set_body_params( [
            'username' => 'jwt_throttle_unknown_user',
            'password' => 'wrong-password',
        ] );
        return rest_get_server()->dispatch( $request );
    }

    public function test_failure_counted_when_routed_via_encoded_rest_route() {
        $_SERVER['REQUEST_URI'] = '/?rest_route=%2Fjwt-auth%2Fv1%2Ftoken';
        $response = $this->dispatch_failed_token_request();

        $this->assertNotSame( 200, $response->get_status() );
        $this->assertSame( 1, (int) get_transient( dt_jwt_throttle_key() ) );
    }

    public function test_failure_outside_token_dispatch_not_counted() {
        $_SERVER['REQUEST_URI'] = '/wp-json/jwt-auth/v1/token';
        do_action( 'wp_login_failed', 'jwt_throttle_unknown_user' );

        $this->assertSame( 0, (int) get_transient( dt_jwt_throttle_key() ) );
    }

    public function test_blocked_after_max_failures() {
        $max = (int) apply_filters( 'dt_jwt_login_throttle_max_attempts', 10 );
        for ( $i = 0; $i < $max; $i++ ) {
            $this->dispatch_failed_token_request();
        }
        $response = $this->dispatch_failed_token_request();

        $this->assertSame( 429, $response->get_status() );
    }
}
