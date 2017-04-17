<?php

add_action('wp_enqueue_scripts', 'dt_chart_enqueue');

function dt_chart_enqueue () {
    wp_register_script( 'dt_google_chart', 'https://www.gstatic.com/charts/loader.js' );
    wp_enqueue_script( 'dt_google_chart', 'https://www.gstatic.com/charts/loader.js' );
}

?>

<?php get_header(); ?>

    <div id="content">

        <div id="inner-content">

            <div class="row">

                <!-- Breadcrumb Navigation-->
                <nav aria-label="You are here:" role="navigation">
                    <ul class="breadcrumbs">
                        <li><a href="/">Dashboard</a></li>
                        <li>
                            <span class="show-for-sr">Current: </span> Reports
                        </li>
                    </ul>
                </nav>

            </div>



            <div class="row medium-up-2 xlarge-up-4">

                <div class="column column-block">
                    <img src="//placehold.it/600x600" class="thumbnail" alt="">
                </div>

                <div class="column column-block">
                    <img src="//placehold.it/600x600" class="thumbnail" alt="">
                </div>

                <div class="column column-block">
                    <img src="//placehold.it/600x600" class="thumbnail" alt="">
                </div>

                <div class="column column-block">
                    <img src="//placehold.it/600x600" class="thumbnail" alt="">
                </div>


            </div>

            <div class="row medium-up-2 xlarge-up-4">

                <div class="column column-block">
                    <img src="//placehold.it/600x600" class="thumbnail" alt="">
                </div>

                <div class="column column-block">
                    <img src="//placehold.it/600x600" class="thumbnail" alt="">
                </div>

                <div class="column column-block">
                    <img src="//placehold.it/600x600" class="thumbnail" alt="">
                </div>

                <div class="column column-block">
                    <img src="//placehold.it/600x600" class="thumbnail" alt="">
                </div>


            </div>

            <div class="row medium-up-3 large-up-3">

                <div class="column column-block">
                    <img src="//placehold.it/600x600" class="thumbnail" alt="">
                </div>

                <div class="column column-block">
                    <img src="//placehold.it/600x600" class="thumbnail" alt="">
                </div>

                <div class="column column-block">
                    <img src="//placehold.it/600x600" class="thumbnail" alt="">
                </div>

            </div>

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>