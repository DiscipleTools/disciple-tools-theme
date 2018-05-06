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
        new Disciple_Tools_Metrics_Personal();
//        new Disciple_Tools_Metrics_Project(); @todo add back

        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_google' ], 100 );
    }

    // Enqueue maps and charts for standard metrics
    public function enqueue_google() {
        $url_path = trim( parse_url( add_query_arg( array() ), PHP_URL_PATH ), '/' );
        if ( 'metrics' === $url_path ) {
            wp_enqueue_script( 'google-charts', 'https://www.gstatic.com/charts/loader.js', [], false );
            wp_enqueue_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . dt_get_option( 'map_key' ), array(), null, true );
        }
    }
}

abstract class Disciple_Tools_Metrics_Hooks_Base
{
    public function __construct() {}

}

class Disciple_Tools_Metrics_Personal extends Disciple_Tools_Metrics_Hooks_Base
{
    public function __construct() {
        add_filter( 'dt_metrics_top_menu', [ $this, 'add_overview_menu' ], 10 );
        add_filter( 'dt_metrics_menu_my_contacts', [ $this, 'add_contacts_menu' ], 10 );
        add_filter( 'dt_metrics_menu_my_groups', [ $this, 'add_groups_menu' ], 10 );
        add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );

        parent::__construct();
    }

    public function add_overview_menu( $content ) {
        $content .= '
            <li><a href="'. site_url( '/metrics/' ) .'#overview" onclick="overview()">' .  esc_html__( 'Overview', 'disciple_tools' ) . '</a></li>
            ';
        return $content;
    }

    public function add_contacts_menu( $content ) {
        $content .= '
            <li><a href="'. site_url( '/metrics/' ) .'#my_contacts_progress" onclick="my_contacts_progress()">' .  esc_html__( 'Progress', 'disciple_tools' ) . '</a></li>
            ';
        return $content;
    }

    public function add_groups_menu( $content ) {
        $content .= '
            <li><a href="'. site_url( '/metrics/' ) .'#my_groups_progress" onclick="my_groups_progress()">' .  esc_html__( 'Progress', 'disciple_tools' ) . '</a></li>
            ';
        return $content;
    }

    public function scripts() {
        $url_path = trim( parse_url( add_query_arg( array() ), PHP_URL_PATH ), '/' );

        if ( 'metrics' === $url_path ) {
            wp_enqueue_script( 'dt_metrics_personal_script', get_stylesheet_directory_uri() . '/dt-metrics/metrics-personal.js', [
                'jquery',
                'jquery-ui-core',
            ], filemtime( get_theme_file_path() . '/dt-metrics/metrics-personal.js' ), true );

            wp_localize_script(
                'dt_metrics_personal_script', 'dtMetricsPersonal', [
                    'root' => esc_url_raw( rest_url() ),
                    'plugin_uri' => disciple_tools()->theme_url,
                    'nonce' => wp_create_nonce( 'wp_rest' ),
                    'current_user_login' => wp_get_current_user()->user_login,
                    'current_user_id' => get_current_user_id(),
                    'map_key' => dt_get_option( 'map_key' ),
                    'translations' => [
                        "my_contacts_progress" => __( "My Contacts Progress" ),
                        "my_groups_progress" => __( "My Groups Progress" ),
                    ],
                    'overview' => $this->overview(),
                    'my_contacts' => $this->my_contacts(),
                    'my_groups' => $this->my_groups(),
                ]
            );
        }
    }

    public function overview() {
        return [
            'translations' => [
                'title' => __( 'Overview' ),
            ],
            'hero_stats' => [
                'total_contacts' => 100,
                'total_groups' => 20,
            ],
            'contacts_progress' => [
                [ 'Step', 'Contacts', [ 'role' => 'annotation' ] ],
                [ 'Contact Attempt Needed', 10, 10 ],
                [ 'Contact Attempted', 10, 10 ],
                [ 'Contact Established', 10, 10 ],
                [ 'First Meeting Scheduled', 10, 10 ],
                [ 'First Meeting Complete', 10, 10 ],
                [ 'Ongoing Meetings', 10, 10 ],
                [ 'Being Coached', 23, 23 ],
            ],
            'groups_progress' => [
                [ 'Step', 'Contacts', [ 'role' => 'annotation' ] ],
                [ 'Contact Attempt Needed', 10, 10 ],
                [ 'Contact Attempted', 10, 10 ],
                [ 'Contact Established', 10, 10 ],
                [ 'First Meeting Scheduled', 10, 10 ],
                [ 'First Meeting Complete', 40, 40 ],
                [ 'Ongoing Meetings', 50, 50 ],
                [ 'Being Coached', 23, 23 ],
            ],
        ];
    }

    public function my_contacts() {
        return [
            'translations' => [
                'title' => __( 'My Contacts' ),
            ],
            'hero_stats' => [
                'total_contacts' => 100,
                'this_month' => 23,
            ],
            'progress' => [
                [ 'Step', 'Contacts', [ 'role' => 'annotation' ] ],
                [ 'Contact Attempt Needed', 10, 10 ],
                [ 'Contact Attempted', 20, 20 ],
                [ 'Contact Established', 100, 100 ],
                [ 'First Meeting Scheduled', 10, 10 ],
                [ 'First Meeting Complete', 10, 10 ],
                [ 'Ongoing Meetings', 10, 10 ],
                [ 'Being Coached', 23, 23 ],
            ],
        ];
    }

    public function my_groups() {
        return [
            'translations' => [
                'title' => __( 'My Groups' ),
            ],
            'hero_stats' => [
                'total_groups' => 20,
                'this_month' => 12,
            ],
            'progress' => [
                [ 'Step', 'Contacts', [ 'role' => 'annotation' ] ],
                [ 'Contact Attempt Needed', 10, 10 ],
                [ 'Contact Attempted', 80, 80 ],
                [ 'Contact Established', 10, 10 ],
                [ 'First Meeting Scheduled', 40, 40 ],
                [ 'First Meeting Complete', 10, 10 ],
                [ 'Ongoing Meetings', 10, 10 ],
                [ 'Being Coached', 23, 23 ],
            ],
        ];
    }
}


class Disciple_Tools_Metrics_Project extends Disciple_Tools_Metrics_Hooks_Base
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
                    'plugin_uri' => Disciple_Tools::instance()->theme_url,
                    'nonce' => wp_create_nonce( 'wp_rest' ),
                    'current_user_login' => wp_get_current_user()->user_login,
                    'current_user_id' => get_current_user_id(),
                    'map_key' => dt_get_option( 'map_key' ),
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

        parent::__construct();
    }
}


