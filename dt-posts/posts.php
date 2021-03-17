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
    public function __construct() {}

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

    public static function can_list_all( string $post_type ) {
        return current_user_can( "list_" . $post_type );
    }

    /**
     *
     * @return bool
     */
    public static function can_view_users() {
        return current_user_can( "dt_list_users" );
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
     * @param $post_id
     * @return bool
     */
    public static function can_delete( string $post_type, $post_id ) {
        $can_delete = current_user_can( 'delete_any_' . $post_type );
        if ( !$can_delete ){
            $contact = DT_Posts::get_post( $post_type, $post_id );
            if ( is_wp_error( $contact ) ){
                return false;
            }
            $can_delete = (int) $contact["post_author"] === get_current_user_id();
        }
        return apply_filters( 'dt_can_delete_permission', $can_delete, $post_id, $post_type );
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
        if ( $post_type !== get_post_type( $post_id ) ){
            return false;
        }
        //check if the user agent has access to all posts. Recommended only for apis
        if ( current_user_can( 'view_any_' . $post_type ) ) {
            return true;
        }

        $user = wp_get_current_user();
        $shares = $wpdb->get_results( $wpdb->prepare(
            "SELECT
                *
            FROM
                `$wpdb->dt_share`
            WHERE
                post_id = %s
                AND user_id = %s
                ",
            $post_id, $user->ID
        ), ARRAY_A );
        foreach ( $shares as $share ) {
            if ( (int) $share['user_id'] === $user->ID ) {
                return true;
            }
        }
        //return false if the user does not have access to the post
        return apply_filters( 'dt_can_view_permission', false, $post_id, $post_type );
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
        if ( $post_type !== get_post_type( $post_id ) ){
            return false;
        }
        global $wpdb;
        //Recommended only for apis
        if ( current_user_can( 'update_any_' . $post_type ) ) {
            return true;
        }
        $user = wp_get_current_user();

        $shares = $wpdb->get_results( $wpdb->prepare(
            "SELECT
                *
            FROM
                `$wpdb->dt_share`
            WHERE
                post_id = %s
                AND user_id = %s",
            $post_id, $user->ID
        ), ARRAY_A );
        foreach ( $shares as $share ) {
            if ( (int) $share['user_id'] === $user->ID ) {
                return true;
            }
        }

        return apply_filters( 'dt_can_update_permission', false, $post_id, $post_type );
    }


    public static function get_label_for_post_type( $post_type, $singular = false ){
        $post_settings = DT_Posts::get_post_settings( $post_type );
        if ( $singular ){
            if ( isset( $post_settings["label_singular"] ) ){
                return $post_settings["label_singular"];
            }
        } else {
            if ( isset( $post_settings["label_plural"] ) ){
                return $post_settings["label_plural"];
            }
        }
        return $post_type;
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

    public static function format_connection_message( $p2p_id, $activity, $action = 'connected to' ){
        // Get p2p record
        $p2p_record = p2p_get_connection( (int) $p2p_id ); // returns object

        if ( !$p2p_record ){
            if ( $activity->field_type === "connection from" ){
                $from = get_post( $activity->object_id );
                $to = get_post( $activity->meta_value );
                $from_title = wp_specialchars_decode( isset( $from->post_title ) ? $from->post_title : "" );
                $to_title = wp_specialchars_decode( isset( $to->post_title ) ? $to->post_title : "" ) ?? '#' . $activity->meta_value;
            } elseif ( $activity->field_type === "connection to" ){
                $to = get_post( $activity->object_id );
                $from = get_post( $activity->meta_value );
                $to_title = wp_specialchars_decode( isset( $to->post_title ) ? $to->post_title : "" );
                $from_title = wp_specialchars_decode( isset( $from->post_title ) ? $from->post_title : "" ) ?? '#' . $activity->meta_value;
            } else {
                return "CONNECTION DESTROYED";
            }
        } else {
            $p2p_from = get_post( $p2p_record->p2p_from, ARRAY_A );
            $p2p_to = get_post( $p2p_record->p2p_to, ARRAY_A );
            $from_title = wp_specialchars_decode( $p2p_from["post_title"] );
            $to_title = wp_specialchars_decode( $p2p_to["post_title"] );
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
                $object_note_from = sprintf( esc_html_x( 'Not baptized by %s', 'Not baptized by contact1', 'disciple_tools' ), $to_title );
            }
        } else if ($p2p_type === "contacts_to_groups"){
            if ($action == "connected to"){
                $object_note_to = sprintf( esc_html_x( '%s added to members', 'contact1 added to members', 'disciple_tools' ), $from_title );
                $object_note_from = sprintf( esc_html_x( 'Added to group %s', 'Added to group group1', 'disciple_tools' ), $to_title );
            } else {
                $object_note_to = sprintf( esc_html_x( 'Removed %s from group', 'Removed contact1 from group', 'disciple_tools' ), $from_title );
                $object_note_from = sprintf( esc_html_x( 'Removed from group %s', 'Removed from group group1', 'disciple_tools' ), $to_title );
            }
        }
        else if ( $p2p_type === "contacts_to_contacts"){
            if ($action === "connected to"){
                $object_note_to = sprintf( esc_html_x( 'Coaching %s', 'Coaching contact1', 'disciple_tools' ), $from_title );
                $object_note_from = sprintf( esc_html_x( 'Coached by %s', 'Coached by contact1', 'disciple_tools' ), $to_title );
            } else {
                $object_note_to = sprintf( esc_html_x( 'No longer coaching %s', 'No longer coaching contact1', 'disciple_tools' ), $from_title );
                $object_note_from = sprintf( esc_html_x( 'No longer coached by %s', 'No longer coached by contact1', 'disciple_tools' ), $to_title );
            }
        } else if ( $p2p_type === "contacts_to_subassigned"){
            if ($action === "connected to"){
                $object_note_to = sprintf( esc_html_x( 'Sub-assigned %s', 'Sub-assigned contact1', 'disciple_tools' ), $from_title );
                $object_note_from = sprintf( esc_html_x( 'Sub-assigned on %s', 'Sub-assigned on contact1', 'disciple_tools' ), $to_title );
            } else {
                $object_note_to = sprintf( esc_html_x( 'Removed sub-assigned %s', 'Removed sub-assigned contact1', 'disciple_tools' ), $from_title );
                $object_note_from = sprintf( esc_html_x( 'No longer sub-assigned on %s', 'No longer sub-assigned on contact1', 'disciple_tools' ), $to_title );
            }
        } else if ( $p2p_type === "contacts_to_peoplegroups" || $p2p_type === "groups_to_peoplegroups"){
            if ($action == "connected to"){
                $object_note_to = sprintf( esc_html_x( '%1$s added as people group on %2$s', 'Shaikh added as people group on contact1', 'disciple_tools' ), $to_title, $from_title );
                $object_note_from = sprintf( esc_html_x( '%s added to people groups', 'Shaikh added to people groups', 'disciple_tools' ), $to_title );
            } else {
                $object_note_to = sprintf( esc_html_x( '%1$s removed from people groups on %2$s', 'Shaikh removed from people groups on contact1', 'disciple_tools' ), $to_title, $from_title );
                $object_note_from = sprintf( esc_html_x( '%s removed from people groups', 'Shaikh removed from people groups', 'disciple_tools' ), $to_title );
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
                $object_note_to = sprintf( esc_html_x( '%s added to peer groups', 'group1 added to peer groups', 'disciple_tools' ), $from_title );
                $object_note_from = sprintf( esc_html_x( '%s added to peer groups', 'group1 added to peer groups', 'disciple_tools' ), $to_title );
            } else {
                $object_note_to = sprintf( esc_html_x( '%s removed from peer groups', 'group1 removed from peer groups', 'disciple_tools' ), $from_title );
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

    public static function format_activity_message( $activity, $post_type_settings ) {
        $fields = $post_type_settings["fields"];
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
                    if ( !empty( $activity->meta_value ) ){
                        $message = sprintf( _x( '%1$s changed to %2$s', "field1 changed to 'text'", 'disciple_tools' ), $fields[$activity->meta_key]["name"], $activity->meta_value );
                    }
                }
                if ( $fields[$activity->meta_key]["type"] === "multi_select" ){
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
                    if ( isset( $fields[$activity->meta_key]["default"][$activity->meta_value]["label"] ) ){
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
                if ( $fields[$activity->meta_key]["type"] === "date" ){
                    if ( $activity->meta_value === "value_deleted" ){
                        $message = sprintf( __( '%s removed', 'disciple_tools' ), $fields[$activity->meta_key]["name"] );
                    } else {
                        $message = $fields[$activity->meta_key]["name"] . ": {" . $activity->meta_value . "}";
                    }
                }
                if ( $fields[$activity->meta_key]["type"] === "location" ){
                    if ( $activity->meta_value === "value_deleted" ){
                        $location_grid = Disciple_Tools_Mapping_Queries::get_by_grid_id( (int) $activity->old_value );
                        $message = sprintf( _x( '%1$s removed from locations', 'Location1 removed from locations', 'disciple_tools' ), $location_grid ? $location_grid["name"] : $activity->old_value );
                    } else {
                        $location_grid = Disciple_Tools_Mapping_Queries::get_by_grid_id( (int) $activity->meta_value );
                        $message = sprintf( _x( '%1$s added to locations', 'Location1 added to locations', 'disciple_tools' ), $location_grid ? $location_grid["name"] : $activity->meta_value );
                    }
                }
                if ( $fields[$activity->meta_key]["type"] === "location_meta" ){
                    if ( $activity->meta_value === "value_deleted" ){
                        $location_grid = Disciple_Tools_Mapping_Queries::get_by_grid_id( (int) $activity->old_value );
                        $message = sprintf( _x( '%1$s removed from locations', 'Location1 removed from locations', 'disciple_tools' ), $location_grid ? $location_grid["name"] : $activity->old_value );
                    } else {
                        $location_grid = Disciple_Tools_Mapping_Queries::get_by_grid_id( (int) $activity->meta_value );
                        $message = sprintf( _x( '%1$s added to locations', 'Location1 added to locations', 'disciple_tools' ), $location_grid ? $location_grid["name"] : $activity->meta_value );
                    }
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
                                    $object_note .= $v ? _x( "verified", 'message', 'disciple_tools' ) : _x( "not verified", 'message', 'disciple_tools' );
                                }
                                if ($k === "invalid") {
                                    $object_note .= $v ? _x( "invalidated", 'message', 'disciple_tools' ) : _x( "not invalidated", 'message', 'disciple_tools' );
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
                    if ( isset( $channel[1] ) && isset( $post_type_settings["channels"][ $channel[1] ] ) ){
                        $channel = $post_type_settings["channels"][ $channel[1] ];
                        if ( $activity->old_value === "" ){
                            $message = sprintf( _x( 'Added %1$s: %2$s', 'Added Facebook: facebook.com/123', 'disciple_tools' ), $channel["label"] ?? $activity->meta_key, $activity->meta_value );
                        } else if ( $activity->meta_value != "value_deleted" ){
                            $message = sprintf( _x( 'Updated %1$s from %2$s to %3$s', 'Update Facebook form facebook.com/123 to facebook.com/mark', 'disciple_tools' ), $channel["label"] ?? $activity->meta_key, $activity->old_value, $activity->meta_value );
                        } else {
                            $message = sprintf( _x( 'Deleted %1$s: %2$s', 'Deleted Facebook: facebook.com/123', 'disciple_tools' ), $channel["label"] ?? $activity->meta_key, $activity->old_value );
                        }
                    }
                } else if ( $activity->meta_key == "title" ){
                    $message = sprintf( _x( "Name changed to: %s", 'message', 'disciple_tools' ), $activity->meta_value );
                } else if ( $activity->meta_key === "_sample"){
                    $message = _x( "Created from Demo Plugin", 'message', 'disciple_tools' );
                } else {
                    $message = $activity->meta_key . ": " . $activity->meta_value;
                }
            }
        } elseif ( $activity->action === "assignment_decline" ){
            $user = get_user_by( "ID", $activity->user_id );
            $message = sprintf( _x( "%s declined assignment", 'message', 'disciple_tools' ), $user->display_name ?? _x( "A user", 'message', 'disciple_tools' ) );
        } elseif ( $activity->action === "assignment_accepted" ){
            $user = get_user_by( "ID", $activity->user_id );
            $message = sprintf( _x( "%s accepted assignment", 'message', 'disciple_tools' ), $user->display_name ?? _x( "A user", 'message', 'disciple_tools' ) );
        } elseif ( $activity->object_subtype === "p2p" ){
            $message = self::format_connection_message( $activity->meta_id, $activity, $activity->action );
        } elseif ( $activity->object_subtype === "share" ){
            if ($activity->action === "share"){
                $message = sprintf( _x( "Shared with %s", 'message', 'disciple_tools' ), dt_get_user_display_name( $activity->meta_value ) );
            } else if ( $activity->action === "remove" ){
                $message = sprintf( _x( "Unshared with %s", 'message', 'disciple_tools' ), dt_get_user_display_name( $activity->meta_value ) );
            }
        } else {
            $message = $activity->object_note;
        }

        return apply_filters( "dt_format_activity_message", $message, $activity );
    }

    /**
     * Get the posts the user has recently viewed
     *
     * @param string $post_type
     * @param int|null $user_id
     * @param int $limit
     * @return array|object|null
     */
    public static function get_recently_viewed_posts( string $post_type, int $user_id = null, int $limit = 30 ){
        if ( !$user_id ){
            $user_id = get_current_user_id();
        }
        global $wpdb;
        $posts = $wpdb->get_results( $wpdb->prepare( "
            SELECT p.ID, p.post_title, p.post_type, p.post_date
            FROM $wpdb->posts p
            INNER JOIN (
                SELECT log.object_id
                FROM $wpdb->dt_activity_log log
                INNER JOIN (
                    SELECT max(l.histid) as maxid FROM $wpdb->dt_activity_log l
                    WHERE l.user_id = %s  AND l.action = 'viewed' AND l.object_type = %s
                    group by l.object_id
                ) x on log.histid = x.maxid
            ORDER BY log.histid desc
            LIMIT %d
            ) as log
            ON log.object_id = p.ID
            WHERE p.post_type = %s AND (p.post_status = 'publish' OR p.post_status = 'private')
        ", esc_sql( $user_id ), esc_sql( $post_type ), esc_sql( $limit ), esc_sql( $post_type ) ), OBJECT );

        $total_rows = min( $limit, sizeof( $posts ) );
        return [
            "posts" => $posts,
            "total" => $total_rows,
        ];
    }

    /**
     * Get the sql to query D.T fields.
     * @param $post_type
     * @param $query_array
     * @param string $operator
     * @param array $args
     * @return array|mixed
     */
    public static function fields_to_sql( $post_type, $query_array, $operator = "AND", $args = [] ){
        $examples = [
            "groups" => [ 3029, 39039 ],
            "groups" => [ -3029 ],
            "location_grid" => [ 123456 ],
            "location_grid" => [ -123456 ],
            "assigned_to" => [ 33 ],
            "assigned_to" => [ -33 ],
            "baptism_date" => [ "start" => "2018-01-01", "end" => "2019-01-01" ],
            "requires_update" => [ "1" ],
            "contact_phone" => [ "798456780" ],
            "contact_phone" => [ "-798456780" ],
            "contact_phone" => [ "^798456780" ],
            "nickname" => [ "hi" ],
            "nickname" => [ "-hi" ],
            "nickname" => [ "-hi", "-other" ],
            "nickname" => [ "^hi" ],
            "baptism_generation" => [ "equality" => ">", "number" => 4 ],
            "overall_status" => [ 'active' ],
            "overall_status" => [ '-close' ],
            "milestones" => [ 'milestone_has_bible', 'milestone_reading_bible' ],
            "milestones" => [ '-milestone_has_bible', '-milestone_reading_bible' ],
        ];

        $field_settings = DT_Posts::get_post_field_settings( $post_type );

        if ( empty( $args ) ){
            $args = [
                "joins_fields" => [],
                "joins_sql" => "",
                "where_sql" => ""
            ];
        }

        global $wpdb;

        $index_pos = 0;


        foreach ( $query_array as $query_key => $query_value ) {
            if ( is_string( $query_key ) ){
                $where_sql = "";
                $table_key = esc_sql( "field_" . $query_key );
                if ( isset( $field_settings[$query_key]["type"] ) ){
                    $field_type = $field_settings[$query_key]["type"];

                    if ( in_array( $query_key, [ "name", "post_date" ] ) ){
                        if ( $query_key === "name" ){
                            if ( !is_array( $query_value ) ){
                                 $query_value = [ $query_value ];
                            }
                            $connector = " OR ";
                            $equality = "LIKE";
                            //allow negative searches
                            foreach ( $query_value as $index => $name ){
                                if ( strpos( $name, "-" ) === 0 ){
                                    $equality = "NOT LIKE";
                                    $name = ltrim( $name, "-" );
                                    $connector = " AND ";
                                } else if ( strpos( $name, "^" ) === 0 ){
                                    $equality = "=";
                                    $name = ltrim( $name, "^" );
                                }
                                $val = $equality === "=" ? $name : "%" . $name . "%";
                                $where_sql .= ( $index > 0 ? $connector : " " ) . "p.post_title $equality '" . esc_sql( $val ) . "' ";
                            }
                        } else if ( $query_key === "post_date" ){
                            $index = -1;
                            foreach ( $query_value as $value_key => $value ){
                                $index++;
                                $connector = " AND ";
                                $equality = "=";
                                if ( $value_key === "start" ){
                                    $value = dt_format_date( $value, 'Y-m-d' );
                                    $equality = ">=";
                                }
                                if ( $value_key === "end" ){
                                    $value = dt_format_date( $value, 'Y-m-d' );
                                    $equality = "<=";
                                }
                                $where_sql .= ( $index > 0 ? $connector : " " ) . " p.post_date $equality '" . esc_sql( $value ) . "'";
                            }
                        }
                    } else {
                        // add postmeta join fields
                        if ( in_array( $field_type, [ 'key_select', 'multi_select', 'boolean', 'date', 'number', 'user_select' ] ) ){
                            if ( !in_array( $table_key, $args["joins_fields"] ) ){
                                $args["joins_fields"][] = $table_key;
                                $args["joins_sql"] .= " LEFT JOIN $wpdb->postmeta as $table_key ON ( $table_key.post_id = p.ID AND $table_key.meta_key = '" . esc_sql( $query_key ) . "' )";
                            }
                        }


                        if ( in_array( $field_type, [ 'key_select', 'multi_select', 'boolean', 'date', 'user_select' ] ) ){
                            /**
                             * key_select, multi_select, boolean, date
                             */
                            $index = -1;
                            $query_for_null_values = false;
                            if ( !is_array( $query_value ) ){
                                return new WP_Error( __FUNCTION__, "$query_key must be an array", [ 'status' => 400 ] );
                            }
                            foreach ( $query_value as $value_key => $value ){
                                $index++;
                                $connector = " OR ";
                                $equality = "=";
                                //allow negative searches
                                if ( strpos( $value, "-" ) === 0 ){
                                    $equality = "!=";
                                    $value = ltrim( $value, "-" );
                                    $connector = " AND ";
                                    if ( sizeof( $query_value ) === 1 ){
                                        $query_for_null_values = true;
                                    }
                                }
                                if ( $field_type === "boolean" ){
                                    if ( $value === "1" || $value === "yes" || $value === "true" ){
                                        $value = true;
                                    } elseif ( $value === "0" || $value === "no" || $value === "false" || $value === false ){
                                        $value = false;
                                        $query_for_null_values = true;
                                    }
                                    $where_sql .= ( $index > 0 ? $connector : " " ) . " $table_key.meta_value $equality '" . esc_sql( $value ) . "'";
                                }
                                //date fields
                                if ( $field_type === "date" ){
                                    $connector = " AND ";
                                    if ( $value_key === "start" ){
                                        $value = strtotime( $value );
                                        $equality = ">=";
                                    }
                                    if ( $value_key === "end" ){
                                        $value = strtotime( $value );
                                        $equality = "<=";
                                    }
                                    $where_sql .= ( $index > 0 ? $connector : " " ) . " $table_key.meta_value $equality " . esc_sql( $value );
                                }
                                if ( $field_type === "key_select" ){
                                    $where_sql .= ( $index > 0 ? $connector : " " ) . " $table_key.meta_value $equality '" . esc_sql( $value ) . "'";
                                }
                                if ( $field_type === "multi_select" ){
                                    if ( $equality === "!=" && $field_type === "multi_select" ){
                                        $where_sql .= ( $index > 0 ? $connector : " " ) . "not exists (select 1 from $wpdb->postmeta where $wpdb->postmeta.post_id = p.ID and $wpdb->postmeta.meta_key = '" . esc_sql( $query_key ) ."'  and $wpdb->postmeta.meta_value = '" . esc_sql( $value ) . "') ";
                                    } else {
                                        $where_sql .= ( $index > 0 ? $connector : " " ) . " $table_key.meta_value $equality '" . esc_sql( $value ) . "'";
                                    }
                                }
                                if ( $field_type === "user_select" ){
                                    if ( $equality === "!=" ){
                                        $query_for_null_values = true;
                                    }
                                    if ( $value === "me" ){
                                        $value = get_current_user_id();
                                    }
                                    $where_sql .= ( $index > 0 ? " $connector" : " " ) . " $table_key.meta_value $equality 'user-" . esc_sql( $value ) . "'";
                                }
                            }
                            if ( $query_for_null_values ){
                                $where_sql .= " OR $table_key.meta_value IS NULL ";
                            }
                            if ( empty( $query_value ) ){
                                $where_sql .= " $table_key.meta_value IS NULL ";
                            }
                        } else if ( in_array( $field_type, [ 'connection' ] ) ){
                            /**
                             * connection
                             */
                            if ( !isset( $field_settings[$query_key]["p2p_direction"], $field_settings[$query_key]["p2p_key"] ) ){
                                continue;
                            }

                            $in = [];
                            $not_in = [];
                            if ( !is_array( $query_value ) ){
                                return new WP_Error( __FUNCTION__, "$query_key must be an array", [ 'status' => 400 ] );
                            }
                            if ( empty( $query_value ) ){
                                if ( $field_settings[$query_key]["p2p_direction"] === "to" ){
                                    $where_sql .= " p.ID NOT IN (
                                        SELECT p2p_to from $wpdb->p2p WHERE p2p_type = '" . esc_html( $field_settings[$query_key]["p2p_key"] ) . "'
                                    ) ";
                                } else {
                                    $where_sql .= " p.ID NOT IN (
                                        SELECT p2p_from from $wpdb->p2p WHERE p2p_type = '" . esc_html( $field_settings[$query_key]["p2p_key"] ) . "'
                                    ) ";
                                }
                            }

                            foreach ( $query_value as &$connection ) {
                                if ( $connection === "me" ){
                                    $contact_id = Disciple_Tools_Users::get_contact_for_user( get_current_user_id() );
                                    if ( $contact_id ){
                                        $connection = $contact_id;
                                    }
                                }
                                if ( strpos( $connection, "-" ) === 0 ){
                                    $connection = ltrim( $connection, "-" );
                                    $not_in[] = $connection;
                                } else {
                                    $in[] = $connection;
                                }
                            }
                            if ( !empty( $in ) ){
                                $connection_ids = dt_array_to_sql( $in );
                                if ( $field_settings[$query_key]["p2p_direction"] === "to" ){
                                    $where_sql .= " p.ID IN (
                                        SELECT p2p_to from $wpdb->p2p WHERE p2p_type = '" . esc_html( $field_settings[$query_key]["p2p_key"] ) . "' AND p2p_from IN (" .  $connection_ids .")
                                    ) ";
                                } else {
                                    $where_sql .= " p.ID IN (
                                        SELECT p2p_from from $wpdb->p2p WHERE p2p_type = '" . esc_html( $field_settings[$query_key]["p2p_key"] ) . "' AND p2p_to IN (" .  $connection_ids .")
                                    ) ";
                                }
                            }
                            if ( !empty( $not_in ) ){
                                $connection_ids = dt_array_to_sql( $not_in );
                                $where_sql .= ( !empty( $where_sql ) ? " AND " : "" );
                                if ( $field_settings[$query_key]["p2p_direction"] === "to" ){
                                    $where_sql .= " p.ID NOT IN (
                                        SELECT p2p_to from $wpdb->p2p WHERE p2p_type = '" . esc_html( $field_settings[$query_key]["p2p_key"] ) . "' AND p2p_from IN (" .  $connection_ids .")
                                    ) ";
                                } else {
                                    $where_sql .= " p.ID NOT IN (
                                        SELECT p2p_from from $wpdb->p2p WHERE p2p_type = '" . esc_html( $field_settings[$query_key]["p2p_key"] ) . "' AND p2p_to IN (" .  $connection_ids .")
                                    ) ";
                                }
                            }
                        } else if ( in_array( $field_type, [ 'location' ] ) ){
                            /**
                             * location
                             */
                            if ( !in_array( $table_key, $args["joins_fields"] ) ){
                                $args["joins_fields"][] = $table_key;
                                $args["joins_sql"] .= " LEFT JOIN (
                                    SELECT g.admin0_grid_id, g.admin1_grid_id,
                                           g.admin2_grid_id, g.admin3_grid_id,
                                           g.grid_id, g.level,
                                           p.post_id
                                    FROM $wpdb->postmeta as p
                                    LEFT JOIN $wpdb->dt_location_grid as g ON g.grid_id=p.meta_value
                                    WHERE p.meta_key = 'location_grid'

                                ) as $table_key ON ( $table_key.post_id = p.ID )";
                            }
                            $in = [];
                            $not_in = [];
                            if ( !is_array( $query_value ) ){
                                return new WP_Error( __FUNCTION__, "$query_key must be an array", [ 'status' => 400 ] );
                            }
                            if ( empty( $query_value ) ){
                                $where_sql .= "$table_key.post_id IS NULL";
                            }
                            foreach ( $query_value as $value ){
                                if ( strpos( $value, "-" ) === 0 ){
                                    $value = ltrim( $value, "-" );
                                    $not_in[] = $value;
                                } else {
                                    $in[] = $value;
                                }
                            }
                            if ( !empty( $in ) ){
                                $location_grid_ids = dt_array_to_sql( $in );
                                $where_sql .= " (
                                    $table_key.admin0_grid_id IN (" . $location_grid_ids .")
                                    OR $table_key.admin1_grid_id IN (" . $location_grid_ids .")
                                    OR $table_key.admin2_grid_id IN (" . $location_grid_ids .")
                                    OR $table_key.admin3_grid_id IN (" . $location_grid_ids .")
                                    OR $table_key.grid_id IN (" . $location_grid_ids .") )
                                ";
                            }
                            if ( !empty( $not_in ) ){
                                $location_grid_ids = dt_array_to_sql( $not_in );
                                $where_sql .= ( !empty( $where_sql ) ? " AND " : "" ) . "
                                    ( $table_key.admin0_grid_id IS NULL OR $table_key.admin0_grid_id NOT IN (" . $location_grid_ids .") )
                                    AND ( $table_key.admin1_grid_id IS NULL OR $table_key.admin1_grid_id NOT IN (" . $location_grid_ids .") )
                                    AND ( $table_key.admin2_grid_id IS NULL OR $table_key.admin2_grid_id NOT IN (" . $location_grid_ids .") )
                                    AND ( $table_key.admin3_grid_id IS NULL OR $table_key.admin3_grid_id NOT IN (" . $location_grid_ids .") )
                                    AND ( $table_key.grid_id IS NULL OR $table_key.grid_id NOT IN (" . $location_grid_ids .") )
                                ";
                            }
                        } else if ( in_array( $field_type, [ 'text', 'communication_channel' ] ) ){
                            /**
                             * text, communication_channel
                             */
                            if ( !in_array( $table_key, $args["joins_fields"] ) ){
                                $args["joins_fields"][] = $table_key;
                                $extra = $field_type === 'communication_channel' ? '%' : '';
                                $args["joins_sql"] .= " LEFT JOIN $wpdb->postmeta as $table_key ON ( $table_key.post_id = p.ID AND $table_key.meta_key LIKE '" . esc_sql( $query_key . $extra ) . "' AND $table_key.meta_key NOT LIKE '%_details' )";
                            }
                            $index = -1;
                            $connector = " OR ";
                            $query_for_null_values = null;
                            if ( !is_array( $query_value ) ){
                                return new WP_Error( __FUNCTION__, "$query_key must be an array", [ 'status' => 400 ] );
                            }
                            if ( empty( $query_value ) ){
                                $where_sql .= " $table_key.meta_value IS NULL ";
                            }
                            foreach ( $query_value as $value_key => $value ){
                                $index ++;
                                $equality = "LIKE";
                                //allow negative searches
                                if ( strpos( $value, "-" ) === 0 ){
                                    $equality = "NOT LIKE";
                                    $value = ltrim( $value, "-" );
                                    $connector = " AND ";
                                } else if ( strpos( $value, "^" ) === 0 ){
                                    $equality = "=";
                                    $value = ltrim( $value, "^" );

                                }
                                $query_for_null_values = ( $query_for_null_values === null && $equality === "NOT LIKE" ) ? true : false;
                                $val = $equality === "=" ? $value : "%" . $value . "%";
                                $where_sql .= ( $index > 0 ? $connector : " " ) . " $table_key.meta_value $equality '" . esc_sql( $val ) . "'";
                            }
                            if ( $query_for_null_values ){
                                $where_sql .= " OR $table_key.meta_value IS NULL ";
                            }
                        } else if ( in_array( $field_type, [ 'number' ] ) ){
                            /**
                             * number
                             */
                            $equality = '=';
                            $value = is_numeric( $query_value ) ? esc_sql( $query_value ) : [];
                            if ( isset( $query_value['operator'] ) ){
                                $equality = esc_sql( $query_value['operator'] );
                            }
                            if ( isset( $query_value['number'] ) ){
                                $value = esc_sql( $query_value['number'] );
                            }
                            if ( empty( $value ) && $value !== 0 ){
                                $where_sql .= " $table_key.meta_value IS NULL";
                            } else {
                                $where_sql .= " $table_key.meta_value $equality " . esc_sql( $value );
                            }
                        } else {
                            return new WP_Error( __FUNCTION__, "you can not filter $field_type fields", [ 'status' => 400 ] );
                        }
                    }
                } else if ( $query_key === 'shared_with' ){
                    if ( !in_array( $table_key, $args["joins_fields"] ) ){
                        $args["joins_fields"][] = $table_key;
                        $args["joins_sql"] .= " LEFT JOIN $wpdb->dt_share as $table_key ON ( $table_key.post_id = p.ID )";
                    }
                    foreach ( $query_value as &$v ){
                        if ( $v === "me" ){
                            $v = get_current_user_id();
                        }
                    }
                    $user_ids = dt_array_to_sql( $query_value );
                    $where_sql .= " $table_key.user_id IN ( $user_ids ) ";

                } else {
                    return new WP_Error( __FUNCTION__, "One or more fields do not exist", [ 'key' => $query_key, 'status' => 400 ] );
                }
                if ( !empty( $where_sql )){
                    $index_pos++;

                    $args["where_sql"] .= ( ( $index_pos > 1 ) ? $operator : " " ) . " (";
                    $args["where_sql"] .= $where_sql;
                    $args["where_sql"] .= ')';
                }
            } else if ( is_array( $query_value ) ){
                $index_pos++;
                $args["where_sql"] .= ( ( $index_pos > 1 ) ? $operator : " " ) . " (";
                $args = self::fields_to_sql( $post_type, $query_value, $operator === "AND" ? "OR" : "AND", $args );
                if ( is_wp_error( $args ) ){
                    return $args;
                }
                $args["where_sql"] .= ")";
            }
        }
        $args["where_sql"] = str_replace( "$operator ()", "", $args["where_sql"] );
        return $args;

    }


    public static function search_viewable_post( string $post_type, array $query, bool $check_permissions = true ){
        if ( $check_permissions && !self::can_access( $post_type ) ) {
            return new WP_Error( __FUNCTION__, "You do not have access to these", [ 'status' => 403 ] );
        }
        $post_types = DT_Posts::get_post_types();
        if ( !in_array( $post_type, $post_types ) ){
            return new WP_Error( __FUNCTION__, "$post_type in not a valid post type", [ 'status' => 400 ] );
        }

        //filter in to add or remove query parameters.
        $query = apply_filters( 'dt_search_viewable_posts_query', $query );

        global $wpdb;

        $post_settings = DT_Posts::get_post_settings( $post_type );
        $post_fields = $post_settings["fields"];

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
        $limit = 100;
        if ( isset( $query["limit"] )){
            $limit = esc_sql( sanitize_text_field( $query["limit"] ) );
            $limit = MIN( $limit, 1000 );
            unset( $query["limit"] );
        }
        $sort = "";
        $sort_dir = "asc";
        if ( isset( $query["sort"] )){
            $sort = esc_sql( sanitize_text_field( $query["sort"] ) );
            if ( strpos( $sort, "-" ) === 0 ){
                $sort_dir = "desc";
                $sort = str_replace( "-", "", $sort );
            }
            unset( $query["sort"] );
        }
        $fields_to_search = [];
        if ( isset( $query["fields_to_search"] )){
            $fields_to_search = $query["fields_to_search"];
            unset( $query ["fields_to_search"] );
        }
        if ( isset( $query["combine"] )){
            unset( $query["combine"] ); //remove deprecated combine
        }

        if ( isset( $query["fields"] ) ){
            $query = $query["fields"];
        }

        $joins = "";
        $post_query = "";

        if ( !empty( $search )){
            $other_search_fields = apply_filters( "dt_search_extra_post_meta_fields", [] );

            if ( empty( $fields_to_search ) ) {
                $post_query .= "AND ( ( p.post_title LIKE '%" . esc_sql( $search ) . "%' )
                    OR p.ID IN ( SELECT post_id
                                FROM $wpdb->postmeta
                                WHERE meta_key LIKE 'contact_%'
                                AND REPLACE( meta_value, ' ', '') LIKE '%" . esc_sql( str_replace( ' ', '', $search ) ) . "%'
                    )
                ";
            }
            if ( !empty( $fields_to_search ) ) {
                if ( in_array( 'name', $fields_to_search ) ) {
                    $post_query .= "AND ( ( p.post_title LIKE '%" . esc_sql( $search ) . "%' )
                        OR p.ID IN ( SELECT post_id
                                    FROM $wpdb->postmeta
                                    WHERE meta_key LIKE 'contact_%'
                                    AND REPLACE( meta_value, ' ', '') LIKE '%" . esc_sql( str_replace( ' ', '', $search ) ) . "%'
                        )
                    ";
                } else {
                    $post_query .= "AND ( ";
                }
                if ( in_array( 'all', $fields_to_search ) ) {
                    if ( substr( $post_query, -6 ) !== 'AND ( ' ) {
                        $post_query .= "OR ";
                    }
                    $post_query .= "p.ID IN ( SELECT comment_post_ID
                    FROM $wpdb->comments
                    WHERE comment_content LIKE '%" . esc_sql( str_replace( ' ', '', $search ) ) . "%'
                    ) OR p.ID IN ( SELECT post_id
                    FROM $wpdb->postmeta
                    WHERE meta_value LIKE '%" . esc_sql( $search ) . "%'
                    ) ";
                } else {
                    if ( in_array( 'comment', $fields_to_search )) {
                        if ( substr( $post_query, -6 ) !== 'AND ( ' ) {
                            $post_query .= "OR ";
                        }
                        $post_query .= " p.ID IN ( SELECT comment_post_ID
                        FROM $wpdb->comments
                        WHERE comment_content LIKE '%" . esc_sql( str_replace( ' ', '', $search ) ) . "%'
                        ) ";
                    }
                    foreach ( $fields_to_search as $field ) {
                        array_push( $other_search_fields, $field );
                    }
                }
            }
            foreach ( $other_search_fields as $field ){
                if ( substr( $post_query, -6 ) !== 'AND ( ' ) {
                    $post_query .= "OR ";
                }
                $post_query .= "p.ID IN ( SELECT post_id
                             FROM $wpdb->postmeta
                             WHERE meta_key LIKE '" . esc_sql( $field ) . "'
                             AND meta_value LIKE '%" . esc_sql( $search ) . "%'
                ) ";
            }
            $post_query .= " ) ";

            if ( $post_type === "peoplegroups" ) {

                $locale = get_user_locale();

                $post_query = " OR p.ID IN ( SELECT post_id
                                  FROM $wpdb->postmeta
                                  WHERE meta_key LIKE '" . esc_sql( $locale ) . "'
                                  AND meta_value LIKE '%" . esc_sql( $search ) . "%' )";
            }
        }

        $sort_sql = "";
        if ( $sort === "name" || $sort === "post_title"){
            $sort_sql = "p.post_title  " . $sort_dir;
        } elseif ( $sort === "post_date" ){
            $sort_sql = "p.post_date  " . $sort_dir;
        }
        if ( empty( $sort ) && isset( $query["name"][0] ) ){
            $sort_sql = "( p.post_title = '" . esc_sql( $query["name"][0] ) . "' ) desc, p.post_title asc";
        }

        if ( empty( $sort_sql ) && isset( $sort, $post_fields[$sort] ) ) {
            if ( $post_fields[$sort]["type"] === "key_select" ) {
                $keys = array_keys( $post_fields[ $sort ]["default"] );
                $joins = "LEFT JOIN $wpdb->postmeta as sort ON ( p.ID = sort.post_id AND sort.meta_key = '$sort')";
                $sort_sql  = "CASE ";
                foreach ( $keys as $index => $key ) {
                    $sort_sql .= "WHEN ( sort.meta_value = '" . esc_sql( $key ) . "' ) THEN $index ";
                }
                $sort_sql .= "else 98 end ";
                $sort_sql .= $sort_dir;
            } elseif ( $post_fields[$sort]["type"] === "multi_select" && !empty( $post_fields[$sort]["default"] )){
                $sort_sql = "CASE ";
                $joins = "";
                $keys = array_reverse( array_keys( $post_fields[$sort]["default"] ) );
                foreach ( $keys as $index  => $key ){
                    $alias = $sort . '_' . esc_sql( $key );
                    $joins .= "LEFT JOIN $wpdb->postmeta as $alias ON
                    ( p.ID = $alias.post_id AND $alias.meta_key = '$sort' AND $alias.meta_value = '" . esc_sql( $key ) . "') ";
                    $sort_sql .= "WHEN ( $alias.meta_value = '" . esc_sql( $key ) . "' ) THEN $index ";
                }
                $sort_sql .= "else 1000 end ";
                $sort_sql .= $sort_dir;
            } elseif ( $post_fields[$sort]["type"] === "connection" ){
                if ( isset( $post_fields[$sort]["p2p_key"], $post_fields[$sort]["p2p_direction"] ) ){
                    if ( $post_fields[$sort]["p2p_direction"] === "from" ){
                        $joins = "LEFT JOIN $wpdb->p2p as sort ON ( sort.p2p_from = p.ID AND sort.p2p_type = '" .  esc_sql( $post_fields[$sort]["p2p_key"] ) . "' )
                        LEFT JOIN $wpdb->posts as p2p_post ON (p2p_post.ID = sort.p2p_to)";
                    } else {
                        $joins = "LEFT JOIN $wpdb->p2p as sort ON ( sort.p2p_to = p.ID AND sort.p2p_type = '" .  esc_sql( $post_fields[$sort]["p2p_key"] ) . "' )
                        LEFT JOIN $wpdb->posts as p2p_post ON (p2p_post.ID = sort.p2p_from)";
                    }
                    $sort_sql = "ISNULL(p2p_post.post_title), p2p_post.post_title $sort_dir";
                }
            } elseif ( $post_fields[$sort]["type"] === "communication_channel" ){
                $joins = "LEFT JOIN $wpdb->postmeta as sort ON ( p.ID = sort.post_id AND sort.meta_key LIKE '{$sort}%' AND sort.meta_key NOT LIKE '%_details' AND sort.meta_id = ( SELECT meta_id FROM $wpdb->postmeta pm_sort  where pm_sort.post_id = p.ID AND pm_sort.meta_key LIKE '{$sort}%' AND sort.meta_key NOT LIKE '%_details' LIMIT 1 ))";
                $sort_sql = "sort.meta_value IS NULL, sort.meta_value = '', sort.meta_value * 1 $sort_dir, sort.meta_value $sort_dir";
            } elseif ( $post_fields[$sort]["type"] === "location" ){
                $joins = "LEFT JOIN $wpdb->postmeta sort ON ( sort.post_id = p.ID AND sort.meta_key = '$sort' AND sort.meta_id = ( SELECT meta_id FROM $wpdb->postmeta pm_sort where pm_sort.post_id = p.ID AND pm_sort.meta_key = '$sort' LIMIT 1 ) )";
                $sort_sql = "sort.meta_value IS NULL, sort.meta_value $sort_dir";
            } else {
                $joins = "LEFT JOIN $wpdb->postmeta as sort ON ( p.ID = sort.post_id AND sort.meta_key = '$sort')";
                $sort_sql = "sort.meta_value IS NULL, sort.meta_value $sort_dir";
            }
        }
        if ( empty( $sort_sql ) ){
            $sort_sql = "p.post_title asc";
        }

        $group_by_sql = "";
        if ( strpos( $sort_sql, 'sort.meta_value' ) !== false ){
            $group_by_sql = ", sort.meta_value";
        }

        $permissions = [
            "shared_with" => [ "me" ]
        ];
        $permissions = apply_filters( "dt_filter_access_permissions", $permissions, $post_type );

        if ( $check_permissions && !empty( $permissions )){
            $query[] = $permissions;
        }

        $fields_sql = self::fields_to_sql( $post_type, $query );
        if ( is_wp_error( $fields_sql ) ){
            return $fields_sql;
        }

        // phpcs:disable
        // WordPress.WP.PreparedSQL.NotPrepared
        $posts = $wpdb->get_results("
            SELECT SQL_CALC_FOUND_ROWS p.ID, p.post_title, p.post_type, p.post_date
            FROM $wpdb->posts p " . $fields_sql["joins_sql"] . " " . $joins . " WHERE " . $fields_sql["where_sql"] . " " . ( empty( $fields_sql["where_sql"] ) ? "" : " AND " ) . "
            (p.post_status = 'publish') AND p.post_type = '" . esc_sql ( $post_type ) . "' " .  $post_query . "
            GROUP BY p.ID " . $group_by_sql . "
            ORDER BY " . $sort_sql . "
            LIMIT " . esc_sql( $offset ) .", " . $limit . "
        ", OBJECT );

        if ( empty( $posts ) && !empty( $wpdb->last_error )){
            return new WP_Error( __FUNCTION__, "Sorry, we had a query issue.", [ 'status' => 500 ] );
        }


        // phpcs:enable
        $total_rows = $wpdb->get_var( "SELECT found_rows();" );

        //search by post_id
        if ( is_numeric( $search ) ){
            $post = get_post( $search );
            if ( $post && self::can_view( $post_type, $post->ID ) ){
                $posts[] = $post;
                $total_rows++;
            }
        }
        //decode special characters in post titles
        foreach ( $posts as $post ) {
            $post->post_title = wp_specialchars_decode( $post->post_title );
        }
        return [
            "posts" => $posts,
            "total" => $total_rows,
        ];
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
    public static function get_post_by_title_cached( $title, $output = OBJECT, $post_type = 'page', $connection_type = 'none' ) {
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


    /*
     * Get disctinct meta values for a specific meta key (joins tables)
     */
    public static function get_multi_select_options( $post_type, $field, $search = "", $limit = 20 ){
        if ( !self::can_access( $post_type ) ){
            return new WP_Error( __FUNCTION__, "You do not have access to: " . $field, [ 'status' => 403 ] );
        }
        global $wpdb;
        $options = $wpdb->get_col( $wpdb->prepare("
            SELECT DISTINCT $wpdb->postmeta.meta_value FROM $wpdb->postmeta
            LEFT JOIN $wpdb->posts on $wpdb->posts.ID = $wpdb->postmeta.post_id
            WHERE $wpdb->postmeta.meta_key = %s
            AND $wpdb->postmeta.meta_value LIKE %s
            AND $wpdb->posts.post_status = 'publish'
            ORDER BY $wpdb->postmeta.meta_value ASC
            LIMIT %d
        ;", esc_sql( $field ), '%' . esc_sql( $search ) . '%', esc_sql( $limit ) ) );

        return $options;
    }

    public static function delete_post( string $post_type, int $post_id, bool $check_permissions = true ){
        if ( $check_permissions && !self::can_delete( $post_type, $post_id ) ) {
            return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
        }

        global $wpdb;
        $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->dt_notifications WHERE post_id = %s", $post_id ) );
        $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->dt_share WHERE post_id = %s", $post_id ) );
        $wpdb->query( $wpdb->prepare( "DELETE p, pm FROM $wpdb->p2p p left join $wpdb->p2pmeta pm on pm.p2p_id = p.p2p_id WHERE (p.p2p_to = %s OR p.p2p_from = %s) ", $post_id, $post_id ) );
        $wpdb->query( $wpdb->prepare( "DELETE p, pm FROM $wpdb->posts p left join $wpdb->postmeta pm on pm.post_id = p.ID WHERE p.ID = %s", $post_id ) );
        $wpdb->query( $wpdb->prepare( "DELETE c, cm FROM $wpdb->comments c left join $wpdb->commentmeta cm on cm.comment_id = c.comment_ID WHERE c.comment_post_ID = %s", $post_id ) );
        $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->dt_activity_log WHERE object_id = %s", $post_id ) );
        $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->dt_post_user_meta WHERE post_id = %s", $post_id ) );

        return true;
    }

    public static function is_post_key_contact_method_or_connection( $post_settings, $key ) {
        $channel_keys = [];
        foreach ( $post_settings["channels"] as $channel_key => $channel_value ) {
            $channel_keys[] = "contact_" . $channel_key;
        }
        $is_communication_method = isset( $post_settings["fields"][$key]["type"] ) && $post_settings["fields"][$key]["type"] === "communication_channel";
        return in_array( $key, $post_settings["connection_types"] ) || in_array( $key, $channel_keys ) || $is_communication_method;
    }

    /**
     * Make sure there are no extra or misspelled fields
     * Make sure the field values are the correct format
     *
     * @param array $post_settings
     * @param array $fields
     * @param array $allowed_fields
     *
     * @return array
     */
    public static function check_for_invalid_post_fields( array $post_settings, array $fields, array $allowed_fields = [] ) {
        $bad_fields = [];
        $field_settings = $post_settings["fields"];
        $field_settings['title'] = "";
        foreach ( $fields as $field => $value ) {
            if ( !isset( $field_settings[ $field ] ) && !self::is_post_key_contact_method_or_connection( $post_settings, $field ) && !in_array( $field, $allowed_fields ) ) {
                $bad_fields[] = $field;
            }
        }

        return $bad_fields;
    }

    public static function update_multi_select_fields( array $field_settings, int $post_id, array $fields, array $existing_contact = null ){
        foreach ( $fields as $field_key => $field ){
            if ( isset( $field_settings[$field_key] ) && ( $field_settings[$field_key]["type"] === "multi_select" ) ){
                if ( !isset( $field["values"] )){
                    return new WP_Error( __FUNCTION__, "missing values field on: " . $field_key, [ 'status' => 400 ] );
                }
                if ( isset( $field["force_values"] ) && $field["force_values"] == true ){
                    delete_post_meta( $post_id, $field_key );
                    $existing_contact[ $field_key ] = [];
                }
                foreach ( $field["values"] as $value ){
                    if ( isset( $value["value"] )){
                        if ( isset( $value["delete"] ) && $value["delete"] == true ){
                            delete_post_meta( $post_id, $field_key, $value["value"] );
                        } else {
                            $existing_array = isset( $existing_contact[ $field_key ] ) ? $existing_contact[ $field_key ] : [];
                            if ( !in_array( $value["value"], $existing_array ) ){
                                add_post_meta( $post_id, $field_key, $value["value"] );
                            }
                        }
                    } else {
                        return new WP_Error( __FUNCTION__, "Something wrong on field: " . $field_key, [ 'status' => 500 ] );
                    }
                }
            }
        }
        return $fields;
    }

    public static function update_location_grid_fields( array $field_settings, int $post_id, array $fields, $post_type, array $existing_post = null ){

        foreach ( $fields as $field_key => $field ){

            /********************************************************
             * Basic Locations
             ********************************************************/
            if ( isset( $field_settings[$field_key] ) && ( $field_settings[$field_key]["type"] === "location" ) ){
                if ( !isset( $field["values"] ) ) {
                    return new WP_Error( __FUNCTION__, "missing values field on: " . $field_key, [ 'status' => 400 ] );
                }
                if ( isset( $field["force_values"] ) && $field["force_values"] == true ){
                    delete_post_meta( $post_id, $field_key );
                    $existing_post[ $field_key ] = [];
                }
                foreach ( $field["values"] as $value ){
                    if ( isset( $value["value"] )){
                        if ( isset( $value["delete"] ) && $value["delete"] == true ){
                            delete_post_meta( $post_id, $field_key, $value["value"] );
                        } else {
                            $existing_array = isset( $existing_post[ $field_key ] ) ? $existing_post[ $field_key ] : [];
                            if ( !in_array( $value["value"], $existing_array ) ){
                                add_post_meta( $post_id, $field_key, $value["value"] );
                            }
                        }
                    } else {
                        return new WP_Error( __FUNCTION__, "Something wrong on field: " . $field_key, [ 'status' => 500 ] );
                    }
                }
            } // end default location_grid processing


            /********************************************************
             * Location Meta Grid - Mapbox Extension
             *
             * Delete
             *  grid_meta_id: 0,
                delete: true
             *
             * Add by grid_id
             * location_grid => 12345
             *
             * Add by Mabox Response
             * lng => 0,
             * lat => 0,
             * level => (country,local,place,nieghborhood,address,admin0,admin1,admin2,admin3,admin4, or admin5),
             * label: readable address,
             * source: (ip, user),
             *
             ********************************************************/
            if ( isset( $field_settings[$field_key] ) && ( $field_settings[$field_key]["type"] === "location_meta" ) ){
                if ( !isset( $field["values"] ) ) {
                    return new WP_Error( __FUNCTION__, "missing values field on: " . $field_key, [ 'status' => 400 ] );
                }
                $geocoder = new Location_Grid_Geocoder();

                // delete everything
                if ( isset( $field["force_values"] ) && $field["force_values"] == true ){
                    delete_post_meta( $post_id, 'location_grid' );
                    delete_post_meta( $post_id, 'location_grid_meta' );
                    Location_Grid_Meta::delete_location_grid_meta( $post_id, 'all', null );
                    $existing_post[ $field_key ] = [];
                }

                // process crud
                foreach ( $field["values"] as $value ){

                    // delete
                    if ( isset( $value["delete"] ) && $value["delete"] == true ) {
                        Location_Grid_Meta::delete_location_grid_meta( $post_id, 'grid_meta_id', $value["grid_meta_id"], $existing_post );
                    }

                    // is new but has provided grid_id
                    else if ( isset( $value["grid_id"] ) && ! empty( $value["grid_id"] ) ) {
                        $grid = $geocoder->query_by_grid_id( $value["grid_id"] );
                        if ( $grid ) {
                            $location_meta_grid = [];

                            Location_Grid_Meta::validate_location_grid_meta( $location_meta_grid );
                            $location_meta_grid['post_id'] = $post_id;
                            $location_meta_grid['post_type'] = $post_type;
                            $location_meta_grid['grid_id'] = $grid["grid_id"];
                            $location_meta_grid['lng'] = $grid["longitude"];
                            $location_meta_grid['lat'] = $grid["latitude"];
                            $location_meta_grid['level'] = $grid["level_name"];
                            $location_meta_grid['label'] = $grid["name"];

                            $potential_error = Location_Grid_Meta::add_location_grid_meta( $post_id, $location_meta_grid );
                            if ( is_wp_error( $potential_error ) ){
                                return $potential_error;
                            }
                        }
                    }
                    // new
                    else {

                        Location_Grid_Meta::validate_location_grid_meta( $value );

                        if ( $value['level'] === 'country' ) {
                            $value['level'] = 'admin0';
                        } else if ( $value['level'] === 'region' ) {
                            $value['level'] = 'admin1';
                        }


                        $grid = $geocoder->get_grid_id_by_lnglat( $value['lng'], $value['lat'], null, $value['level'] );
                        if ( $grid ) {
                            $value['grid_id'] = $grid['grid_id'];
                            $value['post_type'] = $post_type;

                            $potential_error = Location_Grid_Meta::add_location_grid_meta( $post_id, $value );
                            if ( is_wp_error( $potential_error ) ){
                                return $potential_error;
                            }
                        }
                    }
                }
            } // end location_grid processing
        }
        return $fields;
    }

    public static function update_post_contact_methods( array $post_settings, int $post_id, array $fields, array $existing_contact = null ){
        // update contact details (phone, facebook, etc)
        foreach ( $post_settings["fields"] as $field_key => $field_settings ) {
            if ( $field_settings["type"] !== "communication_channel" ){
                continue;
            }
            $details_key = $field_key;
            $values = [];
            if ( isset( $fields[$details_key] ) && isset( $fields[$details_key]["values"] ) ){
                $values = $fields[$details_key]["values"];
            } else if ( isset( $fields[$details_key] ) && is_array( $fields[$details_key] ) ) {
                $values = $fields[$details_key];
            }
            if ( $existing_contact && isset( $fields[$details_key] ) &&
                 isset( $fields[$details_key]["force_values"] ) &&
                 $fields[$details_key]["force_values"] == true ){
                foreach ( $existing_contact[$details_key] as $contact_value ){
                    //don't delete the value if it will be updated later.
                    $continue = true;
                    foreach ( $values as $field ){
                        if ( isset( $field["key"] ) && $field["key"] === $contact_value["key"] ){
                            $continue = false;
                        }
                    }
                    if ( $continue ){
                        $potential_error = delete_post_meta( $post_id, $contact_value["key"] );
                        if ( is_wp_error( $potential_error ) ){
                            return $potential_error;
                        }
                        $potential_error = delete_post_meta( $post_id, $contact_value["key"] . '_details' );
                        if ( is_wp_error( $potential_error ) ){
                            return $potential_error;
                        }
                    }
                }
            }
            foreach ( $values as $field ){
                if ( isset( $field["delete"] ) && $field["delete"] == true){
                    if ( !isset( $field["key"] )){
                        return new WP_Error( __FUNCTION__, "missing key on: " . $details_key, [ 'status' => 400 ] );
                    }
                    //delete field
                    $potential_error = delete_post_meta( $post_id, $field["key"] );
                    if ( is_wp_error( $potential_error ) ){
                        return $potential_error;
                    }
                    $potential_error = delete_post_meta( $post_id, $field["key"] . '_details' );
                    if ( is_wp_error( $potential_error ) ){
                        return $potential_error;
                    }
                } else if ( isset( $field["key"] ) ){
                    //update field
                    $potential_error = self::update_post_contact_method( $post_id, $field["key"], $field );
                } else if ( isset( $field["value"] ) ) {
                    $field["key"] = "new-".$details_key;
                    //create field
                    if ( !empty( $field["value"] ) ){
                        $potential_error = self::add_post_contact_method( $post_settings, $post_id, $field["key"], $field["value"], $field );
                    }
                } else {
                    return new WP_Error( __FUNCTION__, "Is not an array or missing value on: " . $details_key, [ 'status' => 400 ] );
                }
                if ( isset( $potential_error ) && is_wp_error( $potential_error ) ) {
                    return $potential_error;
                }
            }
        }
        return $fields;
    }




    public static function update_post_user_meta_fields( array $field_settings, int $post_id, array $fields, array $existing_record ){
        global $wpdb;
        foreach ( $fields as $field_key => $field ) {
            if ( isset( $field_settings[ $field_key ] ) && ( $field_settings[ $field_key ]["type"] === "post_user_meta" ) ) {
                if ( !isset( $field["values"] )){
                    return new WP_Error( __FUNCTION__, "missing values field on: " . $field_key, [ 'status' => 400 ] );
                }

                foreach ( $field["values"] as $value ){
                    if ( isset( $value["value"] ) || ( !empty( $value["delete"] && !empty( $value['id'] ) ) ) ){
                        $current_user_id = get_current_user_id();
                        if ( !$current_user_id ){
                            return new WP_Error( __FUNCTION__, "Cannot update post_user_meta fields for no user.", [ 'status' => 400 ] );
                        }
                        if ( !empty( $value["id"] ) ) {
                            //see if we find the value with the correct id on this contact for this user.
                            $exists = false;
                            $existing_field = null;
                            foreach ( $existing_record[$field_key] ?? [] as $v ){
                                if ( (int) $v["id"] === (int) $value["id"] ){
                                    $exists = true;
                                    $existing_field = $v;
                                }
                            }
                            if ( !$exists ){
                                return new WP_Error( __FUNCTION__, "A field for key $field_key with id " . $value["id"] . " was not found for this user on this post", [ 'status' => 400 ] );
                            }
                            if ( isset( $value["delete"] ) && $value["delete"] == true ) {
                                //delete user meta
                                $delete = $wpdb->query( $wpdb->prepare( "
                                DELETE FROM $wpdb->dt_post_user_meta
                                WHERE id = %s
                                    AND user_id = %s
                                    AND post_id = %s
                            ", $value["id"], $current_user_id, $post_id ) );
                                if ( !$delete ){
                                    return new WP_Error( __FUNCTION__, "Something wrong deleting post user meta on field: " . $field_key, [ 'status' => 500 ] );
                                }
                            } else {
                                //update user meta
                                $update = [];
                                if ( is_array( $value["value"] ) ){
                                    foreach ( $value["value"] as $val_key => $val_data ) {
                                        $existing_field["value"][$val_key] = $val_data;
                                    }
                                    $update["meta_value"] = serialize( $existing_field["value"] );
                                } else {
                                    $update["meta_value"] = $value["value"];
                                }
                                if ( isset( $value["date"] ) ){
                                    $update["date"] = $value["date"];
                                }
                                if ( isset( $value["category"] ) ){
                                    $update["category"] = $value["category"];
                                }
                                $update = $wpdb->update( $wpdb->dt_post_user_meta,
                                    $update,
                                    [
                                        "id"       => $value["id"],
                                        "user_id"  => $current_user_id,
                                        "post_id"  => $post_id,
                                        "meta_key" => $field_key,
                                    ]
                                );
                                if ( !$update ) {
                                    return new WP_Error( __FUNCTION__, "Something wrong on field: " . $field_key, [ 'status' => 500 ] );
                                }
                            }
                        } else {
                            //create user meta
                            $date = $value["date"] ?? null;
                            $create = $wpdb->insert( $wpdb->dt_post_user_meta,
                                [
                                    "user_id" => $current_user_id,
                                    "post_id" => $post_id,
                                    "meta_key" => $field_key,
                                    "meta_value" => is_array( $value["value"] ) ? serialize( $value["value"] ) : $value["value"],
                                    "date" => $date,
                                    "category" => $value["category"] ?? null
                                ]
                            );
                            if ( !$create ){
                                return new WP_Error( __FUNCTION__, "Something wrong on field: " . $field_key, [ 'status' => 500 ] );
                            }
                        }
                    } else {
                        return new WP_Error( __FUNCTION__, "Missing 'value' or 'id' key on field: " . $field_key, [ 'status' => 400 ] );
                    }
                }
            }
        }
    }


    /**
     * Helper function to create a unique metakey for contact channels.
     *
     * @param $channel_key
     * @param $field_type
     *
     * @return string
     */
    public static function create_channel_metakey( $channel_key, $field_type ) {
        return $field_type . '_' . $channel_key . '_' . self::unique_hash(); // build key
    }

    public static function unique_hash() {
        return substr( md5( rand( 10000, 100000 ) ), 0, 3 ); // create a unique 3 digit key
    }

    public static function add_post_contact_method( array $post_settings, int $post_id, string $key, string $value, array $field ) {
//        @todo permissions
        if ( strpos( $key, "new-" ) === 0 ) {
            $field_key = explode( '-', $key )[1];
            $type = str_replace( "contact_", "", $field_key );


            $new_meta_key = '';
            //check if this is a new field and is in the channel list
            if ( isset( $post_settings["fields"][ $field_key ] ) ) {
                $new_meta_key = self::create_channel_metakey( $type, "contact" );
            }
            update_post_meta( $post_id, $new_meta_key, $value );
            $details = [ "verified" => false ];
            foreach ( $field as $key => $value ){
                if ( $key != "value" && $key != "key" ){
                    $details[$key] = $value;
                }
            }
            update_post_meta( $post_id, $new_meta_key . "_details", $details );

            return $new_meta_key;
        } else {
            return new WP_Error( __FUNCTION__, "malformed key", [ 'status' => 400 ] );
        }
    }

    public static function update_post_contact_method( int $post_id, string $key, array $values ) {
//        @todo permissions
        if ( ( strpos( $key, "contact_" ) === 0 || strpos( $key, "address_" ) === 0 ) &&
             strpos( $key, "_details" ) === false
        ) {
            $old_value = get_post_meta( $post_id, $key, true );
            //check if it is different to avoid setting saving activity
            if ( isset( $values["value"] ) && $old_value != $values["value"] ){
                update_post_meta( $post_id, $key, $values["value"] );
            }
            unset( $values["value"] );
            unset( $values["key"] );

            $details_key = $key . "_details";
            $old_details = get_post_meta( $post_id, $details_key, true );
            $details = isset( $old_details ) ? $old_details : [];
            $new_value = false;
            foreach ( $values as $detail_key => $detail_value ) {
                if ( !isset( $details[$detail_key] ) || $details[$detail_key] !== $detail_value){
                    $new_value = true;
                }
                $details[ $detail_key ] = $detail_value;
            }
            if ($new_value){
                update_post_meta( $post_id, $details_key, $details );
            }
        }

        return $post_id;
    }

    public static function update_connections( array $post_settings, int $post_id, array $fields, $existing_contact = null ){
        //update connections (groups, locations, etc)
        foreach ( $post_settings["connection_types"] as $connection_type ){
            if ( isset( $fields[$connection_type] ) ){
                if ( !isset( $fields[$connection_type]["values"] )){
                    return new WP_Error( __FUNCTION__, "Missing values field on connection: " . $connection_type, [ 'status' => 500 ] );
                }
                $existing_connections = [];
                if ( isset( $existing_contact[$connection_type] ) ){
                    foreach ( $existing_contact[$connection_type] as $connection){
                        $existing_connections[] = isset( $connection->ID ) ? $connection->ID : $connection["ID"];
                    }
                }
                //check for new connections
                $connection_field = $fields[$connection_type];
                $new_connections = [];
                foreach ($connection_field["values"] as $connection_value ){
                    if ( isset( $connection_value["value"] ) && !is_numeric( $connection_value["value"] ) ){
                        if ( filter_var( $connection_value["value"], FILTER_VALIDATE_EMAIL ) ){
                            $user = get_user_by( "email", $connection_value["value"] );
                            if ( $user ){
                                $corresponding_contact = Disciple_Tools_Users::get_contact_for_user( $user->ID );
                                if ( $corresponding_contact ){
                                    $connection_value["value"] = $corresponding_contact;
                                }
                            }
                        } else {
                            $post_types = $post_settings["connection_types"];
                            $post_types[] = "contacts";
                            $post = self::get_post_by_title_cached( $connection_value["value"], OBJECT, $post_types, $connection_type );
                            if ( $post && !is_wp_error( $post ) ){
                                $connection_value["value"] = $post->ID;
                            }
                        }
                    }

                    if ( isset( $connection_value["value"] ) && is_numeric( $connection_value["value"] )){
                        if ( isset( $connection_value["delete"] ) && $connection_value["delete"] == true ){
                            if ( in_array( $connection_value["value"], $existing_connections )){
                                $potential_error = self::remove_connection_from_post( $post_settings["post_type"], $post_id, $connection_type, $connection_value["value"] );
                                if ( is_wp_error( $potential_error ) ) {
                                    return $potential_error;
                                }
                            }
                        } else if ( !empty( $connection_value["value"] )) {
                            $new_connections[] = $connection_value["value"];
                            if ( !in_array( $connection_value["value"], $existing_connections )){
                                $potential_error = self::add_connection_to_post( $post_settings["post_type"], $post_id, $connection_type, $connection_value["value"] );
                                $existing_connections[] = $connection_value["value"];
                                if ( is_wp_error( $potential_error ) ) {
                                    return $potential_error;
                                }
                                $fields["added_fields"][$connection_type] = $potential_error;
                            }
                        }
                    } else {
                        $value = isset( $connection_value["value"] ) ?: json_encode( $connection_value );
                        return new WP_Error( __FUNCTION__, "Missing 'value' key on : " . $connection_type . ", go: " . $value, [ 'status' => 500 ] );
                    }
                }
                //check for deleted connections
                if ( isset( $connection_field["force_values"] ) && $connection_field["force_values"] == true ){
                    foreach ($existing_connections as $connection_value ){
                        if ( !in_array( $connection_value, $new_connections )){
                            $potential_error = self::remove_connection_from_post( $post_settings["post_type"], $post_id, $connection_type, $connection_value );
                            if ( is_wp_error( $potential_error ) ) {
                                return $potential_error;
                            }
                        }
                    }
                }
            }
        }
        return $fields;
    }

    private static function add_connection_to_post( string $post_type, int $post_id, string $field_key, int $value ){
        $post_settings = DT_Posts::get_post_settings( $post_type );
        $connect = null;
        $field_setting = $post_settings["fields"][$field_key] ?? [];
        if ( !isset( $field_setting["p2p_key"], $field_setting["p2p_direction"] ) ) {
            return new WP_Error( __FUNCTION__, "Could not add connection. Field settings missing", [ 'status' => 400 ] );
        }
        if ( $field_setting["p2p_direction"] === "to" || $field_setting["p2p_direction"] === "any" ) {
            $connect = p2p_type( $field_setting["p2p_key"] )->connect(
                $value, $post_id,
                [ 'date' => current_time( 'mysql' ) ]
            );
        } elseif ( $field_setting["p2p_direction"] === "from" ){
            $connect = p2p_type( $field_setting["p2p_key"] )->connect(
                $post_id, $value,
                [ 'date' => current_time( 'mysql' ) ]
            );
        }
        if ( is_wp_error( $connect ) ) {
            return new WP_Error( __FUNCTION__, "Error adding connection on field: " . $field_key, [ "status" => 400 ] );
        }
        if ( $connect ) {
            do_action( "post_connection_added", $post_type, $post_id, $field_key, $value );
            $connection = get_post( $value );
            $connection->permalink = get_permalink( $value );
            return $connection;
        } else {
            return new WP_Error( __FUNCTION__, "Error adding connection on field: " . $field_key, [ "status" => 400 ] );
        }
    }

    private static function remove_connection_from_post( string $post_type, int $post_id, string $field_key, int $value ){
        $post_settings = DT_Posts::get_post_settings( $post_type );
        $field_setting = $post_settings["fields"][$field_key] ?? [];
        if ( !isset( $field_setting["p2p_key"], $field_setting["p2p_direction"] ) ) {
            return new WP_Error( __FUNCTION__, "Could not remove connection. Field settings missing", [ 'status' => 400 ] );
        }
        $disconnect = null;
        if ( $field_setting["p2p_direction"] === "to" || $field_setting["p2p_direction"] === "any" ){
            $disconnect = p2p_type( $field_setting["p2p_key"] )->disconnect( $value, $post_id );
            if ( $field_setting["p2p_direction"] === "any" && $disconnect === 0 ){
                $disconnect = p2p_type( $field_setting["p2p_key"] )->disconnect( $post_id, $value );
            }
        } elseif ( $field_setting["p2p_direction"] === "from" ){
            $disconnect = p2p_type( $field_setting["p2p_key"] )->disconnect( $post_id, $value );
        }
        if ( is_wp_error( $disconnect ) ) {
            return new WP_Error( __FUNCTION__, "Error removing connection on field: " . $field_key, [ "status" => 400 ] );
        }
        if ( $disconnect ){
            do_action( "post_connection_removed", $post_type, $post_id, $field_key, $value );
            return $disconnect;
        } else {
            return new WP_Error( __FUNCTION__, "Error removing connection on field: " . $field_key, [ "status" => 400 ] );
        }
    }

    /**
     * Used in in the method get_custom, this method mutates $fields to add
     * data about a particular contact in the required format. You might want
     * to use this instead of get_custom for performance reasons.
     *
     * @param string $post_type
     * @param int $post_id The ID number of the contact
     * @param array $fields This array will be mutated with the results
     * @param array $fields_to_return if not empty only add the fields that are specified (optional)
     * @param null $meta_fields a way to pass in the post's meta fields instead of getting in from the database (optional)
     * @param null $post_user_meta pass in the user post meta if already available (optional)
     *
     * @return void
     */
    public static function adjust_post_custom_fields( $post_type, int $post_id, array &$fields, array $fields_to_return = [], $meta_fields = null, $post_user_meta = null ) {
        if ( is_array( $post_type ) && isset( $post_type["post_type"] ) ){
            $post_type = $post_type["post_type"];
        }
        $field_settings = DT_Posts::get_post_field_settings( $post_type );
        if ( $meta_fields === null ){
            $meta_fields = get_post_custom( $post_id );
        }
        foreach ( $meta_fields as $key => $value ) {
            if ( empty( $fields_to_return ) || in_array( $key, $fields_to_return ) || strpos( $key, "contact_" ) === 0) {
                //if is contact details and is in a channel
                $key_without_ramdomizers = null;
                if ( strpos( $key, "contact_" ) === 0 ){
                    $exploded = explode( '_', $key );
                    $key_without_ramdomizers = $exploded[0] . '_' . $exploded[1];
                }

                if ( strpos( $key, "contact_" ) === 0 && isset( $field_settings[$key_without_ramdomizers]["type"] ) && $field_settings[$key_without_ramdomizers]["type"] === "communication_channel" ) {
                    if ( strpos( $key, "details" ) === false ) {
                        $type = explode( '_', $key )[1];
                        if ( empty( $fields_to_return ) || in_array( 'contact_' . $type, $fields_to_return ) ) {
                            $fields["contact_" . $type][] = self::format_post_contact_details( $field_settings, $meta_fields, $type, $key, $value[0] );
                        }
                    }
                } elseif ( strpos( $key, "address" ) === 0 ) {
                    if ( strpos( $key, "_details" ) === false ) {

                        $details = [];
                        if ( isset( $meta_fields[$key . '_details'][0] ) ) {
                            $details = maybe_unserialize( $meta_fields[$key . '_details'][0] );
                        }
                        $details["value"] = $value[0];
                        $details["key"] = $key;
                        if ( isset( $details["type"], $field_settings['contact_'.$details["type"]]["name"] ) ) {
                            $details["type_label"] = $field_settings['contact_' . $details["type"]]["name"];
                        }
                        $fields["address"][] = $details;
                    }
                } elseif ( isset( $field_settings[$key] ) && $field_settings[$key]["type"] == "key_select" ) {
                    if ( empty( $value[0] ) ) {
                        unset( $fields[$key] );
                        continue;
                    }
                    $value_options = $field_settings[$key]["default"][$value[0]] ?? $value[0];
                    if ( isset( $value_options["label"] ) ) {
                        $label = $value_options["label"];
                    } elseif ( is_string( $value_options ) ) {
                        $label = $value_options;
                    } else {
                        $label = $value[0];
                    }
//                        $label = $field_settings[ $key ]["default"][ $value[0] ]["label"] ?? $value[0];
                    $fields[$key] = [
                        "key" => $value[0],
                        "label" => $label
                    ];
                } elseif ( $key === "assigned_to" ) {
                    if ( $value ) {
                        $meta_array = explode( '-', $value[0] ); // Separate the type and id
                        $type = $meta_array[0]; // Build variables
                        if ( isset( $meta_array[1] ) ) {
                            $id = $meta_array[1];
                            if ( $type == 'user' && $id ) {
                                $user = get_user_by( 'id', $id );
                                $fields[$key] = [
                                    "id" => $id,
                                    "type" => $type,
                                    "display" => ( $user ? $user->display_name : "Nobody" ),
                                    "assigned-to" => $value[0]
                                ];
                            }
                        }
                    }
                } else if ( isset( $field_settings[$key] ) && $field_settings[$key]['type'] === 'multi_select' ) {
                    $fields[$key] = array_values( array_filter( array_map( 'trim', $value ), 'strlen' ) ); //remove empty values
                } else if ( isset( $field_settings[$key] ) && $field_settings[$key]['type'] === 'boolean' ) {
                    $fields[$key] = $value[0] === "1";
                } else if ( isset( $field_settings[$key] ) && $field_settings[$key]['type'] === 'array' ) {
                    $fields[$key] = maybe_unserialize( $value[0] );
                } else if ( isset( $field_settings[$key] ) && $field_settings[$key]['type'] === 'date' ) {
                    if ( isset( $value[0] ) && !empty( $value[0] ) ){
                        $fields[$key] = [
                            "timestamp" => is_numeric( $value[0] ) ? $value[0] : dt_format_date( $value[0], "U" ),
                            "formatted" => dt_format_date( $value[0] ),
                        ];
                    }
                } else if ( isset( $field_settings[$key] ) && $field_settings[$key]['type'] === 'location' ) {
                    $names = Disciple_Tools_Mapping_Queries::get_names_from_ids( $value );
                    $fields[$key] = [];
                    foreach ( $names as $id => $name ) {
                        $fields[$key][] = [
                            "id" => $id,
                            "label" => $name
                        ];
                    }
                } else if ( isset( $field_settings[$key] ) && $field_settings[$key]['type'] === 'location_meta' ) {
                    $fields[$key] = [];
                    foreach ( $value as $meta ) {
                        $location_grid_meta = Location_Grid_Meta::get_location_grid_meta_by_id( $meta );
                        if ( $location_grid_meta ) {
                            $fields[$key][] = $location_grid_meta;
                        }
                    }
                } else {
                    $fields[$key] = maybe_unserialize( $value[0] );
                }
            }
        }

        if ( class_exists( "DT_Mapbox_API" ) && DT_Mapbox_API::get_key() && isset( $fields['location_grid_meta'] ) ) {
            $ids = dt_get_keys_map( $fields['location_grid'] ?? [], 'id' );
            foreach ( $fields['location_grid_meta'] as $meta ) {
                foreach ( ( $fields['location_grid'] ?? [] ) as $index => $grid ){
                    if ( (int) $grid["id"] === (int) $meta["grid_id"] ){
                        $fields['location_grid'][$index]["matched_search"] = $meta["label"];
                    }
                }
                if ( !in_array( (int) $meta['grid_id'], $ids ) ){
                    $fields['location_grid'][] = [
                        'id' => (int) $meta['grid_id'],
                        'label' => $meta['label']
                    ];
                }
            }
        }

        //add user fields
        global $wpdb;
        $user_id = get_current_user_id();
        if ( $user_id ){
            if ( $post_user_meta === null ){
                $post_user_meta = $wpdb->get_results( $wpdb->prepare(
                    "
                        SELECT * FROM $wpdb->dt_post_user_meta
                        WHERE post_id = %s
                        AND user_id = %s
                    ", $post_id, $user_id
                ), ARRAY_A );
            }
            foreach ( $post_user_meta as $m ){
                if ( !isset( $fields[ $m["meta_key"] ] ) ) {
                    $fields[$m["meta_key"]] = [];
                }
                $fields[$m["meta_key"]][] = [
                    "id" => $m["id"],
                    "value" => maybe_unserialize( $m["meta_value"] ),
                    "date" => $m["date"],
                    "category" => $m["category"]
                ];
            }
        }

        $fields = apply_filters( "dt_adjust_post_custom_fields", $fields, $post_type );
    }


    /**
     * Find and format all p2p connection fields for a record
     *
     * @param $field_settings
     * @param $post_id
     * @param array $fields
     * @return array
     */
    public static function get_all_connection_fields( $field_settings, $post_id, array &$fields ){
        global $wpdb;
        $posts = $wpdb->get_results( $wpdb->prepare( "
            SELECT *
            FROM $wpdb->p2p
            WHERE p2p_to = %s
            OR p2p_from = %s
        ", esc_sql( $post_id ), esc_sql( $post_id ) ), ARRAY_A );
        foreach ( $field_settings as $field_key => $field_value ){
            if ( $field_value["type"] === "connection" && isset( $field_value["p2p_key"] ) ) {
                if ( !isset( $fields[$field_key] ) ) {
                    $fields[$field_key] = [];
                }
                foreach ( $posts as $post ){
                    if ( $post["p2p_type"] === $field_value["p2p_key"] ){
                        if ( ( $field_value["p2p_direction"] === "from" || $field_value["p2p_direction"] === "any" ) && $post["p2p_to"] != $post_id ) {
                            $fields[$field_key][] = self::filter_wp_post_object_fields( get_post( $post["p2p_to"] ) );
                        } else if ( ( $field_value["p2p_direction"] === "to" || $field_value["p2p_direction"] === "any" ) && $post["p2p_from"] != $post_id ) {
                            $fields[$field_key][] = self::filter_wp_post_object_fields( get_post( $post["p2p_from"] ) );
                        }
                    }
                }
            }
        }
        return $fields;
    }

    /**
     * Reduced the number of fields on a post to what is useful in D.T
     *
     * @param object $post
     * @return array
     */
    public static function filter_wp_post_object_fields( $post ){
        $filtered_post = [
            "ID" => $post->ID,
            "post_type" => $post->post_type,
            "post_date_gmt" => $post->post_date_gmt,
            "post_date" => $post->post_date,
            "post_title" => wp_specialchars_decode( $post->post_title ),
            "permalink" => get_permalink( $post->ID )
        ];
        if ( $post->post_type === "peoplegroups" ){
            $locale = get_user_locale();
            $translation = get_post_meta( $post->ID, $locale, true );
            $label  = ( $translation ? $translation : $post->post_title );
            $filtered_post["label"] = $label;
        }

        return $filtered_post;
    }

    public static function format_post_contact_details( $post_settings, $meta_fields, $type, $key, $value ) {
        $details = [];
        if ( isset( $meta_fields[ $key . '_details' ][0] ) ) {
            $details = maybe_unserialize( $meta_fields[ $key . '_details' ][0] );

            if ( !is_array( $details ) ) {
                $details = [];
            }
        }
        $details["value"] = $value;
        $details["key"] = $key;
        return $details;
    }

}

/**
 * @return Disciple_Tools_Metabox_Address
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
     * Field: Contact Fields
     * @param $post_id
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
