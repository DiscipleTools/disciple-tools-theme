<?php
declare(strict_types=1);

/* Fetch and print the first 29 results only, list-contacts.js will fetch all
 * the results using AJAX and will replace the list. */

$query1 = Disciple_Tools_Contacts::get_user_contacts(
    get_current_user_id(), true, [ 'posts_per_page' => 29, 'orderby' => 'ID' ]
);
if (is_wp_error( $query1 )) {
    throw new Exception( "permission denied: " . implode( $query1->get_error_messages() ) );
}


?>
<div class="bordered-box">
    <h3><?php _e( "Contacts" ); ?></h3>

    <?php if ( $query1->have_posts() ) : ?>
        <table class="js-list-contacts">
            <thead><tr>
                <th></th>
                <th><?php _e( "Name" ); ?></th>
                <th><?php _e( "Status" ); ?></th>
                <th><?php _e( "Faith Milestones" ); ?></th>
                <th><?php _e( "Assigned to" ); ?></th>
                <th><?php _e( "Location" ); ?></th>
                <th><?php _e( "Group" ); ?></th>
            </tr></thead>
            <tbody>
                <tr class="js-list-contacts-loading"><td colspan=7><?php _e( "Loading..." ); ?></td></tr>
            </tbody>
        </table>

    <?php else: ?>
        <p><?php _e( "No contacts found." ); ?></p>
    <?php endif; ?>

</div>
