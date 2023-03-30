<?php
if ( !defined( 'ABSPATH' ) ){
    exit; // Exit if accessed directly
}

// Determine if export downloadable package is available for processing.
if ( isset( $_POST['dt_export_downloadable_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dt_export_downloadable_nonce'] ) ), 'dt_export_downloadable_nonce' ) ){
    if ( isset( $_POST['dt_export_downloadable_object'], $_POST['dt_export_downloadable_filename'] ) ){

        // Determine filename and extract downloadable content.
        $filename = sanitize_text_field( wp_unslash( $_POST['dt_export_downloadable_filename'] ) );
        $decoded_base64 = base64_decode( sanitize_text_field( wp_unslash( $_POST['dt_export_downloadable_object'] ) ) );

        // Create temporary file to house content.
        $json_file = tmpfile();
        $json_file_path = stream_get_meta_data( $json_file )['uri'];
        $saved_bytes = file_put_contents( $json_file_path, $decoded_base64 );

        // Inform browser as to what's coming down the pipeline...!
        header( 'Content-Type: application/json' );
        header( 'Content-Length: ' . filesize( $json_file_path ) );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        readfile( $json_file_path );

        // Ensure no extra header html is downloaded!
        exit();
    }
}

/**
 * Class Disciple_Tools_Tab_Exports
 */
