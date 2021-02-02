<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Notifications_Comments
 */
class Disciple_Tools_Notifications_Comments
{

    /**
     * Disciple_Tools_Notifications_Comments constructor.
     */
    public function __construct() {
    }

    /**
     * Filter comment for notification
     *
     * @param      $comment_id
     * @param null $comment
     *
     * @return array|WP_Error
     */
    public static function insert_notification_for_comment( $comment_id, $comment = null ) {

        if ( is_null( $comment ) ) {
            $comment = get_comment( $comment_id );
        }
        if ( $comment->comment_type === "comment") {

            $comment_with_users       = self::match_mention( $comment->comment_content ); // fail if no match for mention found
            $comment->comment_content = $comment_with_users["comment"];
            $mentioned_user_ids       = $comment_with_users["user_ids"];
            $post_id                  = $comment->comment_post_ID;
            $date_notified            = $comment->comment_date;
            $post_type                = get_post_type( $post_id );

            $followers = DT_Posts::get_users_following_post( $post_type, $post_id, false );
            if ( is_wp_error( $followers ) ){
                return $followers;
            }

            $users_to_notify = array_unique( array_merge( $mentioned_user_ids, $followers ) );

            $users_to_notify = apply_filters( "dt_filter_users_receiving_comment_notification", $users_to_notify, $post_type, $post_id, $comment );

            $source_user_id = $comment->user_id;
            $notification = [
                'user_id'             => '',
                'source_user_id'      => $source_user_id,
                'post_id'             => (int) $post_id,
                'secondary_item_id'   => (int) $comment_id,
                'notification_name'   => "comment",
                'notification_action' => 'comment',
                'notification_note'   => "",
                'date_notified'       => current_time( 'mysql' ),
                'is_new'              => 1,
                'field_key'           => 'comments',
                'field_value'         => '',
            ];


            foreach ( $users_to_notify as $user_to_notify ) {

                if ( $user_to_notify != $source_user_id ) { // checks that the user who created the event and the user receiving the notification are not the same.

                    $user      = get_userdata( $user_to_notify );
                    $user_meta = get_user_meta( $user_to_notify );

                    if ( $user && $user_meta ) {
                        // call appropriate action
                        $notification["user_id"] = $user_to_notify;
                        if ( in_array( $user_to_notify, $mentioned_user_ids ) ) {
                            $notification["notification_name"]   = 'mention';
                            $notification["notification_action"] = 'mentioned';
                            // share record with mentioned individual
                            DT_Posts::add_shared( $post_type, $post_id, $user_to_notify, null, false );
                        } else {
                            $notification["notification_name"]   = 'comment';
                            $notification["notification_action"] = 'comment';
                        }


                        $notification["notification_note"] = Disciple_Tools_Notifications::get_notification_message_html( $notification );

                        if ( in_array( $user_to_notify, $mentioned_user_ids ) ){
                            do_action( 'send_notification_on_channels', $user_to_notify, $notification, 'mentions', [] );
                        } else {
                            do_action( 'send_notification_on_channels', $user_to_notify, $notification, 'comments', [] );
                        }
                    }
                }
            }
        }
    }

    /**
     * Checks for mention in text of comment.
     * If mention is found, returns true. If mention is not found, returns false.
     *
     * @param $comment_content
     *
     * @return bool
     */
    public function check_for_mention( $comment_content ) {
        return preg_match( '/\@\[(.*?)\]\((.+?)\)/', $comment_content );
    }




    /**
     * Parse @mention to find user match
     *
     * @param $comment_content
     *
     * @return bool|array
     */
    public static function match_mention( $comment_content ) {
        preg_match_all( '/\@\[(.*?)\]\((.+?)\)/', $comment_content, $matches );

        $user_ids = [];
        foreach ( $matches[2] as $match ) {

            // trim punctuation
            $match = preg_replace( '/\W+/', '', $match );

            // get user_id by name match
            $user = get_user_by( 'id', $match );
            if ( $user ) {
                if ( !in_array( $user->ID, $user_ids ) ){
                    $user_ids[] = $user->ID;
                }
            }
        }
        return [
            "user_ids" => $user_ids,
            "comment" => Disciple_Tools_Notifications::format_comment( $comment_content )
        ];

//        return empty( $user_ids ) ? false : $user_ids;
    }

    /**
     * Create notification activity
     *
     * @param int $mentioned_user_id
     * @param int $source_user_id
     * @param int $post_id
     * @param int $comment_id
     * @param string $notification_action
     * @param string $notification_note
     * @param        $date_notified
     * @param string $field_key
     * @param string $field_value
     */
    protected function add_mention_notification(
        int $mentioned_user_id,
        int $source_user_id,
        int $post_id,
        int $comment_id,
        string $notification_action,
        string $notification_note,
        $date_notified,
        string $field_key = "comments",
        string $field_value = ""
    ) {

        dt_notification_insert(
            [
                'user_id'             => $mentioned_user_id,
                'source_user_id'      => $source_user_id,
                'post_id'             => $post_id,
                'secondary_item_id'   => $comment_id,
                'notification_name'   => 'mention',
                'notification_action' => $notification_action,
                'notification_note'   => $notification_note,
                'date_notified'       => $date_notified,
                'is_new'              => 1,
                'field_key'           => $field_key,
                'field_value'           => $field_value
            ]
        );
    }

    /**
     * Delete notification
     *
     * @param int $mentioned_user_id
     * @param int $post_id
     * @param int $comment_id
     * @param     $date_notified
     */
    private static function delete_mention_notification( int $mentioned_user_id, int $post_id, int $comment_id, $date_notified ) {

        dt_notification_delete(
            [
                'user_id'           => $mentioned_user_id,
                'post_id'           => $post_id,
                'secondary_item_id' => $comment_id,
                'notification_name' => 'mention',
                'date_notified'     => $date_notified,
            ]
        );
    }
}
