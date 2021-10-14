<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

class DT_User_Hooks_And_Configuration {

    public function __construct() {
        add_action( "dt_contact_merged", [ $this, "dt_contact_merged" ], 10, 2 );

        //Make sure a contact exists for users
        add_action( "wpmu_new_blog", [ &$this, "create_contacts_for_existing_users" ] );
        add_action( "after_switch_theme", [ &$this, "create_contacts_for_existing_users" ] );
        add_action( "invite_user", [ $this, "user_register_hook" ], 10, 1 );
        add_action( "after_signup_user", [ $this, "after_signup_user" ], 10, 2 );
        add_action( 'wp_login', [ &$this, 'user_login_hook' ], 10, 2 );
        add_action( 'add_user_to_blog', [ &$this, 'user_register_hook' ] );
        add_action( 'user_register', [ &$this, 'user_register_hook' ] ); // used on non multisite?
        add_action( 'profile_update', [ &$this, 'profile_update_hook' ], 99 );
        add_action( 'signup_user_meta', [ $this, 'signup_user_meta' ], 10, 1 );
        add_action( 'wpmu_activate_user', [ $this, 'wpmu_activate_user' ], 10, 3 );

        //invite user and edit user page modifications
        add_action( "user_new_form", [ &$this, "custom_user_profile_fields" ] );
        add_action( "show_user_profile", [ &$this, "custom_user_profile_fields" ] );
        add_action( "edit_user_profile", [ &$this, "custom_user_profile_fields" ] );
        add_action( "add_user_role", [ $this, "add_user_role" ], 10, 2 );

        //wp admin user list customization
        add_filter( 'user_row_actions', [ $this, 'dt_edit_user_row_actions' ], 10, 2 );
        add_filter( 'manage_users_columns', [ $this, 'new_modify_user_table' ] );
        add_filter( 'manage_users_custom_column', [ $this, 'new_modify_user_table_row' ], 10, 3 );

        add_action( 'remove_user_from_blog', [ $this,'dt_delete_user_contact_meta' ], 10, 1 );
        add_action( 'wpmu_delete_user', [ $this,'dt_multisite_delete_user_contact_meta' ], 10, 1 );
        add_action( 'delete_user', [ $this,'dt_delete_user_contact_meta' ], 10, 1 );

        // translate emails
        add_filter( 'wp_new_user_notification_email', [ $this, 'wp_new_user_notification_email' ], 10, 3 );
        add_action( 'add_user_to_blog', [ $this, 'wp_existing_user_notification_email' ], 10, 3 );
    }




    /**
     * When a new user is invited to a multisite and the "corresponds_to_contact" field is filled out
     * save the username to the contact to be linked to the user when they activate their account.
     *
     * @param $user_name
     * @param $user_email
     */
    public function after_signup_user( $user_name, $user_email ){
        if ( isset( $_REQUEST['action'] ) && 'createuser' == $_REQUEST['action'] ) {
            check_admin_referer( 'create-user', '_wpnonce_create-user' );
        }
        if ( isset( $_REQUEST['action'] ) && 'adduser' == $_REQUEST['action'] ) {
            check_admin_referer( 'add-user', '_wpnonce_add-user' );
        }
        if ( isset( $_POST["corresponds_to_contact_id"] ) && !empty( $_POST["corresponds_to_contact_id"] ) ) {
            $corresponds_to_contact = sanitize_text_field( wp_unslash( $_POST["corresponds_to_contact_id"] ) );
            update_post_meta( $corresponds_to_contact, 'corresponds_to_user_name', $user_name );
        }
    }

    /*
     * Multisite only
     * Save who added the user so that their contact record can later be shared.
     */
    public function signup_user_meta( $meta ){
        $current_user_id = get_current_user_id();
        if ( $current_user_id ){
            $meta["invited_by"] = $current_user_id;
        }
        return $meta;
    }

