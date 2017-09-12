<?php
declare(strict_types=1);

$contacts_update_needed = Disciple_Tools_Contacts::get_user_prioritized_contacts( get_current_user_id(), 'update_needed', true, [ 'posts_per_page' => 5 ] );
$contacts_meeting_scheduled = Disciple_Tools_Contacts::get_user_prioritized_contacts( get_current_user_id(), 'meeting_scheduled', true, [ 'posts_per_page' => 5 ] );
$contacts_contact_unattempted = Disciple_Tools_Contacts::get_user_prioritized_contacts( get_current_user_id(), 'contact_unattempted', true, [ 'posts_per_page' => 5 ] );

?>

<?php get_header(); ?>

    <div id="content">

        <!-- Breadcrumb Navigation-->
        <nav aria-label="You are here:" role="navigation" class="second-bar hide-for-small-only">
            <ul class="breadcrumbs">
                <li><a href="<?php echo home_url( '/' ); ?>">Dashboard</a></li>
                <li>
                    <span class="show-for-sr">Current: </span> Groups
                </li>
            </ul>
        </nav>

        <div id="inner-content" class="grid-x grid-margin-x">

            <aside class="large-3 cell padding-bottom" data-sticky-container>
                <div class="bordered-box">

                    <h5><?php _e( "Priorities" ); ?></h5>

                    <h6>
                        <?php _e( "Update needed" ); ?>
                        (<?php echo $contacts_update_needed->found_posts; ?>)
                        <?php if ($contacts_update_needed->found_posts > $contacts_update_needed->post_count): ?>
                            <a href="<?php echo get_post_type_archive_link( "contacts" ); ?>" class="priorities-show-all"><span><?php _e( "All contacts" ) ?></span></a>
                        <?php endif; ?>
                    </h6>
                    <?php if ( $contacts_update_needed->have_posts() ): ?>
                        <ul>
                        <?php while ( $contacts_update_needed->have_posts() ): $contacts_update_needed->the_post(); ?>
                            <li><a href="<?php the_permalink(); ?>"><?php the_title_attribute(); ?></a></li>
                        <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <ul>
                            <li><?php _e( "Well done! You have no contacts that need to be updated." ); ?></li>
                        </ul>
                    <?php endif; ?>

                    <h6>
                        <?php _e( "Meeting scheduled" ); ?>
                        (<?php echo $contacts_meeting_scheduled->found_posts; ?>)
                        <?php if ($contacts_meeting_scheduled->found_posts > $contacts_meeting_scheduled->post_count): ?>
                            <a href="<?php echo get_post_type_archive_link( "contacts" ); ?>" class="priorities-show-all"><span><?php _e( "All contacts" ); ?></span></a>
                        <?php endif; ?>
                    </h6>
                    <?php if ( $contacts_meeting_scheduled->have_posts() ): ?>
                        <ul>
                        <?php while ( $contacts_meeting_scheduled->have_posts() ): $contacts_meeting_scheduled->the_post(); ?>
                            <li><a href="<?php the_permalink(); ?>"><?php the_title_attribute(); ?></a></li>
                        <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <ul>
                            <li><?php _e( "You have no contacts that are waiting for a scheduled meeting." ); ?></li>
                        </ul>
                    <?php endif; ?>

                    <h6>
                        <?php _e( "Contact unattempted" ); ?>
                        (<?php echo $contacts_contact_unattempted->found_posts; ?>)
                        <?php if ($contacts_contact_unattempted->found_posts > $contacts_contact_unattempted->post_count): ?>
                            <a href="<?php echo get_post_type_archive_link( "contacts" ); ?>" class="priorities-show-all"><span><?php _e( "All contacts" ); ?></span></a>
                        <?php endif; ?>
                    </h6>
                    <?php if ( $contacts_contact_unattempted->have_posts() ): ?>
                        <ul>
                        <?php while ( $contacts_contact_unattempted->have_posts() ): $contacts_contact_unattempted->the_post(); ?>
                            <li><a href="<?php the_permalink(); ?>"><?php the_title_attribute(); ?></a></li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <ul>
                            <li><?php _e( "Well done! You have no contacts that are waiting to be contacted." ); ?></li>
                        </ul>
                    <?php endif; ?>

                </div>

            </aside>

            <main id="main" class="large-6 cell padding-bottom" role="main">

                <?php get_template_part( 'parts/content', 'groups' ); ?>

            </main> <!-- end #main -->

            <aside class="large-3 cell padding-bottom hide-for-small-only">
                <div class="bordered-box js-pane-filters">
                    <?php /* Javascript my move .js-filters-modal-content to this location. */ ?>
                </div>
            </aside>

            <div class="reveal js-filters-modal" id="filters-modal" data-reveal>
                <div class="js-filters-modal-content">
                    <h5><?php _e( "Filters" ); ?></h5>
                    <div>
                        <button disabled class="button small js-clear-filters"><?php _e( "Clear filters" ); ?></button>
                    </div>
                    <div class="filter js-list-filter" data-filter="group_status">
                        <div class="filter__title js-list-filter-title"><?php _e( "Group status" ); ?></div>
                        <p><?php _e( "Loading..." ); ?></p>
                    </div>
                    <div class="filter filter--closed js-list-filter" data-filter="locations">
                        <div class="filter__title js-list-filter-title"><?php _e( "Location" ); ?></div>
                        <p><?php _e( "Loading..." ); ?></p>
                    </div>
                </div>
                <button class="close-button" data-close aria-label="<?php _e( "Close modal" ); ?>" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>
