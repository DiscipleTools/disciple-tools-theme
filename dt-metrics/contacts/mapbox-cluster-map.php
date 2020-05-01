<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.


class DT_Metrics_Mapbox_Cluster_Map extends DT_Metrics_Chart_Base
{

    //slug and title of the top menu folder
    public $base_slug = 'contacts'; // lowercase
    public $base_title = "Contacts";

    public $title = 'Cluster Map';
    public $slug = 'mapbox_cluster_map'; // lowercase
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = 'cluster-map.js'; // should be full file name plus extension
    public $permissions = [ 'view_any_contacts', 'view_project_metrics' ];
    public $namespace = null;

    public function __construct() {
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }
        $this->namespace = "dt-metrics/$this->base_slug/$this->slug";
        $url_path = dt_get_url_path();
        // only load scripts if exact url
        if ( "metrics/$this->base_slug/$this->slug" === $url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
        }
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    public function scripts() {
        DT_Mapbox_API::load_mapbox_header_scripts();
        // Map starter Script
        wp_enqueue_script( 'dt_mapbox_script',
            get_template_directory_uri() . '/dt-metrics/common/' . $this->js_file_name,
            [
                'jquery'
            ],
            filemtime( get_theme_file_path() . '/dt-metrics/common/' . $this->js_file_name ),
            true
        );
        $contact_fields = Disciple_Tools_Contact_Post_Type::instance()->get_contact_field_defaults();
        $group_fields = Disciple_Tools_Groups_Post_Type::instance()->get_group_field_defaults();
        wp_localize_script(
            'dt_mapbox_script', 'dt_mapbox_metrics', [
                'rest_endpoints_base' => esc_url_raw( rest_url() ) . "dt-metrics/$this->base_slug/",
                'base_slug' => $this->base_slug,
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_id' => get_current_user_id(),
                'map_key' => DT_Mapbox_API::get_key(),
                "spinner_url" => get_stylesheet_directory_uri() . '/spinner.svg',
                "theme_uri" => trailingslashit( get_stylesheet_directory_uri() ),
                'translations' => $this->translations(),
                'contact_settings' => [
                    'post_type' => 'contacts',
                    'title' => __( 'Contacts', "disciple_tools" ),
                    'status_list' => $contact_fields['overall_status']['default'] ?? []
                ],
                'group_settings' => [
                    'post_type' => 'groups',
                    'title' => __( 'Groups', "disciple_tools" ),
                    'status_list' => $group_fields['group_status']['default'] ?? []
                ]
            ]
        );
    }

    public function translations() {
        $translations = [];
        $translations['title'] = __( "Mapping", "disciple_tools" );
        $translations['refresh_data'] = __( "Refresh Cached Data", "disciple_tools" );
        $translations['population'] = __( "Population", "disciple_tools" );
        $translations['name'] = __( "Name", "disciple_tools" );
        return $translations;
    }

}
new DT_Metrics_Mapbox_Cluster_Map();
