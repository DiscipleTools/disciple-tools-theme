<?php

/**
 * Disciple_Tools_Notifications_Email
 *
 * @see     https://github.com/techcrunch/wp-async-task
 * @class   Disciple_Tools_Notifications_Email
 * @version 0.1.0
 * @since   0.1.0
 * @package Disciple.Tools
 *
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Shared DT email function to be used throughout the DT system. It provides asynchonous mail delivery that does not halt page load.
 *
 * Example:
 * dt_send_email(
 *     'recipients@email.com',
 *     'subject line',
 *     'content of the message',
 *      true,
 * );
 *
 * @param $email
 * @param $subject
 * @param $message_plain_text
 * @param bool $subject_prefix
 *
 * @return bool|\WP_Error
 */
function dt_send_email( $email, $subject, $message_plain_text, bool $subject_prefix = true ) {

    /**
     * Filter for development use.
     * If set to true this filter will catch all email traffic from the system and generate a log report,
     * this protects against accidental email sending when working on systems with live data.
     */
    $disabled = apply_filters( 'dt_block_development_emails', false );
    if ( $disabled ) {
        $print_email = [];
        $print_email['email'] = $email;
        $print_email['subject'] = $subject;
        $print_email['message'] = $message_plain_text;

        dt_write_log( __METHOD__ );
        dt_write_log( $print_email );

        return true;
    }

    // Sanitize
    $email = sanitize_email( $email );
    $subject = sanitize_text_field( $subject );

    $message_plain_text = wp_kses_post( $message_plain_text );

    if ( $subject_prefix ) {
        $subject = dt_get_option( 'dt_email_base_subject' ) . ': ' . $subject;
    }

    $user = get_user_by( 'email', $email );
    $continue = true;
    // don't send notifications if the user only has "registered" role.
    if ( $user && in_array( 'registered', $user->roles ) && sizeof( $user->roles ) === 1 ){
        $continue = false;
    }
    $continue = apply_filters( 'dt_sent_email_check', $continue, $email, $subject, $message_plain_text );
    if ( !$continue ){
        return false;
    }

    return dt_schedule_mail( $email, $subject, $message_plain_text );
}

function dt_schedule_mail( $email, $subject, $message, $headers = [] ){
    /**
     * if a server cron is set up, then use the email scheduler
     * otherwise send the email normally
     */
    if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON && !( defined( 'WP_DEBUG' ) && WP_DEBUG ) ){
        $is_sent = wp_queue()->push( new DT_Send_Email_Job( $email, $subject, $message, $headers ) );
    } else {
        $is_sent = wp_mail( $email, $subject, $message, $headers );
    }
    return $is_sent;
}

/**
 * Shared DT email function, similar to dt_send_email, but intended for use for
 * emails that are related to a particular contact record.
 *
 * We want to keep the subject line for all updates related to a particular
 * contact the same. For contact 43, the subject line should always be the
 * same:
 *
 * Subject: Update on contact43
 *
 * That way, Gmail.com will group these emails in a single conversation
 * view. Ideally, we would use the `Message-ID` and `References` email
 * headers to make this more robust and more portable in other email
 * clients, but that would make this code more complex, as we probably
 * would have to store the Message-IDs for previous sent emails.
 *
 * This function also appends a link in the email body to the contact record.
 *
 * @param string $email
 * @param int    $post_id
 * @param string $message_plain_text
 *
 * @return bool|\WP_Error
 */
function dt_send_email_about_post( string $email, int $post_id, string $message_plain_text ) {
    $full_message = $message_plain_text . dt_make_post_email_footer( $post_id );
    $full_message .= dt_make_email_footer();
    $subject = dt_make_post_email_subject( $post_id );

    return dt_send_email(
        $email,
        $subject,
        $full_message
    );
}

function dt_make_post_email_footer( int $post_id ) {
    $post_type = get_post_type( $post_id );
    $contact_url = home_url( '/' ) . $post_type . '/' . $post_id;
    return "\r\n\r\n--\r\n" . __( 'Click here to view or reply', 'disciple_tools' ) . ": $contact_url";
}

function dt_make_post_email_subject( $post_id ) {
    $post_type = get_post_type( $post_id );
    $post_label = Disciple_Tools_Posts::get_label_for_post_type( $post_type, true, $return_cache = false );
    return sprintf( esc_html_x( 'Update on %1$s #%2$s', 'ex: Update on Contact #323', 'disciple_tools' ), $post_label, $post_id );
}

function dt_make_email_footer() {
    return "\r\n" . __( 'Do not reply directly to this email.', 'disciple_tools' );
}

/**
 * Determine email address and name to be sent from. Use defaults, if
 * none available.
 */
add_filter( 'wp_mail_from', function ( $email ) {
    $base_email = dt_get_option( 'dt_email_base_address' );
    if ( !empty( $base_email ) ){
        $email = $base_email;
    }
    return $email;
} );
add_filter( 'wp_mail_from_name', function ( $name ) {
    $base_email_name = dt_get_option( 'dt_email_base_name' );
    if ( !empty( $base_email_name ) ) {
        $name = $base_email_name;
    }
    return $name;
} );

/**
 * Intercept all outgoing messages and wrap within email template.
 */

add_filter( 'wp_mail', function ( $args ) {
    if ( empty( $args['headers'] ) || !html_content_type_detected( $args['headers'] ) ) {
        $args['message'] = dt_email_template_wrapper( $args['message'] ?? '', $args['subject'] ?? '' );

        $headers = $args['headers'];
        if ( empty( $headers ) ) {
            $headers = [];
        }

        if ( is_array( $headers ) ) {
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
        } else {
            $headers .= '\r\nContent-Type: text/html; charset=UTF-8';
        }

        $args['headers'] = $headers;
    }

    return $args;
} );

function html_content_type_detected( $headers ): bool {
    $detected = false;

    foreach ( $headers as $header ) {
        $header = strtolower( $header );
        if ( ( strpos( $header, 'content-type' ) !== false ) && ( strpos( $header, 'text/html' ) !== false ) ) {
            $detected = true;
        }
    }

    return $detected;
}

/**
 * Send emails that have been put in the email queue
 */

use WP_Queue\Job;
class DT_Send_Email_Job extends Job{


    public $email_address;
    public $email_message;
    public $email_subject;
    public $email_headers;


    public function __construct( $email_address, $email_subject, $email_message, $email_headers = [] ){
        $this->email_address = $email_address;
        $this->email_message = $email_message;
        $this->email_subject = $email_subject;
        $this->email_headers = $email_headers;
    }

    /**
     * Handle job logic.
     */
    public function handle(){
        wp_mail( $this->email_address, $this->email_subject, $this->email_message, $this->email_headers );
    }
}


