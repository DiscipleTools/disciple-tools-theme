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

    public static function simplify_meta_array( $id ) {
        return array_map( function ( $a ) { return $a[0];
        }, get_post_meta( $id ) );
    }

    public static function contact_transfer( $contact_id, $site_post_id ) {

        $simplified_post_meta = self::simplify_meta_array( $contact_id );

        $site = Site_Link_System::get_site_connection_vars( $site_post_id );
        if ( is_wp_error( $site ) ) {
            return new WP_Error( __METHOD__, 'Error creating site connection details.' );
        }
        $args = [
            'method' => 'POST',
            'body' => [
                'transfer_token' => $site['transfer_token'],
                'contact_data' => [
                    'post' => get_post( $contact_id, ARRAY_A ),
                    'postmeta' => $simplified_post_meta,
                    'dt_activity_log' => self::get_activity_log_for_id( $contact_id )
                ],
            ]
        ];
        $result = wp_remote_post( 'https://' . $site['url'] . '/wp-json/dt-public/v1/contact/transfer', $args );
        if ( is_wp_error( $result ) ) {
            return new WP_Error( 'failed_remote_post', $result->get_error_message() );
        } else {
            return $result['body'];
        }
    }

    public static function get_activity_log_for_id( $id ) {
        global $wpdb;
        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->dt_activity_log WHERE object_id = %s", $id ), ARRAY_A );
        return $results;
    }

    public static function receive_transferred_contact( $contact_data ) {
        dt_write_log( 'Made it' );
        dt_write_log( __METHOD__ );
        dt_write_log( $contact_data );

        $post_args = $contact_data['post'];
        $post_args['meta_input'] = $contact_data['postmeta'];

        // convert user and assignment
        $post_args['post_author'] = dt_get_base_user();
        $post_args['meta_input']['assigned_to'] = "user-" . dt_get_base_user();
        $post_args['meta_input']['overall_status'] = "unassigned" . dt_get_base_user();


        $post_args['meta_input']['transfer_id'] = $post_args['ID'];
        unset( $post_args['ID'] );
        $post_args['meta_input']['transfer_guid'] = $post_args['guid'];
        unset( $post_args['guid'] );


        $post_id = wp_insert_post( $post_args );

        // Install contact
        // Install contact meta
        // Install activity

        $foreign_key = Site_Link_System::generate_token();
        // create, save, and return transfer success key/ foreign key
        // save to meta

        // return
        return $foreign_key;
    }
}
Disciple_Tools_Contacts_Transfer::instance();





