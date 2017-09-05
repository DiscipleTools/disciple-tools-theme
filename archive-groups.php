<?php get_header(); ?>

    <div id="content">

        <!-- Breadcrumb Navigation-->
        <nav aria-label="You are here:" role="navigation" class="hide-for-small-only">
            <ul class="breadcrumbs">
                <li><a href="<?php echo home_url( '/' ); ?>">Dashboard</a></li>
                <li>
                    <span class="show-for-sr">Current: </span> Groups
                </li>
            </ul>
        </nav>

        <div id="inner-content" class="grid-x grid-margin-x">
            <aside class="large-2 medium-2 cell padding-bottom">
                <div class="bordered-box">
                    <h3><?php _e( "Filters" ); ?></h3>
                </div>
            </aside>

            <main id="main" class="large-8 medium-8 cell padding-bottom" role="main">

                <?php get_template_part( 'parts/content', 'groups' ); ?>

            </main> <!-- end #main -->

            <aside class="large-2 medium-2 cell padding-bottom" data-sticky-container>
                <div class="bordered-box">

                    <h3><?php _e( "Priorities" ); ?></h3>

                </div>

            </aside>

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>