    /**
     * Multisite only
     * Share the newly added user-contact with the admin who added the user.
     * @param $user_id
     * @param $password
     * @param $meta
     */
    public function wpmu_activate_user( $user_id, $password, $meta ){
        if ( isset( $meta["invited_by"] ) && !empty( $meta["invited_by"] ) ){
            $contact_id = get_user_option( "corresponds_to_contact", $user_id );
            if ( $contact_id && !is_wp_error( $contact_id ) ){
                DT_Posts::add_shared( "contacts", $contact_id, $meta["invited_by"], null, false, false );
            }
        }
    }

    /**
     * User register hook
     * Check to see if the user is linked to a contact.
     *
     *  When adding an existing multisite to the D.T instance.
     *  Link the user with the existing contact or create a contact for the user.
     *
     * @param $user_id
     */
    public static function user_register_hook( $user_id ) {
        if ( isset( $_REQUEST['action'] ) && 'createuser' == $_REQUEST['action'] ) {
            check_admin_referer( 'create-user', '_wpnonce_create-user' );
        }
        if ( isset( $_REQUEST['action'] ) && 'adduser' == $_REQUEST['action'] ) {
            check_admin_referer( 'add-user', '_wpnonce_add-user' );
        }
        if ( dt_is_rest() ){
            $data = json_decode( WP_REST_Server::get_raw_data(), true );

            if ( isset( $data["locale"] ) && !empty( $data["locale"] ) ){
                $locale = $data["locale"];
                switch_to_locale( $locale );
                $user = get_user_by( 'id', $user_id );
                $user->locale = $locale;
                wp_update_user( $user );
            }
            if ( isset( $data["corresponds_to_contact"] ) ){
                $corresponds_to_contact = $data["corresponds_to_contact"];
                update_user_option( $user_id, "corresponds_to_contact", $corresponds_to_contact );
                $contact = DT_Posts::update_post( "contacts", (int) $corresponds_to_contact, [
                    "corresponds_to_user" => $user_id,
                    "type" => "user"
                ]);
                $user = get_user_by( 'id', $user_id );
                $user->display_name = $contact["title"];
                wp_update_user( $user );
            }
        }
        if ( isset( $_POST["corresponds_to_contact_id"] ) && !empty( $_POST["corresponds_to_contact_id"] ) ) {
            $corresponds_to_contact = sanitize_text_field( wp_unslash( $_POST["corresponds_to_contact_id"] ) );
            update_user_option( $user_id, "corresponds_to_contact", $corresponds_to_contact );
            $contact = DT_Posts::update_post( "contacts", (int) $corresponds_to_contact, [
                "corresponds_to_user" => $user_id,
                "type" => "user"
            ], false, true );
            $user = get_user_by( 'id', $user_id );
            $user->display_name = $contact["title"];
            wp_update_user( $user );
        }
        $corresponds_to_contact = get_user_option( "corresponds_to_contact", $user_id );
        if ( empty( $corresponds_to_contact ) ){
            Disciple_Tools_Users::create_contact_for_user( $user_id );
        }
        if ( isset( $_POST["dt_locale"] ) ) {
            $userdata = get_user_by( 'id', $user_id );

            if ( isset( $_POST["dt_locale"] ) ) {
                if ( $_POST["dt_locale"] === "" ) {
                    $locale = "en_US";
                } else {
                    $locale = sanitize_text_field( wp_unslash( $_POST["dt_locale"] ) );
                }
                $userdata->locale = sanitize_text_field( wp_unslash( $locale ) );
            }

            wp_update_user( $userdata );
        }
    }

    /**
     * Makes sure a user is linked to a contact when logging in.
     * @param $user_name
     * @param $user
     */
    public static function user_login_hook( $user_name, $user ){
        $corresponds_to_contact = get_user_option( "corresponds_to_contact", $user->ID );
        if ( empty( $corresponds_to_contact ) && is_user_member_of_blog( $user->ID ) ){
            Disciple_Tools_Users::create_contact_for_user( $user->ID );
        }
    }

