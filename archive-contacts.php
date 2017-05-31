<?php get_header(); ?>

    <div id="content">

        <div id="inner-content" class="row">

            <!-- Breadcrumb Navigation-->
            <nav aria-label="You are here:" role="navigation">
                <ul class="breadcrumbs">
                    <li><a href="/">Dashboard</a></li>
                    <li>
                        <span class="show-for-sr">Current: </span> Contacts
                    </li>
                </ul>
            </nav>

            <aside class="large-4 medium-4 columns padding-bottom">

                <?php include ('parts/content-assigned-to.php') ?>

                <?php include 'parts/content-required-updates.php'; ?>


                <section class="block">

                </section>

            </aside> <!-- end #aside -->

            <main id="main" class="large-8 medium-8 columns padding-bottom" role="main">

                <?php include ('parts/content-contacts-tabs.php') ?>

            </main> <!-- end #main -->

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->


<?php get_footer(); ?>