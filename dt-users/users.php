<?php
/**
 * Contains create, update and delete functions for users, wrapping access to the database
 *
 * @package  Disciple.Tools
 * @category Plugin
 * @author   Disciple.Tools
 * @since    0.1.0
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

/**
 * Class Disciple_Tools_Users
 * Functions for creating, finding, updating or deleting contacts
 */
class Disciple_Tools_Users
{
    /**
     * Disciple_Tools_Users constructor.
     */
    public function __construct() {
        //wp admin user list customization
        add_filter( 'dt_settings_js_data', [ $this, 'add_current_locations_list' ], 10, 1 );
        add_filter( 'dt_settings_js_data', [ $this, 'get_date_availability_hook' ], 10, 1 );
    }


    public static function can_update( int $user_id ){
        if ( get_current_user_id() === $user_id ){
            return true;
        } else if ( current_user_can( 'list_users' ) ){
            return true;
        }
        return false;
    }

    /**
     * If the current user can view the user record and info
     * @param int $user_id
     * @return bool
     */
    public static function can_view( int $user_id ){
        if ( get_current_user_id() === $user_id ){
            return true;
        } else if ( current_user_can( 'list_users' ) ){
            return true;
        }
        return false;
    }

    public static function current_user_can_upgrade_users(){
        return current_user_can( 'list_users' ) || current_user_can( 'promote_users' ) || current_user_can( 'manage_dt' );
    }

