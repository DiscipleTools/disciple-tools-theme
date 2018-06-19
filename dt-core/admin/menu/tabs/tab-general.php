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
    public static function instance()
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct()
    {
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

    public function content( $tab )
    {
        if ( 'general' == $tab ) :

            $this->template( 'begin' );

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

        /* Site Notifications */
            $this->box( 'top', 'Site Notifications' );
            $this->process_user_notifications();
            $this->user_notifications(); // prints content for the notifications box
            $this->box( 'bottom' );
            /* Site Notifications */

        /* Update Required */
            $this->box( 'top', 'Update Needed Triggers' );
            $this->process_update_required();
            $this->update_required_options();
            $this->box( 'bottom' );
            /* Site Notifications */

        /* Metrics
            if ( dt_metrics_visibility( 'tab' ) ) : // @todo remove after development
                $this->box( 'top', 'Metrics' );
                $this->metrics(); // prints content for the notifications box
                $this->box( 'bottom' );
            endif;
         /* End Metrics */

            $this->template( 'right_column' );

            $this->template( 'end' );

        endif;
    }

    /**
     * Prints the user notifications box
     */
    public function user_notifications()
    {

        $site_options = dt_get_option( 'dt_site_options' );
        $notifications = $site_options['notifications'];

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
    public function process_user_notifications()
    {

        if ( isset( $_POST['notifications_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['notifications_nonce'] ) ), 'notifications' ) ) {

            $site_options = dt_get_option( 'dt_site_options' );

            if ( isset( $_POST['reset_notifications'] ) ) {
                unset( $site_options['notifications'] );
                $site_option_defaults = dt_get_site_options_defaults();
                $site_options['notifications'] = $site_option_defaults['notifications'];
            }

            foreach ( $site_options['notifications'] as $key => $value ) {
                $site_options['notifications'][ $key ]['web'] = isset( $_POST[ $key.'_web' ] );
                $site_options['notifications'][ $key ]['email'] = isset( $_POST[ $key.'_email' ] );
            }

            update_option( 'dt_site_options', $site_options, true );
        }
    }



    /**
     * Print extension module box for options page // @todo in progress
     */
    public function metrics()
    {

//        $site_options = dt_get_option( 'dt_site_options' ); // @todo create new default section for dt_get_option()
        $roles = dt_multi_role_get_roles();
        if ( isset( $roles['administrator'] ) ) {
            unset( $roles['administrator'] );
        }
//        dt_write_log( $roles );

        if ( isset( $_POST['metrics_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['metrics_nonce'] ) ), 'metrics' . get_current_user_id() ) ) {

            dt_write_log( $_POST ); // @todo add saving logic

        }


        ?>
        <form method="post" name="extension_modules_form">

            <button type="submit" class="button-like-link" name="reset_extension_modules" value="1"><?php echo esc_html__( 'reset' ) ?></button>

            <p><?php esc_html_e( 'Configure which groups see metrics' ) ?></p>

            <input type="hidden" name="extension_modules_nonce" id="extension_modules_nonce" value="<?php echo esc_attr( wp_create_nonce( 'metrics' . get_current_user_id() ) ) ?>" />

            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php echo esc_html( 'Role' ) ?></th>
                        <th><?php echo esc_html( 'Hide Personal' ) ?></th>
                        <th><?php echo esc_html( 'Hide Project Basic' ) ?></th>
                        <th><?php echo esc_html( 'Hide Project Advanced' ) ?></th>
                        <th><?php echo esc_html( 'Hide Extensions' ) ?></th>
                    </tr>
                </thead>

                <?php foreach ( $roles as $role ) : ?>
                <tr>
                    <td><?php echo esc_html( $role->name ) ?></td>
                    <td><input name="<?php echo esc_attr( $role->slug ) ?>-personal" type="checkbox" <?php echo ( false ? "checked" : "" ) ?> /></td>
                    <td><input name="<?php echo esc_attr( $role->slug ) ?>-project-basic" type="checkbox" <?php echo ( false ? "checked" : "" ) ?> /></td>
                    <td><input name="<?php echo esc_attr( $role->slug ) ?>-project-advanced" type="checkbox" <?php echo ( false ? "checked" : "" ) ?> /></td>
                    <td><input name="<?php echo esc_attr( $role->slug ) ?>-extensions" type="checkbox" <?php echo ( false ? "checked" : "" ) ?> /></td>
                </tr>
                <?php endforeach; ?>
            </table>

            <br>
            <span style="float:right;"><button type="submit" class="button float-right">Save</button></span>
        </form>
        <?php
    }



    /**
     * Set base user assigns the catch-all user
     */
    public function base_user() {
        $base_user = dt_get_base_user();
        $potential_user_list = get_users(
            [
                'role__in' => [ 'dispatcher', 'administrator', 'multiplier', 'marketer', 'strategist' ],
                'order'    => 'ASC',
                'orderby'  => 'display_name',
            ]
        );

        echo '<form method="post" name="extension_modules_form">';
        echo '<p>Base User is the catch-all account for orphaned contacts and other records to be assigned to. To be a base user, the user must be an administrator, dispatcher, multiplier, marketer, or strategist.</p>';
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
            <label for="email_subject"><?php esc_html_e( "Configure the first part of the subject line in email sent by DT", 'disciple_tools' ) ?></label>
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
                    $site_options["update_required"]["options"][$option_index]["days"] = sanitize_text_field( $_POST[$option_index . "_days"] );
                }
                if ( isset( $_POST[$option_index . "_comment"] ) ){
                    $site_options["update_required"]["options"][$option_index]["comment"] = wp_unslash( sanitize_text_field( $_POST[$option_index . "_comment"] ) );
                }
            }
            update_option( 'dt_site_options', $site_options, true );
        }

    }

    public function update_required_options(){
        $site_options = dt_get_option( 'dt_site_options' );
        $update_required_options = $site_options['update_required']["options"];
        $field_options = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings( false )
        ?>
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
                        <td><?php echo esc_html( $field_options["overall_status"]['default'][$option['status']] ) ?></td>
                        <td><?php echo esc_html( $field_options["seeker_path"]['default'][$option['seeker_path']] ) ?></td>
                        <td>
                            <input name="<?php echo esc_html( $option_key ) ?>_days" type="number"
                                value="<?php echo esc_html( $option["days"] ) ?>"  />
                        </td>
                        <td>
                            <input name="<?php echo esc_html( $option_key ) ?>_comment" type="text" value="<?php echo esc_html( $option["comment"] ) ?>">
                        </td>
                    </tr>
                <?php endforeach; ?>

            </table>
            <br>
            <span style="float:right;"><button type="submit" class="button float-right"><?php esc_html_e( "Save", 'disciple_tools' ) ?></button> </span>
        </form>
        <?php
    }








    // @todo evaluate below and possibly remove

    /**
     * Print extension module box for options page
     */
    public function extension_modules()
    {

        $site_options = dt_get_option( 'dt_site_options' );
        $extension_modules = $site_options['extension_modules'];

        echo '<form method="post" name="extension_modules_form">';
        echo '<button type="submit" class="button-like-link" name="reset_extension_modules" value="1">reset</button>';
        echo '<p>These are optional modules available in the system.</p>';
        echo '<input type="hidden" name="extension_modules_nonce" id="extension_modules_nonce" value="' . esc_attr( wp_create_nonce( 'extension_modules' ) ) . '" />';

        echo '<table class="widefat">';

        echo '<tr><td>Add People Groups Module <span style="color:darkred;float:right;">(planned for future)</span></td><td><input name="add_people_groups" type="checkbox" ' . ( $extension_modules['add_people_groups'] ? "checked" : "" ) . ' /></td></tr>';
        echo '<tr><td>Add Asset Mapping <span style="color:darkred;float:right;">(planned for future)</span></td><td><input name="add_assetmapping" type="checkbox" ' . ( $extension_modules['add_assetmapping'] ? "checked" : "" ) . ' /></td></tr>';
        echo '<tr><td>Add Prayer <span style="color:darkred;float:right;">(planned for future)</span></td><td><input name="add_prayer" type="checkbox" ' . ( $extension_modules['add_prayer'] ? "checked" : "" ) . ' /></td></tr>';
        echo '<tr><td>Add Workers Section <span style="color:darkred;float:right;">(planned for future)</span> </td><td><input name="add_worker" type="checkbox" ' . ( $extension_modules['add_worker'] ? "checked" : "" ) . ' /></td></tr>';

        echo '</table><br><span style="float:right;"><button type="submit" class="button float-right">Save</button> </span></form>';
    }

    /**
     * Print reports selection box @todo remove?
     */
    public function reports()
    {

        $site_options = dt_get_option( 'dt_site_options' );
        $daily_reports = $site_options['daily_reports'];

        echo '<form method="post" name="daily_reports_form">';
        echo '<button type="submit" class="button-like-link" name="reset_reports" value="1">reset</button>';
        echo '<p>These are regular services that run to check and build reports on integrations and system status.</p>';
        echo '<input type="hidden" name="daily_reports_nonce" id="daily_reports_nonce" value="' . esc_attr( wp_create_nonce( 'daily_reports' ) ) . '" />';

        echo '<table class="widefat">';

        echo '<tr><td>Build Report for Contacts</td><td><input name="build_report_for_contacts" type="checkbox" ' . ( $daily_reports['build_report_for_contacts'] ? "checked" : "" ) . ' /></td></tr>';
        echo '<tr><td>Build Report for Groups</td><td><input name="build_report_for_groups" type="checkbox" ' . ( $daily_reports['build_report_for_groups'] ? "checked" : "" ) . ' /></td></tr>';
        echo '<tr><td>Build Report for Facebook</td><td><input name="build_report_for_facebook" type="checkbox" ' . ( $daily_reports['build_report_for_facebook'] ? "checked" : "" ) . ' /></td></tr>';
        echo '<tr><td>Build Report for Twitter</td><td><input name="build_report_for_twitter" type="checkbox" ' . ( $daily_reports['build_report_for_twitter'] ? "checked" : "" ) . ' /></td></tr>';
        echo '<tr><td>Build Report for Analytics</td><td><input name="build_report_for_analytics" type="checkbox" ' . ( $daily_reports['build_report_for_analytics'] ? "checked" : "" ) . ' /></td></tr>';
        echo '<tr><td>Build Report for Adwords</td><td><input name="build_report_for_adwords" type="checkbox" ' . ( $daily_reports['build_report_for_adwords'] ? "checked" : "" ) . ' /></td></tr>';
        echo '<tr><td>Build Report for Mailchimp</td><td><input name="build_report_for_mailchimp" type="checkbox" ' . ( $daily_reports['build_report_for_mailchimp'] ? "checked" : "" ) . ' /></td></tr>';
        echo '<tr><td>Build Report for Youtube</td><td><input name="build_report_for_youtube" type="checkbox" ' . ( $daily_reports['build_report_for_youtube'] ? "checked" : "" ) . ' /></td></tr>';

        echo '</table><br><span style="float:right;"><button type="submit" class="button float-right">Save</button></span>  </form>';
    }

    /**
     * Process reports selections from reports box @todo remove?
     */
    public function process_reports()
    {

        if ( isset( $_POST['daily_reports_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['daily_reports_nonce'] ) ), 'daily_reports' ) ) {

            $site_options = dt_get_option( 'dt_site_options' );

            if ( isset( $_POST['reset_reports'] ) ) {
                unset( $site_options['daily_reports'] );
                $site_option_defaults = dt_get_site_options_defaults();
                $site_options['daily_reports'] = $site_option_defaults['daily_reports'];
            }

            foreach ( $site_options['daily_reports'] as $key => $value ) {
                $site_options['daily_reports'][ $key ] = isset( $_POST[ $key ] );
            }

            update_option( 'dt_site_options', $site_options, true );
        }
    }

    /**
     * Process extension module
     */
    public function process_extension_modules()
    {
        if ( isset( $_POST['extension_modules_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['extension_modules_nonce'] ) ), 'extension_modules' ) ) {

            $site_options = dt_get_option( 'dt_site_options' );

            if ( isset( $_POST['reset_extension_modules'] ) ) {
                unset( $site_options['extension_modules'] );
                $site_option_defaults = dt_get_site_options_defaults();
                $site_options['extension_modules'] = $site_option_defaults['extension_modules'];
            }

            foreach ( $site_options['extension_modules'] as $key => $value ) {
                $site_options['extension_modules'][ $key ] = isset( $_POST[ $key ] );
            }

            update_option( 'dt_site_options', $site_options, true );
        }
    }
}



Disciple_Tools_General_Tab::instance();
