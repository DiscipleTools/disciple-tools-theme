<?php

Disciple_Tools_Metrics_Users::instance();
class Disciple_Tools_Metrics_Users extends Disciple_Tools_Metrics_Hooks_Base
{
    public $permissions = [ 'view_any_contacts', 'view_project_metrics' ];

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );

        $url_path = dt_get_url_path();

        if ( !$this->has_permission() ){
            return;
        }

        if ( 'metrics' === substr( $url_path, '0', 7 ) ) {

            add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ] ); // add custom URL
            add_filter( 'dt_metrics_menu', [ $this, 'add_menu' ], 50 );

            if ( 'metrics/workers' === $url_path ) {
                add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
            }
        }
    }

    public function add_url( $template_for_url ) {
        $template_for_url['metrics/workers'] = 'template-metrics.php';
        return $template_for_url;
    }

    public function add_api_routes() {
        $version = '1';
        $namespace = 'dt/v' . $version;

        register_rest_route(
            $namespace, '/metrics/workers/refresh_pace', [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [ $this, 'refresh_pace' ],
                ],
            ]
        );
        register_rest_route(
            $namespace, '/metrics/workers/workers_pace', [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [ $this, 'workers_pace' ],
                ],
            ]
        );
        register_rest_route(
            $namespace, '/metrics/workers/contact_progress_per_worker', [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [ $this, 'contact_progress_per_worker' ],
                ],
            ]
        );
        register_rest_route(
            $namespace, '/metrics/workers/user_pace', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'user_pace' ],
                ],
            ]
        );
    }

    public function refresh_pace( WP_REST_Request $request ) {
        $query_params = $request->get_query_params();
        $force = isset( $query_params["force"] ) && $query_params["force"] === "1";
        if ( !$this->has_permission() ){
            return new WP_Error( "refresh_pace", "Missing Permissions", [ 'status' => 400 ] );
        }
        return $this->get_workers_data( $force );
    }

    public function workers_pace( WP_REST_Request $request ) {
        $query_params = $request->get_query_params();
        $force = isset( $query_params["force"] ) && $query_params["force"] === "1";
        if ( ! $this->has_permission() ){
            return new WP_Error( "workers_pace", "Missing Permissions", [ 'status' => 400 ] );
        }
        return $this->get_workers_data( $force );
    }

    public function contact_progress_per_worker() {
        if ( ! $this->has_permission() ){
            return new WP_Error( "contact_progress_per_worker", "Missing Permissions", [ 'status' => 400 ] );
        }
        return $this->chart_contact_progress_per_worker();
    }

    public function user_pace( WP_REST_Request $request ) {
        if ( ! $this->has_permission() ){
            return new WP_Error( "pace_assigned_to_accepted", "Missing Permissions", [ 'status' => 400 ] );
        }
        $params = $request->get_params();

        $force_reset = false;
        if ( isset( $params['force_reset'] ) ) {
            $force_reset = $params['force_reset'];
        }
        return $this->chart_user_pace( strtotime( '-12 months', current_time( 'timestamp' ) ), current_time( 'timestamp' ), $force_reset );
    }

    public function add_menu( $content ) {
        $content .= '
            <li><a href="">' .  esc_html__( 'Workers', 'disciple_tools' ) . '</a>
                <ul class="menu vertical nested" id="workers-menu" aria-expanded="true">
                    <li><a href="'. site_url( '/metrics/workers/' ) .'#workers_activity" onclick="workers_activity()">'. esc_html__( 'Activity', 'disciple_tools' ) .'</a></li>
                    <li><a href="'. site_url( '/metrics/workers/' ) .'#follow_up_pace" onclick="show_follow_up_pace()">'. esc_html__( 'Overall Pace', 'disciple_tools' ) .'</a></li>
                    <li><a href="'. site_url( '/metrics/workers/' ) .'#contact_follow_up_pace" onclick="contact_follow_up_pace()">'. esc_html__( 'Follow-up Pace', 'disciple_tools' ) .'</a></li>
                </ul>
            </li>
            ';
        return $content;
    }

    public function scripts() {
        wp_enqueue_script( 'dt_metrics_workers_script', get_stylesheet_directory_uri() . '/dt-metrics/metrics-workers.js', [
            'jquery',
            'jquery-ui-core',
            'amcharts-core',
            'amcharts-charts',
        ], filemtime( get_theme_file_path() . '/dt-metrics/metrics-workers.js' ), true );

        wp_localize_script(
            'dt_metrics_workers_script', 'dtMetricsUsers', [
                'root' => esc_url_raw( rest_url() ),
                'theme_uri' => get_stylesheet_directory_uri(),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'data' => $this->workers(),
            ]
        );
    }

    public function workers() {
        return [
            'hero_stats' => $this->chart_user_hero_stats(),
            'recent_activity' => $this->chart_recent_activity(),
            'translations' => [
                'title_activity' => __( 'Workers Activity', 'disciple_tools' ),
                'title_recent_activity' => __( 'Worker System Engagement for the Last 30 Days', 'disciple_tools' ),
                'title_response' => __( 'Follow-up Pace', 'disciple_tools' ),
                'label_total_workers' => __( 'Total Workers', 'disciple_tools' ),
                'label_total_multipliers' => __( 'Multipliers', 'disciple_tools' ),
                'label_total_dispatchers' => __( 'Dispatchers', 'disciple_tools' ),
                'label_total_administrators' => __( 'Admins', 'disciple_tools' ),
                'label_total_strategists' => __( 'Strategists', 'disciple_tools' ),
                'label_contacts_per_user' => __( 'Contact Progress per Worker', 'disciple_tools' ),
                'label_least_active' => __( 'Least Active', 'disciple_tools' ),
                'label_most_active' => __( 'Most Active', 'disciple_tools' ),
                'label_select_year' => __( 'Select All time or a specific year to display', 'disciple_tools' ),
                'label_all_time' => __( 'All time', 'disciple_tools' ),
            ],
        ];
    }

    public function chart_user_hero_stats() {
        $result = count_users();
        $worker_ids = get_users( [
            'role__in' => [ 'multiplier','dispatcher','dt_admin','strategist','Administrator' ],
            'fields' => 'ID'
        ] );
        $total_workers = count( $worker_ids );

        return [
            'total_workers' => $total_workers,
            'total_multipliers' => $result['avail_roles']['multiplier'] ?? 0,
            'total_dispatchers' => $result['avail_roles']['dispatcher'] ?? 0,
            'total_administrators' => $result['avail_roles']['dt_admin'] ?? 0,
            'total_strategists' => $result['avail_roles']['strategist'] ?? 0,
            'all_roles' => $result['avail_roles'],
        ];
    }

    public function chart_recent_activity() {
        $chart = [];

        $results = Disciple_Tools_Queries::instance()->query( 'recent_unique_logins' );
        if ( empty( $results ) ) {
            return $chart;
        }

        $days = 31;
        $last_30_days = [];
        while ( $days > 0 ) {
            $last_30_days[] = date( 'Y-m-d', strtotime( '- ' . $days . ' days' ) );
            $days--;
        }

        $results = array_reverse( $results );
        foreach ( $last_30_days as $day ) {

            $total = 0;

            foreach ( $results as $result ) {
                if ( $day == $result['report_date'] ) {
                    $total = $result['total'];
                    break;
                }
            }

            $chart[] = [
                'date' => date( 'M d', strtotime( $day ) ),
                'value' => (int) $total
            ];
        }

        return $chart;
    }

    public function chart_contact_progress_per_worker( $force_refresh = false ) {
        $force_refresh = true; // for testing

        if ( $force_refresh ) {
            delete_transient( 'chart_contact_progress_per_worker' );
        }
        if ( get_transient( 'chart_contact_progress_per_worker' ) ) {
            return maybe_unserialize( get_transient( 'chart_contact_progress_per_worker' ) );
        }
        $chart = [];

        $chart[] = [ 'Name', 'Last Update', 'Assigned', 'Accepted', 'Active', 'Attempt Needed', 'Attempted', 'Established', 'Meet Scheduled', 'Meet Complete', 'Ongoing Meeting', 'Baptisms', 'Coaching' ];

        $results = Disciple_Tools_Queries::instance()->query( 'contact_progress_per_worker' );
        $baptized = Disciple_Tools_Queries::instance()->query( 'baptized_per_worker' );
        $activity = Disciple_Tools_Queries::instance()->query( 'last_update_per_worker' );

        $worker_ids = get_users( [
            'role__in' => [ 'multiplier','dispatcher','dt_admin','strategist','Administrator' ],
            'fields' => 'ID'
        ] );

        if ( empty( $results ) ) {
            return $chart;
        }


        foreach ( $results as $result ) {

            if ( ! array_search( $result['user_id'], $worker_ids ) ) {
                continue;
            }

            $user = get_userdata( $result['user_id'] );
            if ( empty( $user ) ) {
                continue;
            }

            $baptisms = 0;
            if ( ! empty( $baptized ) ) {
                foreach ( $baptized as $value ) {
                    if ( empty( $value['user_id'] ) ) {
                        continue;
                    }

                    if ( $value['user_id'] === $result['user_id'] ) {
                        $baptisms = $value['count'];
                    }
                }
            }

            $last_activity = "";
            if ( !empty( $activity ) ){
                foreach ( $activity as $user_activity ){
                    if ( $user_activity["user_id"] == $result["user_id"] ){
                        $last_activity = dt_format_date( $user_activity["last_update"] );
                    }
                }
            }

            $chart[] = [
                $user->display_name,
                $last_activity,
                (int) $result['assigned'],
                (int) $result['accepted'],
                (int) $result['active'],
                (int) $result['attempt_needed'],
                (int) $result['attempted'],
                (int) $result['established'],
                (int) $result['meeting_scheduled'],
                (int) $result['meeting_complete'],
                (int) $result['ongoing'],
                (int) $baptisms,
                (int) $result['being_coached'],
            ];
        }

        set_transient( 'chart_contact_progress_per_worker', maybe_serialize( $chart ), 60 * 60 * 24 );

        return $chart;
    }

    /**
     * @param int|null $begin_date (must be a valid Unix timestamp)
     * @param int|null $end_date (must be a valid Unix timestamp)
     * @param bool     $force_refresh
     *
     * @return array|mixed
     */
    public function chart_user_pace( int $begin_date = null, int $end_date = null, $force_refresh = false ) {
//        $force_refresh = true; // for testing

        if ( $force_refresh ) {
            delete_transient( 'chart_user_pace' );
        }
        if ( get_transient( 'chart_user_pace' ) ) {
            return maybe_unserialize( get_transient( 'chart_user_pace' ) );
        }

        // Setup chart
        $chart = [];
        $chart[] = [ 'Name', 'Assigned to Accepted Pace', 'Average' ];

        // Timestamps
        if ( empty( $begin_date ) || ! is_valid_timestamp( $begin_date ) ) {
            $begin_date = strtotime( '-10 years', current_time( 'timestamp' ) ); // @todo add data select functions to chart
        }
        if ( empty( $end_date ) || ! is_valid_timestamp( $end_date ) ) {
            $end_date = current_time( 'timestamp' );
        }
        $args = [
            'begin_date' => $begin_date,
            'end_date' => $end_date
        ];

        // Get average pace
        $ap = [
            'new_to_assigned' => 0,
            'assigned_to_accepted' => 0,
            'accepted_to_attempted' => 0,
        ];

        // get assigned to accepted coalition pace
        $ap_assigned_to_accepted = Disciple_Tools_Queries::instance()->query( 'pace_coalition', $args );
        if ( ! empty( $ap_assigned_to_accepted['avg'] ) ) {
            $formated_average_pace = Disciple_Tools_Metrics::convert_seconds( intval( $ap_assigned_to_accepted['avg'] ) );
        } else {
            $formated_average_pace = 'unknown';
        }
        $ap['assigned_to_accepted'] = $formated_average_pace;


        // Get user pace
        $results = Disciple_Tools_Queries::instance()->query( 'pace_users', $args );

        $user_pace = [];
        foreach ( $results as $result ) {
            $user_pace[$result['assigned_to']][] = $result['pace'];
        }

        foreach ( $user_pace as $user => $items ) {
            if ( count( $items ) ) {
                $user_id = str_replace( 'user-', '', $user );
                $userdata = get_userdata( $user_id );
                if ( ! $userdata ) {
                    continue;
                }

                $a = array_filter( $items );
                $average_user_pace = array_sum( $a ) /count( $a );
                $average_user_pace_formatted = Disciple_Tools_Metrics::convert_seconds( intval( $average_user_pace ) );

                if ( ! empty( $ap_assigned_to_accepted['avg'] ) ) {

                    $coalition_avg = intval( $ap_assigned_to_accepted['avg'] );
                    if ( $average_user_pace > $coalition_avg ) {
                        $dif = $average_user_pace - $coalition_avg;
                        $acp = '<span style="color:red;">' . Disciple_Tools_Metrics::convert_seconds( intval( $dif ) ) . ' slower than average</span>';
                        $average_user_pace_formatted = '<span style="color:red;">' . $average_user_pace_formatted . '</span>';
                    } else {
                        $dif = $coalition_avg - $average_user_pace;
                        $acp = Disciple_Tools_Metrics::convert_seconds( intval( $dif ) ) . ' faster than average';
                    }
                } else {
                    $acp = 'unknown';
                }

                $chart[] = [
                    $userdata->display_name,
                    $average_user_pace_formatted,
                    $acp,
                ];
            }
        }

        $response = [
          'acp' => $ap['assigned_to_accepted'],
          'chart' => $chart,
          'timestamp' => current_time( 'timestamp' ),
        ];

        set_transient( 'chart_user_pace', $response, 60 * 60 * 24 );

        return $response;
    }

    public function get_workers_data( $force_refresh = false ) {
        if ( $force_refresh ) {
            delete_transient( 'get_workers_data' );
        }
        if ( get_transient( 'get_workers_data' ) ) {
            return maybe_unserialize( get_transient( 'get_workers_data' ) );
        }

        global $wpdb;
        $workers = $wpdb->get_results( "
            SELECT users.ID,
                users.display_name,
                count(pm.post_id) as number_assigned_to,
                count(met.post_id) as number_met,
                count(active.post_id) as number_active,
                count(new_assigned.post_id) as number_new_assigned,
                count(update_needed.post_id) as number_update
            from $wpdb->users as users
            INNER JOIN $wpdb->usermeta as um on ( um.user_id = users.ID AND um.meta_key = 'wp_capabilities' AND um.meta_value LIKE '%multiplier%' )
            INNER JOIN $wpdb->postmeta as pm on (pm.meta_key = 'assigned_to' and pm.meta_value = CONCAT( 'user-', users.ID ) )
            INNER JOIN $wpdb->postmeta as type on (type.post_id = pm.post_id and type.meta_key = 'type' and ( type.meta_value = 'media' OR type.meta_value = 'next_gen' ) )
            LEFT JOIN $wpdb->postmeta as met on (met.post_id = type.post_id and met.meta_key = 'seeker_path' and ( met.meta_value = 'met' OR met.meta_value = 'ongoing' OR met.meta_value = 'coaching' ) )
            LEFT JOIN $wpdb->postmeta as active on (active.post_id = type.post_id and active.meta_key = 'overall_status' and active.meta_value = 'active' )
            LEFT JOIN $wpdb->postmeta as new_assigned on (new_assigned.post_id = type.post_id and new_assigned.meta_key = 'overall_status' and new_assigned.meta_value = 'assigned' )
            LEFT JOIN $wpdb->postmeta as update_needed on (update_needed.post_id = type.post_id and update_needed.meta_key = 'requires_update' and update_needed.meta_value = '1' )
            GROUP by users.ID",
        ARRAY_A);

        $last_assigned = $wpdb->get_results( "
            SELECT users.ID,
                MAX(date_assigned.hist_time) as last_date_assigned
            from $wpdb->users as users
            INNER JOIN $wpdb->usermeta as um on ( um.user_id = users.ID AND um.meta_key = 'wp_capabilities' AND um.meta_value LIKE '%multiplier%' )
            INNER JOIN $wpdb->postmeta as pm on (pm.meta_key = 'assigned_to' and pm.meta_value = CONCAT( 'user-', users.ID ) )
            INNER JOIN $wpdb->dt_activity_log as date_assigned on ( date_assigned.meta_key = 'overall_status' and date_assigned.object_type = 'contacts' AND date_assigned.object_id = pm.post_id AND date_assigned.meta_value = 'assigned' )
            GROUP by users.ID",
        ARRAY_A);

        $baptized = $wpdb->get_results( "
            SELECT users.ID, 
                users.display_name, 
                count(b.p2p_id) as number_baptized
            from $wpdb->users as users
            LEFT JOIN $wpdb->p2p as b on (b.p2p_type = 'baptizer_to_baptized' AND b.p2p_to = (
                SELECT user_pm.post_id 
                FROM $wpdb->postmeta as user_pm 
                WHERE user_pm.meta_key = 'corresponds_to_user' 
                AND user_pm.meta_value = users.ID 
                LIMIT 1
            ))
            GROUP by users.ID",
        ARRAY_A);

        $times = $wpdb->get_results( "
            SELECT AVG(time) as average_time_to_attempt, users.ID
            from (
                SELECT contacts.ID, 
                    MIN(date_assigned.hist_time) as date_assigned, 
                    MIN(date_attempted.hist_time) as date_attempted, 
                    MIN(date_attempted.hist_time) - MIN(date_assigned.hist_time) as time,
                    pm.meta_value as user 
                from $wpdb->posts as contacts
                INNER JOIN $wpdb->postmeta as pm on ( contacts.ID = pm.post_id AND pm.meta_key = 'assigned_to' )
                INNER JOIN $wpdb->dt_activity_log as date_attempted on ( date_attempted.meta_key = 'seeker_path' and date_attempted.object_type = 'contacts' AND date_attempted.object_id = contacts.ID AND date_attempted.meta_value != 'none' )
                INNER JOIN $wpdb->dt_activity_log as date_assigned on ( date_assigned.meta_key = 'overall_status' and date_assigned.object_type = 'contacts' AND date_assigned.object_id = contacts.ID AND date_assigned.meta_value = 'assigned' )
                WHERE date_attempted.hist_time > date_assigned.hist_time
                GROUP by contacts.ID
            ) as times
            LEFT JOIN $wpdb->users as users on ( times.user = CONCAT( 'user-', users.ID ) ) 
            GROUP by users.ID
            ",
        ARRAY_A);


        $coalition_time_to_attempt = 0;
        $number_of_members_with_times = 0;
        foreach ( $workers as $worker_i => $worker_value ){
            foreach ( $baptized as $b ){
                if ( $worker_value["ID"] == $b["ID"] ){
                    $workers[$worker_i]["number_baptized"] = $b["number_baptized"];
                }
            }
            foreach ( $times as $time ){
                if ( $worker_value["ID"] == $time["ID"] ){
                    $workers[$worker_i]["avg_hours_to_contact_attempt"] = round( (int) $time["average_time_to_attempt"] / 60 / 60, 0 );
                    $coalition_time_to_attempt += (int) $time["average_time_to_attempt"];
                    $number_of_members_with_times++;
                }
            }
            foreach ( $last_assigned as $last ){
                if ( $worker_value["ID"] == $last["ID"] ){

                    $workers[$worker_i]["last_date_assigned"] = date( 'Y-m-d', $last["last_date_assigned"] );
                }
            }
        }
        $coalition_time_to_attempt = round( $coalition_time_to_attempt / $number_of_members_with_times / 60 / 60, 0 );

        $return = [
            "data" => $workers,
            "coalition_time_to_attempt" => $coalition_time_to_attempt,
            "timestamp" => current_time( "mysql" ),
        ];

        set_transient( 'get_workers_data', maybe_serialize( $return ), 60 * 60 * 24 );

        return $return;
    }
}


