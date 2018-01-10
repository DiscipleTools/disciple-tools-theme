<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Notifications_Hook_Comments
 */
class Disciple_Tools_Notifications_Hook_Comments extends Disciple_Tools_Notifications_Hook_Base
{

    /**
     * Disciple_Tools_Notifications_Hook_Comments constructor.
     */
    public function __construct()
    {
        add_action( 'wp_insert_comment', [ &$this, 'filter_comment_for_notification' ], 10, 2 );
        add_action( 'edit_comment', [ &$this, 'filter_comment_for_notification' ] );
        add_action( 'trash_comment', [ &$this, 'filter_comment_for_notification' ] );
        add_action( 'untrash_comment', [ &$this, 'filter_comment_for_notification' ] );
        add_action( 'delete_comment', [ &$this, 'filter_comment_for_notification' ] );

        parent::__construct();
    }

    /**
     * Filter comment for notification
     *
     * @param      $comment_id
     * @param null $comment
     */
    public function filter_comment_for_notification( $comment_id, $comment = null )
    {

        if ( is_null( $comment ) ) {
            $comment = get_comment( $comment_id );
        }

        if ( $this->check_for_mention( $comment->comment_content ) == '0' ) { // fail if no mention found
            return;
        }

        $mentioned_user_ids = $this->match_mention( $comment->comment_content ); // fail if no match for mention found
        if ( !$mentioned_user_ids ) {
            return;
        }

        foreach ( $mentioned_user_ids as $mentioned_user_id ) {
            $source_user_id = $comment->user_id;

            if ( $mentioned_user_id != $source_user_id ) { // checks that the user who created the event and the user receiving the notification are not the same.

                // build variables
                $post_id = $comment->comment_post_ID;
                $date_notified = $comment->comment_date;
                $author_name = $comment->comment_author;
                $post_type = get_post_type( $post_id );
                $user = get_userdata( $mentioned_user_id );
                $user_meta = get_user_meta( $mentioned_user_id );

                // call appropriate action
                switch ( current_filter() ) {
                    case 'wp_insert_comment' :
                        $notification_action = 'mentioned';

                        // share record with mentioned individual
                        Disciple_Tools_Contacts::add_shared( $post_type, $post_id, $mentioned_user_id );

                        // web notification
                        if ( dt_user_notification_is_enabled( 'mentions_web', $user_meta, $user->ID ) ) {

                            $notification_note = '<strong>' . strip_tags( $author_name ) . '</strong> mentioned you on <a href="' . home_url( '/' ) . get_post_type( $post_id ) . '/' . $post_id . '">'
                                . strip_tags( get_the_title( $post_id ) ) . '</a> saying, "' . strip_tags( $comment->comment_content ) . '" ';

                            $this->add_mention_notification(
                                $mentioned_user_id,
                                $source_user_id,
                                $post_id,
                                $comment_id,
                                $notification_action,
                                $notification_note,
                                $date_notified
                            );
                        }

                        // email notification
                        if ( dt_user_notification_is_enabled( 'mentions_email', $user_meta, $user->ID ) ) {

                            $message = strip_tags( $author_name ) . ' mentioned you saying: ' . strip_tags( $comment->comment_content );
                            $message .= '\r\n \r\n Want to reply: ' . home_url( '/' ) . get_post_type( $post_id ) . '/' . $post_id;

                            dt_send_email(
                                $user->user_email,
                                'Disciple Tools: You were mentioned!',
                                $message
                            );
                        }

                        break;

                    case 'edit_comment' :
                        $notification_action = 'updated';

                        // share record with mentioned individual
                        Disciple_Tools_Contacts::add_shared( $post_type, $post_id, $mentioned_user_id );

                        // web notification
                        if ( dt_user_notification_is_enabled( 'mentions_web', $user_meta, $user->ID ) ) {

                            $notification_note = '<strong>' . strip_tags( $author_name ) . '</strong> mentioned you on <a href="' . home_url( '/' ) . get_post_type( $post_id ) . '/' . $post_id . '">'
                                . strip_tags( get_the_title( $post_id ) ) . '</a> saying, "' . strip_tags( $comment->comment_content ) . '" ';

                            $this->add_mention_notification(
                                $mentioned_user_id,
                                $source_user_id,
                                $post_id,
                                $comment_id,
                                $notification_action,
                                $notification_note,
                                $date_notified
                            );
                        }

                        // email notification
                        if ( dt_user_notification_is_enabled( 'mentions_email', $user_meta, $user->ID ) ) {

                            $message = strip_tags( $author_name ) . ' updated a comment that mentioned you saying: ' . strip_tags( $comment->comment_content );
                            $message .= '. Want to reply: ' . home_url( '/' ) . get_post_type( $post_id ) . '/' . $post_id;

                            dt_send_email(
                                $user->user_email,
                                'Disciple Tools: You were mentioned!',
                                $message
                            );
                        }

                        break;

                    case 'untrash_comment' :
                        $notification_action = 'untrashed';

                        // share record with mentioned individual
                        Disciple_Tools_Contacts::add_shared( $post_type, $post_id, $mentioned_user_id );

                        // web notification
                        if ( dt_user_notification_is_enabled( 'mentions_web', $user_meta, $user->ID ) ) {

                            $notification_note = '<strong>' . strip_tags( $author_name ) . '</strong> untrashed a comment that mentioned you on <a href="' . home_url( '/' ) . get_post_type( $post_id ) . '/' . $post_id . '">'
                                . strip_tags( get_the_title( $post_id ) ) . '</a> saying, "' . strip_tags( $comment->comment_content ) . '" ';

                            $this->add_mention_notification(
                                $mentioned_user_id,
                                $source_user_id,
                                $post_id,
                                $comment_id,
                                $notification_action,
                                $notification_note,
                                $date_notified
                            );
                        }

                        break;

                    case 'delete_comment' :
                    case 'trash_comment' :
                        $this->delete_mention_notification(
                            $mentioned_user_id,
                            $post_id,
                            $comment_id,
                            $date_notified
                        );
                        break;

                    default:
                        break;
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
    public function check_for_mention( $comment_content )
    {
        return preg_match( '/(?<= |^)@([^@ ]+)/', $comment_content );
    }

    /**
     * Parse @mention to find user match
     *
     * @param $comment_content
     *
     * @return bool|array
     */
    public function match_mention( $comment_content )
    {
        preg_match_all( '/(?<= |^)@([^@ ]+)/', $comment_content, $matches );

        $user_ids = [];
        foreach ( $matches[1] as $match ) {

            // trim punctuation
            $match = preg_replace( '/\W+/', '', $match );

            // get user_id by name match
            $user = get_user_by( 'login', $match );
            if ( $user ) {
                $user_ids[] = $user->ID;
            }
        }

        return empty( $user_ids ) ? false : $user_ids;
    }

    /**
     * Create notification activity
     *
     * @param int    $mentioned_user_id
     * @param int    $source_user_id
     * @param int    $post_id
     * @param int    $comment_id
     * @param string $notification_action
     * @param string $notification_note
     * @param        $date_notified
     */
    protected function add_mention_notification( int $mentioned_user_id, int $source_user_id, int $post_id, int $comment_id, string $notification_action, string $notification_note, $date_notified )
    {

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
    protected function delete_mention_notification( int $mentioned_user_id, int $post_id, int $comment_id, $date_notified )
    {

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
