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
        add_filter( 'site_link_type_capabilities', [ $this, 'site_link_capabilities' ], 10, 2 );
    }

    // Adds the type of connection to the site link system
    public function site_link_type( $type ) {
        $type['contact_sharing'] = __( 'Contact Sharing', 'disciple_tools' );
        $type['contact_sending'] = __( 'Contact Sending Only', 'disciple_tools' );
        $type['contact_receiving'] = __( 'Contact Receiving Only', 'disciple_tools' );
        return $type;
    }

    // Add the specific capabilities needed for the site to site linking.
    public function site_link_capabilities( $connection_type, $capabilities ) {
        if ( 'contact_sharing' === $connection_type ) {
            $capabilities[] = 'create_contacts';
        }
        if ( 'contact_receiving' === $connection_type ) {
            $capabilities[] = 'create_contacts';
        }

        return $capabilities;
    }

    /**
     * Section to display in the share panel for the transfer function
     *
     * @param $post_type
     */
    public function share_panel( $post_type ) {
        if ( 'contacts' === $post_type ) {
            ?>
            <div class="grid-x">
                <div class="cell">
                    Test
                </div>
            </div>
            <?php
        }
    }

    public function get_available_transfer_sites() {
        return Site_Link_System::get_list_of_sites_by_type( [ 'contact_sharing', 'contact_sending' ] );
    }
}
Disciple_Tools_Contacts_Transfer::instance();





