<?php

class DT_User_Management
{
    public $permissions = [ 'list_users', 'manage_dt' ];

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        if ( $this->has_permission() ){
            $url_path = dt_get_url_path();
            if ( strpos( $url_path, 'user-management' ) !== false || strpos( $url_path, 'user-management' ) !== false ) {
                add_filter( 'dt_metrics_menu', [ $this, 'add_menu' ], 20 );
            }
            if ( strpos( $url_path, 'user-management/user' ) !== false || ( strpos( $url_path, 'user-management/add-user' ) !== false && current_user_can( "create_users" ) ) ){
                add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
                add_filter( 'dt_templates_for_urls', [ $this, 'dt_templates_for_urls' ] );


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
            add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
        }
    }

    public function has_permission(){
        $pass = false;
        foreach ( $this->permissions as $permission ){
            if ( current_user_can( $permission ) ){
                $pass = true;
            }
        }
        return $pass;
    }

    public function add_api_routes() {
        $namespace = 'user-management/v1';

        register_rest_route(
            $namespace, '/user', [
                [
                    'methods'  => "GET",
                    'callback' => [ $this, 'get_user_endpoint' ],
                    'permission_callback' => '__return_true',
                ],
            ]
        );
        register_rest_route(
            $namespace, '/user', [
                [
                    'methods'  => "POST",
                    'callback' => [ $this, 'update_settings_on_user' ],
                    'permission_callback' => '__return_true',
                ],
            ]
        );
        register_rest_route(
            $namespace, '/get_users', [
                [
                    'methods'  => "GET",
                    'callback' => [ $this, 'get_users_endpoints' ],
                    'permission_callback' => '__return_true',
                ],
            ]
        );
    }

    public function dt_templates_for_urls( $template_for_url ) {
        $template_for_url['user-management/users'] = './dt-users/template-user-management.php';
        $template_for_url['user-management/add-user'] = 'template-metrics.php';
        return $template_for_url;
    }

    public function add_menu( $content ) {
        $content .= '<li><a href="'. site_url( '/user-management/users/' ) .'" >' .  esc_html__( 'Users', 'disciple_tools' ) . '</a></li>';
        if ( current_user_can( "create_users" ) ){
            $content .= '<li><a href="'. esc_url( site_url( '/user-management/add-user/' ) ) .'" >' .  esc_html__( 'Add User', 'disciple_tools' ) . '</a></li>';
        }
        return $content;
    }

    public static function user_management_options(){
        return [
            "user_status_options" => [
                "active" => __( 'Active', 'disciple_tools' ),
                "away" => __( 'Away', 'disciple_tools' ),
                "inconsistent" => __( 'Inconsistent', 'disciple_tools' ),
                "inactive" => __( 'Inactive', 'disciple_tools' ),
            ]
        ];
    }

