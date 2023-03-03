<?php

/**
 * Disciple.Tools
 *
 * @class      Disciple_Tools_Tab_Custom_Fields
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple.Tools
 * @author     Disciple.Tools
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Tab_Custom_Fields
 */
class Disciple_Tools_Tab_Custom_Tiles extends Disciple_Tools_Abstract_Menu_Base
{

    private static $_instance = null;
    public static function instance() {
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
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_submenu' ], 99 );
        add_action( 'dt_settings_tab_menu', [ $this, 'add_tab' ], 10, 1 );
        add_action( 'dt_settings_tab_content', [ $this, 'content' ], 99, 1 );

        add_filter( 'dt_export_services', [ $this, 'export_import_services' ], 10, 1 );
        add_filter( 'dt_export_payload', [ $this, 'export_payload' ], 10, 1 );
        add_filter( 'dt_import_services', [ $this, 'export_import_services' ], 10, 1 );
        add_filter( 'dt_import_services_details', [ $this, 'import_services_details' ], 10, 2 );
        add_action( 'dt_import_payload', [ $this, 'import_payload' ], 10, 2 );

        parent::__construct();
    } // End __construct()

    private static $export_import_id = 'dt_custom_tile_settings';
    public function export_import_services( $services ){
        $services[self::$export_import_id] = [
            'id' => self::$export_import_id,
            'enabled' => true,
            'label' => __( 'D.T Custom Tile Settings', 'disciple_tools' ),
            'description' => __( 'Export/Import custom D.T tile settings.', 'disciple_tools' )
        ];

        return $services;
    }

    public function export_payload( $export_payload ){
        if ( isset( $export_payload['services'], $export_payload['payload'] ) && in_array( self::$export_import_id, $export_payload['services'] ) ){

            $payload = [];
            foreach ( DT_Posts::get_post_types() as $post_type ){
                $payload[$post_type] = DT_Posts::get_post_tiles( $post_type, false );
            }

            if ( !empty( $payload ) ){
                $export_payload['payload'][self::$export_import_id] = $payload;
            }
        }

        return $export_payload;
    }

    public function import_services_details( $details, $imported_config ){

        // Ensure imported config makes reference to corresponding id.
        if ( !isset( $imported_config['payload'], $imported_config['payload'][self::$export_import_id] ) ){
            return $details;
        }

        // First, construct details html.
        ob_start();
        ?>
        <p><?php echo esc_attr( __( 'D.T Custom Tile Settings', 'disciple_tools' ) ) ?></p>
        <p>
            Tiles not already installed on the system, will be enabled and available for selection.
        </p>
        <table class="widefat striped" id="<?php echo esc_attr( self::$export_import_id ) ?>_details_table">
            <thead>
                <tr>
                    <th style="text-align: right; padding-right: 14px;">
                        <input type="checkbox" id="dt_import_tile_settings_service_select_all_checkbox"/>
                    </th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php

            // Fetch list of existing instance post types.
            $existing_post_types = DT_Posts::get_post_types() ?? [];

            // Ensure displayed post types are driven by incoming config.
            foreach ( $imported_config['payload'][self::$export_import_id] ?? [] as $post_type => $tile_config ) {

                // Target instance, must contain corresponding post type, in order for incoming tiles to be set.
                if ( in_array( $post_type, $existing_post_types ) ){

                    // Fetch existing instance post type settings.
                    $post_type_settings = DT_Posts::get_post_settings( $post_type, false );

                    // Display post type heading.
                    ?>
                    <tr>
                        <td colspan="2">
                        <span style="font-weight: bold;"><?php echo esc_attr( $post_type_settings['label_plural'] ); ?></span>
                        </td>
                    </tr>
                    <?php

                    // Next, display tiles available for import; disabling those already installed within target instance.
                    foreach ( $tile_config ?? [] as $tile_id => $tile ) {
                        $already_has_tile = isset( $post_type_settings['tiles'], $post_type_settings['tiles'][$tile_id] );
                        ?>
                        <tr>
                            <td style="text-align: right;">
                                <input type="checkbox" class="dt-import-tile-settings-details-table-checkbox" data-post_type="<?php echo esc_attr( $post_type ) ?>" data-tile_id="<?php echo esc_attr( $tile_id ) ?>" <?php echo $already_has_tile ? 'disabled' : '' ?> />
                            </td>
                            <td>
                                <span><?php echo esc_attr( $tile['label'] ?? $tile_id ) ?></span>
                            </td>
                        </tr>
                        <?php
                    }
                }
            }
            ?>
            </tbody>
        </table>
        <script>
            jQuery('#dt_import_tile_settings_service_select_all_checkbox').on('click', function (e) {
                jQuery('#<?php echo esc_attr( self::$export_import_id ) ?>_details_table').find('.dt-import-tile-settings-details-table-checkbox').each(function (idx, checkbox) {
                    if( !jQuery(checkbox).attr('disabled') ) {
                        jQuery('.dt-import-tile-settings-details-table-checkbox').prop('checked', jQuery(e.currentTarget).prop('checked'));
                    }
                });
            });
        </script>
        <?php

        // Retrieve all buffered html output.
        $html = ob_get_clean();

        // Next, capture details handler js function logic.
        ob_start();
        ?>

        let tiles = [];
        jQuery('#<?php echo esc_attr( self::$export_import_id ) ?>_details_table').find('.dt-import-tile-settings-details-table-checkbox:checked').each(function (idx, checkbox) {
            let post_type = jQuery(checkbox).data('post_type');
            let tile_id = jQuery(checkbox).data('tile_id');
            if(post_type && tile_id) {
                tiles.push({
                    'post_type' : post_type,
                    'tile_id' : tile_id
                });
            }
        });
        return tiles;

        <?php
        $html_js_handler_func = ob_get_clean();

        // Finally, package detail parts and return.
        $details[self::$export_import_id] = [
            'id' => self::$export_import_id,
            'enabled' => true,
            'html' => $html,
            'html_js_handler_func' => $html_js_handler_func
        ];

        return $details;
    }

