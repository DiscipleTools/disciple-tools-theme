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
        $url_path = trim( parse_url( add_query_arg( array() ), PHP_URL_PATH ), '/' );

        if ( 'metrics' === substr( $url_path, '0', 7 ) ) {

            add_filter( 'dt_metrics_menu', [ $this, 'add_overview_menu' ], 20 );
            add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );

            parent::__construct();

            dt_write_log( Disciple_Tools_Metrics::query_my_contacts_progress( get_current_user_id() ) );
        }
    }

    public function add_overview_menu( $content ) {
        $content .= '
            <li><a href="'. site_url( '/metrics/' ) .'#overview" onclick="overview()">' .  esc_html__( 'Overview', 'disciple_tools' ) . '</a></li>
            ';
        return $content;
    }

    public function scripts() {
        wp_enqueue_script( 'dt_metrics_personal_script', get_stylesheet_directory_uri() . '/dt-metrics/metrics-personal.js', [
            'jquery',
            'jquery-ui-core',
        ], filemtime( get_theme_file_path() . '/dt-metrics/metrics-personal.js' ), true );

        wp_localize_script(
            'dt_metrics_personal_script', 'dtMetricsPersonal', [
                'root' => esc_url_raw( rest_url() ),
                'plugin_uri' => get_stylesheet_directory_uri(),
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

    public function overview() {
        return [
            'translations' => [
                'title' => __( 'Overview' ),
                'total_contacts' => __( 'Total Contacts' ),
                'total_groups' => __( 'Total Groups' ),
                'updates_needed' => __( 'Updates Needed' ),
                'attempts_needed' => __( 'Attempts Needed' ),
            ],
            'hero_stats' => [
                'total_contacts' => dt_count_user_contacts(),
                'total_groups' => dt_count_user_groups(),
                'updates_needed' => dt_count_updates_needed(),
                'attempts_needed' => dt_count_attempts_needed(),
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
Disciple_Tools_Metrics_Personal::instance();