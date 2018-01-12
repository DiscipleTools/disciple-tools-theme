<?php

/**
 * Disciple_Tools_Metabox_Share_Contact
 *
 * @class   Disciple_Tools_Metabox_Share_Contact
 * @version 0.1.0
 * @since   0.1.0
 * @package Disciple_Tools
 *
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * @return \Disciple_Tools_Metabox_Share_Contact
 */
function dt_share_contact_metabox()
{
    return new Disciple_Tools_Metabox_Share_Contact();
}

/**
 * Class Disciple_Tools_Metabox_Share_Contact
 */
class Disciple_Tools_Metabox_Share_Contact
{

    /**
     * Constructor function.
     *
     * @access public
     * @since  0.1.0
     */
    public function __construct()
    {
    } // End __construct()

    /**
     * Contents for the Sharing Metabox
     */
    public function content_display( $post_id )
    {
        $shared_with_list = Disciple_Tools_Contacts::get_shared_with( 'contacts', $post_id );
        if ( !empty( $shared_with_list ) ) { ?>

            <strong>Sharing with:</strong>

            <form method="post">

                <input type="hidden" name="dt_remove_shared_noonce" id="dt_remove_shared_noonce"
                       value="<?php echo esc_html( wp_create_nonce( 'dt_remove_shared' ) ) ?>"/>
                <ul>
                    <?php foreach ( $shared_with_list as $contact ): ?>

                        <li>
                            <a href="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' ) ) . esc_attr( $contact['user_id'] ) ?> "><?php echo esc_attr( $contact['display_name'] ) ?></a>
                        </li>

                    <?php endforeach; ?>
                </ul>
            </form>

            <?php
        } else {

            echo 'Not shared with any other user';
        }
    }
}
