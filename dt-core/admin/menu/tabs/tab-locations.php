<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_General_Tab
 */
class Disciple_Tools_Tab_Locations extends Disciple_Tools_Abstract_Menu_Base
{
    private static $_instance = null;
    public static function instance()
    {
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
    public function __construct()
    {
        add_action( 'dt_settings_tab_menu', [ $this, 'add_tab' ], 99, 1 );
        add_action( 'dt_settings_tab_content', [ $this, 'content' ], 99, 1 );

        parent::__construct();
    } // End __construct()

    public function add_tab( $tab ) {
        echo '<a href="'. esc_url( admin_url() ).'admin.php?page=dt_options&tab=locations" class="nav-tab ';
        if ( $tab == 'locations' ) {
            echo 'nav-tab-active';
        }
        echo '">Locations</a>';
    }

    public function content( $tab ) {
        if ( 'locations' == $tab ) :

            $this->template( 'begin' );

            $this->select_auto_locations();
            $this->select_location_levels_to_record();
            $this->import_simple_list_of_locations();

            $this->template( 'right_column' );

            $this->template( 'end' );

        endif;
    }

    public function select_auto_locations()
    {
        if ( isset( $_POST['dt_zume_auto_levels_nonce'] ) && ! empty( $_POST['dt_zume_auto_levels_nonce'] )
            && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dt_zume_auto_levels_nonce'] ) ), 'dt_zume_auto_levels'. get_current_user_id() ) ) {

            $setting = sanitize_text_field( wp_unslash( $_POST['auto_location'] ) );
            dt_update_option( 'auto_location', $setting, false );
        }

        $auto_location = dt_get_option( 'auto_location' );

        // Build metabox
        $this->box( 'top', 'Auto Build Locations', [
            'col_span' => 2,
            'row_container' => false
        ] );
        ?>

        <form method="post" action="">
            <?php wp_nonce_field( 'dt_zume_auto_levels'. get_current_user_id(), 'dt_zume_auto_levels_nonce', false, true ) ?>
            <tr>
                <td><label>Set Auto Locations</label></td>
                <td><select name="auto_location">
                        <option value="0" <?php echo $auto_location ? 'selected' : '' ?>>Build Location Depths Manually</option>
                        <option value="1" <?php echo $auto_location ? 'selected' : '' ?>>Build Locations Depths Automatically</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <button class="button" type="submit" style="float:right"><?php esc_html_e( 'Save' ) ?></button>
                </td>
            </tr>
        </form>

        <?php
        $this->box( 'bottom' );
        // end metabox
    }

    public function select_location_levels_to_record()
    {
        $list_array = dt_get_location_levels();
        $list_array = $list_array['location_levels_labels'];

        $settings = dt_get_option( 'location_levels' );

        // Check for post
        if ( isset( $_POST['dt_zume_select_levels_nonce'] ) && ! empty( $_POST['dt_zume_select_levels_nonce'] )
            && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dt_zume_select_levels_nonce'] ) ), 'dt_zume_select_levels'. get_current_user_id() ) ) {

            unset( $_POST['dt_zume_select_levels_nonce'] );

            foreach ( array_keys( $list_array ) as $key ) {
                if ( isset( $_POST[$key] ) ) {
                    $settings[$key] = 1;
                } else {
                    $settings[$key] = 0;
                }
            }

            dt_update_option( 'location_levels', $settings, false );
        }

        if ( dt_get_option( 'auto_location' ) ) : // if auto location is not set, hide level configuration

            $this->box( 'top', 'Select Levels for Auto Building Locations', [
                'col_span' => 2,
                'row_container' => false
            ] );
        ?>

        <form method="post" action="">
            <?php wp_nonce_field( 'dt_zume_select_levels'. get_current_user_id(), 'dt_zume_select_levels_nonce', false, true ) ?>
                <?php
                foreach ( $list_array as $item => $label ) : ?>
                    <tr>
                        <td>
                            <label for="<?php echo esc_attr( $item ) ?>"><?php echo esc_attr( $label ) ?></label>
                        </td>
                        <td>
                            <input type="checkbox" value="1" id="<?php echo esc_attr( $item ) ?>" name="<?php echo esc_html( $item ) ?>"
                                <?php isset( $settings[$item] ) && $settings[$item] == 1 ? print esc_html( 'checked' ) : print '' ?>
                            />
                        </td>
                    </tr>
                <?php endforeach; ?>

                <tr>
                    <td colspan="2">
                        <button class="button" type="submit" style="float:right"><?php esc_html_e( 'Save' ) ?></button>
                    </td>
                </tr>
        </form>

        <?php
        $this->box( 'bottom' );

        endif; // hide settings, if auto locations is set to manual
    }

    public function import_simple_list_of_locations() {

        if ( isset( $_POST['dt_import_levels_nonce'] ) && ! empty( $_POST['dt_import_levels_nonce'] )
            && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dt_import_levels_nonce'] ) ), 'dt_import_levels'. get_current_user_id() ) ) {

            unset( $_POST['dt_import_levels_nonce'] );

            $list = explode( "\n", $_POST['import-contents'] );
            $items = array_map( 'sanitize_text_field', wp_unslash( $list ) );


            dt_write_log( $items );


        }


        $this->box( 'top', 'Import List', [
            'col_span' => 1,
            'row_container' => false
        ] );
        ?>

        <form method="post" action="">
            <?php wp_nonce_field( 'dt_import_levels'. get_current_user_id(), 'dt_import_levels_nonce', false, true ) ?>
            <tr>
                <td>
                    Auto import a simple list of locations/addresses. One address per line.
                </td>
            </tr>
            <tr>
                <td>
                    <textarea name="import-contents" rows="10" style="width:100%;"></textarea>
                </td>
            </tr>
            <tr>
                <td>
                    <button class="button" type="submit" style="float:right"><?php esc_html_e( 'Import' ) ?></button>
                </td>
            </tr>
        </form>

        <?php
        $this->box( 'bottom' );
    }
}
Disciple_Tools_Tab_Locations::instance();