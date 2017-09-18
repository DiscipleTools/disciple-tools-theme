<?php
declare(strict_types=1);

?>

<?php get_header(); ?>

<?php dt_print_breadcrumbs( null, __( "Groups" ) ); ?>

    <div id="content">

        <div id="inner-content" class="grid-x grid-margin-x">

            <aside class="large-3 cell padding-bottom hide-for-small-only">
                <div class="bordered-box js-pane-filters">
                    <?php /* Javascript my move .js-filters-modal-content to this location. */ ?>
                </div>
            </aside>

            <div class="reveal js-filters-modal" id="filters-modal" data-reveal>
                <div class="js-filters-modal-content">
                    <h5><?php _e( "Filters" ); ?></h5>
                    <div class="filter js-list-filter" data-filter="group_status">
                        <div class="filter__title js-list-filter-title" tabindex="0"><?php _e( "Group status" ); ?></div>
                        <p><?php _e( "Loading..." ); ?></p>
                    </div>
                    <div class="filter filter--closed js-list-filter" data-filter="locations">
                        <div class="filter__title js-list-filter-title" tabindex="0"><?php _e( "Location" ); ?></div>
                        <p><?php _e( "Loading..." ); ?></p>
                    </div>
                </div>
                <button class="close-button" data-close aria-label="<?php _e( "Close modal" ); ?>" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <main id="main" class="large-9 cell padding-bottom" role="main">

                <?php get_template_part( 'parts/content', 'groups' ); ?>

            </main> <!-- end #main -->

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>
