<?php
declare(strict_types=1);
?>

<?php get_header(); ?>

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
                    <h5><?php esc_html_e( 'Filters', "disciple_tools" ); ?></h5>
                    <div class="list-views">
                        <?php if (user_can( get_current_user_id(), 'view_any_contacts' ) ){ ?>

                            <label class="list-view">
                                <input type="radio" name="view" value="all_contacts" class="js-list-view">
                                <?php esc_html_e( "All contacts", "disciple_tools" ); ?>
                                <span class="list-view__count js-list-view-count" data-value="all_contacts">.</span>
                            </label>
                        <?php } ?>

                        <label class="list-view">
                            <input type="radio" name="view" value="my_contacts" class="js-list-view" checked>
                            <?php esc_html_e( "My contacts", "disciple_tools" ); ?>
                            <span class="list-view__count js-list-view-count" data-value="my_contacts">.</span>
                        </label>
                        <div class="list-views__sub">
                            <?php if (user_can( get_current_user_id(), 'view_any_contacts' ) ){ ?>

                                <label class="list-view">
                                    <input type="radio" name="view" value="assignment_needed" class="js-list-view">
                                    <?php esc_html_e( "Assignment needed", "disciple_tools" ); ?>
                                    <span class="list-view__count js-list-view-count" data-value="assignment_needed">.</span>
                                </label>
                            <?php } ?>
                            <label class="list-view">
                                <input type="radio" name="view" value="update_needed" class="js-list-view">
                                <?php esc_html_e( "Update needed", "disciple_tools" ); ?>
                                <span class="list-view__count js-list-view-count" data-value="update_needed">.</span>
                            </label>
                            <label class="list-view">
                                <input type="radio" name="view" value="meeting_scheduled" class="js-list-view">
                                <?php esc_html_e( "Meeting scheduled", "disciple_tools" ); ?>
                                <span class="list-view__count js-list-view-count" data-value="meeting_scheduled">.</span>
                            </label>
                            <label class="list-view">
                                <input type="radio" name="view" value="contact_unattempted" class="js-list-view">
                                <?php esc_html_e( "Contact unattempted", "disciple_tools" ); ?>
                                <span class="list-view__count js-list-view-count" data-value="contact_unattempted">.</span>
                            </label>
                        </div>
                        <label class="list-view">
                            <input type="radio" name="view" value="contacts_shared_with_me" class="js-list-view">
                            <?php esc_html_e( "Contacts shared with me", "disciple_tools" ); ?>
                            <span class="list-view__count js-list-view-count" data-value="contacts_shared_with_me">.</span>
                        </label>
                    </div>

                    <h5><?php esc_html_e( "Sub-filters", "disciple_tools" ); ?></h5>
                    <div class="filter js-list-filter" data-filter="assigned_login">
                        <div class="filter__title js-list-filter-title" tabindex="0"><?php esc_html_e( "Assigned to" ); ?></div>
                        <p><?php esc_html_e( "Loading...", "disciple_tools" ); ?></p>
                    </div>
                    <div class="filter filter--closed js-list-filter" data-filter="overall_status">
                        <div class="filter__title js-list-filter-title" tabindex="0"><?php esc_html_e( "Status" ); ?></div>
                        <p><?php esc_html_e( "Loading...", "disciple_tools" ); ?></p>
                    </div>
                    <div class="filter filter--closed js-list-filter" data-filter="locations">
                        <div class="filter__title js-list-filter-title" tabindex="0"><?php esc_html_e( "Locations" ); ?></div>
                        <p><?php esc_html_e( "Loading...", "disciple_tools" ); ?></p>
                    </div>
                    <div class="filter filter--closed js-list-filter" data-filter="seeker_path">
                        <div class="filter__title js-list-filter-title" tabindex="0"><?php esc_html_e( "Seeker path" ); ?></div>
                        <p><?php esc_html_e( "Loading...", "disciple_tools" ); ?></p>
                    </div>
                    <div class="filter filter--closed js-list-filter" data-filter="requires_update">
                        <div class="filter__title js-list-filter-title" tabindex="0"><?php esc_html_e( "Update needed" ); ?></div>
                        <p><?php esc_html_e( "Loading...", "disciple_tools" ); ?></p>
                    </div>
                </div>
                <button class="close-button" data-close aria-label="<?php esc_html_e( "Close modal" ); ?>" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <main id="main" class="large-9 cell padding-bottom" role="main">

                <?php get_template_part( 'dt-assets/parts/content', 'contacts' ); ?>

            </main> <!-- end #main -->


        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->


<?php get_footer(); ?>
