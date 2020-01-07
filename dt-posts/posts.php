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
            "locations" => [ "name" => _x( "Locations", 'label name', 'disciple_tools' ) ],
            "groups" => [ "name" => _x( "Groups", 'label name', 'disciple_tools' ) ],
            "people_groups" => [ "name" => _x( "People Groups", 'label name', 'disciple_tools' ) ],
            "baptized_by" => [ "name" => _x( "Baptized By", 'label name', 'disciple_tools' ) ],
            "baptized" => [ "name" => _x( "Baptized", 'label name', 'disciple_tools' ) ],
            "coached_by" => [ "name" => _x( "Coached By", 'label name', 'disciple_tools' ) ],
            "coaching" => [ "name" => _x( "Coaching", 'label name', 'disciple_tools' ) ],
            "subassigned" => [ "name" => _x( "Sub Assigned", 'label name', 'disciple_tools' ) ],
            "leaders" => [ "name" => _x( "Leaders", 'label name', 'disciple_tools' ) ],
            "coaches" => [ "name" => _x( "Coaches/Church Planters", 'label name', 'disciple_tools' ) ],
            "parent_groups" => [ "name" => _x( "Parent Groups", 'label name', 'disciple_tools' ) ],
            "child_groups" => [ "name" => _x( "Child Groups", 'label name', 'disciple_tools' ) ],
            "peer_groups" => [ "name" => _x( "Peer Groups", 'label name', 'disciple_tools' ) ],
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
        if ( $post_type !== get_post_type( $post_id ) ){
            return false;
        }
        //check if the user has access to all posts
        if ( current_user_can( 'view_any_' . $post_type ) ) {
            return true;
        }
        //check if the user has access to all posts of a specific source
        if ( current_user_can( 'access_specific_sources' ) ){
            $sources = get_user_option( 'allowed_sources', get_current_user_id() ) ?? [];
            $post_sources = get_post_meta( $post_id, 'sources' );
            foreach ( $post_sources as $s ){
                if ( in_array( $s, $sources ) ){
                    return true;
                }
            }
        }
        //check if a user is assigned to the post or if the post is shared with the user
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
        //return false if the user does not have access to the post
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
        if ( $post_type !== get_post_type( $post_id ) ){
            return false;
        }
        global $wpdb;
        if ( current_user_can( 'update_any_' . $post_type ) ) {
            return true;
        }
        if ( current_user_can( 'access_specific_sources' ) ){
            $sources = get_user_option( 'allowed_sources', get_current_user_id() ) ?? [];
            $post_sources = get_post_meta( $post_id, 'sources' );
            foreach ( $post_sources as $s ){
                if ( in_array( $s, $sources ) ){
                    return true;
                }
            }
        }
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

    public static function format_connection_message( $p2p_id, $activity, $action = 'connected to' ){
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
                    if ( !empty( $activity->meta_value ) && !empty( $activity->old_value ) ){
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
                    $message = $fields[$activity->meta_key]["name"] . ": " . dt_format_date( $activity->meta_value );
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
                            $message = sprintf( _x( 'Added %1$s: %2$s', 'Added Facebook: facebook.com/123', 'disciple_tools' ), $channel["label"], $activity->meta_value );
                        } else if ( $activity->meta_value != "value_deleted" ){
                            $message = sprintf( _x( 'Updated %1$s from %2$s to %3$s', 'Update Facebook form facebook.com/123 to facebook.com/mark', 'disciple_tools' ), $channel["label"], $activity->old_value, $activity->meta_value );
                        } else {
                            $message = sprintf( _x( 'Deleted %1$s: %2$s', 'Deleted Facebook: facebook.com/123', 'disciple_tools' ), $channel["label"], $activity->old_value );
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

        return $message;
    }


    /**
     * @param string $post_type
     *
     * @param int $most_recent
     *
     * @return array|WP_Error|WP_Query
     */
    public static function get_viewable( string $post_type, int $most_recent = 0 ) {
        if ( !self::can_access( $post_type ) ) {
            return new WP_Error( __FUNCTION__, sprintf( _x( "You do not have access to these %s", 'message', 'disciple_tools' ), $post_type ), [ 'status' => 403 ] );
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

        $post_settings = apply_filters( "dt_get_post_type_settings", [], $post_type );
        $post_fields = $post_settings["fields"];

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
        $limit = 100;
        if ( isset( $query["limit"] )){
            $limit = esc_sql( sanitize_text_field( $query["limit"] ) );
            unset( $query["limit"] );
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
        $location_sql = "";

        $meta_query = "";
        $includes_query = "";
        $share_joins = "";
        $access_joins = "";
        $access_query = "";
        if ( isset( $query["assigned_to"] ) ){
            if ( !is_array( $query["assigned_to"] ) ){
                return new WP_Error( __FUNCTION__, "Assigned_to must be an array. found: " . esc_html( $query["assigned_to"] ), [ 'status' => 400 ] );
            }
        }
        if ( !isset( $query["assigned_to"] ) || in_array( "all", $query["assigned_to"] ) ){
            $query["assigned_to"] = [ "all" ];
            if ( !self::can_view_all( $post_type ) && $check_permissions ){
                $query["assigned_to"] = [ "me" ];
                if ( !in_array( "shared", $include )){
                    $include[] = "shared";
                }
                if ( current_user_can( 'access_specific_sources' ) ){
                    $include[] = "allowed_sources";
                }
            };
        }
        foreach ( $include as $i ){
            if ( $i === "shared" ){
                $share_joins = "LEFT JOIN $wpdb->dt_share AS shares ON ( shares.post_id = $wpdb->posts.ID ) ";
                $access_query = "shares.user_id = $current_user->ID ";
            }
            if ( $i === "allowed_sources" ){
                $allowed_sources = get_user_option( 'allowed_sources', get_current_user_id() ) ?? [];
                if ( !empty( $allowed_sources ) ){
                    $sources_sql = dt_array_to_sql( $allowed_sources );
                    $access_joins .= "LEFT JOIN $wpdb->postmeta AS source_access ON ( $wpdb->posts.ID = source_access.post_id AND source_access.meta_key = 'sources' ) ";
                    $access_query .= ( !empty( $access_query ) ? "OR" : "" ) ." ( source_access.meta_key = 'sources' AND source_access.meta_value IN ( $sources_sql ) )";

                }
            }
        }
        if ( in_array( "shared", $query["assigned_to"] ) ){
            $share_joins = "LEFT JOIN $wpdb->dt_share AS shares ON ( shares.post_id = $wpdb->posts.ID ) ";
            $access_query = ( !empty( $access_query ) ? "OR" : "" ) ." shares.user_id = $current_user->ID ";
            if ( !in_array( "me", $query["assigned_to"] ) && !in_array( "all", $query["assigned_to"] ) ){
                $access_joins .= "INNER JOIN $wpdb->postmeta AS assigned_to ON ( $wpdb->posts.ID = assigned_to.post_id ) ";
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
            if ( !in_array( $query_key, $post_settings["connection_types"] ) && strpos( $query_key, "contact_" ) !== 0 && $query_key !== "location_grid" ){
                if ( $query_key == "assigned_to" ){
                    foreach ( $query_value as $assigned_to ){
                        $connector = "OR";
                        if ( $assigned_to == "me" ){
                            $assigned_to = "user-" . $current_user->ID;
                        } else if ( $assigned_to != "all" && $assigned_to != "shared" ) {
                            if ( self::can_view_all( $post_type ) || !$check_permissions ){
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
                        if ( !empty( $access_query ) ){
                            $access_query .= $connector;
                        }
                        $access_query .= ( $connector == "AND" ? " ( " : "" ) . " ( $wpdb->posts.ID IN ( SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'assigned_to' AND meta_value = '" . esc_sql( $assigned_to ) . "' ) ) " . ( $connector == "AND" ? " ) " : "" );

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
                            if ( $field_type === "date" ){
                                $meta_field_sql .= " ( " . esc_sql( $query_key ) . ".meta_key = '" . esc_sql( $query_key ) ."' AND " . esc_sql( $query_key ) . ".meta_value " . $equality . " " . esc_sql( $value ) . " ) ";
                            } else {
                                $meta_field_sql .= " ( " . esc_sql( $query_key ) . ".meta_key = '" . esc_sql( $query_key ) ."' AND " . esc_sql( $query_key ) . ".meta_value " . $equality . " '" . esc_sql( $value ) . "' ) ";
                            }
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
            $meta_query .= "AND ( ( $wpdb->posts.post_title LIKE '%%" . esc_sql( $search ) . "%%' )
                OR ( search.meta_key LIKE 'contact_%' AND INSTR( search.meta_value, '" . esc_sql( $search ) . "' ) > 0 )
                OR ( search.meta_key LIKE 'contact_phone_%' AND REPLACE( '" . esc_sql( $search ) . "', ' ', '') = REPLACE( search.meta_value, ' ', '') )";
            foreach ( $other_search_fields as $field ){
                $meta_query .= " OR ( search.meta_key LIKE '" . esc_sql( $field ) . "' AND search.meta_value LIKE '%%" . esc_sql( $search ) . "%%'   ) ";
            }
            $meta_query .= " ) ";

        }

        foreach ( $query as $query_key => $query_value ) {
            if ( $query_key === "location_grid" ) {
                $location_grid_ids = dt_array_to_sql( $query_value );
                $location_sql .= "
                    AND (
                        location_grid_counter.admin0_grid_id IN (" . $location_grid_ids .")
                        OR location_grid_counter.admin1_grid_id IN (" . $location_grid_ids .")
                        OR location_grid_counter.admin2_grid_id IN (" . $location_grid_ids .")
                        OR location_grid_counter.admin3_grid_id IN (" . $location_grid_ids .")
                        OR location_grid_counter.grid_id IN (" . $location_grid_ids .")
                    )";
            }
            if ( isset( $post_fields[$query_key]["type"] ) && $post_fields[$query_key]["type"] === "connection" ) {

                $connection_ids = "";
                foreach ( $query_value as $connection ) {
                    if ( $connection === "me" ){
                        $contact_id = Disciple_Tools_Users::get_contact_for_user( get_current_user_id() );
                        $l = get_post( $contact_id );
                    } else {
                        $l = get_post( $connection );
                    }
                    if ( $l ){
                        $connection_ids .= empty( $connection_ids ) ? $l->ID : ( ",".$l->ID );
                    }
                }
                if ( !empty( $connection_ids ) ){
                    if ( $query_key === "subassigned" ) {
                        if ( !empty( $access_query ) && in_array( "subassigned", $combine ) ){
                            $access_query .= "OR ( $wpdb->posts.ID IN ( SELECT p2p_to FROM $wpdb->p2p WHERE p2p_from IN  (" . esc_sql( $connection_ids ) .")  AND p2p_type = 'contacts_to_subassigned' ) )";
                        } else {
                            $connections_sql_from .= "AND ( $wpdb->posts.ID IN ( SELECT p2p_to FROM $wpdb->p2p WHERE p2p_from IN  (" . esc_sql( $connection_ids ) .")  AND p2p_type = 'contacts_to_subassigned' ) )";
                        }
                    } else {
                        if ( $post_fields[$query_key]["p2p_direction"] === "to" ){
                            $meta_query .= " AND ( $wpdb->posts.ID IN (
                                SELECT p2p_to from $wpdb->p2p WHERE p2p_type = '" . esc_html( $post_fields[$query_key]["p2p_key"] ) . "' AND p2p_from IN (" . esc_sql( $connection_ids ) .")
                            ) ) ";
                        } else {
                            $meta_query .= " AND ( $wpdb->posts.ID IN (
                                SELECT p2p_from from $wpdb->p2p WHERE p2p_type = '" . esc_html( $post_fields[$query_key]["p2p_key"] ) . "' AND p2p_to IN (" . esc_sql( $connection_ids ) .")
                            ) ) ";
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
        if ( !empty( $location_sql )){
            $inner_joins .= " INNER JOIN (
                    SELECT
                        g.admin0_grid_id,
                        g.admin1_grid_id,
                        g.admin2_grid_id,
                        g.admin3_grid_id,
                        g.grid_id,
                        g.level,
                        p.post_id
                    FROM $wpdb->postmeta as p
                        LEFT JOIN $wpdb->dt_location_grid as g ON g.grid_id=p.meta_value
                    WHERE p.meta_key = 'location_grid'
            ) as location_grid_counter ON ( location_grid_counter.post_id = $wpdb->posts.ID )";
        }

        $access_query = $access_query ? ( "AND ( " . $access_query . " ) " ) : "";

        $sort_sql = "$wpdb->posts.post_date asc";
        $sort_join = "";
        $post_type_check = "";
        if ( $post_type == "contacts" ){
            $post_type_check = "AND $wpdb->posts.ID NOT IN (
                SELECT post_id FROM $wpdb->postmeta
                WHERE meta_key = 'type' AND meta_value = 'user'
                GROUP BY post_id
            )";
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
            $sort_sql = "ISNULL(p2p_post.post_title), p2p_post.post_title $sort_dir";
        } elseif ( $sort === "post_date" ){
            $sort_sql = "$wpdb->posts.post_date  " . $sort_dir;
        } elseif ( $sort === "location_grid" ){
            $sort_join = "LEFT JOIN $wpdb->postmeta as sort ON ( $wpdb->posts.ID = sort.post_id AND sort.meta_key = '$sort')";
            $sort_sql = "sort.meta_value $sort_dir";
        }

        $group_by_sql = "";
        if ( strpos( $sort_sql, 'sort.meta_value' ) != false ){
            $group_by_sql = ", sort.meta_value";
        }

        // phpcs:disable
        // WordPress.WP.PreparedSQL.NotPrepared
        $prepared_sql = $wpdb->prepare("
            SELECT SQL_CALC_FOUND_ROWS $wpdb->posts.ID, $wpdb->posts.post_title, $wpdb->posts.post_type FROM $wpdb->posts
            " . $inner_joins . " " . $share_joins . " " . $access_joins . " " . $sort_join . "
            WHERE 1=1
            " . $post_type_check . " " . $connections_sql_to . " ". $connections_sql_from . " " . $location_sql . " " . $meta_query . " " . $includes_query . " " . $access_query . "
            AND $wpdb->posts.post_type = %s
            AND ($wpdb->posts.post_status = 'publish' OR $wpdb->posts.post_status = 'private')
            GROUP BY $wpdb->posts.ID " . $group_by_sql . "
            ORDER BY " . $sort_sql . "
            LIMIT %d, " . $limit . "
            ",
            esc_sql( $post_type ),
            $offset
        );
        $posts = $wpdb->get_results( $prepared_sql, OBJECT );

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

    public static function is_post_key_contact_method_or_connection( $post_settings, $key ) {
        $channel_keys = [];
        foreach ( $post_settings["channels"] as $channel_key => $channel_value ) {
            $channel_keys[] = "contact_" . $channel_key;
        }
        return in_array( $key, $post_settings["connection_types"] ) || in_array( $key, $channel_keys );
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
            if ( isset( $field_settings[$field_key] ) && ( $field_settings[$field_key]["type"] === "multi_select" || $field_settings[$field_key]["type"] === "location" ) ){
                if ( !isset( $field["values"] )){
                    return new WP_Error( __FUNCTION__, "missing values field on: " . $field_key );
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
                        return new WP_Error( __FUNCTION__, "Something wrong on field: " . $field_key );
                    }
                }
            }
        }
        return $fields;
    }

    public static function update_post_contact_methods( array $post_settings, int $post_id, array $fields, array $existing_contact = null ){
        // update contact details (phone, facebook, etc)
        foreach ( array_keys( $post_settings["channels"] ) as $channel_key ){
            $details_key = "contact_" . $channel_key;
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
                        return new WP_Error( __FUNCTION__, "missing key on: " . $details_key );
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
                    $field["key"] = "new-".$channel_key;
                    //create field
                    $potential_error = self::add_post_contact_method( $post_settings, $post_id, $field["key"], $field["value"], $field );

                } else {
                    return new WP_Error( __FUNCTION__, "Is not an array or missing value on: " . $details_key );
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
                    return new WP_Error( __FUNCTION__, "missing values field on: " . $field_key );
                }

                foreach ( $field["values"] as $value ){
                    if ( isset( $value["value"] ) || ( !empty( $value["delete"] && !empty( $value['id'] ) ) ) ){
                        $current_user_id = get_current_user_id();
                        if ( !$current_user_id ){
                            return new WP_Error( __FUNCTION__, "Cannot update post_user_meta fields for no user." );
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
                                return new WP_Error( __FUNCTION__, "A field for key $field_key with id " . $value["id"] . " was not found for this user on this post" );
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
                                    return new WP_Error( __FUNCTION__, "Something wrong deleting post user meta on field: " . $field_key );
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
                                    return new WP_Error( __FUNCTION__, "Something wrong on field: " . $field_key );
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
                                return new WP_Error( __FUNCTION__, "Something wrong on field: " . $field_key );
                            }
                        }
                    } else {
                        return new WP_Error( __FUNCTION__, "Missing 'value' or 'id' key on field: " . $field_key );
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
            $type = explode( '-', $key )[1];

            $new_meta_key = '';
            //check if this is a new field and is in the channel list
            if ( isset( $post_settings["channels"][ $type ] ) ) {
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
                        return new WP_Error( __FUNCTION__, "Cannot determine target on connection: " . $connection_type, [ 'status' => 500 ] );
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

    public static function add_connection_to_post( string $post_type, int $post_id, string $field_key, int $value ){
        if ( !self::can_update( $post_type, $post_id ) ) {
            return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
        }
        $post_settings = apply_filters( "dt_get_post_type_settings", [], $post_type );
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

    public static function remove_connection_from_post( string $post_type, int $post_id, string $field_key, int $value ){
        if ( !self::can_update( $post_type, $post_id ) ) {
            return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
        }
        $post_settings = apply_filters( "dt_get_post_type_settings", [], $post_type );
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
     * @param array $post_settings This is what get_custom_fields_settings() returns
     * @param int $post_id The ID number of the contact
     * @param array $fields This array will be mutated with the results
     *
     * @return void
     */
    public static function adjust_post_custom_fields( $post_settings, int $post_id, array &$fields ) {
        $meta_fields = get_post_custom( $post_id );
        $field_settings = $post_settings["fields"];
        foreach ( $meta_fields as $key => $value ) {
            //if is contact details and is in a channel
            if ( strpos( $key, "contact_" ) === 0 && isset( $post_settings["channels"][ explode( '_', $key )[1] ] ) ) {
                if ( strpos( $key, "details" ) === false ) {
                    $type = explode( '_', $key )[1];
                    $fields[ "contact_" . $type ][] = self::format_post_contact_details( $post_settings, $meta_fields, $type, $key, $value[0] );
                }
            } elseif ( strpos( $key, "address" ) === 0 ) {
                if ( strpos( $key, "_details" ) === false ) {

                    $details = [];
                    if ( isset( $meta_fields[ $key . '_details' ][0] ) ) {
                        $details = maybe_unserialize( $meta_fields[ $key . '_details' ][0] );
                    }
                    $details["value"] = $value[0];
                    $details["key"] = $key;
                    if ( isset( $details["type"] ) ) {
                        $details["type_label"] = $post_settings["channels"][ $details["type"] ]["label"];
                    }
                    $fields["address"][] = $details;
                }
            } elseif ( isset( $field_settings[ $key ] ) && $field_settings[ $key ]["type"] == "key_select" && !empty( $value[0] )) {
                if ( empty( $value[0] ) ){
                    unset( $fields[$key] );
                    continue;
                }
                $value_options = $field_settings[ $key ]["default"][ $value[0] ] ?? $value[0];
                if ( isset( $value_options["label"] ) ){
                    $label = $value_options["label"];
                } elseif ( is_string( $value_options ) ) {
                    $label = $value_options;
                } else {
                    $label = $value[0];
                }
//                        $label = $field_settings[ $key ]["default"][ $value[0] ]["label"] ?? $value[0];
                $fields[ $key ] = [
                    "key" => $value[0],
                    "label" => $label
                ];
            } elseif ( $key === "assigned_to" ) {
                if ( $value ) {
                    $meta_array = explode( '-', $value[0] ); // Separate the type and id
                    $type = $meta_array[0]; // Build variables
                    if ( isset( $meta_array[1] ) ) {
                        $id = $meta_array[1];
                        if ( $type == 'user' && $id) {
                            $user = get_user_by( 'id', $id );
                            $fields[ $key ] = [
                                "id" => $id,
                                "type" => $type,
                                "display" => ( $user ? $user->display_name : "Nobody" ) ,
                                "assigned-to" => $value[0]
                            ];
                        }
                    }
                }
            } else if ( isset( $field_settings[ $key ] ) && $field_settings[ $key ]['type'] === 'multi_select' ){
                $fields[ $key ] = $value;
            } else if ( isset( $field_settings[ $key ] ) && $field_settings[ $key ]['type'] === 'boolean' ){
                $fields[ $key ] = $value[0] === "1";
            } else if ( isset( $field_settings[ $key ] ) && $field_settings[ $key ]['type'] === 'array' ){
                $fields[ $key ] = maybe_unserialize( $value[0] );
            } else if ( isset( $field_settings[ $key ] ) && $field_settings[ $key ]['type'] === 'date' ){
                $fields[ $key ] = [
                    "timestamp" => $value[0],
                    "formatted" => dt_format_date( $value[0] ),
                ];
            } else if ( isset( $field_settings[ $key ] ) && $field_settings[ $key ]['type'] === 'location' ){
                $names = Disciple_Tools_Mapping_Queries::get_names_from_ids( $value );
                $fields[ $key ] = [];
                foreach ( $names as $id => $name ){
                    $fields[ $key ][] = [
                        "id" => $id,
                        "label" => $name
                    ];
                }
            } else {
                $fields[ $key ] = $value[0];
            }
        }

        //add user fields
        global $wpdb;
        $user_id = get_current_user_id();
        if ( $user_id ){
            $post_user_meta = $wpdb->get_results( $wpdb->prepare(
                "
                    SELECT * FROM $wpdb->dt_post_user_meta
                    WHERE post_id = %s
                    AND user_id = %s
                ", $post_id, $user_id
            ), ARRAY_A );
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

        $fields = apply_filters( "dt_adjust_post_custom_fields", $fields, $post_settings["post_type"] );
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
            "post_title" => $post->post_title,
            "permalink" => get_permalink( $post->ID )
        ];
        if ( $post->post_type === "peoplegroups" ){
            $locale = get_locale();
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
        if ( isset( $details["type"] ) ) {
            $details["type_label"] = $post_settings["channels"][ $type ]["types"][ $details["type"] ]["label"];
        }
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
     * @param $post_type
     *
     * @return array
     */
    public function get_address_type_list( $post_type ) {

        switch ( $post_type ) {
            case 'contacts':
                $addresses = [
                    "home"  => [ "label" => _x( 'Home', 'field label', 'disciple_tools' ) ],
                    "work"  => [ "label" => _x( 'Work', 'field label', 'disciple_tools' ) ],
                    "other" => [ "label" => _x( 'Other', 'field label', 'disciple_tools' ) ],
                ];

                return $addresses;
                break;
            case 'groups':
                $addresses = [
                    "main"      => [ "label" => _x( 'Main', 'field label', 'disciple_tools' ) ],
                    "alternate" => [ "label" => _x( 'Alternate', 'field label', 'disciple_tools' ) ],
                ];

                return $addresses;
                break;
            case 'locations':
                $addresses = [
                    "main" => [ "label" => _x( 'Main', 'field label', 'disciple_tools' ) ],
                ];

                return $addresses;
                break;
            default:
                return [];
                break;
        }
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
