<?php
/**
 * SHARED FUNCTIONS
 */

// LOGIN PAGE REDIRECT
add_action( 'init', 'dt_login_redirect_login_page' );
function dt_login_redirect_login_page() {

    $login_page_enabled = DT_Login_Fields::get( 'login_enabled' ) === 'on';

    if ( !$login_page_enabled ) {
        return;
    }
    if ( isset( $_SERVER['REQUEST_URI'] ) && !empty( $_SERVER['REQUEST_URI'] ) ) {
        $page_viewed = substr( basename( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ), 0, 12 );

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

        if ( $page_viewed == 'wp-login.php' && isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] == 'GET' ) {
            wp_redirect( dt_login_url( 'login' ) );
            exit;
        }
    }
}
// END LOGIN PAGE REDIRECT

function dt_login_url( string $name ) : string {
    $dt_login = DT_Login_Fields::all_values();

    $login_url = $dt_login['login_url'] ?? '';
    $redirect_url = $dt_login['redirect_url'] ?? '';
    $login_page_enabled = $dt_login['login_enabled'] === 'on';

    if ( !$login_page_enabled ) {
        $login_url = 'wp-login.php';
    }

    switch ( $name ) {
        case 'home':
            return trailingslashit( site_url() );
        case 'login':
            return trailingslashit( site_url() ) . $login_url;
        case 'redirect':
        case 'success':
            return trailingslashit( site_url() ) . $redirect_url;
        case 'logout':
            return trailingslashit( site_url() ) . $login_url . '/?action=logout';
        case 'register':
            return trailingslashit( site_url() ) . $login_url . '/?action=register';
        case 'lostpassword':
            return trailingslashit( site_url() ) . $login_url . '/?action=lostpassword';
        case 'resetpass':
            return trailingslashit( site_url() ) . $login_url . '/?action=resetpass';
        case 'expiredkey':
            return trailingslashit( site_url() ) . $login_url . '/?action=lostpassword&error=expiredkey';
        case 'invalidkey':
            return trailingslashit( site_url() ) . $login_url . '/?action=lostpassword&error=invalidkey';
        default:
            return '';
    }
}


function dt_login_spinner() : string {
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
    $login_page  = dt_login_url( 'login' );
    wp_redirect( $login_page . '?login=failed' );
    exit;
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

    return dt_login_url( 'login' );
}
