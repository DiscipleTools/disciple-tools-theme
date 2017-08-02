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

    <div class="row js-search-tools faded-out">
        <div class="medium-6 columns">
            <input type="search" disabled class="js-list-contacts-search">
        </div>
        <div class="medium-6 columns">
            <button class="button small js-list-contacts-sort" data-sort="post_title" disabled><?php _e( "Sort by name " ); ?></button>
            <button class="button small js-list-contacts-sort" data-sort="assigned_name" disabled><?php _e( "Sort by team" ); ?></button>
        </div>

    </div>

    <?php if ( $query1->have_posts() ) : ?>
        <ul class="js-list-contacts">

            <?php while ( $query1->have_posts() ) : $query1->the_post(); ?>

                <!-- To see additional archive styles, visit the /parts directory -->
                <?php get_template_part( 'parts/loop', 'contacts' ); ?>

            <?php endwhile; ?>

            <li class="js-list-contacts-loading"><?php _e( "Loading..." ); ?></li>

        </ul>

    <?php else: ?>
        <p><?php _e( "No contacts found." ); ?></p>
    <?php endif; ?>

</div>
