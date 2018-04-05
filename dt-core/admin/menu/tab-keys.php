<?php

/**
 * Disciple_Tools_Keys_Tab
 *
 * @class      Disciple_Tools_Keys_Tab
 * @package    Disciple_Tools
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Keys_Tab
 */
class Disciple_Tools_Keys_Tab
{
    /**
     * Packages and returns tab page
     *
     * @return void
     */
    public function content()
    {
        ?>
        <form method="post">

            <div class="wrap">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-2">
                        <div id="post-body-content">

                            <?php $this->google_map_api_key_metabox() ?>

                        </div><!-- end post-body-content -->
                        <div id="postbox-container-1" class="postbox-container">
                        </div><!-- postbox-container 1 -->
                        <div id="postbox-container-2" class="postbox-container">
                        </div><!-- postbox-container 2 -->
                    </div><!-- post-body meta box container -->
                </div>
                <!--poststuff end -->
            </div><!-- wrap end -->
        </form>
        <?php
    }

    public function google_map_api_key_metabox() {

        if ( isset( $_POST['map_key'.get_current_user_id()] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['map_key'.get_current_user_id()] ) ), 'map_key_'.get_current_user_id().'_nonce' ) ) {
            if ( ! empty( $_POST['map_key'] ) ) {
                update_option( 'dt_map_key', trim( sanitize_text_field( wp_unslash( $_POST['map_key'] ) ) ) );
            }
        }

        $current_key = dt_get_option( 'map_key' );

        ?>
        <form method="post">
            <?php wp_nonce_field( 'map_key_'.get_current_user_id().'_nonce', 'map_key'.get_current_user_id() ) ?>
            <table class="widefat striped">
                <thead><th colspan="2">Google Maps API Key</th></thead>
                <tbody>
                    <tr>
                        <td>
                            <label>Google Maps API Key</label>
                        </td>
                        <td>
                            <input type="text" name="map_key" id="map_key" style="width: 100%;" value="<?php echo esc_attr(
                            $current_key )
                            ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <br><span style="float:right;"><button type="submit" class="button float-right">Save</button> </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>

        <?php
    }

}
