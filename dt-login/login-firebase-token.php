<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Verifies Firebase tokens according to their public keys
 */
class DT_Login_Firebase_Token {

    private $token;

    const PUBLIC_KEYS_CACHE = 'dt_login_public_keys';

    public function __construct( string $token ) {
        $this->token = $token;
    }

    /**
     * Verifies the token according to the firebase project id
     * @param string $project_id
     * @throws Error Firebase token payload is invalid.
     * @return array
     */
    public function verify( string $project_id ) : array {
        $keys = $this->get_public_keys();

        $payload = JWT::decode( $this->token, $keys );

        $is_valid = $this->validate_payload( $payload, $project_id );

        if ( !$is_valid ) {
            throw new Error( 'firebase token payload is invalid' );
        }

        return (array) $payload;
    }

    /**
     * Returns googles current public keys. Caches them until they need refetching.
     * @return array
     */
    private function get_public_keys() : array {

        $public_keys = get_transient( static::PUBLIC_KEYS_CACHE );
        if ( $public_keys ) {
            return $public_keys;
        }

        $public_key_url = 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com';

        $response = wp_remote_get( $public_key_url );
        $body = wp_remote_retrieve_body( $response );

        $keys = json_decode( $body, true );

        foreach ( $keys as $kid => $cert ) {
            $keys[$kid] = new Key( $cert, 'RS256' );
        }

        $cache_control = wp_remote_retrieve_header( $response, 'Cache-Control' );
        $matches = [];
        preg_match( '/max-age=(\d*),/', $cache_control, $matches );

        $max_age = (int) $matches[1];

        set_transient( static::PUBLIC_KEYS_CACHE, $keys, $max_age );

        return $keys;
    }

    /**
     * Validates the token payload according to the rules at https://firebase.google.com/docs/auth/admin/verify-id-tokens
     * @param object $payload
     * @param string $project_id
     * @return bool
     */
    private function validate_payload( object $payload, string $project_id ) : bool {

        /* Expiry must be in the future */
        if ( $payload->exp < time() ) {
            return false;
        }
        /* Issued at time must be in the past */
        if ( $payload->iat > time() ) {
            return false;
        }
        /* Audience must be the project id */
        if ( $payload->aud !== $project_id ) {
            return false;
        }
        if ( $payload->iss !== "https://securetoken.google.com/$project_id" ) {
            return false;
        }
        /* Subject must be non empty string and contains the uid of the user */
        if ( empty( $payload->sub ) ) {
            return false;
        }
        /* Authentication time must be in the past */
        if ( $payload->auth_time > time() ) {
            return false;
        }

        return true;
    }

}
