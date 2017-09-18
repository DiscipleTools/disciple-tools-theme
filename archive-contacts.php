<?php
declare(strict_types=1);
?>

<?php get_header(); ?>

<?php dt_print_breadcrumbs( null, __( "Contacts" ) ); ?>

<div id="errors"> </div>

    <div id="content">

        <div id="inner-content" class="grid-x grid-margin-x">

            <aside class="large-3 cell padding-bottom hide-for-small-only">
                <div class="bordered-box js-pane-filters">
                    <?php /* Javascript may move .js-filters-modal-content to this location. */ ?>
                </div>
            </aside>

            <div class="reveal js-filters-modal" id="filters-modal" data-reveal>
                <div class="js-filters-modal-content">
                    <h5><?php _e( "Views" ); ?></h5>
                    <div class="list-views">
                        <label style="cursor: pointer;">
                            <input type="radio" name="view" value="my_contacts" class="js-list-view">
                            <?php _e( "My contacts" ); ?>
                            <span class="js-list-view-count" data-value="my_contacts" style="float: right">.</span>
                        </label>
                        <label style="cursor: pointer;">
                            <input type="radio" name="view" value="my_priorities" class="js-list-view" style="margin-left: 20px">
                            <?php _e( "My priorities" ); ?>
                            <span class="js-list-view-count" data-value="my_priorities" style="float: right">.</span>
                        </label>
                        <label style="cursor: pointer;">
                            <input type="radio" name="view" value="update_needed" class="js-list-view" style="margin-left: 40px">
                            <?php _e( "Updated needed" ); ?>
                            <span class="js-list-view-count" data-value="update_needed" style="float: right">.</span>
                        </label>
                        <label style="cursor: pointer;">
                            <input type="radio" name="view" value="meeting_scheduled" class="js-list-view" style="margin-left: 40px">
                            <?php _e( "Meeting scheduled" ); ?>
                            <span class="js-list-view-count" data-value="meeting_scheduled" style="float: right">.</span>
                        </label>
                        <label style="cursor: pointer;">
                            <input type="radio" name="view" value="contact_unattempted" class="js-list-view" style="margin-left: 40px">
                            <?php _e( "Contact unattempted" ); ?>
                            <span class="js-list-view-count" data-value="contact_unattempted" style="float: right">.</span>
                        </label>
                        <label style="cursor: pointer;">
                            <input type="radio" name="view" value="all_contacts" class="js-list-view" checked>
                            <?php _e( "All contacts" ); ?>
                            <span class="js-list-view-count" data-value="all_contacts" style="float: right">.</span>
                        </label>
                    </div>

                    <h5><?php _e( "Filters" ); ?></h5>
                    <div class="filter js-list-filter" data-filter="assigned_login">
                        <div class="filter__title js-list-filter-title" tabindex="0"><?php _e( "Assigned to" ); ?></div>
                        <p><?php _e( "Loading..." ); ?></p>
                    </div>
                    <div class="filter filter--closed js-list-filter" data-filter="overall_status">
                        <div class="filter__title js-list-filter-title" tabindex="0"><?php _e( "Status" ); ?></div>
                        <p><?php _e( "Loading..." ); ?></p>
                    </div>
                    <div class="filter filter--closed js-list-filter" data-filter="locations">
                        <div class="filter__title js-list-filter-title" tabindex="0"><?php _e( "Locations" ); ?></div>
                        <p><?php _e( "Loading..." ); ?></p>
                    </div>
                    <div class="filter filter--closed js-list-filter" data-filter="seeker_path">
                        <div class="filter__title js-list-filter-title" tabindex="0"><?php _e( "Seeker path" ); ?></div>
                        <p><?php _e( "Loading..." ); ?></p>
                    </div>
                    <div class="filter filter--closed js-list-filter" data-filter="requires_update">
                        <div class="filter__title js-list-filter-title" tabindex="0"><?php _e( "Update needed" ); ?></div>
                        <p><?php _e( "Loading..." ); ?></p>
                    </div>
                </div>
                <button class="close-button" data-close aria-label="<?php _e( "Close modal" ); ?>" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <main id="main" class="large-9 cell padding-bottom" role="main">

                <?php get_template_part( 'parts/content', 'contacts' ); ?>

            </main> <!-- end #main -->


        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->


<?php get_footer(); ?>
