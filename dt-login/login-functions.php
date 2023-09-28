<?php
/**
 * SHARED FUNCTIONS
 */

// LOGIN PAGE REDIRECT
add_action( 'init', 'dt_login_redirect_login_page' );
function dt_login_redirect_login_page() {

    $login_page_enabled = DT_Login_Fields::get( 'login_enabled' ) === 'on';

    if ( isset( $_SERVER['REQUEST_URI'] ) && !empty( $_SERVER['REQUEST_URI'] ) ) {
        $page_viewed = substr( basename( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ), 0, 12 );
        $parsed_request_uri = ( new DT_URL( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) )->parsed_url;
        $page_viewed = ltrim( $parsed_request_uri['path'], '/' );

        if ( $page_viewed == 'wp-login.php' && isset( $_GET['action'] ) && $_GET['action'] === 'register' && !dt_can_users_register() ) {
            wp_redirect( wp_login_url() );
            exit;
        }

        if ( !$login_page_enabled ) {
            return;
        }

        if ( $page_viewed == 'wp-login.php' && isset( $_GET['action'] ) && $_GET['action'] === 'rp' ) {
            return;
        }

//        if ( $page_viewed == "wp-login.php" && isset( $_GET['action'] ) && $_GET['action'] === 'resetpass' ) {
//            wp_redirect( dt_login_url( 'resetpass' ) );
//            exit;
//        }

        if ( $page_viewed == 'wp-login.php' && isset( $_GET['action'] ) && $_GET['action'] === 'logout' ) {
            wp_redirect( dt_login_url( 'logout' ) );
            exit;
        }

        $login_url = DT_Login_Fields::get( 'login_url' );
        $login_url = apply_filters( 'dt_login_url', $login_url );
        if ( $page_viewed == $login_url && isset( $_GET['action'] ) && $_GET['action'] === 'register' && !dt_can_users_register() ) {
            wp_redirect( dt_login_url( 'login' ) );
            exit;
        }

        //phpcs:disable
        if ( $page_viewed == 'wp-login.php' && !isset( $_GET['action'] ) && $_SERVER['REQUEST_METHOD'] == 'POST' ) {
            if ( isset( $_POST['log'] ) && isset( $_POST['pwd'] ) && ( empty( $_POST['log'] ) || empty( $_POST['pwd'] ) ) ) {
                if ( isset( $_POST['redirect_to'] ) ) {
                    wp_redirect( dt_login_url( 'login', $_POST['redirect_to'] ) . '&login=failed' );
                } else {
                    $parsed_url = wp_parse_url( dt_login_url( 'login' ) );
                    if ( isset( $parsed_url['query'] ) && !empty( $parsed_url['query'] ) ) {
                        wp_redirect( dt_login_url( 'login' ) . '&login=failed' );
                    } else {
                        wp_redirect( dt_login_url( 'login' ) . '?login=failed' );
                    }
                }
                exit;
            }
        }
        //phpcs:enable

        if ( $page_viewed == 'wp-login.php' && isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] == 'GET' ) {
            wp_redirect( dt_login_url( 'login' ) );
            exit;
        }
    }
}
// END LOGIN PAGE REDIRECT

/**
 * This function is used to create urls for the different wp login actions
 * In order for the current query params for the current page to propogate to any login action links in the page
 * the query params are extracted from the current url
 */
