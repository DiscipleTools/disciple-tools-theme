<?php

Disciple_Tools_Metrics_Users::instance();
class Disciple_Tools_Metrics_Users extends Disciple_Tools_Metrics_Hooks_Base
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

            add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ] ); // add custom URL
            add_filter( 'dt_metrics_menu', [ $this, 'add_menu' ], 50 );

            if ( 'metrics/users' === $url_path ) {
                add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
            }
        }
    }

    public function add_url( $template_for_url ) {
        $template_for_url['metrics/user'] = 'template-metrics.php';
        return $template_for_url;
    }

    public function add_menu( $content ) {
        $content .= '
            <li><a href="" id="projects-menu">' .  esc_html__( 'Users', 'disciple_tools' ) . '</a>
                <ul class="menu vertical nested" >
                    <li><a href="'. site_url( '/metrics/users/' ) .'#user_activity">'. esc_html__( 'Activity' ) .'</a></li>
                </ul>
            </li>
            ';
        return $content;
    }

    public function scripts() {
        wp_enqueue_script( 'dt_metrics_users_script', get_stylesheet_directory_uri() . '/dt-metrics/metrics-users.js', [
            'jquery',
            'jquery-ui-core',
        ], filemtime( get_theme_file_path() . '/dt-metrics/metrics-users.js' ), true );

        wp_localize_script(
            'dt_metrics_users_script', 'dtMetricsProject', [
                'root' => esc_url_raw( rest_url() ),
                'plugin_uri' => get_stylesheet_directory_uri(),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'map_key' => dt_get_option( 'map_key' ),
                'users' => $this->users(),
            ]
        );
    }

    public function users() {
        return [
            'translations' => [
                'title' => __( 'Users' ),
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
            'critical_path' => [
                [ 'Step', 'Contacts', [ 'role' => 'annotation' ] ],
                [ 'New Contacts', 100, 100 ],
                [ 'Contacts Attempted', 95, 95 ],
                [ 'First Meetings', 80, 80 ],
                [ 'All Baptisms', 6, 6 ],
                [ '1st Gen', 4, 4 ],
                [ '2nd Gen', 2, 2 ],
                [ '3rd Gen', 0, 0 ],
                [ '4th Gen', 0, 0 ],
                [ 'Baptizers', 3, 3 ],
                [ 'Church Planters', 4, 4 ],
                [ 'All Groups', 4, 4 ],
                [ 'Active Pre-Groups', 4, 4 ],
                [ 'Active Groups', 4, 4 ],
                [ 'Active Churches', 5, 5 ],
                [ '1st Gen', 3, 3 ],
                [ '2nd Gen', 2, 2 ],
                [ '3rd Gen', 0, 0 ],
                [ '4th Gen', 0, 0 ],

            ],
            'group_types' => [
                [ 'Group Type', 'Number' ],
                [ 'Pre-Group', 75 ],
                [ 'Group', 25 ],
                [ 'Church', 25 ],
            ],
            'group_health' => [
                [ 'Step', 'Groups', [ 'role' => 'annotation' ] ],
                [ 'Fellowship', 10, 10 ],
                [ 'Giving', 10, 10 ],
                [ 'Communion', 10, 10 ],
                [ 'Baptism', 10, 10 ],
                [ 'Prayer', 40, 40 ],
                [ 'Leaders', 50, 50 ],
                [ 'Word', 23, 23 ],
                [ 'Praise', 23, 23 ],
                [ 'Evangelism', 23, 23 ],
                [ 'Covenant', 23, 23 ],
            ],
            'group_generations' => [
                [ 'Generation', 'Pre-Group', 'Group', 'Church', [ 'role' => 'annotation' ] ],
                [ '1st Gen', 5, 8, 6, 21 ],
                [ '2st Gen', 1, 3, 4, 8 ],
                [ '3st Gen', 0, 2, 0, 2 ],
                [ '4st Gen', 1, 1, 0, 2 ],
                [ '5+ Gen', 0, 0, 1, 1 ],
            ],
        ];
    }
}
