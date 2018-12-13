<?php

Disciple_Tools_Metrics_Users::instance();
class Disciple_Tools_Metrics_Users extends Disciple_Tools_Metrics_Hooks_Base
{
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        $url_path = dt_get_url_path();

        if ( 'metrics' === substr( $url_path, '0', 7 ) ) {

            add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ] ); // add custom URL
            add_filter( 'dt_metrics_menu', [ $this, 'add_menu' ], 50 );

            if ( 'metrics/users' === $url_path ) {
                add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
            }
        }
    }

    public function add_url( $template_for_url ) {
        $template_for_url['metrics/users'] = 'template-metrics.php';
        return $template_for_url;
    }

    public function add_menu( $content ) {
        $content .= '
            <li><a href="">' .  esc_html__( 'Users', 'disciple_tools' ) . '</a>
                <ul class="menu vertical nested" id="users-menu" >
                    <li><a href="'. site_url( '/metrics/users/' ) .'#users_activity" onclick="users_activity()">'. esc_html__( 'Activity' ) .'</a></li>
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
            'dt_metrics_users_script', 'dtMetricsUsers', [
                'root' => esc_url_raw( rest_url() ),
                'theme_uri' => get_stylesheet_directory_uri(),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'map_key' => dt_get_option( 'map_key' ),
                'data' => $this->users(),
            ]
        );
    }

    public function users() {
        return [
            'translations' => [
                'title_activity' => __( 'Users Activity', 'disciple_tools' ),
                'title_recent_activity' => __( 'Recent Activity', 'disciple_tools' ),
                'label_total_users' => __( 'Total Users', 'disciple_tools' ),
                'label_total_multipliers' => __( 'Multipliers', 'disciple_tools' ),
                'label_total_dispatchers' => __( 'Dispatchers', 'disciple_tools' ),
                'label_contacts_per_user' => __( 'Contacts Per User', 'disciple_tools' ),
                'label_least_active' => __( 'Least Active', 'disciple_tools' ),
                'label_most_active' => __( 'Most Active', 'disciple_tools' ),
            ],
            'hero_stats' => $this->chart_user_hero_stats(),
            'recent_activity' => $this->chart_recent_activity(),
        ];
    }

    public function chart_user_hero_stats() {
        $result = count_users();

        return [
            'total_users' => $result['total_users'] ?? 0,
            'total_dispatchers' => $result['avail_roles']['dispatcher'] ?? 0,
            'total_multipliers' => $result['avail_roles']['multiplier'] ?? 0,
            'all_roles' => $result['avail_roles'],
        ];
    }

    public function chart_recent_activity() {
        $chart = [];
        $chart[] = [ 'Year', 'Logins' ];

        $results = Disciple_Tools_Queries::instance()->tree( 'recent_logins' );
        if ( empty( $results ) ) {
            return $chart;
        }

        $results = array_reverse( $results );
        foreach ( $results as $result ) {
            $date = date_create( $result['report_date'] );
            $chart[] = [ date_format( $date, "M d" ), (int) $result['total'] ];
        }

        return $chart;
    }

    public function chart_contacts_per_user() {
        $chart = [];

        $chart[] = [ 'Name', 'Total', 'Attempt Needed', 'Attempted', 'Established', 'Meeting Scheduled', 'Meeting Complete', 'Ongoing', 'Being Coached' ];

        $results = Disciple_Tools_Queries::instance()->query( 'contacts_per_user' );
        if ( empty( $results ) ) {
            return $chart;
        }

        return $chart;
    }
}