function dt_login_url( string $name, string $url = '' ): string {
    $dt_login = DT_Login_Fields::all_values();

    if ( empty( $url ) ){
        $url = dt_get_url_path();
    }

    $dt_url = new DT_URL( $url );
    $query_params = $dt_url->query_params;

    if ( $query_params->has( 'action' ) ) {
        $query_params->delete( 'action' );
    }

    if ( $query_params->has( 'redirect_to' ) ) {
        $query_redirect_url = $query_params->get( 'redirect_to' );
        $query_params->delete( 'redirect_to' );
    }

    $redirect_url = empty( $query_redirect_url ) ? site_url( $dt_login['redirect_url'] ) ?? '' : $query_redirect_url;

    /**
     * Filters the redirect url from the dt login page.
     *
     * @param string $redirect_url
     */
    $redirect_url = apply_filters( 'dt_login_redirect_url', $redirect_url );

    if ( !empty( $redirect_url ) ) {
        $query_params->append( 'redirect_to', rawurlencode( $redirect_url ) );
    }

    $params = $query_params->toArray();

    $login_page_enabled = $dt_login['login_enabled'] === 'on';

    $login_url = $dt_login['login_url'] ?? '';

    if ( !$login_page_enabled ) {
        $login_url = 'wp-login.php';
    }

    /**
     * Filters the base login_url e.g. login or wp-login.php
     *
     * @param string $login_url
     */
    $login_url = apply_filters( 'dt_login_url', $login_url );

    switch ( $name ) {
        case 'home':
            $home_url = apply_filters( 'dt_login_url', '' );
            return dt_create_site_url( $home_url );
        case 'login':
            return dt_create_site_url( $login_url, $params );
        case 'redirect':
        case 'success':
            return $redirect_url;
        case 'logout':
            return dt_create_site_url( $login_url, [ 'action' => 'logout' ] );
        case 'register':
            if ( !dt_can_users_register() ) {
                return dt_login_url( 'login', $url );
            }
            return dt_create_site_url( $login_url, [ 'action' => 'register', ...$params ] );
        case 'lostpassword':
            return dt_create_site_url( $login_url, [ 'action' => 'lostpassword', ...$params ] );
        case 'resetpass':
            return dt_create_site_url( $login_url, [ 'action' => 'resetpass' ] );
        case 'expiredkey':
            return dt_create_site_url( $login_url, [ 'action' => 'lostpassword', 'error' => 'expiredkey' ] );
        case 'invalidkey':
            return dt_create_site_url( $login_url, [ 'action' => 'lostpassword', 'error' => 'invalidkey' ] );
        default:
            return '';
    }
}

function dt_create_site_url( $path = '', $params = [] ) {
    $site_url = site_url( $path );

    if ( !empty( $params ) ) {
        $site_url = add_query_arg( $params, $site_url );
    }

    return $site_url;
}


function dt_login_spinner(): string {
    return plugin_dir_url( __DIR__ ) . 'spinner.svg';
}
/**
 * Changes the logo link from wordpress.org to your site
 */
function dt_login_site_url( $url ) {
    $login_enabled = DT_Login_Fields::get( 'login_enabled' ) === 'on';

    if ( !$login_enabled ) {
        return $url;
    }

    return dt_login_url( 'login' );
}
add_filter( 'login_headerurl', 'dt_login_site_url' );

/**
 * Changes the alt text on the logo to show your site name
 */
function dt_login_login_title() {
    return get_option( 'blogname' );
}
add_filter( 'login_headertext', 'dt_login_login_title' );



/* Where to go if a login failed */
add_action( 'wp_login_failed', 'dt_login_login_failed' );
function dt_login_login_failed() {
    if ( !dt_is_rest() && DT_Login_Fields::get( 'login_enabled' ) === 'on' ){
        $login_page  = dt_login_url( 'login' );
        $parsed_url = wp_parse_url( $login_page );

        if ( isset( $parsed_url['query'] ) && !empty( $parsed_url['query'] ) ) {
            $login_page .= '&login=failed';
        } else {
            $login_page .= '?login=failed';
        }

        wp_redirect( $login_page );
        exit;
    }
}

/* Where to go if any of the fields were empty */
//add_filter( 'authenticate', 'dt_login_verify_user_pass', 1, 3 );
function dt_login_verify_user_pass( $user, $username, $password ) {
    $login_page  = dt_login_url( 'login' );
    if ( $username == '' || $password == '' ) {
        wp_redirect( $login_page . '?login=empty' );
        exit;
    }
}
add_filter( 'wp_signup_location', 'dt_login_multisite_signup_location', 99, 1 );
function dt_login_multisite_signup_location( $url ) {
    $url = dt_login_url( 'login' );
    return $url;
}
add_filter( 'register_url', 'dt_login_multisite_register_location', 99, 1 );
function dt_login_multisite_register_location( $url ) {
    $url = dt_login_url( 'register' );
    return $url;
}

add_filter( 'login_url', 'dt_login_login_url', 99, 3 );
function dt_login_login_url( $url ){

    $login_enabled = DT_Login_Fields::get( 'login_enabled' ) === 'on';

    if ( !$login_enabled ) {
        return $url;
    }

    return dt_login_url( 'login', $url );
}
