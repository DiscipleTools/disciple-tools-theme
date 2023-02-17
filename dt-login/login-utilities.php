<?php

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