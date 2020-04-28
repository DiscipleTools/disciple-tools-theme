<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.


class DT_Metrics_Contacts_Overview extends DT_Metrics_Chart_Base
{
    //slug and title of the top menu folder
    public $base_slug = 'contacts'; // lowercase
    public $base_title = 'Contacts';

    public $title = 'Overview';
    public $slug = 'overview'; // lowercase
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/contacts/overview.js'; // should be full file name plus extension
    public $permissions = [ 'view_any_contacts', 'view_project_metrics' ];
    public $namespace = null;

    public function __construct() {
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }
        $this->namespace = "dt-metrics/$this->base_slug/$this->slug";
        $url_path = dt_get_url_path();
        if ( "metrics/$this->base_slug/$this->slug" === $url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
        }
//        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

//    public function add_api_routes() {
//        $version = '1';
//        $namespace = 'dt/v' . $version;
//        register_rest_route(
//            $namespace, '/metrics/contacts/overview', [
//                [
//                    'methods'  => WP_REST_Server::CREATABLE,
//                    'callback' => [ $this, 'tree' ],
//                ],
//            ]
//        );
//
//    }

    public function scripts() {
        wp_register_script( 'amcharts-core', 'https://www.amcharts.com/lib/4/core.js', false, '4' );
        wp_register_script( 'amcharts-charts', 'https://www.amcharts.com/lib/4/charts.js', false, '4' );
        wp_register_script( 'amcharts-animated', 'https://www.amcharts.com/lib/4/themes/animated.js', [ 'amcharts-core' ], '4' );
        wp_enqueue_script( 'dt_metrics_project_script', get_template_directory_uri() . $this->js_file_name, [
            'jquery',
            'jquery-ui-core',
            'amcharts-core',
            'amcharts-charts',
            'amcharts-animated',
        ], filemtime( get_theme_file_path() . $this->js_file_name ), true );

        wp_localize_script(
            'dt_metrics_project_script', 'dtMetricsProject', [
                'root' => esc_url_raw( rest_url() ),
                'theme_uri' => get_template_directory_uri(),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'data' => $this->data(),
            ]
        );
    }

    public function data() {
        $contact_fields = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings();
        return [
            'translations' => [
                'title_contact_overview' => __( 'Contacts Overview', 'disciple_tools' ),
                'title_overview' => __( 'Project Overview', 'disciple_tools' ),
                'title_contacts' => __( 'Contacts', 'disciple_tools' ),
                'title_groups' => __( 'Groups', 'disciple_tools' ),
                'title_all_contacts' => __( 'All Contacts', 'disciple_tools' ),
                'title_active_contacts' => __( 'Active Contacts', 'disciple_tools' ),
                'title_waiting_on_accept' => __( 'Waiting on Accept', 'disciple_tools' ),
                'title_waiting_on_update' => __( 'Waiting on Update', 'disciple_tools' ),
                'title_total_groups' => __( 'Total Groups', 'disciple_tools' ),
                'title_generations' => __( 'Group and Church Generations', 'disciple_tools' ),
                'title_group_types' => __( 'Group Types', 'disciple_tools' ),
                'title_teams' => __( 'Teams', 'disciple_tools' ),
                'title_status_chart' => $contact_fields["overall_status"]["name"],
                'label_follow_up_progress' => __( 'Follow-up of all active contacts', 'disciple_tools' ),
                'label_group_needs_training' => __( 'Active Group Health Metrics', 'disciple_tools' ),
                'label_groups' => strtolower( __( 'groups', 'disciple_tools' ) ),
                'label_generations' => strtolower( __( 'generations', 'disciple_tools' ) ),
                'label_generation' => __( 'Generation', 'disciple_tools' ),
                'label_group_types' => __( 'Group Types', 'disciple_tools' ),
                'label_pre_group' => __( 'Pre-Group', 'disciple_tools' ),
                'label_group' => __( 'Group', 'disciple_tools' ),
                'label_church' => __( 'Church', 'disciple_tools' ),
            ],
            'preferences' => $this->preferences(),
            'hero_stats' => Disciple_Tools_Metrics_Hooks_Base::chart_project_hero_stats(),
            'contacts_progress' => Disciple_Tools_Metrics_Hooks_Base::chart_contacts_progress( 'project' ),
            'contact_statuses' => Disciple_Tools_Counter_Contacts::get_contact_statuses()
        ];
    }

    public function preferences() {
        $data = [];

        /* Add group preferences*/
        $group_preferences = dt_get_option( 'group_preferences' );
        $data['groups'] = [
            'church_metrics' => $group_preferences['church_metrics'] ?? false,
            'four_fields' => $group_preferences['four_fields'] ?? false,
        ];

        /* Add other preferences. Please, categorize by section, i.e. contacts, groups, etc */

        return $data;
    }

}
new DT_Metrics_Contacts_Overview();
