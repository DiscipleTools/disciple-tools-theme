<?php

class Disciple_Tools_Metrics_Personal_Activity_Log extends DT_Metrics_Chart_Base
{

    //slug and title of the top menu folder
    public $base_slug = 'personal'; // lowercase
    public $slug = 'activity-log'; // lowercase
    public $base_title;

    public $title;
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/personal/activity-log.js'; // should be full file name plus extension
    public $permissions = array();
    public $namespace = null;

    public function __construct() {
        if ( !$this->has_permission() ){
            return;
        }
        parent::__construct();
        $this->title = __( 'Activity Log', 'disciple_tools' );
        $this->base_title = __( 'Personal', 'disciple_tools' );

        $url_path = dt_get_url_path( true );
        if ( "metrics/$this->base_slug/$this->slug" === $url_path || 'metrics' === $url_path ) {
            add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ), 10 );
        }
    }

    public function scripts() {
        wp_enqueue_script( 'dtActivityLogs', get_template_directory_uri() . '/dt-assets/js/activity-log.js', array(
            'jquery',
            'lodash',
        ), filemtime( get_theme_file_path() .  '/dt-assets/js/activity-log.js' ), true );

        wp_enqueue_script( 'dt_metrics_activity_script', get_template_directory_uri() . $this->js_file_name, array(
            'jquery',
            'jquery-ui-core',
            'lodash',
        ), filemtime( get_theme_file_path() .  $this->js_file_name ), true );

        wp_localize_script(
            'dt_metrics_activity_script', 'dtMetricsActivity', array(
                'root' => esc_url_raw( rest_url() ),
                'theme_uri' => get_template_directory_uri(),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'data' => array(
                    'translations' => array(
                        'title' => __( 'Activity Log', 'disciple_tools' ),
                        'more' => __( 'More', 'disciple_tools' ),
                        'less' => __( 'Less', 'disciple_tools' ),
                    ),
                    'user_id' => get_current_user_id(),
                ),
            )
        );
    }
}
new Disciple_Tools_Metrics_Personal_Activity_Log();
