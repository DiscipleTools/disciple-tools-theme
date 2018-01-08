<?php

/**
 * Contains create, update and delete functions for notifications, wrapping access to
 * the database
 *
 * @class      Disciple_Tools_Notifications
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple_Tools
 * @author     Chasm.Solutions & Kingdom.Training
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * @since 0.1.0
 * @see   Disciple_Tools_Activity_Log_API::insert
 *
 * @param array $args
 *
 * @return void
 */
function dt_notification_insert( $args = [] )
{
    Disciple_Tools_Notifications::insert_notification( $args );
}

/**
 * @param array $args
 */
function dt_notification_delete( $args = [] )
{
    Disciple_Tools_Notifications::delete_notification( $args );
}

/**
 * @param array $args
 */
function dt_notification_delete_by_post( $args = [] )
{
    Disciple_Tools_Notifications::delete_by_post( $args );
}

/**
 * Class Disciple_Tools_Notifications
 */
class Disciple_Tools_Notifications
{

    /**
     * Disciple_Tools_Admin_Menus The single instance of Disciple_Tools_Admin_Menus.
     *
     * @var     object
     * @access    private
     * @since     0.1.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_Notifications Instance
     * Ensures only one instance of Disciple_Tools_Notifications is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return Disciple_Tools_Notifications instance
     */
    public static function instance()
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     *
     * @access  public
     * @since   0.1.0
     */
    public function __construct()
    {

    } // End __construct()

