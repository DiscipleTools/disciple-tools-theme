<?php get_header(); ?>

    <div id="content">

        <div id="inner-content">

            <!-- Breadcrumb Navigation-->
            <nav aria-label="You are here:" role="navigation" class="hide-for-small-only">
                <ul class="breadcrumbs">
                    <li><a href="<?php echo home_url( '/' ); ?>">Dashboard</a></li>
                    <li>
                        <span class="show-for-sr">Current: </span> Contacts
                    </li>
                </ul>
            </nav>


            <aside class="large-2 medium-2 columns padding-bottom">
                <div class="bordered-box">
                    <h3><?php _e( "Filters" ); ?></h3>
                    <div class="filter js-contacts-filter" data-filter="assigned_login">
                        <div class="filter__title js-contacts-filter-title"><?php _e( "Assigned to" ); ?></div>
                        <p><?php _e( "Loading..." ); ?></p>
                    </div>
                    <div class="filter filter--closed js-contacts-filter" data-filter="status">
                        <div class="filter__title js-contacts-filter-title"><?php _e( "Status" ); ?></div>
                        <p><?php _e( "Loading..." ); ?></p>
                    </div>
                    <div class="filter filter--closed js-contacts-filter" data-filter="locations">
                        <div class="filter__title js-contacts-filter-title"><?php _e( "Locations" ); ?></div>
                        <p><?php _e( "Loading..." ); ?></p>
                    </div>
                </div>

            </aside> <!-- end #aside -->

            <main id="main" class="large-8 medium-8 columns padding-bottom" role="main">

                <?php get_template_part( 'parts/content', 'contacts' ); ?>

            </main> <!-- end #main -->

            <aside class="large-2 medium-2 columns padding-bottom" data-sticky-container>
                <div class="bordered-box">
                    <h3><?php _e( "Priorities" ); ?></h3>

                </div>
            </aside>


        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->


<?php get_footer(); ?>