    public function import_payload( $selected_services, $imported_config ){

        // Ensure service has been selected, before proceeding!
        if ( !isset( $selected_services[self::$export_import_id] ) ) {
            return;
        }

        // Ensure imported config makes reference to corresponding id and has required settings.
        $service_label = __( 'D.T Custom Tile Settings', 'disciple_tools' );
        if ( !isset( $selected_services[self::$export_import_id], $selected_services[self::$export_import_id]['details'], $imported_config['payload'], $imported_config['payload'][self::$export_import_id] ) || empty( $selected_services[self::$export_import_id]['details'] ) ){
            echo '<p>' . esc_attr( $service_label ) . ': ' . esc_attr( __( 'Unable to detect suitable configuration settings!', 'disciple_tools' ) ) . '</p>';
            return;
        }

        $import_count = 0;
        $existing_tile_settings = [];
        $existing_tile_options = dt_get_option( 'dt_custom_tiles' );

        // Process selected service tiles accordingly, based on instance existence.
        foreach ( $selected_services[self::$export_import_id]['details'] as $selected_tile ) {
            $tile_post_type = $selected_tile['post_type'];
            $tile_id = $selected_tile['tile_id'];

            // If required, load corresponding post type tile settings.
            if ( !isset( $existing_tile_settings[$tile_post_type] ) ) {
                $existing_tile_settings[$tile_post_type] = DT_Posts::get_post_tiles( $tile_post_type );
            }

            // Ensure tile does not already exist.
            if ( !in_array( $tile_id, array_keys( $existing_tile_settings[$tile_post_type] ) ) ) {

                // Fetch corresponding imported tile config.
                if ( isset( $imported_config['payload'][self::$export_import_id][$tile_post_type], $imported_config['payload'][self::$export_import_id][$tile_post_type][$tile_id] ) ) {

                    // Make tile options provision if needed, before committing.
                    if ( !isset( $existing_tile_options[$tile_post_type] ) ){
                        $existing_tile_options[$tile_post_type] = [];
                    }
                    $existing_tile_options[$tile_post_type][$tile_id] = $imported_config['payload'][self::$export_import_id][$tile_post_type][$tile_id];

                    // Keep count of number of imported tiles.
                    $import_count++;
                }
            }
        }

        // Only update options if valid imports have taken place.
        if ( $import_count > 0 ) {
            update_option( 'dt_custom_tiles', $existing_tile_options );
        }

        // Echo tile import summary.
        echo '<p>' . esc_attr( $service_label ) . ': ' . esc_attr( sprintf( __( '[%d] Tile(s) Imported.', 'disciple_tools' ), $import_count ) ) . '</p>';
    }

