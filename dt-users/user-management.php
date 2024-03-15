<?php

class DT_User_Management
{
    public static $permissions = [ 'list_users', 'manage_dt' ];

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        $url_path = dt_get_url_path();
        if ( self::has_permission() || self::non_admins_can_make_users() ) {
            if ( ( strpos( $url_path, 'user-management/user' ) !== false || strpos( $url_path, 'user-management/add-user' ) !== false ) ) {
                add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
                add_filter( 'dt_templates_for_urls', [ $this, 'dt_templates_for_urls' ] );
            }
        }
        if ( self::has_permission() ){
            if ( strpos( $url_path, 'user-management' ) !== false ) {
                add_filter( 'dt_metrics_menu', [ $this, 'add_menu' ], 20 );
            }
            if ( strpos( $url_path, 'user-management/user' ) !== false || ( strpos( $url_path, 'user-management/add-user' ) !== false && ( current_user_can( 'create_users' ) ) ) ){


                add_action( 'init', function() {
                    add_rewrite_rule( 'user-management/user/([a-z0-9-]+)[/]?$', 'index.php?dt_user_id=$matches[1]', 'top' );
                } );
                add_filter( 'query_vars', function( $query_vars ) {
                    $query_vars[] = 'dt_user_id';
                    return $query_vars;
                } );
                add_action( 'template_include', function( $template ) {
                    if ( get_query_var( 'dt_user_id' ) === false || get_query_var( 'dt_user_id' ) === '' ) {
                        return $template;
                    }
                    return get_template_directory() . '/dt-users/template-user-management.php';
                } );
            }
        }
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );

        add_filter( 'script_loader_tag', [ $this, 'script_loader_tag' ], 10, 3 );

    }

    public static function has_permission(){
        $pass = false;
        foreach ( self::$permissions as $permission ){
            if ( current_user_can( $permission ) ){
                $pass = true;
            }
        }
        return $pass;
    }

    public static function non_admins_can_make_users() {
        $user_invite_setting = get_option( 'dt_user_invite_setting', false );

        if ( $user_invite_setting && current_user_can( 'access_contacts' ) ) {
            return true;
        }

        return false;
    }

    public function add_api_routes() {
        $namespace = 'user-management/v1';

        register_rest_route(
            $namespace, '/user', [
                [
                    'methods'  => 'GET',
                    'callback' => [ $this, 'get_user_endpoint' ],
                    'permission_callback' => '__return_true',
                ],
            ]
        );

        register_rest_route(
            $namespace, '/user', [
                [
                    'methods'  => 'POST',
                    'callback' => [ $this, 'update_settings_on_user' ],
                    'permission_callback' => '__return_true',
                ],
            ]
        );
        register_rest_route(
            $namespace, '/get_users', [
                [
                    'methods'  => 'GET',
                    'callback' => [ $this, 'get_users_endpoints' ],
                    'permission_callback' => function(){
                        return $this->has_permission();
                    },
                ],
            ]
        );
        register_rest_route(
            $namespace, '/get-users', [
                [
                    'methods'  => 'POST',
                    'callback' => [ $this, 'get_users_paged_endpoint' ],
                    'permission_callback' => function(){
                        return $this->has_permission();
                    },
                ],
            ]
        );
        register_rest_route(
            $namespace, '/send_pwd_reset_email', [
                [
                    'methods' => 'POST',
                    'callback' => [ $this, 'send_pwd_reset_email' ],
                    'permission_callback' => function(){
                        return $this->has_permission();
                    },
                ],
            ]
        );
    }

    public function dt_templates_for_urls( $template_for_url ) {
        $template_for_url['user-management/users'] = './dt-users/template-user-management.php';
        $template_for_url['user-management/add-user'] = './dt-users/template-new-user.php';
        return $template_for_url;
    }

    public function add_menu( $content ) {
        $content .= '<li><a href="'. site_url( '/user-management/users/' ) .'" >' .  esc_html__( 'Users', 'disciple_tools' ) . '</a></li>';
        if ( current_user_can( 'manage_dt' ) ){
            $content .= '<li><a href="'. esc_url( site_url( '/user-management/add-user/' ) ) .'" >' .  esc_html__( 'Add User', 'disciple_tools' ) . '</a></li>';
        }
        return $content;
    }

    public function scripts() {
        $url_path = dt_get_url_path();
        $dt_user_fields = Disciple_Tools_Users::get_users_fields();
        if ( strpos( $url_path, 'user-management/user' ) !== false || strpos( $url_path, 'user-management/add-user' ) !== false ){


            $dependencies = [
                'jquery',
                'moment',
                'lodash'
            ];

            array_push( $dependencies,
                'amcharts-core',
                'amcharts-charts',
                'amcharts-animated'
            );

            wp_register_script( 'amcharts-core', 'https://www.amcharts.com/lib/4/core.js', false, '4' );
            wp_register_script( 'amcharts-charts', 'https://www.amcharts.com/lib/4/charts.js', false, '4' );
            wp_register_script( 'amcharts-animated', 'https://www.amcharts.com/lib/4/themes/animated.js', [ 'amcharts-core' ], '4' );

            wp_enqueue_script( 'dtActivityLogs', get_template_directory_uri() . '/dt-assets/js/activity-log.js', [
                'jquery',
                'lodash'
            ], filemtime( get_theme_file_path() . '/dt-assets/js/activity-log.js' ), true );

            wp_enqueue_script( 'dt_dispatcher_tools', get_template_directory_uri() . '/dt-users/user-management.js', $dependencies, filemtime( plugin_dir_path( __FILE__ ) . '/user-management.js' ), true );

            wp_localize_script(
                'dt_dispatcher_tools', 'dt_user_management_localized', [
                    'root'               => esc_url_raw( rest_url() ),
                    'theme_uri'          => trailingslashit( get_stylesheet_directory_uri() ),
                    'nonce'              => wp_create_nonce( 'wp_rest' ),
                    'current_user_login' => wp_get_current_user()->user_login,
                    'current_user_id'    => get_current_user_id(),
                    'map_key'            => DT_Mapbox_API::get_key(),
                    'url_path'           => dt_get_url_path(),
                    'translations'       => [
                        'accept_time' => _x( '%1$s was accepted on %2$s after %3$s days', 'Bob was accepted on Jul 8 after 10 days', 'disciple_tools' ),
                        'no_contact_attempt_time' => _x( '%1$s waiting for Contact Attempt for %2$s days', 'Bob waiting for contact for 10 days', 'disciple_tools' ),
                        'contact_attempt_time' => _x( 'Contact with %1$s was attempted on %2$s after %3$s days', 'Contact with Bob was attempted on Jul 8 after 10 days', 'disciple_tools' ),
                        'unable_to_update' => __( 'Unable to update', 'disciple_tools' ),
                        'view_new_user' => __( 'View New User', 'disciple_tools' ),
                        'view_new_contact' => __( 'View New Contact', 'disciple_tools' ),
                        'email_already_in_system' => __( 'Email address is already in the system as a user!', 'disciple_tools' ),
                        'username_in_system' => __( 'Username is already in the system as a user!', 'disciple_tools' ),
                        'remove' => __( 'Remove', 'disciple_tools' ),
                        'already_user' => __( 'This contact is already a user.', 'disciple_tools' ),
                        'view_user' => __( 'View User', 'disciple_tools' ),
                        'view_contact' => __( 'View Contact', 'disciple_tools' ),
                        'more' => __( 'More', 'disciple_tools' ),
                        'less' => __( 'Less', 'disciple_tools' ),
                        'app_state_enable' => __( 'Enable', 'disciple_tools' ),
                        'app_state_active' => __( 'Yes', 'disciple_tools' ),
                        'app_state_inactive' => __( 'No', 'disciple_tools' )
                    ],
                    'language_dropdown' => dt_get_available_languages(),
                    'default_language' => get_option( 'dt_user_default_language', 'en_US' ),
                    'has_permission' => self::has_permission(),
                    'magic_link_apps' => dt_get_registered_types()
                ]
            );

            if ( DT_Mapbox_API::get_key() ){
                DT_Mapbox_API::load_mapbox_header_scripts();
                DT_Mapbox_API::load_mapbox_search_widget_users();
            }
        }

        if ( strpos( $url_path, 'user-management/users' ) !== false ) {
            wp_enqueue_script( 'dt_users_table',
                get_template_directory_uri() . '/dt-users/table/users-table.js',
                [
                    'jquery'
                ],
                filemtime( get_theme_file_path() . '/dt-users/table/users-table.js' ),
            );


            if ( isset( $dt_user_fields['location_grid'] ) ){
                //used locations
                $locations = self::get_used_user_locations();
                $dt_user_fields['location_grid']['options'] = $locations;
            }

            wp_localize_script( 'dt_users_table', 'dt_users_table', [
                'translations' => [
                    'go' => __( 'Go', 'disciple_tools' ),
                    'search' => __( 'Search', 'disciple_tools' ),
                    'users' => __( 'Users', 'disciple_tools' ),
                    'showing_x_of_y' => __( 'Showing %1$s of %2$s', 'disciple_tools' ),
                ],
                'fields' => $dt_user_fields,
                'rest_endpoint' => trailingslashit( rest_url( 'user-management/v1/' ) ),
            ] );
        }
    }

    public function script_loader_tag( $tag, $handle, $src ) {
        if ( $handle === 'dt_users_table' ) {
            $tag = '<script type="module" src="' . esc_url( $src ) . '"></script>'; //phpcs:ignore
        }
        return $tag;
    }


    public function get_dt_user( $user_id, $section = null ) {
        if ( ! self::has_permission() ) {
            return new WP_Error( __METHOD__, 'Permission error', [ 'status' => 403 ] );
        }

        global $wpdb;
        $user = get_user_by( 'ID', $user_id );
        if ( ! $user ) {
            return new WP_Error( __METHOD__, 'No User', [ 'status' => 400 ] );
        }

        $user_response = [
            'display_name' => wp_specialchars_decode( $user->display_name ),
            'user_email' => $user->user_email,
            'user_id' => $user->ID,
            'corresponds_to_contact' => 0,
            'contact' => [],
            'user_status' => '',
            'workload_status' => '',
            'dates_unavailable' => false,
            'location_grid' => [],
            'user_activity' => [],
            'active_contacts' => 0,
            'update_needed' => [],
            'unread_notifications' => 0,
            'needs_accepted' => 0,
            'days_active' => [],
            'times' => [],
            'assigned_counts' => [],
            'contact_statuses' => [],
            'contact_attempts' => [],
            'contact_accepts' => [],
            'unaccepted_contacts' => [],
            'unattempted_contacts' => [],
            'allowed_sources' => [],
            'magic_links' => []
        ];

        /* details section */
        if ( $section === 'details' || $section === null ) {
            /* user status */
            $user_status = get_user_option( 'user_status', $user->ID );
            $user_response['user_status'] = $user_status;

            /* workload status */
            $workload_status = get_user_option( 'workload_status', $user->ID );
            $user_response['workload_status'] = $workload_status;

            /* dates unavailable */
            $dates_unavailable = get_user_option( 'user_dates_unavailable', $user->ID );
            if ( ! empty( $dates_unavailable ) ) {
                foreach ( $dates_unavailable as &$range ) {
                    $range['start_date'] = dt_format_date( $range['start_date'] );
                    $range['end_date'] = dt_format_date( $range['end_date'] );
                }
            }
            $user_response['dates_unavailable'] = $dates_unavailable;

            $user_response['user_location'] = Disciple_Tools_Users::get_user_location( $user->ID );
            $user_response['gender'] = get_user_option( 'user_gender', $user_id );
            $user_response['languages'] = get_user_option( 'user_languages', $user_id );
            $user_response['description'] = get_user_meta( $user_id, 'description', true );
            $contact_id = Disciple_Tools_Users::get_contact_for_user( $user_id );
            $user_response['corresponds_to_contact'] = $contact_id;
            $dt_user_meta = get_user_meta( $user_id ); // Full array of user meta data
            $user_response['user_fields'] = dt_build_user_fields_display( $dt_user_meta );

            // Capture any associated magic links.
            if ( !empty( $contact_id ) ) {
                $record = DT_Posts::get_post( 'contacts', $contact_id, false, false );
                if ( !empty( $record ) && !is_wp_error( $record ) ) {
                    $magic_link_apps = dt_get_registered_types();
                    foreach ( $magic_link_apps ?? [] as $app_root => $app_types ){
                        foreach ( $app_types as $app_type => $app_value ){
                            if ( isset( $app_value['label'], $app_value['meta_key'], $app_value['post_type'] ) ){
                                $user_response['magic_links'][$app_value['meta_key']] = [
                                    'type' => $app_type,
                                    'label' => $app_value['label'],
                                    'post_type' => $app_value['post_type'],
                                    'meta_key' => $app_value['meta_key'],
                                    'meta_key_value' => $record[$app_value['meta_key']] ?? ''
                                ];
                            }
                        }
                    }
                }
            }
        }

        $modules = dt_get_option( 'dt_post_type_modules' );
        if ( ( $section === 'stats' || $section === 'pace' || $section === null ) && isset( $modules['access_module']['enabled'] ) && $modules['access_module']['enabled'] ) {
            $to_accept = DT_Posts::search_viewable_post( 'contacts', [
                'overall_status' => [ 'assigned' ],
                'assigned_to' => [ $user->ID ]
            ], false );
            $update_needed = DT_Posts::search_viewable_post( 'contacts', [
                'requires_update' => [ 'true' ],
                'assigned_to' => [ $user->ID ],
                'overall_status' => [ '-closed', '-paused' ],
                'sort' => 'last_modified'
            ], false );
            if ( sizeof( $update_needed['posts'] ) > 5 ) {
                $update_needed['posts'] = array_slice( $update_needed['posts'], 0, 5 );
            }
            if ( sizeof( $to_accept['posts'] ) > 10 ) {
                $to_accept['posts'] = array_slice( $to_accept['posts'], 0, 10 );
            }
            foreach ( $update_needed['posts'] as &$contact ) {
                $now = time();
                $last_modified = get_post_meta( $contact->ID, 'last_modified', true );
                $days_different = (int) round( ( $now - (int) $last_modified ) / ( 60 * 60 * 24 ) );
                $contact->last_modified_msg = esc_attr( sprintf( __( '%s days since last update', 'disciple_tools' ), $days_different ), 'disciple_tools' );
            }

            $user_response['update_needed'] = $update_needed;
            $user_response['needs_accepted'] = $to_accept;
        }

        /* Locations section */
        if ( $section === 'stats' || $section === null ) {
            /* counts section */
            $assigned_counts = DT_User_Metrics::get_user_assigned_contacts_summary( $user_id );

            $user_response['contact_statuses'] = Disciple_Tools_Counter_Contacts::get_contact_statuses( $user->ID );
            $user_response['active_contacts'] = DT_User_Metrics::get_user_active_contacts_count( $user_id );
            $user_response['assigned_counts'] = isset( $assigned_counts[0] ) ? $assigned_counts[0] : [];
            $user_response['unread_notifications'] = DT_User_Metrics::get_user_unread_notifications_count( $user_id );
        }

        if ( $section === 'activity' || $section === null ) {
            $user_activity = DT_User_Metrics::get_user_activity( $user->ID );
            $user_response['user_activity'] = $user_activity;
        }

        if ( $section === 'contact_attempts' || $section === null ) {
            $user_response['contact_attempts'] = DT_User_Metrics::get_user_time_to_contact_attempt( $user->ID );
            $user_response['contact_attempts'] = [];
        }

        if ( $section === 'contact_accepts' || $section === null ) {
            $user_response['contact_accepts'] = DT_User_Metrics::get_user_time_to_contact_accept( $user->ID );
        }

        if ( $section === 'unaccepted_contacts' || $section === null ) {
            $user_response['unaccepted_contacts'] = DT_User_Metrics::get_user_oldest_unaccepted_contacts( $user->ID );
        }

        if ( $section === 'unattempted_contacts' || $section === null ) {
            $user_response['unattempted_contacts'] = DT_User_Metrics::get_user_oldest_active_contacts_with_no_seeker_path( $user->ID );
        }

        if ( $section === 'days_active' || $section === null ) {
            $user_response['days_active'] = DT_User_Metrics::get_user_days_active_chart_data( $user_id );
        }


        if ( current_user_can( 'promote_users' ) ){
            $user_response['roles'] = $user->roles;
            $user_response['allowed_sources'] = get_user_option( 'allowed_sources', $user->ID ) ?: [];
        }

        return $user_response;

    }

    public function get_user_endpoint( WP_REST_Request $request ) {
        if ( !self::has_permission() ) {
            return new WP_Error( 'get_user', 'Missing Permissions', [ 'status' => 401 ] );
        }

        $params = $request->get_params();
        if ( ! isset( $params['user'] ) ) {
            return new WP_Error( __METHOD__, 'Missing user id', [ 'status' => 400 ] );
        }
        if ( ! isset( $params['section'] ) ) {
            return new WP_Error( __METHOD__, 'Missing collection id', [ 'status' => 400 ] );
        }
        return $this->get_dt_user( $params['user'], $params['section'] );
    }

    public function get_users_endpoints( WP_REST_Request $request ){
        if ( !self::has_permission() ){
            return new WP_Error( 'get_user', 'Missing Permissions', [ 'status' => 401 ] );
        }
        $params = $request->get_params();
        $refresh = isset( $params['refresh'] ) && $params['refresh'] = '1';
        return self::get_users( $refresh );
    }

    public static function get_users( $refresh = false ) {
        $users = [];
        if ( !$refresh && get_transient( 'dispatcher_user_data' ) ) {
            $users = maybe_unserialize( get_transient( 'dispatcher_user_data' ) );
        }
        if ( empty( $users ) ) {
            global $wpdb;
            $users_query = $wpdb->get_results( $wpdb->prepare( "
                SELECT users.ID,
                    users.display_name,
                    um.meta_value as roles
                FROM $wpdb->users as users
                INNER JOIN $wpdb->usermeta as um on ( um.user_id = users.ID AND um.meta_key = %s )
                GROUP by users.ID, um.meta_value
            ", $wpdb->prefix . 'capabilities' ),
            ARRAY_A );

            foreach ( $users_query as $user ){
                $users[ $user['ID'] ] = $user;
                $users[ $user['ID'] ]['location_grid'] = false;
                $users[ $user['ID'] ]['location_grid_meta'] = false;
                $users[ $user['ID'] ]['number_update'] = 0;
                $users[ $user['ID'] ]['number_assigned_to'] = 0;
                $users[ $user['ID'] ]['number_new_assigned'] = 0;
                $users[ $user['ID'] ]['number_active'] = 0;
            }
            $user_data = $wpdb->get_results("
                SELECT
                    assigned_to.meta_value as assigned_to,
                    count( un.meta_value ) as number_update,
                    count(assigned_to.meta_value) as number_assigned_to,
                    count(new_assigned.post_id) as number_new_assigned,
                    count(active.post_id) as number_active
                FROM $wpdb->postmeta as assigned_to
                INNER JOIN $wpdb->posts as p on ( p.ID = assigned_to.post_id and p.post_type = 'contacts' )
                LEFT JOIN $wpdb->postmeta un on ( un.post_id = assigned_to.post_id AND un.meta_key = 'requires_update' AND un.meta_value = '1')
                LEFT JOIN $wpdb->postmeta as active on (active.post_id = p.ID and active.meta_key = 'overall_status' and active.meta_value = 'active' )
                LEFT JOIN $wpdb->postmeta as new_assigned on (new_assigned.post_id = p.ID and new_assigned.meta_key = 'overall_status' and new_assigned.meta_value = 'assigned' )
                WHERE assigned_to.meta_key = 'assigned_to'
                AND assigned_to.post_id NOT IN (
                    SELECT post_id
                    FROM $wpdb->postmeta
                    WHERE meta_key = 'type' AND meta_value = 'user'
                    GROUP BY post_id
                )
                GROUP BY assigned_to.meta_value
            ", ARRAY_A );

            foreach ( $user_data as $user ){
                $user_id = str_replace( 'user-', '', $user['assigned_to'] );
                if ( isset( $users[$user_id] ) ) {
                    $users[$user_id]['number_assigned_to'] = $user['number_assigned_to'];
                    $users[$user_id]['number_active'] = $user['number_active'];
                    $users[$user_id]['number_new_assigned'] = $user['number_new_assigned'];
                    $users[$user_id]['number_update'] = $user['number_update'];
                }
            }

            $user_statuses = $wpdb->get_results( $wpdb->prepare( "
                SELECT * FROM $wpdb->usermeta
                WHERE meta_key = %s
            ", $wpdb->prefix . 'user_status' ), ARRAY_A );
            foreach ( $user_statuses as $meta_row ){
                if ( isset( $users[ $meta_row['user_id'] ] ) ) {
                    $users[$meta_row['user_id']]['user_status'] = $meta_row['meta_value'];
                }
            }
            $user_workloads = $wpdb->get_results( $wpdb->prepare( "
                SELECT * FROM $wpdb->usermeta
                WHERE meta_key = %s
            ", $wpdb->prefix . 'workload_status' ), ARRAY_A );
            foreach ( $user_workloads as $meta_row ){
                if ( isset( $users[ $meta_row['user_id'] ] ) ) {
                    $users[$meta_row['user_id']]['workload_status'] = $meta_row['meta_value'];
                }
            }
            $user_locations_grid_meta = $wpdb->get_results( $wpdb->prepare( "
                SELECT user_id, meta_value as grid_id
                FROM $wpdb->usermeta
                WHERE meta_key = %s
            ", $wpdb->prefix . 'location_grid_meta'), ARRAY_A);
            foreach ( $user_locations_grid_meta as $user_with_location ){
                if ( isset( $users[ $user_with_location['user_id'] ] ) ) {
                    $users[$user_with_location['user_id']]['location_grid_meta'] = true;
                }
            }
            $user_locations_grid = $wpdb->get_results( $wpdb->prepare( "
                SELECT user_id, meta_value as grid_id
                FROM $wpdb->usermeta
                WHERE meta_key = %s
            ", $wpdb->prefix . 'location_grid'), ARRAY_A);
            foreach ( $user_locations_grid as $user_with_location ){
                if ( isset( $users[ $user_with_location['user_id'] ] ) ) {
                    $users[$user_with_location['user_id']]['location_grid'] = true;
                }
            }


            $last_activity = $wpdb->get_results( "
                SELECT user_id,
                log.hist_time as last_activity
                from $wpdb->dt_activity_log as log
                where histid IN (
                    SELECT MAX( histid )
                    FROM $wpdb->dt_activity_log
                    GROUP BY user_id
                )
                GROUP BY user_id,  last_activity
                ORDER by user_id",
            ARRAY_A);
            foreach ( $last_activity as $a ){
                if ( isset( $users[ $a['user_id'] ] ) ) {
                    $users[$a['user_id']]['last_activity'] = $a['last_activity'];
                }
            }

            if ( !empty( $users ) ){
                set_transient( 'dispatcher_user_data', maybe_serialize( $users ), 60 * 60 * 24 );
            }
        }
        if ( current_user_can( 'list_users' ) ) {
            return $users;
        } else {
            $multipliers = [];
            foreach ( $users as $user_id => $user ) {
                $user_roles = maybe_unserialize( $user['roles'] );
                if ( in_array( 'multiplier', $user_roles ) ){
                    unset( $user['roles'] );
                    $multipliers[$user_id] = $user;
                }
            }
            return $multipliers;
        }
    }


    public function get_users_paged_endpoint( WP_REST_Request $request ){
        $params = $request->get_params();
        return self::get_users_paged( $params );
    }

    public static function get_users_paged( $params = [] ){
        global $wpdb;
        $user_fields = Disciple_Tools_Users::get_users_fields();

        $limit = 1000;
        if ( isset( $params['limit'] ) ){
            $limit = $params['limit'];
        }
        $filter = !empty( $params['filter'] ) ? $params['filter'] : [];

        $select = '';
        $joins = '';
        $where = '';

        /**
         * Search users for a string
         */
        $search = esc_sql( !empty( $params['search'] ) ? $params['search'] : '' );
        if ( !empty( $params['search'] ) ){
            $columns = [ 'user_login', 'user_email', 'display_name' ];
            $where .= ' AND ( ';
            foreach ( $columns as $column ){
                $where .= " $column LIKE '%$search%' OR ";
            }
            $where = rtrim( $where, ' OR ' );
            $where .= ' ) ';
        }

        /**
         * Sort by selected fields
         */
        $sort_sql = '';
        $sort = $params['sort'] ?? '';
        $dir = ( !empty( $sort ) && $sort[0] === '-' ) ? 'DESC' : 'ASC';
        $sort_field = esc_sql( str_replace( '-', '', $sort ) );
        if ( !empty( $sort_field ) ){
            $table = $user_fields[$sort_field]['table'] ?? '';
            if ( in_array( $table, [ 'users_table', 'usermeta_table' ] ) ){
                $table = $user_fields[$sort_field]['table'];
                if ( $table === 'users_table' ){
                    $sort_sql = 'ORDER BY users.' . $sort_field . ' ' . $dir;
                } else {
                    $sort_sql = 'ORDER BY um_' . $sort_field . '.meta_value IS NULL, um_' . $sort_field . '.meta_value ' . $dir;
                }
            }
        }


        /**
         * Get a list of the field types
         */
        $fields_by_type = [];
        foreach ( $user_fields as $field_key => $field_value ){
            if ( !isset( $fields_by_type[ $field_value['type'] ] ) ){
                $fields_by_type[ $field_value['type'] ] = [];
            }
            $fields_by_type[ $field_value['type'] ][] = $field_key;
        }

        foreach ( $user_fields as $field_key => $field_value ){
            $field_key = esc_sql( $field_key );
            $field_value = esc_sql( $field_value );

            /**
             * Build query for the users table fields
             */
            if ( $field_value['table'] === 'users_table' ){
                $select .= ", users.$field_key as $field_key";
            }
            /**
             * Build query for the usermeta table fields
             */
            if ( $field_value['table'] === 'usermeta_table' && isset( $field_value['key'] ) ){
                if ( $field_value['type'] === 'text' ){
                    $select .= ", um_$field_key.meta_value as $field_key";
                    $joins .= " LEFT JOIN $wpdb->usermeta as um_$field_key on ( um_$field_key.user_id = users.ID AND um_$field_key.meta_key = '{$field_value['key']}' ) ";
                }
                if ( $field_value['type'] === 'key_select' ){
                    $select .= ", um_$field_key.meta_value as $field_key";
                    $joins .= " LEFT JOIN $wpdb->usermeta as um_$field_key on ( um_$field_key.user_id = users.ID AND um_$field_key.meta_key = '{$field_value['key']}' ) ";
                    if ( !empty( $filter[$field_key] ) ){
                        $where .= $wpdb->prepare( " AND um_$field_key.meta_value LIKE %s ", esc_sql( $filter[$field_key] ) ); //phpcs:ignore
                    }
                }
                if ( $field_value['type'] === 'array' ){
                    $select .= ", um_$field_key.meta_value as $field_key";
                    $joins .= " LEFT JOIN $wpdb->usermeta as um_$field_key on ( um_$field_key.user_id = users.ID AND um_$field_key.meta_key = '{$field_value['key']}' ) ";
                    if ( !empty( $filter[$field_key] ) ){
                        $where .= $wpdb->prepare( " AND um_$field_key.meta_value LIKE %s ", '%'. esc_sql( $filter[$field_key] ) .'%' ); //phpcs:ignore
                    }
                }
                if ( $field_value['type'] === 'array_keys' ){
                    if ( $field_key != 'capabilities' ){
                        $select .= ", um_$field_key.meta_value as $field_key";
                        $joins .= " LEFT JOIN $wpdb->usermeta as um_$field_key on ( um_$field_key.user_id = users.ID AND um_$field_key.meta_key = '{$field_value['key']}' ) ";
                    }
                    if ( !empty( $filter[$field_key] ) ){
                        $where .= $wpdb->prepare( " AND um_$field_key.meta_value LIKE %s ", '%'. esc_sql( $filter[$field_key] ) .'%' ); //phpcs:ignore
                    }
                }

                if ( $field_value['type'] === 'location_grid' ){
                    $select .= ", GROUP_CONCAT(DISTINCT(um_$field_key.meta_value)) as $field_key";
                    $joins .= " LEFT JOIN $wpdb->usermeta as um_$field_key on ( um_$field_key.user_id = users.ID AND um_$field_key.meta_key = '{$field_value['key']}' ) ";
                    if ( !empty( $filter[$field_key] ) ){
                        $where .= $wpdb->prepare( " AND um_$field_key.meta_value LIKE %s ", esc_sql( $filter[$field_key] ) ); //phpcs:ignore
                    }
                }
            }
            /**
             * Build query for the postmeta table fields
             */
            if ( $field_value['table'] === 'postmeta' ){
                $meta_key = esc_sql( $field_value['meta_key'] );
                $meta_value = esc_sql( $field_value['meta_value'] ?? null );
                $select .= ", $field_key.count as $field_key";
                $inner = '';
                if ( !empty( $meta_key ) ){
                    $inner = "INNER JOIN $wpdb->postmeta pm2 ON ( pm2.post_id = pm.post_id AND pm2.meta_key = '$meta_key' )";
                }
                if ( !empty( $meta_key ) && !empty( $meta_value ) ){
                    $inner = "INNER JOIN $wpdb->postmeta pm2 ON ( pm2.post_id = pm.post_id AND pm2.meta_key = '$meta_key' AND pm2.meta_value = '$meta_value' )";
                }
                $joins .= " LEFT JOIN (
                    SELECT REPLACE(pm.meta_value, 'user-', '') as user_id, COUNT(pm.post_id) as count
                    FROM $wpdb->postmeta pm
                    $inner
                    WHERE pm.meta_key = 'assigned_to'
                    GROUP BY pm.meta_value
                ) $field_key ON ( $field_key.user_id = users.ID ) ";

                if ( $sort_field === $field_key ){
                    $sort_sql = " ORDER BY $field_key.count $dir ";
                }
            }
            /**
             * Build query for the dt_activity_log table fields
             */
            if ( $field_value['table'] === 'dt_activity_log' ){
                $select .= ", $field_key.last_activity as $field_key";
                $joins .= " LEFT JOIN ( SELECT user_id,
                    log.hist_time as last_activity
                    FROM $wpdb->dt_activity_log as log
                    WHERE histid IN (
                        SELECT MAX( histid )
                        FROM $wpdb->dt_activity_log
                        GROUP BY user_id
                    )
                    GROUP BY user_id,  last_activity
                ) $field_key ON ( $field_key.user_id = users.ID ) ";
                if ( $sort_field === $field_key ){
                    $sort_sql = " ORDER BY $field_key.last_activity $dir ";
                }
            }
        }

        //phpcs:disable
        $users_query = $wpdb->get_results( $wpdb->prepare( "
            SELECT
                um_capabilities.meta_value as capabilities
                $select
            FROM $wpdb->users as users
            INNER JOIN $wpdb->usermeta as um_capabilities on ( um_capabilities.user_id = users.ID AND um_capabilities.meta_key = %s )
            " . $joins . "
            WHERE 1=1
            $where
            GROUP by users.ID, um_capabilities.meta_value
            $sort_sql
            LIMIT %d
        ", $wpdb->prefix . 'capabilities', $limit ),
            ARRAY_A );
        //phpcs:enable

        /**
         * Get the location names for the location grid ids
         */
        $location_names = self::get_location_grid_names( $users_query );

        /**
         * Format the results
         */
        foreach ( $users_query as &$user ){
            foreach ( $fields_by_type['array'] as $field_key ){
                if ( isset( $user[ $field_key ] ) ){
                    $user[ $field_key ] = unserialize( $user[ $field_key ] );
                }
            }
            foreach ( $fields_by_type['array_keys'] as $field_key ){
                if ( isset( $user[ $field_key ] ) ){
                    $user[ $field_key ] = unserialize( $user[ $field_key ] );
                    $user[ $field_key ] = array_keys( $user[ $field_key ] );
                }
            }
            foreach ( $fields_by_type['location_grid'] as $field_key ){
                if ( isset( $user[$field_key] ) ){
                    $grid_ids = explode( ',', $user[$field_key] );
                    $locations = [];
                    foreach ( $grid_ids as $id ){
                        $locations[] = [
                            'id' => $id,
                            'label' => $location_names[$id] ?? 'Unkonwn',
                        ];
                    }
                    $user[$field_key] = $locations;
                }
            }
        }

        /**
         * Get the total users count
         */
        $total_users = $wpdb->get_var( $wpdb->prepare( "
            SELECT count( users.ID) FROM $wpdb->users as users
            INNER JOIN $wpdb->usermeta as um_capabilities on ( um_capabilities.user_id = users.ID AND um_capabilities.meta_key = %s )
            ", $wpdb->prefix . 'capabilities' ) );

        return [
            'users' => apply_filters( 'dt_users_list', $users_query, $params ),
            'total_users' => intval( $total_users ),
        ];
    }

    public static function get_used_user_locations(){
        global $wpdb;
        $used_location_grids = $wpdb->get_results( $wpdb->prepare( "
            SELECT DISTINCT( g.grid_id ),
            CASE
                WHEN g.level = 0
                    THEN g.alt_name
                WHEN g.level = 1
                    THEN CONCAT( (SELECT country.alt_name FROM $wpdb->dt_location_grid as country WHERE country.grid_id = g.admin0_grid_id LIMIT 1), ' > ',
                g.alt_name )
                WHEN g.level >= 2
                    THEN CONCAT( (SELECT country.alt_name FROM $wpdb->dt_location_grid as country WHERE country.grid_id = g.admin0_grid_id LIMIT 1), ' > ',
                (SELECT a1.alt_name FROM $wpdb->dt_location_grid AS a1 WHERE a1.grid_id = g.admin1_grid_id LIMIT 1), ' > ',
                g.alt_name )
                ELSE g.alt_name
            END as label
            FROM $wpdb->dt_location_grid as g
            INNER JOIN (
                SELECT
                    g.grid_id
                FROM $wpdb->usermeta as um
                JOIN $wpdb->dt_location_grid as g ON g.grid_id=um.meta_value
                WHERE um.meta_key = %s
            ) as counter ON (g.grid_id = counter.grid_id)

            ORDER BY g.country_code, CHAR_LENGTH(label)
            ", $wpdb->prefix . 'location_grid' ),
            ARRAY_A
        );
        //key as index
        $used_location_grids = array_combine( wp_list_pluck( $used_location_grids, 'grid_id' ), $used_location_grids );

        return $used_location_grids;
    }
    public static function get_location_grid_names( $users_query ){
        global $wpdb;
        $location_grid_ids = [];
        foreach ( $users_query as $users ){
            if ( !empty( $users['location_grid'] ) ){
                $location_grid_ids = array_merge( $location_grid_ids, explode( ',', $users['location_grid'] ) );
            }
        }
        $location_grid_ids = array_unique( $location_grid_ids );
        $location_grid_ids_sql = dt_array_to_sql( $location_grid_ids );
        //phpcs:disable
        $location_names_query = $wpdb->get_results( "
            SELECT alt_name, grid_id
            FROM $wpdb->dt_location_grid
            WHERE grid_id IN ( $location_grid_ids_sql )
        ", ARRAY_A );
        //phpcs:enable
        $location_names = [];
        foreach ( $location_names_query as $location ){
            $location_names[ $location['grid_id'] ] = $location['alt_name'];
        }
        return $location_names;
    }


    public function update_settings_on_user( WP_REST_Request $request ){
        if ( !self::has_permission() ){
            return new WP_Error( __METHOD__, 'Missing Permissions', [ 'status' => 401 ] );
        }

        $get_params = $request->get_params();
        $body = $request->get_json_params();

        if ( isset( $get_params['user'] ) ) {
            return Disciple_Tools_Users::update_settings_on_user( $get_params['user'], $body );
        }
        return false;
    }

    public function send_pwd_reset_email( WP_REST_Request $request ){
        $params = $request->get_json_params() ?? $request->get_body_params();

        return [
            'sent' => isset( $params['email'] ) ? retrieve_password( $params['email'] ) : false
        ];
    }


}
new DT_User_Management();
