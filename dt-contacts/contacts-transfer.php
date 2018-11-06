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
    }

    // Adds the type of connection to the site link system
    public function site_link_type( $type ) {
        $type['contact_sharing'] = __( 'Contact Sharing', 'disciple_tools' );
        $type['contact_sending'] = __( 'Contact Sending Only', 'disciple_tools' );
        $type['contact_receiving'] = __( 'Contact Receiving Only', 'disciple_tools' );
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

    /**
     * Rest Endpoints
     * @see /dt-contacts/contacts-endpoints.php
     */

    /**
     * Section to display in the share panel for the transfer function
     *
     * @param $post_type
     */
    public function share_panel( $post_type ) {
        if ( 'contacts' === $post_type && current_user_can( 'view_all_contacts' ) ) {
            $list = Site_Link_System::get_list_of_sites_by_type( [ 'contact_sharing', 'contact_sending' ] );
            if ( empty( $list ) ) {
                return;
            }

            ?>
            <hr size="1px">
            <div class="grid-x">
                <div class="cell">
                    <h6><?php esc_html_e( 'Transfer this contact to:' ) ?></h6>
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

    public static function contact_transfer( $contact_id, $site_post_id ) {
        $errors = new WP_Error();

        $site = Site_Link_System::get_site_connection_vars( $site_post_id );
        if ( is_wp_error( $site ) ) {
            $errors->add( __METHOD__, 'Error creating site connection details.' );
            return $errors;
        }
        $args = [
            'method' => 'POST',
            'body' => [
                'transfer_token' => $site['transfer_token'],
                'contact_data' => [
                    'post' => get_post( $contact_id, ARRAY_A ),
                    'postmeta' => get_post_meta( $contact_id ),
                    'dt_activity_log' => self::get_activity_log_for_id( $contact_id ),
                    'comments' => get_comments( [ 'post_id' => $contact_id ] ),
                    'locations' => '', // @todo add locations titles so that they can be added to a commment
                    'people_groups' => '', // @todo add people groups plain text so that they can be added to a commment
                ],
            ]
        ];
        $result = wp_remote_post( 'https://' . $site['url'] . '/wp-json/dt-public/v1/contact/transfer', $args );

        if ( is_wp_error( $result ) ) {
            $errors->add( 'failed_remote_post', $result->get_error_message() );
            return $errors;
        }

        /**
         * Close current contact
         */
        $result_body = json_decode( $result['body'] );
        dt_write_log( $result_body );

        // log foreign key
        if ( isset( $result_body->foreign_key ) ) {
            $key_result = update_post_meta( $contact_id, 'transfer_foreign_key', $result_body->foreign_key );
            if ( is_wp_error( $key_result ) ) {
                $errors->add( __METHOD__, $result->get_error_message() );
            }
        }

        // add note that the record was transferred
        $time = current_time( 'mysql' );
        $comment_result = wp_insert_comment([
            'comment_post_ID' => $contact_id,
            'comment_content' => sprintf( 'This contact was transferred to %s for further follow-up.', esc_attr( get_the_title( $site_post_id ) ) ),
            'comment_type' => '',
            'comment_parent' => 0,
            'user_id' => get_current_user_id(),
            'comment_date' => $time,
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

    public static function get_activity_log_for_id( $id ) {
        global $wpdb;
        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->dt_activity_log WHERE object_id = %s", $id ), ARRAY_A );
        return $results;
    }

    public static function simplify_meta_array( $array ) {
        return array_map( function ( $a ) {
            if ( isset( $a[1] ) ) {
                return $a;
            } else {
                return $a[0];
            }

        }, $array );
    }

    public static function receive_transferred_contact( $params ) {
        dt_write_log( __METHOD__ );
        dt_write_log( $params );

        $contact_data = $params['contact_data'];
        $comment_data = $contact_data['comments'];
        $meta_input = [];
        $lagging_meta_input = [];
        $errors = new WP_Error();

        /**
         * Insert contact record
         */

        // get site connection data
        $site_key = Site_Link_System::decrypt_transfer_token( $params['transfer_token'] );
        $site_link_post_id = Site_Link_System::get_post_id_by_site_key( $site_key );

        // set post elements
        $post_args = $contact_data['post'];

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

        // add transfer elements
        $post_args['meta_input']['transfer_id'] = $post_args['ID'];
        unset( $post_args['ID'] );
        $post_args['meta_input']['transfer_guid'] = $post_args['guid'];
        unset( $post_args['guid'] );
        $post_args['meta_input']['transfer_foreign_key'] = Site_Link_System::generate_token();
        $post_args['meta_input']['transfer_site_link_post_id'] = $site_link_post_id;

        // insert post
        $post_id = wp_insert_post( $post_args );
        if ( is_wp_error( $post_id ) ) {
            $errors->add( 'transfer_insert_fail', 'Failed to create transfer contact for '. $post_args['ID'] );
            return [
                'status' => 'FAIL',
                'foreign_key' => '',
                'errors' => $errors,
            ];
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
                $comment['comment_post_ID'] = $post_id;
                $comment['user_id'] = dt_get_base_user( true );
                $comment['comment_approved'] = 1;
                unset( $comment['comment_ID'] );
                unset( $comment['comment_author'] );

                $comment_id = wp_insert_comment( $comment );
                if ( is_wp_error( $comment_id ) ) {
                    $errors->add( 'comment_insert_fail', 'Comment insert fail for '. $comment['comment_ID'] );
                }
            }
        }

        // Add transfer record comment
        $transfer_comment = wp_insert_comment([
                'comment_post_ID' => $post_id,
                'user_id' => dt_get_base_user( true ),
                'comment_approved' => 1,
                'comment_content' => __( 'Contact transferred from site' ) . ' "' . esc_html( get_the_title( $site_link_post_id ) ) . '"',
        ]);
        if ( is_wp_error( $transfer_comment ) ) {
            $errors->add( 'comment_insert_fail', 'Comment insert fail for transfer notation.' );
        }


        dt_write_log( $post_id );
        return [
            'status' => 'OK',
            'foreign_key' => $post_args['meta_input']['transfer_foreign_key'],
            'errors' => $errors
        ];
    }
}
Disciple_Tools_Contacts_Transfer::instance();





