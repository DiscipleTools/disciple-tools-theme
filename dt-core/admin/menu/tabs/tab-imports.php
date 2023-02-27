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

            // Determine which import view is to be shown/handled.
            if ( $this->file_upload_detected_and_correct() ){

                // Convert uploaded file into a usable configuration object.
                $uploaded_config = [];
                $uploaded_file_contents = file_get_contents( sanitize_text_field( wp_unslash( $_FILES['dt_import_file_upload_prompt_file']['tmp_name'] ) ) );
                if ( $uploaded_file_contents ){
                    $uploaded_config = json_decode( $uploaded_file_contents, true );
                }

                // Have user select services to be imported, based on state of uploaded config.
                $this->display_services( $uploaded_config );
                $this->template( 'right_column' );
                $this->display_service_details( $uploaded_config );

            } else if ( $this->import_request_detected() && isset( $_POST['dt_import_uploaded_config'], $_POST['dt_import_selected_services'] ) ){

                $uploaded_config = json_decode( base64_decode( sanitize_text_field( wp_unslash( $_POST['dt_import_uploaded_config'] ) ) ), true );
                $selected_services = json_decode( sanitize_text_field( wp_unslash( $_POST['dt_import_selected_services'] ) ), true );

                // Dispatch import request to respective listeners.
                do_action( 'dt_import_payload', $selected_services, $uploaded_config );

            } else{

                // Default view, prompting user to upload configuration file.
                $this->display_file_upload_prompt();
            }

            $this->template( 'end' );

        endif;
    }

    private function file_upload_detected_and_correct(): bool{
        if ( isset( $_POST['dt_import_file_upload_prompt_nonce'], $_FILES['dt_import_file_upload_prompt_file'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dt_import_file_upload_prompt_nonce'] ) ), 'dt_import_file_upload_prompt_nonce' ) ){
            if ( isset( $_FILES['dt_import_file_upload_prompt_file']['type'], $_FILES['dt_import_file_upload_prompt_file']['size'] ) ){
                return ( ( strpos( $_FILES['dt_import_file_upload_prompt_file']['type'], 'json' ) !== false ) && ( $_FILES['dt_import_file_upload_prompt_file']['size'] > 0 ) );
            }
        }

        return false;
    }

    private function import_request_detected(): bool{
        return ( isset( $_POST['dt_import_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dt_import_nonce'] ) ), 'dt_import_nonce' ) );
    }

    private function display_file_upload_prompt(){
        $this->box( 'top', 'Upload D.T Import Configuration File', [ 'col_span' => 4 ] );
        ?>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="dt_import_file_upload_prompt_nonce" id="dt_import_file_upload_prompt_nonce"
                   value="<?php echo esc_attr( wp_create_nonce( 'dt_import_file_upload_prompt_nonce' ) ) ?>"/>

            <table class="widefat striped">
                <tbody>
                <tr>
                    <td>
                        <input type="file" name="dt_import_file_upload_prompt_file"
                               id="dt_import_file_upload_prompt_file">
                    </td>
                </tr>
                </tbody>
            </table>
            <br>
            <span style="float:right;">
                <button type="submit"
                        class="button float-right"><?php esc_html_e( 'Upload File', 'disciple_tools' ) ?></button>
            </span>
        </form>
        <?php
        $this->box( 'bottom' );
    }

    private function display_services( $uploaded_config ){

        $this->box( 'top', 'Available Import Services', [ 'col_span' => 4 ] );

        ?>
        <form id="dt_import_form" method="POST">

            <!-- Capture uploaded import configuration, for further downstream processing. -->
            <div id="dt_import_uploaded_config_raw" style="display: none;">
                <?php echo esc_attr( base64_encode( wp_json_encode( $uploaded_config, JSON_PRETTY_PRINT ) ) ) ?>
            </div>

            <input type="hidden" name="dt_import_nonce" id="dt_import_nonce"
                   value="<?php echo esc_attr( wp_create_nonce( 'dt_import_nonce' ) ) ?>"/>

            <input type="hidden" name="dt_import_uploaded_config" id="dt_import_uploaded_config"/>
            <input type="hidden" name="dt_import_selected_services" id="dt_import_selected_services"/>

            <table class="widefat striped" id="dt_import_table">
                <tbody>
                <?php
                $import_services = apply_filters( 'dt_import_services', [] );
                foreach ( $import_services as $id => $service ){

                    // Only display enabled services; which are also present within uploaded import configuration.
                    if ( isset( $service['id'], $service['enabled'], $service['label'], $uploaded_config['payload'], $uploaded_config['payload'][$id] ) && $service['enabled'] ){
                        ?>
                        <tr>
                            <td style="text-align: right;">
                                <input type="checkbox" class="dt-import-service-checkbox" data-service_id="<?php echo esc_attr( $service['id'] ) ?>"/>
                            </td>
                            <td>
                                <a href="#" class="dt-import-service" data-service_id="<?php echo esc_attr( $service['id'] ) ?>"><?php echo esc_attr( $service['label'] ) ?></a><br>
                                <span style="font-size: 10px; color: #9a9797;">
                                    <?php echo esc_attr( $service['description'] ?? '' ) ?>
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
                <button id="dt_import_submit_but" type="submit"
                        class="button float-right"><?php esc_html_e( 'Import', 'disciple_tools' ) ?></button>
            </span>
        </form>
        <?php

        $this->box( 'bottom' );
    }

    private function display_service_details( $uploaded_config ){
        $this->box( 'top', 'Service Details', [ 'col_span' => 3 ] );

        $import_services_details = apply_filters( 'dt_import_services_details', [], $uploaded_config );
        foreach ( $import_services_details as $id => $service ){
            if ( isset( $service['id'], $service['enabled'], $service['html'], $service['html_js_handler_func'] ) && $service['enabled'] ){
                ?>
                <div class="dt-import-service-details" data-service_id="<?php echo esc_attr( $service['id'] ) ?>"
                     style="display: none;">
                    <?php echo $service['html'] ?>
                </div>
                <div class="dt-import-service-details-js-handler-func"
                     data-service_id="<?php echo esc_attr( $service['id'] ) ?>"
                     style="display: none;">
                    <?php echo $service['html_js_handler_func'] ?>
                </div>
                <?php
            }
        }

        $this->box( 'bottom' );
    }

}

Disciple_Tools_Tab_Imports::instance();
