<?php

/**
 * Disciple_Tools_Multi_Roles
 *
 * @class   Disciple_Tools_Multi_Roles
 * @version 0.1.0
 * @since   0.1.0
 * @package Disciple.Tools
 *
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Multi_Roles
 */
class Disciple_Tools_Tab_Custom_Roles extends Disciple_Tools_Abstract_Menu_Base {
    private static $_instance = null;
    protected $url_base;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     *
     * @access  public
     * @since   0.1.0
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_submenu' ], 99 );
        add_action( 'dt_settings_tab_menu', [ $this, 'add_tab' ], 10, 1 );
        add_action( 'dt_settings_tab_content', [ $this, 'content' ], 99, 1 );
        $this->url_base = esc_url(admin_url()) . "admin.php?page=dt_options&tab=roles";

        parent::__construct();
    } // End __construct()

    public function add_submenu() {
        add_submenu_page( 'dt_options', __( 'Roles', 'disciple_tools' ), __( 'Roles', 'disciple_tools' ), 'manage_dt', 'dt_options&tab=roles', [ 'Disciple_Tools_Settings_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        ?>
        <a href="<?php echo $this->url_base ?>"
           class="nav-tab <?php echo esc_html( $tab == 'roles' ? 'nav-tab-active' : '' ) ?>">
            <?php echo esc_html__( 'Roles' ) ?>
        </a>
        <?php
    }

    /**
     * Packages and prints Roles page
     *
     * @param $tab
     */
    public function content($tab)
    {
        if ($tab !== 'roles') {
            return;
        }


        if ( isset( $_POST['role_select_nonce'] ) ) {
           $this->save($tab);
        }

        $this->box( 'top', __( 'Add or edit custom user roles.', 'disciple_tools' ) );

        $this->index();
    }

    public function index() {
        $roles = apply_filters( 'dt_set_roles_and_permissions', [] );
        ?>
            <style>
                #role-select td,
                #role-select th {
                    padding: 15px;
                }

                #role-select tbody tr {
                    background-color: #F6F7F7;
                }

                #role-select details.flyout {
                    position: relative;
                    width: 30px;
                    margin: 0 0 0 auto;
                }
                #role-select details.flyout > nav {
                    position: absolute;
                    top: 0;
                    right: 100%;
                }

                #role-select details.flyout > nav ul {
                    border-radius: 5px;
                    border: solid 1px #50575E;
                    background-color: white;
                    padding-top: 0px;
                    margin: 0;
                }

                #role-select details.flyout > nav li:not(:last-child) {
                    border-bottom: solid 1px #50575E;
                }

                #role-select details.flyout > nav li {
                    margin-bottom: 0px;
                }

                #role-select details.flyout > nav a {
                    display: block;
                    padding: 5px;
                    color: black;
                }

                #role-select details.flyout > nav a:hover {
                    background-color: #F6F7F7;
                }

                #role-select details.flyout > summary:focus {
                    outline: none;
                }
                #role-select details.flyout > summary::-webkit-details-marker {
                    display: none;
                }
                #role-select details.flyout > summary {
                    list-style: none;
                    padding-right: 10px;
                }
                #role-select details.flyout .dashicons-ellipsis {
                    transform: rotate(-90deg);
                    cursor: pointer;
                }
            </style>
            <form method="post" name="role_select" id="role-select">
                <input type="hidden" name="tag_edit_nonce" id="tag-edit-nonce" value="<?php echo esc_attr( wp_create_nonce( 'tag_edit' ) ) ?>" />
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>
                                <strong><?php _e('Label', 'disciple-tools'); ?></strong>
                            </th>
                            <th>
                                <strong><?php _e('Description', 'disciple-tools'); ?></strong>
                            </th>
                            <th>
                                <string><?php _e('Type', 'disciple-tools'); ?></string>
                            </th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roles as $key => $role): ?>
                            <tr>
                                <td>
                                    <strong>
                                        <a href="" title="View capabilities for <?php echo $role['label'] ?>"><?php echo $role['label'] ?></a>
                                    </strong>
                                </td>
                                <td>
                                    <?php echo $role['description'] ?>
                                </td>
                                <td>
                                    <?php _e('Built-in', 'disciple-tools'); ?>
                                </td>
                                <td style="text-align: right;">
                                    <details class="flyout">
                                        <summary>
                                            <span class="dashicons dashicons-ellipsis"></span>
                                        </summary>
                                        <nav>
                                            <ul>
                                                <li>
                                                    <a href="<?php echo $this->url_base . '&' . http_build_query(['action' => 'duplicate', 'role' => $key]) ?>">Duplicate</a>
                                                </li>
                                            </ul>
                                        </nav>
                                    </details>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                </table>
            </form>
        <?php
    }

    public function save($tab)
    {
        if ($tab !== 'roles') {
            return;
        }


    }
}
Disciple_Tools_Tab_Custom_Roles::instance();
