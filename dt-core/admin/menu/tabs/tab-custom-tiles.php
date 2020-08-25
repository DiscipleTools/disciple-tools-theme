<?php

/**
 * Disciple Tools
 *
 * @class      Disciple_Tools_Tab_Custom_Fields
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple_Tools
 * @author     Chasm.Solutions & Kingdom.Training
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

        parent::__construct();
    } // End __construct()

    public function add_submenu() {
        add_submenu_page( 'dt_options', __( 'Custom Tiles', 'disciple_tools' ), __( 'Custom Tiles', 'disciple_tools' ), 'manage_dt', 'dt_options&tab=custom-tiles', [ 'Disciple_Tools_Settings_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        ?>
        <a href="<?php echo esc_url( admin_url() ) ?>admin.php?page=dt_options&tab=custom-tiles"
           class="nav-tab <?php echo esc_html( $tab == 'custom-tiles' ? 'nav-tab-active' : '' ) ?>">
            <?php echo esc_html__( 'Custom Tiles' ) ?>
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
            $post_type = null;

            $tile_options = dt_get_option( "dt_custom_tiles" );

            $this->template( 'begin' );

            if ( isset( $_POST['tile_select_nonce'] ) ){
                if ( !wp_verify_nonce( sanitize_key( $_POST['tile_select_nonce'] ), 'tile_select' ) ) {
                    return;
                }
                if ( isset( $_POST["show_add_new_tile"] ) ){
                    $show_add_tile = true;
                } else if ( !empty( $_POST["tile-select"] ) ){
                    $tile = explode( "_", sanitize_text_field( wp_unslash( $_POST["tile-select"] ) ), 2 );
                    $tile_key = $tile[1];
                    $post_type = $tile[0];
                }
            }


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
                $post_type = $post_submission["post_type"];
            }
            /*
             * Process Edit tile
             */
            if ( isset( $_POST["tile_edit_nonce"] ) ){
                if ( !wp_verify_nonce( sanitize_key( $_POST['tile_edit_nonce'] ), 'tile_edit' ) ) {
                    return;
                }
                $post_submission = [];
                foreach ( $_POST as $key => $value ){
                    $post_submission[sanitize_text_field( wp_unslash( $key ) )] = sanitize_text_field( wp_unslash( $value ) );
                }
                $this->process_edit_tile( $post_submission );
            }

            $this->box( 'top', __( 'Create or update tiles', 'disciple_tools' ) );
            $this->tile_select();
            $this->box( 'bottom' );


            if ( $show_add_tile ){
                $this->box( 'top', __( "Add new tile", 'disciple_tools' ) );
                $this->add_tile();
                $this->box( 'bottom' );
            }
            if ( $tile_key && $post_type ){
                $this->box( 'top', $tile_options[$post_type][$tile_key]["label"] ?? $tile_key );
                $this->edit_tile( $tile_key, $post_type );
                $this->box( 'bottom' );
            }

            $this->template( 'right_column' );

            $this->template( 'end' );
        endif;
    }

    private function tile_select(){
        global $wp_post_types;
        $tile_options = dt_get_option( "dt_custom_tiles" );
        $post_types = apply_filters( 'dt_registered_post_types', [] );
        foreach ( $post_types as $post_type ){
            $sections = apply_filters( 'dt_details_additional_section_ids', [], $post_type );
            if ( !isset( $tile_options[$post_type] ) ){
                $tile_options[$post_type] = [];
            }
            foreach ( $sections as $section_id ){
                $tile_options[$post_type][$section_id] = [];
            }
        }

        ?>
        <form method="post">
            <input type="hidden" name="tile_select_nonce" id="tile_select_nonce" value="<?php echo esc_attr( wp_create_nonce( 'tile_select' ) ) ?>" />
            <table>
                <tr>
                    <td style="vertical-align: middle">
                        <label for="tile-select"><?php esc_html_e( "Modify an existing tile", 'disciple_tools' ) ?></label>
                    </td>
                    <td>
                        <select id="tile-select" name="tile-select">
                            <option></option>
                            <?php foreach ( $post_types as $post_type ) : ?>
                                <option disabled>---<?php echo esc_html( $wp_post_types[$post_type]->label ); ?> tiles---</option>
                                <?php foreach ( $tile_options[$post_type] as $option_key => $option_value ) : ?>
                                    <option value="<?php echo esc_html( $post_type . '_' . $option_key ) ?>">
                                        <?php echo esc_html( $option_value["label"] ?? $option_key ) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="button" name="tile_selected"><?php esc_html_e( "Select", 'disciple_tools' ) ?></button>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: middle">
                        <?php esc_html_e( "Create a new tile", 'disciple_tools' ) ?>
                    </td>
                    <td>
                        <button type="submit" class="button" name="show_add_new_tile"><?php esc_html_e( "Add a new tile", 'disciple_tools' ) ?></button>
                    </td>
                </tr>
            </table>
            <br>
        </form>

    <?php }

    private function edit_tile( $tile_key, $post_type ){

        $tile_options = dt_get_option( "dt_custom_tiles" );
        $sections = apply_filters( 'dt_details_additional_section_ids', [], $post_type );
        foreach ( $sections as $section_id ){
            if ( !isset( $tile_options[$post_type][$section_id] ) ){
                $tile_options[$post_type][$section_id] = [];
            }
        }
        if ( !isset( $tile_options[$post_type][$tile_key] )){
            self::admin_notice( __( "Tile not found", 'disciple_tools' ), "error" );
            return;
        }

        $tile = $tile_options[$post_type][$tile_key];
        $fields = $this->get_post_fields( $post_type );

        $first = true;

        ?>
        <form method="post" name="tile_edit_form">
        <input type="hidden" name="tile_key" value="<?php echo esc_html( $tile_key )?>">
        <input type="hidden" name="post_type" value="<?php echo esc_html( $post_type )?>">
        <input type="hidden" name="tile-select" value="<?php echo esc_html( $post_type . "_" . $tile_key )?>">
        <input type="hidden" name="tile_select_nonce" id="tile_select_nonce" value="<?php echo esc_attr( wp_create_nonce( 'tile_select' ) ) ?>" />
        <input type="hidden" name="tile_edit_nonce" id="tile_edit_nonce" value="<?php echo esc_attr( wp_create_nonce( 'tile_edit' ) ) ?>" />

        <h4><?php esc_html_e( "Tile Settings", 'disciple_tools' ) ?></h4>
        <?php if ( isset( $tile["hidden"] ) && $tile["hidden"] === true ): ?>
            <p><?php esc_html_e( "Note: This tile is hidden and will not show on the record page", 'disciple_tools' ) ?></p>
        <?php endif; ?>

        <table class="widefat">
            <thead>
            <tr>
                <td><?php esc_html_e( "Key", 'disciple_tools' ) ?></td>
                <td><?php esc_html_e( "Label", 'disciple_tools' ) ?></td>
                <td><?php esc_html_e( "Hide", 'disciple_tools' ) ?></td>
                <td><?php esc_html_e( "Translation", 'disciple_tools' ) ?></td>
                <td></td></td>
            </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <?php echo esc_html( $tile_key ) ?>
                    </td>
                    <td>
                        <input name="tile_label" type="text" value="<?php echo esc_html( $tile["label"] ?? $tile_key ) ?>"/>
                    </td>
                    <td>
                        <?php if ( isset( $tile["hidden"] ) && $tile["hidden"] === true ): ?>
                            <button type="submit" name="restore_tile" class="button"><?php esc_html_e( "Restore tile", 'disciple_tools' ) ?></button>
                        <?php else : ?>
                            <button type="submit" name="hide_tile" class="button"><?php esc_html_e( "Hide tile on page", 'disciple_tools' ) ?></button>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php $langs = dt_get_available_languages(); ?>
                        <button class="button small expand_translations">
                            <?php
                            $number_of_translations = 0;
                            foreach ( $langs as $lang => $val ){
                                if ( !empty( $tile["translations"][$val['language']] ) ){
                                    $number_of_translations++;
                                }
                            }
                            ?>
                            <img style="height: 15px; vertical-align: middle" src="<?php echo esc_html( get_template_directory_uri() . "/dt-assets/images/languages.svg" ); ?>">
                            (<?php echo esc_html( $number_of_translations ); ?>)
                        </button>
                        <div class="translation_container hide">
                            <table>
                                <?php foreach ( $langs as $lang => $val ) : ?>
                                    <tr>
                                        <td><label for="tile_label_translation-<?php echo esc_html( $val['language'] )?>"><?php echo esc_html( $val['native_name'] )?></label></td>
                                        <td><input name="tile_label_translation-<?php echo esc_html( $val['language'] )?>" type="text" value="<?php echo esc_html( $tile["translations"][$val['language']] ?? "" );?>"/></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    </td>
                    <td>
                    <button type="submit" class="button"><?php esc_html_e( "Save", 'disciple_tools' ) ?></button>
                    </td>
                </tr>
            </tbody>
        </table>

        <br>

        <h4><?php esc_html_e( "Tile Fields", 'disciple_tools' ) ?></h4>
        <table class="widefat">
            <thead>
            <tr>
                <td><?php esc_html_e( "Label", 'disciple_tools' ) ?></td>
                <td><?php esc_html_e( "Move", 'disciple_tools' ) ?></td>
            </tr>
            </thead>
            <tbody>
            <?php $order = $tile["order"] ?? [];
            foreach ( $fields as $key => $option ){
                if ( isset( $option["tile"] ) && $option["tile"] === $tile_key ){
                    if ( !in_array( $key, $order )){
                        $order[] = $key;
                    }
                }
            }

            foreach ( $order as $key ) :
                if ( !isset( $fields[$key] ) ){
                    continue;
                }
                $option = $fields[$key];
                if ( isset( $option["tile"] ) && $option["tile"] === $tile_key ):
                    $label = $option["name"] ?? ""; ?>
                    <tr>
                        <td>
                            <?php echo esc_html( $label ) ?>
                        </td>
                        <td>
                            <?php if ( !$first ) : ?>
                                <button type="submit" name="move_up" value="<?php echo esc_html( $key ) ?>" class="button small" >↑</button>
                                <button type="submit" name="move_down" value="<?php echo esc_html( $key ) ?>" class="button small" >↓</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php $first = false;
                endif;
            endforeach; ?>
            </tbody>
        </table>

        <br>
<!--        <button type="submit" style="float:right;" class="button">--><?php //esc_html_e( "Save", 'disciple_tools' ) ?><!--</button>-->


    <?php }


    private function process_edit_tile( $post_submission ){
        //save values
        $post_type = $post_submission["post_type"];
        $tile_options = dt_get_option( "dt_custom_tiles" );
        $sections = apply_filters( 'dt_details_additional_section_ids', [], $post_type );
        foreach ( $sections as $section_id ){
            if ( !isset( $tile_options[$post_type][$section_id] ) ){
                $tile_options[$post_type][$section_id] = [];
            }
        }
        $tile_key = $post_submission["tile_key"];
        if ( !isset( $tile_options[$post_type][$tile_key] )){
            self::admin_notice( __( "Tile not found", 'disciple_tools' ), "error" );
            return;
        }
        $post_fields = $this->get_post_fields( $post_type );
        if ( !isset( $tile_options[$post_type][$tile_key] ) ){
            $tile_options[$post_type][$tile_key] = [];
        }
        $custom_tile = $tile_options[$post_type][$tile_key];

        if ( isset( $post_submission["tile_label"] ) && $post_submission["tile_label"] != ( $custom_tile["label"] ?? $tile_key )){
            $custom_tile["label"] = $post_submission["tile_label"];
        }
        if ( isset( $post_submission["hide_tile"] ) ){
            $custom_tile["hidden"] = true;
        }
        if ( isset( $post_submission["restore_tile"] ) ){
            $custom_tile["hidden"] = false;
        }
        //update other Translations
        $langs = dt_get_available_languages();

        foreach ( $langs as $lang => $val ){
            $langcode = $val['language'];
            $translation_key = "tile_label_translation-" . $langcode;
            if ( isset( $post_submission[$translation_key] ) ) {
                $custom_tile["translations"][$langcode] = $post_submission[$translation_key];
            }
        }


        //move option  up or down
        if ( isset( $post_submission["move_up"] ) || isset( $post_submission["move_down"] )){
            $option_key = $post_submission["move_up"] ?? $post_submission["move_down"];
            $direction = isset( $post_submission["move_up"] ) ? -1 : 1;
            $keys = $custom_tile["order"] ?? [];
            foreach ( $post_fields as $field_key => $field_val ){
                if ( ( isset( $field_val["tile"] ) && $field_val["tile"] == $tile_key ) ){
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
            $custom_tile["order"] = $order;
        }

        if ( !empty( $custom_tile )){
            $tile_options[$post_type][$tile_key] = $custom_tile;
        }

        update_option( "dt_custom_tiles", $tile_options );
    }



    private function add_tile(){
        global $wp_post_types;
        $post_types = apply_filters( 'dt_registered_post_types', [] );
        ?>
        <form method="post">
            <input type="hidden" name="tile_add_nonce" id="tile_add_nonce" value="<?php echo esc_attr( wp_create_nonce( 'tile_add' ) ) ?>" />
            <table>
                <tr>
                    <td style="vertical-align: middle">
                        <?php esc_html_e( "Page type", 'disciple_tools' ) ?>
                    </td>
                    <td>
                        <select name="post_type">
                            <?php foreach ( $post_types as $post_type ) : ?>
                                <option value="<?php echo esc_html( $post_type ); ?>"><?php echo esc_html( $wp_post_types[$post_type]->label ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: middle">
                        <label for="new_tile_name"><?php esc_html_e( "New Tile Name", 'disciple_tools' ) ?></label>
                    </td>
                    <td>
                        <input name="new_tile_name" id="new_tile_name" required>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: middle">
                    </td>
                    <td>
                        <button type="submit" class="button"><?php esc_html_e( "Create Tile", 'disciple_tools' ) ?></button>
                    </td>
                </tr>
            </table>

        </form>
        <?php
    }

    private function process_add_tile( $post_submission ){
        if ( isset( $post_submission["new_tile_name"], $post_submission["post_type"] ) ){
            $post_type = $post_submission["post_type"];
            $tile_options = dt_get_option( "dt_custom_tiles" );
            $sections = apply_filters( 'dt_details_additional_section_ids', [], "contacts" );
            $tile_key = dt_create_field_key( $post_submission["new_tile_name"] );
            if ( in_array( $tile_key, array_keys( $tile_options[$post_type] ) ) || in_array( $tile_key, $sections ) ){
                self::admin_notice( __( "tile already exists", 'disciple_tools' ), "error" );
                return false;
            }
            if ( !isset( $tile_options[$post_type] ) ){
                $tile_options[$post_type] = [];
            }
            $tile_options[$post_type][$tile_key] = [ "label" => $post_submission["new_tile_name"] ];

            update_option( "dt_custom_tiles", $tile_options );
            self::admin_notice( __( "tile added successfully", 'disciple_tools' ), "success" );
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


