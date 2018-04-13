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

            $this->select_location_levels_to_record();

            $this->template( 'right_column' );

            $this->template( 'end' );

        endif;
    }

    public static function admin_levels_array() {
        // @note Changes here might need to be reflected in the activation() in disciple-tools-zume.php
        return [
            'country' => 'Country (recommended)',
            'administrative_area_level_1' => 'Admin Level 1 (ex. state / province) (recommended)',
            'administrative_area_level_2' => 'Admin Level 2',
            'administrative_area_level_3' => 'Admin Level 3',
            'administrative_area_level_4' => 'Admin Level 4',
            'locality' => 'Locality (ex. city name) (recommended)',
            'neighborhood' => 'Neighborhood'
        ];
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
    }
}
Disciple_Tools_Tab_Locations::instance();