<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

class DT_Metrics_Site_Links extends DT_Metrics_Chart_Base {

    //slug and title of the top menu folder
    public $base_slug = 'combined'; // lowercase
    public $base_title;
    public $title;
    public $slug = 'site-links'; // lowercase
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/combined/site-links.js'; // should be full file name plus extension
    public $permissions = [ 'dt_all_access_contacts', 'view_project_metrics' ];

    public function __construct() {
        parent::__construct();
        if ( ! $this->has_permission() ) {
            return;
        }
        $this->title      = __( 'Transferred Contacts', 'disciple_tools' );
        $this->base_title = __( 'Project', 'disciple_tools' );

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
                'amcharts-themes-animated'
            ],
            filemtime( get_theme_file_path() . $this->js_file_name )
        );

        // Localize script with array data
        wp_localize_script(
            'dt_' . $this->slug . '_script', $this->js_object_name, [
                'rest_endpoints_base' => esc_url_raw( rest_url() ) . "dt-metrics/$this->base_slug/$this->slug",
                "data"                => [
                    'sites' => Site_Link_System::get_list_of_sites_by_type( [
                        'contact_sharing',
                        'contact_sending'
                    ] )
                ],
                'translations'        => [
                    'headings' => [
                        'header'                     => __( "Transferred Contacts", 'disciple_tools' ),
                        'sub_header'                 => __( "Filter by date range and available site links", 'disciple_tools' ),
                        'date_range_header'          => __( "Date Ranges", 'disciple_tools' ),
                        'date_range_none_header'     => __( "None Set", 'disciple_tools' ),
                        'site_links_header'          => __( "Site Links", 'disciple_tools' ),
                        'site_links_none_header'     => __( "None Set", 'disciple_tools' ),
                        'totals_header'              => _x( 'Contacts transferred during date range', 'Contacts transferred during date range', 'disciple_tools' ),
                        'status_created_header'      => sprintf( _x( '%s of contacts created in date range', 'Current statuses of contacts created in date range', 'disciple_tools' ), 'Current statuses' ),
                        'status_changes_header'      => sprintf( _x( '%s changes during date range', 'Status changes during date range', 'disciple_tools' ), 'Status' ),
                        'seeker_path_created_header' => sprintf( _x( '%s of contacts created in date range', 'Seeker Paths of contacts created in date range', 'disciple_tools' ), 'Seeker Paths' ),
                        'seeker_path_changes_header' => sprintf( _x( '%s changes during date range', 'Seeker Path changes during date range', 'disciple_tools' ), 'Seeker Path' ),
                        'milestones_created_header'  => sprintf( _x( '%s of contacts created in date range', 'Faith milestones of contacts created in date range', 'disciple_tools' ), 'Faith milestones' ),
                        'milestones_changes_header'  => sprintf( _x( '%s changes during date range', 'Faith milestone changes during date range', 'disciple_tools' ), 'Faith milestone' )
                    ],
                    'general'  => [
                        'no_data_msg' => __( "No Data Available", 'disciple_tools' )
                    ]
                ]
            ]
        );
    }

    public function add_api_routes() {
        $namespace = "dt-metrics/$this->base_slug/$this->slug";
        register_rest_route(
            $namespace, '/site-links/', [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'site_links_endpoint' ],
                    'permission_callback' => '__return_true',
                ],
            ]
        );
    }

    public function site_links_endpoint( WP_REST_Request $request ) {
        if ( ! $this->has_permission() ) {
            return new WP_Error( "site-links", "Missing Permissions", [ 'status' => 400 ] );
        }
        $params = $request->get_params();
        if ( isset( $params["site_id"], $params["start"], $params["end"] ) ) {
            $result = $this->site_link_metrics( $params["site_id"], $params["start"], $params["end"] );
            if ( is_wp_error( $result ) ) {
                return $result;
            } else {
                return new WP_REST_Response( $result );
            }
        } else {
            return new WP_Error( "site-links", "Missing required parameters", [ 'status' => 400 ] );
        }
    }

    public function site_link_metrics( $site_id, $start, $end ): array {

        // Fetch selected remote site connection details
        $site = Site_Link_System::get_site_connection_vars( $site_id );
        if ( ! is_wp_error( $site ) && ! empty( $start ) && ! empty( $end ) ) {

            // Prepare records metrics request payload
            $args = [
                'method'  => 'POST',
                'timeout' => 20,
                'body'    => [
                    'start' => $start,
                    'end'   => $end
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . $site['transfer_token']
                ]
            ];

            // Request records metrics from remote site
            $result = wp_remote_post( 'https://' . $site['url'] . '/wp-json/dt-posts/v2/contacts/transfer/metrics', $args );
            if ( ! is_wp_error( $result ) ) {
                $remote_metrics = json_decode( $result['body'], true );
                if ( ! empty( $remote_metrics ) && ! is_wp_error( $remote_metrics ) ) {
                    return $remote_metrics;
                }
            }
        }

        return [];
    }
}

new DT_Metrics_Site_Links();
