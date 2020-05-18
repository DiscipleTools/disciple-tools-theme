<?php


abstract class DT_Metrics_Chart_Base
{

    public $base_slug = 'example'; //lowercase
    public $base_title = "Example Metrics";

    //child
    public $title = '';
    public $slug = '';
    public $js_object_name = ''; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = ''; // should be full file name plus extension
    public $permissions = [];
    /**
     * Disciple_Tools_Counter constructor.
     */
    public function __construct() {
        $this->base_slug = str_replace( ' ', '', trim( strtolower( $this->base_slug ) ) );
        $url_path = dt_get_url_path();

        if ( strpos( $url_path, 'metrics' ) === 0 ) {
            if ( !$this->has_permission() ){
                return;
            }
            add_filter( 'dt_metrics_menu', [ $this, 'base_menu' ], 99 ); //load menu links

            if ( strpos( $url_path, "metrics/$this->base_slug/$this->slug" ) === 0 ) {
                add_filter( 'dt_templates_for_urls', [ $this, 'base_add_url' ] ); // add custom URLs
                add_action( 'wp_enqueue_scripts', [ $this, 'base_scripts' ], 99 );
            }
        }
    }

    /**
     * Build the menu out on the metrics page
     * The menu item will be added based on the base_slug (menu group) and the base_title from the template extending this class
     *
     * @param $content
     *
     * @return mixed|string
     */
    public function base_menu( $content ) {
        $line = '<li><a href="'. site_url( '/metrics/'.$this->base_slug.'/' . $this->slug ) . '">' . $this->title . '</a></li>';

        $ref = '<ul class="menu vertical nested" id="' . $this->base_slug . '-menu">';
        $pos = strpos( $content, $ref );
        if ( $pos === false ){
            $content .= '
            <li><a href="'. site_url( '/metrics/'. $this->base_slug .'/'. $this->slug ) .'">'.$this->base_title.'</a>
                <ul class="menu vertical nested" id="' . $this->base_slug . '-menu">'
                        . $line . '
            </ul></li>';
        } else {
            $content = substr_replace( $content, $ref . $line, $pos, strlen( $ref ) );
        }

        return $content;
    }


    /**
     *  This hook add a page for the metric charts
     *
     * @param $template_for_url
     *
     * @return mixed
     */
    public function base_add_url( $template_for_url ) {
        $template_for_url["metrics/$this->base_slug/$this->slug"] = 'template-metrics.php';
        return $template_for_url;
    }

    public function base_scripts() {
        wp_localize_script(
            'dt_'.$this->base_slug.'_script', 'wpMetricsBase', [
                'slug' => $this->base_slug,
                'root' => esc_url_raw( rest_url() ),
                'plugin_uri' => plugin_dir_url( __DIR__ ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id()
            ]
        );
    }

    public function has_permission(){
        $permissions = $this->permissions;
        $pass = count( $permissions ) === 0;
        foreach ( $this->permissions as $permission ){
            if ( current_user_can( $permission ) ){
                $pass = true;
            }
        }
        return $pass;
    }
}
