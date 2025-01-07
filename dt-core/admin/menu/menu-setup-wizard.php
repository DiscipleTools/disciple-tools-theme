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
        add_filter( 'dt_setup_wizard_items', [ $this, 'dt_setup_wizard_items' ], 10, 1 );
    }

    public function enqueue_scripts(){
        dt_theme_enqueue_script( 'setup-wizard', 'dt-core/admin/components/setup-wizard.js', [], true );
        dt_theme_enqueue_script( 'setup-wizard-open-element', 'dt-core/admin/components/setup-wizard-open-element.js', [ 'setup-wizard' ], true );
        dt_theme_enqueue_script( 'setup-wizard-use-cases', 'dt-core/admin/components/setup-wizard-use-cases.js', [ 'setup-wizard', 'setup-wizard-open-element' ], true );
        dt_theme_enqueue_script( 'setup-wizard-modules', 'dt-core/admin/components/setup-wizard-modules.js', [ 'setup-wizard', 'setup-wizard-open-element' ], true );
        dt_theme_enqueue_script( 'setup-wizard-plugins', 'dt-core/admin/components/setup-wizard-plugins.js', [ 'setup-wizard', 'setup-wizard-open-element' ], true );
        dt_theme_enqueue_script( 'setup-wizard-details', 'dt-core/admin/components/setup-wizard-details.js', [ 'setup-wizard', 'setup-wizard-open-element' ], true );
        dt_theme_enqueue_script( 'setup-wizard-keys', 'dt-core/admin/components/setup-wizard-keys.js', [ 'setup-wizard', 'setup-wizard-open-element' ], true );
        dt_theme_enqueue_script( 'setup-wizard-controls', 'dt-core/admin/components/setup-wizard-controls.js', [ 'setup-wizard', 'setup-wizard-open-element' ], true );
        dt_theme_enqueue_script( 'setup-wizard-intro', 'dt-core/admin/components/setup-wizard-intro.js', [ 'setup-wizard', 'setup-wizard-open-element' ], true );
        dt_theme_enqueue_script( 'setup-wizard-celebration', 'dt-core/admin/components/setup-wizard-celebration.js', [ 'setup-wizard', 'setup-wizard-open-element' ], true );

        wp_localize_script( 'setup-wizard', 'setupWizardShare', [
            'translations' => [
                'title' => esc_html__( 'Disciple.Tools Setup Wizard', 'disciple_tools' ),
                'next' => esc_html__( 'Next', 'disciple_tools' ),
                'submit' => esc_html__( 'Submit', 'disciple_tools' ),
                'confirm' => esc_html__( 'Confirm', 'disciple_tools' ),
                'back' => esc_html__( 'Back', 'disciple_tools' ),
                'skip' => esc_html__( 'Skip', 'disciple_tools' ),
                'finish' => esc_html__( 'Finish', 'disciple_tools' ),
                'exit' => esc_html__( 'Exit', 'disciple_tools' ),
            ],
            'steps' => $this->setup_wizard_steps(),
            'data' => $this->setup_wizard_data(),
            'admin_url' => admin_url(),
            'image_url' => trailingslashit( get_template_directory_uri() ) . 'dt-assets/images/',
            'can_install_plugins' => current_user_can( 'install_plugins' ),
        ] );
    }

    public function dt_setup_wizard_items( $items ){

        $is_completed = !empty( get_option( 'dt_setup_wizard_completed' ) );
        $is_administrator = current_user_can( 'manage_options' );

        $setup_wizard_step = [
            'label' => 'Setup Wizard',
            'description' => 'D.T. can be used in many ways from managing connections and relationships, all the way through to tracking and managing a movement of Disciple Making.             In order to help you, we want to take you through a series of choices to give you the best start at getting Disciple.Tools setup ready to suit your needs.',
            'link' => esc_url( admin_url( 'admin.php?page=dt_setup_wizard' ) ),
            'complete' => $is_completed || !$is_administrator,
            'hide_mark_done' => true
        ];

        return [
            'getting_started' => $setup_wizard_step,
            ...$items,
        ];
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
        /* Hide the setup wizard in the menu */
        remove_menu_page( 'dt_setup_wizard' );
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
                html.wp-toolbar {
                    padding-top: 0;
                }
                #adminmenuback,
                #adminmenuwrap,
                #wpadminbar  {
                    display: none;
                }
                #wpcontent {
                    margin-left: 0;
                    padding-left: 0;
                }
                #wpbody {
                    padding-top: 0 !important;
                }
            </style>

            <setup-wizard></setup-wizard>

        <?php
    }

    public function setup_wizard_steps() {
        $steps = [
            [
                'key' => 'intro',
                'name' => 'Intro',
                'component' => 'setup-wizard-intro',
            ],
            [
                'key' => 'choose_your_use_cases',
                'name' => 'Use cases',
                'component' => 'setup-wizard-use-cases',
                'description' => 'How are you planning to use DT?',
                'config' => [
                    'crm',
                    'media',
                    'dmm',
                ]
            ],
            [
                'key' => 'choose_your_modules',
                'name' => 'Modules',
                'component' => 'setup-wizard-modules',
                'description' => 'What modules do you want to use?',
            ],
            [
                'name' => 'Plugins',
                'description' => 'Choose which plugins to install.',
                'component' => 'setup-wizard-plugins',
            ],
            [
                'name' => 'Site keys',
                'description' => 'Fill in some site details',
                'component' => 'setup-wizard-keys',
                'config' => [
                    'dt_google_map_key' => Disciple_Tools_Google_Geocode_API::get_key(),
                    'dt_mapbox_api_key' => DT_Mapbox_API::get_key(),
                ],
            ],
            [
                'key' => 'celebration',
                'name' => 'Done',
                'component' => 'setup-wizard-celebration',
            ]
        ];

        $steps = apply_filters( 'dt_setup_wizard_steps', $steps );

        return $steps;
    }

    public static function get_plugins_list(){
        $dt_plugins = Disciple_Tools_Tab_Featured_Extensions::get_dt_plugins();
        $enabled_plugins = [
            'disciple-tools-dashboard',
            'disciple-tools-webform',
            'disciple-tools-facebook',
            'disciple-tools-import',
            'disciple-tools-bulk-magic-link-sender',
            'disciple-tools-team-module',
            'disciple-tools-storage',
            'disciple-tools-prayer-campaigns',
        ];
        if ( is_multisite() ){
            $enabled_plugins[] = 'disciple-tools-multisite';
        }
        //dt-home
        //auto assignment
        //share app


        $plugin_data = [];
        foreach ( $dt_plugins as $plugin ) {
            if ( in_array( $plugin->slug, $enabled_plugins, true ) ) {
                $plugin_data[] = $plugin;
            }
        }
        return $plugin_data;
    }


    public function setup_wizard_data() : array {
        $modules = dt_get_option( 'dt_post_type_modules' );
        $plugin_data = self::get_plugins_list();
        $data = [
            'use_cases' => [
                'crm' => [
                    'key' => 'crm',
                    'name' => 'Simple Setup - Relationship Manager',
                    'description' => 'Launch people ( seekers or believers ) on a journey that leads
                     them closer to Christ. Set up your own fields, integrations and workflows to
                     track them as them go.',
                    'recommended_modules' => [],
                    'recommended_plugins' => [
                        'disciple-tools-webform',
                        'disciple-tools-import',
                        'disciple-tools-bulk-magic-link-sender',
                    ],
                ],
                'media' => [
                    'key' => 'media',
                    'name' => 'Media or Follow-up Ministry',
                    'description' => 'Do you find seekers through media or through events
                        or trainings? Let your team(s) steward these leads, letting none fall
                        through the cracks and inviting them into deeper relationships
                        with God and others.',
                    'recommended_modules' => [
                        'access_module',
                        'contacts_faith_module',
                        'contacts_baptisms_module',
                    ],
                    'recommended_plugins' => [
                        'disciple-tools-webform',
                        'disciple-tools-facebook',
                        'disciple-tools-dashboard',
                        'disciple-tools-import',
                        'disciple-tools-bulk-magic-link-sender',
                    ],
                ],
                'dmm' => [
                    'key' => 'dmm',
                    'name' => 'Church Growth and Disciple Making',
                    'description' => 'Invest in the growth of individuals and churches as they multiply.
                     Monitor church health and track individuals through coaching and faith milestones.',
                    'recommended_modules' => [
                        'contacts_baptisms_module',
                        'contacts_faith_module',
                        'groups_base',
                        'people_groups_module'
                    ],
                    'recommended_plugins' => [
                        'disciple-tools-webform',
                        'disciple-tools-bulk-magic-link-sender',
                        'disciple-tools-training',
                        'disciple-tools-import',
                    ],
                ],
            ],
            'modules' => $modules,
            'plugins' => $plugin_data,
        ];

        $data = apply_filters( 'dt_setup_wizard_data', $data );

        return $data;
    }
}
DT_Setup_Wizard::instance();
