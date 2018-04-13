<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_General_Tab
 */
class Disciple_Tools_Locations_Tab
{
    public function content() {
        // begin columns template
        Disciple_Tools_Config::template( 'begin' );

        self::select_location_levels_to_record();

        // begin right column template
        Disciple_Tools_Config::template( 'right_column' );
        // end columns template
        Disciple_Tools_Config::template( 'end' );
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
            'neighborhood' => 'Neighborhood' ];
    }

    public static function select_location_levels_to_record()
    {
        $list_array = self::admin_levels_array();

        $settings = get_option('dt_zume_selected_location_levels');

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

            dt_write_log($settings);
            update_option('dt_zume_selected_location_levels', $settings, false );
        }


        ?>
        <form method="post" action="">
            <?php wp_nonce_field( 'dt_zume_select_levels'. get_current_user_id(), 'dt_zume_select_levels_nonce', false, true ) ?>

            <!-- Box -->
            <table class="widefat striped">
                <thead>
                <tr>
                    <td colspan="2">
                        <?php esc_html_e( 'Select Levels for Auto Building Locations' ) ?>
                    </td>
                </tr>
                </thead>
                <tbody>

                <?php

                foreach( $list_array as $item => $label ) : ?>
                    <tr>
                        <td>
                            <label for="<?php echo esc_attr( $item ) ?>"><?php echo esc_attr( $label ) ?></label>
                        </td>
                        <td>
                            <input type="checkbox" value="1" id="<?php echo esc_attr( $item ) ?>" name="<?php echo esc_html( $item ) ?>"
                                <?php  isset( $settings[$item] ) && $settings[$item] == 1  ? print esc_html('checked' ) : print '' ?>
                            />
                        </td>
                    </tr>
                <?php endforeach; ?>

                <tr>
                    <td colspan="2">
                        <button class="button" type="submit"><?php esc_html_e( 'Select Levels for Locations Integration' ) ?></button>
                    </td>
                </tr>
                </tbody>
            </table>
            <br>
            <!-- End Box -->



        </form>
        <?php
    }
}