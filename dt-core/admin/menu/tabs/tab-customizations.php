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
        if ( isset( $_GET['page'] ) && $_GET['page'] === 'dt_customizations' ){
            add_action( 'dt_customizations_tab_content', [ $this, 'content' ], 99, 1 );
            add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
        }
        parent::__construct();
    }

    public function add_submenu() {
        $post_types = DT_Posts::get_post_types();
        foreach ( $post_types as $post_type ) {
            $post_type_label = DT_Posts::get_label_for_post_type( $post_type );
            add_submenu_page( 'dt_customizations', esc_html( $post_type_label, 'disciple_tools' ), esc_html( $post_type_label, 'disciple_tools' ), 'manage_dt', "dt_customizations&post_type=$post_type", [ 'Disciple_Tools_Customizations_Menu', 'content' ] );
        }
    }

    public static function get_post_settings_with_customization_status( $post_type ) {
        $post_settings = DT_Posts::get_post_settings( $post_type );
        $base_fields = Disciple_Tools_Post_Type_Template::get_base_post_type_fields();
        $default_fields = apply_filters( 'dt_custom_fields_settings', [], $post_type );
        $all_non_custom_fields = array_merge( $base_fields, $default_fields );

        // Check if field is not a default field and add a note in the array
        foreach ( $post_settings['fields'] as $field_key => $field_settings ) {
            if ( !array_key_exists( $field_key, $all_non_custom_fields ) ) {
                $post_settings['fields'][$field_key]['is_custom'] = true;
                continue;
            }

            if ( isset( $all_non_custom_fields[$field_key]['name'] ) && $post_settings['fields'][$field_key]['name'] != $all_non_custom_fields[$field_key]['name'] ) {
                $post_settings['fields'][$field_key]['default_name'] = $all_non_custom_fields[$field_key]['name'];
            }
        }
        return $post_settings;
    }

    public function admin_enqueue_scripts() {
        wp_register_script( 'jquery-ui-js', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', [ 'jquery' ], '1.12.1', true );
        wp_enqueue_script( 'jquery-ui-js' );
        wp_register_style( 'jquery-ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css' );
        wp_enqueue_style( 'jquery-ui' );

        dt_theme_enqueue_script( 'typeahead-jquery', 'dt-core/dependencies/typeahead/dist/jquery.typeahead.min.js', array( 'jquery' ), true );
        wp_enqueue_script( 'dt_shared_scripts', disciple_tools()->admin_js_url . 'dt-shared.js', [], filemtime( disciple_tools()->admin_js_path . 'dt-shared.js' ), true );
        dt_theme_enqueue_script( 'dt-settings', 'dt-core/admin/js/dt-settings.js', [ 'jquery', 'jquery-ui-js', 'dt_shared_scripts' ], true );

        wp_register_style( 'dt_settings_css', disciple_tools()->admin_css_url . 'dt-settings.css', [], filemtime( disciple_tools()->admin_css_path . 'dt-settings.css' ) );
        wp_enqueue_style( 'dt_settings_css' );


        $post_type = self::get_parameter( 'post_type' );
        if ( !isset( $post_type ) || is_null( $post_type ) ) {
            return;
        }

        $post_settings = self::get_post_settings_with_customization_status( $post_type );

        $translations = [
            'save' => __( 'Save', 'disciple_tools' ),
            'edit' => __( 'Edit', 'disciple_tools' ),
            'delete' => __( 'Delete', 'disciple_tools' ),
            'txt_info' => _x( 'Showing _START_ of _TOTAL_', 'just copy as they are: _START_ and _TOTAL_', 'disciple_tools' ),
            'sorting_by' => __( 'Sorting By', 'disciple_tools' ),
            'creation_date' => __( 'Creation Date', 'disciple_tools' ),
            'date_modified' => __( 'Date Modified', 'disciple_tools' ),
            'empty_custom_filters' => __( 'No filters, create one below', 'disciple_tools' ),
            'empty_list' => __( 'No records found matching your filter.', 'disciple_tools' ),
            'filter_all' => sprintf( _x( 'All %s', 'All records', 'disciple_tools' ), $post_settings['label_plural'] ),
            'range_start' => __( 'start', 'disciple_tools' ),
            'range_end' => __( 'end', 'disciple_tools' ),
            'all' => __( 'All', 'disciple_tools' ),
            'without' => __( 'Without', 'disciple_tools' ),
            'make_selections_below' => __( 'Make Selections Below', 'disciple_tools' ),
            'sent' => _x( 'sent', 'Number of emails sent. i.e. 20 sent!', 'disciple_tools' ),
            'not_sent' => _x( 'not sent (likely missing valid email)', 'Preceded with number of emails not sent. i.e. 20 not sent!', 'disciple_tools' ),
            'exclude_item' => __( 'Exclude Item', 'disciple_tools' )
        ];

        wp_localize_script(
            'dt-settings', 'field_settings', array(
                'all_post_types' => self::get_all_post_types(),
                'post_type' => $post_type,
                'post_type_label' => DT_Posts::get_label_for_post_type( $post_type ),
                'post_type_settings' => $post_settings,
                'post_type_tiles' => DT_Posts::get_post_tiles( $post_type ),
                'custom_tiles' => self::get_custom_tiles( $post_type ),
                'fields_to_show_in_table' => DT_Posts::get_default_list_column_order( $post_type ),
                'translations' => apply_filters( 'dt_list_js_translations', $translations ),
                'filters' => Disciple_Tools_Users::get_user_filters( $post_type ),
                'languages' => dt_get_available_languages( true ),
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'site_url' => get_site_url(),
                'template_dir' => get_template_directory_uri(),
            )
        );

        wp_enqueue_script( 'jquery' );
    }

    public function content() {
        $this->load_overlay_modal();
        self::template( 'begin', 1 );
            $this->space_between_div_open();
            $this->show_post_type_pills();
            $this->fields_typeahead_box();
            $this->space_between_div_close();
        self::template( 'end' );

        self::template( 'begin', 1 );
            $this->show_tabs();
            $this->show_tab_content();
            // $this->save_settings();
        self::template( 'end' );
    }

    public static function get_all_post_types() {
        $all_post_types = [];
        $post_type_keys = DT_Posts::get_post_types();
        foreach ( $post_type_keys as $key ) {
            $all_post_types[$key] = DT_Posts::get_label_for_post_type( $key );
        }
        return $all_post_types;
    }

    public static function get_custom_tiles( $post_type ) {
        $all_custom_tiles = dt_get_option( 'dt_custom_tiles' );
        $custom_tiles = [];
        if ( isset( $all_custom_tiles[$post_type] ) ) {
            foreach ( $all_custom_tiles[$post_type] as $tile_key => $tile_value ) {
                $custom_tiles[] = $tile_key;
            }
        }
        return $custom_tiles;
    }

    public static function load_overlay_modal() {
        $post_type = self::get_parameter( 'post_type' );
        if ( !isset( $post_type ) || is_null( $post_type ) ) {
            return;
        }
        ?>
        <div class="dt-admin-modal-overlay hidden">
            <div class="dt-admin-modal-box hidden">
                <div class="dt-admin-modal-box-inner">
                    <div class="modal-front">
                        <div class="dt-admin-modal-box-close-button">×</div>
                        <div class="dt-admin-modal-box-content">
                                <form id="modal-overlay-form">
                                <table class="modal-overlay-content-table" id="modal-overlay-content-table">
                                    <!-- DYNAMIC CONTENT: START -->
                                    <!-- DYNAMIC CONTENT: END -->
                                </table>
                            </form>
                        </div>
                    </div>
                    <div class="modal-back">
                        <div class="dt-admin-modal-translations-box-close-button">×</div>
                        <div class="dt-admin-modal-translations-box-content">
                            <form id="modal-translations-overlay-form">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    private function space_between_div_open() {
        ?>
        <p style="margin-bottom: 50px">
            This feature is in BETA. Please download a copy of your settings  <a target='_blank' href="<?php echo esc_url( admin_url( 'admin.php?page=dt_utilities&tab=exports' ) ); ?>">here</a>. before making any changes.
        </p>
        <div class="top-nav-row">
        <?php
    }

    private function space_between_div_close() {
        ?>
        </div>
        <?php
    }

    public function show_post_type_pills() {
        ?>
        <div id="post-type-buttons">
            <div style="padding-bottom: 8px;"><b><?php esc_html_e( 'Select a record type:', 'disciple_tools' ); ?></b></div>
        <?php
        $post_types = DT_Posts::get_post_types();
        foreach ( $post_types as $post_type ) :
            $post_type_label = DT_Posts::get_label_for_post_type( $post_type );
            $pill_link = "admin.php?page=dt_customizations&post_type=$post_type&tab=tiles";
            if ( self::get_parameter( 'tab' ) ) {
                $pill_link .= '&tab=' . self::get_parameter( 'tab' );
            }
            ?>
            <a href="<?php echo esc_url( admin_url() . $pill_link ); ?>" class="button <?php echo ( isset( $_GET['post_type'] ) && $_GET['post_type'] === $post_type ) ? 'button-primary' : null; ?>"><?php echo esc_html( $post_type_label ); ?></a>
        <?php endforeach; ?>
        </div>
        <?php
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
            <a href="<?php echo esc_url( admin_url() . "admin.php?page=dt_customizations&post_type=$post_type&tab=tiles" ); ?>" class="nav-tab <?php echo ( isset( $_GET['tab'] ) && $_GET['tab'] === 'tiles' ) ? 'nav-tab-active' : null; ?>"><?php echo esc_html( 'Tiles', 'disciple_tools' ); ?></a>
        </h2>
        <?php
    }

    public function show_tab_content() {
        $post_type = self::get_parameter( 'post_type' );
        $tab = self::get_parameter( 'tab' );
        if ( !$tab ) {
            $tab = 'tiles';
        }

        switch ( $tab ) {
            case 'tiles':
                self::tile_settings_box();
                break;
            default:
                self::tile_settings_box();
                break;
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
        <div class="typeahead-div">
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

    private function post_type_exists( $post_type ) {
        $available_post_types = DT_Posts::get_post_types();
        if ( !in_array( $post_type, $available_post_types ) ) {
            esc_html_e( 'Error: unknown post_type.', 'disciple_tools' );
            return false;
        }
        return true;
    }

    private function get_post_fields( $post_type ){
        return DT_Posts::get_post_field_settings( $post_type, false, true );
    }

    private function tile_settings_box() {
        $post_type = self::get_parameter( 'post_type' );
        $tile_key = self::get_parameter( 'post_tile_key' );

        if ( is_null( $post_type ) ) {
            return;
        }

        $post_settings = DT_Posts::get_post_settings( $post_type );
        $tile_label = '';
        if ( isset( $post_settings['tiles'][$tile_key]['label'] ) ) {
            $tile_label = $post_settings['tiles'][$tile_key]['label'];
        };
        ?>
        <table class="widefat" style="margin-top: 12px;">
            <thead>
                <th colspan="2"><?php echo esc_html( 'Tile Rundown', 'disciple_tools' ); ?></th>
            </thead>
            <tbody>
                <tr>
                    <td class="fields-table-left"><?php $this->display_field_rundown(); ?></td>
                    <td class="fields-table-right"></td>
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

    private function display_field_rundown() {
        $post_type = self::get_parameter( 'post_type' );
        $post_tiles = DT_Posts::get_post_settings( $post_type, false );
        ?>
        <!-- START TABLE -->
        <div class="field-settings-table">
            <?php foreach ( $post_tiles['tiles'] as $tile_key => $tile_value ) : ?>
                <?php if ( $tile_key !== 'no_tile' ) : ?>
                <!-- START TILE -->
                <div class="<?php echo esc_attr( self::get_sortable_class( $tile_key ) ); ?>" id="<?php echo esc_attr( $tile_key ); ?>">
                    <div class="field-settings-table-tile-name expandable" data-modal="edit-tile" data-key="<?php echo esc_attr( $tile_key ); ?>">
                       <span class="sortable ui-icon ui-icon-arrow-4"></span>
                        <span class="expand-icon">+</span>
                        <span id="tile-key-<?php echo esc_attr( $tile_key ); ?>" style="vertical-align: sub;">
                            <?php echo esc_html( isset( $tile_value['label'] ) ? $tile_value['label'] : $tile_key ); ?>
                        </span>
                        <span class="edit-icon"></span>
                    </div>
                    <div class="tile-rundown-elements" data-parent-tile-key="<?php echo esc_attr( $tile_key ); ?>" style="display: none;">
                        <!-- START TOGGLED FIELD ITEMS -->
                        <?php foreach ( $post_tiles['fields'] as $field_key => $field_settings ) : ?>
                            <?php if ( self::field_option_in_tile( $field_key, $tile_key ) ) : ?>
                                <div class="sortable-field" id="<?php echo esc_attr( $field_key ); ?>">
                                <?php if ( $field_settings['type'] !== 'key_select' && $field_settings['type'] !== 'multi_select' ): ?>
                                    <div class="field-settings-table-field-name" id="<?php echo esc_attr( $field_key ); ?>" data-modal="edit-field" data-key="<?php echo esc_attr( $field_key ); ?>" data-parent-tile-key="<?php echo esc_attr( $tile_key ); ?>">
                                       <span class="sortable ui-icon ui-icon-arrow-4"></span>
                                        <span class="field-name-content" data-parent-tile-key="<?php echo esc_attr( $tile_key ); ?>" data-key="<?php echo esc_attr( $field_key ); ?>">
                                            <?php echo esc_html( $field_settings['name'] ); ?>
                                        </span>
                                        <span class="edit-icon"></span>
                                    </div>
                                <?php else : ?>
                                    <div class="field-settings-table-field-name expandable" id="<?php echo esc_attr( $field_key ); ?>" data-modal="edit-field" data-key="<?php echo esc_attr( $field_key ); ?>" data-parent-tile-key="<?php echo esc_attr( $tile_key ); ?>">
                                       <span class="sortable ui-icon ui-icon-arrow-4"></span>
                                        <span class="expand-icon">+</span>
                                        <span class="field-name-content" style="vertical-align: sub;" data-parent-tile-key="<?php echo esc_attr( $tile_key ); ?>" data-key="<?php echo esc_attr( $field_key ); ?>">
                                            <?php echo esc_html( $field_settings['name'] ); ?>
                                        </span>
                                        <span class="edit-icon"></span>
                                    </div>

                                    <!-- START TOGGLED ITEMS -->
                                    <div class="field-settings-table-child-toggle">
                                        <?php foreach ( $field_settings as $key => $value ) : ?>
                                            <?php if ( $key === 'default' && !empty( $field_settings['default'] ) && is_array( $field_settings['default'] ) ) : ?>
                                                <?php foreach ( $value as $k => $v ) {
                                                    $label = 'default blank';
                                                    if ( isset( $v['label'] ) || !empty( $v['label'] ) ) {
                                                        // $option_key = $value;
                                                        $label = $v['label'];
                                                    }

                                                    if ( isset( $v['default'] ) || !empty( $v['default'] ) ) {
                                                        $option_key = $v['default'];
                                                    }
                                                    ?>
                                                    <div class="field-settings-table-field-option" id="<?php echo esc_attr( $k ); ?>">
                                                        <span class="sortable ui-icon ui-icon-arrow-4"></span>
                                                        <span class="field-name-content" data-parent-tile-key="<?php echo esc_attr( $tile_key ); ?>" data-field-key="<?php echo esc_attr( $field_key ); ?>" data-field-option-key="<?php echo esc_attr( $k ); ?>" ><?php echo esc_html( $label ); ?></span>
                                                        <span class="edit-icon" data-modal="edit-field-option" data-parent-tile-key="<?php echo esc_attr( $tile_key ); ?>" data-field-key="<?php echo esc_attr( $field_key ); ?>" data-field-option-key="<?php echo esc_attr( $k ); ?>"></span>
                                                    </div>
                                                    <?php
                                                }
                                            endif; ?>
                                        <?php endforeach; ?>
                                        <div class="field-settings-table-field-option new-field-option add-new-item" data-parent-tile-key="<?php echo esc_attr( $tile_key ); ?>" data-field-key="<?php echo esc_attr( $field_key ); ?>">
                                           <span><?php echo esc_html( 'new field option', 'disciple_tools' ); ?></span>
                                        </div>
                                    </div>
                                    <!-- END TOGGLED ITEMS -->
                                <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <!-- END TOGGLED FIELD ITEMS -->
                        <div class="field-settings-table-field-name expandable add-new-item">
                            <span class="add-new-field" data-parent-tile-key="<?php echo esc_attr( $tile_key ); ?>">
                                <a><?php echo esc_html( 'add new field', 'disciple_tools' ); ?></a>
                            </span>
                        </div>
                    </div>
                </div>
                <!-- END TILE -->
                <?php endif; ?>
            <?php endforeach; ?>
            <!-- START UNTILED FIELDS -->
            <div class="sortable-tile">
                <div class="field-settings-table-tile-name expandable" data-modal="edit-tile" data-key="no-tile-hidden">
                   <span class="sortable ui-icon ui-icon-arrow-4"></span>
                    <span class="expand-icon">+</span>
                    <span id="tile-key-untiled" style="vertical-align: sub;">
                        <?php echo esc_html_e( 'No Tile / Hidden', 'disciple-tools' ); ?>
                    </span>
                </div>
                <div class="tile-rundown-elements" data-parent-tile-key="no-tile-hidden" style="display: none;">
                    <?php foreach ( $post_tiles['fields'] as $field_key => $field_settings ) : ?>
                        <?php if ( ( !array_key_exists( 'tile', $field_settings ) || $field_settings['tile'] === 'no_tile' ) && ( !isset( $field_settings['customizable'] ) || $field_settings['customizable'] !== false ) && empty( $field_settings['hidden'] ) ) : ?>
                        <div class="field-settings-table-field-name" data-modal="edit-field" data-key="<?php echo esc_attr( $field_key ); ?>" data-parent-tile-key="no-tile-hidden">
                           <span class="sortable ui-icon ui-icon-arrow-4"></span>
                            <span class="field-name-content" data-parent-tile-key="no-tile-hidden" data-key="<?php echo esc_attr( $field_key ); ?>">
                                <?php echo esc_html( $field_settings['name'] ); ?>
                            </span>
                            <span class="edit-icon"></span>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
                <!-- END UNTILED FIELDS -->
            <div class="add-new-link">
                <a href="#" id="add-new-tile-link"><?php echo esc_html( 'add new tile', 'disciple_tools' ); ?></a>
            </div>
        </div>
        <!-- END TABLE -->
        <?php
    }

    private function get_sortable_class( $tile_key ) {
        $sortable_class = 'sortable-tile';
        if ( in_array( $tile_key, [ 'status', 'details', 'no-tile-hidden' ] ) ) {
            $sortable_class = 'unsortable-tile';
        }
        return $sortable_class;
    }

    public static function field_option_in_tile( $field_option_name, $tile_name ) {
        $post_type = self::get_parameter( 'post_type' );
        $post_tiles = DT_Posts::get_post_settings( $post_type, true );
        if ( isset( $post_tiles['fields'][$field_option_name]['tile'] ) ) {
            if ( $post_tiles['fields'][$field_option_name]['tile'] === $tile_name ) {
                return true;
            }
        }
        return false;
    }
    public function save_settings(){
        if ( !empty( $_POST ) ){
            if ( isset( $_POST['security_headers_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['security_headers_nonce'] ), 'security_headers' ) ) {
                update_option( 'dt_disable_header_xss', isset( $_POST['xss'] ) ? '0' : '1' );
                update_option( 'dt_disable_header_referer', isset( $_POST['referer'] ) ? '0' : '1' );
                update_option( 'dt_disable_header_content_type', isset( $_POST['content_type'] ) ? '0' : '1' );
                update_option( 'dt_disable_header_strict_transport', isset( $_POST['strict_transport'] ) ? '0' : '1' );
            }

            if ( isset( $_POST['usage_data_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['usage_data_nonce'] ), 'usage_data' ) ) {
                update_option( 'dt_disable_usage_data', isset( $_POST['usage'] ) ? '1' : '0' );
            }
        }
    }
}
Disciple_Tools_Customizations_Tab::instance();
