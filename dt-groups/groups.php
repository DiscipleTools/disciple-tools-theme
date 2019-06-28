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
                    "people_groups",
                    "leaders",
                    "coaches"
                ];
                self::$channel_list = [
                    "address"
                ];
            }
        );
        add_filter( "dt_post_create_fields", [ $this, "create_post_field_hook" ], 10, 2 );
        add_action( "dt_post_created", [ $this, "post_created_hook" ], 10, 3 );
        add_action( 'group_member_count', [ $this, 'update_group_member_count' ], 10, 2 );
        add_filter( "dt_post_update_fields", [ $this, "update_post_field_hook" ], 10, 3 );
        add_filter( "dt_post_updated", [ $this, "post_updated_hook" ], 10, 3 );
        add_filter( "dt_get_post_fields_filter", [ $this, "dt_get_post_fields_filter" ], 10, 2 );
        add_action( "dt_comment_created", [ $this, "dt_comment_created" ], 10, 4 );
        add_action( "post_connection_removed", [ $this, "post_connection_removed" ], 10, 4 );
        add_action( "post_connection_added", [ $this, "post_connection_added" ], 10, 4 );

        parent::__construct();
    }


    /**
     * @param int $most_recent
     *
     * @return array|WP_Query
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
     * @return array|WP_Error
     */
    public static function get_group( int $group_id, bool $check_permissions = true, $load_cache = false ) {
        return DT_Posts::get_post( 'groups', $group_id, $load_cache, $check_permissions );
    }
    public function dt_get_post_fields_filter( $fields, $post_type ) {
        if ( $post_type === 'groups' ){
            $fields = apply_filters( 'dt_groups_fields_post_filter', $fields );
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
        return DT_Posts::update_post( 'groups', $group_id, $fields, $check_permissions );
    }

    //add the required fields to the DT_Post::create_group() function
    public function update_post_field_hook( $fields, $post_type, $post_id ){
        if ( $post_type === "groups" ){
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
                    DT_Posts::add_shared( "groups", $post_id, $user_id, null, false, true, false );
                }
            }
            $existing_group = DT_Posts::get_post( 'groups', $post_id, true, false );
            if ( isset( $fields["group_type"] ) && empty( $fields["church_start_date"] ) && empty( $existing_group["church_start_date"] ) && $fields["group_type"] === 'church' ){
                $fields["church_start_date"] = time();
            }
            if ( isset( $fields["group_status"] ) && empty( $fields["end_date"] ) && empty( $existing_group["end_date"] ) && $fields["group_status"] === 'inactive' ){
                $fields["end_date"] = time();
            }
        }
        return $fields;
    }

    public function post_updated_hook( $post_type, $post_id, $initial_fields ){
        if ( $post_type === 'groups' ){
            $group = DT_Posts::get_post( 'groups', $post_id, true, false );
            do_action( "dt_group_updated", array_keys( $initial_fields ), $group );
        }
    }


    public function post_connection_added( $post_type, $post_id, $field_key, $value ){
        if ( $post_type === "groups" ){
            if ( $field_key === "members" ){
                // share the group with the owner of the contact.
                $assigned_to = get_post_meta( $value, "assigned_to", true );
                if ( $assigned_to && strpos( $assigned_to, "-" ) !== false ){
                    $user_id = explode( "-", $assigned_to )[1];
                    if ( $user_id ){
                        self::add_shared_on_group( $post_id, $user_id, null, false, false );
                    }
                }
                do_action( 'group_member_count', $post_id, "added" );
            }
        }
    }

    public function post_connection_removed( $post_type, $post_id, $field_key, $value ){
        if ( $post_type === "groups" ){
            if ( $field_key === "members" ){
                do_action( 'group_member_count', $post_id, "removed" );
            }
        }
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
            update_post_meta( $group_id, 'member_count', intval( $member_count ) - 1 );
        }
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
     * @param int $group_id
     * @param string $comment_html
     * @param string $type
     * @param array $args
     * @param bool $check_permissions
     * @param bool $silent
     *
     * @return false|int|WP_Error
     */
    public static function add_comment( int $group_id, string $comment_html, string $type = "comment", array $args = [], bool $check_permissions = true, $silent = false ) {
        return DT_Posts::add_post_comment( 'groups', $group_id, $comment_html, $type, $args, $check_permissions, $silent );
    }
    public function dt_comment_created( $post_type, $post_id, $comment_id, $type ){
        if ( $post_type === "groups" ){
            if ( $type === "comment" ){
                self::check_requires_update( $post_id );
            }
        }
    }

    /**
     * @param int $group_id
     *
     * @return array|int|WP_Error
     */
    public static function get_comments( int $group_id ) {
        return DT_Posts::get_post_comments( 'groups', $group_id );
    }


    public static function delete_comment( int $group_id, int $comment_id, bool $check_permissions = true ){
        return DT_Posts::delete_post_comment( $comment_id, $check_permissions );
    }

    public static function update_comment( int $group_id, int $comment_id, string $comment_content, bool $check_permissions = true ){
        return DT_Posts::update_post_comment( $comment_id, $comment_content, $check_permissions );
    }

    /**
     * @param int $group_id
     *
     * @return array|null|object|WP_Error
     */
    public static function get_activity( int $group_id ) {
        return DT_Posts::get_post_activity( 'groups', $group_id );
    }

    /**
     * Gets an array of users whom the group is shared with.
     *
     * @param int $post_id
     *
     * @return array|mixed
     */
    public static function get_shared_with_on_group( int $post_id ) {
        return DT_Posts::get_shared_with( 'groups', $post_id );
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
        return DT_Posts::remove_shared( 'groups', $post_id, $user_id );
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
        return DT_Posts::add_shared( 'groups', $post_id, $user_id, $meta, $send_notifications, $check_permissions );
    }

    /**
     * Create a new group
     *
     * @param  array     $fields , the new group's data
     * @param  bool|true $check_permissions
     * @return int | WP_Error
     */
    public static function create_group( array $fields = [], $check_permissions = true ) {
        $group = DT_Posts::create_post( 'groups', $fields, false, $check_permissions );
        return is_wp_error( $group ) ? $group : $group["ID"];
    }

    //add the required fields to the DT_Post::create_contact() function
    public function create_post_field_hook( $fields, $post_type ){
        if ( $post_type === "groups" ) {
            if ( !isset( $fields["group_status"] ) ) {
                $fields["group_status"] = "active";
            }
            if ( !isset( $fields["group_type"] ) ) {
                $fields["group_type"] = "pre-group";
            }
            if ( !isset( $fields["assigned_to"] ) ) {
                $fields["assigned_to"] = sprintf( "user-%d", get_current_user_id() );
            }
            if ( !isset( $fields["start_date"] ) ) {
                $fields["start_date"] = time();
            }
            if ( isset( $fields["group_type"] ) && !isset( $fields["church_start_date"] ) && $fields["group_type"] === 'church' ){
                $fields["church_start_date"] = time();
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
        }
        return $fields;
    }

    public function post_created_hook( $post_type, $post_id, $initial_fields ){
        if ( $post_type === "groups" ){
            do_action( "dt_group_created", $post_id, $initial_fields );
            $group = DT_Posts::get_post( 'groups', $post_id, true, false );
            if ( isset( $group["assigned_to"] )) {
                if ( $group["assigned_to"]["id"] ) {
                    DT_Posts::add_shared( "groups", $post_id, $group["assigned_to"]["id"], null, false, false, false );
                }
            }
        }
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


    /**
     * Get settings related to contacts
     * @return array|WP_Error
     */
    public static function get_settings(){
        if ( !self::can_access( "groups" ) ) {
            return new WP_Error( __FUNCTION__, "Permission denied.", [ 'status' => 403 ] );
        }

        return [
            'fields' => self::$group_fields,
            'address_types' => self::$address_types,
            'channels' => self::$channel_list,
            'connection_types' => self::$group_connection_types
        ];
    }
}
