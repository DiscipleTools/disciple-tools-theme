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

        // Determine incoming import config settings keys to be used further down stream.
        $uploaded_config_setting_keys = $this->determine_uploaded_config_setting_keys( $imported_config );
        if ( isset( $uploaded_config_setting_keys['post_types_settings_key'] ) ){
            $import_config_json_id = 'dt_settings';

            // Fetch list of existing instance post types.
            $existing_post_types = DT_Posts::get_post_types() ?? [];

            // Identify any new incoming post types.
            if ( isset( $imported_config[$import_config_json_id][$uploaded_config_setting_keys['post_types_settings_key']] ) ){
                foreach ( $imported_config[$import_config_json_id][$uploaded_config_setting_keys['post_types_settings_key']]['values'] ?? [] as $post_type => $post_type_settings ){
                    if ( !in_array( $post_type, $existing_post_types ) ){
                        $post_types[$post_type] = $post_type_settings;
                    }
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

            } else if ( $this->import_request_detected() && isset( $_POST['dt_import_uploaded_config'], $_POST['dt_import_selections'], $_POST['dt_import_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dt_import_nonce'] ) ), 'dt_import_nonce' ) ){

                $uploaded_config = json_decode( base64_decode( sanitize_text_field( wp_unslash( $_POST['dt_import_uploaded_config'] ) ) ), true );
                $import_selections = json_decode( sanitize_text_field( wp_unslash( $_POST['dt_import_selections'] ) ), true );

                // If no import selections detected, then revert back to default upload prompt.
                if ( !empty( $uploaded_config ) && !empty( $import_selections ) ) {

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

                    // Process import request and display results summary.
                    $results = $this->process_imports( $uploaded_config, $import_selections );
                    $this->box( 'top', 'Import Summary', [ 'col_span' => 4 ] );
                    ?>
                    <p>
                        Summary of successfully imported record types, tiles and fields.
                    </p>
                    <br>
                    <?php

                    $uploaded_config_setting_keys = $this->determine_uploaded_config_setting_keys( $uploaded_config );
                    foreach ( $results as $post_type => $result ) {
                        if ( isset( $uploaded_config['dt_settings'][$uploaded_config_setting_keys['post_types_settings_key']]['values'][$post_type] ) ) {
                            $post_type_settings = $uploaded_config['dt_settings'][$uploaded_config_setting_keys['post_types_settings_key']]['values'][$post_type];
                            ?>
                            <span style="font-weight: bold;"><?php echo esc_attr( $post_type_settings['label_plural'] ?? $post_type ); ?></span>
                            <hr>
                            <?php

                            // Tiles result summary.
                            if ( !empty( $result['tiles'] ) ) {
                                ?>
                                <table class="widefat striped" style="margin-bottom: 10px;">
                                    <thead>
                                        <tr>
                                            <th>Tiles</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    foreach ( $result['tiles'] as $tile ) {
                                        ?>
                                        <tr>
                                            <td><?php echo esc_attr( $uploaded_config['dt_settings'][$uploaded_config_setting_keys['tiles_settings_key']]['values'][$post_type][$tile]['label'] ?? $tile ); ?></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    </tbody>
                                </table>
                                <?php
                            }

                            // Fields result summary.
                            if ( !empty( $result['fields'] ) ) {
                                ?>
                                <table class="widefat striped" style="margin-bottom: 10px;">
                                    <thead>
                                    <tr>
                                        <th>Fields</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    foreach ( $result['fields'] as $field ) {
                                        ?>
                                        <tr>
                                            <td><?php echo esc_attr( $uploaded_config['dt_settings'][$uploaded_config_setting_keys['fields_settings_key']]['values'][$post_type][$field]['name'] ?? $field ); ?></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    </tbody>
                                </table>
                                <?php
                            }
                        }
                    }

                    $this->box( 'bottom' );

                } else {

                    // Default view, prompting user to upload configuration file.
                    $this->display_file_upload_prompt();
                }
            } else {

                // Default view, prompting user to upload configuration file.
                $this->display_file_upload_prompt();
            }

            $this->template( 'end' );

        endif;
    }

    private function process_imports( $import_config, $import_selections ): array {
        $response = [];

        // Determine incoming import config settings keys to be used further down stream.
        $uploaded_config_setting_keys = $this->determine_uploaded_config_setting_keys( $import_config );
        if ( isset( $uploaded_config_setting_keys['tiles_settings_key'], $uploaded_config_setting_keys['fields_settings_key'], $uploaded_config_setting_keys['post_types_settings_key'] ) ){
            $existing_tile_options = dt_get_option( 'dt_custom_tiles' );
            $existing_field_options = dt_get_option( 'dt_field_customizations' );

            // Start iterating and processing selected post types.
            foreach ( $import_selections as $post_type => $tiles ) {
                $response[$post_type] = [
                    'tiles' => [],
                    'fields' => []
                ];

                // Post types should have already been created, so process tiles and build list of fields to be imported.
                $selected_fields = [];
                foreach ( $tiles as $tile => $fields ) {

                    // Ignore the no_tile special keyword.
                    if ( ( $tile !== 'no_tile' ) && isset( $import_config['dt_settings'][$uploaded_config_setting_keys['tiles_settings_key']]['values'][$post_type][$tile] ) ) {

                        // Make tile options provision if needed, before committing.
                        if ( !isset( $existing_tile_options[$post_type] ) ){
                            $existing_tile_options[$post_type] = [];
                        }
                        $existing_tile_options[$post_type][$tile] = $import_config['dt_settings'][$uploaded_config_setting_keys['tiles_settings_key']]['values'][$post_type][$tile];

                        // Capture tile id, to signal a successful import.
                        $response[$post_type]['tiles'][] = $tile;
                    }

                    // Capture tile fields for future processing.
                    $selected_fields = array_merge( $selected_fields, $fields );
                }

                // Next, import captured fields, for current post type.
                foreach ( $selected_fields as $field ) {

                    // Ensure a valid field import config can be referenced.
                    if ( isset( $import_config['dt_settings'][$uploaded_config_setting_keys['fields_settings_key']]['values'][$post_type][$field] ) ) {

                        // Make tile options provision if needed, before committing.
                        if ( !isset( $existing_field_options[$post_type] ) ){
                            $existing_field_options[$post_type] = [];
                        }
                        $existing_field_options[$post_type][$field] = $import_config['dt_settings'][$uploaded_config_setting_keys['fields_settings_key']]['values'][$post_type][$field];

                        // Capture field id, to signal a successful import.
                        $response[$post_type]['fields'][] = $field;
                    }
                }
            }

            // Update global custom settings.
            update_option( 'dt_custom_tiles', $existing_tile_options );
            update_option( 'dt_field_customizations', $existing_field_options );
        }

        return $response;
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

    private function determine_uploaded_config_setting_keys( $uploaded_config ) {
        $uploaded_config_setting_keys = [];
        if ( isset( $uploaded_config['dt_settings'] ) ) {
            foreach ( array_keys( $uploaded_config['dt_settings'] ) as $key ) {
                if ( isset( $uploaded_config['dt_settings'][$key]['values'] ) ) {
                    switch ( $key ) {
                        case 'dt_tiles_settings':
                        case 'dt_tiles_custom_settings':
                            $uploaded_config_setting_keys['tiles_settings_key'] = $key;
                            break;
                        case 'dt_fields_settings':
                        case 'dt_fields_custom_settings':
                            $uploaded_config_setting_keys['fields_settings_key'] = $key;
                            break;
                        case 'dt_post_types_settings':
                        case 'dt_post_types_custom_settings':
                            $uploaded_config_setting_keys['post_types_settings_key'] = $key;
                            break;
                    }
                }
            }
        }

        return $uploaded_config_setting_keys;
    }

    private function display_import_sections( $uploaded_config ){
        ?>
        <form id="dt_import_form" method="POST">
            <input type="hidden" name="dt_import_nonce" id="dt_import_nonce"
                   value="<?php echo esc_attr( wp_create_nonce( 'dt_import_nonce' ) ) ?>"/>

            <input type="hidden" name="dt_import_uploaded_config" id="dt_import_uploaded_config"/>
            <input type="hidden" name="dt_import_selections" id="dt_import_selections"/>
        </form>
        <?php
        $this->box( 'top', 'Record Types', [ 'col_span' => 12 ] );

        // Determine incoming import config settings keys to be used further down stream.
        $uploaded_config_setting_keys = $this->determine_uploaded_config_setting_keys( $uploaded_config );
        if ( isset( $uploaded_config_setting_keys['tiles_settings_key'], $uploaded_config_setting_keys['fields_settings_key'], $uploaded_config_setting_keys['post_types_settings_key'] ) ) {
            ?>

            <!-- Capture uploaded import configuration, for further downstream processing. -->
            <div id="dt_import_uploaded_config_raw" style="display: none;">
                <?php echo esc_attr( base64_encode( wp_json_encode( $uploaded_config, JSON_PRETTY_PRINT ) ) ) ?>
            </div>

            <!-- Prepare space to house import selections. -->
            <div id="dt_import_uploaded_config_selections" style="display: none;">
                {}
            </div>

            <!-- Capture existing system post types. -->
            <div id="dt_import_existing_post_types" style="display: none;">
                <?php echo esc_attr( wp_json_encode( array_values( DT_Posts::get_post_types() ), JSON_PRETTY_PRINT ) ) ?>
            </div>

            <!-- Capture import config setting keys. -->
            <div id="dt_import_config_setting_keys" style="display: none;">
                <?php echo esc_attr( wp_json_encode( $uploaded_config_setting_keys, JSON_PRETTY_PRINT ) ) ?>
            </div>

            <p>
                Ensure to select all record type tiles & fields to be included within import process.
            </p>
            <table>
                <tbody>
                    <tr>
                    <?php
                    $post_type_count = 0;
                    foreach ( $uploaded_config['dt_settings'][ $uploaded_config_setting_keys['post_types_settings_key'] ]['values'] as $post_type => $post_type_settings ) {
                        if ( isset( $post_type_settings['label_plural'] ) ) {
                            $post_type_count++;
                            ?>
                            <td style="text-align: center;">
                                <button class="button dt-import-post-type-but"
                                        data-post_type="<?php echo esc_attr( $post_type ); ?>"><?php echo esc_attr( $post_type_settings['label_plural'] ); ?></button>
                            </td>
                            <?php
                        }
                    }
                    ?>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="<?php echo esc_attr( $post_type_count ); ?>">
                            <button id="dt_import_submit_but" class="button" style="min-width: 100%; margin-top: 20px;">Import</button>
                        </td>
                    </tr>
                </tfoot>
            </table>
            <?php
        } else {
            echo esc_attr( __( 'Unable to detect any suitable import configuration settings.', 'disciple_tools' ) );
        }
        $this->box( 'bottom' );
        ?>

        <table>
            <tbody>
            <tr>
                <td style="min-width: 500px; vertical-align: top;">
                    <?php
                    $this->display_import_post_type_meta();
                    $this->display_import_tiles_fields();
                    ?>
                </td>
                <td style="min-width: 350px; vertical-align: top;">
                    <?php $this->display_import_selections(); ?>
                </td>
            </tr>
            </tbody>
        </table>

        <?php
    }

    private function display_import_post_type_meta() {
        ?>
        <div id="dt_import_post_type_meta_div" style="display: none;">
            <?php
            $this->box( 'top', 'Record Type Details', [ 'col_span' => 12 ] );
            ?>
            <table class="widefat striped">
                <tbody>
                <tr>
                    <td>
                        <span style="font-weight: bold;">Key</span>
                    </td>
                    <td id="dt_import_details_key_td" style="text-align: left;"></td>
                </tr>
                <tr>
                    <td>
                        <span style="font-weight: bold;">Already Installed</span>
                    </td>
                    <td id="dt_import_details_already_installed_td" style="text-align: left;"></td>
                </tr>
                <tr>
                    <td>
                        <span style="font-weight: bold;">Label Singular</span>
                    </td>
                    <td id="dt_import_details_label_singular_td" style="text-align: left;"></td>
                </tr>
                <tr>
                    <td>
                        <span style="font-weight: bold;">Label Plural</span>
                    </td>
                    <td id="dt_import_details_label_plural_td" style="text-align: left;"></td>
                </tr>
                <tr>
                    <td>
                        <span style="font-weight: bold;">Record Type</span>
                    </td>
                    <td id="dt_import_details_record_type_td" style="text-align: left;"></td>
                </tr>
                <tr>
                    <td style="width: 30%;">
                        <span style="font-weight: bold;">Import Tiles & Fields</span>
                    </td>
                    <td style="text-align: left;">
                        <input  id="dt_import_details_tiles_fields_checkbox"
                                type="checkbox"
                                data-post_type=""/>
                    </td>
                </tr>
                </tbody>
            </table>
            <?php
            $this->box( 'bottom' );
            ?>
        </div>
        <?php
    }

    private function display_import_tiles_fields() {
        ?>
        <div id="dt_import_tiles_fields_div" style="display: none;">
            <?php
            $this->box( 'top', 'Tiles & Fields', [ 'col_span' => 12 ] );
            ?>
            <div id="dt_import_tiles_fields_content_div" style="display: none;"></div>
            <?php
            $this->box( 'bottom' );
            ?>
        </div>
        <?php
    }

    private function display_import_selections() {
        ?>
        <div id="dt_import_selections_div" style="display: none;">
            <?php
            $this->box( 'top', 'Import Selections', [ 'col_span' => 12 ] );
            ?>
            <div id="dt_import_selections_content_div"></div>
            <?php
            $this->box( 'bottom' );
            ?>
        </div>
        <?php
    }
}

Disciple_Tools_Tab_Imports::instance();
