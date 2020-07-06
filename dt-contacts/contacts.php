<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Disciple_Tools_Contacts
 */
class Disciple_Tools_Contacts extends Disciple_Tools_Posts
{

    /**
     * Disciple_Tools_Contacts constructor.
     */
    public function __construct() {

        add_action( "dt_contact_created", [ $this, "check_for_duplicates" ], 10, 2 );
        add_action( "dt_contact_updated", [ $this, "check_for_duplicates" ], 10, 2 );
        add_action( "dt_contact_updated", [ $this, "check_seeker_path" ], 10, 4 );
        add_filter( "dt_post_create_fields", [ $this, "create_post_field_hook" ], 10, 2 );
        add_action( "dt_post_created", [ $this, "post_created_hook" ], 10, 3 );
        add_filter( "dt_post_update_fields", [ $this, "update_post_field_hook" ], 10, 3 );
        add_action( "dt_post_updated", [ $this, "post_updated_hook" ], 10, 5 );
        add_filter( "dt_get_post_fields_filter", [ $this, "dt_get_post_fields_filter" ], 10, 2 );
        add_action( "dt_comment_created", [ $this, "dt_comment_created" ], 10, 4 );
        add_action( "post_connection_removed", [ $this, "post_connection_removed" ], 10, 4 );
        add_action( "post_connection_added", [ $this, "post_connection_added" ], 10, 4 );
        add_filter( "dt_user_list_filters", [ $this, "dt_user_list_filters" ], 10, 2 );
        add_filter( "dt_comments_additional_sections", [ $this, "add_comm_channel_comment_section" ], 10, 2 );

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
        $contact_settings = apply_filters( "dt_get_post_type_settings", [], "contacts" );
        return $contact_settings["channels"];
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
                    if ( !isset( $fields["overall_status"] ) ){
                        if ( $user_id != get_current_user_id() ){
                            if ( current_user_can( "assign_any_contacts" ) ) {
                                $fields["overall_status"] = 'assigned';
                            }
                            $fields['accepted'] = false;
                        } elseif ( isset( $existing_contact["overall_status"]["key"] ) && $existing_contact["overall_status"]["key"] === "assigned" ) {
                            $fields["overall_status"] = 'active';
                        }
                    }
                    if ( $user_id ){
                        DT_Posts::add_shared( "contacts", $post_id, $user_id, null, false, true, false );
                    }
                }
            }
            if ( isset( $fields["seeker_path"] ) ){
                self::update_quick_action_buttons( $post_id, $fields["seeker_path"] );
            }
            foreach ( $fields as $field_key => $value ){
                if ( strpos( $field_key, "quick_button" ) !== false ){
                    self::handle_quick_action_button_event( $post_id, [ $field_key => $value ] );
                }
            }
            if ( isset( $fields["overall_status"], $fields["reason_paused"] ) && $fields["overall_status"] === "paused"){
                $fields["requires_update"] = false;
            }
            if ( isset( $fields["overall_status"], $fields["reason_closed"] ) && $fields["overall_status"] === "closed"){
                $fields["requires_update"] = false;
            }
        }
        return $fields;
    }

    public function post_updated_hook( $post_type, $post_id, $update_query, $previous_values, $new_values ){
        if ( $post_type === 'contacts' ){
            do_action( "dt_contact_updated", $post_id, $update_query, $new_values, $previous_values );
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
            if ( $requires_update == "yes" || $requires_update == true || $requires_update == "1"){
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
                    $wpdb->postmeta
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

    public static function find_contacts_by_title( $title, $exclude_id, $exact_match = false ){
        global $wpdb;
        $dups = $wpdb->get_results(
            $wpdb->prepare("
                SELECT ID, post_title FROM $wpdb->posts
                WHERE post_title
                LIKE %s
                AND ID != %s
                AND post_type = 'contacts' AND post_status = 'publish'
                ORDER BY (post_title = %s) desc, length(post_title);
                ",
                $exact_match ? $wpdb->esc_like( $title ) : '%'. $wpdb->esc_like( $title ) .'%',
                $exclude_id,
                $wpdb->esc_like( $title )
            ), ARRAY_A
        );
        // if there are more than 25, it is most likely not a duplicate
        return sizeof( $dups ) > 25 ? [] : $dups;
    }

    public static function get_all_duplicates() {
        if ( !self::can_view_all( "contacts" ) ) {
            return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
        }

        global $wpdb;
        $post_settings = apply_filters( "dt_get_post_type_settings", [], "contacts" );
        $records = $wpdb->get_results( "
            SELECT pm.meta_value, pm.meta_key, pm.post_id, dd.meta_value as duplicate_data, rc.meta_value as reason_closed, p.post_title, status.meta_value as status
            FROM $wpdb->postmeta pm
            INNER JOIN (
                SELECT meta_value
                FROM $wpdb->postmeta
                WHERE meta_key LIKE 'contact_%' AND meta_key NOT LIKE '%details'
                GROUP BY meta_value
                HAVING count(meta_id) > 1 AND count(meta_id) < 10
            ) dup ON dup.meta_value = pm.meta_value
            LEFT JOIN $wpdb->posts as p ON ( p.ID = pm.post_id AND p.post_type = 'contacts' )
            LEFT JOIN $wpdb->postmeta as dd ON ( dd.post_id = pm.post_id AND dd.meta_key = 'duplicate_data' )
            LEFT JOIN $wpdb->postmeta as rc ON ( rc.post_id = pm.post_id AND rc.meta_key = 'reason_closed' )
            LEFT JOIN $wpdb->postmeta as status ON ( status.post_id = pm.post_id AND status.meta_key = 'overall_status' )
            WHERE pm.meta_key LIKE 'contact_%' AND pm.meta_key NOT LIKE '%details' AND pm.meta_value NOT LIKE ''
            AND ( rc.meta_value != 'duplicate' OR rc.meta_value IS NULL )
        ", ARRAY_A );

        $dups = [];
        foreach ( $records as $duplicate ){
            $key = explode( '_', $duplicate["meta_key"] )[0] . '_' . explode( '_', $duplicate["meta_key"] )[1];
            $duplicate_data = maybe_unserialize( $duplicate["duplicate_data"] );
            if ( !isset( $dups[$key][$duplicate["meta_value"]] ) ) {
                $dups[$key][$duplicate["meta_value"]] = [
                    "overrides" => [],
                    "posts" => []
                ];
            }
            $dups[$key][$duplicate["meta_value"]]["overrides"] = array_merge( $dups[$key][$duplicate["meta_value"]]["overrides"], $duplicate_data["override"] ?? [] );
            if ( $duplicate["reason_closed"] !== "duplicate" ){
                $dups[$key][$duplicate["meta_value"]]["posts"][$duplicate["post_id"]] = [
                    "name" => $duplicate['post_title'],
                    "status" => $duplicate['status'],
                    "reason_closed" => $duplicate["reason_closed"] ?? null,
                ];
            }
            foreach ( $dups[$key][$duplicate["meta_value"]]["overrides"] as $id ){
                if ( isset( $dups[$key][$duplicate["meta_value"]]["posts"][$id] ) ){
                    unset( $dups[$key][$duplicate["meta_value"]]["posts"][$id] );
                }
            }
        }
        $return = [];
        foreach ( $dups as $channel => $channel_values ) {
            foreach ( $channel_values as $index => $duplicate ){
                if ( sizeof( $duplicate["posts"] ) < 2 ){
                    unset( $dups[$channel][$index] );
                }
            }

            $channel_key = explode( '_', $channel )[1];
            $return[$channel] = [
                "name" => isset( $post_settings["channels"][$channel_key]['label'] ) ? $post_settings["channels"][$channel_key]['label'] : $channel,
                "dups" => $dups[$channel]
            ];
        }


        return $return;

    }

    public static function get_duplicates_on_contact( $contact_id, $include_contacts = true, $exact_match = false ){
        if ( !self::can_access( 'contacts' ) ) {
            return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
        }

        $contact = self::get_contact( $contact_id );

        $possible_duplicates = self::get_possible_duplicates( $contact_id, $contact, $exact_match );
        $ordered = [];

        $shared_with_ids = [];
        if ( !current_user_can( "view_any_contacts" ) ) {
            global $wpdb;
            $shared_with_ids_query = $wpdb->get_results( $wpdb->prepare( "
                SELECT post_id
                FROM $wpdb->dt_share
                WHERE user_id = %s
            ", get_current_user_id() ), ARRAY_A );
            foreach ( $shared_with_ids_query as $res ){
                $shared_with_ids[] = $res["post_id"];
            }
        }

        foreach ( $possible_duplicates as $field_key => $dups ){
            foreach ( $dups as $dup ){
                if ( current_user_can( "view_any_contacts" ) || in_array( $dup["ID"], $shared_with_ids ) ){
                    if ( empty( $dup["ID"] ) ) {
                        continue;
                    }
                    if ( !isset( $ordered[$dup["ID"]] ) ) {
                        $ordered[$dup["ID"]] = [
                            "ID" => $dup["ID"],
                            "points" => 0,
                            "fields" => []
                        ];
                    }
                    if ( $field_key !== 'title' || ( $dup["post_title"] ?? '' ) === $contact["title"] ) {
                        $ordered[$dup["ID"]]["points"] += $field_key === 'title' ? 1 : 2; //increment for exact matches
                    }
                    $ordered[$dup["ID"]]["fields"][] = array_merge( [ "field" => $field_key ], $dup );
                    if ( $include_contacts ){
                        $ordered[$dup["ID"]]["contact"] = DT_Posts::get_post( "contacts", $dup["ID"] );
                    }
                }
            }
        }
        $return = [];
        foreach ( $ordered as $id => $dup ) {
            $return[] = $dup;
        }
        return $return;
    }

    public function check_for_duplicates( $contact_id, $fields ){
        $contact = DT_Posts::get_post( "contacts", $contact_id, true, false );
        if ( is_wp_error( $contact ) ){
            return $contact;
        }
        $duplicate_data = $contact["duplicate_data"] ?? [];
        $possible_duplicates = self::get_possible_duplicates( $contact_id, $contact, true, empty( $duplicate_data["check_dups"] ) ? $fields : [] );
        if ( !isset( $duplicate_data["override"] )){
            $duplicate_data["override"] = [];
        }
        $dup_ids = [];
        foreach ( $possible_duplicates as $field_key => $dups ){
            foreach ( $dups as $dup ){
                if ( !in_array( $dup["ID"], $dup_ids ) ) {
                    $dup_ids[] = $dup["ID"];
                }
            }
        }

        if ( sizeof( $dup_ids ) > sizeof( $duplicate_data["override"] ) ){
            $duplicate_data["check_dups"] = true;
        } else {
            $duplicate_data["check_dups"] = false;
        }
        self::save_duplicate_data( $contact_id, $duplicate_data );
    }

    public static function get_possible_duplicates( $contact_id, $contact, $exact_match = false, $changed_fields = [] ){
        $fields_to_check = [ "contact_phone", "contact_email", "contact_address", "title" ];
        $fields_to_check = apply_filters( "dt_contact_duplicate_fields_to_check", $fields_to_check );
        $duplicates = [];
        $meta_query_fields = [];
        $query = '';
        foreach ( $fields_to_check as $field_id ){
            if ( !empty( $contact[$field_id] ) & ( empty( $changed_fields ) || in_array( $field_id, array_keys( $changed_fields ) ) ) ) {
                $field_value = $contact[$field_id];
                if ( $field_id == "title" ){
                    $contacts = self::find_contacts_by_title( $field_value, $contact_id, $exact_match );
                    $duplicates[$field_id] = $contacts;
                } else {
                    if ( isset( $field_value["values"] ) ){
                        $values = $field_value["values"];
                    } else {
                        $values = $field_value;
                    }
                    foreach ( $values as $val ){
                        if ( !empty( $val["value"] ) ){
                            $meta_query_fields[] = [ $field_id => $val["value"] ];
                            $query .= ( $query ? ' OR ' : ' ' );
                            $query .= " ( meta_key LIKE '" . esc_sql( $field_id ) . "%' AND meta_value LIKE '" . ( $exact_match ? esc_sql( $val["value"] ) : ( '%%' . trim( esc_sql( $val["value"] ) )  . '%%' ) ) . "' )";
                        }
                    }
                }
            }
        }
        if ( !empty( $query ) ) {

            global $wpdb;
            //phpcs:disable
            $matches = $wpdb->get_results( $wpdb->prepare("
                SELECT post_id as ID, meta_key, meta_value
                FROM $wpdb->postmeta
                INNER JOIN $wpdb->posts posts ON ( posts.ID = post_id AND posts.post_type = 'contacts' AND posts.post_status = 'publish' )
                WHERE ( $query )
                AND post_id != %s
            ", esc_sql( $contact_id ) ), ARRAY_A );
            //phpcs:enable
            $by_value = [];
            foreach ( $matches as $match ){
                $key = explode( '_', $match["meta_key"] )[0] . '_' . explode( '_', $match["meta_key"] )[1];
                $by_value[$key][$match["meta_value"]][] = $match;
            }
            foreach ( $by_value as $key => $values ){
                foreach ( $values as $meta_value => $matched ) {
                    // if there are more than 20, it is most likely not a duplicate
                    if ( sizeof( $matched ) < 20 ){
                        foreach ( $matched as $match ){
                            $duplicates[$key][] = $match;
                        }
                    }
                }
            }
        }

        return $duplicates;
    }

    public static function save_duplicate_data( int $contact_id, array $duplicates) {
        if (empty( $duplicates )) { return; }
        $duplicates["override"] = array_values( $duplicates["override"] );
        update_post_meta( $contact_id, "duplicate_data", $duplicates );
    }

    public static function dismiss_all( int $contact_id) {
        if ( !$contact_id) { return; }
        $contact = self::get_contact( $contact_id );
        $possible_duplicates = self::get_duplicates_on_contact( $contact_id, false );
        $data = isset( $contact['duplicate_data'] ) ? is_array( $contact['duplicate_data'] ) ? $contact['duplicate_data'] : unserialize( $contact['duplicate_data'] ) : array();
        foreach ( $possible_duplicates as $dup ){
            $data['override'][] = (int) $dup["ID"];
        }
        $data['override'] = array_values( array_unique( $data['override'] ) );
        $data["check_dups"] = false;
        self::save_duplicate_data( $contact_id, $data );
        return $data;
    }

    public static function dismiss_duplicate( int $contact_id, int $dismiss_id) {
        if ( !$contact_id || !$dismiss_id) { return; }
        $contact = self::get_contact( $contact_id );
        $duplicate_data = isset( $contact['duplicate_data'] ) ? is_array( $contact['duplicate_data'] ) ? $contact['duplicate_data'] : unserialize( $contact['duplicate_data'] ) : array();
        if ( !in_array( $dismiss_id, $duplicate_data["override"] ) ) {
            $duplicate_data["override"][] = $dismiss_id;
        }
        if ( $duplicate_data["chek_dups"] === true ){
            $possible_dups = self::get_possible_duplicates( $contact_id, $contact, true );
            if ( sizeof( $possible_dups ) <= sizeof( $duplicate_data["override"] ) ){
                $data["check_dups"] = false;
            }
        }
        self::save_duplicate_data( $contact_id, $duplicate_data );
        return $duplicate_data;
    }

    public static function close_duplicate_contact( int $duplicate_id, int $contact_id) {
        $duplicate = self::get_contact( $duplicate_id );
        $contact = self::get_contact( $contact_id );

        self::update_contact( $duplicate_id, [
            "overall_status" => "closed",
            "reason_closed" => "duplicate",
            "duplicate_of" => $contact_id
        ] );

        $link = "[" . $contact['title'] .  "](" .  $contact_id . ")";
        $comment = sprintf( esc_html_x( 'This record is a duplicate and was merged into %2$s', 'This record duplicated and was merged into Contact2', 'disciple_tools' ), $duplicate['title'], $link );

        $args = [
            "user_id" => 0,
            "comment_author" => __( "Duplicate Checker", 'disciple_tools' )
        ];

        self::add_comment( $duplicate_id, $comment, "duplicate", $args, true, true );
        self::dismiss_all( $duplicate_id );

        $user = wp_get_current_user();
        //comment on master
        $link = "[" . $duplicate['title'] .  "](" .  $duplicate_id . ")";
        $comment = sprintf( esc_html_x( '%1$s merged %2$s into this record', 'User1 merged Contact1 into this record', 'disciple_tools' ), $user->display_name, $link );
        self::add_comment( $contact_id, $comment, "duplicate", $args, true, true );
    }


    public static function merge_posts( $contact1, $contact2, $args ){
        $contact_fields = self::get_contact_fields();
        $phones = $args["phone"] ?? [];
        $emails = $args["email"] ?? [];
        $addresses = $args["address"] ?? [];

        $master_id = $args["master-record"] ?? $contact1;
        $non_master_id = ( $master_id === $contact1 ) ? $contact2 : $contact1;
        $contact = DT_Posts::get_post( "contacts", $master_id );
        $non_master = DT_Posts::get_post( "contacts", $non_master_id );

        if ( is_wp_error( $contact ) ) { return $contact; }
        if ( is_wp_error( $non_master ) ) { return $non_master; }


        $current = array(
            'contact_phone' => array(),
            'contact_email' => array(),
            'contact_address' => array(),
            // 'contact_facebook' => array()
        );

        foreach ( $contact as $key => $fields ) {
            if ( strpos( $key, "contact_" ) === 0 ) {
                $split = explode( "_", $key );
                if ( !isset( $split[1] ) ) {
                    continue;
                }
                $new_key = $split[0] . "_" . $split[1];
                foreach ( $contact[ $new_key ] ?? array() as $values ) {
                    $current[ $new_key ][ $values['key'] ] = $values['value'];
                }
            }
        }

        $update = array(
            'contact_phone' => array( 'values' => array() ),
            'contact_email' => array( 'values' => array() ),
            'contact_address' => array( 'values' => array() ),
            // 'contact_facebook' => array( 'values' => array() )
        );

        $update_for_duplicate = [];

        $ignore_keys = array();

        foreach ($phones as $phone) {
            $index = array_search( $phone, $current['contact_phone'] );
            if ($index !== false) { $ignore_keys[] = $index;
                continue; }
            array_push( $update['contact_phone']['values'], [ 'value' => $phone ] );
        }
        foreach ($emails as $email) {
            $index = array_search( $email, $current['contact_email'] );
            if ($index !== false) { $ignore_keys[] = $index;
                continue; }
            array_push( $update['contact_email']['values'], [ 'value' => $email ] );
        }
        foreach ($addresses as $address) {
            $index = array_search( $address, $current['contact_address'] );
            if ($index !== false) { $ignore_keys[] = $index;
                continue; }
            array_push( $update['contact_address']['values'], [ 'value' => $address ] );
        }

        /*
            Merge social media + other contact data from the non master to master
        */
        foreach ( $non_master as $key => $fields ) {
            if ( isset( $contact_fields[$key] ) && $contact_fields[$key]["type"] === "multi_select" ){
                $update[$key]["values"] = [];
                foreach ( $fields as $field_value ){
                    $update[$key]["values"][] = [ "value" => $field_value ];
                }
            }
            if ( isset( $contact_fields[ $key ] ) && $contact_fields[ $key ]["type"] === "key_select" && ( !isset( $contact[ $key ] ) || $key === "none" || $key === "" ) ) {
                $update[$key] = $fields["key"];
            }
            if ( isset( $contact_fields[$key] ) && $contact_fields[$key]["type"] === "text" && ( !isset( $contact[$key] ) || empty( $contact[$key] ) )){
                $update[$key] = $fields;
            }
            if ( isset( $contact_fields[$key] ) && $contact_fields[$key]["type"] === "number" && ( !isset( $contact[$key] ) || empty( $contact[$key] ) )){
                $update[$key] = $fields;
            }
            if ( isset( $contact_fields[$key] ) && $contact_fields[$key]["type"] === "date" && ( !isset( $contact[$key] ) || empty( $contact[$key]["timestamp"] ) )){
                $update[$key] = $fields["timestamp"] ?? "";
            }
            if ( isset( $contact_fields[$key] ) && $contact_fields[$key]["type"] === "array" && ( !isset( $contact[$key] ) || empty( $contact[$key] ) )){
                if ( $key != "duplicate_data" ){
                    $update[$key] = $fields;
                }
            }
            if ( isset( $contact_fields[$key] ) && $contact_fields[$key]["type"] === "connection" && ( !isset( $contact[$key] ) || empty( $contact[$key] ) )){
                $update[$key]["values"] = [];
                $update_for_duplicate[$key]["values"] = [];
                foreach ( $fields as $field_value ){
                    $update[$key]["values"][] = [ "value" => $field_value["ID"] ];
                    $update_for_duplicate[$key]["values"][] = [
                        "value" => $field_value["ID"],
                        "delete" => true
                    ];
                }
            }


            if ( strpos( $key, "contact_" ) === 0 ) {
                $split = explode( "_", $key );
                if ( !isset( $split[1] ) ) {
                    continue;
                }
                $new_key = $split[0] . "_" . $split[1];
                if ( in_array( $new_key, array_keys( $update ) ) ) {
                    continue;
                }
                $update[ $new_key ] = array(
                    'values' => array()
                );
                foreach ( $non_master[ $new_key ] ?? array() as $values ) {
                    $index = array_search( $values['value'], $current[ $new_key ] ?? array() );
                    if ( $index !== false ) {
                        $ignore_keys[] = $index;
                        continue;
                    }
                    array_push( $update[ $new_key ]['values'], array(
                        'value' => $values['value']
                    ) );
                }
            }
        }

        $delete_fields = array();
        if ($update['contact_phone']['values']) { $delete_fields[] = 'contact_phone'; }
        if ($update['contact_email']['values']) { $delete_fields[] = 'contact_email'; }
        if ($update['contact_address']['values']) { $delete_fields[] = 'contact_address'; }

        if ( !empty( $delete_fields )) {
            self::remove_fields( $master_id, $delete_fields, $ignore_keys );
        }

        //copy over comments
        $comments = DT_Posts::get_post_comments( "contacts", $non_master_id );
        foreach ( $comments["comments"] as $comment ){
            $comment["comment_post_ID"] = $master_id;
            if ( $comment["comment_type"] === "comment" ){
                $comment["comment_content"] = sprintf( esc_html_x( '(From Duplicate): %s', 'duplicate comment', 'disciple_tools' ), $comment["comment_content"] );
            }
            if ( $comment["comment_type"] !== "duplicate" && !empty( $comment["comment_content"] ) ) {
                wp_insert_comment( $comment );
            }
        }


        // copy over users the contact is shared with.
        global $wpdb;
        $wpdb->query( $wpdb->prepare( "
            INSERT INTO $wpdb->dt_share (user_id, post_id )
            SELECT user_id, %d
            FROM $wpdb->dt_share
            WHERE post_id = %d
            AND user_id NOT IN ( SELECT user_id FROM wp_dt_share WHERE post_id = %d )
        ", $master_id, $non_master_id, $master_id ) );

        //Keep duplicate data override info.
        $contact["duplicate_data"]["override"] = array_merge( $contact["duplicate_data"]["override"] ?? [], $non_master["duplicate_data"]["override"] ?? [] );
        $update["duplicate_data"] = $contact["duplicate_data"];

        $current_user_id = get_current_user_id();
        wp_set_current_user( 0 ); // to keep the merge activity from a specific user.
        $current_user = wp_get_current_user();
        $current_user->display_name = __( "Duplicate Checker", 'disciple_tools' );
        $update_return = DT_Posts::update_post( "contacts", $master_id, $update, true, false );
        if ( is_wp_error( $update_return ) ) { return $update_return; }
        $non_master_update_return = DT_Posts::update_post( "contacts", $non_master_id, $update_for_duplicate, true, false );
        if ( is_wp_error( $non_master_update_return ) ) { return $non_master_update_return; }
        wp_set_current_user( $current_user_id );

        self::dismiss_duplicate( $master_id, $non_master_id );
        self::dismiss_duplicate( $non_master_id, $master_id );
        self::close_duplicate_contact( $non_master_id, $master_id );

        do_action( "dt_contact_merged", $master_id, $non_master_id );
        return true;
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
        $query_sql = "";
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
            if ( current_user_can( "access_specific_sources" ) && $tab === "all" ){
                $allowed_sources = get_user_option( 'allowed_sources', get_current_user_id() );
                $sources_sql = dt_array_to_sql( $allowed_sources );
                $all_access = "Left JOIN $wpdb->postmeta AS source_access ON ( a.ID = source_access.post_id AND source_access.meta_key = 'sources' )";
                $query_sql .= "  AND source_access.meta_value IN ( $sources_sql ) ";
                $all_access .= " LEFT JOIN $wpdb->dt_share AS shares ON ( shares.post_id = a.ID AND shares.user_id = " . $user_id . " ) ";
                $query_sql .= " OR shares.user_id = " . $user_id . " ";
            }
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

        //filter out the contacts linked to users.
        $user_posts = $wpdb->get_results( "
            SELECT post_id FROM $wpdb->postmeta
            WHERE meta_key = 'type' AND meta_value = 'user'
            GROUP BY post_id
        ", ARRAY_A);
        $user_posts = dt_array_to_sql( array_map( function ( $g ) {
            return $g["post_id"];
        }, $user_posts ) );

        // phpcs:disable
        // WordPress.WP.PreparedSQL.NotPrepare
        $contacts_by_status = $wpdb->get_results( $wpdb->prepare( "
            SELECT pm.meta_value, count(pm.meta_value) as count
            FROM $wpdb->postmeta pm
            INNER JOIN $wpdb->posts a ON( a.ID = pm.post_id AND a.post_type = 'contacts' and a.post_status = 'publish' )
            " . $access_sql . "
            WHERE pm.meta_key = %s
            AND pm.post_id NOT IN ( $user_posts )
            GROUP BY pm.meta_value
        ", esc_sql( 'overall_status' ) ), ARRAY_A );
        $active_seeker_path = $wpdb->get_results( $wpdb->prepare( "
            SELECT pm.meta_value, count(pm.meta_value) as count
            FROM $wpdb->postmeta pm
            INNER JOIN $wpdb->posts a ON( a.ID = pm.post_id AND a.post_type = 'contacts' and a.post_status = 'publish' )
            INNER JOIN $wpdb->postmeta status ON( status.post_id = pm.post_id AND status.meta_key = 'overall_status' and status.meta_value = 'active' )
            " . $access_sql . "
            WHERE pm.meta_key = %s
            AND pm.post_id NOT IN ( $user_posts )
            GROUP BY pm.meta_value
        ", esc_sql( 'seeker_path' ) ), ARRAY_A );
        // phpcs:enable


        $numbers["total_my"] = 0;
        foreach ( $contacts_by_status as $value ){
            if ( $value["meta_value"] === "closed" && !$show_closed ){
                continue;
            }
            $numbers["total_my"] += (int) $value["count"];
            switch ( $value["meta_value"] ){
                case "active":
                    $numbers["active"] = $value["count"];
                    break;
                case "assigned":
                    $numbers["needs_accepted"] = $value["count"];
                    break;
                case "unassigned":
                    $numbers["needs_assigned"] = $value["count"];
                    break;
                case "new":
                    $numbers["new"] = $value["count"];
                    break;
                case "unassignable":
                    $numbers["unassignable"] = $value["count"];
                    break;
            }
        }
        foreach ( $active_seeker_path as $value ){
            switch ( $value["meta_value"] ){
                case "none":
                    $numbers["contact_unattempted"] = $value["count"];
                    break;
                case "scheduled":
                    $numbers["meeting_scheduled"] = $value["count"];
                    break;
            }
        }


        // phpcs:disable
        // WordPress.WP.PreparedSQL.NotPrepare
        $personal_counts = $wpdb->get_results("
            SELECT (
                SELECT count( DISTINCT( a.ID ) )
                FROM $wpdb->posts as a
                  " . $access_sql . $closed . "
                WHERE a.post_status = 'publish'
                AND post_type = 'contacts'
                " . $query_sql . "
                AND a.ID NOT IN ( $user_posts )
            ) as total_count,
            (
                SELECT count(a.ID)
                FROM $wpdb->posts as a
                " . $all_access . $closed . "
                WHERE a.post_status = 'publish'
                AND post_type = 'contacts'
                AND a.ID NOT IN ( $user_posts )
            ) as total_all,
            (
                SELECT count(a.ID)
                FROM $wpdb->posts as a
                " . $my_access . $closed . "
                WHERE a.post_status = 'publish'
                AND post_type = 'contacts'
                AND a.ID NOT IN ( $user_posts )
            ) as total_my,
            (
                SELECT count(a.ID)
                FROM $wpdb->posts as a
                " . $subassigned_access . $closed . "
                WHERE a.post_status = 'publish'
                AND post_type = 'contacts'
                AND a.ID NOT IN ( $user_posts )
            ) as total_subassigned,
            (
                SELECT count(a.ID)
                FROM $wpdb->posts as a
                " . $shared_access . $closed . "
                WHERE a.post_status = 'publish'
                AND post_type = 'contacts'
                AND a.ID NOT IN ( $user_posts )
            ) as total_shared,
            (
                SELECT count(a.ID)
                FROM $wpdb->posts as a
                " . $all_access . $closed . "
                WHERE a.post_status = 'publish'
                AND post_type = 'contacts'
                AND a.ID NOT IN ( $user_posts )
            ) as total_all,
            (
                SELECT count(a.ID)
                FROM $wpdb->posts as a
                " . $access_sql . $closed . "
                JOIN $wpdb->postmeta as b
                  ON a.ID=b.post_id
                    AND b.meta_key = 'requires_update'
                    AND b.meta_value = '1'
                WHERE a.post_status = 'publish'
                " . $query_sql . "
                AND post_type = 'contacts'
                AND a.ID NOT IN ( $user_posts )
            ) as update_needed
            ", ARRAY_A );
        // phpcs:enable

        if ( empty( $personal_counts ) ) {
            return new WP_Error( __METHOD__, 'No results from the personal count query' );
        }

        foreach ( $personal_counts[0] as $key => $value ) {
            $numbers[$key] = $value;
        }
        // phpcs:enable

        $numbers = wp_parse_args( $numbers, [
            'my_contacts' => '0',
            'update_needed' => '0',
            'needs_accepted' => '0',
            'contact_unattempted' => '0',
            'meeting_scheduled' => '0',
            'all_contacts' => '0',
            'needs_assigned' => '0',
            'new' => '0',
            'unassignable' => '0',
            'unassigned' => '0'
        ] );

        return $numbers;
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

        $contact_settings = apply_filters( "dt_get_post_type_settings", [], "contacts" );
        return [
            'sources' => $contact_settings["sources"],
            'fields' => $contact_settings["fields"],
            'address_types' => $contact_settings["address_types"],
            'channels' => $contact_settings["channels"],
            'connection_types' => $contact_settings['connection_types'],
        ];
    }


    public static function get_user_posts(){
        $user_posts = get_transient( "contact_ids_for_users" );
        if ( $user_posts ){
            return dt_array_to_sql( array_map( function ( $g ) {
                return $g["post_id"];
            }, $user_posts ) );
        }
        //filter out the contacts linked to users.
        global $wpdb;
        $user_posts = $wpdb->get_results( "
            SELECT post_id FROM $wpdb->postmeta
            WHERE meta_key = 'type' AND meta_value = 'user'
            GROUP BY post_id
        ", ARRAY_A);

        set_transient( "contact_ids_for_users", $user_posts, 3600 );
        return dt_array_to_sql( array_map( function ( $g ) {
            return (int) $g["post_id"];
        }, $user_posts ) );
    }

    public static function get_my_contacts_status_seeker_path(){
        global $wpdb;
        $user_post = Disciple_Tools_Users::get_contact_for_user( get_current_user_id() ) ?? 0;
        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT type.meta_value as type, status.meta_value as overall_status, pm.meta_value as seeker_path, count(pm.post_id) as count, count(un.post_id) as update_needed
            FROM $wpdb->postmeta pm
            INNER JOIN $wpdb->postmeta status ON( status.post_id = pm.post_id AND status.meta_key = 'overall_status' AND status.meta_value != 'closed')
            INNER JOIN $wpdb->posts a ON( a.ID = pm.post_id AND a.post_type = 'contacts' and a.post_status = 'publish' )
            LEFT JOIN $wpdb->postmeta un ON ( un.post_id = pm.post_id AND un.meta_key = 'requires_update' AND un.meta_value = '1' )
            LEFT JOIN $wpdb->postmeta type ON ( type.post_id = pm.post_id AND type.meta_key = 'type' )
            WHERE pm.meta_key = 'seeker_path'
            AND (
                pm.post_id IN ( SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'assigned_to' AND meta_value = CONCAT( 'user-', %s ) )
                OR pm.post_id IN ( SELECT p2p_to from $wpdb->p2p WHERE p2p_from = %s AND p2p_type = 'contacts_to_subassigned' )
            )
            GROUP BY type.meta_value, status.meta_value, pm.meta_value
        ", get_current_user_id(), $user_post ), ARRAY_A);
        return $results;
    }


    public static function get_all_contacts_status_seeker_path(){
        global $wpdb;
        $results = [];

        $can_view_all = false;
        if ( current_user_can( 'access_specific_sources' ) ) {
            $sources = get_user_option( 'allowed_sources', get_current_user_id() ) ?? [];
            if ( empty( $sources ) || in_array( 'all', $sources ) ) {
                $can_view_all = true;
            }
        }

        if ( current_user_can( "view_any_contacts" ) || $can_view_all ) {
            $results = $wpdb->get_results("
                SELECT type.meta_value as type, status.meta_value as overall_status, pm.meta_value as seeker_path, count(pm.post_id) as count, count(un.post_id) as update_needed
                FROM $wpdb->postmeta pm
                INNER JOIN $wpdb->postmeta status ON( status.post_id = pm.post_id AND status.meta_key = 'overall_status' AND status.meta_value != 'closed' )
                INNER JOIN $wpdb->posts a ON( a.ID = pm.post_id AND a.post_type = 'contacts' and a.post_status = 'publish' )
                LEFT JOIN $wpdb->postmeta type ON ( type.post_id = pm.post_id AND type.meta_key = 'type' )
                LEFT JOIN $wpdb->postmeta un ON ( un.post_id = pm.post_id AND un.meta_key = 'requires_update' AND un.meta_value = '1' )
                WHERE pm.meta_key = 'seeker_path'
                GROUP BY type.meta_value, status.meta_value, pm.meta_value
            ", ARRAY_A);
        } else if ( current_user_can( 'access_specific_sources' ) ) {
            $sources = get_user_option( 'allowed_sources', get_current_user_id() ) ?? [];
            $sources_sql = dt_array_to_sql( $sources );
            // phpcs:disable
            $results = $wpdb->get_results( $wpdb->prepare( "
                SELECT type.meta_value as type, status.meta_value as overall_status, pm.meta_value as seeker_path, count(pm.post_id) as count, count(un.post_id) as update_needed
                FROM $wpdb->postmeta pm
                INNER JOIN $wpdb->postmeta status ON( status.post_id = pm.post_id AND status.meta_key = 'overall_status' AND status.meta_value != 'closed' )
                INNER JOIN $wpdb->posts a ON( a.ID = pm.post_id AND a.post_type = 'contacts' and a.post_status = 'publish' )
                LEFT JOIN $wpdb->postmeta type ON ( type.post_id = pm.post_id AND type.meta_key = 'type' )
                LEFT JOIN $wpdb->postmeta un ON ( un.post_id = pm.post_id AND un.meta_key = 'requires_update' AND un.meta_value = '1' )
                WHERE pm.meta_key = 'seeker_path'
                AND ( 
                    pm.post_id IN ( SELECT post_id from $wpdb->postmeta as source where source.meta_value IN ( $sources_sql ) ) 
                    OR pm.post_id IN ( SELECT post_id FROM $wpdb->dt_share AS shares where shares.user_id = %s ) 
                )
                GROUP BY type.meta_value, status.meta_value, pm.meta_value
            ", esc_sql( get_current_user_id() ) ) , ARRAY_A );
            // phpcs:enable
        }
        return $results;
    }


    private static function increment( &$var, $val ){
        if ( !isset( $var ) ){
            $var = 0;
        }
        $var += (int) $val;
    }

    public static function dt_user_list_filters( $filters, $post_type ){
        if ( $post_type === 'contacts' ){
            $counts = self::get_my_contacts_status_seeker_path();
            $fields = self::get_contact_fields();

            /**
             * Setup my contacts filters
             */
            $active_counts = [];
            $update_needed = 0;
            $status_counts = [];
            $total_my = 0;
            foreach ( $counts as $count ){
                if ( $count["type"] != "user" ){
                    $total_my += $count["count"];
                    self::increment( $status_counts[$count["overall_status"]], $count["count"] );
                    if ( $count["overall_status"] === "active" ){
                        if ( isset( $count["update_needed"] ) ) {
                            $update_needed += (int) $count["update_needed"];
                        }
                        self::increment( $active_counts[$count["seeker_path"]], $count["count"] );
                    }
                }
            }
            if ( !isset( $status_counts["closed"] ) ) {
                $status_counts["closed"] = '';
            }

            $filters["tabs"][] = [
                "key" => "assigned_to_me",
                "label" => sprintf( _x( "My %s", 'My records', 'disciple_tools' ), Disciple_Tools_Contact_Post_Type::instance()->plural ),
                "count" => $total_my,
                "order" => 20
            ];
            // add assigned to me filters
            $filters["filters"][] = [
                'ID' => 'my_all',
                'tab' => 'assigned_to_me',
                'name' => _x( "All", 'List Filters', 'disciple_tools' ),
                'query' => [
                    'assigned_to' => [ 'me' ],
                    'subassigned' => [ 'me' ],
                    'combine' => [ 'subassigned' ],
                    'overall_status' => [ '-closed' ],
                    'sort' => 'overall_status'
                ],
                "count" => $total_my,
            ];
            foreach ( $fields["overall_status"]["default"] as $status_key => $status_value ) {
                if ( isset( $status_counts[$status_key] ) ) {
                    $filters["filters"][] = [
                        "ID" => 'my_' . $status_key,
                        "tab" => 'assigned_to_me',
                        "name" => $status_value["label"],
                        "query" => [
                            'assigned_to' => [ 'me' ],
                            'subassigned' => [ 'me' ],
                            'combine' => [ 'subassigned' ],
                            'overall_status' => [ $status_key ],
                            'sort' => 'seeker_path'
                        ],
                        "count" => $status_counts[$status_key]
                    ];
                    if ( $status_key === "active" ){
                        if ( $update_needed > 0 ){
                            $filters["filters"][] = [
                                "ID" => 'my_update_needed',
                                "tab" => 'assigned_to_me',
                                "name" => $fields["requires_update"]["name"],
                                "query" => [
                                    'assigned_to' => [ 'me' ],
                                    'subassigned' => [ 'me' ],
                                    'combine' => [ 'subassigned' ],
                                    'overall_status' => [ 'active' ],
                                    'requires_update' => [ true ],
                                    'sort' => 'seeker_path'
                                ],
                                "count" => $update_needed,
                                'subfilter' => true
                            ];
                        }
                        foreach ( $fields["seeker_path"]["default"] as $seeker_path_key => $seeker_path_value ) {
                            if ( isset( $active_counts[$seeker_path_key] ) ) {
                                $filters["filters"][] = [
                                    "ID" => 'my_' . $seeker_path_key,
                                    "tab" => 'assigned_to_me',
                                    "name" => $seeker_path_value["label"],
                                    "query" => [
                                        'assigned_to' => [ 'me' ],
                                        'subassigned' => [ 'me' ],
                                        'combine' => [ 'subassigned' ],
                                        'overall_status' => [ 'active' ],
                                        'seeker_path' => [ $seeker_path_key ],
                                        'sort' => 'name'
                                    ],
                                    "count" => $active_counts[$seeker_path_key],
                                    'subfilter' => true
                                ];
                            }
                        }
                    }
                }
            }

            /**
             * Setup dispatcher filters
             */
            if ( current_user_can( "view_any_contacts" ) || current_user_can( 'access_specific_sources' ) ) {
                $counts = self::get_all_contacts_status_seeker_path();
                $all_active_counts = [];
                $all_update_needed = 0;
                $all_status_counts = [];
                $total_all = 0;
                foreach ( $counts as $count ){
                    if ( $count["type"] !== "user" ){
                        $total_all += $count["count"];
                        self::increment( $all_status_counts[$count["overall_status"]], $count["count"] );
                        if ( $count["overall_status"] === "active" ){
                            if ( isset( $count["update_needed"] ) ) {
                                $all_update_needed += (int) $count["update_needed"];
                            }
                            self::increment( $all_active_counts[$count["seeker_path"]], $count["count"] );
                        }
                    }
                }
                if ( !isset( $all_status_counts["closed"] ) ) {
                    $all_status_counts["closed"] = '';
                }
                $filters["tabs"][] = [
                    "key" => "all_dispatch",
                    "label" => sprintf( _x( "All %s", 'All records', 'disciple_tools' ), Disciple_Tools_Contact_Post_Type::instance()->plural ),
                    "count" => $total_all,
                    "order" => 10
                ];
                // add assigned to me filters
                $filters["filters"][] = [
                    'ID' => 'all_dispatch',
                    'tab' => 'all_dispatch',
                    'name' => _x( "All", 'List Filters', 'disciple_tools' ),
                    'query' => [
                        'overall_status' => [ '-closed' ],
                        'sort' => 'overall_status'
                    ],
                    "count" => $total_all,
                ];

                foreach ( $fields["overall_status"]["default"] as $status_key => $status_value ) {
                    if ( isset( $all_status_counts[$status_key] ) ) {
                        $filters["filters"][] = [
                            "ID" => 'all_' . $status_key,
                            "tab" => 'all_dispatch',
                            "name" => $status_value["label"],
                            "query" => [
                                'overall_status' => [ $status_key ],
                                'sort' => 'seeker_path'
                            ],
                            "count" => $all_status_counts[$status_key]
                        ];
                        if ( $status_key === "active" ){
                            if ( $all_update_needed > 0 ){
                                $filters["filters"][] = [
                                    "ID" => 'all_update_needed',
                                    "tab" => 'all_dispatch',
                                    "name" => $fields["requires_update"]["name"],
                                    "query" => [
                                        'overall_status' => [ 'active' ],
                                        'requires_update' => [ true ],
                                        'sort' => 'seeker_path'
                                    ],
                                    "count" => $all_update_needed,
                                    'subfilter' => true
                                ];
                            }
                            foreach ( $fields["seeker_path"]["default"] as $seeker_path_key => $seeker_path_value ) {
                                if ( isset( $all_active_counts[$seeker_path_key] ) ) {
                                    $filters["filters"][] = [
                                        "ID" => 'all_' . $seeker_path_key,
                                        "tab" => 'all_dispatch',
                                        "name" => $seeker_path_value["label"],
                                        "query" => [
                                            'overall_status' => [ 'active' ],
                                            'seeker_path' => [ $seeker_path_key ],
                                            'sort' => 'name'
                                        ],
                                        "count" => $all_active_counts[$seeker_path_key],
                                        'subfilter' => true
                                    ];
                                }
                            }
                        }
                    }
                }
            }
            $filters["filters"] = self::add_default_custom_list_filters( $filters["filters"] );
        }
        return $filters;
    }

    public static function add_default_custom_list_filters( $filters ){
        if ( empty( $filters )){
            $filters = [];
        }
        $default_filters = [
            [
                'ID' => 'my_coached',
                'visible' => "1",
                'type' => 'default',
                'tab' => 'custom',
                'name' => 'Coached by me',
                'query' => [
                    'coached_by' => [ 'me' ],
                    'sort' => 'seeker_path',
                ],
                'labels' => [
                    [
                        'id' => 'my_coached',
                        'name' => 'Coached by be',
                        'field' => 'coached_by',
                    ],
                ],
            ],
            [
                'ID' => 'my_subassigned',
                'visible' => "1",
                'type' => 'default',
                'tab' => 'custom',
                'name' => 'Subassigned to me',
                'query' => [
                    'subassigned' => [ 'me' ],
                    'sort' => 'overall_status',
                ],
                'labels' => [
                    [
                        'id' => 'my_subassigned',
                        'name' => 'Subassigned to me',
                        'field' => 'subassigned',
                    ],
                ],
            ],
            [
                'ID' => 'my_shared',
                'visible' => "1",
                'type' => 'default',
                'tab' => 'custom',
                'name' => 'Shared with me',
                'query' => [
                    'assigned_to' => [ 'shared' ],
                    'sort' => 'overall_status',
                ],
                'labels' => [
                    [
                        'id' => 'my_shared',
                        'name' => 'Shared with me',
                        'field' => 'subassigned',
                    ],
                ],
            ]
        ];
        $contact_filter_ids = array_map( function ( $a ){
            return $a["ID"];
        }, $filters );
        foreach ( $default_filters as $filter ) {
            if ( !in_array( $filter["ID"], $contact_filter_ids ) ){
                array_unshift( $filters, $filter );
            }
        }
        //translation for default fields
        foreach ( $filters as $index => $filter ) {
            if ( $filter["name"] === 'Subassigned to me' ) {
                $filters[$index]["name"] = __( 'Subassigned only', 'disciple_tools' );
                $filters[$index]['labels'][0]['name'] = __( 'Subassigned only', 'disciple_tools' );
            }
            if ( $filter["name"] === 'Shared with me' ) {
                $filters[$index]["name"] = __( 'Shared with me', 'disciple_tools' );
                $filters[$index]['labels'][0]['name'] = __( 'Shared with me', 'disciple_tools' );
            }
            if ( $filter["name"] === 'Coached by me' ) {
                $filters[$index]["name"] = __( 'Coached by me', 'disciple_tools' );
                $filters[$index]['labels'][0]['name'] = __( 'Coached by me', 'disciple_tools' );
            }
        }
        return $filters;
    }

    public function add_comm_channel_comment_section( $sections, $post_type ){
        $channels = Disciple_Tools_Contact_Post_Type::instance()->get_channels_list();
        if ( $post_type === "contacts" ){
            foreach ( $channels as $channel_key => $channel_option ) {
                $enabled = !isset( $channel_option['enabled'] ) || $channel_option['enabled'] !== false;
                $hide_domain = isset( $channel_option['hide_domain'] ) && $channel_option['hide_domain'] == true;
                if ( $channel_key == 'phone' || $channel_key == 'email' || $channel_key == 'address' ){
                    continue;
                }

                $sections[] = [
                    "key" => $channel_key,
                    "label" => esc_html( $channel_option["label"] ?? $channel_key )
                ];
            }
        }
        return $sections;
    }
}
