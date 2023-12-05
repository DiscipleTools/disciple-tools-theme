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

        add_filter( 'dt_import_new_post_types', [ $this, 'import_new_post_types' ], 10, 2 );

        parent::__construct();
    } // End __construct()

    public function import_new_post_types( $post_types, $imported_config ) {

        $import_config_json_id = 'dt_settings';
        $dt_post_type_config_json_id = 'dt_post_type_settings';

        // Fetch list of existing instance post types.
        $existing_post_types = DT_Posts::get_post_types() ?? [];

        // Identify any new incoming post types.
        if ( isset( $imported_config[$import_config_json_id][$dt_post_type_config_json_id] ) ) {
            foreach ( $imported_config[$import_config_json_id][$dt_post_type_config_json_id]['values'] ?? [] as $post_type => $post_type_settings ) {
                if ( !in_array( $post_type, $existing_post_types ) ) {
                    $post_types[$post_type] = $post_type_settings;
                }
            }
        }

        return $post_types;
    }

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
            if ( $this->file_upload_detected_and_correct() && isset( $_FILES['dt_import_file_upload_prompt_file']['tmp_name'] ) ){

                // Convert uploaded file into a usable configuration object.
                $uploaded_config = [];
                $uploaded_file_contents = file_get_contents( sanitize_text_field( wp_unslash( $_FILES['dt_import_file_upload_prompt_file']['tmp_name'] ) ) );
                if ( $uploaded_file_contents ){
                    $uploaded_config = json_decode( $uploaded_file_contents, true );
                }

                // Have user select services to be imported, based on state of uploaded config.
                $this->display_import_sections( $uploaded_config );
                //...$this->template( 'right_column' );
                //...$this->display_service_details( $uploaded_config );

            }/* else if ( $this->import_request_detected() && isset( $_POST['dt_import_uploaded_config'], $_POST['dt_import_selected_services'], $_POST['dt_import_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dt_import_nonce'] ) ), 'dt_import_nonce' ) ){

                $uploaded_config = json_decode( base64_decode( sanitize_text_field( wp_unslash( $_POST['dt_import_uploaded_config'] ) ) ), true );
                $selected_services = json_decode( sanitize_text_field( wp_unslash( $_POST['dt_import_selected_services'] ) ), true );

                // If no services have been selected, then revert back to default upload prompt.
                if ( !empty( $selected_services ) ){

                    // Ensure selected post types not already on the system, are installed ahead of import-payload request.
                    $new_post_types = $this->import_new_post_types( [], $uploaded_config );
                    if ( !empty( $new_post_types ) ) {

                        // Iterate over new post types, ensuring they do not already exist, before creating.
                        $existing_post_types = DT_Posts::get_post_types() ?? [];
                        $custom_post_types = get_option( 'dt_custom_post_types', [] );

                        foreach ( $new_post_types as $post_type => $post_type_settings ) {
                            if ( !in_array( $post_type, $existing_post_types ) && !isset( $custom_post_types[$post_type] ) ) {

                                // If all sanity checks have passed, proceed with custom post type creation.
                                $custom_post_types[$post_type] = [
                                    'label_singular' => $post_type_settings['label_singular'] ?? $post_type,
                                    'label_plural' => $post_type_settings['label_plural'] ?? $post_type,
                                    'hidden' => $post_type_settings['hidden'] ?? false,
                                    'is_custom' => $post_type_settings['is_custom'] ?? true
                                ];
                            }
                        }

                        // Update custom post types with any newly identified.
                        update_option( 'dt_custom_post_types', $custom_post_types );
                    }

                    // Dispatch import request to respective listeners.
                    do_action( 'dt_import_payload', $selected_services, $uploaded_config );

                } else {

                    // Default view, prompting user to upload configuration file.
                    $this->display_file_upload_prompt();
                }
            }*/ else {

                // Default view, prompting user to upload configuration file.
                $this->display_file_upload_prompt();
}

            $this->template( 'end' );

        endif;
    }

    private function file_upload_detected_and_correct(): bool{
        if ( isset( $_POST['dt_import_file_upload_prompt_nonce'], $_FILES['dt_import_file_upload_prompt_file'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dt_import_file_upload_prompt_nonce'] ) ), 'dt_import_file_upload_prompt_nonce' ) ){
            if ( isset( $_FILES['dt_import_file_upload_prompt_file']['type'], $_FILES['dt_import_file_upload_prompt_file']['size'] ) ){
                return ( ( strpos( sanitize_text_field( wp_unslash( $_FILES['dt_import_file_upload_prompt_file']['type'] ) ), 'json' ) !== false ) && ( intval( sanitize_text_field( wp_unslash( $_FILES['dt_import_file_upload_prompt_file']['size'] ) ) ) > 0 ) );
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
        <p>
            Select a previously exported json configuration file.
        </p>
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

    private function display_import_sections( $uploaded_config ){
        dt_write_log( $uploaded_config );
        ?>
        <!-- Capture uploaded import configuration, for further downstream processing. -->
        <div id="dt_import_uploaded_config_raw" style="display: none;">
            <?php echo esc_attr( base64_encode( wp_json_encode( $uploaded_config, JSON_PRETTY_PRINT ) ) ) ?>
        </div>
        <?php

        $this->box( 'top', 'Importing Record Types', [ 'col_span' => 12 ] );

        // Determine post type config settings key to be used.
        $dt_post_types_settings_key = null;
        if ( isset( $uploaded_config['dt_settings'] ) ) {
            $dt_settings_keys = array_keys( $uploaded_config['dt_settings'] );
            if ( in_array( 'dt_post_types_settings', $dt_settings_keys ) ) {
                $dt_post_types_settings_key = 'dt_post_types_settings';
            } else if ( in_array( 'dt_post_types_custom_settings', $dt_settings_keys ) ) {
                $dt_post_types_settings_key = 'dt_post_types_custom_settings';
            }
        }

        if ( isset( $dt_post_types_settings_key, $uploaded_config['dt_settings'][$dt_post_types_settings_key]['values'] ) ) {
            $existing_post_types = DT_Posts::get_post_types();
            ?>
            <p>
                Select record types to be imported. Existing record types, are automatically excluded from imports, unless manually included.
            </p>
            <table>
                <tbody>
                    <tr>
                    <?php
                    foreach ( $uploaded_config['dt_settings'][$dt_post_types_settings_key]['values'] as $post_type => $post_type_settings ) {
                        if ( isset( $post_type_settings['label_plural'] ) ) {
                            $post_type_exists = in_array( $post_type, $existing_post_types );
                            ?>
                            <td style="text-align: center;">
                                <button class="button dt-import-post-type-but"
                                        data-post_type="<?php echo esc_attr( $post_type ); ?>"
                                        <?php echo esc_attr( ( $post_type_exists ? 'disabled' : '' ) ); ?>><?php echo esc_attr( $post_type_settings['label_plural'] ); ?></button>
                                <input  type="checkbox"
                                        class="dt-import-post-type-checkbox"
                                        style="margin-top: 10px;"
                                        data-post_type="<?php echo esc_attr( $post_type ); ?>"
                                        <?php echo esc_attr( ( $post_type_exists ? '' : 'checked' ) ); ?>
                                />
                            </td>
                            <?php
                        }
                    }
                    ?>
                    </tr>
                </tbody>
            </table>
            <?php
        } else {
            echo esc_attr( __( 'Unable to detect any suitable importing record types.', 'disciple_tools' ) );
        }
        $this->box( 'bottom' );

        ?>
        <div id="dt_import_post_type_meta_div" style="display: none;">
            <?php
            $this->box( 'top', 'Importing Record Type Details', [ 'col_span' => 12 ] );
            $this->box( 'bottom' );
            ?>
        </div>
        <div id="dt_import_tiles_div" style="display: none;">
            <?php
            $this->box( 'top', 'Importing Tiles', [ 'col_span' => 12 ] );
            $this->box( 'bottom' );
            ?>
        </div>
        <div id="dt_import_fields_div" style="display: none;">
            <?php
            $this->box( 'top', 'Importing Fields', [ 'col_span' => 12 ] );
            $this->box( 'bottom' );
            ?>
        </div>
        <?php
    }

    private function _display_services( $uploaded_config ){

        $this->box( 'top', 'Available Import Services', [ 'col_span' => 4 ] );

        ?>
        <p>
            Select services below, to be imported into current D.T instance. Click on relevant service title to display additional selection details.
        </p>
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
                <thead>
                    <tr>
                        <th style="text-align: right; padding-right: 14px;"></th>
                        <th></th>
                        <th style="text-align: center; font-size: 12px; padding-right: 22px;">
                            <label for="dt_import_service_select_all">All Assets</label><br>
                            <input type="radio" id="dt_import_service_select_all"
                                   name="dt_import_service_select_th_option"
                                   class="dt-import-service-select-th-option"
                                   data-select_type="all"
                                   checked/>
                        </th>
                        <th style="text-align: center; font-size: 12px; padding-right: 22px;">
                            <label for="dt_import_service_select_some">Choose Assets</label><br>
                            <input type="radio" id="dt_import_service_select_some"
                                   name="dt_import_service_select_th_option"
                                   class="dt-import-service-select-th-option"
                                   data-select_type="some"/>
                        </th>
                        <th style="text-align: center; font-size: 12px; padding-right: 22px;">
                            <label for="dt_import_service_select_none">None</label><br>
                            <input type="radio" id="dt_import_service_select_none"
                                   name="dt_import_service_select_th_option" class="dt-import-service-select-th-option"
                                   data-select_type="none"/>
                        </th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $import_services = apply_filters( 'dt_import_services', [] );
                foreach ( $import_services as $id => $service ){

                    // Only display enabled services; which are also present within uploaded import configuration.
                    if ( isset( $service['id'], $service['config_json_id'], $service['enabled'], $service['label'], $uploaded_config[$service['config_json_id']], $uploaded_config[$service['config_json_id']][$id] ) && $service['enabled'] ){
                        ?>
                        <tr>
                            <td style="text-align: right;"></td>
                            <td>
                                <a href="#" class="dt-import-service" data-service_id="<?php echo esc_attr( $service['id'] ) ?>"><?php echo esc_attr( $service['label'] ) ?></a><br>
                                <span style="font-size: 10px; color: #9a9797;">
                                    <?php echo esc_attr( $service['description'] ?? '' ) ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <input type="radio" class="dt-import-service-select-td-option"
                                       name="dt_import_service_select_td_option_<?php echo esc_attr( $service['id'] ) ?>"
                                       data-service_id="<?php echo esc_attr( $service['id'] ) ?>"
                                       data-select_type="all"
                                       checked/>
                            </td>
                            <td style="text-align: center;">
                                <input type="radio" class="dt-import-service-select-td-option"
                                       name="dt_import_service_select_td_option_<?php echo esc_attr( $service['id'] ) ?>"
                                       data-service_id="<?php echo esc_attr( $service['id'] ) ?>"
                                       data-select_type="some"/>
                            </td>
                            <td style="text-align: center;">
                                <input type="radio" class="dt-import-service-select-td-option"
                                       name="dt_import_service_select_td_option_<?php echo esc_attr( $service['id'] ) ?>"
                                       data-service_id="<?php echo esc_attr( $service['id'] ) ?>"
                                       data-select_type="none"/>
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

    private function _display_service_details( $uploaded_config ){
        $this->box( 'top', 'Service Details', [ 'col_span' => 3 ] );

        $import_services_details = apply_filters( 'dt_import_services_details', [], $uploaded_config );
        foreach ( $import_services_details as $id => $service ){
            if ( isset( $service['id'], $service['enabled'], $service['html'], $service['html_js_handler_func'], $service['html_js_selection_handler_func'] ) && $service['enabled'] ){
                ?>
                <div class="dt-import-service-details" data-service_id="<?php echo esc_attr( $service['id'] ) ?>"
                     style="display: none;">
                    <?php
                    //phpcs:disable
                    echo $service['html'];
                    //phpcs:enable
                    ?>
                </div>
                <div class="dt-import-service-details-js-handler-func"
                     data-service_id="<?php echo esc_attr( $service['id'] ) ?>"
                     style="display: none;">
                    <?php
                    //phpcs:disable
                    echo $service['html_js_handler_func'];
                    //phpcs:enable
                    ?>
                </div>
                <div class="dt-import-service-details-js-selection-handler-func"
                     data-service_id="<?php echo esc_attr( $service['id'] ) ?>"
                     style="display: none;">
                    <?php
                    //phpcs:disable
                    echo $service['html_js_selection_handler_func'];
                    //phpcs:enable
                    ?>
                </div>
                <?php
            }
        }

        $this->box( 'bottom' );
    }

}

Disciple_Tools_Tab_Imports::instance();
