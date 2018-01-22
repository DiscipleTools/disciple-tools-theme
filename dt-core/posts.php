<?php
/**
 * Contains create, update and delete functions for posts, wrapping access to
 * the database
 *
 * @package  Disciple_Tools
 * @since    0.1.0
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

/**
 * Class Disciple_Tools_Posts
 * Functions for creating, finding, updating or deleting posts
 */
class Disciple_Tools_Posts
{

    /**
     * Disciple_Tools_Posts constructor.
     */
    public function __construct()
    {
    }

    /**
     * Permissions for interaction with contacts Custom Post Types
     * Example. Role permissions available on contacts:
     *  access_contacts
     *  create_contacts
     *  view_any_contacts
     *  assign_any_contacts  //assign contacts to others
     *  update_any_contacts  //update any contact
     *  delete_any_contacts  //delete any contact
     */

    /**
     * @param string $post_type
     *
     * @return bool
     */
    public static function can_access( string $post_type )
    {
        return current_user_can( "access_" . $post_type );
    }

    /**
     * @param string $post_type
     *
     * @return bool
     */
    public static function can_view_all( string $post_type )
    {
        return current_user_can( "view_any_" . $post_type );
    }

    /**
     * @param string $post_type
     *
     * @return bool
     */
    public static function can_create( string $post_type )
    {
        return current_user_can( 'create_' . $post_type );
    }

    /**
     * @param string $post_type
     *
     * @return bool
     */
    public static function can_delete( string $post_type )
    {
        return current_user_can( 'delete_any_' . $post_type );
    }

