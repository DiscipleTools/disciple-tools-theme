<?php

Disciple_Tools_Metrics_Prayer::instance();
class Disciple_Tools_Metrics_Prayer extends Disciple_Tools_Metrics_Hooks_Base
{
    public $permissions = [ 'view_any_contacts', 'view_project_metrics' ];
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );

        if ( !$this->has_permission() ){
            return;
        }

        $url_path = dt_get_url_path();
        if ( 'metrics' === substr( $url_path, '0', 7 ) ) {

            add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ] ); // add custom URL
            add_filter( 'dt_metrics_menu', [ $this, 'add_menu' ], 50 );

            if ( 'metrics/prayer' === $url_path ) {
                add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
            }
        }
    }

    public function add_url( $template_for_url ) {
        $template_for_url['metrics/prayer'] = 'template-metrics.php';
        return $template_for_url;
    }

    public function add_menu( $content ) {
        $content .= '
            <li><a href="'. site_url( '/metrics/prayer/' ) .'#prayer_overview" onclick="prayer_overview()">'. esc_html__( 'Prayer List', 'disciple_tools' ) .'</a></li>
            ';
        return $content;
    }

    public function scripts() {
        wp_enqueue_script( 'dt_metrics_prayer_script', get_template_directory_uri() . '/dt-metrics/metrics-prayer.js', [
            'jquery',
            'jquery-ui-core',
        ], filemtime( get_theme_file_path() . '/dt-metrics/metrics-prayer.js' ), true );

        wp_localize_script(
            'dt_metrics_prayer_script', 'dtMetricsPrayer', [
                'root' => esc_url_raw( rest_url() ),
                'theme_uri' => get_template_directory_uri(),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'data' => $this->data(),
            ]
        );
    }

    public function data() {
        return [
            'translations' => [
                'title_1' => __( 'Prayer Lists', 'disciple_tools' ),
                'title_2' => __( 'Praises for Steps Taken', 'disciple_tools' ),
                'title_3' => __( 'Requests for Next Steps Needed', 'disciple_tools' ),
            ],
        ];
    }

    /**
     * API Routes
     */
    public function add_api_routes() {
        $version = '1';
        $namespace = 'dt/v' . $version;

        register_rest_route(
            $namespace, '/metrics/prayer_list', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'get_prayer_list' ],
                ],
            ]
        );
    }


    public function get_prayer_list( WP_REST_Request $request ) {
        if ( !$this->has_permission() ){
            return new WP_Error( "get_prayer_list", "Missing Permissions", [ 'status' => 400 ] );
        }

        $params = $request->get_params();

        $days = 30;
        if ( isset( $params['days'] ) ) {
            $days = (int) $params['days'];
        }

        $alias_name = true;
        if ( isset( $params['alias_name'] ) ) {
            $alias_name = $params['alias_name'];
        }

        $alias_location = false;
        if ( isset( $params['alias_location'] ) ) {
            $alias_location = $params['alias_location'];
        }

        return $this->prayer_list( (int) $days, $alias_name, $alias_location );
    }

    public function prayer_list( $days = 30, $alias_name = true, $alias_location = false ) {
        $list = [
            "praise_meetings" => [],
            "request_meetings" => [],
            "baptisms" => [],
            "new_groups" => [],
            "new_contacts" => [],
        ];
        $args = [
            'days' => $days,
        ];

        // Meetings
        $recent_seeker_path = dt_queries()->query( 'recent_seeker_path', $args );
        if ( ! empty( $recent_seeker_path ) ) {
            $unique = [];
            foreach ( $recent_seeker_path as $item ) {

                if ( $alias_name ) {
                    $item['name'] = dt_make_alias_name( $item['name'] );
                }

                if ( $alias_location ) {
                    $item['location_name'] = 'Location ' . $item['location_id'];
                }

                if ( isset( $unique[$item['id']] ) ) {
                    continue;
                }

                if ( 'met' === $item['type'] ) {
                    $list['praise_meetings'][] = [
                        'text' => $item['name'],
                        'type' => $item['type'],
                        'location_name' => $item['location_name'],
                        'id' => $item['id']
                    ];
                } else {
                    $list['request_meetings'][] = [
                        'text' => $item['name'],
                        'type' => $item['type'],
                        'location_name' => $item['location_name'],
                        'id' => $item['id']
                    ];
                }

                $unique[$item['id']] = true;
            }
        }


        // Baptisms
        $baptisms = dt_queries()->query( 'recent_baptisms', $args );
        if ( ! empty( $baptisms ) ) {
            $unique = [];
            foreach ( $baptisms as $item ) {
                if ( $alias_name ) {
                    $item['name'] = dt_make_alias_name( $item['name'] );
                }

                if ( $alias_location ) {
                    $item['location_name'] = 'Location ' . $item['location_id'];
                }

                if ( isset( $unique[$item['id']] ) ) {
                    continue;
                }

                $list['baptisms'][] = [
                    'text' => $item['name'],
                    'location_name' => $item['location_name'],
                    'id' => $item['id']
                ];

                $unique[$item['id']] = true;

            }
        }

        // New Groups and Contacts
        $new = dt_queries()->query( 'new_contacts_groups', $args );
        if ( ! empty( $new ) ) {
            $unique = [];
            foreach ( $new as $item ) {

                if ( $alias_name ) {
                    $item['name'] = dt_make_alias_name( $item['name'] );
                }

                if ( $alias_location ) {
                    if ( empty( $item['location_id'] ) ) {
                        $item['location_name'] = 'Location ' . $item['location_id'];
                    } else {
                        $item['location_name'] = '';
                    }
                }

                if ( isset( $unique[$item['id']] ) ) {
                    continue;
                }

                if ( 'contacts' === $item['type'] ) {
                    $list['new_contacts'][] = [
                        'text' => $item['name'],
                        'type' => $item['type'],
                        'location_name' => $item['location_name'],
                        'id' => $item['id']
                    ];
                } else if ( 'groups' === $item['type'] ) {
                    $list['new_groups'][] = [
                        'text' => $item['name'],
                        'type' => $item['type'],
                        'location_name' => $item['location_name'],
                        'id' => $item['id']
                    ];
                }

                $unique[$item['id']] = true;
            }
        }

        return $list;
    }
}

function dt_make_alias_name( $name ) {
    // SHA256
    $name = hash( 'sha256', $name );
    // base64_encode
    $name = base64_encode( $name );
    // substr_2
    $alias = strtoupper( substr( $name, 0, 2 ) );
    if ( ! ctype_alpha( $alias ) ) {
        $alias = strtoupper( substr( base64_encode( hash( 'sha256', $alias ) ), 0, 2 ) );
    }
    return $alias;
}