    /**
     * Profile update hook
     *
     * @param $user_id
     */
    public static function profile_update_hook( $user_id ) {
        Disciple_Tools_Users::create_contact_for_user( $user_id );

        if ( isset( $_POST["corresponds_to_contact_id"] ) && !empty( $_POST["corresponds_to_contact_id"] ) ){
            $corresponds_to_contact = sanitize_text_field( wp_unslash( $_POST["corresponds_to_contact_id"] ) );
            update_user_option( $user_id, "corresponds_to_contact", $corresponds_to_contact );
            DT_Posts::update_post( "contacts", (int) $corresponds_to_contact, [
                "corresponds_to_user" => $user_id
            ], true, false );
        }

        if ( !empty( $_POST["allowed_sources"] ) ) {
            if ( isset( $_REQUEST['action'] ) && 'update' == $_REQUEST['action'] ) {
                check_admin_referer( 'update-user_' . $user_id );
            }
            $allowed_sources = [];
            foreach ( $_POST["allowed_sources"] as $s ) {  // @codingStandardsIgnoreLine
                $allowed_sources[] = sanitize_key( wp_unslash( $s ) );
            }
            if ( in_array( "restrict_all_sources", $allowed_sources ) ){
                $allowed_sources = [ "restrict_all_sources" ];
            }
            update_user_option( $user_id, "allowed_sources", $allowed_sources );
        }
    }

    public static function add_user_role( $user_id, $role ){
        if ( user_can( $user_id, "access_specific_sources" ) ){
            $allowed_sources = get_user_option( "allowed_sources", $user_id ) ?: [];
            if ( in_array( "restrict_all_sources", $allowed_sources ) || empty( $allowed_sources ) ){
                $allowed_sources = [ "restrict_all_sources" ];
                update_user_option( $user_id, "allowed_sources", $allowed_sources );
            }
        }
    }

    public static function create_contacts_for_existing_users(){
        if ( is_user_logged_in() ){
            $users = get_users();
            foreach ( $users as $user ){
                Disciple_Tools_Users::create_contact_for_user( $user->ID );
            }
        }
    }

    public function dt_contact_merged( $master_id, $non_master_id ){
        //check to make sure both contacts don't point to a user
        $corresponds_to_user = get_post_meta( $master_id, "corresponds_to_user", true );
        if ( $corresponds_to_user ){
            $contact_id = get_user_option( "corresponds_to_contact", $corresponds_to_user );
            //make sure the user points to the right contact
            if ( $contact_id != $master_id ){
                update_user_option( $corresponds_to_user, "corresponds_to_contact", $master_id );
            }
            $dup_corresponds_to_contact = get_post_meta( $non_master_id, "corresponds_to_user", true );
            if ( $dup_corresponds_to_contact ){
                delete_post_meta( $non_master_id, "corresponds_to_user" );
            }
            //update the user display name to the new contact name
            $user = get_user_by( 'id', $corresponds_to_user );
            $user->display_name = get_the_title( $master_id );
            wp_update_user( $user );
        }


        //make sure only one contact keeps the user type
        $master_contact_type = get_post_meta( $master_id, "type", true );
        $non_master_contact_type = get_post_meta( $non_master_id, "type", true );
        if ( $master_contact_type === "user" || $non_master_contact_type === "user" ){
            //keep both records as type "user"
            update_post_meta( $master_id, "type", "user" );
            update_post_meta( $non_master_id, "type", "user" );
        }
    }