    /**
     * A user can view the record if they have the global permission or
     * if the post if assigned or shared with them
     *
     * @param string $post_type
     * @param int    $post_id
     *
     * @return bool
     */
    public static function can_view( string $post_type, int $post_id )
    {
        global $wpdb;
        if ( current_user_can( 'view_any_' . $post_type ) ) {
            return true;
        } else {
            $user = wp_get_current_user();
            $assigned_to = get_post_meta( $post_id, "assigned_to", true );
            if ( $assigned_to && $assigned_to === "user-" . $user->ID ) {
                return true;
            } else {
                $shares = $wpdb->get_results( $wpdb->prepare(
                    "SELECT
                        *
                    FROM
                        `$wpdb->dt_share`
                    WHERE
                        post_id = %s",
                    $post_id
                ), ARRAY_A );
                foreach ( $shares as $share ) {
                    if ( (int) $share['user_id'] === $user->ID ) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * A user can update the record if they have the global permission or
     * if the post if assigned or shared with them
     *
     * @param string $post_type
     * @param int    $post_id
     *
     * @return bool
     */
    public static function can_update( string $post_type, int $post_id )
    {
        global $wpdb;
        if ( current_user_can( 'update_any_' . $post_type ) ) {
            return true;
        } else {
            $user = wp_get_current_user();
            $assigned_to = get_post_meta( $post_id, "assigned_to", true );
            if ( isset( $assigned_to ) && $assigned_to === "user-" . $user->ID ) {
                return true;
            } else {
                $shares = $wpdb->get_results( $wpdb->prepare(
                    "SELECT
                        *
                    FROM
                        `$wpdb->dt_share`
                    WHERE
                        post_id = %s",
                    $post_id
                ), ARRAY_A );
                foreach ( $shares as $share ) {
                    if ( (int) $share['user_id'] === $user->ID ) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param string $post_type
     * @param int    $user_id
     *
     * @return array
     */
    public static function get_posts_shared_with_user( string $post_type, int $user_id )
    {
        global $wpdb;
        $shares = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $wpdb->dt_share as shares
                INNER JOIN $wpdb->posts as posts
                WHERE user_id = %d
                AND shares.post_id = posts.ID
                AND posts.post_type = %s",
                $user_id,
                $post_type
            ),
            ARRAY_A
        );
        $list = [];
        foreach ( $shares as $share ) {
            $post = get_post( $share["post_id"] );
            if ( isset( $post->post_type ) && $post->post_type === $post_type ) {
                $list[] = $post;
            }
        }

        return $list;
    }

    /**
     * @param string $post_type
     * @param int    $group_id
     * @param string $comment
     *
     * @return false|int|\WP_Error
     */
    public static function add_post_comment( string $post_type, int $group_id, string $comment )
    {
        if ( !self::can_update( $post_type, $group_id ) ) {
            return new WP_Error( __FUNCTION__, __( "You do not have permission for this" ), [ 'status' => 403 ] );
        }
        $user = wp_get_current_user();
        $user_id = get_current_user_id();
        $comment_data = [
            'comment_post_ID'      => $group_id,
            'comment_content'      => $comment,
            'user_id'              => $user_id,
            'comment_author'       => $user->display_name,
            'comment_author_url'   => $user->user_url,
            'comment_author_email' => $user->user_email,
            'comment_type'         => 'comment',
        ];

        return wp_new_comment( $comment_data );
    }

    public static function format_connection_message( $p2p_id, $action = 'connected to', $activity ){
        // Get p2p record
        $p2p_record = p2p_get_connection( (int) $p2p_id ); // returns object

        if ( !$p2p_record ){
            if ($activity->field_type === "connection from"){
                $from_title = get_the_title( $activity->object_id );
                $to_title = get_the_title( $activity->meta_value );
            } elseif ( $activity->field_type === "connection to"){
                $from_title = get_the_title( $activity->meta_value );
                $to_title = get_the_title( $activity->object_id );
            } else {
                return "CONNECTION DESTROYED";
            }
        } else {
            $p2p_from = get_post( $p2p_record->p2p_from, ARRAY_A );
            $p2p_to = get_post( $p2p_record->p2p_to, ARRAY_A );
            $from_title = $p2p_from["post_title"];
            $to_title = $p2p_to["post_title"];
        }
        $object_note_from = '';
        $object_note_to = '';

        // Build variables
        $p2p_type = $activity->meta_key;
        if ($p2p_type === "baptizer_to_baptized"){
            if ($action === "connected to"){
                $object_note_to = __( 'Baptized', 'disciple_tools' ) . ' ' . $from_title;
                $object_note_from = __( 'Baptized by', 'disciple_tools' ) . ' ' . $to_title;
            } else {
                $object_note_to = __( 'Did not baptize', 'disciple_tools' ) . ' ' . $from_title;
                $object_note_from = __( 'Not baptized by', 'disciple_tools' ) . ' ' . $to_title;
            }
        } else if ($p2p_type === "contacts_to_groups"){
            if ($action == "connected to"){
                $object_note_to = __( 'Added to group', 'disciple_tools' ) . ' ' . $to_title;
                $object_note_from = __( 'Added to group', 'disciple_tools' ) . ' ' . $to_title;
            } else {
                $object_note_to = __( 'Removed from group', 'disciple_tools' ) . ' ' . $to_title;
                $object_note_from = __( 'Removed from group', 'disciple_tools' ) . ' ' . $to_title;
            }
        }
        else if ($p2p_type === "contacts_to_peoplegroups"){
            if ($action == "connected to"){
                $object_note_to = __( 'Added to people group:', 'disciple_tools' ) . ' ' . $to_title;
                $object_note_from = __( 'Added to people group:', 'disciple_tools' ) . ' ' . $to_title;
            } else {
                $object_note_to = __( 'Removed from people group:', 'disciple_tools' ) . ' ' . $to_title;
                $object_note_from = __( 'Removed from people group:', 'disciple_tools' ) . ' ' . $to_title;
            }
        }
        else if ( $p2p_type === "contacts_to_contacts"){
            if ($action === "connected to"){
                $object_note_to = __( 'Coaching', 'disciple_tools' ) . ' ' . $from_title;
                $object_note_from = __( 'Coached by', 'disciple_tools' ) . ' ' . $to_title;
            } else {
                $object_note_to = __( 'No longer coaching', 'disciple_tools' ) . ' ' . $from_title;
                $object_note_from = __( 'No longed coached by', 'disciple_tools' ) . ' ' . $to_title;
            }
        } else if ( $p2p_type === "contacts_to_subassigned"){
            if ($action === "connected to"){
                $object_note_to = __( 'Sub-assigned', 'disciple_tools' ) . ' ' . $from_title;
                $object_note_from = __( 'Sub-assigned on', 'disciple_tools' ) . ' ' . $to_title;
            } else {
                $object_note_to = __( 'Removed sub-assigned', 'disciple_tools' ) . ' ' . $from_title;
                $object_note_from = __( 'No longed sub-assigned on', 'disciple_tools' ) . ' ' . $to_title;
            }
        } else if ( $p2p_type === "contacts_to_locations"){
            if ($action == "connected to"){
                $object_note_to = __( 'Added to location', 'disciple_tools' ) . ' ' . $to_title;
                $object_note_from = __( 'Added to location', 'disciple_tools' ) . ' ' . $to_title;
            } else {
                $object_note_to = __( 'Removed from location', 'disciple_tools' ) . ' ' . $to_title;
                $object_note_from = __( 'Removed from location', 'disciple_tools' ) . ' ' . $to_title;
            }
        } else {
            if ($action == "connected to"){
                $object_note_to = __( 'Connected to', 'disciple_tools' ) . ' ' . $to_title;
                $object_note_from = __( 'Connected on', 'disciple_tools' ) . ' ' . $to_title;
            } else {
                $object_note_to = __( 'Removed from', 'disciple_tools' ) . ' ' . $to_title;
                $object_note_from = __( 'Removed from', 'disciple_tools' ) . ' ' . $to_title;
            }
        }

        if ( $activity->field_type === "connection from" ){
            return $object_note_from;
        } else {
            return $object_note_to;
        }
    }

    public static function format_activity_message( $activity, $fields) {
        $message = "";
        if ( $activity->action == "field_update" ){
            if ( isset( $fields[$activity->meta_key] ) ){
                if ( $activity->meta_key === "assigned_to"){
                    $meta_array = explode( '-', $activity->meta_value ); // Separate the type and id
                    if ( isset( $meta_array[1] ) ) {
                        $user = get_user_by( "ID", $meta_array[1] );
                        $message = __( 'Assigned to', 'disciple_tools' ) . ": " . ( $user ? $user->display_name : __( "Nobody", 'disciple_tools' ) );
                    }
                }
                if ( $fields[$activity->meta_key]["type"] === "text"){
                    $message = $fields[$activity->meta_key]["name"] . " " . __( "changed to", 'disciple_tools' ) . ": " .$activity->meta_value;
                }
                if ( $fields[$activity->meta_key]["type"] === "key_select" ){
                    if ( isset( $fields[$activity->meta_key]["default"][$activity->meta_value] ) ){
                        $message = $fields[$activity->meta_key]["name"] . ": " . $fields[$activity->meta_key]["default"][$activity->meta_value] ?? $activity->meta_value;
                    } else {
                        $message = $fields[$activity->meta_key]["name"] . ": " . $activity->meta_value;
                    }
                    $tets = "";
                }
                if ($fields[$activity->meta_key]["type"] === "number"){
                    $message = $fields[$activity->meta_key]["name"] . ": " . $activity->meta_value;
                }
            } else {
                if (strpos( $activity->meta_key, "_details" ) !== false ) {
                    $meta_value = maybe_unserialize( $activity->meta_value );
                    $original_key = str_replace( "_details", "", $activity->meta_key );
                    $original = get_post_meta( $activity->object_id, $original_key, true );
                    $name = $fields[ $activity->meta_key ]['name'] ?? "";
                    $object_note = $name . ' "'. $original .'" ';
                    if ( is_array( $meta_value ) ){
                        foreach ($meta_value as $k => $v){
                            $prev_value = $activity->old_value;
                            if (is_array( $prev_value ) && isset( $prev_value[ $k ] ) && $prev_value[ $k ] == $v){
                                continue;
                            }
                            if ($k === "verified") {
                                $object_note .= $v ? __( "verified", 'disciple_tools' ) : __( "not verified", 'disciple_tools' );
                            }
                            if ($k === "invalid") {
                                $object_note .= $v ? __( "invalidated", 'disciple_tools' ) : __( "not invalidated", 'disciple_tools' );
                            }
                            $object_note .= ', ';
                        }
                    } else {
                        $object_note = $meta_value;
                    }
                    $object_note = chop( $object_note, ', ' );
                    $message = $object_note;
                } else if ( $activity->meta_key == "title" ){
                    $message = __( "Name changed to:", 'disciple_tools' ) . ' ' . $activity->meta_value;
                } else if ( $activity->meta_key === "_sample"){
                    $message = __( "Created from Demo Plugin", "disciple_tools" );
                } else {
                    $message = "Deleted field";
                }
            }
        }
        if ( $activity->object_subtype === "p2p" ){
            $message = self::format_connection_message( $activity->meta_id, $activity->action, $activity );
        }

        return $message;
    }

    /**
     * @param string $post_type
     * @param int    $post_id
     *
     * @return array|null|object|\WP_Error
     */
    public static function get_post_activity( string $post_type, int $post_id, array $fields )
    {
        global $wpdb;
        if ( !self::can_view( $post_type, $post_id ) ) {
            return new WP_Error( __FUNCTION__, __( "No permissions to read group" ), [ 'status' => 403 ] );
        }
        $activity = $wpdb->get_results( $wpdb->prepare(
            "SELECT
                *
            FROM
                `$wpdb->dt_activity_log`
            WHERE
                `object_type` = %s
                AND `object_id` = %s",
            $post_type,
            $post_id
        ) );
        foreach ( $activity as $a ) {
            $a->object_note = self::format_activity_message( $a, $fields );
            if ( isset( $a->user_id ) && $a->user_id > 0 ) {
                $user = get_user_by( "id", $a->user_id );
                if ( $user ){
                    $a->name =$user->display_name;
                }
            }
        }

        return $activity;
    }

    /**
     * Get post comments
     *
     * @param string $post_type
     * @param int    $post_id
     *
     * @return array|int|\WP_Error
     */
    public static function get_post_comments( string $post_type, int $post_id )
    {
        if ( !self::can_view( $post_type, $post_id ) ) {
            return new WP_Error( __FUNCTION__, __( "No permissions to read group" ), [ 'status' => 403 ] );
        }
        $comments = get_comments( [ 'post_id' => $post_id ] );

        return $comments;
    }

    /**
     * Get viewable in compact form
     *
     * @param string $post_type
     * @param string $search_string
     *
     * @return array|\WP_Error|\WP_Query
     */
    public static function get_viewable_compact( string $post_type, string $search_string )
    {
        if ( !self::can_access( $post_type ) ) {
            return new WP_Error( __FUNCTION__, sprintf( __( "You do not have access to these %s" ), $post_type ), [ 'status' => 403 ] );
        }
        $current_user = wp_get_current_user();
        $compact = [];

        $query_args = [
            'post_type' => $post_type,
            's'         => $search_string,
            'posts_per_page' => 30,
        ];
        $shared_with_user = [];
        if ( !self::can_view_all( $post_type ) ) {
            $shared_with_user = self::get_posts_shared_with_user( $post_type, $current_user->ID );

            $query_args['meta_key'] = 'assigned_to';
            $query_args['meta_value'] = "user-" . $current_user->ID;
        }
        $posts = new WP_Query( $query_args );
        if ( is_wp_error( $posts ) ) {
            return $posts;
        }
        foreach ( $posts->posts as $post ) {
            $compact[] = [
            "ID" => $post->ID,
            "name" => $post->post_title
            ];
        }
        $post_ids = array_map(
            function( $post ) {
                return $post->ID;
            },
            $posts->posts
        );
        foreach ( $shared_with_user as $shared ) {
            if ( !in_array( $shared->ID, $post_ids ) ) {
                $compact[] = [
                "ID" => $shared->ID,
                "name" => $shared->post_title
                ];
            }
        }

        return [
        "total" => $posts->found_posts,
        "posts" => $compact
        ];
    }

    /**
     * @param string $post_type
     *
     * @return array|\WP_Error|\WP_Query
     */
    public static function get_viewable( string $post_type )
    {
        if ( !self::can_access( $post_type ) ) {
            return new WP_Error( __FUNCTION__, sprintf( __( "You do not have access to these %s" ), $post_type ), [ 'status' => 403 ] );
        }
        $current_user = wp_get_current_user();

        $query_args = [
            'post_type' => $post_type,
            'nopaging'  => true,
        ];
        $posts_shared_with_user = [];
        if ( !self::can_view_all( $post_type ) ) {
            $posts_shared_with_user = self::get_posts_shared_with_user( $post_type, $current_user->ID );

            $query_args['meta_key'] = 'assigned_to';
            $query_args['meta_value'] = "user-" . $current_user->ID;
        }
        $queried_posts = new WP_Query( $query_args );
        if ( is_wp_error( $queried_posts ) ) {
            return $queried_posts;
        }
        $posts = $queried_posts->posts;
        $post_ids = array_map(
            function( $post ) {
                return $post->ID;
            },
            $posts
        );
        //add shared posts to the list avoiding duplicates
        foreach ( $posts_shared_with_user as $shared ) {
            if ( !in_array( $shared->ID, $post_ids ) ) {
                $posts[] = $shared;
            }
        }

        return $posts;
    }

    /**
     * Gets an array of users whom the post is shared with.
     *
     * @param string $post_type
     * @param int    $post_id
     *
     * @return array|mixed
     */
    public static function get_shared_with( string $post_type, int $post_id )
    {
        global $wpdb;

        if ( !self::can_update( $post_type, $post_id ) ) {
            return new WP_Error( 'no_permission', __( "You do not have permission for this" ), [ 'status' => 403 ] );
        }

        $shared_with_list = [];
        $shares = $wpdb->get_results( $wpdb->prepare(
            "SELECT
                *
            FROM
                `$wpdb->dt_share`
            WHERE
                post_id = %s",
            $post_id
        ), ARRAY_A );

        // adds display name to the array
        foreach ( $shares as $share ) {
            $display_name = dt_get_user_display_name( $share['user_id'] );
            if ( is_wp_error( $display_name ) ) {
                $display_name = 'Not Found';
            }
            $share['display_name'] = $display_name;
            $shared_with_list[] = $share;
        }

        return $shared_with_list;
    }

    /**
     * Removes share record
     *
     * @param string $post_type
     * @param int    $post_id
     * @param int    $user_id
     *
     * @return false|int|WP_Error
     */
    public static function remove_shared( string $post_type, int $post_id, int $user_id )
    {
        global $wpdb;

        if ( !self::can_update( $post_type, $post_id ) ) {
            return new WP_Error( __FUNCTION__, __( "You do not have permission for this" ), [ 'status' => 403 ] );
        }

        $table = $wpdb->dt_share;
        $where = [
        'user_id' => $user_id,
        'post_id' => $post_id
        ];
        $result = $wpdb->delete( $table, $where );

        if ( $result == false ) {
            return new WP_Error( 'remove_shared', __( "Record not deleted." ), [ 'status' => 418 ] );
        } else {

            // log share activity
            dt_activity_insert(
                [
                    'action'         => 'remove',
                    'object_type'    => get_post_type( $post_id ),
                    'object_subtype' => 'share',
                    'object_name'    => get_the_title( $post_id ),
                    'object_id'      => $post_id,
                    'meta_id'        => '', // id of the comment
                    'meta_key'       => '',
                    'meta_value'     => '',
                    'meta_parent'    => '',
                    'object_note'    => 'Sharing of ' . get_the_title( $post_id ) . ' was removed for ' . dt_get_user_display_name( $user_id ),
                ]
            );

            return $result;
        }
    }

    /**
     * Adds a share record
     *
     * @param string $post_type
     * @param int    $post_id
     * @param int    $user_id
     * @param array  $meta
     *
     * @return false|int|WP_Error
     */
    public static function add_shared( string $post_type, int $post_id, int $user_id, $meta = null )
    {
        global $wpdb;

        if ( !self::can_update( $post_type, $post_id ) ) {
            return new WP_Error( __FUNCTION__, __( "You do not have permission for this" ), [ 'status' => 403 ] );
        }

        $table = $wpdb->dt_share;
        $data = [
            'user_id' => $user_id,
            'post_id' => $post_id,
            'meta'    => $meta,
        ];
        $format = [
            '%d',
            '%d',
            '%s',
        ];

        $duplicate_check = $wpdb->get_row( $wpdb->prepare(
            "SELECT
                id
            FROM
                `$wpdb->dt_share`
            WHERE
                post_id = %s
                AND user_id = %s",
            $post_id,
            $user_id
        ), ARRAY_A );

        if ( is_null( $duplicate_check ) ) {

            // insert share record
            $results = $wpdb->insert( $table, $data, $format );

            // log share activity
            dt_activity_insert(
                [
                    'action'         => 'share',
                    'object_type'    => get_post_type( $post_id ),
                    'object_subtype' => 'share',
                    'object_name'    => get_the_title( $post_id ),
                    'object_id'      => $post_id,
                    'meta_id'        => '', // id of the comment
                    'meta_key'       => '',
                    'meta_value'     => '',
                    'meta_parent'    => '',
                    'object_note'    => strip_tags( get_the_title( $post_id ) ) . ' was shared with ' . dt_get_user_display_name( $user_id ),

                ]
            );

            // Add share notification
            Disciple_Tools_Notifications::insert_notification_for_share( $user_id, $post_id );

            return $results;
        } else {
            return new WP_Error( 'add_shared', __( "Post already shared with user." ), [ 'status' => 418 ] );
        }
    }

    /**
     * Get most recent activity for the field
     *
     * @param $post_id
     * @param $field_key
     *
     * @return mixed
     */
    public static function get_most_recent_activity_for_field( $post_id, $field_key ){
        global $wpdb;
        $most_recent_activity = $wpdb->get_results( $wpdb->prepare(
            "SELECT
                *
            FROM
                `$wpdb->dt_activity_log`
            WHERE
                `object_id` = %s
                AND `meta_key` = %s
            ORDER BY
                `hist_time` DESC
            LIMIT
                0,1;",
            $post_id,
            $field_key
        ) );
        return $most_recent_activity[0];
    }
}
