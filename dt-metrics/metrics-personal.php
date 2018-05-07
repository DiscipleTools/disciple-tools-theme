<?php


class Disciple_Tools_Metrics_Personal extends Disciple_Tools_Metrics_Hooks_Base
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
        add_filter( 'dt_metrics_top_menu', [ $this, 'add_overview_menu' ], 10 );
        add_filter( 'dt_metrics_menu_my_contacts', [ $this, 'add_contacts_menu' ], 10 );
        add_filter( 'dt_metrics_menu_my_groups', [ $this, 'add_groups_menu' ], 10 );
        add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );

        parent::__construct();

//        dt_write_log( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ) );
        dt_write_log( __METHOD__ );
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
            'critical_path' => [
                [ 'Step', 'Contacts', [ 'role' => 'annotation' ] ],
                [ 'New Contacts', 100, 100 ],
                [ 'Contacts Attempted', 95, 95 ],
                [ 'Contacts Established', 88, 88 ],
                [ 'First Meetings', 80, 80 ],
                [ 'Baptisms', 4, 4 ],
                [ 'Baptizers', 3, 3 ],
                [ 'Active Groups', 4, 4 ],
                [ 'Active Churches', 5, 5 ],
                [ 'Church Planters', 4, 4 ],
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