    /**
     * Translate email using theme translation domain
     */
    public static function wp_new_user_notification_email( $wp_new_user_notification_email, $user, $blogname ){

        dt_switch_locale_for_notifications( $user->ID );

        /* Copied in from https://developer.wordpress.org/reference/functions/wp_new_user_notification/ line 2086ish */
        $key = get_password_reset_key( $user );
        if ( is_wp_error( $key ) ) {
            return $key;
        }

        $subject = __( '[%s] Login Details', 'disciple_tools' );

        $display_name = $user->user_login;
        if ( dt_is_rest() ){
            $data = json_decode( WP_REST_Server::get_raw_data(), true );
            if ( isset( $data["user-display"] ) && !empty( $data["user-display"] ) ){
                $display_name = sanitize_text_field( wp_unslash( $data["user-display"] ) );
            }
        }

        $message = self::common_user_invite_text( $user->user_login, $blogname, home_url(), $display_name );
        $message .= __( 'To set your password, visit the following address:', 'disciple_tools' ) . "\r\n\r\n";
        $message .= home_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' ) . "\r\n\r\n";

        $message .= wp_login_url() . "\r\n";

        $wp_new_user_notification_email['subject'] = $subject;
        $wp_new_user_notification_email['message'] = $message;

        return $wp_new_user_notification_email;
    }

    /**
     * Send email to an existing user being added to a different site on a multisite
     */
    public static function wp_existing_user_notification_email( $user_id, $role, $site_id ){

        dt_switch_locale_for_notifications( $user_id );

        $user = get_user_by( 'ID', $user_id );
        $site = get_blog_details( $site_id );


        $message = self::common_user_invite_text( $user->user_email, $site->blogname, $site->siteurl, $user->display_name );
        $message .= sprintf( __( 'To log in click here: %s', 'disciple_tools' ), wp_login_url() )  . "\r\n";

        $dt_existing_user_notification_email = [
            'to' => $user->user_email,
            'subject' => __( '[%s] Login Details', 'disciple_tools' ),
            'message' => $message,
            'headers' => '',
        ];

        /**
         * Filters the contents of the existing user notification email sent to the new user.
         *
         * @since 1.10.0
         *
         * @param array   $dt_existing_user_notification_email {
         *     Used to build wp_mail().
         *
         *     @type string $to      The intended recipient - New user email address.
         *     @type string $subject The subject of the email.
         *     @type string $message The body of the email.
         *     @type string $headers The headers of the email.
         * }
         * @param WP_User $user     User object for new user.
         * @param string  $blogname The site title.
         */
        $dt_existing_user_notification_email = apply_filters( 'dt_existing_user_notification_email', $dt_existing_user_notification_email, $user, $site->blogname );

        //allow an email to not be sent. Example: demo content users
        $continue = apply_filters( "dt_sent_email_check", true, $dt_existing_user_notification_email['to'], sprintf( $dt_existing_user_notification_email['subject'], $site->blogname ), $dt_existing_user_notification_email['message'] );
        if ( !$continue ){
            return false;
        }
        return wp_mail(
            $dt_existing_user_notification_email['to'],
            /* translators: Login details notification email subject. %s: Site title. */
            sprintf( $dt_existing_user_notification_email['subject'], $site->blogname ),
            $dt_existing_user_notification_email['message'],
            $dt_existing_user_notification_email['headers']
        );
    }

    public static function common_user_invite_text( $username, $sitename, $url, $display_name = null ) {
        $message = sprintf( __( 'Hi %s,', 'disciple_tools' ), $display_name ?? $username ) . "\r\n\r\n";
        $message .= sprintf( _x( 'You\'ve been invited to join %1$s at %2$s', 'You\'ve been invited to join Awesome Site at https://awesome_site.disciple.tools', 'disciple_tools' ), $sitename, $url ) . "\r\n\r\n";
        $message .= sprintf( __( 'Username: %s', 'disciple_tools' ), $username ) . "\r\n\r\n";

        return $message;
    }


