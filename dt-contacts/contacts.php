<?php
/**
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
 */
class Disciple_Tools_Contacts extends Disciple_Tools_Posts
{
    public static $contact_fields;
    public static $channel_list;
    public static $address_types;
    public static $contact_connection_types;

    /**
     * Disciple_Tools_Contacts constructor.
     */
    public function __construct()
    {
        add_action(
            'init',
            function() {
                self::$contact_fields = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings();
                self::$channel_list = Disciple_Tools_Contact_Post_Type::instance()->get_channels_list();
                self::$address_types = dt_address_metabox()->get_address_type_list( "contacts" );
                self::$contact_connection_types = [
                    "locations",
                    "groups",
                    "people_groups",
                    "baptized_by",
                    "baptized",
                    "coached_by",
                    "coaching",
                    "subassigned"
                ];
            }
        );
        add_action( "dt_contact_created", [ $this, "check_for_duplicates" ], 10, 2 );
        add_action( "dt_contact_updated", [ $this, "check_for_duplicates" ], 10, 2 );
        parent::__construct();
    }

    /**
     * Helper method for creating a WP_Query with pagination and ordering
     * separated into a separate argument for validation.
     * These two statements are equivalent in this example:
     * $query = self::query_with_pagination( [ "post_type" => "contacts" ], [ "orderby" => "ID" ] );
     * // equivalent to:
     * $query = new WP_Query( [ "post_type" => "contacts", "orderby" => "ID" ] );
     * The second argument, $query_pagination_args, may only contain keys
     * related to ordering and pagination, if it doesn't, this method will
     * return a WP_Error instance. This is useful in case you want to allow a
     * caller to modify pagination and ordering, but not anything else, in
     * order to keep permission checking valid. If $query_pagination_args is
     * specified with at least one value, then all pagination-related keys in
     * the first argument are ignored.
     *
     * @param array $query_args
     * @param array $query_pagination_args
     *
     * @return WP_Query | WP_Error
     */
    private static function query_with_pagination( array $query_args, array $query_pagination_args )
    {
        $allowed_keys = [
            'order',
            'orderby',
            'nopaging',
            'posts_per_page',
            'posts_per_archive_page',
            'offset',
            'paged',
            'page',
            'ignore_sticky_posts',
        ];
        $error = new WP_Error();
        foreach ( $query_pagination_args as $key => $value ) {
            if ( !in_array( $key, $allowed_keys ) ) {
                $error->add( __FUNCTION__, sprintf( __( "Key %s was an unexpected pagination key" ), $key ) );
            }
        }
        if ( count( $error->errors ) ) {
            return $error;
        }
        if ( count( $query_pagination_args ) ) {
            foreach ( $allowed_keys as $pagination_key ) {
                unset( $query_args[ $pagination_key ] );
            }
        }

        return new WP_Query( array_merge( $query_args, $query_pagination_args ) );
    }

    /**
     * @return mixed
     */
    public static function get_contact_fields()
    {
        return self::$contact_fields;
    }

    /**
     * @return mixed
     */
    public static function get_channel_list()
    {
        return self::$channel_list;
    }



    public function get_field_details( $field, $contact_id ){
        if ( $field === "title" ){
            return [
                "type" => "text",
                "name" => __( "Name", 'disciple_tools' ),
            ];
        }
        if ( strpos( $field, "contact_" ) === 0 ){
            $channel = explode( '_', $field );
            if ( isset( $channel[1] ) && self::$channel_list[ $channel[1] ] ){
                return [
                    "type" => "contact_method",
                    "name" => self::$channel_list[ $channel[1] ]["label"],
                    "channel" => $channel[1]
                ];
            }
        }
        if ( in_array( $field, self::$contact_connection_types ) ){
            return [
                "type" => "connection",
                "name" => $field
            ];
        }

        if ( $contact_id ){
            $contact_fields = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings( isset( $contact_id ), $contact_id );
        } else {
            $contact_fields = self::$contact_fields;
        }
        if ( isset( $contact_fields[$field] ) ){
            return $contact_fields[ $field ];
        }

        return [
            "type" => "unknown",
            "name" => "unknown"
        ];
    }