    public function scripts() {
        $url_path = dt_get_url_path();
        if ( strpos( $url_path, 'user-management/user' ) !== false || strpos( $url_path, 'user-management/add-user' ) !== false ) {

            $dependencies = [
                'jquery',
                'moment',
                'lodash'
            ];

            array_push( $dependencies,
                'datatable',
                'datatable-responsive',
                'amcharts-core',
                'amcharts-charts',
                'amcharts-animated'
            );

            wp_register_style( 'datatable-css', '//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css', [], '1.10.19' );
            wp_enqueue_style( 'datatable-css' );
            wp_register_style( 'datatable-responsive-css', '//cdn.datatables.net/responsive/2.2.3/css/responsive.dataTables.min.css', [], '2.2.3' );
            wp_enqueue_style( 'datatable-responsive-css' );
            wp_register_script( 'datatable', '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js', false, '1.10' );
            wp_register_script( 'datatable-responsive', '//cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js', [ 'datatable' ], '2.2.3' );
            wp_register_script( 'amcharts-core', 'https://www.amcharts.com/lib/4/core.js', false, '4' );
            wp_register_script( 'amcharts-charts', 'https://www.amcharts.com/lib/4/charts.js', false, '4' );
            wp_register_script( 'amcharts-animated', 'https://www.amcharts.com/lib/4/themes/animated.js', [ 'amcharts-core' ], '4' );


            wp_enqueue_script( 'dt_dispatcher_tools', get_template_directory_uri() . '/dt-users/user-management.js', $dependencies, filemtime( plugin_dir_path( __FILE__ ) . '/user-management.js' ), true );
            wp_localize_script(
                'dt_dispatcher_tools', 'dt_user_management_localized', [
                    'root'               => esc_url_raw( rest_url() ),
                    'theme_uri'          => trailingslashit( get_stylesheet_directory_uri() ),
                    'nonce'              => wp_create_nonce( 'wp_rest' ),
                    'current_user_login' => wp_get_current_user()->user_login,
                    'current_user_id'    => get_current_user_id(),
                    'map_key'            => DT_Mapbox_API::get_key(),
                    'options'            => self::user_management_options(),
                    'url_path'           => dt_get_url_path(),
                    'translations'       => [
                        'accept_time' => _x( '%1$s was accepted on %2$s after %3$s days', 'Bob was accepted on Jul 8 after 10 days', 'disciple_tools' ),
                        'no_contact_attempt_time' => _x( '%1$s waiting for Contact Attempt for %2$s days', 'Bob waiting for contact for 10 days', 'disciple_tools' ),
                        'contact_attempt_time' => _x( 'Contact with %1$s was attempted on %2$s after %3$s days', 'Contact with Bob was attempted on Jul 8 after 10 days', 'disciple_tools' ),
                        'unable_to_update' => __( 'Unable to update', 'disciple_tools' ),
                        'add_new_user' => __( 'Add New User', 'disciple_tools' ),
                        'view_new_user' => __( 'View New User', 'disciple_tools' ),
                        'there_are_some_errors' => __( 'There are some errors in your form.', 'disciple_tools' ),
                        'contact_to_user' => __( 'Contact to make a user (optional)', 'disciple_tools' ),
                        'nickname' => __( 'Nickname (Display Name)', 'disciple_tools' ),
                        'email' => __( 'Email', 'disciple_tools' ),
                        'create_user' => __( 'Create User', 'disciple_tools' ),
                        'email_already_in_system' => __( 'Email address is already in the system as a user!', 'disciple_tools' ),
                        'username_in_system' => __( 'Username is already in the system as a user!', 'disciple_tools' ),
                        'search' => __( 'Search multipliers and contacts', 'disciple_tools' ),
                        'remove' => __( 'Remove', 'disciple_tools' ),
                        'already_user' => __( 'This contact is already a user.', 'disciple_tools' ),
                        'view_user' => __( 'View User', 'disciple_tools' ),
                    ]

                ]
            );

            if ( DT_Mapbox_API::get_key() ) {
                DT_Mapbox_API::load_mapbox_header_scripts();
                DT_Mapbox_API::load_mapbox_search_widget_users();
            }
        }
    }

