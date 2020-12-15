<?php

/**
 * Disciple Tools
 *
 * @class      Disciple_Tools_
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple_Tools
 * @author     Chasm.Solutions & Kingdom.Training
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

            $modules = dt_get_option( "dt_post_type_modules" );
            $this->template( 'begin', 2 );

            /* Base User */
            $this->box( 'top', 'Base User' );
            $this->process_base_user();
            $this->base_user();
            $this->box( 'bottom' );
            /* End Base User */

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
            if ( isset( $modules["access_module"]["enabled"] ) && $modules["access_module"]["enabled"] ){
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
            $this->box( 'top', 'User Visibility Preferences' );
            $this->process_user_visibility();
            $this->update_user_visibility();
            $this->box( 'bottom' );
            /* User Visibility */


            $this->template( 'right_column' );

            $this->template( 'end' );

        endif;
    }

    /**
     * Prints the user notifications box
     */
    public function user_notifications() {

        $site_options = dt_get_option( 'dt_site_options' );
        $notifications = $site_options['notifications']["types"];

        ?>
        <form method="post" name="notifications-form">
            <button type="submit" class="button-like-link" name="reset_notifications" value="1"><?php esc_html_e( "reset", 'disciple_tools' ) ?></button>
            <p><?php esc_html_e( "These are site overrides for individual preferences for notifications. Uncheck if you want, users to make their own decision on which notifications to receive.", 'disciple_tools' ) ?></p>
            <input type="hidden" name="notifications_nonce" id="notifications_nonce" value="' <?php echo esc_attr( wp_create_nonce( 'notifications' ) ) ?>'" />

            <table class="widefat">
            <?php foreach ( $notifications as $notification_key => $notification_value ) : ?>
                <tr>
                    <td><?php echo esc_html( $notification_value['label'] ) ?></td>
                    <td>
                        <?php esc_html_e( "Web", 'disciple_tools' ) ?>
                        <input name="<?php echo esc_html( $notification_key ) ?>_web" type="checkbox"
                            <?php echo $notifications[ $notification_key ]['web'] ? "checked" : "" ?>  />
                    </td>
                    <td>
                        <?php esc_html_e( "Email", 'disciple_tools' ) ?>
                        <input name="<?php echo esc_html( $notification_key ) ?>_email" type="checkbox"
                            <?php echo $notifications[ $notification_key ]['email'] ? "checked" : "" ?>  />
                    </td>
                </tr>
            <?php endforeach; ?>

            </table>
            <br>
            <span style="float:right;"><button type="submit" class="button float-right"><?php esc_html_e( "Save", 'disciple_tools' ) ?></button> </span>
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
            }

            foreach ( $site_options['notifications']["types"] as $key => $value ) {
                $site_options['notifications']["types"][ $key ]['web'] = isset( $_POST[ $key.'_web' ] );
                $site_options['notifications']["types"][ $key ]['email'] = isset( $_POST[ $key.'_email' ] );
            }

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

    public function email_settings(){
        ?>
        <form method="POST">
            <input type="hidden" name="email_base_subject_nonce" id="email_base_subject_nonce" value="<?php echo esc_attr( wp_create_nonce( 'email_subject' ) )?>" />
            <label for="email_subject"><?php esc_html_e( "Configure the first part of the subject line in email sent by Disciple.Tools", 'disciple_tools' ) ?></label>
            <input name="email_subject" id="email_subject" value="<?php echo esc_html( dt_get_option( "dt_email_base_subject" ) ) ?>" />
            <span style="float:right;"><button type="submit" class="button float-right"><?php esc_html_e( "Update", 'disciple_tools' ) ?></button></span>
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
        }
    }




    public function process_update_required(){
        if ( isset( $_POST['update_required_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['update_required_nonce'] ) ), 'update_required' ) ) {
            $site_options = dt_get_option( "dt_site_options" );

            if ( isset( $_POST["run_update_required"] ) ){
                do_action( "dt_find_contacts_that_need_an_update" );
            }
            $site_options["update_required"]["enabled"] = isset( $_POST["triggers_enabled"] );

            foreach ( $site_options["update_required"]["options"] as $option_index => $option ){
                if ( isset( $_POST[$option_index . "_days"] ) ){
                    $site_options["update_required"]["options"][$option_index]["days"] = sanitize_text_field( wp_unslash( $_POST[$option_index . "_days"] ) );
                }
                if ( isset( $_POST[$option_index . "_comment"] ) ){
                    $site_options["update_required"]["options"][$option_index]["comment"] = wp_unslash( sanitize_text_field( wp_unslash( $_POST[$option_index . "_comment"] ) ) );
                }
            }
            update_option( 'dt_site_options', $site_options, true );
        }

        if ( isset( $_POST['group_update_required_nonce'] ) &&
             wp_verify_nonce( sanitize_key( wp_unslash( $_POST['group_update_required_nonce'] ) ), 'group_update_required' ) ) {
            $site_options = dt_get_option( "dt_site_options" );

            if ( isset( $_POST["run_update_required"] ) ){
                do_action( "dt_find_contacts_that_need_an_update" );
            }
            $site_options["group_update_required"]["enabled"] = isset( $_POST["triggers_enabled"] );

            foreach ( $site_options["group_update_required"]["options"] as $option_index => $option ){
                if ( isset( $_POST[$option_index . "_days"] ) ){
                    $site_options["group_update_required"]["options"][$option_index]["days"] = sanitize_text_field( wp_unslash( $_POST[$option_index . "_days"] ) );
                }
                if ( isset( $_POST[$option_index . "_comment"] ) ){
                    $site_options["group_update_required"]["options"][$option_index]["comment"] = wp_unslash( sanitize_text_field( wp_unslash( $_POST[$option_index . "_comment"] ) ) );
                }
            }
            update_option( 'dt_site_options', $site_options, true );
        }
    }

    public function update_required_options(){
        $site_options = dt_get_option( 'dt_site_options' );
        $update_required_options = $site_options['update_required']["options"];
        $field_options = DT_Posts::get_post_field_settings( "contacts" );
        ?>
        <h3><?php esc_html_e( "Contacts", 'disciple_tools' ) ?></h3>
        <form method="post" name="update_required-form">
            <button type="submit" class="button-like-link" name="run_update_required" value="1"><?php esc_html_e( "Run checker now", 'disciple_tools' ) ?></button>
            <p><?php esc_html_e( "Change how long to wait before a contact needs an update", 'disciple_tools' ) ?></p>
            <p>
                <?php esc_html_e( "Update needed triggers enabled", 'disciple_tools' ) ?>
                <input type="checkbox" name="triggers_enabled" <?php echo esc_html( $site_options['update_required']["enabled"] ) ? 'checked' : '' ?> />
            </p>

            <input type="hidden" name="update_required_nonce" id="update_required_nonce" value="' <?php echo esc_attr( wp_create_nonce( 'update_required' ) ) ?>'" />

            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php esc_html_e( "Status", 'disciple_tools' ) ?></th>
                        <th><?php esc_html_e( "Seeker Path", 'disciple_tools' ) ?></th>
                        <th><?php esc_html_e( "Days to wait", 'disciple_tools' ) ?></th>
                        <th><?php esc_html_e( "Comment", 'disciple_tools' ) ?></th>
                    </tr>
                </thead>
                <?php foreach ( $update_required_options as $option_key => $option ) : ?>
                    <tr>
                        <td><?php echo esc_html( $field_options["overall_status"]['default'][$option['status']]["label"] ?? '' ) ?></td>
                        <td><?php echo esc_html( $field_options["seeker_path"]['default'][$option['seeker_path']]["label"] ?? '_missing_' ) ?></td>
                        <td>
                            <input name="<?php echo esc_html( $option_key ) ?>_days" type="number"
                                value="<?php echo esc_html( $option["days"] ) ?>"  />
                        </td>
                        <td>
                            <textarea name="<?php echo esc_html( $option_key ) ?>_comment"
                                      style="width:100%"><?php echo esc_html( $option["comment"] ) ?></textarea>
                        </td>
                    </tr>
                <?php endforeach; ?>

            </table>
            <br>
            <span style="float:right;"><button type="submit" class="button float-right"><?php esc_html_e( "Save", 'disciple_tools' ) ?></button> </span>
        </form>

        <?php
        $update_required_options = $site_options['group_update_required']["options"];
        $field_options = DT_Posts::get_post_field_settings( "groups" );
        ?>
        <h3><?php esc_html_e( "Groups", 'disciple_tools' ) ?></h3>
        <form method="post" name="group_update_required-form" style="margin-top: 50px">
            <p><?php esc_html_e( "Change how long to wait before a group needs an update", 'disciple_tools' ) ?></p>
            <p>
                <?php esc_html_e( "Update needed triggers enabled", 'disciple_tools' ) ?>
                <input type="checkbox" name="triggers_enabled" <?php echo esc_html( $site_options['group_update_required']["enabled"] ) ? 'checked' : '' ?> />
            </p>

            <input type="hidden" name="group_update_required_nonce" id="group_update_required_nonce" value="' <?php echo esc_attr( wp_create_nonce( 'group_update_required' ) ) ?>'" />

            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php esc_html_e( "Group Status", 'disciple_tools' ) ?></th>
                        <th><?php esc_html_e( "Days to wait", 'disciple_tools' ) ?></th>
                        <th><?php esc_html_e( "Comment", 'disciple_tools' ) ?></th>
                    </tr>
                </thead>
                <?php foreach ( $update_required_options as $option_key => $option ) : ?>
                    <tr>
                        <td><?php echo esc_html( $field_options["group_status"]['default'][$option['status']]["label"] ) ?></td>
                        <td>
                            <input name="<?php echo esc_html( $option_key ) ?>_days" type="number"
                                value="<?php echo esc_html( $option["days"] ) ?>"  />
                        </td>
                        <td>
                            <textarea name="<?php echo esc_html( $option_key ) ?>_comment"
                                      style="width:100%"><?php echo esc_html( $option["comment"] ) ?></textarea>
                        </td>
                    </tr>
                <?php endforeach; ?>

            </table>
            <br>
            <span style="float:right;"><button type="submit" class="button float-right"><?php esc_html_e( "Save", 'disciple_tools' ) ?></button> </span>
        </form>
        <?php
    }

    /** Group Preferences */
    public function process_group_preferences(){

        if ( isset( $_POST['group_preferences_nonce'] ) &&
             wp_verify_nonce( sanitize_key( wp_unslash( $_POST['group_preferences_nonce'] ) ), 'group_preferences' . get_current_user_id() ) ) {

            $site_options = dt_get_option( "dt_site_options" );
            if ( isset( $_POST['church_metrics'] ) && ! empty( $_POST['church_metrics'] ) ) {
                $site_options["group_preferences"]["church_metrics"] = true;
            } else {
                $site_options["group_preferences"]["church_metrics"] = false;
            }
            if ( isset( $_POST['four_fields'] ) && ! empty( $_POST['four_fields'] ) ) {
                $site_options["group_preferences"]["four_fields"] = true;
            } else {
                $site_options["group_preferences"]["four_fields"] = false;
            }

            update_option( 'dt_site_options', $site_options, true );
        }

    }

    public function update_group_preferences(){
        $group_preferences = dt_get_option( 'group_preferences' );
        ?>
        <form method="post" >
            <table class="widefat">
                <tr>
                    <td>
                        <input type="checkbox" name="church_metrics" <?php echo empty( $group_preferences['church_metrics'] ) ? '' : 'checked' ?> /> Church Metrics
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" name="four_fields" <?php echo empty( $group_preferences['four_fields'] ) ? '' : 'checked' ?> /> Four Fields
                    </td>
                </tr>
                <?php wp_nonce_field( 'group_preferences' . get_current_user_id(), 'group_preferences_nonce' )?>
            </table>
            <br>
            <span style="float:right;"><button type="submit" class="button float-right"><?php esc_html_e( "Save", 'disciple_tools' ) ?></button> </span>
        </form>
        <?php
    }

    /** User Visibility Preferences */
    public function process_user_visibility(){
        if ( isset( $_POST['user_visibility_nonce'] ) &&
             wp_verify_nonce( sanitize_key( wp_unslash( $_POST['user_visibility_nonce'] ) ), 'user_visibility' . get_current_user_id() ) ) {

            $dt_roles = dt_multi_role_get_editable_role_names();
            foreach ( $dt_roles as $role_key => $name ) :
                $role_object = get_role( $role_key );
                if ( isset( $_POST[$role_key] ) && !array_key_exists( 'dt_list_users', $role_object->capabilities ) ) {
                    $role_object->add_cap( 'dt_list_users' );
                } else if ( !isset( $_POST[$role_key] ) && array_key_exists( 'dt_list_users', $role_object->capabilities ) ) {
                    $role_object->remove_cap( 'dt_list_users' );
                }
            endforeach;
        }

    }

    public function update_user_visibility(){
        $dt_roles = dt_multi_role_get_editable_role_names();
        ?>
        <p><?php esc_html_e( "User Roles that can view all other Disciple Tools users names" ) ?></p>
        <form method="post" >
            <table class="widefat">
            <?php foreach ( $dt_roles as $role_key => $name ) : ?>
                <?php
                $role_object = get_role( $role_key );
                ?>
                <?php if ( $role_object && !array_key_exists( 'dt_all_access_contacts', $role_object->capabilities ) ) : ?>
                <tr>
                    <td>
                        <input type="checkbox" name="<?php echo esc_attr( $role_key ); ?>" <?php checked( array_key_exists( 'dt_list_users', $role_object->capabilities ) ); ?>/> <?php echo esc_attr( $name ); ?>
                    </td>
                </tr>
                <?php endif; ?>
            <?php endforeach; ?>

                <?php wp_nonce_field( 'user_visibility' . get_current_user_id(), 'user_visibility_nonce' )?>
            </table>
            <br>
            <span style="float:right;"><button type="submit" class="button float-right"><?php esc_html_e( "Save", 'disciple_tools' ) ?></button> </span>
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
                            <?php echo esc_html( join( ", ", array_map( function ( $req_key ) use ( $modules ){
                                return $modules[$req_key]["name"];
                            }, ( $module_values["prerequisites"] ?? [] ) ) ) );
                            ?>
                        </td>
                        <td>
                            <?php echo esc_html( $module_values["description"] ?? "" ); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <br>
            <span style="float:right;"><button type="submit" class="button float-right"><?php esc_html_e( "Save", 'disciple_tools' ) ?></button> </span>
        </form>
        <?php
    }

    private function process_contact_modules(){
        if ( isset( $_POST['contact_modules_nonce'] ) &&
             wp_verify_nonce( sanitize_key( wp_unslash( $_POST['contact_modules_nonce'] ) ), 'contact_modules' ) ) {

            $module_settings = dt_get_option( "dt_post_type_modules" );
            $module_option = get_option( "dt_post_type_modules", [] );
            foreach ( $module_settings as $module_key => $module_values ){
                if ( !isset( $module_option[$module_key] ) ){
                    $module_option[$module_key] = [ "enabled" => false ];
                }
                $module_option[$module_key]["enabled"] = isset( $_POST[$module_key] ) || ( $module_settings[$module_key]["locked"] ?? false );
                if ( isset( $_POST[$module_key] ) ){
                    foreach ( $module_settings[$module_key]["prerequisites"] ?? [] as $prereq ){
                        if ( !isset( $_POST[$prereq] ) && !( $module_settings[$prereq]["locked"] ?? false ) ){
                            $module_option[$module_key]["enabled"] = false;
                        }
                    }
                }
            }
            update_option( "dt_post_type_modules", $module_option );

        }
    }

}



Disciple_Tools_General_Tab::instance();
