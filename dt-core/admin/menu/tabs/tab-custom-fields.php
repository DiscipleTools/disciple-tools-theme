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
class Disciple_Tools_Tab_Custom_Fields extends Disciple_Tools_Abstract_Menu_Base
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
        add_submenu_page( 'dt_options', __( 'Custom Fields', 'disciple_tools' ), __( 'Custom Fields', 'disciple_tools' ), 'manage_dt', 'dt_options&tab=custom-fields', [ 'Disciple_Tools_Settings_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        ?>
        <a href="<?php echo esc_url( admin_url() ) ?>admin.php?page=dt_options&tab=custom-fields"
           class="nav-tab <?php echo esc_html( $tab == 'custom-fields' ? 'nav-tab-active' : '' ) ?>">
            <?php echo esc_html__( 'Custom Fields' ) ?>
        </a>
        <?php
    }

    private function get_post_fields( $post_type ){
        if ( $post_type === "groups" ){
            return Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings( null, null, true, false );
        } elseif ( $post_type === "contacts" ){
            return Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings( null, null, true, false );
        } else {
            $post_settings = apply_filters( "dt_get_post_type_settings", [], $post_type, false );
            return isset( $post_settings["fields"] ) ? $post_settings["fields"] : null;
        }
    }

    /**
     * Packages and prints tab page
     *
     * @param $tab
     */
    public function content( $tab ) {
        if ( 'custom-fields' == $tab ) :
            $show_add_field = false;
            $field_key = false;
            $post_type = null;
            $this->template( 'begin' );

            if ( isset( $_POST['field_select_nonce'] ) ){
                if ( !wp_verify_nonce( sanitize_key( $_POST['field_select_nonce'] ), 'field_select' ) ) {
                    return;
                }
                if ( isset( $_POST["show_add_new_field"] ) ){
                    $show_add_field = true;
                } else if ( !empty( $_POST["field-select"] ) ){
                    $field = explode( "_", sanitize_text_field( wp_unslash( $_POST["field-select"] ) ), 2 );
                    $field_key = $field[1];
                    $post_type = $field[0];
                }
            }


            /*
             * Process Add field
             */
            if ( isset( $_POST["new_field_type"], $_POST['field_add_nonce'] ) ){
                if ( !wp_verify_nonce( sanitize_key( $_POST['field_add_nonce'] ), 'field_add' ) ) {
                    return;
                }
                $post_submission = [];
                foreach ( $_POST as $key => $value ){
                    $post_submission[sanitize_text_field( wp_unslash( $key ) )] = sanitize_text_field( wp_unslash( $value ) );
                }
                $field_key = $this->process_add_field( $post_submission );
                if ( $field_key === false ){
                    $show_add_field = true;
                }
                $post_type = $post_submission["post_type"];
            }
            /*
             * Process Edit field
             */
            if ( isset( $_POST["field_edit_nonce"] ) ){
                if ( !wp_verify_nonce( sanitize_key( $_POST['field_edit_nonce'] ), 'field_edit' ) ) {
                    return;
                }
                $post_submission = [];

                foreach ( $_POST as $key => $value ){
                    $post_submission[sanitize_text_field( wp_unslash( $key ) )] = sanitize_text_field( wp_unslash( $value ) );
                }
                $this->process_edit_field( $post_submission );
            }

            $this->box( 'top', __( 'Add new fields or modify existing ones on Contacts or Groups', 'disciple_tools' ) );
            $this->field_select();
            $this->box( 'bottom' );


            if ( $show_add_field ){
                $this->box( 'top', __( "Create new field", 'disciple_tools' ) );
                $this->add_field();
                $this->box( 'bottom' );
            }
            if ( $this->get_post_fields( $post_type )[$field_key] && $post_type ){
                $this->box( 'top', $this->get_post_fields( $post_type )[$field_key]["name"] );
                $this->edit_field( $field_key, $post_type );
                $this->box( 'bottom' );
            }

            $this->template( 'right_column' );

            $this->template( 'end' );
        endif;
    }

    private function field_select(){
        global $wp_post_types;
        $select_options = [];
        $post_types = apply_filters( 'dt_registered_post_types', [ 'contacts', 'groups' ] );
        foreach ( $post_types as $post_type ){
            $select_options[$post_type] = [];
            $fields = $this->get_post_fields( $post_type );
            if ( $fields ){
                foreach ( $fields as $field_key => $field_value ){
                    if ( ( isset( $field_value["customizable"] ) && $field_value["customizable"] !== false ) || ( !isset( $field_value["customizable"] ) && empty( $field_value["hidden"] ) ) ){
                        $select_options[ $post_type ][ $field_key ] = $field_value;
                    }
                }
            }
        }

        ?>
        <form method="post">
            <input type="hidden" name="field_select_nonce" id="field_select_nonce" value="<?php echo esc_attr( wp_create_nonce( 'field_select' ) ) ?>" />
            <table>
                <tr>
                    <td style="vertical-align: middle">
                        <label for="field-select"><?php esc_html_e( "Modify an existing field", 'disciple_tools' ) ?></label>
                    </td>
                    <td>
                        <select id="field-select" name="field-select">
                            <option></option>
                            <?php foreach ( $post_types as $post_type ) : ?>
                                <option disabled>---<?php echo esc_html( $wp_post_types[$post_type]->label ); ?> Fields---</option>
                                <?php foreach ( $select_options[$post_type] as $option_key => $option_value ) : ?>

                                <option value="<?php echo esc_html( $post_type . '_' . $option_key ) ?>">
                                    <?php echo esc_html( $option_value["name"] ?? $option_key ) ?>
                                    <span> - (<?php echo esc_html( $option_key ) ?>)</span>
                                </option>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="button" name="field_selected"><?php esc_html_e( "Select", 'disciple_tools' ) ?></button>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: middle">
                        <?php esc_html_e( "Create a new field", 'disciple_tools' ) ?>
                    </td>
                    <td>
                        <button type="submit" class="button" name="show_add_new_field"><?php esc_html_e( "Create new field", 'disciple_tools' ) ?></button>
                    </td>
                </tr>
            </table>

            <br>
        </form>

    <?php }

    private function edit_field( $field_key, $post_type ){

        $post_fields = $this->get_post_fields( $post_type );
        if ( !isset( $post_fields[$field_key]["default"] ) ) {
            wp_die( 'Failed to get dt_site_custom_lists() from options table. Or field is missing the "default" field key' );
        }
        $field = $post_fields[$field_key];

        if ( isset( $field["customizable"] ) && $field["customizable"] === false ){
            ?>
            <p>
                <strong>This field is not customizable</strong>
            </p>
            <?php
            return;
        }

        $defaults = [];
        if ( $post_type === "contacts" ){
            $defaults = Disciple_Tools_Contact_Post_Type::instance()->get_contact_field_defaults();
        } elseif ( $post_type === "groups" ){
            $defaults = Disciple_Tools_Groups_Post_Type::instance()->get_group_field_defaults();
        }

        $field_options = $field["default"];

        $first = true;
        $tile_options = dt_get_option( "dt_custom_tiles" );
        $sections = apply_filters( 'dt_details_additional_section_ids', [], $post_type );
        if ( !isset( $tile_options[$post_type] ) ){
            $tile_options[$post_type] = [];
        }
        foreach ( $sections as $section_id ){
            $tile_options[$post_type][$section_id] = [];
        }

        $langs = dt_get_available_languages();
        ?>
        <form method="post" name="field_edit_form">
        <input type="hidden" name="field_key" value="<?php echo esc_html( $field_key )?>">
        <input type="hidden" name="post_type" value="<?php echo esc_html( $post_type )?>">
        <input type="hidden" name="field-select" value="<?php echo esc_html( $post_type . "_" . $field_key )?>">
        <input type="hidden" name="field_select_nonce" id="field_select_nonce" value="<?php echo esc_attr( wp_create_nonce( 'field_select' ) ) ?>" />
        <input type="hidden" name="field_edit_nonce" id="field_edit_nonce" value="<?php echo esc_attr( wp_create_nonce( 'field_edit' ) ) ?>" />

        <h3><?php esc_html_e( "Field Settings", 'disciple_tools' ) ?></h3>
        <table class="widefat">
            <thead>
            <tr>
                <td><?php esc_html_e( "Key", 'disciple_tools' ) ?></td>
                <td><?php esc_html_e( "Label", 'disciple_tools' ) ?></td>
                <td><?php esc_html_e( "Tile", 'disciple_tools' ) ?></td>
                <td><?php esc_html_e( "Translation", 'disciple_tools' ) ?></td>
                <td></td>
                <td></td>
            </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <?php echo esc_html( $field_key ) ?>
                    </td>
                    <td>
                        <input name="field_key_<?php echo esc_html( $field_key )?>" type="text" value="<?php echo esc_html( $field["name"] ) ?>"/>
                    </td>
                    <td>
                        <?php if ( !isset( $defaults[$field_key] ) ) : ?>
                            <select name="tile_select">
                                <option><?php esc_html_e( "No tile / hidden", 'disciple_tools' ) ?></option>
                                <?php foreach ( $tile_options[$post_type] as $tile_key => $tile_option ) :
                                    $select = isset( $field["tile"] ) && $field["tile"] === $tile_key;
                                    ?>
                                    <option value="<?php echo esc_html( $tile_key ) ?>" <?php echo esc_html( $select ? "selected" : "" )?>>
                                        <?php echo esc_html( $tile_option["label"] ?? $tile_key ) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="button small expand_translations">+</button>
                        <div class="translation_container hide">
                        <table>

                        <?php foreach ( $langs as $lang => $val ) : ?>
                                    <tr>
                                    <td><label for="field_key_<?php echo esc_html( $field_key )?>_translation-<?php echo esc_html( $val['language'] )?>"><?php echo esc_html( $val['native_name'] )?></label></td>
                                    <td><input name="field_key_<?php echo esc_html( $field_key )?>_translation-<?php echo esc_html( $val['language'] )?>" type="text" value="<?php echo esc_html( $field["translations"][$val['language']] ?? "" );?>"/></td>
                                    </tr>
                            <?php endforeach; ?>
                        </table>
                        </div>
                    </td>
                    <td>
                        <button type="submit" name="save" class="button"><?php esc_html_e( "Save", 'disciple_tools' ) ?></button>
                    </td>
                    <td>
                    <?php
                    $custom_fields = dt_get_option( "dt_field_customizations" );
                    $custom_field = $custom_fields[$post_type][$field_key] ?? [];
                    if ( isset( $custom_field["customizable"] ) && $custom_field["customizable"] == "all" ) : ?>
                        <button type="submit" name="delete"  class="button"><?php esc_html_e( "Delete", 'disciple_tools' ) ?></button>
                    <?php endif ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <br>

            <?php if ( $field["type"] === "key_select" || $field["type"] === "multi_select" ){
                ?>

                <h3><?php esc_html_e( "Field Options", 'disciple_tools' ) ?></h3>
                <table id="add_option" style="">
                    <tr>
                        <td style="vertical-align: middle">
                            <?php esc_html_e( "Add new option", 'disciple_tools' ) ?>
                        </td>
                        <td>
                            <input type="text" name="add_option" placeholder="label" />
                            <button type="submit" class="button"><?php echo esc_html( __( 'Add', 'disciple_tools' ) ) ?></button>
                        </td>
                    </tr>
                </table>
                <br>
                <table class="widefat">
                    <thead>
                    <tr>
                        <td><?php esc_html_e( "Key", 'disciple_tools' ) ?></td>
                        <td><?php esc_html_e( "Label", 'disciple_tools' ) ?></td>
                        <td><?php esc_html_e( "Move", 'disciple_tools' ) ?></td>
                        <td><?php esc_html_e( "Hide/Archive", 'disciple_tools' ) ?></td>
                        <td><?php esc_html_e( "Translation", 'disciple_tools' ) ?></td>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ( $field_options as $key => $option ) :

                        if ( !( isset( $option["deleted"] ) && $option["deleted"] == true ) ):
                            $label = $option["label"] ?? "";
                            $in_defaults = isset( $defaults[$field_key]["default"][$key] );
                            ?>
                            <tr>
                                <td>
                                    <?php echo esc_html( $key ) ?>
                                </td>
                                <td>
                                    <input name="field_option_<?php echo esc_html( $key )?>" type="text" value="<?php echo esc_html( $label ) ?>"/>
                                </td>
                                <td>
                                    <?php if ( !$first ) : ?>
                                        <button type="submit" name="move_up" value="<?php echo esc_html( $key ) ?>" class="button small" >↑</button>
                                        <button type="submit" name="move_down" value="<?php echo esc_html( $key ) ?>" class="button small" >↓</button>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ( ( isset( $field["customizable"] ) && ( $field["customizable"] === "all" || ( $field["customizable"] === 'add_only' && !$in_defaults ) ) )
                                        || !isset( $field["default"][$key] ) ) : ?>
                                    <button type="submit" name="delete_option" value="<?php echo esc_html( $key ) ?>" class="button small" ><?php esc_html_e( "Hide", 'disciple_tools' ) ?></button>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="button small expand_translations">+</button>
                                    <div class="translation_container hide">
                                    <table>
                                    <?php
                                    foreach ( $langs as $lang => $val ) : ?>
                                            <tr>
                                            <td><label for="field_option_<?php echo esc_html( $key )?>_translation-<?php echo esc_html( $val['language'] )?>"><?php echo esc_html( $val['native_name'] )?></label></td>
                                            <td><input name="field_option_<?php echo esc_html( $key )?>_translation-<?php echo esc_html( $val['language'] )?>" type="text" value="<?php echo esc_html( $custom_fields[$post_type][$field_key]["default"][$key]["translations"][$val['language']] ?? "" );?>"/></td>
                                            </tr>
                                    <?php endforeach; ?>
                                    </table>
                                    </div>
                                </td>
                            </tr>
                            <?php $first = false;
                        endif;
                    endforeach; ?>
    <!--                <tr style="padding-top:10px; border-top:solid black;"><td>Deleted options:</td></tr>-->
                    <?php foreach ( $field_options as $key => $option ) :
                        $label = $option["label"] ?? "";
                        if ( isset( $option["deleted"] ) && $option["deleted"] == true ): ?>
                            <tr style="background-color: #eee">
                                <td><?php echo esc_html( $key ) ?></td>
                                <td><?php echo esc_html( $label ) ?></td>
                                <td></td>
                                <td>
                                    <button type="submit" name="restore_option" value="<?php echo esc_html( $key ) ?>" class="button small" ><?php esc_html_e( "Restore", 'disciple_tools' ) ?></button>
                                </td>
                            </tr>
                        <?php endif;
                    endforeach; ?>
                    </tbody>
                </table>

                <br>
    <!--            <button type="button" onclick="jQuery('#add_seeker_path').toggle();" class="button">--><?php //esc_html_e( "Add", 'disciple_tools' ) ?><!--</button>-->
                <button type="submit" style="float:right;" class="button"><?php esc_html_e( "Save", 'disciple_tools' ) ?></button>


            <?php } else { ?>


        <?php }

    }

    private static function moveElement( &$array, $a, $b ) {
        $out = array_splice( $array, $a, 1 );
        array_splice( $array, $b, 0, $out );
    }


    private function process_edit_field( $post_submission ){
        //save values
        $post_type = $post_submission["post_type"];
        $post_fields = $this->get_post_fields( $post_type );
        $field_customizations = dt_get_option( "dt_field_customizations" );
        $field_key = $post_submission["field_key"];
        $langs = dt_get_available_languages();
        if ( isset( $post_submission["delete"] ) ){
            if ( isset( $field_customizations[$post_type][$field_key] ) ){
                unset( $field_customizations[$post_type][$field_key] );
            }
            update_option( "dt_field_customizations", $field_customizations );
            wp_cache_delete( $post_type . "_field_settings" );
            return;
        }


        if ( isset( $post_fields[$post_submission["field_key"]]["default"] )){
            if ( !isset( $field_customizations[$post_type][$field_key] ) ){
                $field_customizations[$post_type][$field_key] = [];
            }
            $custom_field = $field_customizations[$post_type][$field_key];
            $field = $post_fields[$field_key];
            $field_options = $field["default"];
            if ( isset( $post_fields[$field_key] ) ){
                //update name
                if ( isset( $post_submission["field_key_" . $field_key] ) ){
                    $custom_field["name"] = $post_submission["field_key_" . $field_key];
                    foreach ( $langs as $lang => $val ){
                        $langcode = $val['language'];
                        $translation_key = "field_key_" . $field_key . "_translation-" . $langcode;
                        if ( isset( $post_submission[$translation_key] ) ) {
                            $custom_field["translations"][$langcode] = $post_submission[$translation_key];
                        }
                    }
                }
                if ( isset( $post_submission["tile_select"] ) ){
                    $custom_field["tile"] = $post_submission["tile_select"];
                }
            }
            if ( $field["type"] === 'multi_select' || $field["type"] === "key_select" ){
                foreach ( $post_submission as $key => $val ){
                    if ( strpos( $key, "field_option_" ) === 0) {
                        if ( strpos( $key, 'translation' ) !== false ) {
                            $option_key = substr( $key, 13, strpos( $key, 'translation' ) - 14 );
                            $translation_langcode = substr( $key, -5 );
                            if ( strpos( $translation_langcode, '-' ) !== false ) {
                                $translation_langcode = substr( $translation_langcode, 3 );
                            }
                            $custom_field["default"][$option_key]["translations"][$translation_langcode] = $val;
                        } else {
                            $option_key = substr( $key, 13 );
                            if ( isset( $field_options[$option_key]["label"] ) ){
                                if ( $field_options[$option_key]["label"] != $val ){
                                    $custom_field[$option_key]["label"] = $val;
                                }
                                $field_options[$option_key]["label"] = $val;
                            }
                        }
                    }
                }
                //delete option
                if ( isset( $post_submission["delete_option"] ) ){
                    $custom_field["default"][$post_submission["delete_option"]]["deleted"] = true;
                    $field_options[ $post_submission["delete_option"] ]["deleted"] = true;
                }
                //delete option
                if ( isset( $post_submission["restore_option"] ) ){
                    $custom_field["default"][$post_submission["restore_option"]]["deleted"] = false;
                    $field_options[ $post_submission["restore_option"] ]["deleted"] = false;
                }
                //move option  up or down
                if ( isset( $post_submission["move_up"] ) || isset( $post_submission["move_down"] )){
                    $option_key = $post_submission["move_up"] ?? $post_submission["move_down"];
                    $direction = isset( $post_submission["move_up"] ) ? -1 : 1;
                    $active_field_options = [];
                    foreach ( $field_options as $field_option_key => $field_option_val ){
                        if ( !( isset( $field_option_val["deleted"] ) && $field_option_val["deleted"] == true ) ){
                            $active_field_options[strval( $field_option_key )] = $field_option_val;
                        }
                    }
                    $pos = (int) array_search( $option_key, array_keys( $active_field_options ) );
                    $option_keys = array_keys( $active_field_options );
                    self::moveElement( $option_keys, $pos, $pos + $direction );
                    $custom_field["order"] = $option_keys;
                }
                /*
                 * add option
                 */
                if ( !empty( $post_submission["add_option"] ) ){
                    $option_key = dt_create_field_key( $post_submission["add_option"] );
                    if ( !isset( $field_options[$option_key] )){
                        if ( !empty( $option_key ) && !empty( $post_submission["add_option"] )){
                            $field_options[ $option_key ] = [ "label" => $post_submission["add_option"] ];
                            $custom_field["default"][$option_key] = [ "label" => $post_submission["add_option"] ];
                        }
                    } else {
                        self::admin_notice( __( "This option already exists", 'disciple_tools' ), "error" );
                    }
                }
            }
            if ( !empty( $custom_field )){
                $field_customizations[$post_type][$field_key] = $custom_field;
            }
            update_option( "dt_field_customizations", $field_customizations );
            wp_cache_delete( $post_type . "_field_settings" );
        }
    }


    private function add_field(){
        global $wp_post_types;
        $tile_options = dt_get_option( "dt_custom_tiles" );
        $post_types = apply_filters( 'dt_registered_post_types', [ 'contacts', 'groups' ] );
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
            <input type="hidden" name="field_add_nonce" id="field_add_nonce" value="<?php echo esc_attr( wp_create_nonce( 'field_add' ) ) ?>" />
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
                <tr>
                    <td style="vertical-align: middle">
                        <label for="new_field_name"><?php esc_html_e( "New Field Name", 'disciple_tools' ) ?></label>
                    </td>
                    <td>
                        <input name="new_field_name" id="new_field_name" required>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: middle">
                        <label><?php esc_html_e( "Field type", 'disciple_tools' ) ?></label>
                    </td>
                    <td>
                        <select name="new_field_type" required>
                            <option></option>
                            <option value="key_select"><?php esc_html_e( "Dropdown", 'disciple_tools' ) ?></option>
                            <option value="multi_select"><?php esc_html_e( "Multi Select", 'disciple_tools' ) ?></option>
                            <option value="text"><?php esc_html_e( "Text", 'disciple_tools' ) ?></option>
                            <option value="date"><?php esc_html_e( "Date", 'disciple_tools' ) ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: middle">
                        <label><?php esc_html_e( "Tile", 'disciple_tools' ) ?></label>
                    </td>
                    <td>
                        <select name="new_field_tile">
                            <option><?php esc_html_e( "No tile", 'disciple_tools' ) ?></option>
                            <?php foreach ( $post_types as $post_type ) : ?>
                                <option disabled>---<?php echo esc_html( $wp_post_types[$post_type]->label ); ?> tiles---</option>
                                <?php foreach ( $tile_options[$post_type] as $option_key => $option_value ) : ?>
                                    <option value="<?php echo esc_html( $option_key ) ?>">
                                        <?php echo esc_html( $option_value["label"] ?? $option_key ) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: middle">
                    </td>
                    <td>
                        <button type="submit" class="button"><?php esc_html_e( "Create Field", 'disciple_tools' ) ?></button>
                    </td>
                </tr>
            </table>
        </form>
        <br>
        <?php esc_html_e( "Field types:", 'disciple_tools' ) ?>
        <ul style="list-style: disc; padding-left:40px">
            <li><?php esc_html_e( "Dropdown: Select an option for a dropdown list", 'disciple_tools' ) ?></li>
            <li><?php esc_html_e( "Multi Select: A field like the milestones to track items like course progress", 'disciple_tools' ) ?></li>
            <li><?php esc_html_e( "Text: This is just a normal text field", 'disciple_tools' ) ?></li>
            <li><?php esc_html_e( "Date: A field that uses a date picker to choose dates (like baptism date)", 'disciple_tools' ) ?></li>
        </ul>
        <?php
    }

    private function process_add_field( $post_submission ){
        if ( isset( $post_submission["new_field_name"], $post_submission["new_field_type"], $post_submission["post_type"] ) ){
            $post_type = $post_submission["post_type"];
            $field_type = $post_submission["new_field_type"];
            $field_tile = $post_submission["new_field_tile"] ?? '';
            $field_key = dt_create_field_key( $post_submission["new_field_name"] );
            if ( !$field_key ){
                return false;
            }
            $post_fields = $this->get_post_fields( $post_type );
            if ( isset( $post_fields[ $field_key ] )){
                self::admin_notice( __( "Field already exists", 'disciple_tools' ), "error" );
                return false;
            }
            $new_field = [];
            if ( $field_type === "key_select" ){
                $new_field = [
                    'name' => $post_submission["new_field_name"],
                    'default' => [],
                    'type' => 'key_select',
                    'tile' => $field_tile,
                    'customizable' => 'all'
                ];
            } elseif ( $field_type === "multi_select" ){
                $new_field = [
                    'name' => $post_submission["new_field_name"],
                    'default' => [],
                    'type' => 'multi_select',
                    'tile' => $field_tile,
                    'customizable' => 'all'
                ];
            } elseif ( $field_type === "date" ){
                $new_field = [
                    'name'        => $post_submission["new_field_name"],
                    'type'        => 'date',
                    'default'     => '',
                    'tile'     => $field_tile,
                    'customizable' => 'all'
                ];
            } elseif ( $field_type === "text" ){
                $new_field = [
                    'name'        => $post_submission["new_field_name"],
                    'type'        => 'text',
                    'default'     => '',
                    'tile'     => $field_tile,
                    'customizable' => 'all'
                ];
            }

            $custom_field_options = dt_get_option( "dt_field_customizations" );
            $custom_field_options[$post_type][$field_key] = $new_field;
            update_option( "dt_field_customizations", $custom_field_options );
            wp_cache_delete( $post_type . "_field_settings" );
            self::admin_notice( __( "Field added successfully", 'disciple_tools' ), "success" );
            return $field_key;
        }
        return false;
    }

    /**
     * Display admin notice
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
Disciple_Tools_Tab_Custom_Fields::instance();