    public function add_submenu() {
        add_submenu_page( 'dt_options', __( 'Tiles', 'disciple_tools' ), __( 'Tiles', 'disciple_tools' ), 'manage_dt', 'dt_options&tab=custom-tiles', [ 'Disciple_Tools_Settings_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        ?>
        <a href="<?php echo esc_url( admin_url() ) ?>admin.php?page=dt_options&tab=custom-tiles"
           class="nav-tab <?php echo esc_html( $tab == 'custom-tiles' ? 'nav-tab-active' : '' ) ?>">
            <?php echo esc_html__( 'Tiles' ) ?>
        </a>
        <?php
    }

    private function get_post_fields( $post_type ){
        return DT_Posts::get_post_field_settings( $post_type, false, true );
    }

    /**
     * Packages and prints tab page
     *
     * @param $tab
     */
    public function content( $tab ) {
        if ( 'custom-tiles' == $tab ) :
            $show_add_tile = false;
            $tile_key = false;
            if ( isset( $_POST['tile_key'] ) ){
                 $tile_key = sanitize_text_field( wp_unslash( $_POST['tile_key'] ) ) ?: false;
            }
            $post_type = null;

            /**
             * Post Type Select
             */
            if ( isset( $_POST['post_type_select_nonce'] ) ){
                if ( !wp_verify_nonce( sanitize_key( $_POST['post_type_select_nonce'] ), 'post_type_select' ) ) {
                    return;
                }
                if ( isset( $_POST['post_type'] ) ){
                    $post_type = sanitize_key( $_POST['post_type'] );
                }
            }
            $this->template( 'begin' );

            /* Translation Dialog */
            dt_display_translation_dialog();

            $this->box( 'top', __( 'Edit Tiles', 'disciple_tools' ) );
            $this->post_type_select( $post_type );
            $this->box( 'bottom' );


            //<------------------------------------------------->


            $tile_options = dt_get_option( 'dt_custom_tiles' );


            /*
             * Process Add tile
             */
            if ( isset( $_POST['tile_add_nonce'] ) ){
                if ( !wp_verify_nonce( sanitize_key( $_POST['tile_add_nonce'] ), 'tile_add' ) ) {
                    return;
                }
                $post_submission = [];
                foreach ( $_POST as $key => $value ){
                    $post_submission[sanitize_text_field( wp_unslash( $key ) )] = sanitize_text_field( wp_unslash( $value ) );
                }
                $tile_key = $this->process_add_tile( $post_submission );
                if ( $tile_key === false ){
                    $show_add_tile = true;
                }
                $post_type = $post_submission['post_type'];
            }

            if ( isset( $_POST['tile_select_nonce'] ) ){
                if ( !wp_verify_nonce( sanitize_key( $_POST['tile_select_nonce'] ), 'tile_select' ) ) {
                    return;
                }
                if ( isset( $_POST['show_add_new_tile'] ) ){
                    $show_add_tile = true;
                } else if ( !empty( $_POST['tile-select'] ) ){
                    $tile_key = sanitize_key( $_POST['tile-select'] );
                }
            }


            /*
             * Process Edit tile
             */
            if ( isset( $_POST['tile_edit_nonce'] ) ){
                if ( !wp_verify_nonce( sanitize_key( $_POST['tile_edit_nonce'] ), 'tile_edit' ) ) {
                    return;
                }
                $post_submission = [];
                foreach ( $_POST as $key => $value ){
                    $post_submission[sanitize_text_field( wp_unslash( $key ) )] = sanitize_text_field( wp_unslash( $value ) );
                }
                $this->process_edit_tile( $post_submission );
            }


            if ( isset( $_POST['tile_order_edit_nonce'] ) ){
                $this->process_tile_order( $post_type );
            }


            global $wp_post_types;
            if ( $post_type ){
                $this->box( 'top', 'Create or update tiles for ' . esc_html( $wp_post_types[$post_type]->label ) );
                $this->tile_select( $post_type );
                $this->box( 'bottom' );
            }
            if ( $show_add_tile ){
                $this->box( 'top', __( 'Add new tile', 'disciple_tools' ) );
                $this->add_tile( $post_type );
                $this->box( 'bottom' );
            }
            if ( $tile_key && $post_type ){
                $this->box( 'top', 'Modify ' . ( $tile_options[$post_type][$tile_key]['label'] ?? $tile_key ) . ' name, translations and fields' );
                $this->edit_tile( $tile_key, $post_type );
                $this->box( 'bottom' );
            }
            if ( $post_type ){
                $this->box( 'top', 'Sort Tiles and Fields for ' . esc_html( $wp_post_types[$post_type]->label ) );
                $this->edit_post_type_tiles( $post_type );
                $this->box( 'bottom' );
            }

            $this->template( 'right_column' );
            $this->box( 'top', 'Help' );
            $this->add_help_box();
            $this->box( 'bottom' );

            $this->template( 'end' );
        endif;
    }

    private function add_help_box(){
        ?>
            <ol>
                <li>Choose a post type</li>
                <li><strong>Create</strong> a new tile or <strong>modify</strong> an existing one</li>
                <li>Sort <strong>tiles order</strong> and tile <strong>fields order</strong></li>
                <li>See extra documentation <a href="https://disciple.tools/user-docs/getting-started-info/admin/settings-dt/custom-tiles/" target="_blank">here</a></li>
            </ol>

        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/sort-tiles.gif' ) ?>" style="width: 100%"/>
        <?php
    }


    private function post_type_select( $selected_post_type ){
        global $wp_post_types;
        $post_types = DT_Posts::get_post_types();
        ?>
        <form method="post">
            <input type="hidden" name="post_type_select_nonce" id="post_type_select_nonce" value="<?php echo esc_attr( wp_create_nonce( 'post_type_select' ) ) ?>" />
            <table>
                <tr>
                    <td style="vertical-align: middle">
                        <label for="tile-select"><?php esc_html_e( 'For what post type?', 'disciple_tools' ) ?></label>
                    </td>
                    <td>
                        <?php foreach ( $post_types as $post_type ) : ?>
                            <button type="submit" name="post_type" class="button <?php echo esc_html( $selected_post_type === $post_type ? 'button-primary' : '' ); ?>" value="<?php echo esc_html( $post_type ); ?>">
                                <?php echo esc_html( $wp_post_types[$post_type]->label ); ?>
                            </button>
                        <?php endforeach; ?>
                    </td>
                </tr>
            </table>
            <br>
        </form>
        <?php
    }

    private function edit_post_type_tiles( $post_type ){
        $fields = DT_Posts::get_post_field_settings( $post_type, false );
        $tile_options = DT_Posts::get_post_tiles( $post_type, false );

        ?>
        <style>
          .connectedSortable li {
            margin: 0 0 5px 0;
            padding: 5px;
            width: 110px;
          }
          .connectedSortable {
            border: 1px solid #eee;
            width: 125px;
            min-height: 60px;
            list-style-type: none;
            margin: 0;
            padding: 5px;
            float: left;
            background-color: #eee;
            height: 300px;
            overflow-x: hidden;
            overflow-y: auto;
          }
          .ui-state-highlight { height: 1.5em; line-height: 1.2em; }


          #sort-tiles > div {
            border: 1px solid #eee;
            width: 150px;
            min-height: 20px;
            float:left;
          }
          .column-header{
            font-weight: bold;
            overflow: hidden;
            white-space: nowrap;
          }
          .field-container {
            padding: 5px;
          }
          .disabled-drag {
            color: grey
          }
          </style>

        <form method="post" name="post_type_tiles_form" id="tile-order-form">
            <input type="hidden" name="post_type" value="<?php echo esc_html( $post_type )?>">
            <input type="hidden" name="post_type_select_nonce" id="post_type_select_nonce" value="<?php echo esc_attr( wp_create_nonce( 'post_type_select' ) ) ?>" />
            <input type="hidden" name="tile_order_edit_nonce" id="tile_order_edit_nonce" value="<?php echo esc_attr( wp_create_nonce( 'tile_order_edit' ) ) ?>" />
            <p><strong>Drag</strong> columns to change the order of the tiles. <strong>Drag</strong> field to change field order. Drag field between columns</p>
            <button type="button" class="button save-drag-changes">Save tile and field order</button>
            <div id="sort-tiles" style="display: inline-block; width: 100%">

                <?php foreach ( $tile_options as $tile_key => $tile ) :
                    if ( $tile_key === 'no_tile' || ( $tile['hidden'] ?? false ) ){
                        continue;
                    }

                    //@todo display hidden tile greyed out
                    $disabled_ui = !in_array( $tile_key, [ 'status', 'details' ] ) ? 'draggable-header' : 'disabled-drag';
                    ?>
                    <div class="sort-tile <?php echo esc_html( $disabled_ui ); ?>" id="<?php echo esc_html( $tile_key ); ?>">
                        <div class="field-container">
                            <h3 class="column-header <?php echo esc_html( $disabled_ui ); ?>">
                                <?php if ( !in_array( $tile_key, [ 'status', 'details' ] ) ) : ?>
                                    <span class="ui-icon ui-icon-arrow-4"></span>
                                <?php endif ?>
                                <?php echo esc_html( isset( $tile['label'] ) ? $tile['label'] : $tile_key ); ?>
                            </h3>
                            <ul class="connectedSortable">
                                <?php foreach ( $tile['order'] ?? [] as $order_key ):
                                    if ( isset( $fields[$order_key]['tile'] ) && $fields[$order_key]['tile'] === $tile_key ) : ?>
                                        <li class="ui-state-default" id="<?php echo esc_html( $order_key ); ?>">
                                            <span class="ui-icon ui-icon-arrow-4"></span>
                                            <?php echo esc_html( $fields[$order_key]['name'] ); ?>
                                            <span style="color:lightcoral"><?php echo esc_html( isset( $fields[$order_key]['hidden'] ) && !empty( $fields[$order_key]['hidden'] ) ? '(Hidden)' : '' ); ?></span>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>


                                <?php foreach ( $fields as $field_key => $field_value ) :
                                    if ( isset( $field_value['tile'] ) && $field_value['tile'] === $tile_key && !in_array( $field_key, $tile['order'] ?? [] ) ) :?>
                                        <li class="ui-state-default" id="<?php echo esc_html( $field_key ); ?>">
                                            <span class="ui-icon ui-icon-arrow-4"></span>
                                            <?php echo esc_html( $field_value['name'] ); ?>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="sort-tile disabled-drag" id="no_tile" >
                    <div class="field-container">
                        <h3 class="column-header disabled-drag"><span class="ui-icon ui-icon-arrow-4"></span>No Tile / Hidden</h3>
                        <ul class="connectedSortable">
                            <?php foreach ( $fields as $field_key => $field_value ) :
                                if ( ( ( empty( $field_value['hidden'] ) && ( isset( $field_value['customizable'] ) && $field_value['customizable'] !== false ) )
                                    && ( !isset( $field_value['tile'] ) || !in_array( $field_value['tile'], array_keys( $tile_options ) ) ) )
                                    || ( isset( $field_value['tile'] ) && $field_value['tile'] === 'no_tile' ) ) :?>
                                    <li class="ui-state-default" id="<?php echo esc_html( $field_key ); ?>">
                                        <span class="ui-icon ui-icon-arrow-4"></span>
                                        <?php echo esc_html( $field_value['name'] ); ?>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <button type="button" class="button save-drag-changes">Save tile and field order</button>
            <?php if ( isset( $_POST['tile_order_edit_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['tile_order_edit_nonce'] ), 'tile_order_edit' ) ) : ?>
                <span style="vertical-align: bottom; margin: 12px">Changes saved!</span>
            <?php endif; ?>
        </form>


        <?php

    }

    private function process_tile_order( $post_type ){
        if ( isset( $_POST['tile_order_edit_nonce'], $_POST['order'] ) ){
            if ( !wp_verify_nonce( sanitize_key( $_POST['tile_order_edit_nonce'] ), 'tile_order_edit' ) ) {
                return;
            }
            $order = dt_recursive_sanitize_array( json_decode( sanitize_text_field( wp_unslash( $_POST['order'] ) ), true ) );
            $tile_options = dt_get_option( 'dt_custom_tiles' );
            if ( !isset( $tile_options[$post_type] ) ){
                $tile_options[$post_type] = [];
            }

            if ( !empty( $order ) ){
                //update order of field in a tile.
                foreach ( $order as $index => $tile_order ){
                    if ( $tile_order['key'] !== 'no_tile' ){
                        if ( !isset( $tile_options[$post_type][$tile_order['key']] ) ){
                            $tile_options[$post_type][$tile_order['key']] = [];
                        }
                        $tile_options[$post_type][$tile_order['key']]['order'] = $tile_order['fields'];
                        $tile_options[$post_type][$tile_order['key']]['tile_priority'] = ( $index + 1 ) * 10;
                    }
                }
                update_option( 'dt_custom_tiles', $tile_options );

                //update tiles fields belong to.
                $custom_fields = dt_get_option( 'dt_field_customizations' );
                foreach ( $order as $tile_order ){
                    foreach ( $tile_order['fields'] as $field_key ){
                        if ( !isset( $custom_fields[$post_type][$field_key] ) ){
                            $custom_fields[$post_type][$field_key] = [];
                        }
                        if ( $tile_order['key'] === 'no_tile' ){
                            $custom_fields[$post_type][$field_key]['tile'] = 'no_tile';
                        } else {
                            $custom_fields[$post_type][$field_key]['tile'] = $tile_order['key'];
                        }
                    }
                }
                update_option( 'dt_field_customizations', $custom_fields );
            }
        }
    }

    private function tile_select( $post_type ){
        $tile_options = DT_Posts::get_post_tiles( $post_type, false );
        ?>
        <form method="post">
            <input type="hidden" name="tile_select_nonce" id="tile_select_nonce" value="<?php echo esc_attr( wp_create_nonce( 'tile_select' ) ) ?>" />
            <input type="hidden" name="post_type" value="<?php echo esc_html( $post_type )?>">
            <input type="hidden" name="post_type_select_nonce" id="post_type_select_nonce" value="<?php echo esc_attr( wp_create_nonce( 'post_type_select' ) ) ?>" />
            <table>
                <tr>
                    <td style="vertical-align: middle">
                        <label for="tile-select"><?php esc_html_e( 'Modify an existing tile', 'disciple_tools' ) ?></label>
                    </td>
                    <td>
                        <?php foreach ( $tile_options as $tile_key => $tile_value ) : ?>

                            <button type="submit" name="tile-select" class="button" value="<?php echo esc_html( $tile_key ); ?>"><?php echo esc_html( isset( $tile_value['label'] ) ? $tile_value['label'] : $tile_key ); ?></button>

                        <?php endforeach; ?>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: middle">
                        <?php esc_html_e( 'Create a new tile', 'disciple_tools' ) ?>
                    </td>
                    <td>
                        <button type="submit" class="button" name="show_add_new_tile"><?php esc_html_e( 'Add a new tile', 'disciple_tools' ) ?></button>
                    </td>
                </tr>
            </table>
            <br>
        </form>

    <?php }

    private function edit_tile( $tile_key, $post_type ){

        $tile_options = DT_Posts::get_post_tiles( $post_type, false );

        if ( !isset( $tile_options[$tile_key] ) ){
            self::admin_notice( __( 'Tile not found', 'disciple_tools' ), 'error' );
            return;
        }

        $tile = $tile_options[$tile_key];
        $fields = $this->get_post_fields( $post_type );

        $first = true;

        $form_name = 'tile_edit_form';
        ?>
        <form method="post" name="<?php echo esc_html( $form_name ) ?>" id="<?php echo esc_html( $form_name ) ?>" onkeydown="return event.key != 'Enter';">
        <input type="hidden" name="tile_key" value="<?php echo esc_html( $tile_key )?>">
        <input type="hidden" name="post_type" value="<?php echo esc_html( $post_type )?>">
        <input type="hidden" name="post_type_select_nonce" id="post_type_select_nonce" value="<?php echo esc_attr( wp_create_nonce( 'post_type_select' ) ) ?>" />
        <input type="hidden" name="tile-select" value="<?php echo esc_html( $tile_key )?>">
        <input type="hidden" name="tile_select_nonce" id="tile_select_nonce" value="<?php echo esc_attr( wp_create_nonce( 'tile_select' ) ) ?>" />
        <input type="hidden" name="tile_edit_nonce" id="tile_edit_nonce" value="<?php echo esc_attr( wp_create_nonce( 'tile_edit' ) ) ?>" />

        <h4><?php esc_html_e( 'Tile Settings', 'disciple_tools' ) ?></h4>
        <table class="widefat">
            <thead>
            <tr>
                <td><?php esc_html_e( 'Key', 'disciple_tools' ) ?></td>
                <td><?php esc_html_e( 'Label', 'disciple_tools' ) ?></td>
                <td><?php esc_html_e( 'Translation', 'disciple_tools' ) ?></td>
                <td></td>
            </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <?php echo esc_html( $tile_key ) ?>
                    </td>
                    <td>
                        <input name="tile_label" type="text" value="<?php echo esc_html( $tile['label'] ?? '' ) ?>"/>
                    </td>
                    <td>
                        <?php $langs = dt_get_available_languages(); ?>
                        <button class="button small expand_translations" data-form_name="<?php echo esc_html( $form_name ) ?>">
                            <?php
                            $number_of_translations = 0;
                            foreach ( $langs as $lang => $val ){
                                if ( !empty( $tile['translations'][$val['language']] ) ){
                                    $number_of_translations++;
                                }
                            }
                            ?>
                            <img style="height: 15px; vertical-align: middle" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/languages.svg' ); ?>">
                            (<?php echo esc_html( $number_of_translations ); ?>)
                        </button>
                        <div class="translation_container hide">
                            <table>
                                <?php foreach ( $langs as $lang => $val ) : ?>
                                    <tr>
                                        <td><label for="tile_label_translation-<?php echo esc_html( $val['language'] )?>"><?php echo esc_html( $val['native_name'] )?></label></td>
                                        <td><input name="tile_label_translation-<?php echo esc_html( $val['language'] )?>" type="text" value="<?php echo esc_html( $tile['translations'][$val['language']] ?? '' );?>"/></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    </td>
                    <td>
                    <button type="submit" class="button"><?php esc_html_e( 'Save', 'disciple_tools' ) ?></button>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>

        <label><h4>Tile Description</h4>
            <input style="width: 100%" type="text" name="tile_description" value="<?php echo esc_html( $tile['description'] ?? '' )?>">
        </label>
        <br><br>

        <?php include 'tab-custom-tiles-display-help-dialog.php'; ?>
        <h4>
            Tile Display
            <a class="help-button" style="cursor: pointer;" data-dialog_id="custom_tile_display_help_dialog">
                <i class="mdi mdi-help-circle help-icon"></i>
            </a>
        </h4>
        <?php if ( isset( $tile['display_conditions']['visibility'] ) && $tile['display_conditions']['visibility'] == 'hidden' ): ?>
            <p><?php esc_html_e( 'Note: This tile is hidden and will not show on the record page', 'disciple_tools' ) ?></p>
        <?php endif; ?>
        <table class="widefat striped">
            <thead>
            <tr>
                <td><?php esc_html_e( 'Hidden', 'disciple_tools' ) ?></td>
                <td><?php esc_html_e( 'Visible', 'disciple_tools' ) ?></td>
                <td><?php esc_html_e( 'Custom', 'disciple_tools' ) ?></td>
            </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="radio" name="tile_display_option" value="hidden" <?php echo ( isset( $tile['display_conditions']['visibility'] ) && $tile['display_conditions']['visibility'] == 'hidden' ) ? 'checked' : ''; ?>></td>
                    <td><input type="radio" name="tile_display_option" value="visible" <?php echo ( isset( $tile['display_conditions']['visibility'] ) && $tile['display_conditions']['visibility'] == 'visible' ) || !isset( $tile['display_conditions']['visibility'] ) ? 'checked' : ''; ?>></td>
                    <td><input type="radio" name="tile_display_option" value="custom" <?php echo ( isset( $tile['display_conditions']['visibility'] ) && $tile['display_conditions']['visibility'] == 'custom' ) ? 'checked' : ''; ?>></td>
                </tr>
            </tbody>
        </table>
        <br>

        <div id="tile_display_custom_elements" style="<?php echo ( isset( $tile['display_conditions']['visibility'] ) && $tile['display_conditions']['visibility'] == 'custom' ) ? '' : 'display: none;'; ?>">
            <h4>Custom Display Conditions</h4>
            <label>Select field and field options. The tile will be displayed when these are selected on the record.</label>
            <br><br>
            <select name="tile_display_custom_condition" id="tile_display_custom_condition">
                <option value="" disabled selected>--- [ choose a field and option ] ---</option>
                <?php
                foreach ( $fields as $field_id => $field )
                    {
                    if ( in_array( $field['type'], [ 'key_select', 'multi_select', 'tags' ] ) && isset( $field['default'] ) ) {
                        echo '<optgroup label="'.esc_html( $field['name'] ).'">';
                        $options = ( $field['type'] == 'tags' ) ? DT_Posts::get_multi_select_options( $post_type, $field_id ) : $field['default'];
                        foreach ( $options ?? [] as $option_id => $option ) {
                            $html_val = $field_id.'___'. ( ( $field['type'] == 'tags' ) ? $option : $option_id );
                            $html_label = ( $field['type'] == 'tags' ) ? $option : $option['label'] ?? $option_id;
                            ?>
                                <option value="<?php echo esc_html( $html_val )?>"><?php echo esc_html( $html_label )?></option>
                            <?php
                        }
                        echo '</optgroup>';
                    }
                }
                ?>
            </select>
            <span>
                <button class="button" type="submit" id="tile_display_custom_condition_select_but" name="tile_display_custom_condition_select_but">Add</button>
            </span>
            <br><br>
            <table class="widefat striped">
                <thead>
                <tr>
                    <td><?php esc_html_e( 'Field', 'disciple_tools' ) ?></td>
                    <td><?php esc_html_e( 'Option', 'disciple_tools' ) ?></td>
                    <td></td>
                </tr>
                </thead>
                <tbody>
                    <?php
                    if ( !isset( $tile['display_conditions']['conditions'] ) || !is_array( $tile['display_conditions']['conditions'] ) ) {
                        $tile['display_conditions']['conditions'] = [];
                    }
                    foreach ( $tile['display_conditions']['conditions'] ?? [] as $condition ) {
                        ?>
                        <tr>
                            <td><?php echo esc_html( $condition['key_label'] ) ?></td>
                            <td><?php echo esc_html( $condition['value_label'] ) ?></td>
                            <td>
                                <span style="float: right;">
                                    <button class="button" type="submit" id="tile_display_custom_condition_remove_but" name="tile_display_custom_condition_remove_but" value="<?php echo esc_html( $condition['key'].'___'.$condition['value'] ) ?>">Remove</button>
                                </span>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
            <table>
                <tbody>
                    <tr>
                        <td>
                            <label>Require all selected condition to be true</label>
                        </td>
                        <td>
                            <input name="tile_display_custom_condition_all_fields" id="tile_display_custom_condition_all_fields" type="checkbox" <?php echo ( isset( $tile['display_conditions']['operator'] ) && $tile['display_conditions']['operator'] == 'and' ) ? 'checked' : '' ?>>
                        </td>
                    </tr>
                </tbody>
            </table>
            <br>
        </div>

        <div>
            <button class="button" name="save" type="submit">Save Settings</button>
            <button type="button" id="open-delete-confirm-modal" class="button button-primary">Delete Customizations</button>
        </div>
        </form>
        <div id='dt-delete-tile-alert' title='Delete Tile'>
            <p>Are you sure you want to delete this Tile?</p>
            <p>Note: Only user added tiles can fully be deleted.</p>
            <button class='button button-primary' id='confirm-tile-delete' name='delete' type="submit">Delete</button>
            <button class='button' type="button" id='tile-close-delete'>Cancel</button>
        </div>


        <script type="application/javascript">
            jQuery(document).ready(function ($){
                $('#dt-delete-tile-alert').dialog({ autoOpen: false });

                $('#open-delete-confirm-modal').click(function(){
                    $('#dt-delete-tile-alert').dialog('open');
                });

                $('#tile-close-delete').click(function(){
                    $('#dt-delete-tile-alert').dialog('close');
                });
                $('#confirm-tile-delete').click(function(){
                    let input = $("<input>").attr("type", "hidden").attr("name", "delete")
                    $('#tile_edit_form').append(input).submit();
                });
            })
        </script>
    <?php }


    private function process_edit_tile( $post_submission ){
        //save values
        $post_type = $post_submission['post_type'];
        $tile_options = dt_get_option( 'dt_custom_tiles' );
        $tile_key = $post_submission['tile_key'];

        if ( isset( $post_submission['delete'] ) ){
            unset( $tile_options[$post_type][$tile_key] );
            update_option( 'dt_custom_tiles', $tile_options );
            return;
        }

        if ( !isset( $tile_options[$post_type][$tile_key] ) ){
            $tile_options[$post_type][$tile_key] = [];
        }
        $post_fields = $this->get_post_fields( $post_type );
        if ( !isset( $tile_options[$post_type][$tile_key] ) ){
            $tile_options[$post_type][$tile_key] = [];
        }
        $custom_tile = $tile_options[$post_type][$tile_key];

        if ( isset( $post_submission['tile_label'] ) && $post_submission['tile_label'] != ( $custom_tile['label'] ?? $tile_key ) ){
            $custom_tile['label'] = $post_submission['tile_label'];
        }
        if ( isset( $post_submission['tile_description'] ) && $post_submission['tile_description'] != ( $custom_tile['description'] ?? '' ) ){
            $custom_tile['description'] = $post_submission['tile_description'];
        }

        // Display Conditions.
        if ( empty( $custom_tile['display_conditions'] ) || !is_array( $custom_tile['display_conditions'] ) ) {
            $custom_tile['display_conditions'] = [];
        }

        // Requested Visibility State.
        if ( isset( $post_submission['tile_display_option'] ) && in_array( $post_submission['tile_display_option'], [ 'hidden','visible','custom' ] ) ){
            $custom_tile['display_conditions']['visibility'] = $post_submission['tile_display_option'];
            $custom_tile['hidden'] = in_array( $post_submission['tile_display_option'], [ 'hidden' ] );

            // Custom Visibility.
            if ( $post_submission['tile_display_option'] == 'custom' ) {

                // Custom - Select Condition.
                if ( isset( $post_submission['tile_display_custom_condition_select_but'] ) ){
                    if ( isset( $post_submission['tile_display_custom_condition'] ) && $post_submission['tile_display_custom_condition'] != '' ) {

                        // If needed, initialise conditions array.
                        if ( empty( $custom_tile['display_conditions']['conditions'] ) || !is_array( $custom_tile['display_conditions']['conditions'] ) ) {
                            $custom_tile['display_conditions']['conditions'] = [];
                        }

                        // Extract tile display condition parts.
                        $condition_parts = explode( '___', $post_submission['tile_display_custom_condition'] );
                        $field_id        = $condition_parts[0];
                        $option_id       = $condition_parts[1];

                        // Append latest entry, ensuring uniqueness.
                        if ( isset( $field_id, $option_id ) ) {
                            $field_id_label = $field_id;
                            $option_id_label = $option_id;

                            // Determine respective labels.
                            if ( isset( $post_fields[ $field_id ] ) ) {
                                $field_id_label = $post_fields[ $field_id ]['name'] ?? $field_id;
                                if ( isset( $post_fields[ $field_id ]['default'], $post_fields[ $field_id ]['default'][ $option_id ] ) ) {
                                    $option_id_label = $post_fields[ $field_id ]['default'][ $option_id ]['label'] ?? $option_id;
                                }
                            }

                            // Package and return....
                            $custom_tile['display_conditions']['conditions'][$field_id.'___'.$option_id] = [
                                'key' => $field_id,
                                'key_label' => $field_id_label,
                                'value' => $option_id,
                                'value_label' => $option_id_label
                            ];
                        }
                    }
                }

                // Custom - Remove Condition.
                if ( isset( $post_submission['tile_display_custom_condition_remove_but'] ) ){

                    // Extract tile display condition parts.
                    $condition_parts = explode( '___', $post_submission['tile_display_custom_condition_remove_but'] );
                    $field_id        = $condition_parts[0];
                    $option_id       = $condition_parts[1];

                    // Remove identified condition, by field id.
                    unset( $custom_tile['display_conditions']['conditions'][$field_id.'___'.$option_id] );
                }

                // Custom - Operator.
                $custom_tile['display_conditions']['operator'] = isset( $post_submission['tile_display_custom_condition_all_fields'] ) ? 'and' : 'or';
            }
        }

        //update other Translations
        $langs = dt_get_available_languages();

        foreach ( $langs as $lang => $val ){
            $langcode = $val['language'];
            $translation_key = 'tile_label_translation-' . $langcode;
            if ( !empty( $post_submission[$translation_key] ) ) {
                $custom_tile['translations'][$langcode] = $post_submission[$translation_key];
            }
        }


        //move option  up or down
        if ( isset( $post_submission['move_up'] ) || isset( $post_submission['move_down'] ) ){
            $option_key = $post_submission['move_up'] ?? $post_submission['move_down'];
            $direction = isset( $post_submission['move_up'] ) ? -1 : 1;
            $keys = $custom_tile['order'] ?? [];
            foreach ( $post_fields as $field_key => $field_val ){
                if ( ( isset( $field_val['tile'] ) && $field_val['tile'] == $tile_key ) ){
                    if ( !in_array( $field_key, $keys ) ){
                        $keys[] = $field_key;
                    }
                }
            }
            $index = (int) array_search( $option_key, $keys );
            $pos = (int) $index + $direction;
            unset( $keys[ $index ] );
            $keys = array_merge(
                array_slice( $keys, 0, $pos ),
                [ $option_key ],
                array_slice( $keys, $pos )
            );
            $order = $keys;
            $custom_tile['order'] = $order;
        }

        if ( !empty( $custom_tile ) ){
            $tile_options[$post_type][$tile_key] = $custom_tile;
        }

        update_option( 'dt_custom_tiles', $tile_options );
    }



    private function add_tile( $post_type ){
        ?>
        <form method="post">
            <input type="hidden" name="post_type" value="<?php echo esc_html( $post_type )?>">
            <input type="hidden" name="post_type_select_nonce" id="post_type_select_nonce" value="<?php echo esc_attr( wp_create_nonce( 'post_type_select' ) ) ?>" />
            <input type="hidden" name="tile_add_nonce" id="tile_add_nonce" value="<?php echo esc_attr( wp_create_nonce( 'tile_add' ) ) ?>" />
            <table>
                <tr>
                    <td style="vertical-align: middle">
                        <label for="new_tile_name"><?php esc_html_e( 'New Tile Name', 'disciple_tools' ) ?></label>
                    </td>
                    <td>
                        <input name="new_tile_name" id="new_tile_name" required>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: middle">
                    </td>
                    <td>
                        <button type="submit" class="button"><?php esc_html_e( 'Create Tile', 'disciple_tools' ) ?></button>
                    </td>
                </tr>
            </table>

        </form>
        <?php
    }

    private function process_add_tile( $post_submission ){
        if ( isset( $post_submission['new_tile_name'], $post_submission['post_type'] ) ){
            $post_type = $post_submission['post_type'];
            $tile_options = dt_get_option( 'dt_custom_tiles' );
            $post_tiles = DT_Posts::get_post_tiles( $post_type );
            $tile_key = dt_create_field_key( $post_submission['new_tile_name'] );
            if ( in_array( $tile_key, array_keys( $post_tiles ) ) ){
                self::admin_notice( __( 'tile already exists', 'disciple_tools' ), 'error' );
                return false;
            }
            if ( !isset( $tile_options[$post_type] ) ){
                $tile_options[$post_type] = [];
            }
            $tile_options[$post_type][$tile_key] = [ 'label' => $post_submission['new_tile_name'] ];

            update_option( 'dt_custom_tiles', $tile_options );
            self::admin_notice( __( 'tile added successfully', 'disciple_tools' ), 'success' );
            return $tile_key;
        }
        return false;
    }

    /**
     * Display admin notice
     *
     * @param $notice string
     * @param $type string error|success|warning
     */
    public static function admin_notice( string $notice, string $type ) {
        ?>
        <div class="notice notice-<?php echo esc_attr( $type ) ?> is-dismissible">
            <p><?php echo esc_html( $notice ) ?></p>
        </div>
        <?php
    }
}
Disciple_Tools_Tab_Custom_Tiles::instance();
