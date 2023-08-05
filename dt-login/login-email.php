<?php

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class DT_Login_Email {
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        // api vars
        add_action( 'dt_login_head_bottom', [ $this, 'dt_login_head_bottom' ], 20 );
    }

    public function dt_login_head_bottom() {
        $dt_login = DT_Login_Fields::all_values();
        ?>
        <script>
            var verifyCallback = function(response) {
                jQuery('#submit').prop("disabled", false);
            };
            var onloadCallback = function() {
                grecaptcha.render('g-recaptcha', {
                    'sitekey' : '<?php echo esc_attr( $dt_login['google_captcha_client_key'] ); ?>',
                    'callback' : verifyCallback,
                });
            };
        </script>
        <?php
    }

    /**
     * @see https://code.tutsplus.com/tutorials/creating-a-custom-wordpress-registration-form-plugin--cms-20968
     * @see https://css-tricks.com/password-strength-meter/
     */
    public function custom_registration_function() {
        $dt_login = DT_Login_Fields::all_values();
        $error = new WP_Error();

        if ( ! ( isset( $_POST['login_form_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['login_form_nonce'] ) ), 'login_form' ) ) ) {
            return 0;
        }

        /* if ( ! isset( $_POST['g-recaptcha-response'] ) ) {
            $error->add( __METHOD__, __( 'Missing captcha response. How did you do that?', 'disciple_tools' ) );
            return $error;
        }
        $args = array(
            'method' => 'POST',
            'body' => array(
                'secret' => $dt_login['google_captcha_server_secret_key'],
                'response' => isset( $_POST['g-recaptcha-response'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) ) : '',
            )
        );
        $post_result = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', $args );
        $post_body = json_decode( wp_remote_retrieve_body( $post_result ), true );
        if ( ! isset( $post_body['success'] ) || false === $post_body['success'] ) {
            $error->add( __METHOD__, __( 'Captcha failure. Try again, if you are human.', 'disciple_tools' ) );
            return $error;
        }
         */
        // validate elements
        if ( empty( $_POST['email'] ) || empty( $_POST['password'] ) ) {
            $error->add( __METHOD__, __( 'Missing email or password.', 'disciple_tools' ) );
            return $error;
        }

        // @todo check if passwords are identical

        // sanitize user form input
        $password   = sanitize_text_field( wp_unslash( $_POST['password'] ) );
        $email      = sanitize_email( wp_unslash( $_POST['email'] ) );
        $explode_email = explode( '@', $email );
        if ( isset( $explode_email[0] ) ) {
            $username = $explode_email[0];
        } else {
            $username = str_replace( '@', '_', $email );
            $username = str_replace( '.', '_', $username );
        }
        $username   = sanitize_user( $username );


        $display_name = $username;
        if ( isset( $_POST['display_name'] ) ) {
            $display_name = trim( sanitize_text_field( wp_unslash( $_POST['display_name'] ) ) );
        }

        if ( email_exists( $email ) ) {
            $error->add( __METHOD__, __( 'Sorry. This email is already registered. Try re-setting your password', 'disciple_tools' ) );
            return $error;
        }

        if ( username_exists( $username ) ) {
            $username = $username . rand( 0, 9 );
        }

        $user_role = !empty( $dt_login['default_role'] ) ? $dt_login['default_role'] : 'registered';
        $userdata = [
            'user_email' => $email,
            'user_login' => $username,
            'display_name' => $display_name,
            'user_pass' => $password,
            'role' => $user_role,
        ];

        $user_id = wp_insert_user( $userdata );

        if ( is_wp_error( $user_id ) ) {
            $error->add( __METHOD__, __( 'Something went wrong. Sorry. Could you try again?', 'disciple_tools' ) );
            return $error;
        }

        if ( is_multisite() && !is_user_member_of_blog( $user_id, get_current_blog_id() ) ) {
            add_user_to_blog( get_current_blog_id(), $user_id, $user_role ); // add user to site.
        }

        // @todo send to location based on user role


        // log user in
        $user = get_user_by( 'id', $user_id );
        if ( $user ) {
            wp_set_current_user( $user_id, $user->user_login );
            wp_set_auth_cookie( $user_id );
            do_action( 'wp_login', $user->user_login, $user );
            wp_safe_redirect( dt_login_url( 'redirect' ) );
            exit;
        } else {
            $error->add( __METHOD__, __( 'No new user found.', 'disciple_tools' ) );
            return $error;
        }
    }

    public static function retrieve_password() {
        $errors = new WP_Error();

        if ( ! ( isset( $_POST['retrieve_password_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['retrieve_password_nonce'] ) ), 'retrieve_password' ) ) ) {
            $errors->add( __METHOD__, __( 'Missing form verification. Refresh and try again.', 'disciple_tools' ) );
            return $errors;
        }

        if ( isset( $_POST['user_login'] ) ) {
            $user_login = trim( sanitize_text_field( wp_unslash( $_POST['user_login'] ) ) );
        } else {
            $errors->add( __METHOD__, __( 'Missing username or email address.', 'disciple_tools' ) );
            return $errors;
        }


        if ( empty( $user_login ) ) {
            $errors->add( __METHOD__, __( 'ERROR: Enter a username or email address.', 'disciple_tools' ) );
        } elseif ( strpos( $user_login, '@' ) ) {
            $user_data = get_user_by( 'email', $user_login );
            if ( empty( $user_data ) ) {
                $errors->add( __METHOD__, __( 'ERROR: There is no user registered with that email address.', 'disciple_tools' ) );
            }
        } else {
            $user_data = get_user_by( 'login', $user_login );
            if ( empty( $user_data ) ) {
                $errors->add( __METHOD__, __( 'ERROR: There is no user registered with that username.', 'disciple_tools' ) );
            }
        }

        /**
         * Fires before errors are returned from a password reset request.
         *
         * @since 2.1.0
         * @since 4.4.0 Added the `$errors` parameter.
         *
         * @param WP_Error $errors A WP_Error object containing any errors generated
         *                         by using invalid credentials.
         */
        do_action( 'lostpassword_post', $errors );

        if ( $errors->get_error_code() ) {
            return $errors;
        }

        if ( ! $user_data ) {
            $errors->add( 'invalidcombo', __( 'ERROR: Invalid username or email.', 'disciple_tools' ) );
            return $errors;
        }

        // Redefining user_login ensures we return the right case in the email.
        $user_login = $user_data->user_login;
        $user_email = $user_data->user_email;
        $key = self::dt_login_get_password_reset_key( $user_data );

        if ( is_wp_error( $key ) ) {
            return $key;
        }

        $site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

        $message = __( 'Someone has requested a password reset for the following account:' ) . "\r\n\r\n";
        /* translators: %s: site name */
        $message .= sprintf( __( 'Site Name: %s' ), $site_name ) . "\r\n\r\n";
        /* translators: %s: user login */
        $message .= sprintf( __( 'Username: %s' ), $user_login ) . "\r\n\r\n";
        $message .= __( 'If this was a mistake, just ignore this email and nothing will happen.' ) . "\r\n\r\n";
        $message .= __( 'To reset your password, visit the following address:' ) . "\r\n\r\n";
        $message .= '<' . dt_login_url( 'login' ) . "?action=rp&key=$key&login=" . rawurlencode( $user_login ) . ">\r\n";

        /* translators: Password reset email subject. %s: Site name */
        $title = sprintf( __( '[%s] Password Reset' ), $site_name );

        /**
         * Filters the subject of the password reset email.
         *
         * @since 2.8.0
         * @since 4.4.0 Added the `$user_login` and `$user_data` parameters.
         *
         * @param string  $title      Default email title.
         * @param string  $user_login The username for the user.
         * @param WP_User $user_data  WP_User object.
         */
        $title = apply_filters( 'retrieve_password_title', $title, $user_login, $user_data );

        /**
         * Filters the message body of the password reset mail.
         *
         * If the filtered message is empty, the password reset email will not be sent.
         *
         * @since 2.8.0
         * @since 4.1.0 Added `$user_login` and `$user_data` parameters.
         *
         * @param string  $message    Default mail message.
         * @param string  $key        The activation key.
         * @param string  $user_login The username for the user.
         * @param WP_User $user_data  WP_User object.
         */
        $message = apply_filters( 'retrieve_password_message', $message, $key, $user_login, $user_data );

        if ( $message && ! wp_mail( $user_email, wp_specialchars_decode( $title ), $message ) ) {
            wp_die( esc_html__( 'The email could not be sent.' ) . "<br />\n" . esc_html__( 'Possible reason: your host may have disabled the mail() function.', 'disciple_tools' ) );
        }

        return true;
    }

    public static function dt_login_get_password_reset_key( $user ) {
        global $wpdb, $wp_hasher;

        /**
         * Fires before a new password is retrieved.
         *
         * Use the {@see 'retrieve_password'} hook instead.
         *
         * @since 1.5.0
         * @deprecated 1.5.1 Misspelled. Use 'retrieve_password' hook instead.
         *
         * @param string $user_login The user login name.
         */
        do_action( 'retreive_password', $user->user_login );

        /**
         * Fires before a new password is retrieved.
         *
         * @since 1.5.1
         *
         * @param string $user_login The user login name.
         */
        do_action( 'retrieve_password', $user->user_login );

        $allow = true;
//    if ( is_multisite() && is_user_spammy( $user ) ) {
//        $allow = false;
//    }

        /**
         * Filters whether to allow a password to be reset.
         *
         * @since 2.7.0
         *
         * @param bool $allow         Whether to allow the password to be reset. Default true.
         * @param int  $user_data->ID The ID of the user attempting to reset a password.
         */
        $allow = apply_filters( 'allow_password_reset', $allow, $user->ID );

        if ( ! $allow ) {
            return new WP_Error( 'no_password_reset', __( 'Password reset is not allowed for this user', 'disciple_tools' ) );
        } elseif ( is_wp_error( $allow ) ) {
            return $allow;
        }

        // Generate something random for a password reset key.
        $key = wp_generate_password( 20, false );

        /**
         * Fires when a password reset key is generated.
         *
         * @since 2.5.0
         *
         * @param string $user_login The username for the user.
         * @param string $key        The generated password reset key.
         */
        do_action( 'retrieve_password_key', $user->user_login, $key );

        // Now insert the key, hashed, into the DB.
        if ( empty( $wp_hasher ) ) {
            require_once ABSPATH . WPINC . '/class-phpass.php';
            // @codingStandardsIgnoreLine
            $wp_hasher = new PasswordHash( 8, true );
        }
        $hashed = time() . ':' . $wp_hasher->HashPassword( $key );
        $key_saved = $wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user->user_login ) );
        if ( false === $key_saved ) {
            return new WP_Error( 'no_password_key_update', __( 'Could not save password reset key to database.', 'disciple_tools' ) );
        }

        return $key;
    }
} // end class
DT_Login_Email::instance();