    public function get_dt_user( $user_id, $section = null ) {
        if ( ! $this->has_permission() ) {
            return new WP_Error( __METHOD__, "Permission error", [ 'status' => 403 ] );
        }

        global $wpdb;
        $user = get_user_by( "ID", $user_id );
        if ( ! $user ) {
            return new WP_Error( __METHOD__, "No User", [ 'status' => 400 ] );
        }

        $user_response = [
            "display_name" => $user->display_name,
            "user_id" => $user->ID,
            "contact_id" => 0,
            "contact" => [],
            "user_status" => '',
            "workload_status" => '',
            "dates_unavailable" => false,
            "location_grid" => [],
            "user_activity" => [],
            "active_contacts" => 0,
            "update_needed" => [],
            "unread_notifications" => 0,
            "needs_accepted" => 0,
            "days_active" => [],
            "times" => [],
            "assigned_counts" => [],
            "contact_statuses" => [],
            "contact_attempts" => [],
            "contact_accepts" => [],
            "unaccepted_contacts" => [],
            "unattempted_contacts" => [],
            "allowed_sources" => [],
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
            $dates_unavailable = get_user_option( "user_dates_unavailable", $user->ID );
            if ( ! empty( $dates_unavailable ) ) {
                foreach ( $dates_unavailable as &$range ) {
                    $range["start_date"] = dt_format_date( $range["start_date"] );
                    $range["end_date"] = dt_format_date( $range["end_date"] );
                }
            }
            $user_response['dates_unavailable'] = $dates_unavailable;

            /* counts section */
            $month_start = strtotime( gmdate( 'Y-m-01' ) );
            $last_month_start = strtotime( 'first day of last month' );
            $this_year = strtotime( "first day of january this year" );
            //number of assigned contacts
            $assigned_counts = $wpdb->get_results($wpdb->prepare("
                SELECT
                COUNT( CASE WHEN date_assigned.hist_time >= %d THEN 1 END ) as this_month,
                COUNT( CASE WHEN date_assigned.hist_time >= %d AND date_assigned.hist_time < %d THEN 1 END ) as last_month,
                COUNT( CASE WHEN date_assigned.hist_time >= %d THEN 1 END ) as this_year,
                COUNT( date_assigned.histid ) as all_time
                FROM $wpdb->dt_activity_log as date_assigned
                INNER JOIN $wpdb->postmeta as type ON ( date_assigned.object_id = type.post_id AND type.meta_key = 'type' AND type.meta_value != 'user' )
                WHERE date_assigned.meta_key = 'assigned_to'
                    AND date_assigned.object_type = 'contacts'
                    AND date_assigned.meta_value = %s
            ", $month_start, $last_month_start, $month_start, $this_year, 'user-' . $user->ID), ARRAY_A);

            $active_contacts = $wpdb->get_var( $wpdb->prepare( "
                SELECT count(a.ID)
                FROM $wpdb->posts as a
                INNER JOIN $wpdb->postmeta as assigned_to
                ON a.ID=assigned_to.post_id
                  AND assigned_to.meta_key = 'assigned_to'
                  AND assigned_to.meta_value = CONCAT( 'user-', %s )
                JOIN $wpdb->postmeta as b
                  ON a.ID=b.post_id
                     AND b.meta_key = 'overall_status'
                         AND b.meta_value = 'active'
                WHERE a.post_status = 'publish'
                AND post_type = 'contacts'
                AND a.ID NOT IN (
                    SELECT post_id FROM $wpdb->postmeta
                    WHERE meta_key = 'type' AND meta_value = 'user'
                    GROUP BY post_id
                )
            ", $user->ID ) );


            $notification_count = $wpdb->get_var($wpdb->prepare(
                "SELECT count(id)
                        FROM `$wpdb->dt_notifications`
                        WHERE
                            user_id = %d
                            AND is_new = '1'",
                $user->ID
            ));

            $contact_statuses = Disciple_Tools_Counter_Contacts::get_contact_statuses( $user->ID );

            $user_response['contact_statuses'] = $contact_statuses;
            $user_response['active_contacts'] = $active_contacts;

            $user_response['assigned_counts'] = isset( $assigned_counts[0] ) ? $assigned_counts[0] : [];
            $user_response['unread_notifications'] = $notification_count;
        }

        $modules = dt_get_option( "dt_post_type_modules" );
        if ( ( $section === 'details' || $section === 'pace' || $section === null ) && isset( $modules["access_module"]["enabled"] ) && $modules["access_module"]["enabled"] ) {
            $to_accept = DT_Posts::search_viewable_post( "contacts", [
                'overall_status' => [ 'assigned' ],
                'assigned_to' => [ $user->ID ]
            ], false );
            $update_needed = DT_Posts::search_viewable_post( "contacts", [
                'requires_update' => [ "true" ],
                'assigned_to' => [ $user->ID ],
                'overall_status' => [ '-closed', '-paused' ],
                'sort' => 'last_modified'
            ], false );
            if (sizeof( $update_needed["posts"] ) > 5) {
                $update_needed["posts"] = array_slice( $update_needed["posts"], 0, 5 );
            }
            if (sizeof( $to_accept["posts"] ) > 10) {
                $to_accept["posts"] = array_slice( $to_accept["posts"], 0, 10 );
            }
            foreach ($update_needed["posts"] as &$contact) {
                $now = time();
                $last_modified = get_post_meta( $contact->ID, "last_modified", true );
                $days_different = (int) round( ( $now - (int) $last_modified ) / ( 60 * 60 * 24 ) );
                $contact->last_modified_msg = esc_attr( sprintf( __( '%s days since last update', 'disciple_tools' ), $days_different ), 'disciple_tools' );
            }

            $user_response['update_needed'] = $update_needed;
            $user_response['needs_accepted'] = $to_accept;
        }

        /* Locations section */
        if ( $section === 'locations' || $section === null ) {
            $user_response['user_location'] = Disciple_Tools_Users::get_user_location( $user->ID );
        }


        if ( $section === 'activity' || $section === null ) {
            $user_activity = $wpdb->get_results($wpdb->prepare("
                SELECT hist_time, action, object_name, meta_key, object_type, object_note
                FROM $wpdb->dt_activity_log
                WHERE user_id = %s
                AND action IN ( 'comment', 'field_update', 'connected_to', 'logged_in', 'created', 'disconnected_from', 'decline', 'assignment_decline' )
                ORDER BY `hist_time` DESC
                LIMIT 100
            ", $user->ID));
            if ( ! empty( $user_activity ) ) {
                foreach ($user_activity as $a) {
                    if ($a->action === 'field_update' || $a->action === 'connected to' || $a->action === 'disconnected from') {
                        if ($a->object_type === "contacts") {
                            $a->object_note = sprintf( _x( "Updated contact %s", 'Updated record Bob', 'disciple_tools' ), $a->object_name );
                        }
                        if ($a->object_type === "groups") {
                            $a->object_note = sprintf( _x( "Updated group %s", 'Updated record Bob', 'disciple_tools' ), $a->object_name );
                        }
                    }
                    if ($a->action == 'comment') {
                        if ($a->meta_key === "contacts") {
                            $a->object_note = sprintf( _x( "Commented on contact %s", 'Commented on record Bob', 'disciple_tools' ), $a->object_name );
                        }
                        if ($a->meta_key === "groups") {
                            $a->object_note = sprintf( _x( "Commented on group %s", 'Commented on record Bob', 'disciple_tools' ), $a->object_name );
                        }
                    }
                    if ($a->action == 'created') {
                        if ($a->object_type === "contacts") {
                            $a->object_note = sprintf( _x( "Created contact %s", 'Created record Bob', 'disciple_tools' ), $a->object_name );
                        }
                        if ($a->object_type === "groups") {
                            $a->object_note = sprintf( _x( "Created group %s", 'Created record Bob', 'disciple_tools' ), $a->object_name );
                        }
                    }
                    if ($a->action === "logged_in") {
                        $a->object_note = __( "Logged In", 'disciple_tools' );
                    }
                    if ($a->action === 'assignment_decline') {
                        $a->object_note = sprintf( _x( "Declined assignment on %s", 'Declined assignment on Bob', 'disciple_tools' ), $a->object_name );
                    }
                }
            }
            $user_response['user_activity'] = $user_activity;
        }

        if ( $section === 'contact_attempts' || $section === null ) {
//            $user_response['contact_attempts'] = $this->query_contact_attempts( $user->ID ); // @todo query running super slow, needs rewrite
            $user_response['contact_attempts'] = [];
        }

        if ( $section === 'contact_accepts' || $section === null ) {
            $user_response['contact_accepts'] = $this->query_contact_accepts( $user->ID );
        }

        if ( $section === 'unaccepted_contacts' || $section === null ) {
            $user_response['unaccepted_contacts'] = $this->query_unaccepted_contacts( $user->ID );
        }

        if ( $section === 'unattempted_contacts' || $section === null ) {
            $user_response['unattempted_contacts'] = $this->query_unattempted_contacts( $user->ID );
        }

        if ( $section === 'days_active' || $section === null ) {

            $one_year = time() - 3600 * 24 * 365;
            $days_active_results = $wpdb->get_results($wpdb->prepare("
                SELECT FROM_UNIXTIME(`hist_time`, '%%Y-%%m-%%d') as day,
                count(histid) as activity_count
                FROM $wpdb->dt_activity_log
                WHERE user_id = %s
                AND hist_time > %s
                group by day
                ORDER BY `day` ASC",
                $user->ID,
                $one_year
            ), ARRAY_A);
            $days_active = [];
            foreach ($days_active_results as $a) {
                $days_active[$a["day"]] = $a;
            }
            $first = isset( $days_active_results[0]['day'] ) ? strtotime( $days_active_results[0]['day'] ) : time();
            $first_week_start = gmdate( 'Y-m-d', strtotime( '-' . gmdate( 'w', $first ) . ' days', $first ) );
            $current = strtotime( $first_week_start );
            $daily_activity = [];
            while ($current < time()) {

                $activity = $days_active[gmdate( 'Y-m-d', $current )]["activity_count"] ?? 0;

                $daily_activity[] = [
                    "day" => dt_format_date( $current ),
                    "weekday" => gmdate( 'l', $current ),
                    "weekday_number" => gmdate( 'N', $current ),
                    "week_start" => gmdate( 'Y-m-d', strtotime( '-' . gmdate( 'w', $current ) . ' days', $current ) ),
                    "activity_count" => $activity,
                    "activity" => $activity > 0 ? 1 : 0
                ];

                $current += 24 * 60 * 60;
            }

            $user_response['days_active'] = $daily_activity;
        }


        if ( current_user_can( "promote_users" ) ){
            $user_response["roles"] = $user->roles;
            $user_response["allowed_sources"] = get_user_option( 'allowed_sources', $user->ID ) ?: [];
        }

        return $user_response;

    }

    public function get_user_endpoint( WP_REST_Request $request ) {
        if ( !$this->has_permission() ) {
            return new WP_Error( "get_user", "Missing Permissions", [ 'status' => 401 ] );
        }

        $params = $request->get_params();
        if ( ! isset( $params["user"] ) ) {
            return new WP_Error( __METHOD__, "Missing user id", [ 'status' => 400 ] );
        }
        if ( ! isset( $params["section"] ) ) {
            return new WP_Error( __METHOD__, "Missing collection id", [ 'status' => 400 ] );
        }
        return $this->get_dt_user( $params["user"], $params["section"] );
    }

    public function get_users_endpoints( WP_REST_Request $request ){
        if ( !$this->has_permission() ){
            return new WP_Error( "get_user", "Missing Permissions", [ 'status' => 401 ] );
        }
        $params = $request->get_params();
        $refresh = isset( $params["refresh"] ) && $params["refresh"] = "1";
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
                $users[ $user["ID"] ] = $user;
                $users[ $user["ID"] ]['location_grid'] = false;
                $users[ $user["ID"] ]['location_grid_meta'] = false;
                $users[ $user["ID"] ]['number_update'] = 0;
                $users[ $user["ID"] ]['number_assigned_to'] = 0;
                $users[ $user["ID"] ]['number_new_assigned'] = 0;
                $users[ $user["ID"] ]['number_active'] = 0;
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
                $user_id = str_replace( "user-", '', $user["assigned_to"] );
                if ( isset( $users[$user_id] ) ) {
                    $users[$user_id]["number_assigned_to"] = $user["number_assigned_to"];
                    $users[$user_id]["number_active"] = $user["number_active"];
                    $users[$user_id]["number_new_assigned"] = $user["number_new_assigned"];
                    $users[$user_id]["number_update"] = $user["number_update"];
                }
            }

            $user_statuses = $wpdb->get_results( $wpdb->prepare( "
                SELECT * FROM $wpdb->usermeta
                WHERE meta_key = %s
            ", $wpdb->prefix . 'user_status' ), ARRAY_A );
            foreach ( $user_statuses as $meta_row ){
                if ( isset( $users[ $meta_row["user_id"] ] ) ) {
                    $users[$meta_row["user_id"]]["user_status"] = $meta_row["meta_value"];
                }
            }
            $user_workloads = $wpdb->get_results( $wpdb->prepare( "
                SELECT * FROM $wpdb->usermeta
                WHERE meta_key = %s
            ", $wpdb->prefix . 'workload_status' ), ARRAY_A );
            foreach ( $user_workloads as $meta_row ){
                if ( isset( $users[ $meta_row["user_id"] ] ) ) {
                    $users[$meta_row["user_id"]]["workload_status"] = $meta_row["meta_value"];
                }
            }
            $user_locations_grid_meta = $wpdb->get_results( $wpdb->prepare( "
                SELECT user_id, meta_value as grid_id
                FROM $wpdb->usermeta
                WHERE meta_key = %s
            ", $wpdb->prefix . 'location_grid_meta'), ARRAY_A);
            foreach ( $user_locations_grid_meta as $user_with_location ){
                if ( isset( $users[ $user_with_location['user_id'] ] ) ) {
                    $users[$user_with_location['user_id']]["location_grid_meta"] = true;
                }
            }
            $user_locations_grid = $wpdb->get_results( $wpdb->prepare( "
                SELECT user_id, meta_value as grid_id
                FROM $wpdb->usermeta
                WHERE meta_key = %s
            ", $wpdb->prefix . 'location_grid'), ARRAY_A);
            foreach ( $user_locations_grid as $user_with_location ){
                if ( isset( $users[ $user_with_location['user_id'] ] ) ) {
                    $users[$user_with_location['user_id']]["location_grid"] = true;
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
                if ( isset( $users[ $a["user_id"] ] ) ) {
                    $users[$a["user_id"]]["last_activity"] = $a["last_activity"];
                }
            }

            if ( !empty( $users ) ){
                set_transient( 'dispatcher_user_data', maybe_serialize( $users ), 60 * 60 * 24 );
            }
        }
        if ( current_user_can( "list_users" ) ) {
            return $users;
        } else {
            $multipliers = [];
            foreach ( $users as $user_id => $user ) {
                $user_roles = maybe_unserialize( $user["roles"] );
                if ( in_array( "multiplier", $user_roles ) ){
                    unset( $user["roles"] );
                    $multipliers[$user_id] = $user;
                }
            }
            return $multipliers;
        }

    }

    public function update_settings_on_user( WP_REST_Request $request ){
        if ( !$this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", [ 'status' => 401 ] );
        }

        $get_params = $request->get_params();
        $body = $request->get_json_params();

        if ( isset( $get_params["user"] ) ) {
            delete_transient( 'dispatcher_user_data' );
            $user = get_user_by( "ID", $get_params["user"] );
            if ( !$user ){
                return new WP_Error( "user_id", "User does not exist", [ 'status' => 400 ] );
            }
            if ( empty( $user->caps ) ) {
                return new WP_Error( "user_id", "Cannot update this user", [ 'status' => 400 ] );
            }
            if ( !empty( $body["user_status"] ) ) {
                update_user_option( $user->ID, 'user_status', $body["user_status"] );
            }
            if ( !empty( $body["workload_status"] ) ) {
                update_user_option( $user->ID, 'workload_status', $body["workload_status"] );
            }
            if ( !empty( $body["add_location"] ) ){
                Disciple_Tools_Users::add_user_location( $body["add_location"], $user->ID );
            }
            if ( !empty( $body["remove_location"] ) ){
                Disciple_Tools_Users::delete_user_location( $body["remove_location"], $user->ID );
            }
            if ( !empty( $body["add_unavailability"] ) ){
                if ( !empty( $body["add_unavailability"]["start_date"] ) && !empty( $body["add_unavailability"]["end_date"] ) ) {
                    $dates_unavailable = get_user_option( "user_dates_unavailable", $user->ID );
                    if ( !$dates_unavailable ){
                        $dates_unavailable = [];
                    }
                    $max_id = 0;
                    foreach ( $dates_unavailable as $range ){
                        $max_id = max( $max_id, $range["id"] ?? 0 );
                    }

                    $dates_unavailable[] = [
                        "id" => $max_id + 1,
                        "start_date" => strtotime( $body["add_unavailability"]["start_date"] ),
                        "end_date" => strtotime( $body["add_unavailability"]["end_date"] ),
                    ];
                    update_user_option( $user->ID, "user_dates_unavailable", $dates_unavailable );
                    return $this->get_dt_user( $user->ID );
                }
            }
            if ( !empty( $body["remove_unavailability"] ) ) {
                $dates_unavailable = get_user_option( "user_dates_unavailable", $user->ID );
                foreach ( $dates_unavailable as $index => $range ) {
                    if ( $body["remove_unavailability"] === $range["id"] ){
                        unset( $dates_unavailable[$index] );
                    }
                }
                $dates_unavailable = array_values( $dates_unavailable );
                update_user_option( $user->ID, "user_dates_unavailable", $dates_unavailable );
                return $dates_unavailable;
            }
            if ( isset( $body["save_roles"] ) ){
                // If the current user can't promote users or edit this particular user, bail.
                if ( !current_user_can( 'promote_users' ) ) {
                    return false;
                }
                $can_not_promote_to_roles = [];
                if ( !is_super_admin() && !dt_current_user_has_role( 'administrator' ) ){
                    $can_not_promote_to_roles = [ 'administrator' ];
                }
                if ( !current_user_can( 'manage_dt' ) ){
                    $can_not_promote_to_roles = array_merge( $can_not_promote_to_roles, dt_multi_role_get_cap_roles( 'manage_dt' ) );
                }

                // Create a new user object.
                $u = new WP_User( $user->ID );

                // If we have an array of roles.
                if ( ! empty( $body['save_roles'] ) ) {

                    // Get the current user roles.
                    $old_roles = (array) $u->roles;

                    // Sanitize the posted roles.
                    $new_roles = array_map( 'dt_multi_role_sanitize_role', array_map( 'sanitize_text_field', wp_unslash( $body['save_roles'] ) ) );

                    // Loop through the posted roles.
                    foreach ( $new_roles as $new_role ) {

                        // If the user doesn't already have the role, add it.
                        if ( dt_multi_role_is_role_editable( $new_role ) && ! in_array( $new_role, (array) $user->roles ) ) {
                            if ( !in_array( $new_role, $can_not_promote_to_roles ) ){
                                $u->add_role( $new_role );
                            }
                        }
                    }

                    // Loop through the current user roles.
                    foreach ( $old_roles as $old_role ) {

                        // If the role is editable and not in the new roles array, remove it.
                        if ( dt_multi_role_is_role_editable( $old_role ) && ! in_array( $old_role, $new_roles ) ) {
                            if ( !in_array( $old_role, $can_not_promote_to_roles ) ){
                                $u->remove_role( $old_role );
                            }
                        }
                    }

                    // If the posted roles are empty.
                } else {

                    // Loop through the current user roles.
                    foreach ( (array) $u->roles as $old_role ) {

                        // Remove the role if it is editable.
                        if ( dt_multi_role_is_role_editable( $old_role ) ) {
                            $u->remove_role( $old_role );
                        }
                    }
                }
                return $this->get_dt_user( $user->ID );
            }
            if ( isset( $body["allowed_sources"] ) ){
                // If the current user can't promote users or edit this particular user, bail.
                if ( !current_user_can( 'promote_users' ) ) {
                    return false;
                }
                $allowed_sources = [];
                foreach ( $body["allowed_sources"] as $s ){
                    $allowed_sources[] = sanitize_key( wp_unslash( $s ) );
                }
                if ( in_array( "restrict_all_sources", $allowed_sources ) ){
                    $allowed_sources = [ "restrict_all_sources" ];
                }
                update_user_option( $user->ID, "allowed_sources", $allowed_sources );
                return $this->get_dt_user( $user->ID );
            }
            if ( isset( $body['update_nickname'] ) ) {
                $display_name = sanitize_text_field( wp_unslash( $body['update_nickname'] ) );
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
        }
        return false;
    }

    public function query_contact_attempts( $user_id ) {
        global $wpdb;
        $user_assigned_to = 'user-' . esc_sql( $user_id );

        return $wpdb->get_results( $wpdb->prepare( "
            SELECT contacts.ID,
                MAX(date_assigned.hist_time) as date_assigned,
                MIN(date_attempted.hist_time) as date_attempted,
                MIN(date_attempted.hist_time) - MAX(date_assigned.hist_time) as time,
                contacts.post_title as name
            from $wpdb->posts as contacts
            INNER JOIN $wpdb->postmeta as pm on ( contacts.ID = pm.post_id AND pm.meta_key = 'assigned_to' )
            INNER JOIN $wpdb->dt_activity_log as date_attempted on ( date_attempted.meta_key = 'seeker_path' and date_attempted.object_type = 'contacts' AND date_attempted.object_id = contacts.ID AND date_attempted.meta_value ='attempted' )
            INNER JOIN $wpdb->dt_activity_log as date_assigned on (
                date_assigned.meta_key = 'assigned_to'
                AND date_assigned.object_type = 'contacts'
                AND date_assigned.object_id = contacts.ID
                AND date_assigned.meta_value = %s )
            WHERE date_attempted.hist_time > date_assigned.hist_time
            AND pm.meta_value = %s
            AND date_assigned.hist_time = (
                SELECT MAX(hist_time) FROM $wpdb->dt_activity_log a WHERE
                a.meta_key = 'assigned_to'
                AND a.object_type = 'contacts'
                AND a.object_id = contacts.ID )
            AND contacts.ID NOT IN (
                SELECT post_id FROM $wpdb->postmeta
                WHERE meta_key = 'type' AND meta_value = 'user'
                GROUP BY post_id )
            GROUP by contacts.ID
            ORDER BY date_attempted desc
            LIMIT 10
        ", $user_assigned_to, $user_assigned_to ), ARRAY_A);
    }

    public function query_unattempted_contacts( $user_id ) {
        global $wpdb;
        $user_assigned_to = 'user-' . esc_sql( $user_id );

        return $wpdb->get_results( $wpdb->prepare( "
            SELECT contacts.ID,
                MAX(date_assigned.hist_time) as date_assigned,
                %d - MAX(date_assigned.hist_time) as time,
                contacts.post_title as name
            from $wpdb->posts as contacts
            INNER JOIN $wpdb->postmeta as pm on ( contacts.ID = pm.post_id AND pm.meta_key = 'assigned_to' )
            INNER JOIN $wpdb->postmeta as pm1 on ( contacts.ID = pm1.post_id AND pm1.meta_key = 'seeker_path' and pm1.meta_value = 'none' )
            INNER JOIN $wpdb->postmeta as pm2 on ( contacts.ID = pm2.post_id AND pm2.meta_key = 'overall_status' and pm2.meta_value = 'active' )
            INNER JOIN $wpdb->dt_activity_log as date_assigned on (
                date_assigned.meta_key = 'assigned_to'
                AND date_assigned.object_type = 'contacts'
                AND date_assigned.object_id = contacts.ID
                AND date_assigned.meta_value = %s )
            WHERE pm.meta_value = %s
            AND contacts.ID NOT IN (
                SELECT post_id FROM $wpdb->postmeta
                WHERE meta_key = 'type' AND meta_value = 'user'
                GROUP BY post_id )
            GROUP by contacts.ID
            ORDER BY date_assigned asc
            LIMIT 10
        ", time(), $user_assigned_to, $user_assigned_to ), ARRAY_A);
    }

    public function query_contact_accepts( $user_id ) {
        global $wpdb;
        $user_assigned_to = 'user-' . esc_sql( $user_id );

        return $wpdb->get_results( $wpdb->prepare( "
            SELECT contacts.ID,
                MAX(date_assigned.hist_time) as date_assigned,
                MIN(date_accepted.hist_time) as date_accepted,
                MIN(date_accepted.hist_time) - MAX(date_assigned.hist_time) as time,
                contacts.post_title as name
            from $wpdb->posts as contacts
            INNER JOIN $wpdb->postmeta as pm on ( contacts.ID = pm.post_id AND pm.meta_key = 'assigned_to' )
            INNER JOIN $wpdb->dt_activity_log as date_accepted on (
                date_accepted.meta_key = 'overall_status'
                AND date_accepted.object_type = 'contacts'
                AND date_accepted.object_id = contacts.ID
                AND date_accepted.meta_value = 'active' )
            INNER JOIN $wpdb->dt_activity_log as date_assigned on (
                date_assigned.meta_key = 'assigned_to'
                AND date_assigned.object_type = 'contacts'
                AND date_assigned.object_id = contacts.ID
                AND date_assigned.user_id != %d
                AND date_assigned.meta_value = %s )
            WHERE date_accepted.hist_time > date_assigned.hist_time
            AND pm.meta_value = %s
            AND date_assigned.hist_time = (
                SELECT MAX(hist_time) FROM $wpdb->dt_activity_log a WHERE
                a.meta_key = 'assigned_to'
                AND a.object_type = 'contacts'
                AND a.object_id = contacts.ID )
            AND contacts.ID NOT IN (
                SELECT post_id FROM $wpdb->postmeta
                WHERE meta_key = 'type' AND meta_value = 'user'
                GROUP BY post_id )
            GROUP by contacts.ID
            ORDER BY date_accepted desc
            LIMIT 10
        ", esc_sql( $user_id ), $user_assigned_to, $user_assigned_to ), ARRAY_A);
    }

    public function query_unaccepted_contacts( $user_id ) {
        global $wpdb;
        $user_assigned_to = 'user-' . esc_sql( $user_id );

        return $wpdb->get_results( $wpdb->prepare( "
            SELECT contacts.ID,
                MAX(date_assigned.hist_time) as date_assigned,
                %d - MAX(date_assigned.hist_time) as time,
                contacts.post_title as name
            from $wpdb->posts as contacts
            INNER JOIN $wpdb->postmeta as pm on ( contacts.ID = pm.post_id AND pm.meta_key = 'assigned_to' )
            INNER JOIN $wpdb->postmeta as pm1 on ( contacts.ID = pm1.post_id AND pm1.meta_key = 'overall_status' and pm1.meta_value = 'assigned' )
            INNER JOIN $wpdb->dt_activity_log as date_assigned on (
                date_assigned.meta_key = 'assigned_to'
                AND date_assigned.object_type = 'contacts'
                AND date_assigned.object_id = contacts.ID
                AND date_assigned.meta_value = %s )
            WHERE pm.meta_value = %s
            AND contacts.ID NOT IN (
                SELECT post_id FROM $wpdb->postmeta
                WHERE meta_key = 'type' AND meta_value = 'user'
                GROUP BY post_id )
            GROUP by contacts.ID
            ORDER BY date_assigned asc
            LIMIT 10
        ", time(), $user_assigned_to, $user_assigned_to ), ARRAY_A);

    }

}
new DT_User_Management();
