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
        $template_for_url['metrics/users'] = 'template-metrics.php';
        return $template_for_url;
    }

    public function add_menu( $content ) {
        $content .= '
            <li><a href="" id="projects-menu">' .  esc_html__( 'Users', 'disciple_tools' ) . '</a>
                <ul class="menu vertical nested" >
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
                'title_activity' => __( 'Users Activity' ),
            ],
            'hero_stats' => self::chart_user_hero_stats(),
            'logins_by_day' => self::chart_user_logins_by_day(),
            'contacts_per_user' => self::chart_user_contacts_per_user(),
            'least_active' => [
                [ 'User', 'Login (Days Ago)' ],
                [ 'Chris', 34 ],
                [ 'Kara', 14 ],
                [ 'Mason', 9 ],
            ],
            'most_active' => [],
        ];
    }
}
