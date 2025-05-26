<?php
/**
 * Module for transferring contacts between DT sites
 */

class Disciple_Tools_Contacts_Transfer {

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
        $type['contact_sharing']   = __( 'Contact Transfer Both Ways', 'disciple_tools' );
        $type['contact_sending']   = __( 'Contact Transfer Sending Only', 'disciple_tools' );
        $type['contact_receiving'] = __( 'Contact Transfer Receiving Only', 'disciple_tools' );

        return $type;
    }

    // Add the specific capabilities needed for the site to site linking.
    public function site_link_capabilities( $args ) {
        if ( 'contact_sharing' === $args['connection_type'] ) {
            $args['capabilities'][] = 'create_contacts';
            $args['capabilities'][] = 'update_own_contacts';
        }
        if ( 'contact_receiving' === $args['connection_type'] ) {
            $args['capabilities'][] = 'create_contacts';
        }

        return $args;
    }

    public function contact_transfer_notification( $post_type, $contact ) {
        if ( $post_type === 'contacts' && isset( $contact['reason_closed']['key'] ) && $contact['reason_closed']['key'] === 'transfer' ) {
            ?>
            <section class="cell small-12">
                <div class="bordered-box detail-notification-box" style="background-color:#3F729B">
                    <h4><img class="dt-white-icon"
                             src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/alert-circle-exc.svg?v=2' ) ?>"/><?php esc_html_e( 'This contact has been transferred', 'disciple_tools' ) ?>
                        .</h4>
                    <p><?php esc_html_e( 'This contact has been transferred to', 'disciple_tools' ) ?>
                        : <?php echo isset( $contact['transfer_site_link_post_id'] ) ? esc_html( get_the_title( $contact['transfer_site_link_post_id'] ) ) : ''; ?></p>
                </div>
            </section>
            <?php

            // Display remote summary
            $this->contact_transfer_summary( $post_type, $contact );
        }
    }

    /**
     * Tile to show details from the contact transferred to a remote instance
     * @param $post_type
     * @param $contact
     * @return void
     */
    private function contact_transfer_summary( $post_type, $contact ) {
        if ( $post_type === 'contacts' && isset( $contact['reason_closed']['key'] ) && $contact['reason_closed']['key'] === 'transfer' && isset( $contact['transfer_foreign_key'], $contact['transfer_site_link_post_id'] ) ) {

            $site = Site_Link_System::get_site_connection_vars( $contact['transfer_site_link_post_id'] );
            if ( ! is_wp_error( $site ) ) {

                // Prepare record summary request payload
                $args = [
                    'method'  => 'POST',
                    'timeout' => 20,
                    'body'    => [
                        'contact_id'           => $contact['ID'],
                        'transfer_foreign_key' => $contact['transfer_foreign_key']
                    ],
                    'headers' => [
                        'Authorization' => 'Bearer ' . $site['transfer_token']
                    ]
                ];

                // Request record summary from remote site
                $result = wp_remote_post( 'https://' . $site['url'] . '/wp-json/dt-posts/v2/contacts/transfer/summary', $args );
                if ( ! is_wp_error( $result ) ) {

                    $remote_contact = json_decode( $result['body'], true );
                    if ( ! empty( $remote_contact ) && ! is_wp_error( $remote_contact ) ) {

                        // Fetch desired remote record contact summary information
                        $field_settings      = DT_Posts::get_post_field_settings( 'contacts' );

                        $status          = $remote_contact['overall_status'] ?? [
                                'key'   => 'empty',
                                'label' => __( 'Status Currently Unavailable', 'disciple_tools' )
                            ];
                        $status_settings = $field_settings['overall_status'];

                        $seeker          = $remote_contact['seeker_path'] ?? [
                                'key'   => 'empty',
                                'label' => __( 'Seeker Path Currently Unavailable', 'disciple_tools' )
                            ];
                        $seeker_settings = $field_settings['seeker_path'];

                        $milestones          = $remote_contact['milestones'] ?? [];
                        $milestones_settings = $field_settings['milestones'];

                        ?>
                        <section class="cell small-12">
                            <div class="bordered-box detail-notification-box"
                                 style="background-color:#FFFFFF; color: #000000;">
                                <h4><?php esc_html_e( 'Remote Contact Summary', 'disciple_tools' ) ?></h4>

                                <!-- Status -->
                                <div class="section-subheader">
                                    <img style="max-height: 14px; max-width: 14px;"
                                         src="<?php echo esc_html( $status_settings['icon'] ); ?>">
                                    <?php echo esc_html( $status_settings['name'] ); ?> : <?php echo esc_html( $status['label'] ?? '' ); ?>
                                </div>

                                <!-- Seeker Path -->
                                <div class="section-subheader">
                                    <img style="max-height: 15px; max-width: 15px;"
                                         src="<?php echo esc_html( $seeker_settings['icon'] ); ?>">
                                    <?php echo esc_html( $seeker_settings['name'] ); ?> : <?php echo esc_html( $seeker['label'] ?? '' ); ?>
                                </div>
                                <br>

                                <!-- Milestones -->
                                <div class="section-subheader">
                                    <img style="max-height: 15px; max-width: 15px;"
                                         src="<?php echo esc_html( $milestones_settings['icon'] ); ?>">
                                    <?php echo esc_html( $milestones_settings['name'] ); ?>
                                </div>

                                <?php
                                foreach ( $milestones_settings['default'] ?? [] as $key => $milestone ) {
                                    if ( in_array( $key, $milestones ) ) {
                                        dt_render_field_icon( $milestone );
                                        echo esc_html( $milestone['label'] ); ?>
                                        <?php
                                    }
                                }
                                if ( empty( $milestones ) ) {
                                    echo esc_html__( 'None Set', 'disciple_tools' );
                                }
                                ?>
                                <hr>

                                <!-- Comments -->
                                <textarea id="transfer_contact_summary_update_comment"
                                          placeholder="<?php esc_html_e( 'Write your comment or note here', 'disciple_tools' ) ?>"
                                          style="overflow: hidden; height: 50px;"></textarea>

                                <div class="shrink cell">
                                    <button id="transfer_contact_summary_update_button" class="button loader">
                                        <?php esc_html_e( 'Submit Update', 'disciple_tools' ) ?>
                                    </button>
                                </div>
                                <br>
                                <span id="transfer_contact_summary_update_message" style="display: none;"></span>

                            </div>
                        </section>
                        <?php
                    }
                }
            }
        }
    }

    /**
     * Rest Endpoints
     */
    public function add_api_routes() {
        $namespace = 'dt-posts/v2';
        //Transfer a contact to another instance
        register_rest_route(
            $namespace, '/contacts/transfer', [
                'methods'             => 'POST',
                'callback'            => [ $this, 'contact_transfer_endpoint' ],
                'permission_callback' => '__return_true',
            ]
        );
        //Provide global metrics on contacts received by transfer
        register_rest_route(
            $namespace, '/contacts/transfer/metrics', [
                'methods'             => 'POST',
                'callback'            => [ $this, 'contact_transfer_metrics_endpoint' ],
                'permission_callback' => '__return_true',
            ]
        );
        //Provide summary details on a contact received by transfer
        register_rest_route(
            $namespace, '/contacts/transfer/summary', [
                'methods'  => 'POST',
                'callback' => [ $this, 'contact_transfer_summary_endpoint' ],
                'permission_callback' => '__return_true',
            ]
        );
        //Send an update to a transferred contact
        register_rest_route(
            $namespace, '/contacts/transfer/summary/send-update', [
                'methods'  => 'POST',
                'callback' => [ $this, 'contact_transfer_summary_send_update_endpoint' ],
                'permission_callback' => '__return_true',
            ]
        );
        //Receive an update on a contact received by transfer
        register_rest_route(
            $namespace, '/contacts/transfer/summary/receive-update', [
                'methods'  => 'POST',
                'callback' => [ $this, 'contact_transfer_summary_receive_update_endpoint' ],
                'permission_callback' => '__return_true',
            ]
        );
        register_rest_route(
            $namespace, '/contacts/receive-transfer', [
                'methods'             => 'POST',
                'callback'            => [ $this, 'receive_transfer_endpoint' ],
                'permission_callback' => '__return_true',
            ]
        );
        register_rest_route(
            $namespace, '/contacts/receive-transfer/comments', [
                'methods'             => 'POST',
                'callback'            => [ $this, 'receive_transfer_comments_endpoint' ],
                'permission_callback' => '__return_true',
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

            $foreign_key_exists         = get_post_meta( $post->ID, 'transfer_foreign_key' );
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
                        <p>
                            <button type="button"
                                    onclick="jQuery('#transfer-form').show();jQuery('#transfer-warning').hide();"
                                    class="button"><?php esc_html_e( 'Override and Continue', 'disciple_tools' ) ?></button>
                        </p>
                    </div>
                <?php endif; ?>

                <div class="cell" id="transfer-form" <?php if ( $foreign_key_exists ) { echo 'style="display:none;"'; } ?>>
                    <h6><a href="https://disciple.tools/docs/site-links/"
                           target="_blank"> <img class="help-icon"
                                                 src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/></a> <?php esc_html_e( 'Transfer this contact to:', 'disciple_tools' ) ?>
                    </h6>
                    <select name="transfer_contact" id="transfer_contact"
                            onchange="jQuery('#transfer_button_div').show();">
                        <option value=""></option>
                        <?php
                        foreach ( $list as $site ) {
                            echo '<option value="' . esc_attr( $site['id'] ) . '">' . esc_html( $site['name'] ) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="cell" id="transfer_button_div" style="display:none;">
                    <button id="transfer_confirm_button" class="button loader"
                            type="button"><?php esc_html_e( 'Confirm Transfer', 'disciple_tools' ) ?></button>
                    <span id="transfer_spinner"></span>
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
        global $wpdb;

        $errors = new WP_Error();

        /**************************************************************************************************************
         * Transfer current contact
         ***************************************************************************************************************/

        $site = Site_Link_System::get_site_connection_vars( $site_post_id );
        if ( is_wp_error( $site ) ) {
            $errors->add( __METHOD__, 'Error creating site connection details.' );

            return $errors;
        }

        $post_data     = get_post( $contact_id, ARRAY_A );
        $postmeta_data = get_post_meta( $contact_id );
        if ( isset( $postmeta_data['duplicate_data'] ) ) {
            unset( $postmeta_data['duplicate_data'] );
        }

        // Ensure location based field types, are shaped accordingly and packaged with universal grid data.
        $field_settings = DT_Posts::get_post_field_settings( $post_data['post_type'] );
        $updated_postmeta_data = array();
        foreach ( $postmeta_data as $key => $value ) {
            if ( isset( $field_settings[ $key ] ) && in_array( $field_settings[ $key ]['type'], array( 'location', 'location_meta' ) ) ) {
                switch ( $field_settings[ $key ]['type'] ) {
                    case 'location':
                        $updated_locations = array();
                        foreach ( $value as $location_grid_id ) {
                            $updated_locations[] = array(
                                'value' => $location_grid_id,
                            );
                        }
                        $updated_postmeta_data[ $key ] = array(
                            'values' => $updated_locations,
                        );
                        break;
                    case 'location_meta':
                        $updated_location_metas = array();
                        foreach ( $value as $grid_meta_id ) {
                            $location_grid_meta = $wpdb->get_results( $wpdb->prepare( "SELECT grid_id, lng, lat, level, label FROM $wpdb->dt_location_grid_meta WHERE grid_meta_id = %d", $grid_meta_id ), ARRAY_A );
                            if ( ( count( $location_grid_meta ) > 0 ) && isset( $location_grid_meta[0]['grid_id'], $location_grid_meta[0]['lng'], $location_grid_meta[0]['lat'], $location_grid_meta[0]['level'], $location_grid_meta[0]['label'] ) ) {
                                $updated_location_metas[] = array(
                                    'grid_id' => $location_grid_meta[0]['grid_id'],
                                    'lng' => $location_grid_meta[0]['lng'],
                                    'lat' => $location_grid_meta[0]['lat'],
                                    'level' => $location_grid_meta[0]['level'],
                                    'label' => $location_grid_meta[0]['label'],
                                );
                            }
                        }
                        $updated_postmeta_data[ $key ] = array(
                            'values' => $updated_location_metas,
                        );
                        break;
                }
            } else {
                $updated_postmeta_data[ $key ] = $value;
            }
        }

        $contact = DT_Posts::get_post( 'contacts', $contact_id );

        $comments       = dt_get_comments_with_redacted_user_data( $contact_id );
        $comment_chunks = array_chunk( $comments, 200 );

        $args = [
            'method'  => 'POST',
            'timeout' => 20,
            'body'    => [
                'transfer_token' => $site['transfer_token'],
                'contact_data'   => [
                    'post'                 => $post_data,
                    'postmeta'             => $updated_postmeta_data,
                    'comments'             => isset( $comment_chunks[0] ) ? $comment_chunks[0] : [],
                    'people_groups'        => $contact['people_groups'],
                    'transfer_foreign_key' => $contact['transfer_foreign_key'] ?? 0,
                ],
            ],
            'headers' => [
                'Authorization' => 'Bearer ' . $site['transfer_token'],
            ],
        ];

        $result = wp_remote_post( 'https://' . $site['url'] . '/wp-json/dt-posts/v2/contacts/receive-transfer', $args );
        if ( is_wp_error( $result ) ) {
            return $result;
        }
        $result_body = json_decode( $result['body'] );

        if ( ! ( isset( $result_body->status ) && 'OK' === $result_body->status ) ) {
            $errors->add( 'transfer', $result_body->error ?? __( 'Unknown error.', 'disciple_tools' ) );

            return $errors;
        }

        if ( sizeof( $comment_chunks ) > 1 && isset( $result_body->transfer_foreign_key, $result_body->created_id ) ) {
            $size = sizeof( $comment_chunks );
            for ( $i = 1; $i < $size; $i++ ) {

                $args = [
                    'method'  => 'POST',
                    'timeout' => 20,
                    'body'    => [
                        'post_id'              => $result_body->created_id,
                        'comments'             => $comment_chunks[ $i ],
                        'transfer_foreign_key' => $result_body->transfer_foreign_key
                    ],
                    'headers' => [
                        'Authorization' => 'Bearer ' . $site['transfer_token'],
                    ],
                ];

                $result = wp_remote_post( 'https://' . $site['url'] . '/wp-json/dt-posts/v2/contacts/receive-transfer/comments', $args );
            }
        }


        if ( ! empty( $result_body->error ) ) {
            foreach ( $result_body->error->errors as $key => $value ) {
                $time_in_mysql_format = current_time( 'mysql' );
                wp_insert_comment( [
                    'comment_post_ID'  => $contact_id,
                    'comment_content'  => __( 'Minor transfer error.', 'disciple_tools' ) . ' ' . $key,
                    'comment_type'     => '',
                    'comment_parent'   => 0,
                    'user_id'          => get_current_user_id(),
                    'comment_date'     => $time_in_mysql_format,
                    'comment_approved' => 1,
                ] );
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
        if ( isset( $result_body->created_id ) ) {
            $comment .= ' [link](https://' . $site['url'] . '/contacts/' . esc_attr( $result_body->created_id ) . ')';
        }
        // add note that the record was transferred
        $time_in_mysql_format = current_time( 'mysql' );
        $comment_result       = wp_insert_comment( [
            'comment_post_ID'  => $contact_id,
            'comment_content'  => $comment,
            'comment_type'     => '',
            'comment_parent'   => 0,
            'user_id'          => get_current_user_id(),
            'comment_date'     => $time_in_mysql_format,
            'comment_approved' => 1,
        ] );
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

        if ( $errors->has_errors() ) {
            dt_write_log( $errors );
        }

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
        // set variables
        $contact_data       = $params['contact_data'];
        $post_args          = $contact_data['post'];
        $comment_data       = $contact_data['comments'] ?? [];
        $meta_input         = [];
        $lagging_meta_input = [];
        $lagging_location_meta_input = [];
        $errors             = new WP_Error();
        $site_link_post_id  = Site_Link_System::get_post_id_by_site_key( Site_Link_System::decrypt_transfer_token( $params['transfer_token'] ) );
        $field_settings = DT_Posts::get_post_field_settings( $post_args['post_type'] );

        /**
         * Insert contact record and meta
         */
        // build meta value elements
        foreach ( $contact_data['postmeta'] as $key => $value ) {
            if ( isset( $field_settings[ $key ] ) && in_array( $field_settings[ $key ]['type'], array( 'location', 'location_meta' ) ) ) {
                $lagging_location_meta_input[ $key ] = $value;
            } elseif ( isset( $value[1] ) ) {
                foreach ( $value as $item ) {
                    $lagging_meta_input[] = [ $key => $item ];
                }
            } else {
                if ( $key === 'type' && $value[0] === 'media' ) {
                    $value[0] = 'access';
                }
                $meta_input[ $key ] = maybe_unserialize( $value[0] );
            }
        }
        $post_args['meta_input'] = $meta_input;

        // update user elements
        $base_user                                 = dt_get_base_user( false );
        $post_args['post_author']                  = $base_user->ID;
        $post_args['meta_input']['assigned_to']    = 'user-' . $base_user->ID;
        $post_args['meta_input']['overall_status'] = 'unassigned';
        $post_args['meta_input']['sources']        = 'transfer';

        $possible_duplicate = false;
        $duplicate_post_id  = false;
        if ( isset( $contact_data['transfer_foreign_key'] ) ) {
            $duplicate_post_id = self::duplicate_check( $contact_data['transfer_foreign_key'] );
        }
        if ( isset( $post_args['meta_input']['reason_closed'] ) && 'transfer' === $post_args['meta_input']['reason_closed'] ) {
            $possible_duplicate = true;
            unset( $post_args['meta_input']['reason_closed'] );
        }

        // add transfer elements
        $post_args['meta_input']['transfer_id'] = $post_args['ID'];
        $post_args['meta_input']['transfer_guid'] = $post_args['guid'];
        $post_args['meta_input']['transfer_foreign_key'] = ( isset( $contact_data['transfer_foreign_key'] ) && !empty( $contact_data['transfer_foreign_key'] ) ) ? $contact_data['transfer_foreign_key'] : Site_Link_System::generate_token();
        $post_args['meta_input']['transfer_site_link_post_id'] = $site_link_post_id;

        unset( $post_args['guid'] );
        unset( $post_args['ID'] );

        // insert
        $post_id = wp_insert_post( $post_args );
        if ( is_wp_error( $post_id ) ) {
            $errors->add( 'transfer_insert_fail', 'Failed to create transfer contact for ' . $post_args['ID'] );

            return $errors;
        }

        // insert lagging post meta
        foreach ( $lagging_meta_input as $index => $row ) {
            foreach ( $row as $key => $value ) {
                $meta_id = add_post_meta( $post_id, $key, $value, false );
                if ( ! $meta_id ) {
                    $errors->add( 'meta_insert_fail', 'Meta data insert fail for "' . $key . '"' );
                }
            }
        }

        // Insert location based lagging post meta field types.
        $inserted_location_fields = DT_Posts::update_location_grid_fields( $field_settings, $post_id, $lagging_location_meta_input, $post_args['post_type'] );
        if ( is_wp_error( $inserted_location_fields ) ) {
            $errors->add( 'location_meta_insert_fail', 'Failed to insert location meta for ' . $post_args['ID'] );
        }

        /**
         * Insert comments
         */
        if ( ! empty( $comment_data ) ) {
            $insert_comments = self::insert_bulk_comments( $comment_data, $post_id );
            if ( is_wp_error( $insert_comments ) ) {
                $errors->add( $insert_comments->get_error_code(), $insert_comments->get_error_message() );
            }
        }

        // Add transfer record comment
        $comment          = '@[' . $base_user->display_name . '](' . $base_user->ID . '), ' . __( 'Contact transferred from site', 'disciple_tools' ) . ' "' . esc_html( get_the_title( $site_link_post_id ) ) . '"';
        $transfer_comment = DT_Posts::add_post_comment( 'contacts', $post_id, $comment, 'comment', [
            'user_id'        => 0,
            'comment_author' => __( 'Transfer Bot', 'disciple_tools' ),
        ], false );
        if ( is_wp_error( $transfer_comment ) ) {
            $errors->add( 'comment_insert_fail', 'Comment insert fail for transfer notation.' );
        }

        if ( $possible_duplicate || $duplicate_post_id ) {
            $message = __( 'ALERT: Possible duplicate contact from a previous transfer.', 'disciple_tools' );
            if ( $duplicate_post_id ) {
                $message = $message . ' <a href="' . esc_url( site_url() ) . '/contacts/' . esc_attr( $duplicate_post_id ) . '">' . esc_attr( get_the_title( $duplicate_post_id ) ) . '</a>';
            }
            // Add transfer record comment
            $transfer_comment = wp_insert_comment( [
                'user_id'          => 0,
                'comment_post_ID'  => $post_id,
                'comment_author'   => __( 'Transfer Bot', 'disciple_tools' ),
                'comment_approved' => 1,
                'comment_content'  => $message,
            ] );
            if ( is_wp_error( $transfer_comment ) ) {
                $errors->add( 'comment_insert_fail', 'Comment insert fail for transfer notation.' );
            }
        }

        return [
            'status'               => 'OK',
            'transfer_foreign_key' => $post_args['meta_input']['transfer_foreign_key'],
            'errors'               => $errors,
            'created_id'           => $post_id
        ];
    }

    private static function insert_bulk_comments( $comments, $post_id ) {
        if ( ! empty( $comments ) ) {
            global $wpdb;

            $hunk = array_chunk( $comments, 200 );
            foreach ( $hunk as $group ) {
                if ( empty( $group ) ) {
                    continue;
                }
                $sql = "INSERT INTO $wpdb->comments (comment_post_ID, comment_author, comment_author_email, comment_date, comment_date_gmt, comment_content, comment_approved, comment_type, comment_parent, user_id) VALUES ";

                foreach ( $group as $comment ) {
                    $comment                   = dt_recursive_sanitize_array( $comment );
                    $comment['comment_author'] = __( 'Transfer Bot', 'disciple_tools' ) . ' (' . ( $comment['user_id'] ?? 0 ) . ')';


                    $sql .= $wpdb->prepare( '( %d, %s, %s, %s, %s, %s, %d, %s, %d, %d ),',
                        $post_id,
                        $comment['comment_author'] ?? '',
                        $comment['comment_author_email'] ?? '',
                        $comment['comment_date'],
                        $comment['comment_date_gmt'],
                        $comment['comment_content'] ?? '',
                        1,
                        $comment['comment_type'] ?? 'comment',
                        $comment['comment_parent'] ?? 0,
                        0
                    );
                }
                $sql .= ';';
                $sql = str_replace( ',;', ';', $sql ); // remove last comma

                $insert_comments = $wpdb->query( $sql ); // @phpcs:ignore
                if ( empty( $insert_comments ) || is_wp_error( $insert_comments ) ) {
                    return new WP_Error( __FUNCTION__, 'Failed to insert comments' );
                }
            }

            return true;
        }

        return false;
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

    public function get_local_post_id( $remote_contact_id, $transfer_foreign_key ) {
        global $wpdb;

        return $wpdb->get_var( $wpdb->prepare( "
            SELECT post_id FROM $wpdb->postmeta
            WHERE post_id IN (
                SELECT post_id FROM $wpdb->postmeta
                WHERE meta_key = 'transfer_id'
                AND meta_value = %d
                GROUP BY post_id
            )
            AND (
                meta_key = 'transfer_foreign_key'
                AND meta_value = %d
            )
            LIMIT 1", $remote_contact_id, $transfer_foreign_key )
        );
    }

    public function contact_transfer_endpoint( WP_REST_Request $request ){

        if ( ! ( current_user_can( 'dt_all_access_contacts' ) || current_user_can( 'manage_dt' ) ) ) {
            return new WP_Error( __METHOD__, 'Insufficient permissions' );
        }

        $params = $request->get_params();
        if ( ! isset( $params['contact_id'] ) || ! isset( $params['site_post_id'] ) ) {
            return new WP_Error( __METHOD__, 'Missing required parameters.', [ 'status' => 400 ] );
        }

        return self::contact_transfer( $params['contact_id'], $params['site_post_id'] );
    }

    /**
     * Provide summary details on a contact received by transfer
     * @param WP_REST_Request $request
     * @return array|WP_Error, the summary (overall_status, seeker_path, milestones)
     */
    public function contact_transfer_summary_endpoint( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( ! isset( $params['contact_id'], $params['transfer_foreign_key'] ) ) {
            return new WP_Error( __METHOD__, 'Missing contact_id or transfer_foreign_key', [ 'status' => 400 ] );
        }

        if ( ! current_user_can( 'update_own_contacts' ) ) {
            return new WP_Error( __METHOD__, 'Missing permissions', [ 'status' => 400 ] );
        }
        global $wp_session;
        if ( ! isset( $wp_session['logged_in_as_site_link'] ) ) {
            return new WP_Error( __METHOD__, 'Missing permissions', [ 'status' => 400 ] );
        }

        // Fetch local post id for summary details
        $post_id = $this->get_local_post_id( $params['contact_id'], $params['transfer_foreign_key'] );
        if ( empty( $post_id ) ) {
            return new WP_Error( __METHOD__, 'Could not find post id to fetch summary', [ 'status' => 404 ] );

        } else {
            $post = DT_Posts::get_post( 'contacts', $post_id, true, false, true );
            if ( ! empty( $post ) && ! is_wp_error( $post ) ) {
                $response = [];

                if ( isset( $post['overall_status'] ) ) {
                    $response['overall_status'] = $post['overall_status'];
                }
                if ( isset( $post['seeker_path'] ) ) {
                    $response['seeker_path'] = $post['seeker_path'];
                }
                if ( isset( $post['milestones'] ) ) {
                    $response['milestones'] = $post['milestones'];
                }

                return $response;

            } else {
                return new WP_Error( __METHOD__, 'Could not find post record to fetch summary', [ 'status' => 404 ] );
            }
        }
    }


    /**
     * Send an update to a transferred contact on another instance
     * A comment is currently the only update supported
     * @param WP_REST_Request $request
     * @return false[]|WP_Error
     */
    public function contact_transfer_summary_send_update_endpoint( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( ! isset( $params['contact_id'], $params['update'] ) ) {
            return new WP_Error( __METHOD__, 'Missing contact_id or update', [ 'status' => 400 ] );
        }
        if ( ! ( current_user_can( 'dt_all_access_contacts' ) || current_user_can( 'manage_dt' ) ) ) {
            return new WP_Error( __METHOD__, 'Insufficient permissions' );
        }

        $success = false;

        // Fetch corresponding post and ensure required transfer information is set
        $contact = DT_Posts::get_post( 'contacts', $params['contact_id'], true, false, true );
        if ( ! empty( $contact ) && ! is_wp_error( $contact ) && isset( $contact['transfer_foreign_key'], $contact['transfer_site_link_post_id'] ) ) {

            // Fetch transferred site connection details
            $site = Site_Link_System::get_site_connection_vars( $contact['transfer_site_link_post_id'] );
            if ( ! empty( $site ) && ! is_wp_error( $site ) ) {

                // Prepare record summary update request payload
                $args = [
                    'method'  => 'POST',
                    'timeout' => 20,
                    'body'    => [
                        'contact_id'           => $contact['ID'],
                        'transfer_foreign_key' => $contact['transfer_foreign_key'],
                        'update'               => $params['update']
                    ],
                    'headers' => [
                        'Authorization' => 'Bearer ' . $site['transfer_token']
                    ]
                ];

                // Post summary update payload
                $response = wp_remote_post( 'https://' . $site['url'] . '/wp-json/dt-posts/v2/contacts/transfer/summary/receive-update', $args );
                if ( ! empty( $response ) && ! is_wp_error( $response ) ) {
                    $result  = json_decode( $response['body'], true );
                    $success = ( ! empty( $result ) && ! is_wp_error( $result ) && isset( $result['success'] ) ) ? $result['success'] : false;
                }
            }
        }

        return [
            'success' => $success
        ];
    }

    /**
     * Receive an update on a contact received by transfer
     * @param WP_REST_Request $request
     * @return bool[]|WP_Error
     */
    public function contact_transfer_summary_receive_update_endpoint( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( ! isset( $params['contact_id'], $params['transfer_foreign_key'], $params['update'] ) ) {
            return new WP_Error( __METHOD__, 'Missing contact_id or transfer_foreign_key or update', [ 'status' => 400 ] );
        }

        if ( !current_user_can( 'update_own_contacts' ) ){
            return new WP_Error( __METHOD__, 'Missing permissions', [ 'status' => 400 ] );
        }
        global $wp_session;
        if ( !isset( $wp_session['logged_in_as_site_link'] ) ){
            return new WP_Error( __METHOD__, 'Missing permissions', [ 'status' => 400 ] );
        }

        // Fetch local post id to be updated
        $post_id = $this->get_local_post_id( $params['contact_id'], $params['transfer_foreign_key'] );
        if ( empty( $post_id ) ) {
            return new WP_Error( __METHOD__, 'Could not find post id to update', [ 'status' => 404 ] );

        } else {

            $args               = [
                'user_id'        => 0,
                'comment_author' => __( 'Transfer Bot', 'disciple_tools' ) . ' - ' . $wp_session['logged_in_as_site_link']['label'],
            ];
            $created_comment_id = DT_Posts::add_post_comment( 'contacts', $post_id, $params['update'], 'comment', $args, false, true );
            $success            = ( ! empty( $created_comment_id ) && ! is_wp_error( $created_comment_id ) );
        }

        return [
            'success' => $success
        ];
    }

    /**
     * Provide metrics on contacts received by transfer
     *
     * @param WP_REST_Request $request
     *
     * @return array|WP_Error, metrics (overall_status, seeker_path, milestones)
     */
    public function contact_transfer_metrics_endpoint( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( ! isset( $params['start'], $params['end'] ) ) {
            return new WP_Error( __METHOD__, 'Missing date range', [ 'status' => 400 ] );
        }

        if ( ! current_user_can( 'update_own_contacts' ) ) {
            return new WP_Error( __METHOD__, 'Missing permissions', [ 'status' => 400 ] );
        }
        global $wp_session;
        if ( ! isset( $wp_session['logged_in_as_site_link'] ) ) {
            return new WP_Error( __METHOD__, 'Missing permissions', [ 'status' => 400 ] );
        }

        $site_link_post_id = $wp_session['logged_in_as_site_link']['post_id'];

        $metrics = [
            'statuses_current'     => [],
            'statuses_changes'     => [],
            'seeker_paths_current' => [],
            'seeker_paths_changes' => [],
            'milestones_current'   => [],
            'milestones_changes'   => []
        ];

        $start = $params['start'];
        $end   = $params['end'];

        // Proceed with metrics retrieval
        global $wpdb;
        $field_settings = DT_Posts::get_post_field_settings( 'contacts' );

        // Total Transferred/Created
        $total_transferred = $wpdb->get_var( $wpdb->prepare( "
        SELECT COUNT(DISTINCT(log.object_id)) AS count FROM $wpdb->dt_activity_log log
        INNER JOIN $wpdb->postmeta AS src ON ( log.object_id = src.post_id AND src.meta_key = 'sources' AND src.meta_value = 'transfer' )
        INNER JOIN $wpdb->postmeta AS id ON ( log.object_id = id.post_id AND id.meta_key = 'transfer_site_link_post_id' AND id.meta_value = %d )
        WHERE log.action = 'created'
        AND log.object_type = 'contacts'
        AND log.hist_time BETWEEN %d AND %d", $site_link_post_id, $start, $end ) );
        if ( ! empty( $total_transferred ) ) {
            $metrics['total'] = $total_transferred;
        }

        // Created Contact Current Statuses
        $statuses_current = $wpdb->get_results( $wpdb->prepare( "
        SELECT COUNT(DISTINCT(log.object_id)) AS count, os.meta_value AS status FROM $wpdb->dt_activity_log log
        INNER JOIN $wpdb->postmeta AS src ON ( log.object_id = src.post_id AND src.meta_key = 'sources' AND src.meta_value = 'transfer' )
        INNER JOIN $wpdb->postmeta AS id ON ( log.object_id = id.post_id AND id.meta_key = 'transfer_site_link_post_id' AND id.meta_value = %d )
        INNER JOIN $wpdb->postmeta AS os ON ( log.object_id = os.post_id AND os.meta_key = 'overall_status' )
        WHERE log.action = 'created'
        AND log.object_type = 'contacts'
        AND log.hist_time BETWEEN %d AND %d
        GROUP BY os.meta_value", $site_link_post_id, $start, $end ), ARRAY_A );
        foreach ( $statuses_current ?? [] as $row ) {
            if ( ! empty( $row['status'] && ! empty( $row['count'] ) ) ) {
                $metrics['statuses_current'][] = [
                    'status' => $field_settings['overall_status']['default'][ $row['status'] ]['label'],
                    'count'  => $row['count']
                ];
            }
        }

        // Status Contact Changes
        $statuses_changes = $wpdb->get_results( $wpdb->prepare( "
        SELECT COUNT(log.meta_value) AS count, log.meta_value AS status FROM $wpdb->dt_activity_log log
        INNER JOIN $wpdb->postmeta AS src ON ( log.object_id = src.post_id AND src.meta_key = 'sources' AND src.meta_value = 'transfer' )
        INNER JOIN $wpdb->postmeta AS id ON ( log.object_id = id.post_id AND id.meta_key = 'transfer_site_link_post_id' AND id.meta_value = %d )
        WHERE log.meta_key = 'overall_status'
        AND log.object_type = 'contacts'
        AND log.hist_time BETWEEN %d AND %d
        GROUP BY log.meta_value", $site_link_post_id, $start, $end ), ARRAY_A );
        foreach ( $statuses_changes ?? [] as $row ) {
            if ( ! empty( $row['status'] && ! empty( $row['count'] ) ) ) {
                $metrics['statuses_changes'][] = [
                    'status' => $field_settings['overall_status']['default'][ $row['status'] ]['label'],
                    'count'  => $row['count']
                ];
            }
        }

        // Created Contact Current Seeker Paths
        $seeker_paths_current = $wpdb->get_results( $wpdb->prepare( "
        SELECT b.meta_value AS seeker_path, COUNT( DISTINCT(a.ID) ) AS count
        FROM $wpdb->posts AS a
        JOIN $wpdb->postmeta AS b
        ON a.ID = b.post_id
            AND b.meta_key = 'seeker_path'
        JOIN $wpdb->postmeta AS c
        ON a.ID = c.post_id
            AND c.meta_key = 'sources'
            AND c.meta_value = 'transfer'
        JOIN $wpdb->postmeta AS d
        ON a.ID = d.post_id
            AND d.meta_key = 'transfer_site_link_post_id'
            AND d.meta_value = %d
        JOIN $wpdb->dt_activity_log AS log
        ON a.ID = log.object_id
            AND log.action = 'created'
            AND log.object_type = 'contacts'
            AND log.hist_time BETWEEN %d AND %d
        WHERE a.post_status = 'publish'
        AND a.post_type = 'contacts'
        AND a.ID NOT IN (
            SELECT post_id FROM $wpdb->postmeta
            WHERE meta_key = 'type' AND meta_value = 'user'
            GROUP BY post_id
        )
        GROUP BY b.meta_value", $site_link_post_id, $start, $end ), ARRAY_A );
        foreach ( $seeker_paths_current ?? [] as $row ) {
            if ( ! empty( $row['seeker_path'] && ! empty( $row['count'] ) ) ) {
                $metrics['seeker_paths_current'][] = [
                    'seeker_path' => $field_settings['seeker_path']['default'][ $row['seeker_path'] ]['label'],
                    'count'       => $row['count']
                ];
            }
        }

        // Seeker Path Contact Changes
        $seeker_paths_changes = $wpdb->get_results( $wpdb->prepare( "
        SELECT log.meta_value AS seeker_path, COUNT( log.meta_value ) AS count
        FROM $wpdb->posts AS a
        JOIN $wpdb->postmeta AS b
        ON a.ID = b.post_id
            AND b.meta_key = 'seeker_path'
        JOIN $wpdb->postmeta AS c
        ON a.ID = c.post_id
            AND c.meta_key = 'sources'
            AND c.meta_value = 'transfer'
        JOIN $wpdb->postmeta AS d
        ON a.ID = d.post_id
            AND d.meta_key = 'transfer_site_link_post_id'
            AND d.meta_value = %d
        JOIN $wpdb->dt_activity_log AS log
        ON a.ID = log.object_id
            AND log.object_type = 'contacts'
            AND log.meta_key = 'seeker_path'
            AND log.hist_time BETWEEN %d AND %d
        WHERE a.post_status = 'publish'
        AND a.post_type = 'contacts'
        AND a.ID NOT IN (
            SELECT post_id FROM $wpdb->postmeta
            WHERE meta_key = 'type' AND meta_value = 'user'
            GROUP BY post_id
        )
        GROUP BY log.meta_value", $site_link_post_id, $start, $end ), ARRAY_A );
        foreach ( $seeker_paths_changes ?? [] as $row ) {
            if ( ! empty( $row['seeker_path'] && ! empty( $row['count'] ) ) ) {
                $metrics['seeker_paths_changes'][] = [
                    'seeker_path' => $field_settings['seeker_path']['default'][ $row['seeker_path'] ]['label'],
                    'count'       => $row['count']
                ];
            }
        }

        // Created Contact Current Faith Milestones
        $milestones_current = $wpdb->get_results( $wpdb->prepare( "
        SELECT COUNT( DISTINCT(log.object_id) ) AS 'value', pm.meta_value AS milestones
        FROM $wpdb->dt_activity_log log
        INNER JOIN $wpdb->posts post
        ON (
            post.ID = log.object_id
            AND post.post_type = 'contacts'
            AND post.post_status = 'publish'
        )
        INNER JOIN $wpdb->postmeta pm
        ON (
            post.ID = pm.post_id
            AND pm.meta_key = 'milestones'
        )
        INNER JOIN $wpdb->postmeta id
        ON (
            post.ID = id.post_id
            AND id.meta_key = 'transfer_site_link_post_id'
            AND id.meta_value = %d
        )
        INNER JOIN $wpdb->postmeta src
        ON (
            post.ID = src.post_id
            AND src.meta_key = 'sources'
            AND src.meta_value = 'transfer'
        )
        WHERE log.action = 'created'
        AND log.object_type = 'contacts'
        AND log.hist_time BETWEEN %d AND %d
        GROUP BY pm.meta_value", $site_link_post_id, $start, $end ), ARRAY_A );
        foreach ( $milestones_current ?? [] as $row ) {
            if ( ! empty( $row['milestones'] && ! empty( $row['value'] ) ) ) {
                $metrics['milestones_current'][] = [
                    'milestone' => $field_settings['milestones']['default'][ $row['milestones'] ]['label'],
                    'count'     => $row['value']
                ];
            }
        }

        // Faith Milestone Contact Changes
        $milestones_changes = $wpdb->get_results( $wpdb->prepare( "
        SELECT COUNT(log.meta_value) AS 'value', log.meta_value AS milestones
        FROM $wpdb->dt_activity_log log
        INNER JOIN $wpdb->posts post
        ON (
            post.ID = log.object_id
            AND post.post_type = 'contacts'
            AND post.post_status = 'publish'
        )
        INNER JOIN $wpdb->postmeta id
        ON (
            post.ID = id.post_id
            AND id.meta_key = 'transfer_site_link_post_id'
            AND id.meta_value = %d
        )
        INNER JOIN $wpdb->postmeta src
        ON (
            post.ID = src.post_id
            AND src.meta_key = 'sources'
            AND src.meta_value = 'transfer'
        )
        WHERE log.object_type = 'contacts'
        AND log.meta_key = 'milestones'
        AND (log.meta_value != 'value_deleted')
        AND (log.meta_value != '')
        AND log.hist_time BETWEEN %d AND %d
        GROUP BY log.meta_value", $site_link_post_id, $start, $end ), ARRAY_A );
        foreach ( $milestones_changes ?? [] as $row ) {
            if ( ! empty( $row['milestones'] && ! empty( $row['value'] ) ) ) {
                $metrics['milestones_changes'][] = [
                    'milestone' => $field_settings['milestones']['default'][ $row['milestones'] ]['label'],
                    'count'     => $row['value']
                ];
            }
        }

        return $metrics;
    }

    public function receive_transfer_endpoint( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( ! current_user_can( 'create_contacts' ) ) {
            return new WP_Error( __METHOD__, 'Insufficient permissions' );
        }

        if ( isset( $params['contact_data'] ) ) {
            $result = self::receive_transferred_contact( $params );
            if ( is_wp_error( $result ) ) {
                return [
                    'status' => 'FAIL',
                    'error'  => $result->get_error_message(),
                ];
            } else {
                return $result;
            }
        } else {
            return [
                'status' => 'FAIL',
                'error'  => 'Missing required parameter'
            ];
        }
    }

    public function receive_transfer_comments_endpoint( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( ! current_user_can( 'create_contacts' ) ) {
            return new WP_Error( __METHOD__, 'Insufficient permissions' );
        }
        if ( ! isset( $params['comments'], $params['transfer_foreign_key'] ) ) {
            return new WP_Error( __METHOD__, 'Missing comments or transfer_foreign_key', [ 'status' => 400 ] );
        }

        global $wpdb;
        $post_id = $wpdb->get_var( $wpdb->prepare( "
            SELECT post_id
            FROM $wpdb->postmeta
            WHERE meta_value = %s
              AND meta_key = 'transfer_foreign_key'
              AND post_id = %s
            LIMIT 1", $params['transfer_foreign_key'], $params['post_id'] )
        );

        if ( empty( $post_id ) ) {
            return new WP_Error( __METHOD__, 'Could not find post to update', [ 'status' => 404 ] );
        }

        $insert_comments = self::insert_bulk_comments( $params['comments'], $post_id );

        return $insert_comments;
    }
}

Disciple_Tools_Contacts_Transfer::instance();

function dt_get_comments_with_redacted_user_data( $post_id ) {
    $comments = DT_Posts::get_post_comments( 'contacts', $post_id );
    if ( is_wp_error( $comments ) || ! isset( $comments['comments'] ) ) {
        return [];
    }
    $comments = $comments['comments'];
    if ( empty( $comments ) ) {
        return $comments;
    }
    $email_note    = __( 'redacted email', 'disciple_tools' );
    $name_note     = __( 'redacted name', 'disciple_tools' );
    $redacted_note = __( 'redacted', 'disciple_tools' );

    $users = get_users();

    foreach ( $comments as $index => $comment ) {
        $comment_content = $comment['comment_content'];

        // replace non-@mention references to login names, display names, or user emails
        foreach ( $users as $user ) {
            if ( ! empty( $user->data->user_login ) ) {
                $comment_content = str_replace( ' ' . $user->data->user_login, '(' . $name_note . ')', $comment_content );
                $comment_content = str_replace( $user->data->user_login . ' ', '(' . $name_note . ')', $comment_content );
            }
            if ( ! empty( $user->data->display_name ) ) {
                $comment_content = str_replace( ' ' . $user->data->display_name, '(' . $name_note . ')', $comment_content );
                $comment_content = str_replace( $user->data->display_name . ' ', '(' . $name_note . ')', $comment_content );
            }
            if ( ! empty( $user->data->user_nicename ) ) {
                $comment_content = str_replace( $user->data->user_nicename . ' ', '(' . $name_note . ')', $comment_content );
                $comment_content = str_replace( ' ' . $user->data->user_nicename, '(' . $name_note . ')', $comment_content );
            }
            if ( ! empty( $user->data->user_email ) ) {
                $comment_content = str_replace( $user->data->user_email, '(' . $email_note . ')', $comment_content );
            }
        }

        // replace @mentions
        preg_match_all( '/@[0-9a-zA-Z](\.?[0-9a-zA-Z])*/', $comment_content, $matches );
        foreach ( $matches[0] as $match_key => $match ) {
            $comment_content = str_replace( $match, '@' . $name_note, $comment_content );
        }

        // replace duplicate notes
        $comment_content = str_replace( site_url(), '#', $comment_content );

        $comments[ $index ]['comment_content'] = $comment_content;
    }

    return $comments;
}
