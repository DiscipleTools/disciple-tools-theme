<?php
/**
 * Presenter template for theme support
 *
 * @package  Disciple_Tools
 * @category Plugin
 * @author   Chasm.Solutions & Kingdom.Training
 * @since    0.1.0
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

/** Functions to output data for the theme. @see Buddypress bp-members-template.php or bp-groups-template.php for an example of the role of this page */

/**
 * @return void
 */
function dt_get_group_edit_form()
{

    if ( class_exists( 'Disciple_Tools' ) ) {

        // Create the title field
        ?>
        <input type="hidden" name="dt_contacts_noonce" id="dt_contacts_noonce"
               value="<?php echo esc_attr( wp_create_nonce( 'update_dt_groups' ) ) ?>"/>
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <td scope="row"><label for="post_title">Title</label></td>
                <td><input name="post_title" type="text" id="post_title" class="regular-text"
                           value="<?php echo esc_html( get_the_title() ); ?>"/>
                </td>
            </tr>
            </tbody>
        </table>
        <?php

        // Call the metadata fields
        $group = Disciple_Tools_Groups_Post_Type::instance();

        $group->load_type_meta_box(); // prints

    } // end if class exists

}

/**
 * Save contact
 *
 * @param $post
 */
function dt_save_group( $post )
{
    if ( class_exists( 'Disciple_Tools' ) ) {

        if ( $post['post_title'] != get_the_title() ) {
            $my_post = [
                'ID'         => get_the_ID(),
                'post_title' => $post['post_title'],
            ];
            wp_update_post( $my_post );
        }

        $group = Disciple_Tools_Groups_Post_Type::instance();
        $group->meta_box_save( get_the_ID() );

        wp_redirect( get_permalink() );
    }
}
