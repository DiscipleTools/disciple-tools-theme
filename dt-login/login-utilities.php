<?php

/**
 * Capture additional jwt tokens for mobile based authentication flows.
 * Implemented directly after authentication wp hooks, to ensure direct
 * /wp-json/jwt-auth/v1/token endpoint remains operational.
 *
 * @param string $cookie
 * @param int    $user_id
 * @param int    $expiration
 * @param string $scheme
 * @param string $token
 *
 * @return string
 */
add_filter( 'auth_cookie', 'dt_auth_cookie_token', 40, 5 );
function dt_auth_cookie_token( $cookie, $user_id, $expiration, $scheme, $token ){

    $user = get_user_by( 'ID', $user_id );
    $login_method = DT_Login_Fields::get_login_method();

    // Only focus on pre-authenticated users, engaged in a mobile session.
    if ( !empty( $user ) && !is_wp_error( $user ) && DT_Login_Methods::MOBILE === $login_method ) {
        $success_redirect = DT_Login_Fields::get( 'redirect_url' );
        $login_page = DT_Login_Fields::get( 'login_url' );

        $token_rest_request = new WP_REST_Request( 'POST', 'token' );
        $token_rest_request->set_query_params( array(
            'auth_by_user' => true,
        ) );
        $response = Jwt_Auth_Public::generate_token( $token_rest_request, $user );

        // On successful token generation, redirect accordingly; assigning token to url parameters.
        if ( !is_wp_error( $response ) && isset( $response['token'] ) ) {
            $updated_url_params = http_build_query( array_merge( $_GET, array( 'token' => $response['token'] ) ) );

            header( "Location: /$login_page?redirect_to=$success_redirect&$updated_url_params" );
            die();
        }
    }

    return $cookie;
}

/**
 * Check if the user is logged in for redirection purposes
 *
 * Spitting out a script if the user is using front end login feels dirty
 *
 * Switching to doing this auth check from the frontend; leaving this here in case it's useful
 * @return mixed
 */
function dt_sso_login_redirect_if_no_auth() {
    /* Check what the login method is */
    $login_method = DT_Login_Fields::get_login_method();
    $success_redirect = DT_Login_Fields::get( 'redirect_url' );
    $login_page = DT_Login_Fields::get( 'login_url' );

    if ( !is_user_logged_in() && DT_Login_Methods::WORDPRESS === $login_method ) {
        $redirect_to = $success_redirect;

        header( "Location: /$login_page?redirect_to=$redirect_to" );
    }

    if ( DT_Login_Methods::MOBILE === $login_method ) {

        ?>

        <script>
            /* Check if the user has a valid token */
            const token = localStorage.getItem( 'login_token' );

            if ( !token ) {
                redirect()
            }

            fetch( '/wp-json/jwt-auth/v1/token/validate', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                }
            } )
            .then((result) => result.text())
            .then((text) => {
                const json = JSON.parse(text)

                const { data } = json
                const { status } = data

                if ( status !== 200 ) {
                    redirect()
                }
            })
            .catch((error) => {
                redirect()
            })

            function redirect() {
                const redirect_to = encodeURIComponent( window.location.href )
                window.location.href = `<?php echo esc_html( $login_page ) ?>?redirect_to=${redirect_to}`
            }

        </script>

        <?php

    }
}
