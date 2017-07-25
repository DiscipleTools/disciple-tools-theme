<?php get_header(); ?>

    <div id="content">

        <div id="inner-content">

            <!-- Breadcrumb Navigation-->
            <nav aria-label="You are here:" role="navigation" class="hide-for-small-only">
                <ul class="breadcrumbs">
                    <li><a href="<?php echo home_url('/'); ?>">Dashboard</a></li>
                    <li>
                        <span class="show-for-sr">Current: </span> Contacts
                    </li>
                </ul>
            </nav>


            <aside class="large-3 medium-3 columns padding-bottom" data-sticky-container>
                <div class="bordered-box sticky" data-sticky data-top-anchor="main:top"  style="width:100%">
                    <h3>Filters</h3>
                </div>

            </aside> <!-- end #aside -->

            <main id="main" class="large-6 medium-6 columns padding-bottom" role="main">

                <?php include ('parts/content-contacts-tabs.php') ?>

            </main> <!-- end #main -->

            <aside class="large-3 medium-3 columns padding-bottom" data-sticky-container>
                <div class="bordered-box sticky" data-sticky data-top-anchor="main:top"  style="width:100%">
                    <h3>Priorities</h3>

                </div>
            </aside>


        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->


<?php get_footer(); ?>
