<?php

/**
 * Login an existing user, or create and login a new one linking them to their firebase authentication
 *
 * This should only be used in the context of a verified firebase access token
 */
class DT_Login_User_Manager {

    private $firebase_auth;
    private $uid;
    private $email;
    private $name;
    private $identities;
    private $sign_in_provider;

    const DEFAULT_AUTH_SERVICE_ENDPOINT = 'wp-json/jwt-auth/v1/token';

    public function __construct( array $firebase_auth ) {
        $this->firebase_auth = $firebase_auth;
        $this->uid = $this->firebase_auth['user_id'];
        $this->email = $this->firebase_auth['email'] ?? null;
        $this->name = $this->firebase_auth['name'];
        $this->identities = (array) $this->firebase_auth['firebase']->identities;
        $this->sign_in_provider = $this->firebase_auth['firebase']->sign_in_provider;
        add_filter( 'dt_allow_rest_access', [ $this, 'authorize_url' ], 10, 1 );
    }

    public function authorize_url( $authorized ){

        if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), static::DEFAULT_AUTH_SERVICE_ENDPOINT ) !== false ) {
            $authorized = true;
        }

        return $authorized;
    }

    /**
     * Login the user, creating them if needed
     * @return mixed
     */
    public function login() {
        if ( !$this->user_exists() ) {

            if ( !dt_can_users_register() ) {
                throw new Exception( esc_html( _x( 'You are not registered. Contact the site admin to get a user account.', 'disciple_tools' ) ), 999 );
            }
            $this->create_user();
        } else {
            $this->update_user();
        }

        /* Login the user using the desired method. */
        $login_method = DT_Login_Fields::get_login_method();

        if ( DT_Login_Methods::MOBILE === $login_method ) {
            /* If mobile app, then do a login using the mobile app plugin */
            $response = $this->mobile_login();
        } else {
            /* Default to normal Wordpress login */
            $response = $this->wordpress_login();
        }

        return $response;
    }

    private function get_user(){
        $user = get_user_by( 'email', $this->email );
        if ( empty( $user ) ) {
            $user = get_user_by( 'login', $this->uid );
        }
        return $user;
    }

    private function user_exists() {
        return !empty( $this->get_user() );
    }

    private function create_user() {
        $password = wp_generate_password();

        $user_role = $this->get_default_role();
        $userdata = [
            'user_email' => $this->email,
            'user_login' => $this->uid,
            'user_pass' => $password,
            'display_name' => $this->name,
            'nickname' => $this->name,
            'role' => $user_role,
        ];

        $user_id = wp_insert_user( $userdata );

        $this->add_user_to_blog_if_needed( $user_id, $user_role ); // add user to site.

        $this->update_user_meta( $user_id );
    }

    private function update_user() {
        $user = $this->get_user();

        if ( !isset( $user->roles ) || !is_array( $user->roles ) || empty( $user_roles ) ) {
            $user_role = $this->get_default_role();
        } else {
            $user_role = $user->roles[0];
        }

        $this->add_user_to_blog_if_needed( $user->ID, $user_role ); // add user to site.

        $this->update_user_meta( $user->ID );
    }

    private function update_user_meta( int $user_id ) {
        update_user_meta( $user_id, 'firebase_uid', $this->uid );
        update_user_meta( $user_id, 'firebase_identities', $this->identities );

        $sign_in_providers = maybe_unserialize( get_user_meta( $user_id, 'sign_in_providers', true ) );

        if ( empty( $sign_in_providers ) ) {
            $sign_in_providers = [];
        }
        if ( !in_array( $this->sign_in_provider, $sign_in_providers ) ) {
            $sign_in_providers[] = $this->sign_in_provider;
        }

        update_user_meta( $user_id, 'sign_in_providers', $sign_in_providers );
    }

    public function add_user_to_blog_if_needed( int $user_id, string $user_role ) {
        if ( is_multisite() && !is_user_member_of_blog( $user_id, get_current_blog_id() ) ) {
            add_user_to_blog( get_current_blog_id(), $user_id, $user_role ); // add user to site.
        }
    }

    private function get_default_role() {
        $default_role = DT_Login_Fields::get( 'default_role' );
        $user_role = !empty( $default_role ) ? $default_role : 'registered';

        return $user_role;
    }

    /**
     * Login the user using default wordpress method
     * @return mixed
     */
    private function wordpress_login() {
        if ( is_user_logged_in() ) {
            wp_logout();
        }

        $login_length = DT_Login_Fields::get( 'login_length' );
        if ( empty( $login_length ) || !is_numeric( $login_length ) ) {
            $login_length = 14;
        }

        add_filter( 'auth_cookie_expiration', function () use ( $login_length ) {
            return $login_length * DAY_IN_SECONDS;
        } );

        add_filter( 'authenticate', [ $this, 'allow_programmatic_login' ], 10, 3 );    // hook in earlier than other callbacks to short-circuit them

        $user = wp_signon( [
            'user_login' => $this->email ?: $this->uid,
            'user_password' => 'not-the-real-password',
            'remember' => true,
        ] );

        remove_filter( 'authenticate', [ $this, 'allow_programmatic_login' ], 10 );

        if ( is_a( $user, 'WP_User' ) ) {
            wp_set_current_user( $user->ID, $user->data->user_login );

            if ( is_user_logged_in() ) {
                return [
                    'login_method' => DT_Login_Methods::WORDPRESS,
                    'jwt' => null,
                ];
            }
        }

        return false;
    }

    /**
     * Login the user by returning a valid JWT token
     * @return array|bool
     */
    private function mobile_login() {
        /* Force logout of any logged in admin user with WP cookies set */
        if ( is_user_logged_in() ) {
            wp_logout();
        }

        add_filter( 'authenticate', [ $this, 'allow_programmatic_login' ], 10, 3 );    // hook in earlier than other callbacks to short-circuit them

        $auth_service_endpoint = DT_Login_Fields::get( 'auth_service_endpoint' );

        if ( !$auth_service_endpoint || empty( $auth_service_endpoint ) ) {
            $auth_service_endpoint = 'http://localhost:8000/' . static::DEFAULT_AUTH_SERVICE_ENDPOINT;
        }

        require_once( get_template_directory() . '/dt-core/libraries/wp-api-jwt-auth/public/class-jwt-auth-public.php' );
        $token = Jwt_Auth_Public::generate_token_static( $this->email, 'dummy-password' );

        remove_filter( 'authenticate', [ $this, 'allow_programmatic_login' ], 10 );

        if ( $token ) {
            return [
                'login_method' => DT_Login_Methods::MOBILE,
                'jwt' => $token,
            ];
        }

        return false;
    }

    /**
     * An 'authenticate' filter callback that authenticates the user using only     the username.
     *
     * To avoid potential security vulnerabilities, this should only be used in     the context of a programmatic login,
     * and unhooked immediately after it fires.
     *
     * @param WP_User $user
     * @param string $user_identifier
     * @param string $password
     * @return bool|WP_User a WP_User object if the username matched an existing user, or false if it didn't
     */
    public function allow_programmatic_login( $user, $user_identifier, $password ) {
        $user = get_user_by( 'email', $user_identifier );
        if ( empty( $user ) ) {
            $user = get_user_by( 'login', $user_identifier );
        }
        return $user;
    }
}
