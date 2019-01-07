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

    public static $connection_types;
    public static $channel_list;

    /**
     * Disciple_Tools_Posts constructor.
     */
    public function __construct() {
        self::$connection_types = [
            "locations" => [ "name" => __( "Locations", "disciple_tools" ) ],
            "groups" => [ "name" => __( "Groups", "disciple_tools" ) ],
            "people_groups" => [ "name" => __( "People Groups", "disciple_tools" ) ],
            "baptized_by" => [ "name" => __( "Baptized By", "disciple_tools" ) ],
            "baptized" => [ "name" => __( "Baptized", "disciple_tools" ) ],
            "coached_by" => [ "name" => __( "Coached By", "disciple_tools" ) ],
            "coaching" => [ "name" => __( "Coaching", "disciple_tools" ) ],
            "subassigned" => [ "name" => __( "Sub Assigned", "disciple_tools" ) ],
            "leaders" => [ "name" => __( "Leaders", "disciple_tools" ) ],
            "coaches" => [ "name" => __( "Coaches/Church planters", "disciple_tools" ) ],
            "parent_groups" => [ "name" => __( "Parent Groups", "disciple_tools" ) ],
            "child_groups" => [ "name" => __( "Child Groups", "disciple_tools" ) ],
            "peer_groups" => [ "name" => __( "Peer Groups", "disciple_tools" ) ],
        ];
        self::$channel_list = Disciple_Tools_Contact_Post_Type::instance()->get_channels_list();
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
    public static function can_access( string $post_type ) {
        return current_user_can( "access_" . $post_type );
    }

    /**
     * @param string $post_type
     *
     * @return bool
     */
    public static function can_view_all( string $post_type ) {
        return current_user_can( "view_any_" . $post_type );
    }

    /**
     * @param string $post_type
     *
     * @return bool
     */
    public static function can_create( string $post_type ) {
        return current_user_can( 'create_' . $post_type );
    }

    /**
     * @param string $post_type
     *
     * @return bool
     */
    public static function can_delete( string $post_type ) {
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
    public static function can_view( string $post_type, int $post_id ) {
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
    public static function can_update( string $post_type, int $post_id ) {
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

    public static function get_label_for_post_type( $post_type, $singular = false ){
        switch ( $post_type ) {
            case "contacts":
            case "contact":
                return $singular ? Disciple_Tools_Contact_Post_Type::instance()->singular : Disciple_Tools_Contact_Post_Type::instance()->plural;
                break;
            case "groups":
            case "group":
                return $singular ? Disciple_Tools_Groups_Post_Type::instance()->singular : Disciple_Tools_Groups_Post_Type::instance()->plural;
                break;
            default:
                return $post_type;
        }
    }

    /**
     * @param string $post_type
     * @param int    $user_id
     *
     * @return array
     */
    public static function get_posts_shared_with_user( string $post_type, int $user_id, $search_for_post_name = '' ) {
        global $wpdb;
        $shares = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * 
                FROM $wpdb->dt_share as shares
                INNER JOIN $wpdb->posts as posts
                WHERE user_id = %d
                AND posts.post_title LIKE %s
                AND shares.post_id = posts.ID
                AND posts.post_type = %s
                AND posts.post_status = 'publish'",
                $user_id,
                "%$search_for_post_name%",
                $post_type
            ),
            OBJECT
        );


        return $shares;
    }

    /**
     * @param string $post_type
     * @param int $post_id
     * @param string $comment_html
     * @param string $type      normally 'comment', different comment types can have their own section in the comments activity
     * @param array $args       [user_id, comment_date, comment_author etc]
     * @param bool $check_permissions
     * @param bool $silent
     *
     * @return false|int|\WP_Error
     */
    public static function add_post_comment( string $post_type, int $post_id, string $comment_html, string $type = "comment", array $args = [], bool $check_permissions = true, $silent = false ) {
        if ( $check_permissions && !self::can_update( $post_type, $post_id ) ) {
            return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
        }
        //limit comment length to 5000
        $comments = str_split( $comment_html, 4999 );
        $user = wp_get_current_user();
        $user_id = $args["user_id"] ?? get_current_user_id();

        $created_comment = null;
        foreach ( $comments as $comment ){
            $comment_data = [
                'comment_post_ID'      => $post_id,
                'comment_content'      => $comment,
                'user_id'              => $user_id,
                'comment_author'       => $args["comment_author"] ?? $user->display_name,
                'comment_author_url'   => $args["comment_author_url"] ?? "",
                'comment_author_email' => $user->user_email,
                'comment_type'         => $type,
            ];
            if ( isset( $args["comment_date"] ) ){
                $comment_data["comment_date"] = $args["comment_date"];
                $comment_data["comment_date_gmt"] = $args["comment_date"];
            }
            $new_comment = wp_new_comment( $comment_data );
            if ( !$created_comment ){
                $created_comment = $new_comment;
            }
        }

        if ( !$silent && !is_wp_error( $created_comment )){
            Disciple_Tools_Notifications_Comments::insert_notification_for_comment( $created_comment );
        }
        return $created_comment;
    }

    public static function format_connection_message( $p2p_id, $action = 'connected to', $activity ){
        // Get p2p record
        $p2p_record = p2p_get_connection( (int) $p2p_id ); // returns object

        if ( !$p2p_record ){
            if ($activity->field_type === "connection from"){
                $from = get_post( $activity->object_id );
                $to = get_post( $activity->meta_value );
                $from_title = $from->post_title;
                $to_title = $to->post_title ?? '#' . $activity->meta_value;
            } elseif ( $activity->field_type === "connection to"){
                $to = get_post( $activity->object_id );
                $from = get_post( $activity->meta_value );
                $to_title = $to->post_title;
                $from_title = $from->post_title ?? '#' . $activity->meta_value;
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
                $object_note_to = sprintf( esc_html_x( 'Baptized %s', 'Baptized contact1', 'disciple_tools' ), $from_title );
                $object_note_from = sprintf( esc_html_x( 'Baptized by %s', 'Baptized by contact1', 'disciple_tools' ), $to_title );
            } else {
                $object_note_to = sprintf( esc_html_x( 'Did not baptize %s', 'Did not baptize contact1', 'disciple_tools' ), $from_title );
                $object_note_form = sprintf( esc_html_x( 'Not baptized by %s', 'Not baptized by contact1', 'disciple_tools' ), $to_title );
            }
        } else if ($p2p_type === "contacts_to_groups"){
            if ($action == "connected to"){
                $object_note_to = sprintf( esc_html_x( '%s added to members', 'contact1 added to members', 'disciple_tools' ), $from_title );
                $object_note_from = sprintf( esc_html_x( 'Added to group %s', 'Added to group group1', 'disciple_tools' ), $to_title );
            } else {
                $object_note_to = sprintf( esc_html_x( 'Removed %s from group', 'Removed contact1 from group', 'disciple_tools' ), $from_title );
                $object_note_from = sprintf( esc_html_x( 'Removed from group %s', 'Removed from group group1', 'disciple_tools' ), $from_title );
            }
        }
        else if ( $p2p_type === "contacts_to_contacts"){
            if ($action === "connected to"){
                $object_note_to = sprintf( esc_html_x( 'Coaching %s', 'Coaching contact1', 'disciple_tools' ), $from_title );
                $object_note_from = sprintf( esc_html_x( 'Coached by %s', 'Coached by contact1', 'disciple_tools' ), $to_title );
            } else {
                $object_note_to = sprintf( esc_html_x( 'No longer coaching %s', 'No longer coaching contact1', 'disciple_tools' ), $from_title );
                $object_note_from = sprintf( esc_html_x( 'No longed coached by %s', 'No longed coached by contact1', 'disciple_tools' ), $to_title );
            }
        } else if ( $p2p_type === "contacts_to_subassigned"){
            if ($action === "connected to"){
                $object_note_to = sprintf( esc_html_x( 'Sub-assigned %s', 'Sub-assigned contact1', 'disciple_tools' ), $from_title );
                $object_note_from = sprintf( esc_html_x( 'Sub-assigned on %s', 'Sub-assigned on contact1', 'disciple_tools' ), $to_title );
            } else {
                $object_note_to = sprintf( esc_html_x( 'Removed sub-assigned %s', 'Removed sub-assigned contact1', 'disciple_tools' ), $from_title );
                $object_note_from = sprintf( esc_html_x( 'No longed sub-assigned on %s', 'No longed sub-assigned on contact1', 'disciple_tools' ), $to_title );
            }
        } else if ( $p2p_type === "contacts_to_locations" || $p2p_type === "groups_to_locations"){
            if ($action == "connected to"){
                $object_note_to = sprintf( esc_html_x( '%1$s added as location on %2$s', 'Paris added as location on contact1', 'disciple_tools' ), $to_title, $from_title );
                $object_note_from = sprintf( esc_html_x( '%s added to locations', 'Paris added to locations', 'disciple_tools' ), $to_title );
            } else {
                $object_note_to = sprintf( esc_html_x( '%1$s removed from locations on %2$s', 'Paris removed from locations on contact1', 'disciple_tools' ), $to_title, $from_title );
                $object_note_from = sprintf( esc_html_x( '%s removed from locations', 'Paris removed from locations', 'disciple_tools' ), $to_title );
            }
        } else if ( $p2p_type === "contacts_to_peoplegroups" || $p2p_type === "groups_to_peoplegroups"){
            if ($action == "connected to"){
                $object_note_to = sprintf( esc_html_x( '%1$s added as people group on %2$s', 'Deaf added as people group on contact1', 'disciple_tools' ), $to_title, $from_title );
                $object_note_from = sprintf( esc_html_x( '%s added to people groups', 'Deaf added to people groups', 'disciple_tools' ), $to_title );
            } else {
                $object_note_to = sprintf( esc_html_x( '%1$s removed from people groups on %2$s', 'Deaf removed from people groups on contact1', 'disciple_tools' ), $to_title, $from_title );
                $object_note_from = sprintf( esc_html_x( '%s removed from people groups', 'Deaf removed from people groups', 'disciple_tools' ), $to_title );
            }
        } else if ( $p2p_type === "groups_to_leaders"){
            if ($action == "connected to"){
                $object_note_to = sprintf( esc_html_x( '%1$s added as leader on %2$s', 'contact1 added as leader on group1', 'disciple_tools' ), $to_title, $from_title );
                $object_note_from = sprintf( esc_html_x( '%s added to leaders', 'contact1 added to leaders', 'disciple_tools' ), $to_title );
            } else {
                $object_note_to = sprintf( esc_html_x( '%1$s removed from leaders on %2$s', 'contact1 removed from leaders on group1', 'disciple_tools' ), $to_title, $from_title );
                $object_note_from = sprintf( esc_html_x( '%s removed from leaders', 'contact1 removed from leaders', 'disciple_tools' ), $to_title );
            }
        } else if ( $p2p_type === "groups_to_coaches"){
            if ($action == "connected to"){
                $object_note_to = sprintf( esc_html_x( '%1$s added as coach on %2$s', 'contact1 added as coach on group1', 'disciple_tools' ), $to_title, $from_title );
                $object_note_from = sprintf( esc_html_x( '%s added to coaches', 'contact1 added to coaches', 'disciple_tools' ), $to_title );
            } else {
                $object_note_to = sprintf( esc_html_x( '%1$s removed from coaches on %2$s', 'contact1 removed from coaches on group2', 'disciple_tools' ), $to_title, $from_title );
                $object_note_from = sprintf( esc_html_x( '%s removed from coaches', 'contact1 removed from coaches', 'disciple_tools' ), $to_title );
            }
        } else if ( $p2p_type === "groups_to_groups"){
            if ($action == "connected to"){
                $object_note_to = sprintf( esc_html_x( '%s added to child groups', 'group2 added to child groups', 'disciple_tools' ), $from_title );
                $object_note_from = sprintf( esc_html_x( '%s added to parent groups', 'group1 added to parent groups', 'disciple_tools' ), $to_title );
            } else {
                $object_note_to = sprintf( esc_html_x( '%s removed from child groups', 'group2 removed from child groups', 'disciple_tools' ), $from_title );
                $object_note_from = sprintf( esc_html_x( '%s removed from parent groups', 'group1 removed from parent groups', 'disciple_tools' ), $to_title );
            }
        }
        else if ( $p2p_type === "groups_to_peers"){
            if ($action == "connected to"){
                $object_note_to = sprintf( esc_html_x( '%s added to peer groups', 'group2 added to peer groups', 'disciple_tools' ), $from_title );
                $object_note_from = sprintf( esc_html_x( '%s added to peer groups', 'group1 added to peer groups', 'disciple_tools' ), $to_title );
            } else {
                $object_note_to = sprintf( esc_html_x( '%s removed from peer groups', 'group2 removed from peer groups', 'disciple_tools' ), $from_title );
                $object_note_from = sprintf( esc_html_x( '%s removed from peer groups', 'group1 removed from peer groups', 'disciple_tools' ), $to_title );
            }
        } else if ( $p2p_type === "contacts_to_relation"){
            if ($action == "connected to"){
                $object_note_to = sprintf( esc_html_x( 'Connected to %s', 'Connected to contact1', 'disciple_tools' ), $from_title );
                $object_note_from = sprintf( esc_html_x( 'Connected to %s', 'Connected to contact1', 'disciple_tools' ), $to_title );
            } else {
                $object_note_to = sprintf( esc_html_x( 'Removed connection to %s', 'Removed connection to contact1', 'disciple_tools' ), $from_title );
                $object_note_from = sprintf( esc_html_x( 'Removed connection to %s', 'Removed connection to contact1', 'disciple_tools' ), $to_title );
            }
        } else {
            if ($action == "connected to"){
                $object_note_to = sprintf( esc_html_x( 'Connected to %s', 'Connected to contact1', 'disciple_tools' ), $from_title );
                $object_note_from = sprintf( esc_html_x( 'Connected to %s', 'Connected to contact1', 'disciple_tools' ), $to_title );
            } else {
                $object_note_to = sprintf( esc_html_x( 'Removed connection to %s', 'Removed connection to contact1', 'disciple_tools' ), $from_title );
                $object_note_from = sprintf( esc_html_x( 'Removed connection to %s', 'Removed connection to contact1', 'disciple_tools' ), $to_title );
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
                        $message = sprintf( _x( 'Assigned to: %s', 'Assigned to: User1', 'disciple_tools' ), ( $user ? $user->display_name : __( "Nobody", 'disciple_tools' ) ) );
                    }
                }
                if ( $fields[$activity->meta_key]["type"] === "text"){
                    if ( !empty( $activity->meta_value ) && !empty( $activity->old_value ) ){
                        $message = sprintf( _x( '%1$s changed to %2$s', 'field1 changed to: text', 'disciple_tools' ), $fields[$activity->meta_key]["name"], $activity->meta_value );
                    }
                }
                if ( $fields[$activity->meta_key]["type"] === "multi_select" ){
                    if ( $activity->meta_key === "sources" && empty( $fields["sources"]["default"] ) ){
                        $fields["sources"]["default"] = dt_get_option( 'dt_site_custom_lists' )['sources'];
                    }
                    $value = $activity->meta_value;
                    if ( $activity->meta_value == "value_deleted" ){
                        $value = $activity->old_value;
                        $label = $fields[$activity->meta_key]["default"][$value]["label"] ?? $value;
                        $message = sprintf( _x( '%1$s removed from %2$s', 'Milestone1 removed from Milestones', 'disciple_tools' ), $label, $fields[$activity->meta_key]["name"] );
                    } else {
                        $label = $fields[$activity->meta_key]["default"][$value]["label"] ?? $value;
                        $message = sprintf( _x( '%1$s added to %2$s', 'Milestone1 added to Milestones', 'disciple_tools' ), $label, $fields[$activity->meta_key]["name"] );
                    }
                }
                if ( $fields[$activity->meta_key]["type"] === "key_select" ){
                    if ( isset( $fields[$activity->meta_key]["default"][$activity->meta_value] ) ){
                        $message = $fields[$activity->meta_key]["name"] . ": " . $fields[$activity->meta_key]["default"][$activity->meta_value]["label"] ?? $activity->meta_value;
                    } else {
                        $message = $fields[$activity->meta_key]["name"] . ": " . $activity->meta_value;
                    }
                }
                if ( $fields[$activity->meta_key]["type"] === "boolean" ){
                    if ( $activity->meta_value === true || $activity->meta_value === '1' ){
                        $message = $fields[$activity->meta_key]["name"] . ": " . __( "yes", 'disciple_tools' );
                    } else {
                        $message = $fields[$activity->meta_key]["name"] . ": " . __( "no", 'disciple_tools' );
                    }
                }
                if ($fields[$activity->meta_key]["type"] === "number"){
                    $message = $fields[$activity->meta_key]["name"] . ": " . $activity->meta_value;
                }
                if ($fields[$activity->meta_key]["type"] === "date" ){
                    $message = $fields[$activity->meta_key]["name"] . ": " . dt_format_date( $activity->meta_value );
                }
            } else {
                if ( strpos( $activity->meta_key, "_details" ) !== false ) {
                    $meta_value = maybe_unserialize( $activity->meta_value );
                    $original_key = str_replace( "_details", "", $activity->meta_key );
                    $original = get_post_meta( $activity->object_id, $original_key, true );
                    if ( !is_string( $original ) ){
                        $original = "Not a string";
                    }
                    $name = $fields[ $activity->meta_key ]['name'] ?? "";
                    if ( !empty( $name ) && !empty( $original ) ){
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
                    }
                } else if ( strpos( $activity->meta_key, "contact_" ) === 0 ) {
                    $channel = explode( '_', $activity->meta_key );
                    if ( isset( $channel[1] ) && self::$channel_list[ $channel[1] ] ){
                        $channel = self::$channel_list[ $channel[1] ];
                        if ( $activity->old_value === "" ){
                            $message = sprintf( _x( 'Added %1$s: %2$s', 'Added Facebook: facebook.com/123', 'disciple_tools' ), $channel["label"], $activity->meta_value );
                        } else if ( $activity->meta_value != "value_deleted" ){
                            $message = sprintf( _x( 'Updated %1$s from %2$s to %3$s', 'Update Facebook form facebook.com/123 to facebook.com/mark', 'disciple_tools' ), $channel["label"], $activity->old_value, $activity->meta_value );
                        } else {
                            $message = sprintf( _x( 'Deleted %1$s: %2$s', 'Deleted Facebook: facebook.com/123', 'disciple_tools' ), $channel["label"], $activity->old_value );
                        }
                    }
                } else if ( $activity->meta_key == "title" ){
                    $message = sprintf( __( "Name changed to: %s", 'disciple_tools' ), $activity->meta_value );
                } else if ( $activity->meta_key === "_sample"){
                    $message = __( "Created from Demo Plugin", "disciple_tools" );
                } else {
                    $message = $activity->meta_key . ": " . $activity->meta_value;
                }
            }
        } elseif ( $activity->action === "assignment_decline" ){
            $user = get_user_by( "ID", $activity->user_id );
            $message = sprintf( __( "%s declined assignment", 'disciple_tools' ), $user->display_name ?? __( "A user", "disciple_tools" ) );
        } elseif ( $activity->action === "assignment_accepted" ){
            $user = get_user_by( "ID", $activity->user_id );
            $message = sprintf( __( "%s accepted assignment", 'disciple_tools' ), $user->display_name ?? __( "A user", "disciple_tools" ) );
        } elseif ( $activity->object_subtype === "p2p" ){
            $message = self::format_connection_message( $activity->meta_id, $activity->action, $activity );
        } elseif ( $activity->object_subtype === "share" ){
            if ($activity->action === "share"){
                $message = sprintf( __( "Shared with %s", "disciple_tools" ), dt_get_user_display_name( $activity->meta_value ) );
            } else if ( $activity->action === "remove" ){
                $message = sprintf( __( "Unshared with %s", "disciple_tools" ), dt_get_user_display_name( $activity->meta_value ) );
            }
        } else {
            $message = $activity->object_note;
        }

        return $message;
    }

    /**
     * @param string $post_type
     * @param int    $post_id
     *
     * @return array|null|object|\WP_Error
     */
    public static function get_post_activity( string $post_type, int $post_id, array $fields ) {
        global $wpdb;
        if ( !self::can_view( $post_type, $post_id ) ) {
            return new WP_Error( __FUNCTION__, "No permissions to read: " . $post_type, [ 'status' => 403 ] );
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
        $activity_simple = [];
        foreach ( $activity as $a ) {
            if ( isset( $a->meta_key, $fields[$a->meta_key]["hidden"] ) && $fields[$a->meta_key]["hidden"] === true ){
                continue;
            }
            $a->object_note = self::format_activity_message( $a, $fields );
            if ( isset( $a->user_id ) && $a->user_id > 0 ) {
                $user = get_user_by( "id", $a->user_id );
                if ( $user ){
                    $a->name =$user->display_name;
                    $a->gravatar = get_avatar_url( $user->ID, [ 'size' => '16' ] );
                }
            }
            $activity_simple[] = [
                "meta_key" => $a->meta_key,
                "gravatar" => isset( $a->gravatar ) ? $a->gravatar : "",
                "name" => isset( $a->name ) ? $a->name : "",
                "object_note" => $a->object_note,
                "hist_time" => $a->hist_time,
                "meta_id" => $a->meta_id,
                "histid" => $a->histid,
            ];
        }

        return $activity_simple;
    }

    public static function get_post_single_activity( string $post_type, int $post_id, array $fields, int $activity_id ){
        global $wpdb;
        if ( !self::can_view( $post_type, $post_id ) ) {
            return new WP_Error( __FUNCTION__, "No permissions to read group", [ 'status' => 403 ] );
        }
        $activity = $wpdb->get_results( $wpdb->prepare(
            "SELECT
                *
            FROM
                `$wpdb->dt_activity_log`
            WHERE
                `object_type` = %s
                AND `object_id` = %s
                AND `histid` = %s",
            $post_type,
            $post_id,
            $activity_id
        ) );
        foreach ( $activity as $a ) {
            $a->object_note = self::format_activity_message( $a, $fields );
            if ( isset( $a->user_id ) && $a->user_id > 0 ) {
                $user = get_user_by( "id", $a->user_id );
                if ( $user ) {
                    $a->name = $user->display_name;
                }
            }
        }
        if ( isset( $activity[0] ) ){
            return $activity[0];
        }
        return $activity;
    }

    /**
     * Get post comments
     *
     * @param string $post_type
     * @param int $post_id
     *
     * @param bool $check_permissions
     * @param string $type
     *
     * @return array|int|\WP_Error
     */
    public static function get_post_comments( string $post_type, int $post_id, bool $check_permissions = true, $type = "all" ) {
        if ( $check_permissions && !self::can_view( $post_type, $post_id ) ) {
            return new WP_Error( __FUNCTION__, "No permissions to read post", [ 'status' => 403 ] );
        }
        //setting type to "comment" does not work.
        $comments = get_comments( [
            'post_id' => $post_id,
            "type" => $type
        ]);

        foreach ( $comments as $comment ){
            $url = !empty( $comment->comment_author_url ) ? $comment->comment_author_url : get_avatar_url( $comment->user_id, [ 'size' => '16' ] );
            $comment->gravatar = preg_replace( "/^http:/i", "https:", $url );
            $display_name = dt_get_user_display_name( $comment->user_id );
            $comment->comment_author = !empty( $display_name ) ? $display_name : $comment->comment_author;
        }

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
    public static function get_viewable_compact( string $post_type, string $search_string ) {
        if ( !self::can_access( $post_type ) ) {
            return new WP_Error( __FUNCTION__, sprintf( "You do not have access to these %s", $post_type ), [ 'status' => 403 ] );
        }
        global $wpdb;
        $current_user = wp_get_current_user();
        $compact = [];
        $search_string = esc_sql( sanitize_text_field( $search_string ) );
        $shared_with_user = [];
        $users_interacted_with =[];
        $posts = [];

        //search by post_id
        if ( is_numeric( $search_string ) ){
            $post = get_post( $search_string );
            if ( $post && self::can_view( $post_type, $post->ID ) ){
                return [
                    "total" => "1",
                    "posts" => [
                        [
                            "ID" => (string) $post->ID,
                            "name" => $post->post_title
                        ]
                    ]
                ];
            }
        }

        if ( !self::can_view_all( $post_type ) ) {
//            @todo better way to get the contact records for users my contacts are shared with
            $users_interacted_with = Disciple_Tools_Users::get_assignable_users_compact( $search_string );
            $shared_with_user = self::get_posts_shared_with_user( $post_type, $current_user->ID, $search_string );
            $query_args['meta_key'] = 'assigned_to';
            $query_args['meta_value'] = "user-" . $current_user->ID;
            $posts = $wpdb->get_results( $wpdb->prepare( "
                SELECT * FROM $wpdb->posts
                INNER JOIN $wpdb->postmeta as assigned_to ON ( $wpdb->posts.ID = assigned_to.post_id AND assigned_to.meta_key = 'assigned_to')
                WHERE assigned_to.meta_value = %s
                AND INSTR( $wpdb->posts.post_title, %s ) > 0
                AND $wpdb->posts.post_type = %s AND ($wpdb->posts.post_status = 'publish' OR $wpdb->posts.post_status = 'private')
                ORDER BY CASE
                    WHEN INSTR( $wpdb->posts.post_title, %s ) = 1 then 1
                    ELSE 2
                END, CHAR_LENGTH($wpdb->posts.post_title), $wpdb->posts.post_title
                LIMIT 0, 30
            ", "user-". $current_user->ID, $search_string, $post_type, $search_string
            ), OBJECT );
        } else {
            $posts = $wpdb->get_results( $wpdb->prepare( "
                SELECT ID, post_title, pm.meta_value as corresponds_to_user 
                FROM $wpdb->posts
                LEFT JOIN $wpdb->postmeta pm ON ( pm.post_id = $wpdb->posts.ID AND pm.meta_key = 'corresponds_to_user' ) 
                WHERE INSTR( $wpdb->posts.post_title, %s ) > 0
                AND $wpdb->posts.post_type = %s AND ($wpdb->posts.post_status = 'publish' OR $wpdb->posts.post_status = 'private')
                ORDER BY  CASE
                    WHEN pm.meta_value > 0 then 1
                    WHEN CHAR_LENGTH(%s) > 0 && INSTR( $wpdb->posts.post_title, %s ) = 1 then 2
                    ELSE 3
                END, CHAR_LENGTH($wpdb->posts.post_title), $wpdb->posts.post_title
                LIMIT 0, 30
            ", $search_string, $post_type, $search_string, $search_string
            ), OBJECT );
        }
        if ( is_wp_error( $posts ) ) {
            return $posts;
        }

        $post_ids = array_map(
            function( $post ) {
                return $post->ID;
            },
            $posts
        );
        foreach ( $users_interacted_with as $user ) {
            $contact_id = Disciple_Tools_Users::get_contact_for_user( $user["ID"] );
            if ( $contact_id ){
                if ( !in_array( $contact_id, $post_ids ) ) {
                    $compact[] = [
                        "ID" => $contact_id,
                        "name" => $user["name"],
                        "user" => true
                    ];
                }
            }
        }
        foreach ( $shared_with_user as $shared ) {
            if ( !in_array( $shared->ID, $post_ids ) ) {
                $compact[] = [
                "ID" => $shared->ID,
                "name" => $shared->post_title
                ];
            }
        }
        foreach ( $posts as $post ) {
            $compact[] = [
                "ID" => $post->ID,
                "name" => $post->post_title,
                "user" => $post->corresponds_to_user > 1
            ];
        }

        return [
            "total" => sizeof( $compact ),
            "posts" => array_slice( $compact, 0, 50 )
        ];
    }

    /**
     * @param string $post_type
     *
     * @param int $most_recent
     *
     * @return array|\WP_Error|\WP_Query
     */
    public static function get_viewable( string $post_type, int $most_recent = 0 ) {
        if ( !self::can_access( $post_type ) ) {
            return new WP_Error( __FUNCTION__, sprintf( "You do not have access to these %s", $post_type ), [ 'status' => 403 ] );
        }
        $current_user = wp_get_current_user();

        $query_args = [
            'post_type' => $post_type,
            'meta_query' => [
                'relation' => "AND",
                [
                    'key' => "last_modified",
                    'value' => $most_recent,
                    'compare' => '>'
                ]
            ],
            'orderby' => 'meta_value_num',
            'meta_key' => "last_modified",
            'order' => 'ASC',
            'posts_per_page' => 1000 // @phpcs:ignore WordPress.VIP.PostsPerPage
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

        $delete_posts = [];
        if ($most_recent){
            global $wpdb;
            $deleted_query = $wpdb->get_results( $wpdb->prepare(
                "SELECT object_id
                FROM `$wpdb->dt_activity_log`
                WHERE
                    ( `action` = 'deleted' || `action` = 'trashed' )
                    AND `object_subtype` = %s
                    AND hist_time > %d
                ",
                $post_type,
                $most_recent
            ), ARRAY_A);
            foreach ( $deleted_query as $deleted ){
                $delete_posts[] = $deleted["object_id"];
            }
        }

        return [
            $post_type => $posts,
            "total" => $queried_posts->found_posts,
            "deleted" => $delete_posts
        ];
    }


    public static function search_viewable_post( string $post_type, array $query, bool $check_permissions = true ){
        if ( $check_permissions && !self::can_access( $post_type ) ) {
            return new WP_Error( __FUNCTION__, "You do not have access to these", [ 'status' => 403 ] );
        }
        global $wpdb;
        $current_user = wp_get_current_user();

        $post_fields = [];
        if ( $post_type === "contacts" ){
            $post_fields = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings();
        } elseif ( $post_type === "groups" ){
            $post_fields = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings();
        }

        $include = [];
        if ( isset( $query["include"] ) ){
            $include = $query["include"];
            unset( $query["include"] );
        }
        $search = "";
        if ( isset( $query["text"] )){
            $search = sanitize_text_field( $query["text"] );
            unset( $query["text"] );
        }
        $offset = 0;
        if ( isset( $query["offset"] )){
            $offset = esc_sql( sanitize_text_field( $query["offset"] ) );
            unset( $query["offset"] );
        }
        $combine = [];
        if ( isset( $query["combine"] )){
            $combine = $query["combine"];
            unset( $query["combine"] );
        }
        $sort = "post_title";
        $sort_dir = "asc";
        if ( isset( $query["sort"] )){
            $sort = esc_sql( sanitize_text_field( $query["sort"] ) );
            if ( strpos( $sort, "-" ) === 0 ){
                $sort_dir = "desc";
                $sort = str_replace( "-", "", $sort );
            }
            unset( $query["sort"] );
        }

        $inner_joins = "";
        $connections_sql_to = "";
        $connections_sql_from = "";

        $meta_query = "";
        $includes_query = "";
        $share_joins = "";
        $access_joins = "";
        $access_query = "";
        if ( !isset( $query["assigned_to"] ) || in_array( "all", $query["assigned_to"] ) ){
            $query["assigned_to"] = [ "all" ];
            if ( !self::can_view_all( 'contacts' ) && $check_permissions ){
                $query["assigned_to"] = [ "me" ];
                if ( !in_array( "shared", $include )){
                    $include[] = "shared";
                }
            };
        }
        foreach ( $include as $i ){
            if ( $i === "shared" ){
                $share_joins = "LEFT JOIN $wpdb->dt_share AS shares ON ( shares.post_id = $wpdb->posts.ID ) ";
                $access_query = "shares.user_id = $current_user->ID ";
            }
        }
        if ( in_array( "shared", $query["assigned_to"] ) ){
            $share_joins = "LEFT JOIN $wpdb->dt_share AS shares ON ( shares.post_id = $wpdb->posts.ID ) ";
            $access_query = ( !empty( $access_query ) ? "OR" : "" ) ." shares.user_id = $current_user->ID ";
            if ( !in_array( "me", $query["assigned_to"] ) && !in_array( "all", $query["assigned_to"] ) ){
                $access_joins = "INNER JOIN $wpdb->postmeta AS assigned_to ON ( $wpdb->posts.ID = assigned_to.post_id ) ";
                $access_query .= ( !empty( $access_query ) ? "AND" : "" ) ." ( assigned_to.meta_key = 'assigned_to' AND assigned_to.meta_value != 'user-$current_user->ID' )";
            }
        }

        /**
         * Filter by creation date
         */
        if ( isset( $query["created_on"] ) ){
            if ( isset( $query["created_on"]["start"] ) ){
                $meta_query .= "AND $wpdb->posts.post_date >= '" . esc_sql( $query["created_on"]["start"] ) . "' ";
            }
            if ( isset( $query["created_on"]["end"] ) ){
                $meta_query .= "AND $wpdb->posts.post_date <= '" . esc_sql( $query["created_on"]["end"] ) . "' ";
            }
            unset( $query["created_on"] );
        }

        foreach ( $query as $query_key => $query_value ){
            $meta_field_sql = "";
            if ( !is_array( $query_value )){
                return new WP_Error( __FUNCTION__, "Filter queries must be arrays", [ 'status' => 403 ] );
            }
            if ( !in_array( $query_key, array_keys( self::$connection_types ) ) && strpos( $query_key, "contact_" ) !== 0 ){
                if ( $query_key == "assigned_to" ){
                    foreach ( $query_value as $assigned_to ){
                        $connector = "OR";
                        if ( $assigned_to == "me" ){
                            $assigned_to = "user-" . $current_user->ID;
                        } else if ( $assigned_to != "all" && $assigned_to != "shared" ) {
                            if ( self::can_view_all( 'contacts' ) || !$check_permissions ){
                                $assigned_to = "user-" . $assigned_to;
                            } else {
                                $assigned_to = "user-" . $assigned_to;
                                if ( !$share_joins ){
                                    $share_joins = "INNER JOIN $wpdb->dt_share AS shares ON ( shares.post_id = $wpdb->posts.ID ) ";
                                    $access_query = "shares.user_id = $current_user->ID ";
                                    $connector = "AND";
                                }
                            }
                        } else {
                            break;
                        }
                        $access_joins = "INNER JOIN $wpdb->postmeta AS assigned_to ON ( $wpdb->posts.ID = assigned_to.post_id ) ";
                        $access_query .= ( !empty( $access_query ) ? $connector : "" ) . ( $connector == "AND" ? " ( " : "" ) . " ( " . esc_sql( $query_key ) . ".meta_key = '" . esc_sql( $query_key ) ."' AND " . esc_sql( $query_key ) . ".meta_value = '" . esc_sql( $assigned_to ) . "' ) " . ( $connector == "AND" ? " ) " : "" );

                    }
                } else {
                    $connector = " OR ";
                    foreach ( $query_value as $value_key => $value ){
                        $equality = "=";
                        $field_type = isset( $post_fields[$query_key]["type"] ) ? $post_fields[$query_key]["type"] : null;
                        // boolean fields
                        if ( $field_type === "boolean" ){
                            if ( $value === "1" || $value === "yes" || $value === "true" ){
                                $value = true;
                            } elseif ( $value === "0" || $value === "no" || $value === "false" ){
                                $value = false;
                            }
                        }
                        //date fields
                        if ( $field_type === "date" ){
                            $connector = "AND";
                            if ( $value_key === "start" ){
                                $value = strtotime( $value );
                                $equality = ">=";
                            }
                            if ( $value_key === "end" ){
                                $value = strtotime( $value );
                                $equality = "<=";
                            }
                        }

                        //allow negative searches
                        if ( strpos( $value, "-" ) === 0 ){
                            $equality = "!=";
                            $value = ltrim( $value, "-" );
                            $connector = " AND ";
                        }
                        if ( !empty( $meta_field_sql ) ){
                            $meta_field_sql .= $connector;
                        }
                        if ($equality === "!=" && $field_type === "multi_select"){
                            //find one with the value to exclude
                            $meta_query .= " AND not exists (select 1 from $wpdb->postmeta where $wpdb->postmeta.post_id = $wpdb->posts.ID and $wpdb->postmeta.meta_key = '" . esc_sql( $query_key ) ."'  and $wpdb->postmeta.meta_value = '" . esc_sql( $value ) . "') ";
                        } else {
                            $meta_field_sql .= " ( " . esc_sql( $query_key ) . ".meta_key = '" . esc_sql( $query_key ) ."' AND " . esc_sql( $query_key ) . ".meta_value " . $equality . " '" . esc_sql( $value ) . "' ) ";
                        }
                    }
                }
                if ( $meta_field_sql ){
                    $inner_joins .= "INNER JOIN $wpdb->postmeta AS " . esc_sql( $query_key ) . " ON ( $wpdb->posts.ID = " . esc_sql( $query_key ) . ".post_id ) ";
                    $meta_query .= "AND ( " .$meta_field_sql . ") ";
                }
            }
        }

        if ( !empty( $search )){
            $inner_joins .= "INNER JOIN $wpdb->postmeta AS search ON ( $wpdb->posts.ID = search.post_id ) ";
            $other_search_fields = apply_filters( "dt_search_extra_post_meta_fields", [] );
            $meta_query .= "AND ( ( INSTR( $wpdb->posts.post_title ,'" . esc_sql( $search ) . "' ) > 0 ) OR ( search.meta_key LIKE 'contact_%' AND INSTR( search.meta_value, '" . esc_sql( $search ) . "' ) > 0 )  ";
            foreach ( $other_search_fields as $field ){
                $meta_query .= " OR ( search.meta_key LIKE '" . esc_sql( $field ) . "' AND INSTR( search.meta_value, '" . esc_sql( $search ) . "' ) > 0  ) ";
            }
            $meta_query .= " ) ";

        }

        foreach ( $query as $query_key => $query_value ) {
            if ( in_array( $query_key, array_keys( self::$connection_types ) ) ) {
                if ( $query_key === "locations" ) {
                    $location_sql = "";
                    foreach ( $query_value as $location ) {
                        $l = get_post( $location );
                        if ( $l && $l->post_type === "locations" ){
                            $location_sql .= empty( $location_sql ) ? $l->ID : ( ",".$l->ID );
                        }
                    }
                    if ( !empty( $location_sql ) ){
                        $connections_sql_to .= "AND ( to_p2p.p2p_type = '" . esc_sql( $post_type ) . "_to_locations' AND to_p2p.p2p_to in (" . esc_sql( $location_sql ) .") )";
                    }
                }
                if ( $query_key === "subassigned" ) {
                    $subassigned_sql = "";
                    foreach ( $query_value as $subassigned ) {
                        $l = get_post( $subassigned );
                        if ( $l && $l->post_type === "contacts" ){
                            $subassigned_sql .= empty( $subassigned_sql ) ? $l->ID : ( ",".$l->ID );
                        }
                    }
                    if ( !empty( $subassigned_sql ) ){
                        if ( !empty( $access_query ) && in_array( "subassigned", $combine ) ){
                            $access_query .= "OR ( from_p2p.p2p_type = 'contacts_to_subassigned' AND from_p2p.p2p_from in (" . esc_sql( $subassigned_sql ) .") )";
                            $connections_sql_from .= " ";
                        } else {
                            $connections_sql_from .= "AND ( from_p2p.p2p_type = 'contacts_to_subassigned' AND from_p2p.p2p_from in (" . esc_sql( $subassigned_sql ) .") )";
                        }
                    }
                }
            }
        }
        if ( !empty( $connections_sql_to )){
            $inner_joins .= " INNER JOIN $wpdb->p2p as to_p2p ON ( to_p2p.p2p_from = $wpdb->posts.ID )";
        }
        if ( !empty( $connections_sql_from )){
            $inner_joins .= " LEFT JOIN $wpdb->p2p as from_p2p ON ( from_p2p.p2p_to = $wpdb->posts.ID )";
        }

        $access_query = $access_query ? ( "AND ( " . $access_query . " ) " ) : "";

        $sort_sql = "$wpdb->posts.post_date asc";
        $sort_join = "";
        $post_type_check = "";
        if ( $post_type == "contacts" ){
            $inner_joins .= "LEFT JOIN $wpdb->postmeta as contact_type ON ( $wpdb->posts.ID = contact_type.post_id AND contact_type.meta_key = 'type' ) ";
            $post_type_check = " AND (
                ( contact_type.meta_key = 'type' AND contact_type.meta_value = 'media' )
                OR
                ( contact_type.meta_key = 'type' AND contact_type.meta_value = 'next_gen' )
                OR ( contact_type.meta_key IS NULL )
            ) ";
            $contact_fields = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings();
            if ( $sort === "overall_status" || $sort === "seeker_path" ) {
                $keys = array_keys( $contact_fields[$sort]["default"] );
                $sort_join = "INNER JOIN $wpdb->postmeta as sort ON ( $wpdb->posts.ID = sort.post_id AND sort.meta_key = '$sort')";
                $sort_sql  = "CASE ";
                foreach ( $keys as $index => $key ) {
                    $i        = $key == "closed" ? 99 : $index;
                    $sort_sql .= "WHEN ( sort.meta_value = '" . esc_sql( $key ) . "' ) THEN $i ";
                }
                $sort_sql .= "else 98 end ";
                $sort_sql .= $sort_dir;
            } elseif ( $sort === "faith_milestones" ){
                $all_field_keys = array_keys( $contact_fields );
                $sort_sql = "CASE ";
                $sort_join = "";
                $milestone_keys = array_reverse( array_keys( $contact_fields["milestones"]["default"] ) );
                foreach ( $milestone_keys as $index  => $key ){
                    $alias = 'faith_' . esc_sql( $key );
                    $sort_join .= "LEFT JOIN $wpdb->postmeta as $alias ON
                    ( $wpdb->posts.ID = $alias.post_id AND $alias.meta_key = 'milestones' AND $alias.meta_value = '" . esc_sql( $key ) . "') ";
                    $sort_sql .= "WHEN ( $alias.meta_value = '" . esc_sql( $key ) . "' ) THEN $index ";
                }
                $sort_sql .= "else 1000 end ";
                $sort_sql .= $sort_dir;
            }
        } elseif ( $post_type === "groups" ){
            $group_fields = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings();
            if ( $sort === "group_status" || $sort === "group_type" ) {
                $keys      = array_keys( $group_fields[ $sort ]["default"] );
                $sort_join = "INNER JOIN $wpdb->postmeta as sort ON ( $wpdb->posts.ID = sort.post_id AND sort.meta_key = '$sort')";
                $sort_sql  = "CASE ";
                foreach ( $keys as $index => $key ) {
                    $sort_sql .= "WHEN ( sort.meta_value = '" . esc_sql( $key ) . "' ) THEN $index ";
                }
                $sort_sql .= "else 98 end ";
                $sort_sql .= $sort_dir;
            } elseif ( $sort === "members" ){
                $sort_join = "LEFT JOIN $wpdb->p2p as sort ON ( sort.p2p_to = $wpdb->posts.ID AND sort.p2p_type = 'contacts_to_groups' )";
                $sort_sql = "COUNT(sort.p2p_id) $sort_dir";
            }
        }
        if ( $sort === "name" ){
            $sort_sql = "$wpdb->posts.post_title  " . $sort_dir;
        } elseif ( $sort === "assigned_to" || $sort === "last_modified" ){
            $sort_join = "INNER JOIN $wpdb->postmeta as sort ON ( $wpdb->posts.ID = sort.post_id AND sort.meta_key = '$sort')";
            $sort_sql = "sort.meta_value $sort_dir";
        } elseif ( $sort === "locations" || $sort === "groups" || $sort === "leaders" ){
            $sort_join = "LEFT JOIN $wpdb->p2p as sort ON ( sort.p2p_from = $wpdb->posts.ID AND sort.p2p_type = '" . $post_type . "_to_$sort' )
            LEFT JOIN $wpdb->posts as p2p_post ON (p2p_post.ID = sort.p2p_to)";
            $sort_sql = "ISNULL(p2p_post.post_name), p2p_post.post_name $sort_dir";
        } elseif ( $sort === "post_date" ){
            $sort_sql = "$wpdb->posts.post_date  " . $sort_dir;
        }


        // phpcs:disable
        // WordPress.WP.PreparedSQL.NotPrepared
        $prepared_sql = $wpdb->prepare("
            SELECT SQL_CALC_FOUND_ROWS $wpdb->posts.ID, $wpdb->posts.post_title, $wpdb->posts.post_type FROM $wpdb->posts
            " . $sort_join . " " . $inner_joins . " " . $share_joins . " " . $access_joins . "
            WHERE 1=1
            " . $post_type_check . " " . $connections_sql_to . " ". $connections_sql_from . " " . $meta_query . " " . $includes_query . " " . $access_query . "
            AND $wpdb->posts.post_type = %s
            AND ($wpdb->posts.post_status = 'publish' OR $wpdb->posts.post_status = 'private')
            GROUP BY $wpdb->posts.ID
            ORDER BY " . $sort_sql . "
            LIMIT %d, 100
            ",
            esc_sql( $post_type ),
            $offset
        );
        $posts = $wpdb->get_results( $prepared_sql, OBJECT );
        // phpcs:enable
        $total_rows = $wpdb->get_var( "SELECT found_rows();" );

        return [
            "posts" => $posts,
            "total" => $total_rows,
        ];
    }


    /**
     * Gets an array of users whom the post is shared with.
     *
     * @param string $post_type
     * @param int $post_id
     *
     * @param bool $check_permissions
     *
     * @return array|mixed
     */
    public static function get_shared_with( string $post_type, int $post_id, bool $check_permissions = false ) {
        global $wpdb;

        if ( $check_permissions && !self::can_update( $post_type, $post_id ) ) {
            return new WP_Error( 'no_permission', "You do not have permission for this", [ 'status' => 403 ] );
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
    public static function remove_shared( string $post_type, int $post_id, int $user_id ) {
        global $wpdb;

        if ( !self::can_update( $post_type, $post_id ) ) {
            return new WP_Error( __FUNCTION__, "You do not have permission to unshare", [ 'status' => 403 ] );
        }

        $assigned_to_meta = get_post_meta( $post_id, "assigned_to", true );
        if ( !( current_user_can( 'update_any_' . $post_type ) ||
             get_current_user_id() === $user_id ||
            dt_get_user_id_from_assigned_to( $assigned_to_meta ) === get_current_user_id() )
        ){
            $name = dt_get_user_display_name( $user_id );
            return new WP_Error( __FUNCTION__, "You do not have permission to unshare with " . $name, [ 'status' => 403 ] );
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
                    'meta_value'     => $user_id,
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
     * @param int $post_id
     * @param int $user_id
     * @param array $meta
     * @param bool $send_notifications
     * @param bool $check_permissions
     * @param bool $insert_activity
     *
     * @return false|int|WP_Error
     */
    public static function add_shared( string $post_type, int $post_id, int $user_id, $meta = null, bool $send_notifications = true, $check_permissions = true, bool $insert_activity = true ) {
        global $wpdb;

        if ( $check_permissions && !self::can_update( $post_type, $post_id ) ) {
            return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
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

            if ( $insert_activity ){
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
                        'meta_value'     => $user_id,
                        'meta_parent'    => '',
                        'object_note'    => strip_tags( get_the_title( $post_id ) ) . ' was shared with ' . dt_get_user_display_name( $user_id ),
                    ]
                );
            }

            // Add share notification
            if ( $send_notifications ){
                Disciple_Tools_Notifications::insert_notification_for_share( $user_id, $post_id );
            }

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

    /**
     * Cached version of get_page_by_title so that we're not making unnecessary SQL all the time
     *
     * @param string $title Page title
     * @param string $output Optional. Output type; OBJECT*, ARRAY_N, or ARRAY_A.
     * @param string|array $post_type Optional. Post type; default is 'page'.
     * @param $connection_type
     *
     * @return WP_Post|null WP_Post on success or null on failure
     * @link http://vip.wordpress.com/documentation/uncached-functions/ Uncached Functions
     */
    public static function get_post_by_title_cached( $title, $output = OBJECT, $post_type = 'page', $connection_type ) {
        $cache_key = $connection_type . '_' . sanitize_key( $title );
        $page_id = wp_cache_get( $cache_key, 'get_page_by_title' );
        if ( $page_id === false ) {
            $page = get_page_by_title( $title, OBJECT, $post_type );
            $page_id = $page ? $page->ID : 0;
            wp_cache_set( $cache_key, $page_id, 'get_page_by_title', 3 * HOUR_IN_SECONDS ); // We only store the ID to keep our footprint small
        }
        if ( $page_id ){
            return get_post( $page_id, $output );
        }
        return null;
    }


    public static function get_subassigned_users( $post_id ){
        $users = [];
        $subassigned = get_posts(
            [
                'connected_type'      => 'contacts_to_subassigned',
                'connected_direction' => 'to',
                'connected_items'     => $post_id,
                'nopaging'            => true,
                'suppress_filters'    => false,
                'meta_key'            => "type",
                'meta_value'          => "user"
            ]
        );
        foreach ( $subassigned as $c ) {
            $user_id = get_post_meta( $c->ID, "corresponds_to_user", true );
            if ( $user_id ){
                $users[] = $user_id;
            }
        }
        return $users;
    }

    /**
     * @param $post_type
     * @param $post_id
     *
     * @return array an array of user ids
     */
    public static function get_users_following_post( $post_type, $post_id ){
        $users = [];
        $assigned_to_meta = get_post_meta( $post_id, "assigned_to", true );
        $assigned_to = dt_get_user_id_from_assigned_to( $assigned_to_meta );
        if ( $post_type === "contacts" ){
            array_merge( $users, self::get_subassigned_users( $post_id ) );
        }
        $shared_with = self::get_shared_with( $post_type, $post_id, false );
        foreach ( $shared_with as $shared ){
            $users[] = $shared["user_id"];
        }
        $users_follow = get_post_meta( $post_id, "follow", false );
        foreach ( $users_follow as $follow ){
            if ( !in_array( $follow, $users ) && user_can( $follow, "view_any_". $post_type ) ){
                $users[] = $follow;
            }
        }
        $users_unfollow = get_post_meta( $post_id, "unfollow", false );
        foreach ( $users_unfollow as $unfollower ){
            if ( ( $key = array_search( $unfollower, $users ) ) !== false ){
                unset( $users[$key] );
            }
        }
        //you always follow a post if you are assigned to it.
        if ( $assigned_to ){
            $users[] = $assigned_to;
        }
        return array_unique( $users );
    }

    public static function get_multi_select_options( $post_type, $field, $search = ""){
        if ( !self::can_access( $post_type ) ){
            return new WP_Error( __FUNCTION__, "You do not have access to: " . $field, [ 'status' => 403 ] );
        }
        global $wpdb;
        $options = $wpdb->get_col( $wpdb->prepare("
            SELECT DISTINCT $wpdb->postmeta.meta_value FROM $wpdb->postmeta
            LEFT JOIN $wpdb->posts on $wpdb->posts.ID = $wpdb->postmeta.post_id
            WHERE $wpdb->postmeta.meta_key = %s
            AND $wpdb->postmeta.meta_value LIKE %s
            AND $wpdb->posts.post_type = %s
            AND $wpdb->posts.post_status = 'publish'
            ORDER BY $wpdb->postmeta.meta_value ASC
            LIMIT 20
        ;", esc_sql( $field ), '%' . esc_sql( $search ) . '%', esc_sql( $post_type )));
        return $options;
    }


    public static function delete_post( int $post_id, string $post_type ){
        if ( !self::can_delete( $post_type ) ) {
            return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
        }

        global $wpdb;
        $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->dt_notifications WHERE post_id = %s", $post_id ) );
        $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->dt_share WHERE post_id = %s", $post_id ) );
        $wpdb->query( $wpdb->prepare( "DELETE p, pm FROM $wpdb->p2p p left join $wpdb->p2pmeta pm on pm.p2p_id = p.p2p_id WHERE (p.p2p_to = %s OR p.p2p_from = %s) ", $post_id, $post_id ) );
        $wpdb->query( $wpdb->prepare( "DELETE p, pm FROM $wpdb->posts p left join $wpdb->postmeta pm on pm.post_id = p.ID WHERE p.ID = %s", $post_id ) );
        $wpdb->query( $wpdb->prepare( "DELETE c, cm FROM $wpdb->comments c left join $wpdb->commentmeta cm on cm.comment_id = c.comment_ID WHERE c.comment_post_ID = %s", $post_id ) );
        $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->dt_activity_log WHERE object_id = %s", $post_id ) );

        return true;
    }
}

/**
 * @return \Disciple_Tools_Metabox_Address
 */
function dt_address_metabox() {
    $object = new Disciple_Tools_Metabox_Address();

    return $object;
}

/**
 * Class Disciple_Tools_Metabox_Address
 */
class Disciple_Tools_Metabox_Address
{

    /**
     * Constructor function.
     *
     * @access public
     * @since  0.1.0
     */
    public function __construct() {
    } // End __construct()

    /**
     * Add Address fields html for adding a new contact channel
     *
     * @usage Added to the bottom of the Contact Details Metabox.
     */
    public function add_new_address_field() {
        global $post;

        echo '<p><a href="javascript:void(0);" onclick="jQuery(\'#new-address\').toggle();"><strong>+ Address Detail</strong></a></p>';
        echo '<table class="form-table" id="new-address" style="display: none;"><tbody>' . "\n";

        $address_types = $this->get_address_type_list( $post->post_type );

        echo '<tr><th>
                <select name="new-key-address" class="edit-input"><option value=""></option> ';
        foreach ( $address_types as $type => $value ) {

            $key = "address_" . $type;

            echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value["label"] ) . '</option>';
        }
        echo '</select></th>';
        echo '<td>
                <input type="text" name="new-value-address" id="new-address" class="edit-input" placeholder="i.e. 888 West Street, Los Angelos CO 90210" />
            </td>
            <td>
                <button type="submit" class="button">Save</button>
            </td>
            </tr>';

        echo '</tbody></table>';
    }

    /**
     * Helper function to create the unique metakey for contacts channels.
     *
     * @param  $channel
     *
     * @return string
     */
    public function create_channel_metakey( $channel ) {
        return "contact_". $channel . '_' . $this->unique_hash(); // build key
    }

    /**
     * Creates 3 digit random hash
     *
     * @return string
     */
    public function unique_hash() {
        return substr( md5( rand( 10000, 100000 ) ), 0, 3 ); // create a unique 3 digit key
    }

    /**
     * Selectable values for different channels of contact information.
     *
     * @return array
     */
    public function get_address_type_list( $post_type ) {

        switch ( $post_type ) {
            case 'contacts':
                $addresses = [
                    "home"  => [ "label" => __( 'Home', 'disciple_tools' ) ],
                    "work"  => [ "label" => __( 'Work', 'disciple_tools' ) ],
                    "other" => [ "label" => __( 'Other', 'disciple_tools' ) ],
                ];

                return $addresses;
                break;
            case 'groups':
                $addresses = [
                    "main"      => [ "label" => _x( 'Main', 'Main address', 'disciple_tools' ) ],
                    "alternate" => [ "label" => _x( 'Alternate', 'Alternate address', 'disciple_tools' ) ],
                ];

                return $addresses;
                break;
            case 'locations':
                $addresses = [
                    "main" => [ "label" => __( 'Main', 'disciple_tools' ) ],
                ];

                return $addresses;
                break;
            default:
                break;
        }
    }

    /**
     * Field: Contact Fields
     *
     * @return array
     */
    public function address_fields( $post_id ) {
        global $wpdb, $post;

        $fields = [];
        $current_fields = [];

        $id = $post->ID ?? $post_id;
        if ( isset( $id ) ) {
            $current_fields = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT
                        meta_key
                    FROM
                        `$wpdb->postmeta`
                    WHERE
                        post_id = %d
                        AND meta_key LIKE %s
                    ORDER BY
                        meta_key DESC",
                    $id,
                    $wpdb->esc_like( 'contact_address_' ) . '%'
                ),
                ARRAY_A
            );
        }

        foreach ( $current_fields as $value ) {
            $names = explode( '_', $value['meta_key'] );
            $tag = null;

            if ( strpos( $value["meta_key"], "_details" ) == false ) {
                $details = get_post_meta( $id, $value['meta_key'] . "_details", true );
                if ( $details && isset( $details["type"] ) ) {
                    if ( $names[1] != $details["type"] ) {
                        $tag = ' (' . ucwords( $details["type"] ) . ')';
                    }
                }
                $fields[ $value['meta_key'] ] = [
                    'name' => ucwords( $names[1] ) . $tag,
                    'tag'  => $names[1],
                ];
            }
        }

        return $fields;
    }

}
