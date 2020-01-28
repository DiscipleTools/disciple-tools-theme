<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly


if ( ! wp_next_scheduled( 'task_notifications' ) ) {
    wp_schedule_event( time(), 'hourly', 'task_notifications' );
}
add_action( 'task_notifications', 'set_task_notifications' );
add_filter( 'dt_custom_notification_note', 'dt_custom_notification_note', 10, 2 );

function set_task_notifications(){
    global $wpdb;

    $tasks = $wpdb->get_results("
        SELECT * 
        FROM $wpdb->dt_post_user_meta pum
        WHERE pum.date <= NOW()
        AND meta_key = 'tasks'
        AND meta_value NOT LIKE '%notification_sent%'
        AND meta_value NOT LIKE '%task_complete%'
    ", ARRAY_A);
    foreach ( $tasks as $task ){
        $val = maybe_unserialize( $task["meta_value"] );
        $message = $val["note"] ?? "";
        $post = get_post( $task["post_id"] );
        $user = get_user_by( "ID", $task["user_id"] );
        $type = $val["category"] ?? null;

        $send = ( $type === "reminder" && $val["notification"] !== 'notification_sent' ) || ( $type !== 'reminder' && $val["status"] !== "task_complete" );
        if ( $post && $send && $user ) {

            //enable comments when private comments are available.
//            $comment_html = esc_html( '@[' . $user->display_name . "](" . $task['user_id']. ") " . __( "You set this task:" ) . " " . $message );
//            $comment_id = DT_Posts::add_post_comment( $post->post_type, $task["post_id"], $comment_html, 'task', [
//                "user_id" => 0,
//                "comment_author" => __( "Tasks", 'disciple_tools' )
//            ], false );

            $notification = [
                'user_id'             => $task["user_id"],
                'source_user_id'      => 0,
                'post_id'             => (int) $task["post_id"],
                'secondary_item_id'   => '',
                'notification_name'   => "tasks",
                'notification_action' => 'tasks',
                'notification_note'   => $val["note"],
                'date_notified'       => current_time( 'mysql' ),
                'is_new'              => 1,
                'field_key'           => 'post_user_meta',
                'field_value'         => $task["id"],
            ];
            do_action( 'send_notification_on_channels', $task["user_id"], $notification, 'tasks', [] );
        }
        $val["notification"] = "notification_sent";
        $wpdb->update( $wpdb->dt_post_user_meta,
            [
                "meta_value" => serialize( $val )
            ],
            [
                "id"       => $task["id"],
                "user_id"  => $task["user_id"],
                "post_id"  => $task["post_id"],
                "meta_key" => "tasks",
            ]
        );
    }
}


function dt_custom_notification_note( $note, $notification ){
    if ( empty( $note ) && $notification["notification_name"] === "tasks" && $notification["field_value"] ) {
        $post = get_post( $notification["post_id"] );
        $post_title = isset( $post->post_title ) ? sanitize_text_field( $post->post_title ) : "";
        $link = '<a href="' . home_url( '/' ) . get_post_type( $notification["post_id"] ) . '/' . $notification["post_id"] . '">' . $post_title . '</a>';
        global $wpdb;
        $task = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->dt_post_user_meta WHERE id = %s", $notification["field_value"] ), ARRAY_A );

        if ( $task["category"] === "reminder" ){
            $note = sprintf( esc_html_x( 'You set a reminder for today on %1$s', 'Tasks', 'disciple_tools' ), $link );
        } else {
            $message = maybe_unserialize( $task["meta_value"] )["note"] ?? null;
            $note = Disciple_Tools_Notifications::instance()->format_comment( $message );
            $note = sprintf( esc_html_x( 'You set this task on %1$s: %2$s', 'Tasks: You set this task on contact1: task note', 'disciple_tools' ), $link, $note );
        }
    }
    return $note;
}