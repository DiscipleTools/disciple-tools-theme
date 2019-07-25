<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Disciple_Tools_Contacts
 */
class Disciple_Tools_Contacts extends Disciple_Tools_Posts
{
    public static $channel_list;
    public static $address_types;
    public static $contact_connection_types;

    /**
     * Disciple_Tools_Contacts constructor.
     */
    public function __construct() {
        self::$channel_list = Disciple_Tools_Contact_Post_Type::instance()->get_channels_list();
        self::$address_types = dt_get_option( "dt_site_custom_lists" )["contact_address_types"];
        self::$contact_connection_types = [
            "locations",
            "groups",
            "people_groups",
            "baptized_by",
            "baptized",
            "coached_by",
            "coaching",
            "subassigned",
            "relation"
        ];
        add_action( "dt_contact_created", [ $this, "check_for_duplicates" ], 10, 2 );
        add_action( "dt_contact_updated", [ $this, "check_for_duplicates" ], 10, 2 );
        add_action( "dt_contact_updated", [ $this, "check_seeker_path" ], 10, 4 );
        add_filter( "dt_post_create_fields", [ $this, "create_post_field_hook" ], 10, 2 );
        add_action( "dt_post_created", [ $this, "post_created_hook" ], 10, 3 );
        add_filter( "dt_post_update_fields", [ $this, "update_post_field_hook" ], 10, 3 );
        add_filter( "dt_post_updated", [ $this, "post_updated_hook" ], 10, 4 );
        add_filter( "dt_get_post_fields_filter", [ $this, "dt_get_post_fields_filter" ], 10, 2 );
        add_action( "dt_comment_created", [ $this, "dt_comment_created" ], 10, 4 );
        add_action( "post_connection_removed", [ $this, "post_connection_removed" ], 10, 4 );
        add_action( "post_connection_added", [ $this, "post_connection_added" ], 10, 4 );

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
    private static function query_with_pagination( array $query_args, array $query_pagination_args ) {
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
                $error->add( __FUNCTION__, sprintf( "Key %s was an unexpected pagination key", $key ) );
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
    public static function get_contact_fields() {
        return Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings();
    }

    /**
     * @return mixed
     */
    public static function get_channel_list() {
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
            $contact_fields = $contact_fields = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings();
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
     * @param  bool|true $silent
     *
     * @since  0.1.0
     * @return int | WP_Error
     */
    public static function create_contact( array $fields = [], $check_permissions = true, $silent = false ) {
        $contact = DT_Posts::create_post( 'contacts', $fields, $silent, $check_permissions );
        return is_wp_error( $contact ) ? $contact : $contact["ID"];
    }

    //add the required fields to the DT_Post::create_contact() function
    public function create_post_field_hook( $fields, $post_type ){
        if ( $post_type === "contacts" ) {
            if ( !isset( $fields["seeker_path"] ) ){
                $fields["seeker_path"] = "none";
            }
            if ( !isset( $fields["type"] ) ){
                $fields["type"] = "media";
            }
            if ( !isset( $fields["last_modified"] ) ){
                $fields["last_modified"] = time();
            }
            if ( !isset( $fields["assigned_to"] ) ){
                if ( get_current_user_id() ) {
                    $fields["assigned_to"] = sprintf( "user-%d", get_current_user_id() );
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
                    $fields["assigned_to"] = sprintf( "user-%d", $base_id );
                }
            } else {
                if ( filter_var( $fields["assigned_to"], FILTER_VALIDATE_EMAIL ) ){
                    $user = get_user_by( "email", $fields["assigned_to"] );
                    if ( $user ) {
                        $fields["assigned_to"] = $user->ID;
                    } else {
                        return new WP_Error( __FUNCTION__, "Unrecognized user", $fields["assigned_to"] );
                    }
                }
                if ( is_numeric( $fields["assigned_to"] ) ||
                     strpos( $fields["assigned_to"], "user" ) === false ){
                    $fields["assigned_to"] = "user-" . $fields["assigned_to"];
                }
            }
            if ( !isset( $fields["overall_status"] ) ){
                $current_roles = wp_get_current_user()->roles;
                if (in_array( "dispatcher", $current_roles, true ) || in_array( "marketer", $current_roles, true )) {
                    $fields["overall_status"] = "new";
                } else if (in_array( "multiplier", $current_roles, true ) ) {
                    $fields["overall_status"] = "active";
                } else {
                    $fields["overall_status"] = "new";
                }
            }
        }
        return $fields;
    }

    public function post_created_hook( $post_type, $post_id, $initial_fields ){
        if ( $post_type === "contacts" ){
            do_action( "dt_contact_created", $post_id, $initial_fields );
            $contact = DT_Posts::get_post( 'contacts', $post_id, true, false );
            if ( isset( $contact["assigned_to"] )) {
                if ( $contact["assigned_to"]["id"] ) {
                    DT_Posts::add_shared( "contacts", $post_id, $contact["assigned_to"]["id"], null, false, false, false );
                }
            }
        }
    }

    /**
     * Update an existing Contact
     *
     * @param  int|null $contact_id , the post id for the contact
     * @param  array $fields , the meta fields
     * @param  bool|null $check_permissions
     * @param bool $silent
     *
     * @return array | WP_Error the contact
     * @access public
     * @since  0.1.0
     */
    public static function update_contact( int $contact_id, array $fields, $check_permissions = true, bool $silent = false ) {
        return DT_Posts::update_post( 'contacts', $contact_id, $fields, $silent, $check_permissions );
    }

    //add the required fields to the DT_Post::create_contact() function
    public function update_post_field_hook( $fields, $post_type, $post_id ){
        if ( $post_type === "contacts" ){
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
                $existing_contact = DT_Posts::get_post( 'contacts', $post_id, true, false );
                if ( !isset( $existing_contact["assigned_to"] ) || $fields["assigned_to"] !== $existing_contact["assigned_to"]["assigned-to"] ){
                    $user_id = explode( '-', $fields["assigned_to"] )[1];
                    if ( $user_id != get_current_user_id() ){
                        if ( current_user_can( "assign_any_contacts" ) ) {
                            $fields["overall_status"] = 'assigned';
                        }
                        $fields['accepted'] = false;
                    } elseif ( isset( $existing_contact["overall_status"]["key"] ) && $existing_contact["overall_status"]["key"] === "assigned" ) {
                        $fields["overall_status"] = 'active';
                    }
                    if ( $user_id ){
                        DT_Posts::add_shared( "contacts", $post_id, $user_id, null, false, true, false );
                    }
                }
            }
            if ( isset( $fields["reason_unassignable"] ) ){
                $fields["overall_status"] = 'unassignable';
            }
            if ( isset( $fields["seeker_path"] ) ){
                self::update_quick_action_buttons( $post_id, $fields["seeker_path"] );
            }
            foreach ( $fields as $field_key => $value ){
                if ( strpos( $field_key, "quick_button" ) !== false ){
                    self::handle_quick_action_button_event( $post_id, [ $field_key => $value ] );
                }
            }
        }
        return $fields;
    }

    public function post_updated_hook( $post_type, $post_id, $updated_fields, $previous_values ){
        if ( $post_type === 'contacts' ){
            $contact = DT_Posts::get_post( 'contacts', $post_id, true, false );
            do_action( "dt_contact_updated", $post_id, $updated_fields, $contact, $previous_values );
        }
    }


    public function post_connection_added( $post_type, $post_id, $post_key, $value ){
        if ( $post_type === "contacts" ){
            if ( $post_key === "subassigned" ){
                $user_id = get_post_meta( $value, "corresponds_to_user", true );
                if ( $user_id ){
                    self::add_shared_on_contact( $post_id, $user_id, null, false, false, false );
                    Disciple_Tools_Notifications::insert_notification_for_subassigned( $user_id, $post_id );
                }
            }
            if ( $post_key === 'baptized' ){
                Disciple_Tools_Counter_Baptism::reset_baptism_generations_on_contact_tree( $value );
                $milestones = get_post_meta( $post_id, 'milestones' );
                if ( empty( $milestones ) || !in_array( "milestone_baptizing", $milestones ) ){
                    add_post_meta( $post_id, "milestones", "milestone_baptizing" );
                }
                Disciple_Tools_Counter_Baptism::reset_baptism_generations_on_contact_tree( $post_id );
            }
            if ( $post_key === 'baptized_by' ){
                $milestones = get_post_meta( $post_id, 'milestones' );
                if ( empty( $milestones ) || !in_array( "milestone_baptized", $milestones ) ){
                    add_post_meta( $post_id, "milestones", "milestone_baptized" );
                }
                Disciple_Tools_Counter_Baptism::reset_baptism_generations_on_contact_tree( $post_id );
            }
            if ( $post_key === "groups" ){
                // share the group with the owner of the contact.
                $assigned_to = get_post_meta( $post_id, "assigned_to", true );
                if ( $assigned_to && strpos( $assigned_to, "-" ) !== false ){
                    $user_id = explode( "-", $assigned_to )[1];
                    if ( $user_id ){
                        DT_Posts::add_shared( "groups", $value, $user_id, null, false, false );
                    }
                }
                do_action( 'group_member_count', $value, "added" );
            }
        }
    }

    public function post_connection_removed( $post_type, $post_id, $post_key, $value ){
        if ( $post_type === "contacts" ){
            if ( $post_key === "groups" ){
                do_action( 'group_member_count', $value, "removed" );
            }
            if ( $post_key === "baptized_by" ){
                Disciple_Tools_Counter_Baptism::reset_baptism_generations_on_contact_tree( $post_id );
            }
            if ( $post_key === "baptized" ){
                Disciple_Tools_Counter_Baptism::reset_baptism_generations_on_contact_tree( $value );
            }
        }
    }


    //check to see if the contact is marked as needing an update
    //if yes: mark as updated
    private static function check_requires_update( $contact_id ){
        if ( get_current_user_id() ){
            $requires_update = get_post_meta( $contact_id, "requires_update", true );
            if ( $requires_update == "yes" || $requires_update == true || $requires_update = "1"){
                //don't remove update needed if the user is a dispatcher (and not assigned to the contacts.)
                if ( self::can_view_all( 'contacts' ) ){
                    if ( dt_get_user_id_from_assigned_to( get_post_meta( $contact_id, "assigned_to", true ) ) === get_current_user_id() ){
                        update_post_meta( $contact_id, "requires_update", false );
                    }
                } else {
                    update_post_meta( $contact_id, "requires_update", false );
                }
            }
        }
    }

    public static function remove_fields( $contact_id, $fields = [], $ignore = []) {
        global $wpdb;
        foreach ($fields as $field) {
            $ignore_keys = preg_grep( "/$field/", $ignore );
            $sql = "delete
                from
                    wp_postmeta
                where
                    post_id = %d and
                    meta_key like %s";
            $params = array( $contact_id, "$field%" );
            if ( !empty( $ignore_keys )) {
                foreach ( $ignore_keys as $key ){
                    $sql .= " and meta_key not like %s";
                }
                array_push( $params, ...$ignore_keys );
            }
            $wpdb->query( $wpdb->prepare( $sql, $params ) ); // @codingStandardsIgnoreLine
        }
    }

    /**
     * Get a single contact
     *
     * @param int  $contact_id , the contact post_id
     * @param bool $check_permissions
     * @param bool $load_cache
     *
     * @access public
     * @since  0.1.0
     * @return array| WP_Error, On success: the contact, else: the error message
     */
    public static function get_contact( int $contact_id, $check_permissions = true, $load_cache = false ) {
        return DT_Posts::get_post( 'contacts', $contact_id, $load_cache, $check_permissions );
    }
    public function dt_get_post_fields_filter( $fields, $post_type ) {
        if ( $post_type === 'contacts' ){
            $fields = apply_filters( 'dt_contact_fields_post_filter', $fields );
        }
        return $fields;
    }


    public static function get_merge_data( int $contact_id, int $duplicate_id) {
        if ( !$contact_id && !$duplicate_id) { return; }

        $contact = self::get_contact( $contact_id );
        $duplicate = self::get_contact( $duplicate_id );

        $fields = array(
            'contact_phone' => 'Phone',
            'contact_email' => 'Email',
            'contact_address' => 'Address',
            'contact_facebook' => 'Facebook'
        );

        $c_fields = array();
        $d_fields = array();

        $data = array(
            'contact_phone' => array(),
            'contact_email' => array(),
            'contact_address' => array(),
            'contact_facebook' => array()
        );

        foreach (array_keys( $fields ) as $key) {
            foreach ($contact[$key] ?? [] as $vals) {
                if ( !isset( $c_fields[$key] )) {
                    $c_fields[$key] = array();
                }
                array_push( $c_fields[$key], $vals['value'] );
            }
            foreach ($duplicate[$key] ?? [] as $vals) {
                if ( !isset( $d_fields[$key] )) {
                    $d_fields[$key] = array();
                }
                array_push( $d_fields[$key], $vals['value'] );
            }
        }

        foreach (array_keys( $fields ) as $field) {
            $max = max( array( count( $c_fields[$field] ?? [] ), count( $d_fields[$field] ?? [] ) ) );
            for ($i = 0; $i < $max; $i++) {
                $hide = false;
                $o_value = $c_fields[$field][$i] ?? null;
                $d_value = $d_fields[$field][$i] ?? null;
                if (in_array( $o_value, $d_fields[$field] ?? [] )) { $hide = true; }
                array_push($data[$field], array(
                    'original' => array(
                        'hide' => $hide,
                        'value' => $o_value
                    ),
                    'duplicate' => array(
                        'hide' => $hide,
                        'value' => $d_value
                    )
                ));
            }
        }

        return array( $contact, $duplicate, $data, $fields );
    }

    public static function merge_p2p( int $master_id, int $non_master_id) {
        if ( !$master_id || !$non_master_id) { return; }
        $master = self::get_contact( $master_id );
        $non_master = self::get_contact( $non_master_id );

        $post_settings = DT_Posts::get_post_settings( 'contacts' );
        $keys = $post_settings["connection_types"];
        $update = [];
        $to_remove = [];

        foreach ($keys as $key) {
            $results = $non_master[$key] ?? array();
            foreach ($results as $result) {
                if ( !isset( $update[$key] )) {
                    $update[$key] = array();
                    $update[$key]['values'] = array();
                }
                if ( !isset( $to_remove[$key] )) {
                    $to_remove[$key] = array();
                    $to_remove[$key]['values'] = array();
                }
                array_push($update[$key]['values'], array(
                    'value' => $result["ID"]
                ));
                array_push($to_remove[$key]['values'], array(
                    'value' => $result["ID"],
                    'delete' => true
                ));
            }
        }

        self::update_contact( $master_id, $update );
        self::update_contact( $non_master_id, $to_remove );
    }

    public static function copy_comments( int $master_id, int $non_master_id, $check_permissions = true ){
        if ( $check_permissions && ( !self::can_update( 'contacts', $master_id ) || !self::can_update( 'contacts', $non_master_id ) )) {
            return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
        }
        $comments = self::get_comments( $non_master_id );
        foreach ( $comments as $comment ){
            $comment->comment_post_ID = $master_id;
            if ( $comment->comment_type === "comment" ){
                $comment->comment_content = sprintf( esc_html_x( '(From Duplicate): %s', 'duplicate comment', 'disciple_tools' ), $comment->comment_content );
            }
            if ( $comment->comment_type !== "duplicate" ){
                wp_insert_comment( (array) $comment );
            }
        }
    }

    /**
     * @param $contact_id
     *
     * @return array|null|object
     */
    public static function get_activity( $contact_id ) {
        $resp = DT_Posts::get_post_activity( "contacts", $contact_id );
        return is_wp_error( $resp ) ? $resp : $resp["activity"];
    }

    /**
     * @param $contact_id
     * @param $activity_id
     *
     * @return array|null|object
     */
    public static function get_single_activity( $contact_id, $activity_id ) {
        return DT_Posts::get_post_single_activity( "contacts", $contact_id, $activity_id );
    }

    /**
     * @param $contact_id
     * @param $activity_id
     *
     * @return bool|int|WP_Error
     */
    public static function revert_activity( $contact_id, $activity_id ){
        if ( !self::can_update( 'contacts', $contact_id ) ) {
            return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
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
    public static function get_user_contacts( int $user_id, bool $check_permissions = true, array $query_pagination_args = [] ) {
        if ( $check_permissions && !self::can_access( 'contacts' ) ) {
            return new WP_Error( __FUNCTION__, "You do not have access to these contacts", [ 'status' => 403 ] );
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
    public static function get_viewable_contacts( int $most_recent, bool $check_permissions = true ) {
        if ( $check_permissions && !self::can_access( 'contacts' ) ) {
            return new WP_Error( __FUNCTION__, "You do not have access to these contacts", [ 'status' => 403 ] );
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
            // @codingStandardsIgnoreLine
            'posts_per_page' => 1000,
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
//        $team_contacts = self::get_team_contacts( $current_user->ID, true, true, $most_recent );
//        if ( isset( $team_contacts["contacts"] ) ){
//            foreach ( $team_contacts["contacts"] as $team_contact ){
//                if ( !in_array( $team_contact->ID, $contact_ids ) ) {
//                    $team_contact->is_team_contact = true;
//                    $contacts[] = $team_contact;
//                }
//            }
//        }

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
     * @return array|WP_Query
     */
    public static function get_viewable_contacts_compact( string $search_string ) {
        return DT_Posts::get_viewable_compact( 'contacts', $search_string );
    }

    public static function search_viewable_contacts( array $query, bool $check_permissions = true){
        $viewable = self::search_viewable_post( "contacts", $query, $check_permissions );
        if ( is_wp_error( $viewable ) ){
            return $viewable;
        }
        return [
            "contacts" => $viewable["posts"],
            "total" => $viewable["total"]
        ];
    }


    /**
     * @param int    $contact_id
     * @param string $path_option
     * @param bool   $check_permissions
     *
     * @return array|int|WP_Error
     */
    public static function update_seeker_path( int $contact_id, string $path_option, $check_permissions = true ) {
        $contact_fields = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings();
        $seeker_path_options = $contact_fields["seeker_path"]["default"];
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
        self::check_requires_update( $contact_id );
    }

    /**
     * @param int   $contact_id
     * @param array $field
     * @param bool  $check_permissions
     *
     * @return array|int|WP_Error
     */
    private static function handle_quick_action_button_event( int $contact_id, array $field, bool $check_permissions = true ) {
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
            self::check_requires_update( $contact_id );
            return self::update_seeker_path( $contact_id, $update["seeker_path"], $check_permissions );
        } else {
            return $contact_id;
        }
    }

    /**
     * @param int $contact_id
     * @param string $comment_html
     * @param string $type      normally 'comment', different comment types can have their own section in the comments activity
     * @param array $args       [user_id, comment_date, comment_author etc]
     * @param bool $check_permissions
     * @param bool $silent
     *
     * @return false|int|WP_Error
     */
    public static function add_comment( int $contact_id, string $comment_html, string $type = "comment", array $args = [], bool $check_permissions = true, $silent = false ) {
        return DT_Posts::add_post_comment( "contacts", $contact_id, $comment_html, $type, $args, $check_permissions, $silent );
    }

    public function dt_comment_created( $post_type, $post_id, $comment_id, $type ){
        if ( $post_type === "contacts" ){
            if ( $type === "comment" ){
                self::check_requires_update( $post_id );
            }
        }
    }

    /**
     * @param int $contact_id
     * @param bool $check_permissions
     *
     * @param string $type
     *
     * @return array|int|WP_Error
     */
    public static function get_comments( int $contact_id, bool $check_permissions = true, $type = "all" ) {
        $resp = DT_Posts::get_post_comments( 'contacts', $contact_id, $check_permissions, $type );
        return is_wp_error( $resp ) ? $resp : $resp["comments"];
    }


    public static function delete_comment( int $contact_id, int $comment_id, bool $check_permissions = true ){
        return DT_Posts::delete_post_comment( $comment_id, $check_permissions );
    }

    public static function update_comment( int $contact_id, int $comment_id, string $comment_content, bool $check_permissions = true ){
        return DT_Posts::update_post_comment( $comment_id, $comment_content, $check_permissions );
    }

    /**
     * @param int  $contact_id
     * @param bool $accepted
     *
     * @return array|WP_Error
     */
    public static function accept_contact( int $contact_id, bool $accepted ) {
        if ( !self::can_update( 'contacts', $contact_id ) ) {
            return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
        }

        if ( $accepted ) {
            $update = [
                "overall_status" => 'active',
                "accepted" => true
            ];
            dt_activity_insert(
                [
                    'action'         => 'assignment_accepted',
                    'object_type'    => get_post_type( $contact_id ),
                    'object_subtype' => '',
                    'object_name'    => get_the_title( $contact_id ),
                    'object_id'      => $contact_id,
                    'meta_id'        => '', // id of the comment
                    'meta_key'       => '',
                    'meta_value'     => '',
                    'meta_parent'    => '',
                    'object_note'    => '',
                ]
            );
            return self::update_contact( $contact_id, $update, true );
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

            $update = [
                "assigned_to" => $assign_to_id,
                "overall_status" => 'unassigned'
            ];
            $contact = self::update_contact( $contact_id, $update, true );
            $current_user = wp_get_current_user();
            dt_activity_insert(
                [
                    'action'         => 'assignment_decline',
                    'object_type'    => get_post_type( $contact_id ),
                    'object_subtype' => 'decline',
                    'object_name'    => get_the_title( $contact_id ),
                    'object_id'      => $contact_id,
                    'meta_id'        => '', // id of the comment
                    'meta_key'       => '',
                    'meta_value'     => '',
                    'meta_parent'    => '',
                    'object_note'    => ''
                ]
            );
            Disciple_Tools_Notifications::insert_notification_for_assignment_declined( $current_user->ID, $assign_to_id, $contact_id );
            return $contact;
        }
    }

    /**
     * Gets an array of users whom the contact is shared with.
     *
     * @param int $post_id
     *
     * @return array|mixed
     */
    public static function get_shared_with_on_contact( int $post_id ) {
        return DT_Posts::get_shared_with( 'contacts', $post_id );
    }

    /**
     * Removes share record
     *
     * @param int $post_id
     * @param int $user_id
     *
     * @return false|int|WP_Error
     */
    public static function remove_shared_on_contact( int $post_id, int $user_id ) {
        return DT_Posts::remove_shared( 'contacts', $post_id, $user_id );
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
     * @param bool $insert_activity
     *
     * @return false|int|WP_Error
     */
    public static function add_shared_on_contact( int $post_id, int $user_id, $meta = null, $send_notifications = true, $check_permissions = true, $insert_activity = true ) {
        return DT_Posts::add_shared( 'contacts', $post_id, $user_id, $meta, $send_notifications, $check_permissions, $insert_activity );
    }

    public static function find_contacts_with( $field, $value, $exclude_id = "", $exact_match = false ){
        global $wpdb;
        $contact_ids = $wpdb->get_results(
            $wpdb->prepare(
                "
                        SELECT post_id
                        FROM {$wpdb->prefix}postmeta
                        INNER JOIN $wpdb->posts posts ON ( posts.ID = post_id AND posts.post_type = 'contacts' AND posts.post_status = 'publish' )
                        WHERE meta_key
                        LIKE %s
                        AND meta_value LIKE %s
                        AND post_id != %s
                        ",
                [
                    esc_sql( $field ) .'%',
                    $exact_match ? esc_sql( $value ) : ( '%' . trim( esc_sql( $value ) ) . '%' ),
                    esc_sql( $exclude_id )
                ]
            ),
            ARRAY_N
        );
//        @todo return just an array
        // if there are more than 50, it is most likely not a duplicate
        return sizeof( $contact_ids ) > 50 ? [] : $contact_ids;
    }

    public function find_contacts_by_title( $title, $exclude_id ){
        global $wpdb;
        $dups = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $wpdb->posts
                WHERE post_title
                LIKE %s
                AND ID != %s
                AND post_type = 'contacts' AND post_status = 'publish'",
                '%'. $wpdb->esc_like( $title ) .'%',
                $exclude_id
            ), ARRAY_N
        );
        // if there are more than 25, it is most likely not a duplicate
        return sizeof( $dups ) > 25 ? [] : $dups;
    }

    public function get_all_duplicates() {
        global $wpdb;
        $records = $wpdb->get_results(
            $wpdb->prepare("
                select
                    *
                from
                    wp_posts p join wp_postmeta m on p.ID = m.post_id
                where
                    p.post_type = %s and
                    m.meta_key = %s
            ", [ 'contacts', 'duplicate_data' ]), ARRAY_A
        );

        $duplicates = array();
        foreach ($records as $record) {
            $dupes = unserialize( $record['meta_value'] );
            $count = 0;
            foreach ($dupes as $key => $dupe) {
                if ($key === 'override') { continue; }
                $count += count( $dupe );
            }
            $duplicates[$record['ID']]['count'] = $count;
            $duplicates[$record['ID']]['name'] = $record['post_title'];
        }

        return $duplicates;
    }

    public static function get_duplicates_on_contact( $contact_id ){
        if ( !self::can_view_all( 'contacts' ) ) {
            return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
        }
        $contact = self::get_contact( $contact_id );
        $all_ids = [];
        $dups = [];
        if ( isset( $contact["duplicate_data"] ) ){
            foreach ( $contact["duplicate_data"] as $dup_ids ){
                $ids = array_diff( $dup_ids, $all_ids );
                $all_ids = array_merge( $all_ids, $ids );
                foreach ( $ids as $index => $contact_id ) {
                    if ( $index < 100 ){
                        $dup = self::get_contact( $contact_id );
                        $dups[] = $dup;
                    }
                }
            }
        }
        return $dups;
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
                self::add_comment( $contact_id, $message, "duplicate", [
                    "user_id" => 0,
                    "comment_author" => "Duplicate Checker"
                ], false );
                update_post_meta( $contact_id, "duplicate_data", $duplicate_data );
            }
        }
    }

    public function check_for_duplicates( $contact_id, $fields ){
        $fields_to_check = [ "contact_phone", "contact_email", "contact_address", "title" ];
        $fields_to_check = apply_filters( "dt_contact_duplicate_fields_to_check", $fields_to_check );
        foreach ( $fields as $field_id => $field_value ){
            if ( in_array( $field_id, $fields_to_check ) && !empty( $field_value ) ){
                if ( $field_id == "title" ){
                    $contacts = $this->find_contacts_by_title( $field_value, $contact_id );
                    $this->save_duplicate_finding( $field_id, $contacts, $contact_id );
                } else {
                    if ( isset( $field_value["values"] ) ){
                        $values = $field_value["values"];
                    } else {
                        $values = $field_value;
                    }
                    foreach ( $values as $val ){
                        if ( !empty( $val["value"] ) ){
                            $contacts = $this->find_contacts_with( $field_id, $val["value"], $contact_id );
                            $this->save_duplicate_finding( $field_id, $contacts, $contact_id );
                        }
                        //else if ( $this->get_field_details( $field_id, $contact_id )["type"] === "array" ){
//                            @todo, specify which field(s) in the array to look for duplicates on
//                            $contacts = $this->find_contacts_with( $field_id, $val, $contact_id );
//                            $this->save_duplicate_finding( $field_id, $contacts, $contact_id );
                        //}
                    }
                }
            }
        }
    }

    public static function escape_regex_mysql( string $regex ) {
        return preg_replace( '/&/', '\\&', preg_quote( $regex ) );
    }


    public static function recheck_duplicates( int $contact_id) {
        global $wpdb;
        $contact = self::get_contact( $contact_id );
        if (empty( $contact )) { return; }
        $fields = array( 'contact_phone', 'contact_email', 'contact_address' );
        $values = array();
        foreach ($fields as $field) {
            foreach ($contact[$field] ?? [] as $arr_val) {
                if ( !empty( $arr_val['value'] ) ){
                    $values[] = $arr_val['value'];
                }
            }
        }
        $unsure = $contact['duplicate_data']['unsure'] ?? array();
        $dismissed = $contact['duplicate_data']['override'] ?? array();
        if (count( $values ) == 0 || count( $fields ) == 0) {
            $results = [];
        } else {
            $vals = join( '|', array_map( [ __CLASS__, 'escape_regex_mysql' ], $values ) );
            $flds = join( '|', array_map( [ __CLASS__, 'escape_regex_mysql' ], $fields ) );
            $results = $wpdb->get_results( $wpdb->prepare( "
                select
                    *
                from
                    wp_posts p join
                    wp_postmeta m on p.ID = m.post_id
                where
                    ID != %d and
                    (meta_key regexp %s and meta_key not like %s) and
                    meta_value regexp %s
                ",
                array(
                    $contact_id,
                    "$flds",
                    '%details',
                    "$vals"
                )
            ), ARRAY_A );
        }
        $duplicates = array();
        foreach ($results as $result) {
            $key = $result['meta_key'];
            if (preg_match( "/contact_/i", $key )) {
                $keys = explode( "_", $key );
                $key = "$keys[0]_$keys[1]";
            }
            if ( !isset( $duplicates[$key] )) {
                $duplicates[$key] = array();
            }
            if ( !in_array( $result['ID'], $unsure ) && !in_array( $result['ID'], $dismissed )) {
                array_push( $duplicates[$key], $result['ID'] );
            }
        }
        foreach ($duplicates as $key => $duplicate) {
            $duplicates[$key] = array_merge( array_unique( $duplicates[$key] ) );
        }
        if ( !empty( $unsure )) {
            $duplicates['unsure'] = $unsure;
        }
        if ( !empty( $dismissed )) {
            $duplicates['override'] = $dismissed;
        }

        self::save_duplicate_data( $contact_id, $duplicates );
    }


    public static function save_duplicate_data( int $contact_id, array $duplicates) {
        if (empty( $duplicates )) { return; }
        update_post_meta( $contact_id, "duplicate_data", $duplicates );
    }

    public static function unsure_all( int $contact_id) {
        if ( !$contact_id) { return; }
        $contact = self::get_contact( $contact_id );
        $data = isset( $contact['duplicate_data'] ) ? is_array( $contact['duplicate_data'] ) ? $contact['duplicate_data'] : unserialize( $contact['duplicate_data'] ) : array();
        $duplicates = array();
        foreach ($data as $key => $duplicate) {
            if ($key === 'override') { continue; }
            foreach ($duplicate as $duplicate_id) {
                $duplicates['unsure'][] = $duplicate_id;
            }
        }

        self::save_duplicate_data( $contact_id, $duplicates );
    }

    public static function unsure_duplicate( int $contact_id, int $unsure_id) {
        if ( !$contact_id || !$unsure_id) { return; }
        $contact = self::get_contact( $contact_id );
        $duplicates = isset( $contact['duplicate_data'] ) ? is_array( $contact['duplicate_data'] ) ? $contact['duplicate_data'] : unserialize( $contact['duplicate_data'] ) : array();
        $unsure = $duplicates['unsure'] ?? array();
        foreach ($duplicates as $key => $values) {
            if (preg_match( "/unsure|override/", $key )) { continue; }
            $index = array_search( $unsure_id, $values );
            if ($index !== false) {
                unset( $duplicates[$key][$index] );
                array_merge( $duplicates[$key] );
            }
            if (empty( $duplicates[$key] )) {
                unset( $duplicates[$key] );
            }
        }
        if ( !in_array( $unsure_id, $unsure )) {
            if (isset( $duplicates['unsure'] )) {
                array_push( $duplicates['unsure'], $unsure_id );
            } else {
                $duplicates['unsure'] = [ $unsure_id ];
            }
        }

        self::save_duplicate_data( $contact_id, $duplicates );
    }

    public static function dismiss_all( int $contact_id) {
        if ( !$contact_id) { return; }
        $contact = self::get_contact( $contact_id );
        $data = isset( $contact['duplicate_data'] ) ? is_array( $contact['duplicate_data'] ) ? $contact['duplicate_data'] : unserialize( $contact['duplicate_data'] ) : array();
        $duplicates = array();
        foreach ($data as $key => $duplicate) {
            foreach ($duplicate as $duplicate_id) {
                $duplicates['override'][] = $duplicate_id;
            }
        }

        self::save_duplicate_data( $contact_id, $duplicates );
    }

    public static function dismiss_duplicate( int $contact_id, int $dismiss_id) {
        if ( !$contact_id || !$dismiss_id) { return; }
        $contact = self::get_contact( $contact_id );
        $duplicates = isset( $contact['duplicate_data'] ) ? is_array( $contact['duplicate_data'] ) ? $contact['duplicate_data'] : unserialize( $contact['duplicate_data'] ) : array();
        $dismissed = $duplicates['override'] ?? array();
        foreach ($duplicates as $key => $values) {
            if (preg_match( "/override/", $key )) { continue; }
            $index = array_search( $dismiss_id, $values );
            if ($index !== false) {
                unset( $duplicates[$key][$index] );
                array_merge( $duplicates[$key] );
            }
            if (empty( $duplicates[$key] )) {
                unset( $duplicates[$key] );
            }
        }
        if ( !in_array( $dismiss_id, $dismissed )) {
            if (isset( $duplicates['override'] )) {
                array_push( $duplicates['override'], $dismiss_id );
            } else {
                $duplicates['override'] = [ $dismiss_id ];
            }
        }

        self::save_duplicate_data( $contact_id, $duplicates );
    }

    public static function close_duplicate_contact( int $duplicate_id, int $contact_id) {
        $duplicate = self::get_contact( $duplicate_id );
        $contact = self::get_contact( $contact_id );

        self::update_contact( $duplicate_id, [
            "overall_status" => "closed",
            "reason_closed" => "duplicate",
            "duplicate_of" => $contact_id
        ] );

        $link = "<a href='" . get_permalink( $contact_id ) . "'>{$contact['title']}</a>";
        $comment = sprintf( esc_html_x( '%1$s is a duplicate and was merged into %2$s', 'Contact1 is a duplicated and was merged into Contact2', 'disciple_tools' ), $duplicate['title'], $link );

        self::add_comment( $duplicate_id, $comment, "duplicate", [], true, true );
        self::dismiss_all( $duplicate_id );

        //comment on master
        $link = "<a href='" . get_permalink( $duplicate_id ) . "'>{$duplicate['title']}</a>";
        $comment = sprintf( esc_html_x( '%1$s was merged into %2$s', 'Contact1 was merged into Contact2', 'disciple_tools' ), $link, $contact['title'] );
        self::add_comment( $contact_id, $comment, "duplicate", [], true, true );
    }

    /**
     * Returns numbers for multiplier and dispatcher
     *
     * Example Array Return:
     * [
     * [my_contacts] => 39
     * [update_needed] => 0
     * [contact_attempted] => 1
     * [meeting_scheduled] => 0
     * [shared] => 5
     * [all_contacts] => 43
     * [needs_assigned] => 0
     * ]
     *
     * This function will always return this array even if the counts are zero.
     *
     * If the current user/supplied user is not a dispatcher role or similar, then it will skip the query and return zeros for
     * all_contacts and needs assigned array elements.
     *
     * @param string $tab
     * @param bool $show_closed
     *
     * @return array|WP_Error
     */
    public static function get_count_of_contacts( $tab = "my", $show_closed = false ) {
        global $wpdb;
        if ( !self::can_access( "contacts" ) ) {
            return new WP_Error( __FUNCTION__, "Permission denied.", [ 'status' => 403 ] );
        }

        $numbers = [];

        $user_id = get_current_user_id();
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
        $closed = "";
        if ( !$show_closed ){
            $closed = " INNER JOIN $wpdb->postmeta as status
              ON ( a.ID=status.post_id 
              AND status.meta_key = 'overall_status'
              AND status.meta_value != 'closed' )";
        }
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


        // phpcs:disable
        // WordPress.WP.PreparedSQL.NotPrepare
        $personal_counts = $wpdb->get_results("
            SELECT (SELECT count(a.ID)
            FROM $wpdb->posts as a
              " . $access_sql . $closed . "
              INNER JOIN $wpdb->postmeta as type
                ON a.ID=type.post_id AND type.meta_key = 'type'
            WHERE a.post_status = 'publish'
            AND post_type = 'contacts'
            AND (( type.meta_value = 'media' OR type.meta_value = 'next_gen' )
                OR ( type.meta_key IS NULL ))
            )
            as total_count,
            (SELECT count(a.ID)
            FROM $wpdb->posts as a
            " . $my_access . $closed . "
            INNER JOIN $wpdb->postmeta as type
              ON a.ID=type.post_id AND type.meta_key = 'type'
            WHERE a.post_status = 'publish'
            AND post_type = 'contacts'
            AND (( type.meta_value = 'media' OR type.meta_value = 'next_gen' )
                OR ( type.meta_key IS NULL ))
            )
            as total_my,
            (SELECT count(a.ID)
            FROM $wpdb->posts as a
            " . $subassigned_access . $closed . "
            INNER JOIN $wpdb->postmeta as type
              ON a.ID=type.post_id AND type.meta_key = 'type'
            WHERE a.post_status = 'publish'
            AND post_type = 'contacts'
            AND (( type.meta_value = 'media' OR type.meta_value = 'next_gen' )
                OR ( type.meta_key IS NULL ))
            )
            as total_subassigned,
            (SELECT count(a.ID)
            FROM $wpdb->posts as a
            " . $shared_access . $closed . "
            INNER JOIN $wpdb->postmeta as type
              ON a.ID=type.post_id AND type.meta_key = 'type'
            WHERE a.post_status = 'publish'
            AND post_type = 'contacts'
            AND (( type.meta_value = 'media' OR type.meta_value = 'next_gen' )
                OR ( type.meta_key IS NULL ))
            )
            as total_shared,
            (SELECT count(a.ID)
            FROM $wpdb->posts as a
            " . $all_access . $closed . "
            INNER JOIN $wpdb->postmeta as type
              ON a.ID=type.post_id AND type.meta_key = 'type'
            WHERE a.post_status = 'publish'
            AND post_type = 'contacts'
            AND (( type.meta_value = 'media' OR type.meta_value = 'next_gen' )
                OR ( type.meta_key IS NULL ))
            )
            as total_all,
            (SELECT count(a.ID)
              FROM $wpdb->posts as a
                " . $access_sql . $closed . "
                JOIN $wpdb->postmeta as b
                  ON a.ID=b.post_id
                    AND b.meta_key = 'requires_update'
                    AND b.meta_value = '1'
                INNER JOIN $wpdb->postmeta as e
                  ON a.ID=e.post_id
                  AND (( e.meta_key = 'type'
                    AND ( e.meta_value = 'media' OR e.meta_value = 'next_gen' ) )
                  OR e.meta_key IS NULL)
              WHERE a.post_status = 'publish'
              AND post_type = 'contacts')
            as update_needed,
            (SELECT count(a.ID)
              FROM $wpdb->posts as a
                " . $access_sql . "
                JOIN $wpdb->postmeta as b
                  ON a.ID=b.post_id
                    AND b.meta_key = 'overall_status'
                    AND b.meta_value = 'active'
                INNER JOIN $wpdb->postmeta as e
                  ON a.ID=e.post_id
                  AND (( e.meta_key = 'type'
                    AND ( e.meta_value = 'media' OR e.meta_value = 'next_gen' ) )
                  OR e.meta_key IS NULL)
              WHERE a.post_status = 'publish'
              AND post_type = 'contacts')
            as active,
            (SELECT count(a.ID)
              FROM $wpdb->posts as a
                " . $access_sql . "
                INNER JOIN $wpdb->postmeta as d
                  ON a.ID=d.post_id
                    AND d.meta_key = 'overall_status'
                    AND d.meta_value = 'assigned'
                INNER JOIN $wpdb->postmeta as e
                  ON a.ID=e.post_id
                  AND (( e.meta_key = 'type'
                    AND ( e.meta_value = 'media' OR e.meta_value = 'next_gen' ) )
                  OR e.meta_key IS NULL)
              WHERE a.post_status = 'publish'
              AND post_type = 'contacts')
            as needs_accepted,
            (SELECT count(a.ID)
              FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as b
                  ON a.ID=b.post_id
                    AND b.meta_key = 'seeker_path'
                    AND b.meta_value = 'none'
                " . $access_sql . $closed . "
                JOIN $wpdb->postmeta as d
                  ON a.ID=d.post_id
                    AND d.meta_key = 'overall_status'
                    AND d.meta_value = 'active'
                INNER JOIN $wpdb->postmeta as e
                  ON a.ID=e.post_id
                  AND (( e.meta_key = 'type'
                    AND ( e.meta_value = 'media' OR e.meta_value = 'next_gen' ) )
                  OR e.meta_key IS NULL)
              WHERE a.post_status = 'publish'
              AND post_type = 'contacts')
            as contact_unattempted,
            (SELECT count(a.ID)
              FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as b
                  ON a.ID=b.post_id
                    AND b.meta_key = 'seeker_path'
                    AND b.meta_value = 'scheduled'
                " . $access_sql . $closed . "
                JOIN $wpdb->postmeta as d
                  ON a.ID=d.post_id
                    AND d.meta_key = 'overall_status'
                    AND d.meta_value = 'active'
                INNER JOIN $wpdb->postmeta as e
                  ON a.ID=e.post_id
                  AND (( e.meta_key = 'type'
                    AND ( e.meta_value = 'media' OR e.meta_value = 'next_gen' ) )
                  OR e.meta_key IS NULL)
              WHERE a.post_status = 'publish'
              AND post_type = 'contacts' )
            as meeting_scheduled
            ", ARRAY_A );

        if ( empty( $personal_counts ) ) {
            return new WP_Error( __METHOD__, 'No results from the personal count query' );
        }

        foreach ( $personal_counts[0] as $key => $value ) {
            $numbers[$key] = $value;
        }

        if ( user_can( $user_id, 'view_any_contacts' ) ) {
            $dispatcher_counts = $wpdb->get_results( "
            SELECT (SELECT count(a.ID)
                FROM $wpdb->posts as a
                INNER JOIN $wpdb->postmeta as b
                  ON a.ID=b.post_id
                     AND b.meta_key = 'overall_status'
                     AND b.meta_value = 'unassigned'
                " . $access_sql . "
                INNER JOIN $wpdb->postmeta as e
                  ON a.ID=e.post_id
                  AND (( e.meta_key = 'type'
                    AND ( e.meta_value = 'media' OR e.meta_value = 'next_gen' ) )
                  OR e.meta_key IS NULL)
                    WHERE a.post_status = 'publish'
                  )
                as needs_assigned,
            (SELECT count(a.ID)
                FROM $wpdb->posts as a
                INNER JOIN $wpdb->postmeta as b
                  ON a.ID=b.post_id
                     AND b.meta_key = 'overall_status'
                     AND b.meta_value = 'new'
                " . $access_sql . "
                INNER JOIN $wpdb->postmeta as e
                  ON a.ID=e.post_id
                  AND (( e.meta_key = 'type'
                    AND ( e.meta_value = 'media' OR e.meta_value = 'next_gen' ) )
                  OR e.meta_key IS NULL)
                    WHERE a.post_status = 'publish'
                  )
                as new
              ", ARRAY_A );

            foreach ( $dispatcher_counts[0] as $key => $value ) {
                $numbers[$key] = $value;
            }
        }
        // phpcs:enable

        $numbers = wp_parse_args( $numbers, [
            'my_contacts' => 0,
            'update_needed' => 0,
            'needs_accepted' => 0,
            'contact_unattempted' => 0,
            'meeting_scheduled' => 0,
            'all_contacts' => 0,
            'needs_assigned' => 0,
        ] );

        return $numbers;
    }


    /**
     * Return an associative array of sources for contacts that the current
     * user can see, (or all sources if the user has permission). The return
     * value looks like this:
     *
     *  $rv = [
     *      // $source_key => $source_label,
     *      "facebook" => null, // when a label could not be found for a source
     *      "phone" => "The phone",
     *      "partner" => "Our partners",
     *      "web" => "Website",
     *  ];
     *
     *  @access public
     *  @return array | WP_Error
     */
    public static function list_sources() {
        if ( !self::can_access( "contacts" ) ) {
            return new WP_Error( __FUNCTION__, "Permission denied.", [ 'status' => 403 ] );
        }
        global $wpdb;
        $source_labels = dt_get_option( 'dt_site_custom_lists' )['sources'];
        $rv = [];

        if ( current_user_can( 'view_any_contacts' ) ) {
            foreach ( $source_labels as $source_key => $source ) {
                if ( !isset( $source["enabled"] ) || $source["enabled"] != false ){
                    $rv[$source_key] = $source['label'];
                }
            }
            //check for sources not in the defined list
            $results = $wpdb->get_results(
                "SELECT DISTINCT meta_value FROM $wpdb->postmeta WHERE meta_key = 'sources'",
                ARRAY_N
            );
            foreach ( $results as $result ) {
                if ( ! array_key_exists( $result[0], $rv ) ) {
                    $rv[ $result[0] ] = null;
                }
            }
        } else {
            $user_id = get_current_user_id();
            // get the sources for the contacts shared with the user
            $results = $wpdb->get_results( $wpdb->prepare(
                "SELECT DISTINCT meta_value 
                FROM $wpdb->postmeta 
                JOIN $wpdb->dt_share as shares ON ( 
                    shares.post_id = $wpdb->postmeta.post_id
                    AND shares.user_id = %s
                )  
                WHERE meta_key = 'sources'",
                $user_id
            ), ARRAY_N );
            foreach ( $results as $result ) {
                $post_source_key = $result[0];
                if ( ! array_key_exists( $post_source_key, $rv ) ) {
                    if ( array_key_exists( $post_source_key, $source_labels ) ) {
                        if ( !isset( $source_labels[$post_source_key]["enabled"] ) || $source_labels[$post_source_key]["enabled"] != false ) {
                            $rv[ $post_source_key ] = $source_labels[ $post_source_key ]['label'];
                        }
                    } else {
                        $rv[ $post_source_key ] = null;
                    }
                }
            }
        }

        asort( $rv );
        return $rv;
    }


    /**
     * Make sure activity is created for all the steps before the current seeker path
     *
     * @param $contact_id
     * @param $initial_fields
     * @param $contact
     * @param $previous_values
     */
    public function check_seeker_path( $contact_id, $initial_fields, $contact, $previous_values ){
        if ( isset( $contact["seeker_path"]["key"] ) && $contact["seeker_path"]["key"] != "none" ){
            $current_key = $contact["seeker_path"]["key"];
            $prev_key = isset( $previous_values["seeker_path"]["key"] ) ? $previous_values["seeker_path"]["key"] : "none";
            $field_settings = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings();
            $seeker_path_options = $field_settings["seeker_path"]["default"];
            $option_keys = array_keys( $seeker_path_options );
            $current_index = array_search( $current_key, $option_keys );
            $prev_option_key = $option_keys[ $current_index - 1 ];

            if ( $prev_option_key != $prev_key && $current_index > array_search( $prev_key, $option_keys ) ){
                global $wpdb;
                $seeker_path_activity = $wpdb->get_results( $wpdb->prepare( "
                    SELECT meta_value, hist_time, meta_id
                    FROM $wpdb->dt_activity_log
                    WHERE object_id = %s
                    AND meta_key = 'seeker_path'
                ", $contact_id), ARRAY_A );
                $existing_keys = [];
                $most_recent = 0;
                $meta_id = 0;
                foreach ( $seeker_path_activity as $activity ){
                    $existing_keys[] = $activity["meta_value"];
                    if ( $activity["hist_time"] > $most_recent ){
                        $most_recent = $activity["hist_time"];
                    }
                    $meta_id = $activity["meta_id"];
                }
                $activity_to_create = [];
                for ( $i = $current_index; $i > 0; $i-- ){
                    if ( !in_array( $option_keys[$i], $existing_keys ) ){
                        $activity_to_create[] = $option_keys[$i];
                    }
                }
                foreach ( $activity_to_create as $missing_key ){
                    $wpdb->query( $wpdb->prepare("
                        INSERT INTO $wpdb->dt_activity_log
                        ( action, object_type, object_subtype, object_id, user_id, hist_time, meta_id, meta_key, meta_value, field_type )
                        VALUES ( 'field_update', 'contacts', 'seeker_path', %s, %d, %d, %d, 'seeker_path', %s, 'key_select' )",
                        $contact_id, get_current_user_id(), $most_recent - 1, $meta_id, $missing_key
                    ));
                }
            }
        }
    }


    /**
     * Get settings related to contacts
     * @return array|WP_Error
     */
    public static function get_settings(){
        if ( !self::can_access( "contacts" ) ) {
            return new WP_Error( __FUNCTION__, "Permission denied.", [ 'status' => 403 ] );
        }

        return [
            'sources' => self::list_sources(),
            'fields' => self::get_contact_fields(),
            'address_types' => self::$address_types,
            'channels' => self::$channel_list,
            'connection_types' => self::$contact_connection_types
        ];
    }
}
