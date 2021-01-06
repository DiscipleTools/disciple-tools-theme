<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Tab_Custom_Fields
 */
class Disciple_Tools_Tab_Custom_Create extends Disciple_Tools_Abstract_Menu_Base
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
        add_submenu_page( 'dt_options', __( 'New Record Page', 'disciple_tools' ), __( 'New Record Page', 'disciple_tools' ), 'manage_dt', 'dt_options&tab=custom-create', [ 'Disciple_Tools_Settings_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        ?>
        <a href="<?php echo esc_url( admin_url() ) ?>admin.php?page=dt_options&tab=custom-create"
           class="nav-tab <?php echo esc_html( $tab == 'custom-create' ? 'nav-tab-active' : '' ) ?>">
            <?php echo esc_html__( 'New Record Page' ) ?>
        </a>
        <?php
    }

    /**
     * Packages and prints tab page
     *
     * @param $tab
     */
    public function content( $tab ) {
        if ( 'custom-create' == $tab ) :
            $post_type = null;

            $this->template( 'begin' );

            if ( isset( $_POST['post_type_select_nonce'] ) ){
                if ( !wp_verify_nonce( sanitize_key( $_POST['post_type_select_nonce'] ), 'post_type_select' ) ) {
                    return;
                }
                if ( isset( $_POST["post_type"] ) ){
                    $post_type = sanitize_key( $_POST["post_type"] );
                }
            }


            /*
             * Process Edit tile
             */
            if ( isset( $_POST["create_fields_edit_nonce"] ) ){
                if ( !wp_verify_nonce( sanitize_key( $_POST['create_fields_edit_nonce'] ), 'create_fields_edit' ) ) {
                    return;
                }
                $post_submission = [];
                foreach ( $_POST as $key => $value ){
                    $post_submission[sanitize_text_field( wp_unslash( $key ) )] = sanitize_text_field( wp_unslash( $value ) );
                }
                $post_submission = dt_sanitize_array_html( $_POST );
                $this->process_edit_create_fields( $post_submission );
            }

            $this->box( 'top', __( 'Choose which fields to include when creating a new record', 'disciple_tools' ) );
            $this->post_type_select();
            $this->box( 'bottom' );



            if ( $post_type ){
                $fields = DT_Posts::get_post_field_settings( $post_type );
                $this->box( 'top', "Select default fields to display on new contact form for all types" );
                $this->edit_create_fields( $post_type );
                $this->box( 'bottom' );

                if ( isset( $fields["type"]["default"] ) ){
                    foreach ( $fields["type"]["default"] as $type_key => $type_settings ){
                        if ( empty( $type_settings["hidden"] ) ){
                            $this->box( 'top', "Select fields to display for " . $type_settings["label"] . " contacts" );
                            $this->edit_create_fields( $post_type, $type_key );
                            $this->box( 'bottom' );
                        }
                    }
                }
            }

            $this->template( 'right_column' );

            $this->template( 'end' );
        endif;
    }

    private function post_type_select(){
        global $wp_post_types;
        $post_types = DT_Posts::get_post_types();
        ?>
        <form method="post">
            <input type="hidden" name="post_type_select_nonce" id="post_type_select_nonce" value="<?php echo esc_attr( wp_create_nonce( 'post_type_select' ) ) ?>" />
            <table>
                <tr>
                    <td style="vertical-align: middle">
                        <label for="tile-select"><?php esc_html_e( "Choose which post type to modify:", 'disciple_tools' ) ?></label>
                    </td>
                    <td>
                        <?php foreach ( $post_types as $post_type ) : ?>
                            <button type="submit" name="post_type" class="button" value="<?php echo esc_html( $post_type ); ?>"><?php echo esc_html( $wp_post_types[$post_type]->label ); ?></button>
                        <?php endforeach; ?>
                    </td>
                </tr>
            </table>
            <br>
        </form>

    <?php }

    private function edit_create_fields( $post_type, $type_key = null ){
        $fields = DT_Posts::get_post_field_settings( $post_type, false );

        $tile_options = DT_Posts::get_post_tiles( $post_type );

        foreach ( $fields as $field_key => &$field_value ) {
            if ( !isset( $field_value["tile"] ) && ( !isset( $field_value["hidden"] ) || $field_value["hidden"] === false ) ){
                $field_value['tile'] = "no_tile";
            }
            if ( !isset( $field_value["in_create_form"] ) ) {
                $field_value["in_create_form"] = false;
            }
        }

        ?>
        <form method="post" name="create_edit_form">
            <input type="hidden" name="post_type" value="<?php echo esc_html( $post_type )?>">
            <input type="hidden" name="post_type_select_nonce" id="post_type_select_nonce" value="<?php echo esc_attr( wp_create_nonce( 'post_type_select' ) ) ?>" />
            <input type="hidden" name="create_fields_edit_nonce" id="create_fields_edit_nonce" value="<?php echo esc_attr( wp_create_nonce( 'create_fields_edit' ) ) ?>" />

            <div style="display:flex; flex-wrap:wrap">
            <?php foreach ( $tile_options as $tile_key => $tile) :
                $index = 0;
                ?>
                <div style="flex-basis:20%">
                    <div style="border:1px solid;margin:10px; padding:10px;">
                        <h3><?php echo esc_html( $tile["label"] ?? $tile_key ); ?></h3>
                        <div>
                            <?php foreach ( $fields as $field_key => $field_settings ):
                                if ( ( $field_settings["tile"] ?? "" ) === $tile_key ) :
                                    $index++;
                                    $disabled = ( $type_key && $field_settings["in_create_form"] === true ) ? 'disabled' : '';
                                    $checked = $field_settings["in_create_form"] === true ? "checked" : '';
                                    if ( $type_key && ( $field_settings["in_create_form"] === true || ( is_array( $field_settings["in_create_form"] ) && in_array( $type_key, $field_settings["in_create_form"] ) ) ) ){
                                        $checked = 'checked';
                                    }
                                    ?>
                                    <div style="display: flex; flex-direction: row; flex-wrap: nowrap; justify-content: space-between">
                                        <span><?php echo esc_html( $index ); ?>.</span>
                                        <span style="padding:0 5px; flex-grow:1"><?php echo esc_html( $field_settings["name"] ); ?></span>
                                        <span><input type="checkbox" name="create_fields[<?php echo esc_html( $field_key ); ?>]" <?php echo esc_html( $checked . " " . $disabled ); ?>></span>
                                    </div>
                                <?php endif;
                            endforeach; ?>
                        </div>
                    </div>
                </div>


            <?php endforeach; ?>
            </div>

            <div>
                <button type="submit" class="button" name="type_key" value="<?php echo esc_html( $type_key ); ?>">Save</button>
            </div>
        </form>




    <?php }


    private function process_edit_create_fields( $post_submission ){
        //save values
        $post_type = $post_submission["post_type"];
        $field_settings = DT_Posts::get_post_field_settings( $post_type );
        $custom_field_options = dt_get_option( "dt_field_customizations" );
        $type_key = !empty( $post_submission["type_key"] ) ? $post_submission["type_key"] : null;

        if ( empty( $post_submission["create_fields"] ) ) {
            $post_submission["create_fields"] = [];
        }
        if ( !isset( $custom_field_options[$post_type] ) ) {
            $custom_field_options[$post_type] = [];
        }

        foreach ( $post_submission["create_fields"] as $field_key => $on ) {
            if ( !isset( $custom_field_options[$post_type][$field_key] ) ) {
                $custom_field_options[$post_type][$field_key] = [];
            }
            if ( !isset( $custom_field_options[$post_type][$field_key]["in_create_form"] ) ){
                $custom_field_options[$post_type][$field_key]["in_create_form"] = [];
            }
            if ( $type_key ){
                if ( is_array( $custom_field_options[$post_type][$field_key]["in_create_form"] ) && !in_array( $type_key, $custom_field_options[$post_type][$field_key]["in_create_form"] ) ){
                    $custom_field_options[$post_type][$field_key]["in_create_form"][] = $type_key;
                }
            } else {
                $custom_field_options[$post_type][$field_key]["in_create_form"] = true;
            }
        }
        foreach ( $field_settings as $key => $val ) {
            //if the field has been removed from the create record form
            if ( isset( $val["in_create_form"] ) && $val["in_create_form"] === true && !isset( $post_submission["create_fields"][$key] ) ) {
                if ( !$type_key && $custom_field_options[$post_type][$key]["in_create_form"] === true ){
                    $custom_field_options[$post_type][$key]["in_create_form"] = false;
                }
            }
            if ( isset( $val["in_create_form"] ) && is_array( $val["in_create_form"] ) && in_array( $type_key, $val["in_create_form"] ) && !isset( $post_submission["create_fields"][$key] ) ){
                if ( isset( $custom_field_options[$post_type][$key]["in_create_form"] ) && is_array( $custom_field_options[$post_type][$key]["in_create_form"] ) ){
                    $index = array_search( $type_key, $custom_field_options[$post_type][$key]["in_create_form"] );
                    unset( $custom_field_options[$post_type][$key]["in_create_form"][$index] );
                }
            }
        }
        update_option( "dt_field_customizations", $custom_field_options );

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
Disciple_Tools_Tab_Custom_Create::instance();


