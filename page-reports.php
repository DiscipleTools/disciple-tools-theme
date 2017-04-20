<?php get_header(); ?>

    <div id="content">

        <div id="inner-content" class="row">


            <!-- Breadcrumb Navigation-->
            <nav aria-label="You are here:" role="navigation">
                <ul class="breadcrumbs">
                    <li><a href="/">Dashboard</a></li>
                    <li>
                        <span class="show-for-sr">Current: </span> Reports
                    </li>
                </ul>
            </nav>


            <main id="main" class="large-12 medium-12 columns" role="main">

                <section class="block">
                    <?php dt_chart_bargraph (); ?>
                </section>

            </main>

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>