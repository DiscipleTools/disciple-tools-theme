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
        foreach ( $post_types as $post_type ) {
            $post_type_label = DT_Posts::get_label_for_post_type( $post_type );
            add_submenu_page( 'dt_customizations', esc_html( $post_type_label, 'disciple_tools' ), esc_html( $post_type_label, 'disciple_tools' ), 'manage_dt', "dt_customizations&post_type=$post_type", [ 'Disciple_Tools_Customizations_Menu', 'content' ] );
        }
    }

    public function admin_enqueue_scripts() {
        dt_theme_enqueue_script( 'typeahead-jquery', 'dt-core/dependencies/typeahead/dist/jquery.typeahead.min.js', array( 'jquery' ), true );
        dt_theme_enqueue_script( 'dt-settings', 'dt-core/admin/js/dt-settings.js', [], true );
        dt_theme_enqueue_script( 'shared-functions', 'dt-assets/js/shared-functions.js', [ 'lodash', 'moment' ] );

        $post_type = self::get_parameter( 'post_type' );
        if ( !isset( $post_type ) || is_null( $post_type ) ) {
            return;
        }
        $post_settings = DT_Posts::get_post_settings( $post_type );

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
            'fields_to_show_in_table' => DT_Posts::get_default_list_column_order( $post_type ),
            'translations' => apply_filters( 'dt_list_js_translations', $translations ),
            'filters' => Disciple_Tools_Users::get_user_filters( $post_type ),
            'languages' => dt_get_available_languages( true ),
            )
        );

        wp_localize_script(
            'shared-functions', 'wpApiShare', array(
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'site_url' => get_site_url(),
                'template_dir' => get_template_directory_uri(),
                'translations' => [
                    'regions_of_focus' => __( 'Regions of Focus', 'disciple_tools' ),
                    'all_locations' => __( 'All Locations', 'disciple_tools' ),
                    'used_locations' => __( 'Used Locations', 'disciple_tools' ),
                    'no_records_found' => _x( 'No results found matching "{{query}}"', 'Empty list results. Keep {{query}} as is in english', 'disciple_tools' ),
                    'showing_x_items' => _x( 'Showing %s items. Type to find more.', 'Showing 30 items', 'disciple_tools' ),
                    'showing_x_items_matching' => _x( 'Showing %1$s items matching %2$s', 'Showing 30 items matching bob', 'disciple_tools' ),
                    'edit' => __( 'Edit', 'disciple_tools' ),
                ],
            ),
        );

        wp_enqueue_script( 'jquery' );
    }

    public function content() {
        $this->load_styles();
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
            <div style="padding-bottom: 8px;"><b><?php esc_html_e( 'Select a post type:', 'disciple_tools' ); ?></b></div>
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
                    <td class="fields-table-left"><?php $this->show_tile_settings(); ?></td>
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

    private function show_tile_settings() {
        $post_type = self::get_parameter( 'post_type' );
        $post_tiles = DT_Posts::get_post_settings( $post_type, false );
        self::display_field_rundown();
    }

    private function display_field_rundown() {
        $post_type = self::get_parameter( 'post_type' );
        $post_tiles = DT_Posts::get_post_settings( $post_type, false );
        ?>
        <!-- START TABLE -->
        <div class="field-settings-table">
            <?php foreach ( $post_tiles['tiles'] as $tile_key => $tile_value ) : ?>
                <!-- START TILE -->
                <div class="sortable-tile" id="<?php echo esc_attr($tile_key); ?>">
                    <div class="field-settings-table-tile-name expandable"data-modal="edit-tile" data-key="<?php echo esc_attr( $tile_key ); ?>">
                        <span class="sortable">⋮⋮</span>
                        <span class="expand-icon">+</span>
                        <span id="tile-key-<?php echo esc_attr( $tile_key ); ?>" style="vertical-align: sub;">
                            <?php echo esc_html( $tile_value['label'] ); ?>
                        </span>
                        <span class="edit-icon"></span>
                    </div>
                    <!-- END TILE -->
                    <div class="tile-rundown-elements" data-parent-tile-key="<?php echo esc_attr( $tile_key ); ?>" style="display: none;">
                        <!-- START TOGGLED FIELD ITEMS -->
                        <?php foreach ( $post_tiles['fields'] as $field_key => $field_settings ) : ?>
                            <?php if ( self::field_option_in_tile( $field_key, $tile_key ) ) : ?>
                                <div class="sortable-fields" id="<?php echo esc_attr( $field_key ); ?>">
                                <?php if ( !isset( $field_settings['default'] ) || $field_settings['default'] === '' || $field_settings['type'] === 'tags' ): ?>
                                    <div class="field-settings-table-field-name" id="<?php echo esc_attr( $field_key ); ?>" data-modal="edit-field" data-key="<?php echo esc_attr( $field_key ); ?>" data-parent-tile-key="<?php echo esc_attr( $tile_key ); ?>">
                                        <span class="sortable">⋮⋮</span>
                                        <span class="field-name-content" style="margin-left: 16px;" data-parent-tile-key="<?php echo esc_attr( $tile_key ); ?>" data-key="<?php echo esc_attr( $field_key ); ?>">
                                            <?php echo esc_html( $field_settings['name'] ); ?>
                                        </span>
                                        <span class="edit-icon"></span>
                                    </div>
                                <?php else : ?>
                                    <div class="field-settings-table-field-name expandable" id="<?php echo esc_attr( $field_key ); ?>" data-modal="edit-field" data-key="<?php echo esc_attr( $field_key ); ?>" data-parent-tile-key="<?php echo esc_attr( $tile_key ); ?>">
                                        <span class="sortable">⋮⋮</span>
                                        <span class="expand-icon" style="padding-left: 16px;">+</span>
                                        <span class="field-name-content" style="vertical-align: sub;" data-parent-tile-key="<?php echo esc_attr( $tile_key ); ?>" data-key="<?php echo esc_attr( $field_key ); ?>">
                                            <?php echo esc_html( $field_settings['name'] ); ?>
                                        </span>
                                        <span class="edit-icon"></span>
                                    </div>

                                    <!-- START TOGGLED ITEMS -->
                                    <div class="field-settings-table-child-toggle">
                                        <?php foreach ( $field_settings as $key => $value ) : ?>
                                            <?php if ( $key === 'default' && !empty( $field_settings['default'] ) ) : ?>
                                                <?php foreach ( $value as $k => $v ) {
                                                    $label = 'NULL';
                                                    if ( isset( $v['label'] ) || !empty( $v['label'] ) ) {
                                                        // $option_key = $value;
                                                        $label = $v['label'];
                                                    }

                                                    if ( isset( $v['default'] ) || !empty( $v['default'] ) ) {
                                                        $option_key = $v['default'];
                                                    }
                                                    ?>
                                                <div class="field-settings-table-field-option" id="<?php echo esc_attr( $k ); ?>">
                                                    <span class="sortable">⋮⋮</span>
                                                    <span class="field-name-content" data-parent-tile-key="<?php echo esc_attr( $tile_key ); ?>" data-field-key="<?php echo esc_attr( $field_key ); ?>" data-field-option-key="<?php echo esc_attr( $k ); ?>" style="padding-left: 16px;"><?php echo esc_html( $label ); ?></span>
                                                    <span class="edit-icon" data-modal="edit-field-option" data-parent-tile-key="<?php echo esc_attr( $tile_key ); ?>" data-field-key="<?php echo esc_attr( $field_key ); ?>" data-field-option-key="<?php echo esc_attr( $k ); ?>"></span>
                                                </div>
                                                    <?php
                                                }
                                            endif; ?>
                                        <?php endforeach; ?>
                                        <div class="field-settings-table-field-option new-field-option" data-parent-tile-key="<?php echo esc_attr( $tile_key ); ?>" data-field-key="<?php echo esc_attr( $field_key ); ?>">
                                            <span class="sortable">⋮⋮</span>
                                            <span style="margin-left: 16px;vertical-align: sub;"><?php echo esc_html( 'new field option', 'disciple_tools' ); ?></span>
                                        </div>
                                    </div>
                                    <!-- END TOGGLED ITEMS -->
                                <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <!-- END TOGGLED FIELD ITEMS -->
                        <div class="field-settings-table-field-name expandable">
                            <span class="sortable">⋮⋮</span>
                            <span class="field-name-content add-new-field" data-parent-tile-key="<?php echo esc_attr( $tile_key ); ?>">
                                <a><?php echo esc_html( 'add new field', 'disciple_tools' ); ?></a>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <!-- START UNTILED FIELDS -->
            <div class="sortable-tile">
                <div class="field-settings-table-tile-name expandable" data-modal="edit-tile" data-key="no-tile-hidden">
                    <span class="sortable">⋮⋮</span>
                    <span class="expand-icon">+</span>
                    <span id="tile-key-untiled" style="vertical-align: sub;">
                        <?php echo esc_html_e( 'No Tile / Hidden', 'disciple-tools' ); ?>
                    </span>
                    <span class="edit-icon"></span>
                </div>
                <div class="tile-rundown-elements" data-parent-tile-key="no-tile-hidden" style="display: none;">
                    <?php foreach( $post_tiles['fields'] as $field_key => $field_settings ) : ?>
                        <?php if ( ( !array_key_exists( 'tile', $field_settings ) || $field_settings['tile'] === 'no_tile' ) && ( !isset( $field_settings['customizable'] ) || $field_settings['customizable'] !== false ) && empty( $field_settings['hidden'] ) ) : ?>
                        <div class="field-settings-table-field-name" data-modal="edit-field" data-key="<?php echo esc_attr( $field_key ); ?>" data-parent-tile-key="no-tile-hidden">
                            <span class="sortable">⋮⋮</span>
                            <span class="field-name-content" style="margin-left: 16px;" data-parent-tile-key="no-tile-hidden" data-key="<?php echo esc_attr( $field_key ); ?>">
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
    public static function field_option_in_tile( $field_option_name, $tile_name ) {
        $post_type = self::get_parameter( 'post_type' );
        $post_tiles = DT_Posts::get_post_settings( $post_type, false );
        if ( isset( $post_tiles['fields'][$field_option_name]['tile'] ) ) {
            if ( $post_tiles['fields'][$field_option_name]['tile'] === $tile_name ) {
                return true;
            }
        }
        return false;
    }
    private function load_styles() {
        ?>
        <style>
            .menu-highlight {
                -webkit-animation: menu-highlight-fadeout 1s ease-in alternate;
                -moz-animation: menu-highlight-fadeout 1s ease-in alternate;
                animation: menu-highlight-fadeout 1s ease-in alternate;
            }
            .submenu-highlight {
                -webkit-animation: submenu-highlight-fadeout 1s ease-in alternate;
                -moz-animation: submenu-highlight-fadeout 1s ease-in alternate;
                animation: submenu-highlight-fadeout 1s ease-in alternate;
            }
            @keyframes menu-highlight-fadeout {
                from { background: #3f729b; }
                to { background: #c2e0ff; }
            }
            @keyframes submenu-highlight-fadeout {
                from { background: #3f729b; }
                to { background: #d3d3d3; }
            }
            .modal-box-title {
                text-align: left;
                margin-bottom: 28px;
            }
            .modal-translations-box-title{
                text-align: left;
                background: #e9e9e9;
            }
            .add-new-field a {
                margin-left: 16px;
            }
            .add-new-field-table {
                padding: 18px;
            }
            .add-new-field-table input:not([type='checkbox']) {
                width: 100%;
                height: 30px;
            }
            .add-new-field-table select {
                width: 100%;
                height: 18px;
            }
            .dt-admin-modal-overlay {
                width: 100%;
                height: 100%;
                background: #0000008C;
                position: fixed;
                top: 0;
                left: 0;
                z-index: 100000;
            }
            .dt-admin-modal-box {
                z-index: 100001;
                perspective: 1000px;
            }
            .dt-admin-modal-box-inner {
                margin-top: 15%;
                display: flex;
                justify-content: center;
                align-items: flex-start;
                transition: transform 0.5s;
                transform-style: preserve-3d;
            }
            .flip-card {
                transform: rotateY(180deg);
            }
            .modal-front, .modal-back {
                position: absolute;
                width: auto;
                height: auto;
                min-width: 33%;
                background: #fefefe;
                border: 1px solid #cacaca;
                -webkit-backface-visibility: hidden;
                backface-visibility: hidden;
            }
            .modal-back {
                transform: rotateY(180deg);
            }
            .dt-admin-modal-box-content {
                padding: 8px;
            }
            .tile-rundown-elements {
                margin-top: -1px;
            }
            .field-settings-table {
                display: table;
                min-width: 50%;
                margin: auto;
            }
            .field-settings-table-tile-name {
                display: flex;
                justify-content: flex-start;
                align-items: center;
                font-weight: bold;
                border: 1px solid lightgray;
                background: #eaeaea;
                cursor: pointer;
                height: 32px;
                margin-top: -1px;
            }
            .field-settings-table-field-name {
                height: 32px;
                display: flex;
                align-items: center;
                border: 1px solid lightgray;
                background: #f6f6f6;
                cursor: pointer;
                box-shadow: inset -5px 0px 5px -2px #0000001a;
                margin-top: -1px;
            }
            .field-settings-table-field-option {
                height: 26px;
                display: flex;
                align-items: center;
                border: 1px solid lightgray;
                background: #fff;
                cursor: pointer;
                box-shadow: inset -5px 0px 5px -2px #0000001a;
                margin-top: -1px;
            }
            .field-name-content {
                vertical-align: sub;
            }
            .field-settings-table-child-toggle {
                display:none;
            }
            .inset-shadow {
                box-shadow: inset -5px 5px 5px -2px #0000001a;
            }
            .sortable {
                color: #000;
                margin: 0 8px 0 4px;
                cursor: pointer;
                vertical-align: -webkit-baseline-middle;
            }
            .expand-icon {
                vertical-align: sub;
                margin-right: 2px;
            }
            .edit-icon {
                width: 18px;
                height: 18px;
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' style='width:24px;height:24px' viewBox='0 0 24 24'%3E%3Cpath fill='%2350575e' d='M20.71,7.04C21.1,6.65 21.1,6 20.71,5.63L18.37,3.29C18,2.9 17.35,2.9 16.96,3.29L15.12,5.12L18.87,8.87M3,17.25V21H6.75L17.81,9.93L14.06,6.18L3,17.25Z' /%3E%3C/svg%3E");
                background-repeat: no-repeat;
                margin-left: auto;
                margin-right: 8px;
            }
            .add-new-link {
                margin: 8px 0 0 8px;
            }
            .dt-admin-modal-box-close-button {
                text-align: right;
                color: #cacaca;
                font-weight: 200;
                font-size: 1.75rem;
                position: absolute;
                right: 0;
                padding: 0.5rem;
                cursor: pointer;
            }
            .dt-admin-modal-translations-box-close-button {
                text-align: right;
                color: #cacaca;
                font-weight: 200;
                font-size: 1.75rem;
                position: absolute;
                right: 0;
                padding: 0.5rem;
                cursor: pointer;
            }
            .modal-overlay-content-table, .modal-translations-overlay-content-table {
                margin: 6px 10px 10px 10px;
                line-height: 2.5;
                max-height: 75%;
            }
            .modal-translations-overlay-content-table {
                height: 40em;
                overflow: auto;
                justify-content: center;
                display: flex;
            }
            .translations-save-row {
                padding: 18px;
                text-align: right;
                border-top: 1px solid lightgray;
            }
            .fields-table-left {
                width: 50%;
                background: #f1f1f1;
            }
            .fields-table-right {
                background-color: #f1f1f1;
                max-width: 300px;
            }
            .field-container {
                width: 100%;
                margin-top: 0.5em;
                cursor: pointer;
            }
            .field-element {
                margin-left: 18px;
            }
            .field-icon {
                width: 30px;
                height: 30px;
                vertical-align: top;
            }
            .new-custom-field {
                display: block;
                width: auto;
                height: auto;
                display: none;
                border: 1px solid #ccc;
                background-color: #fff;
                margin: 3%;
                padding: 1rem;
                overflow: hidden;
                scroll-behavior: smooth;
            }
            .new-field-option {
                color: #0073aa;
                cursor: pointer;
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
            .top-nav-row {
                display: flex;
                justify-content: space-between;
            }
            .typeahead-div {
                min-width: 40%;
                padding: 18px 18px 0 0;
            }
            .tab-content {
                padding: 12px;
            }
            .typeahead__container {
                position: absolute;
                min-width: 40%;
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
        <?php
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