    /**
     * Insert statement
     *
     * @since 0.1.0
     *
     * @param array $args
     *
     * @return void
     */
    public static function insert_notification( $args )
    {
        global $wpdb;

        // Make sure for non duplicate.
        $check_duplicate = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT
                    `id`
                FROM
                    `$wpdb->dt_notifications`
                WHERE
                    `user_id` = %s
                    AND `source_user_id` = %s
                    AND `post_id` = %s
                    AND `secondary_item_id` = %s
                    AND `notification_name` = %s
                    AND `notification_action` = %s
                    AND `notification_note` = %s
                    AND `date_notified` = %s
                    AND `is_new` = %s
				;",
                $args['user_id'],
                $args['source_user_id'],
                $args['post_id'],
                $args['secondary_item_id'],
                $args['notification_name'],
                $args['notification_action'],
                $args['notification_note'],
                $args['date_notified'],
                $args['is_new']
            )
        );

        if ( $check_duplicate ) { // don't create a duplicate record
            return;
        }

        if ( $args['user_id'] == $args['source_user_id'] ) { // check if source of the event and notification target are the same, if so, don't create notification. i.e. I don't want notifications of my own actions.
            return;
        }

        $wpdb->insert(
            $wpdb->dt_notifications,
            [
                'user_id'             => $args['user_id'],
                'source_user_id'      => $args['source_user_id'],
                'post_id'             => $args['post_id'],
                'secondary_item_id'   => $args['secondary_item_id'],
                'notification_name'   => $args['notification_name'],
                'notification_action' => $args['notification_action'],
                'notification_note'   => $args['notification_note'],
                'date_notified'       => $args['date_notified'],
                'is_new'              => $args['is_new'],
            ],
            [ '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%d' ]
        );

        // Fire action after insert.
        do_action( 'dt_insert_notification', $args );
    }

    /**
     * Delete single notification
     *
     * @since 0.1.0
     *
     * @param array $args
     *
     * @return void
     */
    public static function delete_notification( $args )
    {
        global $wpdb;

        $args = wp_parse_args(
            $args,
            [
                'user_id'           => '',
                'post_id'           => '',
                'secondary_item_id' => '',
                'notification_name' => '',
                'date_notified'     => '',
            ]
        );

        $wpdb->delete(
            $wpdb->dt_notifications,
            [
                'user_id'           => $args['user_id'],
                'post_id'           => $args['post_id'],
                'secondary_item_id' => $args['secondary_item_id'],
                'notification_name' => $args['notification_name'],
                'date_notified'     => $args['date_notified'],
            ]
        );

        // Final action on insert.
        do_action( 'dt_delete_notification', $args );
    }

    /**
     * Delete all notifications for a post with a certain notification name
     *
     * @since 0.1.0
     *
     * @param array $args
     *
     * @return void
     */
    public static function delete_by_post( $args )
    {
        global $wpdb;

        $args = wp_parse_args(
            $args,
            [
                'post_id'           => '',
                'notification_name' => '',
            ]
        );

        $wpdb->delete(
            $wpdb->dt_notifications,
            [
                'post_id'           => $args['post_id'],
                'notification_name' => $args['notification_name'],
            ]
        );

        // Final action on insert.
        do_action( 'dt_delete_post_notifications', $args );
    }

    /**
     * Mark the is_new field to 0 after user has viewed notification
     *
     * @param $notification_id
     *
     * @return array
     */
    public static function mark_viewed( $notification_id )
    {
        global $wpdb;

        $wpdb->update(
            $wpdb->dt_notifications,
            [
                'is_new' => 0,
            ],
            [
                'id' => $notification_id,
            ]
        );

        return $wpdb->last_error ? [ 'status' => false, 'message' => $wpdb->last_error ] : [ 'status' => true, 'rows_affected' => $wpdb->rows_affected ];
    }

    /**
     * Mark the is_new field to 0 after user has viewed notification
     *
     * @param $notification_id
     *
     * @return array
     */
    public static function mark_unread( $notification_id )
    {
        global $wpdb;

        $wpdb->update(
            $wpdb->dt_notifications,
            [
                'is_new' => 1,
            ],
            [
                'id' => $notification_id,
            ]
        );

        return $wpdb->last_error ? [ 'status' => false, 'message' => $wpdb->last_error ] : [ 'status' => true, 'rows_affected' => $wpdb->rows_affected ];
    }

    /**
     * Mark all as viewed by user_id
     *
     * @param $user_id int
     *
     * @return array
     */
    public static function mark_all_viewed( int $user_id )
    {
        global $wpdb;

        $wpdb->update(
            $wpdb->dt_notifications,
            [
                'is_new' => 0,
            ],
            [
                'user_id' => $user_id,
            ]
        );

        return $wpdb->last_error ? [ 'status' => false, 'message' => $wpdb->last_error ] : [ 'status' => true, 'rows_affected' => $wpdb->rows_affected ];
    }

    /**
     * Get notifications
     *
     * @param bool $all
     * @param int  $page
     * @param int  $limit
     *
     * @return array
     */
    public static function get_notifications( bool $all, int $page, int $limit )
    {
        global $wpdb;

        $all_where = '';
        if ( !$all ) {
            $all_where = " AND is_new = '1'";
        }

        $user_id = get_current_user_id();

        $result = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM `$wpdb->dt_notifications` WHERE user_id = %d $all_where ORDER BY date_notified DESC LIMIT %d OFFSET %d", // @codingStandardsIgnoreLine
            $user_id,
            $limit,
            $page
        ), ARRAY_A );

        if ( $result ) {

            // user friendly timestamp
            foreach ( $result as $key => $value ) {
                $result[ $key ]['pretty_time'] = self::pretty_timestamp( $value['date_notified'] );
            }

            return [
                'status' => true,
                'result' => $result,
            ];
        } else {
            return [
                'status'  => false,
                'message' => 'No notifications',
            ];
        }
    }

    /**
     * Pretty time stamp helper function
     *
     * @param $timestamp
     *
     * @return false|string
     */
    public static function pretty_timestamp( $timestamp )
    {
        $current_time = current_time( 'mysql' );
        $one_hour_ago = date( 'Y-m-d H:i:s', strtotime( '-1 hour', strtotime( $current_time ) ) );
        $yesterday = date( 'Y-m-d', strtotime( '-1 day', strtotime( $current_time ) ) );
        $seven_days_ago = date( 'Y-m-d', strtotime( '-7 days', strtotime( $current_time ) ) );

        if ( $timestamp > $one_hour_ago ) {
            $current = new DateTime( $current_time );
            $stamp = new DateTime( $timestamp );
            $diff = date_diff( $current, $stamp );
            $friendly_time = date( "i", mktime( $diff->h, $diff->i, $diff->s ) ) . ' minutes ago';
        } elseif ( $timestamp > $yesterday ) {
            $friendly_time = date( "g:i a", strtotime( $timestamp ) );
        } elseif ( $timestamp > $seven_days_ago ) {
            $friendly_time = date( "l g:i a", strtotime( $timestamp ) );
        } else {
            $friendly_time = date( 'F j, Y, g:i a', strtotime( $timestamp ) );
        }

        return $friendly_time;
    }

    /**
     * Get user notifications
     *
     * @return array
     */
    public static function get_new_notifications_count()
    {
        global $wpdb;

        $user_id = get_current_user_id();

        $result = $wpdb->get_var( $wpdb->prepare(
            "SELECT
                count(id)
            FROM
                `$wpdb->dt_notifications`
            WHERE
                user_id = %d
                AND is_new = '1'",
            $user_id
        ) );

        if ( $result ) {
            return [
                'status' => true,
                'result' => (int) $result,
            ];
        } else {
            return [
                'status'  => false,
                'message' => 'No notifications',
            ];
        }
    }

    /**
     * Get the @mention message content
     *
     * @param $comment_id
     *
     * @return array|null|WP_Post
     */
    public static function get_at_mention_message( $comment_id )
    {
        return get_post( $comment_id );
    }

    /**
     * Insert notification for share
     *
     * @param int $user_id
     * @param int $post_id
     */
    public static function insert_notification_for_share( int $user_id, int $post_id )
    {

        if ( $user_id != get_current_user_id() ) { // check if share is not to self, else don't notify

            dt_notification_insert(
                [
                    'user_id'             => $user_id,
                    'source_user_id'      => get_current_user_id(),
                    'post_id'             => $post_id,
                    'secondary_item_id'   => 0,
                    'notification_name'   => 'share',
                    'notification_action' => 'alert',
                    'notification_note'   => '<a href="' . home_url( '/' ) . get_post_type( $post_id ) . '/' . $post_id . '" >' . strip_tags( get_the_title( $post_id ) ) . '</a> was shared with you.',
                    'date_notified'       => current_time( 'mysql' ),
                    'is_new'              => 1,
                ]
            );
        }
    }

    /**
     * Process post notifications for a user who has visited the post. This removes the new status for all notifications for this post
     *
     * @param $post_id
     */
    public static function process_new_notifications( $post_id )
    {
        global $wpdb;
        $user_id = get_current_user_id();

        // change new notifications to viewed
        $results = $wpdb->update(
            $wpdb->dt_notifications,
            [
                'is_new' => 0,
            ],
            [
                'post_id' => $post_id,
                'user_id' => $user_id,
            ],
            [
                '%d'
            ],
            [
                '%d',
                '%d'
            ]
        );

    }


}
