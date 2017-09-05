<?php

$contacts_update_needed = Disciple_Tools_Contacts::get_user_prioritized_contacts( get_current_user_id(), 'update_needed', true, [ 'posts_per_page' => 5 ] );
$contacts_meeting_scheduled = Disciple_Tools_Contacts::get_user_prioritized_contacts( get_current_user_id(), 'meeting_scheduled', true, [ 'posts_per_page' => 5 ] );
$contacts_contact_unattempted = Disciple_Tools_Contacts::get_user_prioritized_contacts( get_current_user_id(), 'contact_unattempted', true, [ 'posts_per_page' => 5 ] );

?>

<?php get_header(); ?>

    <div id="content">

        <!-- Breadcrumb Navigation-->
        <nav aria-label="You are here:" role="navigation" class="hide-for-small-only">
            <ul class="breadcrumbs">
                <li><a href="<?php echo home_url( '/' ); ?>">Dashboard</a></li>
                <li>
                    <span class="show-for-sr">Current: </span> Contacts
                </li>
            </ul>
        </nav>

        <div id="inner-content" class="grid-x grid-margin-x">

            <aside class="large-2 medium-2 cell padding-bottom">
                <div class="bordered-box">
                    <h3><?php _e( "Filters" ); ?></h3>
                    <div>
                        <button class="button small js-my-contacts"><?php _e( "My contacts" ); ?></button>
                        <button disabled class="button small js-clear-filters"><?php _e( "Clear filters" ); ?></button>
                    </div>
                    <div class="filter js-contacts-filter" data-filter="assigned_login">
                        <div class="filter__title js-contacts-filter-title"><?php _e( "Assigned to" ); ?></div>
                        <p><?php _e( "Loading..." ); ?></p>
                    </div>
                    <div class="filter filter--closed js-contacts-filter" data-filter="overall_status">
                        <div class="filter__title js-contacts-filter-title"><?php _e( "Status" ); ?></div>
                        <p><?php _e( "Loading..." ); ?></p>
                    </div>
                    <div class="filter filter--closed js-contacts-filter" data-filter="locations">
                        <div class="filter__title js-contacts-filter-title"><?php _e( "Locations" ); ?></div>
                        <p><?php _e( "Loading..." ); ?></p>
                    </div>
                    <div class="filter filter--closed js-contacts-filter" data-filter="seeker_path">
                        <div class="filter__title js-contacts-filter-title"><?php _e( "Seeker path" ); ?></div>
                        <p><?php _e( "Loading..." ); ?></p>
                    </div>
                    <div class="filter filter--closed js-contacts-filter" data-filter="requires_update">
                        <div class="filter__title js-contacts-filter-title"><?php _e( "Update needed" ); ?></div>
                        <p><?php _e( "Loading..." ); ?></p>
                    </div>
                </div>

            </aside>

            <main id="main" class="large-8 medium-8 cell padding-bottom" role="main">

                <?php get_template_part( 'parts/content', 'contacts' ); ?>

            </main> <!-- end #main -->

            <aside class="large-2 medium-2 cell padding-bottom"  data-sticky-container>
                <div class="bordered-box">
                    <h3><?php _e( "Priorities" ); ?></h3>

                    <h6>
                        <?php _e( "Update needed" ); ?>
                        (<?php echo $contacts_update_needed->found_posts; ?>)
                        <?php if ($contacts_update_needed->found_posts > $contacts_update_needed->post_count): ?>
                            <a href="#" class="priorities-show-all js-priorities-show" data-priority="update_needed"><span>Show all</span></a>
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
                            <a href="#" class="priorities-show-all js-priorities-show" data-priority="meeting_scheduled"><span>Show all</span></a>
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
                            <a href="#" class="priorities-show-all js-priorities-show" data-priority="contact_unattempted"><span>Show all</span></a>
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


        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->


<?php get_footer(); ?>