    /**
     * If the current user can see the queried user in a list
     * @param int $user_id
     * @return bool
     */
    public static function can_list( int $user_id ): bool{
        // I can list myself
        if ( get_current_user_id() === $user_id ){
            return true;
        }
        // if I have permission to list all users
        if ( current_user_can( 'list_users' ) || current_user_can( 'dt_list_users' ) || current_user_can( 'promote_users' ) || current_user_can( 'manage_dt' ) ){
            return true;
        }
        //check if there is a record shared with both users
        global $wpdb;
        $has_a_user_connection = $wpdb->get_var( $wpdb->prepare("
            SELECT user_id
            FROM $wpdb->dt_share
            WHERE post_id IN (
            SELECT post_id
                FROM $wpdb->dt_share
                WHERE user_id = %d
            )
            AND user_id = %d
            GROUP BY user_id
        ", get_current_user_id(), esc_sql( $user_id ) ) );
        if ( !is_wp_error( $has_a_user_connection ) && !empty( $has_a_user_connection ) ){
            return true;
        }
        // I can list all dispatchers
        $dispatchers = $wpdb->get_results( $wpdb->prepare( "
            SELECT user_id FROM $wpdb->usermeta um
            WHERE meta_key = '{$wpdb->prefix}capabilities'
            AND user_id = %d
            AND meta_value LIKE %s
        ", esc_sql( $user_id ), '%dispatcher%' ), ARRAY_A );
        if ( !is_wp_error( $dispatchers ) && !empty( $dispatchers ) ){
            return true;
        }
        return false;
    }

    /**
     * Check if a user exists and is part of the current site
     * @param $user_id
     * @return bool
     */
    public static function is_instance_user( $user_id ){
        $user = get_user_by( 'ID', $user_id );
        if ( !empty( $user ) && ( is_user_member_of_blog( $user->ID ) || is_super_admin( $user->ID ) ) ){
            return true;
        }
        return false;
    }


    public static function get_users_fields( $from_cache = true ){
        global $wpdb;
        $cache = wp_cache_get( 'dt_users_fields' );
        if ( $from_cache && $cache ){
            return $cache;
        }

        $fields = [
            'ID' => [
                'table' => 'users_table',
                'label' => __( 'ID', 'disciple_tools' ),
                'type' => 'number',
            ],
            'user_email' => [
                'table' => 'users_table',
                'label' => __( 'Email', 'disciple_tools' ),
                'type' => 'text',
                'hidden' => true
            ],
            'user_login' => [
                'table' => 'users_table',
                'label' => __( 'Username', 'disciple_tools' ),
                'type' => 'text',
                'hidden' => true
            ],
            'display_name' => [
                'table' => 'users_table',
                'label' => __( 'Display Name', 'disciple_tools' ),
                'type' => 'text',
            ],
            'capabilities' => [
                'table' => 'usermeta_table',
                'key' => $wpdb->prefix . 'user_languages',
                'label' => __( 'Roles', 'disciple_tools' ),
                'options' => Disciple_Tools_Roles::get_dt_roles_and_permissions(),
                'type' => 'array_keys',
            ],
            'locale' => [
                'table' => 'usermeta_table',
                'key' => 'locale',
                'label' => __( 'Locale', 'disciple_tools' ),
                'options' => dt_get_available_languages( true ),
                'type' => 'key_select',
                'hidden' => true,
            ],
            'user_languages' => [
                'table' => 'usermeta_table',
                'key' => $wpdb->prefix . 'user_languages',
                'label' => __( 'Languages', 'disciple_tools' ),
                'options' => dt_get_option( 'dt_working_languages' ) ?: [],
                'type' => 'array',
            ],
            'location_grid' => [
                'table' => 'usermeta_table',
                'key' => $wpdb->prefix . 'location_grid',
                'label' => __( 'Locations', 'disciple_tools' ),
                'type' => 'location_grid',
            ],
            'user_status' => [
                'table' => 'usermeta_table',
                'key' => $wpdb->prefix . 'user_status',
                'label' => __( 'Status', 'disciple_tools' ),
                'options' => [
                    'active' => [ 'label' => __( 'Active', 'disciple_tools' ) ],
                    'away' => [ 'label' => __( 'Away', 'disciple_tools' ) ],
                    'inconsistent' => [ 'label' => __( 'Inconsistent', 'disciple_tools' ) ],
                    'inactive' => [ 'label' => __( 'Inactive', 'disciple_tools' ) ],
                ],
                'type' => 'key_select',
            ],
            'workload_status' => [
                'table' => 'usermeta_table',
                'key' => $wpdb->prefix . 'workload_status',
                'label' => __( 'Workload Status', 'disciple_tools' ),
                'options' => dt_get_site_custom_lists()['user_workload_status'] ?? [],
                'type' => 'key_select',
            ],
            'last_activity' => [
                'label' => 'Last Activity',
                'type' => 'date',
                'table' => 'dt_activity_log',
                'meta_key' => 'activity_date',
            ]
        ];

        $fields = apply_filters( 'dt_users_fields', $fields );
        wp_cache_set( 'dt_users_fields', $fields );
        return $fields;
    }

    /**
     * Get assignable users
     *
     * @param string|null $search_string
     * @param bool $get_all
     * @return array|WP_Error
     */
    public static function get_assignable_users_compact( string $search_string = null, bool $get_all = false, string $post_type = null ) {
        if ( !current_user_can( 'access_contacts' ) ) {
            return new WP_Error( __FUNCTION__, __( 'No permissions to assign', 'disciple_tools' ), [ 'status' => 403 ] );
        }

        global $wpdb;
        $user_id = get_current_user_id();
        $users = [];
        $update_needed = [];
        if ( !current_user_can( 'dt_all_access_contacts' ) && !current_user_can( 'dt_list_users' ) ){
            // users that are shared posts that are shared with me
            if ( $search_string ){
                $users_ids = $wpdb->get_results( $wpdb->prepare("
                    SELECT user_id
                    FROM $wpdb->dt_share
                    INNER JOIN $wpdb->users as u ON ( u.ID = user_id AND display_name LIKE %s )
                    WHERE post_id IN (
                          SELECT post_id
                          FROM $wpdb->dt_share
                          WHERE user_id = %s
                    )
                    GROUP BY user_id
                    ",
                    '%' . $search_string .'%',
                    $user_id
                ), ARRAY_N );
            } else {
                $users_ids = $wpdb->get_results( $wpdb->prepare("
                    SELECT user_id
                    FROM $wpdb->dt_share
                    WHERE post_id IN (
                          SELECT post_id
                          FROM $wpdb->dt_share
                          WHERE user_id = %1\$s
                    )
                    GROUP BY user_id
                    ",
                    $user_id
                ), ARRAY_N );

            }

            if ( $search_string ){
                $dispatchers = $wpdb->get_results($wpdb->prepare( "
                    SELECT user_id FROM $wpdb->usermeta um
                    INNER JOIN $wpdb->users u ON ( u.ID = um.user_id AND display_name LIKE %s )
                    WHERE meta_key = '{$wpdb->prefix}capabilities'
                    AND meta_value LIKE %s
                ", '%' . esc_sql( $search_string ) . '%', '%dispatcher%' ) );

            } else {
                $dispatchers = $wpdb->get_results("
                    SELECT user_id FROM $wpdb->usermeta
                    WHERE meta_key = '{$wpdb->prefix}capabilities'
                    AND meta_value LIKE '%dispatcher%'
                ");
            }

            $assure_unique = [];
            foreach ( $dispatchers as $index ){
                $id = $index->user_id;
                if ( $id && !in_array( $id, $assure_unique ) ){
                    $assure_unique[] = $id;
                    $users[] = get_user_by( 'ID', $id );
                }
            }
            foreach ( $users_ids as $index ){
                $id = $index[0];
                if ( $id && !in_array( $id, $assure_unique ) ){
                    $assure_unique[] = $id;
                    $users[] = get_user_by( 'ID', $id );
                }
            }
        } else {
            $correct_roles = dt_multi_role_get_cap_roles( 'access_' . $post_type );
            $search_string = esc_attr( $search_string );
            $user_query = new WP_User_Query( [
                'search'         => '*' . $search_string . '*',
                'search_columns' => [
                    'user_login',
                    'user_nicename',
                    'user_email',
                    'display_name'
                ],
                'role__in' => $correct_roles,
                'number' => $get_all ? 1000 : 50
            ] );

            $users = $user_query->get_results();

            //used cached updated needed data if available
            //@todo refresh the cache if not available
            $dispatcher_user_data = get_transient( 'dispatcher_user_data' );
            if ( $dispatcher_user_data ){
                foreach ( maybe_unserialize( $dispatcher_user_data ) as $uid => $val ){
                    $update_needed['user-' . $uid] = $val['number_update'];
                }
            } else {
                $ids = [];
                foreach ( $users as $a ){
                    $ids[] = 'user-' . $a->ID;
                }
                $user_ids = dt_array_to_sql( $ids );
                //phpcs:disable
                $update_needed_result = $wpdb->get_results("
                    SELECT pm.meta_value, COUNT(update_needed.post_id) as count
                    FROM $wpdb->postmeta pm
                    INNER JOIN $wpdb->postmeta as update_needed on (update_needed.post_id = pm.post_id and update_needed.meta_key = 'requires_update' and update_needed.meta_value = '1' )
                    WHERE pm.meta_key = 'assigned_to' and pm.meta_value IN ( $user_ids )
                    GROUP BY pm.meta_value
                ", ARRAY_A );
                //phpcs:enable
                foreach ( $update_needed_result as $up ){
                    $update_needed[$up['meta_value']] = $up['count'];
                }
            }
        }
        $list = [];

        $workload_status_options = dt_get_site_custom_lists()['user_workload_status'] ?? [];

        foreach ( $users as $user ) {
            if ( empty( $post_type ) || user_can( $user, 'access_' . $post_type ) ) {
                $u = [
                    'name' => wp_specialchars_decode( $user->display_name ),
                    'ID'   => $user->ID,
                    'avatar' => get_avatar_url( $user->ID, [ 'size' => '16', 'scheme' => 'https' ] ),
                    'contact_id' => self::get_contact_for_user( $user->ID )
                ];
                //extra information for the dispatcher
                if ( current_user_can( 'dt_all_access_contacts' ) && !$get_all ){
                    $workload_status = get_user_option( 'workload_status', $user->ID );
                    if ( $workload_status && isset( $workload_status_options[ $workload_status ]['color'] ) ) {
                        $u['status_color'] = $workload_status_options[$workload_status]['color'];
                    }
                    $u['update_needed'] = $update_needed['user-' . $user->ID] ?? 0;
                }
                $list[] = $u;
            }
        }

        if ( !function_exists( 'asc_meth' ) ){
            function asc_meth( $a, $b ){
                $a['name'] = strtolower( $a['name'] );
                $b['name'] = strtolower( $b['name'] );
                return strcmp( $a['name'], $b['name'] );
            }
        }

        $list = apply_filters( 'dt_assignable_users_compact', $list, $search_string, $get_all );

        usort( $list, 'asc_meth' );
        return $list;
    }



    /**
     * @param $user_id
     * @return int|WP_Error|null the contact ID
     */
    public static function get_contact_for_user( $user_id ){
        if ( !current_user_can( 'access_contacts' ) ){
            return new WP_Error( 'no_permission', __( 'Insufficient permissions', 'disciple_tools' ), [ 'status' => 403 ] );
        }
        $contact_id = get_user_option( 'corresponds_to_contact', $user_id );

        if ( !empty( $contact_id ) && get_post( $contact_id ) ){
            return (int) $contact_id;
        }
        $args = [
            'post_type'  => 'contacts',
            'relation'   => 'AND',
            'meta_query' => [
                [
                    'key' => 'corresponds_to_user',
                    'value' => $user_id
                ],
                [
                    'key' => 'type',
                    'value' => 'user'
                ],
            ],
        ];
        $contacts = new WP_Query( $args );
        if ( isset( $contacts->post->ID ) ){
            update_user_option( $user_id, 'corresponds_to_contact', $contacts->post->ID );
            return (int) $contacts->post->ID;
        } else {
            return null;
        }
    }


    /**
     * Get the base user for the system
     * You can call this function using dt_get_base_user( $id_only = false )
     *
     * @param $id_only     (optional) Default is false and function returns entire WP_User object.
     *
     * @return array|WP_Error|WP_User
     *@since 0.1.0
     *
     */
    public static function get_base_user( $id_only ) {

        // get base user id
        $base_user_id = dt_get_option( 'base_user' );
        if ( ! $base_user_id ) {
            return new WP_Error( 'failed_to_get_base_user', 'Failed to get base user. dt_get_option( base_user ) failed.' );
        }

        // get base user object and test if user exists
        $base_user = get_user_by( 'ID', $base_user_id );
        if ( empty( $base_user ) ) { // if base_user has been deleted.
            update_option( 'dt_base_user', false ); // clear current value
            $base_user_id = dt_get_option( 'base_user' ); // call the reset process to re-assign new base user.
            $base_user = get_user_by( 'ID', $base_user_id );
        }

        // test if id and object are populated
        if ( empty( $base_user ) || empty( $base_user_id ) ) {
            return new WP_Error( 'failed_to_get_base_user', 'Failed to get base user object or id using id: '. $base_user_id );
        }

        if ( $id_only ) {
            return $base_user_id;
        }

        return $base_user;
    }


    /**
     * @param $user_name
     * @param $user_email
     * @param $display_name
     * @param array $user_roles
     * @param null $corresponds_to_contact
     * @param null $locale
     * @param bool $return_contact_id
     * @return int|WP_Error|array
     */
    public static function create_user( $user_name, $user_email, $display_name, array $user_roles = [ 'multiplier' ], $corresponds_to_contact = null, $locale = null, bool $return_contact_id = false, $password = null, $optional_fields = null, $archive_comments = false ){
        if ( !current_user_can( 'create_users' ) && !DT_User_Management::non_admins_can_make_users() ){
            return new WP_Error( 'no_permissions', "You don't have permissions to create users", [ 'status' => 401 ] );
        }

        if ( !current_user_can( 'create_users' ) && DT_User_Management::non_admins_can_make_users() ) {
            $user_roles = [ 'multiplier' ];
            if ( $corresponds_to_contact && ! DT_Posts::can_view( 'contacts', (int) $corresponds_to_contact ) ) {
                return new WP_Error( 'no_permissions', "You don't have permission to create a user for this contact", [ 'status' => 401 ] );
            }
        }
        if ( $corresponds_to_contact ){
            $existing_contact = DT_Posts::get_post( 'contacts', $corresponds_to_contact, true, false );
        }

        $user_email = sanitize_email( wp_unslash( $user_email ) );
        $user_name = sanitize_user( wp_unslash( $user_name ), true );
        $display_name = sanitize_text_field( wp_unslash( $display_name ) );

        $user_id = email_exists( $user_email );
        if ( $user_id ){

            if ( is_user_member_of_blog( $user_id ) ){
                $contact_id = self::get_contact_for_user( $user_id );
                if ( ! $contact_id ) {
                    self::create_contact_for_user( $user_id );
                    return $user_id;
                }

                return new WP_Error( 'email_exists', __( 'Email already exists and is a user on this site', 'disciple_tools' ), [ 'status' => 409 ] );
            } else {

                $blog_id = get_current_blog_id();
                $addition = add_user_to_blog( $blog_id, $user_id, $user_roles[0] ?? 'multiplier' );
                if ( is_wp_error( $addition ) ) {
                    return new WP_Error( 'failed_to_add_user', __( 'Failed to add user to site.', 'disciple_tools' ), [ 'status' => 409 ] );
                }
                self::save_user_roles( $user_id, $user_roles );
            }
        } else {
            $user_id = register_new_user( $user_name, $user_email );
            if ( is_wp_error( $user_id ) ){
                return $user_id;
            }
            if ( $password && current_user_can( 'create_users' ) ) {
                wp_set_password( $password, $user_id );
            }
            $user = get_user_by( 'id', $user_id );
            $user->display_name = $display_name;
            wp_update_user( $user );
            self::save_user_roles( $user_id, $user_roles );
        }

        global $wpdb;
        update_user_meta( $user_id, $wpdb->prefix . 'user_status', 'active' );
        update_user_meta( $user_id, $wpdb->prefix . 'workload_status', 'active' );

        if ( $optional_fields ) {
            foreach ( $optional_fields as $key => $value ) {
                if ( $key === 'gender' ) {
                    update_user_option( $user_id, 'user_gender', $value );
                } else {
                    update_user_meta( $user_id, $key, $value );
                }
            }
        }

        if ( $corresponds_to_contact ) {
            update_user_meta( $user_id, $wpdb->prefix . 'corresponds_to_contact', $corresponds_to_contact );
            update_post_meta( $corresponds_to_contact, 'corresponds_to_user', $user_id );
            update_post_meta( $corresponds_to_contact, 'type', 'user' );

            if ( $archive_comments && !is_wp_error( $existing_contact ) ){
                $archive_contact = DT_Posts::create_post( 'contacts', [
                    'type' => 'placeholder',
                    'overall_status' => 'closed',
                    'title' => $existing_contact['title'] . ' - ' . __( 'Archived', 'disciple_tools' ),
                ] );
                if ( isset( $archive_contact['ID'] ) ){
                    $current_user_id = get_current_user_id();
                    //move comments to new archive post
                    $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->comments SET comment_post_ID = %d WHERE comment_post_ID = %d", $archive_contact['ID'], $corresponds_to_contact ) );
                    //share with same users
                    $wpdb->query( $wpdb->prepare( "INSERT INTO $wpdb->dt_share (user_id, post_id) SELECT DISTINCT user_id, %s FROM $wpdb->dt_share WHERE post_id = %d AND user_id != %d", $archive_contact['ID'], $corresponds_to_contact, $current_user_id ) );
                    //add @mention to the person who archived the comments
                    $mention = dt_get_user_mention_syntax( $current_user_id );
                    $comment = sprintf( _x( 'These are archived comments for %1$s', 'These are archived comments for contact John Doe', 'disciple_tools' ), '[' . $existing_contact['title'] . '](' . $existing_contact['permalink'] .')' );
                    wp_set_current_user( 0 );
                    DT_Posts::add_post_comment( 'contacts', $archive_contact['ID'], $mention . ' ' . $comment, 'comment', [], false, false );
                    wp_set_current_user( $current_user_id );
                }
            }

            // Transfer any existing sub-assignments
            foreach ( p2p_get_connections( 'contacts_to_subassigned', [ 'from' => $corresponds_to_contact ] ) as $p2p ) {
                if ( isset( $p2p->p2p_to ) ) {
                    DT_Posts::add_shared( 'contacts', $p2p->p2p_to, $user_id );
                }
            }
        }

        do_action( 'dt_user_created', $user_id );
        if ( $return_contact_id ) {
            return [
                'user_id' => $user_id,
                'corresponds_to_contact' => self::get_contact_for_user( $user_id ),
            ];
        }
        return $user_id;
    }

    /**
     * Create a Contact for each user that registers
     *
     * @param $user_id
     * @return bool|int|WP_Error
     */
    public static function create_contact_for_user( $user_id ) {
        $user = get_user_by( 'id', $user_id );
        $corresponds_to_contact = get_user_option( 'corresponds_to_contact', $user_id );
        if ( $user && $user->has_cap( 'access_contacts' ) && is_user_member_of_blog( $user_id ) ) {
            if ( empty( $corresponds_to_contact ) ){
                $args = [
                    'post_type'  => 'contacts',
                    'relation'   => 'AND',
                    'meta_query' => [
                        [
                            'key' => 'corresponds_to_user',
                            'value' => $user_id
                        ],
                        [
                            'key' => 'type',
                            'value' => 'user'
                        ],
                    ],
                ];
                $contacts = new WP_Query( $args );
                if ( isset( $contacts->post->ID ) ){
                    $corresponds_to_contact = $contacts->post->ID;
                    update_user_option( $user_id, 'corresponds_to_contact', $corresponds_to_contact );
                }
            }
            if ( empty( $corresponds_to_contact ) ){
                $args = [
                    'post_type'  => 'contacts',
                    'relation'   => 'AND',
                    'meta_query' => [
                        [
                            'key' => 'corresponds_to_user_name',
                            'value' => $user->user_login
                        ]
                    ],
                ];
                $contacts = new WP_Query( $args );
                if ( isset( $contacts->post->ID ) ){
                    $corresponds_to_contact = $contacts->post->ID;
                    update_user_option( $user_id, 'corresponds_to_contact', $corresponds_to_contact );
                    update_post_meta( $corresponds_to_contact, 'corresponds_to_user', $user_id );
                }
            }

            if ( empty( $corresponds_to_contact ) || get_post( $corresponds_to_contact ) === null ) {
                $new_user_contact = DT_Posts::create_post( 'contacts', [
                    'title'               => $user->display_name,
                    'type'                => 'user',
                    'corresponds_to_user' => $user_id,
                    'contact_email'       => [ 'values' => [ [ 'value' => $user->user_email ] ] ],
                ], true, false );
                if ( !is_wp_error( $new_user_contact ) ){
                    update_user_option( $user_id, 'corresponds_to_contact', $new_user_contact['ID'] );

                    /**
                     * The user's corresponding contact has been created, you can update the corresponding contact with user information
                     *
                     * @param array $new_user_contact
                     * @param WP_User $user
                     */
                    do_action( 'dt_create_users_corresponding_contact', $new_user_contact, $user );

                    return $new_user_contact['ID'];
                }
            } else {
                $contact = get_post( $corresponds_to_contact );
                if ( $contact && $contact->post_title != $user->display_name && $user->display_name != $user->user_login ){
                    DT_Posts::update_post( 'contacts', $corresponds_to_contact, [
                        'title' => $user->display_name
                    ], false, false );
                }

                /**
                 * The user profile has updated, you can update the corresponding contact with user information
                 *
                 * @param WP_Post $contact
                 * @param WP_User $user
                 */
                do_action( 'dt_update_users_corresponding_contact', $contact, $user );

                return $contact->ID;
            }
        }
        return false;
    }

    public static function save_user_roles( $user_id, $roles ){
        if ( !self::current_user_can_upgrade_users() ){
            $roles = [ 'multiplier' ];
        }

        $can_not_promote_to_roles = [];
        if ( !dt_is_administrator() ){
            $can_not_promote_to_roles = [ 'administrator' ];
        }
        if ( !current_user_can( 'manage_dt' ) ){
            // get roles that can `manage_dt`
            $can_not_promote_to_roles = array_merge( $can_not_promote_to_roles, dt_multi_role_get_cap_roles( 'manage_dt' ) );
        }

        // Create a new user object.
        $u = new WP_User( $user_id );

        // Sanitize the posted roles.
        $new_roles = array_map( 'dt_multi_role_sanitize_role', array_map( 'sanitize_text_field', wp_unslash( $roles ) ) );

        // Get the current user roles.
        $old_roles = (array) $u->roles;

        // Loop through the posted roles.
        foreach ( $new_roles as $new_role ) {

            // If the user doesn't already have the role, add it.
            if ( dt_multi_role_is_role_editable( $new_role ) ) {
                if ( !in_array( $new_role, $can_not_promote_to_roles ) && !in_array( $new_role, $old_roles ) ){
                    $u->add_role( $new_role );
                }
            }
        }
        // Loop through the current user roles.
        foreach ( $old_roles  as $old_role ) {

            // If the role is editable and not in the new roles array, remove it.
            if ( dt_multi_role_is_role_editable( $old_role ) && !in_array( $old_role, $new_roles ) ) {
                if ( !in_array( $old_role, $can_not_promote_to_roles ) ){
                    $u->remove_role( $old_role );
                }
            }
        }
        return (array) $u->roles;
    }

    //======================================================================
    // USER FILTERS
    //======================================================================

    public static function get_user_filters( $post_type, $force_refresh = false ){
        $current_user_id = get_current_user_id();
        $filters = [];
        if ( $current_user_id ){
            $filters = get_user_option( "dt_cached_filters_$post_type", $current_user_id );
            if ( !empty( $filters ) && $force_refresh === false ) {
                return $filters;
            }
            $custom_filters = maybe_unserialize( get_user_option( 'saved_filters', $current_user_id ) );
            $filters = [
                'tabs' => [
                    [
                        'key' => 'custom',
                        'label' => _x( 'Custom Filters', 'List Filters', 'disciple_tools' ),
                        'order' => 99
                    ]
                ],
                'filters' => []
            ];
            foreach ( $custom_filters[$post_type] ?? [] as $filter ){
                $filter['tab'] = 'custom';
                $filter['ID'] = (string) $filter['ID'];
                $filters['filters'][] = $filter;
            }


            $filters = apply_filters( 'dt_user_list_filters', $filters, $post_type );
            usort( $filters['tabs'], function ( $a, $b ) {
                return ( $a['order'] ?? 50 ) <=> ( $b['order'] ?? 51 );
            } );
            update_user_option( $current_user_id, "dt_cached_filters_$post_type", $filters );
        }
        return $filters;
    }

    public static function save_user_filter( $filter, $post_type ){
        $current_user_id = get_current_user_id();
        if ( $current_user_id && isset( $filter['ID'] ) ){
            $filter = filter_var_array( $filter, FILTER_SANITIZE_STRING );
            $filters = get_user_option( 'saved_filters', $current_user_id );
            if ( !isset( $filters[$post_type] ) ){
                $filters[$post_type] = [];
            }

            $updated = false;
            foreach ( $filters[$post_type] as $index => $f ){
                if ( $f['ID'] === $filter['ID'] ){
                    $filters[$post_type][$index] = $filter;
                    $updated = true;
                }
            }
            if ( $updated === false ){
                $filters[$post_type][] = $filter;
            }
            update_user_option( $current_user_id, 'saved_filters', $filters );
            update_user_option( $current_user_id, "dt_cached_filters_$post_type", [] );
        }
        return true;
    }

    public static function delete_user_filter( $id, $post_type ){
        $current_user_id = get_current_user_id();
        if ( $current_user_id ){
            $filters = get_user_option( 'saved_filters', $current_user_id );
            if ( !isset( $filters[$post_type] ) ){
                $filters[$post_type] = [];
            }
            $index_to_remove = null;
            foreach ( $filters[$post_type] as $index => $filter ){
                if ( (string) $filter['ID'] === (string) $id ){
                    $index_to_remove = $index;
                }
            }
            if ( $index_to_remove !== null ){
                unset( $filters[$post_type][$index_to_remove] );
                $filters[$post_type] = array_values( $filters[$post_type] );
                update_user_option( $current_user_id, 'saved_filters', $filters );
                return true;
            } else {
                return false;
            }
        }
        return false;
    }


    //======================================================================
    // USER PROFILE
    //======================================================================

    public static function update_settings_on_user( int $user_id, $body ){
        if ( !self::can_update( $user_id ) ){
            return new WP_Error( __METHOD__, 'Missing Permissions', [ 'status' => 401 ] );
        }
        delete_transient( 'dispatcher_user_data' );
        $user = get_user_by( 'ID', $user_id );
        if ( !$user ){
            return new WP_Error( 'user_id', 'User does not exist', [ 'status' => 400 ] );
        }
        if ( isset( $body['user_status'] ) ) {
            return update_user_option( $user->ID, 'user_status', $body['user_status'] );
        }
        if ( isset( $body['workload_status'] ) ) {
            return update_user_option( $user->ID, 'workload_status', $body['workload_status'] );
        }
        if ( !empty( $body['add_location'] ) ){
            return self::add_user_location( $body['add_location'], $user->ID );
        }
        if ( !empty( $body['remove_location'] ) ){
            return self::delete_user_location( $body['remove_location'], $user->ID );
        }
        if ( !empty( $body['add_unavailability'] ) ){
            return self::add_date_availability( $body['add_unavailability'], $user->ID );
        }
        if ( !empty( $body['remove_unavailability'] ) ) {
            return self::remove_date_availability( $body['remove_unavailability'], $user->ID );
        }
        if ( isset( $body['save_roles'] ) ){
            return self::save_user_roles( $user_id, $body['save_roles'] );
        }
        if ( isset( $body['allowed_sources'] ) ){
            // If the current user can't promote users or edit this particular user, bail.
            if ( !current_user_can( 'promote_users' ) ) {
                return false;
            }
            $allowed_sources = [];
            foreach ( $body['allowed_sources'] as $s ){
                $allowed_sources[] = sanitize_key( wp_unslash( $s ) );
            }
            if ( in_array( 'restrict_all_sources', $allowed_sources ) ){
                $allowed_sources = [ 'restrict_all_sources' ];
            }
            update_user_option( $user->ID, 'allowed_sources', $allowed_sources );
            return $allowed_sources;
        }
        if ( isset( $body['update_display_name'] ) ) {
            $display_name = sanitize_text_field( wp_unslash( $body['update_display_name'] ) );
            $result = wp_update_user( array(
                'ID' => $user->ID,
                'display_name' => $display_name
            ) );
            if ( is_wp_error( $result ) ) {
                return false;
            } else {
                return $result;
            }
        }
        if ( !empty( $body['locale'] ) ){
            if ( empty( get_user_meta( $user->ID, 'dt_user_initial_setup_default_language', true ) ) ){
                update_user_meta( $user->ID, 'dt_user_initial_setup_default_language', $body['locale'] );
            }

            return self::update_user_locale( $user->ID, $body['locale'] );
        }
        if ( !empty( $body['add_languages'] ) ){
            $languages = get_user_option( 'user_languages', $user->ID ) ?: [];
            if ( !in_array( $body['add_languages'], $languages ) ){
                $languages[] = $body['add_languages'];
            }
            update_user_option( $user->ID, 'user_languages', $languages );
            return $languages;
        }
        if ( !empty( $body['remove_languages'] ) ){
            $languages = get_user_option( 'user_languages', $user->ID );
            if ( in_array( $body['remove_languages'], $languages ) ){
                unset( $languages[array_search( $body['remove_languages'], $languages )] );
            }
            update_user_option( $user->ID, 'user_languages', $languages );
            return $languages;
        }
        if ( !empty( $body['add_people_groups'] ) ){
            $people_groups = get_user_option( 'user_people_groups', $user->ID ) ?: [];
            if ( !in_array( $body['add_people_groups'], $people_groups ) ){
                $people_groups[] = $body['add_people_groups'];
            }
            update_user_option( $user->ID, 'user_people_groups', $people_groups );
            return $people_groups;
        }
        if ( !empty( $body['remove_people_groups'] ) ){
            $people_groups = get_user_option( 'user_people_groups', $user->ID );
            if ( in_array( $body['remove_people_groups'], $people_groups ) ){
                unset( $people_groups[array_search( $body['remove_people_groups'], $people_groups )] );
            }
            update_user_option( $user->ID, 'user_people_groups', $people_groups );
            return $people_groups;
        }
        if ( isset( $body['gender'] ) ) {
            update_user_option( $user->ID, 'user_gender', $body['gender'] );
        }
        if ( !empty( $body['email-preference'] ) ) {
            update_user_meta( $user->ID, 'email_preference', $body['email-preference'] );
        }
        $dt_user_fields = dt_get_site_custom_lists( 'user_fields' );
        foreach ( $dt_user_fields as $field ){
            if ( isset( $body[$field['key']] ) ){
                update_user_meta( $user->ID, $field['key'], $body[$field['key']] );
            }
        }
        if ( isset( $body['description'] ) ) {
            update_user_meta( $user->ID, 'description', $body['description'] );
        }

        try {
            do_action( 'dt_update_user', $user, $body );
        } catch ( Exception $e ) {
            return new WP_Error( __FUNCTION__, $e->getMessage(), [ 'status' => $e->getCode() ] );
        }
        return true;
    }

    /**
     * Switch user preference for notifications and availability meta fields.
     *
     * @param int $user_id
     * @param string $preference_key
     *
     * @param string|null $type
     *
     * @return array
     */
    public static function switch_preference( int $user_id, string $preference_key, string $type = null ) {

        $value = get_user_meta( $user_id, $preference_key, true );

        $default = false;
        if ( $type === 'notifications' ){
            $default = true;
        }

        if ( $value === '' ){
            $status = update_metadata( 'user', $user_id, $preference_key, $default ? '0' : '1' );
            $label = $default ? 'false' : 'true';
        } elseif ( $value === '0' ){
            $status = update_metadata( 'user', $user_id, $preference_key, '1' );
            $label = 'true';
        } else {
            $status = update_metadata( 'user', $user_id, $preference_key, '0' );
            $label = 'false';
        }

        if ( $status ) {
            return [
                'status'   => true,
                'response' => $status,
            ];
        } else {
            return [
                'status'  => false,
                'message' => 'Unable to update_user_option ' . $preference_key . ' to ' . $label
            ];
        }
    }

    /**
     * Magic link user app switch
     *
     * @param int $user_id
     * @param string $preference_key
     * @return array
     */
    public static function app_switch( int $user_id, string $preference_key ) {

        $value = get_user_option( $preference_key, $user_id );
        $hash = dt_create_unique_key();

        $contact = self::get_contact_for_user( $user_id );

        if ( $value === '' || $value === false || $value === '0' ){
            if ( isset( $contact ) ) {
                update_post_meta( $contact, $preference_key, $hash );
            }
            $status = update_user_option( $user_id, $preference_key, $hash );
            $action = $hash;
        } else {
            if ( isset( $contact ) ) {
                delete_post_meta( $contact, $preference_key );
            }
            $status = delete_user_option( $user_id, $preference_key );
            $action = 'removed';
        }

        if ( $status ) {
            return [
                'status'   => true,
                'response' => $action,
            ];
        } else {
            return [
                'status'  => false,
                'message' => 'Unable to update_user_option.'
            ];
        }
    }

    /**
     * Processes updates posted for current user details.
     */
    public static function update_user_contact_info() {
        $current_user = wp_get_current_user();

        // validate nonce
        if ( isset( $_POST['user_update_nonce'] ) && !wp_verify_nonce( sanitize_key( $_POST['user_update_nonce'] ), 'user_' . $current_user->ID . '_update' ) ) {
            return new WP_Error( 'fail_nonce_verification', 'The form requires a valid nonce, in order to process.' );
        }

        $args = [];
        $args['ID'] = $current_user->ID;

        // If specified, directly upload profile pic to registered 3rd-party media object store.
        if ( isset( $_FILES['user_profile_pic'] ) && !empty( $_FILES['user_profile_pic']['tmp_name'] ) ) {

            // To avoid a build up of stale object keys, relating to the same user, reuse existing keys.
            $profile_pic_key = get_user_meta( $current_user->ID, 'dt_user_profile_picture', true );
            $uploaded = DT_Storage::upload_file( 'users', dt_recursive_sanitize_array( $_FILES['user_profile_pic'] ), $profile_pic_key );

            // If successful, persist uploaded object file key.
            if ( !empty( $uploaded ) ) {
                if ( !empty( $uploaded['uploaded_key'] ) ) {
                    update_user_meta( $current_user->ID, 'dt_user_profile_picture', $uploaded['uploaded_key'] );
                }
            }
        }

        // build user name variables
        if ( isset( $_POST['first_name'] ) ) {
            $args['first_name'] = sanitize_text_field( wp_unslash( $_POST['first_name'] ) );
        }
        if ( isset( $_POST['last_name'] ) ) {
            $args['last_name'] = sanitize_text_field( wp_unslash( $_POST['last_name'] ) );
        }
        if ( isset( $_POST['user_email'] ) && !empty( $_POST['user_email'] ) ) {
            $args['user_email'] = sanitize_email( wp_unslash( $_POST['user_email'] ) );
        }
        if ( isset( $_POST['description'] ) ) {
            $args['description'] = sanitize_text_field( wp_unslash( $_POST['description'] ) );
        }
        if ( isset( $_POST['nickname'] ) ) {
            $args['nickname'] = sanitize_text_field( wp_unslash( $_POST['nickname'] ) );
        }
        if ( isset( $_POST['display_name'] ) && !empty( $_POST['display_name'] ) ) {
            $args['display_name'] = $args['nickname'];
        }
        //locale
        if ( isset( $_POST['locale'] ) && !empty( $_POST['locale'] ) ) {
            $args['locale'] = sanitize_text_field( wp_unslash( $_POST['locale'] ) );
        } else {
            $args['locale'] = 'en_US';
        }
        if ( !empty( $args['locale'] ) && empty( get_user_meta( $current_user->ID, 'dt_user_initial_setup_default_language', true ) ) ){
            update_user_meta( $current_user->ID, 'dt_user_initial_setup_default_language', $args['locale'] );
        }
        // _user table defaults
        $result = wp_update_user( $args );

        if ( is_wp_error( $result ) ) {
            return new WP_Error( 'fail_update_user_data', 'Error while updating user data in user table.' );
        }

        // Update custom site fields
        $fields = array_keys( $site_custom_lists = dt_get_option( 'dt_site_custom_lists' )['user_fields'] ?? [] );

        foreach ( $fields as $f ) {

            if ( isset( $_POST[ $f ] ) ) {
                ${$f} = trim( sanitize_text_field( wp_unslash( $_POST[ $f ] ) ) );

                if ( get_user_meta( $current_user->ID, $f, true ) == '' ) {
                    update_user_meta( $current_user->ID, $f, ${$f} );
                } elseif ( ${$f} == '' ) {
                    delete_user_meta( $current_user->ID, $f, get_user_meta( $current_user->ID, $f, true ) );
                } elseif ( ${$f} != get_user_meta( $current_user->ID, $f, true ) ) {
                    update_user_meta( $current_user->ID, $f, ${$f} );
                }
            }
        }

        //check that display name is not null and is a new name
        if ( !empty( $args['nickname'] ) && $current_user->display_name != $args['nickname'] ) {
            //set display name to nickname
            wp_update_user( array(
                    'ID' => (int) $args['ID'],
                    'display_name' => $args['nickname']
                )
            );

        }

        return wp_redirect( get_site_url() .'/settings' );
    }

    public function get_date_availability_hook( $settings ){
        $dates_unavailable = get_user_option( 'user_dates_unavailable', get_current_user_id() );
        if ( !$dates_unavailable ) {
            $dates_unavailable = [];
        }
        foreach ( $dates_unavailable ?? [] as &$range ) {
            $range['start_date'] = dt_format_date( $range['start_date'] );
            $range['end_date'] = dt_format_date( $range['end_date'] );
        }
        $settings['availability'] = $dates_unavailable;
        return $settings;
    }

    public static function add_date_availability( array $data, int $user_id ) {
        if ( !self::can_update( $user_id ) ){
            return new WP_Error( 'add_date_availability', 'permission denied', [ 'status' => 404 ] );
        }
        if ( empty( $data['start_date'] ) || empty( $data['end_date'] ) ) {
            return new WP_Error( __FUNCTION__, 'missing parameters' );
        }

        $dates_unavailable = get_user_option( 'user_dates_unavailable', $user_id );
        if ( !$dates_unavailable ){
            $dates_unavailable = [];
        }
        $max_id = 0;
        foreach ( $dates_unavailable as $range ){
            $max_id = max( $max_id, $range['id'] ?? 0 );
        }

        $dates_unavailable[] = [
            'id' => $max_id + 1,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
        ];
        update_user_option( $user_id, 'user_dates_unavailable', $dates_unavailable );
        return $dates_unavailable;
    }

    public static function remove_date_availability( int $entry_id, int $user_id ) {
        if ( !self::can_update( $user_id ) ){
            return new WP_Error( 'add_date_availability', 'permission denied', [ 'status' => 404 ] );
        }
        $dates_unavailable = get_user_option( 'user_dates_unavailable', $user_id );
        foreach ( $dates_unavailable as $index => $range ) {
            if ( $entry_id === $range['id'] ){
                unset( $dates_unavailable[$index] );
            }
        }
        $dates_unavailable = array_values( $dates_unavailable );
        update_user_option( $user_id, 'user_dates_unavailable', $dates_unavailable );
        return $dates_unavailable;
    }

    public static function update_user_locale( int $user_id, $locale ){
        if ( !self::can_update( $user_id ) ){
            return new WP_Error( __FUNCTION__, 'permission denied', [ 'status' => 404 ] );
        }
        return wp_update_user( [
            'ID' => $user_id,
            'locale' => $locale
        ] );
    }


    //======================================================================
    // USER LOCATIONS
    //======================================================================

    public static function get_user_location( $user_id = null ) {
        if ( empty( $user_id ) ) {
            $user_id = get_current_user_id();
        }
        $grid = [];

        global $wpdb;
        if ( DT_Mapbox_API::get_key() ) {
            $location_grid = get_user_meta( $user_id, $wpdb->prefix . 'location_grid_meta' );
            if ( ! empty( $location_grid ) ) {
                $grid['location_grid_meta'] = [];
                foreach ( $location_grid as $meta ) {
                    $location_grid_meta = Location_Grid_Meta::get_location_grid_meta_by_id( $meta );
                    if ( $location_grid_meta ) {
                        $grid['location_grid_meta'][] = $location_grid_meta;
                    }
                }
                $grid['location_grid'] = [];
                foreach ( $grid['location_grid_meta'] as $meta ) {
                    $grid['location_grid'][] = [
                        'id' => (int) $meta['grid_id'],
                        'label' => $meta['label']
                    ];
                }
            }
        } else {
            $location_grid = get_user_meta( $user_id, $wpdb->prefix . 'location_grid' );
            if ( ! empty( $location_grid ) ) {
                $names = Disciple_Tools_Mapping_Queries::get_names_from_ids( $location_grid );
                $grid['location_grid'] = [];
                foreach ( $names as $id => $name ) {
                    $grid['location_grid'][] = [
                        'id' => $id,
                        'label' => $name
                    ];
                }
            }
        }

        return $grid;
    }

    public function add_current_locations_list( $custom_data ) {
        $custom_data['current_locations'] = DT_Mapping_Module::instance()->get_post_locations( self::get_contact_for_user( get_current_user_id() ) );
        return $custom_data;
    }

    public static function add_user_location( $grid_id, $user_id = null ) {
        if ( empty( $user_id ) ) {
            $user_id = get_current_user_id();
        }

        global $wpdb;
        $umeta_id = add_user_meta( $user_id, $wpdb->prefix . 'location_grid', $grid_id );

        if ( $umeta_id ) {
            return true;
        }
        return false;
    }

    public static function delete_user_location( $grid_id, $user_id = null ) {
        if ( empty( $user_id ) ) {
            $user_id = get_current_user_id();
        }

        global $wpdb;
        $umeta_id = delete_user_meta( $user_id, $wpdb->prefix . 'location_grid', $grid_id );
        if ( $umeta_id ) {
            return true;
        }
        return false;
    }

    /**
     *
     * @param $location_grid_meta
     * @param null $user_id
     * @return array|bool|int|WP_Error
     */
    public static function add_user_location_meta( $location_grid_meta, $user_id = null ) {
        if ( empty( $user_id ) ) {
            $user_id = get_current_user_id();
        }
        $umeta_id = null;
        // use grid_id as primary value
        if ( isset( $location_grid_meta['grid_id'] ) && ! empty( $location_grid_meta['grid_id'] ) ) {
            $geocoder = new Location_Grid_Geocoder();

            $grid = $geocoder->query_by_grid_id( $location_grid_meta['grid_id'] );
            if ( $grid ) {
                $lgm = [];

                Location_Grid_Meta::validate_location_grid_meta( $lgm );
                $lgm['post_id'] = $user_id;
                $lgm['post_type'] = 'users';
                $lgm['grid_id'] = $grid['grid_id'];
                $lgm['lng'] = $grid['longitude'];
                $lgm['lat'] = $grid['latitude'];
                $lgm['level'] = $grid['level_name'];
                $lgm['label'] = $geocoder->_format_full_name( $grid );

                $umeta_id = Location_Grid_Meta::add_user_location_grid_meta( $user_id, $lgm );
                if ( is_wp_error( $umeta_id ) ) {
                    return $umeta_id;
                }
            }
        // use lng lat as base value
        } else {

            if ( empty( $location_grid_meta['lng'] ) || empty( $location_grid_meta['lat'] ) ) {
                return new WP_Error( __METHOD__, 'Missing required lng or lat' );
            }

            $umeta_id = Location_Grid_Meta::add_user_location_grid_meta( $user_id, $location_grid_meta );
        }

        if ( $umeta_id ) {
            return self::get_user_location( $user_id );
        }
        return false;
    }

    public static function delete_user_location_meta( $grid_meta_id, $user_id = null ) {
        if ( empty( $user_id ) ) {
            $user_id = get_current_user_id();
        }

        return Location_Grid_Meta::delete_user_location_grid_meta( $user_id, 'grid_meta_id', $grid_meta_id );
    }

    public static function get_user_settings( $user ): array {
        return [
            'profile'        => self::get_user_settings_profile( $user ),
            'apps'           => self::get_user_settings_apps(),
            'preferences'    => self::get_user_settings_preferences( $user ),
            'unavailability' => self::get_user_settings_unavailability( $user ),
            'notifications'  => self::get_user_settings_notifications( $user )
        ];
    }

    private static function get_user_settings_profile( $user ): array {
        return [
            'ID'           => $user->ID,
            'name'         => trim( $user->first_name . ' ' . $user->last_name ),
            'username'     => $user->user_login,
            'user_email'   => $user->user_email,
            'nickname'     => $user->nickname,
            'display_name' => $user->display_name,
            'roles'        => dt_get_user_role_names( $user->ID ),
            'email'        => self::get_user_settings_profile_field( $user, 'email', [
                [
                    'value' => $user->user_email,
                    'label' => __( 'System Email', 'disciple_tools' )
                ]
            ] ),
            'phone'        => self::get_user_settings_profile_field( $user, 'phone' ),
            'address'      => self::get_user_settings_profile_field( $user, 'address' ),
            'social'       => self::get_user_settings_profile_field( $user, 'social' ),
            'other'        => self::get_user_settings_profile_field( $user, 'other' ),
            'locale'       => get_user_locale( $user->ID ),
            'language'     => self::get_user_settings_profile_language( $user ),
            'bio'          => $user->user_description,
            'gender'       => self::get_user_settings_profile_gender( $user )
        ];
    }

    private static function get_user_settings_profile_field( $user, $field_key, $entries = [] ): array {
        $user_array = get_user_meta( $user->ID );
        if ( is_array( $user_array ) ) {
            foreach ( dt_build_user_fields_display( $user_array ) ?? [] as $field ) {
                if ( $field['type'] == $field_key && ! empty( $field['value'] ) ) {
                    $entries[] = [
                        'value' => $field['value'],
                        'label' => $field['label']
                    ];
                }
            }
        }

        return $entries;
    }

    private static function get_user_settings_profile_language( $user ): string {
        $translations   = dt_get_available_languages( true );
        $dt_user_locale = get_user_locale( $user->ID );

        return isset( $translations[ $dt_user_locale ] ) ? $translations[ $dt_user_locale ]['native_name'] : 'English';
    }

    private static function get_user_settings_profile_gender( $user ): string {
        $contact_fields = DT_Posts::get_post_settings( 'contacts' )['fields'];

        $field_key  = 'gender';
        $user_field = get_user_option( 'user_gender', $user->ID );

        return $contact_fields[ $field_key ]['default'][ $user_field ]['label'] ?? $user_field;
    }

    private static function get_user_settings_apps(): array {
        $apps = [];
        foreach ( apply_filters( 'dt_settings_apps_list', [] ) ?? [] as $app_key => $app_value ) {
            if ( $app_value['settings_display'] ?? true ) {
                $apps[] = [
                    'label'       => $app_value['label'],
                    'description' => $app_value['description'],
                    'link'        => get_user_option( $app_key ) ? trailingslashit( trailingslashit( site_url() ) . $app_value['url_base'] ) . get_user_option( $app_key ) : ''
                ];
            }
        }

        return $apps;
    }

    private static function get_user_settings_preferences( $user ): array {
        return [
            'languages'     => self::get_user_settings_preferences_languages( $user ),
            'locations'     => self::get_user_location( $user->ID ),
            'people_groups' => self::get_user_settings_preferences_people_groups( $user ),
            'workload'      => self::get_user_settings_preferences_workload( $user )
        ];
    }

    private static function get_user_settings_preferences_languages( $user ): array {
        $contact_fields = DT_Posts::get_post_settings( 'contacts', false )['fields'];

        $languages      = [];
        $user_languages = get_user_option( 'user_languages', $user->ID );
        foreach ( $contact_fields['languages']['default'] as $option_key => $option_value ) {
            if ( in_array( $option_key, $user_languages ?: [] ) ) {
                $languages[] = [
                    'key'   => $option_key,
                    'label' => $option_value['label']
                ];
            }
        }

        return $languages;
    }

    private static function get_user_settings_preferences_people_groups( $user ): array {
        $user_people_groups = get_user_option( 'user_people_groups', $user->ID );
        return DT_Posts::get_post_names_from_ids( $user_people_groups ? $user_people_groups : [] );
    }

    private static function get_user_settings_preferences_workload( $user ): array {
        $workload = get_user_option( 'workload_status', $user->ID );
        $options  = dt_get_site_custom_lists()['user_workload_status'] ?? [];

        $workload_response = [];
        if ( isset( $options[ $workload ] ) ) {
            $workload_response       = $options[ $workload ];
            $workload_response['id'] = $workload;
        }

        return $workload_response;
    }

    private static function get_user_settings_unavailability( $user ): array {
        $dates_unavailable = get_user_option( 'user_dates_unavailable', $user->ID );
        if ( ! $dates_unavailable ) {
            $dates_unavailable = [];
        }
        foreach ( $dates_unavailable ?? [] as &$range ) {
            $range['start_date'] = dt_format_date( $range['start_date'] );
            $range['end_date']   = dt_format_date( $range['end_date'] );
        }

        return $dates_unavailable;
    }

    private static function get_user_settings_notifications( $user ): array {
        return [
            'email_preference' => self::get_user_settings_notifications_email_preference( $user ),
            'notify_types'     => self::get_user_settings_notifications_type( $user ),
            'follow_all'       => self::get_user_settings_notifications_follow_all( $user )
        ];
    }

    private static function get_user_settings_notifications_email_preference( $user ): array {
        $dt_user_meta = get_user_meta( $user->ID );

        $email_preference = isset( $dt_user_meta['email_preference'] ) ? $dt_user_meta['email_preference'][0] : null;

        return [
            'realtime' => ( ! $email_preference || $email_preference === 'real-time' ),
            'hourly'   => ( $email_preference === 'hourly' ),
            'daily'    => ( $email_preference === 'daily' )
        ];
    }

    private static function get_user_settings_notifications_type( $user ): array {
        $dt_user_meta                  = get_user_meta( $user->ID );
        $dt_site_notification_defaults = dt_get_site_notification_defaults();

        $types = [];
        foreach ( $dt_site_notification_defaults['types'] as $dt_notification_key => $dt_notification_default ) {
            $type             = [];
            $type['key']      = $dt_notification_key;
            $type['label']    = $dt_notification_default['label'];
            $type['channels'] = [];

            foreach ( $dt_site_notification_defaults['channels'] as $channel_key => $channel_value ) {
                $channel_notification_key = $dt_notification_key . '_' . $channel_key;

                $type['channels'][] = [
                    'key'     => $channel_key,
                    'label'   => $channel_value['label'],
                    'enabled' => ( $dt_notification_default[ $channel_key ] ) || ( isset( $dt_user_meta[ $channel_notification_key ] ) && $dt_user_meta[ $channel_notification_key ][0] == true )
                ];
            }

            $types[] = $type;
        }

        return $types;
    }

    private static function get_user_settings_notifications_follow_all( $user ): bool {
        $dt_user_meta = get_user_meta( $user->ID );

        return user_can( $user, 'dt_all_access_contacts' ) && ( isset( $dt_user_meta['dt_follow_all'] ) && $dt_user_meta['dt_follow_all'][0] == true );
    }
}
