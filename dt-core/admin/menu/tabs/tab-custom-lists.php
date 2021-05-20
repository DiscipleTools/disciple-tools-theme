<?php

/**
 * Disciple Tools
 *
 * @class      Disciple_Tools_
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple.Tools
 * @author     Disciple.Tools
 */


/**
 * @todo First custom quick action gets erased on ENTER key down if icon input text is focused
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Tab_Custom_Lists
 */
class Disciple_Tools_Tab_Custom_Lists extends Disciple_Tools_Abstract_Menu_Base
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
        add_submenu_page( 'dt_options', __( 'Custom Lists', 'disciple_tools' ), __( 'Custom Lists', 'disciple_tools' ), 'manage_dt', 'dt_options&tab=custom-lists', [ 'Disciple_Tools_Settings_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        echo '<a href="'. esc_url( admin_url() ).'admin.php?page=dt_options&tab=custom-lists" class="nav-tab ';
        if ( $tab == 'custom-lists' ) {
            echo 'nav-tab-active';
        }
        echo '">'. esc_html__( 'Custom Lists' ) . '</a>';
    }

    /**
     * Packages and prints tab page
     *
     * @param $tab
     */
    public function content( $tab ) {
        if ( 'custom-lists' == $tab ) :

            $this->template( 'begin' );

            /* Worker Profile */
            $this->box( 'top', 'User (Worker) Contact Profile' );
            $this->process_user_profile_box();
            $this->user_profile_box(); // prints
            $this->box( 'bottom' );
            /* end Worker Profile */

            /* Channels */
            $this->box( 'top', __( 'Contact Communication Channels', 'disciple_tools' ) );
            $this->process_channels_box();
            $this->channels_box(); // prints
            $this->box( 'bottom' );
            /* end Channels */

            /* Quick Actions */
            $this->box( 'top', __( 'Quick Actions', 'disciple_tools' ) );
            $this->process_quick_actions_box();
            $this->quick_actions_box();
            $this->box( 'bottom' );
            /* end Quick Actions */

            /* Languages */
            $this->box( 'top', __( 'Language Options', 'disciple_tools' ) );
            $this->process_languages_box();
            $this->languages_box(); // prints
            $this->box( 'bottom' );
            /* end Languages */

            $this->template( 'right_column' );

            $this->template( 'end' );

        endif;
    }

    /**
     * Print the contact settings box.
     */
    public function user_profile_box() {
        echo '<form method="post" name="user_fields_form">';
        echo '<button type="submit" class="button-like-link" name="enter_bug_fix" value="&nasb"></button>';
        echo '<button type="submit" class="button-like-link" name="user_fields_reset" value="1">' . esc_html( __( "reset", 'disciple_tools' ) ) . '</button>';
        echo '<p>' . esc_html( __( "You can add or remove types of contact fields for user profiles.", 'disciple_tools' ) ) . '</p>';
        echo '<input type="hidden" name="user_fields_nonce" id="user_fields_nonce" value="' . esc_attr( wp_create_nonce( 'user_fields' ) ) . '" />';
        echo '<table class="widefat">';
        echo '<thead><tr><td>Label</td><td>Type</td><td>Description</td><td>Enabled</td><td>' . esc_html( __( "Delete", 'disciple_tools' ) ) . '</td></tr></thead><tbody>';

        // custom list block
        $site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
        if ( ! $site_custom_lists ) {
            wp_die( 'Failed to get dt_site_custom_lists() from options table.' );
        }
        $user_fields = $site_custom_lists['user_fields'];
        foreach ( $user_fields as $field ) {
            echo '<tr>
                        <td>' . esc_attr( $field['label'] ) . '</td>
                        <td>' . esc_attr( $field['type'] ) . '</td>
                        <td>' . esc_attr( $field['description'] ) . ' </td>
                        <td><input name="user_fields[' . esc_attr( $field['key'] ) . ']" type="checkbox" ' . ( $field['enabled'] ? "checked" : "" ) . ' /></td>
                        <td><button type="submit" name="delete_field" value="' . esc_attr( $field['key'] ) . '" class="button small" >' . esc_html( __( "Delete", 'disciple_tools' ) ) . '</button> </td>
                      </tr>';
        }
        // end list block

        echo '</table>';
        echo '<br><button type="button" onclick="jQuery(\'#add_user\').toggle();" class="button">' . esc_html( __( "Add", 'disciple_tools' ) ) . '</button>
                        <button type="submit" style="float:right;" class="button">' . esc_html( __( "Save", 'disciple_tools' ) ) . '</button>';
        echo '<div id="add_user" style="display:none;">';
        echo '<table width="100%"><tr><td><hr><br>
                    <input type="text" name="add_input_field[label]" placeholder="label" />&nbsp;';
        echo '<select name="add_input_field[type]" id="add_input_field_type">';
        // Iterate the options
        $user_fields_types = $site_custom_lists['user_fields_types'];
        foreach ( $user_fields_types as $value ) {
            echo '<option value="' . esc_attr( $value['key'] ) . '" >' . esc_attr( $value['label'] ) . '</option>';
        }
        echo '</select>' . "\n";

        echo '<input type="text" name="add_input_field[description]" placeholder="description" />&nbsp;
                    <button type="submit">' . esc_html( __( 'Add', 'disciple_tools' ) ) . '</button>
                    </td></tr></table></div>';

        echo '</tbody></form>';
    }

    /**
     * Process user profile settings
     */
    public function process_user_profile_box() {

        if ( isset( $_POST['user_fields_nonce'] ) ) {

            if ( !wp_verify_nonce( sanitize_key( $_POST['user_fields_nonce'] ), 'user_fields' ) ) {
                return;
            }

            // Process current fields submitted
            $site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
            if ( ! $site_custom_lists ) {
                wp_die( 'Failed to get dt_site_custom_lists() from options table.' );
            }

            foreach ( $site_custom_lists['user_fields'] as $key => $value ) {
                if ( isset( $_POST['user_fields'][ $key ] ) ) {
                    $site_custom_lists['user_fields'][ $key ]['enabled'] = true;
                } else {
                    $site_custom_lists['user_fields'][ $key ]['enabled'] = false;
                }
            }

            // Process new field submitted
            if ( !empty( $_POST['add_input_field']['label'] ) ) {

                $label = sanitize_text_field( wp_unslash( $_POST['add_input_field']['label'] ) );
                if ( empty( $label ) ) {
                    return;
                }

                if ( !empty( $_POST['add_input_field']['description'] ) ) {
                    $description = sanitize_text_field( wp_unslash( $_POST['add_input_field']['description'] ) );
                } else {
                    $description = '';
                }

                if ( !empty( $_POST['add_input_field']['type'] ) ) {
                    $type = sanitize_text_field( wp_unslash( $_POST['add_input_field']['type'] ) );
                } else {
                    $type = 'other';
                }

                $key = 'dt_user_' . sanitize_key( strtolower( str_replace( ' ', '_', $label ) ) );
                $enabled = true;

                // strip and make lowercase process
                $site_custom_lists['user_fields'][ $key ] = [
                    'label'       => $label,
                    'key'         => $key,
                    'type'        => $type,
                    'description' => $description,
                    'enabled'     => $enabled,
                ];
            }

            // Process a field to delete.
            if ( isset( $_POST['delete_field'] ) ) {

                $delete_key = sanitize_text_field( wp_unslash( $_POST['delete_field'] ) );

                unset( $site_custom_lists['user_fields'][ $delete_key ] );
                //TODO: Consider adding a database query to delete all instances of this key from usermeta

            }

            // Process reset request
            if ( isset( $_POST['user_fields_reset'] ) ) {

                unset( $site_custom_lists['user_fields'] );

                $site_custom_lists['user_fields'] = dt_get_site_custom_lists( 'user_fields' );
            }

            // Update the site option
            update_option( 'dt_site_custom_lists', $site_custom_lists, true );
        }
    }


    public function channels_box(){
        $fields = DT_Posts::get_post_field_settings( "contacts", false );
        ?>
        <form method="post" name="channels_box">
            <input type="hidden" name="channels_box_nonce" value="<?php echo esc_attr( wp_create_nonce( 'channels_box' ) ) ?>" />
            <table class="widefat">
                <thead>
                    <tr>
                        <td><?php esc_html_e( "Name", 'disciple_tools' ) ?></td>
                        <td><?php esc_html_e( "Key", 'disciple_tools' ) ?></td>
                        <td><?php esc_html_e( "Enabled", 'disciple_tools' ) ?></td>
                        <td><?php esc_html_e( "Hide domain if a url", 'disciple_tools' ) ?></td>
                        <td><?php esc_html_e( "Icon link (must be https)", 'disciple_tools' ) ?></td>
                        <td><?php esc_html_e( "Translation", 'disciple_tools' ) ?></td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $fields as $channel_key => $channel_option ) :
                        if ( $channel_option['type'] !== "communication_channel" ){
                            continue;
                        }

                        $enabled = !isset( $channel_option['enabled'] ) || $channel_option['enabled'] !== false;
                        $hide_domain = isset( $channel_option['hide_domain'] ) && $channel_option['hide_domain'] == true;
                        if ( $channel_key == 'phone' || $channel_key == 'email' || $channel_key == 'address' ){
                            continue;
                        } ?>

                    <tr>
                        <td><input type="text" name="channel_label[<?php echo esc_html( $channel_key ) ?>][default]" value="<?php echo esc_html( $channel_option["name"] ?? $channel_key ) ?>"></td>
                        <td><?php echo esc_html( $channel_key ) ?></td>
                        <td>
                            <input name="channel_enabled[<?php echo esc_html( $channel_key ) ?>]"
                                   type="checkbox" <?php echo esc_html( $enabled ? "checked" : "" ) ?> />
                        </td>
                        <td>
                            <input name="channel_hide_domain[<?php echo esc_html( $channel_key ) ?>]"
                                   type="checkbox" <?php echo esc_html( $hide_domain ? "checked" : "" ) ?> />
                        </td>
                        <td>
                            <input type="text" name="channel_icon[<?php echo esc_html( $channel_key ) ?>]" value="<?php echo esc_html( $channel_option["icon"] ?? "" ) ?>">
                            <?php if ( !empty( $channel_option["icon"] ) ) : ?>
                            <button type="submit" class="button" name="channel_reset_icon[<?php echo esc_html( $channel_key ) ?>]"><?php esc_html_e( "Reset link", 'disciple_tools' ) ?></button>
                            <?php endif; ?>
                        </td>
                        <td>
                        <?php $langs = dt_get_available_languages(); ?>
                        <button class="button small expand_translations">
                            <?php
                            $number_of_translations = 0;
                            foreach ( $langs as $lang => $val ){
                                if ( !empty( $channel_option["translations"][$val['language']] ) ){
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
                                    <td><label for="channel_label[<?php echo esc_html( $channel_key ) ?>][<?php echo esc_html( $val['language'] )?>]"><?php echo esc_html( $val['native_name'] )?></label></td>
                                    <td><input name="channel_label[<?php echo esc_html( $channel_key ) ?>][<?php echo esc_html( $val['language'] )?>]" type="text" value="<?php echo esc_html( $channel_option["translations"][$val['language']] ?? "" );?>"/></td>
                                </tr>
                            <?php endforeach; ?>
                            </table>
                        </div>
                    </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <br><button type="button" onclick="jQuery('#add_channel').toggle();" class="button">
                <?php echo esc_html_x( "Add new channel", 'communication channel (Phone, Email, Facebook)', 'disciple_tools' ) ?></button>
            <button type="submit" class="button" style="float:right;">
                <?php esc_html_e( "Save", 'disciple_tools' ) ?>
            </button>
            <div id="add_channel" style="display:none;">
                <hr>
                <input type="text" name="add_channel" placeholder="channel" />
                <button type="submit"><?php esc_html_e( "Add", 'disciple_tools' ) ?></button>
            </div>
        </form>
        <?php
    }

    public function process_channels_box(){
        if ( isset( $_POST["channels_box_nonce"] ) ){
            $fields = DT_Posts::get_post_field_settings( "contacts" );
            if ( !wp_verify_nonce( sanitize_key( $_POST['channels_box_nonce'] ), 'channels_box' ) ){
                self::admin_notice( __( "Something went wrong", 'disciple_tools' ), "error" );
                return;
            }

            $langs = dt_get_available_languages();
            $custom_field_options = dt_get_option( 'dt_field_customizations' );
            $custom_contact_fields = $custom_field_options['contacts'];

            foreach ( $fields as $field_key => $field_settings ){
                if ( $field_settings["type"] !== "communication_channel" ){
                    continue;
                }
                if ( isset( $_POST["channel_label"][$field_key]["default"] ) ){
                    $label = sanitize_text_field( wp_unslash( $_POST["channel_label"][$field_key]["default"] ) );
                    if ( $field_settings["name"] != $label ){
                        $custom_contact_fields[$field_key]["name"] = $label;
                    }
                }
                foreach ( $langs as $lang => $val ){
                    $langcode = $val['language'];

                    if ( isset( $_POST["channel_label"][$field_key][$langcode] ) ) {
                        $translated_label = sanitize_text_field( wp_unslash( $_POST["channel_label"][$field_key][$langcode] ) );
                        if ( ( empty( $translated_label ) && !empty( $custom_contact_fields[$field_key]["translations"][$langcode] ) ) || !empty( $translated_label ) ) {
                            $custom_contact_fields[$field_key]["translations"][$langcode] = $translated_label;
                        }
                    }
                }
                if ( isset( $_POST["channel_icon"][$field_key] ) ){
                    $icon = sanitize_text_field( wp_unslash( $_POST["channel_icon"][$field_key] ) );
                    if ( !isset( $channel_options["icon"] ) || $channel_options["icon"] != $icon ){
                        $custom_contact_fields[$field_key]["icon"] = $icon;
                    }
                }
                if ( isset( $_POST["channel_enabled"][$field_key] ) ){
                    $custom_contact_fields[$field_key]["enabled"] = true;
                } else {
                    $custom_contact_fields[$field_key]["enabled"] = false;
                }
                if ( isset( $_POST["channel_hide_domain"][$field_key] ) ){
                    $custom_contact_fields[$field_key]["hide_domain"] = true;
                } else {
                    $custom_contact_fields[$field_key]["hide_domain"] = false;
                }
                if ( isset( $_POST["channel_reset_icon"][$field_key] ) ){
                    unset( $custom_contact_fields[$field_key]["icon"] );
                }
            }
            if ( !empty( $_POST["add_channel"] ) ){
                $label = sanitize_text_field( wp_unslash( $_POST["add_channel"] ) );
                $key = dt_create_field_key( 'contact_' . $label );
                if ( !empty( $key ) ){
                    if ( isset( $custom_contact_fields[$key] ) ){
                        self::admin_notice( __( "This channel already exists", 'disciple_tools' ), "error" );
                    } else {
                        $custom_contact_fields[$key] = [
                            "name" => $label,
                            "type" => "communication_channel",
                            "tile" => "details",
                            "enabled" => true
                        ];
                        wp_cache_delete( "contacts_field_settings" );
                    }
                }
            }
            $custom_field_options["contacts"] = $custom_contact_fields;
            update_option( "dt_field_customizations", $custom_field_options );
        }
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


    /**
     * UI for picking languages
     */
    private function languages_box(){
        $languages = dt_get_option( "dt_working_languages" ) ?: [];
        $dt_global_languages_list = dt_get_global_languages_list();
        uasort($dt_global_languages_list, function( $a, $b ) {
            return strcmp( $a['label'], $b['label'] );
        });
        ?>
        <form method="post" name="languages_box" id="languages">
            <input type="hidden" name="languages_box_nonce" value="<?php echo esc_attr( wp_create_nonce( 'languages_box' ) ) ?>" />
            <table class="widefat">
                <thead>
                <tr>
                    <td><?php esc_html_e( "Key", 'disciple_tools' ) ?></td>
                    <td><?php esc_html_e( "Default Label", 'disciple_tools' ) ?></td>
                    <td><?php esc_html_e( "Custom Label", 'disciple_tools' ) ?></td>
                    <td><?php esc_html_e( "ISO 639-3 code", 'disciple_tools' ) ?></td>
                    <td><?php esc_html_e( "Enabled", 'disciple_tools' ) ?></td>
                    <td><?php esc_html_e( "Translation", 'disciple_tools' ) ?></td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ( $languages as $language_key => $language_option ) :

                    $enabled = !isset( $language_option['deleted'] ) || $language_option['deleted'] == false; ?>

                    <tr>
                        <td><?php echo esc_html( $language_key ) ?></td>
                        <td><?php echo esc_html( isset( $dt_global_languages_list[$language_key] ) ? $dt_global_languages_list[$language_key]["label"] : "" ) ?></td>
                        <td><input type="text" placeholder="Custom Label" name="language_label[<?php echo esc_html( $language_key ) ?>][default]" value="<?php echo esc_html( ( !isset( $dt_global_languages_list[$language_key] ) || ( isset( $dt_global_languages_list[$language_key] ) && $dt_global_languages_list[$language_key]["label"] != $language_option["label"] ) ) ? $language_option["label"] : "" ) ?>"></td>
                        <td><input type="text" placeholder="ISO 639-3 code" maxlength="3" name="language_code[<?php echo esc_html( $language_key ) ?>]" value="<?php echo esc_html( $language_option["iso_639-3"] ?? "" ) ?>"></td>
                        <td>
                            <input name="language_enabled[<?php echo esc_html( $language_key ) ?>]"
                                   type="checkbox" <?php echo esc_html( $enabled ? "checked" : "" ) ?> />
                        </td>

                        <td>
                            <?php $langs = dt_get_available_languages(); ?>
                            <button class="button small expand_translations">
                                <?php
                                $number_of_translations = 0;
                                foreach ( $langs as $lang => $val ){
                                    if ( !empty( $language_option["translations"][$val['language']] ) ){
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
                                            <td><label for="language_label[<?php echo esc_html( $language_key ) ?>][<?php echo esc_html( $val['language'] )?>]"><?php echo esc_html( $val['native_name'] )?></label></td>
                                            <td><input name="language_label[<?php echo esc_html( $language_key ) ?>][<?php echo esc_html( $val['language'] )?>]" type="text" value="<?php echo esc_html( $language_option["translations"][$val['language']] ?? "" );?>"/></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <br><button type="button" onclick="jQuery('#add_language').toggle();" class="button">
                <?php echo esc_html__( "Add new language", 'disciple_tools' ) ?></button>
            <button type="submit" class="button" style="float:right;">
                <?php esc_html_e( "Save", 'disciple_tools' ) ?>
            </button>
            <div id="add_language" style="display:none;">
                <hr>
                <p><?php esc_html_e( 'Select from the language list', 'disciple_tools' ); ?></p>
                <select name="new_lang_select">
                    <option></option>
                    <?php foreach ( $dt_global_languages_list as $lang_key => $lang_option ) : ?>
                        <option value="<?php echo esc_html( $lang_key ); ?>"><?php echo esc_html( $lang_option["label"] ?? "" ); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="button"><?php esc_html_e( "Add", 'disciple_tools' ) ?></button>
                <br>
                <br>
                <p><?php esc_html_e( 'If your language is not in the list, you can create it manually', 'disciple_tools' ); ?></p>
                <input name="create_custom_language" placeholder="Custom Language" type="text">
                <input name="create_custom_language_code" placeholder="ISO 639-3 code (optional)" maxlength="3" type="text">
                <button type="submit" class="button"><?php esc_html_e( "Create", 'disciple_tools' ) ?></button>
            </div>
        </form>
        <?php
    }

    private function process_languages_box() {
        if ( !isset( $_POST["languages_box_nonce"] ) ) {
            return;
        }
        if ( !wp_verify_nonce( sanitize_key( $_POST['languages_box_nonce'] ), 'languages_box' ) ) {
            self::admin_notice( __( "Something went wrong", 'disciple_tools' ), "error" );
            return;
        }

        $languages = dt_get_option( "dt_working_languages" ) ?: [];
        $dt_global_languages_list = dt_get_global_languages_list();

        $langs = dt_get_available_languages();
        foreach ( $languages as $language_key => $language_options ){

            if ( isset( $_POST["language_label"][$language_key]["default"] ) ){
                $label = sanitize_text_field( wp_unslash( $_POST["language_label"][$language_key]["default"] ) );
                if ( $language_options["label"] != $label ){
                    $languages[$language_key]["label"] = $label;
                }
                if ( empty( $label ) && isset( $dt_global_languages_list[$language_key]["label"] ) ){
                    $languages[$language_key]["label"] = $dt_global_languages_list[$language_key]["label"];
                }
            }
            if ( isset( $_POST["language_code"][$language_key] ) ){
                $code = sanitize_text_field( wp_unslash( $_POST["language_code"][$language_key] ) );
                if ( ( $language_options["iso_639-3"] ?? "" ) != $code ) {
                    $languages[$language_key]["iso_639-3"] = $code;
                }
            }
            foreach ( $langs as $lang => $val ){
                $langcode = $val['language'];
                if ( isset( $_POST["language_label"][$language_key][$langcode] ) ) {
                    $translated_label = sanitize_text_field( wp_unslash( $_POST["language_label"][$language_key][$langcode] ) );
                    if ( ( empty( $translated_label ) && !empty( $languages[$language_key]["translations"][$langcode] ) ) || !empty( $translated_label ) ){
                        $languages[$language_key]["translations"][$langcode] = $translated_label;
                    }
                }
            }
            $languages[$language_key]["deleted"] = !isset( $_POST["language_enabled"][$language_key] );
        }

        if ( !empty( $_POST["new_lang_select"] ) ) {
            $lang_key = sanitize_text_field( wp_unslash( $_POST["new_lang_select"] ) );
            $lang = isset( $dt_global_languages_list[ $lang_key ] ) ? $dt_global_languages_list[ $lang_key ] : null;
            if ( $lang === null ) {
                return;
            };
            $languages[$lang_key] = $dt_global_languages_list[ $lang_key ];
            $languages[$lang_key]["enabled"] = true;
        }
        if ( !empty( $_POST["create_custom_language"] ) ) {
            $language = sanitize_text_field( wp_unslash( $_POST["create_custom_language"] ) );
            $code = isset( $_POST["create_custom_language_code"] ) ?sanitize_text_field( wp_unslash( $_POST["create_custom_language_code"] ) ) : null;
            $lang_key = dt_create_field_key( $language );
            if ( isset( $dt_global_languages_list[$lang_key] ) || isset( $languages[$lang_key] ) ) {
                $lang_key = dt_create_field_key( $language, true );
            }
            $languages[$lang_key] = [
                "label" => $language,
                "enabled" => true
            ];
            if ( !empty( $code ) ){
                $languages[$lang_key]["iso_639-3"] = $code;
            }
        }

        update_option( "dt_working_languages", $languages, false );
    }


    /**
     * UI for Quick Actions
     */
    private function quick_actions_box(){
        $fields = DT_Posts::get_post_settings( 'contacts' )['fields'];
        $default_fields = apply_filters( 'dt_custom_fields_settings', [], "contacts" );
        ?>
        <form method="post" name="quick_actions_box" id="quick-actions">
            <input type="hidden" name="quick_actions_box_nonce" value="<?php echo esc_attr( wp_create_nonce( 'quick_actions_box' ) ) ?>" />
            <table class="widefat">
                <thead>
                <tr>
                    <td></td>
                    <td><?php esc_html_e( 'Name', 'disciple_tools' ) ?></td>
                    <td><?php esc_html_e( 'Icon link (must be https)', 'disciple_tools' ) ?></td>
                    <td><?php esc_html_e( 'Delete', 'disciple_tools' ) ?></td>
                </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ( $fields as $field_key => $field_settings ) :
                        if ( ! isset( $field_settings['section'] ) || substr( $field_settings['section'], 0, 13 ) !== 'quick_buttons' ) {
                            continue;
                        }?>
                        <tr>
                            <td>
                                <img src="<?php echo esc_attr( $field_settings['icon'] ); ?>" class="quick-action-menu">
                            </td>
                            <td>
                                <?php
                                if ( !isset( $default_fields[$field_key] ) ) {
                                    echo '<input type="text" name="edit_field[' . esc_attr( $field_key ) . ']" value="'. esc_html( $field_settings['name'] ) . '">';
                                } else {
                                    echo esc_html( $field_settings['name'] );
                                    echo '<input type="hidden" name="edit_field[' . esc_attr( $field_key ) . ']" value="'. esc_html( $field_settings['name'] ) . '">';
                                } ?>
                            </td>
                            <td class="quick-action-menu"><input type="text" name="edit_field_icon[<?php echo esc_attr( $field_key ); ?>]" value="<?php echo esc_html( $field_settings['icon'] ) ?>"></td>
                            <td>
                                <?php
                                if ( !isset( $default_fields[$field_key] ) ){
                                    echo '<button type="submit" name="delete_field" value="' . esc_attr( $field_key ) . '" class="button small">' . esc_html( __( "Delete", 'disciple_tools' ) ) . '</button>';
                                } ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <br><button type="button" onclick="jQuery('#add_quick_action').toggle();" class="button">
                <?php echo esc_html__( 'Add new quick action', 'disciple_tools' ) ?></button>
            <button type="submit" style="float:right;" class="button"><?php echo esc_html( __( 'Save', 'disciple_tools' ) ) ?></button>
            <div id="add_quick_action" style="display:none;">
                <hr>
                <label for="add_custom_quick_action_label">Name:</label>
                <input name="add_custom_quick_action_label" placeholder="Custom Quick Action" type="text">
                <br>
                <br>
                <div class="menuitem">
                    <label for="default">Default Icon:</label>
                    <input type="radio" name="icon" value="default" checked><img src="<?php echo esc_html( get_template_directory_uri() ); ?>/dt-assets/images/follow.svg"></div>
                    <br>
                    <label for="custom">Custom Icon URL:</label>
                    <input type="radio" name="icon" value="custom">
                    <input name="add_custom_quick_action_icon" type="text">
                    <button type="submit" class="button"><?php esc_html_e( 'Add', 'disciple_tools' ) ?></button>
            </div>
        </form>
        <?php
    }

    public function process_quick_actions_box(){
        // Look for nonce and verify it
        if ( isset( $_POST['quick_actions_box_nonce'] ) ) {
            if ( !wp_verify_nonce( sanitize_key( $_POST['quick_actions_box_nonce'] ), 'quick_actions_box' ) ){
                self::admin_notice( __( 'Something went wrong', 'disciple_tools' ), 'error' );
                return;
            }
        }

        // Load custom fields
        $custom_field_options = dt_get_option( 'dt_field_customizations' );

        // Check if custom fields exists and if not create an empty array
        if ( ! isset( $custom_field_options ) ) {
            update_option( 'dt_field_customizations', [], true );
            $custom_field_options = dt_get_option( 'dt_field_customizations' );
        }

        // Check if custom fields aren't empty and if so create an empty array
        if ( empty( $custom_field_options['contacts'] ) ) {
            update_option( 'dt_field_customizations', [], true );
            $custom_field_options = dt_get_option( 'dt_field_customizations' );
        }

        // Add a new custom field
        if ( ! empty( $_POST['add_custom_quick_action_label'] ) ) {
            $label = sanitize_text_field( wp_unslash( $_POST['add_custom_quick_action_label'] ) );
            $key = dt_create_field_key( 'quick_button_' . $label );

            // Check quick action icon
            if ( ! empty( $_POST['add_custom_quick_action_icon'] ) ) {
                $icon_url = sanitize_text_field( wp_unslash( $_POST["add_custom_quick_action_icon"] ) );
            } else {
                $icon_url = get_template_directory_uri() . '/dt-assets/images/follow.svg';
            }

            if ( empty( $label ) ) {
                wp_die( 'Quick Action Update Error: Label is missing' );
            }

            if ( empty( $key ) ) {
                wp_die( 'Quick Action Update Error: Key is missing' );
            } else {
                // Add new Quick Action
                $key = dt_create_field_key( $key, true );
                $custom_field_options['contacts'][$key] = [
                    'name'        => $label,
                    'description' => '',
                    'type'        => 'number',
                    'default'     => 0,
                    'section'     => 'quick_buttons',
                    'icon'        => $icon_url,
                    'customizable' => false,
                ];

                update_option( 'dt_field_customizations', $custom_field_options, true );
                wp_cache_delete( "contacts_field_settings" );

                self::admin_notice( __( 'Quick Action added successfully', 'disciple_tools' ), 'success' );
                return;
            }
        }

        // Delete Quick Action
        if ( ! empty( $_POST['delete_field'] ) ) {
            $delete_key = sanitize_text_field( wp_unslash( $_POST['delete_field'] ) );

            unset( $custom_field_options['contacts'][ $delete_key ] );
            update_option( 'dt_field_customizations', $custom_field_options, true );

            wp_cache_delete( "contacts_field_settings" );
            self::admin_notice( __( 'Quick Action deleted successfully', 'disciple_tools' ), 'success' );
            return;
        }

        // Edit Quick Action
        if ( ! empty( $_POST['edit_field'] ) ) {

            $quick_action_edits = dt_recursive_sanitize_array( $_POST['edit_field'] );

            if ( isset( $_POST['edit_field_icon'] ) ) {
                $edit_field_icon = dt_recursive_sanitize_array( $_POST['edit_field_icon'] );
            } else {
                $edit_field_icon = get_template_directory_uri() . '/dt-assets/images/follow.svg';
            }

            foreach ( $quick_action_edits as $quick_action_key => $quick_action_new_name ) {
                $quick_action_key = sanitize_text_field( wp_unslash( $quick_action_key ) );
                $quick_action_new_name = sanitize_text_field( wp_unslash( $quick_action_new_name ) );
                $custom_field_options['contacts'][ $quick_action_key ]['name'] = $quick_action_new_name;
            }

            foreach ( $edit_field_icon as $key => $value ) {
                $custom_field_options['contacts'][ $key ]['icon'] = $value;
            }

            update_option( 'dt_field_customizations', $custom_field_options, true );
                    wp_cache_delete( "contacts_field_settings" );

                    self::admin_notice( __( 'Quick Action edited successfully', 'disciple_tools' ), 'success' );
                    return;
        }
    }
}
Disciple_Tools_Tab_Custom_Lists::instance();
