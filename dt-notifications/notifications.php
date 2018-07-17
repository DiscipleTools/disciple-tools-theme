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
                    AND `field_key` = %s
                    AND `field_value` = %s
				;",
                $args['user_id'],
                $args['source_user_id'],
                $args['post_id'],
                $args['secondary_item_id'],
                $args['notification_name'],
                $args['notification_action'],
                $args['notification_note'],
                $args['date_notified'],
                $args['is_new'],
                $args['field_key'],
                $args['field_value']
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
                'notification_note'   => "", // notification note is generated (and translated)
                'date_notified'       => $args['date_notified'],
                'is_new'              => $args['is_new'],
                'field_key'           => $args['field_key'],
                'field_value'         => $args['field_value'],
            ],
            [ '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s' ]
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

        return $wpdb->last_error ? [
        'status' => false,
        'message' => $wpdb->last_error
        ] : [
        'status' => true,
        'rows_affected' => $wpdb->rows_affected
        ];
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

        return $wpdb->last_error ? [
        'status' => false,
        'message' => $wpdb->last_error
        ] : [
        'status' => true,
        'rows_affected' => $wpdb->rows_affected
        ];
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

        return $wpdb->last_error ? [
        'status' => false,
        'message' => $wpdb->last_error
        ] : [
        'status' => true,
        'rows_affected' => $wpdb->rows_affected
        ];
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
            // phpcs:ignore WordPress.WP.PreparedSQL.NotPrepared
            "SELECT * FROM `$wpdb->dt_notifications` WHERE user_id = %d $all_where ORDER BY date_notified DESC LIMIT %d OFFSET %d",
            $user_id,
            $limit,
            $page
        ), ARRAY_A );

        if ( $result ) {

            // user friendly timestamp
            foreach ( $result as $key => $value ) {
                $result[ $key ]['pretty_time'] = self::pretty_timestamp( $value['date_notified'] );
                $result[ $key ]["notification_note"] = self::get_notification_message( $value );
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

            $args = [
                'user_id'             => $user_id,
                'source_user_id'      => get_current_user_id(),
                'post_id'             => $post_id,
                'secondary_item_id'   => 0,
                'notification_name'   => 'share',
                'notification_action' => 'alert',
                'notification_note'   => '<a href="' . home_url( '/' ) . get_post_type( $post_id ) . '/' . $post_id . '" >' . strip_tags( get_the_title( $post_id ) ) . '</a> was shared with you.',
                'date_notified'       => current_time( 'mysql' ),
                'is_new'              => 1,
                'field_key'           => "comments",
                'field_value'         => ''
            ];
            $message = self::get_notification_message( $args ) . ' Click here to view ' . home_url( '/' ) . get_post_type( $post_id ) . '/' . $post_id;

            dt_notification_insert( $args );
            $user = get_userdata( $user_id );
            $subject = __( "Contact shared", 'disciple_tools' ) . '. #' . $post_id;
            dt_send_email( $user->user_email, $subject, $message );
        }
    }

    /**
     * Insert notification for subassigned
     *
     * @param int $user_id
     * @param int $post_id
     */
    public static function insert_notification_for_subassigned( int $user_id, int $post_id )
    {

        if ( $user_id != get_current_user_id() ) { // check if subassigned is not to self, else don't notify

            $args = [
                'user_id'             => $user_id,
                'source_user_id'      => get_current_user_id(),
                'post_id'             => $post_id,
                'secondary_item_id'   => 0,
                'notification_name'   => 'subassigned',
                'notification_action' => 'alert',
                'notification_note'   => '<a href="' . home_url( '/' ) . get_post_type( $post_id ) . '/' . $post_id . '" >' . strip_tags( get_the_title( $post_id ) ) . '</a> was sub-assigned to you.',
                'date_notified'       => current_time( 'mysql' ),
                'is_new'              => 1,
                'field_key'           => "comments",
                'field_value'         => ''
            ];
            $message = self::get_notification_message( $args ) . ' Click here to view ' . home_url( '/' ) . get_post_type( $post_id ) . '/' . $post_id;

            dt_notification_insert( $args );
            $user = get_userdata( $user_id );
            $subject = __( "Contact sub-assigned to you", 'disciple_tools' )  . '. #' . $post_id;
            dt_send_email( $user->user_email, $subject, $message );
        }
    }

    /**
     * Insert notification for assignment_declined
     *
     * @param int $user_who_declined
     * @param int $new_assigned_to
     * @param int $post_id
     */
    public static function insert_notification_for_assignment_declined( int $user_who_declined, int $new_assigned_to, int $post_id )
    {

        if ( $new_assigned_to != get_current_user_id() ) { // check if assignment_declined is not to self, else don't notify

            $args = [
                'user_id'             => $new_assigned_to,
                'source_user_id'      => get_current_user_id(),
                'post_id'             => $post_id,
                'secondary_item_id'   => $user_who_declined,
                'notification_name'   => 'assignment_declined',
                'notification_action' => 'alert',
                'notification_note'   => '',
                'date_notified'       => current_time( 'mysql' ),
                'is_new'              => 1,
                'field_key'           => "",
                'field_value'         => ''
            ];
            $message = self::get_notification_message( $args ) . '\r\n\r\nClick here to view ' . home_url( '/' ) . get_post_type( $post_id ) . '/' . $post_id;

            dt_notification_insert( $args );
            $user = get_userdata( $new_assigned_to );
            $subject = __( "Assignment declined", 'disciple_tools' )  . '. #' . $post_id;
            dt_send_email( $user->user_email, $subject, $message );
        }
    }




    public static function insert_notification_for_new_post( $post_type, $fields, $post_id ){
        // Don't fire off notifications when the contact represents a user.
        if ( $post_type === "contacts" ){
            $contact_type = get_post_meta( $post_id, "type", true );
            if ($contact_type === "user"){
                return;
            }
        }

        if ( isset( $fields["assigned_to"] ) ){
            $user_id = dt_get_user_id_from_assigned_to( $fields["assigned_to"] );
            if ( $user_id && $user_id != get_current_user_id()){
                $notification = [
                    'user_id'             => $user_id,
                    'source_user_id'      => get_current_user_id(),
                    'post_id'             => (int) $post_id,
                    'secondary_item_id'   => '',
                    'notification_name'   => 'created',
                    'notification_action' => 'alert',
                    'notification_note'   => '',
                    'date_notified'       => current_time( 'mysql' ),
                    'is_new'              => 1,
                    'field_key'           => $post_type,
                    'field_value'         => '',
                ];
                $message = self::get_notification_message( $notification );

                $user_meta = get_user_meta( $user_id );
                if ( dt_user_notification_is_enabled( 'new_assigned', 'web', $user_meta, $user_id ) ) {
                    dt_notification_insert( $notification );
                }
                if ( dt_user_notification_is_enabled( 'new_assigned', 'email', $user_meta, $user_id ) ) {
                    $message .= "\r\n\r\n";
                    $message .= 'Click here to reply: ' . home_url( '/' ) . $post_type . '/' . $post_id;
                    $user = get_userdata( $user_id );
                    dt_send_email(
                        $user->user_email,
                        sprintf( esc_html_x( 'New %s created and assigned to you', '', 'disciple_tools' ), Disciple_Tools_Posts::get_label_for_post_type( $post_type, true ) )  . '. #' . $post_id,
                        $message
                    );
                }
            }
        }
    }

    public static function insert_notification_for_post_update( $post_type, $fields, $old_fields, $fields_changed_keys ){
        if ( isset( $fields["type"] ) && $fields["type"] === "user"){
            return;
        }
        $notification_on_fields = [];
        //determine the fields that need a notification
        foreach ( $fields_changed_keys as $key ){
            if ( $key === "assigned_to" && isset( $fields["assigned_to"] ) &&
                ( !isset( $old_fields["assigned_to"] ) || $old_fields["assigned_to"] != $fields["assigned_to"] ) ){
                $notification_on_fields[] = "assigned_to";
            } elseif ( $key === "requires_update" && isset( $fields["requires_update"]["key"] ) &&
                ( $fields["requires_update"]["key"] === 'yes' || $fields["requires_update"]["key"] === '1' ) &&
                ( !isset( $old_fields["requires_update"] ) || $old_fields["requires_update"] != $fields["requires_update"] ) ){
                $notification_on_fields[] = "requires_update";
            } elseif ( strpos( $key, "contact_" ) === 0 && isset( $fields[$key] ) &&
                ( !isset( $old_fields[$key] ) || $old_fields[$key] != $fields[$key] )){
                $notification_on_fields[] = "contact_info_update";
            } elseif ( strpos( $key, "milestone_" ) === 0 && isset( $fields[$key] ) && $fields[$key]["key"] === "yes" &&
                ( !isset( $old_fields[$key] ) || $old_fields[$key] != $fields[$key] )){
                $notification_on_fields[] = "milestone";
            } elseif ( strpos( $key, "church_" ) === 0 && isset( $fields[$key]["key"] ) && $fields[$key]["key"] === "1" &&
                ( !isset( $old_fields[$key] ) || $old_fields[$key] != $fields[$key] )){
                $notification_on_fields[] = "milestone";
            }
        }

        if ( sizeof( $notification_on_fields ) > 0 ){
            $source_user_id = get_current_user_id();
            $subject = null;
            $followers = Disciple_Tools_Posts::get_users_following_post( $post_type, $fields["ID"] );
            $subassigned = $post_type === "contacts" ? Disciple_Tools_Posts::get_subassigned_users( $fields["ID"] ) : [];
            $assigned_to = isset( $fields["assigned_to"]["id"] ) ? $fields["assigned_to"]["id"] : false;
            foreach ( $followers as $follower ){
                $email = "";
                if ( $follower != $source_user_id ){
                    $user_meta = get_user_meta( $follower );
                    if ( $user_meta ){
                        $notification = [
                            'user_id'             => $follower,
                            'source_user_id'      => $source_user_id,
                            'post_id'             => (int) $fields["ID"],
                            'secondary_item_id'   => '',
                            'notification_name'   => 'mention',
                            'notification_action' => 'alert',
                            'notification_note'   => '',
                            'date_notified'       => current_time( 'mysql' ),
                            'is_new'              => 1,
                            'field_key'           => '',
                            'field_value'         => '',
                        ];

                        if ( in_array( "assigned_to", $notification_on_fields ) ){
                            if ( $assigned_to ){
                                if ( $follower === $assigned_to ){
                                    $notification["notification_name"] = "assigned_to";
                                    $notification_type = 'new_assigned';
                                    if ( dt_user_notification_is_enabled( $notification_type, 'web', $user_meta, $follower ) ) {
                                        dt_notification_insert( $notification );
                                    }
                                    if ( dt_user_notification_is_enabled( $notification_type, 'email', $user_meta, $follower ) ) {
                                        $subject = __( 'You have been assigned a new contact!', 'disciple_tools' );
                                        $email .= self::get_notification_message( $notification ) . "\n";
                                    }
                                } else {
                                    $notification["notification_name"] = "assigned_to_other";
                                    $notification['field_key'] = "assigned_to";
                                    $notification['field_value'] = $fields["assigned_to"]["id"];
                                    $notification_type = 'changes';
                                    if ( dt_user_notification_is_enabled( $notification_type, 'web', $user_meta, $follower ) ) {
                                        dt_notification_insert( $notification );
                                    }
                                    if ( dt_user_notification_is_enabled( $notification_type, 'email', $user_meta, $follower ) ) {
                                        $subject = __( 'Assignment on a contact has changed', 'disciple_tools' );
                                        $email .= self::get_notification_message( $notification ) . "\n";
                                    }
                                }
                            }
                        }
                        if ( in_array( "requires_update", $notification_on_fields ) ){
                            if ( $follower === $assigned_to || in_array( $follower, $subassigned ) ){
                                $notification["notification_name"] = "requires_update";
                                $notification_type = 'updates';
                                if ( dt_user_notification_is_enabled( $notification_type, 'web', $user_meta, $follower ) ) {
                                    dt_notification_insert( $notification );
                                }
                                if ( dt_user_notification_is_enabled( $notification_type, 'email', $user_meta, $follower ) ) {
                                    $subject = $subject ?? __( 'Update requested!', 'disciple_tools' );
                                    $email .= self::get_notification_message( $notification ) . "\n";
                                }
                            }
                        }
                        if ( in_array( "milestone", $notification_on_fields )){
                            $notification["notification_name"] = "milestone";
                            $notification_type = 'milestones';
                            if ( dt_user_notification_is_enabled( $notification_type, 'web', $user_meta, $follower ) ) {
                                dt_notification_insert( $notification );
                            }
                            if ( dt_user_notification_is_enabled( $notification_type, 'email', $user_meta, $follower ) ) {
                                $subject = $subject ?? __( 'Milestones update', 'disciple_tools' );
                                $email .= self::get_notification_message( $notification ) . "\n";
                            }
                        }
                        if ( in_array( "contact_info_update", $notification_on_fields )){
                            $notification["notification_name"] = "contact_info_update";
                            $notification_type = 'changes';
                            if ( dt_user_notification_is_enabled( $notification_type, 'web', $user_meta, $follower ) ) {
                                dt_notification_insert( $notification );
                            }
                            if ( dt_user_notification_is_enabled( $notification_type, 'email', $user_meta, $follower ) ) {
                                $subject = $subject ?? __( 'Contact info changed', 'disciple_tools' );
                                $email .= self::get_notification_message( $notification ) . "\n";
                            }
                        }
                        if ( $subject && $email ){
                            $email .= "\r\n\r\n";
                            $email .= 'Click here to view: ' . home_url( '/' ) . $post_type . '/' . $fields["ID"] . " \n";
                            $user = get_userdata( $follower );
                            dt_send_email(
                                $user->user_email,
                                $subject  . '. #' . $fields["ID"],
                                $email
                            );
                        }
                    }
                }
            }
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
        $wpdb->update(
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


    /**
     * Format comment replacing @mentions with a readable value
     *
     * @param $comment_content
     *
     * @return mixed
     */
    public static function format_comment( $comment_content ){
        preg_match_all( '/\@\[(.*?)\]\((.+?)\)/', $comment_content, $matches );
        foreach ( $matches[0] as $match_key => $match ){
            $comment_content = str_replace( $match, '@' . $matches[1][$match_key], $comment_content );
        }
        return $comment_content;
    }


    public static function get_notification_message( $notification ){
        $object_id = $notification["post_id"];
        $post = get_post( $object_id );
        $post_title = isset( $post->post_title ) ? sanitize_text_field( $post->post_title ) : "";
        $notification_note = $notification["notification_note"];
        $link = '<a href="' . home_url( '/' ) . get_post_type( $object_id ) . '/' . $object_id . '">' . $post_title . '</a>';
        if ( $notification["notification_name"] === "created" ) {
            $notification_note = sprintf( esc_html_x( '%s was created and assigned to you.', '', 'disciple_tools' ), $link );
        } elseif ( $notification["notification_name"] === "assigned_to" ) {
            $notification_note = __( 'You have been assigned', 'disciple_tools' ) . ' ' . $link;
        } elseif ( $notification["notification_name"] === "assigned_to_other" ) {
            $source_user = get_userdata( $notification["source_user_id"] );
            $source_user_name = $source_user ? "@" . $source_user->display_name : '';
            $new_assigned = get_userdata( $notification["field_value"] );
            $new_assigned_name = $new_assigned ? '@' . $new_assigned->display_name : '';
            $notification_note = sprintf( esc_html_x( '%1$s assigned %2$s to %3$s', 'user1 assigned contact1 to user2', 'disciple_tools' ), $source_user_name, $link, $new_assigned_name );
        } elseif ( $notification["notification_name"] ==="share" ){
            $source_user = get_userdata( $notification["source_user_id"] );
            $display_name = $source_user ? $source_user->display_name : __( "System", "disciple_tools" );
            $notification_note = $display_name . ' ' . sprintf( esc_html_x( 'shared %s with you.', '', 'disciple_tools' ), $link );
        } elseif ( $notification["notification_name"] ==="mention" ){
            $source_user = get_userdata( $notification["source_user_id"] );
            $comment = get_comment( $notification["secondary_item_id"] );
            $comment_content = $comment ? self::format_comment( $comment->comment_content ) : "";
            $display_name = $source_user ? $source_user->display_name : __( "System", "disciple_tools" );
            $notification_note = $display_name . ' ' . sprintf( esc_html_x( 'mentioned you on %s saying', '', 'disciple_tools' ), $link ) . ' "' . $comment_content . '"';
        } elseif ( $notification["notification_name"] ==="comment" ){
            $source_user = get_userdata( $notification["source_user_id"] );
            $comment = get_comment( $notification["secondary_item_id"] );
            $comment_content = $comment ? self::format_comment( $comment->comment_content ) : "";
            $display_name = $source_user ? $source_user->display_name : __( "System", "disciple_tools" );
            $notification_note = $display_name . ' ' . sprintf( esc_html_x( 'commented on %s saying', '', 'disciple_tools' ), $link ) . ' "' . $comment_content . '"';
        } elseif ( $notification["notification_name"] === "subassigned" ){
            $source_user = get_userdata( $notification["source_user_id"] );
            $display_name = $source_user ? $source_user->display_name : __( "System", "disciple_tools" );
            $notification_note = $display_name . ' ' . sprintf( esc_html_x( 'subassigned %s to you.', '', 'disciple_tools' ), $link );
        } elseif ( $notification["notification_name"] ==="milestone" ){
            $meta_key = $notification["field_key"] ?? '';
            $meta_value = $notification["field_value"] ?? '';
            switch ( $meta_key ) {
                case 'milestone_belief':
                    $element = __( '"Belief" Milestone', 'disciple_tools' );
                    break;
                case 'milestone_can_share':
                    $element = __( '"Can Share" Milestone', 'disciple_tools' );
                    break;
                case 'milestone_sharing':
                    $element = __( '"Actively Sharing" Milestone', 'disciple_tools' );
                    break;
                case 'milestone_baptized':
                    $element = __( '"Baptized" Milestone', 'disciple_tools' );
                    break;
                case 'milestone_baptizing':
                    $element = __( '"Baptizing" Milestone', 'disciple_tools' );
                    break;
                case 'milestone_in_group':
                    $element = __( '"Is in a group" Milestone', 'disciple_tools' );
                    break;
                case 'milestone_planting':
                    $element = __( '"Planting a group" Milestone', 'disciple_tools' );
                    break;
                default:
                    $element = __( 'A Milestone', 'disciple_tools' );
                    break;
            }
            if ( $meta_value === "added" ){
                $element .= ' ' . __( 'has been added to', 'disciple_tools' );
            } elseif ( $meta_value === "removed") {
                $element .= ' ' . __( 'has been removed from', 'disciple_tools' );
            } else {
                $element .= ' ' . __( 'was changed for', 'disciple_tools' );
            }
            $source_user = get_userdata( $notification["source_user_id"] );
            $display_name = $source_user ? $source_user->display_name : __( "System", "disciple_tools" );
            $notification_note = $element . ' ' . $link  . ' ' . __( 'by', 'disciple_tools' ) . ' ' .
                '<strong>' . $display_name . '</strong>';

        } elseif ( $notification["notification_name"] ==="requires_update" ) {
            $notification_note = __( 'An update is requested on:', 'disciple_tools' ) . " ". $link;
        } elseif ( $notification["notification_name"] ==="contact_info_update" ){
            $meta_key = $notification["field_key"] ?? 'contact';
            if ( strpos( $meta_key, "address" ) === 0 ) {
                $element = __( 'Address details on', 'disciple_tools' );
            } elseif ( strpos( $meta_key, "contact" ) === 0 ) {
                $element = __( 'Contact details on', 'disciple_tools' );
            } else {
                $element = __( 'Contact details on', 'disciple_tools' );
            }
            $source_user = get_userdata( $notification["source_user_id"] );
            $display_name = $source_user ? $source_user->display_name : __( "System", "disciple_tools" );
            $notification_note = $element . ' ' . $link . ' ' . __( 'were modified by', 'disciple_tools' ) . " " .
            '<strong>' . $display_name . '</strong>';
        } elseif ( $notification["notification_name"] === "assignment_declined" ){
            $user_who_declined = get_userdata( $notification["source_user_id"] );
            $notification_note = $user_who_declined->display_name . ' ' . __( "declined assignment on contact: ", 'disciple_tools' ) . ' ' . $link;
        }
        return $notification_note;
    }
}
