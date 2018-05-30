<?php

Disciple_Tools_Metrics_Project::instance();
class Disciple_Tools_Metrics_Project extends Disciple_Tools_Metrics_Hooks_Base
{
    private static $_instance = null;
    public static function instance()
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        $url_path = dt_get_url_path();

        if ( 'metrics' === substr( $url_path, '0', 7 ) ) {

            add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ] ); // add custom URL
            add_filter( 'dt_metrics_menu', [ $this, 'add_menu' ], 50 );

            if ( 'metrics/project' === $url_path ) {
                add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
            }
        }
    }

    public function add_url( $template_for_url ) {
        $template_for_url['metrics/project'] = 'template-metrics.php';
        return $template_for_url;
    }

    public function add_menu( $content ) {
        $content .= '
            <li><a href="" >' .  esc_html__( 'Project', 'disciple_tools' ) . '</a>
                <ul class="menu vertical nested" id="project-menu">
                    <li><a href="'. site_url( '/metrics/project/' ) .'#project_overview" onclick="project_overview()">'. esc_html__( 'Overview' ) .'</a></li>
                    <!-- <li><a href="'. site_url( '/metrics/project/' ) .'#project_timeline" onclick="project_timeline()">'. esc_html__( 'Timeline' ) .'</a></li> -->
                    <li><a href="'. site_url( '/metrics/project/' ) .'#project_critical_path" onclick="project_critical_path()">'. esc_html__( 'Critical Path' ) .'</a></li>
                </ul>
            </li>
            ';
        return $content;
    }

    public function scripts() {
        wp_enqueue_script( 'dt_metrics_project_script', get_stylesheet_directory_uri() . '/dt-metrics/metrics-project.js', [
            'jquery',
            'jquery-ui-core',
        ], filemtime( get_theme_file_path() . '/dt-metrics/metrics-project.js' ), true );

        wp_localize_script(
            'dt_metrics_project_script', 'dtMetricsProject', [
                'root' => esc_url_raw( rest_url() ),
                'theme_uri' => get_stylesheet_directory_uri(),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'map_key' => dt_get_option( 'map_key' ),
                'data' => $this->data(),
            ]
        );
    }

    public function data() {

        /**
         * Apply Filters before final enqueue. This provides opportunity for complete override or modification of chart.
         */

        return [
            'translations' => [
                'title_overview' => __( 'Project Overview' ),
                'title_timeline' => __( 'Timeline Activity' ),
                'title_critical_path' => __( 'Critical Path' ),
                'title_outreach' => __( 'Outreach' ),
                'title_follow_up' => __( 'Follow Up' ),
                'title_training' => __( 'Training' ),
                'title_contacts' => __( 'Project Contacts' ),
                'title_groups' => __( 'Project Groups' ),
                'title_multiplication' => __( 'Multiplication' ),
                'title_total_contacts' => __( 'Total Contacts' ),
                'title_waiting_on_accept' => __( 'Waiting on Accept' ),
                'title_waiting_on_update' => __( 'Waiting on Update' ),
                'title_project_groups' => __( 'Project Groups' ),
                'title_total_groups' => __( 'Total Groups' ),
                'title_needs_training' => __( 'Needs Training' ),
                'title_generations' => __( 'Generations' ),
                'title_group_types' => __( 'Groups Types' ),
                'label_number_of_contacts' => strtolower( __( 'number of contacts' ) ),
                'label_follow_up_progress' => __( 'Follow-Up Progress' ),
                'label_group_needs_training' => __( 'Groups Needing Training Attention' ),
                'label_groups' => strtolower( __( 'groups' ) ),
                'label_generations' => strtolower( __( 'generations' ) ),
                'label_groups_by_type' => strtolower( __( 'groups by type' ) ),
                'label_streams' => strtolower( __( 'streams' ) ),
                'label_stats_as_of' => strtolower( __( 'stats as of' ) ),
            ],
            'hero_stats' => self::chart_project_hero_stats(),
            'critical_path' =>  self::chart_critical_path( 'project' ),
            'contacts_progress' => self::chart_contacts_progress( 'project' ),
            'group_types' => self::chart_group_types( 'project' ),
            'group_health' => self::chart_group_health( 'project' ),
            'group_generations' => self::chart_group_generations( 'project' ),
            'timeline' => self::chart_timeline(),
            'streams' => self::chart_streams(),
        ];
    }
}
