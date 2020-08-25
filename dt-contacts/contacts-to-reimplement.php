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
            if ( !isset( $fields["sources"] ) ) {
                $fields["sources"] = [ "values" => [ [ "value" => "personal" ] ] ];
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

    public function dt_comment_created( $post_type, $post_id, $comment_id, $type ){
        if ( $post_type === "contacts" ){
            if ( $type === "comment" ){
                self::check_requires_update( $post_id );
            }
        }
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
