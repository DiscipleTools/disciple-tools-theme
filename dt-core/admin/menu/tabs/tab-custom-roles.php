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

        parent::__construct();
    } // End __construct()

    public function add_submenu() {
        add_submenu_page( 'dt_options', __( 'Roles', 'disciple_tools' ), __( 'Roles', 'disciple_tools' ), 'manage_dt', 'dt_options&tab=roles', [ 'Disciple_Tools_Settings_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        ?>
        <a href="<?php echo esc_url( admin_url() ) ?>admin.php?page=dt_options&tab=roles"
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
        ?>
            <form method="post" name="role_select" id="role-select">
                <input type="hidden" name="tag_edit_nonce" id="tag-edit-nonce" value="<?php echo esc_attr( wp_create_nonce( 'tag_edit' ) ) ?>" />
                <table>
                    <thead>
                        <tr>
                            <th>
                                Label
                            </th>
                            <th>
                                Description
                            </th>

                        </tr>
                    </thead>
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