    /**
     * Create a new Contact
     *
     * @param  array     $fields , the new contact's data
     * @param  bool|true $check_permissions
     *
     * @access private
     * @since  0.1.0
     * @return int | WP_Error
     */
    public static function create_contact( array $fields = [], $check_permissions = true )
    {
        if ( $check_permissions && !current_user_can( 'create_contacts' ) ) {
            return new WP_Error( __FUNCTION__, __( "You may not publish a contact" ), [ 'status' => 403 ] );
        }
        $initial_fields = $fields;

        //required fields
        if ( !isset( $fields["title"] ) ) {
            return new WP_Error( __FUNCTION__, __( "Contact needs a title" ), [ 'fields' => $fields ] );
        }

        //make sure the assigned to is in the right format (user-1)
        if ( isset( $fields["assigned_to"] ) ) {
            if ( filter_var( $fields["assigned_to"], FILTER_VALIDATE_EMAIL ) ){
                $user = get_user_by( "email", $fields["assigned_to"] );
                if ( $user ) {
                    $fields["assigned_to"] = $user->ID;
                } else {
                    return new WP_Error( __FUNCTION__, __( "Unrecognized user" ), $fields["assigned_to"] );
                }
            }
            if ( is_numeric( $fields["assigned_to"] ) ||
                 strpos( $fields["assigned_to"], "user" ) === false ){
                $fields["assigned_to"] = "user-" . $fields["assigned_to"];
            }
        }

        $initial_comment = null;
        if ( isset( $fields["initial_comment"] ) ) {
            $initial_comment = $fields["initial_comment"];
            unset( $fields["initial_comment"] );
        }
        $notes = null;
        if ( isset( $fields["notes"] ) && is_array( $fields["notes"] ) ) {
            $notes = $fields["notes"];
            unset( $fields["notes"] );
        }

        $bad_fields = self::check_for_invalid_fields( $fields );
        if ( !empty( $bad_fields ) ) {
            return new WP_Error( __FUNCTION__, __( "These fields do not exist" ), [ 'bad_fields' => $bad_fields ] );
        }

        $current_roles = wp_get_current_user()->roles;

        $defaults = [
            "seeker_path"    => "none",
            "type" => "media",
            "last_modified" => time(),
        ];
        if ( get_current_user_id() ) {
            $defaults["assigned_to"] = sprintf( "user-%d", get_current_user_id() );
        } else {
            $base_id = dt_get_base_user( true );
            if ( is_wp_error( $base_id ) ) { // if default editor does not exist, get available administrator
                $users = get_users( [ 'role' => 'administrator' ] );
                if ( count( $users ) > 0 ) {
                    foreach ( $users as $user ) {
                        $base_id = $user->ID;
                    }
                }
            }
            $defaults["assigned_to"] = sprintf( "user-%d", $base_id );
        }

        if (in_array( "dispatcher", $current_roles, true ) || in_array( "marketer", $current_roles, true )) {
            $defaults["overall_status"] = "unassigned";
        } else if (in_array( "multiplier", $current_roles, true ) ) {
            $defaults["overall_status"] = "active";
        } else {
            $defaults["overall_status"] = "unassigned";
        }

        $fields = array_merge( $defaults, $fields );

        $title = $fields["title"];
        unset( $fields["title"] );

        $contact_methods_and_connections = [];
        $multi_select_fields = [];
        foreach ( $fields as $field_key => $field_value ){
            if ( self::is_key_contact_method_or_connection( $field_key )){
                $contact_methods_and_connections[$field_key] = $field_value;
                unset( $fields[$field_key] );
            }
            if ( isset( self::$contact_fields[$field_key] ) &&
                 self::$contact_fields[$field_key]["type"] === "multi_select" ){
                $multi_select_fields[$field_key] = $field_value;
                unset( $fields[$field_key] );
            }
        }

        $post = [
            "post_title"  => $title,
            'post_type'   => "contacts",
            "post_status" => 'publish',
            "meta_input"  => $fields,
        ];


        $post_id = wp_insert_post( $post );


        if ( isset( $fields["assigned_to"] )) {
            $user_id = explode( '-', $fields["assigned_to"] )[1];
            if ( $user_id ) {
                self::add_shared( "contacts", $post_id, $user_id, null, false, false );
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

        $potential_error = self::parse_multi_select_fields( $post_id, $multi_select_fields, null );
        if ( is_wp_error( $potential_error )){
            return $potential_error;
        }

        if ( $initial_comment ) {
            $potential_error = self::add_comment( $post_id, $initial_comment, false );
            if ( is_wp_error( $potential_error ) ) {
                return $potential_error;
            }
        }

        if ( $notes ) {
            if ( ! is_array( $notes ) ) {
                return new WP_Error( 'notes_not_array', 'Notes must be an array' );
            }
            $error = new WP_Error();
            foreach ( $notes as $note ) {
                $potential_error = self::add_comment( $post_id, $note, false );
                if ( is_wp_error( $potential_error ) ) {
                    $error->add( 'comment_fail', $potential_error->get_error_message() );
                }
            }
            if ( count( $error->get_error_messages() ) > 0 ) {
                return $error;
            }
        }

        //hook for signaling that a contact has been created and the initial fields
        if ( !is_wp_error( $post_id )){
            do_action( "dt_contact_created", $post_id, $initial_fields );
        }

        return $post_id;
    }

    private static function is_key_contact_method_or_connection( $key ) {
        $channel_keys = [];
        foreach ( self::$channel_list as $channel_key => $channel_value ) {
            $channel_keys[] = "contact_" . $channel_key;
        }
        return in_array( $key, self::$contact_connection_types ) || in_array( $key, $channel_keys );
    }

    /**
     * Make sure there are no extra or misspelled fields
     * Make sure the field values are the correct format
     *
     * @param          $fields  , the contact meta fields
     * @param int|null $post_id , the id of the contact
     *
     * @access private
     * @since  0.1.0
     * @return array
     */
    private static function check_for_invalid_fields( $fields, int $post_id = null )
    {
        $bad_fields = [];
        $contact_fields = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings( isset( $post_id ), $post_id );
        $contact_fields['title'] = "";
        foreach ( $fields as $field => $value ) {
            if ( !isset( $contact_fields[ $field ] ) && !self::is_key_contact_method_or_connection( $field ) ) {
                $bad_fields[] = $field;
            }
        }

        return $bad_fields;
    }

    private static function parse_multi_select_fields( $contact_id, $fields, $existing_contact = null ){
        foreach ( $fields as $field_key => $field ){
            if ( isset( self::$contact_fields[$field_key] ) && self::$contact_fields[$field_key]["type"] === "multi_select" ){
                if ( !isset( $field["values"] )){
                    return new WP_Error( __FUNCTION__, __( "missing values field on:" ) . " " . $field_key );
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
                        return new WP_Error( __FUNCTION__, __( "Something wrong on field:" ) . " " . $field_key );
                    }
                }
            }
        }
        return $fields;
    }

    private static function parse_contact_methods( $contact_id, $fields, $existing_contact = null ){
        $contact_details_field_keys = array_keys( self::$channel_list );
        // update contact details (phone, facebook, etc)
        foreach ( $contact_details_field_keys as $channel_key ){
            $details_key = "contact_" . $channel_key;
            $values = [];
            if ( isset( $fields[$details_key] ) && isset( $fields[$details_key]["values"] ) ){
                $values = $fields[$details_key]["values"];
            } else if ( isset( $fields[$details_key] ) && is_array( $fields[$details_key] ) ) {
                $values = $fields[$details_key];
            }
            if ( $existing_contact && isset( $fields[$details_key] ) &&
                 isset( $fields[$details_key]["force_values"] ) &&
                 $fields[$details_key]["force_values"] === true ){
                foreach ( $existing_contact[$details_key] as $contact_value ){
                    $potential_error = self::delete_contact_field( $contact_id, $contact_value["key"], false );
                    if ( is_wp_error( $potential_error ) ){
                        return $potential_error;
                    }
                }
            }
            foreach ( $values as $field ){
                if ( isset( $field["delete"] ) && $field["delete"] == true){
                    if ( !isset( $field["key"] )){
                        return new WP_Error( __FUNCTION__, __( "missing key on:" ) . " " . $details_key );
                    }
                    //delete field
                    $potential_error = self::delete_contact_field( $contact_id, $field["key"] );
                } else if ( isset( $field["key"] ) ){
                    //update field
                    $potential_error = self::update_contact_method( $contact_id, $field["key"], $field, false );
                } else if ( isset( $field["value"] ) ) {
                    $field["key"] = "new-".$channel_key;
                    //create field
                    $potential_error = self::add_contact_method( $contact_id, $field["key"], $field["value"], $field, false );

                } else {
                    return new WP_Error( __FUNCTION__, __( "Is not an array or missing value on:" ) . " " . $details_key );
                }
                if ( isset( $potential_error ) && is_wp_error( $potential_error ) ) {
                    return $potential_error;
                }
            }
        }
        return $fields;
    }



    private static function parse_connections( $contact_id, $fields, $existing_contact){
        //update connections (groups, locations, etc)
        foreach ( self::$contact_connection_types as $connection_type ){
            if ( isset( $fields[$connection_type] ) ){
                if ( !isset( $fields[$connection_type]["values"] )){
                    return new WP_Error( __FUNCTION__, __( "Missing values field on connection:" ) . " " . $connection_type, [ 'status' => 500 ] );
                }
                $existing_connections = [];
                if ( isset( $existing_contact[$connection_type] ) ){
                    foreach ( $existing_contact[$connection_type] as $connection){
                        $existing_connections[] = $connection->ID;
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
                                $corresponding_contact = self::get_contact_for_user( $user );
                                if ( $corresponding_contact ){
                                    $connection_value["value"] = $corresponding_contact;
                                }
                            }
                        } else {
                            $post_types = self::$contact_connection_types;
                            $post_types[] = "contacts";
                            $post = self::get_post_by_title_cached( $connection_value["value"], OBJECT, $post_types, $connection_type );
                            if ( $post && !is_wp_error( $post ) ){
                                $connection_value["value"] = $post->ID;
                            }
                        }
                    }

                    if ( isset( $connection_value["value"] ) && is_numeric( $connection_value["value"] )){
                        if ( isset( $connection_value["delete"] ) && $connection_value["delete"] === true ){
                            if ( in_array( $connection_value["value"], $existing_connections )){
                                $potential_error = self::remove_contact_connection( $contact_id, $connection_type, $connection_value["value"], false );
                                if ( is_wp_error( $potential_error ) ) {
                                    return $potential_error;
                                }
                            }
                        } else if ( !empty( $connection_value["value"] )) {
                            $new_connections[] = $connection_value["value"];
                            if ( !in_array( $connection_value["value"], $existing_connections )){
                                $potential_error = self::add_contact_detail( $contact_id, $connection_type, $connection_value["value"], false );
                                $existing_connections[] = $connection_value["value"];
                                if ( is_wp_error( $potential_error ) ) {
                                    return $potential_error;
                                }
                                $fields["added_fields"][$connection_type] = $potential_error;
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
                            $potential_error = self::remove_contact_connection( $contact_id, $connection_type, $connection_value, false );
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
     * Update an existing Contact
     *
     * @param  int|null  $contact_id , the post id for the contact
     * @param  array     $fields     , the meta fields
     * @param  bool|null $check_permissions
     *
     * @access public
     * @since  0.1.0
     * @return int | WP_Error of contact ID
     */
    public static function update_contact( int $contact_id, array $fields, $check_permissions = true )
    {

        if ( $check_permissions && !self::can_update( 'contacts', $contact_id ) ) {
            return new WP_Error( __FUNCTION__, __( "You do not have permission for this" ), [ 'status' => 403 ] );
        }
        $initial_fields = $fields;

        $post = get_post( $contact_id );
        if ( isset( $fields['id'] ) ) {
            unset( $fields['id'] );
        }

        if ( !$post ) {
            return new WP_Error( __FUNCTION__, __( "Contact does not exist" ) );
        }


        // don't try to update fields that don't exist
        $bad_fields = self::check_for_invalid_fields( $fields, $contact_id );
        if ( !empty( $bad_fields ) ) {
            return new WP_Error( __FUNCTION__, __( "These fields do not exist" ), [ 'bad_fields' => $bad_fields ] );
        }
        $existing_contact = self::get_contact( $contact_id, false );

        if ( isset( $fields['title'] ) ) {
            wp_update_post( [
                'ID' => $contact_id,
                'post_title' => $fields['title']
            ] );
            dt_activity_insert( [
                'action'            => 'field_update',
                'object_type'       => "contacts",
                'object_subtype'    => 'title',
                'object_id'         => $contact_id,
                'object_name'       => $fields['title'],
                'meta_key'          => 'title',
                'meta_value'        => $fields['title'],
                'old_value'         => $existing_contact['title'],
            ] );
        }

        $potential_error = self::parse_contact_methods( $contact_id, $fields, $existing_contact );
        if ( is_wp_error( $potential_error )){
            return $potential_error;
        }

        $potential_error = self::parse_connections( $contact_id, $fields, $existing_contact );
        if ( is_wp_error( $potential_error )){
            return $potential_error;
        }

        $potential_error = self::parse_multi_select_fields( $contact_id, $fields );
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
            if ( current_user_can( "assign_any_contacts" ) ) {
                $fields["overall_status"] = 'assigned';
            }
            $user_id = explode( '-', $fields["assigned_to"] )[1];
            if ( $user_id ){
                self::add_shared( "contacts", $contact_id, $user_id, null, false );
            }
            $fields['accepted'] = 'no';
        }

        if ( isset( $fields["reason_unassignable"] ) ){
            $fields["overall_status"] = 'unassignable';
        }

        if ( isset( $fields["seeker_path"] ) ){
            self::update_quick_action_buttons( $contact_id, $fields["seeker_path"] );
        }

        foreach ( $fields as $field_key => $value ){
            if ( strpos( $field_key, "quick_button" ) !== false ){
                self::handle_quick_action_button_event( $contact_id, [ $field_key => $value ] );
            }
        }


        $fields["last_modified"] = time(); //make sure the last modified field is updated.
        foreach ( $fields as $field_id => $value ) {
            if ( !self::is_key_contact_method_or_connection( $field_id ) ) {
                // Boolean contact field are stored as yes/no
                if ( $value === true ) {
                    $value = "yes";
                } elseif ( $value === false ) {
                    $value = "no";
                }

                $field_type = self::$contact_fields[$field_id]["type"] ?? '';
                //we handle multi_select above.
                if ( $field_type && $field_type !== "multi_select" ){
                    update_post_meta( $contact_id, $field_id, $value );
                }
            }
        }

        if ( !isset( $fields["requires_update"] )){
            self::check_requires_update( $contact_id );
        }
//        @todo permission?
        $contact = self::get_contact( $contact_id, false );
        if (isset( $fields["added_fields"] )){
            $contact["added_fields"] = $fields["added_fields"];
        }

        //hook for signaling that a contact has been updated and which keys have been changed
        if ( !is_wp_error( $contact )){
            do_action( "dt_contact_updated", $contact_id, $initial_fields, $contact );
        }

        return $contact;
    }

    //check to see if the contact is marked as needing an update
    //if yes: mark as updated
    private static function check_requires_update( $contact_id ){
        if ( get_current_user_id() ){
            $requires_update = get_post_meta( $contact_id, "requires_update", true );
            if ( $requires_update == "yes" ){
                update_post_meta( $contact_id, "requires_update", "no" );
            }
        }
    }

    public static function get_contact_for_user( $user ){
//        if ( $user->has_cap( 'access_contacts' ) ) {
        $args = [
            'post_type'  => 'contacts',
            'relation'   => 'AND',
            'meta_query' => [
                [
                    'key' => "corresponds_to_user",
                    "value" => $user->ID
                ],
                [
                    'key' => "type",
                    "value" => "user"
                ],
            ],
        ];
        $contacts = new WP_Query( $args );
        if ( isset( $contacts->post->ID ) ){
            return $contacts->post->ID;
        } else {
            return null;
        }
    }

    /**
     * @param $contact_id
     * @param $location_id
     *
     * @return mixed
     */
    public static function add_location_to_contact( $contact_id, $location_id )
    {
        return p2p_type( 'contacts_to_locations' )->connect(
            $location_id, $contact_id,
            [ 'date' => current_time( 'mysql' ) ]
        );
    }

    /**
     * @param $contact_id
     * @param $group_id
     *
     * @return mixed
     */
    public static function add_group_to_contact( $contact_id, $group_id )
    {
        // share the group with the owner of the contact.
        $assigned_to = get_post_meta( $contact_id, "assigned_to", true );
        if ( $assigned_to && strpos( $assigned_to, "-" ) !== false ){
            $user_id = explode( "-", $assigned_to )[1];
            if ( $user_id ){
                self::add_shared( "groups", $group_id, $user_id, null, false, false );
            }
        }
        return p2p_type( 'contacts_to_groups' )->connect(
            $group_id, $contact_id,
            [ 'date' => current_time( 'mysql' ) ]
        );
    }

    /**
     * @param $contact_id
     * @param $people_group_id
     *
     * @return mixed
     */
    public static function add_people_group_to_contact( $contact_id, $people_group_id )
    {
        return p2p_type( 'contacts_to_peoplegroups' )->connect(
            $people_group_id, $contact_id,
            [ 'date' => current_time( 'mysql' ) ]
        );
    }

    /**
     * @param $contact_id
     * @param $baptized_by
     *
     * @return mixed
     */
    public static function add_baptized_by_to_contact( $contact_id, $baptized_by )
    {
        return p2p_type( 'baptizer_to_baptized' )->connect(
            $contact_id, $baptized_by,
            [ 'date' => current_time( 'mysql' ) ]
        );
    }

    /**
     * @param $contact_id
     * @param $baptized
     *
     * @return mixed
     */
    public static function add_baptized_to_contact( $contact_id, $baptized )
    {
        return p2p_type( 'baptizer_to_baptized' )->connect(
            $baptized, $contact_id,
            [ 'date' => current_time( 'mysql' ) ]
        );
    }

    /**
     * @param $contact_id
     * @param $coached_by
     *
     * @return mixed
     */
    public static function add_coached_by_to_contact( $contact_id, $coached_by )
    {
        return p2p_type( 'contacts_to_contacts' )->connect(
            $contact_id, $coached_by,
            [ 'date' => current_time( 'mysql' ) ]
        );
    }

    /**
     * @param $contact_id
     * @param $coaching
     *
     * @return mixed
     */
    public static function add_coaching_to_contact( $contact_id, $coaching )
    {
        return p2p_type( 'contacts_to_contacts' )->connect(
            $coaching, $contact_id,
            [ 'date' => current_time( 'mysql' ) ]
        );
    }

    /**
     * @param $contact_id
     * @param $subassigned
     *
     * @return mixed
     */
    public static function add_subassigned_to_contact( $contact_id, $subassigned )
    {

        $user_id = get_post_meta( $subassigned, "corresponds_to_user", true );
        if ( $user_id ){
            self::add_shared_on_contact( $contact_id, $user_id, null, false, false );
        }

        return p2p_type( 'contacts_to_subassigned' )->connect(
            $subassigned, $contact_id,
            [ 'date' => current_time( 'mysql' ) ]
        );
    }

    /**
     * @param $contact_id
     * @param $location_id
     *
     * @return mixed
     */
    public static function remove_location_from_contact( $contact_id, $location_id )
    {
        return p2p_type( 'contacts_to_locations' )->disconnect( $location_id, $contact_id );
    }

    /**
     * @param $contact_id
     * @param $people_group_id
     *
     * @return mixed
     */
    public static function remove_group_from_contact( $contact_id, $people_group_id )
    {
        return p2p_type( 'contacts_to_groups' )->disconnect( $people_group_id, $contact_id );
    }

    /**
     * @param $contact_id
     * @param $group_id
     *
     * @return mixed
     */
    public static function remove_people_group_from_contact( $contact_id, $group_id )
    {
        return p2p_type( 'contacts_to_peoplegroups' )->disconnect( $group_id, $contact_id );
    }

    /**
     * @param $contact_id
     * @param $baptized_by
     *
     * @return mixed
     */
    public static function remove_baptized_by_from_contact( $contact_id, $baptized_by )
    {
        return p2p_type( 'baptizer_to_baptized' )->disconnect( $contact_id, $baptized_by );
    }

    /**
     * @param $contact_id
     * @param $baptized
     *
     * @return mixed
     */
    public static function remove_baptized_from_contact( $contact_id, $baptized )
    {
        return p2p_type( 'baptizer_to_baptized' )->disconnect( $baptized, $contact_id );
    }

    /**
     * @param $contact_id
     * @param $coached_by
     *
     * @return mixed
     */
    public static function remove_coached_by_from_contact( $contact_id, $coached_by )
    {
        return p2p_type( 'contacts_to_contacts' )->disconnect( $contact_id, $coached_by );
    }

    /**
     * @param $contact_id
     * @param $coaching
     *
     * @return mixed
     */
    public static function remove_coaching_from_contact( $contact_id, $coaching )
    {
        return p2p_type( 'contacts_to_contacts' )->disconnect( $coaching, $contact_id );
    }

    /**
     * @param $contact_id
     * @param $subassigned
     *
     * @return mixed
     */
    public static function remove_subassigned_from_contact( $contact_id, $subassigned )
    {
        return p2p_type( 'contacts_to_subassigned' )->disconnect( $subassigned, $contact_id );
    }

    /**
     * @param int       $contact_id
     * @param string    $key
     * @param string    $value
     * @param bool      $check_permissions
     *
     * @return array|mixed|null|string|\WP_Error|\WP_Post
     */
    public static function add_contact_detail( int $contact_id, string $key, string $value, bool $check_permissions )
    {
        if ( $check_permissions && !self::can_update( 'contacts', $contact_id ) ) {
            return new WP_Error( __FUNCTION__, __( "You do not have permission for this" ), [ 'status' => 403 ] );
        }
        if ( strpos( $key, "new-" ) === 0 ) {
            $type = explode( '-', $key )[1];

            $new_meta_key = '';
            if ( isset( self::$channel_list[ $type ] ) ) {
                //check if this is a new field and is in the channel list
                $new_meta_key = Disciple_Tools_Contact_Post_Type::instance()->create_channel_metakey( $type, "contact" );
            }
            update_post_meta( $contact_id, $new_meta_key, $value );
            $details = [ "verified" => false ];
            update_post_meta( $contact_id, $new_meta_key . "_details", $details );

            return $new_meta_key;
        }
        $connect = null;
        if ( $key === "locations" ) {
            $connect = self::add_location_to_contact( $contact_id, $value );
        } elseif ( $key === "groups" ) {
            $connect = self::add_group_to_contact( $contact_id, $value );
        } elseif ( $key === "people_groups" ) {
            $connect = self::add_people_group_to_contact( $contact_id, $value );
        } else if ( $key === "baptized_by" ) {
            $connect = self::add_baptized_by_to_contact( $contact_id, $value );
        } elseif ( $key === "baptized" ) {
            $connect = self::add_baptized_to_contact( $contact_id, $value );
        } elseif ( $key === "coached_by" ) {
            $connect = self::add_coached_by_to_contact( $contact_id, $value );
        } elseif ( $key === "coaching" ) {
            $connect = self::add_coaching_to_contact( $contact_id, $value );
        } elseif ( $key === "subassigned" ){
            $connect = self::add_subassigned_to_contact( $contact_id, $value );
        }
        if ( is_wp_error( $connect ) ) {
            return $connect;
        }
        if ( $connect ) {
            $connection = get_post( $value );
            $connection->permalink = get_permalink( $value );

            return $connection;
        }

        return new WP_Error( "add_contact_detail", "Field not recognized", [ "status" => 400 ] );
    }

    public static function add_contact_method( int $contact_id, string $key, string $value, array $field, bool $check_permissions ) {
        if ( $check_permissions && ! self::can_update( 'contacts', $contact_id ) ) {
            return new WP_Error( __FUNCTION__, __( "You do not have permission for this" ), [ 'status' => 403 ] );
        }
        if ( strpos( $key, "new-" ) === 0 ) {
            $type = explode( '-', $key )[1];

            $new_meta_key = '';
            if ( isset( self::$channel_list[ $type ] ) ) {
                //check if this is a new field and is in the channel list
                $new_meta_key = Disciple_Tools_Contact_Post_Type::instance()->create_channel_metakey( $type, "contact" );
            }
            update_post_meta( $contact_id, $new_meta_key, $value );
            $details = [ "verified" => false ];
            foreach ( $field as $key => $value ){
                if ( $key != "value" && $key != "key" ){
                    $details[$key] = $value;
                }
            }
            update_post_meta( $contact_id, $new_meta_key . "_details", $details );

            return $new_meta_key;
        }
    }


    /**
     * @param int    $contact_id
     * @param string $key
     * @param array  $values
     * @param bool   $check_permissions
     *
     * @return int|\WP_Error
     */
    public static function update_contact_method( int $contact_id, string $key, array $values, bool $check_permissions )
    {
        if ( $check_permissions && !self::can_update( 'contacts', $contact_id ) ) {
            return new WP_Error( __FUNCTION__, __( "You do not have permission for this" ), [ 'status' => 403 ] );
        }
        if ( ( strpos( $key, "contact_" ) === 0 || strpos( $key, "address_" ) === 0 ) &&
            strpos( $key, "_details" ) === false
        ) {
            $old_value = get_post_meta( $contact_id, $key, true );
            //check if it is different to avoid setting saving activity
            if ( isset( $values["value"] ) && $old_value != $values["value"] ){
                update_post_meta( $contact_id, $key, $values["value"] );
            }
            unset( $values["value"] );
            unset( $values["key"] );

            $details_key = $key . "_details";
            $old_details = get_post_meta( $contact_id, $details_key, true );
            $details = isset( $old_details ) ? $old_details : [];
            $new_value = false;
            foreach ( $values as $detail_key => $detail_value ) {
                if ( !isset( $details[$detail_key] ) || $details[$detail_key] !== $detail_value){
                    $new_value = true;
                }
                $details[ $detail_key ] = $detail_value;
            }
            if ($new_value){
                update_post_meta( $contact_id, $details_key, $details );
            }
        }

        return $contact_id;
    }

    /**
     * @param int     $contact_id
     * @param string  $key
     * @param string  $value
     * @param bool    $check_permissions
     *
     * @return bool|mixed|\WP_Error
     */
    public static function remove_contact_connection( int $contact_id, string $key, string $value, bool $check_permissions )
    {
        if ( $check_permissions && !self::can_update( 'contacts', $contact_id ) ) {
            return new WP_Error( __FUNCTION__, __( "You do not have permission for this" ), [ 'status' => 403 ] );
        }
        if ( $key === "locations" ) {
            return self::remove_location_from_contact( $contact_id, $value );
        } elseif ( $key === "groups" ) {
            return self::remove_group_from_contact( $contact_id, $value );
        } elseif ( $key === "baptized_by" ) {
            return self::remove_baptized_by_from_contact( $contact_id, $value );
        } elseif ( $key === "baptized" ) {
            return self::remove_baptized_from_contact( $contact_id, $value );
        } elseif ( $key === "coached_by" ) {
            return self::remove_coached_by_from_contact( $contact_id, $value );
        } elseif ( $key === "coaching" ) {
            return self::remove_coaching_from_contact( $contact_id, $value );
        } elseif ( $key === "people_groups" ) {
            return self::remove_people_group_from_contact( $contact_id, $value );
        } elseif ( $key === "subassigned" ) {
            return self::remove_subassigned_from_contact( $contact_id, $value );
        }

        return false;
    }

    /**
     * @param int    $contact_id
     * @param string $key
     *
     * @return bool|\WP_Error
     */
    public static function delete_contact_field( int $contact_id, string $key, $check_persmissions = true ){
        if ( $check_persmissions && !self::can_update( 'contacts', $contact_id )){
            return new WP_Error( __FUNCTION__, __( "You do not have permission for this" ), [ 'status' => 401 ] );
        }
        delete_post_meta( $contact_id, $key .'_details' );
        return delete_post_meta( $contact_id, $key );
    }

    /**
     * Get a single contact
     *
     * @param int  $contact_id , the contact post_id
     * @param bool $check_permissions
     *
     * @access public
     * @since  0.1.0
     * @return array| WP_Error, On success: the contact, else: the error message
     */
    public static function get_contact( int $contact_id, $check_permissions = true )
    {
        if ( $check_permissions && !self::can_view( 'contacts', $contact_id ) ) {
            return new WP_Error( __FUNCTION__, __( "No permissions to read contact" ), [ 'status' => 403 ] );
        }

        $contact = get_post( $contact_id );
        if ( $contact ) {
            $fields = [];

            $locations = get_posts(
                [
                    'connected_type'   => 'contacts_to_locations',
                    'connected_items'  => $contact,
                    'nopaging'         => true,
                    'suppress_filters' => false,
                ]
            );
            foreach ( $locations as $l ) {
                $l->permalink = get_permalink( $l->ID );
            }
            $fields["locations"] = $locations;
            $groups = get_posts(
                [
                    'connected_type'   => 'contacts_to_groups',
                    'connected_items'  => $contact,
                    'nopaging'         => true,
                    'suppress_filters' => false,
                ]
            );
            foreach ( $groups as $g ) {
                $g->permalink = get_permalink( $g->ID );
            }
            $fields["groups"] = $groups;

            $people_groups = get_posts(
                [
                    'connected_type'   => 'contacts_to_peoplegroups',
                    'connected_items'  => $contact,
                    'nopaging'         => true,
                    'suppress_filters' => false,
                ]
            );
            foreach ( $people_groups as $g ) {
                $g->permalink = get_permalink( $g->ID );
            }
            $fields["people_groups"] = $people_groups;

            $baptized = get_posts(
                [
                    'connected_type'      => 'baptizer_to_baptized',
                    'connected_direction' => 'to',
                    'connected_items'     => $contact,
                    'nopaging'            => true,
                    'suppress_filters'    => false,
                ]
            );
            foreach ( $baptized as $b ) {
                $b->fields = p2p_get_meta( $b->p2p_id );
                $b->permalink = get_permalink( $b->ID );
            }
            $fields["baptized"] = $baptized;
            $baptized_by = get_posts(
                [
                    'connected_type'      => 'baptizer_to_baptized',
                    'connected_direction' => 'from',
                    'connected_items'     => $contact,
                    'nopaging'            => true,
                    'suppress_filters'    => false,
                ]
            );
            foreach ( $baptized_by as $b ) {
                $b->fields = p2p_get_meta( $b->p2p_id );
                $b->permalink = get_permalink( $b->ID );
            }
            $fields["baptized_by"] = $baptized_by;
            $coaching = get_posts(
                [
                    'connected_type'      => 'contacts_to_contacts',
                    'connected_direction' => 'to',
                    'connected_items'     => $contact,
                    'nopaging'            => true,
                    'suppress_filters'    => false,
                ]
            );
            foreach ( $coaching as $c ) {
                $c->permalink = get_permalink( $c->ID );
            }
            $fields["coaching"] = $coaching;
            $coached_by = get_posts(
                [
                    'connected_type'      => 'contacts_to_contacts',
                    'connected_direction' => 'from',
                    'connected_items'     => $contact,
                    'nopaging'            => true,
                    'suppress_filters'    => false,
                ]
            );
            foreach ( $coached_by as $c ) {
                $c->permalink = get_permalink( $c->ID );
            }
            $fields["coached_by"] = $coached_by;
            $subassigned = get_posts(
                [
                    'connected_type'      => 'contacts_to_subassigned',
                    'connected_direction' => 'to',
                    'connected_items'     => $contact,
                    'nopaging'            => true,
                    'suppress_filters'    => false,
                ]
            );
            foreach ( $subassigned as $c ) {
                $c->permalink = get_permalink( $c->ID );
            }
            $fields["subassigned"] = $subassigned;

            $meta_fields = get_post_custom( $contact_id );
            foreach ( $meta_fields as $key => $value ) {
                //if is contact details and is in a channel
                if ( strpos( $key, "contact_" ) === 0 && isset( self::$channel_list[ explode( '_', $key )[1] ] ) ) {
                    if ( strpos( $key, "details" ) === false ) {
                        $type = explode( '_', $key )[1];
                        $fields[ "contact_" . $type ][] = self::format_contact_details( $meta_fields, $type, $key, $value[0] );
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
                            $details["type_label"] = self::$address_types[ $details["type"] ]["label"];
                        }
                        $fields["address"][] = $details;
                    }
                } elseif ( isset( self::$contact_fields[ $key ] ) && self::$contact_fields[ $key ]["type"] == "key_select" ) {
                    $label = self::$contact_fields[ $key ]["default"][ $value[0] ] ?? current( self::$contact_fields[ $key ]["default"] );
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
                } else if ( isset( self::$contact_fields[ $key ] ) && self::$contact_fields[ $key ]['type'] === 'multi_select' ){
                    $fields[ $key ] = $value;
                } else if ( isset( self::$contact_fields[ $key ] ) && self::$contact_fields[ $key ]['type'] === 'array' ){
                    $fields[ $key ] = maybe_unserialize( $value[0] );
                } else {
                    $fields[ $key ] = $value[0];
                }
            }

            $comments = get_comments( [ 'post_id' => $contact_id ] );
            $fields["comments"] = $comments;
            $fields["ID"] = $contact->ID;
            $fields["title"] = $contact->post_title;

            return $fields;
        } else {
            return new WP_Error( __FUNCTION__, __( "No contact found with ID" ), [ 'contact_id' => $contact_id ] );
        }
    }

    /**
     * @param $meta_fields
     * @param $type
     * @param $key
     * @param $value
     *
     * @return array|mixed
     */
    public static function format_contact_details( $meta_fields, $type, $key, $value )
    {

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
            $details["type_label"] = self::$channel_list[ $type ]["types"][ $details["type"] ]["label"];
        }

        return $details;
    }

    /**
     * @param $base_contact
     * @param $duplicate_contact
     */
    public static function merge_contacts( $base_contact, $duplicate_contact )
    {

    }

    /**
     * @param $contact_id
     *
     * @return array|null|object
     */
    public static function get_activity( $contact_id )
    {
        $fields = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings( true, $contact_id );
        return self::get_post_activity( "contacts", $contact_id, $fields );
    }

    /**
     * @param $contact_id
     * @param $activity_id
     *
     * @return array|null|object
     */
    public static function get_single_activity( $contact_id, $activity_id )
    {
        $fields = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings( true, $contact_id );
        return self::get_post_single_activity( "contacts", $contact_id, $fields, $activity_id );
    }

    /**
     * @param $contact_id
     * @param $activity_id
     *
     * @return bool|int|WP_Error
     */
    public static function revert_activity( $contact_id, $activity_id ){
        if ( !self::can_update( 'contacts', $contact_id ) ) {
            return new WP_Error( __FUNCTION__, __( "You do not have permission for this" ), [ 'status' => 403 ] );
        }
        $activity = self::get_single_activity( $contact_id, $activity_id );
        if ( empty( $activity->old_value ) ){
            if ( strpos( $activity->meta_key, "quick_button_" ) !== false ){
                $activity->old_value = 0;
            }
        }
        update_post_meta( $contact_id, $activity->meta_key, $activity->old_value ?? "" );
        return self::get_contact( $contact_id );
    }


    /**
     * Get Contacts assigned to a user
     *
     * @param int   $user_id
     * @param bool  $check_permissions
     * @param array $query_pagination_args -Pass in pagination and ordering parameters if wanted.
     *
     * @access public
     * @since  0.1.0
     * @return WP_Query | WP_Error
     */
    public static function get_user_contacts( int $user_id, bool $check_permissions = true, array $query_pagination_args = [] )
    {
        if ( $check_permissions && !self::can_access( 'contacts' ) ) {
            return new WP_Error( __FUNCTION__, __( "You do not have access to these contacts" ), [ 'status' => 403 ] );
        }

        $query_args = [
            'post_type'  => 'contacts',
            'meta_key'   => 'assigned_to',
            'meta_value' => "user-$user_id",
            'orderby'    => 'ID',
            'nopaging'   => true,
        ];

        return self::query_with_pagination( $query_args, $query_pagination_args );
    }

    /**
     * Get Contacts viewable by a user
     *
     * @param int   $most_recent date of most recent update
     * @param bool  $check_permissions
     *
     * @access public
     * @since  0.1.0
     * @return array | WP_Error | WP_Query
     */
    public static function get_viewable_contacts( int $most_recent, bool $check_permissions = true )
    {
        if ( $check_permissions && !self::can_access( 'contacts' ) ) {
            return new WP_Error( __FUNCTION__, __( "You do not have access to these contacts" ), [ 'status' => 403 ] );
        }
        $current_user = wp_get_current_user();

        $query_args = [
            'post_type' => 'contacts',
//            'nopaging'  => true,
            'meta_query' => [
                'relation' => "AND",
                [
                    'relation' => "OR",
                    [
                        'relation' => "AND",
                        [
                            'key' => 'type',
                            'value' => "user",
                            'compare' => '!='
                        ],
                        [
                            'key' => 'type',
                            'value' => "partner",
                            'compare' => '!='
                        ],
                    ],
                    [
                        'key' => 'type',
                        'compare' => 'NOT EXISTS'
                    ]
                ],
                [
                    'key' => "last_modified",
                    'value' => $most_recent,
                    'compare' => '>'
                ]
            ],
            'orderby' => 'meta_value_num',
            'meta_key' => "last_modified",
            'order' => 'ASC',
            'posts_per_page' => 1000 // @codingStandardsIgnoreLine
        ];
        $contacts_shared_with_user = [];
        if ( !self::can_view_all( 'contacts' ) ) {
            $contacts_shared_with_user = self::get_posts_shared_with_user( 'contacts', $current_user->ID );

            $query_args['meta_key'] = 'assigned_to';
            $query_args['meta_value'] = "user-" . $current_user->ID;
        }
//        $queried_contacts = self::query_with_pagination( $query_args, $query_pagination_args );
        $queried_contacts = new WP_Query( $query_args );
        if ( is_wp_error( $queried_contacts ) ) {
            return $queried_contacts;
        }
        $contacts = $queried_contacts->posts;
        $contact_ids = array_map(
            function( $contact ) {
                return $contact->ID;
            },
            $contacts
        );
        //add shared contacts to the list avoiding duplicates
        foreach ( $contacts_shared_with_user as $shared ) {
            if ( !in_array( $shared->ID, $contact_ids ) ) {
                $contact_ids[] = $shared->ID;
                $contacts[] = $shared;
            }
        }
        $team_contacts = self::get_team_contacts( $current_user->ID, true, true, $most_recent );
        if ( isset( $team_contacts["contacts"] ) ){
            foreach ( $team_contacts["contacts"] as $team_contact ){
                if ( !in_array( $team_contact->ID, $contact_ids ) ) {
                    $team_contact->is_team_contact = true;
                    $contacts[] = $team_contact;
                }
            }
        }

        $delete_contacts = [];
        if ($most_recent){
            global $wpdb;
            $deleted_query = $wpdb->get_results( $wpdb->prepare(
                "SELECT object_id 
                FROM `$wpdb->dt_activity_log`
                WHERE 
                    ( `action` = 'deleted' || `action` = 'trashed' )
                    AND `object_subtype` = 'contacts'
                    AND hist_time > %d
                ", $most_recent
            ), ARRAY_A);
            foreach ( $deleted_query as $deleted ){
                $delete_contacts[] = $deleted["object_id"];
            }
        }


        return [
            "contacts" => $contacts,
            "total" => $queried_contacts->found_posts,
            "deleted" => $delete_contacts
        ];
    }

    /**
     * @param string $search_string
     *
     * @return array|\WP_Query
     */
    public static function get_viewable_contacts_compact( string $search_string )
    {
        return self::get_viewable_compact( 'contacts', $search_string );
    }


    public static function get_team_members(){
        global $wpdb;
        $user_connections = [];
        $user_connections['relation'] = 'OR';
        $members = [];
        $user_id = get_current_user_id();


        // First Query
        // Build arrays for current groups connected to user
        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT DISTINCT
                `$wpdb->term_relationships`.`term_taxonomy_id`
            FROM
                `$wpdb->term_relationships`
            INNER JOIN
                `$wpdb->term_taxonomy`
            ON
                `$wpdb->term_relationships`.`term_taxonomy_id` = `$wpdb->term_taxonomy`.`term_taxonomy_id`
            WHERE
                object_id  = %d
                AND taxonomy = %s
            ",
            $user_id,
            'user-group'
        ), ARRAY_A );

        // Loop
        foreach ( $results as $result ) {
            // create the meta query for the group
            $user_connections[] = [
                'key' => 'assigned_to',
                'value' => 'group-' . $result['term_taxonomy_id']
            ];

            // Second Query
            // query a member list for this group
            // build list of member ids who are part of the team
            $results2 = $wpdb->get_results( $wpdb->prepare(
                "SELECT
                    `$wpdb->term_relationships`.`object_id`
                FROM
                    `$wpdb->term_relationships`
                WHERE
                    `term_taxonomy_id` = %d
                ",
                $result['term_taxonomy_id']
            ), ARRAY_A );

            // Inner Loop
            foreach ( $results2 as $result2 ) {

                if ( $result2['object_id'] != $user_id ) {
                    $members[] = $result2['object_id'];
                }
            }
        }

        $members = array_unique( $members );
        return $members;
    }

    public static function search_viewable_contacts( array $query, bool $check_permissions = true){
        if ( $check_permissions && !self::can_access( 'contacts' ) ) {
            return new WP_Error( __FUNCTION__, __( "You do not have access to these contacts" ), [ 'status' => 403 ] );
        }
        global $wpdb;
        $current_user = wp_get_current_user();
//        $team_members = self::get_team_members();

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

        $bad_fields = self::check_for_invalid_fields( $query );
        if ( !empty( $bad_fields ) ) {
            return new WP_Error( __FUNCTION__, __( "These fields do not exist" ), [ 'bad_fields' => $bad_fields ] );
        }

        $inner_joins = "";
        $connections_sql = "";
        foreach ( $query as $query_key => $query_value ) {
            if ( in_array( $query_key, self::$contact_connection_types ) ) {
                if ( $query_key === "locations" ) {
                    $location_sql = "";
                    foreach ( $query_value as $location ) {
                        $l = get_post( $location );
                        if ( $l && $l->post_type === "locations" ){
                            $location_sql .= empty( $location_sql ) ? $l->ID : ( ",".$l->ID );
                        }
                    }
                    if ( !empty( $location_sql ) ){
                        $connections_sql .= "AND ( p2p_type = 'contacts_to_locations' AND p2p_to in (" . esc_sql( $location_sql ) .") )";
                    }
                }
            }
        }
        if ( !empty( $connections_sql )){
            $inner_joins .= "INNER JOIN $wpdb->p2p ON ( p2p_from = $wpdb->posts.ID )";
        }


        $meta_query = "";
        $includes_query = "";
        $share_joins = "";
        $access_joins = "";
        $access_query = "";
        if ( !isset( $query["assigned_to"] ) || in_array( "all", $query["assigned_to"] ) ){
            $query["assigned_to"] = [ "all" ];
            if ( !self::can_view_all( 'contacts' ) ){
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
        }
        foreach ( $query as $query_key => $query_value ){
            $meta_field_sql = "";
            if ( !is_array( $query_value )){
                return new WP_Error( __FUNCTION__, __( "Filter queries must be arrays" ), [ 'status' => 403 ] );
            }
            if ( !self::is_key_contact_method_or_connection( $query_key )){
                if ( $query_key == "assigned_to" ){
                    foreach ( $query_value as $assigned_to ){
                        $connector = "OR";
                        if ( $assigned_to == "me" ){
                            $assigned_to = "user-" . $current_user->ID;
                        } else if ( $assigned_to != "all" && $assigned_to != "shared" ) {
                            if ( self::can_view_all( 'contacts' ) ){
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
                    foreach ( $query_value as $value ){
                        if ( !empty( $meta_field_sql ) ){
                            $meta_field_sql .= " OR ";
                        }
                        $meta_field_sql .= " ( " . esc_sql( $query_key ) . ".meta_key = '" . esc_sql( $query_key ) ."' AND " . esc_sql( $query_key ) . ".meta_value = '" . esc_sql( $value ) . "' ) ";
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
            $meta_query .= "AND ( ( INSTR( $wpdb->posts.post_title ,'" . esc_sql( $search ) . "' ) > 0 ) OR ( search.meta_key LIKE 'contact_%' AND INSTR( search.meta_value, '" . esc_sql( $search ) . "' ) > 0 ) ) ";

        }

        $access_query = $access_query ? ( "AND ( " . $access_query . " ) " ) : "";
        // phpcs:disable

        $prepared_sql = $wpdb->prepare("
            SELECT SQL_CALC_FOUND_ROWS $wpdb->posts.ID, post_title, post_type FROM $wpdb->posts
            INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id AND $wpdb->postmeta.meta_key = 'type' )
            INNER JOIN $wpdb->postmeta as status ON ( $wpdb->posts.ID = status.post_id AND status.meta_key = 'overall_status')
            " . $inner_joins . " " . $share_joins . " " . $access_joins . "
            WHERE 1=1 
            AND (
                ( $wpdb->postmeta.meta_key = 'type' AND $wpdb->postmeta.meta_value = 'media' )
                OR
                ( $wpdb->postmeta.meta_key = 'type' AND $wpdb->postmeta.meta_value = 'next_gen' )
                OR ( $wpdb->postmeta.meta_key IS NULL )
            ) " . $connections_sql . " " . $meta_query . " " . $includes_query . " " . $access_query . "
            AND $wpdb->posts.post_type = %s
            AND ($wpdb->posts.post_status = 'publish' OR $wpdb->posts.post_status = 'private')
            GROUP BY $wpdb->posts.ID 
            ORDER BY CASE 
            WHEN ( status.meta_value = 'unassigned') THEN 1
            WHEN ( status.meta_value = 'assigned') THEN 2 
            WHEN ( status.meta_value = 'active') THEN 3 
            WHEN ( status.meta_value = 'paused') THEN 4 
            WHEN ( status.meta_value = 'closed') THEN 99 
            else 10
            end asc
            LIMIT %d, 100
            ",
            "contacts",
            $offset
        );
        $contacts = $wpdb->get_results( $prepared_sql, OBJECT );
        // phpcs:enable
        $total_rows = $wpdb->get_var( "SELECT found_rows();" );

        return [
            "contacts" => $contacts,
            "total" => $total_rows,
        ];

    }


    /**
     * Get Contacts assigned to a user's team
     *
     * @param int $user_id
     * @param bool $check_permissions
     *
     * @param bool $exclude_current_user
     * @param int $most_recent
     *
     * @return array | WP_Error
     * @access public
     * @since  0.1.0
     */
    public static function get_team_contacts( int $user_id, bool $check_permissions = true, $exclude_current_user = false, $most_recent = 0 )
    {
        if ( $check_permissions ) {
            $current_user = wp_get_current_user();
            // TODO: the current permissions required don't make sense
            if ( !self::can_access( 'contacts' )
                || ( $user_id != $current_user->ID && !current_user_can( 'edit_team_contacts' ) ) ) {
                return new WP_Error( __FUNCTION__, __( "You do not have permission" ), [ 'status' => 404 ] );
            }
        }
        $user_connections = [];
        $user_connections['relation'] = 'OR';

        $members = self::get_team_members();

        $query_args = [
            'relation' => "AND",
            [
                'relation' => "OR",
                [
                    'relation' => "AND",
                    [
                        'key' => 'type',
                        'value' => "user",
                        'compare' => '!='
                    ],
                    [
                        'key' => 'type',
                        'value' => "partner",
                        'compare' => '!='
                    ],
                ],
                [
                    'key' => 'type',
                    'compare' => 'NOT EXISTS'
                ]
            ],
            [
                'key' => "last_modified",
                'value' => $most_recent,
                'compare' => '>'
            ]
        ];

        if ( sizeof( $members ) === 0 ){
            return [
                "members"  => $user_connections,
                "contacts" => [],
            ];
        }
        foreach ( $members as $member ) {
            if ( !$exclude_current_user || ( $exclude_current_user && $member != $user_id ) ){
                $user_connections[] = [
                'key' => 'assigned_to',
                'value' => 'user-' . $member
                ];
            };
        }
        $query_args[] = $user_connections;


        $args = [
            'post_type'  => 'contacts',
            'nopaging'   => true,
            'meta_query' => $query_args,
        ];
        $query2 = new WP_Query( $args );

        return [
            "members"  => $user_connections,
            "contacts" => $query2->posts,
        ];
    }

    /**
     * @param int    $contact_id
     * @param string $path_option
     * @param bool   $check_permissions
     *
     * @return array|int|\WP_Error
     */
    public static function update_seeker_path( int $contact_id, string $path_option, $check_permissions = true )
    {
        $seeker_path_options = self::$contact_fields["seeker_path"]["default"];
        $option_keys = array_keys( $seeker_path_options );
        $current_seeker_path = get_post_meta( $contact_id, "seeker_path", true );
        $current_index = array_search( $current_seeker_path, $option_keys );
        $new_index = array_search( $path_option, $option_keys );
        if ( $new_index > $current_index ) {
            $current_index = $new_index;
            $update = self::update_contact( $contact_id, [ "seeker_path" => $path_option ], $check_permissions );
            if ( is_wp_error( $update ) ) {
                return $update;
            }
            $current_seeker_path = $path_option;
        }

        return [
            "currentKey" => $current_seeker_path,
            "current" => $seeker_path_options[ $option_keys[ $current_index ] ],
            "next"    => isset( $option_keys[ $current_index + 1 ] ) ? $seeker_path_options[ $option_keys[ $current_index + 1 ] ] : "",
        ];

    }

    public static function update_quick_action_buttons( $contact_id, $seeker_path ){
        if ( $seeker_path === "established" ){
            $quick_button = get_post_meta( $contact_id, "quick_button_contact_established", true );
            if ( empty( $quick_button ) || $quick_button == "0" ){
                update_post_meta( $contact_id, "quick_button_contact_established", "1" );
            }
        }
        if ( $seeker_path === "scheduled" ){
            $quick_button = get_post_meta( $contact_id, "quick_button_meeting_scheduled", true );
            if ( empty( $quick_button ) || $quick_button == "0" ){
                update_post_meta( $contact_id, "quick_button_meeting_scheduled", "1" );
            }
        }
        if ( $seeker_path === "met" ){
            $quick_button = get_post_meta( $contact_id, "quick_button_meeting_complete", true );
            if ( empty( $quick_button ) || $quick_button == "0" ){
                update_post_meta( $contact_id, "quick_button_meeting_complete", "1" );
            }
        }
    }

    /**
     * @param int   $contact_id
     * @param array $field
     * @param bool  $check_permissions
     *
     * @return array|int|\WP_Error
     */
    private static function handle_quick_action_button_event( int $contact_id, array $field, bool $check_permissions = true )
    {
        $update = [];
        $key = key( $field );

        if ( $key == "quick_button_no_answer" ) {
            $update["seeker_path"] = "attempted";
        } elseif ( $key == "quick_button_phone_off" ) {
            $update["seeker_path"] = "attempted";
        } elseif ( $key == "quick_button_contact_established" ) {
            $update["seeker_path"] = "established";
        } elseif ( $key == "quick_button_meeting_scheduled" ) {
            $update["seeker_path"] = "scheduled";
        } elseif ( $key == "quick_button_meeting_complete" ) {
            $update["seeker_path"] = "met";
        }

        if ( isset( $update["seeker_path"] ) ) {
            return self::update_seeker_path( $contact_id, $update["seeker_path"], $check_permissions );
        } else {
            return $contact_id;
        }
    }

    /**
     * @param int $contact_id
     * @param string $comment
     * @param bool $check_permissions
     *
     * @param string $type
     *
     * @return false|int|\WP_Error
     */
    public static function add_comment( int $contact_id, string $comment, bool $check_permissions = true, $type = "comment" )
    {
        if ( $check_permissions && !self::can_update( 'contacts', $contact_id ) ) {
            return new WP_Error( __FUNCTION__, __( "You do not have permission for this" ), [ 'status' => 403 ] );
        }
        $user = wp_get_current_user();
        $user_id = get_current_user_id();
        $comment_data = [
            'comment_post_ID'      => $contact_id,
            'comment_content'      => $comment,
            'user_id'              => $user_id,
            'comment_author'       => $user->display_name,
            'comment_author_url'   => $user->user_url,
            'comment_author_email' => $user->user_email,
            'comment_type'         => $type,
        ];

        self::check_requires_update( $contact_id );
        return wp_new_comment( $comment_data );
    }

    /**
     * @param int $contact_id
     * @param bool $check_permissions
     *
     * @param string $type
     *
     * @return array|int|\WP_Error
     */
    public static function get_comments( int $contact_id, bool $check_permissions = true, $type = "all" )
    {
        if ( $check_permissions && !self::can_view( 'contacts', $contact_id ) ) {
            return new WP_Error( __FUNCTION__, __( "No permissions to read contact" ), [ 'status' => 403 ] );
        }
        //setting type to "comment" does not work.
        $comments = get_comments( [ 'post_id' => $contact_id, "type" => $type ] );

        return $comments;
    }

    /**
     * @param int  $contact_id
     * @param bool $accepted
     * @param bool $check_permissions
     *
     * @return array|\WP_Error
     */
    public static function accept_contact( int $contact_id, bool $accepted, bool $check_permissions )
    {
        if ( !self::can_update( 'contacts', $contact_id ) ) {
            return new WP_Error( __FUNCTION__, __( "You do not have permission for this" ), [ 'status' => 403 ] );
        }

        if ( $accepted ) {
            update_post_meta( $contact_id, 'overall_status', 'active' );
            update_post_meta( $contact_id, 'accepted', 'Yes' );

            return [ "overall_status" => self::$contact_fields["overall_status"]["default"]['active'] ];
        } else {
            $assign_to_id = 0;
            $last_activity = self::get_most_recent_activity_for_field( $contact_id, "assigned_to" );
            if ( isset( $last_activity->user_id )){
                $assign_to_id = $last_activity->user_id;
            } else {
                $base_user = dt_get_base_user( true );
                if ( $base_user ){
                    $assign_to_id = $base_user;
                }
            }
            update_post_meta( $contact_id, 'assigned_to', $meta_value = "user-" . $assign_to_id );
            update_post_meta( $contact_id, 'overall_status', $meta_value = 'unassigned' );
            $assign = get_user_by( 'id', $assign_to_id );
            $current_user = wp_get_current_user();
            dt_activity_insert(
                [
                    'action'         => 'decline',
                    'object_type'    => get_post_type( $contact_id ),
                    'object_subtype' => 'decline',
                    'object_name'    => get_the_title( $contact_id ),
                    'object_id'      => $contact_id,
                    'meta_id'        => '', // id of the comment
                    'meta_key'       => '',
                    'meta_value'     => '',
                    'meta_parent'    => '',
                    'object_note'    => $current_user->display_name . " declined assignment",
                ]
            );

            return [
                "assigned_to" => $assign->display_name,
                "overall_status" => 'unassigned'
            ];
        }
    }

    /**
     * Gets an array of users whom the contact is shared with.
     *
     * @param int $post_id
     *
     * @return array|mixed
     */
    public static function get_shared_with_on_contact( int $post_id )
    {
        return self::get_shared_with( 'contacts', $post_id );
    }

    /**
     * Removes share record
     *
     * @param int $post_id
     * @param int $user_id
     *
     * @return false|int|WP_Error
     */
    public static function remove_shared_on_contact( int $post_id, int $user_id )
    {
        return self::remove_shared( 'contacts', $post_id, $user_id );
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
    public static function add_shared_on_contact( int $post_id, int $user_id, $meta = null, $send_notifications = true, $check_permissions = true )
    {
        return self::add_shared( 'contacts', $post_id, $user_id, $meta, $send_notifications, $check_permissions );
    }

    public function find_contacts_with( $field, $value, $exclude_id = "" ){
        global $wpdb; //This should allow access to this globally defined var.
        $contact_ids = $wpdb->get_results(
            $wpdb->prepare(
                "
                        SELECT post_id
                        FROM {$wpdb->prefix}postmeta
                        WHERE meta_key
                        LIKE %s
                        AND meta_value LIKE %s
                        AND post_id != %s
                        ",
                [
                    $field .'%',
                    '%' . $value . '%',
                    $exclude_id
                ]
            ),
            ARRAY_N
        );
        return $contact_ids;
    }

    public function find_contacts_by_title( $title, $exclude_id ){
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $wpdb->posts
                WHERE post_title
                LIKE %s
                AND ID != %s",
                '%'. $wpdb->esc_like( $title ) .'%',
                $exclude_id
            ), ARRAY_N
        );
    }

    private function get_duplicate_data( $contact_id, $field ){
        $duplicate_data = get_post_meta( $contact_id, "duplicate_data", true );
        if ( empty( $duplicate_data )){
            $duplicate_data = [];
        }
        if ( !isset( $duplicate_data[$field] ) ){
            $duplicate_data[$field] = [];
        }
        return $duplicate_data;
    }

    private function save_duplicate_finding( $field, $dups, $contact_id ){
        if ( sizeof( $dups ) > 0 ){
            $duplicate_data = $this->get_duplicate_data( $contact_id, $field );
            $has_unconfirmed_duplicates = 0;
            $message = "";
            foreach ( $dups as $row ){
                $id_of_duplicate = (int) $row[0];
                if ( $id_of_duplicate != (int) $contact_id ){
                    if ( !in_array( $id_of_duplicate, $duplicate_data[$field] ) ) {
                        $duplicate_data[$field][] = $id_of_duplicate;
                        $post = get_post( $id_of_duplicate );
                        $has_unconfirmed_duplicates++;
                        if ( $has_unconfirmed_duplicates <= 5 ){
                            $message .= "- [" . $post->post_title .  "](".  get_permalink( $id_of_duplicate ) . ")\n";
                        }
                        //uncomment to enable tracking both ways.
//                        $other_contact_duplicate_data = $this->get_duplicate_data( $id_of_duplicate, $field );
//                        if ( !in_array( $contact_id, $other_contact_duplicate_data[$field] )){
//                            self::add_comment( $id_of_duplicate, "Same phone as " . get_permalink( $contact_id ), false );
//                            $other_contact_duplicate_data[$field][] = $contact_id;
//                            update_post_meta( $id_of_duplicate, "duplicate_data", $other_contact_duplicate_data );
//                        }
                    }
                }
            }
            if ( $has_unconfirmed_duplicates ){
                $field_details = $this->get_field_details( $field, $contact_id );
                $message = __( "Possible duplicates on", "disciple_tools" ) . " " . $field_details["name"] . ":
                " . $message;
                if ( $has_unconfirmed_duplicates > 5 ){
                    $message .= "- " . $has_unconfirmed_duplicates . " " . __( "more duplicates not shown", "disciple_tools" );
                }
                self::add_comment( $contact_id, $message, false, "duplicate" );
                update_post_meta( $contact_id, "duplicate_data", $duplicate_data );
            }
        }
    }

    public function check_for_duplicates( $contact_id, $fields ){
        $fields_to_check = [ "contact_phone", "contact_email", "title" ];
        $fields_to_check = apply_filters( "dt_contact_duplicate_fields_to_check", $fields_to_check );
        foreach ( $fields as $field_id => $field_value ){
            if ( in_array( $field_id, $fields_to_check ) && !empty( $field_value ) ){
                if ( $field_id == "title" ){
                    $contacts = $this->find_contacts_by_title( $field_value, $contact_id );
                    $this->save_duplicate_finding( $field_id, $contacts, $contact_id );
                } else {
                    foreach ( $field_value as $val ){
                        if ( !empty( $val["value"] ) ){
                            $contacts = $this->find_contacts_with( $field_id, $val["value"], $contact_id );
                            $this->save_duplicate_finding( $field_id, $contacts, $contact_id );
                        } else if ( $this->get_field_details( $field_id, $contact_id )["type"] === "array" ){
//                            @todo, specify which field(s) in the array to look for duplicates on
//                            $contacts = $this->find_contacts_with( $field_id, $val, $contact_id );
//                            $this->save_duplicate_finding( $field_id, $contacts, $contact_id );
                        }
                    }
                }
            }
        }
    }


    /**
     * Returns numbers for multiplier and dispatcher
     *
     * Example Array Return:
     * [
            [my_contacts] => 39
            [update_needed] => 0
            [contact_attempted] => 1
            [meeting_scheduled] => 0
            [shared] => 5
            [all_contacts] => 43
            [needs_assigned] => 0
        ]
     *
     * This function will always return this array even if the counts are zero.
     *
     * If the current user/supplied user is not a dispatcher role or similar, then it will skip the query and return zeros for
     * all_contacts and needs assigned array elements.
     *
     * @param null $user_id
     *
     * @return array|\WP_Error
     */
    public static function get_count_of_contacts( $user_id = null ) {
        global $wpdb;
        $numbers = [];

        if ( is_null( $user_id ) ) {
            $user_id = get_current_user_id();
        }
        $personal_counts = $wpdb->get_results( $wpdb->prepare( "
            SELECT (SELECT count(a.ID)
            FROM $wpdb->posts as a
              JOIN $wpdb->postmeta as b
                ON a.ID=b.post_id
                   AND b.meta_key = 'assigned_to'
                   AND b.meta_value = CONCAT( 'user-', %s )
             WHERE a.post_status = 'publish')
            as my_contacts,
            (SELECT count(a.ID)
             FROM $wpdb->posts as a
               JOIN $wpdb->postmeta as b
                 ON a.ID=b.post_id
                    AND b.meta_key = 'requires_update'
                    AND b.meta_value = 'yes'
               JOIN $wpdb->postmeta as c
                 ON a.ID=c.post_id
                    AND c.meta_key = 'assigned_to'
                    AND c.meta_value = CONCAT( 'user-', %s )
              WHERE a.post_status = 'publish')
            as update_needed,
            (SELECT count(a.ID)
             FROM $wpdb->posts as a
               JOIN $wpdb->postmeta as b
                 ON a.ID=b.post_id
                    AND b.meta_key = 'seeker_path'
                    AND b.meta_value = 'attempted'
               JOIN $wpdb->postmeta as c
                 ON a.ID=c.post_id
                    AND c.meta_key = 'assigned_to'
                    AND c.meta_value = CONCAT( 'user-', %s )
              WHERE a.post_status = 'publish')
            as contact_attempted,
            (SELECT count(a.ID)
             FROM $wpdb->posts as a
               JOIN $wpdb->postmeta as b
                 ON a.ID=b.post_id
                    AND b.meta_key = 'seeker_path'
                    AND b.meta_value = 'scheduled'
               JOIN $wpdb->postmeta as c
                 ON a.ID=c.post_id
                    AND c.meta_key = 'assigned_to'
                    AND c.meta_value = CONCAT( 'user-', %s )
              WHERE a.post_status = 'publish')
             as meeting_scheduled,
            (SELECT count(ID)
             FROM wp_9_posts
             WHERE ID IN (SELECT post_id
                          FROM wp_9_dt_share
                          WHERE user_id = %s)
                      AND post_status = 'publish' )
              as shared;
            ",
            $user_id,
            $user_id,
            $user_id,
            $user_id,
            $user_id
        ), ARRAY_A );

        if ( empty( $personal_counts ) ) {
            return new WP_Error( __METHOD__, 'No results from the personal count query' );
        }

        foreach ( $personal_counts[0] as $key => $value ) {
            $numbers[$key] = $value;
        }

        if ( user_can( $user_id, 'view_any_contacts' ) ) {
            $dispatcher_counts = $wpdb->get_results( "
            SELECT (SELECT count(ID) as all_contacts
                    FROM wp_9_posts
                    WHERE post_status = 'publish'
                      AND post_type = 'contacts')
                as all_contacts,
                  (SELECT count(a.ID) as assignment_needed
                    FROM wp_9_posts as a
                    JOIN wp_9_postmeta as b
                      ON a.ID=b.post_id
                         AND b.meta_key = 'overall_status'
                         AND b.meta_value = 'unassigned'
                    WHERE a.post_status = 'publish')
                as needs_assigned
              ", ARRAY_A );

            foreach ( $dispatcher_counts[0] as $key => $value ) {
                $numbers[$key] = $value;
            }
        }

        $numbers = wp_parse_args( $numbers, [
            'my_contacts' => 0,
            'update_needed' => 0,
            'contact_attempted' => 0,
            'meeting_scheduled' => 0,
            'all_contacts' => 0,
            'needs_assigned' => 0,
        ] );

        dt_write_log( $numbers );

        return $numbers;
    }

    public static function get_tag_options(){
        if ( !self::can_access( "contacts" ) ){
            return new WP_Error( __FUNCTION__, __( "You do not have access to tags" ), [ 'status' => 403 ] );
        }
        global $wpdb;
        $tags = $wpdb->get_col( "
            SELECT $wpdb->postmeta.meta_value FROM $wpdb->postmeta
            LEFT JOIN $wpdb->posts on $wpdb->posts.ID = $wpdb->postmeta.post_id
            WHERE $wpdb->postmeta.meta_key = 'tags'
            AND $wpdb->posts.post_type = 'contacts'
            AND $wpdb->posts.post_status = 'publish'
        ;");
        return $tags;
    }

}
