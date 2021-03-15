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
        add_action( 'dt_record_top_above_details', [ $this, 'contact_transfer_notification' ], 10, 2 );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
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

    public function contact_transfer_notification( $post_type, $contact ) {
        if ( $post_type === "contacts" && isset( $contact['reason_closed']['key'] ) && $contact['reason_closed']['key'] === 'transfer' ) {
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
     */
    public function add_api_routes() {
        $namespace = "dt-posts/v2";
        register_rest_route(
            $namespace, '/contacts/transfer', [
                "methods"  => "POST",
                "callback" => [ $this, 'contact_transfer_endpoint' ],
            ]
        );
        register_rest_route(
            $namespace, '/contacts/receive-transfer', [
                "methods"  => "POST",
                "callback" => [ $this, 'receive_transfer_endpoint' ],
            ]
        );
        //deprecated
        register_rest_route(
            'dt-public/v1', '/contact/transfer', [
                "methods"  => "POST",
                "callback" => [ $this, 'public_contact_transfer' ],
            ]
        );
    }

    /**
     * Section to display in the share panel for the transfer function
     *
     * @param $post
     */
    public function share_panel( $post ) {
        if ( empty( $post ) ) {
            global $post;
        }

        if ( isset( $post->post_type ) && 'contacts' === $post->post_type && current_user_can( 'dt_all_access_contacts' ) ) {
            $list = Site_Link_System::get_list_of_sites_by_type( [ 'contact_sharing', 'contact_sending' ] );
            if ( empty( $list ) ) {
                return;
            }

            $foreign_key_exists = get_post_meta( $post->ID, 'transfer_foreign_key' );
            $transfer_site_link_post_id = get_post_meta( $post->ID, 'transfer_site_link_post_id', true );
            if ( $transfer_site_link_post_id ) {
                $site_title = get_the_title( $transfer_site_link_post_id );
            } else {
                $site_title = __( 'another site', 'disciple_tools' );
            }

            ?>
            <hr>
            <div class="grid-x">

                <?php if ( $foreign_key_exists ) : ?>
                <div class="cell" id="transfer-warning">

                    <h6><?php echo sprintf( esc_html__( 'Already transfered to %s', 'disciple_tools' ), esc_html( $site_title ) ) ?></h6>
                    <p><?php esc_html_e( 'NOTE: You have already transferred this contact. Transferring again might create duplicates. Do you still want to override this warning and continue with your transfer?', 'disciple_tools' ) ?></p>
                    <p><button type="button" onclick="jQuery('#transfer-form').show();jQuery('#transfer-warning').hide();" class="button"><?php esc_html_e( 'Override and Continue', 'disciple_tools' ) ?></button></p>
                </div>
                <?php endif; ?>

                <div class="cell" id="transfer-form" <?php if ( $foreign_key_exists ) { echo 'style="display:none;"'; }?>>
                    <h6><a href="https://disciple.tools/user-docs/getting-started-info/admin/site-links/" target="_blank"> <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/></a> <?php esc_html_e( 'Transfer this contact to:', 'disciple_tools' ) ?></h6>
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
        if ( isset( $postmeta_data['duplicate_data'] ) ) {
            unset( $postmeta_data['duplicate_data'] );
        }
        $contact = DT_Posts::get_post( "contacts", $contact_id );

        $args = [
            'method' => 'POST',
            'timeout' => 20,
            'body' => [
                'transfer_token' => $site['transfer_token'],
                'contact_data' => [
                    'post' => $post_data,
                    'postmeta' => $postmeta_data,
                    'comments' => dt_get_comments_with_redacted_user_data( $contact_id ),
                    'people_groups' => $contact['people_groups'],
                    'transfer_foreign_key' => $contact['transfer_foreign_key'] ?? 0,
                ],
            ],
            'headers' => [
                'Authorization' => 'Bearer ' . $site['transfer_token'],
            ],
        ];

        $result = wp_remote_post( 'https://' . $site['url'] . '/wp-json/dt-posts/v2/contacts/receive-transfer', $args );
        if ( is_wp_error( $result ) ){
            return $result;
        }
        $result_body = json_decode( $result['body'] );

        if ( ! ( isset( $result_body->status ) && 'OK' === $result_body->status ) ) {
            $errors->add( 'transfer', $result_body->error ?? __( 'Unknown error.', 'disciple_tools' ) );
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


        $comment = sprintf( __( 'This contact was transferred to %s for further follow-up.', 'disciple_tools' ), esc_attr( get_the_title( $site_post_id ) ) );
        if ( isset( $result_body->created_id )){
            $comment .= ' [link](https://' . $site['url'] . '/contacts/' . esc_attr( $result_body->created_id ) . ')';
        }
        // add note that the record was transferred
        $time_in_mysql_format = current_time( 'mysql' );
        $comment_result = wp_insert_comment([
            'comment_post_ID' => $contact_id,
            'comment_content' => $comment,
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
                if ( $key === "type" && $value[0] === "media" ){
                    $value[0] = "access";
                }
                $meta_input[$key] = maybe_unserialize( $value[0] );
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
        foreach ( $lagging_meta_input as $index => $row ) {
            foreach ( $row as $key => $value ) {
                $meta_id = add_post_meta( $post_id, $key, $value, false );
                if ( !$meta_id ) {
                    $errors->add( 'meta_insert_fail', 'Meta data insert fail for "'. $key . '"' );
                }
            }
        }

        /**
         * Insert comments
         */
        if ( ! empty( $comment_data ) ) {
            foreach ( $comment_data as $comment ) {
                // set variables
                $comment['comment_post_ID'] = $post_id;
                $comment['comment_author'] = __( 'Transfer Bot', 'disciple_tools' ) . ' (' . $comment['user_id'] . ')';
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
                'comment_author' => __( 'Transfer Bot', 'disciple_tools' ),
                'comment_approved' => 1,
                'comment_content' => __( 'Contact transferred from site', 'disciple_tools' ) . ' "' . esc_html( get_the_title( $site_link_post_id ) ) . '"',
        ]);
        if ( is_wp_error( $transfer_comment ) ) {
            $errors->add( 'comment_insert_fail', 'Comment insert fail for transfer notation.' );
        }

        if ( $possible_duplicate || $duplicate_post_id ) {
            $message = __( 'ALERT: Possible duplicate contact from a previous transfer.', 'disciple_tools' );
            if ( $duplicate_post_id ) {
                $message = $message . ' <a href="'. esc_url( site_url() ) . '/contacts/' . esc_attr( $duplicate_post_id ) .'">' . esc_attr( get_the_title( $duplicate_post_id ) ) . '</a>';
            }
            // Add transfer record comment
            $transfer_comment = wp_insert_comment([
                'user_id' => 0,
                'comment_post_ID' => $post_id,
                'comment_author' => __( 'Transfer Bot', 'disciple_tools' ),
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
            'errors' => $errors,
            'created_id' => $post_id
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

    public function public_contact_transfer( WP_REST_Request $request ){

        $params = $this->process_token( $request );
        if ( is_wp_error( $params ) ) {
            return [
                'status' => 'FAIL',
                'error' => 'Transfer token error.'
            ];
        }

        if ( ! current_user_can( 'create_contacts' ) ) {
            return [
                'status' => 'FAIL',
                'error' => 'Permission error.'
            ];
        }

        if ( isset( $params['contact_data'] ) ) {
            $result = self::receive_transferred_contact( $params );
            if ( is_wp_error( $result ) ) {
                return [
                    'status' => 'FAIL',
                    'error' => $result->get_error_message(),
                ];
            } else {
                return [
                    'status' => 'OK',
                    'error' => $result['errors'],
                    'created_id' => $result['created_id'],
                ];
            }
        } else {
            return [
                'status' => 'FAIL',
                'error' => 'Missing required parameter'
            ];
        }
    }

    /**
     * Public key processing utility. Use this at the beginning of public endpoints
     *
     * @param WP_REST_Request $request
     *
     * @return array|WP_Error
     */
    public function process_token( WP_REST_Request $request ) {

        $params = $request->get_params();

        // required token parameter challenge
        if ( ! isset( $params['transfer_token'] ) ) {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }

        $valid_token = Site_Link_System::verify_transfer_token( $params['transfer_token'] );

        // required valid token challenge
        if ( ! $valid_token ) {
            dt_write_log( $valid_token );
            return new WP_Error( __METHOD__, 'Invalid transfer token' );
        }

        return $params;
    }

    public function contact_transfer_endpoint( WP_REST_Request $request ){

        if ( ! ( current_user_can( 'dt_all_access_contacts' ) || current_user_can( 'manage_dt' ) ) ) {
            return new WP_Error( __METHOD__, 'Insufficient permissions' );
        }

        $params = $request->get_params();
        if ( ! isset( $params['contact_id'] ) || ! isset( $params['site_post_id'] ) ){
            return new WP_Error( __METHOD__, "Missing required parameters.", [ 'status' => 400 ] );
        }

        return self::contact_transfer( $params['contact_id'], $params['site_post_id'] );

    }

    public function receive_transfer_endpoint( WP_REST_Request $request ){
        $params = $request->get_params();
        if ( ! current_user_can( 'create_contacts' ) ) {
            return new WP_Error( __METHOD__, 'Insufficient permissions' );
        }

        if ( isset( $params['contact_data'] ) ) {
            $result = self::receive_transferred_contact( $params );
            if ( is_wp_error( $result ) ) {
                return [
                    'status' => 'FAIL',
                    'error' => $result->get_error_message(),
                ];
            } else {
                return [
                    'status' => 'OK',
                    'error' => $result['errors'],
                    'created_id' => $result['created_id'],
                ];
            }
        } else {
            return [
                'status' => 'FAIL',
                'error' => 'Missing required parameter'
            ];
        }
    }
}
Disciple_Tools_Contacts_Transfer::instance();

function dt_get_comments_with_redacted_user_data( $post_id ) {
    $comments = get_comments( [ 'post_id' => $post_id ] );
    if ( empty( $comments ) ) {
        return $comments;
    }
    $email_note = __( 'redacted email', 'disciple_tools' );
    $name_note = __( 'redacted name', 'disciple_tools' );
    $redacted_note = __( 'redacted', 'disciple_tools' );

    $users = get_users();

    foreach ( $comments as $index => $comment ) {
        $comment_content = $comment->comment_content;

        // replace non-@mention references to login names, display names, or user emails
        foreach ( $users as $user ) {
            if ( !empty( $user->data->user_login ) ) {
                $comment_content = str_replace( ' ' . $user->data->user_login, '(' . $name_note . ')', $comment_content );
                $comment_content = str_replace( $user->data->user_login . ' ', '(' . $name_note . ')', $comment_content );
            }
            if ( !empty( $user->data->display_name ) ) {
                $comment_content = str_replace( ' ' . $user->data->display_name, '(' . $name_note . ')', $comment_content );
                $comment_content = str_replace( $user->data->display_name . ' ', '(' . $name_note . ')', $comment_content );
            }
            if ( !empty( $user->data->user_nicename ) ) {
                $comment_content = str_replace( $user->data->user_nicename . ' ', '(' . $name_note . ')', $comment_content );
                $comment_content = str_replace( ' ' . $user->data->user_nicename, '(' . $name_note . ')', $comment_content );
            }
            if ( ! empty( $user->data->user_email ) ) {
                $comment_content = str_replace( $user->data->user_email, '(' . $email_note . ')', $comment_content );
            }
        }

        // replace @mentions
        preg_match_all( '/@[0-9a-zA-Z](\.?[0-9a-zA-Z])*/', $comment_content, $matches );
        foreach ( $matches[0] as $match_key => $match ){
            $comment_content = str_replace( $match, '@' . $name_note, $comment_content );
        }

        // replace duplicate notes
        $comment_content = str_replace( site_url(), '#', $comment_content );

        $comments[$index]->comment_content = $comment_content;
    }

    return $comments;
}
