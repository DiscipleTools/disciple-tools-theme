<?php
/**
 * Module for transferring contacts between DT sites
 */

class Disciple_Tools_Contacts_Transfer
{

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Disciple_Tools_Contacts_Transfer constructor.
     */
    public function __construct() {
        add_action( 'dt_share_panel', [ $this, 'share_panel' ], 10, 1 );
        add_filter( 'site_link_type', [ $this, 'site_link_type' ], 10, 1 );
        add_filter( 'site_link_type_capabilities', [ $this, 'site_link_capabilities' ], 10, 1 );
        add_action( 'dt_contact_detail_notification', [ $this, 'contact_transfer_notification' ] );
    }

    // Adds the type of connection to the site link system
    public function site_link_type( $type ) {
        $type['contact_sharing'] = __( 'Contact Transfer Both Ways', 'disciple_tools' );
        $type['contact_sending'] = __( 'Contact Transfer Sending Only', 'disciple_tools' );
        $type['contact_receiving'] = __( 'Contact Transfer Receiving Only', 'disciple_tools' );
        return $type;
    }

    // Add the specific capabilities needed for the site to site linking.
    public function site_link_capabilities( $args ) {
        if ( 'contact_sharing' === $args['connection_type'] ) {
            $args['capabilities'][] = 'create_contacts';
        }
        if ( 'contact_receiving' === $args['connection_type'] ) {
            $args['capabilities'][] = 'create_contacts';
        }

        return $args;
    }

    public function contact_transfer_notification( $contact ) {
        if ( isset( $contact['reason_closed']['key'] ) && $contact['reason_closed']['key'] === 'transfer' ) {
            ?>
            <section class="cell small-12">
                <div class="bordered-box detail-notification-box" style="background-color:#3F729B">
                    <h4><img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/alert-circle-exc.svg' ) ?>"/><?php esc_html_e( 'This contact has been transferred', 'disciple_tools' ) ?>.</h4>
                    <p><?php esc_html_e( 'This contact has been transferred to', 'disciple_tools' )?>: <?php echo isset( $contact['transfer_site_link_post_id'] ) ? esc_html( get_the_title( $contact['transfer_site_link_post_id'] ) ) : ''; ?></p>
                </div>
            </section>
            <?php
        }
    }

    /**
     * Rest Endpoints
     * @see /dt-contacts/contacts-endpoints.php
     */

