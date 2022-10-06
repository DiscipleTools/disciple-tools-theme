<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Disciple_Tools_Customizations_Tab extends Disciple_Tools_Abstract_Menu_Base
{
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor function.
     *
     * @access  public
     * @since   0.1.0
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_submenu' ], 99 );
        add_action( 'dt_customizations_tab_content', [ $this, 'content' ], 99, 1 );
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
        parent::__construct();
    }

    public function add_submenu() {
        $post_types = DT_Posts::get_post_types();
        foreach( $post_types as $post_type ) {
            $post_type_label = DT_Posts::get_label_for_post_type( $post_type );
            add_submenu_page( 'dt_customizations', __( $post_type_label, 'disciple_tools' ), __( $post_type_label, 'disciple_tools' ), 'manage_dt', "dt_customizations&post_type=$post_type", [ 'Disciple_Tools_Customizations_Menu', 'content' ] );
        }
    }

    public function admin_enqueue_scripts() {
        dt_theme_enqueue_script( 'typeahead-jquery', 'dt-core/dependencies/typeahead/dist/jquery.typeahead.min.js', array( 'jquery' ), true );
        wp_enqueue_script( 'jquery' );
    }

    public function content() {
        self::template( 'begin', 2 );
        
        $this->box( 'top', __( 'Select a Post Type', 'disciple_tools' ) );
        $this->show_post_type_pills();
        $this->box( 'bottom' );
        $this->template( 'right_column' );
        $this->box( 'top', __( 'Search', 'disciple_tools' ) );
        $this->fields_typeahead_box();
        $this->box( 'bottom' );
        self::template( 'end' );
        
        self::template( 'begin', 1 );
        $this->show_tabs();
        $this->show_tab_content();

        // $this->save_settings();
        $this->tile_settings_box();
        self::template( 'end' );
    }

    public function show_post_type_pills() {
        $post_types = DT_Posts::get_post_types();
        foreach( $post_types as $post_type ) :
            $post_type_label = DT_Posts::get_label_for_post_type( $post_type ); ?>
            <a href="<?php echo esc_url( admin_url() . "admin.php?page=dt_customizations&post_type=$post_type" ); ?>" class="button <?php echo ( isset( $_GET['post_type'] ) && $_GET['post_type'] === $post_type ) ? 'button-primary' : null; ?>"><?php echo esc_html( $post_type_label ); ?></a>
        <?php endforeach;
    }

    public function show_tabs() {
        $post_type = self::get_parameter( 'post_type' );
        if ( !$post_type ) {
            return;
        }
        
        $tab = self::get_parameter( 'tab' );
        $active_tab = null;
        if ( $tab == $active_tab || !$tab ) {
            $active_tab = 'nav-tab-active';
        }
        ?>
        <h2 class="nav-tab-wrapper" style="padding: 0;">
            <a href="<?php echo esc_url( admin_url() . "admin.php?page=dt_customizations&post_type=$post_type&tab=general" ); ?>" class="nav-tab <?php echo ( !isset( $_GET['tab'] ) || $_GET['tab'] === 'general' ) ? 'nav-tab-active' : null; ?>"><?php echo esc_html( 'General', 'disciple_tools' ); ?></a>
            <a href="<?php echo esc_url( admin_url() . "admin.php?page=dt_customizations&post_type=$post_type&tab=tiles" ); ?>" class="nav-tab <?php echo ( isset( $_GET['tab'] ) && $_GET['tab'] === 'tiles' ) ? 'nav-tab-active' : null; ?>"><?php echo esc_html( 'Tiles', 'disciple_tools' ); ?></a>
            <a href="<?php echo esc_url( admin_url() . "admin.php?page=dt_customizations&post_type=$post_type&tab=fields" ); ?>" class="nav-tab <?php echo ( isset( $_GET['tab'] ) && $_GET['tab'] === 'fields' ) ? 'nav-tab-active' : null; ?>"><?php echo esc_html( 'Fields', 'disciple_tools' ); ?></a>
        </h2>
        <?php
    }

    public function show_tab_content() {
        $post_type = self::get_parameter( 'post_type' );
        $tab = self::get_parameter( 'tab' );
        if ( !$tab ) {
            $tab = 'general';
        }

        if ( $post_type ) {
            if( $tab === 'tiles' ) {
                self::tile_rundown_box();
                return;
            }
            ?>
            <div class="tab-content">
                <b>post_type:</b> <?php echo esc_html( $post_type ); ?><br>
                <b>tab:</b> <?php echo esc_html( $tab ); ?><br>
            </div>
            <?php
        }
    }

    public static function get_parameter( $param ) {
        if ( !isset( $_GET[$param] ) || empty( $_GET[$param] ) ) {
            return null;
        }
        return sanitize_text_field( wp_unslash( $_GET[$param] ) );
    }

    public function fields_typeahead_box() {
        ?>
        <div>
            <form id="form-field_settings_search" name="form-field_settings_search">
                <div class="typeahead__container">
                    <div class="typeahead__field">
                        <div class="typeahead__query">
                            <span class="typeahead__query">
                                <input id="settings[query]" name="settings[query]" class="js-typeahead-settings" autocomplete="off" placeholder="<?php esc_attr_e( 'Search', 'disciple_tools' ); ?>">
                            </span>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <style>
            .tab-content {
                padding: 12px;
            }
            .typeahead__result {
                background-color: #fff;
                border: 1px solid #555;
            }
            .typeahead__item>a{
                color: #000;
                font-size: medium;
                font-weight: normal;
                margin: 12px;
            }
            .typeahead__display {
                line-height: 2.25em;
            }
            .js-typeahead-settings {
                width: 100%;
                height: 3em;
                padding-left: 12px;
            }
            </style>
        <script>
            jQuery(document).ready(function($) {
                var input_text = $('.js-typeahead-settings')[0].value;
                $.typeahead({
                    input: '.js-typeahead-settings',
                    order: "desc",
                    cancelButton: false,
                    dynamic: false,
                    emptyTemplate: '<em style="padding-left:12px;">No results for "{{query}}"</em>',
                    template: '<a href="' + window.location.origin + window.location.pathname + '?page=dt_customizations&post_type={{post_type}}&tab=tiles&post_tile_key={{post_tile}}#{{post_setting}}">{{label}}</a>',
                    correlativeTemplate: true,
                    source: {
                        ajax: {
                            type: "POST",
                            url: window.wpApiSettings.root+ 'dt-public/dt-core/v1/get-post-fields',
                            beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', window.wpApiSettings.nonce);
                            },
                        }
                    },
                    callback: {
                        onResult: function() {
                            $(`.typeahead__result`).show();
                        },
                        onHideLayout: function () {
                            $(`.typeahead__result`).hide();
                        }
                    }
                });
            });
        </script>
        <?php
    }

    public function post_types_box() {
        wp_nonce_field( 'security_headers', 'security_headers_nonce' );
        $post_types = DT_Posts::get_post_types(); ?>
        <?php foreach ( $post_types as $post_type ):
            $post_label = DT_Posts::get_label_for_post_type( $post_type ); ?>
            <a href="admin.php?page=dt_options&tab=new-settings-ui&post_type=<?php echo esc_attr( $post_type ); ?>" class="button <?php self::show_primary_button( $post_type ); ?>"><?php echo esc_html( $post_label ); ?></a>
        <?php endforeach; ?>
        <?php
        $this->box( 'bottom' );
    }

    private function show_primary_button( $post_type ) {
        if ( isset( $_GET['post_type'] ) && $post_type === $_GET['post_type'] ) {
            echo 'button-primary';
        }
    }

    private function tile_rundown_box() {
        $post_type = self::get_parameter( 'post_type' );
        $available_post_types = DT_Posts::get_post_types();
        if ( !in_array( $post_type, $available_post_types ) ) {
            esc_html_e( 'Error: unknown post_type.', 'disciple_tools' );
            return;
        }

        echo '<br>';
        $this->box( 'top', 'Select a Tile' );
        $this->show_post_type_settings( $post_type );
        $this->box( 'bottom' );
    }

    private function tile_settings_box() {
        $post_type = self::get_parameter( 'post_type' );
        $tile_key = self::get_parameter( 'post_tile_key' );

        if ( is_null( $post_type ) || is_null( $tile_key ) ) {
            return;
        }

        $post_settings = DT_Posts::get_post_settings( $post_type );
        $tile_label = '';
        if ( isset( $post_settings['tiles'][$tile_key]['label'] ) ) {
            $tile_label = $post_settings['tiles'][$tile_key]['label'];
        };
        $clean_tile = self::filter_tile_settings();
        ?>
        <table class="widefat">
            <thead>
                <th colspan="2"><?php echo esc_html( $tile_label ); ?> Tile Contains</th>
            </thead>
            <tbody>
                <tr>
                    <td style="border-right: 1px solid #ccc;"><?php $this->show_tile_settings( $clean_tile ); ?></td>
                    <td style="background-color: #f1f1f1;">
                        <div id="new-custom-field-box" class="new-custom-field hidden">
                            <h2><?php esc_html_e( 'Create new field', 'disciple_tools' ); ?></h2>
                            <input type="text" style="width: 100%">
                        </div>
                        <?php $this->tile_preview_box(); ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    public function filter_tile_settings() {
        $post_type = self::get_parameter( 'post_type' );
        $tile_key = self::get_parameter( 'post_tile_key' );

        $tiles = DT_Posts::get_post_settings( $post_type, false );
        $tile_fields = [];
        foreach ( $tiles['fields'] as $key => $values ) {
            if ( isset( $values['tile'] ) && $values['tile'] == $tile_key ) {
                $tile_fields[$key] = $values;
            }
        }
        return $tile_fields;
    }

    private function show_tile_settings( $tile ) {
        foreach ( $tile as $setting_key => $setting_value ) {
            foreach ( $setting_value as $key => $value ) {
                if ( $key === 'default' && !empty( $setting_value['default'] ) ) {
                    ?>
                <b id="<?php echo esc_attr( $setting_key ); ?>"><?php echo esc_html( $setting_value['name'] ); ?></b>
                    <?php
                    foreach ( $value as $v ) {
                        if ( isset( $v['label'] ) ) {
                            $label = $v['label'];
                            if ( is_null( $label ) || empty( $label ) ) {
                                $label = 'NULL';
                            }
                            ?>
                            <div class="field-option-element" style="margin-left: 18px;">
                                └ <?php echo esc_html( $label ); ?>
                                <a href="javascript:void(0);" class="edit-option"><?php esc_html_e( 'edit', 'disciple_tools' ); ?></a>
                            </div>
                            <?php
                        }
                    }
                    ?>
                    
                    <div class="add-new-option">
                    └ <a href="javascript:void(0);"><?php esc_html_e( 'add new', 'disciple_tools' ); ?></a>
                    </div>
                    
                    <?php
                }
            }
        }
    }

    private function tile_preview_box() {
        $tile = self::filter_tile_settings();
        if ( !isset( $_GET['post_type'] ) || !isset( $_GET['post_tile_key'] ) ) {
            esc_html_e( 'Error: missing parameters.', 'disciple_tools' );
            return;
        }
        $post_type = self::get_parameter( 'post_type' );
        $tile_key = self::get_parameter( 'post_tile_key' );
        $post_settings = DT_Posts::get_post_settings( $post_type );
        $tile_label = '';
        if ( isset( $post_settings['tiles'][$tile_key]['label'] ) ) {
            $tile_label = $post_settings['tiles'][$tile_key]['label'];
        }
        ?>
        <script>
            jQuery(document).ready(function($) {
                $('.edit-option').on('click', function() {
                    alert('modal goes here');
                });
                $('.add-new-option').on('click', function() {
                    $('#new-custom-field-box').slideToggle(333, 'swing');
                });
                $('.field-option-element').on('mouseenter', function() {
                    $(this).children('.edit-option').toggle();
                });
                $('.field-option-element').on('mouseleave', function() {
                    $(this).children('.edit-option').toggle();
                });
            });
        </script>
        <style>
            .edit-option {
                margin-left: 18px;
                display: none;
            }
            .field-option-element{
                width: 100%;
                margin-left: 18px;
            }
            .add-new-option {
                margin: 0 0 18px 18px;
            }
            .new-custom-field {
                width: auto;
                height: 250px;
                display: none;
                border: 1px solid #ccc;
                background-color: #fff;
                margin: 3%;
                padding: 1rem;
                overflow: hidden;
                scroll-behavior: smooth;
            }
            .dt-tile-preview {
                width: auto;
                background-color: #fefefe;
                border: 1px solid #e6e6e6;
                border-radius: 10px;
                box-shadow: 0 2px 4px rgb(0 0 0 / 25%);
                padding: 1rem;
                margin: 3%;
            }
            .section-header {
                display: flex;
                color: #3f729b;
                font-size: 1.5rem;
                font-family: Helvetica,Arial,sans-serif;
                font-style: normal;
                font-weight: 300;
                text-rendering: optimizeLegibility;
                line-height: 1.4;
                margin-bottom: 0.5rem;
                margin-top: 0;
            }
            .section-body {
                width: 100%;
            }
            .dt-tile-preview .chevron {
                height:1.3rem;
                width:1.3rem;
                margin-inline-end: 0;
                margin-inline-start: auto;
            }
            .section-subheader {
                font-size: 14px;
                font-weight: 700;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                color: #000;
            }
            .dt-icon {
                height: 15px;
                width: 15px;
            }
            .lightgray {
                filter: invert(18%) sepia(0) saturate(19%) hue-rotate(170deg) brightness(108%) contrast(98%);
            }
            .button-group {
                position: relative;
                -webkit-box-align: stretch;
                -webkit-box-flex: 1;
                align-items: stretch;
                flex-grow: 1;
                flex-wrap: wrap;
                margin-bottom: 1rem;
                font-size: .75rem;
            }
            .button-group>button {
                font-size: .75rem;
                background-color: #eee;
                color: #000;
                margin: 5px;
                border: 1px solid transparent;
                border-radius: 5px;
                padding: 0.85em 1em;
            }
            .typeahead-container {
                margin-bottom: 10px;
                width: 100%;
                min-height: 2.5rem;
                line-height: 1.5rem;
                display: inline-flex;
                position: relative;
            }
            .typeahead-input {
                width: 100%;
                padding: 0.5rem 0.75rem;
                border: 1px solid #ccc;
                border-radius: 2px 0 0 2px;
                appearance: none;
                box-sizing: border-box;
                overflow: visible;
                padding-right: 32px;
            }
            .typeahead-button {
                color: #555;
                border: 1px solid #ccc;
                border-radius: 0 2px 2px 0;
                background-color: #fff;
                margin-left: -2px;
                z-index: 1;
                padding: 0.5rem 0.75rem;
                vertical-align: middle;
            }
            .typeahead-button>img{
                width: 20px;
                height: 20px;
                min-height: 15px;
                min-width: 15px;
            }
            .typeahead-cancel-button {
                height: 100%;
                position: absolute;
                right: 3rem;
                padding: 4px 6px !important;
                color: #555;
            }
            .typeahead-delete-button {
                height: 100%;
                position: absolute;
                right: 0;
                padding: 0.5rem 1.2rem;
                color: #cc4b37;
                background-color: #eee;
                border: 1px solid #ccc;
                border-radius: 0 2px 2px 0;
            }
            .typeahead-delete-button:hover {
                background-color: #cc4b37;
                color: #fff;
            }
            .dt-tile-preview .select-field {
                width: 100%;
            }
            .select-field.color-select {
                background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' version='1.1' width='32' height='24' viewBox='0 0 32 24'><polygon points='0,0 32,0 16,24' style='fill: white'></polygon></svg>");
                background-size: 9px 6px;
                font-weight: 700;
                border: none;
                border-radius: 10px;
                color: #fff;
                background-color: #4caf50;
                text-shadow: rgb(0 0 0 / 45%) 0 0 6px;
            }
            .select-field.color-select:hover{
                color: #fff;
            }
            .select-field:hover{
                text-decoration: none;
                cursor: default;
            }
            select {
                background-repeat: no-repeat;
                margin-bottom: 1rem;
            }
            .dt-tile-preview .text-input{
                border: 1px solid #cacaca;
                border-radius: 0;
                display: block;
                line-height: 1.5;
                margin: 0 0 1rem;
                padding: 0.5rem;
                width:100%;
                box-shadow: inset 0 1px 2px hsl(0deg 0% 4% / 10%);
            }
        </style>
        <div class="dt-tile-preview">
            <div class="section-header">
                <h3 class="section-header"><?php echo esc_html( $tile_label ); ?></h3>
                <img src="<?php echo esc_attr( get_template_directory_uri() ); ?>/dt-assets/images/chevron_up.svg" class="chevron">
            </div>
            <div class="section-body">
            <?php foreach ( $tile as $t ) : ?>
                <div class="section-subheader">
                    <img src="<?php echo esc_attr( $t['icon'] );?>" alt="<?php echo esc_attr( $t['name'] ); ?>" class="dt-icon lightgray">
                    <?php echo esc_html( $t['name'] ); ?>
                </div>
                <?php



                /*** MULTISELECT - START ***/
                if ( $t['type'] === 'multi_select' ) : ?>
                <div class="button-group" style="display: inline-flex;">
                    <?php foreach ( $t['default'] as $key => $value ) : ?>
                    <button>
                        <img src="<?php isset( $value['icon'] ) ? esc_attr( $value['icon'] ) : ''; ?>" class="dt-icon">
                        <?php echo esc_html( $value['label'] ); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php endif;
                /*** MULTISELECT - START ***/



                /*** CONNECTION - START ***/
                if ( $t['type'] === 'connection' ) : ?>
                <div class="typeahead-container">
                    <input class="typeahead-input" placeholder="Search <?php echo esc_attr( $t['name'] ); ?>">
                    <button class="typeahead-button">
                        <img src="<?php echo esc_attr( get_template_directory_uri() ); ?>/dt-assets/images/add-contact.svg">
                    </button>
                </div>
                <?php endif;
                /*** CONNECTION - END ***/


                /*** USER_SELECT - START ***/
                if ( $t['type'] === 'user_select' ) : ?>
                    <div class="typeahead-container">
                        <span class="typeahead-cancel-button">×</span>
                        <input class="typeahead-input" placeholder="<?php esc_attr_e( 'Search Users', 'disciple_tools' ); ?>">
                        <button class="typeahead-button">
                            <img src="<?php echo esc_attr( get_template_directory_uri() ); ?>/dt-assets/images/search.svg">
                        </button>
                    </div>
                    <?php endif;
                /*** USER_SELECT - END ***/



                /*** KEY_SELECT - START ***/
                if ( $t['type'] === 'key_select' ) : ?>
                <select class="select-field <?php isset( $t['custom_display'] ) ? esc_attr_e( 'color-select' ) : ''; ?>" style="max-width: 100%">
                    <?php foreach ( $t['default'] as $key => $value ) : ?>
                        <option><?php echo esc_html( $value['label'] ); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php endif;
                /*** KEY_SELECT - END ***/



                /*** DATE - START ***/
                if ( $t['type'] === 'date' ) : ?>
                    <div class="typeahead-container">
                        <input class="typeahead-input">
                        <button class="typeahead-delete-button">x</button>
                    </div>
                    <?php endif;
                /*** DATE - END ***/



                /*** TEXT - START ***/
                if ( in_array( $t['type'], [ 'text', 'communication_channel', 'location', 'location_meta' ] ) ) : ?>
                    <input type="text" class="text-input">
                    <?php endif;
                /*** TEXT - END ***/



                ?>
            <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    private function show_post_type_settings( $post_type ) {
        $post_tiles = DT_Posts::get_post_tiles( $post_type );
        foreach ( $post_tiles as $key => $value ) : ?>
        <li><a href="admin.php?page=dt_customizations&post_type=<?php echo esc_attr( $post_type ); ?>&tab=tiles&post_tile_key=<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $post_tiles[$key]['label'] ); ?></a></li>
        <?php endforeach;
    }

    public function save_settings(){
        if ( !empty( $_POST ) ){
            if ( isset( $_POST['security_headers_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['security_headers_nonce'] ), 'security_headers' ) ) {
                update_option( "dt_disable_header_xss", isset( $_POST["xss"] ) ? "0" : "1" );
                update_option( "dt_disable_header_referer", isset( $_POST["referer"] ) ? "0" : "1" );
                update_option( "dt_disable_header_content_type", isset( $_POST["content_type"] ) ? "0" : "1" );
                update_option( "dt_disable_header_strict_transport", isset( $_POST["strict_transport"] ) ? "0" : "1" );
            }

            if ( isset( $_POST['usage_data_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['usage_data_nonce'] ), 'usage_data' ) ) {
                update_option( 'dt_disable_usage_data', isset( $_POST["usage"] ) ? "1" : "0" );
            }
        }
    }

}
Disciple_Tools_Customizations_Tab::instance();
