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


            <aside class="large-3 medium-3 columns padding-bottom">
                <div class="bordered-box">
                    <h3><?php _e( "Filters" ); ?></h3>
                    <details class="js-contacts-filter" data-filter="status">
                        <summary><?php _e( "Status" ); ?></summary>
                        <p><?php _e( "Loading..." ); ?></p>
                    </details>
                    <details class="js-contacts-filter" data-filter="locations">
                        <summary><?php _e( "Locations" ); ?></summary>
                        <p><?php _e( "Loading..." ); ?></p>
                    </details>
                </div>

            </aside> <!-- end #aside -->

            <main id="main" class="large-6 medium-6 columns padding-bottom" role="main">

                <?php get_template_part( 'parts/content', 'contacts' ); ?>

            </main> <!-- end #main -->

            <aside class="large-3 medium-3 columns padding-bottom" data-sticky-container>
                <div class="bordered-box">
                    <h3><?php _e( "Priorities" ); ?></h3>

                </div>
            </aside>


        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->


<?php get_footer(); ?>
