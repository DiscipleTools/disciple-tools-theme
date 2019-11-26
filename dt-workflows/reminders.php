<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly


if ( ! wp_next_scheduled( 'reminder_notifications' ) ) {
    wp_schedule_event( time(), 'hourly', 'reminder_notifications' );
}
add_action( 'reminder_notifications', 'set_reminder_notifications' );
add_action( 'init', 'set_reminder_notifications' );
add_filter( 'dt_custom_notification_note', 'dt_custom_notification_note', 10, 2 );

function set_reminder_notifications(){
    global $wpdb;

    $reminders = $wpdb->get_results("
        SELECT * 
        FROM $wpdb->dt_post_user_meta pum
        WHERE pum.date <= NOW()
        AND meta_key = 'reminders'
        AND meta_value NOT LIKE '%reminder_sent%'
    ", ARRAY_A);
    foreach ( $reminders as $reminder ){
        $val = maybe_unserialize( $reminder["meta_value"] );
        $message = $val["note"] ?? "";
        $post = get_post( $reminder["post_id"] );
        $user = get_user_by( "ID", $reminder["user_id"] );

        if ( $post && $message && $user ){

            //enable comments when private comments are available.
//            $comment_html = esc_html( '@[' . $user->display_name . "](" . $reminder['user_id']. ") " . __( "You set this reminder:" ) . " " . $message );
//            $comment_id = DT_Posts::add_post_comment( $post->post_type, $reminder["post_id"], $comment_html, 'reminder', [
//                "user_id" => 0,
//                "comment_author" => __( "Reminders", 'disciple_tools' )
//            ], false );

            $notification = [
                'user_id'             => $reminder["user_id"],
                'source_user_id'      => 0,
                'post_id'             => (int) $reminder["post_id"],
                'secondary_item_id'   => '',
                'notification_name'   => "reminders",
                'notification_action' => 'reminders',
                'notification_note'   => $val["note"],
                'date_notified'       => current_time( 'mysql' ),
                'is_new'              => 1,
                'field_key'           => 'post_user_meta',
                'field_value'         => $reminder["id"],
            ];
            do_action( 'send_notification_on_channels', $reminder["user_id"], $notification, 'reminders', [] );
        }
        $val["status"] = "reminder_sent";
        $wpdb->update( $wpdb->dt_post_user_meta,
            [
                "meta_value" => serialize( $val )
            ],
            [
                "id"       => $reminder["id"],
                "user_id"  => $reminder["user_id"],
                "post_id"  => $reminder["post_id"],
                "meta_key" => "reminders",
            ]
        );
    }
}


function dt_custom_notification_note( $note, $notification ){
    if ( empty( $note ) && $notification["notification_name"] === "reminders" && $notification["field_value"] ) {
        $post = get_post( $notification["post_id"] );
        $post_title = isset( $post->post_title ) ? sanitize_text_field( $post->post_title ) : "";
        $link = '<a href="' . home_url( '/' ) . get_post_type( $notification["post_id"] ) . '/' . $notification["post_id"] . '">' . $post_title . '</a>';
        global $wpdb;
        $reminder = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM $wpdb->dt_post_user_meta WHERE id = %s", $notification["field_value"] ) );
        $message = maybe_unserialize( $reminder )["note"];
        $note = Disciple_Tools_Notifications::instance()->format_comment( $message );
        $note = sprintf( esc_html_x( 'You set this reminder on %1$s: %2$s', 'You set this reminder on contact1: test', 'disciple_tools' ), $link, $note );
    }
    return $note;
}