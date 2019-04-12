<?php
/**
 * Contains create, update and delete functions for groups, wrapping access to
 * the database
 *
 * @package  Disciple_Tools
 * @category Plugin
 * @author   Chasm.Solutions & Kingdom.Training
 * @since    0.1.0
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

/**
 * Class Disciple_Tools_Contacts
 * Functions for creating, finding, updating or deleting contacts
 */
class Disciple_Tools_Groups extends Disciple_Tools_Posts
{

    public static $address_types;
    public static $group_fields;
    public static $group_connection_types;
    public static $channel_list;

    /**
     * Disciple_Tools_Groups constructor.
     */
    public function __construct() {
        add_action(
            'init',
            function() {
                self::$address_types = dt_address_metabox()->get_address_type_list( "groups" );
                self::$group_fields = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings();
                self::$group_connection_types = [
                    "members",
                    "parent_groups",
                    "child_groups",
                    "peer_groups",
                    "locations",
                    "people_groups",
                    "leaders",
                    "coaches"
                ];
                self::$channel_list = [
                    "address"
                ];
            }
        );
        add_action( 'group_member_count', [ $this, 'update_group_member_count' ], 10, 2 );
        parent::__construct();
    }

    /**
     * @param  $search_string
     *
     * @return array|\WP_Query
     */
    public static function get_groups_compact( string $search_string ) {
        return self::get_viewable_compact( 'groups', $search_string );
    }

    /**
     * @param int $most_recent
     *
     * @return array|\WP_Query
     */
    public static function get_viewable_groups( int $most_recent = 0 ) {
        return self::get_viewable( 'groups', $most_recent );
    }


    public static function search_viewable_groups( array $query, bool $check_permissions = true ){
        $viewable = self::search_viewable_post( "groups", $query, $check_permissions );
        if ( is_wp_error( $viewable ) ){
            return $viewable;
        }
        return [
            "groups" => $viewable["posts"],
            "total" => $viewable["total"]
        ];
    }


    /**
     * @param int $group_id
     * @param bool $check_permissions
     * @param bool $load_cache
     *
     * @return array|\WP_Error
     */
    public static function get_group( int $group_id, bool $check_permissions = true, $load_cache = false ) {
        if ( $check_permissions && !self::can_view( 'groups', $group_id ) ) {
            return new WP_Error( __FUNCTION__, "No permissions to read group", [ 'status' => 403 ] );
        }
        $cached = wp_cache_get( "group_" . $group_id );
        if ( $cached && $load_cache ){
            return $cached;
        }

        $group = get_post( $group_id );
        if ( $group ) {
            $fields = [];

            $connection_types = [
                [ "groups_to_locations", "locations", "any" ],
                [ "groups_to_peoplegroups", "people_groups", "any" ],
                [ "contacts_to_groups", "members", "to" ],
                [ "groups_to_leaders", "leaders", "any" ],
                [ "groups_to_coaches", "coaches", "any" ],
                [ "groups_to_groups", "child_groups", "to" ],
                [ "groups_to_groups", "parent_groups", "from" ],
                [ "groups_to_peers", "peer_groups", "any" ],
            ];

            foreach ( $connection_types as $type ){
                $args = [
                    'connected_type'   => $type[0],
                    'connected_direction' => $type[2],
                    'connected_items'  => $group,
                    'nopaging'         => true,
                    'suppress_filters' => false,
                ];
                $connections = get_posts( $args );
                foreach ( $connections as $c ){
                    $c->permalink = get_permalink( $c->ID );
                }
                $fields[$type[1]] = $connections;
            }

            $meta_fields = get_post_custom( $group_id );
            foreach ( $meta_fields as $key => $value ) {
                if ( strpos( $key, "address_" ) !== false) {
                    if ( strpos( $key, "_details" ) === false ) {

                        $details = [];
                        if ( isset( $meta_fields[ $key . '_details' ][0] ) ) {
                            $details = maybe_unserialize( $meta_fields[ $key . '_details' ][0] );
                        }
                        $details["value"] = $value[0];
                        $details["key"] = $key;
                        if ( isset( $details["type"] ) ) {
                            $details["type_label"] = self::$address_types[ $details["type"] ]["label"];
                        }
                        $fields["contact_address"][] = $details;
                    }
                } elseif ( isset( self::$group_fields[ $key ] ) && self::$group_fields[ $key ]["type"] == "key_select" && !empty( $value[0] )) {
                    $value_options = self::$group_fields[ $key ]["default"][ $value[0] ] ?? $value[0];
                    if ( isset( $value_options["label"] ) ){
                        $label = $value_options["label"];
                    } elseif ( is_string( $value_options ) ) {
                        $label = $value_options;
                    } else {
                        $label = $value[0];
                    }

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
                            if ( $type == 'user' ) {
                                $user = get_user_by( 'id', $id );
                                if ( $user ) {
                                    $fields[ $key ] = [
                                        "id"          => (int) $id,
                                        "type"        => $type,
                                        "display"     => $user->display_name,
                                        "assigned-to" => $value[0],
                                    ];
                                }
                            }
                        }
                    }
                } else if ( isset( self::$group_fields[ $key ] ) && self::$group_fields[ $key ]['type'] === 'multi_select' ){
                    $fields[ $key ] = $value;
                } else if ( isset( self::$group_fields[ $key ] ) && self::$group_fields[ $key ]['type'] === 'boolean' ) {
                    $fields[ $key ] = $value[0] === "1";
                } else if ( isset( self::$group_fields[ $key ] ) && self::$group_fields[ $key ]['type'] === 'array' ){
                    $fields[ $key ] = maybe_unserialize( $value[0] );
                } else if ( isset( self::$group_fields[ $key ] ) && self::$group_fields[ $key ]['type'] === 'date' ){
                    $fields[ $key ] = [
                        "timestamp" => $value[0],
                        "formatted" => dt_format_date( $value[0] ),
                    ];
                } else {
                    $fields[ $key ] = $value[0];
                }
            }
            $fields["ID"] = $group->ID;
            $fields["name"] = $group->post_title;
            $fields["title"] = $group->post_title;

