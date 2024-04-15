<?php

/**
 * Disciple.Tools
 *
 * @class      Disciple_Tools_
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple.Tools
 * @author     Disciple.Tools
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_General_Tab
 */
class Disciple_Tools_General_Tab extends Disciple_Tools_Abstract_Menu_Base
{
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
//        add_action( 'admin_menu', [ $this, 'add_submenu' ] );
        add_action( 'dt_settings_tab_menu', [ $this, 'add_tab' ], 5, 1 );
        add_action( 'dt_settings_tab_content', [ $this, 'content' ], 10, 1 );

        parent::__construct();
    }

    public function add_submenu() {
//        add_submenu_page( 'dt_options', __( 'General', 'disciple_tools' ), __( 'General', 'disciple_tools' ), 'manage_dt', 'dt_options&tab=general', [ 'Disciple_Tools_Settings_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        echo '<a href="'. esc_url( admin_url() ).'admin.php?page=dt_options&tab=general" class="nav-tab ';
        if ( $tab == 'general' || !isset( $tab ) ) {
            echo 'nav-tab-active';
        }
        echo '">' . esc_html__( 'General Settings' ) . '</a>';
    }

    public function content( $tab ) {
        if ( 'general' == $tab ) :

            $modules = dt_get_option( 'dt_post_type_modules' );
            $this->template( 'begin', 1 );

            /* Base User */
            $this->box( 'top', 'Base User' );
            $this->process_base_user();
            $this->base_user();
            $this->box( 'bottom' );
            /* End Base User */

            /* Storage Settings */
            $this->box( 'top', 'Storage Settings' );
            $this->process_storage_settings();
            $this->storage_settings();
            $this->box( 'bottom' );
            /* End Storage Settings */

            /* Email Settings */
            $this->box( 'top', 'Email Settings' );
            $this->process_email_settings();
            $this->email_settings();
            $this->box( 'bottom' );
            /* End Email Settings */

            /* Modules */
            $this->box( 'top', 'Modules' );
            $this->process_contact_modules();
            $this->display_contact_modules();
            $this->box( 'bottom' );
            /* Modules */

            /* Site Notifications */
            $this->box( 'top', 'Site Notifications' );
            $this->process_user_notifications();
            $this->user_notifications(); // prints content for the notifications box
            $this->box( 'bottom' );
            /* Site Notifications */

            /* Update Required */
            if ( isset( $modules['access_module']['enabled'] ) && $modules['access_module']['enabled'] ){

                /* Translation Dialog */
                dt_display_translation_dialog();

                $this->box( 'top', 'Update Needed Triggers' );
                $this->process_update_required();
                $this->update_required_options();
                $this->box( 'bottom' );
            }
            /* Update Required */

            /* Update Required */
            $this->box( 'top', 'Group Tile Preferences' );
            $this->process_group_preferences();
            $this->update_group_preferences();
            $this->box( 'bottom' );
            /* Site Notifications */

            /* User Visibility */
            $this->box( 'top', 'User Preferences' );
            $this->process_user_preferences();
            $this->update_user_preferences();
            $this->box( 'bottom' );
            /* User Visibility */


            /* Contact Setup  */
            $this->box( 'top', 'Contact Preferences' );
            $this->process_dt_contact_preferences();
            $this->show_dt_contact_preferences();
            $this->box( 'bottom' );
            /* Contact Setup */

            /* Custom Logo */
            $this->box( 'top', 'Custom Logo' );
            $this->process_custom_logo();
            $this->custom_logo();
            $this->box( 'bottom' );

            $this->box( 'top', 'Performance Mode' );
            $this->process_dt_performance_mode();
            $this->dt_performance_mode();
            $this->box( 'bottom' );

            $this->template( 'right_column' );

            $this->template( 'end' );

        endif;
    }

    /**
     * Prints the user notifications box
     */
    public function user_notifications() {
        $notifications = dt_get_site_notification_defaults();
        ?>
        <form method="post" name="notifications-form">
            <button type="submit" class="button-like-link" name="reset_notifications" value="1"><?php esc_html_e( 'reset', 'disciple_tools' ) ?></button>
            <p><?php esc_html_e( 'These are site overrides for individual preferences for notifications. Uncheck if you want, users to make their own decision on which notifications to receive.', 'disciple_tools' ) ?></p>
            <input type="hidden" name="notifications_nonce" id="notifications_nonce" value="' <?php echo esc_attr( wp_create_nonce( 'notifications' ) ) ?>'" />

            <table class="widefat">
                <?php
                foreach ( $notifications['types'] ?? [] as $type => $type_settings ) {
                    ?>
                    <tr>
                        <td><?php echo esc_html( !empty( $type_settings['label'] ) ? $type_settings['label'] : $type ) ?></td>
                        <?php
                        foreach ( $type_settings as $setting_key => $setting_value ) {
                            if ( $setting_key !== 'label' ) {
                                $channel_label = isset( $notifications['channels'], $notifications['channels'][$setting_key], $notifications['channels'][$setting_key]['label'] ) ? $notifications['channels'][$setting_key]['label'] : $setting_key;
                                $input_name = $type .'_'. $setting_key;
                                ?>
                                <td>
                                    <?php echo esc_html( $channel_label ) ?>
                                    <input name="<?php echo esc_html( $input_name ) ?>" type="checkbox"
                                        <?php echo $setting_value ? 'checked' : '' ?> />
                                </td>
                                <?php
                            }
                        }
                        ?>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <br>
            <span style="float:right;"><button type="submit" class="button float-right"><?php esc_html_e( 'Save', 'disciple_tools' ) ?></button> </span>
        </form>
        <?php
    }

    /**
     * Process user notifications box
     */
    public function process_user_notifications() {

        if ( isset( $_POST['notifications_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['notifications_nonce'] ) ), 'notifications' ) ) {

            $site_options = dt_get_option( 'dt_site_options' );

            if ( isset( $_POST['reset_notifications'] ) ) {
                unset( $site_options['notifications'] );
                $site_option_defaults = dt_get_site_options_defaults();
                $site_options['notifications'] = $site_option_defaults['notifications'];
                return update_option( 'dt_site_options', $site_options, true );
            }

            $notifications = dt_get_site_notification_defaults();
            $updated_types = [];
            foreach ( $notifications['types'] as $type => $type_settings ) {
                $updated_types[$type] = $type_settings;
                foreach ( $type_settings as $setting_key => $setting_value ) {
                    if ( $setting_key !== 'label' ) {
                        $updated_types[$type][$setting_key] = isset( $_POST[ $type .'_'. $setting_key ] );
                    }
                }
            }
            $notifications['types'] = $updated_types;
            $site_options['notifications'] = $notifications;

            update_option( 'dt_site_options', $site_options, true );
        }
    }

    /**
     * Set base user assigns the catch-all user
     */
    public function base_user() {
        $base_user = dt_get_base_user();
        $potential_user_list = get_users(
            [
                'role__in' => [ 'dispatcher', 'administrator', 'dt_admin', 'multiplier', 'marketer', 'strategist' ],
                'order'    => 'ASC',
                'orderby'  => 'display_name',
            ]
        );

        echo '<form method="post" name="extension_modules_form">';
        echo '<p>Base User is the catch-all account for orphaned contacts and other records to be assigned to. To be a Base User, the user must be an Administrator, Dispatcher, Multiplier, Digital Responder, or Strategist.</p>';
        echo '<hr>';
        echo '<input type="hidden" name="base_user_nonce" id="base_user_nonce" value="' . esc_attr( wp_create_nonce( 'base_user' ) ) . '" />';

        echo 'Current Base User: <select name="base_user_select">';

        echo '<option value="'. esc_attr( $base_user->ID ) . '">' . esc_attr( $base_user->display_name ) . '</option>';
        echo '<option disabled>---</option>';

        foreach ( $potential_user_list as $potential_user ) {
            echo '<option value="' . esc_attr( $potential_user->ID ) . '">' . esc_attr( $potential_user->display_name ) . '</option>';
        }

        echo '</select>';

        echo '<span style="float:right;"><button type="submit" class="button float-right">Update</button></span>';
        echo '</form>';
    }

    /**
     * Process changes to the base user.
     */
    public function process_base_user() {
        if ( isset( $_POST['base_user_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['base_user_nonce'] ) ), 'base_user' ) ) {
            if ( isset( $_POST['base_user_select'] ) ) {
                $user_id = sanitize_key( wp_unslash( $_POST['base_user_select'] ) );
                if ( is_numeric( $user_id ) ) {
                    update_option( 'dt_base_user', $user_id );
                }
            }
        }
    }

    public function storage_settings(): void {
        $storage_connections = apply_filters( 'dt_storage_connections', [] );
        if ( !array_key_exists( 'disciple-tools-storage/disciple-tools-storage.php', get_plugins() ) || !is_plugin_active( 'disciple-tools-storage/disciple-tools-storage.php' ) ) {
            ?>
          <span class="notice notice-warning" style="display: inline-block; padding-top: 10px; padding-bottom: 10px; width: 97%;">
                    <?php echo sprintf( 'Ensure Disciple.Tools Storage Plugin has been <a href="%s" target="_blank">installed and activated.</a>', 'https://github.com/DiscipleTools/disciple-tools-storage' ); ?>
                </span>
            <?php
        } elseif ( empty( $storage_connections ) ) {
            ?>
          <span class="notice notice-warning" style="display: inline-block; padding-top: 10px; padding-bottom: 10px; width: 97%;">
                    <?php echo sprintf( 'Ensure Disciple.Tools Storage Plugin has been <a href="%s">set up with valid connections</a>; which have been enabled.', esc_url( get_admin_url( null, 'admin.php?page=disciple_tools_storage' ) ) ); ?>
                </span>
            <?php
        } else { ?>
        <form method="POST">
            <input type="hidden" name="storage_settings_nonce" id="storage_settings_nonce"
                   value="<?php echo esc_attr( wp_create_nonce( 'storage_settings' ) ) ?>"/>

            <table class="widefat">
                <tbody>
                <tr>
                    <td>
                        <label for="storage_connection"><?php esc_html_e( 'Select storage connection for uploading pictures and files.', 'disciple_tools' ) ?></label>
                        <br>
                        See configuration settings <a href="<?php echo esc_html( admin_url( 'admin.php?page=disciple_tools_storage' ) ) ?>">here</a>
                    </td>
                    <td>
                        <select name="storage_connection" id="storage_connection">
                            <option value="">--- None ---</option>
                            <?php
                            $storage_connection_id = dt_get_option( 'dt_storage_connection_id' );
                            foreach ( $storage_connections as $connection ) {
                                $selected = $connection['id'] === $storage_connection_id;
                                ?>
                                <option <?php echo ( $selected ? 'selected' : '' ) ?> value="<?php echo esc_attr( $connection['id'] ) ?>"><?php echo esc_attr( $connection['name'] ) ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                </tbody>
            </table>

            <br>
            <span style="float:right;">
              <button type="submit" class="button float-right"><?php esc_html_e( 'Update', 'disciple_tools' ) ?></button></span>
        </form>
        <?php }
    }

    public function process_storage_settings() {
        if ( isset( $_POST['storage_settings_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['storage_settings_nonce'] ) ), 'storage_settings' ) ) {
            if ( isset( $_POST['storage_connection'] ) ) {
                update_option( 'dt_storage_connection_id', sanitize_text_field( wp_unslash( $_POST['storage_connection'] ) ) );
            }
        }
    }

    public function email_settings(){
        ?>
        <form method="POST">
            <input type="hidden" name="email_base_subject_nonce" id="email_base_subject_nonce"
                   value="<?php echo esc_attr( wp_create_nonce( 'email_subject' ) ) ?>"/>

            <table class="widefat">
                <tbody>
                <tr>
                    <td>
                        <label
                            for="email_address"><?php echo esc_html( sprintf( 'Specify notification from email address. Leave blank to use default (%s)', dt_default_email_address() ) ) ?></label>
                    </td>
                    <td>
                        <input name="email_address" id="email_address"
                               value="<?php echo esc_html( dt_get_option( 'dt_email_base_address' ) ) ?>"/>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label
                            for="email_name"><?php echo esc_html( sprintf( 'Specify notification from name. Leave blank to use default (%s)', dt_default_email_name() ) ) ?></label>
                    </td>
                    <td>
                        <input name="email_name" id="email_name"
                               value="<?php echo esc_html( dt_get_option( 'dt_email_base_name' ) ) ?>"/>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label
                            for="email_subject"><?php esc_html_e( 'Configure the first part of the subject line in email sent by Disciple.Tools', 'disciple_tools' ) ?></label>
                    </td>
                    <td>
                        <input name="email_subject" id="email_subject"
                               value="<?php echo esc_html( dt_get_option( 'dt_email_base_subject' ) ) ?>"/>
                    </td>
                </tr>
                </tbody>
            </table>

            <br>
            <span style="float:right;"><button type="submit"
                                               class="button float-right"><?php esc_html_e( 'Update', 'disciple_tools' ) ?></button></span>
        </form>
        <?php
    }

    /**
     * Process changes to the email settings
     */
    public function process_email_settings() {
        if ( isset( $_POST['email_base_subject_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['email_base_subject_nonce'] ) ), 'email_subject' ) ) {
            if ( isset( $_POST['email_subject'] ) ) {
                $email_subject = sanitize_text_field( wp_unslash( $_POST['email_subject'] ) );
                update_option( 'dt_email_base_subject', $email_subject );
            }

            if ( isset( $_POST['email_address'] ) ) {
                $email_subject = sanitize_text_field( wp_unslash( $_POST['email_address'] ) );
                update_option( 'dt_email_base_address', $email_subject );
            }

            if ( isset( $_POST['email_name'] ) ) {
                $email_subject = sanitize_text_field( wp_unslash( $_POST['email_name'] ) );
                update_option( 'dt_email_base_name', $email_subject );
            }
        }
    }

    public function process_update_required(){
        if ( isset( $_POST['update_required_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['update_required_nonce'] ) ), 'update_required' ) ) {
            $site_options = dt_get_option( 'dt_site_options' );

            if ( isset( $_POST['run_update_required'] ) ){
                do_action( 'dt_find_contacts_that_need_an_update' );
            }
            $site_options['update_required']['enabled'] = isset( $_POST['triggers_enabled'] );

            foreach ( $site_options['update_required']['options'] as $option_index => $option ){
                if ( isset( $_POST[$option_index . '_days'] ) ){
                    $site_options['update_required']['options'][$option_index]['days'] = sanitize_text_field( wp_unslash( $_POST[$option_index . '_days'] ) );
                }
                if ( isset( $_POST[$option_index . '_comment'] ) ){
                    $site_options['update_required']['options'][$option_index]['comment'] = wp_unslash( sanitize_text_field( wp_unslash( $_POST[$option_index . '_comment'] ) ) );
                }
                if ( isset( $_POST[$option_index . '_translations'] ) ){
                    $translations = [];
                    $uploaded_translations = dt_recursive_sanitize_array( $_POST[$option_index . '_translations'] );
                    foreach ( $uploaded_translations ?? [] as $lang_key => $translation ) {
                        if ( !empty( $translation ) ) {
                            $translations[$lang_key] = sanitize_text_field( wp_unslash( $translation ) );
                        }
                    }
                    $site_options['update_required']['options'][$option_index]['translations'] = $translations;
                }
            }
            update_option( 'dt_site_options', $site_options, true );
        }

        if ( isset( $_POST['group_update_required_nonce'] ) &&
             wp_verify_nonce( sanitize_key( wp_unslash( $_POST['group_update_required_nonce'] ) ), 'group_update_required' ) ) {
            $site_options = dt_get_option( 'dt_site_options' );

            if ( isset( $_POST['run_update_required'] ) ){
                do_action( 'dt_find_contacts_that_need_an_update' );
            }
            $site_options['group_update_required']['enabled'] = isset( $_POST['triggers_enabled'] );

            foreach ( $site_options['group_update_required']['options'] as $option_index => $option ){
                if ( isset( $_POST[$option_index . '_days'] ) ){
                    $site_options['group_update_required']['options'][$option_index]['days'] = sanitize_text_field( wp_unslash( $_POST[$option_index . '_days'] ) );
                }
                if ( isset( $_POST[$option_index . '_comment'] ) ){
                    $site_options['group_update_required']['options'][$option_index]['comment'] = wp_unslash( sanitize_text_field( wp_unslash( $_POST[$option_index . '_comment'] ) ) );
                }
                if ( isset( $_POST[$option_index . '_translations'] ) ){
                    $translations = [];
                    $uploaded_translations = dt_recursive_sanitize_array( $_POST[$option_index . '_translations'] );
                    foreach ( $uploaded_translations ?? [] as $lang_key => $translation ) {
                        if ( !empty( $translation ) ) {
                            $translations[$lang_key] = sanitize_text_field( wp_unslash( $translation ) );
                        }
                    }
                    $site_options['group_update_required']['options'][$option_index]['translations'] = $translations;
                }
            }
            update_option( 'dt_site_options', $site_options, true );
        }
    }

    public function update_required_options(){
        $site_options            = dt_seeker_path_triggers_capture_pre_existing_options( dt_get_option( 'dt_site_options' ) );
        $update_required_options = $site_options['update_required']['options'];
        $field_options           = DT_Posts::get_post_field_settings( 'contacts' );
        $available_languages = dt_get_available_languages();
        ?>
        <h3><?php esc_html_e( 'Contacts', 'disciple_tools' ) ?></h3>
        <form method="post" name="update_required-form">
            <button type="submit" class="button-like-link" name="run_update_required" value="1"><?php esc_html_e( 'Run checker now', 'disciple_tools' ) ?></button>
            <p><?php esc_html_e( 'Change how long to wait before a contact needs an update', 'disciple_tools' ) ?></p>

            <table style="min-width: 100%;">
                <tbody>
                <tr>
                    <td style="text-align: left; padding: 0;">
                        <p>
                            <?php esc_html_e( 'Update needed triggers enabled', 'disciple_tools' ) ?>
                            <input type="checkbox"
                                   name="triggers_enabled" <?php echo esc_html( $site_options['update_required']['enabled'] ) ? 'checked' : '' ?> />
                        </p>
                    </td>
                    <td style="text-align: right; padding: 0;">
                        <a href="<?php echo esc_url( admin_url() ) ?>admin.php?page=dt_options&tab=custom-fields&post_type=contacts&field-select=contacts_seeker_path&field_selected">
                            <?php esc_html_e( 'Update seeker path options', 'disciple_tools' ) ?>
                        </a>
                    </td>
                </tr>
                </tbody>
            </table>

            <input type="hidden" name="update_required_nonce" id="update_required_nonce" value="' <?php echo esc_attr( wp_create_nonce( 'update_required' ) ) ?>'" />

            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Status', 'disciple_tools' ) ?></th>
                        <th><?php esc_html_e( 'Seeker Path', 'disciple_tools' ) ?></th>
                        <th><?php esc_html_e( 'Days to wait', 'disciple_tools' ) ?></th>
                        <th><?php esc_html_e( 'Comment', 'disciple_tools' ) ?></th>
                        <th><?php esc_html_e( 'Translations', 'disciple_tools' ) ?></th>
                    </tr>
                </thead>

                <?php
                foreach ( $field_options['seeker_path']['default'] as $default_option_key => $default_option ) {
                    $deleted_flag = $default_option['deleted'] ?? null;
                    if ( ! ( isset( $deleted_flag ) && ( $deleted_flag === true ) ) ) {
                        foreach ( $update_required_options as $option_key => $option ) {
                            if ( $default_option_key === $option['seeker_path'] ) {
                                ?>
                                <tr>
                                    <td><?php echo esc_html( $field_options['overall_status']['default'][ $option['status'] ]['label'] ?? '' ) ?></td>
                                    <td><?php echo esc_html( $field_options['seeker_path']['default'][ $option['seeker_path'] ]['label'] ?? '_missing_' ) ?></td>
                                    <td>
                                        <input name="<?php echo esc_html( $option_key ) ?>_days" type="number"
                                               value="<?php echo esc_html( $option['days'] ) ?>"/>
                                    </td>
                                    <td>
                                        <textarea name="<?php echo esc_html( $option_key ) ?>_comment"
                                                  style="width:100%"><?php echo esc_html( $option['comment'] ) ?></textarea>
                                    </td>
                                    <td>
                                        <button class="button small expand_translations"
                                                data-form_name="update_required-form"
                                                data-source="update_needed_triggers">
                                            <img style="height: 15px; vertical-align: middle" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/languages.svg' ); ?>">
                                            (<span><?php echo esc_html( count( $option['translations'] ?? [] ) ); ?></span>)
                                        </button>
                                        <div class="translation_container hide">
                                            <table>
                                                <?php foreach ( $available_languages as $lang => $val ) : ?>
                                                    <tr>
                                                        <td><label
                                                                for="<?php echo esc_html( $option_key ) ?>_translations[<?php echo esc_html( $val['language'] ) ?>]"><?php echo esc_html( $val['native_name'] ) ?></label>
                                                        </td>
                                                        <td><input
                                                                name="<?php echo esc_html( $option_key ) ?>_translations[<?php echo esc_html( $val['language'] ) ?>]"
                                                                type="text"
                                                                value="<?php echo esc_html( $option['translations'][$val['language']] ?? '' ); ?>"/>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                    }
                }
                ?>

            </table>
            <br>
            <span style="float:right;"><button type="submit" class="button float-right"><?php esc_html_e( 'Save', 'disciple_tools' ) ?></button> </span>
        </form>

        <?php
        $update_required_options = $site_options['group_update_required']['options'];
        $field_options = DT_Posts::get_post_field_settings( 'groups' );
        ?>
        <h3><?php esc_html_e( 'Groups', 'disciple_tools' ) ?></h3>
        <form method="post" name="group_update_required-form" style="margin-top: 50px">
            <p><?php esc_html_e( 'Change how long to wait before a group needs an update', 'disciple_tools' ) ?></p>
            <p>
                <?php esc_html_e( 'Update needed triggers enabled', 'disciple_tools' ) ?>
                <input type="checkbox" name="triggers_enabled" <?php echo esc_html( $site_options['group_update_required']['enabled'] ) ? 'checked' : '' ?> />
            </p>

            <input type="hidden" name="group_update_required_nonce" id="group_update_required_nonce" value="' <?php echo esc_attr( wp_create_nonce( 'group_update_required' ) ) ?>'" />

            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Group Status', 'disciple_tools' ) ?></th>
                        <th><?php esc_html_e( 'Days to wait', 'disciple_tools' ) ?></th>
                        <th><?php esc_html_e( 'Comment', 'disciple_tools' ) ?></th>
                        <th><?php esc_html_e( 'Translations', 'disciple_tools' ) ?></th>
                    </tr>
                </thead>
                <?php foreach ( $update_required_options as $option_key => $option ) : ?>
                    <tr>
                        <td><?php echo esc_html( $field_options['group_status']['default'][$option['status']]['label'] ) ?></td>
                        <td>
                            <input name="<?php echo esc_html( $option_key ) ?>_days" type="number"
                                value="<?php echo esc_html( $option['days'] ) ?>"  />
                        </td>
                        <td>
                            <textarea name="<?php echo esc_html( $option_key ) ?>_comment"
                                      style="width:100%"><?php echo esc_html( $option['comment'] ) ?></textarea>
                        </td>
                        <td>
                            <button class="button small expand_translations"
                                    data-form_name="group_update_required-form"
                                    data-source="update_needed_triggers">
                                <img style="height: 15px; vertical-align: middle" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/languages.svg' ); ?>">
                                (<span><?php echo esc_html( count( $option['translations'] ?? [] ) ); ?></span>)
                            </button>
                            <div class="translation_container hide">
                                <table>
                                    <?php foreach ( $available_languages as $lang => $val ) : ?>
                                        <tr>
                                            <td><label
                                                    for="<?php echo esc_html( $option_key ) ?>_translations[<?php echo esc_html( $val['language'] ) ?>]"><?php echo esc_html( $val['native_name'] ) ?></label>
                                            </td>
                                            <td><input
                                                    name="<?php echo esc_html( $option_key ) ?>_translations[<?php echo esc_html( $val['language'] ) ?>]"
                                                    type="text"
                                                    value="<?php echo esc_html( $option['translations'][$val['language']] ?? '' ); ?>"/>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

            </table>
            <br>
            <span style="float:right;"><button type="submit" class="button float-right"><?php esc_html_e( 'Save', 'disciple_tools' ) ?></button> </span>
        </form>
        <?php
    }

    /** Group Preferences */
    public function process_group_preferences(){

        if ( isset( $_POST['group_preferences_nonce'] ) &&
             wp_verify_nonce( sanitize_key( wp_unslash( $_POST['group_preferences_nonce'] ) ), 'group_preferences' . get_current_user_id() ) ) {

            $site_options = dt_get_option( 'dt_site_options' );
            $tile_options = dt_get_option( 'dt_custom_tiles' );
            $four_fields_tile = $tile_options['groups']['four-fields'] ?? [];
            $church_metrics_tile = $tile_options['groups']['health-metrics'] ?? [];

            if ( isset( $_POST['church_metrics'] ) && ! empty( $_POST['church_metrics'] ) ) {
                $site_options['group_preferences']['church_metrics'] = true;
                $church_metrics_tile['hidden'] = false;
            } else {
                $site_options['group_preferences']['church_metrics'] = false;
                $church_metrics_tile['hidden'] = true;
            }
            if ( isset( $_POST['four_fields'] ) && ! empty( $_POST['four_fields'] ) ) {
                $site_options['group_preferences']['four_fields'] = true;
                $four_fields_tile['hidden'] = false;
            } else {
                $site_options['group_preferences']['four_fields'] = false;
                $four_fields_tile['hidden'] = true;
            }

            if ( !empty( $four_fields_tile ) ){
                $tile_options['groups']['four-fields'] = $four_fields_tile;
            }
            if ( !empty( $church_metrics_tile ) ){
                $tile_options['groups']['health-metrics'] = $church_metrics_tile;
            }

            update_option( 'dt_site_options', $site_options, true );
            update_option( 'dt_custom_tiles', $tile_options, true );
        }

    }

    public function update_group_preferences(){
        $group_preferences = dt_get_option( 'group_preferences' );
        ?>
        <form method="post" >
            <table class="widefat">
                <tr>
                    <td>
                        <label>
                            <input type="checkbox" name="church_metrics" <?php echo empty( $group_preferences['church_metrics'] ) ? '' : 'checked' ?> /> Church Metrics
                        </label>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label>
                            <input type="checkbox" name="four_fields" <?php echo empty( $group_preferences['four_fields'] ) ? '' : 'checked' ?> /> Four Fields
                        </label>
                    </td>
                </tr>
                <?php wp_nonce_field( 'group_preferences' . get_current_user_id(), 'group_preferences_nonce' )?>
            </table>
            <br>
            <span style="float:right;"><button type="submit" class="button float-right"><?php esc_html_e( 'Save', 'disciple_tools' ) ?></button> </span>
        </form>
        <?php
    }

    /** User Visibility Preferences */
    public function process_user_preferences(){
        if ( isset( $_POST['user_preferences_nonce'] ) &&
             wp_verify_nonce( sanitize_key( wp_unslash( $_POST['user_preferences_nonce'] ) ), 'user_preferences' . get_current_user_id() ) ) {
            $role_options = get_option( 'dt_options_roles_and_permissions', [] );
            $dt_roles = dt_multi_role_get_editable_role_names();
            foreach ( $dt_roles as $role_key => $name ) :
                $role_object = get_role( $role_key );
                if ( isset( $_POST[$role_key] ) && !array_key_exists( 'dt_list_users', $role_object->capabilities ) ) {
                    $role_options[$role_key]['permissions']['dt_list_users'] = true;
                } else if ( !isset( $_POST[$role_key] ) && array_key_exists( 'dt_list_users', $role_object->capabilities ) ) {
                    if ( isset( $role_options[$role_key]['permissions']['dt_list_users'] ) ){
                        unset( $role_options[$role_key]['permissions']['dt_list_users'] );
                    }
                }
            endforeach;
            update_option( 'dt_options_roles_and_permissions', $role_options );
            dt_setup_roles_and_permissions();

            if ( isset( $_POST['user_invite_check'] ) && $_POST['user_invite_check'] === 'user_invite' ) {
                update_option( 'dt_user_invite_setting', true );
            } else {
                delete_option( 'dt_user_invite_setting' );
            }

            if ( isset( $_POST['user_default_language'] ) && !empty( $_POST['user_default_language'] ) ){
                update_option( 'dt_user_default_language', sanitize_text_field( wp_unslash( $_POST['user_default_language'] ) ) );
            }

            update_option( 'dt_disable_wp_new_user_email_notifications', ( isset( $_POST['disable_wp_new_user_email_notifications'] ) && boolval( wp_unslash( $_POST['disable_wp_new_user_email_notifications'] ) ) ) );
            update_option( 'dt_disable_dt_new_user_email_notifications', ( isset( $_POST['disable_dt_new_user_email_notifications'] ) && boolval( wp_unslash( $_POST['disable_dt_new_user_email_notifications'] ) ) ) );

            if ( isset( $_POST['dt_enable_registration'] ) ) {
                update_option( 'dt_enable_registration', 1, true );
            } else {
                delete_option( 'dt_enable_registration' );
            }
        }
    }

    public function update_user_preferences(){
        $dt_roles = dt_multi_role_get_editable_role_names();
        $user_invite_allowed = get_option( 'dt_user_invite_setting', false );
        $user_default_language = get_option( 'dt_user_default_language', 'en_US' );
        $disable_wp_new_user_email_notifications = get_option( 'dt_disable_wp_new_user_email_notifications', false );
        $disable_dt_new_user_email_notifications = get_option( 'dt_disable_dt_new_user_email_notifications', false );
        $enable_this_site_registration = get_option( 'dt_enable_registration' );
        ?>
        <p><?php esc_html_e( 'User Roles that can view all other Disciple.Tools users names' ) ?></p>
        <form method="post" >
            <table class="widefat">
            <?php foreach ( $dt_roles as $role_key => $name ) : ?>
                <?php
                $role_object = get_role( $role_key );
                ?>
                <?php if ( $role_object && !array_key_exists( 'dt_all_access_contacts', $role_object->capabilities ) && !array_key_exists( 'list_users', $role_object->capabilities ) ) : ?>
                <tr>
                    <td>
                        <label>
                            <input type="checkbox" name="<?php echo esc_attr( $role_key ); ?>" <?php checked( array_key_exists( 'dt_list_users', $role_object->capabilities ) ); ?>/> <?php echo esc_attr( $name ); ?>
                        </label>
                    </td>
                </tr>
                <?php endif; ?>
            <?php endforeach; ?>

                <?php wp_nonce_field( 'user_preferences' . get_current_user_id(), 'user_preferences_nonce' )?>
            </table>
            <br>
            <p>
                <label><?php esc_html_e( 'Allow multipliers to invite other users. New users will have the multiplier role:' ) ?>
                    <input type="checkbox" name="user_invite_check" id="user_invite_check" value="user_invite" <?php echo $user_invite_allowed ? 'checked' : '' ?> />
                </label>
            </p>

            <p>
                <label><?php esc_html_e( 'Default user language:' ) ?>
                    <select id="user_default_language" name="user_default_language">
                        <?php
                        $languages = dt_get_available_languages();
                        foreach ( $languages as $language ){
                            ?>
                            <option
                                value="<?php echo esc_html( $language['language'] ); ?>" <?php selected( $user_default_language === $language['language'] ) ?>>
                                <?php echo esc_html( !empty( $language['flag'] ) ? $language['flag'] . ' ' : '' ); ?><?php echo esc_html( $language['native_name'] ); ?>
                            </option>
                            <?php
                        }
                        ?>
                    </select>
                </label>
            </p>

            <br>
            <p>
                <label><?php esc_html_e( 'Disable initial new user Wordpress email notifications:' ) ?>
                    <input type="checkbox" name="disable_wp_new_user_email_notifications" id="disable_wp_new_user_email_notifications" <?php echo $disable_wp_new_user_email_notifications ? 'checked' : '' ?> />
                </label>
            </p>
            <p>
                <label><?php esc_html_e( 'Disable initial new user D.T. email notifications:' ) ?>
                    <input type="checkbox" name="disable_dt_new_user_email_notifications" id="disable_dt_new_user_email_notifications" <?php echo $disable_dt_new_user_email_notifications ? 'checked' : '' ?> />
                </label>
            </p>

            <?php

            if ( is_multisite() ) :
                $registration = get_site_option( 'registration' );
                if ( 'all' === $registration || 'user' === $registration ) :

                    ?>

                    <table class="widefat">
                        <tr>
                            <td>
                                <label><?php esc_html_e( 'Enable User Registrations:' ) ?>
                                    <input type="checkbox" name="dt_enable_registration" <?php echo checked( $enable_this_site_registration ) ?> /> <br>
                                </label>
                            </td>
                        </tr>
                    </table>
                    <br>

                    <?php

                endif;
            endif;

            ?>

            <span style="float:right;"><button type="submit" class="button float-right"><?php esc_html_e( 'Save', 'disciple_tools' ) ?></button> </span>
        </form>
        <?php
    }

    /** Contact Preferences */
    public function process_dt_contact_preferences(){

        if ( isset( $_POST['dt_contact_preferences_nonce'] ) &&
            wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dt_contact_preferences_nonce'] ) ), 'dt_contact_preferences' . get_current_user_id() ) ) {

            $contact_preferences = get_option( 'dt_contact_preferences' );
            if ( isset( $_POST['hide_personal_contact_type'] ) && ! empty( $_POST['hide_personal_contact_type'] ) ) {
                $contact_preferences['hide_personal_contact_type'] = false;
            } else {
                $contact_preferences['hide_personal_contact_type'] = true;
            }

            update_option( 'dt_contact_preferences', $contact_preferences, true );
        }

    }

    public function show_dt_contact_preferences(){
        $contact_preferences = get_option( 'dt_contact_preferences', [] );
        ?>
        <form method="post" >
            <table class="widefat">
                <tr>
                    <td>
                        <label>
                            <input type="checkbox" name="hide_personal_contact_type" <?php echo empty( $contact_preferences['hide_personal_contact_type'] ) ? 'checked' : '' ?> /> Personal Contact Type Enabled
                        </label>
                    </td>
                </tr>
                <?php wp_nonce_field( 'dt_contact_preferences' . get_current_user_id(), 'dt_contact_preferences_nonce' )?>
            </table>
            <br>
            <span style="float:right;"><button type="submit" class="button float-right"><?php esc_html_e( 'Save', 'disciple_tools' ) ?></button> </span>
        </form>
        <?php
    }

    public function process_dt_performance_mode(){

        if ( isset( $_POST['dt_performance_mode_nonce'] ) &&
            wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dt_performance_mode_nonce'] ) ), 'dt_performance_mode' ) ) {

            $dt_performance_mode = ! empty( $_POST['dt_performance_mode'] );

            update_option( 'dt_performance_mode', $dt_performance_mode, true );
        }

    }

    public function dt_performance_mode(){
        $dt_performance_mode = get_option( 'dt_performance_mode', false );
        ?>
        <form method="post" >
            <table class="widefat">
                <tr>
                    <td>
                        <label>
                            <input type="checkbox" name="dt_performance_mode" <?php echo $dt_performance_mode ? 'checked' : '' ?> /> Performance Mode
                        </label>
                    </td>
                    <td>
                        This is useful for large databases. Performance Mode will disable some features to improve performance:
                        <ul style="list-style: disc; padding: revert">
                            <li>Counts on Contact and Group list filters</li>
                        </ul>
                    </td>
                </tr>
                <?php wp_nonce_field( 'dt_performance_mode', 'dt_performance_mode_nonce' )?>
            </table>
            <br>
            <span style="float:right;"><button type="submit" class="button float-right"><?php esc_html_e( 'Save', 'disciple_tools' ) ?></button> </span>
        </form>
        <?php
    }

    private function display_contact_modules(){
        $modules = dt_get_option( 'dt_post_type_modules' )

        ?>
        <form method="post" >
            <?php wp_nonce_field( 'contact_modules', 'contact_modules_nonce' )?>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Enabled</th>
                        <th>Requires</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ( $modules as $module_key => $module_values ) : ?>
                    <tr>
                        <td><?php echo esc_html( $module_values['name'] ); ?></td>
                        <td>
                            <input type="checkbox"
                                   name="<?php echo esc_html( $module_key ); ?>"
                                   <?php disabled( $module_values['locked'] ?? false ) ?>
                                   <?php checked( $module_values['enabled'] ) ?> />
                        </td>
                        <td>
                            <?php echo esc_html( join( ', ', array_map( function ( $req_key ) use ( $modules ) {
                                return $modules[$req_key]['name'];
                            }, ( $module_values['prerequisites'] ?? [] ) ) ) );
                            ?>
                        </td>
                        <td>
                            <?php echo esc_html( $module_values['description'] ?? '' ); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <br>
            <span style="float:right;"><button type="submit" class="button float-right"><?php esc_html_e( 'Save', 'disciple_tools' ) ?></button> </span>
        </form>
        <?php
    }

    private function process_contact_modules(){
        if ( isset( $_POST['contact_modules_nonce'] ) &&
             wp_verify_nonce( sanitize_key( wp_unslash( $_POST['contact_modules_nonce'] ) ), 'contact_modules' ) ) {

            $module_settings = dt_get_option( 'dt_post_type_modules' );
            $module_option = get_option( 'dt_post_type_modules', [] );
            foreach ( $module_settings as $module_key => $module_values ){
                if ( !isset( $module_option[$module_key] ) ){
                    $module_option[$module_key] = [ 'enabled' => false ];
                }
                $module_option[$module_key]['enabled'] = isset( $_POST[$module_key] ) || ( $module_settings[$module_key]['locked'] ?? false );
                if ( isset( $_POST[$module_key] ) ){
                    foreach ( $module_settings[$module_key]['prerequisites'] ?? [] as $prereq ){
                        if ( !isset( $_POST[$prereq] ) && !( $module_settings[$prereq]['locked'] ?? false ) ){
                            $module_option[$module_key]['enabled'] = false;
                        }
                    }
                }
            }
            update_option( 'dt_post_type_modules', $module_option );

        }
    }

    public function custom_logo() {
        $dt_nav_tabs = dt_default_menu_array();
        $logo_url = esc_url( $dt_nav_tabs['admin']['site']['icon'] );
        $custom_logo_url = get_option( 'custom_logo_url' );

        if ( ! empty( $custom_logo_url ) ) {
            $logo_url = esc_url( $custom_logo_url );
        }
        ?>
        <form method="post" name="custom_logo_box">
            <input type="hidden" name="custom_logo_box_nonce" value="<?php echo esc_attr( wp_create_nonce( 'custom_logo_box' ) ); ?>" />
            <table class="widefat striped">
                <thead>
                    <tr>
                        <td><?php esc_html_e( 'Image', 'disciple_tools' ); ?></td>
                        <td><?php esc_html_e( 'Image link (must be https)', 'disciple_tools' ); ?></td>
                        <td></td>
                        <td></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="background-color:#3f729b"><img height="22px" style="vertical-align:-webkit-baseline-middle;" src="<?php echo esc_html( $logo_url ); ?>"></td>
                        <td><input type="text" name="custom_logo_url" value="<?php echo esc_html( $logo_url ); ?>"></td>
                        <td><button class="button" name="default_logo_url">Default</button></td>
                        <td><button class="button file-upload-display-uploader" data-form="custom_logo_box" data-icon-input="custom_logo_url" style="margin-left:1%"><?php esc_html_e( 'Upload', 'disciple_tools' ); ?></button></td>
                    </tr>
                </tbody>
            </table>
        </form>
        <?php
    }

    public function process_custom_logo() {
        if ( isset( $_POST['custom_logo_box_nonce'] ) ) {
            if ( !wp_verify_nonce( sanitize_key( $_POST['custom_logo_box_nonce'] ), 'custom_logo_box' ) ) {
                self::admin_notice( __( 'Something went wrong', 'disciple_tools' ), 'error' );
                return;
            }

            // Change Custom Logo URL
            if ( isset( $_POST['custom_logo_url'] ) ) {

                $custom_logo_url = esc_url( sanitize_text_field( wp_unslash( $_POST['custom_logo_url'] ) ) );
                update_option( 'custom_logo_url', $custom_logo_url );
            }

            // Revert to Default Logo URL
            if ( isset( $_POST['default_logo_url'] ) ) {
                delete_option( 'custom_logo_url' );
            }
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
}


Disciple_Tools_General_Tab::instance();
