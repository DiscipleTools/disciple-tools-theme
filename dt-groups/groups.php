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
     * @return array|\WP_Query
     */
    public static function get_viewable_groups()
    {
        return self::get_viewable( 'groups' );
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

            $meta_fields = get_post_custom( $group_id );
            foreach ( $meta_fields as $key => $value ) {
                if ( strpos( $key, "address" ) === 0 ) {
                    if ( strpos( $key, "_details" ) === false ) {

                        $details = [];
                        if ( isset( $meta_fields[ $key . '_details' ][0] ) ) {
                            $details = unserialize( $meta_fields[ $key . '_details' ][0] );
                        }
                        $details["value"] = $value[0];
                        $details["key"] = $key;
                        if ( isset( $details["type"] ) ) {
                            $details["type_label"] = self::$address_types[ $details["type"] ]["label"];
                        }
                        $fields["address"][] = $details;
                    }
                } elseif ( isset( self::$group_fields[ $key ] ) && self::$group_fields[ $key ]["type"] == "key_select" ) {
                    $label = self::$group_fields[ $key ]["default"][ $value[0] ] ?? current( self::$group_fields[ $key ]["default"] );
                    $fields[ $key ] = [ "key" => $value[0], "label" => $label ];
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

            return $fields;
        } else {
            return new WP_Error( __FUNCTION__, __( "No group found with ID" ), [ 'contact_id' => $group_id ] );
        }
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
            if ( !isset( $group_fields[ $field ] ) ) {
                $bad_fields[] = $field;
            }
        }

        return $bad_fields;
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

        $post = get_post( $group_id );
        if ( isset( $fields['id'] ) ) {
            unset( $fields['id'] );
        }

        if ( !$post ) {
            return new WP_Error( __FUNCTION__, __( "Group does not exist" ) );
        }
        $bad_fields = self::check_for_invalid_fields( $fields, $group_id );
        if ( !empty( $bad_fields ) ) {
            return new WP_Error( __FUNCTION__, __( "One or more fields do not exist" ), [ 'bad_fields' => $bad_fields, 'status' => 400 ] );
        }

        if ( isset( $fields['title'] ) ) {
            wp_update_post( [ 'ID' => $group_id, 'post_title' => $fields['title'] ] );
        }

        foreach ( $fields as $field_id => $value ) {
            update_post_meta( $group_id, $field_id, $value );
        }

        return self::get_group( $group_id, true );
    }

    /**
     * @param int    $group_id
     * @param string $key
     *
     * @return bool|\WP_Error
     */
    public static function delete_group_field( int $group_id, string $key ){
        if ( !self::can_update( 'groups', $group_id )){
            return new WP_Error( __FUNCTION__, __( "You do not have permission for this" ), ['status' => 401] );
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
        return p2p_type( 'contacts_to_groups' )->connect(
            $member_id, $group_id,
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
    public static function update_detail_on_field( int $group_id, string $key, array $values, bool $check_permissions )
    {
        if ( $check_permissions && !self::can_update( 'groups', $group_id ) ) {
            return new WP_Error( __FUNCTION__, __( "You do not have permission for this" ), [ 'status' => 403 ] );
        }
        if ( ( strpos( $key, "group_" ) === 0 || strpos( $key, "address_" ) === 0 ) &&
            strpos( $key, "_details" ) === false
        ) {
            $details_key = $key . "_details";
            $details = get_post_meta( $group_id, $details_key, true );
            $details = isset( $details ) ? $details : [];
            foreach ( $values as $detail_key => $detail_value ) {
                $details[ $detail_key ] = $detail_value;
            }
            update_post_meta( $group_id, $details_key, $details );
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
    public static function remove_item_from_field( int $group_id, string $key, string $value, bool $check_permissions )
    {
        if ( $check_permissions && !self::can_update( 'groups', $group_id ) ) {
            return new WP_Error( __FUNCTION__, __( "You do not have have permission for this" ), [ 'status' => 403 ] );
        }
        if ( $key === "locations" ) {
            return self::remove_location_from_group( $group_id, $value );
        } elseif ( $key === "members" ) {
            return self::remove_member_from_group( $group_id, $value );
        } elseif ( $key === "people_groups" ) {
            return self::remove_people_group_from_group( $group_id, $value );
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
     * @param int $contact_id
     *
     * @return array|null|object|\WP_Error
     */
    public static function get_activity( int $contact_id )
    {
        return self::get_post_activity( 'groups', $contact_id );
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
     * @param int   $post_id
     * @param int   $user_id
     * @param array $meta
     *
     * @return false|int|WP_Error
     */
    public static function add_shared_on_group( int $post_id, int $user_id, $meta = null )
    {
        return self::add_shared( 'groups', $post_id, $user_id, $meta );
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

        if ( ! isset( $fields ["title"] ) ) {
            return new WP_Error( __FUNCTION__, __( "Group needs a title" ), [ 'fields' => $fields ] );
        }

        $post = [
            "post_title" => $fields["title"],
            "post_type" => "groups",
            "post_status" => "publish",
            "meta_input" => [
                "group_status" => "no_value",
                "assigned_to" => sprintf( "user-%d", get_current_user_id() ),
            ],
        ];

        $post_id = wp_insert_post( $post );
        return $post_id;
    }

}
