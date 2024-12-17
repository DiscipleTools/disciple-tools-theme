<?php
/**
 * DT_Setup_Wizard class for the admin page
 *
 * @class      DT_Setup_Wizard
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple.Tools
 * @author     Disciple.Tools
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class DT_Setup_Wizard
 */
class DT_Setup_Wizard
{
    private static $_instance = null;
    private $root = 'setup-wizard';
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_dt_options_menu' ] );
        add_filter( 'tgmpa_load', '__return_false', 100 );

        add_action( 'admin_head', function() {
            remove_action( 'admin_notices', 'update_nag', 3 );
            remove_action( 'admin_notices', 'maintenance_nag', 10 );
            remove_action( 'network_admin_notices', 'update_nag', 3 );
            remove_action( 'network_admin_notices', 'maintenance_nag', 3 );
        });
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_filter( 'script_loader_tag', [ $this, 'filter_script_loader_tag' ], 10, 2 );
    }

    public function enqueue_scripts(){
        dt_theme_enqueue_script( 'setup-wizard', 'dt-core/admin/components/setup-wizard.js', [], true );
        dt_theme_enqueue_script( 'setup-wizard-open-element', 'dt-core/admin/components/setup-wizard-open-element.js', [ 'setup-wizard' ], true );
        dt_theme_enqueue_script( 'setup-wizard-use-cases', 'dt-core/admin/components/setup-wizard-use-cases.js', [ 'setup-wizard', 'setup-wizard-open-element' ], true );
        dt_theme_enqueue_script( 'setup-wizard-modules', 'dt-core/admin/components/setup-wizard-modules.js', [ 'setup-wizard', 'setup-wizard-open-element' ], true );
        dt_theme_enqueue_script( 'setup-wizard-plugins', 'dt-core/admin/components/setup-wizard-plugins.js', [ 'setup-wizard', 'setup-wizard-open-element' ], true );
        dt_theme_enqueue_script( 'setup-wizard-details', 'dt-core/admin/components/setup-wizard-details.js', [ 'setup-wizard', 'setup-wizard-open-element' ], true );
        dt_theme_enqueue_script( 'setup-wizard-controls', 'dt-core/admin/components/setup-wizard-controls.js', [ 'setup-wizard', 'setup-wizard-open-element' ], true );

        wp_localize_script( 'setup-wizard', 'setupWizardShare', [
            'translations' => [
                'title' => esc_html__( 'Disciple.Tools Setup Wizard', 'disciple_tools' ),
                'next' => esc_html__( 'Next', 'disciple_tools' ),
                'submit' => esc_html__( 'Submit', 'disciple_tools' ),
                'confirm' => esc_html__( 'Confirm', 'disciple_tools' ),
                'back' => esc_html__( 'Back', 'disciple_tools' ),
            ],
            'steps' => $this->setup_wizard_steps(),
            'data' => $this->setup_wizard_data(),
        ] );
    }

    public function has_access_permission() {
        return !current_user_can( 'manage_dt' );
    }

    public function filter_script_loader_tag( $tag, $handle ) {
        if ( str_starts_with( $handle, 'setup-wizard' ) ) {
            $tag = preg_replace( '/(.*)(><\/script>)/', '$1 type="module"$2', $tag );
        }
        return $tag;
    }

    public function add_dt_options_menu() {
        if ( $this->has_access_permission() ) {
            return;
        }

        $image_url = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyBpZD0iTGF5ZXJfMiIgZGF0YS1uYW1lPSJMYXllciAyIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB2aWV3Qm94PSIwIDAgNDUwLjY0IDQzMS41NCI+CiAgPGRlZnM+CiAgICA8c3R5bGU+CiAgICAgIC5jbHMtMSB7CiAgICAgICAgZmlsbDogIzhiYzM0YTsKICAgICAgfQoKICAgICAgLmNscy0yIHsKICAgICAgICBmaWxsOiB1cmwoI2xpbmVhci1ncmFkaWVudCk7CiAgICAgIH0KICAgIDwvc3R5bGU+CiAgICA8bGluZWFyR3JhZGllbnQgaWQ9ImxpbmVhci1ncmFkaWVudCIgeDE9IjIyNS4zMyIgeTE9IjI0My44IiB4Mj0iNDUwLjY0IiB5Mj0iMjQzLjgiIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIj4KICAgICAgPHN0b3Agb2Zmc2V0PSIwIiBzdG9wLWNvbG9yPSIjMWQxZDFiIi8+CiAgICAgIDxzdG9wIG9mZnNldD0iLjQ3IiBzdG9wLWNvbG9yPSIjOGJjMzRhIi8+CiAgICAgIDxzdG9wIG9mZnNldD0iMSIgc3RvcC1jb2xvcj0iIzhiYzM0YSIvPgogICAgPC9saW5lYXJHcmFkaWVudD4KICA8L2RlZnM+CiAgPGcgaWQ9IkxheWVyXzEtMiIgZGF0YS1uYW1lPSJMYXllciAxIj4KICAgIDxnPgogICAgICA8cG9seWdvbiBjbGFzcz0iY2xzLTIiIHBvaW50cz0iNDUwLjY0IDQzMS41NCAzNzUuNTQgNDMxLjU0IDIyNS4zMyAxMTcuMjcgMjU0LjU5IDU2LjA1IDQ1MC42NCA0MzEuNTQiLz4KICAgICAgPHBvbHlnb24gY2xhc3M9ImNscy0xIiBwb2ludHM9IjI1NC41OSA1Ni4wNSAyMjUuMzMgMTE3LjI3IDIyNS4zMiAxMTcuMjcgNzUuMTEgNDMxLjU0IDAgNDMxLjU0IDI5LjM1IDM3NS4zMyAyMDUuMyAzOC4zNSAyMjUuMzIgMCAyNDQuMzQgMzYuNDMgMjU0LjU5IDU2LjA1Ii8+CiAgICA8L2c+CiAgPC9nPgo8L3N2Zz4=';
        add_menu_page(
            __( 'Setup Wizard (D.T)', 'disciple_tools' ),
            __( 'Setup Wizard (D.T)', 'disciple_tools' ),
            'manage_dt',
            'dt_setup_wizard',
            [ $this, 'content' ],
            $image_url,
            52,
        );
    }

    public function content() {
        if ( $this->has_access_permission() ) {
            wp_die( 'You do not have sufficient permissions to access this page.' );
        }

        ?>

            <style>
                body {
                    margin: 0;
                }
            </style>

            <setup-wizard></setup-wizard>

        <?php
    }

    public function setup_wizard_steps() {
        $bloginfo = get_bloginfo();
        $steps = [
            [
                'key' => 'choose_your_use_cases',
                'name' => 'Choose your use cases',
                'component' => 'setup-wizard-use-cases',
                'description' => 'How are you planning to use DT?',
                'config' => [
                    'media',
                    'crm',
                    'dmm',
                ]
            ],
            [
                'key' => 'choose_your_modules',
                'name' => 'Choose your modules',
                'component' => 'setup-wizard-modules',
                'description' => 'What modules do you want to use?',
            ],
            [
                'name' => 'Site details',
                'description' => 'Fill in some site details',
                'component' => 'setup-wizard-details',
                'config' => [
                    [
                        'type' => 'options',
                        'options' => [
                            [
                                'key' => 'blogname',
                                'name' => 'Site name',
                                'value' => isset( $bloginfo['name'] ) ? $bloginfo['name'] : '',
                            ],
                            [
                                'key' => 'blogdescription',
                                'name' => 'Site description',
                                'value' => isset( $bloginfo['description'] ) ? $bloginfo['description'] : '',
                            ],
                            [
                                'key' => 'admin_email',
                                'name' => 'Admin email',
                                'value' => isset( $bloginfo['admin_email'] ) ? $bloginfo['admin_email'] : '',
                            ]
                        ],
                    ]
                ],
            ],
            [
                'name' => 'Plugins',
                'description' => 'Choose which plugins to install.',
                'component' => 'setup-wizard-plugins',
            ],
        ];

        $steps = apply_filters( 'dt_setup_wizard_steps', $steps );

        return $steps;
    }
    public function setup_wizard_data() : array {
        $modules = dt_get_option( 'dt_post_type_modules' );
        $data = [
            'use_cases' => [
                'media' => [
                    'key' => 'media',
                    'name' => 'Access Ministry',
                    'description' => 'Are you filtering for contacts for engagement?',
                    'recommended_modules' => [
                        'access_module',
                    ],
                    'recommended_plugins' => [
                        'loremoo',
                        'ipsum',
                        'dolor',
                        'amit',
                    ],
                ],
                'crm' => [
                    'key' => 'crm',
                    'name' => 'Relationship Manager',
                    'description' => 'Are you needing to manage your contacts?',
                    'recommended_modules' => [],
                    'recommended_plugins' => [
                        'loremoo',
                        'ipsum',
                        'dolor',
                        'amit',
                    ],
                ],
                'dmm' => [
                    'key' => 'dmm',
                    'name' => 'Disciple Making Movements',
                    'description' => 'Are you managing multiplying groups?',
                    'recommended_modules' => [
                        'contacts_faith_module',
                        'contacts_coaching_module',
                        'contacts_baptisms_module',
                        'groups_base',
                        'people_groups_module',
                    ],
                    'recommended_plugins' => [],
                ],
            ],
            'modules' => $modules,
            'plugins' => [
                [
                    'key' => 'lorem',
                    'name' => 'lorem',
                    'description' => 'Fugiat deserunt Lorem veniam et veniam cillum tempor exercitation velit ex velit cupidatat nostrud.',
                ],
                [
                    'key' => 'ipsum',
                    'name' => 'ipsum',
                    'description' => 'Mollit pariatur sit fugiat officia aliqua irure commodo nostrud et elit.',
                ],
                [
                    'key' => 'dolor',
                    'name' => 'dolor',
                    'description' => 'Esse qui fugiat irure nisi consectetur.',
                ],
                [
                    'key' => 'amit',
                    'name' => 'amit',
                    'description' => 'Consectetur consequat esse id aute commodo ea qui cillum.',
                ],
            ],
        ];

        $data = apply_filters( 'dt_setup_wizard_data', $data );

        return $data;
    }
}
DT_Setup_Wizard::instance();