            $group = apply_filters( 'dt_groups_fields_post_filter', $fields );
            wp_cache_set( "group_" . $group_id, $group );
            return $group;
        } else {
            return new WP_Error( __FUNCTION__, "No group found with ID", [ 'contact_id' => $group_id ] );
        }
    }

    private static function is_key_contact_method_or_connection( $key ) {
        $channel_keys = [];
        foreach ( self::$channel_list as $channel_key ) {
            $channel_keys[] = "contact_" . $channel_key;
        }
        return in_array( $key, self::$group_connection_types ) || in_array( $key, $channel_keys );
    }

    /**
     * Make sure there are no extra or misspelled fields
     * Make sure the field values are the correct format
     *
     * @param array $fields , the group meta fields
     * @param int|null $post_id , the id of the group
     * @param array $allowed_fields
     *
     * @return array
     * @access private
     * @since  0.1.0
     */
    private static function check_for_invalid_fields( array $fields, int $post_id = null, $allowed_fields = [] ) {
        $bad_fields = [];
        $group_fields = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings( isset( $post_id ), $post_id );
        $group_fields['title'] = "";
        foreach ( $fields as $field => $value ) {
            if ( !isset( $group_fields[ $field ] ) && !self::is_key_contact_method_or_connection( $field ) && !in_array( $field, $allowed_fields ) ) {
                $bad_fields[] = $field;
            }
        }
        return $bad_fields;
    }

    private static function parse_multi_select_fields( $contact_id, $fields, $existing_contact = null ){
        foreach ( $fields as $field_key => $field ){
            if ( isset( self::$group_fields[$field_key] ) && self::$group_fields[$field_key]["type"] === "multi_select" ){
                if ( !isset( $field["values"] )){
                    return new WP_Error( __FUNCTION__, "missing values field on:" . $field_key );
                }
                if ( isset( $field["force_values"] ) && $field["force_values"] === true ){
                    delete_post_meta( $contact_id, $field_key );
                }
                foreach ( $field["values"] as $value ){
                    if ( isset( $value["value"] )){
                        if ( isset( $value["delete"] ) && $value["delete"] == true ){
                            delete_post_meta( $contact_id, $field_key, $value["value"] );
                        } else {
                            $existing_array = isset( $existing_contact[ $field_key ] ) ? $existing_contact[ $field_key ] : [];
                            if ( !in_array( $value["value"], $existing_array ) ){
                                add_post_meta( $contact_id, $field_key, $value["value"] );
                            }
                        }
                    } else {
                        return new WP_Error( __FUNCTION__, "Something went wrong on field:" . $field_key );
                    }
                }
            }
        }
        return $fields;
    }


    private static function parse_contact_methods( $group_id, $fields, $existing_group = null ){
        // update group details (phone, facebook, etc)
        foreach ( self::$channel_list as $channel_key ){
            $details_key = "contact_" . $channel_key;
            $values = [];
            if ( isset( $fields[$details_key] ) && isset( $fields[$details_key]["values"] ) ){
                $values = $fields[$details_key]["values"];
            } else if ( isset( $fields[$details_key] ) && is_array( $fields[$details_key] ) ) {
                $values = $fields[$details_key];
            }
            if ( $existing_group && isset( $fields[$details_key] ) &&
                 isset( $fields[$details_key]["force_values"] ) &&
                 $fields[$details_key]["force_values"] === true ){
                foreach ( $existing_group[$details_key] as $contact_value ){
                    $potential_error = self::delete_group_field( $group_id, $contact_value["key"], false );
                    if ( is_wp_error( $potential_error ) ){
                        return $potential_error;
                    }
                }
            }
            foreach ( $values as $field ){
                if ( isset( $field["delete"] ) && $field["delete"] == true){
                    if ( !isset( $field["key"] )){
                        return new WP_Error( __FUNCTION__, "missing key on: " . $details_key );
                    }
                    //delete field
                    $potential_error = self::delete_group_field( $group_id, $field["key"] );
                } else if ( isset( $field["key"] ) ){
                    //update field
                    $potential_error = self::update_contact_method( $group_id, $field["key"], $field, false );
                } else if ( isset( $field["value"] ) ) {
                    $field["key"] = "new-".$channel_key;
                    //create field
                    $potential_error = self::add_item_to_field( $group_id, $field["key"], $field["value"], false );

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

    private static function parse_connections( $group_id, $fields, $existing_group){
        //update connections (groups, locations, etc)
        foreach ( self::$group_connection_types as $connection_type ){
            if ( isset( $fields[$connection_type] ) ){
                if ( !isset( $fields[$connection_type]["values"] )){
                    return new WP_Error( __FUNCTION__, "Missing values field on connection: " . $connection_type, [ 'status' => 500 ] );
                }
                $existing_connections = [];
                if ( isset( $existing_group[$connection_type] ) ){
                    foreach ( $existing_group[$connection_type] as $connection){
                        $existing_connections[] = $connection->ID;
                    }
                }
                //check for new connections
                $connection_field = $fields[$connection_type];
                $new_connections = [];
                foreach ($connection_field["values"] as $connection_value ){
                    if ( isset( $connection_value["value"] ) && !is_numeric( $connection_value["value"] ) ){
                        $post_types = self::$group_connection_types;
                        $post_types[] = "groups";
                        $post = self::get_post_by_title_cached( $connection_value["value"], OBJECT, $post_types, $connection_type );
                        if ( $post && !is_wp_error( $post ) ){
                            $connection_value["value"] = $post->ID;
                        }
                    }
                    if ( isset( $connection_value["value"] ) && is_numeric( $connection_value["value"] )){
                        if ( isset( $connection_value["delete"] ) && $connection_value["delete"] === true ){
                            if ( in_array( $connection_value["value"], $existing_connections )){
                                $potential_error = self::remove_group_connection( $group_id, $connection_type, $connection_value["value"], false );
                                if ( is_wp_error( $potential_error ) ) {
                                    return $potential_error;
                                }
                            }
                        } else {
                            $new_connections[] = $connection_value["value"];
                            if ( !in_array( $connection_value["value"], $existing_connections )){
                                $potential_error = self::add_item_to_field( $group_id, $connection_type, $connection_value["value"], false );
                                if ( is_wp_error( $potential_error ) ) {
                                    return $potential_error;
                                }
                                $added_fields[$connection_type] = $potential_error;
                            }
                        }
                    } else {
                         return new WP_Error( __FUNCTION__, "Cannot determine target on connection: " . $connection_type, [ 'status' => 500 ] );
                    }
                }
                //check for deleted connections
                if ( isset( $connection_field["force_values"] ) && $connection_field["force_values"] === true ){
                    foreach ($existing_connections as $connection_value ){
                        if ( !in_array( $connection_value, $new_connections )){
                            $potential_error = self::remove_group_connection( $group_id, $connection_type, $connection_value, false );
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


    /**
     * Update an existing Group
     *
     * @param int   $group_id , the post id for the group
     * @param array $fields   , the meta fields
     * @param bool  $check_permissions
     *
     * @access public
     * @since  0.1.0
     * @return int | WP_Error of group ID
     */
    public static function update_group( int $group_id, array $fields, bool $check_permissions = true ) {

        if ( $check_permissions && !self::can_update( 'groups', $group_id ) ) {
            return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
        }

        $field_keys = array_keys( $fields );
        $post = get_post( $group_id );
        if ( isset( $fields['id'] ) ) {
            unset( $fields['id'] );
        }

        if ( !$post ) {
            return new WP_Error( __FUNCTION__, "Group does not exist" );
        }
        $bad_fields = self::check_for_invalid_fields( $fields, $group_id );
        if ( !empty( $bad_fields ) ) {
            return new WP_Error( __FUNCTION__, "One or more fields do not exist", [
                'bad_fields' => $bad_fields,
                'status' => 400
            ] );
        }

        $existing_group = self::get_group( $group_id, false );
        $added_fields = [];

        if ( isset( $fields['title'] ) && $existing_group["title"] != $fields['title']) {
            wp_update_post( [
                'ID' => $group_id,
                'post_title' => $fields['title']
            ] );
            dt_activity_insert( [
                'action'         => 'field_update',
                'object_type'    => "groups",
                'object_subtype' => 'title',
                'object_id'      => $group_id,
                'object_name'    => $fields['title'],
                'meta_key'       => 'title',
                'meta_value'     => $fields['title'],
                'old_value'      => $existing_group['title'],
            ] );
        }

        $potential_error = self::parse_contact_methods( $group_id, $fields, $existing_group );
        if ( is_wp_error( $potential_error )){
            return $potential_error;
        }

        $potential_error = self::parse_connections( $group_id, $fields, $existing_group );
        if ( is_wp_error( $potential_error )){
            return $potential_error;
        }

        $potential_error = self::parse_multi_select_fields( $group_id, $fields, $existing_group );
        if ( is_wp_error( $potential_error )){
            return $potential_error;
        }

        if ( isset( $fields["assigned_to"] ) ) {
            if ( filter_var( $fields["assigned_to"], FILTER_VALIDATE_EMAIL ) ){
                $user = get_user_by( "email", $fields["assigned_to"] );
                if ( $user ) {
                    $fields["assigned_to"] = $user->ID;
                } else {
                    return new WP_Error( __FUNCTION__, "Unrecognized user", $fields["assigned_to"] );
                }
            }
            //make sure the assigned to is in the right format (user-1)
            if ( is_numeric( $fields["assigned_to"] ) ||
                 strpos( $fields["assigned_to"], "user" ) === false ){
                $fields["assigned_to"] = "user-" . $fields["assigned_to"];
            }
            $user_id = explode( '-', $fields["assigned_to"] )[1];
            if ( $user_id ){
                self::add_shared( "groups", $group_id, $user_id, null, false, false, false );
            }
        }
        if ( isset( $fields["group_type"] ) && empty( $fields["church_start_date"] ) && empty( $existing_group["church_start_date"] ) && $fields["group_type"] === 'church' ){
            $fields["church_start_date"] = time();
        }
        if ( isset( $fields["group_status"] ) && empty( $fields["end_date"] ) && empty( $existing_group["end_date"] ) && $fields["group_status"] === 'inactive' ){
            $fields["end_date"] = time();
        }

        $fields["last_modified"] = time(); //make sure the last modified field is updated.
        foreach ( $fields as $field_id => $value ) {
            if ( !self::is_key_contact_method_or_connection( $field_id )){
                $field_type = self::$group_fields[$field_id]["type"] ?? '';
                //we handle multi_select above.
                if ( $field_type === 'date' && !is_numeric( $value )){
                    $value = strtotime( $value );
                }
                if ( $field_type && $field_type !== "multi_select" ){
                    update_post_meta( $group_id, $field_id, $value );
                }
            }
        }

        $group = self::get_group( $group_id, true );
        $group["added_fields"] = $added_fields;

        //hook for when a group has been updated
        if ( !is_wp_error( $group ) ){
            do_action( "dt_group_updated", $field_keys, $group );
            Disciple_Tools_Notifications::insert_notification_for_post_update( "groups", $group, $existing_group, $field_keys );
        }
        return $group;

    }

    //check to see if the group is marked as needing an update
    //if yes: mark as updated
    private static function check_requires_update( $group_id ){
        if ( get_current_user_id() ){
            $requires_update = get_post_meta( $group_id, "requires_update", true );
            if ( $requires_update == "yes" || $requires_update == true || $requires_update = "1"){
                //don't remove update needed if the user is a dispatcher (and not assigned to the groups.)
                if ( self::can_view_all( 'groups' ) ){
                    if ( dt_get_user_id_from_assigned_to( get_post_meta( $group_id, "assigned_to", true ) ) === get_current_user_id() ){
                        update_post_meta( $group_id, "requires_update", false );
                    }
                } else {
                    update_post_meta( $group_id, "requires_update", false );
                }
            }
        }
    }

    /**
     * @param int    $group_id
     * @param string $key
     *
     * @return bool|\WP_Error
     */
    public static function delete_group_field( int $group_id, string $key, $check_permissions = true ){
        if ( $check_permissions && !self::can_update( 'groups', $group_id )){
            return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 401 ] );
        }
        delete_post_meta( $group_id, $key .'_details' );
        return delete_post_meta( $group_id, $key );
    }

    /**
     * @param int $group_id
     * @param int $location_id
     *
     * @return mixed
     */
    public static function add_location_to_group( int $group_id, int $location_id ) {
        return p2p_type( 'groups_to_locations' )->connect(
            $location_id, $group_id,
            [ 'date' => current_time( 'mysql' ) ]
        );
    }

    /**
     * @param $group_id
     * @param $people_group_id
     *
     * @return mixed
     */
    public static function add_people_group_to_group( $group_id, $people_group_id ) {
        return p2p_type( 'groups_to_peoplegroups' )->connect(
            $people_group_id, $group_id,
            [ 'date' => current_time( 'mysql' ) ]
        );
    }

    /**
     * @param int $group_id
     * @param int $member_id
     *
     * @return mixed
     */
    public static function add_member_to_group( int $group_id, int $member_id ) {
        // share the group with the owner of the contact.
        $added = p2p_type( 'contacts_to_groups' )->connect(
            $member_id, $group_id,
            [ 'date' => current_time( 'mysql' ) ]
        );
        if ( !is_wp_error( $added )){
            $assigned_to = get_post_meta( $member_id, "assigned_to", true );
            if ( $assigned_to && strpos( $assigned_to, "-" ) !== false ){
                $user_id = explode( "-", $assigned_to )[1];
                if ( $user_id ){
                    self::add_shared_on_group( $group_id, $user_id, null, false, false );
                }
            }
            do_action( 'group_member_count', $group_id, "added" );
        }
        return $added;
    }

    public function update_group_member_count( $group_id, $action = "added" ){
        $group = get_post( $group_id );

        $args = [
            'connected_type'   => "contacts_to_groups",
            'connected_direction' => 'to',
            'connected_items'  => $group,
            'nopaging'         => true,
            'suppress_filters' => false,
        ];
        $members = get_posts( $args );
        $member_count = get_post_meta( $group_id, 'member_count', true );
        if ( sizeof( $members ) > intval( $member_count ) ){
            update_post_meta( $group_id, 'member_count', sizeof( $members ) );
        } elseif ( $action === "removed" ){
            update_post_meta( $group_id, 'member_count', $member_count - 1 );
        }
    }

    /**
     * @param int $group_id
     * @param int $leader_id
     *
     * @return mixed
     */
    public static function add_leader_to_group( int $group_id, int $leader_id ) {
        return p2p_type( 'groups_to_leaders' )->connect(
            $group_id, $leader_id,
            [ 'date' => current_time( 'mysql' ) ]
        );
    }

    /**
     * @param int $group_id
     * @param int $coach_id
     *
     * @return mixed
     */
    public static function add_coach_to_group( int $group_id, int $coach_id ) {
        return p2p_type( 'groups_to_coaches' )->connect(
            $group_id, $coach_id,
            [ 'date' => current_time( 'mysql' ) ]
        );
    }

    /**
     * @param int $group_id
     * @param int $post_id
     *
     * @return mixed
     */
    public static function add_child_group_to_group( int $group_id, int $post_id ) {
        return p2p_type( 'groups_to_groups' )->connect(
            $post_id, $group_id,
            [ 'date' => current_time( 'mysql' ) ]
        );
    }

    /**
     * @param int $group_id
     * @param int $post_id
     *
     * @return mixed
     */
    public static function add_parent_group_to_group( int $group_id, int $post_id ) {
        return p2p_type( 'groups_to_groups' )->connect(
            $group_id, $post_id,
            [ 'date' => current_time( 'mysql' ) ]
        );
    }

    public static function add_peer_group_to_group( int $group_id, int $post_id ) {
        return p2p_type( 'groups_to_peers' )->connect(
            $group_id, $post_id,
            [ 'date' => current_time( 'mysql' ) ]
        );
    }

    /**
     * @param int $group_id
     * @param int $location_id
     *
     * @return mixed
     */
    public static function remove_location_from_group( int $group_id, int $location_id ) {
        return p2p_type( 'groups_to_locations' )->disconnect( $location_id, $group_id );
    }

    /**
     * @param $group_id
     * @param $people_group_id
     *
     * @return mixed
     */
    public static function remove_people_group_from_group( $group_id, $people_group_id ) {
        return p2p_type( 'groups_to_peoplegroups' )->disconnect( $people_group_id, $group_id );
    }


    /**
     * @param int $group_id
     * @param int $member_id
     *
     * @return mixed
     */
    public static function remove_member_from_group( int $group_id, int $member_id ) {
        $removed = p2p_type( 'contacts_to_groups' )->disconnect( $member_id, $group_id );
        if ( !is_wp_error( $removed ) ){
            do_action( 'group_member_count', $group_id, "removed" );
        }
        return $removed;
    }

    /**
     * @param int $group_id
     * @param int $leader_id
     *
     * @return mixed
     */
    public static function remove_leader_from_group( int $group_id, int $leader_id ) {
        return p2p_type( 'groups_to_leaders' )->disconnect( $group_id, $leader_id );
    }

    /**
     * @param int $group_id
     * @param int $coach_id
     *
     * @return mixed
     */
    public static function remove_coach_from_group( int $group_id, int $coach_id ) {
        return p2p_type( 'groups_to_coaches' )->disconnect( $group_id, $coach_id );
    }

    /**
     * @param int $group_id
     * @param int $post_id
     *
     * @return mixed
     */
    public static function remove_child_group_from_group( int $group_id, int $post_id ) {
        return p2p_type( 'groups_to_groups' )->disconnect( $post_id, $group_id );
    }

    /**
     * @param int $group_id
     * @param int $post_id
     *
     * @return mixed
     */
    public static function remove_parent_group_from_group( int $group_id, int $post_id ) {
        return p2p_type( 'groups_to_groups' )->disconnect( $group_id, $post_id );
    }

    public static function remove_peer_group_from_group( int $group_id, int $post_id ) {
        return p2p_type( 'groups_to_peers' )->disconnect( $group_id, $post_id );
    }

    /**
     * @param int    $group_id
     * @param string $key
     * @param string $value
     * @param bool   $check_permissions
     *
     * @return array|mixed|null|string|\WP_Error|\WP_Post
     */
    public static function add_item_to_field( int $group_id, string $key, string $value, bool $check_permissions ) {
        if ( $check_permissions && !self::can_update( 'groups', $group_id ) ) {
            return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
        }
        if ( $key === "new-address" ) {
            $new_meta_key = dt_address_metabox()->create_channel_metakey( "address" );
            update_post_meta( $group_id, $new_meta_key, $value );
            $details = [ "verified" => false ];
            update_post_meta( $group_id, $new_meta_key . "_details", $details );

            return $new_meta_key;
        }
        $connect = null;
        if ( $key === "locations" ) {
            $connect = self::add_location_to_group( $group_id, $value );
        } elseif ( $key === "members" ) {
            $connect = self::add_member_to_group( $group_id, $value );
        } elseif ( $key === "people_groups" ) {
            $connect = self::add_people_group_to_group( $group_id, $value );
        } elseif ( $key === "leaders" ) {
            $connect = self::add_leader_to_group( $group_id, $value );
        } elseif ( $key === "coaches" ) {
            $connect = self::add_coach_to_group( $group_id, $value );
        } elseif ( $key === "child_groups" ) {
            $connect = self::add_child_group_to_group( $group_id, $value );
        } elseif ( $key === "parent_groups" ) {
            $connect = self::add_parent_group_to_group( $group_id, $value );
        } elseif ( $key === "peer_groups" ) {
            $connect = self::add_peer_group_to_group( $group_id, $value );
        } else {
            return new WP_Error( __FUNCTION__, "Field not recognized: " . $key, [ "status" => 400 ] );
        }
        if ( is_wp_error( $connect ) ) {
            return $connect;
        }
        if ( $connect ) {
            $connection = get_post( $value );
            $connection->guid = get_permalink( $value );

            return $connection;
        } else {
            return new WP_Error( __FUNCTION__, "Field not parsed or understood: " . $key, [ "status" => 400 ] );
        }
    }

    /**
     * @param int    $group_id
     * @param string $key
     * @param array  $values
     * @param bool   $check_permissions
     *
     * @return int|\WP_Error
     */
    public static function update_contact_method( int $group_id, string $key, array $values, bool $check_permissions ) {
        if ( $check_permissions && !self::can_update( 'groups', $group_id ) ) {
            return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
        }
        if ( ( strpos( $key, "contact_" ) === 0 || strpos( $key, "address_" ) === 0 ) &&
              strpos( $key, "_details" ) === false
        ) {
            $old_value = get_post_meta( $group_id, $key, true );
            //check if it is different to avoid setting saving activity
            if ( isset( $values["value"] ) && $old_value != $values["value"] ){
                update_post_meta( $group_id, $key, $values["value"] );
            }
            unset( $values["value"] );
            unset( $values["key"] );

            $details_key = $key . "_details";
            $old_details = get_post_meta( $group_id, $details_key, true );
            $details = isset( $old_details ) ? $old_details : [];
            $new_value = false;
            foreach ( $values as $detail_key => $detail_value ) {
                if ( !isset( $details[$detail_key] ) || $details[$detail_key] !== $detail_value){
                    $new_value = true;
                }
                $details[ $detail_key ] = $detail_value;
            }
            if ($new_value){
                update_post_meta( $group_id, $details_key, $details );
            }
        }

        return $group_id;
    }


    /**
     * @param int    $group_id
     * @param string $key
     * @param string $value
     * @param bool   $check_permissions
     *
     * @return bool|mixed|\WP_Error
     */
    public static function remove_group_connection( int $group_id, string $key, string $value, bool $check_permissions ) {
        if ( $check_permissions && !self::can_update( 'groups', $group_id ) ) {
            return new WP_Error( __FUNCTION__, "You do not have have permission for this", [ 'status' => 403 ] );
        }
        if ( $key === "locations" ) {
            return self::remove_location_from_group( $group_id, $value );
        } elseif ( $key === "members" ) {
            return self::remove_member_from_group( $group_id, $value );
        } elseif ( $key === "leaders" ) {
            return self::remove_leader_from_group( $group_id, $value );
        } elseif ( $key === "coaches" ) {
            return self::remove_coach_from_group( $group_id, $value );
        } elseif ( $key === "people_groups" ) {
            return self::remove_people_group_from_group( $group_id, $value );
        } elseif ( $key === "child_groups" ) {
            return self::remove_child_group_from_group( $group_id, $value );
        } elseif ( $key === "parent_groups" ) {
            return self::remove_parent_group_from_group( $group_id, $value );
        } elseif ( $key === "peer_groups" ) {
            return self::remove_peer_group_from_group( $group_id, $value );
        }

        return false;
    }

    /**
     * @param int $group_id
     * @param string $comment_html
     * @param string $type
     * @param array $args
     * @param bool $check_permissions
     * @param bool $silent
     *
     * @return false|int|\WP_Error
     */
    public static function add_comment( int $group_id, string $comment_html, string $type = "comment", array $args = [], bool $check_permissions = true, $silent = false ) {
        $result = self::add_post_comment( 'groups', $group_id, $comment_html, $type, $args, $check_permissions, $silent );
        if ( $type === "comment" && !is_wp_error( $result )){
            self::check_requires_update( $group_id );
        }
        return $result;
    }

    /**
     * @param int $group_id
     *
     * @return array|int|\WP_Error
     */
    public static function get_comments( int $group_id ) {
        return self::get_post_comments( 'groups', $group_id );
    }


    public static function delete_comment( int $group_id, int $comment_id, bool $check_permissions = true ){
        $comment = get_comment( $comment_id );
        if ( $check_permissions && isset( $comment->user_id ) && $comment->user_id != get_current_user_id() ) {
            return new WP_Error( __FUNCTION__, "You don't have permission to delete this comment", [ 'status' => 403 ] );
        }
        if ( !$comment ){
            return new WP_Error( __FUNCTION__, "No comment found with id: " . $comment_id, [ 'status' => 403 ] );
        }
        return wp_delete_comment( $comment_id );
    }

    public static function update_comment( int $group_id, int $comment_id, string $comment_content, bool $check_permissions = true ){
        $comment = get_comment( $comment_id );
        if ( $check_permissions && isset( $comment->user_id ) && $comment->user_id != get_current_user_id() ) {
            return new WP_Error( __FUNCTION__, "You don't have permission to edit this comment", [ 'status' => 403 ] );
        }
        if ( !$comment ){
            return new WP_Error( __FUNCTION__, "No comment found with id: " . $comment_id, [ 'status' => 403 ] );
        }
        $comment = [
            "comment_content" => $comment_content,
            "comment_ID" => $comment_id,
        ];
        return wp_update_comment( $comment );
    }

    /**
     * @param int $group_id
     *
     * @return array|null|object|\WP_Error
     */
    public static function get_activity( int $group_id ) {
        $fields = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings( isset( $group_id ), $group_id );
        return self::get_post_activity( 'groups', $group_id, $fields );
    }

    /**
     * Gets an array of users whom the group is shared with.
     *
     * @param int $post_id
     *
     * @return array|mixed
     */
    public static function get_shared_with_on_group( int $post_id ) {
        return self::get_shared_with( 'groups', $post_id );
    }

    /**
     * Removes share record
     *
     * @param int $post_id
     * @param int $user_id
     *
     * @return false|int|WP_Error
     */
    public static function remove_shared_on_group( int $post_id, int $user_id ) {
        return self::remove_shared( 'groups', $post_id, $user_id );
    }

    /**
     * Adds a share record
     *
     * @param int $post_id
     * @param int $user_id
     * @param array $meta
     *
     * @param bool $send_notifications
     * @param bool $check_permissions
     *
     * @return false|int|WP_Error
     */
    public static function add_shared_on_group( int $post_id, int $user_id, $meta = null, bool $send_notifications = true, bool $check_permissions = true ) {
        return self::add_shared( 'groups', $post_id, $user_id, $meta, $send_notifications, $check_permissions );
    }

    /**
     * Create a new group
     *
     * @param  array     $fields , the new group's data
     * @param  bool|true $check_permissions
     * @return int | WP_Error
     */
    public static function create_group( array $fields = [], $check_permissions = true ) {
        if ( $check_permissions && ! current_user_can( 'create_groups' ) ) {
            return new WP_Error( __FUNCTION__, "You may not create a group", [ 'status' => 403 ] );
        }
        $initial_fields = $fields;

        if ( ! isset( $fields ["title"] ) ) {
            return new WP_Error( __FUNCTION__, "Group needs a title", [ 'fields' => $fields ] );
        }

        if ( isset( $fields["assigned_to"] ) ) {
            if ( filter_var( $fields["assigned_to"], FILTER_VALIDATE_EMAIL ) ){
                $user = get_user_by( "email", $fields["assigned_to"] );
                if ( $user ) {
                    $fields["assigned_to"] = $user->ID;
                } else {
                    return new WP_Error( __FUNCTION__, "Unrecognized user", $fields["assigned_to"] );
                }
            }
            //make sure the assigned to is in the right format (user-1)
            if ( is_numeric( $fields["assigned_to"] ) ||
                 strpos( $fields["assigned_to"], "user" ) === false ){
                $fields["assigned_to"] = "user-" . $fields["assigned_to"];
            }
        }
        $allowed_fields = [ "parent_group_id", "created_from_contact_id" ];
        $bad_fields = self::check_for_invalid_fields( $fields, null, $allowed_fields );
        if ( !empty( $bad_fields ) ) {
            return new WP_Error( __FUNCTION__, "One or more fields do not exist", [
                'bad_fields' => $bad_fields,
                'status' => 400
            ] );
        }

        $defaults = [
            "group_status" => "active",
            "group_type" => "pre-group",
            "assigned_to" => sprintf( "user-%d", get_current_user_id() ),
            "start_date" => time()
        ];
        if ( isset( $fields["group_type"] ) && !isset( $fields["church_start_date"] ) && $fields["group_type"] === 'church' ){
            $fields["church_start_date"] = time();
        }

        $fields = array_merge( $defaults, $fields );

        $contact_methods_and_connections = [];
        foreach ( $fields as $field_key => $field_value ){
            if ( self::is_key_contact_method_or_connection( $field_key )){
                $contact_methods_and_connections[$field_key] = $field_value;
                unset( $fields[$field_key] );
            }
            $field_type = self::$group_fields[$field_key]["type"] ?? '';
            if ( $field_type === 'date' && !is_numeric( $field_value )){
                $fields[$field_value] = strtotime( $field_value );
            }
        }

        $post = [
            "post_title" => $fields["title"],
            "post_type" => "groups",
            "post_status" => "publish",
            "meta_input" => $fields
        ];

        $post_id = wp_insert_post( $post );

        if ( isset( $fields["assigned_to"] )){
            $user_id = explode( '-', $fields["assigned_to"] )[1];
            if ( $user_id ){
                self::add_shared( "groups", $post_id, $user_id, null, false );
            }
        }

        $potential_error = self::parse_contact_methods( $post_id, $contact_methods_and_connections );
        if ( is_wp_error( $potential_error )){
            return $potential_error;
        }

        $potential_error = self::parse_connections( $post_id, $contact_methods_and_connections, null );
        if ( is_wp_error( $potential_error )){
            return $potential_error;
        }

        if ( isset( $fields["created_from_contact_id"] ) ){
            self::add_item_to_field( $post_id, "members", $fields["created_from_contact_id"], true );
        }
        if ( isset( $fields["parent_group_id"] )){
            self::add_child_group_to_group( $fields["parent_group_id"], $post_id );
        }

        //hook for signaling that a group has been created and the initial fields
        if ( !is_wp_error( $post_id )){
            do_action( "dt_group_created", $post_id, $initial_fields );
            Disciple_Tools_Notifications::insert_notification_for_new_post( "groups", $fields, $post_id );
        }
        return $post_id;
    }


    public static function get_group_default_filter_counts( $tab = "all", $show_closed = false ){
        if ( !self::can_access( "groups" ) ) {
            return new WP_Error( __FUNCTION__, "Permission denied.", [ 'status' => 403 ] );
        }
        $user_id = get_current_user_id();
        global $wpdb;

        $access_sql = "";
        $user_post = Disciple_Tools_Users::get_contact_for_user( $user_id ) ?? 0;
        // contacts assigned to me
        $my_access = "INNER JOIN $wpdb->postmeta as assigned_to
            ON a.ID=assigned_to.post_id
              AND assigned_to.meta_key = 'assigned_to'
              AND assigned_to.meta_value = CONCAT( 'user-', " . $user_id . " )";
        //contacts subassigned to me
        $subassigned_access = "INNER JOIN $wpdb->p2p as from_p2p 
            ON ( from_p2p.p2p_to = a.ID 
                AND from_p2p.p2p_type = 'contacts_to_subassigned' 
                AND from_p2p.p2p_from = " . $user_post. ")";
        //contacts shared with me
        $shared_access = "
            INNER JOIN $wpdb->dt_share AS shares 
            ON ( shares.post_id = a.ID  
                AND shares.user_id = " . $user_id . "
                AND a.ID NOT IN (
                    SELECT assigned_to.post_id 
                    FROM $wpdb->postmeta as assigned_to
                    WHERE a.ID = assigned_to.post_id
                      AND assigned_to.meta_key = 'assigned_to'
                      AND assigned_to.meta_value = CONCAT( 'user-', " . $user_id . " )
                )
            )";
        $all_access = "";
        //contacts shared with me.
        if ( !self::can_view_all( "contacts" ) ){
            $all_access = "INNER JOIN $wpdb->dt_share AS shares 
            ON ( shares.post_id = a.ID
                 AND shares.user_id = " . $user_id . " ) ";
        }
        if ( $tab === "my" ){
            $access_sql = $my_access;
        } elseif ( $tab === "subassigned" ){
            $access_sql = $subassigned_access;
        } elseif ( $tab === "shared" ){
            $access_sql = $shared_access;
        } elseif ( $tab === "all" ){
            $access_sql = $all_access;
        }
        $closed = "";
        if ( !$show_closed ){
            $closed = " INNER JOIN $wpdb->postmeta as status
              ON ( a.ID=status.post_id 
              AND status.meta_key = 'group_status'
              AND status.meta_value != 'inactive' )";
        }

        // phpcs:disable
        // WordPress.WP.PreparedSQL.NotPrepare
        $personal_counts = $wpdb->get_results( "
            SELECT (
                SELECT COUNT(DISTINCT(a.ID))
                FROM $wpdb->posts as a
                " . $access_sql . $closed . "
                WHERE a.post_status = 'publish'
                AND a.post_type = 'groups'
            ) as total_count,
            (SELECT COUNT(DISTINCT(a.ID))
                FROM $wpdb->posts as a
                " . $my_access . $closed . "
                WHERE a.post_status = 'publish'
                AND a.post_type = 'groups'
            ) as total_my,
            (SELECT COUNT(DISTINCT(a.ID))
                FROM $wpdb->posts as a
                " . $shared_access . $closed . "
                WHERE a.post_status = 'publish'
                AND a.post_type = 'groups'
            ) as total_shared,
            (SELECT COUNT(DISTINCT(a.ID))
                FROM $wpdb->posts as a
                " . $all_access . $closed . "
                WHERE a.post_status = 'publish'
                AND a.post_type = 'groups'
            ) as total_all
        ", ARRAY_A );
        // phpcs:enable

        return $personal_counts[0] ?? [];

    }
}
