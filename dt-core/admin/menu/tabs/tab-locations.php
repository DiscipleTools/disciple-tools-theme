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
        add_action( 'admin_menu', [ $this, 'add_submenu' ], 99 );
        add_action( 'dt_settings_tab_menu', [ $this, 'add_tab' ], 99, 1 );
        add_action( 'dt_settings_tab_content', [ $this, 'content' ], 99, 1 );

        parent::__construct();
    } // End __construct()

    public function add_submenu() {
        add_submenu_page( 'dt_options', __( 'Locations', 'disciple_tools' ), __( 'Locations', 'disciple_tools' ), 'manage_dt', 'dt_options&tab=locations', [ 'Disciple_Tools_Settings_Menu', 'content' ] );
    }

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

            if ( dt_get_option( 'auto_location' ) ) :
                $this->select_location_levels_to_record();
                $this->import_geocoded_list_of_locations();
            else :
                $this->import_simple_list_of_locations();
            endif;

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
                        <option value="0" <?php echo $auto_location ? 'selected' : '' ?>>Manually Build Locations</option>
                        <option value="1" <?php echo $auto_location ? 'selected' : '' ?>>Auto GeoCode and Build Location Levels</option>
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

    public function import_geocoded_list_of_locations() {

        if ( isset( $_POST['dt_import_levels_nonce'] ) && ! empty( $_POST['dt_import_levels_nonce'] )
            && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dt_import_levels_nonce'] ) ), 'dt_import_levels'. get_current_user_id() ) ) {

            unset( $_POST['dt_import_levels_nonce'] );

            // parse and sanitize list
            $list = explode( "\n", $_POST['import-list'] );
            $items = array_filter( array_map( 'sanitize_text_field', wp_unslash( $list ) ) );

            $country = sanitize_text_field( wp_unslash( $_POST['country'] ) );

            $geocode = new Disciple_Tools_Google_Geocode_API();

            $results = [];
            foreach ( $items as $item ) {
                $raw = $geocode::query_google_api_with_components( $item, [ 'country' => $country ] );
                if ( $geocode::check_valid_request_result( $raw ) ) {
                    $results[$item] = Disciple_Tools_Locations::auto_build_location( $raw, 'raw' );
                }
            }
        }

        $this->box( 'top', 'Import List', [
            'col_span' => 1,
            'row_container' => false
        ] );

        // get country list
        $countries = [ '00' => 'No Countries Loaded' ];
        $file = file_get_contents( Disciple_Tools::instance()->theme_path . 'dt-locations/sources/countries.json' );
        if ( $file ) {
            $countries = json_decode( $file );
        }
        ?>

        <form method="post" action="">
            <?php wp_nonce_field( 'dt_import_levels'. get_current_user_id(), 'dt_import_levels_nonce', false, true ) ?>
            <tr class="dt_import_levels">
                <td>
                    Auto import a simple list of locations/addresses. One address per line.
                </td>
            </tr>
            <tr class="dt_import_levels">
                <td>
                    <label>Country</label><br>
                    <select name="country" id="country">
                        <option></option>
                        <?php
                        foreach ( $countries as $key => $label ) {

                            echo '<option value="'.esc_attr( $key ).'">' . esc_html( $label ) . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>

            <tr class="dt_import_levels">
                <td>
                    <label>List of Locations</label>
                    <textarea name="import-list" id="import-list" rows="10" style="width:100%;"></textarea>
                </td>
            </tr>
            <tr class="dt_import_levels">
                <td>
                    <button class="button" type="button" onclick="import_list()" style="float:right"><?php esc_html_e( 'Import' ) ?></button><br>
                </td>
            </tr>
            <tr class="dt_import_levels" style="display: none;">
                <td>
                    <div id="spinner"></div><br>
                    <div id="results-import-list"></div>
                    <br><br>
                </td>
            </tr>
        </form>

        <?php
        $this->box( 'bottom' );
    }

    public function import_simple_list_of_locations() {

        $this->box( 'top', 'Import List', [
            'col_span' => 1,
            'row_container' => false
        ] );

        ?>

        <form method="post" action="">
            <?php wp_nonce_field( 'dt_simple_import_levels'. get_current_user_id(), 'dt_simple_import_levels_nonce', false, true ) ?>
            <tr class="dt_simple_import_levels">
                <td>
                    <p>Import a list of simple location titles. One location per line.</p>
                    <em>Note: These will not be geocoded. To import a geocoded list, select "Auto GeoCode and Build Location Levels" in the Auto Build Locations box.</em>
                </td>
            </tr>
            <tr class="dt_simple_import_levels">
                <td>
                    <label>List of Locations</label>
                    <textarea name="import-list" id="import-list" rows="10" style="width:100%;"></textarea>
                </td>
            </tr>
            <tr class="dt_simple_import_levels">
                <td>
                    <button class="button" type="button" onclick="import_simple_list()" style="float:right"><?php esc_html_e( 'Import' ) ?></button><br>

                </td>
            </tr>
            <tr class="dt_simple_import_levels" style="display: none;">
                <td>
                    <div id="spinner"></div><br>
                    <div id="results"></div>
                    <br><br>
                </td>
            </tr>

        </form>

        <?php
        $this->box( 'bottom' );
    }
}
Disciple_Tools_Tab_Locations::instance();