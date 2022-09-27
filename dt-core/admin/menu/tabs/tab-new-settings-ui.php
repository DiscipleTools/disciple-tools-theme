<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Disciple_Tools_New_Settings_Ui_Tab extends Disciple_Tools_Abstract_Menu_Base
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
        add_action( 'dt_settings_tab_menu', [ $this, 'add_tab' ], 50, 1 ); // use the priority setting to control load order
        add_action( 'dt_settings_tab_content', [ $this, 'content' ], 99, 1 );


        parent::__construct();
    } // End __construct()


    public function add_submenu() {
        add_submenu_page( 'dt_options', __( 'New Settings UI', 'disciple_tools' ), __( 'New Settings UI', 'disciple_tools' ), 'manage_dt', 'dt_options&tab=new-settings-ui', [ 'Disciple_Tools_Settings_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        echo '<a href="'. esc_url( admin_url() ).'admin.php?page=dt_options&tab=new-settings-ui" class="nav-tab ';
        if ( $tab == 'new-settings-ui' ) {
            echo 'nav-tab-active';
        }
        echo '">'. esc_attr__( 'New Settings UI', 'disciple_tools' ) .'</a>';
    }

    public function content( $tab ) {
        if ( 'new-settings-ui' == $tab ) {
            self::template( 'begin', 1 );
            $this->save_settings();
            $this->post_types_box();

            if ( isset( $_GET['post_type'] ) ) {
                $this->tile_rundown_box();
            }
            if ( isset( $_GET['tile'] ) ) {
                $this->tile_settings_box();
            }
            self::template( 'end' );
        }
    }

    public function post_types_box() {
        $this->box( 'top', 'Post Types' );
        wp_nonce_field( 'security_headers', 'security_headers_nonce' );
        $post_types = DT_Posts::get_post_types(); ?>
        <?php foreach( $post_types as $post_type ):
            $post_label = DT_Posts::get_label_for_post_type( $post_type ); ?>
            <li><a href="admin.php?page=dt_options&tab=new-settings-ui&post_type=<?php echo esc_attr( $post_type ); ?>" class="dt-post-type"><?php echo esc_html( $post_label ); ?></a></li>
        <?php endforeach; ?>
        <?php
        $this->box( 'bottom' );
    }

    private function tile_rundown_box() {
        $post_type = sanitize_text_field( wp_unslash( $_GET['post_type'] ) );
        $available_post_types = DT_Posts::get_post_types();
        if ( !in_array( $post_type, $available_post_types ) ) {
            return;
        }

        $this->box( 'top', 'Select a Tile' );
        $this->show_post_type_settings( $post_type );
        $this->box( 'bottom' );
    }

    private function tile_settings_box() {
        $post_type = self::get_post_type();
        $tile_key = self::get_tile_key();

        if ( is_null( $post_type ) || is_null( $tile_key ) ) {
            esc_html_e( 'Error: missing parameters.', 'disciple_tools' );
            return;
        }

        $post_settings = DT_Posts::get_post_settings( $post_type );
        $tile_label = $post_settings['tiles'][$tile_key]['label'];
        $clean_tile = self::filter_tile_settings();
        
        $this->box( 'top', "$tile_label Tile Contains" );
        $this->show_tile_settings( $clean_tile );
        $this->box( 'bottom' );
        $this->tile_preview_box( $clean_tile ); //foobar
    }

    private function get_post_type() {
        if( !isset( $_GET['post_type'] ) || empty( $_GET['post_type'] ) ) {
            return;
        }
        return sanitize_text_field( wp_unslash( $_GET['post_type'] ) );
    }
    private function get_tile_key() {
        if( !isset( $_GET['tile'] ) || empty( $_GET['tile'] ) ) {
            return;
        }
        return sanitize_text_field( wp_unslash( $_GET['tile'] ) );
    }

    public function filter_tile_settings() {
        $post_type = self::get_post_type();
        $tile_key = self::get_tile_key();

        $tiles = DT_Posts::get_post_settings( $post_type, false );
        $tile_fields = [];
        foreach( $tiles['fields'] as $fields ) {
            if ( isset( $fields['tile'] ) && $fields['tile'] == $tile_key ) {
                $tile_fields[] = $fields;
            }
        }
        return $tile_fields;
    }

    private function show_tile_settings( $tile ) {
        foreach ( $tile as $setting ) : ?>
            <li><?php echo esc_html( $setting['name'] ); ?></li>
        <?php endforeach;
    }

    private function tile_preview_box( $tile ) {
        if ( !isset( $_GET['post_type'] ) || !isset( $_GET['tile'] ) ) {
            esc_html_e( 'Error: missing parameters.', 'disciple_tools' );
            return;
        }
        $post_type = self::get_post_type();
        $tile_key = self::get_tile_key();
        $post_settings = DT_Posts::get_post_settings( $post_type );
        $tile_label = $post_settings['tiles'][$tile_key]['label'];

        // var_dump( $tile );die();
        ?>
        <style>
            .dt-tile-preview {
                width: auto%;
                background-color: #fefefe;
                border: 1px solid #e6e6e6;
                border-radius: 10px;
                box-shadow: 0 2px 4px rgb(0 0 0 / 25%);
                padding: 1rem;
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
        </style>
        <div class="dt-tile-preview">
            <div class="section-header">
                <h3 class="section-header"><?php echo esc_html( $tile_label ); ?></h3>
                <img src="<?php echo esc_attr( get_template_directory_uri() ); ?>/dt-assets/images/chevron_up.svg" class="chevron">
            </div>
            <?php foreach( $tile as $t ) : ?>
            <div class="section-body">
                <div class="section-subheader">
                    <img src="<?php echo esc_attr( $t['icon'] );?>" alt="Faith Milestones" class="dt-icon lightgray">
                    <?php echo esc_html( $t['name'] ); ?>
                </div>
                <?php
                /*** MULTISELECT - START ***/
                if ( $t['type'] === 'multi_select' ) : ?>
                <div class="button-group" style="display: inline-flex;">
                    <?php foreach ( $t['default'] as $key => $value ) : ?>
                    <button>
                        <img src="<?php echo esc_attr( $value['icon'] );?>" class="dt-icon">
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
                ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    private function show_post_type_settings( $post_type ) {
        $post_settings_fields = DT_Posts::get_post_field_settings( $post_type, false );
        $post_tiles = DT_Posts::get_post_tiles( $post_type );
        foreach ( $post_tiles as $key => $value ) : ?>
        <li><a href="admin.php?page=dt_options&tab=new-settings-ui&post_type=<?php echo esc_attr( $post_type ); ?>&tile=<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $post_tiles[$key]['label'] ); ?></a></li>
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
Disciple_Tools_New_Settings_Ui_Tab::instance();
