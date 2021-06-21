<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

class DT_Metrics_Daily_Activity extends DT_Metrics_Chart_Base {

    //slug and title of the top menu folder
    public $base_slug = 'contacts'; // lowercase
    public $base_title;
    public $title;
    public $slug = 'daily-activity'; // lowercase
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/contacts/daily-activity.js'; // should be full file name plus extension
    public $permissions = [ 'dt_all_access_contacts', 'view_project_metrics' ];

    public function __construct() {
        parent::__construct();
        if ( ! $this->has_permission() ) {
            return;
        }
        $this->title      = __( 'Activity by Day', 'disciple_tools' );
        $this->base_title = __( 'Contacts', 'disciple_tools' );

        $url_path = dt_get_url_path();
        if ( "metrics/$this->base_slug/$this->slug" === $url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
        }
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    /**
     * Load scripts for the plugin
     */
    public function scripts() {

        wp_register_script( 'datepicker', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js', false );
        wp_enqueue_style( 'datepicker-css', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css', array() );

        wp_register_script( 'amcharts-core', 'https://cdn.amcharts.com/lib/4/core.js', false, '4' );
        wp_register_script( 'amcharts-charts', 'https://cdn.amcharts.com/lib/4/charts.js', false, '4' );
        wp_register_script( 'amcharts-plugins-timeline', 'https://cdn.amcharts.com/lib/4/plugins/timeline.js', false, '4' );
        wp_register_script( 'amcharts-plugins-bullets', 'https://cdn.amcharts.com/lib/4/plugins/bullets.js', false, '4' );
        wp_register_script( 'amcharts-themes-animated', 'https://cdn.amcharts.com/lib/4/themes/animated.js', false, '4' );

        wp_enqueue_script( 'dt_' . $this->slug . '_script',
            get_template_directory_uri() . $this->js_file_name,
            [
                'moment',
                'jquery',
                'jquery-ui-core',
                'datepicker',
                'amcharts-core',
                'amcharts-charts',
                'amcharts-plugins-timeline',
                'amcharts-plugins-bullets',
                'amcharts-themes-animated'
            ],
            filemtime( get_theme_file_path() . $this->js_file_name )
        );

        // Localize script with array data
        wp_localize_script(
            'dt_' . $this->slug . '_script', $this->js_object_name, [
                'rest_endpoints_base' => esc_url_raw( rest_url() ) . "dt-metrics/$this->base_slug/$this->slug",
                "data"                => [
                    'activities' => $this->daily_activity( 'week' )
                ],
                'translations'        => [
                    'activities'           => __( "Activity by Day", 'disciple_tools' ),
                    'filter_to_date_range' => __( "Filter to date range", 'disciple_tools' ),
                ]
            ]
        );
    }

    public function add_api_routes() {
        $namespace = "dt-metrics/$this->base_slug/$this->slug";
        register_rest_route(
            $namespace, '/daily-activity/', [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'daily_activity_endpoint' ],
                    'permission_callback' => '__return_true',
                ],
            ]
        );
    }

    public function daily_activity_endpoint( WP_REST_Request $request ) {
        if ( ! $this->has_permission() ) {
            return new WP_Error( "daily-activity", "Missing Permissions", [ 'status' => 400 ] );
        }
        $params = $request->get_params();
        if ( isset( $params["date_range"] ) ) {
            $result = $this->daily_activity( $params["date_range"] );
            if ( is_wp_error( $result ) ) {
                return $result;
            } else {
                return new WP_REST_Response( $result );
            }
        } else {
            return new WP_Error( "daily-activity", "Missing required parameters", [ 'status' => 400 ] );
        }
    }


    public function daily_activity( $date_range ): array {

        // First, identify date range boundaries.
        $start = null;
        $end   = gmdate( 'Y-m-d' );

        switch ( $date_range ) {
            case 'week':
                $start = gmdate( 'Y-m-d', strtotime( '-1 week' ) );
                break;
            case 'fortnight':
                $start = gmdate( 'Y-m-d', strtotime( '-1 fortnight' ) );
                break;
            case 'month':
                $start = gmdate( 'Y-m-d', strtotime( '-1 month' ) );
                break;
            case '3-months':
                $start = gmdate( 'Y-m-d', strtotime( '-3 months' ) );
                break;
        }

        if ( ! empty( $start ) ) {

            $days = null;

            try {

                // Convert daily intervals into array elements.
                $days = new DatePeriod(
                    new DateTime( $start ),
                    new DateInterval( 'P1D' ),
                    new DateTime( $end )
                );

            } catch ( Exception $e ) {
                $days = null;
            }

            if ( ! empty( $days ) ) {

                // Cycle through each day with range and obtain required metric counts
                $daily_activities = [];

                // Iterate each day, sourcing relevant counts.
                foreach ( $days as $day ) {

                    $current_day_format = $day->format( 'Y-m-d' );
                    $next_day_format    = gmdate( 'Y-m-d', strtotime( '+1 day', $day->getTimestamp() ) );

                    $current_day_ts = strtotime( $current_day_format );
                    $next_day_ts    = strtotime( $next_day_format );

                    $new_contacts        = Disciple_Tools_Counter_Contacts::get_contacts_count( 'new_contacts', $current_day_ts, $next_day_ts );
                    $first_meetings      = Disciple_Tools_Counter_Contacts::get_contacts_count( 'first_meetings', $current_day_ts, $next_day_ts );
                    $ongoing_meetings    = Disciple_Tools_Counter_Contacts::get_contacts_count( 'ongoing_meetings', $current_day_ts, $next_day_ts );
                    $seeker_path_updates = Disciple_Tools_Counter_Contacts::seeker_path_activity( $current_day_ts, $next_day_ts );

                    $active_groups   = Disciple_Tools_Counter_Groups::get_groups_count( 'active_groups', $current_day_ts, $next_day_ts );
                    $active_churches = Disciple_Tools_Counter_Groups::get_groups_count( 'active_churches', $current_day_ts, $next_day_ts );

                    $baptisms = Disciple_Tools_Counter_Baptism::get_baptism_generations( $current_day_ts, $next_day_ts );

                    $health                   = $this->health_metrics( $current_day_format, $next_day_format );
                    $new_other_post_creations = $this->new_other_post_creations( $current_day_format, $next_day_format );

                    // Package counts
                    $daily_activities[ $current_day_format ] = [
                        'new_contacts'             => $new_contacts,
                        'first_meetings'           => $first_meetings,
                        'ongoing_meetings'         => $ongoing_meetings,
                        'active_groups'            => $active_groups,
                        'active_churches'          => $active_churches,
                        'new_other_post_creations' => $new_other_post_creations,
                        'baptisms'                 => ! is_wp_error( $baptisms ) ? array_sum( $baptisms ) : 0,
                        'seeker_path_updates'      => $this->seeker_path_updates_total_count( $seeker_path_updates ),
                        'health'                   => $health
                    ];
                }

                return [
                    'start' => $start,
                    'end'   => $end,
                    'days'  => $daily_activities
                ];
            }
        }

        return [];
    }

    private function seeker_path_updates_total_count( $seeker_path_updates ): int {
        if ( ! empty( $seeker_path_updates ) && is_array( $seeker_path_updates ) ) {
            $total = 0;
            foreach ( $seeker_path_updates as $update ) {
                if ( isset( $update['value'] ) ) {
                    $total += intval( $update['value'] );
                }
            }

            return $total;
        }

        return 0;
    }

    public function health_metrics( $start, $end ): array {
        global $wpdb;

        $group_fields = DT_Posts::get_post_field_settings( "groups" );
        $labels       = [];

        foreach ( $group_fields["health_metrics"]["default"] as $key => $option ) {
            $labels[ $key ] = $option["label"];
        }

        $chart = [];

        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT d.meta_value as health_key,
              count(distinct(a.ID)) as count,
              ( SELECT count(*)
              FROM wp_posts as a
                JOIN wp_postmeta as c
                  ON a.ID=c.post_id
                     AND c.meta_key = 'group_status'
                     AND c.meta_value = 'active'
                JOIN wp_postmeta as d
                  ON a.ID=d.post_id
                     AND d.meta_key = 'group_type'
                     AND ( d.meta_value = 'group' OR d.meta_value = 'church' )
              WHERE a.post_status = 'publish'
                    AND a.post_type = 'groups'
AND a.post_date >= %s
AND a.post_date < %s
              ) as out_of
              FROM wp_posts as a
                JOIN wp_postmeta as c
                  ON a.ID=c.post_id
                     AND c.meta_key = 'group_status'
                     AND c.meta_value = 'active'
                JOIN wp_postmeta as d
                  ON ( a.ID=d.post_id
                    AND d.meta_key = 'health_metrics' )
                JOIN wp_postmeta as e
                  ON a.ID=e.post_id
                     AND e.meta_key = 'group_type'
                     AND ( e.meta_value = 'group' OR e.meta_value = 'church' )
              WHERE a.post_status = 'publish'
                    AND a.post_type = 'groups'
AND a.post_date >= %s
AND a.post_date < %s
              GROUP BY d.meta_value", $start, $end, $start, $end ), ARRAY_A );

        if ( $results ) {
            $out_of = 0;
            if ( isset( $results[0]['out_of'] ) ) {
                $out_of = $results[0]['out_of'];
            }
            foreach ( $labels as $label_key => $label_value ) {
                $row = [
                    "label"      => $label_value,
                    "practicing" => 0,
                    "remaining"  => (int) $out_of
                ];
                foreach ( $results as $result ) {
                    if ( $result['health_key'] === $label_key ) {
                        $row["practicing"] = (int) $result["count"];
                        $row["remaining"]  = intval( $result['out_of'] ) - intval( $result['count'] );
                    }
                }
                $chart[] = $row;
            }
        }

        return $chart;
    }

    public function new_other_post_creations( $start, $end ): int {
        global $wpdb;

        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT COUNT(*) new_other_post_creations_count
FROM wp_posts
WHERE (post_type != 'contacts')
AND (post_date >= %s)
AND (post_date < %s)", $start, $end ), ARRAY_A );

        return ( ! empty( $results ) && isset( $results[0]['new_other_post_creations_count'] ) ) ? intval( $results[0]['new_other_post_creations_count'] ) : 0;
    }
}

new DT_Metrics_Daily_Activity();