class Disciple_Tools_Tab_Exports extends Disciple_Tools_Abstract_Menu_Base{
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
        add_submenu_page( 'edit.php?post_type=exports', __( 'Exports', 'disciple_tools' ), __( 'Exports', 'disciple_tools' ), 'manage_dt', 'dt_utilities&tab=exports', [
            'Disciple_Tools_Settings_Menu',
            'content'
        ] );
        add_submenu_page( 'dt_utilities', __( 'Exports', 'disciple_tools' ), __( 'Exports', 'disciple_tools' ), 'manage_dt', 'dt_utilities&tab=exports', [
            'Disciple_Tools_Settings_Menu',
            'content'
        ] );
    }

    public function add_tab( $tab ){
        echo '<a href="' . esc_url( admin_url() ) . 'admin.php?page=dt_utilities&tab=exports" class="nav-tab ';
        if ( $tab == 'exports' ){
            echo 'nav-tab-active';
        }
        echo '">' . esc_attr__( 'Exports' ) . '</a>';
    }

    public function content( $tab ){
        if ( 'exports' == $tab ) :

            $this->template( 'begin' );

            $this->process_export();
            $this->display_services();

            $this->template( 'right_column' );

            $this->template( 'end' );

        endif;
    }

    private function process_export(){
        if ( isset( $_POST['dt_export_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dt_export_nonce'] ) ), 'dt_export_nonce' ) ){
            if ( isset( $_POST['dt_export_selected_services'] ) ){

                // Extract selected services to be exported.
                $services = json_decode( sanitize_text_field( wp_unslash( $_POST['dt_export_selected_services'] ) ), true );

                // Assuming services have been selected, proceed with export payload generation.
                if ( !empty( $services ) ){
                    $export_payload = apply_filters( 'dt_export_payload', [
                        'services' => $services,
                        'payload' => []
                    ] );

                    // Assuming valid export payloads have been returned, force a download.
                    if ( !empty( $export_payload['payload'] ) ){

                        // Take a quick snapshot of existing plugins.
                        $plugins = get_plugins();
                        $active_plugins = get_option( 'active_plugins', [] );
                        foreach ( get_site_option( 'active_sitewide_plugins', [] ) as $plugin => $time ){
                            $active_plugins[] = $plugin;
                        }

                        $filtered_plugins = [];
                        foreach ( $plugins as $i => $v ){
                            if ( isset( $v['Name'], $v['Version'] ) ){
                                $filtered_plugins[] = [
                                    'id' => $i,
                                    'name' => $v['Name'],
                                    'version' => $v['Version'],
                                    'active' => in_array( $i, $active_plugins )
                                ];
                            }
                        }

                        // Package into a downloadable object.
                        $downloadable = [
                            'site_meta' => [
                                'timestamp' => time(),
                                'wp_version' => get_bloginfo( 'version' ),
                                'php_version' => phpversion(),
                                'server' => isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '',
                                'dt_version' => wp_get_theme()->version,
                                'site_url' => get_site_url(),
                                'multisite' => is_multisite(),
                                'plugins' => $filtered_plugins
                            ],
                            'dt_settings' => $export_payload['payload'],
                            'wp_settings' => [],
                            'multisite_settings' => []
                        ];

                        $dt_export_downloadable_filename = 'dt-' . time() . '.json';
                        ?>
                        <span>Downloaded: <?php echo esc_attr( $dt_export_downloadable_filename ) ?></span>
                        <form id="dt_export_downloadable_form" method="POST">

                            <input type="hidden" name="dt_export_downloadable_nonce"
                                   value="<?php echo esc_attr( wp_create_nonce( 'dt_export_downloadable_nonce' ) ) ?>"/>

                            <input type="hidden" name="dt_export_downloadable_object"
                                   value="<?php echo esc_attr( base64_encode( wp_json_encode( $downloadable, JSON_PRETTY_PRINT ) ) ) ?>">

                            <input type="hidden" name="dt_export_downloadable_filename"
                                   value="<?php echo esc_attr( $dt_export_downloadable_filename ) ?>">

                        </form>
                        <script type="text/javascript">
                            document.getElementById('dt_export_downloadable_form').submit();
                        </script>
                        <?php
                    } else {
                        echo esc_attr( __( 'Unable to detect any suitable service settings to export.', 'disciple_tools' ) );
                        exit();
                    }
                }
            }
        }
    }

    private function display_services(){

        $this->box( 'top', 'Available Export Services', [ 'col_span' => 4 ] );

        ?>
        <p>
            Select services below, to be exported into a json configuration file. A Full export is a deep extraction of a service's entire settings; whilst a Partial extraction will only contain recent customizations.
        </p>
        <form id="dt_export_form" method="POST">
            <input type="hidden" name="dt_export_nonce" id="dt_export_nonce"
                   value="<?php echo esc_attr( wp_create_nonce( 'dt_export_nonce' ) ) ?>"/>

            <input type="hidden" name="dt_export_selected_services" id="dt_export_selected_services"/>

            <table class="widefat striped" id="dt_export_table">
                <thead>
                    <tr>
                        <th style="text-align: right; padding-right: 14px;"></th>
                        <th></th>
                        <th style="text-align: center; font-size: 12px; padding-right: 22px;">
                            <label for="dt_export_service_select_full">Full</label><br>
                            <input type="radio" id="dt_export_service_select_full"
                                   name="dt_export_service_select_th_option"
                                   class="dt-export-service-select-th-option"
                                   data-select_type="full"/>
                        </th>
                        <th style="text-align: center; font-size: 12px; padding-right: 22px;">
                            <label for="dt_export_service_select_partial">Partial</label><br>
                            <input type="radio" id="dt_export_service_select_partial"
                                   name="dt_export_service_select_th_option"
                                   class="dt-export-service-select-th-option"
                                   data-select_type="partial"
                                   checked/>
                        </th>
                        <th style="text-align: center; font-size: 12px; padding-right: 22px;">
                            <label for="dt_export_service_select_none">None</label><br>
                            <input type="radio" id="dt_export_service_select_none"
                                   name="dt_export_service_select_th_option"
                                   class="dt-export-service-select-th-option"
                                   data-select_type="none"/>
                        </th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $export_services = apply_filters( 'dt_export_services', [] );
                foreach ( $export_services as $id => $service ){
                    if ( isset( $service['id'], $service['enabled'], $service['label'] ) && $service['enabled'] ){
                        ?>
                        <tr>
                            <td style="text-align: right;"></td>
                            <td>
                                <?php echo esc_attr( $service['label'] ) ?><br>
                                <span style="font-size: 10px; color: #9a9797;">
                                    <?php echo esc_attr( $service['description'] ?? '' ) ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <input type="radio" class="dt-export-service-select-td-option"
                                       name="dt_export_service_select_td_option_<?php echo esc_attr( $service['id'] ) ?>"
                                       data-service_id="<?php echo esc_attr( $service['id'] ) ?>"
                                       data-select_type="full"/>
                            </td>
                            <td style="text-align: center;">
                                <input type="radio" class="dt-export-service-select-td-option"
                                       name="dt_export_service_select_td_option_<?php echo esc_attr( $service['id'] ) ?>"
                                       data-service_id="<?php echo esc_attr( $service['id'] ) ?>"
                                       data-select_type="partial"
                                       checked/>
                            </td>
                            <td style="text-align: center;">
                                <input type="radio" class="dt-export-service-select-td-option"
                                       name="dt_export_service_select_td_option_<?php echo esc_attr( $service['id'] ) ?>"
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
                <button id="dt_export_submit_but" type="submit"
                        class="button float-right"><?php esc_html_e( 'Export', 'disciple_tools' ) ?></button>
            </span>
        </form>
        <?php

        $this->box( 'bottom' );
    }

}

Disciple_Tools_Tab_Exports::instance();
