<?php get_header(); ?>

    <div id="content">

        <!-- Breadcrumb Navigation-->
        <nav aria-label="You are here:" role="navigation" class="second-bar">
            <ul class="breadcrumbs">
                <li><a href="/">Dashboard</a></li>
                <li>
                    <span class="show-for-sr">Current: </span> Reports
                </li>
            </ul>
        </nav>

        <div id="inner-content" class="row">

            <main id="main" class="large-12 medium-12 columns" role="main">

                <section class="bordered-box">
                    <?php dt_chart_bargraph(); ?>
                </section>

            </main>

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>
