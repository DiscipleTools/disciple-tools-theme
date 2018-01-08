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

    /**
     * @param string $post_type
     * @param int    $post_id
     *
     * @return array|null|object|\WP_Error
     */
    public static function get_post_activity( string $post_type, int $post_id )
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
            if ( isset( $a->user_id ) && $a->user_id > 0 ) {
                $a->name = get_user_by( "id", $a->user_id )->display_name;
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
            $compact[] = [ "ID" => $post->ID, "name" => $post->post_title ];
        }
        $post_ids = array_map(
            function( $post ) {
                return $post->ID;
            },
            $posts->posts
        );
        foreach ( $shared_with_user as $shared ) {
            if ( !in_array( $shared->ID, $post_ids ) ) {
                $compact[] = [ "ID" => $shared->ID, "name" => $shared->post_title ];
            }
        }

        return $compact;
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
        $where = [ 'user_id' => $user_id, 'post_id' => $post_id ];
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
