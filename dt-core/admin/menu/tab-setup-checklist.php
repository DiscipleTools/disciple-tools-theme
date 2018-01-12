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
 * Class Disciple_Tools_Import_Export_Tab
 */
class Disciple_Tools_Setup_Steps_Tab
{
    /**
     * Packages and returns tab page
     *
     * @return void
     */
    public function content()
    {
        $this->process_checklist();
        $checklist = get_option( 'dt_setup_checklist' );
        ?>
        <style>
            .float-right {
                float: right;
            }

            .green-check {
                color: white;
                font-weight: 400;
                background-color: green;
                border-radius: 5px;
            }
        </style>
        <form method="post">
            <input type="hidden" name="checklist_nonce" id="checklist_nonce"
                   value="<?php echo esc_attr( wp_create_nonce( 'checklist' ) ); ?>"/>
            <div class="wrap">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-2">
                        <div id="post-body-content">
                            <table class="widefat">
                                <thead>
                                <th><strong>STEP 1 - CONFIGURE WORDPRESS</strong></th>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>

                                        <!-- box -->
                                        <table class="widefat striped">
                                            <thead>
                                            <th>Install SSL</th>
                                            <th width="20px">Installed</th>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>Really Simple SSL <span class="float-right"><a
                                                            href="https://wordpress.org/plugins/really-simple-ssl/"
                                                            target="_blank">view plugin website</a></span></td>
                                                <td width="20px">
                                                    <button type="submit" class="button-like-link" name="input[ssl]"
                                                            value="really_simple"><span
                                                            class="dashicons dashicons-yes <?php $this->selected( 'ssl', 'really_simple', $checklist ); ?>"></span>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Other</td>
                                                <td width="20px">
                                                    <button type="submit" class="button-like-link" name="input[ssl]"
                                                            value="other"><span
                                                            class="dashicons dashicons-yes <?php $this->selected( 'ssl', 'other', $checklist ); ?>"></span>
                                                    </button>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <br>
                                        <!-- End box-->

                                        <!-- box -->
                                        <table class="widefat striped">
                                            <thead>
                                            <th>Install & Configure Wordpress Security Plugin</th>
                                            <td width="20px">Installed</td>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>WordFence Security
                                                    <span class="float-right">
                                                        <a href="https://wordpress.org/plugins/wordfence/" target="_blank">view plugin website</a>
                                                    </span>
                                                </td>
                                                <td width="20px">
                                                    <button type="submit" class="button-like-link"
                                                            name="input[security]" value="word_fence"><span
                                                            class="dashicons dashicons-yes <?php $this->selected( 'security', 'word_fence', $checklist ); ?>"></span></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>iThemes Security
                                                    <span class="float-right">
                                                        <a href="https://wordpress.org/plugins/better-wp-security/" target="_blank">view plugin website</a>
                                                    </span>
                                                </td>
                                                <td width="20px">
                                                    <button type="submit" class="button-like-link"
                                                            name="input[security]" value="ithemes"><span
                                                            class="dashicons dashicons-yes <?php $this->selected( 'security', 'ithemes', $checklist ); ?>"></span></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Other</td>
                                                <td width="20px">
                                                    <button type="submit" class="button-like-link"
                                                            name="input[security]" value="other"><span
                                                            class="dashicons dashicons-yes <?php $this->selected( 'security', 'other', $checklist ); ?>"></span></button>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <br>
                                        <!-- End box-->

                                        <!-- box -->
                                        <table class="widefat striped">
                                            <thead>
                                            <th>Install & Configure Backup Service</th>
                                            <th width="20px">Installed</th>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>BackupGuard - Wordpress Backup and Migrate Plugin <span
                                                        class="float-right"><a
                                                            href="https://wordpress.org/plugins/backup/"
                                                            target="_blank">view plugin website</a></span></td>
                                                <td width="20px">
                                                    <button type="submit" class="button-like-link"
                                                            name="input[backup]" value="backupguard"><span
                                                            class="dashicons dashicons-yes <?php $this->selected( 'backup', 'backupguard', $checklist ); ?>"></span></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>UpdraftPlus Wordpress Backup Plugin <span class="float-right"><a
                                                            href="https://wordpress.org/plugins/updraftplus/"
                                                            target="_blank">view plugin website</a></span></td>
                                                <td width="20px">
                                                    <button type="submit" class="button-like-link"
                                                            name="input[backup]" value="updraftplus"><span
                                                            class="dashicons dashicons-yes <?php $this->selected( 'backup', 'updraftplus', $checklist ); ?>"></span></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Other</td>
                                                <td width="20px">
                                                    <button type="submit" class="button-like-link"
                                                            name="input[backup]" value="other"><span
                                                            class="dashicons dashicons-yes <?php $this->selected( 'backup', 'other', $checklist ); ?>"></span></button>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <br>
                                        <!-- End box-->

                                        <!-- box -->
                                        <table class="widefat striped">
                                            <thead>
                                            <th>Install & Configure Email Service</th>
                                            <th width="20px">Installed</th>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>Gmail-SMTP <span class="float-right"><a
                                                            href="https://wordpress.org/plugins/gmail-smtp/"
                                                            target="_blank">view plugin website</a></span></td>
                                                <td width="20px">
                                                    <button type="submit" class="button-like-link"
                                                            name="input[email]" value="gmail_smtp"><span
                                                            class="dashicons dashicons-yes <?php $this->selected( 'email', 'gmail_smtp', $checklist ); ?>"></span></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Wordpress Easy SMTP <span class="float-right"><a
                                                            href="https://wordpress.org/plugins/wp-easy-smtp/"
                                                            target="_blank">view plugin website</a></span></td>
                                                <td width="20px">
                                                    <button type="submit" class="button-like-link"
                                                            name="input[email]" value="easy_smtp"><span
                                                            class="dashicons dashicons-yes <?php $this->selected( 'email', 'easy_smtp', $checklist ); ?>"></span></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Other</td>
                                                <td width="20px">
                                                    <button type="submit" class="button-like-link"
                                                            name="input[email]" value="other"><span
                                                            class="dashicons dashicons-yes <?php $this->selected( 'email', 'other', $checklist ); ?>"></span></button>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <br>
                                        <!-- End box-->


                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <br>
                            <table class="widefat ">
                                <thead>
                                <th><strong>STEP 2 - CONFIGURE DISCIPLE TOOLS</strong></th>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>
                                        <!-- box -->
                                        <table class="widefat striped">
                                            <thead>
                                            <th>Configure User Requirements</th>
                                            <th width="20px">Configured</th>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>Decide if workers can register themselves, or be allowed to register
                                                    on the login screen. <a href="#">more</a></td>
                                                <td width="20px">
                                                    <button type="submit" class="button-like-link"
                                                            name="input[decide]" value="workers"><span
                                                            class="dashicons dashicons-yes <?php $this->selected( 'decide', 'workers', $checklist ); ?>"></span></button>
                                                </td>
                                            </tr>

                                            </tbody>
                                        </table>
                                        <br>
                                        <!-- End box-->

                                        <!-- box -->
                                        <table class="widefat striped">
                                            <thead>
                                            <th>Configure Preferences</th>
                                            <th>Configured</th>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>General Preferences</td>
                                                <td width="20px">
                                                    <button type="submit" class="button-like-link"
                                                            name="input[general]" value="general"><span
                                                            class="dashicons dashicons-yes <?php $this->selected( 'general', 'general', $checklist ); ?>"></span></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Custom Lists</td>
                                                <td width="20px">
                                                    <button type="submit" class="button-like-link"
                                                            name="input[lists]" value="custom"><span
                                                            class="dashicons dashicons-yes <?php $this->selected( 'lists', 'custom', $checklist ); ?>"></span></button>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <br>
                                        <!-- End box-->

                                        <!-- box -->
                                        <table class="widefat striped">
                                            <thead>
                                            <th>Configure Reporting</th>
                                            <th>Configured</th>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>Google Analytics</td>
                                                <td width="20px">
                                                    <button type="submit" class="button-like-link"
                                                            name="input[reporting_analytics]" value="reporting_analytics"><span
                                                            class="dashicons dashicons-yes <?php $this->selected( 'reporting_analytics', 'reporting_analytics', $checklist ); ?>"></span></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Facebook Pages</td>
                                                <td width="20px">
                                                    <button type="submit" class="button-like-link"
                                                            name="input[reporting_facebook]" value="reporting_facebook"><span
                                                            class="dashicons dashicons-yes <?php $this->selected( 'reporting_facebook', 'reporting_facebook', $checklist ); ?>"></span></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Mailchimp</td>
                                                <td width="20px">
                                                    <button type="submit" class="button-like-link"
                                                            name="input[reporting_mailchimp]" value="reporting_mailchimp"><span
                                                            class="dashicons dashicons-yes <?php $this->selected( 'reporting_mailchimp', 'reporting_mailchimp', $checklist ); ?>"></span></button>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <br>
                                        <!-- End box-->

                                        <!-- box -->
                                        <table class="widefat striped">
                                            <thead>
                                            <th>Import Data</th>
                                            <th>Installed</th>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>Locations</td>
                                                <td width="20px">
                                                    <button type="submit" class="button-like-link"
                                                            name="input[locations]" value="locations"><span
                                                            class="dashicons dashicons-yes <?php $this->selected( 'locations', 'locations', $checklist ); ?>"></span></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>People Groups</td>
                                                <td width="20px">
                                                    <button type="submit" class="button-like-link"
                                                            name="input[people_groups]" value="people_groups"><span
                                                            class="dashicons dashicons-yes <?php $this->selected( 'people_groups', 'people_groups', $checklist ); ?>"></span></button>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <br>
                                        <!-- End box-->

                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <br>
                            <table class="widefat ">
                                <thead>
                                <th>Step 3 (Extend Additional Features)</th>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>



                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <br>
                        </div><!-- end post-body-content -->
                        <div id="postbox-container-1" class="postbox-container">
                            <?php self::progress_bar(); ?>
                        </div><!-- postbox-container 1 -->
                        <div id="postbox-container-2" class="postbox-container">
                        </div><!-- postbox-container 2 -->
                    </div><!-- post-body meta box container -->
                </div>
                <!--poststuff end -->
            </div><!-- wrap end -->
        </form>
        <?php
    }

