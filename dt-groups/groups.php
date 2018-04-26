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
    public function __construct()
    {
        add_action(
            'init',
            function() {
                self::$address_types = dt_address_metabox()->get_address_type_list( "groups" );
                self::$group_fields = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings();
                self::$group_connection_types = [
                    "members",
                    "parent_groups",
                    "child_groups",
                    "locations",
                    "people_groups",
                    "leaders"
                ];
                self::$channel_list = [
                    "address"
                ];
            }
        );
        parent::__construct();
    }

    /**
     * @param  $search_string
     *
     * @return array|\WP_Query
     */
    public static function get_groups_compact( string $search_string )
    {
        return self::get_viewable_compact( 'groups', $search_string );
    }

    /**
     * @param int $most_recent
     *
     * @return array|\WP_Query
     */
    public static function get_viewable_groups( int $most_recent = 0 )
    {
        return self::get_viewable( 'groups', $most_recent );
    }

    /**
     * @param  int  $group_id
     * @param  bool $check_permissions
     *
     * @return array|\WP_Error
     */
    public static function get_group( int $group_id, bool $check_permissions = true )
    {
        if ( $check_permissions && !self::can_view( 'groups', $group_id ) ) {
            return new WP_Error( __FUNCTION__, __( "No permissions to read group" ), [ 'status' => 403 ] );
        }

        $group = get_post( $group_id );
        if ( $group ) {
            $fields = [];

            $locations = get_posts(
                [
                    'connected_type'   => 'groups_to_locations',
                    'connected_items'  => $group,
                    'nopaging'         => true,
                    'suppress_filters' => false,
                ]
            );
            foreach ( $locations as $l ) {
                $l->permalink = get_permalink( $l->ID );
            }
            $fields["locations"] = $locations;


            $people_groups = get_posts(
                [
                    'connected_type'   => 'groups_to_peoplegroups',
                    'connected_items'  => $group,
                    'nopaging'         => true,
                    'suppress_filters' => false,
                ]
            );
            foreach ( $people_groups as $g ) {
                $g->permalink = get_permalink( $g->ID );
            }
            $fields["people_groups"] = $people_groups;

            $members = get_posts(
                [
                    'connected_type'   => 'contacts_to_groups',
                    'connected_items'  => $group,
                    'nopaging'         => true,
                    'suppress_filters' => false,
                ]
            );
            foreach ( $members as $l ) {
                $l->permalink = get_permalink( $l->ID );
            }
            $fields["members"] = $members;

            $leaders = get_posts(
                [
                    'connected_type'   => 'groups_to_leaders',
                    'connected_items'  => $group,
                    'nopaging'         => true,
                    'suppress_filters' => false,
                ]
            );
            foreach ( $leaders as $l ) {
                $l->permalink = get_permalink( $l->ID );
            }
            $fields["leaders"] = $leaders;

            $child_groups = get_posts(
                [
                    'connected_type'   => 'groups_to_groups',
                    'connected_direction' => 'to',
                    'connected_items'  => $group,
                    'nopaging'         => true,
                    'suppress_filters' => false,
                ]
            );
            foreach ( $child_groups as $g ) {
                $g->permalink = get_permalink( $g->ID );
            }
            $fields["child_groups"] = $child_groups;

            $parent_groups = get_posts(
                [
                    'connected_type'   => 'groups_to_groups',
                    'connected_direction' => 'from',
                    'connected_items'  => $group,
                    'nopaging'         => true,
                    'suppress_filters' => false,
                ]
            );
            foreach ( $parent_groups as $g ) {
                $g->permalink = get_permalink( $g->ID );
            }
            $fields["parent_groups"] = $parent_groups;

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
                } elseif ( isset( self::$group_fields[ $key ] ) && self::$group_fields[ $key ]["type"] == "key_select" ) {
                    $label = self::$group_fields[ $key ]["default"][ $value[0] ] ?? current( self::$group_fields[ $key ]["default"] );
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
                                        "ID"          => (int) $id,
                                        "type"        => $type,
                                        "display"     => $user->display_name,
                                        "assigned-to" => $value[0],
                                    ];
                                }
                            }
                        }
                    }
                } else {
                    $fields[ $key ] = $value[0];
                }
            }
            $fields["ID"] = $group->ID;
            $fields["name"] = $group->post_title;
            $fields["title"] = $group->post_title;

            return $fields;
        } else {
            return new WP_Error( __FUNCTION__, __( "No group found with ID" ), [ 'contact_id' => $group_id ] );
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
     * @param array    $fields  , the group meta fields
     * @param int|null $post_id , the id of the group
     *
     * @access private
     * @since  0.1.0
     * @return array
     */
    private static function check_for_invalid_fields( array $fields, int $post_id = null )
    {
        $bad_fields = [];
        $group_fields = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings( isset( $post_id ), $post_id );
        $group_fields['title'] = "";
        foreach ( $fields as $field => $value ) {
            if ( !isset( $group_fields[ $field ] ) && !self::is_key_contact_method_or_connection( $field ) ) {
                $bad_fields[] = $field;
            }
        }
        return $bad_fields;
    }

    private static function parse_contact_methods( $group_id, $fields ){
        // update group details (phone, facebook, etc)
        foreach ( self::$channel_list as $channel_key ){
            $details_key = "contact_" . $channel_key;
            if ( isset( $fields[$details_key] ) && is_array( $fields[$details_key] )){
                foreach ( $fields[$details_key] as $field ){
                    if ( isset( $field["delete"] ) && $field["delete"] == true){
                        if ( !isset( $field["key"] )){
                            return new WP_Error( __FUNCTION__, __( "missing key on:" ) . " " . $details_key );
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
                        return new WP_Error( __FUNCTION__, __( "Is not an array or missing value on:" ) . " " . $details_key );
                    }
                    if ( isset( $potential_error ) && is_wp_error( $potential_error ) ) {
                        return $potential_error;
                    }
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
                    return new WP_Error( __FUNCTION__, __( "Missing values field on connection:" ) . " " . $connection_type, [ 'status' => 500 ] );
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
                         return new WP_Error( __FUNCTION__, __( "Cannot determine target on connection:" ) . " " . $connection_type, [ 'status' => 500 ] );
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
    public static function update_group( int $group_id, array $fields, bool $check_permissions = true )
    {

        if ( $check_permissions && !self::can_update( 'groups', $group_id ) ) {
            return new WP_Error( __FUNCTION__, __( "You do not have permission for this" ), [ 'status' => 403 ] );
        }

        $field_keys = array_keys( $fields );
        $post = get_post( $group_id );
        if ( isset( $fields['id'] ) ) {
            unset( $fields['id'] );
        }

        if ( !$post ) {
            return new WP_Error( __FUNCTION__, __( "Group does not exist" ) );
        }
        $bad_fields = self::check_for_invalid_fields( $fields, $group_id );
        if ( !empty( $bad_fields ) ) {
            return new WP_Error( __FUNCTION__, __( "One or more fields do not exist" ), [
                'bad_fields' => $bad_fields,
                'status' => 400
            ] );
        }

        $existing_group = self::get_group( $group_id, false );
        $added_fields = [];

        if ( isset( $fields['title'] ) ) {
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

        $potential_error = self::parse_contact_methods( $group_id, $fields );
        if ( is_wp_error( $potential_error )){
            return $potential_error;
        }

        $potential_error = self::parse_connections( $group_id, $fields, $existing_group );
        if ( is_wp_error( $potential_error )){
            return $potential_error;
        }

        if ( isset( $fields["assigned_to"] ) ) {
            if ( filter_var( $fields["assigned_to"], FILTER_VALIDATE_EMAIL ) ){
                $user = get_user_by( "email", $fields["assigned_to"] );
                if ( $user ) {
                    $fields["assigned_to"] = $user->ID;
                } else {
                    return new WP_Error( __FUNCTION__, __( "Unrecognized user" ), $fields["assigned_to"] );
                }
            }
            //make sure the assigned to is in the right format (user-1)
            if ( is_numeric( $fields["assigned_to"] ) ||
                 strpos( $fields["assigned_to"], "user" ) === false ){
                $fields["assigned_to"] = "user-" . $fields["assigned_to"];
            }
            $user_id = explode( '-', $fields["assigned_to"] )[1];
            if ( $user_id ){
                self::add_shared( "groups", $group_id, $user_id, null, false );
            }
        }

        foreach ( $fields as $field_id => $value ) {
            if ( !self::is_key_contact_method_or_connection( $field_id )){
                $field_type = self::$group_fields[$field_id]["type"] ?? '';
                if ( $field_type ) {
                    update_post_meta( $group_id, $field_id, $value );
                }
            }
        }

        $group = self::get_group( $group_id, true );
        $group["added_fields"] = $added_fields;

        //hook for when a group has been updated
        if ( !is_wp_error( $group ) ){
            do_action( "dt_group_updated", $field_keys, $group );
        }
        return $group;

    }

    /**
     * @param int    $group_id
     * @param string $key
     *
     * @return bool|\WP_Error
     */
    public static function delete_group_field( int $group_id, string $key ){
        if ( !self::can_update( 'groups', $group_id )){
            return new WP_Error( __FUNCTION__, __( "You do not have permission for this" ), [ 'status' => 401 ] );
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
    public static function add_location_to_group( int $group_id, int $location_id )
    {
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
    public static function add_people_group_to_group( $group_id, $people_group_id )
    {
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
    public static function add_member_to_group( int $group_id, int $member_id )
    {
        // share the group with the owner of the contact.
        $assigned_to = get_post_meta( $member_id, "assigned_to", true );
        if ( $assigned_to && strpos( $assigned_to, "-" ) !== false ){
            $user_id = explode( "-", $assigned_to )[1];
            if ( $user_id ){
                self::add_shared_on_group( $group_id, $user_id, null, false, false );
            }
        }

        return p2p_type( 'contacts_to_groups' )->connect(
            $member_id, $group_id,
            [ 'date' => current_time( 'mysql' ) ]
        );
    }

    /**
     * @param int $group_id
     * @param int $leader_id
     *
     * @return mixed
     */
    public static function add_leader_to_group( int $group_id, int $leader_id )
    {
        return p2p_type( 'groups_to_leaders' )->connect(
            $group_id, $leader_id,
            [ 'date' => current_time( 'mysql' ) ]
        );
    }

    /**
     * @param int $group_id
     * @param int $post_id
     *
     * @return mixed
     */
    public static function add_child_group_to_group( int $group_id, int $post_id )
    {
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
    public static function add_parent_group_to_group( int $group_id, int $post_id )
    {
        return p2p_type( 'groups_to_groups' )->connect(
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
    public static function remove_location_from_group( int $group_id, int $location_id )
    {
        return p2p_type( 'groups_to_locations' )->disconnect( $location_id, $group_id );
    }

    /**
     * @param $group_id
     * @param $people_group_id
     *
     * @return mixed
     */
    public static function remove_people_group_from_group( $group_id, $people_group_id )
    {
        return p2p_type( 'groups_to_peoplegroups' )->disconnect( $people_group_id, $group_id );
    }


    /**
     * @param int $group_id
     * @param int $member_id
     *
     * @return mixed
     */
    public static function remove_member_from_group( int $group_id, int $member_id )
    {
        return p2p_type( 'contacts_to_groups' )->disconnect( $member_id, $group_id );
    }

    /**
     * @param int $group_id
     * @param int $leader_id
     *
     * @return mixed
     */
    public static function remove_leader_from_group( int $group_id, int $leader_id )
    {
        return p2p_type( 'groups_to_leaders' )->disconnect( $group_id, $leader_id );
    }

    /**
     * @param int $group_id
     * @param int $post_id
     *
     * @return mixed
     */
    public static function remove_child_group_from_group( int $group_id, int $post_id )
    {
        return p2p_type( 'groups_to_groups' )->disconnect( $post_id, $group_id );
    }

    /**
     * @param int $group_id
     * @param int $post_id
     *
     * @return mixed
     */
    public static function remove_parent_group_from_group( int $group_id, int $post_id )
    {
        return p2p_type( 'groups_to_groups' )->disconnect( $group_id, $post_id );
    }

    /**
     * @param int    $group_id
     * @param string $key
     * @param string $value
     * @param bool   $check_permissions
     *
     * @return array|mixed|null|string|\WP_Error|\WP_Post
     */
    public static function add_item_to_field( int $group_id, string $key, string $value, bool $check_permissions )
    {
        if ( $check_permissions && !self::can_update( 'groups', $group_id ) ) {
            return new WP_Error( __FUNCTION__, __( "You do not have permission for this" ), [ 'status' => 403 ] );
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
        } elseif ( $key === "child_groups" ) {
            $connect = self::add_child_group_to_group( $group_id, $value );
        } elseif ( $key === "parent_groups" ) {
            $connect = self::add_parent_group_to_group( $group_id, $value );
        }
        if ( is_wp_error( $connect ) ) {
            return $connect;
        }
        if ( $connect ) {
            $connection = get_post( $value );
            $connection->guid = get_permalink( $value );

            return $connection;
        }

        return new WP_Error( "add_group_detail", "Field not recognized", [ "status" => 400 ] );
    }

    /**
     * @param int    $group_id
     * @param string $key
     * @param array  $values
     * @param bool   $check_permissions
     *
     * @return int|\WP_Error
     */
    public static function update_contact_method( int $group_id, string $key, array $values, bool $check_permissions )
    {
        if ( $check_permissions && !self::can_update( 'groups', $group_id ) ) {
            return new WP_Error( __FUNCTION__, __( "You do not have permission for this" ), [ 'status' => 403 ] );
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
    public static function remove_group_connection( int $group_id, string $key, string $value, bool $check_permissions )
    {
        if ( $check_permissions && !self::can_update( 'groups', $group_id ) ) {
            return new WP_Error( __FUNCTION__, __( "You do not have have permission for this" ), [ 'status' => 403 ] );
        }
        if ( $key === "locations" ) {
            return self::remove_location_from_group( $group_id, $value );
        } elseif ( $key === "members" ) {
            return self::remove_member_from_group( $group_id, $value );
        } elseif ( $key === "leaders" ) {
            return self::remove_leader_from_group( $group_id, $value );
        } elseif ( $key === "people_groups" ) {
            return self::remove_people_group_from_group( $group_id, $value );
        } elseif ( $key === "child_groups" ) {
            return self::remove_child_group_from_group( $group_id, $value );
        } elseif ( $key === "parent_groups" ) {
            return self::remove_parent_group_from_group( $group_id, $value );
        }

        return false;
    }

    /**
     * @param int    $group_id
     * @param string $comment
     *
     * @return false|int|\WP_Error
     */
    public static function add_comment( int $group_id, string $comment )
    {
        return self::add_post_comment( 'groups', $group_id, $comment );
    }

    /**
     * @param int $group_id
     *
     * @return array|int|\WP_Error
     */
    public static function get_comments( int $group_id )
    {
        return self::get_post_comments( 'groups', $group_id );
    }

    /**
     * @param int $group_id
     *
     * @return array|null|object|\WP_Error
     */
    public static function get_activity( int $group_id )
    {
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
    public static function get_shared_with_on_group( int $post_id )
    {
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
    public static function remove_shared_on_group( int $post_id, int $user_id )
    {
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
    public static function add_shared_on_group( int $post_id, int $user_id, $meta = null, bool $send_notifications = true, bool $check_permissions = true )
    {
        return self::add_shared( 'groups', $post_id, $user_id, $meta, $send_notifications, $check_permissions );
    }

    /**
     * Create a new group
     *
     * @param  array     $fields , the new group's data
     * @param  bool|true $check_permissions
     * @return int | WP_Error
     */
    public static function create_group( array $fields = [], $check_permissions = true )
    {
        if ( $check_permissions && ! current_user_can( 'create_groups' ) ) {
            return new WP_Error( __FUNCTION__, __( "You may not public a group" ), [ 'status' => 403 ] );
        }
        $initial_fields = $fields;

        if ( ! isset( $fields ["title"] ) ) {
            return new WP_Error( __FUNCTION__, __( "Group needs a title" ), [ 'fields' => $fields ] );
        }

        if ( isset( $fields["assigned_to"] ) ) {
            if ( filter_var( $fields["assigned_to"], FILTER_VALIDATE_EMAIL ) ){
                $user = get_user_by( "email", $fields["assigned_to"] );
                if ( $user ) {
                    $fields["assigned_to"] = $user->ID;
                } else {
                    return new WP_Error( __FUNCTION__, __( "Unrecognized user" ), $fields["assigned_to"] );
                }
            }
            //make sure the assigned to is in the right format (user-1)
            if ( is_numeric( $fields["assigned_to"] ) ||
                 strpos( $fields["assigned_to"], "user" ) === false ){
                $fields["assigned_to"] = "user-" . $fields["assigned_to"];
            }
        }

        $defaults = [
            "group_status" => "active",
            "group_type" => "pre-group",
            "assigned_to" => sprintf( "user-%d", get_current_user_id() ),
        ];

        $fields = array_merge( $defaults, $fields );

        $contact_methods_and_connections = [];
        foreach ( $fields as $field_key => $field_value ){
            if ( self::is_key_contact_method_or_connection( $field_key )){
                $contact_methods_and_connections[$field_key] = $field_value;
                unset( $fields[$field_key] );
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
        }
        return $post_id;
    }

}
