<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.


class DT_Metrics_Mapbox_Groups_Cluster_Map extends DT_Metrics_Chart_Base
{

    //slug and title of the top menu folder
    public $base_slug = 'groups'; // lowercase
    public $base_title;

    public $title;
    public $slug = 'mapbox_cluster_map'; // lowercase
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/common/cluster-map.js'; // should be full file name plus extension
    public $permissions = [ 'view_any_contacts', 'view_project_metrics' ];
    public $namespace = null;

    public function __construct() {
        if ( ! DT_Mapbox_API::get_key() ) {
            return;
        }
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }
        $this->title = __( 'Cluster Map', 'disciple_tools' );
        $this->base_title = __( 'Groups', 'disciple_tools' );

        $url_path = dt_get_url_path();
        if ( "metrics/$this->base_slug/$this->slug" === $url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
        }
    }

    public function scripts() {
        DT_Mapbox_API::load_mapbox_header_scripts();
        // Map starter Script
        wp_enqueue_script( 'dt_mapbox_script',
            get_template_directory_uri() .  $this->js_file_name,
            [
                'jquery'
            ],
            filemtime( get_theme_file_path() .  $this->js_file_name ),
            true
        );
        $group_fields = Disciple_Tools_Groups_Post_Type::instance()->get_group_field_defaults();
        wp_localize_script(
            'dt_mapbox_script', 'dt_mapbox_metrics', [
                'map_key' => DT_Mapbox_API::get_key(),
                'translations' => [
                    'title' => __( "Mapping", "disciple_tools" ),
                    'refresh_data' => __( "Refresh Cached Data", "disciple_tools" ),
                    'population' => __( "Population", "disciple_tools" ),
                    'name' => __( "Name", "disciple_tools" ),
                ],
                'settings' => [
                    'rest_url' => 'cluster_geojson',
                    'rest_base_url' => 'dt-metrics/mapbox/',
                    'menu_slug' => $this->base_slug,
                    'post_type' => 'groups',
                    'title' => $this->title,
                    'status_list' => $group_fields['group_status']['default'] ?? []
                ]
            ]
        );
    }

}
new DT_Metrics_Mapbox_Groups_Cluster_Map();
