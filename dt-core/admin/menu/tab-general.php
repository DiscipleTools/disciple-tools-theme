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
class Disciple_Tools_General_Tab
{
    /**
     * Packages and prints tab page
     */
    public function content()
    {
        echo '<div class="wrap"><div id="poststuff"><div id="post-body" class="metabox-holder columns-2">';
        echo '<div id="post-body-content">';
        /* Main Column */


        /* Box */
        echo '<table class="widefat striped">
                    <thead><th>Base User</th></thead>
                    <tbody><tr><td>';

        $this->process_base_user();
        $this->base_user();

        echo '</td></tr></tbody></table><br>';
        /* End Box */


        /* Box */
        echo '<table class="widefat striped">
                    <thead><th>Site Notifications</th></thead>
                    <tbody><tr><td>';

        $this->process_user_notifications();
        $this->user_notifications(); // prints content for the notifications box

        echo '</td></tr></tbody></table><br>';
        /* End Box */

        /* Box */
        echo '<table class="widefat striped">
                    <thead><th>Reports Settings</th></thead>
                    <tbody><tr><td>';

        $this->process_reports();
        $this->reports(); // prints

        echo '</td></tr></tbody></table><br>';
        /* End Box */

        /* Box */
        echo '<table class="widefat striped">
                    <thead><th>Extended Modules</th></thead>
                    <tbody><tr><td>';

        $this->process_extension_modules(); // prints
        $this->extension_modules(); // prints

        echo '</td></tr></tbody></table>';
        /* End Box */

        /* End Main Column */
        echo '</div><!-- end post-body-content --><div id="postbox-container-1" class="postbox-container">';
        /* Right Column */

        /* Box */
        echo '<table class="widefat striped">
                    <thead><th>Instructions</th></thead>
                    <tbody><tr><td>';

        echo '</td></tr></tbody></table>';
        /* End Box */

        /* End Right Column*/
        echo '</div><!-- postbox-container 1 --><div id="postbox-container-2" class="postbox-container">';
        echo '</div><!-- postbox-container 2 --></div><!-- post-body meta box container --></div><!--poststuff end --></div><!-- wrap end -->';
    }

    /**
     * Prints the user notifications box
     */
    public function user_notifications()
    {

        $site_options = dt_get_option( 'dt_site_options' );
        $notifications = $site_options['user_notifications'];

        echo '<form method="post" name="notifications-form">';
        echo '<button type="submit" class="button-like-link" name="reset_notifications" value="1">reset</button>';
        echo '<p>These are site overrides for individual preferences for notifications. Uncheck if you want, users to make their own decision on which notifications to recieve.</p>';
        echo '<input type="hidden" name="notifications_nonce" id="notifications_nonce" value="' . esc_attr( wp_create_nonce( 'notifications' ) ) . '" />';

        echo '<table class="widefat">';

        echo '<tr><td>New Contacts</td><td>Web <input name="new_web" type="checkbox" ' . ( $notifications['new_web'] ? "checked" : "" ) . ' /></td><td>Email <input name="new_email" type="checkbox" ' . ( $notifications['new_email'] ? "checked" : "" ) . ' /></td></tr>';
        echo '<tr><td>@Mentions</td><td>Web <input name="mentions_web" type="checkbox" ' . ( $notifications['mentions_web'] ? "checked" : "" ) . ' /></td><td>Email <input name="mentions_email" type="checkbox" ' . ( $notifications['mentions_email'] ? "checked" : "" ) . ' /></td></tr>';
        echo '<tr><td>Updates Required</td><td>Web <input name="updates_web" type="checkbox" ' . ( $notifications['updates_web'] ? "checked" : "" ) . ' /></td><td>Email <input name="updates_email" type="checkbox" ' . ( $notifications['updates_email'] ? "checked" : "" ) . ' /></td></tr>';
        echo '<tr><td>Contact Info Changes</td><td>Web <input name="changes_web" type="checkbox" ' . ( $notifications['changes_web'] ? "checked" : "" ) . ' /></td><td>Email <input name="changes_email" type="checkbox" ' . ( $notifications['changes_email'] ? "checked" : "" ) . ' /></td></tr>';
        echo '<tr><td>Contact Milestones</td><td>Web <input name="milestones_web" type="checkbox" ' . ( $notifications['milestones_web'] ? "checked" : "" ) . ' /></td><td>Email <input name="milestones_email" type="checkbox" ' . ( $notifications['milestones_email'] ? "checked" : "" ) . ' /></td></tr>';

        echo '</table><br><span style="float:right;"><button type="submit" class="button float-right">Save</button> </span></form>';
    }

    /**
     * Process user notifications box
     */
    public function process_user_notifications()
    {

        if ( isset( $_POST['notifications_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['notifications_nonce'] ) ), 'notifications' ) ) {

            $site_options = dt_get_option( 'dt_site_options' );

            if ( isset( $_POST['reset_notifications'] ) ) {
                unset( $site_options['user_notifications'] );
                $site_option_defaults = dt_get_site_options_defaults();
                $site_options['user_notifications'] = $site_option_defaults['user_notifications'];
            }

            foreach ( $site_options['user_notifications'] as $key => $value ) {
                if ( isset( $_POST[ $key ] ) ) {
                    $site_options['user_notifications'][ $key ] = true;
                } else {
                    $site_options['user_notifications'][ $key ] = false;
                }
            }



            update_option( 'dt_site_options', $site_options, true );
        }
    }

    /**
     * Print reports selection box
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
     * Process reports selections from reports box
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
                if ( isset( $_POST[ $key ] ) ) {
                    $site_options['daily_reports'][ $key ] = true;
                } else {
                    $site_options['daily_reports'][ $key ] = false;
                }
            }

            update_option( 'dt_site_options', $site_options, true );
        }
    }

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
                if ( isset( $_POST[ $key ] ) ) {
                    $site_options['extension_modules'][ $key ] = true;
                } else {
                    $site_options['extension_modules'][ $key ] = false;
                }
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



}