    /**
     * Modifies the wp-admin users list table to add a link to the users's contact
     *
     * @param $actions
     * @param $user
     *
     * @return mixed
     */
    public function dt_edit_user_row_actions( $actions, $user ){
        $contact_id = Disciple_Tools_Users::get_contact_for_user( $user->ID );
        $link = get_permalink( $contact_id );
        if ( $contact_id && $link ){
            $actions["view"] = '<a href="' . $link . '" aria-label="View contact">' . esc_html( __( "View contact record", 'disciple_tools' ) ) . '</a>';
        } else {
            unset( $actions["view"] );
        }
        return $actions;
    }

    /**
     * Modifies the wp-admin users list table to add the display name column
     *
     * @param $column
     *
     * @return array
     */
    public function new_modify_user_table( $column ) {
        return array_slice( $column, 0, 3, true ) +
            array( "display_name" => "Display Name" ) +
            array_slice( $column, 3, null, true );
    }

    public function new_modify_user_table_row( $val, $column_name, $user_id ) {
        switch ( $column_name ) {
            case 'display_name' :
                return dt_get_user_display_name( $user_id );
            default:
                break;
        }
        return $val;
    }


    /** Multisite Only
     *  This will remove the 'corresponds_to_user' meta key and value from all sites on the network if deleted by a super admin
     */
    public static function dt_multisite_delete_user_contact_meta( $user_id ) {
        $blogs = get_sites();
        if ( ! empty( $blogs ) ) {
            foreach ( $blogs as $blog ) {
                switch_to_blog( $blog->userblog_id );
                global $wpdb;
                $wpdb->get_results(
                    $wpdb->prepare( "DELETE FROM $wpdb->postmeta pm WHERE meta_key = 'corresponds_to_user' AND pm.meta_value = %d
                    ", $user_id )
                );

                restore_current_blog();
            }
        }
    }

    /**
     * Modifies the add user wp-admin page to add the 'corresponds to contact' field.
     *
     * @param $user
     */
    public function custom_user_profile_fields( $user ){
        if ( ! current_user_can( 'access_contacts' ) ) {
            return;
        }

        $contact_id = "";
        $contact_title = "";
        if ( $user != "add-new-user" && $user != "add-existing-user" && isset( $user->ID ) ) {
            $contact_id   = get_user_option( "corresponds_to_contact", $user->ID );
            if ( $contact_id ){
                $contact = get_post( $contact_id );
                if ( $contact ){
                    $contact_title = $contact->post_title;
                }
            }
        }
        if ( empty( $contact_title ) ) : ?>
            <script type="application/javascript">
                jQuery(document).ready(function($) {
                    //removes the Wordpress Language selector that only shows the Wordpress languages and not the Disciple tools languages.
                    jQuery(".form-field.user-language-wrap").remove();

                    jQuery(".corresponds_to_contact").each(function () {
                        jQuery(this).autocomplete({
                            source: function (request, response) {
                                jQuery.ajax({
                                    url: '<?php echo esc_html( rest_url() ) ?>dt-posts/v2/contacts/compact',
                                    data: {
                                        s: request.term
                                    },
                                    beforeSend: function (xhr) {
                                        xhr.setRequestHeader('X-WP-Nonce',
                                            "<?php echo esc_html( wp_create_nonce( 'wp_rest' ) ) ?>");
                                    }
                                }).then(data => {
                                    response(data.posts);
                                })
                            },
                            minLength: 2,
                            select: function (event, ui) {
                                $(".corresponds_to_contact").val(ui.item.name);
                                $(".corresponds_to_contact_id").val(ui.item.ID);
                                return false;
                            }
                        }).autocomplete("instance")._renderItem = function (ul, item) {
                            return $("<li>")
                            .append(`<div>${item.name} (${item.ID})</div>`)
                            .appendTo(ul);
                        };
                    })
                });
            </script>
        <?php endif; ?>
        <h3><?php esc_html_e( "Extra Disciple.Tools Information", 'disciple_tools' ) ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="contact"><?php esc_html_e( "Corresponds to Contact", 'disciple_tools' ) ?></label></th>
                <td>
                    <?php if ( !empty( $contact_title ) ) : ?>
                        <a href="<?php echo esc_html( get_permalink( $contact_id ) ) ?>"><?php echo esc_html( $contact_title ) ?></a>
                    <?php else : ?>
                        <input type="text" class="regular-text corresponds_to_contact" name="corresponds_to_contact" value="<?php echo esc_html( $contact_title )?>" /><br />
                        <input type="hidden" class="regular-text corresponds_to_contact_id" name="corresponds_to_contact_id" value="<?php echo esc_html( $contact_id )?>" />
                        <?php if ( $contact_id ) : ?>
                            <span class="description"><a href="<?php echo esc_html( get_site_url() . '/contacts/' . $contact_id )?>" target="_blank"><?php esc_html_e( "View Contact", 'disciple_tools' ) ?></a></span>
                        <?php else : ?>
                            <span class="description"><?php esc_html_e( "Add the name of the contact record this user corresponds to.", 'disciple_tools' ) ?>
                                <a target="_blank" href="https://disciple.tools/user-docs/getting-started-info/users/inviting-users/"><?php esc_html_e( "Learn more.", "disciple_tools" ) ?></a>
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><label for="dt_locale"><?php esc_html_e( "User Language", 'disciple_tools' ) ?></label></th>
                <td>
                    <?php
                    dt_language_select()
                    ?>
                </td>
            </tr>
        </table>
        <?php if ( isset( $user->ID ) && user_can( $user->ID, 'access_specific_sources' ) ) :
            $selected_sources = get_user_option( 'allowed_sources', $user->ID );
            $post_settings = DT_Posts::get_post_settings( "contacts" );
            $sources = isset( $post_settings["fields"]["sources"]["default"] ) ? $post_settings["fields"]["sources"]["default"] : [];
            ?>
            <h3>Access by Source</h3>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e( "Sources", 'disciple_tools' ) ?></th>
                    <td>
                        <ul>
                            <li>
                                <?php $checked = in_array( 'all', $selected_sources === false ? [ 'all' ] : $selected_sources ) ? "checked" : ''; ?>
                                <label>
                                    <input type="radio" name="allowed_sources[]" value="all" <?php echo esc_html( $checked ) ?>/>
                                    <?php esc_html_e( 'All Sources - gives access to all contacts', 'disciple_tools' ); ?>
                                </label>
                            </li>
                            <li>
                                <?php $checked = in_array( 'custom_source_restrict', $selected_sources === false ? [] : $selected_sources ) ? "checked" : ''; ?>
                                <label>
                                    <input type="radio" name="allowed_sources[]" value="custom_source_restrict" <?php echo esc_html( $checked ) ?>/>
                                    <?php esc_html_e( 'Custom - Access own contacts and all the contacts of the selected sources below', 'disciple_tools' ); ?>
                                </label>
                            </li>
                            <li>
                                <?php $checked = in_array( 'restrict_all_sources', $selected_sources === false ? [] : $selected_sources ) ? "checked" : ''; ?>
                                <label>
                                    <input type="radio" name="allowed_sources[]" value="restrict_all_sources" <?php echo esc_html( $checked ) ?>/>
                                    <?php esc_html_e( 'No Sources - only own contacts', 'disciple_tools' ); ?>
                                </label>
                            </li>
                            <li>
                                &nbsp;
                            </li>
                            <?php foreach ( $sources as $source_key => $source_value ) :
                                $checked = in_array( $source_key, $selected_sources === false ? [] : $selected_sources ) ? "checked" : '';
                                ?>
                                <li>
                                    <label>
                                        <input type="checkbox" name="allowed_sources[]" value="<?php echo esc_html( $source_key ) ?>" <?php echo esc_html( $checked ) ?>/>
                                        <?php echo esc_html( $source_value["label"] ) ?>
                                    </label>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                </tr>
            </table>

        <?php endif;
    }
}