    /**
     * @param null $checklist
     */
    protected function process_checklist( $checklist = null )
    {
        if ( isset( $_POST['checklist_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['checklist_nonce'] ) ), 'checklist' ) ) {

            // get option
            if ( is_null( $checklist ) ) {
                $checklist = get_option( 'dt_setup_checklist' );
            }

            if ( isset( $_POST['input'] ) ) {

                $post = array_map( 'sanitize_text_field', wp_unslash( $_POST['input'] ) );

                foreach ( $post as $key => $value ) {
                    if ( isset( $checklist[ $key ] ) && $checklist[ $key ] == $value ) {
                        unset( $checklist[ $key ] );
                    } else {
                        $checklist[ $key ] = $value;
                    }
                }

                update_option( 'dt_setup_checklist', $checklist, false );
            }
        }
    }

    /**
     * Checks to see if element is checked
     *
     * @param string $group
     * @param string $item
     * @param        $checklist
     *
     * @return bool
     */
    protected function selected( string $group, string $item, $checklist = null )
    {
        if ( is_null( $checklist ) ) {
            $checklist = get_option( 'dt_setup_checklist' );
        }

        if ( isset( $checklist[ $group ] ) && $checklist[ $group ] == $item ) {
            echo 'green-check';

            return true;
        } else {
            return false;
        }
    }

    public static function progress_bar() {
        $group_total = 12; // Manually set the total number of unique groups
        $checklist = get_option( 'dt_setup_checklist' ); // get stored option with current groups data
        $count = count( $checklist ); // count the stored option
        $percentage = $count / $group_total * 100; // get percentage
        $percentage = (int) $percentage;
        ?>
        <table class="widefat striped">
            <thead>
            <th>Setup Checklist Progress</th>
            </thead>
            <tbody>
            <tr>
                <td>
                    <div class="container">
                        <?php
                        if ($percentage > 5 ) { echo '<div class="complete green"> '. esc_attr( $percentage ) . '% &nbsp;</div>';}
                        else { echo '<div class="begin">Get Started!</div>'; }
                        ?>

                    </div>
                    <style type="text/css">
                        .container {
                            width: 100%; /* Full width */
                            background-color: #ddd; /* Grey background */
                        }

                        .complete {
                            text-align: right; /* Right-align text */
                            line-height: 40px; /* Set the line-height to center the text inside the skill bar, and to expand the height of the container */
                            color: white; /* White text color */
                        }

                        .green {
                            width: <?php echo esc_attr( $percentage ) . '%'; ?>;
                            background-color: #4CAF50;
                        }

                        .begin {
                            text-align: center; /* Right-align text */
                            line-height: 40px; /* Set the line-height to center the text inside the skill bar, and to expand the height of the container */
                            color: black; /* White text color */
                            width: 100%;
                            background-color: #ddd;
                        }
                    </style>

                </td>
            </tr>
            <?php
            if ( isset( $_GET["tab"] ) && !($_GET["tab"] == 'setup-checklist') ) { ?>
                <tr><td><a href="<?php echo esc_url( admin_url( 'admin.php?page=dt_options&tab=setup-checklist' ) ) ?>">View Setup Checklist</a></td></tr>
            <?php } ?>
            </tbody>
        </table>
        <br>
        <?php
    }

}
