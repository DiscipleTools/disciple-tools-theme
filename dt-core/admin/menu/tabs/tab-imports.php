<?php

if ( !defined( 'ABSPATH' ) ){
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Tab_Imports
 */
class Disciple_Tools_Tab_Imports extends Disciple_Tools_Abstract_Menu_Base{
    private static $_instance = null;

    public static function instance(){
        if ( is_null( self::$_instance ) ){
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
    public function __construct(){
        add_action( 'admin_menu', [ $this, 'add_submenu' ], 125 );
        add_action( 'dt_utilities_tab_menu', [ $this, 'add_tab' ], 125, 1 );
        add_action( 'dt_utilities_tab_content', [ $this, 'content' ], 125, 1 );

        parent::__construct();
    } // End __construct()

    public function add_submenu(){
        add_submenu_page( 'edit.php?post_type=imports', __( 'Imports', 'disciple_tools' ), __( 'Imports', 'disciple_tools' ), 'manage_dt', 'dt_utilities&tab=imports', [
            'Disciple_Tools_Settings_Menu',
            'content'
        ] );
        add_submenu_page( 'dt_utilities', __( 'Imports', 'disciple_tools' ), __( 'Imports', 'disciple_tools' ), 'manage_dt', 'dt_utilities&tab=imports', [
            'Disciple_Tools_Settings_Menu',
            'content'
        ] );
    }

    public function add_tab( $tab ){
        echo '<a href="' . esc_url( admin_url() ) . 'admin.php?page=dt_utilities&tab=imports" class="nav-tab ';
        if ( $tab == 'imports' ){
            echo 'nav-tab-active';
        }
        echo '">' . esc_attr__( 'Imports' ) . '</a>';
    }

    public function content( $tab ){
        if ( 'imports' == $tab ) :

            $this->template( 'begin' );

            $this->process_import();
            $this->display_services();

            $this->template( 'right_column' );

            $this->template( 'end' );

        endif;
    }

    private function process_import(){
        if ( isset( $_POST['dt_import_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dt_import_nonce'] ) ), 'dt_import_nonce' ) ){
        }
    }

    private function display_services(){

        $this->box( 'top', 'Available Import Services', [ 'col_span' => 4 ] );

        ?>
        <form method="POST">
            <input type="hidden" name="dt_import_nonce" id="dt_import_nonce"
                   value="<?php echo esc_attr( wp_create_nonce( 'dt_import_nonce' ) ) ?>"/>
            <table class="widefat striped">
                <tbody>
                <?php
                $import_services = apply_filters( 'dt_import_services', [] );
                foreach ( $import_services as $id => $service ){
                    if ( isset( $service['id'], $service['enabled'], $service['label'] ) && $service['enabled'] ){
                        ?>
                        <tr>
                            <td style="text-align: right;">
                                <input type="checkbox" name="services[<?php esc_html_e( $service['id'] ) ?>]"/>
                            </td>
                            <td>
                                <?php esc_html_e( $service['label'] ) ?><br>
                                <span style="font-size: 10px; color: #9a9797;">
                                    <?php esc_html_e( $service['description'] ?? '' ) ?>
                                </span>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
            <br>
            <span style="float:right;">
                <button type="submit"
                        class="button float-right"><?php esc_html_e( 'Import', 'disciple_tools' ) ?></button>
            </span>
        </form>
        <?php

        $this->box( 'bottom' );
    }

}

Disciple_Tools_Tab_Imports::instance();
