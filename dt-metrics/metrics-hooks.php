<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class DT_Zume_Hooks
 */
class Disciple_Tools_Metrics_Hooks
{

    private static $_instance = null;

    public static function instance()
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    /**
     * Build hook classes
     */
    public function __construct()
    {
        new Disciple_Tools_Metrics_Project();
    }
}


class Disciple_Tools_Metrics_Project
{
    /**
     * Add New URL Endpoint
     *
     * @see functions.php:213
     * @param $template_for_url
     *
     * @return mixed
     */
    public function add_url( $template_for_url ) {
        $template_for_url['metrics/project'] = 'template-metrics.php';
        return $template_for_url;
    }

    public function menu( $content ) {
        $content .= '<li><a href="'. site_url( '/metrics/project/' ) .'#dt_overview" onclick="show_zume_project()">' .  esc_html__( 'Project', 'disciple_tools' ) . '</a>
            <ul class="menu vertical nested is-active">
              <li><a href="'. site_url( '/metrics/project/' ) .'#dt_overview" onclick="show_zume_project()">' .  esc_html__( 'Overview', 'disciple_tools' ) . '</a></li>
              <li><a href="'. site_url( '/metrics/project/' ) .'#dt_contacts" onclick="show_zume_locations()">' .  esc_html__( 'Contacts', 'disciple_tools' ) . '</a></li>
            </ul>
          </li>';
        return $content;
    }

    /**
     * Load scripts for the plugin
     */
    public function scripts() {
        $url_path = trim( parse_url( add_query_arg( array() ), PHP_URL_PATH ), '/' );

        if ( 'metrics/project' === $url_path ) {
            wp_enqueue_script( 'dt_project_metrics_script', get_stylesheet_directory_uri() . '/dt-metrics/metrics.js', [
                'jquery',
                'jquery-ui-core',
            ], filemtime( get_theme_file_path() . '/dt-metrics/metrics.js' ), true );

            wp_localize_script(
                'dt_project_metrics_script', 'dtMetrics', [
                    'root' => esc_url_raw( rest_url() ),
                    'plugin_uri' => DT_Zume::get_instance()->dir_uri,
                    'nonce' => wp_create_nonce( 'wp_rest' ),
                    'current_user_login' => wp_get_current_user()->user_login,
                    'current_user_id' => get_current_user_id(),
                    'map_key' => dt_get_option( 'map_key' ),
                    'zume_stats' => DT_Zume_Core::get_project_stats(),
                    'translations' => [
                        "zume_project" => __( "Zúme Overview", "dt_zume" ),
                        "zume_groups" => __( "Zúme Groups", "dt_zume" ),
                        "zume_people" => __( "Zúme People", "dt_zume" ),
                        "zume_locations" => __( "Zúme Locations", "dt_zume" ),
                    ]
                ]
            );
        }
    }

    public function __construct() {
        add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ] );
        add_filter( 'dt_metrics_menu', [ $this, 'menu' ], 1 );
        add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 999 );
    }
}