    /**
     * Section to display in the share panel for the transfer function
     *
     * @param $post_type
     */
    public function share_panel( $post ) {
        if ( empty( $post ) ) {
            global $post;
        }

        if ( isset( $post->post_type ) && 'contacts' === $post->post_type && current_user_can( 'view_any_contacts' ) ) {
            $list = Site_Link_System::get_list_of_sites_by_type( [ 'contact_sharing', 'contact_sending' ] );
            if ( empty( $list ) ) {
                return;
            }

            $foreign_key_exists = get_post_meta( $post->ID, 'transfer_foreign_key' );
            $transfer_site_link_post_id = get_post_meta( $post->ID, 'transfer_site_link_post_id', true );
            if ( $transfer_site_link_post_id ) {
                $site_title = get_the_title( $transfer_site_link_post_id );
            } else {
                $site_title = __( 'another site' );
            }

            ?>
            <hr size="1px">
            <div class="grid-x">

                <?php if ( $foreign_key_exists ) : ?>
                <div class="cell" id="transfer-warning">

                    <h6><?php echo sprintf( esc_html__( 'Already transfered to %s' ), esc_html( $site_title ) ) ?></h6>
                    <p><?php esc_html_e( 'NOTE: You have already transferred this contact. Transferring again might create duplicates. Do you still want to override this warning and continue with your transfer?', 'disciple_tools' ) ?></p>
                    <p><button type="button" onclick="jQuery('#transfer-form').show();jQuery('#transfer-warning').hide();" class="button"><?php esc_html_e( 'Override and Continue', "disciple_tools" ) ?></button></p>
                </div>
                <?php endif; ?>

                <div class="cell" id="transfer-form" <?php if ( $foreign_key_exists ) { echo 'style="display:none;"'; }?>>
                    <h6><?php esc_html_e( 'Transfer this contact to:', "disciple_tools" ) ?></h6>
                    <select name="transfer_contact" id="transfer_contact" onchange="jQuery('#transfer_button_div').show();">
                        <option value=""></option>
                        <?php
                        foreach ( $list as $site ) {
                            echo '<option value="'.esc_attr( $site['id'] ).'">'.esc_html( $site['name'] ).'</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="cell" id="transfer_button_div" style="display:none;">
                    <button id="transfer_confirm_button" class="button" type="button"><?php esc_html_e( 'Confirm Transfer', 'disciple_tools' ) ?></button> <span id="transfer_spinner"></span>
                </div>
            </div>

            <?php
        }
    }

    public function get_available_transfer_sites() {
        return Site_Link_System::get_list_of_sites_by_type( [ 'contact_sharing', 'contact_sending' ] );
    }

    public static function get_activity_log_for_id( $id ) {
        global $wpdb;
        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->dt_activity_log WHERE object_id = %s", $id ), ARRAY_A );
        return $results;
    }

    /**
     * Contact sending function
     *
     * @param $contact_id
     * @param $site_post_id
     *
     * @return WP_Error|bool
     */
    public static function contact_transfer( $contact_id, $site_post_id ) {
        $errors = new WP_Error();

        /**************************************************************************************************************
         * Transfer current contact
         ***************************************************************************************************************/

        $site = Site_Link_System::get_site_connection_vars( $site_post_id );
        if ( is_wp_error( $site ) ) {
            $errors->add( __METHOD__, 'Error creating site connection details.' );
            return $errors;
        }

        $post_data = get_post( $contact_id, ARRAY_A );
        $postmeta_data = get_post_meta( $contact_id );
        $contact = Disciple_Tools_Contacts::get_contact( $contact_id );

        $args = [
            'method' => 'POST',
            'body' => [
                'transfer_token' => $site['transfer_token'],
                'contact_data' => [
                    'post' => $post_data,
                    'postmeta' => $postmeta_data,
                    'comments' => dt_get_comments_with_redacted_user_data( $contact_id ),
                    'locations' => $contact['locations'], // @todo remove or rewrite? Because of location_grid upgrade.
                    'people_groups' => $contact['people_groups'],
                    'transfer_foreign_key' => $contact['transfer_foreign_key'] ?? 0,
                ],
            ]
        ];

        $result = wp_remote_post( 'https://' . $site['url'] . '/wp-json/dt-public/v1/contact/transfer', $args );
        if ( is_wp_error( $result ) ){
            return $result;
        }
        $result_body = json_decode( $result['body'] );

        if ( ! ( isset( $result_body->status ) && 'OK' === $result_body->status ) ) {
            $errors->add( 'transfer', $result_body->error ?? __( 'Unknown error.' ) );
            return $errors;
        }

        if ( ! empty( $result_body->error ) ) {
            foreach ( $result_body->error->errors as $key => $value ) {
                $time_in_mysql_format = current_time( 'mysql' );
                wp_insert_comment([
                    'comment_post_ID' => $contact_id,
                    'comment_content' => __( 'Minor transfer error.', 'disciple_tools' ) . ' ' . $key,
                    'comment_type' => '',
                    'comment_parent' => 0,
                    'user_id' => get_current_user_id(),
                    'comment_date' => $time_in_mysql_format,
                    'comment_approved' => 1,
                ]);
            }
        }

        /**************************************************************************************************************
         * Close current contact
         **************************************************************************************************************/
        // log foreign key
        if ( isset( $result_body->transfer_foreign_key ) ) {
            $key_result = update_post_meta( $contact_id, 'transfer_foreign_key', $result_body->transfer_foreign_key );
            if ( is_wp_error( $key_result ) ) {
                $errors->add( 'error_transfer_foreign_key', $result->get_error_message() );
            }
        }

        // add note that the record was transferred
        $time_in_mysql_format = current_time( 'mysql' );
        $comment_result = wp_insert_comment([
            'comment_post_ID' => $contact_id,
            'comment_content' => sprintf( 'This contact was transferred to %s for further follow-up.', esc_attr( get_the_title( $site_post_id ) ) ),
            'comment_type' => '',
            'comment_parent' => 0,
            'user_id' => get_current_user_id(),
            'comment_date' => $time_in_mysql_format,
            'comment_approved' => 1,
        ]);
        if ( is_wp_error( $comment_result ) ) {
            $errors->add( __METHOD__, $result->get_error_message() );
        }

        // add site connection details of the site it was transferred to.
        $site_result = update_post_meta( $contact_id, 'transfer_site_link_post_id', $site_post_id );
        if ( is_wp_error( $site_result ) ) {
            $errors->add( __METHOD__, $result->get_error_message() );
        }

        // add overall status change
        $status_result = update_post_meta( $contact_id, 'overall_status', 'closed' );
        if ( is_wp_error( $status_result ) ) {
            $errors->add( __METHOD__, $result->get_error_message() );
        }

        // add reason for close
        $reason_result = update_post_meta( $contact_id, 'reason_closed', 'transfer' );
        if ( is_wp_error( $reason_result ) ) {
            $errors->add( __METHOD__, $result->get_error_message() );
        }

        dt_write_log( $errors );

        return true;
    }

    /**
     * Receive transfer request and save transferred contact
     *
     * @param $params
     *
     * @return array|WP_Error
     */
    public static function receive_transferred_contact( $params ) {
        dt_write_log( __METHOD__ );

        // set variables
        $contact_data = $params['contact_data'];
        $post_args = $contact_data['post'];
        $comment_data = $contact_data['comments'] ?? [];
        $meta_input = [];
        $lagging_meta_input = [];
        $errors = new WP_Error();
        $site_link_post_id = Site_Link_System::get_post_id_by_site_key( Site_Link_System::decrypt_transfer_token( $params['transfer_token'] ) );

    /**
     * Insert contact record and meta
     */
        // build meta value elements
        foreach ( $contact_data['postmeta'] as $key => $value ) {
            if ( isset( $value[1] ) ) {
                foreach ( $value as $item ) {
                    $lagging_meta_input[] = [ $key => $item ];
                }
            } else {
                $meta_input[$key] = $value[0];
            }
        }
        $post_args['meta_input'] = $meta_input;

        // update user elements
        $post_args['post_author'] = dt_get_base_user( true );
        $post_args['meta_input']['assigned_to'] = "user-" . dt_get_base_user( true );
        $post_args['meta_input']['overall_status'] = "unassigned";
        $post_args['meta_input']['sources'] = "transfer";

        $possible_duplicate = false;
        $duplicate_post_id = self::duplicate_check( $contact_data['transfer_foreign_key'] );
        if ( isset( $post_args['meta_input']['reason_closed'] ) && 'transfer' === $post_args['meta_input']['reason_closed'] ) {
            $possible_duplicate = true;
            unset( $post_args['meta_input']['reason_closed'] );
        }

        // add transfer elements
        $post_args['meta_input']['transfer_id'] = $post_args['ID'];
        $post_args['meta_input']['transfer_guid'] = $post_args['guid'];
        $post_args['meta_input']['transfer_foreign_key'] = $contact_data['transfer_foreign_key'] ?: Site_Link_System::generate_token();
        $post_args['meta_input']['transfer_site_link_post_id'] = $site_link_post_id;

        unset( $post_args['guid'] );
        unset( $post_args['ID'] );

        // insert
        $post_id = wp_insert_post( $post_args );
        if ( is_wp_error( $post_id ) ) {
            $errors->add( 'transfer_insert_fail', 'Failed to create transfer contact for '. $post_args['ID'] );
            return $errors;
        }

        // insert lagging post meta
        foreach ( $lagging_meta_input as $key => $value ) {
            $meta_id = add_post_meta( $post_id, $key, $value, true );
            if ( ! $meta_id ) {
                $errors->add( 'meta_insert_fail', 'Meta data insert fail for "'. $key . '"' );
            }
        }

    /**
     * Insert comments
     */
        if ( ! empty( $comment_data ) ) {
            foreach ( $comment_data as $comment ) {
                // set variables
                $comment['comment_post_ID'] = $post_id;
                $comment['comment_author'] = __( 'Transfer Bot' ) . ' (' . $comment['user_id'] . ')';
                $comment['user_id'] = 0;
                $comment['comment_approved'] = 1;
                unset( $comment['comment_ID'] );

                // insert
                $comment_id = wp_insert_comment( $comment );
                if ( is_wp_error( $comment_id ) ) {
                    $errors->add( 'comment_insert_fail', 'Comment insert fail for '. $comment['comment_ID'] );
                    return $errors;
                }
            }
        }

        // Add transfer record comment
        $transfer_comment = wp_insert_comment([
                'user_id' => 0,
                'comment_post_ID' => $post_id,
                'comment_author' => __( 'Transfer Bot' ),
                'comment_approved' => 1,
                'comment_content' => __( 'Contact transferred from site' ) . ' "' . esc_html( get_the_title( $site_link_post_id ) ) . '"',
        ]);
        if ( is_wp_error( $transfer_comment ) ) {
            $errors->add( 'comment_insert_fail', 'Comment insert fail for transfer notation.' );
        }

        if ( $possible_duplicate || $duplicate_post_id ) {
            $message = __( 'ALERT: Possible duplicate contact from a previous transfer.' );
            if ( $duplicate_post_id ) {
                $message = $message . ' <a href="'. esc_url( site_url() ) . '/contacts/' . esc_attr( $duplicate_post_id ) .'">' . esc_attr( get_the_title( $duplicate_post_id ) ) . '</a>';
            }
            // Add transfer record comment
            $transfer_comment = wp_insert_comment([
                'user_id' => 0,
                'comment_post_ID' => $post_id,
                'comment_author' => __( 'Transfer Bot' ),
                'comment_approved' => 1,
                'comment_content' => $message,
            ]);
            if ( is_wp_error( $transfer_comment ) ) {
                $errors->add( 'comment_insert_fail', 'Comment insert fail for transfer notation.' );
            }
        }

        return [
            'status' => 'OK',
            'transfer_foreign_key' => $post_args['meta_input']['transfer_foreign_key'],
            'errors' => $errors
        ];
    }

    /**
     * @param $transfer_foreign_key
     *
     * @return bool|null|string  Duplicate exists, returns post_id; Duplicate does not exist, returns false.
     */
    public static function duplicate_check( $transfer_foreign_key ) {
        global $wpdb;
        $duplicate = $wpdb->get_var( $wpdb->prepare( "
            SELECT post_id
            FROM $wpdb->postmeta
            WHERE meta_value = %s
              AND meta_key = 'transfer_foreign_key'
            LIMIT 1", $transfer_foreign_key ) );
        if ( $duplicate ) {
            return $duplicate;
        } else {
            return false;
        }
    }
}
Disciple_Tools_Contacts_Transfer::instance();
