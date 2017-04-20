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

            <main id="main" class="large-8 medium-8 columns frame" role="main">

                <?php include ('parts/content-contacts-tabs.php') ?>

            </main> <!-- end #main -->

            <aside class="large-4 medium-4 columns ">

                <section class="block">



                </section>

            </aside> <!-- end #aside -->

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->


<?php get_footer(); ?>